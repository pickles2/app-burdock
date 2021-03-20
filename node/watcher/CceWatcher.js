module.exports = class{

	constructor(main){
		this.main = main;
	}

	/**
	 * コマンドを実行する
	 */
	execute(projectCode, branchName, cceId, userId, eventType, fileJson, fileInfo){
		this.recieveCceEvents(projectCode, branchName, cceId, userId, eventType, fileJson, fileInfo);
	}

	/**
	* プロジェクトのブランチ別ワーキングツリーのパスを取得する
	*/
	get_project_workingtree_dir($project_code, $branch_name) {
		let $project_path = this.main.env['BD_DATA_DIR'] + '/repositories/' + $project_code + '---' + $branch_name + '/';
		if( this.main.utils79.is_dir($project_path) ){
			$project_path = require('path').resolve($project_path);
		}
		if( this.main.utils79.is_dir($project_path) ){
			$project_path += ($project_path ? '/' : '');
		}
		return $project_path;
	}

	get_px_execute_path($project_code, $branch_name)
	{
		let $project_path = this.get_project_workingtree_dir($project_code, $branch_name);
		let $px_execute_path = '.px_execute.php'; // <- default

		if( !this.main.utils79.is_dir($project_path) ){
			return false;
		}
		if(!this.main.utils79.is_file($project_path+'/composer.json')) {
			return false;
		}

		let $json = this.main.fsEx.readFileSync($project_path+'/composer.json');
		let $arr = JSON.parse($json);
		// console.log($arr);

		let $packageInfos = [];
		if( typeof($arr) === typeof({}) && $arr.extra && $arr.extra.px2package ){
			if( typeof($arr.extra.px2package) === typeof([]) ){
				$packageInfos = $arr.extra.px2package;
			}else if( typeof($arr.extra.px2package) === typeof({}) ){
				$packageInfos.push($arr.extra.px2package);
			}
		}
		// console.log($packageInfos);

		for( let idx in $packageInfos ){
			let $packageInfo = $packageInfos[idx];
			if( $packageInfo.type === 'project' ){
				if(this.main.utils79.is_file($project_path + '/' + $arr.extra.px2package.path)) {
					// console.log($arr.extra.px2package);
					$px_execute_path = $arr.extra.px2package.path;
				}
				break;
			}
		}

		// 相対パスで書かれなければいけない
		$px_execute_path = $px_execute_path.replace(/^[\/\:\;\\]*/si, '');

		return $px_execute_path;
	}

	/**
	 * Custom Console Extensions: サーバーサイドからの非同期イベントを受信する
	 */
	recieveCceEvents(projectCode, branchName, cceId, userId, eventType, content, fileInfo){
		const _this = this;
		// console.log(projectCode, branchName, eventType, content);
		let prjectWorkingTreeDir = this.get_project_workingtree_dir(projectCode, branchName);
		let px2EntryScript = this.get_px_execute_path(projectCode, branchName);
		// console.log(prjectWorkingTreeDir + px2EntryScript);
		const px2proj = this.main.px2agent.createProject( prjectWorkingTreeDir + px2EntryScript );

		if( eventType == 'async' ){
			// --------------------
			// Async

			px2proj.query('/?PX=px2dthelper.get.all', {
				"output": "json",
				"success": function(data){
					// console.log(data);
				},
				"complete": function(px2all, code){
					// console.log(px2all, code);
					try{
						px2all = JSON.parse(px2all);
					}catch(e){}

					var realpathDataDir = px2all.realpath_homedir+'_sys/ram/data/';
					var watchDir = _this.main.getWatchDir();
					// console.log('watchDir:', watchDir);

					var getParam = '';
					getParam += 'PX=px2dthelper.custom_console_extensions_async_run'
						+'&appMode=desktop'
						+'&asyncMethod=file'
						+'&asyncDir='+watchDir+'cce/async/'+projectCode+'/'+branchName+'/'+cceId+'/'+userId+'/'
						+'&broadcastMethod=file'
						+'&broadcastDir='+watchDir+'cce/broadcast/'+projectCode+'/'+branchName+'/'+cceId+'/'+userId+'/'
					;
					// console.log(getParam);

					var testTimestamp = (new Date()).getTime();
					var tmpFileName = '__tmp_'+_this.main.utils79.md5( Date.now() )+'.txt';
					// console.log('=-=-=-=-=-=-=-=', realpathDataDir+tmpFileName, getParam);
					_this.main.fs.writeFileSync( realpathDataDir+tmpFileName, getParam );

					px2proj.query(
						'/?'+getParam,
						{
							'method': 'post',
							'bodyFile': tmpFileName,
							'complete': function(rtn){
								// console.log('--- returned(millisec)', (new Date()).getTime() - testTimestamp);
								// console.log(rtn);
								_this.main.fsEx.unlinkSync( realpathDataDir+tmpFileName );
							}
						}
					);
				}
			});

		}else if( eventType == 'broadcast' ){
			// --------------------
			// Broadcast

			console.log('*** Broadcast:', content);
			console.log(fileInfo.realpath);

			const childProc = require('child_process');
			childProc.exec(
				'php ./artisan bd:custom_console_extensions_broadcast "'+userId+'" "'+projectCode+'" "'+branchName+'" "'+cceId+'" "'+fileInfo.realpath+'"',
				(err, stdout, stderr) => {
					// console.log('------------------');
					// console.log(err, stdout, stderr);
					_this.main.fsEx.removeSync(fileInfo.realpath);
				}
			);

		}
		return;
	}

}
