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
 * A Snak collector.
 */
class Snaks {

	var $snaks = [];

	public function __construct( $snaks = [] ) {
		$this->set( $snaks );
	}

	public function add( Snak $snak ) {
		$this->snaks[] = $snak;
		return $this;
	}

	public function set( $snaks ) {
		foreach( $snaks as $snak ) {
			$this->add( $snak );
		}
		return $this;
	}

	public function get() {
		$properties = [];
		foreach( $this->snaks as $snak ) {
			$property = $snak->getProperty();
			if( ! isset( $properties[ $property ] ) ) {
				$properties[ $property ] = [];
			}
			$properties[ $property ][] = $snak;
		}
		return $properties;
	}
}
