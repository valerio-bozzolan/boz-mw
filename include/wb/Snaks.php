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
 * A Snak collector
 *
 * @since 2019-07-18
 */
class Snaks {

	/**
	 * All the Snaks indexed by property
	 */
	private $snaks = [];

	/**
	 * Constructor
	 *
	 * @param array $snaks Initial set of snaks
	 */
	public function __construct( $snaks = [] ) {
		foreach( $snaks as $snak ) {
			$this->add( $snak );
		}
	}

	/**
	 * Add a Snak
	 *
	 * @param object $snak
	 * @return this
	 */
	public function add( Snak $snak ) {

		// eventually init the property container of snaks
		$property = $snak->getProperty();
		if( !isset( $this->snaks[ $property ] ) ) {
			$this->snaks[ $property ] = [];
		}

		// append
		$this->snaks[ $property ][] = $snak;

		return $this;
	}

	/**
	 * Get all the snaks from a certain property
	 *
	 * @param string $property Property name e.g. 'P123'
	 * @return array
	 */
	public function getInProperty( $property ) {
		if( $this->hasInProperty( $property ) ) {
			return $this->snaks[ $property ];
		}
		return [];
	}

	/**
	 * Check if there are some snaks in this property
	 */
	public function hasInProperty( $property ) {
		return !empty( $this->snaks[ $property ] );
	}

	/**
	 * Check if there are not snaks
	 *
	 * @return boolean
	 */
	public function isEmpty() {
		return empty( $this->snaks );
	}

	/**
	 * Get all the snaks indexed by property
	 *
	 * @return array
	 */
	public function getAllByProperty() {
		return $this->snaks;
	}

	/**
	 * Count all the snaks
	 *
	 * @return int
	 */
	public function count() {
		$n = 0;
		foreach( $this->getAll() as $property => $snaks ) {
			$n += count( $snaks );
		}
		return $n;
	}

	/**
	 * Convert this object to an associative array suitable for JSON encoding
	 *
	 * @return array
	 */
	public function toData() {
		$data = [];
		foreach( $this->getAllByProperty() as $property => $snaks ) {
			$data[ $property ] = [];
			foreach( $snaks as $snak ) {
				$data[ $property ][] = $snak->toData();
			}
		}
		return $data;
	}

}
