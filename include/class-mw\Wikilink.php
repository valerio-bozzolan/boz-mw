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
	 * Legal characters for a title
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:$wgLegalTitleChars
	 */
	const LEGAL_TITLE_CHARS = ' %!\"$&\'()*,\\-.\\/0-9:;=?@A-Z\\\\^_`a-z~\\x80-\\xFF+';

	/**
	 * Legal characters for an alias
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:$wgLegalTitleChars
	 */
	const LEGAL_ALIAS_CHARS = self::LEGAL_TITLE_CHARS . '#<>\[\]{}\n\t';

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
	 * @return string
	 */
	public function getRegexTitle() {
		return $this->title
			? $this->title->getRegex()
			: '[' . self::LEGAL_TITLE_CHARS . ']*';
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

		// match whatever alias otherwise (non-greedy, because of it can contains ']')
		return '[' . self::LEGAL_ALIAS_CHARS . ']*?';
	}

	/**
	 * Get a regex matching this wikilink
	 *
	 * @param $args array Arguments to be specified
	 * 	'title-group-name': If specified, the title will be captured in a group with this name
	 * 	'alias-group-name': If specified, the alias will be captured in a group with this name
	 */
	public function getRegex( $args = [] ) {

		// default options
		$args = array_replace( [
			'title-group-name' => null,
			'alias-group-name' => null,
		], $args );

		// regex matching the title
		$title_regex = \regex\Generic::groupNamed( $this->getRegexTitle(), $args[ 'title-group-name' ] );

		// regex matching the alias (if any)
		$alias_regex = false;
		if( $this->alias !== self::NO_ALIAS ) {
			$alias_regex = \regex\Generic::groupNamed( $this->getRegexAlias(), $args[ 'alias-group-name' ] );

			// the alias part, when it's a captch-all, is optional
			if( $this->alias === self::WHATEVER_ALIAS ) {
				// note, do not try to create an atomic group, because atomic groups do not backreference past
				$alias_regex = "(\|$alias_regex)?";
			}
		}

		// complete regex
		$regex = $title_regex;
		if( $alias_regex ) {
			$regex .= $alias_regex;
		}

		// surround with brackets
		return "\[\[$regex\]\]";
	}

}
