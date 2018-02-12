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
 * A StatementItem is a Statement that contains a SnakItem.
 */
class StatementGlobeCoordinate extends Statement {

	/**
	 * @param $property Wikidata property as 'P123'
	 * @param $item     Wikidata item as 'Q1'
	 */
	public function __construct( $property, $latitude, $longitude, $altitude = null, $precision = null, $globe = null ) {
		parent::__construct( new SnakGlobeCoordinate( $property, $latitude, $longitude, $altitude, $precision, $globe ) );
	}

	/**
	 * @param $property Wikidata property as 'P123'
	 * @param $item     Wikidata item as 'Q1'
	 */
	public static function factory( $property, $item ) {
		return new self( $property, $item );
	}
}
