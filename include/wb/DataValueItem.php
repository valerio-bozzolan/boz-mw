<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2018, 2019, 2020 Valerio Bozzolan
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
 * A DataValue for a wikibase Item.
 */
class DataValueItem extends DataValue {

	/**
	 * @param $qcode string Entity Q-code as 'Q1'
	 */
	public function __construct( $qcode ) {
		parent::__construct( DataType::ENTITY_ID, [
			'entity-type' => 'item',
			'numeric-id'  => Item::numericQCode( $qcode ),
			'id'          => $qcode,
		] );
	}

	/**
	 * Get a wikitext-compatible version of this value
	 *
	 * This may be awared about which is the wiki that will contain this value,
	 * in order to properly choose a correct permalink in wikilinks etc.
	 *
	 * See https://gitpull.it/T221
	 *
	 * @param $site You can eventually specify in which site you want to print this value
	 */
	public function toPrintableWikitext( \mw\Site $site = null ) {

		/**
		 * If you are on every wiki but Wikidata,
		 * links to items and properties will fail without
		 * an interwiki prefix.
		 *
		 * See https://gitpull.it/T221
		 */
		$prefix = '';
		if( $site && $site::UID !== 'wikidatawiki' ) {
			$prefix = 'wikidata:';
		}

		// stupid way to create a simple wikilink
		// that's good enough for an automatically generated edit summary
		return sprintf(
			'[[%s%s]]',
			$prefix,
			$this->getValue()['id']
		);
	}
}
