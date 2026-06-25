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

const config = require("./config.local.js");
const mix = require("laravel-mix");
require("laravel-mix-tailwind");

mix
  .options({ processCssUrls: false })
  .sourceMaps(false, "eval-source-map")
  .webpackConfig({ devtool: "source-map" })
  .disableNotifications();

mix
  .copy("src/images", "assets/images")
  .copy("node_modules/reveal.js/dist/reveal.css", "assets/css/reveal")
  .copy("node_modules/reveal.js/dist/theme/white.css", "assets/css/reveal")
  .copy("node_modules/reveal.js/dist/theme/fonts", "assets/css/reveal/fonts")
  .copy("node_modules/reveal.js/dist/reveal.js", "assets/js/reveal")
  .js("src/js/analytics.script.js", "assets/js")
  .js("src/js/reports.js", "assets/js")
  .js("src/js/tableau-tiles.js", "assets/js")
  .js("src/js/tableau-menu-nav.js", "assets/js")
  .sass("src/sass/analytics.style.scss", "assets/css")
  .tailwind()
  .browserSync(config.browsersync);
