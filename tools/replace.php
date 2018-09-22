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

// exit if not CLI
$argv or exit( 1 );

// load boz-mw
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload.php';

// load configuration
include 'config.php';

use \cli\Log;
use \cli\Input;
use \cli\Opts;
use \cli\Param;
use \web\MediaWikis;

// whitelisted API parameter => description
$API_PARAMS = [
	'titles'       => 'A list of titles to work on.',
	'pageids'      => 'A list of page IDs to work on.',
	'generator'    => 'Choose between: linkshere, categorymembers, transcludedin, etc.',

	// linkshere generator
	'glhnamespace' => null,

	// transcludedin generator
	'gtinamespace' => null,

	// categorymembers generator
	'gcmtitle'     => null,
	'gcmpageid'    => null,
	'gcmnamespace' => null,
];

// all the available wiki UIDs
$mediawiki_uids = [];
foreach( MediaWikis::all() as $site ) {
	$mediawiki_uids[] = $site::UID;
}
$mediawiki_uids = implode( ', ', $mediawiki_uids );

// register all CLI parameters
$opts = new Opts( [
	new Param( 'wiki',          null, Param::REQUIRED_VALUE, "Specify the UID of the wiki you want to edit from: $mediawiki_uids" ),
	new Param( 'plain',         null, Param::NO_VALUE,       'Use plain text instead of regexes (default)' ),
	new Param( 'regex',         null, Param::NO_VALUE,       'Use regexes instead of plain text' ),
	new Param( 'summary',       'm',  Param::REQUIRED_VALUE, 'Specify an edit summary' ),
	new Param( 'simulate',      null, Param::NO_VALUE,       'Show changes without saving' ),
	new Param( 'always',        null, Param::NO_VALUE,       'Always run without asking y/n' ),
	new Param( 'limit',         null, Param::REQUIRED_VALUE, 'Maximum number of replacements for each SEARCH' ),
	new Param( 'not-minor',     null, Param::NO_VALUE,       'Do not mark this change as minor' ),
	new Param( 'not-bot',       null, Param::NO_VALUE,       'Do not mark this change as edited by a bot' ),
	new Param( 'first-section', null, Param::NO_VALUE,       'Edit only the first section' ),
	new Param( 'show',          null, Param::NO_VALUE,       'Show the wikitext before saving' ),
	new Param( 'help',          'h',  Param::NO_VALUE,       'Show this help and quit' ),
] );

// register also all CLI API parameters
foreach( $API_PARAMS as $param => $description ) {
	$opts->add( new Param( $param, null, Param::REQUIRED_VALUE, $description ) );
};

// unnamed arguments
$main_args = Opts::unnamedArguments();

// operate using regex or in plain text?
$IS_REGEX = $opts->getArg( 'regex', ! $opts->getArg( 'plain', true ) );

// maximum number of replacements for each LIMIT
$LIMIT = $opts->getArg( 'limit' );
if( $LIMIT ) {
	$LIMIT = (int) $LIMIT;
}

// show the help?
$help = $opts->getArg( 'help' );

// the wiki is mandatory
if( ! $help && ! $opts->getArg( 'wiki' ) ) {
	echo "Please specify a wiki\n";
	$help = true;
}

// the generator is mandatory
if( ! $help && ! $opts->getArg( 'generator' ) ) {
	echo "Please specify a generator\n";
	$help = true;
}

// the search and replace are mandatory
if( ! $help && ! $main_args ) {
	echo "Please specify a SEARCH and a REPLACE\n";
	$help = true;
}

// every search must have its replace
if( ! $help && count( $main_args ) % 2 !== 0 ) {
	$last = $main_args[ count( $main_args ) - 1 ];
	echo "Please specify a REPLACE for your latest SEARCH ($last)\n";
	$help = true;
}

// plain vs regex
if( ! $help && null === $IS_REGEX ) {
	echo "Please specify if it's --plain or it's --regex\n";
	$help = true;
}

// plain with regex
if( ! $help && $opts->getArg( 'regex' ) && $opts->getArg( 'plain' ) ) {
	echo "Please do not specify both --regex or --plain\n";
	$help = true;
}

