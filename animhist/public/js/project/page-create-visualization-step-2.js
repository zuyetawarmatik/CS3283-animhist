// TODO: data validation: number

var gfusionProps, gfusionData;
var gridColumns = new Array(), gridData = new Array();
var slickGrid;
var commandQueue = [];

var month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

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
	return date.getDate() + " " + month[date.getMonth()] + " " + date.getFullYear();
};

$(function() {
	$(window).resize(function() {
		slickGrid.resizeCanvas();
	});
});

function parseRetrievedData() {
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
		default: columnItem["editor"] = Slick.Editors.Text; break;
		}
		gridColumns.push(columnItem);
	}
	
	/* Parse row */
	if (!gfusionData["rows"]) return;
	for (var i = 0; i < gfusionData["rows"].length; i++) {
		var rowItem = {};
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
			parseRetrievedData();
			
			slickGrid = new Slick.Grid("#edit-area-table #table", gridData, gridColumns, gridOptions);
			slickGrid.onCellChange.subscribe(slickGrid_cellChange);
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
	var activeRow = args["row"];
    var activeCol = args["cell"];
    var activeColField = gridColumns[activeCol]["field"];
    var activeRowItem = args["item"]; // the whole row data
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
			row: activeRow,
			colvalPairs: pairs
		}),
		global: false,
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

function getCellValue(rowItem, col) {
	var colField = gridColumns[col]["field"];
	if (!rowItem) return false;
	return rowItem[colField];
}