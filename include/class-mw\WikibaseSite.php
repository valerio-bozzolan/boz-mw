<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2018, 2019, 2020 Valerio Bozzolan
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
	 * See:
	 *   https://www.wikidata.org/w/api.php?action=help&modules=wbgetentities
	 *
	 * @param $entity_id string Entity Q-ID
	 * @param $data array Additional data such as [ 'props' => '..' ]
	 *   Some available props:
	 *      aliases, claims, datatype, descriptions, info, labels, sitelinks, sitelinks/urls
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

		return $this->createDataModelFromObject( $entity->entities->{ $entity_id } );
	}

	/**
	 * Edit a Wikidata entity using the wbeditentity API
	 *
	 * You do not need to send the CSRF 'token' and the 'action' parameters.
	 *
	 * See:
	 *   https://www.wikidata.org/w/api.php?action=help&modules=wbeditentity
	 *
	 * @param $data array API data request (with some extensions)
	 * 	Allowed extensions:
	 * 		summary.pre  Add something before the summary
	 * 		summary.post Add something after  the summary
	 * @return mixed
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
	 * Create an empty Wikibase data model related to this site
	 *
	 * @param $entity_id string Entity Q-ID
	 * @return DataModel
	 */
	public function createDataModel( $entity_id = null ) {
		return new \wb\DataModel( $this, $entity_id );
	}

	/**
	 * Create an empty Wikibase data model related to this site
	 *
	 * @param $data object Single Entity object retrieved from wbgetentities API
	 * @return DataModel
	 */
	public function createDataModelFromObject( $data ) {
		$id = $data->id;
		return \wb\DataModel::createFromObject( $data, $this, $id );
	}

}
