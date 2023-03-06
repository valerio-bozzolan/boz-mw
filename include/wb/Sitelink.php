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
 * An entity Sitelink
 *
 * @see https://www.wikidata.org/wiki/Wikidata:Glossary#Sitelink
 */
class Sitelink {

	/**
	 * @var string
	 */
	private $site;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var array
	 */
	private $badges = [];

	/**
	 * Constructor
	 *
	 * @param $site string
	 * @param $title string
	 */
	public function __construct( $site, $title, $badges = [] ) {
		$this->setsite(   $site   )
		     ->setTitle(  $title  )
		     ->setBadges( $badges );
	}

	/**
	 * Get the site
	 *
	 * @return string
	 */
	public function getSite() {
		return $this->site;
	}

	/**
	 * Get the title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Set the site
	 *
	 * @param $site string
	 * @return self
	 */
	public function setSite( $site ) {
		$this->site = $site;
		return $this;
	}

	/**
	 * Get the badges
	 *
	 * @return array
	 */
	public function getBadges() {
		return $this->badges;
	}

	/**
	 * Set the title
	 *
	 * @param $site string
	 * @return self
	 */
	public function setTitle( $title ) {
		$this->title = $title;
		return $this;
	}

	/**
	 * Set the badges
	 *
	 * @param $badges array
	 * @return self
	 */
	public function setBadges( $badges ) {
		$this->badges = $badges;
		return $this;
	}

	/**
	 * Static constructor from an array of data
	 *
	 * @param $data array
	 * @return self
	 */
	public static function createFromData( $data ) {
		if( ! isset( $data[ 'site' ], $data[ 'title' ] ) ) {
			throw new WrongDataException( __CLASS__ );
		}
		return new self( $data[ 'site' ], $data[ 'title' ] );
	}

	/**
	 * Get as an associative array
	 *
	 * @return array
	 */
	public function toData() {
		return [
			'site'  => $this->getSite(),
			'title' => $this->getTitle(),
		];
	}

	/**
	 * String rappresentation
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf( '%s: %s', $this->getSite(), $this->getTitle() );
	}
}
