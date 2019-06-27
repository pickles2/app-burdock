<template>
	<div class="contents" style="height: 70vh;">
		<div class="cont_scene" id="cont_before_publish" v-bind:class="[isPublishButton === true ? 'show' : 'hidden']">
			<div class="unit center">
				<p>パブリッシュは実行されていません。</p>
				<p>次のボタンを押して、パブリッシュを実行します。</p>
				<p><button class="px2-btn px2-btn--primary" v-on:click="publish">パブリッシュする</button></p>
			</div>
		</div>
		<div class="cont_scene hidden" id="cont_after_publish-zero_files">
			<div class="unit center">
				<p>パブリッシュを実行しましたが、何も出力されませんでした。</p>
				<p>パブリッシュ対象範囲に何も含まれていない可能性があります。</p>
				<p>次のボタンを押し、パブリッシュ範囲の設定を変えてもう一度パブリッシュを実行してみてください。</p>
				<p><button class="px2-btn px2-btn--primary">パブリッシュする</button></p>
			</div>
		</div>
		<div class="cont_scene" id="cont_before_publish-progress" v-bind:class="[isPublish === true ? 'show' : 'hidden']">
			<div class="cont_canvas">
				<div class="unit cont_progress">
					<div class="center">
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
		<div class="cont_scene" id="cont_after_publish" v-bind:class="[isPublishResult === true ? 'show' : 'hidden']">
			<div class="cont_canvas">
				<div class="cont_results" v-bind:class="[isPublishError === true ? 'cont_results-error' : '']">
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
			<div class="cont_buttons">
				<div class="btn-group btn-group-justified" role="group">
					<div class="btn-group" role="group">
						<button class="px2-btn px2-btn--block" v-on:click="publish(1)">もう一度パブリッシュする</button>
					</div>
				</div>
			</div>
		</div>
		<div class="cont_scene hidden" id="cont_on_publish">
			<p>ただいまパブリッシュプロセスが進行しています。</p>
			<p>しばらくお待ち下さい...。</p>
			<p><a href="#" class="glyphicon glyphicon-menu-right">しばらく待ってもこの状態から復旧しない場合は...詳細</a></p>
			<div class="cont_recovery_on_publish hidden">

				<h2>これはどういう状態ですか？</h2>
				<p>Pickles 2 のパブリッシュプロセスは、二重起動を避けるために、次のパスにロックファイルを生成します。</p>
				<ul>
					<li><code>./px-files/_sys/ram/publish/applock.txt</code></li>
				</ul>

				<p>このファイルは、パブリッシュ開始時に生成され、パブリッシュ完了時に削除されます。</p>
				<p>パブリッシュ中であれば、このファイルが存在することは健康な状態です。しかし、パブリッシュの途中でプロセスが異常終了した場合(途中でアプリを落とす、なども含む)、このファイルが残ってしまうため、次のパブリッシュが実行できない状態になります。</p>

				<h2>復旧方法</h2>
				<p>ロックファイル <code>./px-files/_sys/ram/publish/applock.txt</code> を手動で<a href="#">削除します</a>。</p>

				<ul class="notes">
					<li class="notes-li">※ただし、バックグラウンドでプロセスが進行中ではないか、事前に確認してください。</li>
					<li class="notes-li">※<code>applock.txt</code> をテキストファイルで開くと、このファイルを生成したプロセスの <strong>プロセスID</strong> と <strong>最終アクセス日時</strong> が記載されています。この情報が手がかりになるはずです。</li>
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
		"branchName",
		"logExist"
	],
	// メソッドで使う&テンプレート内で使う変数を定義
	data () {
    	return {
			info: '',
			message: '',
			messages: '',
			error: '',
			errors: '',
			parse: 0,
			queue_count: '',
			parse_count: '',
			k: 0,
			alert_array: [],
			time_array: [],
			publish_file: '',
			isPublishButton: true,
			isPublish: false,
			isPublishResult: false,
			isPublishError: false,
		}
	},
	mounted() {
		this.connectChannel();
 	},
	// (読み込み時に)実行するメソッド
    methods: {
		publish(reset) {
			console.log(this.logExist);
			if(reset === 1) {
				this.message = '';
				this.messages = '';
				this.error = '';
				this.errors = '';
				this.parse = 0;
				this.queue_count = '';
				this.parse_count = '0';
				this.k = 0;
				this.alert_array = [];
				this.time_array = [];
				this.publish_file = '';
				this.isPublishButton = false;
				this.isPublish = false;
				this.isPublishResult = false;
				this.isPublishError = false;
			}
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
				console.log(e.message);
				// 標準出力の全文
				this.messages = this.messages + '\n' + this.message;
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
					this.isPublishError = true;
				}
				// パブリッシュにかかった時間を配列で出力
				if(e.time_array !== '') {
					this.time_array = e.time_array;
					console.log(this.time_array);
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
	},

	// computed: {
	// 	isPublish: function() {
	// 		if(this.logExist === '1') {
	// 			this.isPublishButton = true;
	// 		}
	// 		return this.isPublish
	// 	}
	// }
}
</script>
