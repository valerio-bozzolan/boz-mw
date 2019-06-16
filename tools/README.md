# boz-mw command line tools

This is a directory for command line tools that uses the boz-mw framework. Actually there are not too much tools.

## Configuration 

Copy the `config-example.php` to `config.php` and fill it with your bot credentials.

## Available scripts

### Replace script - `replace.php`

This is a script to sobstitute text in the wikitext from an API query.

e.g. to transform in *farfallese* the whole Italian Wikipedia:

```bash
./replace.php --wiki=itwiki --generator=allpages \
    a afa \
    e efe \
    i ifi \
    o ofo \
    u ufu
```

e.g. to replace a simple template parameter in top of the page, e.g. from `{{Sito web|commerciale = Sì}}` to `{{Sito web|lucro = Sì}}`:

```bash
./replace.php \
    --wiki=itwiki \
    --generator=transcludedin \
    --titles=Template:Sito_web \
    --rvsection=0 \
    --regex \
    '/\|commerciale(.*=.*)(Sì|No|sì|no)(.*)/' \
    '|lucro$1$2$3'
```

Other options:

```bash
./replace.php --help
```

You can see some [examples](./examples).

### Mega export - `mega-export.php`

This is a script that acts similar to the `[[Special:Export]]` page, but exporting the full page history.

Note that you have to provide your user credentials in the `config.php` script in order to download more than `50` revisions at time.

```
Usage:
 ./mega-export.php --wiki=WIKI --file=out.xml [OPTIONS] Page_title
Allowed OPTIONS:
 --wiki=VALUE          Available wikis: itwiki, wikidatawiki, commonswiki, metawiki, landscapeforwiki
 --limit=VALUE         Number of revisions for each request
 --file=VALUE          Output filename
 --help|-h             Show this help and quit
```

E.g. to download the full history of the [Software libero](https://it.wikipedia.org/wiki/Software_libero) page:

```
./mega-export.php --wiki=itwiki --file=out.xml "Software libero"
```

Note that actually the official MediaWiki/XML format is actually mistreated at least for the heading section: you will not obtain the namespace list, the wiki name, and other unuseful things. Just revisions. Much revisions.
