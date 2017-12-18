<?php
# Copyright (C) 2017 Valerio Bozzolan
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.

class ShellCommand {
	private $prefix;
	private $args;

	function __construct( $prefix, $args = [] ) {
		$this->prefix = $prefix;
		$this->args = $args;
	}

	static function factory( $prefix, $args = [] ) {
		return new self( $prefix, $args );
	}

	function arg($arg) {
		$this->args[] = $arg;
		return $this;
	}

	function getArgs() {
		$args = [];
		foreach( $this->args as $arg ) {
			$args[] = self::sanitize( $arg );
		}
		return implode(' ', $args);
	}

	function get() {
		return $this->prefix . ' ' . $this->getArgs();
	}

	static private function sanitize( $s ) {
		return escapeshellarg( $s );
	}
}
