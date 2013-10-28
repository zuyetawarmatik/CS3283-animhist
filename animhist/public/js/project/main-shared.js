var map;

function mapResizeTrigger() {
	google.maps.event.trigger(map, 'resize');
}

$(function() {
	/* Right sidebar show-hide */
	var rightSidebar = $("#right-area");
	var mainArea = $("#left-area");
	$("#right-area-showhide-btn").click(function() {
		if (rightSidebar.hasClass("hidden")) {
			rightSidebar.stop(true).animate({right: "0"}, 400, "easeOutQuad");
			mainArea.stop(true).animate({right: "440px"}, 400, "easeOutQuad", mapResizeTrigger);
			$(this).html("&#57477;");
		} else {
			rightSidebar.stop(true).animate({right: "-440px"}, 400, "easeOutQuad");
			mainArea.stop(true).animate({right: "0"}, 400, "easeOutQuad", mapResizeTrigger);
			$(this).html("&#57528;");
		}
		rightSidebar.toggleClass("hidden");
	});
});
