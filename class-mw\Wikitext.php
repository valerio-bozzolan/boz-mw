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

# MediaWiki
namespace mw;

class Wikitext {

	/**
	 * MediaWiki site
	 */
	private $site;

	/**
	 * Plain text wikitext
	 * @var string
	 */
	private $wikitext;

	public function __construct( $site, $wikitext ) {
		$this->setWikitext( $wikitext );
		$this->setSite( $site );
	}

	public function getSite() {
		return $this->site;
	}

	public function getWikitext() {
		return $this->wikitext;
	}

	public function setSite( $site ) {
		$this->site = $site;
		return $this;
	}

	public function setWikitext( $wikitext ) {
		$this->wikitext = $wikitext;
		return $this;
	}

	public function appendWikitext( $wikitext ) {
		$this->wikitext = $this->wikitext . $wikitext;
		return $this;
	}

	public function prependWikitext( $wikitext ) {
		$this->wikitext = $wikitext . $this->wikitext;
		return $this;
	}

	public function pregMatch( $pattern, & $matches = [], $flags = 0, $offset = 0 ) {
		return preg_match( $pattern, $this->getWikitext(), $matches, $flags, $offset );
	}

	public function pregReplace( $pattern, $replacement, $limit = -1, &$count = 0 ) {
		$this->setWikitext( preg_replace( $pattern, $replacement, $this->getWikitext(), $limit, $count ) );
		return $this;
	}

	public function hasCategory( $category_name ) {
		$category_name_regex = Title::factory( $category_name )->getRegexFirstCaseInsensitive();
		$category_name_regex = \regex\Generic::spaceBurger( $category_name_regex );

		$category_ns_titleparts = $this->getSite()->getNamespace( 14 )->getAllTitlePartsCapitalized();
		$category_ns_regexes = [];
		foreach( $category_ns_titleparts as $category_ns_titlepart ) {
			$category_ns_regexes[] = $category_ns_titlepart->getRegexFirstCaseInsensitive();
		}

		$whatever_sortkey = '(\|.*?)?';
		foreach( $category_ns_regexes as $category_ns_regex ) {
			$category_ns_regex = \regex\Generic::spaceBurger( $category_ns_regex );
			$pattern = sprintf(
				'/\[\[%s\]\]/',
				\regex\Generic::spaceBurger( $category_ns_regex . ':' . $category_name_regex . $whatever_sortkey )
			);
			if( 1 === $this->pregMatch( $pattern ) ) {
				return true;
			}
		}

		return false;
	}

	public function addCategory( $category_name, $sortkey = null ) {
		if( $this->hasCategory( $category_name ) ) {
			return false;
		}
		$category_namespace = $this->getSite()->getNamespace( 14 )->getName();
		$this->appendWikitext( sprintf(
			"\n[[%s:%s%s]]",
			$category_namespace,
			$category_name,
			$sortkey ? "|$sortkey" : ''
		) );
		return true;
	}
}
