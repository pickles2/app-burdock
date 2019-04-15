@php
	$title = __('Contents');
@endphp
@extends('layouts.px2_project')

@section('content')
<div class="container">
	<h1>サイトマップ</h1>
	<div class="contents">
		<div class="btn-group cont_buttons" role="group">
			<button class="btn px2-btn" onclick="contApp.openInFinder(); return false;">sitemaps フォルダを開く</button>
			<button class="btn px2-btn px2-btn--primary" onclick="contApp.commitSitemap(); return false;">サイトマップをコミットする</button>
			<button class="btn px2-btn" onclick="contApp.logSitemap(); return false;">コミットログ</button>
			<button class="btn px2-btn" onclick="contApp.openManual(); return false;">ヘルプ</button>
		</div>
		<div class="cont_filelist_sitemap" style="height: 745px;">
			<ul class="listview">
				<li>
					<a href="javascript:;" data-filename="sitemap.xlsx" data-num="sitemap">
					<h2>sitemap.xlsx</h2>
					<ul class="cont_filelist_sitemap__ext-list">
						<li>
							<lavel>Upload：</lavel>
						</li>
						<li>
							<form class="form-inline" method="POST" action="{{ url('/upload'.'/'.$project->project_name.'/'.$branch_name) }}" enctype="multipart/form-data">
								@csrf
								@method('POST')
								<div class="form-group">
									<input type="file" class="form-control @if($errors->has('file')) is-invalid @endif" name="file" value="{{ old('file') }}" placeholder="aファイル選択..." accept=".csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
								</div>
								<button type="submit" class="px2-btn px2-btn--primary">送信</button>
								<button type="reset" class="px2-btn px2-btn--danger">キャンセル</button>
							</form>
						</li>
						<li>
							@if($errors->has('file'))
								<span class="invalid-feedback" role="alert">
									{{ $errors->first('file') }}
								</span>
							@endif
						</li>
					</ul>
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
