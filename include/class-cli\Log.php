<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2018, 2019, 2020 Valerio Bozzolan
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.

# Command line interface
namespace cli;

/**
 * Show log messages (when in CLI)
 */
class Log {

	/**
	 * Normal information messages flag
	 *
	 * @var bool
	 */
	public static $INFO = true;

	/**
	 * Verbose information messages flag
	 *
	 * @var bool
	 */
	public static $DEBUG = false;

	/**
	 * Verbose sensitive information messages flag
	 *
	 * @var bool
	 */
	public static $SENSITIVE = false;

	/**
	 * Format in use when we are in command line mode
	 *
	 * Order of arguments:
	 *   Date, Type, Message
	 *
	 * @var string
	 */
	public static $FORMAT_COMMAND_LINE = '[%1$s][%2$s] %3$s';

	/**
	 * Format in use when we are in webserver mode
	 *
	 * Order of arguments:
	 *   Date, Type, Message
	 *
	 * As default the Date is not printed because usually
	 * Apache or Nginx already append it.
	 *
	 * @var string
	 */
	public static $FORMAT_WEBSERVER = '%2$s %3$s';

	/**
	 * Format used to eventually print dates in the log
	 *
	 * @var string
	 */
	public static $DATE_FORMAT = 'Y-m-d H:i:s';

	/**
	 * If defined, this file will be used to append log shit
	 *
	 * @var string
	 */
	public static $DEDICATED_FILEPATH = null;

	/**
	 * Show a warning
	 *
	 * Use it for errors that can be solved without interaction
	 *
	 * @param $message string
	 * @param $args array arguments
	 */
	public static function warn( $message, $args = [] ) {
		self::log( 'WARN', $message, $args );
	}

	/**
	 * Show a debug information
	 *
	 * Use it to show actions under the hood
	 *
	 * @param $message string
	 * @param $args array arguments
	 */
	public static function info( $message, $args = [] ) {
		if( self::$INFO ) {
			self::log( 'INFO', $message, $args );
		}
	}

	/**
	 * Show a debug information
	 *
	 * Use it to show actions under the hood
	 *
	 * @param $message string
	 * @param $args array arguments
	 */
	public static function debug( $message, $args = [] ) {
		if( self::$DEBUG ) {
			self::log( 'DEBUG', $message, $args );
		}
	}

	/**
	 * Show an error
	 *
	 * @param $message string
	 * @param $args array arguments
	 */
	public static function error( $message, $args = [] ) {
		self::log( 'ERROR', $message, $args );
	}

	/**
	 * Show a debug information message that contains sensitive informations
	 *
	 * @param $message_sensitive Message with sensitive informations
	 * @param $message_unsensitive Message secure to be shown
	 * @param $args array arguments
	 */
	public static function sensitive( $message_sensitive, $message_unsensitive, $args = [] ) {
		if( self::$DEBUG ) {
			if( self::$SENSITIVE ) {
				self::log( '!DEBUG!', $message_sensitive, $args );
			} elseif( $message_unsensitive ) {
				self::log( '!DEBUG!', "$message_unsensitive [SENSITIVE DATA HIDDEN]", $args );
			}
		}
	}

	/**
	 * Show a message
	 *
	 * @param $type string
	 * @param $message string
	 * @param $args array arguments
	 */
	public static function log( $type, $message, $args = [] ) {
		// default arguments
		$args = array_replace( [
			'newline' => true,
		], $args );

		// eventually end with a newline
		if( $args['newline'] ) {
			$message .= "\n";
		}

		// check if we are in command line mode
		$cli = isset( $_SERVER['argv'] );

		// are we in command line?
		$format = $cli
			? self::$FORMAT_COMMAND_LINE
			: self::$FORMAT_WEBSERVER;

		// in command line print a nice format with a date
		$date = date( self::$DATE_FORMAT );
		$msg = sprintf( $format, $date, $type, $message );

		// check if we have to write into a dedicated file (default to no)
		if( self::$DEDICATED_FILEPATH ) {

			// try to append something in the log file
			$status = file_put_contents( self::$DEDICATED_FILEPATH, $msg, FILE_APPEND | LOCK_EX );

			// no log no party
			if( $status === false ) {
				throw new \Exception( sprintf(
					"apologize Sir but we are very sad to note that we cannot write in your damn log file '%s'",
					self::$DEDICATED_FILEPATH
				) );
			}

		} else {

			// do not write into a dedicated file

			// check if we are in command line mode
			if( $cli ) {
				// in command line, just print everything to stdout
				echo $msg;
			} else {
				// in a webserver, just print everything in the syslog
				error_log( $msg );
			}
		}
	}

}
