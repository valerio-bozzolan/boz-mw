<?php
# boz-mw - Another MediaWiki API handler in PHP
# Copyright (C) 2018, 2019, 2020, 2021 Valerio Bozzolan
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

# MediaWiki API
namespace mw\API;

/**
 * Merge your custom page objects with the API response pages
 *
 * Useful when you try to retrieve informations from many pages
 * with less request as possible.
 */
class PageMatcher {

	/**
	 * Response of the 'action: query' API request
	 *
	 * @var object
	 */
	private $responseQuery;

	/**
	 * Associative array of normalized titles
	 *
	 * @var array
	 */
	private $normalizedTitles;

	/**
	 * Name of the data container
	 *
	 * @var string
	 */
	private $dataContainer;

	/**
	 * Array of my custom page objects
	 *
	 * @var array
	 */
	private $myPages;

	/**
	 * Constructor
	 *
	 * @param $response_query object Response of an API query, the part that involves pages. e.g.:
	 * 	{
	 * 		normalized: [
	 * 			{ from: , to: },
	 * 			{ from: , to: },
	 * 			...
	 * 		],
	 *			query: pages: [
	 * 			id: { id: , title: , ... },
	 * 			id: { id: , title: , ... },
	 * 			...
	 * 		]
	 * 	}
	 * @param $my_pages array My custom page objects
	 * @param $query_containers string|array Container of the queried response e.g. [ 'query' ]
	 * @param $data_container   string       Container of the pages e.g. 'pages'
	 * @see https://it.wikipedia.org/w/api.php?action=query&prop=info&titles=Ghhh|%20Main%20page
	 */
	public function __construct( $response_query, $my_stuff, $query_containers = null, $data_container = null ) {

		// default containers
		if( !$query_containers ) {
			$query_containers = [ 'query' ];
		}

		// default container of the results
		if( !$data_container ) {
			$data_container = 'pages';
		}

		$this->responseQuery = $response_query;
		if( $query_containers ) {
			if( !is_array( $query_containers ) ) {
				$query_containers = [ $query_containers ];
			}
			foreach( $query_containers as $query_container ) {
				if( isset( $this->responseQuery->{ $query_container } ) ) {
					$this->responseQuery = $this->responseQuery->{ $query_container };
				} else {
					throw new \Exception( "missing property $query_container in the response" );
				}
			}
		}

		$this->myPages = $my_stuff;

		$this->dataContainer = $data_container;

		$this->validate();
	}

	/**
	 * Check if this object has sense or throw
	 */
	private function validate() {

		// check if ->query->pages exists
		if( !isset( $this->getResponseQuery()->{ $this->dataContainer } ) ) {
			throw new \Exception( "expected '->{$this->dataContainer}' in the response - try updating your '\$data_container' - see the constructor (4 argument)" );
		}
	}

	/**
	 * Get the response of the 'action: query' API request
	 *
	 * @return object
	 */
	public function getResponseQuery() {
		return $this->responseQuery;
	}

	/**
	 * Get the response pages
	 *
	 * @return array
	 */
	public function getResponseQueryPages() {
		return $this->getResponseQuery()->{ $this->dataContainer };
	}

	/**
	 * Get my custom pages
	 *
	 * @return array
	 */
	public function getMyPages() {
		return $this->myPages;
	}

	/**
	 * Has some normalized titles?
	 *
	 * @return bool
	 */
	public function hasNormalizedTitles() {
		return isset( $this->getResponseQuery()->normalized );
	}

	/**
	 * Get an associative array of normalized titles
	 *
	 * @result array e.g. [ ' a' => 'A', ... ]
	 */
	public function getNormalizedTitles() {

		// initialize only once
		if( null === $this->normalizedTitles ) {
			$this->normalizedTitles = [];
			if( $this->hasNormalizedTitles() ) {
				foreach( $this->getResponseQuery()->normalized as $fromto ) {
					$this->normalizedTitles[ $fromto->from ] = $fromto->to;
				}
			}
		}

		return $this->normalizedTitles;
	}

	/**
	 * Get a normalized title
	 *
	 * @param $title string Page title that may have been normalized
	 * @param $normalized bool Is this page title normalized?
	 * @return string Page title normalized
	 */
	public function getNormalizedTitle( $title, & $normalized = false ) {
		$normalized_titles = $this->getNormalizedTitles();
		if( isset( $normalized_titles[ $title ] ) ) {
			$normalized = true;
			return $normalized_titles[ $title ];
		}
		return $title;
	}

