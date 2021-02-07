$(window).on('load', function(){
	const it79 = require('iterate79');
	let csrfToken = $('meta[name=csrf-token]').attr('content');
	let px2all;
	let realpathDataDir;
	let realpathThemeCollectionDir;
	let pickles2ThemeEditor;

	it79.fnc({}, [
		function(it1){
			execPx2(
				'/?PX=px2dthelper.get.all',
				{
					complete: function(result){
						// console.log('=-=-=-=-=-=-=', result);
						px2all = result;
						realpathDataDir = px2all.realpath_homedir+'_sys/ram/data/';
						realpathThemeCollectionDir = px2all.realpath_theme_collection_dir;
						it1.next();
					}
				}
			);
		},
		function(it1){
			// クライアントリソースをロード
			execPx2(
				'/?PX=px2dthelper.px2te.client_resources',
				{
					complete: function(resources){
						console.log('resources:', resources);
						it79.ary(
							resources.css,
							function(it2, row, idx){
								var link = document.createElement('link');
								link.addEventListener('load', function(){
									it2.next();
								});
								$('head').append(link);
								link.rel = 'stylesheet';
								link.href = '/assets/px2te_resources/'+project_code+'/'+branch_name+'/'+row;
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
										script.src = '/assets/px2te_resources/'+project_code+'/'+branch_name+'/'+row;
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
		function(it1){
			pickles2ThemeEditor = new Pickles2ThemeEditor(); // px2te client
			it1.next();
		},
		function(it1){

			pickles2ThemeEditor.init(
				{
					'elmCanvas': $('.contents').get(0), // <- 編集画面を描画するための器となる要素
					'lang': 'ja',
					'gpiBridge': function(input, callback){
						// GPI(General Purpose Interface) Bridge
						// broccoliは、バックグラウンドで様々なデータ通信を行います。
						// GPIは、これらのデータ通信を行うための汎用的なAPIです。

						console.log('gpiBridge:', input);
						let rtn;

						$.ajax({
							"url": '/themes/'+project_code+'/'+branch_name+'/px2teGpi',
							"method": 'post',
							'data': {
								'data': input,
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
								callback(rtn);
							}
						});


						var testTimestamp = (new Date()).getTime();
						var tmpFileName = '__tmp_'+utils79.md5( Date.now() )+'.json';
						main.fs.writeFileSync( realpathDataDir+tmpFileName, JSON.stringify(input) );

						pj.execPx2(
							'/?PX=px2dthelper.px2te.gpi&appMode=web&data_filename='+encodeURIComponent( tmpFileName ),
							{
								complete: function(rtn){
									console.log('--- returned(millisec)', (new Date()).getTime() - testTimestamp);
									new Promise(function(rlv){rlv();})
										.then(function(){ return new Promise(function(rlv, rjt){
											try{
												rtn = JSON.parse(rtn);
											}catch(e){
												console.error('Failed to parse JSON String -> ' + rtn);
											}
											rlv();
										}); })
										.then(function(){ return new Promise(function(rlv, rjt){
											main.fs.unlinkSync( realpathDataDir+tmpFileName );
											// pj.updateGitStatus(function(){});
											rlv();
										}); })
										.then(function(){ return new Promise(function(rlv, rjt){
											callback( rtn );
										}); })
									;
								}
							}
						);
						return;
					},
					'themeLayoutEditor': function(themeId, layoutId){
						alert('※TODO:開発中です。' + themeId+'/'+layoutId);
						return;
					},
					'openInFinder': function(path){
						var url = realpathThemeCollectionDir;
						if(path){
							url += path;
						}
						alert('※TODO:開発中です。' + url);
					},
					'openInTextEditor': function(path){
						var url = realpathThemeCollectionDir;
						if(path){
							url += path;
						}
						alert('※TODO:開発中です。' + url);
					}
				},
				function(){
					// スタンバイ完了したら呼び出されるコールバックメソッドです。
					it1.next();
				}
			);

		} ,
		function(it1){

			$(window).on('resize', function(){
				console.log('window.resized');
				$elms.editor
					.css({
						'height': $(window).innerHeight() - 0
					})
				;
			});

			console.log('Theme Editor: Standby.');
			callback();
		}
	]);

	function execPx2(path, options){
		options = options || {};
		options.complete = options.complete || function(){};

		let rtn;

		$.ajax({
			"url": '/themes/'+project_code+'/'+branch_name+'/ajax',
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
