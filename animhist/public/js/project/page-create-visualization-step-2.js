var tableProps, tableData;

/*
var grid;

var columns = [ {
		id : "title",
		name : "Title",
		field : "title",
		headerCssClass: "table-header",
		cssClass: "table-cell",
		minWidth: 150
	}, {
		id : "duration",
		name : "Duration",
		field : "duration",
		headerCssClass: "table-header",
		cssClass: "table-cell",
		minWidth: 150
	}, {
		id : "%",
		name : "% Complete",
		field : "percentComplete",
		headerCssClass: "table-header",
		cssClass: "table-cell",
		minWidth: 150
	}, {
		id : "start",
		name : "Start",
		field : "start",
		headerCssClass: "table-header",
		cssClass: "table-cell",
		minWidth: 150
	}, {
		id : "finish",
		name : "Finish",
		field : "finish",
		headerCssClass: "table-header",
		cssClass: "table-cell",
		minWidth: 150
	}, {
		id : "effort-driven",
		name : "Effort Driven",
		field : "effortDriven",
		headerCssClass: "table-header",
		cssClass: "table-cell",
		minWidth: 150
}];

var options = {
	enableCellNavigation: true,
	enableColumnReorder: false,
	forceFitColumns: true
};

$(function() {
	var data = [];
	for (var i = 0; i < 250; i++) {
		data[i] = {
			title : "Task " + i,
			duration : "5 days",
			percentComplete : Math.round(Math.random() * 100),
			start : "01/01/2009",
			finish : "01/05/2009",
			effortDriven : (i % 5 == 0)
		};
	}

	grid = new Slick.Grid("#edit-area-table #table", data, columns, options);
});

$(function() {
	$(window).resize(function() {
		grid.resizeCanvas();
	});
});
*/

function retrieveTableData() {
	$.ajax({
		processData: false,
	    contentType: false,
		url: "/" + $("#edit-area").data("user-id") + "/visualization/" + $("#edit-area").data("vi-id") + "/info?request=data&_token=" + $("[name='hidden-form'] [type='hidden']").val(),
		type: "GET",
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
			tableProps = responseData["tableProps"];
			tableData = responseData["tableData"];
		}
	});
}


$(function() {
	retrieveTableData();
});