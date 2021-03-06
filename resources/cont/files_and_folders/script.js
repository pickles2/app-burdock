
$(window).on('load', function(){
	var defaultFileName = window.filename ||'/';
	var remoteFinder = window.remoteFinder = new RemoteFinder(
		document.getElementById('cont-finder'),
		{
			"gpiBridge": function(input, callback){ // required
				// px2style.loading();
				$.ajax({
					type : 'post',
					url : window.contRemoteFinderGpiEndpoint,
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					contentType: 'application/json',
					dataType: 'json',
					data: JSON.stringify({
						'data': JSON.stringify(input)
					}),
					success: function(data){
						// px2style.closeLoading();
						callback(data);
					}
				});
			},
			"open": function(fileinfo, callback){
				// console.log(fileinfo);
				px2style.loading();

				switch( fileinfo.ext ){
					case 'html':
					case 'htm':
						parsePx2FilePathEndpoint(fileinfo.path, function(pxExternalPath, pathFiles, pathType){
							console.log(pxExternalPath, pathType);
							var url = 'about:blank';
							if(pathType == 'contents'){
								url = window.contContentsEditorEndpoint + '?page_path='+encodeURIComponent(pxExternalPath);
							}else{
								url = window.contCommonFileEditorEndpoint + '?filename='+encodeURIComponent(fileinfo.path);
							}
							window.open(url);
						});
						break;
					default:
						var url = window.contCommonFileEditorEndpoint + '?filename='+encodeURIComponent(fileinfo.path);
						window.open(url);
						break;
				}
				px2style.closeLoading();
				callback(true);
			},
			"mkdir": function(current_dir, callback){
				// --------------------------------------
				// 新規フォルダの作成
				var $body = $('<div>').html( $('#template-mkdir').html() );
				$body.find('.cont_current_dir').text(current_dir);
				$body.find('[name=dirname]').on('change keyup', function(){
					var dirname = $body.find('[name=dirname]').val();
					if( dirname.match(/\.html?$/i) ){
						$body.find('.cont_html_ext_option').show();
					}else{
						$body.find('.cont_html_ext_option').hide();
					}
				});
				px2style.modal({
					'title': 'Create new Directory',
					'body': $body,
					'buttons': [
						$('<button type="button" class="px2-btn">')
							.text('Cancel')
							.on('click', function(e){
								px2style.closeModal();
							}),
						$('<button class="px2-btn px2-btn--primary">')
							.text('OK')
					],
					'form': {
						'submit': function(){
							px2style.closeModal();
							var dirname = $body.find('[name=dirname]').val();
							if( !dirname ){ return; }

							callback( dirname );
						}
					},
					'width': 460
				}, function(){
					$body.find('[name=dirname]').focus();
				});
			},
			"mkfile": function(current_dir, callback){
				// --------------------------------------
				// 新規ファイルの作成
				px2style.loading();

				var $body = $('<div>').html( $('#template-mkfile').html() );
				var pxExternalPath_before;
				var pathType_before;
				var pxExternalPath;
				var pathType;
				var pageInfoAll_before;
				new Promise(function(rlv){rlv();})
					.then(function(){ return new Promise(function(rlv, rjt){
						parsePx2FilePathEndpoint(current_dir+'___before.html', function(_pxExternalPath, _pathFiles, _pathType){
							pxExternalPath_before = _pxExternalPath;
							pathType_before = _pathType;
							if( !pxExternalPath_before || pathType_before != 'contents' ){
								rlv();
								return;
							}
							fs('px_command', pxExternalPath_before, {px_command: 'px2dthelper.get.all'}, function(result){
								pageInfoAll_before = result.result;
								rlv();
							});
						});
						return;
					}); })
					.then(function(){ return new Promise(function(rlv, rjt){
						$body.find('.cont_current_dir').text(current_dir);
						$body.find('[name=filename]').on('change keyup', function(){
							var filename = $body.find('[name=filename]').val();
							if( pxExternalPath_before && pathType_before == 'contents' && filename.match(/\.html?$/i) ){
								$body.find('.cont_html_ext_option').show();
							}else{
								$body.find('.cont_html_ext_option').hide();
							}
						});
						rlv();
						return;
					}); })
					.then(function(){ return new Promise(function(rlv, rjt){
						px2style.closeLoading();

						px2style.modal({
							'title': 'Create new File',
							'body': $body,
							'buttons': [
								$('<button type="button" class="px2-btn">')
									.text('Cancel')
									.on('click', function(e){
										px2style.closeModal();
									}),
								$('<button class="px2-btn px2-btn--primary">')
									.text('OK')
							],
							'form': {
								'submit': function(){
									px2style.closeModal();
									var filename = $body.find('[name=filename]').val();
									if( !filename ){ return; }
									var pageInfoAll;

									px2style.loading();

									new Promise(function(rlv){rlv();})
										.then(function(){ return new Promise(function(rlv, rjt){
											if( pxExternalPath_before && pathType_before == 'contents' && filename.match(/\.html?$/i) && $body.find('[name=is_guieditor]:checked').val() ){
												// GUI編集モードが有効
												parsePx2FilePathEndpoint(current_dir+filename, function(_pxExternalPath, _pathFiles, _pathType){
													pxExternalPath = _pxExternalPath;
													pathType = _pathType;
													if( !pxExternalPath || pathType != 'contents' ){
														rlv();
														return;
													}
													fs('px_command', pxExternalPath, {px_command: 'px2dthelper.get.all'}, function(result){
														pageInfoAll = result.result;
														fs('initialize_data_dir', pxExternalPath, {}, function(result){
															rlv();
														});
														return;
													});
													return;
												});
												return;
											}
											rlv();
											return;
										}); })
										.then(function(){ return new Promise(function(rlv, rjt){
											px2style.closeLoading();
											callback( filename );
											rlv();
											return;
										}); })
									;

								}
							},
							'width': 460
						}, function(){
							$body.find('[name=filename]').focus();
						});
						rlv();
						return;
					}); })
				;

			},
			"copy": function(copyFrom, callback){
				// --------------------------------------
				// ファイルまたはフォルダの複製
				px2style.loading();

				var is_file;
				var pxExternalPathFrom;
				var pathFilesFrom;
				var pathTypeFrom;
				var pageInfoAllFrom;
				var pxExternalPathTo;
				var pathFilesTo;
				var pathTypeTo;
				var pageInfoAllTo;
				new Promise(function(rlv){rlv();})
					.then(function(){ return new Promise(function(rlv, rjt){
						fs('is_file', copyFrom, {}, function(result){
							is_file = result.result;
							rlv();
						});
						return;
					}); })
					.then(function(){ return new Promise(function(rlv, rjt){
						if(!is_file){
							rlv();
							return;
						}
						parsePx2FilePathEndpoint(copyFrom, function(_pxExternalPath, _pathFiles, _pathType){
							pxExternalPathFrom = _pxExternalPath;
							pathFilesFrom = _pathFiles;
							pathTypeFrom = _pathType;
							if( !pxExternalPathFrom || pathTypeFrom != 'contents' ){
								rlv();
								return;
							}
							fs('px_command', pxExternalPathFrom, {px_command: 'px2dthelper.get.all'}, function(result){
								pageInfoAllFrom = result.result;
								rlv();
							});
						});
						return;
					}); })
					.then(function(){ return new Promise(function(rlv, rjt){
						var $body = $('<div>').html( $('#template-copy').html() );
						$body.find('.cont_target_item').text(copyFrom);
						$body.find('[name=copy_to]').val(copyFrom);
						if(is_file && pxExternalPathFrom && pathTypeFrom == 'contents'){
							$body.find('.cont_contents_option').show();
						}else{
							$body.find('.cont_contents_option').hide();
						}
						px2style.closeLoading();

						px2style.modal({
							'title': 'Copy',
							'body': $body,
							'buttons': [
								$('<button type="button" class="px2-btn">')
									.text('Cancel')
									.on('click', function(e){
										px2style.closeModal();
									}),
								$('<button class="px2-btn px2-btn--primary">')
									.text('複製する')
							],
							'form': {
								'submit': function(){
									px2style.closeModal();
									var copyTo = $body.find('[name=copy_to]').val();
									if( !copyTo ){ return; }
									if( copyTo == copyFrom ){ return; }

									px2style.loading();
									new Promise(function(rlv){rlv();})
										.then(function(){ return new Promise(function(rlv, rjt){
											if( is_file && $body.find('[name=is_copy_files_too]:checked').val() ){
												// リソースも一緒に複製する
												parsePx2FilePathEndpoint(copyTo, function(_pxExternalPath, _pathFiles, _pathType){
													pxExternalPathTo = _pxExternalPath;
													pathFilesTo = _pathFiles;
													pathTypeTo = _pathType;

													fs('is_dir', pathFilesFrom, {}, function(result){
														if(result.result){
															fs('copy', pathFilesFrom, {to: pathFilesTo}, function(result){
																rlv();
															});
															return;
														}
														rlv();
													});
													return;
												});
												return;
											}
											rlv();
											return;
										}); })
										.then(function(){ return new Promise(function(rlv, rjt){
											px2style.closeLoading();
											callback(copyFrom, copyTo);
											rlv();
											return;
										}); })
									;
								}
							},
							'width': 460
						}, function(){
							$body.find('[name=copy_to]').focus();
						});
						rlv();
						return;
					}); })
				;
			},
			"rename": function(renameFrom, callback){
				// --------------------------------------
				// ファイルまたはフォルダの移動・改名
				px2style.loading();

				var is_file;
				var pxExternalPathFrom;
				var pathFilesFrom;
				var pathTypeFrom;
				var pxExternalPathTo;
				var pathFilesTo;
				var pathTypeTo;
				new Promise(function(rlv){rlv();})
					.then(function(){ return new Promise(function(rlv, rjt){
						fs('is_file', renameFrom, {}, function(result){
							is_file = result.result;
							rlv();
						});
						return;
					}); })
					.then(function(){ return new Promise(function(rlv, rjt){
						if(!is_file){
							rlv();
							return;
						}
						parsePx2FilePathEndpoint(renameFrom, function(_pxExternalPath, _pathFiles, _pathType){
							pxExternalPathFrom = _pxExternalPath;
							pathFilesFrom = _pathFiles;
							pathTypeFrom = _pathType;
							rlv();
							return;
						});
						return;
					}); })
					.then(function(){ return new Promise(function(rlv, rjt){
						var $body = $('<div>').html( $('#template-rename').html() );
						$body.find('.cont_target_item').text(renameFrom);
						$body.find('[name=rename_to]').val(renameFrom);
						if(is_file && pxExternalPathFrom && pathTypeFrom == 'contents'){
							$body.find('.cont_contents_option').show();
						}else{
							$body.find('.cont_contents_option').hide();
						}

						px2style.closeLoading();

						px2style.modal({
							'title': 'Rename',
							'body': $body,
							'buttons': [
								$('<button type="button" class="px2-btn">')
									.text('Cancel')
									.on('click', function(e){
										px2style.closeModal();
									}),
								$('<button class="px2-btn px2-btn--primary">')
									.text('移動する')
							],
							'form': {
								'submit': function(){
									px2style.closeModal();
									var renameTo = $body.find('[name=rename_to]').val();
									if( !renameTo ){ return; }
									if( renameTo == renameFrom ){ return; }

									px2style.loading();

									new Promise(function(rlv){rlv();})
										.then(function(){ return new Promise(function(rlv, rjt){
											if( is_file && pxExternalPathFrom && pathTypeFrom == 'contents' && $body.find('[name=is_rename_files_too]:checked').val() ){
												// リソースも一緒に移動する
												parsePx2FilePathEndpoint(renameTo, function(_pxExternalPath, _pathFiles, _pathType){
													pxExternalPathTo = _pxExternalPath;
													pathFilesTo = _pathFiles;
													pathTypeTo = _pathType;
													if( !pxExternalPathTo || pathTypeTo != 'contents' ){
														rlv();
														return;
													}
													fs('is_dir', pathFilesFrom, {}, function(result){
														if(result.result){
															fs('rename', pathFilesFrom, {to: pathFilesTo}, function(result){
																rlv();
															});
															return;
														}
														rlv();
													});
													return;
												});
												return;
											}
											rlv();
											return;
										}); })
										.then(function(){ return new Promise(function(rlv, rjt){
											px2style.closeLoading();
											callback(renameFrom, renameTo);
											rlv();
											return;
										}); })
									;
								}
							},
							'width': 460
						}, function(){
							$body.find('[name=rename_to]').focus();
						});
						rlv();
						return;
					}); })
				;
			},
			"remove": function(target_item, callback){
				// --------------------------------------
				// ファイルまたはフォルダの削除

				px2style.loading();

				var is_file;
				var pageInfoAll;
				var pxExternalPath;
				var pathFiles;
				var pathType;
				new Promise(function(rlv){rlv();})
					.then(function(){ return new Promise(function(rlv, rjt){
						fs('is_file', target_item, {}, function(result){
							is_file = result.result;
							rlv();
						});
						return;
					}); })
					.then(function(){ return new Promise(function(rlv, rjt){
						if(!is_file){
							rlv();
							return;
						}
						parsePx2FilePathEndpoint(target_item, function(_pxExternalPath, _pathFiles, _pathType){
							pxExternalPath = _pxExternalPath;
							pathFiles = _pathFiles;
							pathType = _pathType;
							if( !pxExternalPath || pathType != 'contents' ){
								rlv();
								return;
							}
							fs('px_command', pxExternalPath, {px_command: 'px2dthelper.get.all'}, function(result){
								pageInfoAll = result.result;
								rlv();
							});
						});
						return;
					}); })
					.then(function(){ return new Promise(function(rlv, rjt){
						var $body = $('<div>').html( $('#template-remove').html() );
						$body.find('.cont_target_item').text(target_item);
						if(is_file && pxExternalPath && pathType == 'contents'){
							$body.find('.cont_contents_option').show();
						}

						px2style.closeLoading();

						px2style.modal({
							'title': 'Remove',
							'body': $body,
							'buttons': [
								$('<button type="button" class="px2-btn">')
									.text('Cancel')
									.on('click', function(e){
										px2style.closeModal();
									}),
								$('<button class="px2-btn px2-btn--danger">')
									.text('削除する')
							],
							'form': {
								'submit': function(){
									px2style.closeModal();

									px2style.loading();

									new Promise(function(rlv){rlv();})
										.then(function(){ return new Promise(function(rlv, rjt){

											if( is_file && pxExternalPath && pathType == 'contents' && $body.find('[name=is_remove_files_too]:checked').val() && pathFiles.length ){
												// リソースも一緒に削除する
												fs('is_dir', pathFiles, {}, function(result){
													if(result.result){
														fs('remove', pathFiles, {}, function(result){
															rlv();
														});
														return;
													}
													rlv();
												});
												return;
											}
											rlv();
											return;
										}); })
										.then(function(){ return new Promise(function(rlv, rjt){
											px2style.closeLoading();
											callback();
											rlv();
											return;
										}); })
									;
								}
							},
							'width': 460
						}, function(){
						});
						rlv();
						return;
					}); })
				;
			}
		}
	);
	// console.log(remoteFinder);
	remoteFinder.init(defaultFileName, {}, function(){
		console.log('ready.');
	});


	function parsePx2FilePathEndpoint( filepath, callback ){
		callback = callback || function(){};
		$.ajax({
			type : 'get',
			url : window.contApiParsePx2FilePathEndpoint,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			contentType: 'application/json',
			dataType: 'json',
			data: {
				'path': filepath
			},
			success: function(data){
				// console.log(data);
				callback(data.pxExternalPath, data.pathFiles, data.pathType);
			}
		});
		return;
	}


	function fs(method, filename, options, callback){
		callback = callback || function(){};
		$.ajax({
			type : 'post',
			url : window.contCommonFileEditorGpiEndpoint,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			contentType: 'application/json',
			dataType: 'json',
			data: JSON.stringify({
				'method': method,
				'filename': filename,
				'to': options.to,
				'px_command': options.px_command,
				'bin': options.bin
			}),
			success: function(data){
				callback(data);
			}
		});
	}

});
