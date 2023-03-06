<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2019 Valerio Bozzolan
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
 * A DataValue for a monolingual text
 *
 * @see https://www.mediawiki.org/wiki/Wikibase/DataModel#Monolingual_texts
 */
class DataValueMonolingualText extends DataValue {

	/**
	 * Constructor
	 *
	 * @param string $language Language (e.g. 'it', 'en', ...)
	 * @param string $text     Text
	 */
	public function __construct( $language, $text ) {
		parent::__construct( DataType::MONOLINGUAL_TEXT, [
			'text'     => $text,
			'language' => $language,
		] );
	}

	/**
	 * Get the text
	 *
	 * @return string
	 */
	public function getText() {
		return $this->getValue()->text;
	}

	/**
	 * Get the language
	 *
	 * @return string
	 */
	public function getLanguage() {
		return $this->getValue()->language;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$text     = $this->getText();
		$language = $this->getLanguage();
		return "$text [$language]";
	}
}
