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
 * A Statement is a type of Claim with references and a rank.
 *
 * @see https://www.wikidata.org/wiki/Wikidata:Glossary#Statement
 */
class Statement extends Claim {

	/**
	 * ID of the statement (if any)
	 *
	 * @var string|null
	 */
	private $id;

	/**
	 * Type of the statement
	 *
	 * I think that it's hardcoded to 'statement' in Wikibase.
	 *
	 * @var string
	 */
	private $type = 'statement';

	/**
	 * Rank of this statement
	 *
	 * @var string
	 */
	private $rank = 'normal';

	/**
	 * Get the type
	 *
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Get the rank
	 *
	 * @return string
	 */
	public function getRank() {
		return $this->rank;
	}

	/**
	 * Check if this statement has an ID
	 *
	 * @return boolean
	 */
	public function hasID() {
		return isset( $this->id );
	}

	/**
	 * Get the ID (if any)
	 *
	 * @return string|null
	 */
	public function getID() {
		return $this->id;
	}

	/**
	 * Get the ID (if any)
	 *
	 * @return string|null
	 */
	public function setID( $id ) {
		$this->id = $id;
	}

	/**
	 * Set the type
	 *
	 * @param string $type
	 * @return self
	 */
	public function setType( $type ) {
		$this->type = $type;
		return $this;
	}

	/**
	 * Set a rank
	 *
	 * @param  string $rank
	 * @return self
	 */
	public function setRank( $rank ) {
		$this->rank = $rank;
		return $this;
	}

	/**
	 * Set the rank as "preferred"
	 *
	 * @return self
	 */
	public function setRankPreferred() {
		return $this->setRank( 'preferred' );
	}

	/**
	 * Set the rank as "deprecated"
	 *
	 * @return self
	 */
	public function setRankDeprecated() {
		return $this->setRank( 'deprecated' );
	}

	/**
	 * Create a statement from raw data returned from API responses
	 *
	 * @param $data array
	 * @return self
	 */
	public static function createFromData( $data ) {
		if( ! isset( $data['type'], $data['rank'] ) ) {
			throw new WrongDataException( __CLASS__ );
		}
		$statement = parent::createFromData( $data );
		$statement->setType( $data['type'] );
		$statement->setRank( $data['rank'] );
		if( $data['id'] ) {
			$statement->setID( $data['id'] );
		}
		if( $data['references'] ) {
			$statement->setReferences( References::createFromData( $data['references'] ) );
		}
		return $statement;
	}

	/**
	 * Convert this object to an associative array suitable for JSON encoding
	 *
	 * @return array
	 */
	public function toData() {
		$data = parent::toData();

		// statement ID
		if( $this->hasID() ) {
			$data['id'] = $this->getID();
		}

		// statement type
		$data['type'] = $this->getType();

		// statement rank
		$data['rank'] = $this->getRank();

		return $data;
	}

}
