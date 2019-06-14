#!/usr/bin/php
<?php
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

// exit if not CLI
$argv or exit( 1 );

// default seconds to wait in always mode
$DEFAULT_ALWAYS_WAIT = 3;

// load boz-mw
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload.php';

// load configuration
include 'config.php';

use \cli\Log;
use \cli\Input;
use \cli\Opts;
use \cli\Param;
use \cli\ParamFlag;
use \cli\ParamFlagLong;
use \cli\ParamValued;
use \cli\ParamValuedLong;
use \web\MediaWikis;

// all the available wiki UIDs
$mediawiki_uids = [];
foreach( MediaWikis::all() as $site ) {
	$mediawiki_uids[] = $site::UID;
}
$mediawiki_uids = implode( ', ', $mediawiki_uids );

// register all CLI parameters
$opts = new Opts( [
	new ParamValuedLong( 'wiki',          "Available wikis: $mediawiki_uids" ),
	new ParamValuedLong( 'limit',         "Number of revisions for each request" ),
	new ParamFlag(       'help',    'h',  "Show this help and quit" ),
] );

$help = '';

// page titles
$page_titles = Opts::unnamedArguments();
if( !$page_titles ) {
	$help .= "Please specify some page titles";
}


// choosen wiki
$wiki_uid = $opts->getArg( 'wiki' );
if( !$wiki_uid ) {
	$help .= "Please specify --wiki";
}

// show the help
if( $help ) {
	echo "Usage:\n {$argv[ 0 ]} [OPTIONS] Page_title...\n";
	echo "Allowed OPTIONS:\n";
	foreach( $opts->getParams() as $param ) {
		$commands = [];
		if( $param->hasLongName() ) {
			$commands[] = '--' . $param->getLongName();
		}
		if( $param->hasShortName() ) {
			$commands[] = '-' . $param->getShortName();
		}
		$command = implode( '|', $commands );
		if( $command && ! $param->isFlag() ) {
			$command .= $param->isValueOptional()
				? '=[VALUE]'
				: '=VALUE';
		}
		printf( ' % -20s ', $command );
		if( $param->hasDescription() ) {
			echo ' ' . $param->getDescription();
		}
		echo "\n";
	}
	if( is_string( $help ) ) {
		echo "\nError: $help\n";
	}
	exit( $opts->getArg( 'help' ) ? 0 : 1 );
}

$wiki = MediaWikis::findFromUID( $wiki_uid );

$limit = $opts->getArg( 'limit', 500 );

$requests = $wiki->createQuery( [
	'action' => 'query',
	'titles' => $page_titles,
	'prop'   => 'revisions',
	'rvprop' => [
		'ids',
		'flags',
		'timestamp',
		'user',
		'userid',
		'size',
		'slotsize',
		'sha1',
		'comment',
		'content',
		'tags',
	],
	'rvslots' => 'main',
	'rvlimit' => $limit,
] );


foreach( $requests as $request ) {
	print_r( $request );
}
