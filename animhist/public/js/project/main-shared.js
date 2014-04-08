$(function() {
	$mainArea = $("#left-area");
	$rightSidebar = $("#right-area");
	$rightAreaTab = $("#right-area-tab");
});

function inArray(val, array) {
	return $.inArray(val, array) >= 0;
}

function getUrlParameters(parameter, staticURL, decode) {
	var currLocation = (staticURL.length) ? staticURL : window.location.search;
	var parArr;
	if (currLocation.split("?").length > 1)
		parArr = currLocation.split("?")[1].split("&");
	else return false; 
	
	var	returnBool = true;	
	for (var i = 0; i < parArr.length; i++) {
		parr = parArr[i].split("=");
		if (parr[0] == parameter) {
			return (decode) ? decodeURIComponent(parr[1]) : parr[1];
			returnBool = true;
		} else {
			returnBool = false;
		}
	}

	if (!returnBool) return false;
}

function handleJSONRedirectResponse(response, backable) {
	if (response["wholePage"])
		window.top.location.href = response["redirect"];
	else
		parent.changeIFrameSrc(response["redirect"], backable);
}

$(function() {
	/* Right sidebar show-hide */
	$("#right-area-showhide-btn").click(function() {
		var rightAreaWidth = $rightSidebar.width();
		var rightAreaTabWidth = $rightAreaTab.length ? $rightAreaTab.width() : 0;
		var callback = typeof mapResizeTrigger != "undefined" ? mapResizeTrigger : null;
		if ($rightSidebar.hasClass("hidden")) {
			$rightSidebar.stop(true).animate({right: "0"}, 400, "easeOutQuad");
			$mainArea.stop(true).animate({right: rightAreaWidth + "px"}, 400, "easeOutQuad", callback);
			$(this).html("&#57477;");
		} else {
			$rightSidebar.stop(true).animate({right: (-rightAreaWidth - rightAreaTabWidth) + "px"}, 400, "easeOutQuad");
			$mainArea.stop(true).animate({right: -rightAreaTabWidth + "px"}, 400, "easeOutQuad", callback);
			$(this).html("&#57528;");
		}
		$rightSidebar.toggleClass("hidden");
	});
	
	$(window).resize(function() {
		var rightAreaWidth = $rightSidebar.width();
		var rightAreaTabWidth = $rightAreaTab.length ? $rightAreaTab.width() : 0;
		if ($rightSidebar.hasClass("hidden")) {
			$rightSidebar.css({right: (-rightAreaWidth - rightAreaTabWidth) + "px"});
			$mainArea.css({right: -rightAreaTabWidth + "px"});
		} else {
			$mainArea.css({right: rightAreaWidth + "px"});
		}
	});
	
	/* Back button click */
	$("#back-btn").click(function() {
		parent.changeIFrameSrcOrdinary($(this).data("url"));
	});
	
	/* Topbar link click */
	$("#top-bar").on("click", "a",
		function(e) {
			e.preventDefault();
			e.stopPropagation();
			parent.changeIFrameSrc($(this).attr("href"), true);
		}
	);
});

$(document).ajaxStart(function() {
	noty({
		layout: 'center',
		text: 'Please wait...',
		type: 'information',
		animation: {
			open: {height: 'toggle'},
			close: {height: 'toggle'},
			easing: 'swing',
			speed: 300
		},
		maxVisible: 1
	});
});

function notyError(options) {
	var notyErrTemp = {
		layout: 'bottomCenter',
		type: 'error',
		killer: true,
		timeout: 1000,
		maxVisible: 1
	};
	
	$.each(options, function(key, val) {
		notyErrTemp[key] = val;
	});
	
	noty(notyErrTemp);
}

function notySuccess(options) {
	var notySuccTemp = {
		layout: 'center',
		type: 'success',
		killer: true,
		timeout: 500,
		maxVisible: 1
	};
	
	$.each(options, function(key, val) {
		notySuccTemp[key] = val;
	});
	
	noty(notySuccTemp);
}