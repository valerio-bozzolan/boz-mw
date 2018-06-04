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

namespace network;

/**
 * A class for whatever HTTP status that is not 200
 */
class NotOKException extends Exception {

	/**
	 * HTTP status
	 *
	 * @var Status
	 */
	private $httpStatus;

	/**
	 * Constructor
	 *
	 * @param $http_status object HTTP status
	 * @param $code int
	 * @param $previous object
	 */
	public function __construct( $http_status, $code = 0, Exception $previous = null ) {
		$this->httpStatus = $http_status;
		parent::__construct( $http_status->getHeader(), $code, $previous );
	}

	/**
	 * Get the HTTP status object
	 *
	 * @return Status
	 */
	public function getHTTPStatus() {
		return $this->httpStatus;
	}

}
