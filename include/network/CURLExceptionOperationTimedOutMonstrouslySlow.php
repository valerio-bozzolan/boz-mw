<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2018-2024 Valerio Bozzolan
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

namespace network;

/**
 * Exception thrown when a cURL connection is mostrously slow.
 * For example with message:
 *   "Operation too slow. Less than 1 bytes/sec transferred the last 120 seconds".
 * https://phabricator.wikimedia.org/T375937
 */
class CURLExceptionOperationTimedOutMonstrouslySlow extends CURLExceptionTransport {

}
