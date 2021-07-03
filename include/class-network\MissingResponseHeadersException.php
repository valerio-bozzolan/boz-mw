<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2018, 2019, 2020, 2021 Valerio Bozzolan
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
 * Exception thrown when from the response there are no headers
 *
 * NOTE: This exception is never thrown.
 * TODO: Remove this file in 2024.
 * See https://gitpull.it/T886
 *
 * @deprecated
 */
class MissingResponseHeadersException extends Exception {

	/**
	 * The URL involved in this exception
	 *
	 * @var string
	 */
	private $url;

	/**
	 * Some context from the request
	 *
	 * @var object
	 */
	private $context;

	/**
	 * Constructor
	 *
	 * @param $http_status object HTTP status
	 * @param $code int
	 * @param $previous object
	 */
	public function __construct( $url, $context ) {
		$this->url = $url;
		$this->context = $context;
		parent::__construct( "failed to obtain a valid response from the server. Wrong request? Server dead?", 0, null );
	}

	/**
	 * Get the URL involved in this Exception
	 *
	 * @return string
	 */
	public function getURL() {
		return $this->url;
	}

	/**
	 * Get the context involved in this Exception
	 *
	 * @return object
	 */
	public function getContext() {
		return $this->context;
	}
}
