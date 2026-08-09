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

use \mw\WikibaseSite;
use \wb\Label;
use \wb\Description;

/**
 * Wikibase data container
 *
 * It can be used to abstract a Wikibase Entity and
 * remove/add labels, descriptions, attributes,
 * references, etc.
 *
 * It has shortcuts to directly talk with the APIs,
 * generate a smart edit summary, printing changes, etc.
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

	/*
	 * @var Sitelinks
	 */
	private $sitelinks;

	/**
	 * Constructor
	 *
	 * @param $site WikibaseSite
	 * @param $entity_id string Entity Q-ID
	 */
	public function __construct( $site = null, $entity_id = null ) {
		$this->site         = $site;
		$this->labels       = new Labels();
		$this->descriptions = new Descriptions();
		$this->claims       = new Claims();
		$this->setSitelinks(  new Sitelinks() );
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
	 * Get all the sitelinks
	 *
	 * @return array
	 */
	public function getSitelinks() {
		return $this->sitelinks;
	}

	/**
	 * Set all the sitelinks
	 *
	 * @param $sitelinks
	 * @return array
	 */
	public function setSitelinks( Sitelinks $sitelinks ) {
		$this->sitelinks = $sitelinks;
		return $this;
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
	 * Add a Claim
	 *
	 * @param $claim Claim
	 * @return self
	 */
	public function addClaim( $claim ) {
		$this->claims->add( $claim );
		return $this;
	}

	/**
	 * Set claims
	 *
	 * @param $claims array
	 * @return self
	 */
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

	/**
	 * Get claims in a property
	 *
	 * @param $property string
	 * @return array
	 */
	public function getClaimsInProperty( $property ) {
		return $this->claims->getInProperty( $property );
	}

	/**
	 * Has sitelink in site
	 *
	 * @param $site string
	 * @return boolean
	 */
	public function hasSitelinkInSite( $site ) {
		return false !== $this->getSitelinkInSite( $site );
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
	 * Check if it's empty
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return
			   ! $this->countClaims()
			&& ! $this->getLabels()
			&& ! $this->getDescriptions()
			&& ! $this->sitelinks->getAll();
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
	 * Get a label in a specific language
	 *
	 * @param $language Language code, as accepted by Wikidata
	 * @return string|null
	 */
	public function getLabelValue( $language ) {
		return $this->labels->getLanguageValue( $language );
	}

	/**
	 * Set a label in a specific language
	 *
	 * @param $language Language code, as accepted by Wikidata
	 * @param $value Language value
	 * @return self
	 */
	public function setLabelValue( $language, $value ) {
		$this->labels->setLanguageValue( $language, $value );
		return $this;
	}

	/**
	 * Set, delete, preserve if it exists, a label.
	 *
	 * You may want to use setLabelValue() instead that is more user-friendly.
	 *
	 * @param $label Label object
	 * @return self
	 */
	public function setLabel( $label ) {
		$this->labels->set( $label );
		return $this;
	}

	/**
	 * Check if a description exists in a certain language
	 *
	 * @param $language string Language code, as accepted by Wikidata
	 * @return bool
	 */
	public function hasDescriptionInLanguage( $language ) {
		return $this->descriptions->have( $language );
	}

	/**
	 * Get a description in a specific language
	 *
	 * @param $language Language code, as accepted by Wikidata
	 * @return string|null
	 */
	public function getDescriptionValue( $language ) {
		return $this->descriptions->getLanguageValue( $language );
	}

	/**
	 * Set a label in a specific language
	 *
	 * @param $language Language code, as accepted by Wikidata
	 * @param $value Language value
	 * @return self
	 */
	public function setDescriptionValue( $language, $value ) {
		$this->descriptions->setLanguageValue( $language, $value );
		return $this;
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
			'labels'       => $this->getLabels(),       // TODO: $this->getLabelsData()
			'descriptions' => $this->getDescriptions(), // TODO: $this->getDescriptionsData()
			'sitelinks'    => $this->sitelinks->toData(),
			'claims'       => $this->claims->toData(),
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
	 *
	 * @return self
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
			\cli\Log::info( "descriptions: ");
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
						\cli\Log::info( "\t\t" . $snak->getDataValue()->toPrintableWikitext( $this->site ) );
					} else {
						\cli\Log::info( "\t\t" . $claim->toPrintableWikitext( $site ) );
					}
				}
			}
		}
		return $this;
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
			$changes[] = $this->labels->__toString();
		}

		$descriptions = $this->getDescriptions();
		if( $descriptions ) {
			$changes[] = $this->descriptions->__toString();
		}

		$sitelinks = $this->getSitelinks();
		if( $sitelinks->getAll() ) {
			$changes[] = $this->sitelinks->__toString();
		}

		foreach( $this->getClaimsGrouped() as $property => $claims ) {
			foreach( $claims as $claim ) {
				$snak = $claim->getMainsnak();
				if( $snak ) {
					$changes[] = '+' . $snak->toPrintableWikitext( $this->site );
				} elseif( $claim->isMarkedForRemoval() ) {
					//$changes[] = '-claim id=' . $claim->getID(); // too much verbose as edit summary
					$changes[] = '-claim ' . $snak->getPropertyWLink( $this->site );
				} else {
					throw new \Exception( "unexpected undefined main snak, that it's allowed only in claims marked for deletion" );
				}
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
	 * It can also create an Entity.
	 *
	 * @param $data array API data request
	 * 	Allowed extensions:
	 * 		summary.pre  Add something before the summary
	 * 		summary.post Add something after  the summary
	 * @return mixed
	 * @see https://www.wikidata.org/w/api.php?action=help&modules=wbeditentity
	 */
	public function editEntity( $data = [] ) {

		// can auto-generate a summary
		if( !isset( $data['summary'] ) ) {
			$data['summary'] = $this->getEditSummary();
		}

		// eventually prefill ID
		if( !isset( $data['id'] ) ) {
			$data['id'] = $this->hasEntityID() ? $this->getEntityID() : null;
		}

		// eventually prefill to-be-saved data
		if( !isset( $data['data'] ) ) {
			$data['data'] = $this->getJSON();
		}

		return $this->getWikibaseSite()->editEntity( $data );
	}

		/**
	 * Removes Wikibase claims using the wbremoveclaims API
	 *
	 * You do not need to send the CSRF 'token' and the 'action' parameters.
	 *
	 * @see https://www.wikidata.org/w/api.php?action=help&modules=wbremoveclaims
	 *
	 * @param $data array API data request
	 * 	Allowed extensions:
	 * 		summary.pre  Add something before the summary
	 * 		summary.post Add something after  the summary
	 * @return mixed
	 */
	public function removeClaim( $data = [] ) {

		// can auto-generate a summary
		if( !isset( $data['summary'] ) ) {
			$data['summary'] = $this->getEditSummary();
		}

		// eventually prefill ID
		if( !isset( $data['id'] ) ) {
			$data['id'] = $this->hasEntityID() ? $this->getEntityID() : null;
		}

		// tell the wiki that a bot is performing the edit
		if( !isset( $data['bot'] ) ) {
			$data['bot'] = true;
		}

		return $this->getWikibaseSite()->removeClaim( $data );
	}

	/**
	 * Static constructor from an associative array
	 *
	 * This method is used to import an array obtained
	 * from JSON-decoding the Wikibase wbgetentity API result
	 *
	 * @param $data array Response of wbgetentity API result
	 * @param $site WikibaseSite
	 * @param $entity_id string
	 * @return self
	 * @see https://www.wikidata.org/w/api.php?action=help&modules=wbgetentities
	 */
	public static function createFromData( $data, $site = null, $entity_id = null ) {
		$dataModel = new self( $site, $entity_id );
		if( ! empty( $data[ 'labels' ] ) ) {
			// TODO: Labels::createFromData()
			foreach( $data[ 'labels' ] as $label ) {
				$dataModel->setLabel( Label::createFromData( $label ) );
			}
		}
		if( ! empty( $data[ 'descriptions' ] ) ) {
			// TODO: Descriptions::createFromData()
			foreach( $data[ 'descriptions' ] as $description ) {
				$dataModel->setDescription( Description::createFromData( $description ) );
			}
		}

		/**
		 * Process the claims... or statements... or whatever damn name they have!
		 *
		 * See https://gitpull.it/T223
		 * See https://phabricator.wikimedia.org/T149410
		 */
		$claims = $data['claims'] ?? $data['statements'] ?? null;
		if( !empty( $claims ) ) {
			// TODO: Claims::createFromData()
			foreach( $claims as $claims ) {
				foreach( $claims as $claim ) {
//					$dataModel->addClaim( Claim::createFromData( $claim ) );

					// these are really statements ("type": "statement")
					$dataModel->addClaim( Statement::createFromData( $claim ) );
				}
			}
		}

		if( ! empty( $data[ 'sitelinks' ] ) ) {
			$dataModel->setSitelinks( Sitelinks::createFromData( $data[ 'sitelinks' ] ) );
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
