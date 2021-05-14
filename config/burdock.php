<?php

return [


    /*
    |--------------------------------------------------------------------------
    | ウェブサーバーツール名
    |--------------------------------------------------------------------------
    |
    | `apache` または `nginx` をセットしてください。
    |
    */
    'webserver' => env('BD_WEBSERVER', null),


    /*
    |--------------------------------------------------------------------------
    | コマンドのパス
    |--------------------------------------------------------------------------
    |
    */
    'command_path' => [
        'php' => env('BD_COMMAND_PHP', 'php'),
        'php_ini' => env('BD_COMMAND_PHP_INI', null),
        'php_extension_dir' => env('BD_COMMAND_PHP_EXTENSION_DIR', null),
        'git' => env('BD_COMMAND_GIT', 'git'),
    ],


    /*
    |--------------------------------------------------------------------------
    | ウェブサーバーの設定を再読み込みするコマンド
    |--------------------------------------------------------------------------
    |
    | バーチャルホスト設定を更新したあとに実行されます。
    | 設定を省略した場合、再読み込みは実行されません。
    |
    */
    'command_reload_webserver_config' => env('BD_COMMAND_RELOAD_WEBSERVER_CONFIG', null),


    /*
    |--------------------------------------------------------------------------
    | データディレクトリのパス
    |--------------------------------------------------------------------------
    |
    */
    'data_dir' => env('BD_DATA_DIR', __DIR__.'/../bd_data/'),

    /*
    |--------------------------------------------------------------------------
    | プレビュー環境のドメイン名
    |--------------------------------------------------------------------------
    |
    | ポート番号を含む場合は、合わせて設定してください。 (例: preview.example.com:8080)
    | 実際は、この前にサブドメインを1つ追加して使用されます。 (例: proj-code---master.preview.example.com)
    |
    */
    'preview_domain' => env('BD_PREVIEW_DOMAIN', 'preview.example.com'),


    /*
    |--------------------------------------------------------------------------
    | ステージング環境のドメイン名
    |--------------------------------------------------------------------------
    |
    | ポート番号を含む場合は、合わせて設定してください。 (例: staging.example.com:8080)
    | 実際は、この前にサブドメインを1つ追加して使用されます。 (例: proj-code---stg1.staging.example.com)
    |
    */
    'staging_domain' => env('BD_STAGING_DOMAIN', 'staging.example.com'),

];
