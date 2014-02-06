<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
	   	<meta http-equiv="Pragma" content="no-cache">
	   	<meta http-equiv="Expires" content="0">
					
		<!-- Stylesheet -->
		{{ HTML::style('css/reset.css'); }}
		{{ HTML::style('js/flatstrap/css/bootstrap.min.css'); }}
		{{ HTML::style('fonts/icomoon/stylesheet.css'); }}
		{{ HTML::style('fonts/Source_Sans_Pro/stylesheet.css'); }}
		@yield('css')
		<!---------------->
		
		<!-- Script -->
		{{ HTML::script('js/less-1.4.2.min.js'); }}
		{{ HTML::script('js/jquery-2.0.3.min.js'); }}
		{{ HTML::script('js/jquery-ui-1.10.3/ui/jquery-ui.js'); }}
		{{ HTML::script('js/flatstrap/js/bootstrap.min.js'); }}
		{{ HTML::script('js/noty-2.2.2/js/noty/packaged/jquery.noty.packaged.min.js'); }}
		{{ HTML::script('js/project/main-shared.js'); }}
		@yield('js')
		<!---------------->
	</head>
	<body>
		@yield('body')
	</body>
</html>