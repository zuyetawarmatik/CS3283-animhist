$(function() {
	$("[name='register-form']").submit(function(event) {
		event.preventDefault();
		var formData = new FormData(this);
		$.ajax({
			processData: false,
		    contentType: false,
			url: this.action,
			type: this.method,
			data: formData,
			error: function(responseData) {
				var alertSt = "";
				$.each(responseData["responseJSON"], function(key, val) {
					$.each(val, function(index, tx) {
						alertSt += tx + "\n";
					});
				});
				alert(alertSt);
			}
		});
	});
});