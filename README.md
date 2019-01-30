# boz-mw

This is another MediaWiki API handler in PHP.

## Tools

See [the tools](./tools/README.md).

## API showcase

Here some usage examples.

### Basic API query

```php
<?php
require 'boz-mw/autoload.php';

// load it.wiki
$wiki = \wm\WikipediaIt::instance();

$response =
	$wiki->fetch( [
		'action' => 'query',
		'prop'   => 'info',
		'titles' => [
			'Pagina principale'
		]
	] );

print_r( $response );
```

### API query with continuation


```php
$wiki = \wm\WikipediaIt::instance();

$queries =
	$wiki->createQuery( [
		'action' => 'query',
		'list'   => 'categorymembers',
		'cmtitle' => 'Categoria:Software con licenza GNU GPL',
	] );

foreach( $queries as $query ) {
	print_r( $query );
}
```

### Edit API query

```php
$wiki = \wm\WikipediaIt::instance();

$user     = '';
$password = '';
$wiki->login( $user, $password );

$wiki->edit( [
	'title'   => 'Special:Nothing',
	'text'    => 'My wikitext',
	'summary' => 'My edit summary',
] );
```

## Known usage
* [MediaWikiOrphanizerBot](https://github.com/valerio-bozzolan/MediaWikiOrphanizerBot)
* [ItalianWikipediaDeletionBot](https://github.com/valerio-bozzolan/ItalianWikipediaDeletionBot)
* [wiki-users-leaflet](https://github.com/valerio-bozzolan/wiki-users-leaflet/) hosted at [WMF Labs](https://tools.wmflabs.org/it-wiki-users-leaflet/)
