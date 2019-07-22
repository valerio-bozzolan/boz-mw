<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2018, 2019 Valerio Bozzolan
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
 * A Snak for a string.
 */
class SnakTime extends Snak {

	/**
	 * Constructor
	 *
	 * Note that: <Hour, minute, and second are currently unused and should always be 00.> WHAT THE FUCK I'M READING HOLY CRAP WIKIBASE
	 *
	 * @param $property string Property as 'P123'
	 * @param $time string example "+1539-00-00T00:00:00Z" As default it's the current datetime
	 * @param $precision int As default it's 11 (days)
	 *	0: 1 Gigayear
	 * 	1: 100 Megayears
	 * 	2: 10 Megayears
	 * 	3: Megayear
	 * 	4: 100 Kiloyears
	 * 	5: 10 Kiloyears
	 * 	6: Kiloyear
	 * 	7: 100 years
	 * 	8: 10 years
	 * 	9: years
	 * 	10: months
	 * 	11: days
	 * @param $args array example:
	 * 	timezone: 0,
	 * 	before: 0,
	 * 	after: 0,
	 * 	precision: 9,
	 * 	calendarmodel: "Q1985786"
	 * @see https://www.mediawiki.org/wiki/Wikibase/DataModel/JSON
	 */
	public function __construct( $property, $time = null, $precision = 11, $args = [] ) {

		// as default take current date and time
		if( !$time ) {
			$time = new \DateTime();
			$time->setTimezone( new \DateTimeZone( 'UTC' ) );

			// Note that: <Hour, minute, and second are currently unused and should always be 00.> WHAT THE FUCK I'M READING HOLY CRAP WIKIBASE
			$time->setTime( 0, 0 );
		}

		// accept both a string or a datetime object
		if( $time instanceof \DateTime ) {
			$time = $time->format( '+Y-m-d\TH:i:s\Z' );
		}

		return parent::__construct( 'value', $property, DataType::TIME,
			new DataValueTime( $time, $precision, $args )
		);
	}
}
