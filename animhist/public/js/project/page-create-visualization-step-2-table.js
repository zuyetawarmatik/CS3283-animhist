var gfusionProps, gfusionData, gfusionRowsID;
var gridColumns, gridData, gridTimeline;
var dataView, slickGrid;
var checkboxSelector;
var commandQueue = [];
var ajaxTemplate;
var currentTimelineMilestoneId;

var gridOptions = {
	asyncEditorLoading: false,
	editable: true,
	editCommandHandler: slickGrid_queueAndExecuteCommand,
	enableAddRow: true,
	enableCellNavigation: true,
	enableColumnReorder: false,
	explicitInitialization: true,
	forceFitColumns: true
};

function dateFormatter(row, cell, value, columnDef, dataContext) {
	/* Fix issue for year before 1000 AD */
	var res = value.split("/");
	if (res.length == 1) value = "1/1/" + value;
	else if (res.length == 2) value = res[0] + "/1/" + res[1]; 
	
	switch (viProps["milestoneFormat"]) {
	case "day": return moment(value, "M/D/YYYY").format("D MMM YYYY");
	case "month": return moment(value, "M/D/YYYY").format("MMM YYYY");
	case "year": return moment(value, "M/D/YYYY").format("YYYY");
	}
};

function numberValidator(value) {
	if (isNaN(value)) {
		return {
			valid : false,
			msg : "Please input a number"
		};
	} else {
		return {
			valid : true,
			msg : null
		};
	}
}

function milestoneFilter(item) {
	var filter = $("#filter-list").attr("data-filter");
	if (filter != "All" && item["MilestoneRep"] != filter)
		return false;
	return true;
}

function parseRetrievedData() {
	gridColumns = new Array();
	gridData = new Array();
	/* Parse column */
	if (!gfusionProps["columns"]) return;
	
	checkboxSelector = new Slick.CheckboxSelectColumn();
	gridColumns.push(checkboxSelector.getColumnDefinition());
	gridColumns[0]["headerCssClass"] = "table-header";
	gridColumns[0]["cssClass"] = "table-cell-checkbox";
	
	for (var i = 3; i < gfusionProps["columns"].length; i++) { // Omit CreatedAt, MilestoneRep, Geocode column
		var columnItem = {id: gfusionProps["columns"][i]["columnId"],
						name: gfusionProps["columns"][i]["name"],
						field: gfusionProps["columns"][i]["name"],
						headerCssClass: "table-header",
						cssClass: "table-cell",
						minWidth: 150};
		
		switch (gfusionProps["columns"][i]["type"]) {
		case "DATETIME": columnItem["editor"] = Slick.Editors.Date; columnItem["formatter"] = dateFormatter; break;
		case "NUMBER": columnItem["validator"] = numberValidator;
		default: columnItem["editor"] = Slick.Editors.Text; break;
		}
		gridColumns.push(columnItem);
	}
	
	/* Move the HTMLData column to third place */
	var htmlDataIndex = -1;
	for (var i = 0; i < gridColumns.length; i++) {
		if (gridColumns[i]["name"].toLowerCase() == "htmldata") {
			htmlDataIndex = i; break;
		}
	}
	if (htmlDataIndex != -1) {
		var tempArr = gridColumns.splice(htmlDataIndex, 1);
		gridColumns.splice(3, 0, tempArr[0]);
	}
	
	/* Parse row */
	if (!gfusionData["rows"]) return;
	for (var i = 0; i < gfusionData["rows"].length; i++) {
		var rowItem = {};
		rowItem["ROWID"] = gfusionRowsID["rows"][i][0];
		rowItem["id"] = rowItem["ROWID"];
		for (var j = 1; j < gfusionData["columns"].length; j++) { // Omit CreatedAt column only, keep MilestoneRep and Geocode column
			rowItem[gfusionData["columns"][j]] = gfusionData["rows"][i][j];
		}
		gridData.push(rowItem);
	}
}

function retrieveFusionData() {
	$.ajax({
		url: getPOSTURLPrefix() + "/info?request=data",
		type: "GET",
		error: function() {
			notyError({
				text: "Loading data error, refresh to try again"
			});
		},
		success: function(response) {
			notySuccess({
				layout: 'bottomCenter',
				text: "Loading data finished"
			});
			gfusionProps = response["gfusionProps"];
			gfusionData = response["gfusionData"];
			gfusionRowsID = response["gfusionRowsID"];
			parseRetrievedData();
			
			dataView = new Slick.Data.DataView();
			slickGrid = new Slick.Grid("#edit-area-table #table", dataView, gridColumns, gridOptions);
			
			dataView.onRowCountChanged.subscribe(function(e, args) {
				slickGrid.updateRowCount();
				slickGrid.render();
			});
			dataView.onRowsChanged.subscribe(function(e, args) {
				slickGrid.invalidateRows(args.rows);
				slickGrid.render();
			});
			dataView.beginUpdate();
			dataView.setItems(gridData);
			dataView.setFilter(milestoneFilter);
			dataView.endUpdate();
		    
			slickGrid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));
			slickGrid.registerPlugin(checkboxSelector);
			slickGrid.onCellChange.subscribe(slickGrid_cellChange);
			slickGrid.onAddNewRow.subscribe(slickGrid_addNewRow);
			slickGrid.onSelectedRowsChanged.subscribe(slickGrid_selectedRowsChanged);
			slickGrid.init();
		}
	});
}

