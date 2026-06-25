/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your application. See https://github.com/JeffreyWay/laravel-mix.
 |
 */
const proxy = 'leadership-academy-d9.lndo.site';
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
    processCssUrls: false,
  });

mix.webpackConfig({
  devtool: 'source-map',
})

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
mix.sass('src/sass/customrenovation_leadership.style.scss', 'css');

/*
 |--------------------------------------------------------------------------
 | JS
 |--------------------------------------------------------------------------
 */
mix.js('src/js/customrenovation_leadership.js', 'js')
    .sourceMaps(true,'eval-source-map');
