# Boz-MW command line tools

This is a directory for command line tools that uses the boz-mw framework. Actually this is more a proof-of-concept.

## Configuration 

Copy the `config-example.php` to `config.php` and fill it with your bot credentials.

## replace.php

This is another `replace.py` version.

To transform in *farfallese* the whole Italian Wikipedia:

```bash
./replace.php --wiki=itwiki --generator=allpages --plain \
    a afa \
    e efe \
    i ifi \
    o ofo \
    u ufu
```

To replace a simple template parameter in top of the page, e.g. from `{{Sito web|commerciale = Sì}}` to `{{Sito web|lucro = Sì}}`:

```bash
./replace.php \
	--wiki=itwiki \
    --generator=transcludedin \
    --titles=Template:Sito_web \
    --first-section \
    --limit=1 \
    --regex \
    '/\|commerciale(.*=.*)(Sì|No|sì|no)(.*)/' \
    '|lucro$1$2$3'
```

Other options:

```bash
./replace.php --help
```
