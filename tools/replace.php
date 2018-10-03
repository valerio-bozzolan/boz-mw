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

// these classes are useful only to distinguish this type of params :^)
class APIParam    extends ParamValuedLong {}
class APIParamSub extends APIParam        {}

// register all CLI parameters
$opts = new Opts( [
	new ParamValuedLong( 'wiki',          "Available wikis: $mediawiki_uids" ),
	new APIParam(        'generator',     'Choose between: linkshere, categorymembers, transcludedin' ),
	new APIParamSub(     'glhnamespace',  'only in linkshere:       Namespace number' ),
	new APIParamSub(     'gtinamespace',  'only in transcludedin:   Namespace number' ),
	new APIParamSub(     'gcmtitle',      'only in categorymembers: Category name prefixed' ),
	new APIParamSub(     'gcmpageid',     'only in categorymembers: Category page ID' ),
	new APIParamSub(     'gcmnamespace',  'only in categorymembers: Namespace number' ),
	new APIParam(        'titles',        'Title of pages to work on' ),
	new APIParam(        'pageids',       'Page IDs to work on' ),
	new ParamFlagLong(   'plain',         'Use plain text instead of regexes (default)' ),
	new ParamFlagLong(   'regex',         'Use regexes instead of plain text' ),
	new ParamValued(     'summary', 'm',  'Specify an edit summary' ),
	new ParamValuedLong( 'limit',         'Maximum number of replacements for each SEARCH' ),
	new ParamFlagLong(   'not-minor',     'Do not mark this change as minor' ),
	new ParamFlagLong(   'not-bot',       'Do not mark this change as edited by a bot' ),
	new ParamValueLong(  'rvsection',     'Number of section to be edited' ),
	new ParamFlagLong(   'always',        'Always run without asking y/n' ),
	new ParamFlagLong(   'simulate',      'Show changes without saving' ),
	new ParamFlagLong(   'show',          'Show the wikitext before saving' ),
	new ParamFlag(       'help',    'h',  'Show this help and quit' ),
] );

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
$help = (bool) $opts->getArg( 'help' );

// the wiki is mandatory
if( ! $help && ! $opts->getArg( 'wiki' ) ) {
	$help = "Please specify a --wiki=VALUE";
}

// pick the wiki
$wiki = MediaWikis::findFromUID( $opts->getArg( 'wiki' ) );
if( ! $help && ! $wiki ) {
	$help = "Please specify a valid wiki UID from: $mediawiki_uids";
}

// the generator is mandatory
if( ! $help && ! $opts->getArg( 'generator' ) ) {
	$help = "Please specify the --generator=VALUE";
}

// the search and replace are mandatory
if( ! $help && ! $main_args ) {
	$help = "Please specify a SEARCH and a REPLACE";
}

// every search must have its replace
if( ! $help && count( $main_args ) % 2 !== 0 ) {
	$last = $main_args[ count( $main_args ) - 1 ];
	$help = "Please specify a REPLACE for your latest SEARCH ($last)";
}

// plain vs regex
if( ! $help && null === $IS_REGEX ) {
	$help = "Please specify if it's --plain or it's --regex";
}

// plain with regex
if( ! $help && $opts->getArg( 'regex' ) && $opts->getArg( 'plain' ) ) {
	$help = "Please do not specify both --regex or --plain";
}

// show the help
if( $help ) {
	echo "Usage:\n {$argv[ 0 ]} [OPTIONS] SEARCH... REPLACE...\n";
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
		if( $param instanceof APIParamSub ) {
			echo ' ';
		}
		printf( ' % -20s ', $command );
		if( $param->hasDescription() ) {
			echo ' ' . $param->getDescription();
		}
		echo "\n";
	}
	echo "Example:\n {$argv[ 0 ]} --wiki=itwiki --generator=allpages 'a' 'afa'\n";
	if( is_string( $help ) ) {
		echo "\nError: $help\n";
	}
	exit( $opts->getArg( 'help' ) ? 0 : 1 );
}

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

// apply API arguments from CLI API parameters
foreach( $opts->getParams() as $param ) {
	$arg = $opts->getArg( $param );
	if( $arg ) {
		$args[ $param->getLongName() ] = $arg;
	}
}

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
					$wiki->edit( [
						'pageid'        => $page->pageid,
						'basetimestamp' => $page->revisions[ 0 ]->timestamp,
						'text'          => $wikitext->getWikitext(),
						'section'       => $opt->getArg( 'rvsection' ),
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
