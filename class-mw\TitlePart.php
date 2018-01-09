<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017 Valerio Bozzolan
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

# MediaWiki
namespace mw;

/**
 * A string part of a wikilink, a namespace, etc.
 */
class TitlePart {

	private $s;

	public function __construct( $s ) {
		$this->set( $s );
	}

	public static function factory( $s ) {
		return new static( $s );
	}

	public function get() {
		return $this->s;
	}

	public function set( $s ) {
		$this->s = static::normalize( $s );
		return $this;
	}

	public function isEmpty() {
		return '' === $this->get();
	}

	public function getRegex( $delimiter = null ) {
		return self::regex( $this->get(), $delimiter );
	}

	public static function normalize( $s ) {
		$s = trim( $s );
		$s = self::underscore2space( $s );
		return $s;
	}

	public static function underscore2space( $s ) {
		return str_replace('_', ' ', $s);
	}

	public static function regex( $s, $delimiter ) {
		$s = preg_quote( $s, $delimiter );

		// These are all valids
		// [[Main page]]
		// [[Main      page]]
		// [[Main______page]]
		// [[Main_ _ _ page]]
		return str_replace( ' ', '[ _]*', $s );
	}
}
