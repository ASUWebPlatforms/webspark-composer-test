const path = require("path");

module.exports = {
  mode: "production",
  entry: "./src/loans/index.js",
  output: {
    path: path.resolve(__dirname, "dist/loans"),
    filename: "bundle.js",
  },
  module: {
    rules: [
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        use: "babel-loader",
      },
      {
        test: /\.css$/,
        use: ["style-loader", "css-loader"],
      },
    ],
  },
  resolve: {
    extensions: [".js", ".jsx"],
  },
};
