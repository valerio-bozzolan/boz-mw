<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2018, 2019 Valerio Bozzolan
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

	/**
	 * When a claim has not a main snak, we assume that it has this dummy property.
	 *
	 * It's very useful to send claims to be deleted. It works. asd
	 *
	 * @see https://phabricator.wikimedia.org/T203572
	 */
	const DUMMY_PROPERTY = 'ASD-ASD-ASD';

	/**
	 * Snak
	 *
	 * @var Snak|null
	 */
	//var $mainsnak;

	//var $id;

	/**
	 * Qualifiers collector
	 *
	 * @var Snaks
	 */
	private $qualifiers;

	/**
	 * Constructor
	 *
	 * @param $mainsnak Snak|null Main snak
	 */
	public function __construct( $mainsnak = null ) {
		// avoid to set a NULL value in order to do not send { 'mainsnak': null } to the API
		if( $mainsnak ) {
			$this->setMainsnak( $mainsnak );
		}

		// initialize snaks collector
		$this->qualifiers = new Snaks();
	}

	/**
	 * Get the mainsnak
	 *
	 * @return Snak|null
	 */
	public function getMainsnak() {
		return isset( $this->mainsnak ) ? $this->mainsnak : null;
	}

	/**
	 * Get a property. Also a dummy one. NOW. asd
	 *
	 * @return string
	 */
	public function getPropertyAlsoDummy() {
		$snak = $this->getMainsnak();
		return $snak ? $snak->getProperty() : self::DUMMY_PROPERTY;
	}

	/**
	 * Set the mainsnak
	 *
	 * @param $mainsnak Snak|null
	 * @return self
	 */
	public function setMainsnak( $mainsnak ) {
		$this->mainsnak = $mainsnak;
		return $this;
	}

	/**
	 * Set the qualifiers (snaks)
	 *
	 * @param $qualifiers
	 * @return self
	 */
	public function setQualifiers( Snaks $qualifiers ) {
		$this->qualifiers = $qualifiers;
		return $this;
	}

	/**
	 * Add a qualifier (snak)
	 *
	 * @param object $qualifier Qualifier (snak)
	 * @return array
	 */
	public function addQualifier( Snak $qualifier ) {
		$this->qualifiers->add( $qualifier );
		return $this;
	}

	/**
	 * Check if the claim has at least a qualifier
	 *
	 * @return boolean
	 */
	public function hasQualifiers() {
		return !$this->qualifiers->isEmpty();
	}

	/**
	 * Check if there are qualifiers related to a property
	 *
	 * @param $property string e.g. 'P123'
	 * @return boolean
	 */
	public function hasQualifiersInProperty( $property ) {
		return $this->qualifiers->hasInProperty( $property );
	}

	/**
	 * Get all the qualifiers indexed by property (they are snaks)
	 *
	 * @return array
	 */
	public function getQualifiers() {
		return $this->qualifiers->getAll();
	}

	/**
	 * Get all the qualifiers that are related to a property (they are snaks)
	 *
	 * @param $property string e.g. 'P123'
	 * @return array
	 */
	public function getQualifiersInProperty( $property ) {
		return $this->qualifiers->getInProperty( $property );
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
	 * Check if this claim is marked for removal
	 *
	 * @return array
	 */
	public function isMarkedForRemoval() {
		return isset( $this->remove ) && $this->remove;
	}

	/**
	 * Mark this claim as to be remove
	 *
	 * @see https://www.wikidata.org/w/api.php?action=help&modules=wbeditentity
	 * @return self
	 */
	public function markForRemoval() {
		$this->remove = 1;
		return $this;
	}

	/**
	 * Clone this claim and obtain a claim marked for removal
	 *
	 * @return self
	 */
	public function cloneForRemoval() {
		return ( new self() )
			->setID( $this->getID() )
			->markForRemoval();
	}

	/**
	 * Create a claim from raw data returned from API responses
	 *
	 * @param $data array
	 * @return self
	 */
	public static function createFromData( $data ) {

		// wtf is this shit
		if( ! isset( $data[ 'mainsnak' ] ) ) {
			throw new WrongDataException( __CLASS__ );
		}

		// initialize the claim
		$claim = new static( Snak::createFromData( $data[ 'mainsnak' ] ) );

		// add qualifiers
		if( isset( $data[ 'qualifiers' ] ) ) {
			foreach( $data[ 'qualifiers' ] as $property => $qualifier_raws ) {
				foreach( $qualifier_raws as $qualifier_raw ) {
					$qualifier = Snak::createFromData( $qualifier_raw );
					$claim->addQualifier( $qualifier );
				}
			}
		}

		// claim ID
		if( isset( $data[ 'id' ] ) ) {
			$claim->setID( $data[ 'id' ] );
		}

		return $claim;
	}

	/**
	 * @override
	 */
	public function __toString() {
		$snak = $this->getMainsnak();
		if( $snak ) {
			return $snak->getDataValue();
		}
		$id = $this->getID();
		if( $id && $this->isMarkedForRemoval() ) {
			return "remove claim id = $id";
		}
		throw new \Exception( 'empty claim' );
	}
}
