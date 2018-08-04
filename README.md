# Boz-MW

This is another MediaWiki API handler in PHP.

## Showcase

Here some usage examples:

	<?php
	// autoload classes
	require 'autoload.php';

	// enable verbose messages
	\cli\Log::$DEBUG = true;

	echo "Simple Italian Wikipedia API query:\n";
	$WIT = \wm\WikipediaIt::getInstance();
	$response = $WIT->fetch( [
		'action' => 'query',
		'prop'   => 'info',
		'titles' => [
			'Pagina principale'
		]
	] );
	print_r( $response );

	echo "Simple Italian Wikipedia API query with continuation support:\n";
	$members = \wm\WikipediaIt::getInstance()->createQuery( [
		'action' => 'query',
		'list'   => 'categorymembers',
		'cmtitle' => 'Categoria:Software con licenza GNU GPL',
	] );
	foreach( $members->getGenerator() as $response ) {
		print_r( $response );
	}

	echo "Simple POST request (every POST do an implicit login):\n";
	\mw\API::$DEFAULT_USERNAME = 'My username';
	\mw\API::$DEFAULT_PASSWORD = 'My bot password';
	$wit = \wm\WikipediaIt::getInstance();
	$response = $WIT->post( [
		'action'  => 'edit',
		'title'   => 'Special:Nothing',
		'text'    => 'My wikitext',
		'summary' => 'My edit summary',
		'token'   => $wit->getToken( 'csrf' ),
    ] );
	print_r( $response );

## Known usage
* [ItalianWikipediaDeletionBot](https://github.com/valerio-bozzolan/ItalianWikipediaDeletionBot)
* [wiki-users-leaflet](https://github.com/valerio-bozzolan/wiki-users-leaflet/) hosted at [WMF Labs](https://tools.wmflabs.org/it-wiki-users-leaflet/)
