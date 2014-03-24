$(function() {
	var commentArea = $("#comment-area");
	
	$("#comment-area-title").hover(function() {
		if (commentArea.hasClass("expanded"))
			$(this).html("&#57636;");
		else
			$(this).html("&#57632;");
	}, function() {
		$(this).html("12 comments");
	});
	
	$("#comment-area-title").click(function() {
		var visualArea = $("#visualization-area");
		if (commentArea.hasClass("expanded")) {
			visualArea.stop(true).animate({bottom: "60px"}, 400, mapResizeTrigger);
			commentArea.stop(true).animate({height: "60px"}, 400);
		} else {
			visualArea.stop(true).animate({bottom: "300px"}, 400, mapResizeTrigger);
			commentArea.stop(true).animate({height: "300px"}, 400);
		}
		commentArea.toggleClass("expanded");
	});
});
