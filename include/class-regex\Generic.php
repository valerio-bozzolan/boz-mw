<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2018, 2019 Valerio Bozzolan
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

namespace regex;

/**
 * Handle generic regexes
 */
class Generic {

	/**
	 * Create a regex with the first character insensitive
	 *
	 * @param $s string
	 * @param $group_name string|null
	 * @return string
	 */
	public static function firstCaseInsensitive( $s, $group_name = null ) {
		list( $fc, $rest ) = self::splitFirstCase( $s );
		return self::groupNamed( self::insensitiveChar( $fc ), $group_name ) . $rest;
	}

	/**
	 * Match a case insensitive string
	 *
	 * @param $s string
	 * @return string
	 */
	public static function insensitive( $s ) {
		$s = preg_quote( $s );
		return "(?i)$s(?-i)";
	}

	/**
	 * Create a regex with a capturing group with a name
	 *
	 * @param $group string
	 * @param $name string Name or NULL to do not group
	 * @return string
	 */
	public function groupNamed( $group, $name = null ) {
		if( null === $name ) {
			return $group;
		}
		return "(?P<$name>$group)";
	}

	public function groupName( $group_name ) {
		return '${' . $name . '}';
	}

	public static function spaceBurger( $s ) {
		return self::burger( $s, '[_ ]*' );
	}

	public static function tabBurger( $s ) {
		return self::burger( $s, '[ \t]*' );
	}

	public static function newlineBurger( $s ) {
		return self::burger( $s, '[ \t\n]*' );
	}

	/**
	 * Take a character and get a case insensitive regex
	 *
	 * @param $c string
	 * @return string
	 */
	private static function insensitiveChar( $c ) {
		$u = strtoupper( $c );
		$l = strtolower( $c );
		if( $u === $l ) {
			return $c;
		}
		return "[$u$l]";
	}

	/**
	 * Split the string into an array with the first char and the rest
	 *
	 * @param $s string
	 * @return array
	 */
	private static function splitFirstCase( $s ) {
		return [
			substr( $s, 0, 1 ),
			substr( $s, 1    )
		];
	}

	/**
	 * Surround the string with something before and after
	 *
	 * @param $s string
	 * @param $bread string
	 * @return string
	 */
	public function burger( $s, $bread ) {
		return $bread . $s . $bread;
	}
}
