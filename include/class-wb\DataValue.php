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
 * A generic DataValue is part of a Snak
 */
class DataValue {

	/**
	 * Type of the DataValue
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Value of the DataValue
	 *
	 * @var array
	 */
	private $value;

	/**
	 * Constructor
	 *
	 * @param $type  string Type
	 * @param $value mixed  Value
	 */
	public function __construct( $type, $value ) {
		$this->setType(  $type )
		     ->setValue( $value );
	}

	/**
	 * Get the type
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Get the value
	 *
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Set the type
	 */
	public function setType( $type ) {
		$this->type = $type;
		return $this;
	}

	/**
	 * Set the value
	 *
	 * @param  mixed $value
	 * @return self
	 */
	public function setValue( $value ) {
		$this->value = $value;
		return $this;
	}

	/**
	 * Static constructor from a standard object
	 *
	 * @param  object $data
	 * @return self
	 */
	public static function createFromData( $data ) {
		if( ! isset( $data['type'], $data['value'] ) ) {
			throw new WrongDataException( __CLASS__ );
		}
		return new self( $data['type'], $data['value'] );
	}

	/**
	 * Export this object to an associative array suitable for JSON-encoding
	 *
	 * @return array
	 */
	public function toData() {
		return [
			'type'  => $this->getType(),
			'value' => $this->getValue(),
		];
	}

	/**
	 * Get this object in form of a string
	 *
	 * @return string
	 */
	public function __toString() {
		return json_encode( $this->getValue() );
	}
}
