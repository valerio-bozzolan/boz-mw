<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2018-2023 Valerio Bozzolan
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

/**
 * Exception thrown on network problems reported by cURL
 */
class CURLException extends \Exception {

	private $curlErrorNumber;

	/**
	 * Construct a generic cURL exception
	 *
	 * @param $curl_error_msg string The error message reported by cURL
	 * @param $curl_error_num int The numeric error num reported by cURL
	 *
	 * https://www.php.net/manual/en/function.curl-error.php
	 * https://www.php.net/manual/en/function.curl-errno.php
	 */
	public function __construct( $curl_error_msg, $curl_error_num ) {

		// remember this error number so the user can handle it
		$this->curlErrorNumber = $curl_error_num;

		$message = sprintf(
			"Error reported from cURL [error n. %d]: %s",
			$curl_error_num,
			$curl_error_msg
		);

		return parent::__construct( $message );
	}

	/**
	 * Get the cURL error number related to this exception
	 *
	 * @return int
	 */
	public function getCURLErrorNumber() {
		return $this->curlErrorNumber;
	}

	/**
	 * Throw a specific CURLException from its error message and number
	 *
	 * @param $curl_error_msg string The error message reported by cURL
	 * @param $curl_error_num int The numeric error num reported by cURL
	 */
	public static function throwFromErrorMsgAndNum( $curl_error_msg, $curl_error_num ) {

		// really throw the right Exception
		throw self::createFromErrorMsgAndNum( $curl_error_msg, $curl_error_num );
	}

	/**
	 * Create a new specific instance of CURLException from a cURL error msg and number
	 *
	 * Note that this method just returns an exception and does NOT throw it.
	 *
	 * @param string $curl_error_msg
	 * @param int $curl_error_num
	 */
	protected static function createFromErrorMsgAndNum( $curl_error_msg, $curl_error_num ) {

		// feel free to implement specific classes here
		// if you need a tip:
		//     https://www.php.net/manual/en/function.curl-errno.php#103128
//		switch( $curl_error_num ) {
//			case 1:  return CURLExceptionUnsupportedProtocol( $curl_error_msg, $curl_error_num );
//			case 2:  return CURLExceptionFailedInit         ( $curl_error_msg, $curl_error_num );
//                      ...
//			case 58: return CURLExceptionCertificateProblem ( $curl_error_msg, $curl_error_num );
//			...
//		}

		// this is the base case - probably it's enough for everything
		// at the moment it just throws a generic exception
		return new CURLException( $curl_error_msg, $curl_error_num );
	}

}
