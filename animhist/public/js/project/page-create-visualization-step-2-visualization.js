var gfusionTableID;
var map, gfusionLayer;

$(window).on('vi_property_loaded', function() {
	mapInitialize();
});

$(window).on('vi_property_changed', function(e) {
	if (map !== undefined) {
		var fields = e.fields;
		if ($.inArray("zoom", fields) >= 0)
			map.setZoom(parseInt(viProps['zoom']));
		if ($.inArray("centerLatitude", fields) >= 0 || $.inArray("centerLongitude", fields) >= 0)
			map.setCenter(new google.maps.LatLng(viProps['centerLatitude'], viProps['centerLongitude']));
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

$(function() {
	$("#timeline-list").attrchange({
		trackValues: true, 
		callback: function (event) {
			if (event.attributeName == "data-milestone") {
				$(".timeline-item.focused").removeClass("focused");
				var milestoneIndex = $.inArray(event.newValue, gridTimeline);
				$(".timeline-item:nth-child(" + milestoneIndex + ")").addClass("focused");
				updateLayerQuery(event.newValue);
			}
		}
	});
});

$(function() {
	$("#timeline-list").on("click", ".timeline-item", function() {
		if (!$(this).hasClass("focused")) {
			$("#timeline-list").attr("data-milestone", $(this).html());
		}
	});
	gfusionTableID = $("#map").data("fusion-table");
});