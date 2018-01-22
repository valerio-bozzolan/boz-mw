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
namespace network;

/**
 * HTTP request handler for GET and POST requests.
 */
class HTTPRequest {

	/**
	 * Class version
	 *
	 * To be incremented every time do you notice this number.
	 *
	 * @var string
	 */
	const VERSION = 0.4;

	/**
	 * Source code URL
	 *
	 * @var string
	 */
	const REPO = 'https://github.com/valerio-bozzolan/boz-mw';

	/**
	 * Wait seconds before each GET request.
	 */
	static $WAIT = 0.2;

	/**
	 * Wait seconds before each POST request
	 *
	 * @var float
	 */
	static $WAIT_POST = 0.2;

	/**
	 * Wait seconds before each lag
	 *
	 * @var float
	 */
	const WAIT_DOS  = 5;

	/**
	 * Full HTTP URL to the API endpoint
	 *
	 * @var string
	 */
	private $api;

	/**
	 * HTTP GET/POST data
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Internal arguments
	 *
	 * @param array
	 */
	private $args;

	/**
	 * Latest HTTP response headers
	 *
	 * @var array
	 */
	private $latestHttpResponseHeaders = [];

	/**
	 * HTTP cookies
	 *
	 * @var array
	 */
	private $cookies = [];

	/**
	 * Flag for debug mode
	 *
	 * @var bool
	 */
	private $debug = false;

	/**
	 * Constructor.
	 *
	 * @param $api string API endpoint
	 * @param $data array GET/POST data
	 * @param $args array Internal arguments
	 */
	public function __construct( $api, $data = [], $args = [] ) {
		$this->api = $api;
		$this->setData( $data );
		$this->setArgs( $args );
	}

	/**
	 * Statical constructor.
	 *
	 * @return network\HTTPRequest
	 */
	public static function factory( $api, $data = [], $args = [] ) {
		return new self( $api, $data, $args );
	}

	/**
	 * Get current HTTP GET/POST data.
	 *
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Get internal arguments.
	 *
	 * @return array
	 */
	public function getArgs() {
		return $this->args;
	}

	/**
	 * Check if debug mode is enabled
	 *
	 * @return bool
	 */
	public function isDebug() {
		return $this->debug;
	}

	/**
	 * Set HTTP GET/POST data for same future requests.
	 *
	 * @param $data array GET/POST data
	 * @return self
	 */
	public function setData( $data ) {
		$this->data = $data;
		return $this;
	}

	/**
	 * Toggle debug mode
	 *
	 * @return self
	 */
	public function setDebug( $debug ) {
		$this->debug = $debug;
		return $this;
	}

	/**
	 * Set internal arguments
	 *
	 * @param $args array Internal arguments
	 * @return self
	 */
	public function setArgs( $args ) {
		$this->args = array_replace( [
			'method'     => 'GET',
			'wait'       => self::$WAIT,
			'wait-post'  => self::$WAIT_POST,
			'user-agent' => sprintf( 'boz-mw HTTPRequest.php/%s %s', self::VERSION, self::REPO ),
			'headers'    => []
		], $args );
		return $this;
	}

	/**
	 * Make an HTTP GET query
	 *
	 * @param $data array GET data
	 * @param $args array Internal arguments
	 * @return mixed Response
	 */
	public function fetch( $data = [], $args = [] ) {
		$data = array_replace( $this->data, $data );
		$args = array_replace( $this->args, $args );

		// Eventually post-process the data before using
		$data = static::onDataReady( $data );

		// HTTP query using file_get_contents()
		$url = $this->api;
		$query = http_build_query( $data );
		$context = [
			'http' => []
		];
		$context['http']['method'] = $args['method'];
		if( $args['user-agent'] ) {
			$args['headers'][] = self::header( 'User-Agent', $args['user-agent'] );
		}

		if( $this->haveCookies() ) {
			$args['headers'][] = $this->getCookieHeader();
		}
		if( $query ) {
			if( 'POST' === $args['method'] ) {
				$args['headers'][] = self::header('Content-Type', 'application/x-www-form-urlencoded' );
				$context['http']['content'] = $query;
			} else {
				$url .= "?$query";
			}
		}
		if( $args['headers'] ) {
			$context['http']['header'] = self::implodeHTTPHeaders( $args['headers'] );
		}

		if( $this->isDebug() ) {
			self::log('DEBUG', $url );
			self::log('DEBUG', $args['method'] );
			if( $args['method'] === 'POST' ) {
				self::log('DEBUG', $query );
			}
		}

		if( $args['wait'] ) {
			self::log('INFO', sprintf(
				"Waiting %.2f s...",
				$args['wait']
			) );
			usleep( $args['wait'] * 1000000 );
		}

		self::log('INFO', sprintf(
			"%s request",
			$args['method']
		) );

		$stream_context = stream_context_create( $context );
		$result = file_get_contents( $url, false, $stream_context );

		// Here $http_response_header exists magically (PHP merda!)
		$this->loadHTTPResponseHeaders( $http_response_header );

		if( isset( $this->latestHttpResponseHeaders['HTTP/1.1 500 Internal Server Error'] ) ) {
			self::log( 'WARN', "500 Internal Server Error!");
			$args = array_replace( [
				'wait' => self::WAIT_DOS
			], $args );
			return $this->fetch( $data , $args );
		}

		self::log('INFO', "Fetched");

		return static::onFetched( $result );
	}

