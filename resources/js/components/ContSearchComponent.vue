<template>
	<div>
		<form v-on:submit.prevent="contentsSearch">
			<div class="input-group" data-original-title="" title="">
				<input v-model="str" type="text" name="title" placeholder="Search...">
				<span class="input-group-btn" data-original-title="" title="">
					<button class="px2-btn px2-btn--primary" v-on:click="contentsSearch">検索</button>
				</span>
			</div>
		</form>
		<div class="btn-group btn-group-justified" data-toggle="buttons" data-original-title="" title="">
			<label class="btn px2-btn active" data-original-title="" title="" v-on:click="changeTitleClass">
				<input type="radio" name="list-label" value="title" checked="checked" data-original-title="" title="">title
			</label>
			<label class="btn px2-btn" data-original-title="" title="" v-on:click="changePathClass">
				<input type="radio" name="list-label" value="path" data-original-title="" title="">path
			</label>
		</div>
		<div id="cont_sitemap_search_title" class="cont_sitemap_search" v-bind:class="[isTitle === true ? 'show' : 'hidden']" data-original-title="" title="" style="">
			<ul v-if="results.length" class="listview">
				<li v-for="result in results">
					<!-- idが空だった場合＝トップページ -->
					<a v-if="result.id === ''" v-bind:href="'/pages/'+projectName+'/'+branchName+'/index.html?page_path='+result.path+'&page_id='+result.id" style="padding-left: 1em; font-size: 12px;" v-bind:class="{current: result.id === pageId}">{{ result.title }}</a>
					<!-- ロジカルパスが空だった場合＝カテゴリトップ -->
					<a v-else-if="result.logical_path === ''" v-bind:href="'/pages/'+projectName+'/'+branchName+'/index.html?page_path='+result.path+'&page_id='+result.id" style="padding-left: 2em; font-size: 12px;" v-bind:class="{current: result.id === pageId}">{{ result.title }}</a>
					<!-- その他ページ -->
					<a v-else v-bind:href="'/pages/'+projectName+'/'+branchName+'/index.html?page_path='+result.path+'&page_id='+result.id" style="font-size: 12px;" v-bind:class="{current: result.id === pageId}" v-bind:style="{paddingLeft: (result.logical_path.split(/>/).length+1)*1.3+'em'}">{{ result.title }}</a>
				</li>
			</ul>
		</div>
		<div id="cont_sitemap_search_path" class="cont_sitemap_search" v-bind:class="[isPath === true ? 'show' : 'hidden']" data-original-title="" title="" style="">
			<ul v-if="results.length" class="listview">
				<li v-for="result in results">
					<a v-bind:href="'/pages/'+projectName+'/'+branchName+'/index.html?page_path='+result.path+'&page_id='+result.id" style="padding-left: 1em; font-size: 12px;" v-bind:class="{current: result.id === pageId}">{{ result.path }}</a>
				</li>
			</ul>
		</div>
	</div>
</template>

<script>
export default {
	// view側から変数をプロパティとして渡す
	props: [
		"projectName",
		"branchName",
		"pageId"
	],
	// メソッドで使う&テンプレート内で使う変数を定義
	data () {
    	return {
    		str: '',
			results: [],
			isTitle: true,
			isPath: false
		}
	},
	// (読み込み時に)実行するメソッド
    methods: {
        contentsSearch() {
			var data = {
                'str': this.str
            }
			if(data.str === '') {
				data.str = '/';
			}
            axios.post('/pages/'+this.projectName+'/'+this.branchName+'/searchAjax',data).then(res => {
					this.results = res.data.info;
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
