<template>
	<div id="targetId" class="contents" style="height: 70vh;">
		<div v-bind:class="[isPublishButton === true ? 'show' : 'hidden']">
			<form v-on:submit.prevent="publish">
				<p><button class="px2-btn px2-btn--primary">フルパブリッシュ</button></p>
			</form>
		</div>
		<div class="cont_scene" id="cont_before_publish-progress" v-bind:class="[isPublish === true ? 'show' : 'hidden']">
			<div class="cont_canvas">
				<div class="unit cont_progress">
					<div class="text-center">
						<p>パブリッシュしています。</p>
						<p>そのまましばらくお待ちください...</p>
						<div v-if="queue_count !== ''">
							<div class="cont_progress-phase" style="font-weight: bold;">Publishing...</div>
							<div class="cont_progress-row">{{ publish_file }}</div>
							<div class="cont_progress-currentTask">{{ queue_count }}</div>
						</div>
						<div class="cont_progress-bar">
							<div class="progress">
								<div class="progress-bar progress-bar-striped active" role="progressbar" v-bind:style="{width: parse+'%'}">{{ parse }}%</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="cont_buttons">
				<div class="btn-group btn-group-justified" role="group">
					<div class="btn-group" role="group">
						<button class="px2-btn px2-btn--block">キャンセル</button>
					</div>
				</div>
			</div>
		</div>
		<div class="cont_results cont_results-error" v-bind:class="[isPublishResult === true ? 'show' : 'hidden']">
			<div class="cont_results-messageBox">
				<div class="cont_results-total_file_count">total: <strong>{{ parse_count }}</strong> files.</div>
				<div v-if="alert_array[7] !== ''" class="cont_results-errorMessage">{{ alert_array[7] }}件のエラーが検出されています。</div>
				<div class="cont_results-spentTime">time: <span>{{ time_array[2] }} sec</span></div>
				<p><button class="px2-btn px2-btn--primary px2-btn--lg">パブリッシュされたファイルを確認する</button></p>
				<ul class="horizontal">
					<li class="horizontal-li"><a href="#" class="px2-link px2-link--burette">パブリッシュレポート</a></li>
				</ul>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	// view側から変数をプロパティとして渡す
	props: [
		"projectCode",
		"branchName"
	],
	// メソッドで使う&テンプレート内で使う変数を定義
	data () {
    	return {
			info: '',
			message: '',
			messages: '',
			error: '',
			errors: '',
			parse: '',
			queue_count: '',
			parse_count: '',
			k: 0,
			alert_array: [],
			time_array: [],
			publish_file: '',
			isPublishButton: true,
			isPublish: false,
			isPublishResult: false,
		}
	},
	mounted() {
		this.connectChannel();
 	},
	// (読み込み時に)実行するメソッド
    methods: {
		publish() {
			var data = 'publish';
			axios.post('/publish/'+this.projectCode+'/'+this.branchName+'/publishAjax',data).then(res => {
				this.info = res.data.info;
			})
		},

		// 購読するチャンネルの設定
		connectChannel() {
			window.Echo.channel('publish-event').listen('PublishEvent', (e) => {
				// 標準出力
				this.message = e.message;
				// 標準出力の全文
				this.messages = this.messages + this.message;
				// 標準エラー出力
				this.error = e.error;
				// 標準エラー出力の全文
				this.errors = this.errors + this.error;
				// 標準出力が数値または数値+改行コードだった場合parseに代入
				if(e.judge === 1) {
					this.parse = e.parse;
				}
				// パブリッシュファイル件数を計算して出力
				if(e.queue_count !== '') {
					this.queue_count = e.queue_count;
					this.parse_count = String(this.k);
					this.k++;
				}
				// アラートを配列で出力
				if(e.alert_array !== '') {
					this.alert_array = e.alert_array;
				}
				// パブリッシュにかかった時間を配列で出力
				if(e.time_array !== '') {
					this.time_array = e.time_array;
				}
				// パブリッシュしているファイル情報を配列で出力
				if(e.publish_file !== '') {
					this.publish_file = e.publish_file;
					this.isPublishButton = false;
					this.isPublish = true;
				}
				if(e.end_publish === 1) {
					this.isPublish = false;
					this.isPublishResult = true;
				}
			})
		}
	}
}
</script>
