<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017 Valerio Bozzolan
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

class APIRequest {
	static $WAIT      = 0.2;
	static $WAIT_POST = 5;

	const WAIT_DOS  = 5;

	const VERSION = 0.3;

	private $api;
	private $data;
	private $args;
	private $continue;

	private $last = null;

	private $latestHttpResponseHeaders = [];
	private $cookies = [];

	private $debug = false;

	public function __construct( $api, $data = [], $args = [] ) {
		$this->api = $api;

		$this->data = array_replace( [
			'maxlag'  =>  5,
			'format'  => 'json'
		], $data );

		$this->args = array_replace( [
			'method'     => 'GET',
			'wait'       => self::$WAIT,
			'wait-post'  => self::$WAIT_POST,
			'user-agent' => sprintf( 'Boz-MW APIRequest.php/%s', self::VERSION ),
			'headers'    => [],
			'assoc'      => false
		], $args );
	}

	public static function factory( $api, $args = [] ) {
		return new self( $api, $args );
	}

	public function lastfetch() {
		return isset( $this->last ) ? $this->last : $this->fetch();
	}

	// https://www.wikidata.org/w/api.php?action=wbgetclaims&entity=Q38&format=jsonfm
	public function fetchFirstClaimValue($property) {
		return @ $this->lastfetch()->claims->{$property}[0]->mainsnak->datavalue->value;
	}

	public function hasNext() {
		return $this->continue !== false;
	}

	public function setDebug( $debug ) {
		$this->debug = $debug;
		return $this;
	}

	public function fetch( $data = [], $args = [] ) {
		$data = array_replace( $this->data, $data );
		$args = array_replace( $this->args, $args );

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

		if( $this->debug ) {
			self::log('DEBUG', $url );
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
		$result = json_decode( $result, $args['assoc'] );

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

		if( isset( $result->error ) ) {
			if( 'maxlag' === $result->error->code ) {
				self::log( 'WARN', "Lag!");
				$args = array_replace( [
					'wait' => self::WAIT_DOS
				], $args );
				return $this->fetch( $data , $args );
			} else {
				print_r( $result->error );
				throw new \Exception( "API error" );
			}
		}

		return $this->last = $result;
	}

	public function post( $data = [], $args = [] ) {
		$args = array_replace( [
			'method' => 'POST',
			'wait'   => $this->args['wait-post']
		], $args );
		return $this->fetch( $data, $args );
	}

	public function getNext() {
		$data = $this->data;

		if( $this->continue ) {
			self::log('INFO', "Will continue");
			foreach($this->continue as $arg => $value) {
				$data[ $arg ] = $value;
			}
		}

		$next = $this->fetch( $data );

		$this->continue = isset( $next->continue )
			? $next->continue
			: false;

		return $next;
	}

	public function getLatestHTTPResponseHeaders() {
		return $this->latestHttpResponseHeaders;
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

	public function haveCookies() {
		return $this->cookies;
	}

	public function getCookies() {
		return $this->cookies;
	}

	public function setCookie( $name, $value ) {
		$this->cookies[ $name ] = $value;
		return $this;
	}

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

	private static function header( $name, $value ) {
		return self::headerRaw( $name, urlencode( $value ) );
	}

	private static function headerRaw( $name, $value ) {
		return sprintf( '%s: %s', $name, $value );
	}

	private static function log( $type, $msg ) {
		printf("# [%s] \t %s\n", $type, $msg);
	}
}
