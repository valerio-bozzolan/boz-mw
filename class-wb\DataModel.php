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
 * Wikibase data container
 */
class DataModel {

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
	 */
	public function __construct() {
		$this->labels       = new Labels();
		$this->descriptions = new Descriptions();
		$this->claims       = new Claims();
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
	 * @TODO rename to hasLabelInLanguage()
	 * @param $language string
	 * @return bool
	 */
	public function hasLabelsInLanguage( $language ) {
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
	 * @TODO rename to hasDescriptionInLanguage()
	 * @param $language string
	 * @return bool
	 */
	public function hasDescriptionsInLanguage( $language ) {
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
			'claims'       => $this->getClaims()
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
	 * Static constructor from an array
	 *
	 * @param $data array
	 * @return self
	 */
	public static function createFromData( $data ) {
		$dataModel = new self();
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
	 * @return self
	 */
	public static function createFromObject( $object ) {
		return self::createFromData( self::object2array( $object ) );
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
