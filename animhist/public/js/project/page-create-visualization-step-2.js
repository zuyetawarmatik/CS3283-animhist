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
				updateLayerQuery($("#timeline-list").attr("data-milestone"));
			} else if (newCurrent == 1) {
				slickGrid.resizeCanvas();
			}
		}
	});
});

$(function() {
	$("#description-area p.editable").append("<a class='edit-a'>&#57350;</a>");
});

function getCSRFToken() {
	return $("[name='hidden-form'] [name='_token']").val();
}