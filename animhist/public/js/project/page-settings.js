var CSRFToken;

$(function() {
	CSRFToken = $("[name='_token']").val();
	$followerList = $("#follower-list");
	$followingList = $("#following-list");
	$settingsForm = $("[name='settings-form']");
	
	$followerList.on("click", "a", function(e) {
		e.preventDefault();
		parent.changeIFrameSrc($(this).attr("href"), true);
	});
	
	$followingList.on("click", "a", function(e) {
		e.preventDefault();
		parent.changeIFrameSrc($(this).attr("href"), true);
	});
	
	$followingList.on("click", "div.unfollow-btn", function(e) {
		$thisLi = $(this).closest("li.following-item");
		$.ajax({
			url: $(this).data("url"),
			type: "POST",
			global: false,
			headers: {'X-CSRF-Token': CSRFToken},
			error: function() {
				notyError({
					text: "Error"
				});
			},
			success: function() {
				$thisLi.slideUp(500, function(){
					$thisLi.remove();
					if ($followingList.empty()) $followingList.html("<label>(None)</label>");
				});
			}
		});
	});
	
	$settingsForm.submit(function(event) {
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
						url: "/" + $settingsForm.data("user-id") + "/updatepassword",
						type: "POST",
						headers: {'X-CSRF-Token': CSRFToken},
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