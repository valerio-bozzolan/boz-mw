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

use cli\Log;

/**
 * Make HTTP request to a MediaWiki API
 */
class API extends \network\HTTPRequest {

	/**
	 * MediaWiki tokens handler
	 *
	 * @var mw\Tokens
	 */
	private $tokens;

	/**
	 * Default MediaWiki login username
	 *
	 * @var string
	 */
	static $DEFAULT_USERNAME;

	/**
	 * Default MediaWiki login password
	 *
	 * @var string
	 */
	static $DEFAULT_PASSWORD;

	/**
	 * Default MediaWiki API maxlag
	 *
	 * See https://www.mediawiki.org/wiki/Manual:Maxlag_parameter
	 *
	 * @var int
	 */
	static $DEFAULT_MAXLAG = 5;

	/**
	 * Default MediaWiki API response format
	 *
	 * @var string
	 */
	static $DEFAULT_FORMAT = 'json';

	/**
	 * Inspect POST flag
	 *
	 * @var bool
	 */
	public static $INSPECT_BEFORE_POST = false;

	/**
	 * Username used for the login.
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Constructor
	 *
	 * @param $api string API endpoint
	 */
	public function __construct( $api ) {
		parent::__construct( $api, [] );
		$this->tokens = new Tokens( $this );
	}

	/**
	 * Create an API query with continuation handler
	 *
 	 * @param $data array Data
	 * @return mw\APIQuery
	 */
	public function createQuery( $data ) {
		return new APIQuery( $this, $data );
	}

	/**
	 * Effectuate an HTTP POST request but only after a login.
	 *
	 * @param $data array GET/POST data
	 * @param $args array Internal arguments
	 * @override \network\HTTPRequest#post()
	 * @return mixed
	 */
	public function post( $data = [], $args = [] ) {
		if( !$this->isLogged() ) {
			$this->login();
		}
		if( static::$INSPECT_BEFORE_POST ) {
			print_r( $data );
			\cli\Input::askInput( "Press ENTER to submit" );
		}
		return parent::post( $data, $args );
	}

	/**
	 * Effectuate an HTTP POST (multipart) request but only after a login.
	 *
	 * @param $data array Array of ContentDispositions(s)
	 * @param $args array Internal arguments
	 * @override \network\HTTPRequest#post()
	 * @return mixed
	 */
	public function postMultipart( $data = [], $args = [] ) {
		if( !$this->isLogged() ) {
			$this->login();
		}
		if( static::$INSPECT_BEFORE_POST ) {
			print_r( $data );
			\cli\Input::askInput( "Press ENTER to submit" );
		}
		return parent::postMultipart( $data, $args );
	}

	/**
	 * Fetch response
	 *
	 * @param $data array GET/POST data
	 * @return mixed
	 */
	public function fetch( $data = [], $args = [] ) {
		if( [] === $data ) {
			throw \InvalidArgumentException( 'empty data' );
		}
		return parent::fetch( $data, $args );
	}

	/**
	 * Preload some tokens
	 *
	 * @return self
	 */
	public function preloadTokens( $tokens ) {
		$this->tokens->preload( $tokens );
		return $this;
	}

	/**
	 * Get the value of a token
	 *
	 * @param $token string Token name
	 * @return string Token value
	 */
	public function getToken( $token ) {
		return $this->tokens->get( $token );
	}

	/**
	 * Invalidate a token
	 *
	 * @param $token string Token name
	 * @return self
	 */
	public function invalidateToken( $token ) {
		$this->tokens->invalidate( $token );
		return $this;
	}

	/**
	 * Get the username used for the login
	 *
	 * @return string|null
	 */
	 public function getUsername() {
	 	return $this->username;
	 }

	/**
	 * Check if it's already logged in.
	 *
	 * @return bool
	 */
	public function isLogged() {
		return $this->getUsername() !== null;
	}

	/**
	 * Login into MediaWiki using an username/password pair.
	 *
	 * Yes, I'm talking about a bot password.
	 */
	public function login( $username = null, $password = null ) {

		// Can use a default set of credentials
		if( ! $username && ! $password ) {
			if( $this->isLogged() ) {
				return $this;
			}
			$username = self::$DEFAULT_USERNAME;
			$password = self::$DEFAULT_PASSWORD;
		}

		if( ! $username || ! $password ) {
			throw new \Exception( sprintf(
				'you must call %1$s#login( $username, $password ) or '.
				'set %1$s::$DEFAULT_USERNAME and %1$s::$DEFAULT_PASSWORD ' .
				'before POSTing',
				__CLASS__
			) );
		}

		Log::info( "login with username '$username'" );

		// Login
		$response = parent::post( [
			'action'     => 'login',
			'lgname'     => $username,
			'lgpassword' => $password,
			'lgtoken'    => $this->getToken( Tokens::LOGIN ),
		], [
			'sensitive' => true
		] );
		if( ! isset( $response->login->result ) || $response->login->result !== 'Success' ) {
			print_r( $response );
			throw new \Exception("login failed");
		}

		$this->username = $response->login->lgusername;

		return $this;
	}

	/**
	 * Filters the data before using it.
	 *
	 * Array elements are imploded by a pipe
	 * NULL values are unset
	 *
	 * @override network\HTTPRequest::onDataReady()
	 * @param $data array GET/POST data
	 * @return array
	 */
	protected function onDataReady( $data ) {

		// Some default values
		$data = array_replace( [
			'maxlag'  => self::$DEFAULT_MAXLAG,
			'format'  => self::$DEFAULT_FORMAT,
		], $data );

		foreach( $data as $k => $v ) {
			if( null === $v ) {
				unset( $data[ $k ] );
			} elseif( is_array( $v ) ) {
				// remove duplicates (API netiquette)
				$v = array_unique( $v );

				// index alphabetically (API netiquette)
				sort( $v, SORT_STRING );

				$data[ $k ] = implode( '|', $v );
			}
		}
		if( $this->isLogged() ) {
			$data = array_replace( [
				'assertuser' => $this->getUsername(),
			], $data );
		}
		return $data;
	}

	/**
	 * JSON decode and check formal API errors
	 *
	 * @param $response mixed Response
	 * @param $request_data array GET/POST request data
	 * @override \network\HTTPRequest#onFetched()
	 * @throws \mw\API\Exception
	 */
	protected function onFetched( $response_raw, $request_data ) {
		$response = json_decode( $response_raw );
		if( null === $response ) {
			Log::debug( $response_raw );
			throw new \Exception( 'response is not JSON-encoded' );
		}
		if( isset( $response->warnings ) ) {
			foreach( $response->warnings as $subject => $warning ) {
				Log::warn( sprintf( '%s: %s', $subject, $warning->{'*'} ) );
			}
		}
		if( isset( $response->error ) ) {
			$exception = API\Exception::createFromApiError( $response->error );
			if( $exception instanceof API\MaxLagException ) {
				// retry after some time when server lags
				Log::warn( "Lag! ({$this->api}) {$response->error->info}" );

				$response = $this->fetch( $request_data, [
					'wait-anti-dos' => true,
				] );
			} else {
				throw $exception;
			}
		}
		return $response;
	}
}
