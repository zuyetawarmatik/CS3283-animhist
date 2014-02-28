var columnList = [
                  {"caption":"Milestone", "type-caption":"Year", "editable":true},
                  {"caption":"Position", "type-caption":"Location: KML or Lat/Long or String"},
                  {"caption":"HTMLData", "type-caption":"String", "disable":true, "disabled":false},
                  {"caption":"New Valuable", "type-caption":"Number", "editable":true, "deletable":true}
                 ];

function addColumnListButtons() {
	$("#column-list").empty();
	
	$.each(columnList, function(i, obj) {
		var caption = obj["caption"] + " (" + obj["type-caption"] + ")";
		var element = $(document.createElement("li")).addClass("btn-group").html("<button type='button'>" + caption + "</button>");
		
		if (obj["editable"])
			$("<button class='column-edit-btn' type='button'>&#57350;</button>").appendTo(element);
		
		if (obj["deletable"])
			$("<button class='column-delete-btn' type='button'>&#57597;</button>").appendTo(element);
		else
			if (!obj["disable"]) element.addClass("red-btn-group");
		
		if (obj["disable"]) {
			if (obj["disabled"]) {
				element.addClass("grey-btn-group");
				$("<button class='column-disable-btn' type='button'>&#57657;</button>").appendTo(element);
			} else
				$("<button class='column-disable-btn' type='button'>&#57656;</button>").appendTo(element);
		}
		
		element.appendTo("#column-list");
	});
	
	$(".red-btn-group button").each(function() {
		$(this).addClass("red-btn");
	});
	
	$(".grey-btn-group button").each(function() {
		$(this).addClass("grey-btn");
	});
	
	$("<li class='btn-group' id='column-add-btn-group'><button class='column-add-btn grey-btn' type='button'>&#57602;</button></li>").appendTo("#column-list");
}

$(function() {
	addColumnListButtons();
	
	$("#column-list").on("click", ".column-delete-btn", function() {
		var $parent = $(this).parent(); 
		var id = $parent.index();
		$parent.remove();
		columnList.splice(id, 1);
	});
	
	$("#column-list").on("click", ".column-disable-btn", function() {
		var $parent = $(this).parent(); 
		var id = $parent.index();
		
		columnList[id]["disabled"] ^= true;
		
		if (columnList[id]["disabled"]) {
			$parent.addClass("grey-btn-group");
			$parent.children().each(function() {
				$(this).addClass("grey-btn");
			});
			$(this).html("&#57657;");
		} else {
			$parent.removeClass("grey-btn-group");
			$parent.children().each(function() {
				$(this).removeClass("grey-btn");
			});
			$(this).html("&#57656;");
		}
	});
});

$(function() {
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
				if (columnName.toLowerCase() == "createdat" || columnName.toLowerCase() == "milestonerep") return;
				if (columnName != "" && columnName.match(/^[a-z0-9\-\s]+$/i)) {
					var exit = false;
					$.each(columnList, function(i, obj) {
						if (columnName.toLowerCase() == obj["caption"].toLowerCase()) exit = true;
					});
					if (exit) return;
					columnList.push({"caption":columnName, "type-caption":data.columntype, "editable":true, "deletable":true});
					
					addColumnListButtons();
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
				if (columnName != "Milestone" && !data.columnname) return;
				var newColumnName = columnName == "Milestone" ? "Milestone" : data.columnname.trim();
				if (newColumnName.toLowerCase() == "createdat" || newColumnName.toLowerCase() == "milestonerep") return;
				if (newColumnName != "" && newColumnName.match(/^[a-z0-9\-\s]+$/i)) {
					var exit = false;
					$.each(columnList, function(i, obj) {
						if (newColumnName.toLowerCase() == obj["caption"].toLowerCase() && i != index) exit = true;
					});
					if (exit) return;
					
					columnList[index]["caption"] = newColumnName;
					columnList[index]["type-caption"] = data.columntype;
					
					addColumnListButtons();
				}
			}
		});
	});
});

$(function() {
	$("[name='create-visualization-form']").submit(function(event) {
		event.preventDefault();
		
		var formData = new FormData(this);
		var preparedColumnList = prepareColumnList();
		formData.append("column-list", JSON.stringify(preparedColumnList));
		
		$.ajax({
			processData: false,
		    contentType: false,
			url: this.action,
			type: this.method,
			data: formData,
			error: function(responseData) {
				var alertSt = "";
				$.each(responseData["responseJSON"]["error"], function(key, val) {
					$.each(val, function(index, tx) {
						alertSt += tx + "<br/>";
					});
				});
				noty({
					layout: 'bottomCenter',
					text: alertSt,
					type: 'error',
					killer: true,
					timeout: 2000,
					maxVisible: 1
				});
			},
		
			success: function(responseData) {
				noty({
					layout: 'center',
					text: "Create visualization successfully!<br/>Redirecting to step 2...",
					type: 'success',
					killer: true,
					timeout: 1000,
					callback: {
						afterShow: function(){handleJSONRedirectResponse(responseData, false);}
					}
				});
			}
		});
	});
});

function prepareColumnList() {
	var result = new Array();
	$.each(columnList, function(i, obj) {
		if (!obj["disabled"]) {
			result.push({"caption":obj["caption"], "type-caption":obj["type-caption"]});
		}
	});
	return result;
}