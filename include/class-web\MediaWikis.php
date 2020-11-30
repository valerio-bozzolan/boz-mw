<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2018, 2019 Valerio Bozzolan
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

# websites in the Internet
namespace web;

/**
 * All the MediaWiki instances in the Internet :^)
 */
abstract class MediaWikis {

	/**
	 * Get all the registered MediaWiki classes
	 *
	 * @return array
	 */
	protected static function allClasses() {
		return [
			\wm\WikipediaIt      ::class,
			\wm\Wikidata         ::class,
			\wm\Commons          ::class,
			\wm\MetaWiki         ::class,
			\web\LandscapeforWiki::class,
		];
	}

	/**
	 * Get all the registered MediaWiki instances
	 *
	 * @generator
	 */
	public static function all() {
		foreach( self::allClasses() as $classname ) {
			yield $classname::instance();
		}
	}

	/**
	 * Get a specific MediaWiki instance from its UID
	 *
	 * @param $uid string
	 * @return mw\StaticSite|false
	 */
	public static function findFromUID( $uid ) {
		foreach( self::all() as $one ) {
			if( $one::UID === $uid ) {
				return $one;
			}
		}
		return false;
	}

	/**
	 * Get all the registered MediaWiki UIDs ordered alphabetically
	 *
	 * @return array
	 */
	public static function allUIDs() {
		$all = [];

		foreach( self::all() as $wiki ) {
			$all[] = $wiki::UID;
		}

		sort( $all );

		return $all;
	}
}
