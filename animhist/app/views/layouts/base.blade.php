<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
		<title>Animated History</title>
		
		<!-- Stylesheet -->
		{{ HTML::style('css/reset.css'); }}
		<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,200,200italic,300,300italic,400italic,600,600italic,700,700italic,900,900italic&subset=latin,vietnamese,latin-ext' rel='stylesheet' type='text/css'>
		<link rel="stylesheet/less" type="text/css" href="css/base.less.css" />
		<!---------------->
		
		<!-- Script -->
		{{ HTML::script('js/less-1.4.2.min.js'); }}
		<!---------------->
		
	</head>
	<body>
		<section id="left-panel">
			@if (true)
				<div id="user-bar">	
					<img id="avatar" src="images/avatar.jpg" width="60" height="60" />
					<div id="username"><a href="#">{{ $username }}</a></div>
					<div id="logout"></div>
				</div> 
				<ul class="nav-list">
					<li class="nav-item">
						<span class="nav-icon"></span>
						<span class="nav-caption"></span>
					</li>
					<li class="nav-item">
						<span class="nav-icon"></span>
						<span class="nav-caption"></span>
					</li>
					<li class="nav-item">
						<span class="nav-icon"></span>
						<span class="nav-caption"></span>
					</li>
					<li class="nav-item">
						<span class="nav-icon"></span>
						<span class="nav-caption"></span>
					</li>
					<li class="nav-item">
						<span class="nav-icon"></span>
						<span class="nav-caption"></span>
					</li>
				</ul>
			@endif
		</section>
		<section id="main-panel">
			@yield('main-panel-content')
		</section>
	</body>
</html>