var LAYER_STYLES = {
		'Residential': {
			'min': 0,
			'max': 10000,
			'colors': [
			           '#f4cccc',
			           '#ea9999',
			           '#e06666',
			           '#cc0000',
			           '#990000'
			           ]
		},
		'Non-Residential': {
			'min': 0,
			'max': 10000,
			'colors': [
			           '#d0e0e3',
			           '#a2c4c9',
			           '#76a5af',
			           '#45818e',
			           '#134f5c'
			           ]
		},
		'Total': {
			'min': 0,
			'max': 20000,
			'colors': [
			           '#d9d2e9',
			           '#b4a7d6',
			           '#8e7cc3',
			           '#674ea7',
			           '#351c75'
			           ]
		}
}

function initialize() {
	var sector = 'Residential';

	var map = new google.maps.Map(document.getElementById('map'), {
		center: new google.maps.LatLng(37.4, -119.8),
		zoom: 5,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		},
	});

	var layer = new google.maps.FusionTablesLayer();
	updateLayerQuery(layer, sector);
	layer.setMap(map);

	styleLayerBySector(layer, sector);
	styleMap(map);
	drawVisualization('Alameda');

	google.maps.event.addListener(layer, 'click', function(e) {
		var county = e.row['County'].value;
		drawVisualization(county);

		var electricity = e.row['2010'].value;
		if (electricity > 5000) {
			e.infoWindowHtml = '<p class="high">High Usage!</p>';
		} else if (electricity > 2500) {
			e.infoWindowHtml = '<p class="medium">Medium Usage</p>';
		} else {
			e.infoWindowHtml = '<p class="low">Low Usage</p>';
		}
	});
}

function updateLayerQuery(layer, sector, county) {
	var where = "Sector = '" + sector + "'";
	if (county) {
		where += " AND County = '" + county + "'";
	}
	layer.setOptions({
		query: {
			select: 'geometry',
			from: '18fyPg1LvW3KB3N5DE_ub-MKicB0Nx7vkGn9kw4s',
			where: where
		}
	});
}

function styleLayerBySector(layer, sector) {
	var layerStyle = LAYER_STYLES[sector];
	var colors = layerStyle.colors;
	var minNum = layerStyle.min;
	var maxNum = layerStyle.max;
	var step = (maxNum - minNum) / colors.length;

	var styles = new Array();
	for (var i = 0; i < colors.length; i++) {
		var newMin = minNum + step * i;
		styles.push({
			where: generateWhere(newMin, sector),
			polygonOptions: {
				fillColor: colors[i],
				fillOpacity: 1
			}
		});
	}
	layer.set('styles', styles);
}

function generateWhere(minNum, sector) {
	var whereClause = new Array();
	whereClause.push("Sector = '");
	whereClause.push(sector);
	whereClause.push("' AND '2010' >= ");
	whereClause.push(minNum);
	return whereClause.join('');
}

function styleMap(map) {
	var style = [{
		featureType: 'all',
		stylers: [{
			saturation: -99
		}]
	}, {
		featureType: 'poi',
		stylers: [{
			visibility: 'off'
		}]
	}, {
		featureType: 'road',
		stylers: [{
			visibility: 'off'
		}]
	}];

	var styledMapType = new google.maps.StyledMapType(style, {
		map: map,
		name: 'Styled Map'
	});
	map.mapTypes.set('map-style', styledMapType);
	map.setMapTypeId('map-style');
}

function drawVisualization(county) {
	google.visualization.drawChart({
		containerId: "visualization",
		dataSourceUrl: "http://www.google.com/fusiontables/gvizdata?tq=",
		query: "SELECT Sector,'2006','2007','2008','2009','2010' " +
		"FROM 18fyPg1LvW3KB3N5DE_ub-MKicB0Nx7vkGn9kw4s WHERE County = '" + county + "'",
		chartType: "ColumnChart",
		options: {
			title: county,
			height: 400,
			width: 400
		}
	});
}

google.maps.event.addDomListener(window, 'load', initialize);
