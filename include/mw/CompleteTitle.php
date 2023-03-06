<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2019, 2020, 2021 Valerio Bozzolan
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
 * A MediaWiki complete title (with namespace)
 */
class CompleteTitle {

	/**
	 * Which MediaWiki site
	 *
	 * @var object
	 */
	private $wiki;

	/**
	 * @var object
	 */
	private $ns;

	/**
	 * @var object
	 */
	private $title;

	/**
	 * Constructor
	 *
	 * @param $wiki  object Dependency injection of the wiki
	 * @param $ns    object Dependency injection of the namespace
	 * @param $title object Dependency injection of the Title object (the part after the namespace)
	 */
	public function __construct( $wiki, Ns $ns, Title $title ) {
		$this->wiki  = $wiki;
		$this->ns    = $ns;
		$this->title = $title;
	}

	/**
	 * Get the namespace object
	 *
	 * @return Ns
	 */
	public function getNs() {
		return $this->ns;
	}

	/**
	 * Get the title object
	 *
	 * @return Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Get the complete title, as displayed in a page, without underscores
	 *
	 * @return string
	 */
	public function getCompleteTitle() {
		$title = $this->getTitle()->get();
		$ns = $this->getNs()->getName();
		if( $ns ) {
			$ns .= ':';
		}
		return $ns . $title;
	}

	/**
	 * Get the complete title, as displayed in a page, but with underscores
	 *
	 * @return string
	 */
	public function getCompleteTitleUnderscored() {
		$title = $this->getCompleteTitle();
		return TitlePart::space2underscore( $title );
	}

	/**
	 * Get the {{SUBPAGENAME}} for this complete page title
	 *
	 * It reproduces the MediaWiki {{SUBPAGENAME}} behaviour, so:
	 *  - return 'asd'        from 'Discussion:The/asd'
	 *  - return 'Discussion' from 'Discussion:Asd'
	 *
	 * See https://www.mediawiki.org/wiki/Help:Magic_words
	 */
	public function getSubPageName() {
		return $this->getTitle()->getSubPageName();
	}

	/**
	 * Get the regex able to match this complete title
	 *
	 * @param $args array
	 * 	ns-group-name:    Name of the capturing group for the namespace
	 * 	title-group-name: Name of the capturing group for the title
	 * @return string
	 */
	public function getRegex( $args = [] ) {

		// default options
		$args = array_replace( [
			'wikilink'         => true,
			'ns-group-name'    => null,
			'title-group-name' => null,
		], $args );

		// namespace regex
		$ns = $this->getNs()->getRegex( [
			'wikilink' => $args[ 'wikilink' ],
		] );

		// title regex
		// see mw\Title#getRegex()
		$title = $this->getTitle()->getRegex();

		// @TODO: handle anchor

		// eventually group
		$ns    = \regex\Generic::groupNamed( $ns,    $args[ 'ns-group-name'    ] );
		$title = \regex\Generic::groupNamed( $title, $args[ 'title-group-name' ] );

		return $ns . '[ _]*' . $title;
	}

	/**
	 * Create a Wikilink to this title
	 *
	 * @param $alias string|false|null (NULL: whatever, false: no one)
	 * @return object
	 */
	public function createWikilink( $alias = null ) {
		return new Wikilink( $this, $alias );
	}

	/**
	 * Get the URL to this page
	 *
	 * @return string
	 */
	public function getURL() {
		$title = $this->getCompleteTitleUnderscored();
		$title = urlencode( $title );
		return $this->wiki->getBaseURL() . $title;
	}

	/**
	 * Static constructor parsing a string
	 *
	 * @param $wiki object
	 * @param $s string e.g. ' Mediawiki: test '
	 * @return self
	 */
	public static function createParsingTitle( $wiki, $s ) {

		// @TODO: parse also anchor

		// split namespace and title
		$ns_raw = '';
		$tokens = explode( ':', $s, 2 );
		if( count( $tokens ) === 2 ) {
			$ns_raw    = $tokens[ 0 ];
			$title_raw = $tokens[ 1 ];
		} else {
			// no namespace? that's the main namespace!
			$ns_raw    = '';
			$title_raw = $tokens[ 0 ];
		}

		// validate namespace
		$ns = $wiki->findNamespace( $ns_raw );
		if( ! $ns ) {
			// that was the main namespace with a ':' in the title
			$ns = $wiki->getNamespace( 0 );
			$title_raw = "$ns_raw:$title_raw";
		}

		$title = new Title( $title_raw, $wiki );

		return new self( $wiki, $ns, $title );
	}

}
