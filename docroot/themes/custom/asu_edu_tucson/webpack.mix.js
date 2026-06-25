/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your application. See https://github.com/JeffreyWay/laravel-mix.
 |
 */
const proxy = 'http://my.ddev.site';
const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Configuration
 |--------------------------------------------------------------------------
 */
mix
  .setPublicPath('assets')
  .disableNotifications()
  .options({
    processCssUrls: false
  })
  .webpackConfig({
    devtool: 'source-map'
  })
  .sourceMaps(false,'eval-source-map');

/*
 |--------------------------------------------------------------------------
 | Browsersync
 |--------------------------------------------------------------------------
 */
mix.browserSync({
  proxy: proxy,
  files: ['assets/js/**/*.js', 'assets/css/**/*.css'],
  stream: true,
});

/*
 |--------------------------------------------------------------------------
 | SASS
 |--------------------------------------------------------------------------
 */
mix.sass('src/sass/asu_edu_tucson.style.scss', 'css');

/*
 |--------------------------------------------------------------------------
 | JS
 |--------------------------------------------------------------------------
 */
// TODO: Output should be optimized
mix.js('src/js/asu_edu_tucson.script.js', 'js')
  .sourceMaps(false, 'source-map')