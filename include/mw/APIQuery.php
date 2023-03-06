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

use Iterator;
use cli\Log;

/**
 * MediaWiki API query with continuation handler
 */
class APIQuery implements Iterator {

	/**
	 * MediaWiki API object
	 *
	 * @var mw\API
	 */
	private $api;

	/**
	 * Data of the GET/POST request
	 *
	 * @var array
	 */
	private $data;

	/**
	 * MediaWiki continuation parameter
	 *
	 * @var mixed
	 */
	private $continue = null;

	/**
	 * Actual (current) element
	 *
	 * @var mixed
	 */
	private $actual;

	/**
	 * Iterator key
	 *
	 * @var int
	 */
	private $i = -1;

	/**
	 * Construct
	 *
	 * @param $api mw\API
	 * @param $data array
	 */
	public function __construct( $api, $data ) {
		$this->api = $api;
		$this->data = $data;
	}

	/**
	 * Check if there are other requests to do to fetch the whole result set.
	 *
	 * @return bool
	 */
	public function hasNext() {
		return $this->continue !== false;
	}

	/**
	 * Fetch the next results
	 *
	 * @return mixed
	 */
	public function fetchNext() {
		$data = $this->data;
		if( $this->continue ) {
			Log::debug( "query continue" );
			foreach( $this->continue as $arg => $value ) {
				$data[ $arg ] = $value;
			}
		}
		return $this->actual = $this->fetch( $data );
	}

	/**
	 * Create an API result generator
	 *
	 * It handles the API query continuation transparently.
	 *
	 * @return Generator
	 */
	public function getGenerator() {
		while( $this->hasNext() ) {
			yield $this->fetchNext();
		}
	}

	/**
	 * Fetch results
	 *
	 * @param $data array GET/POST data
	 * @return mixed
	 */
	private function fetch( $data ) {
		$this->i++;
		$latest = $this->api->fetch( $data );
		$this->continue = isset( $latest->continue )
			? $latest->continue
			: false;
		return $latest;
	}

	/**
	 * @override
	 */
	public function rewind() {
		$this->i = -1;
		$this->continue = null;
		$this->fetchNext();
	}

	/**
	 * @override
	 * @return bool
	 */
	public function valid() {
		return (bool) $this->actual;
	}

	/**
	 * @override
	 * @return mixed
	 */
	public function current() {
		return $this->actual;
	}

	/**
	 * @override
	 * @return int
	 */
	public function key() {
		return $this->i;
	}

	/**
	 * @override
	 */
	public function next() {
		if( $this->hasNext() ) {
			$this->fetchNext();
		} else {
			$this->actual = false;
		}
	}

}
