// TODO: data validation: number

var gfusionProps, gfusionData;
var gridColumns = new Array(), gridData = new Array();
var slickGrid;

var backupActiveRow, backupActiveCol, backupActiveRowItem, backupCellValue;

var gridOptions = {
	asyncEditorLoading: false,
	editable: true,
	enableAddRow: true,
	enableCellNavigation: true,
	enableColumnReorder: false,
	forceFitColumns: true
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
		case "DATETIME": columnItem["editor"] = Slick.Editors.Date; break;
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
			slickGrid.onBeforeEditCell.subscribe(slickGrid_BeforeEditCell);
			slickGrid.onCellChange.subscribe(slickGrid_CellChange);
		}
	});
}

$(function() {
	retrieveFusionData();
});

function slickGrid_BeforeEditCell(e, args) {
	backupActiveRow = args["row"];
	backupActiveCol = args["cell"];
	backupActiveRowItem = args["item"];
	backupCellValue = getCellValue(backupActiveRowItem, backupActiveCol);
}

function slickGrid_CellChange(e, args) {
	var activeRow = args["row"];
    var activeCol = args["cell"];
    var activeRowItem = args["item"]; // the whole row data
    var cellValue = getCellValue(activeRowItem, activeCol);
    
    
}

function getCellValue(rowItem, col) {
	var colField = gridColumns[col]["field"];
	return rowItem[colField];
}