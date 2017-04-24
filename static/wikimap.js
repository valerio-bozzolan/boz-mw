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

WikiMap = {};

WikiMap.wiki = 'https://it.wikipedia.org';
WikiMap.map = null;
WikiMap.dataAPI = 'data/data.min.js';
WikiMap.data = {};
WikiMap.levelGroup = [];
WikiMap.activeLayer = null;
WikiMap.firstZoom = 5;

WikiMap.start = function () {
	this.map = L.map('map').setView([43, 16], 2);

	L.tileLayer('http://{s}.tiles.wmflabs.org/osm/{z}/{x}/{y}.png', {
		attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, '+
			'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
			'Imagery © <a href="http://wmflabs.org">Wikimedia Labs</a>'
		,
		maxZoom: 18,
	} ).addTo(this.map);

	this.activeLayer = L.layerGroup().addTo(this.map);

	var that = this;
	$.getJSON(this.dataAPI, function (data) {
		that.data = data;
		that.preparePlot();
		that.plot();
	} );

	this.map.on('zoomend', function () {
		that.plot();
	} );
};

WikiMap.preparePlot = function () {
	var maxLevel = 0;
	for(var i=0; i<this.data.length; i++) {
		maxLevel = Math.max(maxLevel, this.data[i].level);
	}
	for(var i=0; i<=maxLevel; i++) {
		this.levelGroup[i] = L.layerGroup(); 
	}
	for(var i=0; i<this.data.length; i++) {
		var row = this.data[i];
		if( ! row.lat || ! row.lng ) {
			continue;
		}
		var m = L.marker( [ row.lat, row.lng ] );
		m.bindPopup( this.format(
			'<a href="{1}/wiki/{2}">{3}</a>:<br />{4}',
			this.wiki,
			row.title,
			row.title,
			row.count
		) );
		this.levelGroup[ row.level ].addLayer(m);
	}
};

WikiMap.plot = function () {
	var z = WikiMap.map.getZoom() - this.firstZoom;

	if( z < 0 ) {
		this.activeLayer.clearLayers()
			.addLayer( this.levelGroup[0] );
	} else if( this.levelGroup[ z ] ) {
		this.activeLayer.clearLayers()
			.addLayer( this.levelGroup[ z ] );
	}
};

WikiMap.format = function () {
	var s = arguments[0];
	for(var arg in arguments) {
		s = s.replace('{' + arg + '}', arguments[arg] );
	}
	return s;
};
