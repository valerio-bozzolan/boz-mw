<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017-2023 Valerio Bozzolan, contributors
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
 * A Wikibase Label
 *
 * https://www.wikidata.org/wiki/Wikidata:Glossary#Label
 */
class Label {

	/**
	 * @var string
	 */
	public $language;

	/**
	 * @var string
	 */
	public $value;

	/**
	 * Constructor
	 *
	 * @var $language string Language code
	 * @var $value string Label value
	 */
	public function __construct( $language, $value ) {
		$this->setLanguage( $language )
		     ->setValue( $value );
	}

	/**
	 * Get the language code
	 *
	 * @return string Language code
	 */
	public function getLanguage() {
		return $this->language;
	}

	/**
	 * Get the language value
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Change the language
	 *
	 * This is more an internal method and should be avoided.
	 *
	 * @param $language string Language code
	 * @return self
	 */
	protected function setLanguage( $language ) {
		$this->language = $language;
		return $this;
	}

	/**
	 * Set the label value
	 *
	 * @param $value Label value
	 * @return self
	 */
	public function setValue( $value ) {
		$this->value = $value;
		return $this;
	}

	/**
	 * Create a Label object from raw array data
	 *
	 * @param $data array
	 * @return self
	 */
	public static function createFromData( $data ) {
		if( ! isset( $data['language'], $data['value'] ) ) {
			throw new WrongDataException( self::class );
		}
		return new static( $data['language'], $data['value'] );
	}

	/**
	 * String rappresentation
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf( '%s: %s', $this->getLanguage(), $this->getValue() );
	}
}
