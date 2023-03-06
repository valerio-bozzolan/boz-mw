<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2018, 2019, 2020, 2021, 2022 Valerio Bozzolan
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
	 * The 'mainsnak' is a Snak
	 *
	 * @var Snak|null
	 */
	private $mainsnak;

	/**
	 * Claim ID
	 *
	 * @var string|null
	 */
	private $id;

	/**
	 * Claim rank
	 */
	private $rank;

	/**
	 * Qualifiers collector
	 *
	 * @var Snaks
	 */
	private $qualifiers;

	/**
	 * References collector
	 *
	 * @TODO: it has also an hash!
	 *
	 * @var References
	 */
	private $references;

	/**
	 * Constructor
	 *
	 * @param $mainsnak Snak|null Main snak
	 */
	public function __construct( $mainsnak = null ) {

		// eventually initialize mainsnak
		if( $mainsnak ) {
			$this->setMainsnak( $mainsnak );
		}

		// initialize snaks collector
		$this->qualifiers = new Snaks();

		// initialize references collector
		$this->references = new References();
	}

	/**
	 * Check if there is a mainsnak
	 *
	 * @return boolean
	 */
	public function hasMainsnak() {
		return isset( $this->mainsnak );
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
	 * @param  object $qualifiers
	 * @return self
	 */
	public function setQualifiers( Snaks $qualifiers ) {
		$this->qualifiers = $qualifiers;
		return $this;
	}

	/**
	 * Set the references
	 *
	 * @param  object $references
	 * @return self
	 */
	public function setReferences( References $references ) {
		$this->references = $references;
		return $this;
	}

	/**
	 * Add a qualifier (snak)
	 *
	 * @param object $qualifier Qualifier (snak)
	 * @return self
	 */
	public function addQualifier( Snak $qualifier ) {
		$this->qualifiers->add( $qualifier );
		return $this;
	}

	/**
	 * Add a reference
	 *
	 * @param object $reference Reference
	 * @return self
	 */
	public function addReference( Reference $reference ) {
		$this->references->add( $reference );
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
	 * Check if the claim has at least a reference
	 *
	 * @return boolean
	 */
	public function hasReferences() {
		return !$this->references->isEmpty();
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
	 * Check if there are references related to a property
	 *
	 * @param $property string e.g. 'P123'
	 * @return boolean
	 */
	public function hasReferencesInProperty( $property ) {
		return $this->references->hasInProperty( $property );
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
	 * Get all the qualifiers indexed by property (they are snaks)
	 *
	 * @return array
	 */
	public function getReferences() {
		return $this->references->getAll();
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
	 * Get all the references that are related to a property (they are snaks)
	 *
	 * @param $property string e.g. 'P123'
	 * @return array
	 */
	public function getReferencesInProperty( $property ) {
		return $this->references->getInProperty( $property );
	}

	/**
	 * Check if there is an ID
	 *
	 * @return boolean
	 */
	public function hasID() {
		return isset( $this->id );
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
		if( !$this->hasID() ) {
			throw new \Exception( 'missing id' );
		}
		return $this->id;
	}

	/**
	 * Check if the Rank is set
	 *
	 * @return boolean
	 */
	public function hasRank() {
		return isset( $this->rank );
	}

	/**
	 * Get the claim rank
	 *
	 * @return string
	 */
	public function getRank() {
		return $this->rank;
	}

	/**
	 * Set the Claim rank
	 *
	 * @param  string $rank
	 * @return self
	 */
	public function setRank( $rank ) {
		$this->rank = $rank;
		return $this;
	}

	/**
	 * Check if the Rank of the Claim is 'deprecated'
	 *
	 * @return boolean
	 */
	public function isDeprecated() {
		return $this->getRank() === 'deprecated';
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
	 * Set the rank as "preferred"
	 *
	 * @return self
	 */
	public function setRankPreferred() {
		return $this->setRank( 'preferred' );
	}

	/**
	 * Set the rank as "deprecated"
	 *
	 * @return self
	 */
	public function setRankDeprecated() {
		return $this->setRank( 'deprecated' );
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
		if( ! isset( $data['mainsnak'] ) ) {
			throw new WrongDataException( self::class );
		}

		// initialize the claim
		$claim = new static( Snak::createFromData( $data[ 'mainsnak' ] ) );

		// add qualifiers
		if( isset( $data['qualifiers'] ) ) {
			foreach( $data['qualifiers'] as $property => $qualifier_raws ) {
				foreach( $qualifier_raws as $qualifier_raw ) {
					$qualifier = Snak::createFromData( $qualifier_raw );
					$claim->addQualifier( $qualifier );
				}
			}
		}

		// add references
		if( isset( $data['references'] ) ) {
			$claim->setReferences( References::createFromData( $data['references'] ) );
		}

		// claim ID
		if( isset( $data[ 'id' ] ) ) {
			$claim->setID( $data[ 'id' ] );
		}

		// set rank
		if( isset( $data['rank'] ) ) {
			$claim->setRank( $data['rank'] );
		}

		return $claim;
	}

	/**
	 * Convert this object to an associative array suitable for JSON encoding
	 *
	 * @return array
	 */
	public function toData() {

		$data = [];

		// mainsnak
		if( $this->hasMainsnak() ) {
			$data['mainsnak'] = $this->getMainsnak()->toData();
		}

		// id
		if( $this->hasID() ) {
			$data['id'] = $this->getID();
		}

		// rank
		if( $this->hasRank() ) {
			$data['rank'] = $this->getRank();
		}

		// qualifiers
		if( $this->hasQualifiers() ) {
			$data['qualifiers'] = $this->qualifiers->toData();
		}

		// references
		if( $this->hasReferences() ) {
			$data['references'] = $this->references->toData();
		}

		return $data;
	}

	/**
	 * Get a wikitext-compatible version of this value
	 *
	 * This may be awared about which is the wiki that will contain this value,
	 * in order to properly choose a correct permalink in wikilinks etc.
	 *
	 * See https://gitpull.it/T221
	 *
	 * @param $site You can eventually specify in which site you want to print this value
	 */
	public function toPrintableWikitext( \mw\Site $site = null ) {

		// if it's a Snak, just print the DataValue
		$snak = $this->getMainsnak();
		if( $snak ) {
			return $snak
				->getDataValue()
				->toPrintableWikitext( $site );
		}

		// eventually show that this Claim is marked for removal
		$id = $this->getID();
		if( $id && $this->isMarkedForRemoval() ) {
			return "remove claim id = $id";
		}

		// I really can't figure out what do you want to do with me
		throw new \Exception( 'empty claim' );
	}

	/**
	 * @override
	 */
	public function __toString() {
		throw new Exception("asd");
		return $this->toPrintableWikitext();
	}
}
