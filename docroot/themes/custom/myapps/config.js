// ----------------------------------------------------------------------------
// Config[.local].js
// Use this file to add customizations for Laravel Mix
// ----------------------------------------------------------------------------

module.exports = {
  browsersync: {
    proxy: 'https://my-local.site',
    files: ['assets/js/**/*.js', 'assets/css/**/*.css'],
    stream: true
  }
};
