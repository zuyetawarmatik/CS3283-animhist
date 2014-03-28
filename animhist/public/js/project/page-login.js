$(function() {
	$('#register-btn').click(function() {
		var endReferer = getUrlParameters("referer", "", true);
		var cs = $(this).data('url').split("?").length > 1 ? "&" : "?";
		var url = endReferer ? $(this).data('url') + cs + "end-referer=" + endReferer : $(this).data('url');
		parent.changeIFrameSrc(url, true);
	});
	
	$("[name='login-form']").submit(function(event) {
		event.preventDefault();
		var formData = new FormData(this);
		var endReferer = getUrlParameters("referer", "", true);
		if (!endReferer) endReferer = getUrlParameters("referer", window.top.location.href, true);
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
				handleJSONRedirectResponse(response, false);
			}
		});
	});
	
});