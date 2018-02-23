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
 * Make HTTP request to a MediaWiki API
 */
class API extends \network\HTTPRequest {

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
	 * @var int
	 */
	static $DEFAULT_MAXLAG = 5;

	/**
	 * Default MediaWiki API result format
	 *
	 * @var string
	 */
	static $DEFAULT_FORMAT = 'json';

	/**
	 * Username used for the login.
	 */
	private $username;

	/**
	 * Frequent API response token to continue a result set.
	 *
	 * @var mixed
	 */
	private $continue = null;

	/**
	 * Flag to avoid duplicate logins
	 */
	private $logged = false;

	/**
	 * Last API response
	 *
	 * @var mixed
	 */
   private $last = null;

	/**
	 * Effectuate an HTTP POST request but only after a login.
	 *
	 * @param $data array GET/POST data
	 * @param $args array Internal arguments
	 * @override \network\HTTPRequest#post()
	 * @return mixed
	 */
	public function post( $data = [], $args = [] ) {
		if( ! $this->isLogged() ) {
			$this->login();
		}
		return parent::post( $data, $args );
	}

	/**
	 * Check if there are other requests to do to fetch the whole result set.
	 *
	 * @return bool
	 */
	public function hasNext() {
		// Note that as default continue is NULL
		return false !== $this->continue;
	}

	/**
	 * Fetch the next result set.
	 *
	 * @return mixed
	 */
	public function fetchNext() {
		$data = $this->getData();

		if( $this->continue ) {
			self::log('INFO', "Will continue");
			foreach( $this->continue as $arg => $value ) {
				$data[ $arg ] = $value;
			}
		}

		$next = $this->fetch( $data );

		$this->continue = isset( $next->continue )
			? $next->continue
			: false;

		return $next;
	}

	/**
	 * Check if it's already logged in.
	 *
	 * @return bool
	 */
	public function isLogged() {
		return $this->logged;
	}

	/**
	 * Login into MediaWiki using an username/password pair.
	 *
	 * Yes, I'm talking about a bot password.
	 */
	public function login( $username = null, $password = null ) {

		// Can use a default set of credentials
		if( ! $username && ! $password ) {
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

		// Fetch the login token
		$logintoken = $this->fetch( [
			'action' => 'query',
			'meta'   => 'tokens',
			'type'   => 'login'
		] )->query->tokens->logintoken;

		// Login
		$response = parent::post( [
			'action'     => 'login',
			'lgname'     => $username,
			'lgpassword' => $password,
			'lgtoken'    => $logintoken
		] );
		if( ! isset( $response->login->result ) || $response->login->result !== 'Success' ) {
			throw new \Exception("login failed");
		}

		$this->username = $response->login->lgusername;
		$this->logged = true;

		return $this;
	}

	/**
	 * Set GET/POST data.
	 *
	 * As default, it propose a maxlag and a format.
	 *
	 * @param $data array GET/POST data
	 * @override \network\HTTPRequest#setData()
	 */
	public function setData( $data ) {
		$this->continue = null;
		$data = array_replace( [
			'maxlag'  => self::$DEFAULT_MAXLAG,
			'format'  => self::$DEFAULT_FORMAT
		], $data );
		return parent::setData( $data );
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
		foreach( $data as $k => $v ) {
			if( null === $v ) {
				unset( $data[ $k ] );
			} elseif( is_array( $v ) ) {
				$data[ $k ] = implode( '|', $v );
			}
		}
		if( $this->isLogged() ) {
			$data = array_replace( [
				'assertuser' => $this->username
			], $data );
		}
		return $data;
	}

	/**
	 * JSON decode and check formal API errors
	 *
	 * @param $result mixed Result
	 * @override \network\HTTPRequest#onFetched()
	 * @throws \mw\API\Exception
	 */
	protected function onFetched( $result ) {
		$result = json_decode( $result );
		if( isset( $result->error ) ) {
			$exception = API\Exception::createFromApiError( $result->error );
			if( $exception instanceof MaxLagException ) {
				// Retry after some time when server lags
				self::log( 'WARN', "Lag!" );
				$args = array_replace( [
					'wait' => self::WAIT_DOS
				], $this->getArgs() );
				$result = $this->fetch( $data , $args );
			} else {
				throw $exception;
			}
		}
		return $this->last = $result;
	}
}
