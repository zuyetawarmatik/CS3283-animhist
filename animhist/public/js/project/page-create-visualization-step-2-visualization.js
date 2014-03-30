var map, gfusionLayer;
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
	$("#description-area .editable .repos-a").on("click", function() {
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
	$("#play-btn").attr("data-is-playing", $("#play-btn").attr("data-is-playing") == "false" ? "true" : "false");
}

function pauseVisualization() {
	$("#play-btn").attr("data-is-playing", "false");
}

$(function() {
	$("#timeline-list").attrchange({
		trackValues: true, 
		callback: function (event) {
			if (event.attributeName == "data-milestone") {
				$(".timeline-item.focused").removeClass("focused");
				currentTimelineMilestoneId = $.inArray(event.newValue, gridTimeline);
				$(".timeline-item:nth-child(" + currentTimelineMilestoneId + ")").addClass("focused");
				updateLayerQuery(event.newValue);
			}
		}
	});
	
	$("#play-btn").attrchange({
		trackValues: true,
		callback: function (event) {
			if (event.attributeName == "data-is-playing") {
				if (event.newValue == "true") {
					$(this).html("<i>&#57611;</i>");
					playingTimer = window.setInterval(function() {
						var nextTimelineMilestoneId = (currentTimelineMilestoneId + 1) % gridTimeline.length;
						if (nextTimelineMilestoneId == 0) nextTimelineMilestoneId = 1; // Omit "All" entry
						$("#timeline-list").attr("data-milestone", gridTimeline[nextTimelineMilestoneId]);
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
	$("#play-btn").click(function() {
		if (gridTimeline.length > 1) {
			togglePlayVisualization();
		}
	});
	
	$("#timeline-list").on("click", ".timeline-item", function() {
		if (!$(this).hasClass("focused")) {
			$("#timeline-list").attr("data-milestone", $(this).html());
		}
	});
});