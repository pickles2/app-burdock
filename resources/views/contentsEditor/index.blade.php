@php
	$title = __('Contents');
@endphp
@extends('layouts.contents_editor')

@section('head')
<?php
foreach($px2ce_client_resources->css as $value) {
	echo '<link href="'.'/assets/px2ce_resources/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/'.$value.'" rel="stylesheet" />'."\n";
}
?>
@endsection



@section('content')
<div id="canvas" style="height:100vh;"></div>
@endsection



@section('foot')
<?php
foreach($px2ce_client_resources->js as $value) {
	echo '<script src="'.'/assets/px2ce_resources/'.urlencode($project->project_code).'/'.urlencode($branch_name).'/'.$value.'"></script>'."\n";
}
?>

<!-- Ace Editor -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.4/ace.js"></script>

<!-- Keypress -->
<script src="/common/dmauro-Keypress/keypress.js"></script>

<script type="text/javascript">
	(function(){
		var project_code = <?php echo json_encode($project->project_code, JSON_UNESCAPED_SLASHES); ?>;
		var branch_name = <?php echo json_encode($branch_name, JSON_UNESCAPED_SLASHES); ?>;
		var page_path = <?php echo json_encode($page_path, JSON_UNESCAPED_SLASHES); ?>;
		var theme_id = <?php echo json_encode($theme_id, JSON_UNESCAPED_SLASHES); ?>;
		var layout_id = <?php echo json_encode($layout_id, JSON_UNESCAPED_SLASHES); ?>;
		var target_mode = 'page_content';
		// .envよりプレビューサーバーのURLを取得
		var preview_url = '{{ 'https://'.urlencode($project->project_code).'---'.urlencode($branch_name).'.'.env('BD_PREVIEW_DOMAIN') }}';
		var resizeTimer;

		if( page_path ){
			target_mode = 'page_content';
		}else if( theme_id && layout_id ){
			target_mode = 'theme_layout';
			page_path = '/'+theme_id+'/'+layout_id+'.html';
		}

		// Px2CE の初期化
		var pickles2ContentsEditor = new Pickles2ContentsEditor(); // px2ce client
		pickles2ContentsEditor.init(
			{
				// いろんな設定値
				// これについては Px2CE の README を参照
				// https://github.com/pickles2/node-pickles2-contents-editor
				'page_path': page_path , // <- 編集対象ページのパス
				'elmCanvas': document.getElementById('canvas'), // <- 編集画面を描画するための器となる要素
				'preview':{
					'origin': preview_url// プレビュー用サーバーの情報を設定します。
				},
				'lang': 'ja', // language
				'gpiBridge': function(input, callback){
					console.log(input);
					$.ajax({
						"url": '/contentsEditor/'+project_code+'/'+branch_name+'/px2ceGpi?page_path='+page_path+'&target_mode='+target_mode, // ←呼び出し元が決める
						"method": 'post',
						'data': {
							'data':JSON.stringify(input),
							_token: '{{ csrf_token() }}'
						},
						"success": function(data){
							callback(data);
							console.log(data);
						}
					});
					return;
				},
				'complete': function(){
					window.open('about:blank','_self').close();
				},
				'onClickContentsLink': function( uri, data ){
					// TODO: 編集リンクを生成する
					// alert('編集: ' + uri);
				},
				'onMessage': function( message ){
					// ユーザーへ知らせるメッセージを表示する
					px2style.flashMessage(message);
					console.info('message: '+message);
				}
			},
			function(){
				// コールバック
				// 初期化完了！
				console.info('Standby!');

			}
		);

		$(window).resize(function(){
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function(){
				if(pickles2ContentsEditor.redraw){
					pickles2ContentsEditor.redraw();
				}
			}, 500);
			return;
		});
	})();
</script>

@endsection
