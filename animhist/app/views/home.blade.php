<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Expires" content="0">

		<title>Animated History</title>
		
		<!-- Stylesheet -->
		{{ HTML::style('css/reset.css'); }}
		{{ HTML::style('fonts/icomoon/stylesheet.css'); }}
		{{ HTML::style('fonts/Source_Sans_Pro/stylesheet.css'); }}
		<link rel="stylesheet/less" type="text/css" href="/css/home.less.css" />
		<!---------------->
		
		<!-- Script -->
		{{ HTML::script('js/less-1.4.2.min.js'); }}
		{{ HTML::script('js/jquery-2.1.0.min.js'); }}
		{{ HTML::script('js/jquery-ui-1.10.3/ui/jquery-ui.js'); }}
		<!---------------->
		
		<script>
			$(function() {
				$("button").click(function() {
					window.location = $(this).data("url");
				});
			});
		</script>
	</head>
	<body>
		<section id="welcome">
			<div id="welcome-text">
				<h1>Welcome to Animhist!</h1>
				<p>Explore the ever-changing world with us. Be a geek historian.</p>
			</div>
			<div id="buttons">
				<button data-url="{{URL::Route('user.showLogin')}}" class="red-btn">Login</button>
				<button data-url="{{URL::Route('visualization.showFeatured')}}">Our Featured Playlist</button>
			</div>
		</section>
	</body>
</html>