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
 * A DataValue for a quantity.
 */
class DataValueQuantity extends DataValue {

	/**
	 * @param $value mixed amount Any number e.g. 0
	 * @param $wikidata_unit_id string Wikidata ID of a unit e.g. 'Q11573' for metre
	 * @param $upper_bound mixed The $value at its maximum error e.g. +1
	 * @param $lower_bound mixed The $value at its minimum error e.g. -1
	 */
	public function __construct( $amount, $wikidata_unit_id, $upper_bound = null, $lower_bound = null ) {
		if (($wikidata_unit_id == null) or (strtoupper(substr($wikidata_unit_id, 0)) == "Q")) {
			$value = [
				'amount' => self::sign($amount),
				'unit'   => "1"
			];
		} else {
			$value = [
				'amount' => self::sign($amount),
				'unit'   => "http://www.wikidata.org/entity/$wikidata_unit_id"
			];
		}
		if( null !== $upper_bound ) {
			$value['upperBound'] = self::int2string( $upper_bound );
		}
		if( null !== $lower_bound ) {
			$value['lowerBound'] = self::int2string( $lower_bound );
		}
		parent::__construct( DataType::QUANTITY, $value );
	}

	/**
	 * Convert a number to a prefixed string e.g. 1 is converted to '+1'
	 *
	 * @param $n mixed
	 * @return string
	 */
	private static function sign( $n ) {
		return $n > 0 ? '+' . $n : '-' . $n;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$value = $this->getValue();
		return $value[ 'amount' ];
	}
}
