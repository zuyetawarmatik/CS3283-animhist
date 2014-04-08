var CSRFToken;

$(function() {
	CSRFToken = $("[name='_token']").val();	
	$visualizationList = $("#visualization-list");
	$visualizationInfoP = $(".visualizations-info p");
	$numVisualizations = $("#num-visualizations .content");
	$numPubVisualizations = $("#num-pub-visualizations .content");
	$categoryList = $("#category-list");
});

$(function() {
	$visualizationList.on({
		mouseenter: function() {
			$("div.overlay", this).stop(true).show('fade', 400);
		},
		mouseleave: function() {
			$("div.overlay", this).hide('fade', 400);
		}
	}, "li.visualization-item");
	
	$visualizationList.on("click", "div.overlay",
		function() {
			parent.changeIFrameSrc($(this).data("url"), true);
		}
	);
	
	$visualizationList.on("click", "a:not(.del)",
		function(e) {
			e.preventDefault();
			e.stopPropagation();
			parent.changeIFrameSrc($(this).attr("href"), true);
		}
	);
	
	$visualizationList.on("click", "a.del",
		function(e) {
			e.preventDefault();
			e.stopPropagation();
			$this = $(this);
			$.ajax({
				url: $this.data("url"),
				type: "DELETE",
				headers: {'X-CSRF-Token': CSRFToken},
				error: function() {
					notyError({
						text: "Visualization deletion failed"
					});
				},
				success: function(response) {
					notySuccess({
						text: "Visualization deleted",
						callback: {
							afterShow: function() {
								$thisLi = $this.closest("li.visualization-item");
								
								$numVisualizations.html($numVisualizations.html() - 1);
								if (!$thisLi.hasClass("unpublished")) $numPubVisualizations.html($numPubVisualizations.html() - 1);
								
								var category = $thisLi.data("vi-category");
								$thisLi.fadeOut(500, function(){$thisLi.remove();});
								var sameCatCount = $("li.visualization-item[data-vi-category='" + category + "']").length;
								if (sameCatCount == 0) {
									$("li.category-item").each(function() {
										if ($(".category-caption", $(this)).html() == category) {
											$(this).remove();
											return false;
										}
									});
								}
							}
						}
					});
				}
			});
		}
	);
	
	/* Right category area animation */
	$("#category-list > li").prepend('<span class="category-bck"></span>');
	$(document).on({
		mouseenter: function() {
			$(".category-bck", this).stop(true).animate({left: "0", opacity: "1"}, 300, "easeOutQuad");
		},
		mouseleave: function() {
			$(".category-bck", this).stop(true).animate({left: "100%", opacity: "0"}, 300, "easeOutQuad");
		}
	}, "#category-list > li:not(.selected)");
	
	/* Right category click */
	$categoryList.on("click", ".category-item", function() {
		$("li.category-item.selected .category-bck").attr("style", "");
		$("li.category-item.selected").removeClass("selected");
		
		$(this).addClass("selected");
		
		var category = $(".category-caption", this).html();
		var username = $visualizationInfoP.data("username");
		if (category == "All") {
			$("li.visualization-item").show();
			if (username !== undefined)
				$visualizationInfoP.html(username + "'s all visualizations:");
			else
				$visualizationInfoP.html("Showing all visualizations:");
		} else {
			$("li.visualization-item:not([data-vi-category='" + category + "'])").hide();
			$("li.visualization-item[data-vi-category='" + category + "']").show();
			if (username !== undefined)
				$visualizationInfoP.html(username + "'s visualizations of category <span style='font-style:italic'>" + category + "</span>:");
			else
				$visualizationInfoP.html("Showing visualizations of category <span style='font-style:italic'>" + category + "</span>:");
		}
	});
});