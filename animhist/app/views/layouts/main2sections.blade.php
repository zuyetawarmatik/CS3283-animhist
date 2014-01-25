<div id="top-bar">
	@if ($has_back)
		<div id="back-btn" data-url="{{ $back_url }}">&#57446;</div>
		<div id="title">{{ $title }}</div>
	@else
		<div id="title" class="no-back">{{ $title }}</div>
	@endif
</div>
<div id="main-area"></div>