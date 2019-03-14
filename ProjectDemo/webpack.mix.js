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
   .copyDirectory('resources/img','public/img')
   .styles(['node_modules/admin-lte/bower_components/bootstrap/dist/css/bootstrap.min.css',
            'node_modules/admin-lte/dist/css/AdminLTE.css',
            'node_modules/admin-lte/bower_components/font-awesome/css/font-awesome.min.css',
            'node_modules/admin-lte/bower_components/Ionicons/css/ionicons.min.css',
            'node_modules/admin-lte/dist/css/skins/skin-blue.min.css'
            ],'public/admin/lte/css/library.min.css')
    .scripts([
        'node_modules/admin-lte/bower_components/jquery/dist/jquery.min.js',
        'node_modules/admin-lte/bower_components/bootstrap/dist/js/bootstrap.min.js',
        'node_modules/admin-lte/dist/js/adminlte.min.js'
    ],'public/admin/lte/js/library.min.js')
