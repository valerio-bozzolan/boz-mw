#!/usr/bin/env php
<?php
# boz-mw - Another MediaWiki API framework
# Copyright (C) 2019, 2020, 2021, 2022 Valerio Bozzolan
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
require __DIR__ . '/../autoload-with-laser-cannon.php';

// the number '500' gives to much <This result was truncated because it would otherwise be larger than the limit of 12,582,912 bytes>
$DEFAULT_LIMIT = 100;

// load configuration
config_wizard( 'config.php' );

use \web\MediaWikis;
use \cli\Log;

// all the available wiki UIDs
$mediawiki_uids = implode( ', ', MediaWikis::allUIDs() );

// register all CLI parameters
$opts = cli_options()
	->addValued( 'wiki',  null, "Available wikis: $mediawiki_uids"                     )
	->addValued( 'limit', null, "Number of revisions for each request", $DEFAULT_LIMIT )
	->addValued( 'file',  null, "Output filename",                      'export.xml'   )
	->addFlag(   'help',  'h',  "Show this help and quit"                              );

$messages = [];

// choosen wiki
$wiki_uid = $opts->get( 'wiki' );
if( !$wiki_uid ) {
	$messages[] = "Please specify --wiki=WIKI";
}

// page titles
$page_titles = $opts::unnamedArguments();
if( !$page_titles ) {
	$messages[] = "Please specify some page titles";
}

// output filename
$filename = $opts->get( 'file' );
if( !$filename ) {
	$messages[] = "Please specify a filename";
}

$limit = (int) $opts->get( 'limit' );

// show the help
$show_help = $opts->get( 'help' );
if( $show_help ) {
	$messages = [];
} else {
	$show_help = $messages;
}

if( $show_help ) {
	echo "Usage:\n {$argv[ 0 ]} --wiki=WIKI --file=export.xml [OPTIONS] Page_title\n";
	echo "Allowed OPTIONS:\n";

	$opts->printParams();

	foreach( $messages as $msg ) {
		echo "\nError: $msg";
	}
	echo "\n";

	exit( $opts->get( 'help' ) ? 0 : 1 );
}

// try to open the file
$file = fopen( $filename, 'w' );
if( !$file ) {
	Log::error( "Can't open file '$filename'" );
	exit( 1 );
}

// pick the wiki and login
$wiki = wiki( $wiki_uid );

$wiki->login();

// build the MediaWiki API query
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
	],
	'rvslots' => 'main',
	'rvlimit' => $limit,
] );

// total number of revisions
$total = 0;

$n_requests = 1;

// do not print to the out
$out  = '<mediawiki xmlns="http://www.mediawiki.org/xml/export-0.10/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.mediawiki.org/xml/export-0.10/ http://www.mediawiki.org/xml/export-0.10.xsd" version="0.10" xml:lang="it">' . "\n";
foreach( $requests as $request ) {

	// show a kind of progress
	Log::info( sprintf(
		"processing request %d (continuing from $total revisions)",
		$n_requests++
	) );

	$response_warning_shown = false;

	foreach( $request->query->pages as $page ) {

		if( isset( $page->missing ) ) {
			Log::error( "Page '{$page->title}' is missing" );
			exit( 1 );
		}

		$alert_much_revisions = true;
		foreach( $page->revisions as $i => $revision ) {

			// avoid nonsense revisions
			if( empty( $revision->comment ) ) {
				$count = count( $page->revisions );
				if( $count !== $limit && !$response_warning_shown ) {
					Log::warn( "response with $count revisions instead of $limit: consider to lower your limit (ignore if you see this just once)" );
					$response_warning_shown = true;
				}
			}

			$total++;

			foreach( $revision->slots as $slot ) {

				// avoid nonsense slots
				if( empty( $slot->contentmodel ) ) {
					continue;
				}

				$safe_user    = htmlentities( $revision->user      );
				$safe_userid  = htmlentities( $revision->userid    );
				$safe_comment = htmlentities( $revision->comment   );
				$safe_model   = htmlentities( $slot->contentmodel  );
				$safe_format  = htmlentities( $slot->contentformat );
				$safe_text    = htmlentities( $slot->{'*'}         );

				$out .= "<revision>\n";
					$out .= "\t<id>{$revision->revid}</id>\n";
					$out .= "\t<parentid>{$revision->parentid}</parentid>\n";
					$out .= "\t<timestamp>{$revision->timestamp}</timestamp>\n";
					$out .= "\t<contributor>\n";
						$out .= "\t\t<username>$safe_user</username>\n";
						$out .= "\t\t<id>$safe_userid</id>\n";
					$out .= "\t</contributor>\n";
					$out .= "\t<comment>$safe_comment ?></comment>";
					$out .= "\t<model>$safe_model</model>\n";
					$out .= "\t<format>$safe_format</format>\n";
					$out .= "\t<text xml:space=\"preserve\" bytes=\"{$slot->size}\">$safe_text</text>\n";
					$out .= "\t<sha1>{$revision->sha1}</sha1>\n";
				$out .= "</revision>\n";
			}
		}
	}

	// write the file in chunks
	fwrite( $file, $out );
	$out = '';
}

Log::info( sprintf(
	"you mega-exported $total revisions! nice shot! See %s",
	$opts->get( 'file' )
) );

fwrite( $file, "</mediawiki>\n" );
fclose( $file );
