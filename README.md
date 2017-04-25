# wiki-users-leaflet
Wikipedians on a Leaflet map. Automagically.

## Why
Because I have a lot of other work to do, but I absolutely don't want to do it now. Also because of promoting Google Mashup is not a good idea in a Wikimedia project.

## Hacking
First of all:

    git clone https://github.com/valerio-bozzolan/boz-php-another-php-framework.git
    cp load-example.php load.php
    ./bot.php

The script will gain informations from Wikipedia APIs and Wikidata recursively scanning a users category like [Categoria:Utenti dall'Italia](https://it.wikipedia.org/wiki/Categoria:Utenti_dall%27Italia).

It will take some time finally generating the file `data/data.js`:

```
{
    "Categoria:Utenti dall'Italia": {
        "title": "Categoria:Utenti dall'Italia",
        "count": 3080,
        "level": 0,
        "lat": "43",
        "lng": "12",
        "osmid": "365331"
    },
    "Categoria:Utenti abruzzesi": {
        "title": "Categoria:Utenti abruzzesi",
        "count": 35,
        "level": 1,
        "lat": "42.216666666667",
        "lng": "13.833333333333",
        "osmid": null
    },
```

Note that every category has a `count` that is the sum of the users in that category _plus_ the sum of every users in sub-categories.

The `level` is the category-tree level.

The `osmid` is the OpenStreetMap relation identifier ([53937](https://www.openstreetmap.org/relation/53937)).

## Implementation notes
- Every user category should be connected to Wikidata
  - Every wikidata element should have a [geographic of topic P2633](https://www.wikidata.org/wiki/Property:P2633)
    - Every geographic topic should have
      - a [coordinate location P625](https://www.wikidata.org/wiki/Property:P625)
      - the [OpenStreetMap Relation identifier P402](https://www.wikidata.org/wiki/Property:P402)
