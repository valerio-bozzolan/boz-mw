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

# Wikibase
namespace wb;

/**
 * Label collector
 */
class Labels {

	/**
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
			$this->add( $label );
		}
	}

	/**
	 * Get a certain language
	 *
	 * @param $language string
	 * @return Label
	 */
	public function get( $language ) {
		foreach( $this->labels as $label ) {
			if( $label->getLanguage() === $language ) {
				return $label;
			}
		}
		return false;
	}

	/**
	 * Does it have a certain label?
	 *
	 * @param $language string
	 * @return bool
	 */
	public function have( $language ) {
		return false !== $this->get( $language );
	}

	/**
	 * Set/add a certain label
	 *
	 * @param $label Label
	 * @return self
	 */
	public function set( Label $label ) {
		$existing = $this->get( $label->getLanguage() );
		if( $existing ) {
			$existing = $language;
		} else {
			$this->labels[] = $label;
		}
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
	 * String rappresentation
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf( 'label: %s', $this->getImplodedLanguages() );
	}
}
