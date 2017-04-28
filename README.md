# wiki-users-leaflet
Wikipedians on a Leaflet map. Automagically.

This is the source code of a tool hosted in [Wikimedia Foundation Labs](https://tools.wmflabs.org/).

* <https://tools.wmflabs.org/it-wiki-users-leaflet/>

## How to add yourself
* Insert your user page in a sub-category of [Categoria:Utenti per provenienza territoriale](https://it.wikipedia.org/wiki/Categoria:Utenti_per_provenienza_territoriale)
* Wait weeks/months or ping the current [maintainer](https://it.wikipedia.org/wiki/Wikipedia:Mappa_dei_wikipediani/ManutentoreLeaflet)

### Hacking
The PHP website uses an unknown framework called [Boz-PHP](https://github.com/valerio-bozzolan/boz-php-another-php-framework).

    git clone https://github.com/valerio-bozzolan/boz-php-another-php-framework.git
    cp load-example.php load.php
    ./bot.php

The map uses [Leaflet's reference](http://leafletjs.com/reference.html).

The bot scan can be run calling `./bot.php` and will gain informations from Wikipedia APIs and Wikidata. It recursively scans every user territorial category like [Categoria:Utenti dall'Italia](https://it.wikipedia.org/wiki/Categoria:Utenti_dall%27Italia).

It will take some time finally generating the file `data/data.js` and `data/data.min.js`:

```
[
    {
        "title": "Categoria:Utenti dall'Albania",
        "count": 2,
        "level": 1,
        "lat": 41,
        "lng": 20,
        "osmid": 53292,
        "parent": "Categoria:Utenti per provenienza territoriale",
        "users": [

        ],
        "isLeaf": true
    },
```

* `title`: The category title.
* `count`: The sum of the users in that category _plus_ the sum of every users in sub-categories.
* `level`: The root is 0, every leaf increment.
* `lat`, `lng`: [coordinate location P625](https://www.wikidata.org/wiki/Property:P625)
* `osmid`: [OpenStreetMap Relation identifier P402](https://www.wikidata.org/wiki/Property:P402). It's used to obtain the GeoJSON from <http://polygons.openstreetmap.fr>. The GeoJSON is saved in `data/geojson.[osmid].js`.
* `level`: the category-tree level.
* `osmid`: the OpenStreetMap relation identifier ([53937](https://www.openstreetmap.org/relation/53937)).
* `users`: active users without the user namespace. Active users have the last contribution date not older than 6 months.
* `isLeaf`: if it hasn't children categories

## Implementation notes
- Every user should:
  - be active
  - has a sub-category of [Categoria:Utenti per provenienza territoriale](https://it.wikipedia.org/wiki/Categoria:Utenti_per_provenienza_territoriale) placed anywhere in:
    - in his user page (or a sub-page)
    - in his user talk page (or a sub-page)
- Every sub-category of [Categoria:Utenti per provenienza territoriale](https://it.wikipedia.org/wiki/Categoria:Utenti_per_provenienza_territoriale) should:
  - be connected to Wikidata
    - Every wikidata element should have a [geographic of topic P2633](https://www.wikidata.org/wiki/Property:P2633)
      - Every geographic topic should have
        - a [coordinate location P625](https://www.wikidata.org/wiki/Property:P625)
        - the [OpenStreetMap Relation identifier P402](https://www.wikidata.org/wiki/Property:P402)
          - an obtainable GeoJSON from <http://polygons.openstreetmap.fr>

## Why
Trust me, I have a lot of work to do in IRL but I absolutely don't want to do it. See [procastination](https://en.wikipedia.org/wiki/Procrastination). In addition, before this map, the only interactive alternative was made using a non Free as in Freedom tool and this triggered my procastination daemon.
