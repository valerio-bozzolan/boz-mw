<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2019 Valerio Bozzolan
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

# Wikibase
namespace wb;

/**
 * Sitelink collector
 */
class Sitelinks {

	/**
	 * @var array
	 */
	private $sitelinks = [];

	/**
	 * Constructor
	 *
	 * @param $sitelinks array
	 */
	public function __construct( $sitelinks = [] ) {
		foreach( $sitelinks as $sitelink ) {
			$this->set( $sitelink );
		}
	}

	/**
	 * Get a certain site
	 *
	 * @param $site string
	 * @return Sitelink|false
	 */
	public function get( $site ) {
		if( isset( $this->sitelinks[ $site ] ) ) {
			return $this->sitelinks[ $site ];
		}
		return false;
	}

	/**
	 * Does it have a certain sitelink?
	 *
	 * @param $site string
	 * @return bool
	 */
	public function have( $site ) {
		return false !== $this->get( $site );
	}

	/**
	 * Set/add a certain sitelink
	 *
	 * @param $sitelink Sitelink
	 * @return self
	 */
	public function set( Sitelink $sitelink ) {
		$this->sitelinks[ $sitelink->getSite() ] = $sitelink;
		return $this;
	}

	/**
	 * Get all the sitelinks
	 *
	 * @return array
	 */
	public function getAll() {
		return $this->sitelinks;
	}

	/**
	 * Get as an associative array
	 *
	 * @return array
	 */
	public function toData() {
		$all = $this->getAll();
		foreach( $all as $site => $sitelink ) {
			$all[ $site ] = $sitelink->toData();
		}
		return $all;
	}

	/**
	 * Create from an associative array (JSON response part)
	 *
	 * @param $data array
	 * @return self
	 */
	public static function createFromData( $data ) {
		if( ! is_array( $data ) ) {
			throw new WrongDataException( self::class );
		}
		$sitelinks = new self();
		foreach( $data as $sitelink ) {
			$sitelinks->set( Sitelink::createFromData( $sitelink ) );
		}
		return $sitelinks;
	}

	/**
	 * Get all the sites imploded
	 *
	 * @param $glue string
	 * @return string
	 */
	protected function getImplodedSites( $glue = ',' ) {
		$codes = [];
		foreach( $this->getAll() as $sitelink ) {
			$codes[] = $sitelink->getSite();
		}
		return implode( $glue, $codes );
	}

	/**
	 * String rappresentation
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf( 'sitelink: %s', $this->getImplodedsites() );
	}
}
