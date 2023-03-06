<?php
# boz-mw - Another MediaWiki API handler in PHP
# Copyright (C) 2018, 2019, 2020, 2021 Valerio Bozzolan
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

# command line interface
namespace cli;

/**
 * A command line parameter
 *
 * A difference between a 'parameter' and an argument is that a
 * 'parameter' can have a value and that value is its 'argument'.
 */
class Param {

	/**
	 * To specify that the parameter has no any value
	 */
	const NO_VALUE = 1;

	/**
	 * To specify that the parameter has an optional value
	 */
	const OPTIONAL_VALUE = 2;

	/**
	 * To specify that the parameter has a mandatory value
	 */
	const REQUIRED_VALUE = 4;

	/**
	 * Param long name
	 *
	 * @var string|null
	 */
	private $longName;

	/**
	 * Param short name (one letter)
	 *
	 * @var string|null
	 */
	private $shortName;

	/**
	 * Can be Param::NO_VALUE|Param::OPTIONAL_VALUE|Param::REQUIRED_VALUE
	 *
	 * @var int
	 */
	private $valueKind = 1;

	/**
	 * Parameter argument
	 *
	 * @var mixed
	 */
	private $value;

	/**
	 * Description
	 *
	 * @var string|null
	 */
	private $description;

	/**
	 * Default value
	 *
	 * @var string|null
	 */
	private $defaultVal;

	/**
	 * Constructor
	 *
	 * @param $long_name   string Long name like 'custom-option-name'
	 * @param $short_name  string Short name like 'c'
	 * @param $value_kind  int    Value kind (1 = NO VALUE, 2 = OPTIONAL VALUE, 3 = REQUIRED VALUE)
	 * @param $description string Short human description
	 * @param $default_val string Default value
	 */
	public function __construct( $long_name, $short_name = null, $value_kind = null, $description = null, $default_val = null ) {
		$this->longName = $long_name;
		$this->setShortName( $short_name );
		$this->setValueKind( $value_kind );
		$this->setDescription( $description );
		$this->defaultVal = $default_val;
	}

	/**
	 * Set the short name
	 *
	 * @param $short_name string|null
	 */
	public function setShortName( $short_name ) {
		if( strlen( $short_name ) > 1 ) {
			throw new \InvalidArgumentException( 'short option must be 1 characters long' );
		}
		$this->shortName = $short_name;
	}

	/**
	 * Set the value kind
	 *
	 * @param $value_kind
	 */
	public function setValueKind( $value_kind ) {
		$value_kind = (int) $value_kind;
		switch( $value_kind ) {
			case self::NO_VALUE:
			case self::OPTIONAL_VALUE:
			case self::REQUIRED_VALUE:
				$this->valueKind = $value_kind;
				break;
			default:
				throw new \InvalidArgumentException( 'unexpected value kind' );
		}
	}

	/**
	 * Has a long name?
	 *
	 * @return bool
	 */
	public function hasLongName() {
		return null !== $this->getLongName();
	}

	/**
	 * Get the long name
	 *
	 * @return string|null
	 */
	public function getLongName() {
		return $this->longName;
	}

	/**
	 * Has a short name?
	 *
	 * @return bool
	 */
	public function hasShortName() {
		return null !== $this->getShortName();
	}

	/**
	 * Get the short name
	 *
	 * @return string|null
	 */
	public function getShortName() {
		return $this->shortName;
	}

	/**
	 * Check if the option has a default value
	 *
	 * @return boolean
	 */
	public function hasDefaultValue() {
		return null !== $this->getDefaultValue();
	}

	/**
	 * Get the default value (if any)
	 *
	 * @return string|null
	 */
	public function getDefaultValue() {
		return $this->defaultVal;
	}

	/**
	 * Check if a name belongs to this parameter
	 *
	 * @param string $name Long or short parameter name
	 * @return bool
	 */
	public function isName( $name ) {
		return $this->getLongName() === $name || $this->getShortName() === $name;
	}

	/**
	 * Get the kind of the value
	 *
	 * @return int
	 */
	public function getValueKind() {
		return $this->valueKind;
	}

	/**
	 * Get the parameter's argument or a default one
	 *
	 * @param $default_val string Default value
	 * @return string|null
	 */
	public function getValue( $default_val = null ) {

		// eventually inherit the default value
		if( $default_val === null ) {
			$default_val = $this->getDefaultValue();
		}

		return $this->value ? $this->value : $default_val;
	}

	/**
	 * Set the parameter's argument
	 *
	 * @param $value string|null
	 */
	public function setValue( $value ) {
		$this->value = $value;
	}

	/**
	 * Check if the description exists
	 *
	 * @return bool
	 */
	public function hasDescription() {
		return null !== $this->getDescription();
	}

	/**
	 * Get the parameter's argument
	 *
	 * @param $description string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Set the parameter's argument
	 *
	 * @param $description string
	 */
	public function setDescription( $description ) {
		$this->description = $description;
	}

	/**
	 * Is the value of a certain kind?
	 *
	 * @param $kind int
	 * @return bool
	 */
	protected function isValueKind( $kind ) {
		return $this->getValueKind() === $kind;
	}

	/**
	 * Is the value optional?
	 *
	 * @return bool
	 */
	public function isValueOptional() {
		return $this->isValueKind( self::OPTIONAL_VALUE );
	}

	/**
	 * Is the value required?
	 *
	 * @return bool
	 */
	public function isValueRequired() {
		return $this->isValueKind( self::REQUIRED_VALUE );
	}

	/**
	 * Check if it can't have a value
	 *
	 * @return bool
	 */
	public function isFlag() {
		return $this->isValueKind( self::NO_VALUE );
	}

	/**
	 * Get the long option format
	 *
	 * @return string
	 */
	public function getLongOptFormat() {
		$v = '';
		if( $this->hasLongName() ) {
			$v = $this->getLongName();
			if( $this->isValueKind( self::OPTIONAL_VALUE ) ) {
				$v .= ':';
			} elseif( $this->isValueKind( self::REQUIRED_VALUE ) ) {
				$v .= '::';
			}
		}
		return $v;
	}

	/**
	 * Get the short option format
	 *
	 * @return string
	 */
	public function getShortOptFormat() {
		$v = '';
		if( $this->hasShortName() ) {
			$v = $this->getShortName();
			if( $this->isValueKind( self::OPTIONAL_VALUE ) ) {
				$v .= ':';
			} elseif( $this->isValueKind( self::REQUIRED_VALUE ) ) {
				$v .= '::';
			}
		}
		return $v;
	}

}
