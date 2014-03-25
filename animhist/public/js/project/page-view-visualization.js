var gfusionTableID;
var map, gfusionLayer;
var playingTimer;

$(window).on('load', function() {
	//retrieveTimeline();
	mapInitialize();
	//updateLayerStyle();
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
	var select = 'Position, Geocode'; 
	var where = "MilestoneRep = '" + milestone + "'";
	gfusionLayer.setOptions({
		query: {
			select: select,
			from: gfusionTableID,
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
	gfusionTableID = $("#map").data("fusion-table");
	
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