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
 * A generic Snak is a combination of a property and a datatype + datavalue.
 *
 * A Snak is part of a Claim or a Reference. It's based on a DataValue.
 */
class Snak {

	private $hash;

	private $snaktype;

	private $property;

	private $datatype;

	private $datavalue;

	/**
	 * @param $snaktype string
	 * @param $property string
	 * @param $datatype string
	 * @param $datavalue mixed
	 */
	public function __construct( $snaktype, $property, $datatype, $datavalue = null ) {
		$this->setSnakType(  $snaktype )
		     ->setProperty(  $property )
		     ->setDataType(  $datatype );

		if( null !== $datavalue ) {
			 $this->setDataValue( $datavalue );
		}
	}

	/**
	 * Get the property
	 *
	 * @return string
	 */
	public function getProperty() {
		return $this->property;
	}

	/**
	 * Get the snak type
	 */
	public function getSnakType() {
		return $this->snaktype;
	}

	/**
	 * Get the data type
	 */
	public function getDataType() {
		return $this->datatype;
	}

	/**
	 * Get the data value
	 *
	 * @return DataValue
	 */
	public function getDataValue() {
		return $this->datavalue;
	}

	/**
	 * Set the snak type
	 *
	 * @param $snaktype
	 * @param self
	 */
	public function setSnakType( $snaktype ) {
		$this->snaktype = $snaktype;
		return $this;
	}

	/**
	 * Set the property
	 *
	 * @param $property string
	 * @param self
	 */
	public function setProperty( $property ) {
		$this->property = $property;
		return $this;
	}

	/**
	 * Get an human property label, if available in cache
	 */
	public function getPropertyLabel() {
		return self::propertyLabel( $this->getProperty() );
	}

	/**
	 * Set the data type
	 *
	 * @param $datatype
	 * @param self
	 */
	public function setDataType( $datatype ) {
		$this->datatype = $datatype;
		return $this;
	}

	/**
	 * Set the data value
	 *
	 * @param $datavalue DataValue
	 * @param self
	 */
	public function setDataValue( DataValue $datavalue ) {
		$this->datavalue = $datavalue;
		return $this;
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
	 * Get a wikilink to this property
	 *
	 * @return string
	 */
	protected function getPropertyWLink() {
		$prop  = $this->getProperty();
		$label = $this->getPropertyLabel();
		if( ! $label ) {
			$label = $prop;
		}
		return sprintf( '[[P:%s|%s]]', $prop, $label );
	}

	/**
	 * Try to read the property label from the cache
	 *
	 * @TODO: ask also the site
	 * @return string|false
	 */
	public static function propertyLabel( $property ) {
		return \wm\Wikidata::propertyLabel( $property );
	}

	/**
	 * Create a snak from raw data
	 *
	 * @param $data array
	 * @return self
	 */
	public static function createFromData( $data ) {
		if( ! isset( $data['snaktype'], $data['property'], $data['datatype'] ) ) {
			var_dump( $data );
			throw new WrongDataException( __CLASS__ );
		}
		$snak = new self(
			$data['snaktype'],
			$data['property'],
			$data['datatype']
		);
		if( isset( $data['datavalue'] ) ) {
			$snak->setDataValue( DataValue::createFromData( $data['datavalue'] ) );
		}
		if( isset( $data['hash'] ) ) {
			$snak->setHash( $data['hash'] );
		}
		return $snak;
	}

	/**
	 * Convert this object to an associative array suitable for JSON encoding
	 *
	 * @return array
	 */
	public function toData() {
		$data = [];

		// it may have an hash
		if( $this->hasHash() ) {
			$data['hash'] = $this->getHash();
		}

		$data['snaktype']  = $this->getSnakType();
		$data['property']  = $this->getProperty();
		$data['datatype']  = $this->getDataType();
		$data['datavalue'] = $this->getDataValue();

		if( $data['datavalue'] ) {
			$data['datavalue'] = $data['datavalue']->toData();
		}

		return $data;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return sprintf( '%s: %s',
			$this->getPropertyWLink(),
			$this->getDataValue()
		);
	}
}
