var viProps;
var map, gfusionLayer;
var playingTimer;
var playingTimeline;
var currentTimelineMilestoneId;
var CSRFToken, postURLPrefix;

$(function() {
	CSRFToken = $("[name='_token']").val();
	
	$visualizationArea = $("#visualization-area");
	$commentArea = $("#comment-area");
	$descriptionArea = $("#description-area");
	$timelineList = $("#timeline-list");
	$commentList = $("#comment-list");
	$playBtn = $("#play-btn");
	$commentAreaTitle = $("#comment-area-title");
	$commentForm = $("[name='comment-form']");
	$followBtn = $('#follow-btn');
	
	var userID = $visualizationArea.data("user-id");
	var visualizationID = $visualizationArea.data("vi-id");
	postURLPrefix = "/" + userID + "/visualization/" + visualizationID;
});

function retrieveVisualizationProperty() {
	$.ajax({
		url: postURLPrefix + "/info?request=property",
		type: "GET",
		global: false,
		success: function(response) {
			viProps = response;
			$(window).trigger("vi_property_loaded");
		}
	});
}

function retrieveTimeline() {
	$.ajax({
		url: postURLPrefix + "/info?request=timeline",
		type: "GET",
		global: false,
		success: function(response) {
			$timelineList.empty();
			
			playingTimeline = response;
			
			for (var i = 0; i < playingTimeline.length; i++) {
				$("<li class='timeline-item'>" + playingTimeline[i] + "</li>").appendTo($timelineList);
			}
			
			$timelineList.attr("data-milestone", playingTimeline[currentTimelineMilestoneId = 0]);
		}
	});	
}

$(function() {
	retrieveVisualizationProperty();
});

$(window).on('vi_property_loaded', function() {
	mapInitialize();
	retrieveTimeline();
	updateLayerStyle();
});

$(function() {
	$descriptionArea.find(".repos-a").on("click", function() {
		if (map !== undefined) {
			var field = $(this).parent().attr("id");
			if (field == "zoom")
				map.setZoom(parseInt(viProps['zoom']));
			else if (field == "center")
				map.setCenter(new google.maps.LatLng(viProps['centerLatitude'], viProps['centerLongitude']));
		}
	});
});

function mapResizeTrigger() {
	var curCenter = map.getCenter();
	google.maps.event.trigger(map, 'resize');
	map.setCenter(curCenter);
}

function mapInitialize() {
	map = new google.maps.Map(document.getElementById('map'), {
		center: new google.maps.LatLng(viProps['centerLatitude'], viProps['centerLongitude']),
		zoom: parseInt(viProps['zoom']),
		mapTypeId: google.maps.MapTypeId.TERRAIN,
		zoomControlOptions: {
			style: google.maps.ZoomControlStyle.SMALL
		},
	});

	gfusionLayer = new google.maps.FusionTablesLayer();
	gfusionLayer.setMap(map);
	
	if (viProps['htmlData']) {
		google.maps.event.addListener(gfusionLayer, 'click', function(e) {
			// Change the content of the InfoWindow by using HTMLData
			e.infoWindowHtml = e.row['HTMLData'].value;
		});
	}
}

function updateLayerStyle() {
	if (viProps["defaultStyleId"] !== undefined)
		gfusionLayer.setOptions({"styleId": viProps["defaultStyleId"]});
}

function updateLayerQuery(milestone) {
	var select = 'Geocode'; 
	var where = "MilestoneRep = '" + milestone + "'";
	gfusionLayer.setOptions({
		query: {
			select: select,
			from: viProps["gfusionTableID"],
			where: where 
		}
	});
}

function togglePlayVisualization() {
	$playBtn.attr("data-is-playing", $playBtn.attr("data-is-playing") == "false" ? "true" : "false");
}

$(function() {
	$timelineList.attrchange({
		trackValues: true, 
		callback: function (event) {
			if (event.attributeName == "data-milestone") {
				$("li.timeline-item.focused").removeClass("focused");
				currentTimelineMilestoneId = $.inArray(event.newValue, playingTimeline);
				$("li.timeline-item:nth-child(" + currentTimelineMilestoneId + ")").addClass("focused");
				updateLayerQuery(event.newValue);
			}
		}
	});
	
	$playBtn.attrchange({
		trackValues: true,
		callback: function (event) {
			if (event.attributeName == "data-is-playing") {
				if (event.newValue == "true") {
					$(this).html("<i>&#57611;</i>");
					playingTimer = window.setInterval(function() {
						var nextTimelineMilestoneId = (currentTimelineMilestoneId + 1) % playingTimeline.length;
						$timelineList.attr("data-milestone", playingTimeline[nextTimelineMilestoneId]);
					}, 1000);
				} else if (event.newValue == "false") {
					$(this).html("<i>&#57610;</i>");
					window.clearInterval(playingTimer);
				}
			}
		}
	});
});

$(function() {
	$commentList.on("click", "a",
		function(e) {
			e.preventDefault();
			parent.changeIFrameSrc($(this).attr("href"), true);
		}
	);
	
	$playBtn.click(function() {
		if (playingTimeline.length > 0) {
			togglePlayVisualization();
		}
	});
	
	$timelineList.on("click", "li.timeline-item", function() {
		if (!$(this).hasClass("focused")) {
			$timelineList.attr("data-milestone", $(this).html());
		}
	});
	
	$("#edit-visualization-btn").click(function() {
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
				var alertSt = "";
				$.each(response["responseJSON"]["error"], function(key, val) {
					$.each(val, function(index, tx) {
						alertSt += tx + "<br/>";
					});
				});
				notyError({
					text: alertSt
				});
			},
			success: function(response) {
				if (response["redirect"]) {
					handleJSONRedirectResponse(response, true);
					return;
				}
				
				if (link.substr(link.length - 7, link.length) === "/follow") {
					$followBtn.html('<i>&#57551;</i>Unfollow The Author')
								.data('url', link.replace("/follow", "/unfollow"));
				} else {
					$followBtn.html('<i>&#57552;</i>Follow The Author')
								.data('url', link.replace("/unfollow", "/follow"));
				}
			}
		});
	});
	
	$commentForm.submit(function(e) {
		e.preventDefault();
		$.ajax({
			url: this.action,
			data: $(this).serialize(),
			type: "POST",
			global: false,
			error: function() {
				notyError({
					text: "Comment failed"
				});
			},
			success: function(response) {
				
			}
		});
	});
});

$(function() {
	var commentAreaTitle;
	$commentAreaTitle.hover(function() {
		commentAreaTitle = $(this).html();
		if ($commentArea.hasClass("expanded"))
			$(this).html("&#57636;");
		else
			$(this).html("&#57632;");
	}, function() {
		$(this).html(commentAreaTitle);
	});
	
	$commentAreaTitle.click(function() {
		if ($commentArea.hasClass("expanded")) {
			$visualizationArea.stop(true).animate({bottom: "6rem"}, 400, mapResizeTrigger);
			$commentArea.stop(true).animate({height: "6rem"}, 400);
		} else {
			$visualizationArea.stop(true).animate({bottom: "30rem"}, 400, mapResizeTrigger);
			$commentArea.stop(true).animate({height: "30rem"}, 400);
		}
		$commentArea.toggleClass("expanded");
	});
});