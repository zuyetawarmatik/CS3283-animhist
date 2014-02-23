var viProps;
var columnList;

var ajaxTemplate;
$(function(){
	ajaxTemplate = {
		processData: false,
	    contentType: "application/json; charset=utf-8",
	    url: "/" + $("#edit-area").data("user-id") + "/visualization/" + $("#edit-area").data("vi-id") + "/updatetable",
		type: "POST",
		headers: {'X-CSRF-Token': $("[name='hidden-form'] [type='hidden']").val()},
		global: false,
		beforeSend: function() {
			noty({
				layout: 'bottomCenter',
				text: '.................',
				type: 'information',
				animation: {
					open: {height: 'toggle'},
					close: {height: 'toggle'},
					easing: 'swing',
				    speed: 300
				},
				maxVisible: 1
			});
		},
		error: function(responseData) {
			noty({
				layout: 'bottomCenter',
				text: "Updating data error, rolling back...",
				type: 'error',
				killer: true,
				timeout: 500,
				maxVisible: 1
			});
		}
	};
});

function retrieveVisualizationProperty() {
	$.ajax({
		processData: false,
	    contentType: false,
		url: "/" + $("#edit-area").data("user-id") + "/visualization/" + $("#edit-area").data("vi-id") + "/info?request=property",
		type: "GET",
		headers: {'X-CSRF-Token': $("[name='hidden-form'] [type='hidden']").val()},
		global: false,
		success: function(responseData) {
			viProps = responseData;
			columnList = responseData["columnList"];
			addColumnListButtons();
		}
	});
}

function addColumnListButtons() {
	$("#column-list").empty();
	
	$.each(columnList, function(i, obj) {
		var caption = obj["caption"] + " (" + obj["type-caption"] + ")";
		var element = $(document.createElement("li")).addClass("btn-group").html("<button>" + caption + "</button>");
		
		if (obj["editable"])
			$("<button class='column-edit-btn'>&#57350;</button>").appendTo(element);
		
		if (obj["deletable"])
			$("<button class='column-delete-btn'>&#57597;</button>").appendTo(element);
		else
			if (!obj["disable"]) element.addClass("red-btn-group");
		
		if (obj["disable"]) {
			if (obj["disabled"]) {
				element.addClass("grey-btn-group");
				$("<button class='column-disable-btn'>&#57657;</button>").appendTo(element);
			} else
				$("<button class='column-disable-btn'>&#57656;</button>").appendTo(element);
		}
		
		element.appendTo("#column-list");
	});
	
	$(".red-btn-group button").each(function() {
		$(this).addClass("red-btn");
	});
	
	$(".grey-btn-group button").each(function() {
		$(this).addClass("grey-btn");
	});
	
	$("<li class='btn-group' id='column-add-btn-group'><button class='column-add-btn grey-btn'>&#57602;</button></li>").appendTo("#column-list");
}

$(function() {
	retrieveVisualizationProperty();
});

$(function() {
	$("#column-list").on("click", ".column-delete-btn", function() {
		var index = $(this).parent().index();
		var ajaxVar = $.extend({}, ajaxTemplate, {
			data: JSON.stringify({
				type: "column-delete",
				col: columnList[index]["column-id"]
			}),
			success: function(responseData) {
				noty({
					layout: 'bottomCenter',
					text: "Column removed, refreshing page...",
					type: 'success',
					killer: true,
					timeout: 500,
					maxVisible: 1,
					callback: {
						afterShow: function() {
							window.location.reload();
						}
					}
				});
			}
		});
		
		$.ajax(ajaxVar);
	});
	
	$("#column-list").on("click", ".column-disable-btn", function() {
		var index = $(this).parent().index();
		if (!columnList[index]["disabled"]) {
			var ajaxVar = $.extend({}, ajaxTemplate, {
				data: JSON.stringify({
					type: "column-delete",
					col: columnList[index]["column-id"]
				}),
				success: function(responseData) {
					noty({
						layout: 'bottomCenter',
						text: "Column removed, refreshing page...",
						type: 'success',
						killer: true,
						timeout: 500,
						maxVisible: 1,
						callback: {
							afterShow: function() {
								window.location.reload();
							}
						}
					});
				}
			});
			$.ajax(ajaxVar);
		} else;
	});
});
