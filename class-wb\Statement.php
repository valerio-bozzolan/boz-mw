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
 * A Statement is a type of Claim with references and a rank.
 *
 * @see https://www.wikidata.org/wiki/Wikidata:Glossary#Statement
 */
class Statement extends Claim {

	//var $id;
	var $type = 'statement';
	var $rank = 'normal';
	var $references = [];

	public function getType() {
		return $this->type;
	}

	public function getRank() {
		return $this->rank;
	}

	public function getReferences() {
		return $this->references;
	}

	public function hasID() {
		return isset( $this->id );
	}

	public function getID() {
		return $this->id;
	}

	public function setType( $type ) {
		$this->type = $type;
		return $this;
	}

	public function setRank( $rank ) {
		$this->rank = $rank;
		return $this;
	}

	public function setReferences( $references ) {
		$this->references = $references;
		return $this;
	}

	public function setID( $id ) {
		$this->id = $id;
	}

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
			$statement->setReferences( $data['references'] );
		}
		return $statement;
	}
}
