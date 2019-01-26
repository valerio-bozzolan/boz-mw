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

	private $id;

	private $name;

	private $canonicalName;

	private $aliases = [];

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

	public function getID() {
		return $this->id;
	}

	public function getName() {
		return $this->name->get();
	}

	public function getCanonicalName() {
		return $this->canonicalName->get();
	}

	public function setID( $id ) {
		$this->id = $id;
		return $this;
	}

	public function setName( $name ) {
		$this->name = new TitlePartCapitalized( $name );
		return $this;
	}

	public function setCanonicalName( $canonical_name ) {
		$this->canonicalName = new TitlePartCapitalized( $canonical_name );
		return $this;
	}

	public function addAlias( $alias ) {
		$this->aliases[] = new TitlePartCapitalized( $alias );
		return $this;
	}

	/**
	 * @return TitlePartCapitalized[]
	 */
	public function getAllTitlePartsCapitalized() {
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

	public function isCanonicalNameDifferent() {
		return $this->getName() !== $this->getCanonicalName();
	}

	public static function existsDefaultCanonicalName( $id ) {
		return array_key_exists( $id, self::defaultCanonicalNames() );
	}

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
		return ucfirst( strtolower( $name ) );
	}

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
