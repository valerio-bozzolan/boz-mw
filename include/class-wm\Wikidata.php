<?php
# boz-mw - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2018, 2019, 2020, 2021 Valerio Bozzolan
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

# Wikimedia
namespace wm;

/**
 * Wikidata singleton instance
 *
 * @see https://www.wikidata.org/
 */
class Wikidata extends \mw\StaticWikibaseSite {

	/**
	 * @override
	 */
	const UID = 'wikidatawiki';

	/**
	 * @override
	 */
	const API_URL = 'https://www.wikidata.org/w/api.php';

	/**
	 * Execute a SPARQL query
	 *
	 * @param $query string SPARQL query
	 * @return array
	 */
	public static function querySPARQL( $query ) {

		// do some cleanage
		$query = trim( $query );

		$request = new \network\HTTPRequest( 'https://query.wikidata.org/sparql' );
		$response = $request->fetch( [
			'format' => 'json',
			'query'  => $query,
		] );

		// TODO: nice exceptions
		$response_obj = json_decode( $response );
		return $response_obj->results->bindings;
	}

	/**
	 * Retrieve the label (in English) of a property (from the cache)
	 *
	 * @param $property string Property title e.g. P123
	 * @return string|false Label in English or false
	 */
	public static function propertyLabel( $property ) {
		static $cache;
		$path = __DIR__ . '/../cache/Wikidata/properties.json';
		if( ! $cache && file_exists( $path ) ) {
			$cache = json_decode( file_get_contents( $path ) );
		}
		if( isset( $cache->{ $property } ) ) {
			return $cache->{ $property };
		}
		return false;
	}

}
