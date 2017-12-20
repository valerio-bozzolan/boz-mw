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

class DataModel {

	private $labels;
	private $descriptions;
	private $claims;

	public function __construct() {
		$this->labels = [];
		$this->descriptions = [];
		$this->claims = new Claims();
	}

	public function getLabels() {
		return $this->labels;
	}

	public function getDescriptions() {
		return $this->descriptions;
	}

	public function getClaims() {
		return $this->claims->get();
	}

	public function addClaim( $claim ) {
		$this->claims->add( $claim );
		return $this;
	}

	public function setClaims( $claims ) {
		$this->claims->set( $claims );
		return $this;
	}

	/**
	 * Set, delete, preserve if it exists, a label.
	 */
	public function setLabel( $language, $value, $action = 'only-if-missing' ) {
		$label = new LabelAction( $language, $value );
		switch( $action ) {
			case 'only-if-missing':
				$label->pleasePreserve();
				break;
			case 'delete':
				$label->pleaseDelete();
			case 'overwrite':
				break;
			default:
				throw new Exception();
		}
		$this->labels[] = $label;
		return $this;
	}

	/**
	 * Set, delete, preserve if it exists, a description.
	 */
	public function setDescription( $language, $value, $action = 'only-if-missing' ) {
		$description = new DescriptionAction( $language, $value );
		switch( $action ) {
			case 'only-if-missing':
				$description->pleasePreserve();
				break;
			case 'delete':
				$description->pleaseDelete();
			case 'overwrite':
				break;
			default:
				throw new Exception();
		}
		$this->descriptions[] = $description;
		return $this;
	}

	public function countClaims() {
		return $this->claims->count();
	}

	public function get() {
		return [
			'labels'       => $this->getLabels(),
			'descriptions' => $this->getDescriptions(),
			'claims'       => $this->getClaims()
		];
	}

	public function getJSON( $args = null ) {
		return json_encode( $this->get(), $args );
	}
}
