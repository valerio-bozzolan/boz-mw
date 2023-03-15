<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017-2023 Valerio Bozzolan
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
 * General interface of a Site
 *
 * This interface exists just to make it clear what should
 * be the public stable methods, and what are instead
 * more internal.
 */
interface Site {

	/**
	 * Create an API query with continuation handler
	 *
	 * @param $data array GET/POST data arguments
	 * @return mw\APIQuery
	 */
	public function createQuery( $data );

	/**
	 * Make an HTTP GET request to the API
	 *
	 * @param $data array HTTP GET data
	 * @return mixed API result
	 */
	public function fetch( $data );

	/**
	 * Make an HTTP POST request to the API
	 *
	 * This method will call the API#login() method.
	 *
	 * @param $data array HTTP GET data
	 * @return mixed API result
	 */
	public function post( $data );

	/**
	 * Do an API edit request
	 *
	 * @param $data array API request data
	 * @return mixed
	 * @see https://www.mediawiki.org/wiki/API:Edit
	 */
	public function edit( $data );

	/**
	 * Make an HTTP POST request to the API
	 *
	 * This method will call the API#login() method.
	 *
	 * @param array $data Array of ContentDisposition(s)
	 * @return mixed API result
	 */
	public function postMultipart( $data );

	/**
	 * Do an API upload request
	 *
	 * @param $data array API request data and ContentDisposition(s)
	 * @return mixed
	 * @see https://www.mediawiki.org/wiki/API:Upload
	 */
	public function upload( $data );

	/**
	 * Check if I'm logged
	 *
	 * @return bool
	 */
	public function isLogged();

	/**
	 * Get the username used for the login (if any)
	 *
	 * @return string|null
	 */
	public function getUsername();

	/**
	 * Preload some tokens
	 *
	 * @return self
	 */
	public function preloadTokens( $tokens );

	/**
	 * Get the value of a token
	 *
	 * @param $token string Token name
	 * @return string Token value
	 */
	public function getToken( $token );

	/**
	 * Invalidate a token
	 *
	 * @param $token string Token name
	 * @return self
	 */
	public function invalidateToken( $token );

	/**
	 * Make a login
	 *
	 * @see API#login()
	 * @param $username string MediaWiki username
	 * @param $password string MediaWiki password
	 * @return self
	 */
	public function login( $username = null, $password = null );

	/**
	 * Check if a certain namespace ID is registered
	 *
	 * @param int $id Namespace ID
	 * @return bool
	 */
	public function hasNamespace( $id );

	/**
	 * Get the namespace related to the specified ID
	 *
	 * @param int $id Namespace ID
	 * @return Ns Corresponding namespace
	 */
	public function getNamespace( $id );

	/**
	 * Set/overwrite a namespace
	 *
	 * @param $namespace Ns Namespace to be set/overwrited
	 * @return self
	 */
	public function setNamespace( Ns $namespace );

	/**
	 * Find a namespace by it's name
	 *
	 * @param $name string
	 * @return object|false
	 */
	public function findNamespace( $name );

	/**
	 * Stupid shortcut for setting multiple namespaces
	 *
	 * @param $namespaces array Array of namespaces
	 * @return self
	 */
	public function setNamespaces( $namespaces );

	/**
	 * Get internal MediaWiki API object
	 *
	 * @return API
	 */
	public function getApi();

	/**
	 * Get the MediaWiki base URL
	 *
	 * Often it ends with '/wiki/'
	 *
	 * @return string
	 */
	public function getBaseURL();

	/**
	 * Get the UID
	 *
	 * @return null|string e.g. 'enwiki'
	 */
	public function getUID();

	/**
	 * Create a Wikitext object
	 *
	 * @return Wikitext
	 */
	public function createWikitext( $wikitext = '' );

	/**
	 * Create a CompleteTitle object
	 *
	 * @param $title string Page title without namespace prefix
	 * @param $ns int Namespace number
	 * @return CompleteTitle
	 */
	public function createTitle( $title, $ns = 0 );

	/**
	 * Create a CompleteTitle object
	 *
	 * @param $s Page title with namespace prefix
	 * @return CompleteTitle
	 */
	public function createTitleParsing( $s );

	/**
	 * Create a wikilink object
	 *
	 * @deprecate Unuseful, just use createTitleParsing()->createWikilink()
	 * @return object
	 */
	public function createWikilink( CompleteTitle $title, $alias = null );

	/**
	 * Check if in this wiki the first case is insensitive
	 *
	 * @TODO generalize
	 * @return boolean
	 */
	public function hasCapitalLinks();

}
