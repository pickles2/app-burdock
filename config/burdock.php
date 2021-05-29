<?php

return [


    /*
    |--------------------------------------------------------------------------
    | アプリケーションの著作権者名
    |--------------------------------------------------------------------------
    |
    */
    'app_copyright' => env('BD_APP_COPYRIGHT', 'Pickles Project'),


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
    | SSLが有効な環境である場合は、ポート番号 443 を設定してください。 (例: preview.example.com:443)
    | ワイルドカード `*` を、サブドメイン名に置き換えて使用されます。 (例: *.preview.example.com → proj-code---master.preview.example.com)
    | ワイルドーカードを含まない場合は、この前にサブドメインを1つ追加して使用されます。 (例: preview.example.com → proj-code---master.preview.example.com)
    |
    */
    'preview_domain' => env('BD_PREVIEW_DOMAIN', ''),


    /*
    |--------------------------------------------------------------------------
    | ステージング環境のドメイン名
    |--------------------------------------------------------------------------
    |
    | ポート番号を含む場合は、合わせて設定してください。 (例: staging.example.com:8080)
    | SSLが有効な環境である場合は、ポート番号 443 を設定してください。 (例: staging.example.com:443)
    | ワイルドカード `*` を、サブドメイン名に置き換えて使用されます。 (例: *.staging.example.com → proj-code---stg1.staging.example.com)
    | ワイルドーカードを含まない場合は、この前にサブドメインを1つ追加して使用されます。 (例: staging.example.com → proj-code---stg1.staging.example.com)
    |
    */
    'staging_domain' => env('BD_STAGING_DOMAIN', ''),


    /*
    |--------------------------------------------------------------------------
    | htpasswd のパスワードハッシュ生成アルゴリズム名
    |--------------------------------------------------------------------------
    |
    | 次のいずれかの値を設定します。
    | bcrypt, md5, sha1, crypt, plain
    |
    */
    'htpasswd_hash_algorithm' => env('BD_HTPASSWD_HASH_ALGORITHM', 'crypt'),


    /*
    |--------------------------------------------------------------------------
    | ユーザー登録を許可するメールアドレスのパターン
    |--------------------------------------------------------------------------
    |
    | アスタリスクでワイルドカードを表現できます。
    | カンマ区切りで複数のパターンを指定できます。
    | 例: *@example.com
    | 例: *@example.com,*@example2.com,*@example3.com
    |
    */
    'user_email_allowlist' => env('BD_USER_EMAIL_ALLOWLIST', ''),

];
