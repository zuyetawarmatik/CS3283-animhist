var viProps;
var columnList;

function retrieveVisualizationProperty() {
	$.ajax({
		url: getPOSTURLPrefix() + "/info?request=property",
		type: "GET",
		headers: {'X-CSRF-Token': getCSRFToken()},
		global: false,
		success: function(response) {
			viProps = response;
			$(window).trigger("vi_property_loaded");
			columnList = response["columnList"];
			addDefaultColumnOptions();
			addColumnListButtons();
		}
	});
}

$(function() {
	retrieveVisualizationProperty();
});

var editFieldRef = 
	{
	zoom:
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
	 	{
		 	title: "Category",
		 	content: "<table>" +
						"<tr>" +
							"<td>" +
								"<label for='category'>Category:</label>" +
							"</td>" +
							"<td>" +
								"<input name='category' type='text'/>" + 
							"</td>" +
						"</tr>" +
					"</table>"
		}, 
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
		},
	displayname:
		{
		 	title: "Display Name",
		 	content: "<table>" +
						"<tr>" +
							"<td>" +
								"<label for='displayname'>Display Name:</label>" +
							"</td>" +
							"<td>" +
								"<input name='displayname' type='text'/>" + 
							"</td>" +
						"</tr>" +
					"</table>"
		}
	};

$(function() {
	$("#description-area .editable .edit-a").on("click", function() {
		var field = $(this).parent().attr("id");
		var message = "Edit '" + editFieldRef[field].title + "' Field";
		
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
					case "description":
						$("[name='description']").val(viProps["description"]);
						break;
					case "category":
						$("[name='category']").val(viProps["category"]);
						break;
					case "displayname":
						$("[name='displayname']").val(viProps["displayName"]);
						break;
				}
			},
			callback: function(data) {
				if (data) {
					var formData = new FormData();
					switch (field) {
						case "zoom":
							if (data.zoom.trim() == '') return;
							formData.append("zoom", data.zoom.trim());
							break;
						case "center":
							if (data.centerlat.trim() == '' || data.centerlong.trim() == '') return;
							formData.append("center-latitude", data.centerlat.trim());
							formData.append("center-longitude", data.centerlong.trim());
							break;
						case "description":
							if (data.description.trim() == '')
								formData.append("description", "NUL");
							else
								formData.append("description", data.description.trim());
							break;
						case "category":
							if (data.category.trim() == '') return;
							formData.append("category", data.category.trim());
							break;
						case "displayname":
							if (data.displayname.trim() == '') return;
							formData.append("display-name", data.displayname.trim());
							break;
					}
					
					$.ajax({
						processData: false,
						contentType: false,
					    url: getPOSTURLPrefix() + "/updateproperty",
						type: "POST",
						headers: {'X-CSRF-Token': getCSRFToken()},
						global: false,
						data: formData,
						error: function(response) {
							var alertSt = "";
							$.each(response["responseJSON"]["error"], function(key, val) {
								$.each(val, function(index, tx) {
									alertSt += tx + "<br/>";
								});
							});
							noty({
								layout: 'bottomCenter',
								text: alertSt,
								type: 'error',
								killer: true,
								timeout: 1000,
								maxVisible: 1
							});
						},
						success: function(response) {
							noty({
								layout: 'bottomCenter',
								text: "Property changed",
								type: 'success',
								killer: true,
								timeout: 500,
								maxVisible: 1
							});
							
							var fields = [];
							$.each(response, function(key, val) {
								viProps[key] = response[key];
								fields.push(key);
							});
							$(window).trigger({
								type: "vi_property_changed",
								fields: fields
							});
						}
					});
				}
			}
		});
	});
	
	$(window).on("vi_property_changed", function(e) {
		var fields = e.fields;
		if ($.inArray("zoom", fields) >= 0)
			$("p#zoom span.content").html(viProps["zoom"]);
		if ($.inArray("centerLatitude", fields) >= 0 || $.inArray("centerLongitude", fields) >= 0)
			$("p#center span.content").html(viProps["centerLatitude"] + ", " + viProps["centerLongitude"]);
		if ($.inArray("category", fields) >= 0)
			$("p#category span.content").html(viProps["category"]);
		if ($.inArray("description", fields) >= 0) {
			if (viProps["description"] == null)
				$("p#description + p").html("(The visualization does not have any description yet.)");
			else
				$("p#description + p").html(viProps["description"]);
		}
		if ($.inArray("displayName", fields) >= 0)
			$("h1#displayname span.content").html(viProps["displayName"]);
	});
	
	$("#button-area #delete-btn").on("click", function() {
		$.ajax({
			url: getPOSTURLPrefix(),
			type: "DELETE",
			headers: {'X-CSRF-Token': getCSRFToken()},
			global: false,
			error: function() {
				noty({
					layout: 'bottomCenter',
					text: "Visualization deletion failed",
					type: 'error',
					killer: true,
					timeout: 1000,
					maxVisible: 1
				});
			},
			success: function(response) {
				noty({
					layout: 'center',
					text: "Visualization deleted",
					type: 'success',
					killer: true,
					timeout: 500,
					maxVisible: 1,
					callback: {
						afterShow: function(){handleJSONRedirectResponse(response, false);}
					}
				});
			}
		});
	});
	
	$("#button-area #publish-btn").on("click", function() {
		$.ajax({
		    url: getPOSTURLPrefix() + "/updateproperty",
			type: "POST",
			headers: {'X-CSRF-Token': getCSRFToken()},
			global: false,
			data: {"published": true},
			error: function() {
				noty({
					layout: 'bottomCenter',
					text: "Visualization publishment failed",
					type: 'error',
					killer: true,
					timeout: 1000,
					maxVisible: 1
				});
			},
			success: function(response) {
				noty({
					layout: 'center',
					text: "Visualization published",
					type: 'success',
					killer: true,
					timeout: 500,
					maxVisible: 1,
					callback: {
						//afterShow: function(){handleJSONRedirectResponse(response, false);}
					}
				});
			}
		});
	});
});

function getMapCurrentZoom() {
	if (map != undefined) {
		$("[name='zoom']").val(map.getZoom());
	}
}

function getMapCurrentCenter() {
	if (map != undefined) {
		$("[name='centerlat']").val(map.getCenter().lat().toFixed(2));
		$("[name='centerlong']").val(map.getCenter().lng().toFixed(2));
	}
}