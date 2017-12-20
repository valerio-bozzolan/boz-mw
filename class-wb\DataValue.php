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
 * A generic DataValue is part of a Snak.
 */
class DataValue {

	var $type;
	var $value;

	/**
	 * @param $type  string Type
	 * @param $value mixed  Value
	 */
	public function __construct( $type, $value ) {
		$this->setType(  $type )
		     ->setValue( $value );
	}

	public function getType() {
		return $this->type;
	}

	public function getValue() {
		return $this->value();
	}

	public function setType( $type ) {
		$this->type = $type;
		return $this;
	}

	public function setValue( $value ) {
		$this->value = $value;
		return $this;
	}
}
