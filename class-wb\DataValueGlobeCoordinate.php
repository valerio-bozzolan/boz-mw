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

# Wikibase DataValue
namespace wb;

/**
 * A DataValue for a globe coordinate.
 */
class DataValueGlobeCoordinate extends DataValue {

	/**
	 * @param $latitude float
	 * @param $longitude float
	 * @param $precision float
	 * @param $altitude float
	 * @param $globe string
	 */
	public function __construct( $latitude, $longitude, $precision, $altitude = null, $globe = null ) {
		if( null === $globe ) {
			$globe = 'Q2';
		}
		parent::__construct( DataType::GLOBE_COORDINATE, [
			'latitude'  => $latitude,
			'longitude' => $longitude,
			'precision' => $precision,
			'altitude'  => $altitude,
			'globe'     => "http://www.wikidata.org/entity/$globe"
		] );
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$value = $this->getValue();
		return sprintf( '%f %f',
			$value['latitude'],
			$value['longitude']
		);
	}
}
