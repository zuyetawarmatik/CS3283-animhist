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
					text: "Register successfully!<br/>Redirecting to your personal page...",
					type: 'success',
					killer: true,
					timeout: 1000,
					callback: {
						afterShow: function(){handleJSONRedirectResponse(responseData, false);}
					}
				});
			}
		});
	});
});