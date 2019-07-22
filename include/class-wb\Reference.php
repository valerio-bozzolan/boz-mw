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
 * Reference
 *
 * A reference is a collection of snaks.
 */
class Reference {

	/**
	 * Identifier of this reference
	 *
	 * @var string
	 */
	private $hash;

	/**
	 * All the snaks
	 *
	 * @var object
	 */
	private $snaks;

	/**
	 * Snaks order
	 *
	 * It's an array of property names. e.g. [ 'P123' , ... ]
	 *
	 * @var array
	 */
	private $snaksOrder;

	/**
	 * Constructor
	 *
	 * @param array $snaks
	 */
	public function __construct( $snaks = [] ) {
		$this->snaks = new Snaks( $snaks );
	}

	/**
	 * Check if the snak has an hash
	 *
	 * @return bool
	 */
	public function hasHash() {
		return isset( $this->hash );
	}

	/**
	 * Get the hash
	 *
	 * @return string|null
	 */
	public function getHash() {
		return $this->hash;
	}

	/**
	 * Set the hash
	 *
	 * @param $hash string
	 * @return self
	 */
	public function setHash( $hash ) {
		$this->hash = $hash;
		return $this;
	}

	/**
	 * Add a snak
	 *
	 * @param  object $snak
	 * @return self
	 */
	public function add( Snak $snak ) {
		$this->snaks->add( $snak );
		return $this;
	}

	/**
	 * Count all the snaks
	 *
	 * @return int
	 */
	public function count() {
		return $this->snaks->count();
	}

	/**
	 * Get all the snaks in a certain property
	 *
	 * @param $property string
	 * @return array
	 */
	public function getSnaksInProperty( $property ) {
		return $this->snaks->getInProperty( $property );
	}

	/**
	 * Check if there are snaks in a certain property
	 *
	 * @param $property string
	 * @return bool
	 */
	public function hasSnaksInProperty( $property ) {
		return $this->snaks->hasInProperty( $property );
	}

	/**
	 * Get all the snaks indexed by property
	 *
	 * @return array
	 */
	public function getSnaksByProperty() {
		return $this->snaks->getAllByProperty();
	}

	/**
	 * Check if the snaks order is specified
	 *
	 * @return boolean
	 */
	public function hasSnaksOrder() {
		return isset( $this->snaksOrder );
	}

	/**
	 * Check if the reference is empty
	 *
	 * @return boolean
	 */
	public function isEmpty() {
		return $this->snaks->isEmpty();
	}

	/**
	 * Get the snaks order
	 *
	 * @return array|null
	 */
	public function getSnaksOrder() {
		return $this->snaksOrder;
	}

	/**
	 * Set the snaks order
	 *
	 * @param  array $properties
	 * @return self
	 */
	public function setSnaksOrder( $properties ) {
		$this->snaksOrder = $properties;
		return $this;
	}

	/**
	 * Constructor from a raw object retrieved from API results
	 *
	 * @param  object $reference_raw
	 * @return self
	 */
	public static function createFromData( $reference_raw ) {
		$reference = new self();

		// reference snaks
		if( !isset( $reference_raw['snaks'] ) ) {
			throw new WrongDataException( 'bad reference object: no snaks field' );
		}
		foreach( $reference_raw['snaks'] as $property => $snaks_raw ) {
			foreach( $snaks_raw as $snak_raw ) {
				$snak = Snak::createFromData( $snak_raw );
				$reference->add( $snak );
			}
		}

		// reference hash
		if( isset( $reference_raw['hash'] ) ) {
			$reference->setHash( $reference_raw['hash'] );
		}

		// reference snaks order (array of properties)
		if( isset( $reference_raw['snaks-order'] ) ) {
			$reference->setSnaksOrder( $reference_raw['snaks-order'] );
		}

		return $reference;
	}

	/**
	 * Convert this object to an associative array suitable for JSON encoding
	 *
	 * @return array
	 */
	public function toData() {
		$data = [];

		// reference hash
		if( $this->hasHash() ) {
			$data['hash'] = $this->getHash();
		}

		// reference snaks
		$data['snaks'] = $this->snaks->toData();

		// snaks order (property list)
		if( $this->hasSnaksOrder() ) {
			$data['snaks-order'] = $this->getSnaksOrder();
		}

		return $data;
	}
}
