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

class DataModel {

	private $data;

	public function __construct() {
		$this->data = [];
	}

	public function getJSON() {
		return json_encode( $this->get() );
	}

	public function get() {
		return $this->data;
	}

	/**
	 * Set Wikidata labels
	 *
	 * @param array $langs_label Associative array of language-code => label.
	 * @param string $action Set to 'add' or 'remove' or NULL.
	 */
	public function setLabels( $langs_label = [], $action = null ) {
		return $this->set( self::labels( $langs_label, $action ) );
	}

	/**
	 * Wikidata labels data model
	 *
	 * @param array $langs_label Associative array of language-code => label.
	 * @param string $action Set to 'add' or 'remove' or NULL.
	 * @see https://www.wikidata.org/w/api.php?action=help&modules=wbeditentity
	 */
	static public function labels( $langs_label = [], $action = null ) {
		if( null !== $action ) {
			if( 'add' !== $action && 'remove' !== $action ) {
				throw new Exception('wrong labels action');
			}
		}
		$labels = [];
		foreach( $labels as $lang => $value ) {
			$labels[ $lang ] = [
				'language' => $lang,
				'value'    => $value
			];
			if( $action ) {
				$labels[ $lang ][ $action ] = '';
			}
		}
		if( $labels ) {
			$labels = [ 'labels' => $labels ];
		}
		return $labels;
	}

	public function set( $additional_data ) {
		$this->data = array_merge( $this->data, $additional_data );
		return $this;
	}
}
