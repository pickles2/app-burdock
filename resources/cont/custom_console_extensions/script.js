$(window).on('load', function(){
	const it79 = require('iterate79');
	let csrfToken = $('meta[name=csrf-token]').attr('content');
	let cce_id = window.cce_id;
	let project_code = window.project_code;
	let branch_name = window.branch_name;
	let px2all;
	let realpathDataDir;
	let customConsoleExtensionId;
	let cceInfo;
	let $elm;

	it79.fnc({}, [
		function(it1){
			execPx2(
				'/?PX=px2dthelper.get.all',
				{
					complete: function(result){
						// console.log('=-=-=-=-=-=-=', result);
						px2all = result;
						realpathDataDir = px2all.realpath_homedir+'_sys/ram/data/';
						it1.next();
					}
				}
			);
		},
		function(it1){
			customConsoleExtensionId = window.cce_id;
			$elm = $('.contents');
			it1.next();
		},
		function(it1){
			$elm.text(customConsoleExtensionId);
			it1.next();
		},
		function(it1){
			// 拡張機能情報をロード
			execPx2(
				'/?PX=px2dthelper.custom_console_extensions.'+customConsoleExtensionId,
				{
					complete: function(objRes){
						console.log(objRes);
						if( !objRes ){
							alert('Undefined Extension.');
							return;
						}
						if( !objRes.result ){
							alert('Undefined Extension. ' + objRes.message);
							return;
						}
						cceInfo = objRes.info;
						it1.next();
					}
				}
			);
		},
		function(it1){
			// クライアントリソースをロード
			execPx2(
				'/?PX=px2dthelper.custom_console_extensions.'+customConsoleExtensionId+'.client_resources',
				{
					complete: function(res){
						// console.log('--- client_resources', res);
						if( !res.result ){
							alert('Undefined Extension. ' + res.message);
							return;
						}
						var resources = res.resources;

						it79.ary(
							resources.css,
							function(it2, row, idx){
								var link = document.createElement('link');
								link.addEventListener('load', function(){
									it2.next();
								});
								$('head').append(link);
								link.rel = 'stylesheet';
								link.href = '/assets/cce/'+customConsoleExtensionId+'/'+project_code+'/'+branch_name+'/'+row;
							},
							function(){
								it79.ary(
									resources.js,
									function(it3, row, idx){
										var script = document.createElement('script');
										script.addEventListener('load', function(){
											it3.next();
										});
										$('head').append(script);
										script.src = '/assets/cce/'+customConsoleExtensionId+'/'+project_code+'/'+branch_name+'/'+row;
									},
									function(){
										it1.next();
									}
								);
							}
						);

					}
				}
			);
		},
		// function(it1){

		// 	var watchDir = main.cceWatcher.getWatchDir();
		// 	// console.log('watchDir:', watchDir);

		// 	if(!main.utils.isDirectory(watchDir+'async/'+pj.projectInfo.id+'/')){
		// 		main.fs.mkdirSync(watchDir+'async/'+pj.projectInfo.id+'/');
		// 	}
		// 	if(!main.utils.isDirectory(watchDir+'broadcast/'+pj.projectInfo.id+'/')){
		// 		main.fs.mkdirSync(watchDir+'broadcast/'+pj.projectInfo.id+'/');
		// 	}

		// 	px2dthelperCceAgent = new Px2dthelperCceAgent({
		// 		'elm': $('.contents').get(0),
		// 		'lang': main.getDb().language,
		// 		'appMode': 'desktop',
		// 		'gpiBridge': function(input, callback){
		// 			// GPI(General Purpose Interface) Bridge

		// 			var getParam = '';
		// 			getParam += 'PX=px2dthelper.custom_console_extensions.'+customConsoleExtensionId+'.gpi'
		// 				+'&request='+encodeURIComponent( JSON.stringify(input) )
		// 				+'&appMode=desktop'
		// 				+'&asyncMethod=file'
		// 				+'&asyncDir='+watchDir+'async/'+pj.projectInfo.id+'/'
		// 				+'&broadcastMethod=file'
		// 				+'&broadcastDir='+watchDir+'broadcast/'+pj.projectInfo.id+'/';
		// 			// console.log(getParam);

		// 			var testTimestamp = (new Date()).getTime();
		// 			var tmpFileName = '__tmp_'+main.utils79.md5( Date.now() )+'.json';
		// 			// console.log('=-=-=-=-=-=-=-=', realpathDataDir+tmpFileName, getParam);
		// 			main.fs.writeFileSync( realpathDataDir+tmpFileName, getParam );

		// 			pj.execPx2(
		// 				'/?' + getParam,
		// 				{
		// 					'method': 'post',
		// 					'bodyFile': tmpFileName,
		// 					'complete': function(rtn){
		// 						// console.log('--- returned(millisec)', (new Date()).getTime() - testTimestamp);
		// 						new Promise(function(rlv){rlv();})
		// 							.then(function(){ return new Promise(function(rlv, rjt){
		// 								try{
		// 									rtn = JSON.parse(rtn);
		// 								}catch(e){
		// 									console.error('Failed to parse JSON String -> ' + rtn);
		// 								}
		// 								rlv();
		// 							}); })
		// 							.then(function(){ return new Promise(function(rlv, rjt){
		// 								main.fs.unlinkSync( realpathDataDir+tmpFileName );
		// 								rlv();
		// 							}); })
		// 							.then(function(){ return new Promise(function(rlv, rjt){
		// 								callback( rtn );
		// 							}); })
		// 						;
		// 					}
		// 				}
		// 			);
		// 			return;
		// 		}
		// 	});
		// 	pj.onCceBroadcast(function(message){
		// 		px2dthelperCceAgent.putBroadcastMessage(message);
		// 	});
		// 	it1.next();

		// } ,
		// function(it1){
		// 	eval(cceInfo.client_initialize_function+'(px2dthelperCceAgent);');
		// 	it1.next();

		// } ,
		function(it1){
			// --------------------------------------
			// スタンバイ完了
			console.log('Standby.');
		}

	]);

	function execPx2(path, options){
		options = options || {};
		options.complete = options.complete || function(){};

		let rtn;

		$.ajax({
			"url": '/custom_console_extensions/'+customConsoleExtensionId+'/'+project_code+'/'+branch_name+'/ajax',
			"method": 'post',
			'data': {
				'path': path,
				'_token': csrfToken
			},
			"success": function(data){
				// console.log(data);
				rtn = data;
			},
			"error": function(e){
				console.error('Ajax Error:', e);
			},
			"complete": function(){
				// console.log('=-=-=-=-=-=', rtn);
				options.complete(rtn);
			}
		});
	}

});
