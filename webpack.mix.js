const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
	// Project Common Scripts
	.js('resources/js/app.js', 'public/js')
	.sass('resources/sass/app.scss', 'public/css')

	// Project Local Resources
	.sass('resources/cont/publish/style.scss', 'public/cont/publish/style.css')

	// Static Frontend Libraries
	.copyDirectory('vendor/pickles2/px2style/dist', 'public/common/px2style/dist')
	.copyDirectory('vendor/pickles2/lib-plum/res', 'public/common/lib-plum/res')
	.copyDirectory('vendor/pickles2/lib-indigo/res', 'public/common/lib-indigo/res')
	.copyDirectory('vendor/tomk79/remote-finder/dist', 'public/common/remote-finder/dist')
	.copyDirectory('submodules/gitui79.js/dist', 'public/common/gitui79/dist')
	.copyDirectory('submodules/node-git-parser/dist', 'public/common/gitparse79/dist')
;
