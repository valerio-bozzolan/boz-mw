<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2019 Valerio Bozzolan
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

/**
 * A Namespace.
 *
 * Yes, PHP does not allow to call a class "Namespace".
 */
class Ns {

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var object
	 */
	private $name;

	/**
	 * @var object
	 */
	private $canonicalName;

	/**
	 * Aliases of this namespace
	 *
	 * @var array
	 */
	private $aliases = [];

	/**
	 * Constructor
	 *
	 * @var $id int
	 * @var $name string
	 * @var $aliases array
	 */
	public function __construct( $id, $name, $aliases = [] ) {
		$this->setID( $id );
		$this->setName( $name );
		$canonical_name = self::existsDefaultCanonicalName( $id )
			? self::defaultCanonicalName( $id )
			: $name;
		$this->setCanonicalName( $canonical_name );
		foreach( $aliases as $alias ) {
			$this->addAlias( $alias );
		}
	}

	/**
	 * Get the namespace ID
	 *
	 * @return int
	 */
	public function getID() {
		return $this->id;
	}

	/**
	 * Get the local namespace name (eventually localized)
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name->get();
	}

	/**
	 * Get the namespace canonical name (in English)
	 *
	 * @return string
	 */
	public function getCanonicalName() {
		return $this->canonicalName->get();
	}

	/**
	 * Set the namespace ID
	 *
	 * @param $id int
	 * @return self
	 */
	public function setID( $id ) {
		$this->id = $id;
		return $this;
	}

	/**
	 * Set the namespace name
	 *
	 * @param $name string
	 * @return self
	 */
	public function setName( $name ) {
		$this->name = new NsPart( $name );
		return $this;
	}

	/**
	 * Set the namespace canonical name
	 *
	 * @param $name string
	 * @param $id int
	 * @return self
	 */
	public function setCanonicalName( $name ) {
		$this->canonicalName = new NsPart( $name );
		return $this;
	}

	/**
	 * Add another namespace alias
	 *
	 * @param $alias string
	 * @return self
	 */
	public function addAlias( $alias ) {
		$this->aliases[] = new NsPart( $alias );
		return $this;
	}

	/**
	 * Get all namespace parts
	 *
	 * @return array
	 */
	public function getParts() {
		$all = [];
		$all[] = $this->name;
		if( $this->isCanonicalNameDifferent() ) {
			$all[] = $this->canonicalName;
		}
		foreach( $this->aliases as $alias ) {
			$all[] = $alias;
		}
		return $all;
	}

	/**
	 * @deprecated
	 * @return array
	 */
	public function getAllTitlePartsCapitalized() {
		return $this->getParts();
	}

	/**
	 * Get a regex that matches this namespace
	 *
	 * @return string
	 */
	public function getRegex() {
		$all = [];
		foreach( $this->getParts() as $part ) {
			$all[] = $part->getRegex();
		}

		// category corner-case: a wikilink to a category has the ':' prefix
		$catprefix = $this->getID() === 14
			? ':'
			: '';

		// in case of a single part we can avoid the OR group
		if( count( $all ) === 1 ) {
			return $catprefix . $all[ 0 ];
		}

		// return an 'OR' query, without creating a group
		$or = implode( '|', $all );
		return $catprefix . "(?>$or)";
	}

	/**
	 * Check if the canonical name is different from the local one
	 *
	 * @return bool
	 */
	public function isCanonicalNameDifferent() {
		return $this->getName() !== $this->getCanonicalName();
	}

	/**
	 * Check if it exists this default canonical name (from ID)
	 *
	 * @param $id int
	 * @return boolean
	 */
	public static function existsDefaultCanonicalName( $id ) {
		return array_key_exists( $id, self::defaultCanonicalNames() );
	}

	/**
	 * Get the default canonical name from its ID
	 *
	 * @param $id int
	 * @return boolean
	 */
	public static function defaultCanonicalName( $id ) {
		if( ! self::existsDefaultCanonicalName( $id ) ) {
			throw new \Exception('unexisting namespace ID');
		}
		return self::defaultCanonicalNames()[ $id ];
	}

	/**
	 * Normalize a namespace name
	 *
	 * @param $name string
	 * @return string
	 */
	public static function normalizeName( $name ) {
		return ucfirst( strtolower( trim( $name ) ) );
	}

	/**
	 * Get all the known MediaWiki default canonical names
	 *
	 * @return array
	 */
	public static function defaultCanonicalNames() {
		return [
			0  => '',
			1  => 'Talk',
			2  => 'User',
			3  => 'User talk',
			4  => 'Project',
			5  => 'Project talk',
			6  => 'File',
			7  => 'File talk',
			8  => 'MediaWiki',
			9  => 'MediaWiki talk',
			10 => 'Template',
			11 => 'Template talk',
			12 => 'Help',
			13 => 'Help talk',
			14 => 'Category',
			15 => 'Category talk',
			-1 => 'Special',
			-2 => 'Media'
		];
	}
}
