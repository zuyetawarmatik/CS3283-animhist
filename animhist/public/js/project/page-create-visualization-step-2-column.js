var ajaxTemplate2;

$(function(){
	ajaxTemplate2 = {
		processData: false,
		contentType: "application/json; charset=utf-8",
		url: postURLPrefix + "/updatetable",
		type: "POST",
		headers: {'X-CSRF-Token': CSRFToken},
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
		error: function() {
			notyError({
				text: "Updating data error, rolling back...",
				timeout: 500
			});
		}
	};
});

$(window).on('vi_property_loaded', function() {
	addDefaultColumnOptions();
	addColumnListButtons();
});

/* Prepare the list of columns to set default */
function addDefaultColumnOptions() {
	$defaultColumnSelect.empty();
	
	$.each(columnList, function(i, obj) {
		if (obj["type-caption"] == 'Number') {
			$defaultColumnSelect.append("<option value='" +  obj["caption"] + "'>" + obj["caption"] + "</option>");
		}
	});
	
	$defaultColumnSelect.find("option[value='" + viProps["defaultColumn"] + "']").attr("selected", "selected");
}

/* Prepare the list of column item buttons */
function addColumnListButtons() {
	$columnList.empty();
	
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
		
		element.appendTo($columnList);
	});
	
	$("li.red-btn-group").find("button").each(function() {
		$(this).addClass("red-btn");
	});
	
	$("li.grey-btn-group").find("button").each(function() {
		$(this).addClass("grey-btn");
	});
	
	$("<li class='btn-group' id='column-add-btn-group'><button class='column-add-btn grey-btn'>&#57602;</button></li>").appendTo($columnList);
}

$(function() {
	/* Delete a column item */
	$columnList.on("click", ".column-delete-btn", function() {
		var index = $(this).parent().index();
		var ajaxVar = $.extend({}, ajaxTemplate2, {
			data: JSON.stringify({
				type: "column-delete",
				col: columnList[index]["column-id"]
			}),
			success: function() {
				notySuccess({
					text: "Column removed, refreshing page...",
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
	
	/* Disable HTMLData column item */
	$columnList.on("click", ".column-disable-btn", function() {
		var index = $(this).parent().index();
		if (!columnList[index]["disabled"]) {
			var ajaxVar = $.extend({}, ajaxTemplate2, {
				data: JSON.stringify({
					type: "column-delete",
					col: columnList[index]["column-id"]
				}),
				success: function() {
					notySuccess({
						text: "Column removed, refreshing page...",
						callback: {
							afterShow: function() {
								window.location.reload();
							}
						}
					});
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
				success: function() {
					notySuccess({
						text: "Column added, refreshing page...",
						callback: {
							afterShow: function() {
								window.location.reload();
							}
						}
					});
				}
			});
			
			$.ajax(ajaxVar);
		}
	});
	
	/* Add a new column item */
	$columnList.on("click", ".column-add-btn", function() {
		vex.dialog.open({
			message: "Add a New Column",
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
						success: function() {
							notySuccess({
								text: "Column added, refreshing page...",
								callback: {
									afterShow: function() {
										window.location.reload();
									}
								}
							});
						}
					});
					
					$.ajax(ajaxVar);
				}
			}
		});
	});
	
	/* Edit a column item */
	$columnList.on("click", ".column-edit-btn", function() {
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
			message: "Edit '" + columnName +  "' Column",
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
						success: function() {
							notySuccess({
								text: "Column updated, refreshing page...",
								callback: {
									afterShow: function() {
										window.location.reload();
									}
								}
							});
						}
					});
					
					$.ajax(ajaxVar);
				}
			}
		});
	});
	
	/* Change the default column to visualize */
	$defaultColumnSelect.change(function() {
		var val = $(this).val();
		if (val != "") {
			$.ajax({
				url: postURLPrefix + "/updateproperty",
				type: "POST",
				headers: {'X-CSRF-Token': CSRFToken},
				global: false,
				data: {"default-column": val},
				success: function(response) {
					viProps["defaultColumn"] = response["defaultColumn"];
					viProps["defaultStyleId"] = response["defaultStyleId"];
					$(window).trigger({
						type: "vi_property_changed",
						fields: ["defaultColumn"]
					});
				}
			});
		}
	});
});
