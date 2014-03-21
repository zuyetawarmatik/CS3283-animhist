var pointStyleColumns = ["Level", "Icon"];
var polygonStyleColumns = ["Level", "Color", "Opacity"];

var gfusionStyle;
var styleGridColumns, styleGridData;
var styleDataView, styleSlickGrid;
var styleCheckboxSelector;

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
	
	console.log(JSON.stringify(styleGridData));
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
		}
	});	
}

$(window).on('vi_property_loaded', function() {
	retrieveStyle(viProps["defaultColumn"]);
});