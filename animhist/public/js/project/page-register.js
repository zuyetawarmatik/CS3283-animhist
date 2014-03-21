$(function() {
	$("[name='register-form']").submit(function(event) {
		event.preventDefault();
		var formData = new FormData(this);
		var endReferer = getUrlParameters("end-referer", "", true);
		if (!endReferer) endReferer = getUrlParameters("end-referer", window.top.location.href, true);
		if (endReferer) formData.append("referer", endReferer);
		$.ajax({
			processData: false,
		    contentType: false,
			url: this.action,
			type: this.method,
			data: formData,
			error: function(response) {
				var alertSt = "";
				$.each(response["responseJSON"]["error"], function(key, val) {
					$.each(val, function(index, tx) {
						alertSt += tx + "<br/>";
					});
				});
				notyError({
					text: alertSt,					
					timeout: 2000
				});
			},
			success: function(response) {
				notySuccess({
					layout: 'center',
					text: "Register successfully!<br/>Redirecting to your personal page...",
					callback: {
						afterShow: function(){handleJSONRedirectResponse(response, false);}
					}
				});
			}
		});
	});
});