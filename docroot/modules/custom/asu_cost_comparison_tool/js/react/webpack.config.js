// webpack.config.js
const path = require('path');

module.exports = {
  mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
  entry: path.resolve(__dirname, 'src', 'index.js'),
  output: {
    path: path.resolve(__dirname, 'dist'),
   // filename: 'app.bundle.js',
   // chunkFilename: '[name].chunk.js',
    filename: "[name].bundle.js",
    chunkFilename: "[name].chunk.js",
    publicPath: "auto",
    clean: true,
  },
  module: {
    rules: [
      { test: /\.js$/, exclude: /node_modules/, use: 'babel-loader' },
      { test: /\.css$/, use: ['style-loader', 'css-loader'] },
    ],
  },

  
  
  resolve: { extensions: ['.js', '.jsx'] },
  devtool: process.env.NODE_ENV === 'production' ? false : 'inline-source-map',
};
