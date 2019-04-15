@php
	$title = __('Contents');
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
			<ul class="listview">
				<li>
					<a href="javascript:;" data-filename="sitemap.xlsx" data-num="sitemap">
					<h2>sitemap.xlsx</h2>
					<ul class="cont_filelist_sitemap__ext-list">
						<li>
							<lavel>Download：</lavel>
						</li>
						<li>
							<form method="POST" action="{{ url('/download'.'/'.$project->project_name.'/'.$branch_name) }}" enctype="multipart/form-data">
		                    	@csrf
		                        <input type="hidden" name="file" value="csv">
		                        <button type="submit" name="submit" class="px2-btn">{{ __('CSV')}}</button>
	                    	</form>
						</li>
						<li>
							<form method="POST" action="{{ url('/download'.'/'.$project->project_name.'/'.$branch_name) }}" enctype="multipart/form-data">
		                    	@csrf
		                        <input type="hidden" name="file" value="xlsx">
		                        <button type="submit" name="submit" class="px2-btn">{{ __('XLSX')}}</button>
	                    	</form>
						</li>
						<li>
							<button class="px2-btn px2-btn--danger" data-basefilename="sitemap">Delete</button>
						</li>
					</ul>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>
@endsection
