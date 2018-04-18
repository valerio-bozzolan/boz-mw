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

class Site {

	/**
	 * MediaWiki API
	 *
	 * @var API
	 */
	private $api;

	/**
	 * Site namespaces
	 *
	 * @var array Array of namespaces
	 */
	private $namespaces = [];

	/**
	 * A sort of internal UID
	 *
	 * @var null|string E.g. 'enwiki'
	 */
	private $uid;

	/**
	 * Constructor
	 *
	 * @param $url string MediaWiki API URL
	 */
	public function __construct( $url ) {
		$this->api = self::createApi( $url );
	}

	/**
	 * Make an HTTP GET request to the API
	 *
	 * @param $data array HTTP GET data
	 * @return mixed API result
	 */
	public function fetch( $data ) {
		return $this->getApi()->fetch( $data );
	}

	/**
	 * Make an HTTP POST request to the API
	 *
	 * This method will call the API#login() method.
	 *
	 * @param $data array HTTP GET data
	 * @return mixed API result
	 */
	public function post( $data ) {
		return $this->getApi()->post( $data );
	}

	/**
	 * Require some tokens
	 *
	 * @return self
	 */
	public function requireTokens( $tokens ) {
		$this->getApi()->requireTokens( $tokens );
		return $this;
	}

	/**
	 * Get the value of a token
	 *
	 * @param $token string Token name
	 * @return string Token value
	 */
	public function getToken( $token ) {
		return $this->getApi()->getToken( $token );
	}

	/**
	 * Invalidate a token
	 *
	 * @param $token string Token name
	 * @return self
	 */
	public function invalidateToken( $token ) {
		$this->getApi()->invalidateToken( $token );
		return $this;
	}

	/**
	 * Make a login
	 *
	 * @see API#login()
	 * @param $username string MediaWiki username
	 * @param $password string MediaWiki password
	 * @return self
	 */
	public function login( $username = null, $password = null ) {
		$this->getApi()->login( $username, $password );
		return $this;
	}

	/**
	 * Check if we can continue fetching the next result set
	 *
	 * @see API#hasNext()
	 * @return bool
	 */
	public function hasNext() {
		return $this->getApi()->hasNext();
	}

	/**
	 * Fetch the next result set
	 *
	 * @see API#fetchNext()
	 * @return mixed Response
	 */
	public function fetchNext() {
		return $this->getApi()->fetchNext();
	}

	/**
	 * Set the default API data
	 *
	 * @see API#setData()
	 * @data array HTTP GET/POST data
	 * @return self
	 */
	public function setApiData( $data ) {
		$this->getApi()->setData( $data );
		return $this;
	}

	/**
	 * Check if a certain namespace ID is registered
	 *
	 * @param int $id Namespace ID
	 * @return bool
	 */
	public function hasNamespace( $id ) {
		return array_key_exists( $id, $this->namespaces );
	}

	/**
	 * Get the namespace related to the specified ID
	 *
	 * @param int $id Namespace ID
	 * @return Ns Corresponding namespace
	 */
	public function getNamespace( $id ) {
		if( ! $this->hasNamespace( $id ) ) {
			throw new \Exception( sprintf( 'missing namespace %d', $id ) );
		}
		return $this->namespaces[ $id ];
	}

	/**
	 * Set/overwrite a namespace
	 *
	 * @param $namespace Ns Namespace to be set/overwrited
	 * @return self
	 */
	public function setNamespace( Ns $namespace ) {
		$id = $namespace->getID();
		$this->namespaces[ $id ] = $namespace;
		return $this;
	}

	/**
	 * Stupid shortcut for setting multiple namespaces
	 *
	 * @param $namespaces array Array of namespaces
	 * @return self
	 */
	public function setNamespaces( $namespaces ) {
		foreach( $namespaces as $namespace ) {
			$this->setNamespace( $namespace );
		}
		return $this;
	}

	/**
	 * Set the UID
	 *
	 * @param string E.g. 'enwiki'
	 * @return self
	 */
	public function setUID( $uid ) {
		$this->uid = $uid;
		return $this;
	}

	/**
	 * Get internal MediaWiki API object
	 *
	 * @return API
	 */
	public function getApi() {
		return $this->api;
	}

	/**
	 * Get the UID
	 *
	 * @return null|string e.g. 'enwiki'
	 */
	public function getUID() {
		return $this->uid;
	}

	/**
	 * Create a Wikitext object
	 *
	 * @return Wikitext
	 */
	public function createWikitext( $wikitext ) {
		return new Wikitext( $this, $wikitext );
	}

	/**
	 * Create a MediaWiki API object from an API URL
	 *
	 * @param $url string API URL
	 * @return API
	 */
	private static function createApi( $url ) {
		return new API( $url );
	}
}
