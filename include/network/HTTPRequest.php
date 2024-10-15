<?php
# boz-mw - Another MediaWiki API handler in PHP
# Copyright (C) 2017-2024 Valerio Bozzolan, contributors
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

# Network
namespace network;

use cli\Log;
use InvalidArgumentException;

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
	const VERSION = 1.1;

	/**
	 * Source code URL
	 *
	 * @var string
	 */
	const REPO = 'https://gitpull.it/source/boz-mw/';

	/**
	 * Wait seconds before each GET request.
	 *
	 * @var float
	 */
	public static $WAIT = 0.2;

	/**
	 * Wait seconds before each POST request
	 *
	 * @var float
	 */
	public static $WAIT_POST = 0.2;

	/**
	 * Seconds to wait before each server error
	 *
	 * Well, do not try to not denial of service the webserver.
	 *
	 * @var float
	 */
	public static $WAIT_ANTI_DOS = 5.0;

	/**
	 * Additional seconds to wait for each retry
	 *
	 * @var float
	 */
	public static $WAIT_ANTI_DOS_STEP = 1.5;

	/**
	 * Number of requests done because of server errors
	 *
	 * When it's over self::$MAX_RETRIES, the script dies.
	 *
	 * @var int
	 */
	private $retries = 0;

	/**
	 * Counter of executed HTTP queries
	 *
	 * @var int
	 */
	private $queries = 0;

	/**
	 * Maximum number of retries before quitting.
	 * I like to insist a bit.
	 */
	public static $MAX_RETRIES = 8;

	/**
	 * Full HTTP URL to the API endpoint
	 *
	 * @var string
	 */
	protected $api;

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
	 * The cURL object
	 *
	 * @param mixed
	 */
	private $curl;

	/**
	 * Latest HTTP response headers
	 *
	 * They are indexed by lowercase header name.
	 *
	 * @var array
	 */
	private $latestHttpResponseHeaders = [];

	/**
	 * Latest HTTP error status code
	 *
	 * @var Status
	 */
	private $latestHttpResponseStatus;

	/**
	 * HTTP cookies
	 *
	 * @var array
	 */
	private $cookies = [];

	/**
	 * Constructor.
	 *
	 * @param $api string API endpoint
	 * @param $args array Internal arguments
	 */
	public function __construct( $api, $args = [] ) {
		$this->api = $api;
		$this->setArgs( $args );

		// initialize cURL session
		$this->curl = curl_init();

		// keep cookies just for this session
		curl_setopt( $this->curl, CURLOPT_COOKIESESSION, true );
	}

	/**
	 * Destructor
	 *
	 * Free some resources
	 */
	public function __destruct() {
		curl_close( $this->curl );
	}

	/**
	 * Statical constructor.
	 *
	 * @return network\HTTPRequest
	 */
	public static function factory( $api, $args = [] ) {
		return new self( $api, $args );
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
	 * Set internal arguments
	 *
	 * method: GET, POST etc.
	 * user-agent: HTTP user agent
	 * sensitive: flag to indicate if the data is sensitive and should not be printed as usual in the log
	 * wait: microseconds to wait after every GET request
	 * wait-post: microseconds to wait after every POST request
	 * headers: HTTP headers
	 *
	 * @param $args array Internal arguments
	 * @return self
	 */
	public function setArgs( $args ) {
		$this->args = array_replace( [
			'method'     => 'GET',
			'user-agent' => sprintf( 'boz-mw HTTPRequest.php/%s %s', self::VERSION, self::REPO ),
			'sensitive'  => false,
			'multipart'  => false,
			'wait'       => self::$WAIT,
			'wait-post'  => self::$WAIT_POST,
			'headers'    => [],
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

		$curl = $this->curl;

		// merge the default arguments with the specified ones (the last have more priority)
		$args = array_replace( $this->args, $args );

		// Eventually post-process the data before using
		$data = static::onDataReady( $data );

		// HTTP query using file_get_contents()
		$url = $this->api;

		// well, we support Cookies
		// TODO: use CURLOPT_COOKIEJAR and CURLOPT_COOKIEFILE
		if( $this->haveCookies() ) {
			curl_setopt( $curl, CURLOPT_COOKIE, $this->getCookieHeaderValue() );
		}

		// populate the User-Agent
		if( $args['user-agent'] ) {
			curl_setopt( $curl, CURLOPT_USERAGENT, $args['user-agent'] );
		}

		// request method
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $args['method'] );

		// process query string
		$query = '';
		switch( $args['method'] ) {
			case 'POST':
			case 'PUT':
				// populate the content context

				// the multipart has a data boundary
				if( $args['multipart'] ) {
					// get the request body aggregating the content dispositions generating a safe boundary
					$query = ContentDisposition::aggregate( $data, $boundary );

					// override the content type and set the boundary
					$args['content-type'] = "multipart/form-data; boundary=$boundary";
				} else {
					// normal POST/PUT request
					$query = http_build_query( $data );
				}

				$args['headers'][ 'Content-Type' ] = $args['content-type'];

				// set contend (works for both PUT and POST)
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $query );

				Log::sensitive( "{$args['method']} $url $query", "{$args['method']} $url" );
				break;
			case 'GET':
			case 'HEAD':
				$query = http_build_query( $data );
				$url .= "?$query";

				Log::debug( "GET $url" );
				break;
		}

		// set headers
		// note that this will reset headers from the previous session
		curl_setopt( $curl, CURLOPT_HTTPHEADER, self::headersFlatArray( $args['headers'] ) );

		// eventually preserve server resources to avoid a denial of serve
		if( isset( $args['wait-anti-dos'] ) ) {

			// I hope you will see this amazing warning
			if( $this->retries >= self::$MAX_RETRIES ) {
				Log::error( "stop riding a dead horse: this server ({$this->api}) is burning ¯\_(ツ)_/¯" );
				exit( 1 );
			}

			// set a base wait time and increase it on each step
			$args['wait']  = self::$WAIT_ANTI_DOS;
			$args['wait'] += self::$WAIT_ANTI_DOS_STEP * $this->retries;
			$this->retries++;

			Log::warn( sprintf( "wait and retry (%d of %d)", $this->retries, self::$MAX_RETRIES ) );
		}

		// if this query is not the first one
		// wait before executing the query
		if( $args['wait'] && $this->queries ) {
			Log::debug( sprintf(
				"waiting %.2f seconds",
				$args['wait']
			) );
			usleep( $args['wait'] * 1000000 );
		}

		// URL
		curl_setopt( $curl, CURLOPT_URL, $url );

		//
		// Set a speed-based timeout.
		//
		// Set a reasonable speed-based timeout to avoid to be stuck forever in cURL,
		// for example when you successfully initialize a TCP/IP connection but then
		// the connection is dropped for whatever reason.
		// Interestingly, we were VERY often stuck in cURL for years, for example when
		// (I think) Wikimedia Foundation SRE do their super-interesting networking tricks
		// in Wikimedia Toolforge or in other WMCloud services and friends.
		// The intention here is to drop our cURL connection if we are not exchanging
		// sufficient bytes in a very-high time frame. Note that this is probably
		// generally better than just putting a generic fixed timeout in seconds.
		// I wonder why this is not a default in cURL. I don't know. Anyway...
		//
	  // We apply this combination:
	  //
	  //   CURLOPT_LOW_SPEED_LIMIT
	  //     https://www.php.net/manual/en/curl.constants.php#constant.curlopt-low-speed-limit
	  //       The transfer speed, in bytes per second, that the transfer should be below during
	  //       the count of CURLOPT_LOW_SPEED_TIME seconds before PHP considers the transfer too
	  //       slow and aborts.
	  //   CURLOPT_LOW_SPEED_TIME
	  //     https://www.php.net/manual/en/curl.constants.php#constant.curlopt-low-speed-time
	  //       The number of seconds the transfer speed should be below CURLOPT_LOW_SPEED_LIMIT
	  //       before PHP considers the transfer too slow and aborts.
	  //
	  // With the above combination we can trigger the following desired exception:
	  //
		//    "Operation too slow. Less than 1 bytes/sec transferred the last 120 seconds"
		//
		// If you are affected by this exception, maybe because you are downloading
		// something VERY VERY VERY SLOW; PLEASE STOP connecting with carrier pigeons
		// (or whatever you are doing) and use a decent Internet connection.
		// To the dear SRE team: if you are reading, PLEASE stop pulling network plugs
		// at random LOL in the middle of a nice TCP conversation... thaaanks <3 <3 <3 <3 asd
		// Special thanks to Parma1983 and all the other itwiki folks, for all their patient bug
		// reporting over these very long and boring years...
		// This bug affected at least itwiki since 2019, 2020, 2021, 2022, 2023 and 2024 (!!!!).
		// https://it.wikipedia.org/w/index.php?title=Discussioni_utente:BotCancellazioni&oldid=141641187#Bot_fermo
		// https://it.wikipedia.org/w/index.php?title=Discussioni_utente:BotCancellazioni&oldid=141641187#Bot_fermo_2
		// https://it.wikipedia.org/w/index.php?title=Discussioni_utente:BotCancellazioni&oldid=141641187#Bot_Fermo_3
		// https://phabricator.wikimedia.org/T375937
		// https://gitpull.it/T1274
		//   - [[User:Valerio_Bozzolan]] 2024-10-15 16:51
		curl_setopt( $curl, CURLOPT_LOW_SPEED_LIMIT, 1 ); // bytes
		curl_setopt( $curl, CURLOPT_LOW_SPEED_TIME, 120 ); // seconds

		// cURL execution will return the result on success, false on failure
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

		// internal cURL flag to also return headers
		curl_setopt( $curl, CURLOPT_HEADER, true );

		// this contains also the headers
		$http_response_raw = curl_exec( $curl );

		// yeah! another one
		$this->queries++;

		// if the cURL execution fails, then the detailed error message is reported
		if( $http_response_raw === false ) {
			$error_msg = curl_error( $curl );
			$error_num = curl_errno( $curl );

			// In some well-known cases, suppress the exception to retry later.
			try {
				CURLException::throwFromErrorMsgAndNum( $error_msg, $error_num );
			} catch (CURLExceptionTransport $e) {
				Log::error( sprintf(
					"A wild cURL transport problem appears: %s: %s",
					get_class($e),
					$e->getMessage()
				) );
			}
		}

		// load the response with the headers
		$response = $this->loadHTTPResponseRaw( $http_response_raw );

		// Check the HTTP status
		$http_status = $this->getLatestHTTPResponseStatus();

		// no status no party
		if( !$http_status ) {

			// oh nose!
			Log::error( "Houston, we have not a valid response status" );

			// retry but without DOSsing
			$args = array_replace( $args, [
				'wait-anti-dos' => true,
			] );

			// retry...
			return $this->fetch( $data, $args );

		} elseif( !$http_status->isOK() ) {

			// retry again if it's a server error
			if( $http_status->isServerError() ) {

				// oh nose!
				Log::error( sprintf( "Houston, we have the code %s: %s",
					$http_status->getCode(),
					$http_status->getMessage()
				) );

				// retry but without DOSsing
				$args = array_replace( $args, [
					'wait-anti-dos' => true,
				] );

				// retry
				return $this->fetch( $data, $args );
			}

			throw new NotOKException( $http_status );
		}

		// show what's happening
		Log::debug( $http_status->getHeader() );

		return static::onFetched( $response, $data, $args['method'] );
	}

	/**
	 * Effectuate an HTTP POST.
	 *
	 * @param $data array POST data
	 * @param $args Internal arguments
	 * @return mixed Response
	 */
	public function post( $data = [], $args = [] ) {
		$args = array_replace(
			// low-priority arguments
			[
				'wait'         => $this->args['wait-post'],
				'content-type' => 'application/x-www-form-urlencoded',
			],

			// medium priority arguments
			$args,

			// high priority arguments
			[ 'method' => 'POST' ]
		);
		return $this->fetch( $data, $args );
	}

	/**
	 * Effectuate an HTTP POST with multipart (suitable for files)
	 *
	 * An useful reference:
	 *  	https://stackoverflow.com/a/4247082
	 *
	 * @param array $data POST data to be converted in content dispositions
	 * @param array $content_disposition Array of ContentDisposition(s)
	 * @param array $args Internal arguments
	 * @return mixed Response
	 */
	public function postMultipart( $data = [], $args = [] ) {
		$args = array_replace( $args, [
			'multipart'    => true,
			'content-type' => null, // will be filled later
		] );
		return $this->post( $data, $args );
	}

	/**
	 * Get the latest HTTP response headers
	 *
	 * They will be indexed by lowercase HTTP header name.
	 *
	 * @return array
	 */
	public function getLatestHTTPResponseHeaders() {
		return $this->latestHttpResponseHeaders;
	}

	/**
	 * Get latest HTTP status
	 *
	 * @return Status
	 */
	private function getLatestHTTPResponseStatus() {
		return $this->latestHttpResponseStatus;
	}

	/**
	 * Check if cookies are set
	 *
	 * @return bool
	 */
	public function haveCookies() {
		return $this->cookies;
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

	/**
	 * Get the 'Cookie' HTTP header value
	 *
	 * @return string
	 */
	public function getCookieHeaderValue() {
		$cookies = [];
		foreach( $this->getCookies() as $name => $value ) {
			$cookies[] = urlencode( $name ) . '=' . urlencode( $value );
		}
		return implode( '; ', $cookies );
	}

	/**
	 * Get the 'Cookie' HTTP header
	 *
	 * @return string
	 */
	public function getCookieHeader() {
		return self::header(
			'Cookie',
			$this->getCookieHeaderValue()
		);
	}

	/**
	 * Get an array of all the HTTP cookies
	 *
	 * @return array
	 */
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
	 * Load the raw HTTP response that contains HTTP status code(s), empty line(s) and the body
	 *
	 * It returns the body.
	 *
	 * Note that, after a POST, you can have two HTTP status codes (one is an unuseful 100 continue).
	 * In this case, the 100 continue is ignored.
	 *
	 * It will be analyzed the 'Set-Cookie' response header.
	 *
	 * @param string $http_response_raw HTTP raw response with headers and all the stuff
	 * @return string HTTP response body without headers
	 */
	private function loadHTTPResponseRaw( $http_response_raw ) {

		$SEPARATOR = "\r\n";

		// each line has this separator
		$lines = explode( $SEPARATOR, $http_response_raw );

		$http_status = null;
		$is_header = true;

		// document body
		$body = '';

		// parse each line
		foreach( $lines as $line ) {

			// have we already found the HTTP status code?
			if( $http_status ) {

				// are we in the header?
				if( $is_header ) {

					// is this an empty line?
					if( $line === '' ) {

						// the next line is the body
						$is_header = false;

					// is this line not empty?
					} else {

						// check if it's an header like 'Foo: bar'
						$header_parts = explode( ':', $line, 2 );

						// is this line with a semicolon?
						if( 2 === count( $header_parts ) ) {

							list( $name, $value ) = $header_parts;

							// the header names must be considered case-insensitive
							$name = strtolower( $name );

							if( !isset( $headers[ $name ] ) ) {
								$headers[ $name ] = [];
							}
							$headers[ $name ][] = ltrim( $value );

						// is this line without a semicolon?
						} else {

							Log::warning( sprintf(
								"nonsense header %s",
								$line
							) );
						}
					}

				// is this the body?
				} else {

					$body .= $line . $SEPARATOR;
				}

			// is the HTTP status missing?
			} else {

				// in this case it's an HTTP/1.1 200 or something like that
				try {
					$http_status = Status::createFromHeader( $line );

					// skip unuseufl transactional states
					if( $http_status->getCode() === '100' ) {
						$http_status = null;
					}

				} catch( InvalidArgumentException $e ) {
					$headers[ $line ] = true;
				}
			}
		}

		// remember this stuff
		$this->latestHttpResponseStatus = $http_status;
		$this->latestHttpResponseHeaders = $headers;

		// parse each cookie (the header name will be always case insensitive)
		if( isset( $this->latestHttpResponseHeaders['set-cookie'] ) ) {
			foreach( $this->latestHttpResponseHeaders['set-cookie'] as $cookie ) {
				$this->setRawCookie( $cookie );
			}
		}

		return $body;
	}

	/**
	 * Convert an array of headers (with values as strings or associative arrays)
	 * to a simple array of strings.
	 *
	 * @param array $headers
	 * @return array headers
	 */
	public static function headersFlatArray( $headers ) {

		$flat = [];
		foreach( $headers as $key => $header ) {

			// convert 'Key' => 'Value'
			// to      'Key: Value'
			if( is_string( $key ) ) {
				$header = self::header( $key, $header );
			}

			$flat[] = $header;
		}

		return $flat;
	}

	/**
	 * Implode HTTP headers with CRLF
	 *
	 * @param array $headers
	 * @return string
	 */
	public static function implodeHTTPHeaders( $headers ) {

		// no headers no party
		if( !$headers ) {
			return null;
		}

		$s = '';
		foreach( $headers as $key => $header ) {
			$s .= "$header\r\n";
		}

		return $s;
	}

	/**
	 * Group HTTP headers by keys and get the HTTP Status.
	 *
	 * Note that the keys always will be lowercase e.g. 'set-cookie'.
	 *
	 * @param  array $http_response_headers
	 * @return array The first element contains an associative array of header name and value(s). The second one contains the Status.
	 */
	private static function parseHTTPResponseHeaders( $lines ) {

		$http_status = null;
		$headers = [];
		foreach( $http_response_headers as $header ) {

		}

		// do not generate an exception - just retry
		// wtf
		//if( null === $http_status ) {
			// throw new Exception( "HTTP response without an HTTP status code" );
		//}

		return [ $headers, $http_status ];
	}

	/**
	 * Build a sanitized HTTP header string from a name => value pair
	 *
	 * @param string $name  HTTP header name
	 * @param string $value HTTP header value
	 * @return string HTTP header
	 */
	public static function header( $name, $value ) {
		return self::headerRaw( sprintf( '%s: %s', $name, $value ) );
	}

	/**
	 * Sanitize a single header
	 *
	 * As you know an header does not contains a line feed or a carriage return.
	 *
	 * @param $name string HTTP header
	 * @return string HTTP header
	 */
	private static function headerRaw( $header ) {
		if( false !== strpos( $header, "\n" ) || false !== strpos( $header, "\r" ) ) {
			Log::warn( "wtf header with line feed or carriage return (header injection?)" );
			Log::debug( $header );
			return str_replace( [ "\n", "\r" ], '', $header );
		}
		return $header;
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
	 * @param $response mixed Response
	 * @param $request_data mixed GET/POST data
	 * @param $method string HTTP Method 'GET'/'POST'
	 * @return mixed Response
	 */
	protected function onFetched( $response, $request_data, $method ) {
		return $response;
	}
}
