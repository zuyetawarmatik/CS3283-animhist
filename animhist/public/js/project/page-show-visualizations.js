$(function() {
	$("#visualization-list").on({
		mouseenter: function() {
			$(".overlay", this).stop(true).show('fade', 400);
		},
		mouseleave: function() {
			$(".overlay", this).hide('fade', 400);
		}
	}, ".visualization-item");
	
	$("#visualization-list .overlay").on("click",
		function() {
			parent.changeIFrameSrc($(this).data("url"), true);
		}
	);
	
	$("#visualization-list a:not(.del)").on("click",
		function(e) {
			e.preventDefault();
			e.stopPropagation();
			parent.changeIFrameSrc($(this).attr("href"), true);
		}
	);
	
	$("#visualization-list a.del").on("click",
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
								$this.closest(".visualization-item").remove();
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
	$(".category-item").on("click", function() {
		$(".category-item.selected .category-bck").attr("style", "");
		$(".category-item.selected").removeClass("selected");
		
		$(this).addClass("selected");
		
		var category = $(".category-caption", this).html();
		var username = $(".visualizations-info p").data("username");
		if (category == "All") {
			$(".visualization-item").show();
			$(".visualizations-info p").html(username + "'s all visualizations");
		} else {
			$(".visualization-item:not([data-vi-category='" + category + "'])").hide();
			$(".visualization-item[data-vi-category='" + category + "']").show();
			$(".visualizations-info p").html(username + "'s visualizations of category " + category);
		}
	});
});