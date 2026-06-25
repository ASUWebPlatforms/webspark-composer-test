const path = require('path');
const webpack = require('webpack');
const dotenv = require('dotenv');

// Load environment variables from .env file
dotenv.config();

const config = {
  entry: './src/index.js',
  devtool: (process.env.NODE_ENV === 'production') ? false : 'inline-source-map',
  mode: (process.env.NODE_ENV === 'production') ? 'production' : 'development',
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'app.bundle.js'
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /(node_modules)/,
        use: {
          loader: 'babel-loader'
        }
      },
	  {
        test: /\.css$/,
        exclude: /(node_modules)/,
        use: ["style-loader","css-loader"]
      }	
    ]
  },
  plugins: [
    new webpack.DefinePlugin({
      'process.env.REACT_APP_MAPP_KEY': JSON.stringify(process.env.REACT_APP_MAPP_KEY),
    }),
  ],
};

module.exports = config;
