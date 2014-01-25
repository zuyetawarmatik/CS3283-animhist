$(function() {
	$('#register-btn').click(function() {
		parent.changeIFrameSrc($(this).data('url'), true);
	});
	
	$("[name='login-form']").submit(function(event) {
		event.preventDefault();
		$.ajax({
			url: this.action,
			type: this.method,
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