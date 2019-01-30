<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2019 Valerio Bozzolan
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
 *
 * The first character is always capitalized.
 */
class NsPart extends TitlePartCapitalized {

	/**
	 * Get a regex that matches this namespace part
	 *
	 * @return string
	 */
	public function getRegex( $unused = null ) {
		$s = $this->get();
		if( $s ) {
			// normal namespace
			$s = \regex\Generic::insensitive( $this->get() );
			$s = "{$s}[ _]*:";
		} else {
			// main namespace, it can start with ':'
			$s = ':?';
		}
		return $s;
	}

}
