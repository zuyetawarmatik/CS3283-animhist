var CSRFToken;

$(function() {
	CSRFToken = $("[name='_token']").val();
	$followBtn = $('#follow-btn');
	$numFollowers = $("#num-followers");
	$categoryArea = $("#category-area");
	$descriptionArea = $("#description-area");
});

$(function() {
	$('#create-visualization-btn').click(function() {
		parent.changeIFrameSrc($(this).data('url'), true);
	});
	
	$("#edit-profile-btn").click(function() {
		parent.changeIFrameSrc($(this).data('url'), true);
	});
	
	$followBtn.click(function() {
		var link = $(this).data('url');
		$.ajax({
			url: link,
			type: "POST",
			global: false,
			headers: {'X-CSRF-Token': CSRFToken},
			error: function(response) {
				notyError({
					text: "Error"
				});
			},
			success: function(response) {
				if (response["redirect"]) {
					handleJSONRedirectResponse(response, true);
					return;
				}
				
				if (link.substr(link.length - 7, link.length) === "/follow") {
					$followBtn.html('<i>&#57555;</i>Unfollow The Author')
								.data('url', link.replace("/follow", "/unfollow"));
				} else {
					$followBtn.html('<i>&#57553;</i>Follow The Author')
								.data('url', link.replace("/unfollow", "/follow"));
				}
				
				$numFollowers.html(response["numFollowers"]);
			}
		});
	});
	
	$categoryArea.hide();
	
	$rightAreaTab.find("li").click(function() {
		$rightAreaTab.find("li.selected").removeClass("selected");
		if ($(this).attr("id") == "right-area-tab-info") {
			$categoryArea.hide();
			$descriptionArea.show();
		} else if ($(this).attr("id") == "right-area-tab-category") {
			$descriptionArea.hide();
			$categoryArea.show();
		}
		$(this).addClass("selected");
	});
});