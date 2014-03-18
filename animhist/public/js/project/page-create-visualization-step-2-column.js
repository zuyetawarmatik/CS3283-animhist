var viProps;
var columnList;

var ajaxTemplate2, notySuccessTemplate2;

$(function(){
	ajaxTemplate2 = {
		processData: false,
	    contentType: "application/json; charset=utf-8",
	    url: "/" + $("#edit-area").data("user-id") + "/visualization/" + $("#edit-area").data("vi-id") + "/updatetable",
		type: "POST",
		headers: {'X-CSRF-Token': getCSRFToken()},
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
		headers: {'X-CSRF-Token': getCSRFToken()},
		global: false,
		success: function(responseData) {
			viProps = responseData;
			$(window).trigger("vi_property_loaded");
			columnList = responseData["columnList"];
			addDefaultColumnOptions();
			addColumnListButtons();
		}
	});
}

function addDefaultColumnOptions() {
	$("#default-column-select").empty();
	
	$.each(columnList, function(i, obj) {
		if (obj["type-caption"] == 'Number') {
			$("#default-column-select").append("<option value='" +  obj["caption"] + "'>" + obj["caption"] + "</option>");
		}
	});
	
	$("#default-column-select option[value='" + viProps["defaultColumn"] + "']").attr("selected", "selected");
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
					colName: "HTMLData",
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
				if (data) {
					var columnName = data.columnname.trim();
					if (!checkColumnName(columnName, columnList)) return;
						
					var ajaxVar = $.extend({}, ajaxTemplate2, {
						data: JSON.stringify({
							type: "column-insert",
							colName: columnName,
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
	
	$("#column-list").on("click", ".column-edit-btn", function() {
		var index = $(this).parent().index();
		var columnName = columnList[index]["caption"], columnType = columnList[index]["type-caption"];
		var nameEditRow = columnName != "Milestone" ? "<tr><td><label for='columnname'>Column name:</label></td><td><input name='columnname' type='text'/></td></tr>" : "";
		var typeEditOption = columnName != "Milestone" ?
								"<option value='String'>String</option><option value='Number'>Number</option>" :
								"<option value='Hour'>Hour</option><option value='Day'>Day</option><option value='Month'>Month</option><option value='Year'>Year</option><option value='Decade'>Decade</option><option value='Century'>Century</option><option value='Mixed'>Mixed</option>"

		var vexContent = "<table>" + nameEditRow +
							"<tr>" +
								"<td>" +
									"<label for='columntype'>Column type:</label>" +
								"</td>" +
								"<td>" +
									"<div class='styled-select'><select name='columntype'>" +
										typeEditOption +
									"</select></div>" +
								"</td>" +
							"</tr>" +
						"</table>";
		
		vex.dialog.open({
			message: "Edit '" + columnName +  "' column",
			input: vexContent,
			afterOpen: function() {
				$("[name='columnname']").val(columnName);
				$("option[value='" + columnType + "']").attr("selected", "selected");
			},
			callback: function(data) {
				if (data) {
					var newColumnName = columnName == "Milestone" ? "Milestone" : data.columnname.trim();
					if (columnName != "Milestone" && !checkColumnName(newColumnName, columnList, index)) return;
					
					var ajaxVar = $.extend({}, ajaxTemplate2, {
						data: JSON.stringify({
							type: "column-update",
							col: columnList[index]["column-id"],
							colName: newColumnName,
							colType: data.columntype.toUpperCase()
						}),
						success: function(responseData) {
							var notySuccessVar = $.extend({}, notySuccessTemplate2, {
								text: "Column updated, refreshing page..."
							});
							noty(notySuccessVar);
						}
					});
					
					$.ajax(ajaxVar);
				}
			}
		});
	});
	
	$("#default-column-select").change(function() {
		var val = $(this).val();
		if (val != "") {
			var formData = new FormData();
			formData.append("default-column", val);
			$.ajax({
				processData: false,
			    contentType: false,
				url: "/" + $("#edit-area").data("user-id") + "/visualization/" + $("#edit-area").data("vi-id") + "/updateproperty",
				type: "POST",
				headers: {'X-CSRF-Token': getCSRFToken()},
				global: false,
				data: formData,
				success: function(response) {
					viProps["defaultColumn"] = val;
					$(window).trigger("vi_property_loaded");
				}
			});
		}
	});
});
