$(function() {
	$("#tab li a").click(function() {
		if (!$(this).hasClass("current")) {
			var newCurrent = $(this).parent().index();
			$("#edit-area .current").removeClass("current");
			
			$("a", "#tab li:nth-child(" + (newCurrent + 1) + ")").addClass("current");
			$("#edit-area>div:nth-child(" + (newCurrent + 3) + ")").addClass("current");
		}
	});
});