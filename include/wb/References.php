<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2019 Valerio Bozzolan
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
 * References collector
 *
 * It is very similar to the claims collector.
 *
 * References are assigned to a claim.
 */
class References {

	private $references = [];

	/**
	 * Constructor
	 *
	 * @param array $references
	 */
	public function __construct( $references = [] ) {
		foreach( $references as $reference ) {
			$this->add( $reference );
		}
	}

	/**
	 * Add a reference
	 *
	 * @param  object $reference
	 * @return self
	 */
	public function add( Reference $reference ) {
		$this->references[] = $reference;
		return $this;
	}

	/**
	 * Get all the references
	 *
	 * @return array
	 */
	public function getAll() {
		return $this->references;
	}

	/**
	 * Check if it does not contain references
	 *
	 * @return boolean
	 */
	public function isEmpty() {
		return empty( $this->references );
	}

	/**
	 * Convert this object to an associative array suitable for JSON encoding
	 *
	 * @return array
	 */
	public function toData() {
		$data = [];
		foreach( $this->getAll() as $reference ) {
			$data[] = $reference->toData();
		}
		return $data;
	}

	/**
	 * Constructor from a raw object retrieved from API results
	 *
	 * @param  array $references_raw
	 * @return self
	 */
	public static function createFromData( $references_raw ) {
		$references = new self();
		foreach( $references_raw as $reference_raw ) {
			$references->add( Reference::createFromData( $reference_raw ) );
		}
		return $references;
	}
}