// show the help
if( $help ) {
	echo "Usage: {$argv[ 0 ]} --generator=GENERATOR [OPTIONS] SEARCH... REPLACE...\n";
	echo "You can use this script to search and replace things\n";
	echo "Allowed OPTIONS:\n";
	foreach( $opts->getParams() as $param ) {
		$commands = [];
		if( $param->hasLongName() ) {
			$commands[] = '--' . $param->getLongName();
		}
		if( $param->hasShortName() ) {
			$commands[] = '-' . $param->getShortName();
		}
		echo "\t" . implode( '|', $commands );
		if( $commands && ! $param->isFlag() ) {
			echo $param->isValueOptional()
				? '=[VALUE]'
				: '=VALUE';
		}
		if( $param->hasDescription() ) {
			echo " \t " . $param->getDescription();
		}
		echo "\n";
	}
	exit( $opts->getArg( 'help' ) ? 0 : 1 );
}

// edit only the first section?
$ONLY_FIRST_SECTION = $opts->getArg( 'first-section' );

// API arguments
$args = [
	'action'    => 'query',
	'prop'      => 'revisions',
	'rvslots'   => 'main',
	'rvprop'    => [
		'content',
		'timestamp',
	]
];

// restrict to section
if( $ONLY_FIRST_SECTION ) {
	$args[ 'rvsection' ] = 0;
}

// apply API arguments from CLI API parameters
foreach( $API_PARAMS as $param => $description ) {
	$arg = $opts->getArg( $param );
	if( $arg ) {
		$args[ $param ] = $arg;
	}
}

// find the desired wiki
$wiki = MediaWikis::findFromUID( $opts->getArg( 'wiki' ) );

$wiki->login();

// do the API query with continuation support
$results = $wiki->createQuery( $args );
foreach( $results->getGenerator() as $response ) {

	if( ! isset( $response->query ) ) {
		Log::error( 'empty API output (have you specified nothing?) try --help' );
		exit( 1 );
	}

	foreach( $response->query->pages as $page ) {

		// show the page title
		Log::info( "Page [[{$page->title}]]" );

		// populate the slot with backward compatibility to MediaWiki <= 1.32
		$main_slot = $page->revisions[ 0 ];
		if( isset( $main_slot->slots->main ) ) {
			$main_slot = $main_slot->slots->main;
		}

		// Wikitext object
		$wikitext = $wiki->createWikitext( $main_slot->{ '*' } );

		// apply search and replacements
		$n = count( $main_args );
		for( $i = 0; $i < $n; $i += 2 ) {
			$search  = $main_args[ $i     ];
			$replace = $main_args[ $i + 1 ];
			if( $IS_REGEX ) {
				$wikitext->pregReplace( $search, $replace, $LIMIT );
			} else {
				$wikitext->strReplace(  $search, $replace, $LIMIT );
			}
		}

		// eventually show the wikitext
		if( $opts->getArg( 'show' ) ) {
			Log::info( "Wikitext:\n{$wikitext->getWikitext()}" );
		}

		// are there changes?
		if( $wikitext->getSobstitutions() ) {

			// show changes
			Log::info( 'Changes:' );
			foreach( $wikitext->getHumanUniqueSobstitutions() as $message ) {
				Log::info( "\t" . $message );
			}

			// edit summary
			$summary = $opts->getArg( 'summary', 'Bot: changed ' . implode( ', ', $wikitext->getHumanUniqueSobstitutions() ) );
			Log::info( "Summary: \t $summary" );

			if( ! $opts->getArg( 'simulate' ) ) {

				$proceed = true;
				if( ! $opts->getArg( 'always' ) ) {
					$proceed = 'y' === Input::yesNoQuestion( "Save page [[{$page->title}]]?" );
				}

				if( $proceed ) {

					// save
					$wiki->post( [
						'action'        => 'edit',
						'pageid'        => $page->pageid,
						'basetimestamp' => $page->revisions[ 0 ]->timestamp,
						'text'          => $wikitext->getWikitext(),
						'section'       => $ONLY_FIRST_SECTION ? 0 : null,
						'token'         => $wiki->getToken( \mw\Tokens::CSRF ),
						'summary'       => $summary,
						'minor'         => ! $opts->getArg( 'not-minor' ),
						'bot'           => ! $opts->getArg( 'not-bot'   ),
					] );

					Log::info( 'saved' );
				} else {
					Log::info( 'not saved' );
				}
			}
		} else {
			Log::info( 'no changes' );
		}
	}
}
