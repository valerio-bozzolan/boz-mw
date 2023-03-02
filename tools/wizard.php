#!/usr/bin/php
<?php
# Copyright (C) 2018-2023 Valerio Bozzolan, contributors
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
 * The purpose of this file is to just run a configuration wizard.
 */

// exit if not CLI
$argv or exit( 1 );

// load boz-mw
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload-with-laser-cannon.php';

// load configuration or run the wizard
config_wizard( 'config.php' );

echo "Well done! You already have your config file. Now use a random tool!\n";
