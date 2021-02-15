#!/usr/bin/php
<?php
# boz-mw - Another MediaWiki API framework
# Copyright (C) 2019, 2020, 2021 Valerio Bozzolan
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.

/**
 * Shortcuts very useful when you are creating a bot
 */

/**
 * Get a single wiki from its UID
 *
 * Some known UIDs:
 *  wikidatawiki - Wikidata
 *  commonswiki  - Wikimedia Commons
 *  metawiki     - Meta-wiki
 *  itwiki       - Wikipedia (it)
 *
 * @param string $uid
 * @return mw\StaticSite
 */
function wiki( $uid ) {
	return \web\MediaWikis::findFromUID( $uid );
}
