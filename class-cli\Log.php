<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2018 Valerio Bozzolan
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
	 * Verbose sensitive information messages flag
	 */
	public static $SENSITIVE = false;

	/**
	 * Verbose information messages flag
	 *
	 * @var bool
	 */
	public static $DEBUG = false;

	/**
	 * Show a warning
	 *
	 * Use it for errors that can be solved without interaction
	 *
	 * @param $message string
	 */
	public static function warn( $message ) {
		self::log( 'WARN', $message );
	}

	/**
	 * Show a debug information
	 *
	 * Use it to show actions under the hood
	 *
	 * @param $message string
	 */
	public static function info( $message ) {
		if( self::$DEBUG ) {
			self::log( 'DEBUG', $message );
		}
	}

	/**
	 * Show an error
	 *
	 * @param $message string
	 */
	public static function error( $message ) {
		self::log( 'ERROR', $message );
	}

	/**
	 * Show a debug information message that contains sensitive informations
	 *
	 * @param $message_sensitive Message with sensitive informations
	 * @param $message_unsensitive Message secure to be shown
	 */
	public static function sensitive( $message_sensitive, $message_unsensitive ) {
		if( self::$DEBUG ) {
			if( self::$SENSITIVE ) {
				self::log( '!DEBUG!', $message_sensitive );
			} elseif( $message_unsensitive ) {
				self::log( '!DEBUG!', "$message_unsensitive [SENSITIVE DATA HIDDEN]" );
			}
		}
	}

	/**
	 * Show a message
	 *
	 * @param $type string
	 * @param $message string
	 */
	public static function log( $type, $message ) {
		if( isset( $_SERVER['argv'] ) ) {
			printf( "# [%s] \t %s\n", $type, $message );
		} else {
			error_log( "$type $message" );
		}
	}

}
