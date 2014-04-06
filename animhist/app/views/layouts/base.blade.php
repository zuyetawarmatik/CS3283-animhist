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
		<link rel="stylesheet/less" type="text/css" href="/css/base/base-left.less.css" />
		<!---------------->
		
		<!-- Script -->
		{{ HTML::script('js/less-1.4.2.min.js'); }}
		{{ HTML::script('js/jquery-2.1.0.min.js'); }}
		{{ HTML::script('js/jquery-ui-1.10.3/ui/jquery-ui.js'); }}
		{{ HTML::script('js/attrchange/attrchange.js'); }}
		{{ HTML::script('js/project/left-sidebar.js'); }}
		<!---------------->
	</head>
	<body>
		<section id="left-panel" data-loggedin = "{{ Auth::check() }}">
			@if (Auth::check())
				<div id="user-bar">	
					<img id="avatar" style="background:#666" src="{{ Auth::user()->avatar->url('thumb') }}" />
					<div id="username">
						<a href="{{ URL::route('user.show', [Auth::user()->username]) }}">{{ '@'.Auth::user()->username }}</a>
						<div id="logout-btn">&#57603;</div>
					</div>
				</div> 
				<ul id="nav-list" data-highlight-id = "{{ $highlight_id }}">
					<li class="nav-item" data-url="{{ URL::route('user.show', [Auth::user()->username]) }}">
						<span class="nav-icon">&#57513;</span>
						<span class="nav-caption">My Visualizations</span>
					</li>
					<li class="nav-item" data-url="{{ URL::to('featured') }}">
						<span class="nav-icon">&#57552;</span>
						<span class="nav-caption">Featured</span>
					</li>
					<li class="nav-item" data-url="{{ URL::route('visualization.showFollowing') }}">
						<span class="nav-icon">&#57553;</span>
						<span class="nav-caption">Following</span>
					</li>
					<li class="nav-item" data-url="{{ URL::route('user.showEdit', [Auth::user()->username]) }}">
						<span class="nav-icon">&#57488;</span>
						<span class="nav-caption">Settings</span>
					</li>
					<li class="nav-item" data-url="{{ URL::route('visualization.showSearch') }}">
						<span class="nav-icon">&#57471;</span>
						<span class="nav-caption">Search</span>
					</li>
					@if ($highlight_id == Constant::SIDEBAR_USERVISUALIZATION)
					<li class="nav-item" data-url="{{ URL::route('user.show', $user->username) }}">
						<span class="nav-icon">&#57513;</span>
						<span class="nav-caption">{{ $user->display_name.'&#39;s Visualizations' }}</span>
					</li>
					@endif
				</ul>
			@else
				<ul id="nav-list" data-highlight-id = "{{ $highlight_id }}">
					<li class="nav-item" data-url="{{ URL::route('user.showLogin') }}">
						<span class="nav-icon">&#57604;</span>
						<span class="nav-caption">Login</span>
					</li>
					<li class="nav-item" data-url="{{ URL::to('featured') }}">
						<span class="nav-icon">&#57552;</span>
						<span class="nav-caption">Featured</span>
					</li>
					<li class="nav-item" data-url="{{ URL::route('visualization.showSearch') }}">
						<span class="nav-icon">&#57471;</span>
						<span class="nav-caption">Search</span>
					</li>
					@if ($highlight_id == Constant::SIDEBAR_GUEST_USERVISUALIZATION)
					<li class="nav-item" data-url="{{ URL::route('user.show', $user->username) }}">
						<span class="nav-icon">&#57513;</span>
						<span class="nav-caption">{{ $user->display_name.'&#39;s Visualizations' }}</span>
					</li>
					@endif
				</ul>
			@endif
		</section>
		<section id="main-panel">
			<iframe style="width:100%;height:100%" scrolling="no" src="{{ $main_panel_iframe_url }}"></iframe>
		</section>
	</body>
</html>