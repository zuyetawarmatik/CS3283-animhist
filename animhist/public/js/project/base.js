var map;

function mapResizeTrigger() {
	google.maps.event.trigger(map, 'resize');
}

$(function() {
	/* Left sidebar animation */
	$(".nav-list > li").prepend('<span class="nav-bck"></span>');
	$(".nav-list > li:not(.selected)").hover(
		function() {
			$(".nav-bck", this).stop(true).animate({left: "0", opacity: "1"}, 400, "easeOutQuad");
		},
		function() {
			$(".nav-bck", this).stop(true).animate({left: "-100%", opacity: "0"}, 400, "easeOutQuad");
		}
	);
	
	/* Right sidebar show-hide */
	var rightSidebar = $("#right-area");
	var mainArea = $("#left-area");
	$("#right-area-showhide-btn").click(function() {
		if (rightSidebar.hasClass("hidden")) {
			rightSidebar.stop(true).animate({width: "440px"}, 400, "easeOutQuad");
			mainArea.stop(true).animate({right: "440px"}, 400, "easeOutQuad", mapResizeTrigger);
			$(this).html("&#57477;");
		} else {
			rightSidebar.stop(true).animate({width: "0"}, 400, "easeOutQuad");
			mainArea.stop(true).animate({right: "0"}, 400, "easeOutQuad", mapResizeTrigger);
			$(this).html("&#57528;");
		}
		rightSidebar.toggleClass("hidden");
	});
	
});