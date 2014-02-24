$(function() {
	$('#create-visualization-btn').click(function() {
		parent.changeIFrameSrc($(this).data('url'), true);
	});
	
	$('#follow-btn').click(function() {
		var text = String($(this).data('url'));
		$.ajax({
			url: $(this).data('url'),
			type: "POST",
			global: false,
			error: function(responseData) {
				/*
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
				});*/
			},
		
			success: function(responseData) {
				if (text.substr(text.length - 7, text.length) === "/follow") {
					$('#follow-btn').html('<i>&#57552;</i>Unfollow The Author');
					//console.log(text.replace("follow","unfollow"));
					$('#follow-btn').data('url', text.replace("follow","unfollow"));
				} else {
					$('#follow-btn').html('<i>&#57552;</i>Follow The Author');
					//console.log(text.replace("unfollow","follow"));
					$('#follow-btn').data('url', text.replace("unfollow","follow"));
				}
					
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