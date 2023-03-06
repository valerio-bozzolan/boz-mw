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

# Wikibase
namespace wb;

/**
 * @see https://www.wikidata.org/w/api.php?action=help&modules=wbeditentity
 */
class LabelAction extends Label {

	const ADD       = 'add';
	const OVERWRITE = 'overwrite';
	const REMOVE    = 'remove';

	public function __construct( $language, $value, $action = self::ADD ) {
		parent::__construct( $language, $value );
		if( self::ADD === $action ) {
			$this->pleasePreserve();
		} elseif( self::REMOVE === $action ) {
			$this->pleaseRemove();
		} elseif( self::OVERWRITE !== $action ) {
			throw new \Exception('unknown action');
		}
	}

	/**
	 * Without overwriting if it already exists.
	 */
	public function pleasePreserve() {
		$this->add = '';
		unset( $this->remove );
		return $this;
	}

	/**
	 * Removing if it already exists.
	 */
	public function pleaseRemove() {
		$this->remove = '';
		unset( $this->add );
		return $this;
	}

}
