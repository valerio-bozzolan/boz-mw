/*
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

WikiMap.dataPath = '/data';
WikiMap.wiki = 'https://it.wikipedia.org';
WikiMap.firstZoom = 5;
WikiMap.minLevelArea = 1;

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
	$.getJSON(this.dataPath + '/data.min.js', function (data) {
		that.data = data;
		that.initPlot();
		that.plot();
	} );

	this.map.on('zoomend', function () {
		that.plot();
	} );
};

WikiMap.initPlot = function () {
	this.levelGroup = [];
	this.maxLevel = 0;
	this.minLevel = 999;
	for(var i=0; i<this.data.length; i++) {
		var row = this.data[i];
		if(row.lat && row.lng) {
			this.maxLevel = Math.max(this.maxLevel, row.level);
			this.minLevel = Math.min(this.minLevel, row.level);
		}
	}
	for(var i=this.minLevel; i<=this.maxLevel; i++) {
		this.levelGroup[i] = L.layerGroup(); 
	}
	for(var i=0; i<this.data.length; i++) {
		var row = this.data[i];
		if( ! row.lat || ! row.lng ) {
			continue;
		}
		var m = L.marker( [ row.lat, row.lng ] );
		m.bindPopup( this.popupContent(row) );
		this.levelGroup[ row.level ].addLayer(m);
		this.osmRelation = [];
		this.initOSMRelation(row);
	}
};

WikiMap.plot = function () {
	var z = WikiMap.map.getZoom() - this.firstZoom;

	if( z <= this.minLevel ) {
		this.activeLayer.clearLayers()
			.addLayer( this.levelGroup[ this.minLevel ] );
	} else if( this.levelGroup[ z ] ) {
		this.activeLayer.clearLayers()
			.addLayer( this.levelGroup[ z ] );
	}
};

WikiMap.initOSMRelation = function (row) {
	if( ! row.osmid || row.level < this.minLevelArea ) {
		return;
	}

	if( this.osmRelation[ row.osmid ] === undefined ) {
		var that = this;
		$.getJSON(this.dataPath + '/geojson.' + row.osmid + '.js', function (data) {
			that.osmRelation[row.osmid] = data;
			if( ! data ) {
				return;
			}
			var geojson = L.geoJson(data, {
				onEachFeature: function (feature, layer) {
					// Why it does not work? °^°
					layer.bindPopup( that.popupContent(row) );
				}
			} );
			that.levelGroup[ row.level ].addLayer( L.geoJson(data) );
		} );		
	}
	
};

WikiMap.popupContent = function (row) {
	return this.format(
		'<a href="{1}/wiki/{2}">{3}</a>:<br />{4}',
		this.wiki,
		row.title,
		row.title,
		row.count
	);
};

WikiMap.format = function () {
	var s = arguments[0];
	for(var arg in arguments) {
		s = s.replace('{' + arg + '}', arguments[arg] );
	}
	return s;
};
