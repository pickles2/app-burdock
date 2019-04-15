@php
    $id_attr = 'modal-sitemap_upload' . $controller;
@endphp

{{-- 削除ボタン --}}
<button class="btn px2-btn" data-toggle="modal" data-target="#{{ $id_attr }}">
    サイトマップをアップロードする
</button>

{{-- モーダルウィンドウ --}}
<div class="modal fade" id="{{ $id_attr }}" tabindex="-1" role="dialog" aria-labelledby="{{ $id_attr }}-label" aria-hidden="true">
    <div style="position: absolute; left: 0px; top: 0px; padding-top: 4em; overflow: auto; width: 100%; height: 100%;">
	    <div class="dialog_box" style="width: 80%; margin: 3em auto;">
	        <h1>サイトマップのアップロード</h1>
	        <div>
	            <div class="px2dt-git-commit">
                    <ul class="listview">
        				<li>
                            <ul class="cont_filelist_sitemap__ext-list" style="margin: 20px 20px;">
                                <li>
        							<form class="form-inline" method="POST" action="{{ url('/upload'.'/'.$project_name.'/'.$branch_name) }}" enctype="multipart/form-data">
        								@csrf
        								@method('POST')
        								<div class="form-group">
        									<input type="file" class="form-control @if($errors->has('file')) is-invalid @endif" name="file" value="{{ old('file') }}" placeholder="aファイル選択..." accept=".csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" style="width: 700px;" onchange="uploadFile(event);">
                                            <script>
                                                function uploadFile(e) {
                    								var errorMessage = document.getElementById("errorMessage");
                                                    var submitStatus = document.getElementById("submitStatus");
                                                    var file = e.target.files;  //選択ファイルを配列形式で取得
                                                    var num  = file.length;       //選択されたファイル数を格納
                                                    var str = "";                 //ファイル情報を格納する変数
                                                    for ( var i = 0 ; i < num ; i++ ) {
                                                        str += file[i].type;
                                                    }
                    								$.ajax({
                    									url: "/sitemaps/{{ $project_name }}/{{ $branch_name }}/uploadAjax",
                    									type: 'post',
                    									data : {
                    										"str" : str,
                    										_token : '{{ csrf_token() }}'
                    									},
                    								}).done(function(data){
                    									// ajaxで取得してきたパスとIDでページ遷移
                                                        if(data.status === 0) {
                                                            errorMessage.innerHTML = data.error;
                                                            submitStatus.disabled = true;
                                                        } else {
                                                            submitStatus.disabled = false;
                                                        }

                    								});
                    							}
                							</script>
        								</div>
        								<button id="submitStatus" type="submit" class="px2-btn px2-btn--primary" disabled="disabled">送信</button>
        								<button type="reset" class="px2-btn px2-btn--danger">キャンセル</button>
        							</form>
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
