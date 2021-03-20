module.exports = class{

	constructor(main){
		this.main = main;
	}

	/**
	 * PXコマンドを実行する
	 */
	execute(fileJson, fileInfo, callback){
		callback = callback || function(){};

		const childProc = require('child_process');
		childProc.exec(
			'php ./artisan bd:pxcmd ' + JSON.stringify(fileInfo.realpath) + '',
			(err, stdout, stderr) => {
				// console.log('------------------');
				// console.log(err, stdout, stderr);
				callback();
			}
		);

		return;
	}

}
