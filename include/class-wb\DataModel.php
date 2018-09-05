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

use \mw\WikibaseSite;

/**
 * Wikibase data container
 */
class DataModel {

	/**
	 * Dependency injection of a Wikibase site
	 *
	 * @var WikibaseSite|null
	 */
	private $site;

	/**
	 * Dependency injection of the entity Q-ID this data refers to
	 *
	 * @var string|null
	 */
	private $entityID;

	/**
	 * @var Labels
	 */
	private $labels;

	/**
	 * @var Descriptions
	 */
	private $descriptions;

	/*
	 * @var Claims
	 */
	private $claims;

	/**
	 * Constructor
	 *
	 * @param $site WikibaseSite
	 * @param $entity_id string
	 */
	public function __construct( $site = null, $entity_id = null ) {
		$this->site         = $site;
		$this->labels       = new Labels();
		$this->descriptions = new Descriptions();
		$this->claims       = new Claims();
		if( $entity_id ) {
			$this->setEntityID( $entity_id );
		}
	}

	/**
	 * Get the Wikibase site
	 *
	 * @return WikibaseSite
	 */
	public function getWikibaseSite() {
		if( ! isset( $this->site ) ) {
			throw new \Exception( 'can\'t access to undefined Wikibase site' );
		}
		return $this->site;
	}

	/**
	 * Set the entity Q-ID
	 *
	 * @param $entity_id string Q-ID
	 * @return self
	 */
	public function setEntityID( $entity_id ) {
		$this->entityID = $entity_id;
		return $this;
	}

	/**
	 * Check if it has the entity Q-ID
	 *
	 * @return bool
	 */
	public function hasEntityID() {
		return isset( $this->entityID );
	}

	/**
	 * Get the entity Q-ID
	 *
	 * @return string
	 */
	public function getEntityID() {
		if( isset( $this->entityID ) ) {
			return $this->entityID;
		}
		throw new \Exception( 'undefined entity ID' );
	}

	/**
	 * Get all the labels
	 *
	 * @return array
	 */
	public function getLabels() {
		return $this->labels->getAll();
	}

	/**
	 * Get all the descriptions
	 *
	 * @return array
	 */
	public function getDescriptions() {
		return $this->descriptions->getAll();
	}

	/**
	 * Get all the claims
	 *
	 * @return array
	 */
	public function getClaims() {
		return $this->claims->getAll();
	}

	/**
	 * Get all the claims grouped by property
	 *
	 * n.b. The claims without a property will be indexed by 'asd'
	 *
	 * @return array
	 */
	public function getClaimsGrouped() {
		return $this->claims->getAllGrouped();
	}

	/**
	 * Add a claim
	 *
	 * @param $claim Claim
	 * @return self
	 */
	public function addClaim( $claim ) {
		$this->claims->add( $claim );
		return $this;
	}

	public function setClaims( $claims ) {
		$this->claims->set( $claims );
		return $this;
	}

	/**
	 * Check if some claims exist in a certain property
	 *
	 * @param $property string
	 * @return bool
	 */
	public function hasClaimsInProperty( $property ) {
		return $this->claims->haveProperty( $property );
	}

	public function getClaimsInProperty( $property ) {
		return $this->claims->getInProperty( $property );
	}

	/**
	 * Count all the claims
	 *
	 * @return int
	 */
	public function countClaims() {
		return $this->claims->count();
	}

	/**
	 * Check if a label of a certain language exists
	 *
	 * @param $language string
	 * @return bool
	 */
	public function hasLabelInLanguage( $language ) {
		return $this->labels->have( $language );
	}

	/**
	 * Set, delete, preserve if it exists, a label.
	 */
	public function setLabel( $label ) {
		$this->labels->set( $label );
		return $this;
	}

	/**
	 * Check if a label of a certain language exists
	 *
	 * @param $language string
	 * @return bool
	 */
	public function hasDescriptionInLanguage( $language ) {
		return $this->descriptions->have( $language );
	}

	/**
	 * Set, delete, preserve if it exists, a description.
	 *
	 * @param $description Description
	 * @return self
	 */
	public function setDescription( $description ) {
		$this->descriptions->set( $description );
		return $this;
	}

	/**
	 * Get a pure data rappresentation
	 *
	 * @param $clear bool Flag to be enabled to strip out empty elements
	 * @return array
	 */
	public function get( $clear = false ) {
		$data = [
			'labels'       => $this->getLabels(),
			'descriptions' => $this->getDescriptions(),
			'claims'       => $this->getClaimsGrouped()
		];
		foreach( $data as $k => $v ) {
			if( 0 === count( $v ) ) {
				unset( $data[ $k ] );
			}
		}
		if( $clear ) {
			$data['clear'] = true;
		}
		return $data;
	}

	/**
	 * Get a JSON rappresentation of this data
	 *
	 * @param $args int Options for json_encode()
	 * @return string
	 */
	public function getJSON( $options = 0 ) {
		return json_encode( $this->get(), $options );
	}

