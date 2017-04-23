/**
* Leaflet Wikipedians map
* Copyright (C) 2017 Valerio Bozzolan
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

var WikiMap = {};

WikiMap.start = function () {
	this.map = L.map('map').setView([43, 16], 2);

	L.tileLayer('http://{s}.tiles.wmflabs.org/osm/{z}/{x}/{y}.png', {
		attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, '+
			'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
			'Imagery © <a href="http://wmflabs.org">Wikimedia Labs</a>'
		,
		maxZoom: 18,
	} ).addTo(this.map);
};
