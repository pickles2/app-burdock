<template>
	<div>
		<div class="contents">
			<div class="cont_info"></div>
			<div class="cont_maintask_ui">
				<div class="center" style="margin-top: 70px;">
					<h2>プロジェクトに Pickles 2 をセットアップします</h2>
					<div class="cont_setup_options left">
						<h3>セットアップオプション</h3>
						<ul>
							<li>
								<label><input type="radio" value="pickles2" v-model="isCheckedOption"> Packagist から Pickles 2 プロジェクトテンプレート をセットアップ</label>
							</li>
							<li>
								<label><input type="radio" value="git" v-model="isCheckedOption"> Gitリポジトリ から クローン</label>
								<div v-bind:class="classCheckedOption">
									<div class="form-group row" style="margin-top: 20px;">
			                            <label class="col-sm-2" style="font-weight: normal;">Repository URL: </label>
			                            <div class="col-md-6">
			                                <input type="text" class="form-control" v-model="repository">
			                            </div>
			                        </div>
									<div class="form-group row">
			                            <label class="col-sm-2" style="font-weight: normal;">User name: </label>
			                            <div class="col-md-6">
			                                <input type="text" class="form-control" v-model="user_name">
			                            </div>
			                        </div>
									<div class="form-group row">
			                            <label class="col-sm-2" style="font-weight: normal;">Password: </label>
			                            <div class="col-md-6">
			                                <input type="password" class="form-control" v-model="password">
			                            </div>
			                        </div>
								</div>
							</li>
						</ul>
					</div>
					<div class="cont_setup_description">
						<p>
							<img src="/common/images/install_image_clip.png" alt="Composer ☓ Packagist ☓ Pickles 2">
						</p>
						<p>
							Pickles 2 の プロジェクトテンプレート を Packagest から自動的に取得し、セットアップを完了します。<br>
						</p>
					</div>
					<p>
						次のボタンを押して、セットアップを続けてください。<br>
					</p>
					<p>
						<button class="px2-btn px2-btn--primary" v-on:click="setup">プロジェクトをセットアップする</button>
					</p>
				</div>
			</div>
			<address class="center">(C)Pickles 2 Project.</address>
		</div>
		<div class="contents" tabindex="-1" style="position: fixed; left: 0px; top: 0px; width: 100%; height: 100%; z-index: 10000;" v-bind:class="classModal">
			<div style="position: fixed; left: 0px; top: 0px; width: 100%; height: 100%; overflow: hidden; background: rgb(0, 0, 0); opacity: 0.5;"></div>
			<div style="position: absolute; left: 0px; top: 0px; padding-top: 4em; overflow: auto; width: 100%; height: 100%;">
				<div class="dialog_box" style="width: 80%; margin: 3em auto;" v-bind:class="classSetup">
					<h1>Pickles 2 プロジェクトのセットアップ</h1>
					<div>{{ info }}</div>
					<div v-if="isCheckedOption === 'pickles2'">
						<pre style="height: 12em; overflow: auto;" v-bind:class="[isSetupBefore ? 'show' : 'hidden']">
							<div class="selectable" style="font-weight: lighter;">実行中...</div>
						</pre>
						<pre style="height: 12em; overflow: auto;" v-bind:class="[isSetupDuring ? 'show' : 'hidden']">
							<div class="selectable" style="font-weight: lighter;">{{ message }}</div>
						</pre>
					</div>
					<div v-else class="cont_scene" id="cont_before_publish-progress">
						<div class="cont_canvas">
							<div class="unit cont_progress">
								<div class="center">
									<p>クローンしています。</p>
									<p>そのまましばらくお待ちください...</p>
									<div>
										<div class="cont_progress-phase" style="font-weight: bold;">Cloning into...</div>
										<div class="cont_progress-currentTask">{{ fraction }}</div>
									</div>
									<div class="cont_progress-bar">
										<div class="progress">
											<div class="progress-bar progress-bar-striped active" role="progressbar" v-bind:style="{width: rate+'%'}">{{ rate }}%</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="dialog-buttons center" v-bind:class="[isSetupDuringButton ? 'show' : 'hidden']">
						<button disabled="disabled" class="px2-btn">セットアップしています...</button>
					</div>
					<div class="dialog-buttons center" v-bind:class="[isSetupAfterButton ? 'show' : 'hidden']">
						<button class="px2-btn px2-btn--primary" v-on:click="next">次へ</button>
					</div>
				</div>
				<div class="dialog_box" style="width: 80%; margin: 3em auto;" v-bind:class="classOption">
					<h1>Pickles 2 プロジェクトのセットアップ</h1>
					<div style="margin-bottom: 1em;">
						<table class="form_elements" style="width:100%;">
							<colgroup>
								<col width="30%">
								<col width="70%">
							</colgroup>
							<tbody>
								<tr>
									<th>Composer package name</th>
									<td>
										<p><code>composer.json</code> の name に設定するパッケージ名を入力してください。 空欄にすると name プロパティを削除します。 この値は、あとで <code>composer.json</code> を編集することで変更できます。</p>
										<p>vendorNameおよびprojectNameは半角英数字で入力してください。 詳しくは composer のドキュメントを参照してください。</p>
										<p>この値は、再利用可能なテンプレートとして公開する場合は必要になります。 そうでない場合(一般的なウェブ制作プロジェクト)では、空欄のままでよいでしょう。</p>
										<p>vendorName</p>
										<p class="">
											<input type="text" class="form-control" v-model="vendor_name">
										</p>
										<p>projectName</p>
										<p class="">
											<input type="text" class="form-control" v-model="project_name">
										</p>
									</td>
								</tr>
								<tr>
									<th>Gitリポジトリ</th>
									<td>
										<p>このオプションを有効にすると、自動的に gitローカルリポジトリが作成され、最初のコミットが作成されます。</p>
										<p>バージョン管理をしない場合、または Git以外のツールを使ってバージョン管理する場合は、このオプションをオフにしてください。</p>
										<p>通常は、オンにされることをお勧めします。</p>
										<div class="options_git_init"></div>
										<p>
											<label><input type="checkbox" v-model="isCheckedInit"> Gitリポジトリを初期化する</label>
										</p>
										<p>コミットの名義情報を入力してください。この値は git のグローバル設定領域に登録されます。</p>
										<p>repository</p>
										<p class="">
											<input type="text" class="form-control" v-model="repository">
										</p>
										<p>UserName</p>
										<p class="">
											<input type="text" class="form-control" v-model="user_name">
										</p>
										<p>password</p>
										<p class="">
											<input type="password" class="form-control" v-model="password">
										</p>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="dialog-buttons center">
						<button class="px2-btn--primary px2-btn" v-on:click="option">OK</button>
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
		"branchName"
	],
	// メソッドで使う&テンプレート内で使う変数を定義
	data () {
    	return {
			// Ajax\PublishController@publishAjaxからの返り値
			info: '',
			// パブリッシュの状態（1:未パブリッシュ/2:パブリッシュ中/3:パブリッシュ後/999:パブリッシュ中のリロード）
			setup_status: 1,
			message: '',
			isSetupBefore: true,
			isSetupDuring: false,
			isSetupDuringButton: false,
			isSetupAfter: false,
			isSetupAfterButton: false,
			isCheckedOption: 'pickles2',
			isCheckedInit: true,
			repository: '',
			user_name: '',
			password: '',
			vendor_name: '',
			project_name: '',
			i: 0,
			fraction: '',
			rate: ''
		}
	},
	// レンダリング前に***の有無によって処理分け
	created () {
		//
	},

	mounted() {
		this.connectChannel();
 	},
	// (読み込み時に)実行するメソッド
    methods: {
		setup(reset) {
			//
			var data = {
				'checked_option': this.isCheckedOption,
                'repository': this.repository,
				'user_name': this.user_name,
				'password': this.password
            }
			this.setup_status = 2;
			this.info = 'Pickles 2 プロジェクトをセットアップしています。この処理はしばらく時間がかかります。';
			this.isSetupDuringButton = true;
			// AjaxでAjax\SetupController@setupAjaxにpost処理
			axios.post('/setup/'+this.projectCode+'/'+this.branchName+'/setupAjax', data).then(res => {
				//
				this.info = 'Pickles 2 プロジェクトのセットアップが完了しました。';
				this.isSetupDuringButton = false;
				this.isSetupAfterButton = true;
				console.log(res.data);
			})
		},

		// 購読するチャンネルの設定
		connectChannel() {
			// Ajax\SetupController@setupAjaxの返り値
			window.Echo.channel('setup-event').listen('SetupEvent', (e) => {
				//
				this.i++;
				this.isSetupBefore = false;
				this.isSetupDuring = true;
				if(e.stderr) {
					this.message = this.message+e.stderr;
					if(e.rate !== '') {
						this.fraction = e.numerator + ' / ' + e.denominator;
						this.rate = e.rate;
					}
				}
				console.log(this.i + ':' + e.stderr);
			})
		},

		next(reset) {
			this.setup_status = 3;
		},

		option(reset) {
			var data = {
				'checked_option': this.isCheckedOption,
				'checked_init': this.isCheckedInit,
				'vendor_name': this.vendor_name,
				'project_name': this.project_name,
                'repository': this.repository,
				'user_name': this.user_name,
				'password': this.password
            }
			// AjaxでAjax\SetupController@setupOptionAjaxにpost処理
			axios.post('/setup/'+this.projectCode+'/'+this.branchName+'/setupOptionAjax', data).then(res => {
				//
				console.log(res.data);
			})
			location.href = '/projects/'+this.projectCode+'/'+this.branchName;
		},

		prepare() {
			alert('準備中の機能です。');
		}
	},

	computed: {
		// 未パブリッシュ時のパブリッシュボタンの表示・非表示
		classModal: function () {
			return {
				show: this.setup_status === 2 || this.setup_status === 3,
				hidden: this.setup_status === 1 || this.setup_status === 4
			}
		},

		classSetup: function () {
			return {
				show: this.setup_status === 2,
				hidden: this.setup_status !== 2
			}
		},

		classOption: function () {
			return {
				show: this.setup_status === 3,
				hidden: this.setup_status !== 3
			}
		},

		classCheckedOption: function () {
			return {
				show: this.isCheckedOption === 'git',
				hidden: this.isCheckedOption !== 'git'
			}
		}
	}
}
</script>
