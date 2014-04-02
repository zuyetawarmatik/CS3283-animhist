$(function() {
	$("#visualization-list").on({
		mouseenter: function() {
			$(".overlay", this).stop(true).show('fade', 400);
		},
		mouseleave: function() {
			$(".overlay", this).hide('fade', 400);
		}
	}, ".visualization-item");
	
	$("#visualization-list").on("click", ".overlay",
		function() {
			parent.changeIFrameSrc($(this).data("url"), true);
		}
	);
	
	$("#visualization-list").on("click", "a:not(.del)",
		function(e) {
			e.preventDefault();
			e.stopPropagation();
			parent.changeIFrameSrc($(this).attr("href"), true);
		}
	);
	
	$("#visualization-list").on("click", "a.del",
		function(e) {
			e.preventDefault();
			e.stopPropagation();
			$this = $(this);
			$.ajax({
				url: $this.data("url"),
				type: "DELETE",
				headers: {'X-CSRF-Token': $("[name='_token']").val()},
				global: false,
				error: function() {
					notyError({
						text: "Visualization deletion failed"
					});
				},
				success: function(response) {
					notySuccess({
						text: "Visualization deleted",
						layout: 'bottomCenter',
						callback: {
							afterShow: function() {
								$thisLi = $this.closest(".visualization-item");
								var category = $thisLi.data("vi-category");
								$thisLi.remove();
								var sameCatCount = $(".visualization-item[data-vi-category='" + category + "']").length;
								if (sameCatCount == 0)
									$(".category-item").each(function() {
										if ($(".category-caption", $(this)).html() == category) {
											$(this).remove();
											return false;
										}
									});
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
	$("#category-list").on("click", ".category-item", function() {
		$(".category-item.selected .category-bck").attr("style", "");
		$(".category-item.selected").removeClass("selected");
		
		$(this).addClass("selected");
		
		var category = $(".category-caption", this).html();
		var username = $(".visualizations-info p").data("username");
		if (category == "All") {
			$(".visualization-item").show();
			if (username !== undefined)
				$(".visualizations-info p").html(username + "'s all visualizations:");
			else
				$(".visualizations-info p").html("Showing all visualizations:");
		} else {
			$(".visualization-item:not([data-vi-category='" + category + "'])").hide();
			$(".visualization-item[data-vi-category='" + category + "']").show();
			if (username !== undefined)
				$(".visualizations-info p").html(username + "'s visualizations of category <span style='font-style:italic'>" + category + "</span>:");
			else
				$(".visualizations-info p").html("Showing visualizations of category <span style='font-style:italic'>" + category + "</span>:");
		}
	});
});