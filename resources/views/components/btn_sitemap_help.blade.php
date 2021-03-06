@php
    $id_attr = 'modal-sitemap_help' . $controller;
@endphp

{{-- 削除ボタン --}}
<button class="btn px2-btn" data-toggle="modal" data-target="#{{ $id_attr }}">ヘルプ</button>

{{-- モーダルウィンドウ --}}
<div class="modal fade" id="{{ $id_attr }}" role="dialog" aria-labelledby="{{ $id_attr }}-label" aria-hidden="true">
	<div class="px2-modal__dialog">
		<div class="px2-modal__header">
			<div class="px2-modal__title">サイトマップの編集方法</div>
		</div>
		<div class="px2-modal__body" style="height: 810px;">
			<div class="px2-modal__body-inner">
				<div>
					<div class="px2-document">
						<h2>CSVの形式について</h2>
						<ul>
							<li>CSVファイルはUTF-8で保存してください。</li>
							<li>1行目は定義行として、2行目以降にページデータを記述してください。</li>
							<li>定義行は、<code>* 定義名</code> のように、先頭にアスタリスクを記述します。</li>
							<li><code>* path</code>、<code>* title</code> は必須です。必ず定義に加えてください。</li>
						</ul>

						<h3>規定の定義</h3>
						<div class="px2-p">
							<table class="px2-table">
								<thead>
									<tr>
										<th>列</th>
										<th>キー</th>
										<th>意味</th>
									</tr>
								</thead>
								<tbody>
									<tr><th>A</th><td class="selectable">path</td><td>ページのパス</td></tr>
									<tr><th>B</th><td class="selectable">content</td><td>コンテンツファイルの格納先</td></tr>
									<tr><th>C</th><td class="selectable">id</td><td>ページID</td></tr>
									<tr><th>D</th><td class="selectable">title</td><td>ページタイトル</td></tr>
									<tr><th>E</th><td class="selectable">title_breadcrumb</td><td>ページタイトル(パン屑表示用)</td></tr>
									<tr><th>F</th><td class="selectable">title_h1</td><td>ページタイトル(H1表示用)</td></tr>
									<tr><th>G</th><td class="selectable">title_label</td><td>ページタイトル(リンク表示用)</td></tr>
									<tr><th>H</th><td class="selectable">title_full</td><td>ページタイトル(タイトルタグ用)</td></tr>
									<tr><th>I</th><td class="selectable">logical_path</td><td>論理構造上のパス</td></tr>
									<tr><th>J</th><td class="selectable">list_flg</td><td>一覧表示フラグ</td></tr>
									<tr><th>K</th><td class="selectable">layout</td><td>レイアウト</td></tr>
									<tr><th>L</th><td class="selectable">orderby</td><td>表示順</td></tr>
									<tr><th>M</th><td class="selectable">keywords</td><td>metaキーワード</td></tr>
									<tr><th>N</th><td class="selectable">description</td><td>metaディスクリプション</td></tr>
									<tr><th>O</th><td class="selectable">category_top_flg</td><td>カテゴリトップフラグ</td></tr>
									<tr><th>P</th><td class="selectable">role</td><td>ロール</td></tr>
									<tr><th>Q</th><td class="selectable">proc_type</td><td>コンテンツの処理方法</td></tr>
								</tbody>
							</table>
						</div><!-- /.unit -->

						<h2>その他のヒント</h2>
						<ul>
							<li>定義列は、任意に並べ替えることができます。</li>
							<li>定義は任意の名称で追加することができ、コンテンツやテーマから簡単に参照できます。例えば、 <code>* custom_col</code> と定義した列の値は、<code class="selectable">$px-&gt;site()-&gt;get_page_info( 'page_id', 'custom_col' )</code> や <code class="selectable">$px-&gt;site()-&gt;get_current_page_info( 'custom_col' )</code> で取得することができます。</li>
							<li>サイトマップCSVを、グラフィカルなExcelの形式(*.xlsx)で編集することができます。<a href="https://github.com/pickles2/px2-sitemapexcel" onclick="px.utils.openURL( this.href ); return false;">pickles2/px2-sitemapexcel プラグイン</a>をプロジェクトに設定してください。</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="px2-modal__footer">
			<ul>
				<li><button type="button" class="px2-btn px2-btn--primary" data-dismiss="modal">OK</button></li>
			</ul>
		</div>
	</div>
</div>
