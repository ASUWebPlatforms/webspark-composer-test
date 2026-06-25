const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const globImporter = require('node-sass-glob-importer');

// Determine the mode from an environment variable or default to 'development'
const mode = process.env.NODE_ENV || 'development';
const isProduction = mode === 'production';

module.exports = {
  mode,
  entry: './src/sass/styles.scss',
  output: {
    path: path.resolve(__dirname, 'css'),
  },
module: {
    rules: [
      {
        test: /\.scss$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          {
            loader: 'sass-loader',
            options: {
              // Use globImporter as a function here
              sassOptions: {
                importer: globImporter(),
              },
            },
          },
        ],
      },
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: '[name].css',
    }),
  ],
  optimization: {
    minimizer: isProduction ? [
      new CssMinimizerPlugin(),
      '...',
    ] : [],
  },
  watch: !isProduction, // Enable watching in development mode
};
