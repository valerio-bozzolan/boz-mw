<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2018 Valerio Bozzolan
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
class Wikidata extends \mw\StaticSite {

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
		$request = new \network\HTTPRequest( 'https://query.wikidata.org/sparql' );
		$response = $request->fetch( [
			'format' => 'json',
			'query'  => $query
		] );
		$response_obj = json_decode( $response );
		return $response_obj->results->bindings;
	}

	/**
	 * Fetch a single Wikidata entity
	 *
	 * @param $entity_id string Entity Q-ID
	 * @param $data array Additional data such as [ 'props' => '..' ]
	 * @return wb\DataModel
	 */
	public function fetchSingleEntity( $entity_id, $data = [] ) {
		$data = array_replace( [
			'action' => 'wbgetentities',
			'ids'    => $entity_id,
		], $data );

		$entity = $this->fetch( $data );
		if( ! isset( $entity->entities->{ $entity_id } ) ) {
			throw new Exception( "$wikidata_item does not exist" );
		}
		return \wb\DataModel::createFromObject( $entity->entities->{ $entity_id } );
	}

	/**
	 * Edit a Wikidata entity
	 *
	 * @param $data array API data request
	 * @return mixed
	 * @see https://www.wikidata.org/w/api.php?action=help&modules=wbgetentities
	 */
	public function editEntity( $data = [] ) {
		return $this->post( array_replace( [
			'action' => 'wbeditentity',
			'token'  => $this->getToken( \mw\Tokens::CSRF ),
		], $data ) );
	}

}
