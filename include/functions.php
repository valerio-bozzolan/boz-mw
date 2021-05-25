<?php
# boz-mw - Another MediaWiki API framework
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

/**
 * Shortcuts very useful when you are creating a bot
 */

/**
 * Get a single wiki from its UID
 *
 * Some known UIDs:
 *  wikidatawiki - Wikidata
 *  commonswiki  - Wikimedia Commons
 *  metawiki     - Meta-wiki
 *  itwiki       - Wikipedia (it)
 *
 * @param string $uid
 * @return mw\StaticSite
 */
function wiki( $uid ) {
	return \web\MediaWikis::findFromUID( $uid );
}

/**
 * Get Wikidata
 *
 * @return wm\Wikidata
 */
function wikidata() {
	return wiki( 'wikidatawiki' );
}

/**
 * Get Wikimedia Meta-wiki
 *
 * @return wm\MetaWiki
 */
function meta() {
	return wiki( 'metawiki' );
}

/**
 * Get Wikimedia Commons
 *
 * @return wm\Wikidata
 */
function commons() {
	return wiki( 'commonswiki' );
}

/**
 * Get the Italian Wikipedia
 *
 * @return wm\WikipediaIt
 */
function itwiki() {
	return wiki( 'itwiki' );
}

/**
 * Get the Wikimedia CH members wiki
 *
 * @return web\WikimediaCH
 */
function wmch() {
	return wiki( 'wmch' );
}

/**
 * Merge your custom page objects with the API response pages
 *
 * Useful when you try to retrieve informations from many pages
 * with less request as possible.
 *
 * @param $response_query   object
 * @param $my_stuff         array
 * @param $query_containers array|string e.g. 'query'
 * @param $page_containers  string e.g. 'pages' or 'users'
 * @return array
 */
function response_matcher( $response_query, $my_stuff, $query_containers = [], $page_container = null ) {
	return new \mw\API\PageMatcher( $response_query, $my_stuff, $query_containers, $page_container );
}

/**
 * Enable debug mode
 *
 * @param $status status Enable debug or not
 */
function bozmw_debug( $status = true ) {
	\cli\Log::$DEBUG = $status;
}

/**
 * Serialize some data and write into a file
 *
 * @param $file string File path
 * @param $data mixed Your data
 */
function file_put_data( $file, $data ) {
	$data_raw = serialize( $data );
	file_put_contents( $file, $data_raw );
}

/**
 * Unserialize some data from a file
 *
 * @param $file string File path
 * @return mixed Data
 */
function file_get_data( $file ) {
	$contents = file_get_contents( $file );
	return unserialize( $contents );
}

/**
 * Shortcut to get the Opts class
 *
 * It's useful to get and set your command line argument list
 */
function cli_options() {
	return \cli\Opts::instance();
}

