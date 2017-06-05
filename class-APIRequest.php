<?php
# Leaflet Wikipedians map
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

	private $api;
	private $args;
	private $continue;
	private $cmcontinue;

	private $last = null;

	function __construct($api, $args) {
		$this->args = array_merge( [
	                'maxlag'  =>  5,
			'format'  => 'json'
		], $args);

		$this->api  = $api;

		self::hello();
	}

	static function factory($api, $args) {
		return new self($api, $args);
	}

	static function hello() {
		ini_set('user_agent', 'it-wiki-users-leaflet/0.1 (https://wikitech.wikimedia.org/wiki/User_talk:Valerio_Bozzolan; gnu@linux.it) PHP/5.5.9');
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
			logit(INFO, "Waiting");
			sleep(self::WAIT);
		}
		logit(INFO, "Fetching");

		$query = http_build_query($args);
		$result = json_decode( file_get_contents("{$this->api}?$query") );

		logit(INFO, "Fetched");

		if( isset( $next->error ) ) {
			if( $next->error->code == 'maxlag' ) {
				logit(WARN, "Lag! Waiting");
				sleep(self::WAIT_DOS);
				return $this->fetch(false, $args);
			}
		}

		return $this->last = $result;
	}

	function getNext($wait = true) {
		$args = $this->args;

		if( $this->continue ) {
			logit(INFO, "Will continue");
			$args['continue']   = $this->continue;
			$args['cmcontinue'] = $this->cmcontinue;
		}

		$next = $this->fetch($wait, $args);

		if( isset( $next->continue ) ) {
			$this->continue =  $next->continue->continue;
			$this->cmcontinue = $next->continue->cmcontinue;
		} else {
			$this->continue = false;
		}

		return $next;
	}
}
