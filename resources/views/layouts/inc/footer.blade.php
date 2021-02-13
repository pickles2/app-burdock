{{-- JavaScript --}}
{{-- <script src="{{ asset('js/custom.js') }}"></script> --}}

<script>
window.addEventListener('load', function(){
	var current = '';
	@if (Request::is('login') || Request::is('login/*')) current = 'login'; @endif
	@if (Request::is('register') || Request::is('register/*')) current = 'register'; @endif
	@if (Request::is('home/*')) current = 'home'; @endif
	@if (Request::is('sitemaps/*')) current = 'sitemaps'; @endif
	@if (Request::is('themes/*')) current = 'themes'; @endif
	@if (Request::is('contents/*')) current = 'contents'; @endif
	@if (Request::is('publish/*')) current = 'publish'; @endif
	@if (Request::is('projects/*')) current = 'projects'; @endif
	@if (Request::is('composer/*')) current = 'composer'; @endif
	@if (Request::is('git/*')) current = 'git'; @endif
	@if (Request::is('search/*')) current = 'search'; @endif
	@if (Request::is('staging/*')) current = 'staging'; @endif
	@if (Request::is('delivery/*')) current = 'delivery'; @endif
	@if (Request::is('files-and-folders/*')) current = 'files-and-folders'; @endif
	@if( isset($global->cce) && (is_object($global->cce) || is_array($global->cce)) )
		@foreach($global->cce as $cce_id=>$cce_info)
			@if (Request::is('custom_console_extensions/'.$cce_id.'/*')) current = 'custom_console_extensions.{{$cce_id}}'; @endif
		@endforeach
	@endif
	@if (Request::is('system-maintenance') || Request::is('system-maintenance/*')) current = 'system-maintenance'; @endif
	@if (Request::is('mypage') || Request::is('mypage/*')) current = 'mypage'; @endif
	px2style.header.init({'current': current});
});
</script>

<!-- App Resources -->
<script src="{{ asset('js/app.js') }}"></script>
