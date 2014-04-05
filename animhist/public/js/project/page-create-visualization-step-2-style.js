var allowedIcons = ["small_red", "small_blue", "small_green", "small_yellow", "small_purple", "measle_brown", "measle_grey", "measle_white", "measle_turquoise"];
var pointStyleColumns = ["Level", "Icon"];
var polygonStyleColumns = ["Level", "Color", "Opacity"];

var incrementVal = 0;
var currentStyleColumn;
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

function iconValidator(value) {
	if (allowedIcons.indexOf(value) < 0) {
		return {
			valid : false,
			msg : "Please input a valid icon"
		};
	} else {
		return {
			valid : true,
			msg : null
		};
	}
}

function iconEditor(args) {
	var $select;
	var defaultValue;
	var scope = this;

	this.init = function() {
		$select = $("<select></select>");
		for (var i = 0; i < allowedIcons.length; i++) {
			$option = $("<option value='" + allowedIcons[i] + "'>" + allowedIcons[i] + "</option>");
			$option.appendTo($select);
		}
		$select.appendTo(args.container);
	};

	this.destroy = function() {
		$select.remove();
	};

	this.focus = function() {
		$select.focus();
	};

	this.loadValue = function(item) {
		$select.val((defaultValue = item[args.column.field]) ? item[args.column.field] : "");
		$select.select();
	};

	this.serializeValue = function() {
		return $select.val();
	};

	this.applyValue = function(item, state) {
		item[args.column.field] = state;
	};

	this.isValueChanged = function() {
		if (defaultValue === undefined)
			return $select.val() != "";
		
		return ($select.val() != defaultValue);
	};

	this.validate = function() {
		return {
			valid : true,
			msg : null
		};
	};

	this.init();
}

function colorEditor(args) {
	var $text;
	var defaultValue;
	var scope = this;

	this.init = function() {
		$text = $("<input type='text' class='editor-text'>");
		$text.appendTo(args.container);
	};

	this.destroy = function() {
		$text.remove();
	};

	this.focus = function() {
		$text.focus();
	};

	this.loadValue = function(item) {
		var colorVal = (item[args.column.field] !== undefined && item[args.column.field] != "") ? item[args.column.field] : "#000000"; 
		$text.val(colorVal);
		$text.focus().select();
		$text.spectrum({showInput: true, color: colorVal});
	};

	this.serializeValue = function() {
		return $text.val();
	};

	this.applyValue = function(item, state) {
		item[args.column.field] = state;
	};

	this.isValueChanged = function() {
		if (defaultValue === undefined)
			return $text.val() != "";
		
		return ($text.val() != defaultValue);
	};

	this.validate = function() {
		return {
			valid : true,
			msg : null
		};
	};

	this.init();
}

function colorFormatter(row, cell, value, columnDef, dataContext) {
	if (value == null || value === "")
		return "";

	var color = value;

	return "<div style='background:" + color
			+ ";height:100%;width:100%'></div>";
}

function colorValidator(value) {
	if (!value.match(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/)) {
		return {
			valid : false,
			msg : "Please input a valid color"
		};
	} else {
		return {
			valid : true,
			msg : null
		};
	}
}

function opacityValidator(value) {
	if (isNaN(value)) {
		return {
			valid : false,
			msg : "Please input a number"
		};
	} else {
		if (value < 0 || value > 1) {
			return {
				valid : false,
				msg : "Please input a valid opacity"
			};
		} else {
			return {
				valid : true,
				msg : null
			};
		}
	}
}

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
		case "Icon": columnItem["validator"] = iconValidator; columnItem["editor"] = iconEditor; break;
		case "Color": columnItem["validator"] = colorValidator; columnItem["formatter"] = colorFormatter; columnItem["editor"] = colorEditor; break;
		case "Level": columnItem["validator"] = numberValidator; columnItem["editor"] = Slick.Editors.Text; break;
		case "Opacity": columnItem["validator"] = opacityValidator; columnItem["editor"] = Slick.Editors.Text; break;
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
	
	incrementVal = i;
}

