var viProps;
var columnList;

function retrieveVisualizationProperty() {
	$.ajax({
		processData: false,
	    contentType: false,
		url: "/" + $("#edit-area").data("user-id") + "/visualization/" + $("#edit-area").data("vi-id") + "/info?request=property",
		type: "GET",
		headers: {'X-CSRF-Token': getCSRFToken()},
		global: false,
		success: function(responseData) {
			viProps = responseData;
			$(window).trigger("vi_property_loaded");
			columnList = responseData["columnList"];
			addDefaultColumnOptions();
			addColumnListButtons();
		}
	});
}

$(function() {
	retrieveVisualizationProperty();
});

var editFieldRef = 
	{zoom:
		{
			title: "Zoom",
			content: "<table>" +
						"<tr>" +
							"<td>" +
								"<label for='zoom'>Zoom:</label>" +
							"</td>" +
							"<td>" +
								"<input name='zoom' type='text'/>" + 
							"</td>" +
						"</tr>" +
						"<tr>" + "<td></td>" +
							"<td><button type='button' id='current-zoom-btn' onclick='getMapCurrentZoom()'>Use Current Zoom</button></td>" +
						"</tr>" +
					"</table>"
		},
	 center:
	 	{
		 	title: "Center",
		 	content: "<table>" +
						"<tr>" +
							"<td>" +
								"<label for='centerlat'>Latitude:</label>" +
							"</td>" +
							"<td>" +
								"<input name='centerlat' type='text'/>" + 
							"</td>" +
						"</tr>" +
						"<tr>" +
							"<td>" +
								"<label for='centerlong'>Longitude:</label>" +
							"</td>" +
							"<td>" +
								"<input name='centerlong' type='text'/>" + 
							"</td>" +
						"</tr>" +
						"<tr>" + "<td></td>" +
							"<td><button type='button' id='current-center-btn' onclick='getMapCurrentCenter()'>Use Current Center</button></td>" +
						"</tr>" +
					"</table>"
		},
	 category:
	 	{title: "Category"}, 
	 description:
	 	{
		 	title: "Brief Description",
		 	content: "<table>" +
						"<tr>" +
							"<td>" +
								"<label for='description'>Brief Description:</label>" +
							"</td>" +
							"<td>" +
								"<textarea name='description'/>" + 
							"</td>" +
						"</tr>" +
					"</table>"
		}
	 
	};

$(function() {
	$("#description-area .editable .edit-a").on("click", function() {
		var field = $(this).parent().attr("id");
		var message = "Edit " + editFieldRef[field].title + " Field";
		
		vex.dialog.open({
			message: message,
			input: editFieldRef[field].content,
			afterOpen: function() {
				switch (field) {
					case "zoom":
						$("[name='zoom']").val(viProps["zoom"]);
						break;
					case "center":
						$("[name='centerlat']").val(viProps["centerLatitude"]);
						$("[name='centerlong']").val(viProps["centerLongitude"]);
						break;
				}
			},
		});
	});
});

function getMapCurrentZoom() {
	if (map != undefined) {
		$("[name='zoom']").val(map.getZoom().toFixed(2));
	}
}

function getMapCurrentCenter() {
	if (map != undefined) {
		$("[name='centerlat']").val(map.getCenter().lat().toFixed(2));
		$("[name='centerlong']").val(map.getCenter().lng().toFixed(2));
	}
}