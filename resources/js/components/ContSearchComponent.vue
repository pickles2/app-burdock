<template>
	<div>
		<form v-on:submit.prevent="contentsSearch">
			<div class="input-group">
				<input v-model="str" type="text" name="title" class="form-control" placeholder="Search...">
				<span class="input-group-btn">
					<button class="px2-btn px2-btn--primary">検索</button>
				</span>
			</div>
		</form>
		<div class="btn-group btn-group-justified" data-toggle="buttons">
			<label class="btn px2-btn active" v-on:click="changeTitleClass">
				<input type="radio" name="list-label" value="title" checked="checked">title
			</label>
			<label class="btn px2-btn" v-on:click="changePathClass">
				<input type="radio" name="list-label" value="path">path
			</label>
		</div>
		<div id="cont_sitemap_search_title" class="cont_sitemap_search" v-bind:style="[isTitle === true && isResult === true ? {'display':'block'} : {'display':'none'}]">
			<ul v-if="results.length" class="listview">
				<li v-for="result in results">
					<!-- idが空だった場合＝トップページ -->
					<a v-if="result.id === ''" v-bind:href="'/contents/'+projectCode+'/'+branchName+'?page_path='+result.path+'&page_id='+result.id" style="padding-left: 1em; font-size: 12px;" v-bind:class="{current: result.id === pageId}">{{ result.title }}</a>
					<!-- ロジカルパスが空だった場合＝カテゴリトップ -->
					<a v-else-if="result.logical_path === ''" v-bind:href="'/contents/'+projectCode+'/'+branchName+'?page_path='+result.path+'&page_id='+result.id" style="padding-left: 2em; font-size: 12px;" v-bind:class="{current: result.id === pageId}">{{ result.title }}</a>
					<!-- その他ページ -->
					<a v-else v-bind:href="'/contents/'+projectCode+'/'+branchName+'?page_path='+result.path+'&page_id='+result.id" style="font-size: 12px;" v-bind:class="{current: result.id === pageId}" v-bind:style="{paddingLeft: (result.logical_path.split(/>/).length+1)*1.3+'em'}">{{ result.title }}</a>
				</li>
			</ul>
			<p v-else-if="isResult === true && str.length >= 1" class="listview">該当するページがありません。</p>
		</div>
		<div id="cont_sitemap_search_path" class="cont_sitemap_search" v-bind:style="[isPath === true && isResult === true ? {'display':'block'} : {'display':'none'}]">
			<ul v-if="results.length" class="listview">
				<li v-for="result in results">
					<a v-bind:href="'/contents/'+projectCode+'/'+branchName+'?page_path='+result.path+'&page_id='+result.id" style="padding-left: 1em; font-size: 12px;" v-bind:class="{current: result.id === pageId}">{{ result.path }}</a>
				</li>
			</ul>
			<p v-else-if="isResult === true && str.length >= 1" class="listview">該当するページがありません。</p>
		</div>
	</div>
</template>

<script>
export default {
	// view側から変数をプロパティとして渡す
	props: [
		"projectCode",
		"branchName",
		"pageId"
	],
	// メソッドで使う&テンプレート内で使う変数を定義
	data () {
		return {
			str: '',
			results: [],
			isTitle: true,
			isPath: false,
			isResult: false
		}
	},
	// (読み込み時に)実行するメソッド
	methods: {
		contentsSearch() {
			var data = {
				'str': this.str
			}
			if(data.str === '') {
				data.str = '';
			}
			this.results = [];
			this.isResult = false;

			axios.post('/contents/'+this.projectCode+'/'+this.branchName+'/searchAjax',data).then(res => {
				if( data.str.length ){
					$('.cont_workspace_container').hide();
					this.results = res.data.info;
					this.isResult = true;
				}else{
					$('.cont_workspace_container').show();
				}
				$(window).resize();
			})
		},
		changeTitleClass() {
			this.isTitle = true;
			this.isPath = false;
		},
		changePathClass() {
			this.isTitle = false;
			this.isPath = true;
		}
	}
}
</script>
