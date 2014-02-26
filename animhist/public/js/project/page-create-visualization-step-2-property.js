var viProps;
var columnList;

var ajaxTemplate2, notySuccessTemplate2;

$(function(){
	ajaxTemplate2 = {
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
	
	notySuccessTemplate2 = {
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
	}
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
		var ajaxVar = $.extend({}, ajaxTemplate2, {
			data: JSON.stringify({
				type: "column-delete",
				col: columnList[index]["column-id"]
			}),
			success: function(responseData) {
				var notySuccessVar = $.extend({}, notySuccessTemplate2);
				noty(notySuccessVar);
			}
		});
		
		$.ajax(ajaxVar);
	});
	
	$("#column-list").on("click", ".column-disable-btn", function() {
		var index = $(this).parent().index();
		if (!columnList[index]["disabled"]) {
			var ajaxVar = $.extend({}, ajaxTemplate2, {
				data: JSON.stringify({
					type: "column-delete",
					col: columnList[index]["column-id"]
				}),
				success: function(responseData) {
					var notySuccessVar = $.extend({}, notySuccessTemplate2);
					noty(notySuccessVar);
				}
			});
			$.ajax(ajaxVar);
		} else {
			var exit = false;
			$.each(columnList, function(i, obj) {
				if (obj["caption"].toLowerCase() == "htmldata" && obj["disabled"] == false) exit = true;
			});
			if (exit) return;
			
			var ajaxVar = $.extend({}, ajaxTemplate2, {
				data: JSON.stringify({
					type: "column-insert",
					col: "HTMLData",
					colType: "STRING"
				}),
				success: function(responseData) {
					var notySuccessVar = $.extend({}, notySuccessTemplate2, {
						text: "Column added, refreshing page..."
					});
					noty(notySuccessVar);
				}
			});
			
			$.ajax(ajaxVar);
		}
	});
	
	$("#column-list").on("click", ".column-add-btn", function() {
		vex.dialog.open({
			message: "Add a new column",
			input: "<table>" +
						"<tr>" +
							"<td>" +
								"<label for='columnname'>Column name:</label>" +
							"</td>" +
							"<td>" +
								"<input name='columnname' type='text'/>" +
							"</td>" +
						"</tr>" +
						"<tr>" +
							"<td>" +
								"<label for='columntype'>Column type:</label>" +
							"</td>" +
							"<td>" +
								"<div class='styled-select'><select name='columntype'>" +
									"<option value='String'>String</option><option value='Number'>Number</option>" +
								"</select></div>" +
							"</td>" +
						"</tr>" +
					"</table>",
			callback: function(data) {
				if (!data.columnname) return;
				var columnName = data.columnname.trim();
				if (columnName != "" && columnName.match(/^[a-z0-9\-\s]+$/i)) {
					var exit = false;
					$.each(columnList, function(i, obj) {
						if (columnName.toLowerCase() == obj["caption"].toLowerCase()) exit = true;
					});
					if (exit) return;
					
					var ajaxVar = $.extend({}, ajaxTemplate2, {
						data: JSON.stringify({
							type: "column-insert",
							col: columnName,
							colType: data.columntype.toUpperCase()
						}),
						success: function(responseData) {
							var notySuccessVar = $.extend({}, notySuccessTemplate2, {
								text: "Column added, refreshing page..."
							});
							noty(notySuccessVar);
						}
					});
					
					$.ajax(ajaxVar);
				}
			}
		});
	});
	
});
