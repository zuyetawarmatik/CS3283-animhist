function search(query) {
	$.ajax({
		url: $("[name='search-form']")[0].action,
		type: "POST",
		data: {q: query},
		global: false,
		success: function(response) {
			$("#visualization-list").empty();
			$.each(response, function(i, vi) {
				$thisViLi = $("<li class='visualization-item' data-vi-category='" + vi.category + "'>\
									<div class='overlay' style='display:none' data-url='" + vi.viewURL + "'>\
										<p><span class='h2'>Created at: </span>" + vi.createdAt + "</p>\
										<p><span class='h2'>Last Updated at: </span>" + vi.updatedAt + "</p>\
										<div class='visualization-buttons'>\
											<a href='" + vi.viewURL + "'>&#57542;</a>\
										</div>\
									</div>\
									<div class='visualization-img'><img src='" + vi.imgURL + "'/></div>\
									<div class='avatar-wrapper'>\
										<a href='" + vi.userURL + "'><img class='avatar' src='" + vi.userAvatarURL + "' /></a>\
									</div>\
									<div class='visualization-main'>\
										<p class='visualization-title'>" + vi.displayName + "</p>\
										<p class='visualization-author'><a href='" + vi.userURL + "' class='username'>" + vi.userDisplayName + "</a></p>\
									</div>\
								</li>");
				$thisViLi.appendTo("#visualization-list");
			});
		}
	});
}

function initialSearch() {
	var q = getUrlParameters("q", window.top.location.href, true);
	search(q);
}

$(function() {
	initialSearch();
	$("[name='search-form']").submit(function(event) {
		event.preventDefault();
		search($("[name='search-box']").val());
	});
});