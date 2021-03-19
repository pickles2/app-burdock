module.exports = class{

	constructor(main){
		this.main = main;
	}

	/**
	 * コマンドを実行する
	 */
	execute(fileJson, fileInfo, callback){
		callback = callback || function(){};

		// 開発中

		callback();
		return;
	}

}
