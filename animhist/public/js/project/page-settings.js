$(function() {
	$("[name='settings-form']").submit(function(event) {
		event.preventDefault();
		$.ajax({
			processData: false,
			url: this.action,
			type: 'PUT',
			data: $(this).serialize(),
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
		
			success: function() {
				notySuccess({
					text: "Update information successfully!"
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
				if (data) {
					var formData = {
						"password-old": data.oldpassword,
						"password-new": data.newpassword,
						"password-retype": data.retypepassword
					};
					$.ajax({
						url: "/" + $("[name='settings-form']").data("user-id") + "/updatepassword",
						type: "POST",
						headers: {'X-CSRF-Token': $("[name='_token']").val()},
						data: formData,
						error: function(response) {
							var alertSt = "";
							$.each(response["responseJSON"]["error"], function(key, val) {
								$.each(val, function(index, tx) {
									alertSt += tx + "<br/>";
								});
							});
							notyError({
								text: alertSt
							});
						},
						success: function() {
							notySuccess({
								text: "Password changed"
							});
						}
					});
				}
			}
		});
	});
});