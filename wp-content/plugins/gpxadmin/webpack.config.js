const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TerserPlugin = require("terser-webpack-plugin");
const {CleanWebpackPlugin} = require('clean-webpack-plugin');
const {WebpackManifestPlugin} = require('webpack-manifest-plugin');
const { VueLoaderPlugin } = require("vue-loader");
const WebpackConcatPlugin = require('webpack-concat-files-plugin');
const terser = require('terser');
const path = require('node:path');
const webpack = require('webpack');

const devMode = process.env.NODE_ENV !== "production";

module.exports = {
    mode: process.env.NODE_ENV || 'production',
    entry: {
        gpxadmin: path.resolve(__dirname, 'js/gpxadmin.js'),
    },
    output: {
        path: path.resolve(__dirname, 'dist'),
        filename: devMode ? '[name].js' : '[name].[contenthash].js',
        publicPath: '/wp-content/plugins/gpxadmin/dist/'
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
        new WebpackConcatPlugin({
            bundles: [
                {
                    src: [
                        path.join(__dirname, 'dashboard/src/js/helpers/*.js'),
                        path.join(__dirname, 'dashboard/src/js/*.js'),
                    ],
                    dest: path.join(__dirname, 'dashboard/build/js/custom.js'),
                    transforms: {
                        after: async (code) => {
                            const minifiedCode = await terser.minify(code);
                            return minifiedCode.code;
                        },
                    },
                },
            ],
            separator: ';\n',
            allowWatch: true,
            allowOptimization: false
        }),
    ],
    resolve: {
        alias: {
            "@dashboard": path.resolve(__dirname, 'dashboard/src'),
            //"@theme": path.resolve(__dirname, 'dashboard/src'),
            "@js": path.resolve(__dirname, 'js'),
            "@css": path.resolve(__dirname, 'css'),
            "@font": path.resolve(__dirname, 'fonts'),
            "@img": path.resolve(__dirname, 'images'),
        },
    },
};