	/**
	 * Walk the response pages matching mine (from a custom key)
	 *
	 * @param $matched_callback callback Callback that will be called for each match between a response page and your custom object:
	 * 	The 1° argument of the callback will be the response page object.
	 * 	The 2° argument of the callback will be your related custom page object.
	 *  The 3° argument of the callback will be the used join key (for debugging purposes).
	 *  If you return FALSE, the matcher stops.
	 * @param $my_page_callback callback Callback that must return something from your custom page object:
	 * 	The 1° argoment is your object.
	 * 	It must return something: the join key (often it's the normalized page title or the page id).
	 * @param $response_page_callback callback Callback that must return something from a response page:
	 * 	The 1° is a response page object.
	 * 	It must return something: the join key (often it's the normalized page title or the page id).
	 */
	public function matchByCustomJoin( $matched_callback, $my_page_callback, $response_page_callback = null ) {

		// allow lazy calls
		if( !$response_page_callback ) {
			$response_page_callback = $my_page_callback;
		}

		// for each response page
		foreach( $this->getResponseQueryPages() as $response_page ) {

			// get a value to be used as join key from response key
			$response_page_value = $response_page_callback( $response_page );

			// for each of my pages
			foreach( $this->getMyPages() as $my_page ) {

				// get a value to be used as join key from my page
				$my_page_value = $my_page_callback( $my_page );

				// compare
				if( $my_page_value === $response_page_value ) {

					// found
					$loop = $matched_callback( $response_page, $my_page, $my_page_value );

					// if the user returns false, just stop
					if( $loop === false ) {
						return;
					}
				}
			}
		}
	}

	/**
	 * Walk the response pages matching mine (from the page id)
	 *
	 * @param $matched_callback callback Callback that will be called for each match between a response page and your custom object:
	 * 	The 1° argument is the response page object.
	 * 	The 2° argument is your related custom page object.
	 * @param $my_page_id_callback callback Callback that must returns a page id from your custom object.
	 * 	The 1° is your object.
	 * 	It must return a page id.
	 */
	public function matchById( $matched_callback, $my_page_id_callback ) {
		$this->matchByCustomJoin(
			$matched_callback,

			// callback that returns the page id from my custom page object
			$my_page_id_callback,

			// callback that returns the page id from the response page object
			function ( $response_page ) {
				return (int) $response_page->id;
			}
		);
	}

	/**
	 * Walk the response pages, matching them and your own objects (from the page title)
	 *
	 * @param $matched_callback callback Callback that will be called for each of your matched pages.
	 *  The 1st argument of the callback will be the response page object.
	 *  The 2nd argument of the callback will be your related custom page object.
	 *  The 3° argument of the callback will be the used join key (for debugging purposes).
	 *  If you return FALSE, the matcher stops.
	 * @param $my_page_title_callback callback Callback that must returns a page title from your custom object.
	 * 	 The 1st argument of the callback will be your object.
	 * 	 It must return a page title.
	 *   If unspecified it means that your object it's the title itself
	 */
	public function matchByTitle( $matched_callback, $my_page_title_callback = null ) {
		$this->matchByCustomJoin(
			$matched_callback,

			// callback that returns the normalized page title from my custom page object
			function ( $my_page ) use ( $matched_callback, $my_page_title_callback ) {

				// callback that returns the page title from my custom page object...
				$title =
					  $my_page_title_callback
					? $my_page_title_callback( $my_page )
					: $my_page; // or it's just the string title itself?

				// normalize it
				return $this->getNormalizedTitle( $title );
			},

			// callback that returns the normalized page title from the response page object
			function ( $response_page ) {

				// this is the response page title and its always normalized
				return $response_page->title;
			}
		);
	}

	/**
	 * Get every match indexed by my original requested page title (not normalized).
	 *
	 * @return array Associative array of title => page object
	 */
	public function getMatchesByMyTitle() {
		$matches_by_my_title = [];

		$this->matchByTitle( function( $matched_page, $my_title ) use ( & $matches_by_my_title ) {
			$matches_by_my_title[ $my_title ] = $matched_page;
		} );

		return $matches_by_my_title;
	}
}
