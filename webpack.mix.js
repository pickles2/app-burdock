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
	// --------------------------------------
	// Project Common Scripts
	.js('resources/js/app.js', 'public/js')
	.sass('resources/sass/app.scss', 'public/css')

	// --------------------------------------
	// Project Local Resources

	// Theme
	.js('resources/cont/theme/script.js', 'public/cont/theme/script.js')
	.sass('resources/cont/theme/style.scss', 'public/cont/theme/style.css')

	// publish
	.sass('resources/cont/publish/style.scss', 'public/cont/publish/style.css')

	// Files and Folders
	.js('resources/cont/files_and_folders/script.js', 'public/cont/files_and_folders/script.js')
	.sass('resources/cont/files_and_folders/style.scss', 'public/cont/files_and_folders/style.css')

	// Custom Console Extensions
	.js('resources/cont/custom_console_extensions/script.js', 'public/cont/custom_console_extensions/script.js')
	.sass('resources/cont/custom_console_extensions/style.scss', 'public/cont/custom_console_extensions/style.css')

	// --------------------------------------
	// Static Frontend Libraries
	.copyDirectory('vendor/pickles2/px2style/dist', 'public/common/px2style/dist')
	.copyDirectory('vendor/pickles2/lib-plum/dist', 'public/common/lib-plum/dist')
	.copyDirectory('vendor/pickles2/lib-indigo/res', 'public/common/lib-indigo/res')
	.copyDirectory('vendor/tomk79/remote-finder/dist', 'public/common/remote-finder/dist')
	.copyDirectory('submodules/gitui79.js/dist', 'public/common/gitui79/dist')
	.copyDirectory('submodules/node-git-parser/dist', 'public/common/gitparse79/dist')
	.copyDirectory('submodules/common-file-editor/dist', 'public/common/common-file-editor/dist')
	.copyDirectory('node_modules/@tomk79/pickles2-code-search/dist', 'public/common/pickles2-code-search/dist')
;
