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

		callback();
		return;
	}

}
