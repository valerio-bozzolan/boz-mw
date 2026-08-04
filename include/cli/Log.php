<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017-2026 Valerio Bozzolan
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
 *
 * This classed was initially designed as a static class,
 * but we should slowly use it as an instance.
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
	 * Default maximum length of a message in command line
	 *
	 * If the message is largher than this, will be split in lines.
	 *
	 * NOTE: This does not take into consideration the prefix (date).
	 *
	 * @deprecated
	 */
	public static $CLI_MAX_MSG_LEN = 80;

	/**
	 * Cache for the available terminal columns.
	 *
	 * @var int|false|null False means unitialized. Null means not available. Int means available.
	 */
	private static $CACHE_AVAILABLE_TERM_COLS = false;

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

		// check if we are in command line mode
		$cli = isset( $_SERVER['argv'] );

		// are we in command line?
		$format = $cli
			? self::$FORMAT_COMMAND_LINE
			: self::$FORMAT_WEBSERVER;

		// in command line print a nice format with a date
		$date = date( self::$DATE_FORMAT );
		$message_formatted = self::formatMessageLine( $format, $date, $type, $message );

		// eventually end with a newline
		if( $args['newline'] ) {
			$message_formatted .= "\n";
		}

		// check if we have to write into a dedicated file (default to no)
		if( self::$DEDICATED_FILEPATH ) {

			// try to append something in the log file
			$status = file_put_contents( self::$DEDICATED_FILEPATH, $message_formatted, FILE_APPEND );

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

				// but break terminal lines
				$lines = self::splitMessageInLines($message, $format, $date, $type);
				foreach( $lines as $i => $line ) {
					// Print this line to standard output, as it fits the available space.
					echo self::formatMessageLine($format, $date, $type, "$line\n", $i);
				}

			} else {
				// in a webserver, just print everything in the syslog
				error_log( $message_formatted );
			}
		}
	}

	/**
	 * Get the a cached number of terminal COLUMNS, or null if columns cannot be estimated.
	 *
	 * This is generally useful to print a decent terminal output, without exceding.
	 *
	 * @return int|null
	 */
	public static function availableTerminalColumns() {
		if (self::$CACHE_AVAILABLE_TERM_COLS === false) {
			self::$CACHE_AVAILABLE_TERM_COLS = self::parseAvailableTerminalColumns();
		}
		return self::$CACHE_AVAILABLE_TERM_COLS;
	}

	/**
	 * Force the unavailability of terminal columns.
	 */
	public static function doNotFillTerminalColumns() {
		self::$CACHE_AVAILABLE_TERM_COLS = null;
	}

	/**
	 * Parse the number of terminal COLUMNS at runtime,
	 * or null if columns cannot be estimated.
	 *
	 * This method should be considered internal and should not be used directly.
	 * See availableTerminalColumns() instead, which is cached.
	 *
	 * @return int|null
	 */
	public static function parseAvailableTerminalColumns() {
		$cols = (int)getenv('COLUMNS');

		if ($cols <= 1) {
			$cols = null;
		}

		if ($cols === null) {
			try {
				if (php_sapi_name() === 'cli') {
					// Use standard unix-like utility 'tput' to get terminal columns.
					// When the command is not available (frequent in Kubernetes),
					// suppress the standard error, as otherwise its passed through
					// (and this is undocumented behaviour of shell_exec().. lol).
					// Maybe there is an easier way to STFU, working also in non-unix systems.
					$cols = (int)@shell_exec('tput cols 2>/dev/null');
					if ($cols <= 1) {
						$cols = null;
					}
				}
			} catch(Throwable $e) {
				// Silently ignore. Probably the shell is not available.
			}
		}

		return $cols;
	}

	/**
	 * Format a command line message
	 *
	 * @param string $format
	 * @param string $date
	 * @param string $type
	 * @param string $message
	 * @param int $line_n For multi-line messages, this is the line number, starting from zero.
	 * @return string
	 */
	private static function formatMessageLine(string $format, string $date, string $type, string $message, int $line_n = 0): string {
		// For multi-line messages, indent a bit the consequent lines.
		if ($line_n) {
			$message = " {$message}";
		}
		return sprintf( $format, $date, $type, $message );
	}

	/**
	 * Split a message in terminal lines.
	 *
	 * The lines are designed in a way that, even when prefixed with our log format,
	 * lines will still be contained in your small terminal horizontal space.
	 *
	 * @TODO: uniform the parameters with formatMessageLine().
	 *
	 * @param string $message
	 * @param string $format
	 * @param string $date
	 * @param string $type
	 * @return array
	 */
	private static function splitMessageInLines(string $message, string $format, string $date, string $type) {

		$all = [];

		// The message should fit your terminal.
		// The format prefix must be considered to fit the terminal.
		$terminal_columns = self::availableTerminalColumns();
		if ($terminal_columns) {
			$message_prefix = self::formatMessageLine($format, $date, $type, '');
			$message_prefix_len = mb_strlen($message_prefix);
			$terminal_columns -= $message_prefix_len;
			if ($terminal_columns <= 10) {
				$terminal_columns = null;
			}
		}

		// split by lines
		$lines = explode( "\n", $message );
		foreach( $lines as $i => $line ) {
			// try do not overlap in your terminal (for more cuteness)
			$parts = null;
			if ($terminal_columns) {
				$parts = [];

				// The first line part can fit the whole terminal space.
				$parts[] = mb_substr($line, 0, $terminal_columns);

				// Remaining line parts will be padded with an extra space.
				$remaining_line = mb_substr($line, $terminal_columns);
				$terminal_columns--;
				$remaining_parts = mb_str_split($remaining_line, $terminal_columns);
				$parts = array_merge($parts, $remaining_parts);
			} else {
				$parts = [ $line ];
			}

			foreach ($parts as $part) {

				// do not trim lines: spaces are sometime useful to indent better
				// $part = trim( $part );

				if( $part ) {
					$all[] = $part;
				}
			}
		}

		return $all;
	}

}
