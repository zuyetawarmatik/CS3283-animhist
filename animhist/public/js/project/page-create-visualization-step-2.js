$(function() {
	$("#tab li a").click(function() {
		if (!$(this).hasClass("current")) {
			var newCurrent = $(this).parent().index();
			$("#edit-area .current").removeClass("current");
			$("#right-area .current").removeClass("current");
			
			$("a", "#tab li:nth-child(" + (newCurrent + 1) + ")").addClass("current");
			$("#edit-area>div:nth-child(" + (newCurrent + 3) + ")").addClass("current");
			$("#right-area>div:nth-child(" + (newCurrent + 1) + ")").addClass("current");
			
			if (newCurrent == 0) {
				if (map !== undefined) {
					var curCenter = map.getCenter();
					google.maps.event.trigger(map, 'resize');
					map.setCenter(curCenter);
					updateLayerQuery($("#timeline-list").attr("data-milestone"));
				}
				
				$("#button-area").addClass("current");
			} else if (newCurrent == 1) {
				pauseVisualization();
				slickGrid.resizeCanvas();
				$("#button-area").removeClass("current");
			} else if (newCurrent == 2) {
				pauseVisualization();
				styleSlickGrid.resizeCanvas();
				$("#button-area").removeClass("current");
			}
		}
	});
});

$(function() {
	$("#description-area p.editable").append("<a class='edit-a'>&#57350;</a>");
	$("#description-area h1.editable").prepend("<a class='edit-a'>&#57350;</a>");
	
	$("#description-area #zoom").append("<a class='repos-a'>&#57475;</a>");
	$("#description-area #center").append("<a class='repos-a'>&#57475;</a>");
});

function getCSRFToken() {
	return $("[name='hidden-form'] [name='_token']").val();
}

function getUserID() {
	return $("#edit-area").data("user-id");
}

function getVisualizationID() {
	return $("#edit-area").data("vi-id");
}

function getPOSTURLPrefix() {
	return "/" + getUserID() + "/visualization/" + getVisualizationID();
}