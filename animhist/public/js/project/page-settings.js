$(function() {
	$("[name='settings-form']").submit(function(event) {
		event.preventDefault();
		$.ajax({
			processData: false,
			url: this.action,
			type: 'PUT',
			data: $(this).serialize(),
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
					killer: true,
					timeout: 2000,
					maxVisible: 1
				});
			},
		
			success: function(responseData) {
				noty({
					layout: 'center',
					text: "Update information successfully!",
					type: 'success',
					killer: true,
					timeout: 500
				});
			}
		});
	});
	
});