<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Project;

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
	protected $description = '本番配信ツール indigo が、配信予約に従って配信を実行する。';

	/** BD_DATA_DIR */
	private $realpath_vhosts_dir;


	/** $fs */
	private $fs;

	/** preview dir list */
	private $list_preview_dirs = array();

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
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
		$this->line( '' );

		$projects = Project::all();
		if( !$projects ){
			$this->error('Failed to load Project list.');
			return 1;
		}

		$this->realpath_vhosts_dir = env('BD_DATA_DIR').'/vhosts/';
		if( !is_dir($this->realpath_vhosts_dir) ){
			mkdir($this->realpath_vhosts_dir);
		}
		if( is_file( $this->realpath_vhosts_dir.'vhosts.conf.tmp' ) ){
			unlink( $this->realpath_vhosts_dir.'vhosts.conf.tmp' );
		}
		touch($this->realpath_vhosts_dir.'vhosts.conf.tmp');

		$this->fs = new \tomk79\filesystem();
		$prevew_dirs = $this->fs->ls( env('BD_DATA_DIR').'/repositories/' );
		foreach( $prevew_dirs as $prevew_dir ){
			if( preg_match( '/^(.*?)\-\-\-(.*)$/', $prevew_dir, $matched ) ){
				$tmp_project_code = $matched[1];
				$tmp_branch_name = $matched[2];
				if( !array_key_exists($tmp_project_code, $this->list_preview_dirs) || !is_array( $this->list_preview_dirs[$tmp_project_code] ) ){
					$this->list_preview_dirs[$tmp_project_code] = array();
				}
				array_push($this->list_preview_dirs[$tmp_project_code], $tmp_branch_name);
			}
		}

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
			sleep(1);
		}

		if( !is_file($this->realpath_vhosts_dir.'vhosts.conf') || md5_file($this->realpath_vhosts_dir.'vhosts.conf') != md5_file($this->realpath_vhosts_dir.'vhosts.conf.tmp') ){
			// 前回の結果との差分があったら置き換える
			copy( $this->realpath_vhosts_dir.'vhosts.conf.tmp', $this->realpath_vhosts_dir.'vhosts.conf' );
		}
		$this->fs->rm( $this->realpath_vhosts_dir.'vhosts.conf.tmp' );

		$this->line(' finished!');
		$this->line( '' );

		$this->line( 'Reloading Web Server Config...' );
		if( $this->reload_webserver_config() ){
			$this->line( 'Done!' );
		}else{
			$this->line( 'Failed...!' );
		}
		$this->line( '' );

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
		$domain = null;
		// var_dump( $config );
		if( property_exists($config, 'domain') ){
			$domain = $config->domain;
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

		// Production
		$src_vhosts = '';
		$src_vhosts .= "\n";
		$src_vhosts .= '# Production'."\n";
		$this->put_tmp_contents( $src_vhosts );

		$src_vhosts = '';
		$tpl_vars = [
			'domain' => $domain,
			'project_code' => $project->project_code,
			'document_root' => $this->fs->normalize_path($this->fs->get_realpath( env('BD_DATA_DIR').'/projects/'.$project->project_code.'/indigo/production/'.$relpath_docroot_dist )),
			'path_htpasswd' => false,
		];
		if( !strlen($domain) ){
			$src_vhosts .= '# NO DOMAIN'."\n";
		}elseif( is_file( $realpath_template_root_dir.'production.twig' ) ){
			$template = $twig->load('production.twig');
			$src_vhosts .= $template->render($tpl_vars);
		}else{
			$src_vhosts .= '<VirtualHost '.$tpl_vars['domain'].':80>'."\n";
			$src_vhosts .= '	# Production ('.$tpl_vars['project_code'].')'."\n";
			$src_vhosts .= '	ServerName '.$tpl_vars['domain'].''."\n";
			$src_vhosts .= '	DocumentRoot '.$tpl_vars['document_root']."\n";
			$src_vhosts .= '</VirtualHost>'."\n";
		}

		$this->put_tmp_contents( $src_vhosts );

		// Preview dirs
		$src_vhosts = '';
		$src_vhosts .= "\n";
		$src_vhosts .= '# Previews'."\n";
		$this->put_tmp_contents( $src_vhosts );

		foreach($this->list_preview_dirs[$project->project_code] as $branch_name){

			$tpl_vars = [
				'domain' => $project->project_code.'---'.$branch_name.'.'.env('BD_PREVIEW_DOMAIN'),
				'project_code' => $project->project_code,
				'document_root' => $this->fs->normalize_path($this->fs->get_realpath( env('BD_DATA_DIR').'/repositories/'.$project->project_code.'---'.$branch_name.'/'.$relpath_docroot_preview )),
				'branch_name' => $branch_name,
				'path_htpasswd' => false,
			];
			$src_vhosts = '';
			if( is_file( $realpath_template_root_dir.'preview.twig' ) ){
				$template = $twig->load('preview.twig');
				$src_vhosts .= $template->render($tpl_vars);
			}else{
				$src_vhosts .= '<VirtualHost '.$tpl_vars['domain'].':80>'."\n";
				$src_vhosts .= '	# Preview '.$tpl_vars['branch_name'].' ('.$tpl_vars['project_code'].')'."\n";
				$src_vhosts .= '	ServerName '.$tpl_vars['domain'].''."\n";
				$src_vhosts .= '	DocumentRoot '.$tpl_vars['document_root']."\n";
				$src_vhosts .= '</VirtualHost>'."\n";
			}

			$this->put_tmp_contents( $src_vhosts );
		}

		// Staging dirs
		$src_vhosts = '';
		$src_vhosts .= "\n";
		$src_vhosts .= '# Stagings'."\n";
		$this->put_tmp_contents( $src_vhosts );

		for( $i = 0; $i < 10; $i ++ ){

			$tpl_vars = [
				'domain' => $project->project_code.'---stg'.($i+1).'.'.env('BD_PLUM_STAGING_DOMAIN'),
				'project_code' => $project->project_code,
				'document_root' => $this->fs->normalize_path($this->fs->get_realpath( env('BD_DATA_DIR').'/stagings/'.$project->project_code.'---stg'.($i+1).'/'.$relpath_docroot_dist )),
				'staging_index' => $i+1,
				'path_htpasswd' => false,
			];
			if( $this->fs->is_file($this->fs->get_realpath( env('BD_DATA_DIR').'/projects/'.$project->project_code.'/plum_data_dir/htpasswds/stg'.($i).'.htpasswd' )) ){
				$tpl_vars['path_htpasswd'] = $this->fs->get_realpath( env('BD_DATA_DIR').'/projects/'.$project->project_code.'/plum_data_dir/htpasswds/stg'.($i).'.htpasswd' );
			}

			$src_vhosts = '';
			if( is_file( $realpath_template_root_dir.'staging.twig' ) ){
				$template = $twig->load('staging.twig');
				$src_vhosts .= $template->render($tpl_vars);
			}else{
				$src_vhosts .= '<VirtualHost '.$tpl_vars['domain'].':80>'."\n";
				$src_vhosts .= '	# Staging '.$tpl_vars['staging_index'].' ('.$tpl_vars['project_code'].')'."\n";
				$src_vhosts .= '	ServerName '.$tpl_vars['domain'].''."\n";
				$src_vhosts .= '	DocumentRoot '.$tpl_vars['document_root'].''."\n";
				if( $tpl_vars['path_htpasswd'] ){
					$src_vhosts .= '	Require valid-user'."\n";
					$src_vhosts .= '	AuthType Basic'."\n";
					$src_vhosts .= '	AuthName "Please enter your ID and password"'."\n";
					$src_vhosts .= '	AuthUserFile '.$tpl_vars['path_htpasswd']."\n";
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
		$command = env('BD_COMMAND_RELOAD_WEBSERVER_CONFIG');
		if( !$command ){
			$this->line( 'env "BD_COMMAND_RELOAD_WEBSERVER_CONFIG" is not set.' );
			return false;
		}

		$result = exec( $command );

		return true;
	}

	/**
	 * 一時ファイルにテキストを出力
	 */
	private function put_tmp_contents($src){
		file_put_contents( $this->realpath_vhosts_dir.'vhosts.conf.tmp', $src, FILE_APPEND );
		return true;
	}
}
