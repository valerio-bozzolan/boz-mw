#!/usr/bin/php
<?php
# Leaflet Wikipedians map
# Copyright (C) 2017 Valerio Bozzolan
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

error_reporting(E_ALL);
ini_set('display_errors', 1);

isset( $argv ) or die("Not CLI\n");

require 'load.php';

// --
$lang = 'it';
$wiki = 'wikipedia.org';
$api  = "https://$lang.$wiki/w/api.php";

$cat  = isset( $argv[1] ) ? $argv[1] : "Categoria:Utenti per provenienza territoriale";
// --

function logit($type, $msg) {
	printf("[%s] \t %s\n", $type, $msg);
}
define('INFO',  'I');
define('WARN',  'W');
define('ERROR', 'E');

define('CATEGORY_PAGES',     PRIVATE_DATA . __ . 'category=>pages');
define('CATEGORY_CHILDREN',  PRIVATE_DATA . __ . 'category=>children');
define('CATEGORY_WDATA',     PRIVATE_DATA . __ . 'category=>wdata');
define('CATEGORY_GEOQ',      PRIVATE_DATA . __ . 'category=>geoq');
define('CATEGORY_LATLNG',    PRIVATE_DATA . __ . 'category=>latlng');
define('CATEGORY_OSM',       PRIVATE_DATA . __ . 'category=>osm');
define('CATEGORY_GEOJSON',   PRIVATE_DATA . __ . 'category=>geojson');
define('CATEGORY_PARENT',    PRIVATE_DATA . __ . 'category=>parent');
define('USER_LASTCONTRIB',   PRIVATE_DATA . __ . 'user=>lastcontrib');

@ mkdir(CATEGORY_PAGES);
@ mkdir(CATEGORY_CHILDREN);
@ mkdir(CATEGORY_WDATA);
@ mkdir(CATEGORY_GEOQ);
@ mkdir(CATEGORY_LATLNG);
@ mkdir(CATEGORY_OSM);
@ mkdir(CATEGORY_PARENT);
@ mkdir(USER_LASTCONTRIB);

function fetch_user_contrib($api, $user) {
	$timestamp = @ file_get_contents(USER_LASTCONTRIB . __ . $user);
	if( ! $timestamp ) {
		$userdata = APIRequest::factory($api, [
			'action'  => 'query',
			'list'    => 'usercontribs',
			'uclimit' => 1,
			'ucuser'  => $user
		] )->fetch();
		$timestamp = @$userdata->query->usercontribs[0]->timestamp;
		if( ! isset($timestamp) ) {
			logit(ERROR, "No timestamp in usercontribs $user");
			$timestamp = '1991-01-01T00:00:00Z';
		}
		logit(INFO, "Lastcontrib \t $user \t $timestamp");
		file_put_contents(USER_LASTCONTRIB . __ . $user, $timestamp);
	}
	return $timestamp;
}

