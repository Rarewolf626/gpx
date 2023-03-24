const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const {WebpackManifestPlugin} = require('webpack-manifest-plugin');
const path = require('path');

const devMode = process.env.NODE_ENV !== "production";

module.exports = {
    entry: {
        app: path.resolve(__dirname, 'js/app.js'),
        home: path.resolve(__dirname, 'css/home.scss'),
        inner: path.resolve(__dirname, 'css/inner.scss'),
        custom: path.resolve(__dirname, 'css/custom.scss')
    },
    output: {
        path: path.resolve(__dirname, 'dist'),
        filename: devMode ? '[name].js' : '[name].[contenthash].js',
        publicPath: '/wp-content/themes/gpx_new/dist/'
    },
    optimization: {
        runtimeChunk: 'single',
        minimize: !devMode,
        minimizer: [new TerserPlugin()]
    },
    devtool: devMode ? 'source-map' : false,
    module: {
        rules: [
            {
                test: /\.(css|scss|sass)$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            modules: false,
                        }
                    },
                    // "postcss-loader",
                    "sass-loader"
                ]
            },
            {
                test: /\.(js|jsx|vue|svelte)$/,
                exclude: /node_modules/,
                use: 'babel-loader',
            },
        ],
    },
    plugins: [
        new CleanWebpackPlugin({
            dry: false,
            verbose: true,
            cleanOnceBeforeBuildPatterns: [
                path.resolve(__dirname, 'dist')
            ],
        }),
        new MiniCssExtractPlugin(),
        new WebpackManifestPlugin({
            basePath: '',
        })
    ],
    resolve: {
        alias: {
            "@js": path.resolve(__dirname, 'js'),
            "@css": path.resolve(__dirname, 'css'),
            "@font": path.resolve(__dirname, 'fonts'),
            "@img": path.resolve(__dirname, 'images'),
        },
    },
};
