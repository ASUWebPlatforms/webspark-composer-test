// ----------------------------------------------------------------------------
// Laravel Mix
// @see https://laravel-mix.com
// ----------------------------------------------------------------------------
const config = require('./config.local.js');
const mix = require('laravel-mix');

// BrowserSync
// ----------------------------------------------------------------------------
mix.browserSync(config.browserSync);

// UDS
// ----------------------------------------------------------------------------
mix.copy('node_modules/@asu/unity-bootstrap-theme/dist/js/bootstrap.bundle.min.js', 'assets/js');
mix.copy('node_modules/@asu/unity-bootstrap-theme/dist/img', 'assets/img');
mix.copy('img/ui-icons', 'assets/img/ui-icons');

// SASS
// ----------------------------------------------------------------------------
mix.sass('src/sass/renovation_tsmc.style.scss', 'assets/css');
mix.sass('src/sass/layout-builder.scss', 'css');

// JS
// ----------------------------------------------------------------------------
mix.js('src/js/renovation_tsmc.script.js', 'assets/js');
