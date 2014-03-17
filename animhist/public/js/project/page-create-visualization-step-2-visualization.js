var gfusionTableID;
var map, gfusionLayer;

function mapResizeTrigger() {
	google.maps.event.trigger(map, 'resize');
}

$(window).on('vi_property_loaded', function() {
	mapInitialize();
});

function mapInitialize() {
	map = new google.maps.Map(document.getElementById('map'), {
		center: new google.maps.LatLng(viProps['centerLatitude'], viProps['centerLongitude']),
		zoom: parseFloat(viProps['zoom']),
		mapTypeId: google.maps.MapTypeId.TERRAIN,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		},
	});

	gfusionLayer = new google.maps.FusionTablesLayer();
	gfusionLayer.setMap(map);
}

function updateLayerQuery(milestone) {
	var select = 'Position, Gecode';
	if (viProps['defaultColumn']) select += ', ' + viProps['defaultColumn']; 
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