$(function() {
	$('#create-visualization-btn').click(function() {
		parent.changeIFrameSrc($(this).data('url'), true);
	});
	
	$('#follow-btn').click(function() {
		$.ajax({
			url: $(this).data('url'),
			type: "POST",
			error: function(responseData) {
				var alertSt = "";
				$.each(responseData["responseJSON"]["error"], function(key, val) {
					$.each(val, function(index, tx) {
						alertSt += tx + "<br/>";
					});
				});
				noty({
					layout: 'bottomCenter',
					text: alertSt,
					type: 'error',
					killer: true,
					timeout: 2000,
					maxVisible: 1
				});
			},
		
			success: function(responseData) {
				//handleJSONRedirectResponse(responseData, false);
			}
		});
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