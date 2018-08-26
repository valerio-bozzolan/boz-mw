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

namespace regex;

class Generic {

	public static function firstCaseInsensitive( $s, $group_name = null ) {
		list( $fc, $rest ) = self::splitFirstCase( $s );
		return self::groupNamed( self::insensitiveChar( $fc ), $group_name ) . $rest;
	}

	public function groupNamed( $group, $group_name = null ) {
		if( null === $group_name ) {
			return $group;
		}
		return "(?P<$group_name>$group)";
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

	private static function insensitiveChar( $c ) {
		$u = strtoupper( $c );
		$l = strtolower( $c );
		if( $u === $l ) {
			return $c;
		}
		return "[$u$l]";
	}

	private static function splitFirstCase( $s ) {
		return [
			substr( $s, 0, 1 ),
			substr( $s, 1    )
		];
	}

	private function burger( $s, $bread ) {
		return $bread . $s . $bread;
	}
}
