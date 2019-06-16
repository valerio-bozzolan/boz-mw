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

// load boz-mw
require __DIR__ . '/../autoload.php';

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
use \mw\API\PageMatcher;

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
	new ParamValuedLong( 'file',          "Output filename" ),
	new ParamFlag(       'help',    'h',  "Show this help and quit" ),
] );

$help = '';

// choosen wiki
$wiki_uid = $opts->getArg( 'wiki' );
if( !$wiki_uid ) {
	$help .= "Please specify --wiki=WIKI";
}

// page titles
$page_titles = Opts::unnamedArguments();
if( !$page_titles ) {
	$help .= "Please specify some page titles";
}

$limit = $opts->getArg( 'limit', 500 );

// show the help
if( $help ) {
	echo "Usage:\n {$argv[ 0 ]} [OPTIONS] Page_title > filename.xml\n";
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
$wiki->login();

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

?>
<mediawiki xmlns="http://www.mediawiki.org/xml/export-0.10/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.mediawiki.org/xml/export-0.10/ http://www.mediawiki.org/xml/export-0.10.xsd" version="0.10" xml:lang="it">
<?php foreach( $requests as $request ): ?>
	<?php foreach( $request->query->pages as $page ): ?>
		<?php foreach( $page->revisions as $revision ): ?>
			<?php if( empty( $revision->comment ) ) continue ?>
			<?php foreach( $revision->slots as $slot ): ?>
				<?php if( empty( $slot->contentmodel ) ) continue ?>
				<revision>
					<id><?=        $revision->revid ?></id>
					<parentid><?=  $revision->parentid ?></parentid>
					<timestamp><?= $revision->timestamp ?></timestamp>
					<contributor>
						<username><?= htmlentities( $revision->user ) ?></username>
						<id><?=       htmlentities( $revision->userid ) ?></id>
					</contributor>
					<comment><?= htmlentities( $revision->comment ) ?></comment>
					<model><?=   htmlentities( $slot->contentmodel ) ?></model>
					<format><?=  htmlentities( $slot->contentformat ) ?></format>
					<text xml:space="preserve" bytes="<?= $slot->size ?>"><?= htmlentities( $slot->{'*'} ) ?></text>
					<sha1><?= htmlentities( $revision->sha1 ) ?></sha1>
				</revision>
			<?php endforeach ?>
		<?php endforeach ?>
	<?php endforeach ?>
<?php endforeach ?>
</mediawiki>
