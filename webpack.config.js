const BundleAnalyzerPlugin = require( 'webpack-bundle-analyzer' ).BundleAnalyzerPlugin;

const config = require( './config' );

module.exports = {
  mode: process.env.NODE_ENV,
  devtool: process.env.NODE_ENV == 'development' ? 'eval' : false,
  module : {
    rules: [
      // {
      //   test:    /\.js$/,
      //   exclude: '/node_modules/',
      //   loader : 'babel-loader',
      // },
      {
        test:    /\.jsx$/,
        exclude: '/node_modules/',
        loader : 'babel-loader',
      },
      {
        test: /\.css$/,
        loader: 'css-loader',
        options: {
          esModule: false,
        }
      },
    ]
  },
  resolve: {
    alias: {
      'react': 'preact/compat',
      'react-dom': 'preact/compat',
    }
  },
  output: {
    filename: '[name].js',
    publicPath: `${process.env.NODE_ENV === 'development' ? config.LOCAL_DIST_URL : config.PUBLIC_DIST_URL}/js/`,
    chunkFilename: '[name].[chunkhash].js',
  },
  plugins: [
    ... process.env.NODE_ENV === 'production' ? [
      new BundleAnalyzerPlugin()
    ] : []
  ],
};