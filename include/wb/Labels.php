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
 * Label collector
 */
class Labels {

	/**
	 * Labels indexed by language code
	 *
	 * @var array
	 */
	private $labels = [];

	/**
	 * Constructor
	 *
	 * @param $labels array
	 */
	public function __construct( $labels = [] ) {
		foreach( $labels as $label ) {
			$this->set( $label );
		}
	}

	/**
	 * Get a certain language
	 *
	 * @param $language string
	 * @return Label
	 */
	public function get( $language ) {
		return $this->labels[ $language ] ?? false;
	}

	/**
	 * Get a certain Label value by its language
	 *
	 * @param $language string
	 * @return string The language value or NULL
	 */
	public function getLanguageValue( $language ) {
		$value = null;
		$label = $this->get( $language );
		if( $label ) {
			$value = $label->getValue();
		}
		return $value;
	}

	/**
	 * Get a certain Label value by its language
	 *
	 * @param $language string
	 * @param string The language value or NULL
	 * @return self
	 */
	public function setLanguageValue( $language, $value ) {
		$label = $this->createSingleFromLanguageValue( $language, $value );
		return $this->set( $label );
	}

	/**
	 * Do our labels have this one?
	 *
	 * @param $language string
	 * @return bool
	 */
	public function have( $language ) {
		return $this->get( $language ) !== false;
	}

	/**
	 * Set/add a certain Label
	 *
	 * @param $label Label
	 * @return self
	 */
	public function set( Label $label ) {
		$lang_code = $label->getLanguage();
		$this->labels[ $lang_code ] = $label;
		return $this;
	}

	/**
	 * Get all the labels
	 *
	 * @return array
	 */
	public function getAll() {
		return $this->labels;
	}

	/**
	 * Get all the languages imploded
	 *
	 * @param $glue string
	 * @return string
	 */
	protected function getImplodedLanguages( $glue = ',' ) {
		$all = $this->getAll();
		$codes = [];
		foreach( $all as $label ) {
			$codes[] = $label->getLanguage();
		}
		return implode( $glue, $codes );
	}

	/**
	 * Create a single element from a language and its value
	 *
	 * @param $language string Language code
	 * @param $value string Label value
	 * @return Label
	 */
	protected function createSingleFromLanguageValue( $language, $value ) {
		return new Label( $language, $value );
	}

	/**
	 * String rappresentation
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf( 'label: %s', $this->getImplodedLanguages() );
	}
}
