@php
	$title = __('Sitemaps');
@endphp
@extends('layouts.default')

@section('content')

<div class="px2-p">
	<div class="btn-group cont_buttons" role="group">
		@component('components.btn_sitemap_upload')
			@slot('controller', 'sitemap')
			@slot('project_code', $project->project_code)
			@slot('branch_name', $branch_name)
			@slot('errors', $errors)
		@endcomponent
		{{-- <button class="btn px2-btn">サイトマップをコミットする</button>
		<button class="btn px2-btn">コミットログ</button> --}}
		@component('components.btn_sitemap_help')
			@slot('controller', 'sitemap')
		@endcomponent
	</div>
</div>
<div class="cont_filelist_sitemap" style="height: 630px;">
	@if(isset($get_files))
		<ul class="listview">
		@foreach($get_files as $file)
			<li>
				<a href="javascript:;" data-filename="{{ $file['basename'] }}" data-num="sitemap">
				<h2>{{ $file['basename'] }}</h2>
				<ul class="cont_filelist_sitemap__ext-list">
					<li>
						<label>Download：</label>
					</li>
					@foreach($file['extensions'] as $extension)
					<li>
						<form method="POST" action="{{ url('/sitemaps/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/download?file_name='.$extension['basename']) }}" enctype="multipart/form-data">
							@csrf
							<input type="hidden" name="file" value="{{ $extension['ext'] }}">
							<button type="submit" name="submit" class="px2-btn">{{ $extension['ext'] }}</button>
						</form>
					</li>
					@endforeach
					<li>
						@component('components.btn_sitemap_destroy')
							@slot('controller', 'sitemap')
							@slot('project_code', $project->project_code)
							@slot('branch_name', $branch_name)
							@slot('file_name', $file['basename'])
						@endcomponent
					</li>
				</ul>
				</a>
			</li>
		@endforeach
		</ul>
	@endif
</div>

@endsection
