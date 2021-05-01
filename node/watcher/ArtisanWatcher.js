module.exports = class{

	constructor(main){
		this.main = main;
	}

	/**
	 * Artisanコマンドを実行する
	 */
	execute(fileJson, fileInfo, callback){
		callback = callback || function(){};

		// 非同期実行を許可する artisan コマンドを
		// ホワイトリスト管理する
		switch( fileJson.artisan_cmd ){
			case 'bd:pxcmd':
			case 'bd:cmd':
			case 'bd:px2:publish':
			case 'bd:plum:async':
			case 'bd:custom_console_extensions_broadcast':
			case 'bd:generate_vhosts':
			case 'bd:setup':
			case 'bd:setup_options':
				break;
			default:
				console.error('"'+fileJson.artisan_cmd+'" is disallow command.');
				callback();
				return;
				break;
		}

		var cmd = '';
		if( fileJson.options && fileJson.options.length ){
			for(var idx = 0; idx < fileJson.options.length; idx ++ ){
				cmd += ' ' + fileJson.options[idx];
			}
		}

		if( fileJson.params && typeof(fileJson.params) == typeof({}) ){
			// パラメータが渡される場合は、 JSON のパスとして渡す。
			// 受け取った側で JSON をデコードして取り出す。
			cmd += ' ' + JSON.stringify(fileInfo.realpath) + '';
		}

		const childProc = require('child_process');
		childProc.exec(
			'php ./artisan ' + JSON.stringify(fileJson.artisan_cmd) + cmd,
			(err, stdout, stderr) => {
				// console.log('------------------');
				// console.log(err, stdout, stderr);
				callback();
			}
		);

		return;
	}

}
