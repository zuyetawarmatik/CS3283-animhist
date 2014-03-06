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
				google.maps.event.trigger(map, 'resize');
			} else if (newCurrent == 1) {
				slickGrid.resizeCanvas();
			}
		}
	});
});