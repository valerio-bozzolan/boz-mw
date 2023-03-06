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
 * A Snak for a Wikibase global coordinate.
 *
 * @see https://www.wikidata.org/wiki/Special:ListDatatypes
 */
class SnakGlobeCoordinate extends Snak {

	/**
	 * @param $property Wikidata property as 'P123'
	 * @param $latitude float
	 * @param $longitude float
	 * @param $precision float
	 * @param $altitude float
	 * @param $globe string
	 */
	public function __construct( $property, $latitude, $longitude, $precision, $altitude = null, $globe = null ) {
		return parent::__construct( 'value', $property, 'globe-coordinate',
			new DataValueGlobeCoordinate( $latitude, $longitude, $precision, $altitude, $globe )
		);
	}
}
