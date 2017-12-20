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
 * A Claim consists of a property and a value (Snak).
 *
 * Optionally, it can have qualifiers.
 *
 * @see https://www.wikidata.org/wiki/Wikidata:Glossary#Claim
 */
class Claim {

	var $property;
	var $mainsnak;
	//var $qualifiers;

	/**
	 * @param $property string Property as 'P123'
	 * @param $mainsnak Snak   Main snak
	 */
	public function __construct( $property, $mainsnak ) {
		$this->setProperty( $property )
		     ->setMainsnak( $mainsnak );
	}

	public function getProperty() {
		return $this->property;
	}

	public function getMainsnak() {
		return $this->property;
	}

	public function getQualifiers() {
		return $this->property;
	}

	public function setProperty( $property ) {
		$this->property = $property;
		return $this;
	}

	public function setMainsnak( Snak $mainsnak ) {
		$this->mainsnak = $mainsnak;
		return $this;
	}

	public function setQualifiers( $qualifiers ) {
		$this->qualifiers = $qualifiers;
		return $this;
	}
}
