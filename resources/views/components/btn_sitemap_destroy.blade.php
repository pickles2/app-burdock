@php
	$id_attr = 'modal-sitemap_destroy' . $controller . '-' . md5($file_name);
@endphp

{{-- 削除ボタン --}}
<button class="px2-btn px2-btn--danger" data-toggle="modal" data-target="#{{ $id_attr }}">
	Delete
</button>

{{-- モーダルウィンドウ --}}
<div class="modal fade" id="{{ $id_attr }}" tabindex="-1" role="dialog" aria-labelledby="{{ $id_attr }}-label" aria-hidden="true">
	<div style="position: absolute; left: 0px; top: 0px; padding-top: 4em; overflow: auto; width: 100%; height: 100%;">
		<div class="dialog_box" style="width: 50%; margin: 3em auto;">
			<h1>サイトマップの削除</h1>
			<div>
				<div class="px2dt-git-commit">
					<p>サイトマップファイル <code>{{ $file_name }}</code> を削除します。</p>
					<p>本当に削除してもよろしいですか？</p>
					<form class="form-inline" method="POST" action="{{ url('/sitemaps/'.urlencode($project_code).'/'.urlencode($branch_name).'/destroy') }}" enctype="multipart/form-data">
						@csrf
						@method('POST')
						<input type="hidden" name="file_name" value="{{ $file_name }}">
						<button id="submitStatus" type="submit" class="px2-btn px2-btn--danger">削除する</button>
					</form>
				</div>
			</div>
			<div class="dialog-buttons px2-text-align-center">
				<button type="button" class="px2-btn" data-dismiss="modal">閉じる</button>
			</div>
		</div>
	</div>
</div>
