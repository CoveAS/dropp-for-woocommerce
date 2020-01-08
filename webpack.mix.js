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

mix.js('resources/js/dropp-admin.js', 'assets/js')
	.js('resources/js/dropp.js', 'assets/js')
    .sass('resources/scss/dropp.scss', 'assets/css')
    .sass('resources/scss/dropp-admin.scss', 'assets/css');
