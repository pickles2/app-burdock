@php
	$title = __('Files And Folders');
@endphp
@extends('layouts.px2_project')

@section('content')
<div class="container">
	<h1>{{ __('Files And Folders') }}</h1>
	<div class="contents">
		<div id="cont-finder"></div>
	</div>
</div>
@endsection

@section('stylesheet')
<link rel="stylesheet" href="/common/remote-finder/dist/remote-finder.css">
@endsection

@section('script')
<script src="/common/remote-finder/dist/remote-finder.js"></script>

		<!-- Template: mkfile dialog -->
		<script id="template-mkfile" type="text/template">
<p>Current Directory</p>
<div>
	<pre class="cont_current_dir"></pre>
</div>
<p>File name</p>
<div>
	<p><input type="text" name="filename" value="" class="form-control" /></p>
</div>
<div class="cont_html_ext_option" style="display: none;">
	<p>GUI編集モード</p>
	<div>
		<p><label><input type="checkbox" name="is_guieditor" value="1" checked="checked" /> GUI編集モードを有効にする</label></p>
	</div>
</div>
		</script>

		<!-- Template: mkdir dialog -->
		<script id="template-mkdir" type="text/template">
<p>Current Directory</p>
<div>
	<pre class="cont_current_dir"></pre>
</div>
<p>Directory name</p>
<div>
	<p><input type="text" name="dirname" value="" class="form-control" /></p>
</div>
		</script>

		<!-- Template: copy dialog -->
		<script id="template-copy" type="text/template">
<p>Target item</p>
<div>
	<pre class="cont_target_item"></pre>
</div>
<p>New file name</p>
<div>
	<p><input type="text" name="copy_to" value="" class="form-control" /></p>
</div>
<div class="cont_contents_option" style="display: none;">
	<div>
		<p><label><input type="checkbox" name="is_copy_files_too" value="1" checked="checked" /> リソースファイルもあわせて複製する</label></p>
	</div>
</div>
		</script>

		<!-- Template: rename dialog -->
		<script id="template-rename" type="text/template">
<p>Target item</p>
<div>
	<pre class="cont_target_item"></pre>
</div>
<p>New file name</p>
<div>
	<p><input type="text" name="rename_to" value="" class="form-control" /></p>
</div>
<div class="cont_contents_option" style="display: none;">
	<div>
		<p><label><input type="checkbox" name="is_rename_files_too" value="1" checked="checked" /> リソースファイルもあわせて移動する</label></p>
	</div>
</div>
		</script>

		<!-- Template: remove dialog -->
		<script id="template-remove" type="text/template">
<p>本当に削除してよろしいですか？</p>
<div>
	<pre class="cont_target_item"></pre>
</div>
<div class="cont_contents_option" style="display: none;">
	<div>
		<p><label><input type="checkbox" name="is_remove_files_too" value="1" checked="checked" /> リソースファイルもあわせて削除する</label></p>
	</div>
</div>
		</script>

