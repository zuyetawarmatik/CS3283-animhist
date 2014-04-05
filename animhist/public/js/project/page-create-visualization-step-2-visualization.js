var map, gfusionLayer, drawingManager;
var drawnShape;
var playingTimer;

$(window).on('vi_style_loaded', function() {
	retrieveTimeline();
	mapInitialize();
	updateLayerStyle();
});

$(window).on('vi_property_changed', function(e) {
	if (map !== undefined) {
		var fields = e.fields;
		if ($.inArray("zoom", fields) >= 0)
			map.setZoom(parseInt(viProps['zoom']));
		if ($.inArray("centerLatitude", fields) >= 0 || $.inArray("centerLongitude", fields) >= 0)
			map.setCenter(new google.maps.LatLng(viProps['centerLatitude'], viProps['centerLongitude']));
		if ($.inArray("defaultColumn", fields) >= 0)
			updateLayerStyle();
	}
});

$(function() {
	$descriptionArea.find(".repos-a").on("click", function() {
		if (map !== undefined) {
			var field = $(this).parent().attr("id");
			if (field == "zoom")
				map.setZoom(parseInt(viProps['zoom']));
			else if (field == "center")
				map.setCenter(new google.maps.LatLng(viProps['centerLatitude'], viProps['centerLongitude']));
		}
	});
});

function mapResizeTrigger() {
	var curCenter = map.getCenter();
	google.maps.event.trigger(map, 'resize');
	map.setCenter(curCenter);
}

function mapInitialize() {
	map = new google.maps.Map(document.getElementById('map'), {
		center: new google.maps.LatLng(viProps['centerLatitude'], viProps['centerLongitude']),
		zoom: parseInt(viProps['zoom']),
		mapTypeId: google.maps.MapTypeId.TERRAIN,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		},
	});
	
	gfusionLayer = new google.maps.FusionTablesLayer();
	gfusionLayer.setMap(map);
	
	if (viProps['htmlData']) {
		google.maps.event.addListener(gfusionLayer, 'click', function(e) {
			// Change the content of the InfoWindow by using HTMLData
			e.infoWindowHtml = e.row['HTMLData'].value;
		});
	}
	
	/* For drawing on the map */
	drawingManager = new google.maps.drawing.DrawingManager({
		drawingControl: true,
		drawingControlOptions: {
			position: google.maps.ControlPosition.TOP_CENTER,
			drawingModes: viProps["type"] == "point" ? [google.maps.drawing.OverlayType.MARKER] : [google.maps.drawing.OverlayType.POLYGON]
		},
		polygonOptions: {
			strokeWeight: 1
		}
	});
	drawingManager.setMap(map);
	google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {
		drawingManager.setDrawingMode(null);
		drawnShape = e.overlay;
		switch (e.type) {
		case google.maps.drawing.OverlayType.MARKER:
			var lat = drawnShape.getPosition().lat().toFixed(3);
			var lng = drawnShape.getPosition().lng().toFixed(3);
			openAddRowVex({Position: lat + " " + lng});
			break;
		case google.maps.drawing.OverlayType.POLYGON:
			var vertices = drawnShape.getPath().j;
			var verticesStr = "";
			for (var i = 0; i < vertices.length; i++) {
				var lat = vertices[i].A.toFixed(3);
				var lng = vertices[i].k.toFixed(3);
				verticesStr += lat + "," + lng;
				if (i < vertices.length - 1) verticesStr += " ";
			}
			var kmlStr = "<Polygon><outerBoundaryIs><LinearRing><coordinates>" + verticesStr + "</coordinates></LinearRing></outerBoundaryIs></Polygon>";
			openAddRowVex({Position: kmlStr});
			break;
		}
	});
}

function updateLayerStyle() {
	if (viProps["defaultStyleId"] !== undefined)
		gfusionLayer.setOptions({"styleId": viProps["defaultStyleId"]});
}

function updateLayerQuery(milestone) {
	var select = 'Geocode'; 
	var where = "MilestoneRep = '" + milestone + "'";
	gfusionLayer.setOptions({
		query: {
			select: select,
			from: viProps["gfusionTableID"],
			where: where 
		}
	});
}

function togglePlayVisualization() {
	$playBtn.attr("data-is-playing", $playBtn.attr("data-is-playing") == "false" ? "true" : "false");
}

function pauseVisualization() {
	$playBtn.attr("data-is-playing", "false");
}

$(function() {
	$timelineList.attrchange({
		trackValues: true, 
		callback: function (event) {
			if (event.attributeName == "data-milestone") {
				$("li.timeline-item.focused").removeClass("focused");
				currentTimelineMilestoneId = $.inArray(event.newValue, gridTimeline);
				$("li.timeline-item:nth-child(" + currentTimelineMilestoneId + ")").addClass("focused");
				updateLayerQuery(event.newValue);
			}
		}
	});
	
	$playBtn.attrchange({
		trackValues: true,
		callback: function (event) {
			if (event.attributeName == "data-is-playing") {
				if (event.newValue == "true") {
					$(this).html("<i>&#57611;</i>");
					playingTimer = window.setInterval(function() {
						var nextTimelineMilestoneId = (currentTimelineMilestoneId + 1) % gridTimeline.length;
						if (nextTimelineMilestoneId == 0) nextTimelineMilestoneId = 1; // Omit "All" entry
						$timelineList.attr("data-milestone", gridTimeline[nextTimelineMilestoneId]);
					}, 1000);
				} else if (event.newValue == "false") {
					$(this).html("<i>&#57610;</i>");
					window.clearInterval(playingTimer);
				}
			}
		}
	});
});

$(function() {
	$playBtn.click(function() {
		if (gridTimeline.length > 1) {
			togglePlayVisualization();
		}
	});
	
	$timelineList.on("click", "li.timeline-item", function() {
		if (!$(this).hasClass("focused")) {
			$timelineList.attr("data-milestone", $(this).html());
		}
	});
});