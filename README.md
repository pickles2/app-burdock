# app-burdock
PHPサーバー+ブラウザ上で動作する Pickles 2 のGUIツール。

Pickles Framework 2 に特化した CMSライクなテキストエディタ "Pickles 2" のウェブ版です。

## インストール

### 1. app-burdock の依存ライブラリをインストールする

```
$ git clone https://github.com/pickles2/app-burdock.git
$ cd app-burdock
$ composer install
$ npm install
```

### 2. `.env` ファイルを作成、設定する

サンプルの設定ファイル `.env.example` から `.env` という名前で複製します。

```
$ cp .env.example .env
```

この設定ファイルに、適宜必要な設定を更新してください。

```
$ vi .env
```

#### アプリケーションの基本情報の設定

必要に応じて設定を変更してください。

```
APP_NAME=Burdock
APP_ENV=local
APP_KEY=base64:NOwK3+2AQLj41zWorz0d1JXe7cKSGRTKMtJs9tSm4/g=
APP_DEBUG=true
```

アプリケーションキーは次のコマンドで再生成してください。

```
$ php artisan key:generate
Application key set successfully.
```

#### アプリサーバーとプレビューサーバーのURLを設定

アプリサーバーとプレビューサーバーは異なるURLを設定してください。

```
APP_URL=https://example.com
BD_PREVIEW_DOMAIN=preview.example.com
BD_PLUM_STAGING_DOMAIN=staging.example.com
```

#### データベース接続設定

データベースの接続先情報を更新します。
次の例は、SQLite を使用する設定例です。

```
DB_CONNECTION=sqlite
DB_HOST=
DB_PORT=
DB_DATABASE=../bd_data/database.sqlite
DB_USERNAME=
DB_PASSWORD=
```

### プロジェクトを作成するディレクトリパスの設定

プロジェクトは任意のディレクトリにインストールすることができます。
次の例は、Users > hoge > fuga 配下にある path_to_project_dir フォルダにプロジェクトを作成したい場合の設定です。

```
BD_DATA_DIR=/Users/hoge/fuga/path_to_project_dir
```


#### その他

メール送信サーバーなどの設定項目があります。
必要に応じて修正してください。

### 4. データベースを初期化する

```
$ php artisan migrate --seed
```

#### データベースシステム に sqlite を利用する場合の注意点

SQLite を使用する場合は、先に 空白のデータベースファイルを作成しておく必要があります。

また、データベースのパスを相対パスで指定したい場合、 migrate コマンド実行時に注意が必要です。
実際のアプリケーションは相対パスの起点が `public/` で実行されます。 migrate コマンドは、これと同じカレントディレクトリで実行される必要があります。

`DB_DATABASE` の値を `public/` 起点の相対パスに設定して、 publicディレクトリ で migrate を実行します。

```
$ cd public/;
$ touch ../bd_data/database.sqlite;
$ php ../artisan migrate --seed;
$ cd ..;
```

### サーバーを起動して確認する

以上でセットアップは完了です。
次のコマンドを実行してサーバーを起動し、確認してみることができます。

```
$ php artisan serve
```

正常に起動したら、 `http://127.0.0.1:8000` でアクセスできます。
ブラウザではじめの画面が表示されたら完了です。


## cron コマンド設定

```
* * * * * apache cd /path/to/burdock && php artisan schedule:run >> /dev/null 2>&1
```


## Redis で WebSocket 環境をセットアップする

WebSocket環境を利用すると、サーバーとの間で非同期に対話する機能を追加できます。 特にパブリッシュなどの時間がかかる処理の利便性が向上します。

このセットアップはオプションです。

Redis と Laravel Echo Server がインストールされている必要があります。

```
$ brew install redis
$ npm install -g laravel-echo-server
```

Laravel Echo Server をセットアップします。
次のコマンドで質問に答えていくと、 `laravel-echo-server.json` が作成されます。

```
$ laravel-echo-server init
```

`.env` ファイルを開き、 `BROADCAST_DRIVER` を `redis` に設定します。

```
BROADCAST_DRIVER=redis
```


次のコマンドで Redis と Laravel Echo Server を起動します。

```
$ redis-server
$ laravel-echo-server start
```


## artisan コマンド

### indigo:cron

配信予約ツール Indigo の配信処理をキックします。

### bd:deploy-script

Indigo の配信処理の後処理をキックします。

### bd:generate_vhosts

Apache 用の Virtual Hosts の設定ファイルを出力します。



## 更新履歴 - Change log

### Burdock v0.0.1 (リリース日未定)

- Initial Release.


## ライセンス - License

MIT License

## 開発者向け情報 - for Developer

### データベースへのダミーデータシーディング

```
$ php artisan db:seed --class=DummyDataSeeder
```
