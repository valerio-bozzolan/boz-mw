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

# Wikibase
namespace wb;

/**
 * Exception throw when something in a Wikibase API data is unexpected
 */
class WrongDataException extends \Exception {

	/**
	 * Constructor
	 *
	 * @param string $subject Class name or subject of the problem
	 * @param string $message Some additional details
	 */
	public function __construct( $subject, $message = null, $code = 0, Throwable $previous = null ) {

		// build an error message
		$message_prefix = sprintf( "invalid %s data", $subject );
		if( $message ) {
			$message = "$message_prefix: $message";
		} else {
			$message = $message_prefix;
		}

		// call the standard parent constructor
		parent::__construct( $message, $code, $previous );
	}
}
