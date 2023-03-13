const CopyPlugin = require('copy-webpack-plugin')
const ESLintPlugin = require('eslint-webpack-plugin')
const HtmlWebpackPlugin = require('html-webpack-plugin')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const StylelintPlugin = require('stylelint-webpack-plugin')
const path = require('path')

module.exports = (env, argv) => {
  const isProd = argv.mode === 'production'
  const isDev = !isProd

  const filename = ext => (isProd ? `[name].[contenthash].bundle.${ext}` : `[name].bundle.${ext}`)

  const plugins = () => {
    const base = [
      new CopyPlugin({
        patterns: [
          {
            from: path.resolve(__dirname, 'media'),
            to: path.resolve(__dirname, 'dist/media')
          }
        ]
      }),
      new HtmlWebpackPlugin({
        template: './index.html'
      }),
      new MiniCssExtractPlugin({
        filename: filename('css')
      })
    ]

    if (isDev) {
      base.push(new ESLintPlugin())
      base.push(new StylelintPlugin({
        configFile: '.stylelintrc.json',
        fix: true
      }))
    }

    return base
  }

  return {
    target: 'web',
    context: path.resolve(__dirname, 'src'),
    entry: {
      main: './index.js'
    },
    output: {
      clean: true,
      path: path.resolve(__dirname, 'dist'),
      publicPath: isProd ? '/f/dist/' : '/',
      filename: filename('js'),
    },
    devServer: {
      port: 3001,
      open: true,
      historyApiFallback: true,
      watchContentBase: true,
      proxy: {
        '/local/api/': {
          target: 'http://rpcanon.de-us.ru/',
          pathRewrite: {
            '^/local/api/': '/local/api/'
          },
          secure: true,
          changeOrigin: true
        },
        '/upload/': {
          target: 'http://rpcanon.de-us.ru/',
          pathRewrite: {
            '^/upload/': '/upload/'
          },
          secure: true,
          changeOrigin: true
        }
      }
    },
    devtool: isDev ? 'source-map' : false,
    resolve: {
      extensions: ['.js', '.jsx'],
      alias: {
        '@': path.resolve(__dirname, 'src'),
        '@components': path.resolve(__dirname, 'src/components'),
        '@contexts': path.resolve(__dirname, 'src/contexts'),
        '@hooks': path.resolve(__dirname, 'src/hooks'),
        '@store': path.resolve(__dirname, 'src/store')
      }
    },
    plugins: plugins(),
    module: {
      rules: [
        {
          test: /\.s[ac]ss$/i,
          use: [
            MiniCssExtractPlugin.loader,
            'css-loader',
            'sass-loader'
          ]
        },
        {
          test: /\.(png|ttf|woff2|svg)$/,
          use: ['file-loader']
        },
        {
          test: /\.(js|jsx)$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: {
              'presets': [
                '@babel/preset-env',
                '@babel/preset-react'
              ]
            }
          }
        }
      ]
    }
  }
}
