<template>
	<div>
		<div class="contents" style="height: 70vh;">
			<div class="cont_scene" id="cont_before_publish" v-bind:class="classPublishButton">
				<div class="px2-p px2-text-align-center">
					<p>パブリッシュは実行されていません。</p>
					<p>次のボタンを押して、パブリッシュを実行します。</p>
					<p><button class="px2-btn px2-btn--primary" v-on:click="publish_option">パブリッシュする</button></p>
				</div>
			</div>
			<div class="cont_scene hidden" id="cont_after_publish-zero_files">
				<div class="px2-p px2-text-align-center">
					<p>パブリッシュを実行しましたが、何も出力されませんでした。</p>
					<p>パブリッシュ対象範囲に何も含まれていない可能性があります。</p>
					<p>次のボタンを押し、パブリッシュ範囲の設定を変えてもう一度パブリッシュを実行してみてください。</p>
					<p><button class="px2-btn px2-btn--primary">パブリッシュする</button></p>
				</div>
			</div>
			<div class="cont_scene" id="cont_before_publish-progress" v-bind:class="classPublishProgress">
				<div class="cont_canvas">
					<div class="px2-p cont_progress">
						<div class="px2-text-align-center">
							<p>パブリッシュしています。</p>
							<p>そのまましばらくお待ちください...</p>
							<div>
								<div class="cont_progress-phase" style="font-weight: bold;">Publishing...</div>
								<div class="cont_progress-row">{{ publishFile }}</div>
								<div class="cont_progress-currentTask">{{ queueCount }}</div>
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
							<button class="px2-btn px2-btn--block" v-on:click="publishCancel">キャンセル</button>
						</div>
					</div>
				</div>
			</div>
			<div class="cont_scene" id="cont_after_publish" v-bind:class="classPublishLog">
				<div class="cont_canvas">
					<div class="cont_results">
						<div class="cont_results-messageBox">
							<div class="cont_results-total_file_count">total: <strong>{{ totalFiles }}</strong> files.</div>
							<div class="cont_results-errorMessage" v-bind:class="classAlertLog">{{ alert }}件のエラーが検出されています。</div>
							<div class="cont_results-spentTime">time: <span>{{ time }} sec</span></div>
							<p><a class="px2-btn px2-btn--primary" v-bind:href="publishFileDownload">パブリッシュされたファイルをダウンロードする</a></p>
							<ul class="px2-horizontal-list px2-horizontal-list--right">
								<li><a class="px2-link px2-link--burette" v-bind:href="publishReportDownload">パブリッシュレポート</a></li>
							</ul>
						</div>
					</div>
				</div>
				<div class="cont_buttons">
					<div class="btn-group btn-group-justified" role="group">
						<div class="btn-group" role="group">
							<button class="px2-btn px2-btn--block" v-on:click="publish_option(1)">もう一度パブリッシュする</button>
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
					<ul class="px2-note-list">
						<li>ただし、バックグラウンドでプロセスが進行中ではないか、事前に確認してください。</li>
						<li><code>applock.txt</code> をテキストファイルで開くと、このファイルを生成したプロセスの <strong>プロセスID</strong> と <strong>最終アクセス日時</strong> が記載されています。この情報が手がかりになるはずです。</li>
					</ul>
				</div>
			</div>
		</div>
		<div class="contents" tabindex="-1" style="position: fixed; left: 0px; top: 0px; width: 100%; height: 100%; z-index: 10000;" v-bind:class="classModal">
			<div style="position: fixed; left: 0px; top: 0px; width: 100%; height: 100%; overflow: hidden; background: rgb(0, 0, 0); opacity: 0.5;">
			</div>
			<div style="position: absolute; left: 0px; top: 0px; padding-top: 4em; overflow: auto; width: 100%; height: 100%;">
				<div class="dialog_box" style="width: 80%; margin: 3em auto;">
					<h1>パブリッシュ</h1>
					<div>
						<div>
						<!-- <h2>パブリッシュ対象範囲</h2> -->
							<div class="cont_form_pattern" style="margin: 1em auto;">
								<select name="cont_form_pattern" v-model="isPublishOption">
									<option value="">select pattern...</option>
									<option v-for="(item, key) in isPublishPatterns" v-bind:value="key">{{ item.label }}</option>
								</select>
							</div>
							<div class="form-group" v-if="isPublishOption === ''">
								<label for="path_region">パブリッシュ対象範囲</label>
								<textarea class="form-control" placeholder="/" rows="5" v-model="classPathsRegion">{{ classPathsRegion }}</textarea>
								<span class="help-block">パブリッシュ対象のディレクトリパスを指定してください。スラッシュから始まるパスで指定します。1行1ディレクトリで複数件指定できます。</span>

								<label for="paths_ignore">パブリッシュ対象外範囲</label>
								<textarea class="form-control" placeholder="/path/ignore/1/
