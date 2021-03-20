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
			case 'bd:px2:publish':
			case 'bd:custom_console_extensions_broadcast':
			case 'bd:generate_vhosts':
				break;
			default:
				callback();
				return;
				break;
		}

		const childProc = require('child_process');
		childProc.exec(
			'php ./artisan ' + JSON.stringify(fileJson.artisan_cmd) + ' ' + JSON.stringify(fileInfo.realpath) + '',
			(err, stdout, stderr) => {
				// console.log('------------------');
				// console.log(err, stdout, stderr);
				callback();
			}
		);

		return;
	}

}
