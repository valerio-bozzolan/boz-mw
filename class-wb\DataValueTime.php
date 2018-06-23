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

use DateTime;

/**
 * A DataValue for a time.
 */
class DataValueTime extends DataValue {

	const PRECISION_YEARS  = 9;
	const PRECISION_MONTHS = 10;
	const PRECISION_DAYS   = 11;

	/**
	 * Gregorian calendar
	 *
	 * @var string
	 */
	static $DEFAULT_CALENDAR = 'Q1985727';

	/**
	 * Human precisions indexed
	 *
	 * @var array
	 */
	static $HUMAN_PRECISIONS = [
		'1 Gigayear',
		'100 Megayears',
		'10 Megayears',
		'Megayear',
		'100 Kiloyears',
		'10 Kiloyears',
		'Kiloyear',
		'100 years',
		'10 years',
		'years',
		'months',
		'days',
		'hours',   // unused
		'minutes', // unused
		'seconds', // unused
	];

	/**
	 * @param $time string example "+1539-00-00T00:00:00Z"
	 * @param $precision int
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
	 * 	calendarmodel: "Q12138"
	 * @see https://www.mediawiki.org/wiki/Wikibase/DataModel/JSON
	 */
	public function __construct( $time, $precision, $args = [] ) {

		$args = array_replace( [
			'time'          => $time,
			'precision'     => $precision,
			'calendarmodel' => self::$DEFAULT_CALENDAR,
			'timezone'      => 0, // unused
			'before'        => 0, // unused
			'after'         => 0, // unused
		], $args );

		$args['calendarmodel'] = 'http://www.wikidata.org/entity/' . $args['calendarmodel'];

		parent::__construct( DataType::TIME, $args );
	}

	/**
	 * Return an human rappresentation of the precision
	 *
	 * @param $precision int
	 * @return string
	 */
	public static function humanPrecision( $precision ) {
		return self::$HUMAN_PRECISIONS[ $precision ];
	}

	/**
	 * Return an human rappresentation of the time
	 *
	 * @param $time string
	 * @param $precision int
	 * @return string
	 */
	public static function humanTime( $time, $precision ) {
		$sign = substr( $time, 0, 1 );
		$rest = substr( $time, 1    );
		$date = new DateTime( $rest );
		return $date->format( self::humanTimeFormat( $precision ) );
	}

	/**
	 * Return a format for the time
	 *
	 * @param $time string
	 * @return string
	 */
	private static function humanTimeFormat( $precision ) {
		if( self::PRECISION_YEARS  >=  $precision )  return 'Y';
		if( self::PRECISION_MONTHS === $precision )  return 'Y-m';
		if( self::PRECISION_DAYS   === $precision )  return 'Y-m-d';
		                                             return 'Y-m-d H:i:s';
	}

	/**
	 * @return string
	 */
	public function __toString() {
		$value = $this->getValue();
		$precision = $value['precision'];
		return sprintf( "%s±%s",
			self::humanTime( $value['time'], $precision ),
			self::humanPrecision( $precision )
		);
	}
}
