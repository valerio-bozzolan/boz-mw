<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2018, 2019 Valerio Bozzolan
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

# website in the Internet
namespace web;

/**
 * MediaWiki instance that belongs to Landscapefor Cultural Association
 *
 * @see https://wiki.landscapefor.eu/
 * @see https://www.landscapefor.eu/
 */
class LandscapeforWiki extends \mw\StaticSite {

	/**
	 * @override
	 */
	const UID = 'landscapeforwiki';

	/**
	 * @override
	 */
	const API_URL = 'https://wiki.landscapefor.eu/api.php';

	/**
	 * @override
	 */
	protected static function create() {
		$site = parent::create();
		$site->setNamespaces( [
			new \mw\Ns( 1,  'Discussione' ),
			new \mw\Ns( 2,  'Utente' ),
			new \mw\Ns( 3,  'Discussioni utente' ),
			new \mw\Ns( 4,  'Landscapefor' ),
			new \mw\Ns( 5,  'Discussioni Landscapefor' ),
			new \mw\Ns( 6,  'File' ),
			new \mw\Ns( 7,  'Discussioni file' ),
			new \mw\Ns( 9,  'Discussioni MediaWiki' ),
			new \mw\Ns( 10, 'Template' ),
			new \mw\Ns( 11, 'Discussioni template' ),
			new \mw\Ns( 12, 'Aiuto' ),
			new \mw\Ns( 13, 'Discussioni aiuto' ),
			new \mw\Ns( 14, 'Categoria' ),
			new \mw\Ns( 15, 'Discussioni categoria' ),
		] );
		return $site;
	}

}
