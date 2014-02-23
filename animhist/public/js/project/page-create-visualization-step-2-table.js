var gfusionProps, gfusionData, gfusionRowsID;
var gridColumns, gridData;
var slickGrid;
var commandQueue = [];

var gridOptions = {
	asyncEditorLoading: false,
	editable: true,
	editCommandHandler: slickGrid_queueAndExecuteCommand,
	enableAddRow: true,
	enableCellNavigation: true,
	enableColumnReorder: false,
	forceFitColumns: true
};

var dateFormatter = function(row, cell, value, columnDef, dataContext) {
	date = new Date(value);
	switch (viProps["milestoneFormat"]) {
	case "day": return date.format("d mmm yyyy");
	case "month": return date.format("mmm yyyy");
	case "year": return date.format("yyyy");
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

$(function() {
	$(window).resize(function() {
		slickGrid.resizeCanvas();
	});
});

function parseRetrievedData() {
	gridColumns = new Array();
	gridData = new Array();
	/* Parse column */
	if (!gfusionProps["columns"]) return;
	for (var i = 0; i < gfusionProps["columns"].length; i++) {
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
	
	/* Parse row */
	if (!gfusionData["rows"]) return;
	for (var i = 0; i < gfusionData["rows"].length; i++) {
		var rowItem = {};
		rowItem["ROWID"] = gfusionRowsID["rows"][i][0];
		for (var j = 0; j < gfusionData["columns"].length; j++) {
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
		headers: {'X-CSRF-Token': $("[name='hidden-form'] [type='hidden']").val()},
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
			
			slickGrid = new Slick.Grid("#edit-area-table #table", gridData, gridColumns, gridOptions);
			slickGrid.onCellChange.subscribe(slickGrid_cellChange);
			slickGrid.onAddNewRow.subscribe(slickGrid_addNewRow);
		}
	});
}

$(function() {
	retrieveFusionData();
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
    var activeRowID = activeRowItem["ROWID"];
    var cellValue = getCellValue(activeRowItem, activeCol);
    var pairs = {};
    pairs[activeColField] = cellValue;

    $.ajax({
		processData: false,
	    contentType: "application/json; charset=utf-8",
		url: "/" + $("#edit-area").data("user-id") + "/visualization/" + $("#edit-area").data("vi-id") + "/updatetable",
		type: "POST",
		headers: {'X-CSRF-Token': $("[name='hidden-form'] [type='hidden']").val()},
		data: JSON.stringify({
			type: "row-update",
			row: activeRowID,
			colvalPairs: pairs
		}),
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
				maxVisible: 1,
				callback: {
					onShow: function(){
						slickGrid_undo();
					}
				}
			});
		},
		success: function(responseData) {
			noty({
				layout: 'bottomCenter',
				text: "All changes saved",
				type: 'success',
				killer: true,
				timeout: 500,
				maxVisible: 1
			});
		}
	});
}

function slickGrid_addNewRow(e, args) {
	var rowItem = args["item"];
	var col = args["col"];
	
	if (!rowItem["Milestone"]) {
		var date = new Date(2000, 0, 1);
		rowItem["Milestone"] = date.format("mm/dd/yyyy");
	}
	
	var pairs = {};
	$.each(rowItem, function(key, val) {
		if (val) pairs[key] = val;
	});
	
	$.ajax({
		processData: false,
	    contentType: "application/json; charset=utf-8",
		url: "/" + $("#edit-area").data("user-id") + "/visualization/" + $("#edit-area").data("vi-id") + "/updatetable",
		type: "POST",
		headers: {'X-CSRF-Token': $("[name='hidden-form'] [type='hidden']").val()},
		data: JSON.stringify({
			type: "row-insert",
			colvalPairs: pairs
		}),
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
		},
		success: function(responseData) {
			noty({
				layout: 'bottomCenter',
				text: "New row added, refreshing page...",
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
		},
	});
}

function getCellValue(rowItem, col) {
	var colField = gridColumns[col]["field"];
	if (!rowItem) return false;
	return rowItem[colField];
}