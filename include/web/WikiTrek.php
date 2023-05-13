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
 * MediaWiki instance for websites of the WikiTrek family
 *
 * @see https://wikitrek.org/
 * @see https://data.wikitrek.org/
 */
class WikiTrek extends \mw\StaticSite
{

	/**
	 * @override
	 */
	const UID = 'wikitrek';

	/**
	 * @override
	 */
	const API_URL = 'https://wikitrek.org/wt/api.php';

	/**
	 * @override
	 */
	protected static function create()
	{
		$site = parent::create();
		$site->setNamespaces([
			new \mw\Ns(-2, 'Media'),
			new \mw\Ns(-1, 'Special'),
			new \mw\Ns(1, 'Talk'),
			new \mw\Ns(2, 'User'),
			new \mw\Ns(3, 'User talk'),
			new \mw\Ns(4, 'Project'),
			new \mw\Ns(5, 'Project talk'),
			new \mw\Ns(6, 'File'),
			new \mw\Ns(7, 'File talk'),
			new \mw\Ns(8, 'MediaWiki'),
			new \mw\Ns(9, 'MediaWiki talk'),
			new \mw\Ns(10, 'Template'),
			new \mw\Ns(11, 'Template talk'),
			new \mw\Ns(12, 'Help'),
			new \mw\Ns(13, 'Help talk'),
			new \mw\Ns(14, 'Category'),
			new \mw\Ns(15, 'Category talk'),
			new \mw\Ns(102, 'Propriet\u00e0'),
			new \mw\Ns(103, 'Discussione propriet\u00e0'),
			new \mw\Ns(108, 'Concetto'),
			new \mw\Ns(109, 'Discussione concetto'),
			new \mw\Ns(112, 'smw/schema'),
			new \mw\Ns(113, 'smw/schema talk'),
			new \mw\Ns(114, 'Rule'),
			new \mw\Ns(115, 'Rule talk'),
			new \mw\Ns(828, 'Module'),
			new \mw\Ns(829, 'Module talk'),
			new \mw\Ns(3046, 'TC'),
			new \mw\Ns(3047, 'TC talk'),
		]);
		return $site;
	}
}
class DataTrek extends \mw\StaticWikibaseSite
{

	/**
	 * @override
	 */
	const UID = 'datatrek';

	/**
	 * @override
	 */
	const API_URL = 'https://data.wikitrek.org/dt/api.php';

	/**
	 * @override
	 */
	protected static function create()
	{
		$site = parent::create();
		$site->setNamespaces([
			new \mw\Ns(-2, 'Media'),
			new \mw\Ns(-1, 'Special'),
			new \mw\Ns(1, 'Talk'),
			new \mw\Ns(2, 'User'),
			new \mw\Ns(3, 'User talk'),
			new \mw\Ns(4, 'Project'),
			new \mw\Ns(5, 'Project talk'),
			new \mw\Ns(6, 'File'),
			new \mw\Ns(7, 'File talk'),
			new \mw\Ns(8, 'MediaWiki'),
			new \mw\Ns(9, 'MediaWiki talk'),
			new \mw\Ns(10, 'Template'),
			new \mw\Ns(11, 'Template talk'),
			new \mw\Ns(12, 'Help'),
			new \mw\Ns(13, 'Help talk'),
			new \mw\Ns(14, 'Category'),
			new \mw\Ns(15, 'Category talk'),
			new \mw\Ns(120, 'Item'),
			new \mw\Ns(121, 'Item talk'),
			new \mw\Ns(122, 'Property'),
			new \mw\Ns(123, 'Property talk'),
			new \mw\Ns(828, 'Module'),
			new \mw\Ns(829, 'Module talk'),
			new \mw\Ns(1198, 'Translations'),
			new \mw\Ns(1199, 'Translations talk'),
		]);
		return $site;
	}
}
