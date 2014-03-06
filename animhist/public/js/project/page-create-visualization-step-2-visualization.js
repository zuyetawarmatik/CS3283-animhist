var gfusionTableID;
var map, gfusionLayer;

function mapResizeTrigger() {
	google.maps.event.trigger(map, 'resize');
}

google.maps.event.addDomListener(window, 'load', mapInitialize);

function mapInitialize() {
	map = new google.maps.Map(document.getElementById('map'), {
		// TODO: use visualization property
		center: new google.maps.LatLng(0, 0),
		zoom: 3,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		},
	});

	gfusionLayer = new google.maps.FusionTablesLayer();
	updateLayerQuery("Jan 2000");
	gfusionLayer.setMap(map);
}

function updateLayerQuery(milestone, valuable) {
	var where = "MilestoneRep = '" + milestone + "'";
	gfusionLayer.setOptions({
		query: {
			select: 'Position',
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