function retrieveTimeline(focused) {
	$.ajax({
		url: getPOSTURLPrefix() + "/info?request=timeline",
		type: "GET",
		global: false,
		success: function(response) {
			$("#filter-list").empty();
			$("#timeline-list").empty();
			
			gridTimeline = response;
			gridTimeline.unshift("All");
			
			for (var i = 0; i < gridTimeline.length; i++) {
				$("<li class='filter-item'>" + gridTimeline[i] + "</li>").appendTo("#filter-list");
				
				if (i > 0)
					$("<li class='timeline-item'>" + gridTimeline[i] + "</li>").appendTo("#timeline-list");
			}
			
			if (!$("#filter-list").attr("data-filter") || focused === undefined) focused = "All";
			$("#filter-list").attr("data-filter", focused);
			
			// Have one or more milestones (rather than 'All')
			if (gridTimeline.length > 1)
				$("#timeline-list").attr("data-milestone", gridTimeline[currentTimelineMilestoneId = 1]);
			else currentTimelineMilestoneId = 0;
		}
	});	
}

$(function() {
	ajaxTemplate = {
		processData: false,
		contentType: "application/json; charset=utf-8",
		url: getPOSTURLPrefix() + "/updatetable",
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
		}
	};
	
	retrieveFusionData();
});

$(window).resize(function() {
	if (slickGrid)
		slickGrid.resizeCanvas();
});

$(function() {
	/* Filtering attribute change */
	$("#filter-list").attrchange({
		trackValues: true, 
		callback: function (event) {
			if (event.attributeName == "data-filter") {
				$(".filter-item.focused").removeClass("focused");
				var filterIndex = $.inArray(event.newValue, gridTimeline);
				$(".filter-item:nth-child(" + (filterIndex + 1) + ")").addClass("focused");
				if (slickGrid) slickGrid.setSelectedRows([]);
				if (dataView) dataView.refresh();
			}
		}
	});
});

$(function() {
	$("#filter-list").on("click", ".filter-item", function() {
		if (!$(this).hasClass("focused")) {
			$("#filter-list").attr("data-filter", $(this).html());
		}
	});
});

function slickGrid_queueAndExecuteCommand(item, column, editCommand) {
	commandQueue.push(editCommand);
	editCommand.execute();
}

function slickGrid_undo() {
	var command = commandQueue.pop();
	if (command && Slick.GlobalEditorLock.cancelCurrentEdit()) {
		command.undo();
		slickGrid.gotoCell(command.row, command.cell, false);
	}
}

function slickGrid_cellChange(e, args) {
    var activeCol = args["cell"];
    var activeColField = gridColumns[activeCol]["field"];
    var activeRowItem = args["item"]; // the whole row data
    var activeRowID = activeRowItem["id"];
    var pairs = {};
    pairs[activeColField] = activeRowItem[activeColField];

    if (pairs["Milestone"])
    	pairs["Milestone"] = prepareProperDateTime(pairs["Milestone"]);
    
    var ajaxVar = $.extend({}, ajaxTemplate, {
    	data: JSON.stringify({
			type: "row-update",
			row: activeRowID,
			colvalPairs: pairs
		}),
		error: function() {
			notyError({
				text: "Updating data error, rolling back...",
				timeout: 500,
				callback: {
					onShow: function(){
						slickGrid_undo();
					}
				}
			});
		},
		success: function(response) {
			notySuccess({
				layout: 'bottomCenter',
				text: "All changes saved",
				callback: {
					onShow: function(){
						var responseRow = response["rows"][0];
						for (var i = 1; i < responseRow.length; i++) {
							activeRowItem[response["columns"][i]] = responseRow[i];
						}
						
						dataView.updateItem(activeRowID, activeRowItem);
						
						// Update timeline
						var mr = activeRowItem["MilestoneRep"];
						var indexOfMilestone = $.inArray(mr, gridTimeline);
						if (indexOfMilestone < 0) {
							var toFocus = $("#filter-list").attr("data-filter");
							if (toFocus != "All") toFocus = mr; 
							retrieveTimeline(toFocus);
						} else {
							dataView.refresh();
						}
					}
				}
			});
		}
    });
    $.ajax(ajaxVar);
}

