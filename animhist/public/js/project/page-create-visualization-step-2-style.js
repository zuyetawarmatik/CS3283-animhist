function retrieveStyle(column) {
	$.ajax({
		processData: false,
	    contentType: false,
		url: getPOSTURLPrefix() + "/info?request=style&column=" + column,
		type: "GET",
		global: false,
		headers: {'X-CSRF-Token': getCSRFToken()},
		success: function(response) {
			
		}
	});	
}

$(window).on('vi_property_loaded', function() {
	retrieveStyle(viProps["defaultColumn"]);
});