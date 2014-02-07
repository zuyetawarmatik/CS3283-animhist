var map;

function mapResizeTrigger() {
	google.maps.event.trigger(map, 'resize');
}

function getUrlParameters(parameter, staticURL, decode) {
	var currLocation = (staticURL.length) ? staticURL : window.location.search;
	var parArr;
	if (currLocation.split("?").length > 1)
		parArr = currLocation.split("?")[1].split("&");
	else return false; 
	
	var	returnBool = true;	
	for (var i = 0; i < parArr.length; i++) {
		parr = parArr[i].split("=");
		if (parr[0] == parameter) {
			return (decode) ? decodeURIComponent(parr[1]) : parr[1];
			returnBool = true;
		} else {
			returnBool = false;
		}
	}

	if (!returnBool) return false;
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
			rightSidebar.stop(true).animate({right: "-480px"}, 400, "easeOutQuad");
			mainArea.stop(true).animate({right: "-40px"}, 400, "easeOutQuad", mapResizeTrigger);
			$(this).html("&#57528;");
		}
		rightSidebar.toggleClass("hidden");
	});
	
	/* Right category area animation */
	$("#category-list > li").prepend('<span class="category-bck"></span>');
	$("#category-list > li:not(.selected)").hover(
		function() {
			$(".category-bck", this).stop(true).animate({left: "0", opacity: "1"}, 300, "easeOutQuad");
		},
		function() {
			$(".category-bck", this).stop(true).animate({left: "100%", opacity: "0"}, 300, "easeOutQuad");
		}
	);
	
	/* Back button click */
	$("#back-btn").click(function() {
		parent.changeIFrameSrcOrdinary($(this).data("url"));
	});
});