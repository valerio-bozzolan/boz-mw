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

class Footer {
	static function spawn( $args = [] ) {
		if( Header::$args['container'] ) {
			echo "\n\t<div><!-- /container -->\n";
		}
?>
	<p><?php printf('<a href="%s">Codice sorgente</a>. Sei libero di fruire di questo software libero sotto i termini della licenza <a href="%s">GNU AGPL v3+</a>.',
		'https://github.com/valerio-bozzolan/wiki-users-leaflet/',
		'https://www.gnu.org/licenses/agpl.html'
	) ?></p>
</body>
</html><?php

} }
