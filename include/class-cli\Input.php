<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2018 Valerio Bozzolan
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

class Input {

	/**
	 * Do a yes/no question
	 *
	 * @param $question string e.g. 'Do you want to continue?'
	 * @param $default string e.g. 'y'
	 * @return string e.g. 'y'
	 */
	public static function yesNoQuestion( $question, $default = 'y' ) {
		return self::askSingleChar( $question, 'yn', $default );
	}

	/**
	 * Do a single character question
	 *
	 * @param $question string e.g. 'Do you want to continue?'
	 * @param $choices string e.g. 'yn'
	 * @param $default string e.g. 'y'
	 */
	public static function askSingleChar( $question, $choices, $default ) {
		$choices = str_split( $choices );
		foreach( $choices as &$choice ) {
			if( $choice === $default ) {
				$choice = strtoupper( $choice );
			}
		}
		return self::askInput( "$question [" . implode( '/', $choices ) . "]", $default );
	}

	/**
	 * Ask for user input
	 *
	 * @param $question string e.g. 'Please insert something'
	 * @param $default string e.g. 'something'
	 * @return string e.g. 'something'
	 */
	public static function askInput( $question, $default = '' ) {
		echo "$question\n";
		return self::read( $default );
	}

	/**
	 * Ask for user standard input
	 *
	 * @param $default string Default string when empty input
	 * @return string
	 */
	public static function read( $default = '' ) {
		$handle = fopen( 'php://stdin', 'r' );
		$line = rtrim( fgets( $handle ) );
		fclose( $handle );
		return '' === $line ? $default : $line;
	}
}
