/*
 |--------------------------------------------------------------------------
 | Welcome to Laravel Mix!
 |--------------------------------------------------------------------------
 |
 | Laravel Mix provides a clean, fluent API for defining basic webpack
 | build steps for your Laravel application. Mix supports a variety
 | of common CSS and JavaScript pre-processors out of the box.
 |
 | https://laravel-mix.com
 */

let mix = require('laravel-mix');
const CONFIG = require('./config.local.js');

// ----------------------------------------------------------------------------
// Mix settings
// ----------------------------------------------------------------------------

mix.setPublicPath('assets').disableNotifications();

// ----------------------------------------------------------------------------
// Mix tasks
// ----------------------------------------------------------------------------

mix.js('src/js/myapps.js', 'js');
mix.sass('src/scss/myapps.scss', 'css');
mix.copy('src/images', 'assets/images');
mix.browserSync(CONFIG.browsersync);
