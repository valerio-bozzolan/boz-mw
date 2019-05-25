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

### Upload API query

Uploading a file requires to respect the [RFC1341](https://tools.ietf.org/html/rfc1341) about an HTTP multipart request.

Well, we made it easy:

```php
$photo_url = 'http://.../libre-image.jpg';
$wiki->upload( [
	'comment'  => 'upload file about...',
	'text'     => 'bla bla [[bla]]',
	'filename' => 'Libre image.jpg',
	\network\ContentDisposition::createFromNameURLType( 'file', $photo_url, 'image/jpg' ),
] );
```

See the [`ContentDisposition`](include/class-network\ContentDisposition.php) class for some other constructors.

## Known usage
* [MediaWikiOrphanizerBot](https://github.com/valerio-bozzolan/MediaWikiOrphanizerBot)
* [ItalianWikipediaDeletionBot](https://github.com/valerio-bozzolan/ItalianWikipediaDeletionBot)
* [wiki-users-leaflet](https://github.com/valerio-bozzolan/wiki-users-leaflet/) hosted at [WMF Labs](https://tools.wmflabs.org/it-wiki-users-leaflet/)
