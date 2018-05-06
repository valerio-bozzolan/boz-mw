# Boz-MW

This is another MediaWiki API handler in PHP.

## Showcase

Here some usage examples:

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
	$categories = \wm\WikipediaIt::getInstance()->createQuery( [
		'action' => 'query',
		'prop'   => 'categories',
		'titles' => 'Gallus gallus domesticus'
	] );
	while( $categories->hasNext() ) {
		$response = $categories->fetchNext();
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
* [wiki-users-leaflet](https://github.com/valerio-bozzolan/wiki-users-leaflet/) hosted at [WMF Labs](https://tools.wmflabs.org/it-wiki-users-leaflet/)
