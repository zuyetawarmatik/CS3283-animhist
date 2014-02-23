var viProps;

function retrieveVisualizationProperty() {
	$.ajax({
		processData: false,
	    contentType: false,
		url: "/" + $("#edit-area").data("user-id") + "/visualization/" + $("#edit-area").data("vi-id") + "/info?request=property",
		type: "GET",
		headers: {'X-CSRF-Token': $("[name='hidden-form'] [type='hidden']").val()},
		global: false,
		success: function(responseData) {
			viProps = responseData;
		}
	});
}

$(function() {
	retrieveVisualizationProperty();
});