const path = require('path');
const Setting = require('./src/setting.env');
// 引入打包分析文件
const { BundleAnalyzerPlugin } = require('webpack-bundle-analyzer');
// 引入Gzip压缩文件
const CompressionPlugin = require('compression-webpack-plugin');
// 引入js打包工具
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

const MonacoWebpackPlugin = require('monaco-editor-webpack-plugin');

const resolve = (dir) => {
  return path.join(__dirname, dir);
};
// 项目部署基础
// 默认情况下，我们假设你的应用将被部署在域的根目录下,
// 例如：https://www.my-app.com/
// 默认：'/'
// 如果您的应用程序部署在子路径中，则需要在这指定子路径
// 例如：https://www.foobar.com/my-app/
// 需要将它改为'/my-app/'
// iview-admin线上演示打包路径： https://file.iviewui.com/admin-dist/
const BASE_URL = process.env.NODE_ENV === 'production' ? '/' : '/';
const env = process.env.NODE_ENV;
module.exports = {
  // Project deployment base
  // By default we assume your app will be deployed at the root of a domain,
  // e.g. https://www.my-app.com/
  // If your app is deployed at a sub-path, you will need to specify that
  // sub-path here. For example, if your app is deployed at
  // https://www.foobar.com/my-app/
  // then change this to '/my-app/'
  outputDir: Setting.outputDir,
  runtimeCompiler: true,
  productionSourceMap: false, //关闭生产环境下的SourceMap映射文件
  baseUrl: BASE_URL,
  // tweak internal webpack configuration.
  // see https://github.com/vuejs/vue-cli/blob/dev/docs/webpack.md
  // 如果你不需要使用eslint，把lintOnSave设为false即可
  lintOnSave: false,
  // 打包优化
  // 打包优化
  configureWebpack: (config) => {
    const pluginsPro = [new BundleAnalyzerPlugin()];

    pluginsPro.push(
      new CompressionPlugin({
        algorithm: 'gzip',
        test: /\.js$|\.html$|\.css$/, // 匹配文件名
        minRatio: 0.8, // 压缩率小于1才会压缩
        threshold: 10240, // 对超过10k的数据压缩
        deleteOriginalAssets: false, // 是否删除未压缩的源文件，谨慎设置，如果希望提供非gzip的资源，可不设置或者设置为false（比如删除打包后的gz后还可以加载到原始资源文件）
      }),
    );
    pluginsPro.push(
      // js文件压缩
      new UglifyJsPlugin({
        uglifyOptions: {
          compress: {
            drop_debugger: true,
            drop_console: true, //生产环境自动删除console
            pure_funcs: ['console.log'], //移除console
          },
        },
        sourceMap: false,
        parallel: true, //使用多进程并行运行来提高构建速度。默认并发运行数：os.cpus().length - 1。
      }),
    );

    if (process.env.NODE_ENV === 'production') {
      config.plugins = [...config.plugins, ...pluginsPro];
    }
    if (process.env.NODE_ENV === 'production') {
      // 开启分离js
      // config.optimization = {
      //   runtimeChunk: 'single',
      //   splitChunks: {
      //     chunks: 'all',
      //     maxInitialRequests: Infinity,
      //     minSize: 20000,
      //     cacheGroups: {
      //       vendor: {
      //         test: /[\\/]node_modules[\\/]/,
      //         name(module) {
      //           // get the name. E.g. node_modules/packageName/not/this/part.js
      //           // or node_modules/packageName
      //           const packageName = module.context.match(/[\\/]node_modules[\\/](.*?)([\\/]|$)/)[1];
      //           // npm package names are URL-safe, but some servers don't like @ symbols
      //           return `npm.${packageName.replace('@', '')}`;
      //         },
      //       },
      //     },
      //   },
      // };
    }
  },
  chainWebpack: (config) => {
    config.resolve.alias
      .set('@', resolve('src')) // key,value自行定义，比如.set('@@', resolve('src/components'))
      .set('_c', resolve('src/components'));
    // 使用 iView Loader
    config.module
      .rule('vue')
      .test(/\.vue$/)
      .use('iview-loader')
      .loader('iview-loader')
      .tap(() => {
        return Setting.iviewLoaderOptions;
      })
      .end();
    // 重新设置 alias
    config.resolve.alias.set('@api', resolve('src/api'));
    // node
    config.node.set('__dirname', true).set('__filename', true);
    // 判断是否需要加入模拟数据
    const entry = config.entry('app');
    if (Setting.isMock) {
      entry.add('@/mock').end();
    }
    config.plugin('monaco').use(new MonacoWebpackPlugin());
  },

  // 设为false打包时不生成.map文件
  productionSourceMap: false,
  // 这里写你调用接口的基础路径，来解决跨域，如果设置了代理，那你本地开发环境的axios的baseUrl要写为 '' ，即空字符串
  // devServer: {
  //   proxy: 'localhost:3000'
  // }
  publicPath: env === 'development' ? '/admin/' : '/admin/',
};
