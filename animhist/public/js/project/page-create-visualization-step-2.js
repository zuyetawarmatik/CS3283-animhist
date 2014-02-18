var fusionProps, fusionData;
var gridColumns = new Array(), gridData = new Array();
var slickGrid;

var gridOptions = {
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
	if (!fusionProps["columns"]) return;
	for (var i = 0; i < fusionProps["columns"].length; i++) {
		var columnItem = {id: fusionProps["columns"][i]["columnId"],
						name: fusionProps["columns"][i]["name"],
						field: fusionProps["columns"][i]["name"],
						headerCssClass: "table-header",
						cssClass: "table-cell",
						minWidth: 150};
		
		switch (fusionProps["columns"][i]["type"]) {
		case "DATETIME": columnItem["editor"] = Slick.Editors.Date; break;
		default: columnItem["editor"] = Slick.Editors.Text; break;
		}
		gridColumns.push(columnItem);
	}
	
	/* Parse row */
	if (!fusionProps["rows"]) return;
	for (var i = 0; i < fusionData["rows"].length; i++) {
		var rowItem = {};
		for (var j = 0; j < fusionData["columns"].length; j++) {
			rowItem[fusionData["columns"][j]] = fusionData["rows"][i][j];
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
			fusionProps = responseData["fusionProps"];
			fusionData = responseData["fusionData"];
			parseRetrievedData();
			slickGrid = new Slick.Grid("#edit-area-table #table", gridData, gridColumns, gridOptions);
		}
	});
}

$(function() {
	retrieveFusionData();
});