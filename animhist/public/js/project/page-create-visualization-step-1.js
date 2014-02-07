var columnList = [
                  {"caption":"Milestone", "type-caption":"Year", "editable":true, "deletable":false},
                  {"caption":"Position", "type-caption":"Location: KML or Lat/Long", "editable":false, "deletable":false},
                  {"caption":"HTMLData", "type-caption":"String", "editable":false, "deletable":true},
                  {"caption":"New Valuable", "type-caption":"Number", "editable":true, "deletable":true}
                 ];

$(window).load(function() {
	$.each(columnList, function(i, obj) {
		var caption = obj["caption"] + " (" + obj["type-caption"] + ")";
		var element = $(document.createElement("li")).addClass("btn-group").html("<button>" + caption + "</button>");
		if (obj["editable"])
			$("<button class='column-edit-btn'>&#57350;</button>").appendTo(element);
			
		if (!obj["deletable"])
			element.addClass("red-btn-group");
		else
			$("<button class='column-delete-btn'>&#57594;</button>").appendTo(element);
		
		element.appendTo("#column-list");
	});
	
	$(".red-btn-group button").each(function() {
		$(this).addClass("red-btn");
	});
	
	$("<li class='btn-group'><button class='column-add-btn grey-btn'>&#57602;</button></li>").appendTo("#column-list");
});

$(function() {
	$("[name='create-visualization-form']").submit(function(event) {
		event.preventDefault();
		
		var formData = new FormData(this);
		formData.append("column-list", JSON.stringify(columnList));
		$.ajax({
			processData: false,
		    contentType: false,
			url: this.action,
			type: this.method,
			data: formData,
			error: function(responseData) {
				var alertSt = "";
				$.each(responseData["responseJSON"]["error"], function(key, val) {
					$.each(val, function(index, tx) {
						alertSt += tx + "<br/>";
					});
				});
				noty({
					layout: 'bottomCenter',
					text: alertSt,
					type: 'error',
					timeout: 3000,
					maxVisible: 1
				});
			},
		
			success: function(responseData) {
				//window.top.location.href = responseData["redirect"];
			}
		});
	});
	
});