function fetch_cat($api, $cat, & $oldcats = [] ) {
	// https://it.wikipedia.org/w/api.php?action=query&list=categorymembers&cmtitle=Categoria:Utenti_dall%27Italia&prop=pageprops&titles=Categoria:Utenti_dall%27Italia
	$apiRequest = new APIRequest($api, [
		// Retrieve categories
		'action'  => 'query',
		'list'    => 'categorymembers',
		'cmtitle' =>  $cat,

		// Retrieve Wikidata infos
		'prop'    => 'pageprops',
		'titles'  => $cat
	] );

	$users = [];
	$children = [];

	while( $apiRequest->hasNext() ) {
		$next = $apiRequest->getNext();

		// It's accessed only during the first cycle
		if( isset( $next->query->pages ) ) {
			// Called once
			foreach($next->query->pages as $pageid => $page) {
				if( ! isset( $page->pageprops->wikibase_item ) ) {
					logit(ERROR, "Missing Wikidata Q for $cat");
					continue;
				}

				$ok  = file_exists(CATEGORY_OSM    . __ . $cat);
				$ok &= file_exists(CATEGORY_LATLNG . __ . $cat);
				if($ok) {
					$osmid = file_get_contents(CATEGORY_OSM . __ . $cat);
					$ok &= file_exists(PUBLIC_DATA . __ . sprintf("geojson.%d.js", $osmid) );
				}
				if($ok) {
					logit(INFO, "Skipping full category \t $cat");
					continue;
				}

				$wikidata_el = $page->pageprops->wikibase_item;

				logit(INFO, "WikidataQ \t $wikidata_el");

				file_put_contents(CATEGORY_WDATA . __ . $cat, $wikidata_el);

				// https://www.wikidata.org/w/api.php?action=wbgetclaims&entity=Q8901055&format=jsonfm
				$geoq = APIRequest::factory('https://www.wikidata.org/w/api.php', [
					'action' => 'wbgetclaims',
					'entity' => $wikidata_el
				] )->fetchFirstClaimValue('P2633'); // geography

				// Get geography
				if( $geoq ) {
					$geoq = $geoq->id;

					logit(INFO, "GEOQ  \t $geoq");

					$wikidata_result = APIRequest::factory('https://www.wikidata.org/w/api.php', [
						'action' => 'wbgetclaims',
						'entity' => $geoq
					] );

					$latlng = $wikidata_result->fetchFirstClaimValue('P625');
					if( $latlng ) {
						$lat = $latlng->latitude;
						$lng = $latlng->longitude;
						logit(INFO, "latlng \t $lat;$lng");
						file_put_contents(CATEGORY_LATLNG . __ . $cat, "$lat;$lng");
					} else {
						logit(ERROR, "Missing P625 (latlong) from $geoq");
					}

					$osmid = $wikidata_result->fetchFirstClaimValue('P402'); // OSM
					if( $osmid ) {
						logit(INFO, "OSMID \t $osmid");

						file_put_contents(CATEGORY_OSM . __ . $cat, $osmid);

						$geojson_path = PUBLIC_DATA . __ . sprintf("geojson.%d.js", $osmid);
						if( file_exists($geojson_path) ) {
							logit(INFO, "Skipped GeoJSON");
						} else {
							logit(INFO, "Fetching GeoJSON");
							$geojson = file_get_contents( sprintf('http://polygons.openstreetmap.fr/get_geojson.py?id=%d', $osmid ) );
							if($geojson && $geojson !== 'None') {
								logit(INFO, "Fetched GeoJSON");
								file_put_contents($geojson_path, $geojson);
							} else {
								logit(WARNING, "Missing GeoJSON");
							}
						}
					} else {
						logit(ERROR, "Missing P402 OSMID from $geoq");
					}
				} else {
					logit(ERROR, "Missing P2633 (geography) from $wikidata_el");
				}
			}
		}
		// End query->pages

		// Append purpose
		$handle_pages = fopen(CATEGORY_PAGES  . __ . $cat, 'a');
		$handle_cats  = fopen(CATEGORY_CHILDREN . __ . $cat, 'a');
		if($handle_pages === false) {
			die( sprintf("Can't write in %s\n", $file) );
		}

		foreach( $next->query->categorymembers as $categorymember ) {
			$title = $categorymember->title;
			switch( $categorymember->ns )  {
				case 14:
					// È una categoria
					logit(INFO, "$cat \t <$title");
					fwrite($handle_cats, "\n$title");
					if( ! in_array($title, $oldcats, true) ) {
						$children[] = $title;
					}
					break;
				case 2: // Utente
				case 3: // Discussioni utente
					// Clean user
					$user_prefixes = [
						'Discussioni utente:',
						'Utente:'
					];
					$title = str_replace($user_prefixes, '', $title);
					$slash = strpos($title, '/');
					if( $slash !== false ) {
						$title = substr($title, 0, $slash);
					}
					logit(INFO, "$cat \t +$title");
					fwrite($handle_pages, "\n$title");
					$users[] = $title;
					break;
				default:
					logit(INFO, "ignored \t $title");
					break;
			}
		}
		fclose($handle_pages);
		fclose($handle_cats);
	}

	foreach($children as $child) {
		$oldcats[] = $child;
		file_put_contents(CATEGORY_PARENT . __ . $child, $cat);
	}

	foreach($users as $user) {
		fetch_user_contrib($api, $user);
	}

	foreach($children as $child) {
		fetch_cat($api, $child, $oldcats);
	}
}

function file2array($file) {
	$v = @ file_get_contents($file);
	if( $v === false ) {
		return [];
	}
	$v = trim($v);
	$v = explode("\n", $v);
	array_walk($v, 'trim');
	$v = array_filter($v); // Strip empty elements
	return array_unique($v);
}

function deep_count($cat, & $cats_ready = [], & $cats_seen = [], $level = 0) {
	isset( $cats_seen[ $cat ] )
		and die("Recursion in deep count!?!?\n");

	$cats_seen[ $cat ] = true;

	$n = count( file2array(CATEGORY_PAGES . __ . $cat) );

	$cat_children = file2array(CATEGORY_CHILDREN . __ . $cat);
	foreach($cat_children as $cat_child) {
		$n += count( file2array(CATEGORY_PAGES . __ . $cat_child) );
		$n += deep_count($cat_child, $cats_ready, $cats_seen, $level + 1);
	}

	logit(INFO, "count $cat = \t $n");

	$cats_ready[] = new MapArea($cat, $n, $level);

	return $n;
}

// fetch_cat($api, $cat);

$cats_ready = [];
deep_count($cat, $cats_ready);

$now = new DateTime();

$six_months = 6 * 30;

foreach($cats_ready as $i => $cat_ready) {
	$title = $cat_ready->getTitle();

	$latlng = @ file_get_contents( CATEGORY_LATLNG . __ . $title );
	if( $latlng ) {
		list($lat, $lng) = explode(';', trim( $latlng ) );
		$cat_ready->setLatLng($lat, $lng);
	}

	$osmid = @ file_get_contents( CATEGORY_OSM . __ . $title );
	$osmid and $cat_ready->setOSMID( trim( $osmid ) );

	$parent = @ file_get_contents( CATEGORY_PARENT . __ . $title );
	$parent and $cat_ready->setParent( trim( $parent ) );

	$children = @file_get_contents(CATEGORY_CHILDREN . __ . $title);
	if( empty($children) ) {
		$cat_ready->isLeaf(true);
		$users = file2array(CATEGORY_PAGES . __ . $title );
		foreach($users as $j => $user) {
			$lastcontrib = fetch_user_contrib($api, $user);
			if($lastcontrib) {
				$lastcontrib = DateTime::createFromFormat(DateTime::ISO8601, $lastcontrib);
				$days = $lastcontrib->diff($now)->days;
				if($days > $six_months) {
					unset($users[$j]);
				}
			}
		}
		$cat_ready->setUsers( array_values($users) );
	}
}

file_put_contents(PUBLIC_DATA . __ . 'data.min.js', json_encode( $cats_ready ) );
file_put_contents(PUBLIC_DATA . __ . 'data.js',     json_encode( $cats_ready, JSON_PRETTY_PRINT ) );
