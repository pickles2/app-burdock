<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Project;
use App\EventLog;
use App\Helpers\applock;

class GenerateVirtualHostsCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'bd:generate_vhosts';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'プレビュー、ステージング、本番環境のためのバーチャルホスト設定を生成します。';

	/** BD_DATA_DIR */
	private $realpath_vhosts_dir;


	/** $fs */
	private $fs;

	/** preview dir list */
	private $list_preview_dirs = array();


	/** 一時ファイルのファイル名 */
	private $vhosts_tmp_filename;

	/** 基本認証のデフォルトパスワードファイル */
	private $realpath_basicauth_default_htpasswd;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		$this->fs = new \tomk79\filesystem();

		$this->realpath_vhosts_dir = config('burdock.data_dir').'/vhosts/';
		$this->realpath_basicauth_default_htpasswd = $this->realpath_vhosts_dir.'default.htpasswd';
		if( is_file($this->realpath_basicauth_default_htpasswd) ){
			$this->realpath_basicauth_default_htpasswd = realpath($this->realpath_basicauth_default_htpasswd);
		}else{
			$this->realpath_basicauth_default_htpasswd = null;
		}
		$this->vhosts_tmp_filename = 'vhosts.conf.tmp.'.microtime(true);
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{

		$this->info('================================================================');
		$this->info('  Start '.$this->signature);
		$this->info('    - Local Time: '.date('Y-m-d H:i:s'));
		$this->info('    - GMT: '.gmdate('Y-m-d H:i:s'));
		$this->info('----------------------------------------------------------------');

		if( is_file( $this->realpath_basicauth_default_htpasswd ) ){
			$this->info('BasicAuth default htpasswd file: '.$this->realpath_basicauth_default_htpasswd);
		}else{
			$this->info('BasicAuth default htpasswd file: (No Set)');
		}

		$this->info('----------------------------------------------------------------');
		$this->line( '' );

		$applock = new applock('generate_vhosts', null, null, null);
		if( !$applock->lock() ){
			$this->error('generate_vhosts is now progress.');
			$encore_request = '';
			$encore_request .= 'ProcessID='.getmypid()."\r\n";
			$encore_request .= @date( 'Y-m-d H:i:s' , time() )."\r\n";
			$this->fs->save_file( $this->realpath_vhosts_dir.'encore_request.txt', $encore_request );
			return 0;
		}


		// イベントログを記録する
		$this->event_log('start', 'Starting Re-generate vhosts.conf');


		$projects = Project::all();
		if( !$projects ){
			$this->error('Failed to load Project list.');
			$applock->unlock();
			return 1;
		}

		if( !is_dir($this->realpath_vhosts_dir) ){
			mkdir($this->realpath_vhosts_dir);
		}
		if( is_file( $this->realpath_vhosts_dir.$this->vhosts_tmp_filename ) ){
			unlink( $this->realpath_vhosts_dir.$this->vhosts_tmp_filename );
		}
		touch($this->realpath_vhosts_dir.$this->vhosts_tmp_filename);

		$prevew_dirs = $this->fs->ls( config('burdock.data_dir').'/repositories/' );
		foreach( $prevew_dirs as $prevew_dir ){
			if( preg_match( '/^(.*?)\-\-\-\-(.*)$/', $prevew_dir, $matched ) ){
				$tmp_project_code = $matched[1];
				$tmp_branch_name = $matched[2];
				if( !array_key_exists($tmp_project_code, $this->list_preview_dirs) || !is_array( $this->list_preview_dirs[$tmp_project_code] ) ){
					$this->list_preview_dirs[$tmp_project_code] = array();
				}
				array_push($this->list_preview_dirs[$tmp_project_code], $tmp_branch_name);
			}
		}


		// --------------------------------------
		// 各プロジェクトのバーチャルホスト生成処理
		$count = count($projects);
		$current = 0;

		foreach ($projects as $project) {
			$current ++;
			$this->line( '' );
			$this->info( $project->project_name );
			$this->line( ' ('.$project->id.' - '.$project->project_code.')' );
			$this->line( ' '.$current.'/'.$count );

			$result = $this->execute_project_task( $project );

			$this->line( ' ----- '.$result['message'] );
			$this->line( '' );
		}

		if( !is_file($this->realpath_vhosts_dir.'vhosts.conf') || md5_file($this->realpath_vhosts_dir.'vhosts.conf') != md5_file($this->realpath_vhosts_dir.$this->vhosts_tmp_filename) ){
			// 前回の結果との差分があったら置き換える
			copy( $this->realpath_vhosts_dir.$this->vhosts_tmp_filename, $this->realpath_vhosts_dir.'vhosts.conf' );
		}
		$this->fs->rm( $this->realpath_vhosts_dir.$this->vhosts_tmp_filename );

		$this->line(' finished!');
		$this->line( '' );

		$this->line( 'Reloading Web Server Config...' );
		if( $this->reload_webserver_config() ){
			$this->line( 'Done!' );
		}else{
			$this->line( 'Failed...!' );
		}
		$this->line( '' );

		$applock->unlock();
		// イベントログを記録する
		$this->event_log('exit', 'Finished Re-generate vhosts.conf');


		// --------------------------------------
		// アンコールリクエストがあったら再実行する
		clearstatcache();
		if( $this->fs->is_file( $this->realpath_vhosts_dir.'encore_request.txt' ) ){
			if( $this->fs->rm( $this->realpath_vhosts_dir.'encore_request.txt' ) ){
				// --------------------------------------
				// vhosts.conf を更新する
				$bdAsync = new \App\Helpers\async();
				$bdAsync->set_channel_name( 'system-mentenance___generate_vhosts' );
				$bdAsync->artisan(
					'bd:generate_vhosts'
				);
				$this->line( 'Encore was accepted!' );
				$this->line( '' );
			}else{
				$this->error( 'Encore was rejected!' );
				$this->error( 'Failed to delete Encore file.' );
				$this->line( '' );
			}

		}


		$this->line('Local Time: '.date('Y-m-d H:i:s'));
		$this->line('GMT: '.gmdate('Y-m-d H:i:s'));
		$this->comment('------------ '.$this->signature.' successful ------------');
		$this->line( '' );

		return 0; // 終了コード
	}



	/**
	 * プロジェクト１件分のタスクを実行する
	 */
	private function execute_project_task( $project ){
		$realpath_template_root_dir = __DIR__.'/../../../settings/generate_vhosts/';
		$twig_loader = new \Twig\Loader\FilesystemLoader($realpath_template_root_dir);
		$twig = new \Twig\Environment($twig_loader, [
		]);

		$gitUtil = new \App\Helpers\git($project);
		$default_branch_name = 'master';
		if( strlen($project->git_url) ){
			$default_branch_name = $gitUtil->get_branch_name();
		}
		if( !strlen($default_branch_name) ){
			$default_branch_name = 'master';
		}


		// --------------------------------------
		// composer.json を読み込む
		$path_working_tree_dir = \get_project_workingtree_dir($project->project_code, $default_branch_name);
		$composerJson = false;
		if( !is_file( $path_working_tree_dir.'composer.json' ) ){
			return array(
				'result' => false,
				'message' => 'Failed to load `composer.json`',
			);
		}
		$composerJson = json_decode( file_get_contents($path_working_tree_dir.'composer.json') );
		if( !is_object( $composerJson ) ){
			return array(
				'result' => false,
				'message' => 'Failed to parse `composer.json`',
			);
		}
		$px2packages = array();
		if( property_exists( $composerJson, 'extra' ) && property_exists( $composerJson->extra, 'px2package' ) ){
			if( is_array( $composerJson->extra->px2package ) ){
				$px2packages = $composerJson->extra->px2package;
			}elseif( is_object($composerJson->extra->px2package) ){
				$px2packages = array($composerJson->extra->px2package);
			}
		}
		// var_dump($px2packages);


		// --------------------------------------
		// Pickles 2 のコンフィグ情報を取得する
		$config = \px2query($project->project_code, $default_branch_name, '/?PX=api.get.config');
		$config = json_decode($config);
		if( !is_object($config) ){
			return array(
				'result' => false,
				'message' => 'Failed to get config',
			);
		}
		$config_production_domain = null;
		$config_production_port = 80;
		// var_dump( $config );
		if( property_exists($config, 'domain') ){
			$config_production_domain = $config->domain;
		}
		if( property_exists($config, 'scheme') ){
			if( $config->scheme == 'https' ){
				$config_production_port = 443;
			}
		}
		if( strlen($config_production_domain) && preg_match('/^(.+)\:([0-9]+)$/', $config_production_domain, $matched) ){
			$config_production_domain = $matched[1];
			$config_production_port = $matched[2];
		}


		// --------------------------------------
		// 必要なパス情報を計算
		$path_entry_script = \get_px_execute_path($project->project_code, $default_branch_name);
		$path_publish_dir = null;
		if( property_exists($config, 'path_publish_dir') ){
			$path_publish_dir = $config->path_publish_dir;
		}
		$path_controot = null;
		if( property_exists($config, 'path_controot') ){
			$path_controot = $config->path_controot;
		}
		// var_dump($path_entry_script);
		// var_dump($path_publish_dir);
		// var_dump($path_controot);

		$relpath_docroot_dist = $this->fs->normalize_path($this->fs->get_realpath('/'.dirname($path_entry_script).'/'.$path_publish_dir.'/'));
		$relpath_docroot_preview = $this->fs->normalize_path($this->fs->get_realpath('/'.dirname($path_entry_script).'/'));
		if( strlen($path_controot) ){
			$path_controot = $this->fs->normalize_path($this->fs->get_realpath('/'.$path_controot.'/'));
			$relpath_docroot_preview = preg_replace( '/'.preg_quote($path_controot, '/').'$/s', '/', $relpath_docroot_preview );
		}
		// var_dump($relpath_docroot_dist);
		// var_dump($relpath_docroot_preview);

		// --------------------------------------
		// config header
		$src_vhosts = '';
		$src_vhosts .= "\n\n";
		$src_vhosts .= '## --------------------------------------'."\n";
		$src_vhosts .= '## '.$project->project_name."\n";
		$src_vhosts .= '## '.$project->project_code.' - '.$project->id."\n";
		$src_vhosts .= "\n";
		$this->put_tmp_contents( $src_vhosts );


		// --------------------------------------
		// Production
		// (Pickles 2 のコンフィグより)
		$src_vhosts = '';
		$src_vhosts .= "\n";
		$src_vhosts .= '# --------------------------------------'."\n";
		$src_vhosts .= '# Production (Project Config)'."\n";
		$this->put_tmp_contents( $src_vhosts );

		$src_vhosts = '';
		$tpl_vars = [
			'domain' => $config_production_domain,
			'port' => intval($config_production_port),
			'project_code' => $project->project_code,
			'document_root' => $this->fs->normalize_path($this->fs->get_realpath( config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/indigo/production/'.$relpath_docroot_dist )),
			'path_htpasswd' => false,
		];
		if( !strlen($config_production_domain) ){
			$src_vhosts .= '# NO DOMAIN'."\n";
		}elseif( is_file( $realpath_template_root_dir.'production.twig' ) ){
			$template = $twig->load('production.twig');
			$src_vhosts .= $template->render($tpl_vars);
		}elseif( is_file( $realpath_template_root_dir.'production-'.config('burdock.webserver').'.twig' ) ){
			$template = $twig->load('production-'.config('burdock.webserver').'.twig');
			$src_vhosts .= $template->render($tpl_vars);
		}else{
			$src_vhosts .= '<VirtualHost '.$tpl_vars['domain'].':80>'."\n";
			$src_vhosts .= '	# Production ('.$tpl_vars['project_code'].')'."\n";
			$src_vhosts .= '	ServerName '.$tpl_vars['domain'].''."\n";
			$src_vhosts .= '	DocumentRoot '.$tpl_vars['document_root']."\n";
			$src_vhosts .= '</VirtualHost>'."\n";
		}

		$this->put_tmp_contents( $src_vhosts );


		// --------------------------------------
		// Production
		// Burdock固有の閲覧環境を提供する。
		//
		// vhostsテンプレートは Staging と同じものを利用する。
		// ドメイン名生成設定は Staging と同じものを利用する。
		// 基本認証設定は プレビューと同様、プロジェクトのデフォルトを使う。
		$src_vhosts = '';
		$src_vhosts .= "\n";
		$src_vhosts .= '# --------------------------------------'."\n";
		$src_vhosts .= '# Production (Burdock provided)'."\n";
		$this->put_tmp_contents( $src_vhosts );


		$bd_config_staging_domain = \App\Helpers\utils::staging_host_name($project->project_code, 'production');
		$bd_config_staging_port = 80;
		if( strlen($bd_config_staging_domain) && preg_match('/^(.+)\:([0-9]+)$/', $bd_config_staging_domain, $matched) ){
			$bd_config_staging_domain = $matched[1];
			$bd_config_staging_port = $matched[2];
		}

		$tpl_vars = [
			'domain' => $bd_config_staging_domain,
			'port' => intval($bd_config_staging_port),
			'project_code' => $project->project_code,
			'document_root' => $this->fs->normalize_path($this->fs->get_realpath( config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/indigo/production/'.$relpath_docroot_dist )),
			'staging_index' => 'production',
			'path_htpasswd' => false,
		];
		if( $this->fs->is_file( config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/preview.htpasswd' ) ){
			$tpl_vars['path_htpasswd'] = $this->fs->get_realpath( config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/preview.htpasswd' );
		}elseif( $this->fs->is_file( $this->realpath_basicauth_default_htpasswd ) ){
			$tpl_vars['path_htpasswd'] = $this->realpath_basicauth_default_htpasswd;
		}

		$src_vhosts = '';
		if( is_file( $realpath_template_root_dir.'staging.twig' ) ){
			$template = $twig->load('staging.twig');
			$src_vhosts .= $template->render($tpl_vars);
		}elseif( is_file( $realpath_template_root_dir.'staging-'.config('burdock.webserver').'.twig' ) ){
			$template = $twig->load('staging-'.config('burdock.webserver').'.twig');
			$src_vhosts .= $template->render($tpl_vars);
		}else{
			$src_vhosts .= '<VirtualHost '.$tpl_vars['domain'].':80>'."\n";
			$src_vhosts .= '	# Production (Burdock provided) ('.$tpl_vars['project_code'].')'."\n";
			$src_vhosts .= '	ServerName '.$tpl_vars['domain'].''."\n";
			$src_vhosts .= '	DocumentRoot '.$tpl_vars['document_root'].''."\n";
			if( $tpl_vars['path_htpasswd'] ){
				$src_vhosts .= '<Directory "'.$tpl_vars['document_root'].'">'."\n";
				$src_vhosts .= '	Require valid-user'."\n";
				$src_vhosts .= '	AuthType Basic'."\n";
				$src_vhosts .= '	AuthName "Please enter your ID and password"'."\n";
				$src_vhosts .= '	AuthUserFile '.$tpl_vars['path_htpasswd']."\n";
				$src_vhosts .= '</Directory>'."\n";
			}
			$src_vhosts .= '</VirtualHost>'."\n";
		}

		$this->put_tmp_contents( $src_vhosts );


		// --------------------------------------
		// Pre-Production
		// Indigoの管理下にある `waiting`、 `backup`、`released` を閲覧するためのホスト。
		//
		// vhostsテンプレートは Staging と同じものを利用する。
		// ドメイン名生成設定は Staging と同じものを利用する。
		// 基本認証設定は プレビューと同様、プロジェクトのデフォルトを使う。
		$src_vhosts = '';
		$src_vhosts .= "\n";
		$src_vhosts .= '# --------------------------------------'."\n";
		$src_vhosts .= '# Pre-Production'."\n";
		$this->put_tmp_contents( $src_vhosts );

		$preproduction_list = array();
		$indigo_working_base_dir = config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/indigo/workdir/';
		foreach( array('waiting','backup','released') as $tmp_dir ){
			if( !is_dir( $indigo_working_base_dir.$tmp_dir.'/' ) ){
				continue;
			}
			$tmp_server_list = $this->fs->ls( $indigo_working_base_dir.$tmp_dir.'/' );
			foreach($tmp_server_list as $tmp_server){
				array_push( $preproduction_list, array(
					'division' => $tmp_dir,
					'name' => $tmp_server,
					'path' => $tmp_dir.'/'.$tmp_server,
				) );
			}
		}

		foreach( $preproduction_list as $preproduction_info ){
			$bd_config_staging_domain = \App\Helpers\utils::staging_host_name($project->project_code, $preproduction_info['division'].'-'.$preproduction_info['name']);
			$bd_config_staging_port = 80;
			if( strlen($bd_config_staging_domain) && preg_match('/^(.+)\:([0-9]+)$/', $bd_config_staging_domain, $matched) ){
				$bd_config_staging_domain = $matched[1];
				$bd_config_staging_port = $matched[2];
			}

			$tpl_vars = [
				'domain' => $bd_config_staging_domain,
				'port' => intval($bd_config_staging_port),
				'project_code' => $project->project_code,
				'document_root' => $this->fs->normalize_path($this->fs->get_realpath( $indigo_working_base_dir.$preproduction_info['path'].'/'.$relpath_docroot_dist )),
				'staging_index' => $preproduction_info['division'].'-'.$preproduction_info['name'],
				'path_htpasswd' => false,
			];
			if( $this->fs->is_file( config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/preview.htpasswd' ) ){
				$tpl_vars['path_htpasswd'] = $this->fs->get_realpath( config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/preview.htpasswd' );
			}elseif( $this->fs->is_file( $this->realpath_basicauth_default_htpasswd ) ){
				$tpl_vars['path_htpasswd'] = $this->realpath_basicauth_default_htpasswd;
			}

			$src_vhosts = '';
			if( is_file( $realpath_template_root_dir.'staging.twig' ) ){
				$template = $twig->load('staging.twig');
				$src_vhosts .= $template->render($tpl_vars);
			}elseif( is_file( $realpath_template_root_dir.'staging-'.config('burdock.webserver').'.twig' ) ){
				$template = $twig->load('staging-'.config('burdock.webserver').'.twig');
				$src_vhosts .= $template->render($tpl_vars);
			}else{
				$src_vhosts .= '<VirtualHost '.$tpl_vars['domain'].':80>'."\n";
				$src_vhosts .= '	# Pre-Production '.$tpl_vars['staging_index'].' ('.$tpl_vars['project_code'].')'."\n";
				$src_vhosts .= '	ServerName '.$tpl_vars['domain'].''."\n";
				$src_vhosts .= '	DocumentRoot '.$tpl_vars['document_root'].''."\n";
				if( $tpl_vars['path_htpasswd'] ){
					$src_vhosts .= '<Directory "'.$tpl_vars['document_root'].'">'."\n";
					$src_vhosts .= '	Require valid-user'."\n";
					$src_vhosts .= '	AuthType Basic'."\n";
					$src_vhosts .= '	AuthName "Please enter your ID and password"'."\n";
					$src_vhosts .= '	AuthUserFile '.$tpl_vars['path_htpasswd']."\n";
					$src_vhosts .= '</Directory>'."\n";
				}
				$src_vhosts .= '</VirtualHost>'."\n";
			}
			$this->put_tmp_contents( $src_vhosts );
		}


		// --------------------------------------
		// Preview
		$src_vhosts = '';
		$src_vhosts .= "\n";
		$src_vhosts .= '# --------------------------------------'."\n";
		$src_vhosts .= '# Preview'."\n";
		$this->put_tmp_contents( $src_vhosts );

		foreach($this->list_preview_dirs[$project->project_code] as $branch_name){

			$burdockProjectManager = new \tomk79\picklesFramework2\burdock\projectManager\main( config('burdock.data_dir') );
			$project_branch = $burdockProjectManager->project($project->project_code)->branch($branch_name, 'preview');
			$project_branch_status = $project_branch->status();
			$project_branch_entry_script = $project_branch->get_entry_script();
			$project_branch_info = $project_branch->get_project_info();

			$bd_config_preview_domain = \App\Helpers\utils::preview_host_name($project->project_code, $branch_name);
			$bd_config_preview_port = 80;
			if( strlen($bd_config_preview_domain) && preg_match('/^(.+)\:([0-9]+)$/', $bd_config_preview_domain, $matched) ){
				$bd_config_preview_domain = $matched[1];
				$bd_config_preview_port = $matched[2];
			}

			$tpl_vars = [
				'domain' => $bd_config_preview_domain,
				'port' => intval($bd_config_preview_port),
				'project_code' => $project->project_code,
				'document_root' => $this->fs->get_realpath( config('burdock.data_dir').'/repositories/'.urlencode($project->project_code).'----'.urlencode($branch_name).'/'.$relpath_docroot_preview ),
				'branch_name' => $branch_name,
				'path_htpasswd' => false,
				'nginx_rewrite_entry_script' => '',
			];
			if( $this->fs->is_file( config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/preview.htpasswd' ) ){
				$tpl_vars['path_htpasswd'] = $this->fs->get_realpath( config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/preview.htpasswd' );
			}elseif( $this->fs->is_file( $this->realpath_basicauth_default_htpasswd ) ){
				$tpl_vars['path_htpasswd'] = $this->realpath_basicauth_default_htpasswd;
			}

			$path_entry_script = $project_branch_info->config->path_controot.basename($project_branch_entry_script);
			$path_entry_script = preg_replace('/^\/*/', '', $path_entry_script);
			$tpl_vars['nginx_rewrite_entry_script'] .= '	# Pickles Framework へ転送'."\n";
			$tpl_vars['nginx_rewrite_entry_script'] .= '	location ~ ^(?!/'.preg_quote($path_entry_script, '').'/).*?(/|\.(html|htm|css|js))$ {'."\n";
			$tpl_vars['nginx_rewrite_entry_script'] .= '		rewrite ^'.$project_branch_info->config->path_controot.'(.*)$ /'.$path_entry_script.'/$1 last;'."\n";
			$tpl_vars['nginx_rewrite_entry_script'] .= '	}'."\n";
			$tpl_vars['nginx_rewrite_entry_script'] .= '	# 除外ファイルへのアクセスを拒否'."\n";
			$tpl_vars['nginx_rewrite_entry_script'] .= '	location ~ ^/(?!'.preg_quote($path_entry_script, '').'/).*?\.(?:ignore)([\.\/].*)?$ {'."\n";
			$tpl_vars['nginx_rewrite_entry_script'] .= '		rewrite ^'.$project_branch_info->config->path_controot.'(.*)$ /'.$path_entry_script.'/.ignore.html last;'."\n";
			$tpl_vars['nginx_rewrite_entry_script'] .= '	}'."\n";

			$src_vhosts = '';
			if( is_file( $realpath_template_root_dir.'preview.twig' ) ){
				$template = $twig->load('preview.twig');
				$src_vhosts .= $template->render($tpl_vars);
			}elseif( is_file( $realpath_template_root_dir.'preview-'.config('burdock.webserver').'.twig' ) ){
				$template = $twig->load('preview-'.config('burdock.webserver').'.twig');
				$src_vhosts .= $template->render($tpl_vars);
			}else{
				$src_vhosts .= '<VirtualHost '.$tpl_vars['domain'].':80>'."\n";
				$src_vhosts .= '	# Preview '.$tpl_vars['branch_name'].' ('.$tpl_vars['project_code'].')'."\n";
				$src_vhosts .= '	ServerName '.$tpl_vars['domain'].''."\n";
				$src_vhosts .= '	DocumentRoot '.$tpl_vars['document_root']."\n";
				if( $tpl_vars['path_htpasswd'] ){
					$src_vhosts .= '<Directory "'.$tpl_vars['document_root'].'">'."\n";
					$src_vhosts .= '	Require valid-user'."\n";
					$src_vhosts .= '	AuthType Basic'."\n";
					$src_vhosts .= '	AuthName "Please enter your ID and password"'."\n";
					$src_vhosts .= '	AuthUserFile '.$tpl_vars['path_htpasswd']."\n";
					$src_vhosts .= '</Directory>'."\n";
				}
				$src_vhosts .= '</VirtualHost>'."\n";
			}

			$this->put_tmp_contents( $src_vhosts );
		}


		// --------------------------------------
		// Staging
		$src_vhosts = '';
		$src_vhosts .= "\n";
		$src_vhosts .= '# --------------------------------------'."\n";
		$src_vhosts .= '# Staging'."\n";
		$this->put_tmp_contents( $src_vhosts );

		for( $i = 0; $i < 10; $i ++ ){

			$bd_config_staging_domain = \App\Helpers\utils::staging_host_name($project->project_code, 'stg'.($i+1));
			$bd_config_staging_port = 80;
			if( strlen($bd_config_staging_domain) && preg_match('/^(.+)\:([0-9]+)$/', $bd_config_staging_domain, $matched) ){
				$bd_config_staging_domain = $matched[1];
				$bd_config_staging_port = $matched[2];
			}

			$tpl_vars = [
				'domain' => $bd_config_staging_domain,
				'port' => intval($bd_config_staging_port),
				'project_code' => $project->project_code,
				'document_root' => $this->fs->get_realpath( config('burdock.data_dir').'/stagings/'.urlencode($project->project_code).'---stg'.($i+1).'/'.$relpath_docroot_dist ),
				'staging_index' => $i+1,
				'path_htpasswd' => false,
			];
			if( $this->fs->is_file( config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/plum_data_dir/htpasswds/stg'.($i).'.htpasswd' ) ){
				$tpl_vars['path_htpasswd'] = $this->fs->get_realpath( config('burdock.data_dir').'/projects/'.urlencode($project->project_code).'/plum_data_dir/htpasswds/stg'.($i).'.htpasswd' );
			}elseif( $this->fs->is_file( $this->realpath_basicauth_default_htpasswd ) ){
				$tpl_vars['path_htpasswd'] = $this->realpath_basicauth_default_htpasswd;
			}

			$src_vhosts = '';
			if( is_file( $realpath_template_root_dir.'staging.twig' ) ){
				$template = $twig->load('staging.twig');
				$src_vhosts .= $template->render($tpl_vars);
			}elseif( is_file( $realpath_template_root_dir.'staging-'.config('burdock.webserver').'.twig' ) ){
				$template = $twig->load('staging-'.config('burdock.webserver').'.twig');
				$src_vhosts .= $template->render($tpl_vars);
			}else{
				$src_vhosts .= '<VirtualHost '.$tpl_vars['domain'].':80>'."\n";
				$src_vhosts .= '	# Staging '.$tpl_vars['staging_index'].' ('.$tpl_vars['project_code'].')'."\n";
				$src_vhosts .= '	ServerName '.$tpl_vars['domain'].''."\n";
				$src_vhosts .= '	DocumentRoot '.$tpl_vars['document_root'].''."\n";
				if( $tpl_vars['path_htpasswd'] ){
					$src_vhosts .= '<Directory "'.$tpl_vars['document_root'].'">'."\n";
					$src_vhosts .= '	Require valid-user'."\n";
					$src_vhosts .= '	AuthType Basic'."\n";
					$src_vhosts .= '	AuthName "Please enter your ID and password"'."\n";
					$src_vhosts .= '	AuthUserFile '.$tpl_vars['path_htpasswd']."\n";
					$src_vhosts .= '</Directory>'."\n";
				}
				$src_vhosts .= '</VirtualHost>'."\n";
			}

			$this->put_tmp_contents( $src_vhosts );
		}

		return array(
			'result' => true,
			'message' => 'OK',
		);
	}


	/**
	 * ウェブサーバーの設定を再読み込みする
	 */
	private function reload_webserver_config(){
		$command = config('burdock.command_reload_webserver_config');
		if( !$command ){
			$this->line( 'config "burdock.command_reload_webserver_config" (or env "BD_COMMAND_RELOAD_WEBSERVER_CONFIG") is not set.' );
			return false;
		}

		$result = exec( $command );

		return true;
	}

	/**
	 * 一時ファイルにテキストを出力
	 */
	private function put_tmp_contents($src){
		file_put_contents( $this->realpath_vhosts_dir.$this->vhosts_tmp_filename, $src, FILE_APPEND );
		return true;
	}

	/**
	 * イベントログを記録する
	 */
	private function event_log( $progress, $message ){
		// イベントログを記録する
		$eventLog = new EventLog;
		$eventLog->pid = getmypid();
		$eventLog->function_name = 'generate_vhosts';
		$eventLog->event_name = 'generate';
		$eventLog->progress = $progress;
		$eventLog->message = $message;
		$eventLog->save();
		return;
	}
}
