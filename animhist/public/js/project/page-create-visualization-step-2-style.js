var pointStyleColumns = ["Level", "Icon"];
var polygonStyleColumns = ["Level", "Color", "Opacity"];

var gfusionStyle;
var styleGridColumns, styleGridData;
var styleDataView, styleSlickGrid;
var styleCheckboxSelector;
var styleCommandQueue = [];

var styleGridOptions = {
	asyncEditorLoading: false,
	editable: true,
	editCommandHandler: styleSlickGrid_queueAndExecuteCommand,
	enableAddRow: true,
	enableCellNavigation: true,
	enableColumnReorder: false,
	explicitInitialization: true,
	forceFitColumns: true
};

function parseRetrievedStyle() {
	styleGridColumns = new Array();
	styleGridData = new Array();
	
	/* Parse column */
	styleCheckboxSelector = new Slick.CheckboxSelectColumn();
	styleGridColumns.push(styleCheckboxSelector.getColumnDefinition());
	styleGridColumns[0]["headerCssClass"] = "table-header";
	styleGridColumns[0]["cssClass"] = "table-cell-checkbox";
	
	var refColumns = viProps.type == "point" ? pointStyleColumns : polygonStyleColumns;
	
	for (var i = 0; i < refColumns.length; i++) {
		var columnItem = {id: i,
						name: refColumns[i],
						field: refColumns[i],
						headerCssClass: "table-header",
						cssClass: "table-cell",
						minWidth: 150};
		
		switch (refColumns[i]) {
		case "Level": case "Opacity": columnItem["validator"] = numberValidator;
		default: columnItem["editor"] = Slick.Editors.Text; break;
		}
		styleGridColumns.push(columnItem);
	}
	
	/* Parse row */
	if (!gfusionStyle) return;
	
	var styleBuckets = viProps.type == "point" ? gfusionStyle.markerOptions.iconStyler.buckets : gfusionStyle.polygonOptions.fillColorStyler.buckets;
	
	for (var i = 0; i < styleBuckets.length; i++) {
		var rowItem = {};
		rowItem["id"] = i;
		if (viProps.type == "point") {
			rowItem["Level"] = styleBuckets[i].min;
			rowItem["Icon"] = styleBuckets[i].icon;
		} else if (viProps.type == "polygon") {
			rowItem["Level"] = styleBuckets[i].min;
			rowItem["Color"] = styleBuckets[i].color;
			rowItem["Opacity"] = styleBuckets[i].opacity;
		}
		
		styleGridData.push(rowItem);
	}
}

function retrieveStyle(column) {
	$.ajax({
		url: getPOSTURLPrefix() + "/info?request=style&column=" + column,
		type: "GET",
		global: false,
		headers: {'X-CSRF-Token': getCSRFToken()},
		success: function(response) {
			gfusionStyle = response;
			parseRetrievedStyle();
			$(window).trigger("vi_style_loaded");
			
			styleDataView = new Slick.Data.DataView();
			styleSlickGrid = new Slick.Grid("#edit-area-style #table", styleDataView, styleGridColumns, styleGridOptions);
			
			styleDataView.onRowCountChanged.subscribe(function(e, args) {
				styleSlickGrid.updateRowCount();
				styleSlickGrid.render();
			});
			styleDataView.onRowsChanged.subscribe(function(e, args) {
				styleSlickGrid.invalidateRows(args.rows);
				styleSlickGrid.render();
			});
			styleDataView.beginUpdate();
			styleDataView.setItems(styleGridData);
			styleDataView.endUpdate();
		    
			styleSlickGrid.setSelectionModel(new Slick.RowSelectionModel({selectActiveRow: false}));
			styleSlickGrid.registerPlugin(styleCheckboxSelector);
			styleSlickGrid.onCellChange.subscribe(styleSlickGrid_cellChange);
			styleSlickGrid.onAddNewRow.subscribe(styleSlickGrid_addNewRow);
			styleSlickGrid.onSelectedRowsChanged.subscribe(styleSlickGrid_selectedRowsChanged);
			styleSlickGrid.init();
		}
	});	
}

function styleSlickGrid_queueAndExecuteCommand(item, column, editCommand) {
	styleCommandQueue.push(editCommand);
	editCommand.execute();
}

function styleSlickGrid_undo() {
	var command = styleCommandQueue.pop();
	if (command && Slick.GlobalEditorLock.cancelCurrentEdit()) {
		command.undo();
		styleSlickGrid.gotoCell(command.row, command.cell, false);
	}
}

function styleSlickGrid_cellChange(e, args) {
    var activeCol = args["cell"];
    var activeColField = gridColumns[activeCol]["field"];
    var activeRowItem = args["item"]; // the whole row data
    var pairs = {};
    pairs[activeColField] = activeRowItem[activeColField];
}

function styleSlickGrid_addNewRow(e, args) {
	var rowItem = args["item"];
	var col = args["col"];
	
	var pairs = {};
	$.each(rowItem, function(key, val) {
		if (val) pairs[key] = val;
	});
	
}

function styleSlickGrid_selectedRowsChanged(e, args) {
	var selectedRows = args["rows"];
	if (!selectedRows.length) $("#edit-area-style #delete-row-btn").attr("disabled", true);
	else $("#edit-area-style #delete-row-btn").attr("disabled", false);
}

$(window).on('vi_property_loaded', function() {
	retrieveStyle(viProps["defaultColumn"]);
});