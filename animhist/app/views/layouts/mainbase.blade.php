<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="Cache-Control" content="no-cache">
	   	<meta http-equiv="Pragma" content="no-cache">
	   	<meta http-equiv="Expires" content="0">
					
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
		{{ HTML::script('js/project/main-shared.js'); }}
		@yield('js')
		<!---------------->
	</head>
	<body>
		@yield('body')
	</body>
</html>