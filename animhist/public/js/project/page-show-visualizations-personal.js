$(function() {
	$('#create-visualization-btn').click(function() {
		parent.changeIFrameSrc($(this).data('url'), true);
	});
	
	$('#follow-btn').click(function() {
		$.ajax({
			url: $(this).data('url'),
			type: "POST",
			global: false,
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
				var text = $(this).data('url');
				if (text.substr(text.length - 7, 7) === "/follow")
				// check $(this).data('url') whether having "/follow" at the end
					$('#follow-btn').text('Unfollow The Author');
				else
					$('#follow-btn').text('Follow The Author');
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