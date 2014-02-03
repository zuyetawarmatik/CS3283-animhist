$(function() {
	$("[name='login-form']").submit(function(event) {
		event.preventDefault();
		var formData = new FormData(this);
		var endReferer = getUrlParameters("referer", "", true);
		if (endReferer) formData.append("referer", endReferer);
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
				window.top.location.href = responseData["redirect"];
			}
		});
	});
	
});