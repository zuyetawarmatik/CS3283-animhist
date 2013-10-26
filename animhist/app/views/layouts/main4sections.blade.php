<div id="left-area">
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
<div id="right-area">
	<div id="action-bar">
		@if (true)
			<div id="like-btn">&#57556;</div>
		@endif
		<div id="like-info">
			{{ $like_info }}
		</div>
	</div>
	<div id="right-area-main">
		@yield('right-area-main')
	</div>
</div>