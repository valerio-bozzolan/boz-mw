# boz-mw

This is `boz-mw`,  __another MediaWiki API handler in PHP__. It has a tons of features that will make your head spin!

This is a library to interact with MediaWiki and Wikibase APIs. There are also some command line scripts.

## Download

	git clone https://github.com/valerio-bozzolan/boz-mw

## Command line tools

See [the tools](./tools/README.md).

### Command line script `replace.php`

The [replace.php](https://github.com/valerio-bozzolan/boz-mw/tree/master/tools#replace-script---replacephp) allows you to do some sobstitutions in a wiki.

### Command line script `mega-export.php`

The [mega-export.php](https://github.com/valerio-bozzolan/boz-mw/tree/master/tools#mega-export---mega-exportphp) allows you to export the _full_ page history of whatever page.

## API framework showcase

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
		'action'  => 'query',
		'list'    => 'categorymembers',
		'cmtitle' => 'Categoria:Software con licenza GNU GPL',
	] );

foreach( $queries as $query ) {
	print_r( $query );
}
```

### Login and Edit API query

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

Note that you can also call `login()` without parameters if you specify a global username and password on the top of your script:

```php
\mw\API::$DEFAULT_USERNAME = '':
\mw\API::$DEFAULT_PASSWORD = '';
```

### Wikidata SPARQL query

What if you want to list all the [cats from Wikidata](https://query.wikidata.org/#%23Cats%0ASELECT%20%3Fitem%20%3FitemLabel%20%0AWHERE%20%0A%7B%0A%20%20%3Fitem%20wdt%3AP31%20wd%3AQ146.%0A%20%20SERVICE%20wikibase%3Alabel%20%7B%20bd%3AserviceParam%20wikibase%3Alanguage%20%22%5BAUTO_LANGUAGE%5D%2Cen%22.%20%7D%0A%7D)?

```php
// you should know how to build a SPARQL query
$query  = 'SELECT ?item ?itemLabel WHERE {';
$query .= ' ?item wdt:P31 wd:Q146 ';
$query .= ' SERVICE wikibase:label { bd:serviceParam wikibase:language "en". } ';
$query .= '}';

// query Wikidata and decode the response
$rows = \wm\Wikidata::querySPARQL( $query );

// for each cat
foreach( $rows as $row ) {

	// example: 'http://www.wikidata.org/entity/Q5317221'
	$url = $row->item->value;

	// example: 'Q5317221'
	$id = basename( $url );

	// example: 'Dusty the Klepto Kitty'
	$itemLabel = $row->itemLabel->value;

	echo "Found cat ID: $id. Name: $itemLabel \n";
}
```

### Wikidata edit API

```php
$data = new \wb\DataModel();

// add a Commons category
$statement = new \wb\StatementCommonsCategory( 'P373', 'Test category name' );
$data->addClaim( $statement );

// set a new label value
$label = \wb\Label( 'en', "New label" );
$data->setLabel( $label );

// save
\wm\Wikidata::instance()->editEntity(
	'id'   => 'Q4115189',
	'data' => $data->getJSON(),
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

### Other examples?

Feel free to fork and improve this documentation! Or just look inside the [/include](./include) directory where there is some inline documentation for you!

## Known usage
* [MediaWikiOrphanizerBot](https://github.com/valerio-bozzolan/MediaWikiOrphanizerBot)
* [ItalianWikipediaDeletionBot](https://github.com/valerio-bozzolan/ItalianWikipediaDeletionBot)
* [ItalianWikipediaListAdmins](https://github.com/valerio-bozzolan/ItalianWikipediaListAdmins)
* [2018 MiBACT Wikidata fixed](https://github.com/valerio-bozzolan/Wikimedia-Valerio-Bozzolan-bot-tasks/tree/master/2018-09-mibact-fixer)
* [2018 Wiki loves monuments CH](https://github.com/valerio-bozzolan/Wikimedia-Valerio-Bozzolan-bot-tasks/tree/master/2018-08-wiki-loves-monuments-switzerland)
* [wiki-users-leaflet](https://github.com/valerio-bozzolan/wiki-users-leaflet/) hosted at [WMF Labs](https://tools.wmflabs.org/it-wiki-users-leaflet/)
