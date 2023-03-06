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
 * The first character can be capitalized.
 */
class TitlePartCapitalized extends TitlePart {

	/**
	 * @TODO Just set ucfirst only if wiki is configured to do it
	 */
	public static function normalize( $s ) {
		$s = ucfirst( $s );
		return parent::normalize( $s );
	}

	/**
	 * @deprecated
	 */
	public function getRegexFirstCaseInsensitive( $first_case_group_name = null, $delimiter = null ) {
		return \regex\Generic::firstCaseInsensitive(
			parent::getRegex( $delimiter ),
			$first_case_group_name
		);
	}

}
