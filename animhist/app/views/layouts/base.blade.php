<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
		
		<title>Animated History</title>
		
		<!-- Stylesheet -->
		{{ HTML::style('css/reset.css'); }}
		{{ HTML::style('fonts/icomoon/stylesheet.css'); }}
		<link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,200,200italic,300,300italic,400italic,600,600italic,700,700italic,900,900italic&subset=latin,vietnamese,latin-ext' rel='stylesheet' type='text/css'>
		<link rel="stylesheet/less" type="text/css" href="css/base/base-left.less.css" />
		<!---------------->
		
		<!-- Script -->
		{{ HTML::script('js/less-1.4.2.min.js'); }}
		{{ HTML::script('js/jquery-2.0.3.min.js'); }}
		{{ HTML::script('js/jquery-ui-1.10.3/ui/jquery-ui.js'); }}
		{{ HTML::script('js/project/left-sidebar.js'); }}
		<!---------------->
		
	</head>
	<body>
		<section id="left-panel">
			@if (true)
				<div id="user-bar">	
					<img id="avatar" src="images/avatar.jpg" width="60" height="60" />
					<div id="username"><a href="#">{{ $username }}</a></div>
					<div id="logout-btn">&#57603;</div>
				</div> 
				<ul id="nav-list">
					<li class="nav-item before-selected">
						<span class="nav-icon">&#57513;</span>
						<span class="nav-caption">My Visualizations</span>
					</li>
					<li class="nav-item selected">
						<span class="nav-icon">&#57552;</span>
						<span class="nav-caption">Featured</span>
					</li>
					<li class="nav-item after-selected">
						<span class="nav-icon">&#57553;</span>
						<span class="nav-caption">Following</span>
					</li>
					<li class="nav-item">
						<span class="nav-icon">&#57488;</span>
						<span class="nav-caption">Settings</span>
					</li>
					<li class="nav-item">
						<span class="nav-icon">&#57471;</span>
						<span class="nav-caption">Search</span>
					</li>
				</ul>
			@endif
		</section>
		<section id="main-panel">
			<iframe style="width: 100%; height: 100%" src="{{ $main_panel_iframe_url }}" scrolling="no"></iframe>
		</section>
	</body>
</html>