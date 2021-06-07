@php
	$id_attr = 'modal-sitemap_upload' . $controller;
@endphp

{{-- 削除ボタン --}}
<button class="btn px2-btn cont-btn-sitemap-upload-dialog">
	サイトマップをアップロードする
</button>
<script>
$(window).on('load', function(){
	var modal;
	var $btnUpload = $('<button class="px2-btn px2-btn--primary">')
		.text('アップロードする')
		.attr({
			'type': 'submit',
			'disabled': true,
		});

	$('.cont-btn-sitemap-upload-dialog').on('click', function(){
		var $body = $($('#cont-template-sitemap-upload-dialog').html());
		px2style.modal({
			"title": 'サイトマップのアップロード',
			"body": $body,
			"form": {
				"action": "{{ url('/sitemaps/'.urlencode($project_code).'/'.urlencode($branch_name).'/upload') }}",
				"method": "post",
				"submit": function(){
					// modal.lock(); // TODO: ここで lockしてからフォーム送信すると 419 エラーで弾かれる。なぜ・・・？？
					px2style.loading();
				}
			},
			"buttons": [
				$btnUpload
			],
			"buttonsSecondary": [
				$('<button>')
					.text('キャンセル')
					.on('click', function(){
						px2style.closeModal();
					})
			],
		}, function(_modal){
			modal = _modal;
			modal.$modal.find('form').attr({'enctype': 'multipart/form-data'});
			// modal.$modal.find('input[name=_token]').val($('meta[name=csrf-token]').attr('content'));
		});


		$body.find('input[type=file]').on('change', function(e){
			px2style.loading();
			px2style.loadingMessage("しばらくお待ちください。");

			var $errorMessage = $(".cont-sitemap-upload-error-message");
			var file = e.target.files;  //選択ファイルを配列形式で取得
			var num  = file.length;     //選択されたファイル数を格納
			var mimeType = "";          //ファイル情報を格納する変数
			for ( var i = 0 ; i < num ; i++ ) {
				mimeType += file[i].type;
			}

			// ajaxでファイルのmimetypeを取得しコントローラーに送信
			$.ajax({
				url: "/sitemaps/{{ urlencode($project_code) }}/{{ urlencode($branch_name) }}/uploadAjax",
				type: 'post',
				data : {
					"str" : mimeType,
					_token : $('meta[name=csrf-token]').attr('content'),
				},
			}).done(function(data){
				// ajaxで取得してきた値で処理分け
				$errorMessage.html( data.error );
				if(data.status === 0) {
					$btnUpload.attr({'disabled': true});
				} else {
					$btnUpload.attr({'disabled': false});
				}
			}).always(function(data){
				px2style.closeLoading();
			});
		});
	})

});

</script>

<script id="cont-template-sitemap-upload-dialog" type="text/template">
	<div>
		@csrf
		@method('POST')
		<div class="form-group">
			<input type="file"
				class="form-control @if($errors->has('file')) is-invalid @endif"
				name="file"
				value="{{ old('file') }}"
				placeholder="aファイル選択..."
				accept=".csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
				/>
		</div>
		<span class="cont-sitemap-upload-error-message invalid-feedback" role="alert"></span>
	</div>
</script>