<script>
	$(window).on('load', function(){
		var remoteFinder = window.remoteFinder = new RemoteFinder(
			document.getElementById('cont-finder'),
			{
				"gpiBridge": function(input, callback){ // required
					$.ajax({
						type : 'post',
						url : "/files-and-folders/{{ $project->project_code }}/{{ $branch_name }}/gpi",
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						contentType: 'application/json',
						dataType: 'json',
						data: JSON.stringify({
 							'data': JSON.stringify(input)
						}),
						success: function(data){
							callback(data);
						}
					});
				},
				"open": function(fileinfo, callback){
					window.open('/files-and-folders/{{ $project->project_code }}/{{ $branch_name }}/common-file-editor?filename='+encodeURIComponent(fileinfo.path));
					callback(true);
				},
				"mkdir": function(current_dir, callback){
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
					var $body = $('<div>').html( $('#template-mkfile').html() );
					$body.find('.cont_current_dir').text(current_dir);
					$body.find('[name=filename]').on('change keyup', function(){
						var filename = $body.find('[name=filename]').val();
						if( filename.match(/\.html?$/i) ){
							$body.find('.cont_html_ext_option').show();
						}else{
							$body.find('.cont_html_ext_option').hide();
						}
					});
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

								new Promise(function(rlv){rlv();})
									.then(function(){ return new Promise(function(rlv, rjt){
										_pj.execPx2(
											current_dir+filename+'?PX=px2dthelper.get.all',
											{
												complete: function(resources){
													try{
														resources = JSON.parse(resources);
													}catch(e){
														console.error('Failed to parse JSON "client_resources".', e);
													}
													// console.log(resources);
													pageInfoAll = resources;
													rlv();
												}
											}
										);
										return;
									}); })
									.then(function(){ return new Promise(function(rlv, rjt){
										if( filename.match(/\.html?$/i) && $body.find('[name=is_guieditor]:checked').val() ){
											// GUI編集モードが有効
											var realpath_data_dir = pageInfoAll.realpath_data_dir;
											px.fsEx.mkdirpSync( realpath_data_dir );
											px.fs.writeFileSync( realpath_data_dir+'data.json', '{}' );
										}
										rlv();
										return;
									}); })
									.then(function(){ return new Promise(function(rlv, rjt){
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
				},
				"copy": function(copyFrom, callback){
					var is_file;
					var pageInfoAll;
					new Promise(function(rlv){rlv();})
						.then(function(){ return new Promise(function(rlv, rjt){
							is_file = px.utils79.is_file( _pj.get('path')+copyFrom );
							rlv();
							return;
						}); })
						.then(function(){ return new Promise(function(rlv, rjt){
							if(!is_file){
								rlv();
								return;
							}
							_pj.execPx2(
								copyFrom+'?PX=px2dthelper.get.all',
								{
									complete: function(resources){
										try{
											resources = JSON.parse(resources);
										}catch(e){
											console.error('Failed to parse JSON "client_resources".', e);
										}
										console.log(resources);
										pageInfoAll = resources;
										rlv();
									}
								}
							);
							return;
						}); })
						.then(function(){ return new Promise(function(rlv, rjt){
							var $body = $('<div>').html( $('#template-copy').html() );
							$body.find('.cont_target_item').text(copyFrom);
							$body.find('[name=copy_to]').val(copyFrom);
							if(is_file){
								$body.find('.cont_contents_option').show();
							}
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

										new Promise(function(rlv){rlv();})
											.then(function(){ return new Promise(function(rlv, rjt){
												if( is_file && $body.find('[name=is_copy_files_too]:checked').val() ){
													// リソースも一緒に複製する
													_pj.execPx2(
														copyTo+'?PX=px2dthelper.get.all',
														{
															complete: function(resources){
																try{
																	resources = JSON.parse(resources);
																}catch(e){
																	console.error('Failed to parse JSON "client_resources".', e);
																}
																// console.log(resources);

																var realpath_files_from = pageInfoAll.realpath_files;
																var realpath_files_to = resources.realpath_files;
																if(px.utils79.is_dir(realpath_files_from)){
																	px.fsEx.copySync( realpath_files_from, realpath_files_to );
																}
																rlv();
															}
														}
													);
													return;
												}
												rlv();
												return;
											}); })
											.then(function(){ return new Promise(function(rlv, rjt){
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
					var is_file;
					var pageInfoAll;
					new Promise(function(rlv){rlv();})
						.then(function(){ return new Promise(function(rlv, rjt){
							is_file = px.utils79.is_file( _pj.get('path')+renameFrom );
							rlv();
							return;
						}); })
						.then(function(){ return new Promise(function(rlv, rjt){
							if(!is_file){
								rlv();
								return;
							}
							_pj.execPx2(
								renameFrom+'?PX=px2dthelper.get.all',
								{
									complete: function(resources){
										try{
											resources = JSON.parse(resources);
										}catch(e){
											console.error('Failed to parse JSON "client_resources".', e);
										}
										console.log(resources);
										pageInfoAll = resources;
										rlv();
									}
								}
							);
							return;
						}); })
						.then(function(){ return new Promise(function(rlv, rjt){
							var $body = $('<div>').html( $('#template-rename').html() );
							$body.find('.cont_target_item').text(renameFrom);
							$body.find('[name=rename_to]').val(renameFrom);
							if(is_file){
								$body.find('.cont_contents_option').show();
							}
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

										new Promise(function(rlv){rlv();})
											.then(function(){ return new Promise(function(rlv, rjt){
												if( is_file && $body.find('[name=is_rename_files_too]:checked').val() ){
													// リソースも一緒に移動する
													_pj.execPx2(
														renameTo+'?PX=px2dthelper.get.all',
														{
															complete: function(resources){
																try{
																	resources = JSON.parse(resources);
																}catch(e){
																	console.error('Failed to parse JSON "client_resources".', e);
																}
																// console.log(resources);

																var realpath_files_from = pageInfoAll.realpath_files;
																var realpath_files_to = resources.realpath_files;
																if(px.utils79.is_dir(realpath_files_from)){
																	px.fsEx.renameSync( realpath_files_from, realpath_files_to );
																}
																rlv();
															}
														}
													);
													return;
												}
												rlv();
												return;
											}); })
											.then(function(){ return new Promise(function(rlv, rjt){
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
					var is_file;
					var pageInfoAll;
					new Promise(function(rlv){rlv();})
						.then(function(){ return new Promise(function(rlv, rjt){
							is_file = px.utils79.is_file( _pj.get('path')+target_item );
							rlv();
							return;
						}); })
						.then(function(){ return new Promise(function(rlv, rjt){
							if(!is_file){
								rlv();
								return;
							}
							_pj.execPx2(
								target_item+'?PX=px2dthelper.get.all',
								{
									complete: function(resources){
										try{
											resources = JSON.parse(resources);
										}catch(e){
											console.error('Failed to parse JSON "client_resources".', e);
										}
										console.log(resources);
										pageInfoAll = resources;
										rlv();
									}
								}
							);
							return;
						}); })
						.then(function(){ return new Promise(function(rlv, rjt){
							var $body = $('<div>').html( $('#template-remove').html() );
							$body.find('.cont_target_item').text(target_item);
							if(is_file){
								$body.find('.cont_contents_option').show();
							}
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

										new Promise(function(rlv){rlv();})
											.then(function(){ return new Promise(function(rlv, rjt){
												if( is_file && $body.find('[name=is_remove_files_too]:checked').val() ){
													// リソースも一緒に削除する
													var realpath_files = pageInfoAll.realpath_files;
													if(px.utils79.is_dir(realpath_files)){
														px.fsEx.removeSync( realpath_files );
													}
												}
												rlv();
												return;
											}); })
											.then(function(){ return new Promise(function(rlv, rjt){
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
		remoteFinder.init('/', {}, function(){
			console.log('ready.');
		});

	});
</script>
@endsection
