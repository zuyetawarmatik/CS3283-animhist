$(function() {
	var commentArea = $("#comment-area");
	
	$("#comment-area-title").hover(function() {
		if (commentArea.hasClass("expanded"))
			$(this).html("&#57636;");
		else
			$(this).html("&#57632;");
	}, function() {
		$(this).html("12 comments");
	});
	
	$("#comment-area-title").click(function() {
		var visualArea = $("#visualization-area");
		if (commentArea.hasClass("expanded")) {
			visualArea.stop(true).animate({bottom: "60px"}, 400, mapResizeTrigger);
			commentArea.stop(true).animate({height: "60px"}, 400);
		} else {
			visualArea.stop(true).animate({bottom: "300px"}, 400, mapResizeTrigger);
			commentArea.stop(true).animate({height: "300px"}, 400);
		}
		commentArea.toggleClass("expanded");
	});
});

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
	var sector = 'Non-Residential';
	var year = 2005;
	
	map = new google.maps.Map(document.getElementById('map'), {
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

	timer();
	styleMap(map);
	
	$(".timeline-item").each(function() {
		google.maps.event.addDomListener($(this)[0],
				'click', function() {
			var year = $(this).html();
			styleLayerBySector(layer, sector, year);
			if (!$(this).hasClass("focused")) {
				$(".timeline-item.focused").removeClass("focused");
				$(this).addClass("focused");
			}
		});
	});
	
	//window.setInterval(timer, 1000);
	function timer() {
		year += 1;
		if (year == 2011) year = 2006;
		$(".timeline-item.focused").removeClass("focused");
		$(".timeline-item").each(function() {
			if ($(this).html() == year)
				$(this).addClass("focused");
		});
		styleLayerBySector(layer, sector, year);
	}
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

function styleLayerBySector(layer, sector, year) {
	var layerStyle = LAYER_STYLES[sector];
	var colors = layerStyle.colors;
	var minNum = layerStyle.min;
	var maxNum = layerStyle.max;
	var step = (maxNum - minNum) / colors.length;

	var styles = new Array();
	for (var i = 0; i < colors.length; i++) {
		var newMin = minNum + step * i;
		styles.push({
			where: generateWhere(newMin, sector, year),
			polygonOptions: {
				fillColor: colors[i],
				fillOpacity: 1
			}
		});
	}
	layer.set('styles', styles);
}

function generateWhere(minNum, sector, year) {
	var whereClause = new Array();
	whereClause.push("Sector = '");
	whereClause.push(sector);
	whereClause.push("' AND '");
	whereClause.push(year);
	whereClause.push("' >= ");
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

google.maps.event.addDomListener(window, 'load', initialize);
