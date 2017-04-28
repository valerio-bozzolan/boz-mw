<?php
# Leaflet Wikipedians map
# Copyright (C) 2017 Valerio Bozzolan
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

defined('BOZ_PHP') or exit;

defined('DEFAULT_WIKI')
	or define('DEFAULT_WIKI', 'https://it.wikipedia.org');

// Absolute URL pathname of Debian libjs packages
defined('JAVASCRIPT')
	or define('JAVASCRIPT', '/javascript');

// libjs-jquery Debian package
defined('JQUERY')
	or define('JQUERY', JAVASCRIPT . '/jquery/jquery.min.js');

// libjs-leaflet Debian package
defined('LEAFLET')
	or define('LEAFLET', JAVASCRIPT . '/leaflet');

defined('LEAFLET_JS')
	or define('LEAFLET_JS',  LEAFLET . '/leaflet.js');

defined('LEAFLET_CSS')
	or define('LEAFLET_CSS', LEAFLET . '/leaflet.css');

define('STITIC', 'static'); // Not a reserved word :^)

defined('PUBLIC_DATA_DIR')
	or define('PUBLIC_DATA_DIR', 'data');

defined('PUBLIC_DATA')
	or define('PUBLIC_DATA', ABSPATH . __ . PUBLIC_DATA_DIR);

defined('PRIVATE_DATA')
	or define('PRIVATE_DATA', ABSPATH . __ . 'private-data');

register_js( 'jquery',  JQUERY     );
register_js( 'leaflet', LEAFLET_JS );
register_js( 'wikimap', ROOT . _ . STITIC . '/wikimap.js');
register_css('wikimap', ROOT . _ . STITIC . '/wikimap.css');
register_css('leaflet', LEAFLET_CSS);

define('SITE_NAME', _("Wikipedians map") );

add_menu_entries( [
	new MenuEntry('index', URL, SITE_NAME )
] );

define('INCLUDES', 'includes');

// includes/class-$php.php
spl_autoload_register( function($c) {
    $path = ABSPATH . __ . INCLUDES . __ . "class-$c.php";
    if( is_file( $path ) ) {
        require $path;
    }
} );
