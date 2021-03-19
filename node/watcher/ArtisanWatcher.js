module.exports = class{

	constructor(main){
		this.main = main;
	}

	/**
	 * Artisanコマンドを実行する
	 */
	execute(fileJson, fileInfo, callback){
		callback = callback || function(){};

		// 開発中
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
