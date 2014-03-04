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
});