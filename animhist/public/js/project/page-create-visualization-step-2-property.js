var viProps;
var columnList;

function retrieveVisualizationProperty() {
	$.ajax({
		url: postURLPrefix + "/info?request=property",
		type: "GET",
		global: false,
		success: function(response) {
			viProps = response;
			columnList = response["columnList"];
			$(window).trigger("vi_property_loaded");
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
	$descriptionArea.find(".edit-a").on("click", function() {
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
					var formData = {};
					switch (field) {
						case "zoom":
							if (data.zoom.trim() == '') return;
							formData["zoom"] = data.zoom.trim();
							break;
						case "center":
							if (data.centerlat.trim() == '' || data.centerlong.trim() == '') return;
							formData["center-latitude"] = data.centerlat.trim();
							formData["center-longitude"] = data.centerlong.trim();
							break;
						case "description":
							if (data.description.trim() == '')
								formData["description"] = "NUL";
							else
								formData["description"] = data.description.trim();
							break;
						case "category":
							if (data.category.trim() == '') return;
							formData["category"] = data.category.trim();
							break;
						case "displayname":
							if (data.displayname.trim() == '') return;
							formData["display-name"] = data.displayname.trim();
							break;
					}
					
					$.ajax({
					    url: postURLPrefix + "/updateproperty",
						type: "POST",
						headers: {'X-CSRF-Token': CSRFToken},
						global: false,
						data: formData,
						error: function(response) {
							var alertSt = "";
							$.each(response["responseJSON"]["error"], function(key, val) {
								$.each(val, function(index, tx) {
									alertSt += tx + "<br/>";
								});
							});
							notyError({
								text: alertSt
							});
						},
						success: function(response) {
							notySuccess({
								layout: 'bottomCenter',
								text: "Property changed"
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
			$("p#zoom").find("span.content").html(viProps["zoom"]);
		if ($.inArray("centerLatitude", fields) >= 0 || $.inArray("centerLongitude", fields) >= 0)
			$("p#center").find("span.content").html(viProps["centerLatitude"] + ", " + viProps["centerLongitude"]);
		if ($.inArray("category", fields) >= 0)
			$("p#category").find("span.content").html(viProps["category"]);
		if ($.inArray("description", fields) >= 0) {
			if (viProps["description"] == null)
				$("p#description + p").html("(The visualization does not have any description yet.)");
			else
				$("p#description + p").html(viProps["description"]);
		}
		if ($.inArray("displayName", fields) >= 0)
			$("h1#displayname").find("span.content").html(viProps["displayName"]);
	});
	
	$buttonArea.find("#delete-btn").on("click", function() {
		$.ajax({
			url: postURLPrefix,
			type: "DELETE",
			headers: {'X-CSRF-Token': CSRFToken},
			global: false,
			error: function() {
				notyError({
					text: "Visualization deletion failed"
				});
			},
			success: function(response) {
				notySuccess({
					text: "Visualization deleted",
					callback: {
						afterShow: function(){handleJSONRedirectResponse(response, false);}
					}
				});
			}
		});
	});
	
	$buttonArea.find("#publish-btn").on("click", function() {
		$.ajax({
		    url: postURLPrefix + "/updateproperty",
			type: "POST",
			headers: {'X-CSRF-Token': CSRFToken},
			global: false,
			data: {"published": true},
			error: function() {
				notyError({
					text: "Visualization publishment failed"
				});
			},
			success: function(response) {
				notySuccess({
					text: "Visualization published",
					callback: {
						afterShow: function(){handleJSONRedirectResponse(response, false);}
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
		$("[name='centerlat']").val(map.getCenter().lat().toFixed(3));
		$("[name='centerlong']").val(map.getCenter().lng().toFixed(3));
	}
}