	/**
	 * Get a JSON rappresentation of this data (without empty elements)
	 *
	 * @param $args int Options for json_encode()
	 * @return string
	 */
	public function getJSONClearing( $args = null ) {
		return json_encode( $this->get( true ), $args );
	}

	/**
	 * Print the changes in order to confirm them
	 */
	public function printChanges() {
		if( $this->hasEntityID() ) {
			\cli\Log::info( "-- changes for {$this->getEntityID()} --" );
		}
		$labels = $this->getLabels();
		if( $labels ) {
			\cli\Log::info( "labels:" );
			foreach( $labels as $label ) {
				\cli\Log::info( "\t" . $label );
			}
		}

		$descriptions = $this->getDescriptions();
		if( $descriptions ) {
			mw\Log::info( "descriptions: ");
			foreach( $descriptions as $description ) {
				\cli\Log::info( "\t" . $description );
			}
		}

		$properties = $this->getClaimsGrouped();
		if( $properties ) {
			\cli\Log::info( "claims:" );
			foreach( $properties as $property => $claims ) {
				if( $property === Claim::DUMMY_PROPERTY ) {
					\cli\Log::info( "\twithout property:" );
				} else {
					\cli\Log::info( "\t$property:" );
				}
				foreach( $claims as $claim ) {
					$snak = $claim->getMainsnak();
					if( $snak ) {
						\cli\Log::info( "\t\t" . $snak->getDataValue() );
					} else {
						\cli\Log::info( "\t\t" . $claim );
					}
				}
			}
		}
	}

	/**
	 * Generate an edit summary for the data contained in here
	 *
	 * @return string
	 */
	public function getEditSummary() {
		$changes = [];

		$labels = $this->getLabels();
		if( $labels ) {
			$changes[] = $labels->__toString();
		}

		$descriptions = $this->getDescriptions();
		if( $descriptions ) {
			$changes[] = $descriptions->__toString();
		}

		foreach( $this->getClaims() as $claim ) {
			$snak = $claim->getMainsnak();
			if( $snak ) {
				$changes[] = '+' . $snak;
			} elseif( $claim->isMarkedForRemoval() ) {
				$changes[] = '-claim id=' . $claim->getID();
			} else {
				throw new \Exception( "unexpected undefined main snak, that it's allowed only in claims marked for deletion" );
			}
		}

		return implode( '; ', $changes );
	}

	/**
	 * Obtain an empty clone of this data container
	 *
	 * @return self
	 */
	public function cloneEmpty() {
		$cloned = new self( $this->getWikibaseSite() );
		if( $this->hasEntityID() ) {
			$cloned->setEntityID( $this->getEntityID() );
		}
		return $cloned;
	}

	/**
	 * Edit a Wikibase entity using the wbgetentities API
	 *
	 * It works only if the Wikibase site is specified
	 * You can not specify the 'id' if you specified the entity ID
	 *
	 * @param $data array API data request
	 * @return mixed
	 * @see https://www.wikidata.org/w/api.php?action=help&modules=wbgetentities
	 */
	public function editEntity( $data = [] ) {
		$data = array_replace( [
			'id'      => $this->hasEntityID() ? $this->getEntityID() : null,
			'summary' => $this->getEditSummary(),
			'data'    => $this->getJSON(),
		], $data );
		return $this->getWikibaseSite()->editEntity( $data );
	}

	/**
	 * Static constructor from an array
	 *
	 * @param $data array
	 * @param $site WikibaseSite
	 * @param $entity_id string
	 * @return self
	 */
	public static function createFromData( $data, $site = null, $entity_id = null ) {
		$dataModel = new self( $site, $entity_id );
		if( ! empty( $data[ 'labels' ] ) ) {
			foreach( $data[ 'labels' ] as $label ) {
				$dataModel->setLabel( Label::createFromData( $label ) );
			}
		}
		if( ! empty( $data[ 'descriptions' ] ) ) {
			foreach( $data[ 'descriptions' ] as $description ) {
				$dataModel->setDescription( Description::createFromData( $description ) );
			}
		}
		if( ! empty( $data[ 'claims' ] ) ) {
			foreach( $data[ 'claims' ] as $claims ) {
				foreach( $claims as $claim ) {
					$dataModel->addClaim( Claim::createFromData( $claim ) );
				}
			}
		}
		return $dataModel;
	}

	/**
	 * Static constructor from an object
	 *
	 * @param $object object
	 * @param $site WikibaseSite
	 * @param $entity_id string
	 * @return self
	 */
	public static function createFromObject( $object, $site = null, $entity_id = null ) {
		return self::createFromData( self::object2array( $object ), $site, $entity_id );
	}

	/**
	 * Convert an object to an array
	 *
	 * @param $object object
	 * @return array
	 */
	private static function object2array( $object ) {
		if( ! is_object( $object) && ! is_array( $object ) ) {
			return $object;
		}
		$array = [];
		foreach( $object as $k => $v ) {
			$array[ $k ] = self::object2array( $v );
		}
		return $array;
	}
}
