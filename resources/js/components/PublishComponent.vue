<template>
	<div class="contents" style="height: 70vh;">
		<div class="cont_scene" id="cont_before_publish" v-bind:class="classPublishButton">
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
		<div class="cont_scene" id="cont_before_publish-progress" v-bind:class="classPublishProgress">
			<div class="cont_canvas">
				<div class="unit cont_progress">
					<div class="center">
						<p>パブリッシュしています。</p>
						<p>そのまましばらくお待ちください...</p>
						<div>
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
		<div class="cont_scene" id="cont_after_publish" v-bind:class="classPublishLog">
			<div class="cont_canvas">
				<div class="cont_results">
					<div class="cont_results-messageBox">
						<div class="cont_results-total_file_count">total: <strong>{{ total_files }}</strong> files.</div>
						<div class="cont_results-errorMessage" v-bind:class="classAlertLog">{{ alert }}件のエラーが検出されています。</div>
						<div class="cont_results-spentTime">time: <span>{{ time }} sec</span></div>
						<p><button class="px2-btn px2-btn--primary px2-btn--lg" v-on:click="prepare">パブリッシュされたファイルを確認する</button></p>
						<ul class="horizontal">
							<li class="horizontal-li"><a href="#" class="px2-link px2-link--burette" v-on:click="prepare">パブリッシュレポート</a></li>
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
		<div class="cont_scene" id="cont_on_publish" v-bind:class="classPublishWait">
			<p>ただいまパブリッシュプロセスが進行しています。</p>
			<p>しばらくお待ち下さい...。</p>
			<p><a href="#" class="glyphicon glyphicon-menu-right" v-on:click="recoveryOnPublish">しばらく待ってもこの状態から復旧しない場合は...詳細</a></p>
			<div class="cont_recovery_on_publish" v-bind:class="[isRecoveryOnPublish === true ? 'show' : 'hidden']">
				<h2>これはどういう状態ですか？</h2>
				<p>Pickles 2 のパブリッシュプロセスは、二重起動を避けるために、次のパスにロックファイルを生成します。</p>
				<ul>
					<li><code>./px-files/_sys/ram/publish/applock.txt</code></li>
				</ul>
				<p>このファイルは、パブリッシュ開始時に生成され、パブリッシュ完了時に削除されます。</p>
				<p>パブリッシュ中であれば、このファイルが存在することは健康な状態です。しかし、パブリッシュの途中でプロセスが異常終了した場合(途中でアプリを落とす、なども含む)、このファイルが残ってしまうため、次のパブリッシュが実行できない状態になります。</p>
				<h2>復旧方法</h2>
				<p>ロックファイル <code>./px-files/_sys/ram/publish/applock.txt</code> を手動で<a v-bind:href="deleteApplock">削除します</a>。</p>
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
		// publish_log.csvの有無
		"existsPublishLog",
		// alert_log.csvの有無
		"existsAlertLog",
		// applock.txtの有無
		"existsApplock",
		// publish_log.csvの行数（先頭行を除く）
		"publishFiles",
		// alert_log.cscの行数（先頭行を除く）
		"alertFiles",
		// publish_log.csvの最終行と1行目の時間の差分
		"diffSeconds",
		"sessionMyStatus"
	],
	// メソッドで使う&テンプレート内で使う変数を定義
	data () {
    	return {
			// Ajax\PublishController@publishAjaxからの返り値
			info: '',
			// PublishEventからの返り値（プログレスバーの％）
			parse: 0,
			// PublishEventからの返り値（パブリシュファイル件数）
			queue_count: '',
			// PublishEventからの返り値（パブリッシュファイル情報）
			publish_file: '',
			// パブリッシュの状態（1:未パブリッシュ/2:パブリッシュ中/3:パブリッシュ後/999:パブリッシュ中のリロード）
			publish_status: '',
			// publishFilesをバインディング
			total_files: this.publishFiles,
			// alertFilesをバインディング
			alert: this.alertFiles,
			// diffSecondsをバインディング
			time: this.diffSeconds,
			// パブリッシュリカバリ画面の表示・非表示
			isRecoveryOnPublish: false,
			// アップロックを削除するためのリンクパス
			deleteApplock: '/publish/'+this.projectCode+'/'+this.branchName+'/deleteApplock'
		}
	},
	// レンダリング前にpublish_log.csvの有無によって処理分け
	created () {
		if(this.existsPublishLog === '') {
			this.publish_status = 1;
		} else if(this.existsPublishLog === '1' && this.existsApplock === '1') {
			this.publish_status = 999;
		} else {
			this.publish_status = 3;
		}
		// フラッシュメッセージが出ていれば2000ミリ秒後に削除
		if(this.sessionMyStatus !== '') {
			setTimeout(function() {
				var flashMessage = document.getElementById("session-my-status").remove();
			}, 2000);
		}
	},

	mounted() {
		this.connectChannel();
 	},
	// (読み込み時に)実行するメソッド
    methods: {
		publish(reset) {
			this.publish_status = 999;
			if(reset === 1) {
				this.parse = 0;
				this.queue_count = '';
				this.publish_file = '';
				this.alert = '';
				this.time = '';
			}
			var data = 'publish';
			// AjaxでAjax\PublishController@publishAjaxにpost処理
			axios.post('/publish/'+this.projectCode+'/'+this.branchName+'/publishAjax',data).then(res => {
				this.info = res.data.info;
			})
		},

		// 購読するチャンネルの設定
		connectChannel() {
			// Ajax\PublishController@publishAjaxの返り値
			window.Echo.channel('publish-event').listen('PublishEvent', (e) => {
				this.publish_status = 2;
				// 標準出力が数値または数値+改行コードだった場合parseに代入
				if(e.judge === 1) {
					this.parse = e.parse;
				}
				// パブリッシュファイル件数を計算して出力
				if(e.queue_count !== '') {
					this.queue_count = e.queue_count;
				}
				// パブリッシュしているファイル情報を配列で出力
				if(e.publish_file !== '') {
					this.publish_file = e.publish_file;
				}
				if(e.end_publish === 1) {
					// Ajax\PublishController@readCsvAjaxにpost処理
					axios.post('/publish/'+this.projectCode+'/'+this.branchName+'/readCsvAjax').then(res => {
						this.total_files = res.data.publish_files;
						// アラート件数を取得
						this.alert = res.data.alert_files;
						// パブリッシュにかかった時間を取得
						this.time = res.data.diff_seconds;
						this.publish_status = 3;
					})
				}
			})
		},

		recoveryOnPublish() {
			if(this.isRecoveryOnPublish === false) {
				this.isRecoveryOnPublish = true;
			} else {
				this.isRecoveryOnPublish = false;
			}
		},

		prepare() {
			alert('準備中の機能です。');
		}
	},

	computed: {
		// 未パブリッシュ時のパブリッシュボタンの表示・非表示
		classPublishButton: function () {
			return {
				show: this.publish_status === 1,
				hidden: this.publish_status !== 1
			}
		},
		// パブリッシュ中のプログレスバーの表示・非表示
		classPublishProgress: function () {
			return {
				show: this.publish_status === 2,
				hidden: this.publish_status !== 2
			}
		},
		// パブリッシュ後のログ情報の表示・非表示
		classPublishLog: function () {
			return {
				show: this.publish_status === 3,
				hidden: this.publish_status !== 3
			}
		},
		// アラート情報の表示・非表示
		classAlertLog: function () {
			return {
				show: this.existsAlertLog === '1',
				hidden: this.existsAlertLog === ''
			}
		},
		// パブリッシュ中のWAIT画面の表示・非表示
		classPublishWait: function () {
			return {
				show: this.publish_status === 999,
				hidden: this.publish_status !== 999
			}
		}
	}
}
</script>
