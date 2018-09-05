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

# MediaWiki
namespace mw;

/**
 * A site with wikibase enabled
 */
class WikibaseSite extends Site {

	/**
	 * Fetch a single Wikidata entity using the wbgetentities API
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
		return \wb\DataModel::createFromObject( $entity->entities->{ $entity_id }, $this, $entity_id );
	}

	/**
	 * Edit a Wikidata entity using the wbeditentity API
	 *
	 * @param $data array API data request (with some extensions)
	 * 	Allowed extensions:
	 * 		summary.pre  Add something before the summary
	 * 		summary.post Add something after  the summary
	 * @return mixed
	 * @see https://www.wikidata.org/w/api.php?action=help&modules=wbgetentities
	 */
	public function editEntity( $data = [] ) {

		// extends the API arguments adding a 'summary.pre' argument
		if( isset( $data[ 'summary.pre' ] ) ) {
			$data[ 'summary' ] = $data[ 'summary.pre' ] . $data[ 'summary' ];
			unset( $data[ 'summary.pre' ] );
		}

		// extends the API arguments adding a 'summary.post' argument
		if( isset( $data[ 'summary.post' ] ) ) {
			$data[ 'summary' ] .= $data[ 'summary.post' ];
			unset( $data[ 'summary.post' ] );
		}

		return $this->post( array_replace( [
			'action' => 'wbeditentity',
			'token'  => $this->getToken( \mw\Tokens::CSRF ),
		], $data ) );
	}

	/**
	 * Create an empty data model related to this site
	 *
	 * @return DataModel
	 */
	public function createDataModel() {
		return new \wb\DataModel( $this );
	}

}
