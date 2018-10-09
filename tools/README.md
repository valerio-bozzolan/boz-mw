# boz-mw command line tools

This is a directory for command line tools that uses the boz-mw framework. Actually there are not too much tools.

## Configuration 

Copy the `config-example.php` to `config.php` and fill it with your bot credentials.

## replace.php

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
