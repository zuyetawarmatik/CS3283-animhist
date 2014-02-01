/* Src with inputs */
function changeIFrameSrcOrdinary(src) {
	$('#main-panel iframe').attr("src", src);
	history.pushState(null, null, src.substr(0, src.indexOf("?")));
	
	// TODO: Check type of source to highlight respective sidebar item (change data-highlight-id)
}

/* Src without any input */
function changeIFrameSrc(src, backable) {
	if (backable) {
		/* Only used when clicking a link inside IFRAME */
		var oldURL = $('#main-panel iframe').attr("src");
		$('#main-panel iframe').attr("src", src + "?ajax=1&back=1&referer=" + oldURL);
	} else {
		$('#main-panel iframe').attr("src", src + "?ajax=1");
	}
	history.pushState(null, null, src);
	
	// TODO: Check type of source to highlight respective sidebar item (change data-highlight-id)
}

$(function() {
	/* Binding highlight-id attribute to highlight the sidebar item */
	$("#nav-list").attrchange({
		trackValues: true, 
		callback: function (event) {
			if (event.attributeName == "data-highlight-id") {
				$("#nav-list > li.selected").prev().removeClass("before-selected");
				$("#nav-list > li.selected").next().removeClass("after-selected");
				$("#nav-list > li.selected .nav-bck").attr("style", "");
				$("#nav-list > li.selected").removeClass("selected");
				
				$("#nav-list > li:nth-child(" + event.newValue + ")").addClass("selected");
				$("#nav-list > li.selected").prev().addClass("before-selected");
				$("#nav-list > li.selected").next().addClass("after-selected");
			}
		}
	});
});

$(function() {
	/* Left sidebar at page loaded */
	$("#nav-list > li:nth-child(" + $("#nav-list").data("highlight-id") + ")").addClass("selected");
	$("#nav-list > li.selected").prev().addClass("before-selected");
	$("#nav-list > li.selected").next().addClass("after-selected");
	
	/* Left sidebar animation */
	$("#nav-list > li").prepend('<span class="nav-bck"></span>');
	$("#nav-list > li").hover(
		function() {
			if (!$(this).hasClass("selected"))
				$(".nav-bck", this).stop(true).animate({left: "0", opacity: "1"}, 400, "easeOutQuad");
		},
		function() {
			if (!$(this).hasClass("selected"))
				$(".nav-bck", this).stop(true).animate({left: "-100%", opacity: "0"}, 400, "easeOutQuad");
		}
	);
	
	/* Left sidebar navigation */
	$("#nav-list > li").on("click", function() {
		changeIFrameSrc($(this).data("url"), false);
		$("#nav-list").attr("data-highlight-id", $(this).index() + 1);
	});
	
	/* Log out */
	$("#logout-btn").click(function() {
		$.ajax({
			url: "/user/logout",
			type: "POST",
			data: {"referer": window.location.href},
			success: function(data) {
				window.location.href = data["redirect"];
			}
		});
	});
});