module.exports = class{

	constructor(){
		this.env = require('dotenv').config().parsed;
		this.fs = require('fs');
		this.fsEx = require('fs-extra');
		this.utils79 = require('utils79');
		this.px2agent = require('px2agent');
		this.chokidar = require('chokidar');
		this.cceWatcher = require('./CceWatcher.js');
	}

	/**
	 * 監視対象ディレクトリパスを取得する
	 */
	getWatchDir(){
		const path = require('path');
		let pathAppDataDir = path.resolve(this.env.BD_DATA_DIR)+'/watcher/';
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
		this.fsEx.removeSync(pathAppDataDir + '/watcher/');
		// console.log(pathAppDataDir + '/watcher/');
		var tmpDirs = [
			'/watcher/',
			'/watcher/artisan/',
			'/watcher/cmd/',
			'/watcher/pxcmd/',
			'/watcher/cce/',
			'/watcher/cce/async/',
			'/watcher/cce/broadcast/',
		];
		for(var idx in tmpDirs){
			this.fsEx.mkdirSync(pathAppDataDir + tmpDirs[idx]);
		}

		_targetPath = require('path').resolve(pathAppDataDir + '/watcher/')+'/';
		console.log('WatchDir:', _targetPath);

		if( !_this.utils79.is_dir( _targetPath ) ){
			// ディレクトリが存在しないなら、監視は行わない。
			console.error('CustomConsoleExtensions: 対象ディレクトリが存在しないため、 fs.watch を起動しません。', _targetPath);
			return;
		}

		this._watcher = this.chokidar.watch('glob', {
			ignored: /(^|[\/\\])\../, // ignore dotfiles
			persistent: true
		});
		this._watcher.add(_targetPath+'**/*');

		this._watcher.on('all',function(event, filename) {
			// console.log('=-=-=-=-=', event, filename);
			filename = filename.replace(_targetPath, '');

			var fileInfo = {};
			fileInfo.realpath = require('path').resolve(_targetPath+'/'+filename);
			// console.log(event + ' - ' + fileInfo.realpath);
			if( !fileInfo.realpath || !_this.utils79.is_file(fileInfo.realpath) ){
				return;
			}
			var fileBin = _this.fs.readFileSync(fileInfo.realpath).toString();
			var fileJson = JSON.parse(fileBin);

			if( filename.match(/^cce\/(async|broadcast)[\/\\]([a-zA-Z0-9\_\-]+)[\/\\]([a-zA-Z0-9\_\-]+)[\/\\]([a-zA-Z0-9\_\-]+)[\/\\]([a-zA-Z0-9\_\-]+)[\/\\]([\s\S]+\.json)$/) ){
				var eventType = RegExp.$1;
				var projectCode = RegExp.$2;
				var branchName = RegExp.$3;
				var cceId = RegExp.$4;
				var userId = RegExp.$5;
				console.log('* ', eventType, projectCode, branchName, cceId, userId, event);

				let cceWatcher = new _this.cceWatcher(_this);
				cceWatcher.execute(projectCode, branchName, cceId, userId, eventType, fileJson, fileInfo);

				return;
			}
			return;

		});

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

}
