<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2018, 2019 Valerio Bozzolan
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

use InvalidArgumentException;

/**
 * HTTP status
 *
 * @see https://tools.ietf.org/html/rfc2616
 */
class Status {

	/**
	 * HTTP status code
	 *
	 * @var string
	 */
	private $code;

	/**
	 * HTTP status message
	 *
	 * @var string
	 */
	private $message;

	/**
	 * HTTP status class
	 *
	 * @var string
	 */
	private $class;

	/**
	 * HTTP status class
	 *
	 * @var string
	 */
	private $header;

	/**
	 * Constructor
	 *
	 * @param $code    string e.g. '200'
	 * @param $message string e.g. 'OK'
	 * @param $class   string e.g. '2'
	 * @param $header  string e.g. 'HTTP/1.1 200 OK'
	 */
	public function __construct( $code, $message, $class, $header ) {
		$this->code    = $code;
		$this->message = $message;
		$this->class   = $class;
		$this->header  = $header;
	}

	/**
	 * Create an HTTP status object from an header
	 *
	 * @param $header string e.g. 'HTTP/1.1 200 OK'
	 * @return self
	 */
	public static function createFromHeader( $header ) {
		preg_match( '/^HTTP\/[0-9.]* (([0-9])[0-9]+) (.*)$/', $header, $matches );
		if( 4 === count( $matches ) ) {
			list( , $code, $class, $message ) = $matches;
		} else {
			throw new InvalidArgumentException( 'not an HTTP status' );
		}
		return new self( $code, $message, $class, $header );
	}

	/**
	 * Get the HTTP status code
	 *
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * Get the HTTP status message
	 *
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}

	/**
	 * Get the HTTP status class
	 *
	 * @return string
	 */
	public function getClass() {
		return $this->class;
	}

	/**
	 * Get the HTTP status header
	 *
	 * @return string
	 */
	public function getHeader() {
		return $this->header;
	}

	/**
	 * Is this HTTP status code?
	 *
	 * @param $code string|int HTTP status code
	 * @return bool
	 */
	public function isCode( $code ) {
		return $this->getCode() == $code;
	}

	/**
	 * Is this HTTP status class?
	 *
	 * @param $class string|int HTTP status class
	 * @return bool
	 */
	public function isClass( $class ) {
		return $this->getClass() == $class;
	}

	/**
	 * Is the HTTP status 200 OK?
	 *
	 * @return boolean
	 */
	public function isOK() {
		return $this->isCode( 200 );
	}

	/**
	 * Is the HTTP class 4?
	 *
	 * @return boolean
	 */
	public function isClientError() {
		return $this->isClass( 4 );
	}

	/**
	 * Is the HTTP class 5?
	 *
	 * @return boolean
	 */
	public function isServerError() {
		return $this->isClass( 5 );
	}

}
