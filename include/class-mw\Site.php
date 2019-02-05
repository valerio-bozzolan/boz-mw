<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2018, 2019 Valerio Bozzolan
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
	 * A sort of $wgCapitalLinks
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:$wgCapitalLinks
	 */
	const CAPITAL_LINKS = true;

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
		$this->api = new API( $url );
	}

	/**
	 * Create an API query with continuation handler
	 *
	 * @param $data array GET/POST data arguments
	 * @return mw\APIQuery
	 */
	public function createQuery( $data ) {
		return $this->getApi()->createQuery( $data );
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
	 * Do an API edit request
	 *
	 * @param $data array API request data
	 * @return mixed
	 * @see https://www.mediawiki.org/wiki/API:Edit
	 */
	public function edit( $data ) {
		return $this->getApi()->post( array_replace( $data, [
			'action' => 'edit',
			'token'  => $this->getToken( \mw\Tokens::CSRF )
		] ) );
	}

	/**
	 * Preload some tokens
	 *
	 * @return self
	 */
	public function preloadTokens( $tokens ) {
		$this->getApi()->preloadTokens( $tokens );
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
	 * Find a namespace by it's name
	 *
	 * @param $name string
	 * @return object|false
	 */
	public function findNamespace( $name ) {
		$name = Ns::normalizeName( $name );
		foreach( $this->namespaces as $ns ) {
			if( $ns->getName() === $name ) {
				return $ns;
			}
		}
		return false;
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
	public function createWikitext( $wikitext = '' ) {
		return new Wikitext( $this, $wikitext );
	}

	/**
	 * Create a CompleteTitle object
	 *
	 * @param $title string Page title without namespace prefix
	 * @param $ns int Namespace number
	 * @return object
	 */
	public function createTitle( $title, $ns = 0 ) {
		return new CompleteTitle( $this, $this->getNamespace( $ns ), new Title( $title, $this ) );
	}

	/**
	 * Create a CompleteTitle object
	 *
	 * @param $s Page title with namespace prefix
	 * @return object
	 */
	public function createTitleParsing( $s ) {
		return CompleteTitle::createParsingTitle( $this, $s );
	}

	/**
	 * Create a wikilink object
	 *
	 * @return object
	 */
	public function createWikilink( CompleteTitle $title, $alias = null ) {
		return new Wikilink( $title, $alias );
	}

	/**
	 * Check if in this wiki the first case is insensitive
	 *
	 * @TODO generalize
	 * @return boolean
	 */
	public function hasCapitalLinks() {
		return static::CAPITAL_LINKS;
	}

	/**
	 * Create from an API URL
	 *
	 * @param $url string MediaWiki API URL
	 * @return self
	 */
	public static function createFromAPIURL( $url ) {
		return new static( $url );
	}
}