/path/ignore/2/" rows="5" v-model="classPathsIgnore">{{ classPathsIgnore }}</textarea>
								<span class="help-block">パブリッシュ対象外にするディレクトリパスを指定してください。スラッシュから始まるパスで指定します。1行1ディレクトリで複数件指定できます。</span>

								<label><input type="checkbox" v-model="classKeepCache" v-bind:value="classKeepCache"> キャッシュを消去しない</label>
							</div>
							<div class="form-group" v-else>
								<label for="path_region">パブリッシュ対象範囲</label>
								<textarea class="form-control" placeholder="/" rows="5" v-model="classPathsRegion">{{ classPathsRegion }}</textarea>
								<span class="help-block">パブリッシュ対象のディレクトリパスを指定してください。スラッシュから始まるパスで指定します。1行1ディレクトリで複数件指定できます。</span>

								<label for="paths_ignore">パブリッシュ対象外範囲</label>
								<textarea class="form-control" placeholder="/path/ignore/1/
/path/ignore/2/" rows="5" v-model="classPathsIgnore">{{ classPathsIgnore }}</textarea>
								<span class="help-block">パブリッシュ対象外にするディレクトリパスを指定してください。スラッシュから始まるパスで指定します。1行1ディレクトリで複数件指定できます。</span>

								<label><input type="checkbox" v-model="classKeepCache" v-bind:value="classKeepCache"> キャッシュを消去しない</label>
							</div>
						</div>
					</div>
					<div class="dialog-buttons px2-text-align-center">
						<button type="submit" class="px2-btn px2-btn--primary" v-on:click="publish">パブリッシュを実行する</button>
						<button class="px2-btn" v-on:click="cancel">キャンセル</button>
					</div>
				</div>
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
		//
		"sessionMyStatus",
		//
		"publishPatterns"
	],
	// メソッドで使う&テンプレート内で使う変数を定義
	data () {
    	return {
			// Ajax\PublishController@publishAjaxからの返り値
			info: '',
			// PublishEventからの返り値（プログレスバーの％）
			parse: 0,
			// PublishEventからの返り値（パブリシュファイル件数）
			queueCount: '',
			// PublishEventからの返り値（パブリッシュファイル情報）
			publishFile: '',
			// パブリッシュの状態（0:未パブリッシュ/1:パブリッシュオプション/2:パブリッシュ中/3:パブリッシュ後/999:パブリッシュ中のリロード）
			publishStatus: '',
			//
			isPublishPatterns: JSON.parse(this.publishPatterns),
			//
			isPublishOption: '',
			//
			pathsRegion: '/',
			//
			pathsIgnore: '',
			//
			keepCache: false,
			//
			isPublishRestart: false,
			// publishFilesをバインディング
			totalFiles: this.publishFiles,
			// alertFilesをバインディング
			alert: this.alertFiles,
			// diffSecondsをバインディング
			time: this.diffSeconds,
			// パブリッシュリカバリ画面の表示・非表示
			isRecoveryOnPublish: false,
			// アップロックを削除するためのリンクパス
			deleteApplock: '/publish/'+this.projectCode+'/'+this.branchName+'/deleteApplock',
			// existsAlertLogをバインディング
			isExistsAlertLog: this.existsAlertLog,
			// パブリッシュしたファイルをダウンロードするためのリンクパス
			publishFileDownload: '/publish/'+this.projectCode+'/'+this.branchName+'/publishFileDownload',
			// パブリッシュしたレポートをダウンロードするためのリンクパス
			publishReportDownload: '/publish/'+this.projectCode+'/'+this.branchName+'/publishReportDownload',
			//
			process: []
		}
	},
	// レンダリング前にpublish_log.csvの有無によって処理分け
	created () {
		if(this.existsPublishLog === '') {
			this.publishStatus = 0;
		} else if(this.existsPublishLog === '1' && this.existsApplock === '1') {
			this.publishStatus = 999;
		} else {
			this.publishStatus = 3;
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
		publish_option(reset) {
			if(reset === 1) {
				this.isPublishRestart = true;
			}
			this.publishStatus = 1;
		},

		publish() {
			if(this.isPublishRestart) {
				this.parse = 0;
				this.queueCount = '';
				this.publishFile = '';
				this.alert = '';
				this.time = '';
				this.isPublishOption = '';
				this.pathsRegion = '/';
				this.pathsIgnore = '';
				this.keepCache = false;
			}
			this.publishStatus = 999;
			//
			if(this.classPathsRegion) {
				if(Array.isArray(this.pathsRegion) === false) {
					var text = this.classPathsRegion.replace(/\r\n|\r/g, "\n");
					var lines = text.split( '\n' );
					var outArray = new Array();
					for ( var i = 0; i < lines.length; i++ ) {
						// 空行は無視する
						if ( lines[i] == '' ) {
							continue;
						}
						outArray.push( lines[i] );
					}
					this.pathsRegion = outArray;
				}
			}
			//
			if(this.classPathsIgnore) {
				if(Array.isArray(this.pathsIgnore) === false) {
					var text = this.classPathsIgnore.replace(/\r\n|\r/g, "\n");
					var lines = text.split( '\n' );
					var outArray = new Array();
					for ( var i = 0; i < lines.length; i++ ) {
						// 空行は無視する
						if ( lines[i] == '' ) {
							continue;
						}
						outArray.push( lines[i] );
					}
					this.pathsIgnore = outArray;
				}
			}
			//
			if(this.classKeepCache) {
				if(this.keepCache !== this.classKeepCache) {
					this.keepCache = this.classKeepCache;
				}
			}
			//
			var data = {
				'publish_option': this.isPublishOption,
				'paths_region': this.pathsRegion,
				'paths_ignore': this.pathsIgnore,
				'keep_cache': this.keepCache
			}
			// AjaxでAjax\PublishController@publishAjaxにpost処理
			axios.post('/publish/'+this.projectCode+'/'+this.branchName+'/publishAjax',data).then(res => {
				// console.log('*********', res);
				this.info = res.data.info;
			})
		},

		// 購読するチャンネルの設定
		connectChannel() {
			// Ajax\PublishController@publishAjaxの返り値
			window.Echo.channel('publish-event').listen('PublishEvent', (e) => {
				this.process = e.process.pid;
				this.publishStatus = 2;
				// 標準出力が数値または数値+改行コードだった場合parseに代入
				if(e.judge === 1) {
					this.parse = e.parse;
				}
				// パブリッシュファイル件数を計算して出力
				if(e.queue_count !== '') {
					this.queueCount = e.queue_count;
				}
				// パブリッシュしているファイル情報を配列で出力
				if(e.publish_file !== '') {
					this.publishFile = e.publish_file;
				}
				if(e.end_publish === 1) {
					// Ajax\PublishController@readCsvAjaxにpost処理
					axios.post('/publish/'+this.projectCode+'/'+this.branchName+'/readCsvAjax').then(res => {
						this.totalFiles = res.data.publish_files;
						// アラート件数を取得
						this.alert = res.data.alert_files;
						// パブリッシュにかかった時間を取得
						this.time = res.data.diff_seconds;
						// alert_log.csvの有無
						this.isExistsAlertLog = res.data.exists_alert_log;
						this.publishStatus = 3;
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

		publishCancel() {
			var data = {
                'process': this.process
            }
			// AjaxでAjax\PublishController@publishAjaxにpost処理
			axios.post('/publish/'+this.projectCode+'/'+this.branchName+'/publishCancelAjax',data).then(res => {
				// Ajax\PublishController@readCsvAjaxにpost処理
				axios.post('/publish/'+this.projectCode+'/'+this.branchName+'/readCsvAjax').then(res => {
					this.totalFiles = res.data.publish_files;
					// アラート件数を取得
					this.alert = res.data.alert_files;
					// パブリッシュにかかった時間を取得
					this.time = res.data.diff_seconds;
					// alert_log.csvの有無
					this.isExistsAlertLog = res.data.exists_alert_log;
					this.publishStatus = 3;
				})
			})
		},

		cancel() {
			if(this.existsPublishLog === '') {
				this.publishStatus = 0;
			} else if(this.existsPublishLog === '1') {
				this.publishStatus = 3;
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
				show: this.publishStatus === 0,
				hidden: this.publishStatus !== 0
			}
		},
		//
		classModal: function () {
			return {
				show: this.publishStatus === 1,
				hidden: this.publishStatus !== 1
			}
		},
		// パブリッシュ中のプログレスバーの表示・非表示
		classPublishProgress: function () {
			return {
				show: this.publishStatus === 2,
				hidden: this.publishStatus !== 2
			}
		},
		// パブリッシュ後のログ情報の表示・非表示
		classPublishLog: function () {
			return {
				show: this.publishStatus === 3,
				hidden: this.publishStatus !== 3
			}
		},
		// アラート情報の表示・非表示
		classAlertLog: function () {
			return {
				show: this.isExistsAlertLog === '1' || this.isExistsAlertLog === true,
				hidden: this.isExistsAlertLog === '' || this.isExistsAlertLog === false
			}
		},
		// パブリッシュ中のWAIT画面の表示・非表示
		classPublishWait: function () {
			return {
				show: this.publishStatus === 999,
				hidden: this.publishStatus !== 999
			}
		},
		//
		classPathsRegion: {
			get: function () {
				var result = '';
				if(this.isPublishOption === '') {
					result = '/';
				} else {
					result = this.isPublishPatterns[this.isPublishOption].paths_region[0];
				}
				return result;
			},

			set: function (value) {
				var text = value.replace(/\r\n|\r/g, "\n");
				var lines = text.split( '\n' );
				var outArray = new Array();
				for ( var i = 0; i < lines.length; i++ ) {
					// 空行は無視する
					if ( lines[i] == '' ) {
						continue;
					}
					outArray.push( lines[i] );
				}
				this.pathsRegion = outArray;
			}
		},

		//
		classPathsIgnore: {
			get: function () {
				var result = '';
				if(this.isPublishOption === '') {
					result = '';
				} else {
					result = this.isPublishPatterns[this.isPublishOption].paths_ignore[0];
				}
				return result;
			},

			set: function (value) {
				var text = value.replace(/\r\n|\r/g, "\n");
				var lines = text.split( '\n' );
				var outArray = new Array();
				for ( var i = 0; i < lines.length; i++ ) {
					// 空行は無視する
					if ( lines[i] == '' ) {
						continue;
					}
					outArray.push( lines[i] );
				}
				this.pathsIgnore = outArray;
			}
		},

		//
		classKeepCache: {
			get: function () {
				var result = '';
				if(this.isPublishOption === '') {
					result = false;
				} else {
					result = this.isPublishPatterns[this.isPublishOption].keep_cache;
				}
				return result;
			},

			set: function (value) {
				this.keepCache = value;
			}
		}
	}
}
</script>
