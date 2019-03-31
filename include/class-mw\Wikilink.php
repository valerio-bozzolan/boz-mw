<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2019 Valerio Bozzolan
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
 * A MediaWiki wikilink
 *
 * Something like [[Wikipedia:Contatti|contatti]].
 */
class Wikilink {

	/**
	 * A valid alias value
	 */
	const WHATEVER_ALIAS = null;

	/**
	 * A valid alias value
	 */
	const NO_ALIAS = false;

	/**
	 * MediaWiki title
	 *
	 * When falsy, it means that can be whatever
	 *
	 * @var object|null
	 */
	private $title;

	/**
	 * Displayed alias
	 *
	 * When NULL, it means that can be whatever,
	 * When false, it means that there is no one.
	 *
	 * @var object
	 */
	private $alias;

	/**
	 * Constructor
	 *
	 * @param $title object
	 * @param $alias string|false|null (NULL: whatever, false: no one)
	 */
	public function __construct( CompleteTitle $title, $alias = null ) {
		$this->setTitle( $title )
		     ->setAlias( $alias );
	}

	/**
	 * Set the title (link)
	 *
	 * @param $title object
	 * @return self
	 */
	public function setTitle( CompleteTitle $title ) {
		$this->title = $title;
		return $this;
	}

	/**
	 * Set the alias
	 *
	 * @param $alias string|null|false (NULL: whatever, false: no one
	 * @return self
	 */
	public function setAlias( $alias ) {
		$this->alias = $alias;
		return $this;
	}

	/**
	 * Get a regex matching this wikilink's title
	 *
	 * If there is no title, match a generic one.
	 *
	 * @param $args array Arguments
	 * 	'wikilink' boolean If false, a category will categorize (default true)
	 * @return string
	 */
	public function getRegexTitle( $args = [] ) {
		return $this->title
			? $this->title->getRegex( $args )
			: '[' . self::legalTitleCharset() . ']*';
	}

	/**
	 * Get a regex matching this wikilink's anchor
	 *
	 * @param $args array Arguments
	 * 	anchor-group-name (string) group name for the anchor
	 * @return string
	 */
	public function getRegexAnchor( $args = [] ) {
		//if( $this->title ) {
			// @TODO: allow CompleteTitle objects to have a specific anchor
			// return $this->title->getRegexAnchor( $args );
		//}

		// allowed characters in the anchor
		$regex = self::legalTitleCharset();

		// may be empty
		$regex = "[$regex]*";

		// create a group for just the text after the '#' (that may be empty)
		$regex = \regex\Generic::groupNamed( $regex, $args[ 'anchor-group-name' ] );

		// the anchor may be not specified
		return "( *#$regex)?";
	}

	/**
	 * Get a regex matching this wikilink's alias
	 *
	 * If there is no alias, match a generic one.
	 *
	 * @return string
	 */
	public function getRegexAlias() {
		// match the alias exactly if present
		if( $this->alias ) {
			return preg_quote( $this->alias );
		}

		// match whatever alias otherwise (non-greedy)
		return '.*?';
	}

	/**
	 * Get the wikitext that will point to this wikilink
	 *
	 * @param $args array
	 * @return string
	 */
	public function getWikitext( $args = [] ) {

		// default arguments
		$args = array_replace( [
			'wikilink' => true,
		], $args );

		$completetitle = $this->title;
		$ns = $completetitle->getNs();
		$ns_name = $ns->getName();
		$title = $completetitle->getTitle()->get();

		// categories must me prefixed with ':' if you want a wikilink
		$prefix = '';
		if( $ns->getID() === 14 && $args[ 'wikilink' ] ) {
			$prefix = ':';
		}

		// the alias is the piped text
		$alias = '';
		if( strlen( $this->alias ) > 1 ) {
			$alias = "|$this->alias";
		}

		// @TODO: get the anchor from CompleteTitle

		return "[[$prefix$ns_name:$title$alias]]";
	}

	/**
	 * Get a regex matching this wikilink
	 *
	 * @param $args array Arguments to be specified
	 * 	'title-group-name'  string If specified, the title will be captured in a group with this name
	 * 	'alias-group-name'  string If specified, the alias will be captured in a group with this name
	 *  'anchor-group-name' string If specified, the anchor will be captured in a group with this name
	 *    'wikilink':        bool   If false, a category will categorize (default true)
	 */
	public function getRegex( $args = [] ) {

		// default options
		$args = array_replace( [
			'wikilink'          => true,
			'title-group-name'  => null,
			'alias-group-name'  => null,
			'anchor-group-name' => null,
		], $args );

		// regex matching the title
		$title_regex = $this->getRegexTitle( [
			'wikilink' => $args[ 'wikilink' ],
		] );
		$title_regex = \regex\Generic::groupNamed( $title_regex, $args[ 'title-group-name' ] );

		// regex matching the anchor
		$anchor_regex = $this->getRegexAnchor( $args );

		// regex matching the alias (if any)
		$alias_regex = false;
		if( $this->alias !== self::NO_ALIAS ) {
			$alias_regex = \regex\Generic::groupNamed( $this->getRegexAlias(), $args[ 'alias-group-name' ] );

			// the alias part, when it's a captch-all, is optional
			if( $this->alias === self::WHATEVER_ALIAS ) {
				// note, do not try to create an atomic group, because atomic groups do not backreference past
				$alias_regex = "([ _]*\|$alias_regex)?";
			}
		}

		// complete regex
		$regex  = $title_regex;
		$regex .= $anchor_regex;
		if( $alias_regex ) {
			$regex .= $alias_regex;
		}

		// surround with spaces
		$regex = \regex\Generic::spaceBurger( $regex );

		// surround with brackets
		return "\[\[$regex\]\]";
	}

	/**
	 * Legal characters for a title
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:$wgLegalTitleChars
	 */
	public static function legalTitleCharset() {
		return ' %!\"$&\'()*,\\-.\\/0-9:;=?@A-Z\\\\^_`a-z~\\x80-\\xFF+';
	}

	/**
	 * Legal characters for an alias
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:$wgLegalTitleChars
	 */
	public static function legalAliasCharset() {
		return self::legalTitleCharset() . '#<>\[\]{}\n\t';
	}

}
