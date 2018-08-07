<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017 Valerio Bozzolan
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

	//var $hash;
	var $snaktype;
	var $property;
	var $datatype;
	var $datavalue;

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

	public function getProperty() {
		return $this->property;
	}

	public function getSnakType() {
		return $this->snaktype;
	}

	public function getDataType() {
		return $this->datatype;
	}

	public function getDataValue() {
		return $this->datavalue;
	}

	public function setSnakType( $snaktype ) {
		$this->snaktype = $snaktype;
		return $this;
	}

	public function setProperty( $property ) {
		$this->property = $property;
		return $this;
	}

	public function setDataType( $datatype ) {
		$this->datatype = $datatype;
		return $this;
	}

	public function setDataValue( DataValue $datavalue ) {
		$this->datavalue = $datavalue;
		return $this;
	}

	public function hasHash() {
		return isset( $this->hash );
	}

	public function getHash() {
		return $this->hash;
	}

	public function setHash( $hash ) {
		$this->hash = $hash;
	}

	public static function createFromData( $data ) {
		if( ! isset( $data['snaktype'], $data['property'], $data['datatype'] ) ) {
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
	 * @return string
	 */
	public function __toString() {
		return sprintf( '[[P:%s]]: %s',
			$this->getProperty(),
			$this->getDataValue()
		);
	}
}