function addNewRow(sentData) {
	var ajaxVar = $.extend({}, ajaxTemplate, {
		data: JSON.stringify({
			type: "row-insert",
			colvalPairs: sentData
		}),
		error: function() {
			notyError({
				text: "Updating data error, rolling back...",
				timeout: 500
			});
		},
		success: function(response) {
			notySuccess({
				layout: 'bottomCenter',
				text: "New row added",
				callback: {
					onShow: function(){
						var responseRow = response["rows"][0];
						var newRow = {};
						for (var i = 1; i < responseRow.length; i++) {
							newRow[response["columns"][i]] = responseRow[i];
						}
						newRow["id"] = newRow["ROWID"];
						dataView.addItem(newRow);
						
						// Update timeline
						var mr = newRow["MilestoneRep"];
						var indexOfMilestone = $.inArray(mr, gridTimeline);
						if (indexOfMilestone < 0) {
							var toFocus = $("#filter-list").attr("data-filter");
							if (toFocus != "All") toFocus = mr; 
							retrieveTimeline(toFocus);
						} else {
							dataView.refresh();
						}
						
						// Update center property if this is the first row having Geocode
						var needChangeCenter = true;
						for (var i = 0; i < gridData.length; i++) {
							if (gridData[i]["ROWID"] != newRow["ROWID"] && gridData[i]["Geocode"] != "") {
								needChangeCenter = false;
							}
						}
						if (needChangeCenter) {
							if (newRow["Geocode"] != "") {
								var latlng = newRow["Geocode"].split(" ");
								$.ajax({
								    url: getPOSTURLPrefix() + "/updateproperty",
									type: "POST",
									headers: {'X-CSRF-Token': getCSRFToken()},
									global: false,
									data: {"center-latitude": latlng[0], "center-longitude": latlng[1]},
									success: function(response) {
										viProps.centerLatitude = response.centerLatitude;
										viProps.centerLongitude = response.centerLongitude;
										$(window).trigger({
											type: "vi_property_changed",
											fields: ["centerLatitude", "centerLongitude"]
										});
									}
								});
							}
						}
					}
				}
			});
		}
	});
	
	$.ajax(ajaxVar);
}

function slickGrid_addNewRow(e, args) {
	var rowItem = args["item"];
	var col = args["col"];
	
	var pairs = {};
	$.each(rowItem, function(key, val) {
		if (val) pairs[key] = val;
	});
	
	if (pairs["Milestone"])
		pairs["Milestone"] = prepareProperDateTime(pairs["Milestone"]);

	addNewRow(pairs);
}

function slickGrid_selectedRowsChanged(e, args) {
	var selectedRows = args["rows"];
	if (!selectedRows.length) $("#edit-area-table #row-delete-btn").attr("disabled", true);
	else $("#edit-area-table #row-delete-btn").attr("disabled", false);
}

function openAddRowVex(presetData) {
	var colIDArr = [];
	$.each(gfusionProps.columns, function(i, v) {
		colIDArr.push(v.columnId);
	});
	
	$vexContent = $("<table></table>");
	for (var i = 1; i < gridColumns.length; i++) {
		var column = gridColumns[i];
		var columnName = column["field"];
		$vexRow = $("<tr><td></td><td></td></tr>");
		$("td:first-child", $vexRow).html("<label>" + columnName + "</label>");
		
		var columnId = column["id"];
		var columnRefId = colIDArr.indexOf(columnId);
		var columnType = gfusionProps.columns[columnRefId].type;
		$("td:nth-child(2)", $vexRow).html("<input name='" + columnName + "' type='text' data-col-id='" + columnId + "' data-col-type='" + columnType + "'/>");
		
		$vexRow.appendTo($vexContent);
	}

	vex.dialog.open({
		message: "Add New Row",
		input: $vexContent[0].outerHTML,
		afterOpen: function() {
			if (presetData) {
				$.each(presetData, function(k, v) {
					$("input[name='" + k + "']").val(v);
				});
			}
		},
		callback: function(data){
			if (data) {
				var pairs = {};
				$.each(data, function(key, val) {
					if (val) pairs[key] = val;
				});
				if (Object.keys(pairs).length == 0) return;
				
				if (pairs["Milestone"])
					pairs["Milestone"] = prepareProperDateTime(pairs["Milestone"]);
				
				addNewRow(pairs);
			}
		},
		afterClose: function() {
			// For manual drawing
			if (drawnShape) drawnShape.setMap(null);
		}
	});
}

$(function() {
	$("#edit-area-table #row-add-btn").click(function() {
		openAddRowVex();
	});
	
	$("#edit-area-table #row-delete-btn").click(function() {
		var rowsID = dataView.mapRowsToIds(slickGrid.getSelectedRows());
		var ajaxVar = $.extend({}, ajaxTemplate, {
			data: JSON.stringify({
				type: "row-delete",
				row: rowsID
			}),
			error: function() {
				notyError({
					text: "Updating data error, rolling back...",
					timeout: 500
				});
			},
			success: function() {
				notySuccess({
					layout: 'bottomCenter',
					text: "Rows removed",
					callback: {
						onShow: function(){
							$.each(rowsID, function(i, val) {
								dataView.deleteItem(val);
							});
							slickGrid.setSelectedRows([]);
							retrieveTimeline();
						}
					}
				});
			}
		});
		$.ajax(ajaxVar);
	});
});

function prepareProperDateTime(str) {
	var ret = str;
	var splitted = str.split('/');
	if (splitted.length == 1)
		ret = '1/1/' + str;
	else if (splitted.length == 2)
		ret = splitted[0] + '/1/' + splitted[1];
	
	ret = moment(ret, "M/D/YYYY").format("M/D/YYYY");
	return ret;
}