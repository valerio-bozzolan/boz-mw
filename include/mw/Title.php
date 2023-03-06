<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2019, 2020, 2021 Valerio Bozzolan
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
 * A page Title without a namespace.
 *
 * See also CompleteTitle class.
 */
class Title extends TitlePartCapitalized {

	private $site;

	/**
	 * Constructor
	 *
	 * @param $name string
	 * @param $site object
	 */
	public function __construct( $name, $site ) {
		parent::__construct( $name );
		$this->site = $site;
	}

	/**
	 * Get the {{SUBPAGENAME}} for this complete page title
	 *
	 * Returns 'asd' from 'The/great/asd'
	 *
	 * See https://www.mediawiki.org/wiki/Help:Magic_words
	 */
	public function getSubPageName() {
		return basename( $this->get() );
	}

	/**
	 * Get a regex matching this title part
	 *
	 * @return string
	 */
	public function getRegex( $unused = null ) {
		return $this->site->hasCapitalLinks()
			? $this->getRegexFirstCaseInsensitive()
			: parent::getRegex();
	}

}
