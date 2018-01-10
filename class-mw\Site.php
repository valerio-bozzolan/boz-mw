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
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.

# MediaWiki
namespace mw;

class Site {

	private $api;

	/**
	 * @var Namespace[]
	 */
	private $namespaces = [];

	public function __construct( $url ) {
		$this->setApi( $url );
	}

	public function getApi() {
		return $this->api;
	}

	public function setApi( $url ) {
		$this->api = self::createApi( $url );
		return $this;
	}

	public function fetch( $data ) {
		return $this->getApi()->fetch( $data );
	}

	public function post( $data ) {
		return $this->getApi()->post( $data );
	}

	public function login( $username = null, $password = null ) {
		$this->getApi()->login( $username, $password );
		return $this;
	}

	public function hasNext() {
		return $this->getApi()->hasNext();
	}

	public function fetchNext() {
		return $this->getApi()->fetchNext();
	}

	public function setApiData( $data ) {
		$this->getApi()->setData( $data );
		return $this;
	}

	public function hasNamespace( $id ) {
		return array_key_exists( $id, $this->namespaces );
	}

	public function getNamespace( $id ) {
		if( ! $this->hasNamespace( $id ) ) {
			throw new \Exception( sprintf( 'missing namespace %d', $id ) );
		}
		return $this->namespaces[ $id ];
	}

	public function setNamespace( Ns $namespace ) {
		$id = $namespace->getID();
		$this->namespaces[ $id ] = $namespace;
		return $this;
	}

	public function setNamespaces( $namespaces ) {
		foreach( $namespaces as $namespace ) {
			$this->setNamespace( $namespace );
		}
		return $this;
	}

	private static function createApi( $url ) {
		return new API( $url );
	}
}
