$(function() {
	$('#create-visualization-btn').click(function() {
		parent.changeIFrameSrc($(this).data('url'), true);
	});
	
	$("#category-area").hide();
	
	$("#right-area-tab li").click(function() {
		$("#right-area-tab li.selected").removeClass("selected");
		if ($(this).attr("id") == "right-area-tab-info") {
			$("#category-area").hide();
			$("#description-area").show();
		} else if ($(this).attr("id") == "right-area-tab-category") {
			$("#description-area").hide();
			$("#category-area").show();
		}
		$(this).addClass("selected");
	});
});