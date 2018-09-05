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
 * A Claim consists of a Snak and Qualifiers.
 *
 * Optionally, it can have qualifiers.
 *
 * @see https://www.wikidata.org/wiki/Wikidata:Glossary#Claim
 */
class Claim {

	var $mainsnak;
	//var $id;
	//var $qualifiers;

	/**
	 * Constructor
	 *
	 * @param $mainsnak Snak Main snak
	 */
	public function __construct( $mainsnak ) {
		$this->setMainsnak( $mainsnak );
	}

	/**
	 * Get the mainsnak
	 *
	 * @return Snak|null
	 */
	public function getMainsnak() {
		return $this->mainsnak;
	}

	/**
	 * Get the mainsnak
	 *
	 * @return Snak|null
	 */
	public function hasQualifiers() {
		return ! empty( $this->qualifiers );
	}

	/**
	 * Get the qualifiers
	 */
	public function getQualifiers() {
		return $this->qualifiers;
	}

	/**
	 * Set the mainsnak
	 *
	 * @param $mainsnak object
	 * @return self
	 */
	public function setMainsnak( Snak $mainsnak ) {
		$this->mainsnak = $mainsnak;
		return $this;
	}

	/**
	 * Set the qualifiers
	 *
	 * @param $qualifiers
	 * @return self
	 */
	public function setQualifiers( $qualifiers ) {
		$this->qualifiers = $qualifiers;
		return $this;
	}

	/**
	 * Set the claim ID
	 *
	 * @param $id string
	 * @return string
	 */
	public function setID( $id ) {
		$this->id = $id;
		return $this;
	}

	/**
	 * Get the claim ID
	 *
	 * @return string
	 */
	public function getID() {
		if( empty( $this->id ) ) {
			throw new \Exception( 'missing id' );
		}
		return $this->id;
	}

	/**
	 * Create a claim from raw data
	 *
	 * @param $data array
	 * @return self
	 */
	public static function createFromData( $data ) {
		if( ! isset( $data['mainsnak'] ) ) {
			throw new WrongDataException( __CLASS__ );
		}
		$claim = new static( Snak::createFromData( $data['mainsnak'] ) );
		if( isset( $data['qualifiers'] ) ) {
			$claim->setQualifiers( $data['qualifiers'] );
		}
		return $claim;
	}
}
