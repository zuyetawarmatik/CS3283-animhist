var viProps;
var map, gfusionLayer;
var playingTimer;
var playingTimeline;
var currentTimelineMilestoneId;

function retrieveVisualizationProperty() {
	$.ajax({
		url: getPOSTURLPrefix() + "/info?request=property",
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
		url: getPOSTURLPrefix() + "/info?request=timeline",
		type: "GET",
		global: false,
		success: function(response) {
			$("#timeline-list").empty();
			
			playingTimeline = response;
			
			for (var i = 0; i < playingTimeline.length; i++) {
				$("<li class='timeline-item'>" + playingTimeline[i] + "</li>").appendTo("#timeline-list");
			}
			
			$("#timeline-list").attr("data-milestone", playingTimeline[currentTimelineMilestoneId = 0]);
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
	$("#description-area .editable .repos-a").on("click", function() {
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
	$("#play-btn").attr("data-is-playing", $("#play-btn").attr("data-is-playing") == "false" ? "true" : "false");
}

$(function() {
	$("#timeline-list").attrchange({
		trackValues: true, 
		callback: function (event) {
			if (event.attributeName == "data-milestone") {
				$(".timeline-item.focused").removeClass("focused");
				currentTimelineMilestoneId = $.inArray(event.newValue, playingTimeline);
				$(".timeline-item:nth-child(" + currentTimelineMilestoneId + ")").addClass("focused");
				updateLayerQuery(event.newValue);
			}
		}
	});
	
	$("#play-btn").attrchange({
		trackValues: true,
		callback: function (event) {
			if (event.attributeName == "data-is-playing") {
				if (event.newValue == "true") {
					$(this).html("<i>&#57611;</i>");
					playingTimer = window.setInterval(function() {
						var nextTimelineMilestoneId = (currentTimelineMilestoneId + 1) % playingTimeline.length;
						$("#timeline-list").attr("data-milestone", playingTimeline[nextTimelineMilestoneId]);
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
	$("#comment-list").on("click", "a",
		function(e) {
			e.preventDefault();
			parent.changeIFrameSrc($(this).attr("href"), true);
		}
	);
	
	$("#play-btn").click(function() {
		if (playingTimeline.length > 0) {
			togglePlayVisualization();
		}
	});
	
	$("#timeline-list").on("click", ".timeline-item", function() {
		if (!$(this).hasClass("focused")) {
			$("#timeline-list").attr("data-milestone", $(this).html());
		}
	});
	
	$("#edit-visualization-btn").click(function() {
		parent.changeIFrameSrc($(this).data('url'), true);
	});
	
	$('#follow-btn').click(function() {
		var link = $(this).data('url');
		$.ajax({
			url: link,
			type: "POST",
			global: false,
			headers: {'X-CSRF-Token': $("[name='_token']").val()},
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
					$('#follow-btn').html('<i>&#57551;</i>Unfollow The Author');
					$('#follow-btn').data('url', link.replace("/follow", "/unfollow"));
				} else {
					$('#follow-btn').html('<i>&#57552;</i>Follow The Author');
					$('#follow-btn').data('url', link.replace("/unfollow", "/follow"));
				}
			}
		});
	});
	
	$("[name='comment-form']").submit(function(e) {
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
	var commentArea = $("#comment-area");
	
	$("#comment-area-title").hover(function() {
		if (commentArea.hasClass("expanded"))
			$(this).html("&#57636;");
		else
			$(this).html("&#57632;");
	}, function() {
		$(this).html("12 comments");
	});
	
	$("#comment-area-title").click(function() {
		var visualArea = $("#visualization-area");
		if (commentArea.hasClass("expanded")) {
			visualArea.stop(true).animate({bottom: "60px"}, 400, mapResizeTrigger);
			commentArea.stop(true).animate({height: "60px"}, 400);
		} else {
			visualArea.stop(true).animate({bottom: "300px"}, 400, mapResizeTrigger);
			commentArea.stop(true).animate({height: "300px"}, 400);
		}
		commentArea.toggleClass("expanded");
	});
});

function getUserID() {
	return $("#visualization-area").data("user-id");
}

function getVisualizationID() {
	return $("#visualization-area").data("vi-id");
}

function getPOSTURLPrefix() {
	return "/" + getUserID() + "/visualization/" + getVisualizationID();
}