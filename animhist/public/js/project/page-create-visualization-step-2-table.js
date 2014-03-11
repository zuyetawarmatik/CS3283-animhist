var gfusionProps, gfusionData, gfusionRowsID;
var gridColumns, gridData, gridTimeline;
var dataView, slickGrid;
var checkboxSelector;
var commandQueue = [];
var ajaxTemplate, notyErrorTemplate, notySuccessTemplate;

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

var dateFormatter = function(row, cell, value, columnDef, dataContext) {
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

var numberValidator = function(value) {
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

var milestoneFilter = function(item) {
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
	
	for (var i = 2; i < gfusionProps["columns"].length; i++) { // Omit CreatedAt and MilestoneRep column
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
		for (var j = 1; j < gfusionData["columns"].length; j++) { // Omit CreatedAt column only, keep MilestoneRep column
			rowItem[gfusionData["columns"][j]] = gfusionData["rows"][i][j];
		}
		gridData.push(rowItem);
	}
}

function retrieveFusionData() {
	$.ajax({
		processData: false,
	    contentType: false,
		url: "/" + $("#edit-area").data("user-id") + "/visualization/" + $("#edit-area").data("vi-id") + "/info?request=data",
		type: "GET",
		headers: {'X-CSRF-Token': getCSRFToken()},
		error: function(responseData) {
			noty({
				layout: 'bottomCenter',
				text: "Loading data error, refresh to try again",
				type: 'error',
				killer: true,
				timeout: 2000,
				maxVisible: 1
			});
		},
		success: function(responseData) {
			noty({
				layout: 'bottomCenter',
				text: "Loading data finished",
				type: 'success',
				killer: true,
				timeout: 500,
				maxVisible: 1
			});
			gfusionProps = responseData["gfusionProps"];
			gfusionData = responseData["gfusionData"];
			gfusionRowsID = responseData["gfusionRowsID"];
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
		processData: false,
	    contentType: false,
		url: "/" + $("#edit-area").data("user-id") + "/visualization/" + $("#edit-area").data("vi-id") + "/info?request=timeline",
		type: "GET",
		global: false,
		headers: {'X-CSRF-Token': getCSRFToken()},
		success: function(responseData) {
			$("#filter-list").empty();
			$("#timeline-list").empty();
			
			gridTimeline = responseData;
			gridTimeline.unshift("All");
			
			for (var i = 0; i < gridTimeline.length; i++) {
				$("<li class='filter-item'>" + gridTimeline[i] + "</li>").appendTo("#filter-list");
				
				if (i > 0)
					$("<li class='timeline-item'>" + gridTimeline[i] + "</li>").appendTo("#timeline-list");
			}
			
			if (!$("#filter-list").attr("data-filter") || !focused)	focused = "All";
			$("#filter-list").attr("data-filter", focused);
			
			if (gridTimeline.length > 1)
				$("#timeline-list").attr("data-milestone", gridTimeline[1]);
		}
	});	
}

$(function() {
	ajaxTemplate = {
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
		}
	};
	
	notyErrorTemplate = {
		layout: 'bottomCenter',
		text: "Updating data error, rolling back...",
		type: 'error',
		killer: true,
		timeout: 500,
		maxVisible: 1
	};
	
	notySuccessTemplate = {
		layout: 'bottomCenter',
		text: "All changes saved",
		type: 'success',
		killer: true,
		timeout: 500,
		maxVisible: 1
	};
	
	retrieveTimeline();
	retrieveFusionData();
	
	$(window).resize(function() {
		if (slickGrid)
			slickGrid.resizeCanvas();
	});
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
		error: function(responseData) {
			var notyErrorVar = $.extend({}, notyErrorTemplate, {
				callback: {
					onShow: function(){
						slickGrid_undo();
					}
				}
			});
			noty(notyErrorVar);
		},
		success: function(responseData) {
			var notySuccessVar = $.extend({}, notySuccessTemplate, {
				callback: {
					onShow: function(){
						var responseRow = responseData["rows"][0];
						for (var i = 1; i < responseRow.length; i++) {
							activeRowItem[responseData["columns"][i]] = responseRow[i];
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
			noty(notySuccessVar);
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
	
	var ajaxVar = $.extend({}, ajaxTemplate, {
		data: JSON.stringify({
			type: "row-insert",
			colvalPairs: pairs
		}),
		error: function(responseData) {
			var notyErrorVar = $.extend({}, notyErrorTemplate);
			noty(notyErrorVar);
		},
		success: function(responseData) {
			var notySuccessVar = $.extend({}, notySuccessTemplate, {
				text: "New row added",
				callback: {
					onShow: function(){
						var responseRow = responseData["rows"][0];
						var newRow = {};
						for (var i = 1; i < responseRow.length; i++) {
							newRow[responseData["columns"][i]] = responseRow[i];
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
					}
				}
			});
			noty(notySuccessVar);
		}
	});
	$.ajax(ajaxVar);
}

function slickGrid_selectedRowsChanged(e, args) {
	var selectedRows = args["rows"];
	if (!selectedRows.length) $("#delete-row-btn").attr("disabled", true);
	else $("#delete-row-btn").attr("disabled", false);
}

$(function() {
	$("#delete-row-btn").click(function() {
		var rowsID = dataView.mapRowsToIds(slickGrid.getSelectedRows());
		var ajaxVar = $.extend({}, ajaxTemplate, {
			data: JSON.stringify({
				type: "row-delete",
				row: rowsID
			}),
			error: function(responseData) {
				var notyErrorVar = $.extend({}, notyErrorTemplate);
				noty(notyErrorVar);
			},
			success: function(responseData) {
				var notySuccessVar = $.extend({}, notySuccessTemplate, {
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
				noty(notySuccessVar);
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