$(function() {
	$searchForm = $("[name='search-form']");
	$searchBox = $("[name='search-box']");
});

function search(query) {
	$.ajax({
		url: $searchForm[0].action,
		type: "POST",
		data: {q: query},
		beforeSend: function() {
			$visualizationList.empty();
			$visualizationInfoP.html("Searched results for <span style='font-style:italic'>'" + query + "':</span>");
		},
		success: function(response) {
			$.noty.closeAll();
			$.each(response.visualizations, function(i, vi) {
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
				$thisViLi.appendTo($visualizationList);
			});
			
			$categoryList.find("li:not(:first-child)").remove();
			$categoryList.find("li:first-child").addClass("selected");
			$.each(response.categories, function(i, cat) {
				$("<li class='category-item'><span class='category-bck'></span><span class='category-caption'>" + cat + "</span></li>").appendTo($categoryList);
			});
		}
	});
}

function initialSearch() {
	var q = getUrlParameters("q", window.top.location.href, true);
	if (q !== false) search(q);
}

$(function() {
	initialSearch();
	$searchForm.submit(function(event) {
		event.preventDefault();
		search($searchBox.val());
	});
});