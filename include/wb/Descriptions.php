<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017-2023 Valerio Bozzolan
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
 * Description collector
 */
class Descriptions extends Labels {

	/**
	 * Create a single element from a language and its value
	 *
	 * @param $language string Language code
	 * @param $value string Label value
	 * @return Description
	 */
	protected function createSingleFromLanguageValue( $language, $value ) {
		return new Description( $language, $value );
	}

	/**
	 * String rappresentation
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf( 'description: %s', $this->getImplodedLanguages() );
	}

}
