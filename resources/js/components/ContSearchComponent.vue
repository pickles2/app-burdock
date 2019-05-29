<template>
    <!-- <div>
        <p>
            Count : <span :class="numberClasses">{{ number }}</span>
        </p>
        <button @click="countDown">−１</button>
        <button @click="countUp">＋１</button>
    </div> -->
	<div class="col-xs-3" data-original-title="" title="">
		<div class="cont_workspace_search" data-original-title="" title="">
			<div class="input-group input-group-sm" data-original-title="" title="">

					<div class="input-group" data-original-title="" title="">
						<input id="searchText" type="text" class="form-control" placeholder="Search..." value="">
						<span class="input-group-btn" data-original-title="" title="">
							<button class="px2-btn px2-btn--primary" type="submit" onclick="contentsSearch(event);">検索</button>
							<script>
								function contentsSearch(e) {
									// 処理前に Loading 画像を表示
									px2style.loading();
									px2style.loadingMessage("しばらくお待ちください。");

									var sholderNavi = document.getElementById("sholderNavi");
									var flashAlert = document.getElementById("flash_alert");
									var str = document.getElementById("searchText").value;

									// ajaxでファイルのmimetypeを取得しコントローラーに送信
									$.ajax({
										url: "/pages/{{ $project->project_name }}/{{ $branch_name }}/searchAjax",
										type: 'post',
										data : {
											"str" : str,
											_token : '{{ csrf_token() }}'
										},
									}).done(function(data){
										console.log(data.info);

									}).always(function(data){
										// 処理終了時にLading 画像を消す
										px2style.closeLoading();
									});
								}
							</script>
						</span>
						@component('components.btn_contents_commit')
							@slot('controller', 'page')
							@slot('project_name', $project->project_name)
							@slot('branch_name', $branch_name)
						@endcomponent
					</div>

					<div class="btn-group btn-group-justified" data-toggle="buttons" data-original-title="" title="">
						<label class="btn px2-btn active" data-original-title="" title="">
							<input type="radio" name="list-label" value="title" checked="checked" data-original-title="" title="">title
						</label>
						<label class="btn px2-btn" data-original-title="" title="">
							<input type="radio" name="list-label" value="path" data-original-title="" title="">path
						</label>
					</div>

			</div>
		</div>
	
</template>

<script>
export default {
    name: "ContSearchComponent",
    data () {
        return {
            number: 0
        }
    },
    computed: {
        numberClasses: function () {
            return {
                'positive': (this.number > 0),
                'negative': (this.number < 0)
            }
        }
    },
    methods: {
        countDown: function () {
            this.number--
        },
        countUp: function () {
            this.number++
        }
    }
}
</script>
