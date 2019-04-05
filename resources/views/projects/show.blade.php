@php
    $title = $project->project_name;
@endphp
@extends('layouts.px2_project')
@section('content')
<div class="container">
    <h1 id="project-title" style="margin-bottom: 50px;">Project "{{ $title }}"</h1>
    <div class="contents">
        <div class="cont_info"></div>
        <div class="cont_maintask_ui">
            <h2>基本的な手順</h2>
            <div>
                {{ Auth::user()->project_name }}
            </div>
            <div class="row" style="margin-bottom: 100px;">
                <div class="col-sm-3">
                    <a href="{{ url('/sitemaps'.'/'.$project->project_name.'/'.$branch_name) }}" class="px2-btn cont_mainmenu">{{ __('Edit Sitemap')}}</a>
                </div>
                <div class="col-sm-3">
                    <a href="#" class="px2-btn cont_mainmenu">テーマを編集する</a>
                </div>
                <div class="col-sm-3">
                    <a href="{{ url('pages/'.$project->project_name.'/'.$branch_name.'/index.html?page_path='.'%2Findex.html')}}" class="px2-btn cont_mainmenu">{{ __('Edit Contents')}}</a>
                </div>
                <div class="col-sm-3">
                    <a href="{{ url('/publish'.'/'.$project->project_name.'/'.$branch_name) }}" class="px2-btn cont_mainmenu">{{ __('To Publish')}}</a>
                </div>
            </div><!-- / .row -->
            {{-- <ul class="px2-horizontal-list px2-horizontal-list--right">
                <li><a href="javascript:px.getCurrentProject().open();" class="px2-link px2-link--burette">フォルダを開く</a></li>
                <li><a href="javascript:px.openInBrowser();" class="px2-link px2-link--burette">ブラウザで開く</a></li>
                <li><a href="javascript:px.openInTextEditor( contApp.pj.get('path') );" class="px2-link px2-link--burette">外部テキストエディタで開く</a></li>
                <li><a href="javascript:px.openInGitClient( contApp.pj.get('path') );" class="px2-link px2-link--burette">外部Gitクライアントで開く</a></li>
                <li><a href="javascript:px.openInTerminal( contApp.pj.get('path') );" class="px2-link px2-link--burette">コマンドラインで開く</a></li>
            </ul>
            <ul class="px2-horizontal-list px2-horizontal-list--right">
                <li><a href="javascript:px.subapp('fncs/config/index.html');" class="px2-link px2-link--burette">config.php を編集する</a></li>
                <li><a href="javascript:px.subapp('fncs/composer/index.html');" class="px2-link px2-link--burette">composer を操作する</a></li>
            </ul> --}}
        </div>
        <div class="alert alert-info">Hint! : <span class="cont_hint">Burdock は Pickles2をベースにしたWebアプリケーションです。</span></div>
        <div class="row">
            <div class="col-sm-12">
                {{-- <h2>README</h2> --}}
                <div class="cont_readme_content selectable">
                    {{-- <h1 id="get-start-pickles-2-">Get start "Pickles 2" !</h1>
                    <p><a href="http://pickles2.pxt.jp/">Pickles 2</a> は、静的で大きなウェブサイトを効率よく構築できる オープンソースのHTML生成ツールです。</p>
                    <ul>
                        <li>サイトマップ(ページリスト)をCSV形式で管理し、グローバルナビゲーションの生成やカレント処理、パンくず生成、タイトルやメタタグの出力などを自動化します。</li>
                        <li>コンテンツ(ページ固有の内容部分)と、テーマ(ヘッダ、フッタ、ナビゲーションなどの共通部分)に分けてコーディングします。テーマはサイト全体を通して一元化された共通コードから自動生成します。</li>
                        <li>データベース不要、PHP5.4以上 が動くウェブサーバーに手軽に導入できます。</li>
                        <li>Markdown や SCSS などの文法を動的に導入できます。</li>
                        <li>簡単なコマンドで、スタティックなHTMLファイルを出力(パブリッシュ)できます。</li>
                        <li>Composer 導入により、機能の追加、拡張が手軽にできるようになりました。</li>
                    </ul>
                    <h2 id="-system-requirement">システム要件 - System Requirement</h2>
                    <ul>
                        <li>Linux系サーバ または Windowsサーバ</li>
                        <li>Apache1.3以降
                            <ul>
                                <li>mod_rewrite が利用可能であること</li>
                                <li>.htaccess が利用可能であること</li>
                            </ul>
                        </li>
                        <li>PHP5.4以上
                            <ul>
                                <li>mb_string が有効に設定されていること</li>
                                <li>safe_mode が無効に設定されていること</li>
                            </ul>
                        </li>
                    </ul> --}}
                    <h2 id="-license">ライセンス - License</h2>
                    <p>Copyright (c)2001-2018 Tomoya Koyanagi, and Pickles 2 Project<br>MIT License <a href="https://opensource.org/licenses/mit-license.php">https://opensource.org/licenses/mit-license.php</a></p>
                    <h2 id="-author">作者 - Author</h2>
                    <ul>
                        <li>Tomoya Koyanagi <a href="mailto:tomk79@gmail.com">tomk79@gmail.com</a></li>
                        <li>website: <a href="http://www.pxt.jp/">http://www.pxt.jp/</a></li>
                        <li>Twitter: @tomk79 <a href="http://twitter.com/tomk79/">http://twitter.com/tomk79/</a></li>
                    </ul>
                </div>
                <h2>Project Information</h2>
                <div class="px2-responsive">
                    <table class="px2-table" style="width:100%; table-layout: fixed;">
                        <colgroup><col width="30%"><col width="70%"></colgroup>
                        <tbody>
                            <tr>
                                <th>Project Name</th>
                                <td class="tpl_name selectable">{{ $bd_object->packages->package_list->projects[0]->name }}</td>
                            </tr>
                            <tr>
                                <th>Path</th>
                                <td class="tpl_path selectable">{{ $bd_object->realpath_docroot }}</td>
                            </tr>
                            <tr>
                                <th>Home Directory</th>
                                <td class="tpl_home_dir selectable">{{ $bd_object->packages->package_list->projects[0]->path_homedir }}</td>
                            </tr>
                            <tr>
                                <th>Entry Script</th>
                                <td class="tpl_entry_script selectable">{{ $bd_object->packages->package_list->projects[0]->path }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                {{-- <h3>Optional</h3>
                <div class="px2-responsive">
                    <table class="px2-table" style="width:100%; table-layout: fixed;">
                        <colgroup><col width="30%"><col width="70%"></colgroup>
                        <tbody>
                            <tr>
                                <th>External Preview Server Origin</th>
                                <td class="tpl_external_preview_server_origin selectable"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p><button class="px2-btn" onclick="contApp.editProject(); return false;">プロジェクト情報を編集する</button></p>
                <h2>Project Status</h2>
                <div class="px2-responsive">
                    <table class="px2-table tpl_status_table" style="width:100%; table-layout: fixed;">
                        <tr>
                            <th>api.available</th>
                            <td>true</td>
                        </tr>
                        <tr>
                            <th>api.version</th>
                            <td>2.0.39</td>
                        </tr>
                        <tr>
                            <th>api.is_sitemap_loaded</th>
                            <td>true</td>
                        </tr>
                        <tr>
                            <th>px2dthelper.available</th>
                            <td>true</td>
                        </tr>
                        <tr>
                            <th>px2dthelper.version</th>
                            <td>2.0.11</td>
                        </tr>
                        <tr>
                            <th>px2dthelper.is_sitemap_loaded</th>
                            <td>true</td>
                        </tr>
                        <tr>
                            <th>pathExists</th>
                            <td>true</td>
                        </tr>
                        <tr>
                            <th>pathContainsFileCount</th>
                            <td>12</td>
                        </tr>
                        <tr>
                            <th>entryScriptExists</th>
                            <td>true</td>
                        </tr>
                        <tr>
                            <th>homeDirExists</th>
                            <td>true</td>
                        </tr>
                        <tr>
                            <th>confFileExists</th>
                            <td>true</td>
                        </tr>
                        <tr>
                            <th>px2DTConfFileExists</th>
                            <td>true</td>
                        </tr>
                        <tr>
                            <th>composerJsonExists</th>
                            <td>true</td>
                        </tr>
                        <tr>
                            <th>vendorDirExists</th>
                            <td>true</td>
                        </tr>
                        <tr>
                            <th>isPxStandby</th>
                            <td>true</td>
                        </tr>
                        <tr>
                            <th>gitDirExists</th>
                            <td>false</td>
                        </tr>
                        <tr>
                            <th>guiEngineName</th>
                            <td>broccoli-html-editor</td>
                        </tr>
                    </table>
                </div> --}}
            </div>
        </div><!-- /.row -->
        {{-- <p><button class="px2-btn" onclick="px.deselectProject(); px.subapp(); return false;">ダッシュボードへ戻る</button></p>
        <p><button class="px2-btn" onclick="px.healthCheck(); return false;">診断ツール</button></p>
        <p><button class="px2-btn px2-btn--danger" onclick="if(confirm('このプロジェクトを削除してよろしいですか？')){px.deleteProject( px.getCurrentProject().projectId, function(){px.subapp();} );} return false;">このプロジェクトを削除</button></p> --}}
        <hr>
        <address class="center">(C)Pickles 2 Project.</address>
    </div>
</div>
@endsection
