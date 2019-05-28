@php
	$title = __('Sitemaps');
@endphp
@extends('layouts.px2_project')

@section('content')
<div class="container">
	<h1>サイトマップ</h1>
	<div class="contents">
		<div class="btn-group cont_buttons" role="group">
			@component('components.btn_sitemap_upload')
                @slot('controller', 'sitemap')
				@slot('project_name', $project->project_name)
				@slot('branch_name', $branch_name)
				@slot('errors', $errors)
            @endcomponent
			<button class="btn px2-btn">サイトマップをコミットする</button>
			<button class="btn px2-btn">コミットログ</button>
			<button class="btn px2-btn">ヘルプ</button>
		</div>
		<div class="cont_filelist_sitemap" style="height: 630px;">
			@if(isset($get_files))
				@foreach($get_files as $file)
				<ul class="listview">
					<li>
						<a href="javascript:;" data-filename="sitemap.xlsx" data-num="sitemap">
						<h2>{{ $file->getFilename() }}</h2>
						<ul class="cont_filelist_sitemap__ext-list">
							<li>
								<lavel>Download：</lavel>
							</li>
							<li>
								<form method="POST" action="{{ url('/sitemaps/'.$project->project_name.'/'.$branch_name.'/download?file_name='.$file->getFilename()) }}" enctype="multipart/form-data">
			                    	@csrf
			                        <input type="hidden" name="file" value="csv">
			                        <button type="submit" name="submit" class="px2-btn">{{ __('CSV')}}</button>
		                    	</form>
							</li>
							<li>
								<form method="POST" action="{{ url('/sitemaps/'.$project->project_name.'/'.$branch_name.'/download?file_name='.$file->getFilename()) }}" enctype="multipart/form-data">
			                    	@csrf
			                        <input type="hidden" name="file" value="xlsx">
			                        <button type="submit" name="submit" class="px2-btn">{{ __('XLSX')}}</button>
		                    	</form>
							</li>
							<li>
								@component('components.btn_sitemap_destroy')
					                @slot('controller', 'sitemap')
									@slot('project_name', $project->project_name)
									@slot('branch_name', $branch_name)
									@slot('file_name', $file->getFilename())
					            @endcomponent
							</li>
						</ul>
						</a>
					</li>
				</ul>
				@endforeach
			@endif
		</div>
	</div>
</div>
@endsection
