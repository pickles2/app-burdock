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

mix.js('resources/js/app.js', 'public/js')
	.sass('resources/sass/app.scss', 'public/css')
	.copyDirectory('submodules/px2style/dist', 'public/common/px2style/dist')
	.copyDirectory('vendor/pickles2/lib-plum/res', 'public/common/lib-plum/res')
	.copyDirectory('vendor/pickles2/lib-indigo/res', 'public/common/lib-indigo/res')
;
