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
WikiMap.minLevelArea = 2;
WikiMap.userPrefix = 'Utente';
WikiMap.zoomMessage = '(Zoomma)';

WikiMap.start = function () {
	this.map = L.map('wikimap-map').setView([43, 16], 2);

	this.$usersContainer = this.$usersContainer || $('#wikimap-users');

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

	this.map.on('popupopen', function(popup) {
		var data = popup.popup._source.wikimapData;
		if( ! data ) {
			for(var i in popup.popup._source._eventParents) {
				data = popup.popup._source._eventParents[i].wikimapData;
			}
		}
		console.log(data);
		that.printUsers(data);
	} );
};

WikiMap.initPlot = function () {
	this.osmRelation = [];
	this.lastZoom = this.map.getZoom();
	this.levelGroup = [];
	this.dataByTitle = {};
	this.childrenByParent = {};
	this.maxLevel = 0;
	this.minLevel = 999;
	for(var i=0; i<this.data.length; i++) {
		var row = this.data[i];
		if(! row.lat || ! row.lng) {
			continue;
		}
		this.maxLevel = Math.max(this.maxLevel, row.level);
		this.minLevel = Math.min(this.minLevel, row.level);

		this.dataByTitle[ row.title ] = row;

		if( row.parent ) {
			if( ! this.childrenByParent[ row.parent ] ) {
				this.childrenByParent[ row.parent ] = [];
			}
			this.childrenByParent[ row.parent ].push(row);
		}
	}

	for(var i=this.minLevel; i<=this.maxLevel; i++) {
		this.levelGroup[i] = L.layerGroup(); 
	}
	var that = this;

	for(var i=0; i<this.data.length; i++) {
		var row = this.data[i];
		if( ! row.lat || ! row.lng ) {
			continue;
		}
		this.initOSMRelation(row, function (row, geojson) {
			if( geojson ) {
				geojson.wikimapData = row;
				that.addDataLayer(row, geojson);
			} else {
				var m = L.marker( [ row.lat, row.lng ] ).bindPopup( that.popupContent(row) );
				m.wikimapData = row;				
				that.addDataLayer(row, m);
			}
		} );
	}
};

WikiMap.addDataLayer = function (data, layer) {
	if( this.childrenByParent[ data.title ] ) {
		this.levelGroup[ data.level ].addLayer(layer);
	} else {
		// No childrens
		for(var i=data.level; i<=this.maxLevel; i++) {
			// Append in children levels
			this.levelGroup[i].addLayer(layer);
		}
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

WikiMap.initOSMRelation = function (row, callback) {
	if( ! row.osmid || row.level < this.minLevelArea ) {
		callback(row, false);
		return;
	}
	if( this.osmRelation[ row.osmid ] !== undefined ) {
		callback(row, false);
		return;
	}
	var that = this;
	$.getJSON(this.dataPath + '/geojson.' + row.osmid + '.js', function (data) {
		that.osmRelation[row.osmid] = false;
		if( data ) {
			var geojson = L.geoJson(data, {
				onEachFeature: function (feature, layer) {
					layer.bindPopup( that.popupContent(row) );
					layer.wikimapData = row;
				}
			} );
			that.osmRelation[row.osmid] = geojson;
		}
		callback(row, geojson);
	} );		
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

WikiMap.printUsers = function (data) {
	var users = this.zoomMessage;
	if(data && data.users) {
		users = '';
		for(var i=0; i<data.users.length; i++) {
			users += (i > 0) ? ', ' : '';
			users += this.format('<a href="{1}/wiki/{2}:{3}">{4}</a>', this.wiki, this.userPrefix, data.users[i], data.users[i]);
		}
	}
	this.$usersContainer.empty().html(users);
};
