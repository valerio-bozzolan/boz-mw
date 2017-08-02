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
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

class APIRequest {
	const WAIT = 2;
	const WAIT_DOS = 5;

	const VERSION = 0.1;

	private $api;
	private $args;
	private $continue;

	private $last = null;

	function __construct($api, $args) {
		$this->args = array_merge( [
	                'maxlag'  =>  5,
			'format'  => 'json',
			'hello'   => sprintf("boz-mw/%s", self::VERSION)
		], $args);

		$this->api = $api;

		$this->args['hello'] and
			self::hello( $this->args['hello'] );

		// Don't send
		unset( $this->args['hello'] );
	}

	static function factory($api, $args) {
		return new self($api, $args);
	}

	function lastfetch() {
		return isset( $this->last ) ? $this->last : $this->fetch();
	}

	// https://www.wikidata.org/w/api.php?action=wbgetclaims&entity=Q38&format=jsonfm
	function fetchFirstClaimValue($property) {
		return @ $this->lastfetch()->claims->{$property}[0]->mainsnak->datavalue->value;
	}

	function hasNext() {
		return $this->continue !== false;
	}

	function fetch($wait = true, $args = null) {
		$args = $args ? $args : $this->args;

		if( $wait ) {
			self::log('INFO', "Waiting");
			sleep(self::WAIT);
		}
		self::log('INFO', "Fetching");

		$query = http_build_query($args);
		$result = json_decode( file_get_contents("{$this->api}?$query") );

		self::log('INFO', "Fetched");

		if( isset( $next->error ) && $next->error->code === 'maxlag' ) {
			self::log(WARN, "Lag! Waiting");
			sleep(self::WAIT_DOS);
			return $this->fetch(false, $args);
		}

		return $this->last = $result;
	}

	function getNext($wait = true) {
		$args = $this->args;

		if( $this->continue ) {
			self::log('INFO', "Will continue");
			foreach($this->continue as $arg => $value) {
				$args[ $arg ] = $value;
			}
		}

		$next = $this->fetch($wait, $args);

		if( isset( $next->continue ) ) {
			$this->continue = $next->continue;
		} else {
			$this->continue = false;
		}

		return $next;
	}

	static function hello($wall) {
		ini_set('user_agent', $wall);
	}

	static function log($type, $msg) {
		printf("[%s] \t %s\n", $type, $msg);
	}
}
