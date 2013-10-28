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
});