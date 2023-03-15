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

use generic\Singleton;

/**
 * A generic MediaWiki website
 *
 * This class is designed to have just one instance of this site
 */
class StaticSite extends Singleton implements Site {

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
	 * Public MediaWiki base URL
	 *
	 * e.g. 'https://www.mediawiki.org/wiki/'
	 *
	 * It's the base for every public page.
	 *
	 * @var string
	 */
	private $baseURL;

	/**
	 * Constructor
	 *
	 * @param $api_url string MediaWiki API URL
	 */
	public function __construct( $api_url ) {
		$this->api = new API( $api_url );

		// just for laziness, to avoid some refactors.
		// actually the user provides the API URL and not
		// other stuff. So just use the API URL.
		$this->guessBaseURLFromAPIURL( $api_url );
	}

	/**
	 * @override
	 */
	protected static function create() {
		$site = static::createFromAPIURL( static::getApiURL() );

		$site->setUID( static::UID );

		// Set default namespaces
		foreach( Ns::defaultCanonicalNames() as $ns_id => $ns ) {
			$site->setNamespace( new Ns( $ns_id, $ns ) );
		}

		return $site;
	}

	/**
	 * To be overloaded.
	 *
	 * @return string
	 */
	protected static function getApiURL() {
		return static::API_URL;
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
		return $this->post( array_replace( $data, [
			'action' => 'edit',
			'token'  => $this->getToken( \mw\Tokens::CSRF )
		] ) );
	}

	/**
	 * Make an HTTP POST request to the API
	 *
	 * This method will call the API#login() method.
	 *
	 * @param array $data Array of ContentDisposition(s)
	 * @return mixed API result
	 */
	public function postMultipart( $data ) {
		return $this->getApi()->postMultipart( $data );
	}

	/**
	 * Do an API upload request
	 *
	 * @param $data array API request data and ContentDisposition(s)
	 * @return mixed
	 * @see https://www.mediawiki.org/wiki/API:Upload
	 */
	public function upload( $data ) {
		return $this->postMultipart( array_replace( $data, [
			'action' => 'upload',
			'token'  => $this->getToken( \mw\Tokens::CSRF )
		] ) );
	}

	/**
	 * Check if I'm logged
	 *
	 * @return bool
	 */
	public function isLogged() {
		return $this->getApi()->isLogged();
	}

	/**
	 * Get the username used for the login (if any)
	 *
	 * @return string|null
	 */
	public function getUsername() {
		return $this->getApi()->getUsername();
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
	 * Set the MediaWiki base URL
	 *
	 * Often it ends with '/wiki/'
	 *
	 * @param string $base_url
	 * @return self
	 */
	public function setBaseURL( $base_url ) {
		$this->baseURL = $base_url;
		return $this;
	}

	/**
	 * Get the MediaWiki base URL
	 *
	 * Often it ends with '/wiki/'
	 *
	 * @return string
	 */
	public function getBaseURL() {
		return $this->baseURL;
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
	 * @return CompleteTitle
	 */
	public function createTitle( $title, $ns = 0 ) {
		return new CompleteTitle( $this, $this->getNamespace( $ns ), new Title( $title, $this ) );
	}

	/**
	 * Create a CompleteTitle object
	 *
	 * @param $s Page title with namespace prefix
	 * @return CompleteTitle
	 */
	public function createTitleParsing( $s ) {
		return CompleteTitle::createParsingTitle( $this, $s );
	}

	/**
	 * Create a wikilink object
	 *
	 * @deprecate Unuseful, just use createTitleParsing()->createWikilink()
	 * @return object
	 */
	public function createWikilink( CompleteTitle $title, $alias = null ) {
		return $title->createWikilink( $alias );
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

	/**
	 * Guess the MediaWiki base URL from the API URL
	 *
	 * I know, I know, it's not that simple! Calm down.
	 * Anyway 99% of the world has the API in /w/api.php and the website at /wiki/.
	 * If you do not appreciate this, just call setBaseURL() manually.
	 *
	 * I do not want to start adding random arguments in the Site() constructor.
	 *
	 * I thought: a smart default plus the ability to override it should be effective.
	 * ...isn't it?
	 *
	 * @param string $api_url API URL
	 */
	protected function guessBaseURLFromAPIURL( $api_url ) {

		// I know, I know, it's not that simple
		$this->setBaseURL( str_replace( '/w/api.php', '/wiki/', $api_url ) );
	}

}
