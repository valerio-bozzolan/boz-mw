<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2018 Valerio Bozzolan
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

# MediaWiki API
namespace mw\API;

/**
 * Generic API exception class
 */
class Exception extends \Exception {

	/**
	 * API error object
	 *
	 * @var object
	 */
	private $apiError;

	/**
	 * Constructor
	 *
	 * @param $api_error object The complete error
	 * @param $code int
	 * @param $previous object
	 */
	public function __construct( $api_error, $code = 0, Exception $previous = null ) {
		$this->apiError = $api_error;
		parent::__construct(
			sprintf(
				"API error code: '%s' info: '%s'",
				$this->getApiErrorCode(),
				$this->getApiErrorInfo()
			),
			$code,
			$previous
		);
	}

	/**
	 * Create a specific exception from a generic API error
	 *
	 * @param $api_error object The complete error
	 * @return self
	 */
	public static function createFromApiError( $api_error ) {
		$exception = new self( $api_error );
		$code = $exception->getApiErrorCode();
		switch( $code ) {
			case 'bad-token':
				$exception = new BadTokenException( $api_error );
				break;
			case 'maxlag':
				$exception = new MaxLagException( $api_error );
				break;
			case 'articleexists':
				$exception = new ArticleExistsException( $api_error );
				break;
			case 'missingtitle':
				$exception = new MissingTitleException( $api_error );
				break;
			case 'protectedpage':
				$exception = new ProtectedPageException( $api_error );
				break;
		}
		return $exception;
	}

	/**
	 * Get the complete error object
	 *
	 * @return object
	 */
	public function getApiError() {
		return $this->apiError;
	}

	/**
	 * Get the API error code
	 *
	 * @return string
	 */
	public function getApiErrorCode() {
		return $this->getApiError()->code;
	}

	/**
	 * Get the API error info
	 *
	 * @return string
	 */
	public function getApiErrorInfo() {
		return $this->getApiError()->info;
	}
}
