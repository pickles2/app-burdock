module.exports = class{

	constructor(main){
		this.main = main;
	}

	/**
	 * コマンドを実行する
	 */
	execute(projectCode, branchName, userId, fileJson, fileInfo, callback){
		callback = callback || function(){};

		const px2proj = require('px2agent').createProject(fileJson.entry_script);
		px2proj.px_command(
			fileJson.pxcommand,
			fileJson.path,
			fileJson.params,
			function(result){
				callback();
			}
		);
		return;
	}

}
