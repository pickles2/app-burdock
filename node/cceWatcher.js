class CceWatcher{

	constructor(){
		console.log('Starting CCE Watcher');
		this.env = require('dotenv').config().parsed;
		this.fs = require('fs');
		this.fsEx = require('fs-extra');
		this.utils79 = require('utils79');
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
		// console.log(_targetPath);

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
				if( !filename.match(/^(async|broadcast)[\/\\]([a-zA-Z0-9\_\-]+)[\/\\]([a-zA-Z0-9\_\-]+)[\/\\]([\s\S]+\.json)$/) ){
					return;
				}
				var eventType = RegExp.$1;
				var projectCode = RegExp.$2;
				var branchName = RegExp.$3;
				// console.log('* ', eventType, projectCode, branchName);

				var fileInfo = {};
				fileInfo.realpath = require('path').resolve(_targetPath+'/'+filename);
				// console.log(event + ' - ' + fileInfo.realpath);
				if( !fileInfo.realpath || !_this.utils79.is_file(fileInfo.realpath) ){
					return;
				}

				var fileBin = _this.fs.readFileSync(fileInfo.realpath).toString();
				var fileJson = JSON.parse(fileBin);
				if(eventType == 'broadcast'){
					_this.fsEx.removeSync(fileInfo.realpath);
				}

				console.log(fileJson);
				console.log('------- temporary exit;');
				return;

				var pj = main.getCurrentProject();
				if( pj && pj.projectInfo.id == projectCode ){
					_this.recieveCceEvents(projectCode, branchName, eventType, fileJson);
				}
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
	 * Custom Console Extensions: サーバーサイドからの非同期イベントを受信する
	 */
	recieveCceEvents(projectCode, branchName, eventType, content){
		// console.log(eventType, content);
		if( eventType == 'async' ){
			// --------------------
			// Async

			_this.px2dthelperGetAll('/', {}, function(px2all){
				var realpathDataDir = px2all.realpath_homedir+'_sys/ram/data/';
				var watchDir = main.cceWatcher.getWatchDir();
				// console.log('watchDir:', watchDir);

				var getParam = '';
				getParam += 'PX=px2dthelper.custom_console_extensions_async_run'
					+'&appMode=desktop'
					+'&asyncMethod=file'
					+'&asyncDir='+watchDir+'async/'+_this.projectInfo.id+'/'
					+'&broadcastMethod=file'
					+'&broadcastDir='+watchDir+'broadcast/'+_this.projectInfo.id+'/';
				// console.log(getParam);

				var testTimestamp = (new Date()).getTime();
				var tmpFileName = '__tmp_'+main.utils79.md5( Date.now() )+'.json';
				// console.log('=-=-=-=-=-=-=-=', realpathDataDir+tmpFileName, getParam);
				main.fs.writeFileSync( realpathDataDir+tmpFileName, getParam );

				_this.execPx2(
					'/?'+getParam,
					{
						'method': 'post',
						'bodyFile': tmpFileName,
						'complete': function(rtn){
							// console.log('--- returned(millisec)', (new Date()).getTime() - testTimestamp);
							// console.log(rtn);
							main.fsEx.unlinkSync( realpathDataDir+tmpFileName );
						}
					}
				);
			});

		}else if( eventType == 'broadcast' ){
			// --------------------
			// Broadcast
			console.log('*** Broadcast:', content);
			_cceBroadcastCallback( content );
		}
		return;
	}

}

const cceWatcher = new CceWatcher();
cceWatcher.start();
