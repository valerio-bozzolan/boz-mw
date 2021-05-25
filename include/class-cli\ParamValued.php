<?php
# boz-mw - Another MediaWiki API handler in PHP
# Copyright (C) 2018, 2019, 2020, 2021 Valerio Bozzolan
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

# command line interface
namespace cli;

/**
 * A command line parameter that must have a value
 */
class ParamValued extends Param {

	/**
	 * Constructor
	 *
	 * @param $long_name   string Long name like 'revision-text'
	 * @param $short_name  string Short name like 'r'
	 * @param $description string Short human description
	 * @param $default_val string Default value
	 */
	public function __construct( $long_name, $short_name = null, $description = null, $default_val = null ) {
		parent::__construct( $long_name, $short_name, self::REQUIRED_VALUE, $description, $default_val );
	}

}
