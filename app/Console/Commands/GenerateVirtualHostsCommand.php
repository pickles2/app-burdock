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
			$this->line( '' );
			$this->info( $project->project_name );
			$this->line( ' ('.$project->id.' - '.$project->project_code.')' );
			$this->line( ' '.$count.'/'.$count );

			$result = $this->execute_project_task( $project );

			$this->line( ' ----- '.$result['message'] );
			$this->line( '' );
			sleep(1);
		}

		if( !is_file($this->realpath_vhosts_dir.'vhosts.conf') || md5_file($this->realpath_vhosts_dir.'vhosts.conf') != md5_file($this->realpath_vhosts_dir.'vhosts.conf.tmp') ){
			copy( $this->realpath_vhosts_dir.'vhosts.conf.tmp', $this->realpath_vhosts_dir.'vhosts.conf' );
		}
		$this->fs->rm( $this->realpath_vhosts_dir.'vhosts.conf.tmp' );

		$this->line(' finished!');
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
		$default_branch_name = 'master';
		if( strlen($project->git_url) ){
			$default_branch_name = \get_git_remote_default_branch_name($project->git_url);
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
		var_dump($px2packages);


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


		// config header
		$src_vhosts = '';
		$src_vhosts .= "\n\n";
		$src_vhosts .= '## --------------------------------------'."\n";
		$src_vhosts .= '## '.$project->project_name."\n";
		$src_vhosts .= '## '.$project->project_code.' - '.$project->id."\n";
		$src_vhosts .= "\n";
		file_put_contents( $this->realpath_vhosts_dir.'vhosts.conf.tmp', $src_vhosts, FILE_APPEND );

		// Production
		$src_vhosts = '';
		$src_vhosts .= "\n";
		$src_vhosts .= '# Production'."\n";
		file_put_contents( $this->realpath_vhosts_dir.'vhosts.conf.tmp', $src_vhosts, FILE_APPEND );

		$src_vhosts = '';
		if( !strlen($domain) ){
			$src_vhosts .= '# NO DOMAIN'."\n";
		}else{
			$src_vhosts .= '<VirtualHost '.$domain.':443>'."\n";
			$src_vhosts .= '	# Production ('.$project->project_code.')'."\n";
			$src_vhosts .= '	ServerName '.$domain.''."\n";
			$src_vhosts .= '	VirtualDocumentRoot '.env('BD_DATA_DIR').'/projects/'.$project->project_code.'/indigo/production/dist'."\n";
			$src_vhosts .= '	SSLEngine on'."\n";
			$src_vhosts .= '	SSLProtocol all -SSLv2 -SSLv3'."\n";
			$src_vhosts .= '	SSLCipherSuite ALL:!ADH:!EXPORT:!SSLv2:RC4+RSA:+HIGH:+MEDIUM:+LOW'."\n";
			$src_vhosts .= '	SSLCertificateFile "/path/to/localhost.crt"'."\n";
			$src_vhosts .= '	SSLCertificateKeyFile "/path/to/localhost.key"'."\n";
			$src_vhosts .= '</VirtualHost>'."\n";
		}

		file_put_contents( $this->realpath_vhosts_dir.'vhosts.conf.tmp', $src_vhosts, FILE_APPEND );

		// Preview dirs
		$src_vhosts = '';
		$src_vhosts .= "\n";
		$src_vhosts .= '# Previews'."\n";
		file_put_contents( $this->realpath_vhosts_dir.'vhosts.conf.tmp', $src_vhosts, FILE_APPEND );
		foreach($this->list_preview_dirs[$project->project_code] as $branch_name){

			$src_vhosts = '';
			$src_vhosts .= '<VirtualHost '.$project->project_code.'---'.$branch_name.'.'.env('BD_PREVIEW_DOMAIN').':443>'."\n";
			$src_vhosts .= '	# Preview ('.$project->project_code.')'."\n";
			$src_vhosts .= '	ServerName '.$project->project_code.'---'.$branch_name.'.'.env('BD_PREVIEW_DOMAIN').''."\n";
			$src_vhosts .= '	VirtualDocumentRoot '.env('BD_DATA_DIR').'/repositories/'.$project->project_code.'---'.$branch_name.'/htdocs'."\n";
			$src_vhosts .= '	SSLEngine on'."\n";
			$src_vhosts .= '	SSLProtocol all -SSLv2 -SSLv3'."\n";
			$src_vhosts .= '	SSLCipherSuite ALL:!ADH:!EXPORT:!SSLv2:RC4+RSA:+HIGH:+MEDIUM:+LOW'."\n";
			$src_vhosts .= '	SSLCertificateFile "/path/to/localhost.crt"'."\n";
			$src_vhosts .= '	SSLCertificateKeyFile "/path/to/localhost.key"'."\n";
			$src_vhosts .= '</VirtualHost>'."\n";

			file_put_contents( $this->realpath_vhosts_dir.'vhosts.conf.tmp', $src_vhosts, FILE_APPEND );
		}

		// Staging dirs
		$src_vhosts = '';
		$src_vhosts .= "\n";
		$src_vhosts .= '# Stagings'."\n";
		file_put_contents( $this->realpath_vhosts_dir.'vhosts.conf.tmp', $src_vhosts, FILE_APPEND );
		for( $i = 0; $i < 10; $i ++ ){

			$src_vhosts = '';
			$src_vhosts .= '<VirtualHost '.$project->project_code.'---stg'.($i+1).'.'.env('BD_PLUM_STAGING_DOMAIN').':443>'."\n";
			$src_vhosts .= '	# Staging '.($i+1).' ('.$project->project_code.')'."\n";
			$src_vhosts .= '	ServerName '.$project->project_code.'---stg'.($i+1).'.'.env('BD_PLUM_STAGING_DOMAIN').''."\n";
			$src_vhosts .= '	VirtualDocumentRoot '.env('BD_DATA_DIR').'/stagings/'.$project->project_code.'---stg'.($i+1).'/dist'."\n";
			$src_vhosts .= '	SSLEngine on'."\n";
			$src_vhosts .= '	SSLProtocol all -SSLv2 -SSLv3'."\n";
			$src_vhosts .= '	SSLCipherSuite ALL:!ADH:!EXPORT:!SSLv2:RC4+RSA:+HIGH:+MEDIUM:+LOW'."\n";
			$src_vhosts .= '	SSLCertificateFile "/path/to/localhost.crt"'."\n";
			$src_vhosts .= '	SSLCertificateKeyFile "/path/to/localhost.key"'."\n";
			$src_vhosts .= '</VirtualHost>'."\n";

			file_put_contents( $this->realpath_vhosts_dir.'vhosts.conf.tmp', $src_vhosts, FILE_APPEND );
		}

		return array(
			'result' => true,
			'message' => 'OK',
		);
	}
}
