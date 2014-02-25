$(function() {
	$('#create-visualization-btn').click(function() {
		parent.changeIFrameSrc($(this).data('url'), true);
	});
	
	$('#follow-btn').click(function() {
		var link = $(this).data('url');
		$.ajax({
			url: link,
			type: "POST",
			global: false,
			headers: {'X-CSRF-Token': $("[name='hidden-form'] [type='hidden']").val()},
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
				if (responseData["redirect"]) {
					handleJSONRedirectResponse(responseData, true);
					return;
				}
				
				if (link.substr(link.length - 7, link.length) === "/follow") {
					$('#follow-btn').html('<i>&#57551;</i>Unfollow The Author');
					$('#follow-btn').data('url', link.replace("follow", "unfollow"));
				} else {
					$('#follow-btn').html('<i>&#57552;</i>Follow The Author');
					$('#follow-btn').data('url', link.replace("unfollow", "follow"));
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