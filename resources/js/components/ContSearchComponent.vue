<template>
	<div>
		<div class="input-group" data-original-title="" title="">
			<input v-model="str" type="text" name="title" placeholder="Search...">
			<span class="input-group-btn" data-original-title="" title="">
				<button class="px2-btn px2-btn--primary" v-on:click="contentsSearch">検索</button>
			</span>
			<!-- <div v-for="result in results">
				<span v-html="result.title"></span>
			</div> -->
		</div>
		<div class="cont_sitemap_search" data-original-title="" title="" style="display: block;">
			<ul v-if="results.length" class="listview">
				<li v-for="result in results">
					<a v-bind:href="result.path" style="padding-left: 1em; font-size: 12px;">{{ result.title }}</a>
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
		"branchName"
	],
	// メソッドで使う&テンプレート内で使う変数を定義
	data () {
    	return {
    		str: '',
			results: []
		}
	},
	// (読み込み時に)実行するメソッド
    methods: {
        contentsSearch(){
			var data = {
                'str': this.str
            };
            axios.post('/pages/'+this.projectName+'/'+this.branchName+'/searchAjax',data).then(res => {
					this.results = res.data.info;
            });
		}
    }
}
</script>
