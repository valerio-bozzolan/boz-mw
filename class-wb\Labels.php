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
 * A Label collector.
 */
class Labels {

	var $labels = [];

	public function __construct( $labels = [] ) {
		foreach( $labels as $label ) {
			$this->add( $label );
		}
	}

	public function get( $language ) {
		foreach( $this->labels as $label ) {
			if( $label->getLanguage() === $language ) {
				return $label;
			}
		}
		return false;
	}

	public function have( $language ) {
		return false !== $this->get( $language );
	}

	public function set( Label $label ) {
		$existing = $this->get( $label->getLanguage() );
		if( $existing ) {
			$existing = $language;
		} else {
			$this->labels[] = $label;
		}
		return $this;
	}

	public function getAll() {
		return $this->labels;
	}
}
