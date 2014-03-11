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
	
	$("[name='changepwd-btn'").click(function() {
		var vexContent = "<table>" + 
								"<tr>" +
									"<td>" +
										"<label for='oldpassword'>Old Password:</label>" +
									"</td>" +
									"<td>" +
										"<input name='oldpassword' type='password'/>" +
									"</td>" +
								"</tr>" +
								"<tr>" +
									"<td>" +
										"<label for='newpassword'>New Password:</label>" +
									"</td>" +
									"<td>" +
										"<input name='newpassword' type='password'/>" +
									"</td>" +
								"</tr>" +
								"<tr>" +
									"<td>" +
										"<label for='retypepassword'>Re-type New Password:</label>" +
									"</td>" +
									"<td>" +
										"<input name='retypepassword' type='password'/>" +
									"</td>" +
								"</tr>" +
							"</table>";
			vex.dialog.open({
				message: "Change Password",
				input: vexContent,
			
				callback: function(data){
					var ajaxVar = {
						processData: false,
						contentType: false,
					    url: "/" + 'user1' + "/updatepassword",
						type: "POST",
						headers: {'X-CSRF-Token': $("[name='hidden-form'] [type='hidden']").val()},
						global: false,
						beforeSend: function() {
							noty({
								layout: 'bottomCenter',
								text: '.................',
								type: 'information',
								animation: {
									open: {height: 'toggle'},
									close: {height: 'toggle'},
									easing: 'swing',
								    speed: 300
								},
								maxVisible: 1
							});
						},
						error: function(responseData) {
							noty({
								layout: 'bottomCenter',
								text: "Updating data error, rolling back...",
								type: 'error',
								killer: true,
								timeout: 500,
								maxVisible: 1
							});
						},
						data: new FormData(this),
						success: function(responseData) {
							var notySuccessVar = $.extend({}, notySuccessTemplate2, {
								text: "Password changed, refreshing page..."
							});
							noty(notySuccessVar);
						}
						
					};
					$.ajax(ajaxVar);
				}
			});

	});
});