// type: "load" or "select"
function retrieveStyle(column, type) {
	currentStyleColumn = column;
	
	$.ajax({
		url: postURLPrefix + "/info?request=style&column=" + column,
		type: "GET",
		global: false,
		success: function(response) {
			gfusionStyle = response;
			if (type == "load") $(window).trigger("vi_style_loaded");
			initStyleTable();
		}
	});
}

function initStyleTable() {
	parseRetrievedStyle();
	
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
	if (currentStyleColumn != null)
		styleSlickGrid.onAddNewRow.subscribe(styleSlickGrid_addNewRow);
	styleSlickGrid.onSelectedRowsChanged.subscribe(styleSlickGrid_selectedRowsChanged);
	styleSlickGrid.init();
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

function styleSlickGrid_selectedRowsChanged(e, args) {
	var selectedRows = args["rows"];
	if (!selectedRows.length) $editAreaStyleDelBtn.attr("disabled", true);
	else $editAreaStyleDelBtn.attr("disabled", false);
}

function styleSlickGrid_addNewRow(e, args) {
	var newRow = args["item"];
	
	newRow["id"] = incrementVal++;
	if (newRow["Level"] === undefined) newRow["Level"] = parseInt(findStyleMaxLevel()) + 10;
	if (viProps["type"] == "polygon") {
		if (newRow["Opacity"] === undefined) newRow["Opacity"] = 0.5;
		if (newRow["Color"] === undefined) newRow["Color"] = "#000000";
	} else if (viProps["type"] == "point")
		if (newRow["Icon"] === undefined) newRow["Icon"] = "small_red";
	
	styleDataView.addItem(newRow);
}

function findStyleMaxLevel() {
	var ret = -10;
	var items = styleGridData;
	for (var i = 0; i < items.length; i++) {
		if (items[i]["Level"] > ret) ret = items[i]["Level"];
	}
	return ret;
}

$(function() {
	$editAreaStyleDelBtn.click(function() {
		var rowsID = styleDataView.mapRowsToIds(styleSlickGrid.getSelectedRows());
		$.each(rowsID, function(i, val) {
			styleDataView.deleteItem(val);
		});
		styleSlickGrid.setSelectedRows([]);
	});
	
	$editAreaStyleSaveBtn.click(function() {
		var sentStyle = $.extend(true, [], styleGridData);
		$.each(sentStyle, function(i, val) {
			delete val.id;
			val.Level = Number(val.Level);
			if (val.Opacity !== undefined)
				val.Opacity = Number(val.Opacity);
		});
		sentStyle.sort(function(a, b) {
			var lvA = Number(a.Level);
			var lvB = Number(b.Level);
			if (lvA < lvB) return -1;
			else if (lvA > lvB) return 1;
			return 0;
		});
		
		$.ajax({
			processData: false,
			contentType: "application/json; charset=utf-8",
			url: postURLPrefix + "/updatestyle",
			type: "POST",
			headers: {'X-CSRF-Token': CSRFToken},
			global: false,
			data: JSON.stringify({
				colName: currentStyleColumn,
				style: sentStyle
			}),
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
			error:  function() {
				notyError({
					text: 'Update style failed',
				})
			},
			success: function(response) {
				notySuccess({
					text: 'Update style successfully',
				})
				
				gfusionStyle = response;
				initStyleTable();
			}
		});
	});
	
	$styleColumnSelect.change(function() {
		var val = $(this).val();
		if (val != "") {
			retrieveStyle(val, "select");
		}
	});
});

function addStyleColumnOptions() {
	$styleColumnSelect.empty();
	
	$.each(columnList, function(i, obj) {
		if (obj["type-caption"] == 'Number') {
			$styleColumnSelect.append("<option value='" +  obj["caption"] + "'>" + obj["caption"] + "</option>");
		}
	});
	
	$styleColumnSelect.find("option[value='" + viProps["defaultColumn"] + "']").attr("selected", "selected");
}

$(window).on('vi_property_loaded', function() {
	addStyleColumnOptions();
	retrieveStyle(viProps["defaultColumn"], "load");
});

$(window).resize(function() {
	if (styleSlickGrid)
		styleSlickGrid.resizeCanvas();
});