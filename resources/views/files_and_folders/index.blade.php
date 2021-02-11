@php
	$title = __('Files And Folders');
@endphp
@extends('layouts.default')

@section('content')

<div id="cont-finder"></div>

@endsection

@section('stylesheet')
<link rel="stylesheet" href="/common/remote-finder/dist/remote-finder.css" />
<link rel="stylesheet" href="{{ asset('/cont/files_and_folders/style.css') }}" type="text/css" />
@endsection

@section('script')
<script>
window.contRemoteFinderGpiEndpoint = "/files-and-folders/{{ $project->project_code }}/{{ $branch_name }}/gpi";
window.contCommonFileEditorEndpoint = '/files-and-folders/{{ $project->project_code }}/{{ $branch_name }}/common-file-editor';
window.contCommonFileEditorGpiEndpoint = '/files-and-folders/{{ $project->project_code }}/{{ $branch_name }}/common-file-editor/gpi';
window.contContentsEditorEndpoint = '/contentsEditor/{{ $project->project_code }}/{{ $branch_name }}';
window.contApiParsePx2FilePathEndpoint = '/files-and-folders/{{ $project->project_code }}/{{ $branch_name }}/api/parsePx2FilePath';
window.filename = <?= json_encode( $filename, JSON_UNESCAPED_SLASHES ) ?>;
</script>
<script src="/common/remote-finder/dist/remote-finder.js"></script>
<script src="{{ asset('/cont/files_and_folders/script.js') }}"></script>

		<!-- Template: mkfile dialog -->
		<script id="template-mkfile" type="text/template">
<p>Current Directory</p>
<div>
	<pre class="cont_current_dir"></pre>
</div>
<p>File name</p>
<div>
	<p><input type="text" name="filename" value="" class="form-control" /></p>
</div>
<div class="cont_html_ext_option" style="display: none;">
	<p>GUI編集モード</p>
	<div>
		<p><label><input type="checkbox" name="is_guieditor" value="1" checked="checked" /> GUI編集モードを有効にする</label></p>
	</div>
</div>
		</script>

		<!-- Template: mkdir dialog -->
		<script id="template-mkdir" type="text/template">
<p>Current Directory</p>
<div>
	<pre class="cont_current_dir"></pre>
</div>
<p>Directory name</p>
<div>
	<p><input type="text" name="dirname" value="" class="form-control" /></p>
</div>
		</script>

		<!-- Template: copy dialog -->
		<script id="template-copy" type="text/template">
<p>Target item</p>
<div>
	<pre class="cont_target_item"></pre>
</div>
<p>New file name</p>
<div>
	<p><input type="text" name="copy_to" value="" class="form-control" /></p>
</div>
<div class="cont_contents_option" style="display: none;">
	<div>
		<p><label><input type="checkbox" name="is_copy_files_too" value="1" checked="checked" /> リソースファイルもあわせて複製する</label></p>
	</div>
</div>
		</script>

		<!-- Template: rename dialog -->
		<script id="template-rename" type="text/template">
<p>Target item</p>
<div>
	<pre class="cont_target_item"></pre>
</div>
<p>New file name</p>
<div>
	<p><input type="text" name="rename_to" value="" class="form-control" /></p>
</div>
<div class="cont_contents_option" style="display: none;">
	<div>
		<p><label><input type="checkbox" name="is_rename_files_too" value="1" checked="checked" /> リソースファイルもあわせて移動する</label></p>
	</div>
</div>
		</script>

		<!-- Template: remove dialog -->
		<script id="template-remove" type="text/template">
<p>本当に削除してよろしいですか？</p>
<div>
	<pre class="cont_target_item"></pre>
</div>
<div class="cont_contents_option" style="display: none;">
	<div>
		<p><label><input type="checkbox" name="is_remove_files_too" value="1" checked="checked" /> リソースファイルもあわせて削除する</label></p>
	</div>
</div>
		</script>

@endsection
