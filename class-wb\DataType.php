<?php
# Boz-MW - Another MediaWiki API handler in PHP
# Copyright (C) 2017 Valerio Bozzolan
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

# Wikibase
namespace wb;

/**
 * A DataType.
 *
 * @see https://www.wikidata.org/wiki/Special:ListDatatypes
 */
class DataType {

	const COMMONS_MEDIA    = 'commonsMedia';
	const STRING           = 'string';
	const URL              = 'url';
	const TIME             = 'time';
	const QUANTITY         = 'quantity';
	const MONOLINGUAL_TEXT = 'monolingualtext';
	const ITEM             = 'wikibase-item';
	const ENTITY_ID        = 'wikibase-entityid';
	const PROPERTY         = 'wikibase-property';
	const EXTERNAL_ID      = 'external-id';

}
