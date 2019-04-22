@php
    $id_attr = 'modal-contents_commit' . $controller;
@endphp

{{-- 削除ボタン --}}
<span class="input-group-btn">
    <button class="px2-btn px2-btn--primary" data-toggle="modal" data-target="#{{ $id_attr }}">
        コミット
    </button>
</span>
{{-- モーダルウィンドウ --}}
<div class="modal fade" id="{{ $id_attr }}" tabindex="-1" role="dialog" aria-labelledby="{{ $id_attr }}-label" aria-hidden="true">
    <div style="position: absolute; left: 0px; top: 0px; padding-top: 4em; overflow: auto; width: 100%; height: 100%;">
	    <div class="dialog_box" style="width: 80%; margin: 3em auto;">
	        <h1>コンテンツをコミットする</h1>
	        <div>
	            <div class="px2dt-git-commit">
                    <ul class="listview" style="margin: 20px 20px;">
        				<li>
                            <ul class="cont_filelist_sitemap__ext-list" style="margin: 40px 20px;">
                                <li>
                                    <div class="form-inline">
    								<div class="form-group">
    									<input type="text" id="commit" class="form-control @if($errors->has('commit')) is-invalid @endif" name="commit" value="{{ old('commit') }}" placeholder="Your commit comme" style="width: 700px;">
                                        <script>
                                            function contentsCommit(e) {
                                                // 処理前に Loading 画像を表示
                                                dispLoading("処理中...");

                								var errorMessage = document.getElementById("errorMessage");
                                                var flashAlert = document.getElementById("flash_alert");
                                                var str = document.getElementById("commit").value;

                                                // ajaxでファイルのmimetypeを取得しコントローラーに送信
                								$.ajax({
                									url: "/pages/{{ $project_name }}/{{ $branch_name }}/editAjax",
                									type: 'post',
                									data : {
                										"str" : str,
                										_token : '{{ csrf_token() }}'
                									},
                								}).done(function(data){
                									// ajaxで取得してきた値で処理分け
                                                    $('#{{ $id_attr }}').modal('hide');
                                                    flashAlert.innerHTML = data.message;
                                                    flashAlert.style.display = "block";
                                                    setTimeout(function() {
                                                        $('#flash_alert').fadeOut(500);
                                                    }, 2000);
                                                    setTimeout(function() {
                                                        flashAlert.innerHTML = '';
                                                    }, 3000);
                                                    cancelButton(event);

                                                }).always(function(data){
                                                    // 処理終了時にLading 画像を消す
                                                    removeLoading();
                								});
                							}
            							</script>
    								</div>
    								<button id="submitStatus" type="submit" class="px2-btn px2-btn--primary" onclick="contentsCommit(event);">プッシュ</button>
    								<button type="reset" class="px2-btn px2-btn--danger" onclick="cancelButton(event);">キャンセル</button>
                                    <script>
                                        // キャンセルボタンを押した際に送信ボタンをdisabledにする
                                        function cancelButton(e) {
                                            var commitMessage = document.getElementById("commit");
                                            commitMessage.value = '';
                                        }
                                    </script>
                                </div>
                                </li>
                                <li>
                                    <span id="errorMessage" class="invalid-feedback" role="alert"></span>
                                    @if($errors->has('file'))
                                        <span class="invalid-feedback" role="alert">
                                            {{ $errors->first('file') }}
                                        </span>
                                    @endif
                                </li>
                            </ul>
        				</li>
        			</ul>
	            </div>
	        </div>
	        <div class="dialog-buttons center">
	            <button type="button" class="px2-btn px2-btn--primary" data-dismiss="modal">閉じる</button>
	        </div>
	    </div>
	</div>
</div>
