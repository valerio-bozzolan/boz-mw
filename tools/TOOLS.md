# Boz-MW command line tools

## replace.php

This is another `replace.py` version.

To transform in *farfallese* a whole wiki:

    ./replace.php --generator=allpages --plain \
        a afa \
        e efe \
        i ifi \
        o ofo \
        u ufu

To replace a template parameter, e.g. from `|commerciale = Sì` to `|lucro = Sì`:

    ./replace.php \
        --generator=transcludedin \
        --titles=Template:Sito_web \
        --first-section \
        --limit=1 \
        --regex \
        '/\|commerciale(.*=.*)(Sì|No|sì|no)(.*)/' \
        '|lucro$1$2$3'

Other options:

    ./replace.php --help
