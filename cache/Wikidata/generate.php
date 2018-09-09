#!/usr/bin/php
<?php
# Copyright (C) 2018 Valerio Bozzolan
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

// force CLI
$argv or exit( 1 );

// load boz-mw
require __DIR__ . '/../../autoload.php';

// associative property => label
$properties = [];

// query all the Wikidata properties
$rows = \wm\Wikidata::querySPARQL(
	'SELECT DISTINCT ?item ?itemLabel WHERE {'.
		'?item wdt:P31/wdt:P279* wd:Q18616576. '.
		'FILTER( SUBSTR( STR( ?item ), 32, 1 ) = "P" ). '. // the 'P' is the 32nd character in 'http://www.wikidata.org/entity/P123' (and sometime it's Q...)
		'SERVICE wikibase:label { bd:serviceParam wikibase:language "en". } '.
	'} ORDER BY xsd:integer( REPLACE( STR( ?item ), "http://www.wikidata.org/entity/P", "" ) )' // order by the number after the 'P'
);

// populate the array
foreach( $rows as $row ) {
	$label = $row->itemLabel->value;
	$property = $row->item->value;
	$property = str_replace( 'http://www.wikidata.org/entity/', '', $property );
	$properties[ $property ] = $label;
}

// save properties as a clean JSON
$properties_json = json_encode( $properties, JSON_PRETTY_PRINT );
$properties_json = str_replace( '    ', "\t", $properties_json );
file_put_contents( 'properties.json', $properties_json );
