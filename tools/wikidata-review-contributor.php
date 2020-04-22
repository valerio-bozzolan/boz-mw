#!/usr/bin/php
<?php
# Copyright (C) 2020 Valerio Bozzolan, Ferdinando Traversa
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

// load the framework
require __DIR__ . '/../autoload.php';

// used classes from boz-mw
use \cli\Log;
use \cli\Input;
use \cli\Opts;
use \wm\Wikidata;
use \mw\API\PageMatcher;
use \cli\ConfigWizard;
use \cli\ParamFlag;

// load configuration or create it
ConfigWizard::requireOrCreate( __DIR__ . '/config.php' );

// register all CLI parameters
$opts = new Opts( [
	new ParamFlag( 'help', 'h', "Show this help and quit" ),
] );

// check if the software is interactive or not
$INTERACTIVE = true;

// command line arguments
$arguments = Opts::unnamedArguments();
if( count( $arguments ) < 2 ) {
	help( [ "Please specify both the SPARQL file and the user name to blame" ] );
}

// take the SPARQL file pathname
$sparql_file = array_shift( $arguments );

// user names to blame
$user_names = $arguments;

// no file no party
if( !file_exists( $sparql_file ) ) {
	help( [ sprintf(
		"The file '%s' does not exist",
		$sparql_file
	) ] );
}

// read the SPARQL query
$sparql_query = file_get_contents( $sparql_file );
if( !$sparql_query ) {
	help( [ sprintf(
		"Obtained a non-empty SPARQL query from the file '%s'",
		$sparql_file
	) ] );
}

// show the help
$show_help = $opts->getArg( 'help' );
if( $show_help ) {
	help();
}

// activate Wikidata power and login
$wikidata = Wikidata::instance();

// login to download more infos
$wikidata->login();

// an array of item IDs
$ids = [];

// some stats
$founds          = 0;
$allcontribs     = 0;
$lastcontribdate = '';

// exec the query and store the item IDs
$rows = $wikidata->querySPARQL( $sparql_query );
foreach( $rows as $row ) {
	$item = $row->item ?? null;

	// no item no party
	if( !$item ) {
		Log::error( "the query does not return any 'item' column - probably you forgot to SELECT the '?item'" );
		exit( 2 );
	}

	$ids[] = basename( $item->value );
}

Log::info( "Starting looking for contributions" );

// and now the magic begins - looks for usercontribs
// https://www.wikidata.org/w/api.php?action=help&modules=query%2Busercontribs
$requests = $wikidata->createQuery( [
	'action'  => 'query',
	'list'    => 'usercontribs',
	'ucuser'  => $user_names,
	'ucprop'  => ['ids', 'title', 'timestamp'],
	'ucdir'   => 'newer',
	'uclimit' => 'max',
] );
foreach( $requests as $request ) {

	// loop the contributions
	$contribs = $request->query->usercontribs;
	foreach( $contribs as $contrib ) {

		$timestamp = $contrib->timestamp;
		$revid     = $contrib->revid;

		// check if this is a known page
		if( in_array( $contrib->title, $ids, true ) ) {
			Log::info( "$timestamp - https://www.wikidata.org/w/index.php?diff=$revid" );
			$founds++;

			// allow to pause the script
			if( $INTERACTIVE ) {
				Input::askInput( "(Press ENTER)" );
			}
		}

		$allcontribs++;
		$lastcontribdate = $contrib->timestamp;

	}

	Log::info( "Last date: $lastcontribdate (total $allcontribs)" );
}

// riepilogue
Log::info( "RIEPILOGUE"                                  );
Log::info( "  Read contributions:      $allcontribs"     );
Log::info( "  Last contribution read:  $lastcontribdate" );
Log::info( "  Total founds:            $founds"          );

if( !$founds ) {
	Log::error( "Sorry, no results found!" );
}

/**
 * Print an help message
 *
 * @param array $errors
 */
function help( $errors = [] ) {
	global $argv, $opts;

	echo "Usage:\n {$argv[ 0 ]} FILE.sparql USER_NAME\n";
	echo "Allowed OPTIONS:\n";
	foreach( $opts->getParams() as $param ) {
		$commands = [];
		if( $param->hasLongName() ) {
			$commands[] = '--' . $param->getLongName();
		}
		if( $param->hasShortName() ) {
			$commands[] = '-' . $param->getShortName();
		}
		$command = implode( ' ', $commands );
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
	foreach( $errors as $msg ) {
		echo "\nError: $msg";
	}
	echo "\n";

	// quit the program
	exit( $errors ? 1 : 0 );
}
