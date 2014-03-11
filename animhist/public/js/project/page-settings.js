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
	
	$("[name='changepwd-btn']").click(function() {
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
				var formData = new FormData();
				formData.append("password-old", data.oldpassword);
				formData.append("password-new", data.newpassword);
				formData.append("password-retype", data.retypepassword);
				$.ajax({
					processData: false,
					contentType: false,
				    url: "/" + $("[name='settings-form']").data("user-id") + "/updatepassword",
					type: "POST",
					headers: {'X-CSRF-Token': $("[name='settings-form'] [name='_token']").val()},
					global: false,
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
							timeout: 1000,
							maxVisible: 1
						});
					},
					success: function(responseData) {
						noty({
							layout: 'bottomCenter',
							text: "Password changed",
							type: 'success',
							killer: true,
							timeout: 500,
							maxVisible: 1
						});
					}
				});
			}
		});
	});
});