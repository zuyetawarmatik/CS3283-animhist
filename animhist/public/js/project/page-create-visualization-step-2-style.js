function retrieveStyle(column) {
	$.ajax({
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