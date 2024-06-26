const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const {WebpackManifestPlugin} = require('webpack-manifest-plugin');
const { VueLoaderPlugin } = require("vue-loader");
const path = require('path');
const webpack = require('webpack');

const devMode = process.env.NODE_ENV !== "production";

module.exports = {
    mode: process.env.NODE_ENV || 'production',
    entry: {
        app: path.resolve(__dirname, 'js/app.js'),
        profile: path.resolve(__dirname, 'js/profile.js'),
        home: path.resolve(__dirname, 'css/home.scss'),
        inner: path.resolve(__dirname, 'css/inner.scss'),
        custom: path.resolve(__dirname, 'css/custom.scss')
    },
    output: {
        path: path.resolve(__dirname, 'dist'),
        filename: devMode ? '[name].js' : '[name].[contenthash].js',
        publicPath: '/wp-content/themes/gpx_new/dist/',
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
                    // 'vue-style-loader',
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
                test: /\.tsx?$/,
                loader: 'ts-loader',
                options: {
                    appendTsSuffixTo: [/\.vue$/],
                    transpileOnly:true,
                },
                exclude: /node_modules/,
            },
            {
                test: /\.(js)$/,
                exclude: /node_modules/,
                use: 'babel-loader',
            },
            {
                test: /\.vue$/,
                loader: "vue-loader",
            },
            {
                test: /\.(jpe?g|png|gif|svg)$/i,
                type: 'asset/resource',
                generator: {
                    filename: 'images/[hash][ext][query]'
                }
            },
            {
                test: /\.(woff|woff2|ttf|eot)$/i,
                type: 'asset/resource',
                generator: {
                    filename: 'fonts/[hash][ext][query]'
                }
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
        }),
        new webpack.DefinePlugin({
            __VUE_OPTIONS_API__: false,
            __VUE_PROD_DEVTOOLS__: false,
        }),
        new VueLoaderPlugin(),
    ],
    resolve: {
        extensions: ['.js', '.jsx', '.vue', '.ts', '.tsx', '.scss', '.css'],
        alias: {
            "@js": path.resolve(__dirname, 'js'),
            "@css": path.resolve(__dirname, 'css'),
            "@font": path.resolve(__dirname, 'fonts'),
            "@img": path.resolve(__dirname, 'images'),
        },
    },
};
