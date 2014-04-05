var CSRFToken, userID, visualizationID, postURLPrefix;

$(function() {
	CSRFToken = $("[name='hidden-form'] [name='_token']").val();
	
	$tabA = $("#tab").find("a");
	$defaultColumnSelect = $("#default-column-select");
	$columnList = $("#column-list");
	$descriptionArea = $("#description-area");
	$buttonArea = $("#button-area");
	$editArea = $("#edit-area");
	$editAreaStyle = $("#edit-area-style");
	$editAreaStyleSaveBtn = $editAreaStyle.find("#save-btn");
	$editAreaStyleDelBtn = $editAreaStyle.find("#row-delete-btn");
	$styleColumnSelect = $("#style-column-select");
	$filterList = $("#filter-list");
	$timelineList = $("#timeline-list");
	$editAreaTable = $("#edit-area-table");
	$editAreaTableAddBtn = $editAreaTable.find("#row-add-btn");
	$editAreaTableDelBtn = $editAreaTable.find("#row-delete-btn");
	$playBtn = $("#play-btn");
	$rightArea = $("#right-area");
	
	userID = $editArea.data("user-id");
	visualizationID = $editArea.data("vi-id");
	postURLPrefix = "/" + userID + "/visualization/" + visualizationID;
});

$(function() {
	$tabA.click(function() {
		if (!$(this).hasClass("current")) {
			var newCurrent = $(this).parent().index();
			$editArea.find(".current").removeClass("current");
			$rightArea.find(".current").removeClass("current");
			
			$("a", "#tab li:nth-child(" + (newCurrent + 1) + ")").addClass("current");
			$("#edit-area>div:nth-child(" + (newCurrent + 3) + ")").addClass("current");
			$("#right-area>div:nth-child(" + (newCurrent + 1) + ")").addClass("current");
			
			if (newCurrent == 0) {
				if (map !== undefined) {
					var curCenter = map.getCenter();
					google.maps.event.trigger(map, 'resize');
					map.setCenter(curCenter);
					updateLayerQuery($timelineList.attr("data-milestone"));
				}
				
				$buttonArea.addClass("current");
			} else if (newCurrent == 1) {
				pauseVisualization();
				slickGrid.resizeCanvas();
				$buttonArea.removeClass("current");
			} else if (newCurrent == 2) {
				pauseVisualization();
				styleSlickGrid.resizeCanvas();
				$buttonArea.removeClass("current");
			}
		}
	});
});

$(function() {
	$descriptionArea.find("p.editable").append("<a class='edit-a'>&#57350;</a>");
	$descriptionArea.find("h1.editable").prepend("<a class='edit-a'>&#57350;</a>");
	
	$descriptionArea.find("#zoom").append("<a class='repos-a'>&#57475;</a>");
	$descriptionArea.find("#center").append("<a class='repos-a'>&#57475;</a>");
});