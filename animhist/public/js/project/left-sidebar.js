$(function() {
	$iframe = $('#main-panel').find('iframe');
	$navList = $("#nav-list");
	$navListLi = $("#nav-list > li");
});

/* Src with inputs */
function changeIFrameSrcOrdinary(src) {
	$iframe.attr("src", src);
	history.pushState(null, null, src.substr(0, src.indexOf("?")));
	
	// TODO: Check type of source to highlight respective sidebar item (change data-highlight-id)
}

/* Src without any input */
function changeIFrameSrc(src, backable) {
	var cs = src.split("?").length > 1 ? "&" : "?";
	if (backable) {
		/* Only used when clicking a link inside IFRAME */
		var oldURL = $('#main-panel iframe').attr("src");
		$iframe.attr("src", src + cs + "ajax=1&back=1&referer=" + oldURL);
	} else {
		$iframe.attr("src", src + cs + "ajax=1");
	}
	history.pushState(null, null, src);
	
	// TODO: Check type of source to highlight respective sidebar item (change data-highlight-id)
}

$(function() {
	/* Binding highlight-id attribute to highlight the sidebar item */
	$navList.attrchange({
		trackValues: true, 
		callback: function (event) {
			if (event.attributeName == "data-highlight-id") {
				$navListLiSelected = $("#nav-list > li.selected");
				$navListLiSelected.prev().removeClass("before-selected");
				$navListLiSelected.next().removeClass("after-selected");
				$navListLiSelected.find(".nav-bck").attr("style", "");
				$navListLiSelected.removeClass("selected");
				
				$("#nav-list > li:nth-child(" + event.newValue + ")").addClass("selected");
				$navListLiSelected = $("#nav-list > li.selected");
				$navListLiSelected.prev().addClass("before-selected");
				$navListLiSelected.next().addClass("after-selected");
			}
		}
	});
});

$(function() {
	/* Left sidebar at page loaded */
	$navListLiSelected = $("#nav-list > li.selected");
	$("#nav-list > li:nth-child(" + $navList.data("highlight-id") + ")").addClass("selected");
	$navListLiSelected.prev().addClass("before-selected");
	$navListLiSelected.next().addClass("after-selected");
	
	/* Left sidebar animation */
	$navListLi.prepend('<span class="nav-bck"></span>');
	$(document).on({
		mouseenter: function() {
			$(".nav-bck", this).stop(true).animate({left: "0", opacity: "1"}, 400, "easeOutQuad");
		},
		mouseleave: function() {
			$(".nav-bck", this).stop(true).animate({left: "-100%", opacity: "0"}, 400, "easeOutQuad");
		}
	}, "#nav-list > li:not(.selected)");
	
	/* Left sidebar navigation */
	$navListLi.on("click", function() {
		changeIFrameSrc($(this).data("url"), false);
		$navList.attr("data-highlight-id", $(this).index() + 1);
	});
	
	/* Log out */
	$("#logout-btn").click(function() {
		$.ajax({
			url: "/user/logout",
			type: "POST",
			data: {"referer": window.location.href},
			success: function(data) {
				window.location.href = data["redirect"];
			}
		});
	});
});