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

require 'load.php';

enqueue_js('leaflet');
enqueue_js('jquery');
enqueue_js('wikimap');

enqueue_css('leaflet');
enqueue_css('wikimap');

Header::spawn('index', [
	'complete-title' => false,
	'container' => false
] );

?>
	<div id="wikimap-users"></div>
	<div id="wikimap-map"></div>

	<script>
	WikiMap.wiki    = '<?php echo DEFAULT_WIKI ?>';
	WikiMap.dataPath = '<?php echo ROOT ?>/<?php echo PUBLIC_DATA_DIR ?>';
	WikiMap.start();
	</script>

<?php

Footer::spawn();