	/**
	 * Effectuate an HTTP POST.
	 */
	public function post( $data = [], $args = [] ) {
		$args = array_replace( [
			'method' => 'POST',
			'wait'   => $this->args['wait-post']
		], $args );
		return $this->fetch( $data, $args );
	}

	/**
	 * Get latest HTTP response headers.
	 *
	 * @return array
	 */
	public function getLatestHTTPResponseHeaders() {
		return $this->latestHttpResponseHeaders;
	}

	/**
	 * Check if cookies are set
	 *
	 * @return bool
	 */
	public function haveCookies() {
		return isset( $this->cookies );
	}

	/**
	 * Get cookies
	 *
	 * @return array
	 */
	public function getCookies() {
		return $this->cookies;
	}

	/**
	 * Set an HTTP cookie name => value pair.
	 *
	 * @param $name string Cookie name
	 * @param $value string Cookie value
	 * @return self
	 */
	public function setCookie( $name, $value ) {
		$this->cookies[ $name ] = $value;
		return $this;
	}

	public function getCookieHeader() {
		$cookies = [];
		foreach( $this->getCookies() as $name => $value ) {
			$cookies[] = urlencode( $name ) . '=' . urlencode( $value );
		}
		return self::headerRaw(
			'Cookie',
			implode( '; ', $cookies )
		);
	}

	public function getHTTPCookies() {
		return $this->cookies;
	}

	/**
	 * Set an HTTP cookie in its raw form.
	 *
	 * @param $cookie string Cookie text
	 */
	private function setRawCookie( $cookie ) {
		$parts = explode(';', $cookie);
		if( isset( $parts[0] ) ) {
			$name_value = explode('=', $parts[0] );
			if( 2 === count( $name_value ) ) {
				list( $name, $value ) = $name_value;
				$this->setCookie( $name, $value );
			}
		}
	}

	/**
	 * Load HTTP response headers, filling cookies.
	 */
	private function loadHTTPResponseHeaders( $http_response_headers ) {
		$this->latestHttpResponseHeaders = self::parseHTTPResponseHeaders( $http_response_headers );
		if( isset( $this->latestHttpResponseHeaders['Set-Cookie'] ) ) {
			foreach( $this->latestHttpResponseHeaders['Set-Cookie'] as $cookie ) {
				$this->setRawCookie( $cookie );
			}
		}
	}

	private function implodeHTTPHeaders( $headers ) {
		if( ! $headers ) {
			return null;
		}
		$s = '';
		foreach( $headers as $header ) {
			$s .= "$header\r\n";
		}
		return $s;
	}

	/**
	 * Group HTTP headers by keys.
	 *
	 * @return array
	 */
	private static function parseHTTPResponseHeaders( $http_response_headers ) {
		$headers = [];
		foreach( $http_response_headers as $header ) {
			$header_parts = explode(':', $header, 2);
			if( 2 === count( $header_parts ) ) {
				list( $name, $value ) = $header_parts;
				if( ! isset( $headers[ $name ] ) ) {
					$headers[ $name ] = [];
				}
				$headers[ $name ][] = ltrim( $value );
			}
		}
		return $headers;
	}

	/**
	 *	Make an HTTP header raw string from a name => value pair.
	 *
	 * The value will be URL-encoded.
	 *
	 * @param $name string HTTP header name
	 * @param $value string HTTP header value
	 * @return string HTTP header
	 */
	private static function header( $name, $value ) {
		return self::headerRaw( $name, urlencode( $value ) );
	}

	/**
	 * Make an HTTP header raw string from a name => value pair.
	 *
	 * The value is not URL-encoded.
	 *
	 */
	private static function headerRaw( $name, $value ) {
		return sprintf( '%s: %s', $name, $value );
	}

	/**
	 * Print a tedious message to the output.
	 *
	 * @param $type string Something like 'warn'
	 * @param $msg string Log message
	 */
	protected static function log( $type, $msg ) {
		printf("# [%s] \t %s\n", $type, $msg);
	}

	/**
	 * Can be overrided to post-process the data before its use
	 *
	 * @param $data GET/POST data
	 * @return array GET/POST data post-processed
	 */
	protected function onDataReady( $data ) {
		return $data;
	}

	/**
	 * Callback to be overloaded
	 *
	 * This is called every time something is fetched.
	 *
	 * @param $result mixed Result
	 * @return mixed Result
	 */
	protected function onFetched( $result ) {
		return $result;
	}
}
