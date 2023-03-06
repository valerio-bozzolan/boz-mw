<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2018, 2019 Valerio Bozzolan
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
 * A DataValue for a Wikimedia Commons category
 *
 * There is no special datavalue for a Wikimedia Commons file :^)
 */
class DataValueCommonsCategory extends DataValueString {

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->toPrintableWikitext();
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

		// commons prefix
		$prefix = 'c:Category:';

		// stupid way to create a simple wikilink
		// that's good enough for an automatically generated edit summary
		return sprintf(
			'[[%s%s]]',
			$prefix,
			$this->getValue()
		);
	}

}
