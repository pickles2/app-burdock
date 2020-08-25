<template>
	<div>
		<div class="contents">
			<div class="cont_info"></div>
			<div class="cont_maintask_ui">
				<div class="px2-text-align-center" style="margin-top: 70px;">
					<h2>プロジェクトに Pickles 2 をセットアップします</h2>
					<div class="cont_setup_options px2-text-align-left">
						<h3>セットアップオプション</h3>
						<ul>
							<li>
								<label><input type="radio" value="pickles2" v-model="isCheckedOption"> Packagist から Pickles 2 プロジェクトテンプレート をセットアップ</label>
							</li>
							<li>
								<label><input type="radio" value="git" v-model="isCheckedOption"> Gitリポジトリ から クローン</label>
								<div v-bind:class="classCheckedOption">
									<div class="form-group row" style="margin-top: 20px;">
										<div class="col-md-12">
											<div class="col-md-3"></div>
											<div class="col-md-6 text-danger">{{ errorCloneRepository }}{{ cloneRepositoryConfirm }}</div>
										</div>
										<div class="col-md-12">
			                            	<label class="col-md-3" style="font-weight: normal;">Repository URL: <span class="must">Required</span></label>
				                            <div class="col-md-6">
				                                <input type="text" class="form-control" v-model="cloneRepository" placeholder="https://github.com/pickles2/preset-get-start-pickles2.git">
				                            </div>
										</div>
			                        </div>
									<div class="form-group row">
										<div class="col-md-12">
											<div class="col-md-3"></div>
											<div class="col-md-6 text-danger">{{ errorCloneUserName }}{{ cloneUserNameConfirm }}</div>
										</div>
										<div class="col-md-12">
				                            <label class="col-md-3" style="font-weight: normal;">User name: </label>
				                            <div class="col-md-6">
				                                <input type="text" class="form-control" v-model="cloneUserName" placeholder="（任意）クローンするリモートリポジトリのユーザー名">
				                            </div>
										</div>
			                        </div>
									<div class="form-group row">
										<div class="col-md-12">
											<div class="col-md-3"></div>
											<div class="col-md-6 text-danger">{{ errorClonePassword }}{{ clonePasswordConfirm }}</div>
										</div>
										<div class="col-md-12">
				                            <label class="col-md-3" style="font-weight: normal;">Password: </label>
				                            <div class="col-md-6">
				                                <input type="password" class="form-control" v-model="clonePassword" placeholder="（任意）クローンするリモートリポジトリのパスワード">
				                            </div>
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
						<div v-bind:class="classSetupStartEnable">
							<button class="px2-btn px2-btn--primary col-md-12" v-on:click="setup" style="margin-bottom: 16px;">プロジェクトをセットアップする</button>
						</div>
						<div v-bind:class="classSetupStartDisable">
							<button class="px2-btn px2-btn--primary col-md-12" disabled="disabled" style="margin-bottom: 16px;">プロジェクトをセットアップする</button>
						</div>
					</p>
				</div>
			</div>
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
								<div class="px2-text-align-center">
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
					<div class="dialog-buttons px2-text-align-center" v-bind:class="[isSetupDuringButton ? 'show' : 'hidden']">
						<button disabled="disabled" class="px2-btn">セットアップしています...</button>
					</div>
					<div class="dialog-buttons px2-text-align-center" v-bind:class="[isSetupAfterButton ? 'show' : 'hidden']">
						<button class="px2-btn px2-btn--primary" v-on:click="next">次へ</button>
					</div>
					<div class="dialog-buttons px2-text-align-center" v-bind:class="[isSetupRestartButton ? 'show' : 'hidden']">
						<button class="px2-btn px2-btn--danger" v-on:click="setup(1)">再セットアップ</button>
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
										<p>vendorNameおよびprojectNameは半角英数字と <code>-</code>(ハイフン)、 <code>_</code>(アンダースコア) が使えます。 詳しくは composer のドキュメントを参照してください。</p>
										<p>この値は、再利用可能なテンプレートとして公開する場合は必要になります。 そうでない場合(一般的なウェブ制作プロジェクト)では、空欄のままでよいでしょう。</p>
										<p>vendorName</p>
										<p class="text-danger">{{ errorVendorName }}</p>
										<p>
											<input type="text" class="form-control" v-model="vendorName" placeholder="pickles2">
										</p>
										<p>projectName</p>
										<p class="text-danger">{{ errorProjectName }}</p>
										<p>
											<input type="text" class="form-control" v-model="projectName" placeholder="preset-get-start-pickles2">
										</p>
									</td>
								</tr>
								<tr>
									<th>Gitリポジトリ</th>
									<td>
										<div v-bind:class="classOptionPickles2">
											<p>このオプションを有効にすると、自動的に gitローカルリポジトリが作成され、最初のコミットが作成されます。</p>
											<p>バージョン管理をしない場合、または Git以外のツールを使ってバージョン管理する場合は、このオプションをオフにしてください。</p>
											<p>通常は、オンにされることをお勧めします。</p>
											<div class="options_git_init"></div>
											<p>
												<label><input type="checkbox" v-model="isCheckedInit"> Gitリポジトリを初期化する</label>
											</p>
											<div v-bind:class="classCheckedInit">
												<p>コミットするGitリポジトリの情報を入力してください。</p>
												<p>Repository <span class="must">Required</span></p>
												<p class="text-danger">{{ errorRepository }}{{ repositoryConfirm }}</p>
												<p>
													<input type="text" class="form-control" v-model="repository" placeholder="https://github.com/pickles2/preset-get-start-pickles2.git">
												</p>
												<p>UserName <span class="must">Required</span></p>
												<p class="text-danger">{{ errorUserName }}{{ userNameConfirm }}</p>
												<p>
													<input type="text" class="form-control" v-model="userName" placeholder="リモートリポジトリのユーザー名">
												</p>
												<p>Password <span class="must">Required</span></p>
												<p class="text-danger">{{ errorPassword }}{{ passwordConfirm }}</p>
												<p>
													<input type="password" class="form-control" v-model="password" placeholder="リモートリポジトリのパスワード">
												</p>
											</div>
										</div>
										<div v-bind:class="classOptionGit">
											<div class="options_git_init"></div>
											<p>
												<label><input type="radio" value="original" v-model="isCheckedRepository"> クローン元のGitリポジトリに参加する</label>
											</p>
											<p>
												<label><input type="radio" value="new" v-model="isCheckedRepository"> 初期化して別のGitリポジトリを作成する</label>
											</p>
											<p>
												<label><input type="radio" value="none" v-model="isCheckedRepository"> Gitリポジトリを作成しない</label>
											</p>
											<div v-bind:class="classCheckedOriginalRepository">
												<p>クローン元のGitリポジトリの情報を入力してください。</p>
												<p>Original Repository <span class="must">Required</span></p>
												<p class="text-danger">{{ errorRepository }}{{ repositoryConfirm }}</p>
												<p>
													<input type="text" class="form-control" v-model="cloneRepository" placeholder="https://github.com/pickles2/preset-get-start-pickles2.git">
												</p>
												<p>Original UserName <span class="must">Required</span></p>
												<p class="text-danger">{{ errorUserName }}{{ userNameConfirm }}</p>
												<p>
													<input type="text" class="form-control" v-model="cloneUserName" placeholder="リモートリポジトリのユーザー名">
												</p>
												<p>Original Password <span class="must">Required</span></p>
												<p class="text-danger">{{ errorPassword }}{{ passwordConfirm }}</p>
												<p>
													<input type="password" class="form-control" v-model="clonePassword" placeholder="リモートリポジトリのパスワード">
												</p>
											</div>
											<div v-bind:class="classCheckedNewRepository">
												<p>新規作成するGitリポジトリの情報を入力してください。</p>
												<p>New Repository <span class="must">Required</span></p>
												<p class="text-danger">{{ errorRepository }}{{ repositoryConfirm }}</p>
												<p>
													<input type="text" class="form-control" v-model="cloneNewRepository" placeholder="https://github.com/pickles2/preset-get-start-pickles2.git">
												</p>
												<p>New UserName <span class="must">Required</span></p>
												<p class="text-danger">{{ errorUserName }}{{ userNameConfirm }}</p>
												<p>
													<input type="text" class="form-control" v-model="cloneNewUserName" placeholder="リモートリポジトリのユーザー名">
												</p>
												<p>New Password <span class="must">Required</span></p>
												<p class="text-danger">{{ errorPassword }}{{ passwordConfirm }}</p>
												<p>
													<input type="password" class="form-control" v-model="cloneNewPassword" placeholder="リモートリポジトリのパスワード">
												</p>
											</div>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="dialog-buttons px2-text-align-center" v-bind:class="classSetupEnable">
						<button class="px2-btn--primary px2-btn" v-on:click="option">OK</button>
					</div>
					<div class="dialog-buttons px2-text-align-center" v-bind:class="classSetupDisable">
						<button disabled="disabled" class="px2-btn">OK</button>
					</div>
				</div>

				<div class="dialog_box" style="width: 80%; margin: 3em auto;" v-bind:class="classProgress">
					<h1>Pickles 2 プロジェクトのセットアップ</h1>
					<div>{{ info }}</div>
					<div class="cont_scene" id="cont_before_publish-progress">
						<div class="cont_canvas">
							<div class="unit cont_progress">
								<div class="px2-text-align-center">
									<p>セットアップを完了しています。</p>
									<p>そのまましばらくお待ちください...</p>
									<div>
										<div class="cont_progress-phase" style="font-weight: bold;">In progress...</div>
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
		"existsSetupLog",
		"logCheckedOption",
		"logCheckedInit",
		"logCloneRepository",
		"logCloneUserName",
		"logClonePassword",
		"logSetupStatus",
		"logCheckedRepository",
		"logVendorName",
		"logProjectName",
		"logRepository",
		"logUserName",
		"logPassword"
	],
	// メソッドで使う&テンプレート内で使う変数を定義
	data () {
    	return {
			info: '',
			setup_status: 1,
			message: '',
			stdout: '',
			isSetupBefore: true,
			cloneRepository: '',
			cloneRepositoryConfirm: '',
			isCloneRepositoryEnable: false,
			cloneNewRepository: '',
			cloneUserName: '',
			cloneUserNameConfirm: '',
			isCloneUserNameEnable: false,
			cloneNewUserName: '',
			clonePassword: '',
			clonePasswordConfirm: '',
			isClonePasswordEnable: false,
			cloneNewPassword: '',
			isSetupDuring: false,
			isSetupDuringButton: true,
			isSetupAfter: false,
			isSetupAfterButton: false,
			isSetupRestartButton: false,
			isSetupDisableButton: true,
			isSetupEnableButton: false,
			isCheckedOption: 'pickles2',
			isCheckedInit: true,
			isCheckedRepository: 'original',
			repository: '',
			repositoryConfirm: '',
			isRepositoryEnable: false,
			userName: '',
			userNameConfirm: '',
			isUserNameEnable: false,
			password: '',
			passwordConfirm: '',
			isPasswordEnable: false,
			vendorName: '',
			isVendorNameEnable: true,
			projectName: '',
			isProjectNameEnable: true,
			i: 0,
			fraction: '',
			rate: 0,
			restart: ''
		}
	},
	// レンダリング前にsetupStatusの状態によって処理分け
	created () {
		//
		if(this.existsSetupLog === '') {
			this.setup_status = 1;
		} else if(this.existsSetupLog === '1') {
			this.isCheckedOption = this.logCheckedOption;
			if(this.logCheckedOption === 'pickles2') {
				if(this.logCheckedInit === '1') {
					this.cloneRepository = this.logRepository;
					this.cloneUserName = this.logUserName;
					this.clonePassword = this.logPassword;
				}
			} else if(this.logCheckedOption === 'git') {
				if(this.logCheckedRepository === 'original' || this.logCheckedRepository === '') {
					this.cloneRepository = this.logRepository;
					this.cloneUserName = this.logUserName;
					this.clonePassword = this.logPassword;
				} else if(this.logCheckedRepository === 'new') {
					this.cloneNewRepository = this.logRepository;
					this.cloneNewUserName = this.logUserName;
					this.cloneNewPassword = this.logPassword;
				}
			}
			if(this.logCheckedRepository === '') {
				this.isCheckedRepository = 'original';
			} else {
				this.isCheckedRepository = this.logCheckedRepository;
			}
			this.setupStatus = Number(this.logSetupStatus);
			this.vendorName = this.logVendorName;
			this.projectName = this.logProjectName;
		}
	},

	mounted() {
		this.connectChannel();
 	},
	// (読み込み時に)実行するメソッド
    methods: {
		// 購読するチャンネルの設定
		connectChannel() {
			// Ajax\SetupController@setupAjaxの返り値
			window.Echo.channel('setup-event').listen('SetupEvent', (e) => {
				//
				console.log(e);
				this.isCheckedOption === e.checked_option;
				this.i++;
				this.isSetupBefore = false;
				this.isSetupDuring = true;
				if(e.stdout) {
					this.message = this.message+e.stdout;
					if(/Generating autoload files/.test(e.stdout)) {
						this.stdout = e.stdout;
					}
				}
				if(e.std_array[0] === 'Receiving' && e.std_array[1] === 'objects:') {
					if(/Receiving objects: 100%/.test(e.stdout)) {
						this.stdout = e.stdout;
						this.fraction = e.denominator + ' / ' + e.denominator;
						this.rate = 100;
					} else if(e.rate !== '') {
						this.fraction = e.numerator + ' / ' + e.denominator;
						this.rate = e.rate;
					}
				}
				if(/remote: Not Found/.test(e.stdout)) {
					// リモートリポジトリが存在しない場合
					this.errorCloneRepository = 1;
					this.setupStatus = 1;
				} else if(/rejected/.test(e.stdout)) {
					// リモートリポジトリから拒否された場合
					this.errorCloneRepository = 2;
					this.setupStatus = 1;
				} else if(/unable to access/.test(e.stdout)) {
					// リモートリポジトリにアクセスできない場合
					this.errorCloneRepository = 3;
					this.setupStatus = 1;
				} else if(/could not read Username/.test(e.stdout)) {
					// ユーザー名が見つからないと言われた場合
					this.errorCloneUserName = 1;
					this.errorClonePassword = 1;
					this.setupStatus = 1;
				} else if(/Authentication failed/.test(e.stdout)) {
					// 認証に失敗した場合
					this.errorCloneUserName = 2;
					this.errorClonePassword = 2;
					this.setupStatus = 1;
				} else if (/early EOF/.test(e.stdout)) {
					// 早期EOFエラーが発生した場合
					this.errorCloneRepository = 4;
					this.setupStatus = 1;
				}
			})

			window.Echo.channel('setup-option-event').listen('SetupOptionEvent', (e) => {
				//
				this.i++;

				if(e.std_array[0] === 'Writing' && e.std_array[1] === 'objects:') {
					if(/Writing objects: 100%/.test(e.stdout)) {
						this.fraction = e.denominator + ' / ' + e.denominator;
						this.rate = 100;
					} else if(e.rate !== '') {
						this.fraction = e.numerator + ' / ' + e.denominator;
						this.rate = e.rate;
					}
				}

				if(/remote: Repository not found/.test(e.stdout)) {
					// リモートリポジトリが存在しない場合
					this.errorRepository = 1;
					this.setupStatus = 3;
				} else if(/rejected/.test(e.stdout)) {
					// リモートリポジトリから拒否された場合
					this.errorRepository = 2;
					this.setupStatus = 3;
				} else if(/unable to access/.test(e.stdout)) {
					// リモートリポジトリにアクセスできない場合
					this.errorRepository = 3;
					this.setupStatus = 3;
				} else if(/Invalid username or password/.test(e.stdout)) {
					// ユーザー名またはパスワードが違う場合
					this.errorUserName = 1;
					this.errorPassword = 1;
					this.setupStatus = 3;
				} else if(/new branch/.test(e.stdout)) {
					// location.href = '/home/'+this.projectCode+'/'+this.branchName;
				} else if(/could not read Username/.test(e.stdout)) {
					// ユーザー名が見つからないと言われた場合
					this.errorUserName = 1;
					this.errorPassword = 1;
					this.setupStatus = 3;
				}
			})
		},

		setup(reset) {
			// 引数が0の場合は新規で1の場合は再セットアップ
			if(reset === 1) {
				this.restart = 1;
			} else {
				this.restart = 0;
			}
			this.setupStatus = 2;
			var data = {
				'checked_option': this.isCheckedOption,
				'checked_init': this.isCheckedInit,
                'clone_repository': this.cloneRepository,
				'clone_user_name': this.cloneUserName,
				'clone_password': this.clonePassword,
				'setup_status': this.setup_status,
				'restart': this.restart
            }
			this.info = 'Pickles 2 プロジェクトをセットアップしています。この処理はしばらく時間がかかります。';

			// AjaxでAjax\SetupController@setupAjaxにpost処理
			axios.post('/setup/'+this.projectCode+'/'+this.branchName+'/setupAjax', data).then(res => {
				//
				if(/Generating autoload files/.test(this.stdout) && res.data.info === true) {
					this.info = 'Pickles 2 プロジェクトのセットアップが完了しました。';
					this.isSetupDuringButton = false;
					this.isSetupAfterButton = true;
					// this.next();
				} else if(/Receiving objects: 100%/.test(this.stdout) && res.data.info === true) {
					this.info = 'Pickles 2 プロジェクトのセットアップが完了しました。';
					this.isSetupDuringButton = false;
					this.isSetupAfterButton = true;
					// this.next();
				} else {
					this.info = 'Pickles 2 プロジェクトのセットアップができませんでした。もう一度やり直してください。';
					this.isSetupDuringButton = false;
					this.isSetupRestartButton = true;
				}
			})
		},

		next(reset) {
			this.setupStatus = 3;
		},

		option(reset) {
			this.message = '';
			this.repositoryConfirm = '';
			this.userNameConfirm = '';
			this.passwordConfirm = '';
			this.setupStatus = 4;
			var data = {
				'checked_option': this.isCheckedOption,
				'checked_init': this.isCheckedInit,
				'setup_status': this.setup_status,
				'checked_repository': this.isCheckedRepository,
				'vendor_name': this.vendorName,
				'project_name': this.projectName,
                'repository': this.repository,
				'clone_repository': this.cloneRepository,
				'clone_new_repository': this.cloneNewRepository,
				'user_name': this.userName,
				'clone_user_name': this.cloneUserName,
				'clone_new_user_name': this.cloneNewUserName,
				'password': this.password,
				'clone_password': this.clonePassword,
				'clone_new_password': this.cloneNewPassword
            }

			// AjaxでAjax\SetupController@setupOptionAjaxにpost処理
			axios.post('/setup/'+this.projectCode+'/'+this.branchName+'/setupOptionAjax', data).then(res => {
				if(this.rate === 100 && res.data.info === true) {
					location.href = '/home/'+this.projectCode+'/'+this.branchName;
				} else if(res.data.checked_option === 'pickles2' && res.data.checked_init === false && res.data.info === true) {
					this.rate = 100;
					this.fraction = '100 / 100';
					location.href = '/home/'+this.projectCode+'/'+this.branchName;
				} else if(res.data.checked_option === 'git' && res.data.checked_repository === 'none' && res.data.info === true) {
					this.rate = 100;
					this.fraction = '100 / 100';
					location.href = '/home/'+this.projectCode+'/'+this.branchName;
				} else if(res.data.checked_option === 'git' && res.data.checked_repository === 'original' && res.data.info === true) {
					this.rate = 100;
					this.fraction = '100 / 100';
					location.href = '/home/'+this.projectCode+'/'+this.branchName;
				} else {
					console.error('Burdock: Unknown Pattern');
					this.rate = 100;
					this.fraction = 'Unknown Pattern';
					setTimeout(function(){
						location.href = '/home/'+this.projectCode+'/'+this.branchName;
					}, 5000);
				}
			})
		},

		prepare() {
			alert('準備中の機能です。');
		}
	},

	computed: {
		// setup_status
		setupStatus: {
			get: function () {
				//
			},

			set: function(value) {
				if(value === 1) {
					this.message = '';
					this.isSetupRestartButton = false;
					this.isSetupDuring = false;
					this.isSetupDuringButton = true;
					this.isSetupBefore = true;
					this.setup_status = 1;
				} else if(value === 2) {
					this.rate = 0;
					this.fraction = '';
					this.message = '';
					this.isSetupRestartButton = false;
					this.isSetupDuring = false;
					this.isSetupDuringButton = true;
					this.isSetupBefore = true;
					this.setup_status = 2;
				} else if (value === 3) {
					this.info = '';
					this.rate = 0;
					this.fraction = '';
					this.message = '';
					this.setup_status = 3;
				} else if (value === 4) {
					this.setup_status = 4;
				}
			}
		},
		//
		classModal: function () {
			return {
				show: this.setup_status === 2 || this.setup_status === 3 || this.setup_status === 4,
				hidden: this.setup_status === 1 || this.setup_status === 5
			}
		},
		//
		classSetup: function () {
			return {
				show: this.setup_status === 2,
				hidden: this.setup_status !== 2
			}
		},
		//
		classOption: function () {
			return {
				show: this.setup_status === 3,
				hidden: this.setup_status !== 3
			}
		},
		//
		classOptionPickles2: function () {
			return {
				show: this.isCheckedOption === 'pickles2',
				hidden: this.isCheckedOption !== 'pickles2'
			}
		},
		//
		classOptionGit: function () {
			return {
				show: this.isCheckedOption === 'git',
				hidden: this.isCheckedOption !== 'git'
			}
		},
		//
		classProgress: function () {
			return {
				show: this.setup_status === 4,
				hidden: this.setup_status !== 4
			}
		},
		//
		classCheckedOption: function () {
			return {
				show: this.isCheckedOption === 'git',
				hidden: this.isCheckedOption !== 'git'
			}
		},
		// Gitリポジトリを初期化するのチェックで処理分け
		classCheckedInit: function () {
			return {
				show: this.isCheckedInit === true,
				hidden: this.isCheckedInit !== true
			}
		},
		//
		classCheckedOriginalRepository: function () {
			return {
				show: this.isCheckedRepository === 'original',
				hidden: this.isCheckedRepository !== 'original'
			}
		},
		//
		classCheckedNewRepository: function () {
			return {
				show: this.isCheckedRepository === 'new',
				hidden: this.isCheckedRepository !== 'new'
			}
		},
		// クローン用リポジトリのバリデーション
		errorCloneRepository: {
			get: function () {
				var result = '';
				if(this.isCheckedOption === 'git') {
					if(this.cloneRepository === '') {
						result = 'リポジトリが未入力です。';
						this.isCloneRepositoryEnable = false;
					} else if(/^https(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/.test(this.cloneRepository) === false) {
						result = 'リポジトリはhttps形式で入力してください。';
						this.isCloneRepositoryEnable = false;
					} else {
						result = '';
						this.isCloneRepositoryEnable = true;
					}
				} else {
					result = '';
					this.isCloneRepositoryEnable = true;
				}
				return result;
			},

			set: function (value) {
				if(value === 1) {
					this.cloneRepositoryConfirm = 'リモートリポジトリが存在しません。別のリポジトリを指定してください。';
				} else if(value === 2) {
					this.cloneRepositoryConfirm = 'リモートリポジトリに拒否されました。別のリポジトリを指定してください。';
				} else if(value === 3) {
					this.cloneRepositoryConfirm = 'リモートリポジトリにアクセスできません。ネットワークを確認してください。';
				} else if(value === 4) {
					this.cloneRepositoryConfirm = '早期EOFエラーが発生しました。ネットワークを確認してください。';
				}
			}
		},
		// クローン用ユーザー名のバリデーション
		errorCloneUserName: {
			get: function () {
				var result = '';
				this.isCloneUserNameEnable = true;
				return result;
			},

			set: function(value) {
				if(value === 1) {
					this.cloneUserNameConfirm = 'ユーザー名が必要なリポジトリです。ユーザー名を入力してください。';
				} else if(value === 2) {
					this.cloneUserNameConfirm = 'ユーザー名またはパスワードに誤りがあります。';
				}
			}
		},
		// クローン用パスワードのバリデーション
		errorClonePassword: {
			get: function () {
				var result = '';
				this.isClonePasswordEnable = true;
				return result;
			},

			set: function(value) {
				if(value === 1) {
					this.clonePasswordConfirm = 'パスワードが必要なリポジトリです。パスワードを入力してください。';
				} else if(value === 2) {
					this.clonePasswordConfirm = 'ユーザー名またはパスワードに誤りがあります。';
				}
			}
		},
		// ベンダー名のバリデーション
		errorVendorName: function () {
			var result = '';
			if(/^[A-Za-z0-9\-\_]*$/.test(this.vendorName) === false) {
				result = 'ベンダー名は半角英数字で入力してください。';
				this.isVendorNameEnable = false;
			} else {
				result = '';
				this.isVendorNameEnable = true;
			}
			return result;
		},
		// プロジェクト名のバリデーション
		errorProjectName: function () {
			var result = '';
			if(/^[A-Za-z0-9\-\_]*$/.test(this.projectName) === false) {
				result = 'プロジェクト名は半角英数字で入力してください。';
				this.isProjectNameEnable = false;
			} else {
				result = '';
				this.isProjectNameEnable = true;
			}
			return result;
		},
		// リポジトリのバリデーション
		errorRepository: {
			get: function () {
				var result = '';
				if(this.isCheckedOption === 'pickles2') {
					if(this.isCheckedInit === true) {
						if(this.repository === '') {
							result = 'リポジトリが未入力です。';
							this.isRepositoryEnable = false;
						} else if(/^https(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/.test(this.repository) === false) {
							result = 'リポジトリはhttps形式で入力してください。';
							this.isRepositoryEnable = false;
						} else {
							result = '';
							this.isRepositoryEnable = true;
						}
					} else {
						result = '';
						this.isRepositoryEnable = true;
					}
				} else if(this.isCheckedOption === 'git') {
					if(this.isCheckedRepository === 'original') {
						if(this.cloneRepository === '') {
							result = 'リポジトリが未入力です。';
							this.isRepositoryEnable = false;
						} else if(/^https(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/.test(this.cloneRepository) === false) {
							result = 'リポジトリはhttps形式で入力してください。';
							this.isRepositoryEnable = false;
						} else {
							result = '';
							this.isRepositoryEnable = true;
						}
					} else if(this.isCheckedRepository === 'new') {
						if(this.cloneNewRepository === '') {
							result = 'リポジトリが未入力です。';
							this.isRepositoryEnable = false;
						} else if(/^https(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/.test(this.cloneNewRepository) === false) {
							result = 'リポジトリはhttps形式で入力してください。';
							this.isRepositoryEnable = false;
						} else {
							result = '';
							this.isRepositoryEnable = true;
						}
					} else {
						result = '';
						this.isRepositoryEnable = true;
					}
				}
				return result;
			},

			set: function (value) {
				if(value === 1) {
					this.repositoryConfirm = 'リモートリポジトリが存在しません。別のリポジトリを指定してください。';
					this.isRepositoryEnable = false;
				} else if(value === 2) {
					this.repositoryConfirm = 'リモートリポジトリに拒否されました。別のリポジトリを指定してください。';
					this.isRepositoryEnable = false;
				} else {
					this.repositoryConfirm = 'リモートリポジトリにアクセスできません。ネットワークを確認してください。';
					this.isRepositoryEnable = true;
				}
			}
		},

		// ユーザー名のバリデーション
		errorUserName: {
			get: function () {
				var result = '';
				if(this.isCheckedOption === 'pickles2') {
					if(this.isCheckedInit === true) {
						if(this.userName === '') {
							result = 'ユーザー名が未入力です。';
							this.isUserNameEnable = false;
						} else {
							result = '';
							this.isUserNameEnable = true;
						}
					} else {
						result = '';
						this.isUserNameEnable = true;
					}
				} else if(this.isCheckedOption === 'git') {
					if(this.isCheckedRepository === 'original') {
						if(this.cloneUserName === '') {
							result = 'ユーザー名が未入力です。';
							this.isUserNameEnable = false;
						} else {
							result = '';
							this.isUserNameEnable = true;
						}
					} else if(this.isCheckedRepository === 'new') {
						if(this.cloneNewUserName === '') {
							result = 'ユーザー名が未入力です。';
							this.isUserNameEnable = false;
						} else {
							result = '';
							this.isUserNameEnable = true;
						}
					} else {
						result = '';
						this.isUserNameEnable = true;
					}
				}
				return result;
			},

			set: function(value) {
				if(value === 1) {
					this.userNameConfirm = 'ユーザー名またはパスワードに誤りがあります。';
					this.isUserNameEnable = false;
				}
			}
		},
		// パスワードのバリデーション
		errorPassword: {
			get: function () {
				var result = '';
				if(this.isCheckedOption === 'pickles2') {
					if(this.isCheckedInit === true) {
						if(this.password === '') {
							result = 'パスワードが未入力です。';
							this.isPasswordEnable = false;
						} else {
							result = '';
							this.isPasswordEnable = true;
						}
					} else {
						result = '';
						this.isPasswordEnable = true;
					}
				} else if(this.isCheckedOption === 'git') {
					if(this.isCheckedRepository === 'original') {
						if(this.clonePassword === '') {
							result = 'パスワードが未入力です。';
							this.isPasswordEnable = false;
						} else {
							result = '';
							this.isPasswordEnable = true;
						}
					} else if(this.isCheckedRepository === 'new') {
						if(this.cloneNewPassword === '') {
							result = 'パスワードが未入力です。';
							this.isPasswordEnable = false;
						} else {
							result = '';
							this.isPasswordEnable = true;
						}
					} else {
						result = '';
						this.isPasswordEnable = true;
					}
				}
				return result;
			},

			set: function(value) {
				if(value === 1) {
					this.passwordConfirm = 'ユーザー名またはパスワードに誤りがあります。';
					this.isPasswordEnable = false;
				}
			}
		},
		// プロジェクトをセットアップするボタンのON/OFF
		classSetupStartEnable: function () {
			return {
				show: this.isCloneRepositoryEnable === true && this.isCloneUserNameEnable === true && this.isClonePasswordEnable === true,
				hidden: this.isCloneRepositoryEnable === false || this.isCloneUserNameEnable === false || this.isClonePasswordEnable === false
			}
		},
		// OK DisabledボタンのON/OFF
		classSetupStartDisable: function () {
			return {
				hidden: this.isCloneRepositoryEnable === true && this.isCloneUserNameEnable === true && this.isClonePasswordEnable === true,
				show: this.isCloneRepositoryEnable === false || this.isCloneUserNameEnable === false || this.isClonePasswordEnable === false
			}
		},
		// OKボタンのON/OFF
		classSetupEnable: function () {
			return {
				show: this.isVendorNameEnable === true && this.isProjectNameEnable === true && this.isRepositoryEnable === true && this.isUserNameEnable === true && this.isPasswordEnable === true,
				hidden: this.isVendorNameEnable === false || this.isProjectNameEnable === false || this.isRepositoryEnable === false || this.isUserNameEnable === false || this.isPasswordEnable === false
			}
		},
		// OK DisabledボタンのON/OFF
		classSetupDisable: function () {
			return {
				hidden: this.isVendorNameEnable === true && this.isProjectNameEnable === true && this.isRepositoryEnable === true && this.isUserNameEnable === true && this.isPasswordEnable === true,
				show: this.isVendorNameEnable === false || this.isProjectNameEnable === false || this.isRepositoryEnable === false || this.isUserNameEnable === false || this.isPasswordEnable === false
			}
		}
	}
}
</script>
