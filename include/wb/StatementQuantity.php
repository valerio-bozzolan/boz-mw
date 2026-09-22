<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2018 Valerio Bozzolan
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
 * A StatementQuantity is a Statement that contains a SnakQuantity.
 */
class StatementQuantity extends Statement {

	/**
	 * @param $property string Property as 'P123'
	 * @param $value mixed amount Any number e.g. 0
	 * @param $wikidata_unit_id string Wikidata ID of a unit e.g. 'Q11573' for metre
	 * @param $upper_bound mixed The $value at its maximum error e.g. +1
	 * @param $lower_bound mixed The $value at its minimum error e.g. -1
	 */
	public function __construct( $property, $amount, $unit, $upper_bound = null, $lower_bound = null ) {
		parent::__construct( new SnakQuantity( $property, $amount, $unit, $upper_bound = null, $lower_bound = null ) );
	}

	/**
	 * @param $property string Property as 'P123'
	 * @param $value mixed amount Any number e.g. 0
	 * @param $wikidata_unit_id string Wikidata ID of a unit e.g. 'Q11573' for metre
	 * @param $upper_bound mixed The $value at its maximum error e.g. +1
	 * @param $lower_bound mixed The $value at its minimum error e.g. -1
	 */
	public static function factory( $property, $amount, $unit, $upper_bound = null, $lower_bound = null ) {
		return new self( $property, $amount, $unit, $upper_bound = null, $lower_bound = null );
	}
}
