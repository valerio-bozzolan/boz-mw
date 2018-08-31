<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017, 2018 Valerio Bozzolan
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

# MediaWiki
namespace mw;

// traits can't be autoloadded
require 'class-generic\Singleton.php';

/**
 * A site that can be instantiated once using getInstance()
 */
class StaticSite extends Site {

	use \generic\Singleton;

	/**
	 * @override
	 */
	protected static function create() {
		$site = static::createFromAPIURL( static::getApiURL() );

		$site->setUID( static::UID );

		// Set default namespaces
		foreach( Ns::defaultCanonicalNames() as $ns_id => $ns ) {
			$site->setNamespace( new Ns( $ns_id, $ns ) );
		}

		return $site;
	}

	/**
	 * To be overloaded.
	 *
	 * @return string
	 */
	protected static function getApiURL() {
		return static::API_URL;
	}

	/**
	 * To be overloaded.
	 *
	 * @param $site mw\Site
	 */
	protected static function onCreate( $site ) {
		// Please override
	}

}
