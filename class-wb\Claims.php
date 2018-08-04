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
 * Claim collector
 */
class Claims {

	/**
	 * All the claims
	 *
	 * @TODO: check if it can be declared as private without breaking casts :^)
	 * @var array
	 */
	public $claims = [];

	/**
	 * Constructor
	 *
	 * @param $claims array
	 */
	public function __construct( $claims = [] ) {
		foreach( $claims as $claim ) {
			$this->add( $claim );
		}
	}

	/**
	 * Add a claim
	 *
	 * @param $claim $Claim
	 * @return self
	 */
	public function add( Claim $claim ) {
		$this->claims[] = $claim;
		return $this;
	}

	/**
	 * Count all
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->claims );
	}

	/**
	 * Get all the claims in a certain property
	 *
	 * @param $property string
	 * @return array
	 */
	public function getInProperty( $property ) {
		$claims = [];
		foreach( $this->claims as $claim ) {
			if( $claim->getMainSnak()->getProperty() === $property ) {
				$claims[] = $claim;
			}
		}
		return $claims;
	}

	/**
	 * Check if there are claims in a certain property
	 *
	 * @param $property string
	 * @return bool
	 */
	public function haveProperty( $property ) {
		foreach( $this->claims as $claim ) {
			if( $claim->getMainSnak()->getProperty() === $property ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get all the claims indexed by their property
	 *
	 * @return array
	 */
	public function getAll() {
		$properties = [];
		foreach( $this->claims as $claim ) {
			$property = $claim->getMainSnak()->getProperty();
			if( ! isset( $properties[ $property ] ) ) {
				$properties[ $property ] = [];
			}
			$properties[ $property ][] = $claim;
		}
		return $properties;
	}
}
