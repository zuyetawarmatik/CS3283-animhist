<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
		
		<!-- Stylesheet -->
		{{ HTML::style('css/reset.css'); }}
		{{ HTML::style('fonts/icomoon/stylesheet.css'); }}
		<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,200,200italic,300,300italic,400italic,600,600italic,700,700italic,900,900italic&subset=latin,vietnamese,latin-ext' rel='stylesheet' type='text/css'>
		@yield('css')
		<!---------------->
		
		<!-- Script -->
		{{ HTML::script('js/less-1.4.2.min.js'); }}
		{{ HTML::script('js/jquery-2.0.3.min.js'); }}
		{{ HTML::script('js/jquery-ui-1.10.3/ui/jquery-ui.js'); }}
		@yield('js')
		<!---------------->
	</head>
	<body>
		<div id="left-area" class="main4sections">
			<div id="top-bar">
				@if (true)
					<div id="back-btn">&#57446;</div>
				@endif
				<div id="title">
					{{ $title }}
				</div>
			</div>
			<div id="left-area-main">
				@yield('left-area-main')
			</div>
		</div>
		<div id="right-area" class="main4sections">
			<div id="action-bar">
				@if (true)
					<div id="like-btn">&#57556;</div>
				@endif
				<div id="like-info">
					{{ $like_info }}
				</div>
				<div id="right-area-showhide-btn">&#57477;</div>
			</div>
			<div id="right-area-main">
				@yield('right-area-main')
			</div>
		</div>
	</body>
</html>