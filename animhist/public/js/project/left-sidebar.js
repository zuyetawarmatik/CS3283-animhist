function changeIFrameSrc(src, backable) {
	if (backable)
		$('#main-panel iframe').attr("src", src + "?ajax=1&back=1");
	else
		$('#main-panel iframe').attr("src", src + "?ajax=1");
	history.pushState(null, null, src);
}

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
		
		$("#nav-list > li.selected").prev().removeClass("before-selected");
		$("#nav-list > li.selected").next().removeClass("after-selected");
		$("#nav-list > li.selected .nav-bck").attr("style", "");
		$("#nav-list > li.selected").removeClass("selected");
		
		$(this).prev().addClass("before-selected");
		$(this).addClass("selected");
		$(this).next().addClass("after-selected");
	});
	
	/* Log out */
	$("#logout-btn").click(function() {
		$.ajax({
			url: "/user/logout",
			type: "POST",
			success: function(data) {
				window.location.href = data["redirect"];
			}
		});
	});
});