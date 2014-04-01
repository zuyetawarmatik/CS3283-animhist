$(function() {
	$("[name='search-form']").submit(function(event) {
		event.preventDefault();
		$.ajax({
			url: this.action,
			type: this.method,
			data: {q: $("[name='search-box']").val()},
			success: function(response) {
				
			}
		});
	});
});