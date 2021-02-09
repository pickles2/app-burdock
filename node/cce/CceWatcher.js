module.exports = class{

	constructor(){
		this.env = require('dotenv').config().parsed;
		this.fs = require('fs');
		this.fsEx = require('fs-extra');
		this.utils79 = require('utils79');
		this.px2agent = require('px2agent');
	}

	/**
	 * 監視対象ディレクトリパスを取得する
	 */
	getWatchDir(){
		const path = require('path');
		let pathAppDataDir = path.resolve(this.env.BD_DATA_DIR)+'/customConsoleExtensions/watcher/';
		return path.resolve(pathAppDataDir)+'/';
	}

	/**
	 * ファイル監視を開始する
	 */
	start(){
		var _this = this;
		var _targetPath;

		this.stop();

		var pathAppDataDir = require('path').resolve(this.env.BD_DATA_DIR)+'/';
		// console.log(pathAppDataDir);
		this.fsEx.removeSync(pathAppDataDir + '/customConsoleExtensions/');
		// console.log(pathAppDataDir + '/customConsoleExtensions/');
		var tmpDirs = [
			'/customConsoleExtensions/',
			'/customConsoleExtensions/watcher/',
			'/customConsoleExtensions/watcher/async/',
			'/customConsoleExtensions/watcher/broadcast/',
		];
		for(var idx in tmpDirs){
			this.fsEx.mkdirSync(pathAppDataDir + tmpDirs[idx]);
		}

		_targetPath = require('path').resolve(pathAppDataDir + '/customConsoleExtensions/watcher/')+'/';
		console.log('WatchDir:', _targetPath);

		if( !_this.utils79.is_dir( _targetPath ) ){
			// ディレクトリが存在しないなら、監視は行わない。
			console.error('CustomConsoleExtensions: 対象ディレクトリが存在しないため、 fs.watch を起動しません。', _targetPath);
			return;
		}

		this._watcher = this.fs.watch(
			_targetPath,
			{
				"recursive": true
			},
			function(event, filename) {
				// console.log('=-=-=-=-=', event, filename);
				if( !filename.match(/^(async|broadcast)[\/\\]([a-zA-Z0-9\_\-]+)[\/\\]([a-zA-Z0-9\_\-]+)[\/\\]([a-zA-Z0-9\_\-]+)[\/\\]([a-zA-Z0-9\_\-]+)[\/\\]([\s\S]+\.json)$/) ){
					return;
				}
				var eventType = RegExp.$1;
				var projectCode = RegExp.$2;
				var branchName = RegExp.$3;
				var cceId = RegExp.$4;
				var userId = RegExp.$5;
				// console.log('* ', eventType, projectCode, branchName, cceId, userId, event);

				var fileInfo = {};
				fileInfo.realpath = require('path').resolve(_targetPath+'/'+filename);
				// console.log(event + ' - ' + fileInfo.realpath);
				if( !fileInfo.realpath || !_this.utils79.is_file(fileInfo.realpath) ){
					return;
				}

				console.log('* ', eventType, projectCode, branchName);

				var fileBin = _this.fs.readFileSync(fileInfo.realpath).toString();
				var fileJson = JSON.parse(fileBin);

				_this.recieveCceEvents(projectCode, branchName, cceId, userId, eventType, fileJson, fileInfo);
				return;

			}
		);

		return;
	}

	/**
	 * ファイル監視を停止する
	 */
	stop(){
		try{
			this._watcher.close();
		}catch(e){}
		return;
	}


	/**
	* プロジェクトのブランチ別ワーキングツリーのパスを取得する
	*/
	get_project_workingtree_dir($project_code, $branch_name) {
		let $project_path = this.env['BD_DATA_DIR'] + '/repositories/' + $project_code + '---' + $branch_name + '/';
		if( this.utils79.is_dir($project_path) ){
			$project_path = require('path').resolve($project_path);
		}
		if( this.utils79.is_dir($project_path) ){
			$project_path += ($project_path ? '/' : '');
		}
		return $project_path;
	}

	get_px_execute_path($project_code, $branch_name)
	{
		let $project_path = this.get_project_workingtree_dir($project_code, $branch_name);
		let $px_execute_path = '.px_execute.php'; // <- default

		if( !this.utils79.is_dir($project_path) ){
			return false;
		}
		if(!this.utils79.is_file($project_path+'/composer.json')) {
			return false;
		}

		let $json = this.fsEx.readFileSync($project_path+'/composer.json');
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
				if(this.utils79.is_file($project_path + '/' + $arr.extra.px2package.path)) {
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
		const px2proj = this.px2agent.createProject( prjectWorkingTreeDir + px2EntryScript );

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
					var watchDir = _this.getWatchDir();
					// console.log('watchDir:', watchDir);

					var getParam = '';
					getParam += 'PX=px2dthelper.custom_console_extensions_async_run'
						+'&appMode=desktop'
						+'&asyncMethod=file'
						+'&asyncDir='+watchDir+'async/'+projectCode+'/'+branchName+'/'+cceId+'/'+userId+'/'
						+'&broadcastMethod=file'
						+'&broadcastDir='+watchDir+'broadcast/'+projectCode+'/'+branchName+'/'+cceId+'/'+userId+'/'
					;
					// console.log(getParam);

					var testTimestamp = (new Date()).getTime();
					var tmpFileName = '__tmp_'+_this.utils79.md5( Date.now() )+'.txt';
					// console.log('=-=-=-=-=-=-=-=', realpathDataDir+tmpFileName, getParam);
					_this.fs.writeFileSync( realpathDataDir+tmpFileName, getParam );

					px2proj.query(
						'/?'+getParam,
						{
							'method': 'post',
							'bodyFile': tmpFileName,
							'complete': function(rtn){
								// console.log('--- returned(millisec)', (new Date()).getTime() - testTimestamp);
								// console.log(rtn);
								_this.fsEx.unlinkSync( realpathDataDir+tmpFileName );
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
			childProc.exec('php ./artisan bd:custom_console_extensions_broadcast "'+userId+'" "'+projectCode+'" "'+branchName+'" "'+cceId+'" "'+fileInfo.realpath+'"', (err, stdout, stderr) => {
				// console.log('------------------');
				// console.log(err, stdout, stderr);
				_this.fsEx.removeSync(fileInfo.realpath);
			});

		}
		return;
	}

}
