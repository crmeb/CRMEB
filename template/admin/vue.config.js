// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

const path = require('path');
// 引入js打包工具
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const MonacoWebpackPlugin = require('monaco-editor-webpack-plugin');

const resolve = (dir) => {
  return path.join(__dirname, dir);
};
// 项目部署基础
module.exports = {
  // 打包路径
  outputDir: 'dist',
  // 打包路径--线上部署文件地址
  // outputDir: '../../crmeb/public/admin',
  runtimeCompiler: true,
  productionSourceMap: false, //关闭生产环境下的SourceMap映射文件
  // 如果你不需要使用eslint，把lintOnSave设为false即可
  lintOnSave: false,
  // 打包优化
  configureWebpack: (config) => {
    const pluginsPro = [];
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
  },
  css: {
    loaderOptions: {
      scss: {
        sassOptions: {
          silenceDeprecations: ['legacy-js-api'],
        },
      },
    },
  },
  chainWebpack: (config) => {
    config.plugins.delete('prefetch');
    config.resolve.alias
      .set('@', resolve('src')) // key,value自行定义，比如.set('@@', resolve('src/components'))
      .set('_c', resolve('src/components'));
    config.module
      .rule('vue')
      .test(/\.vue$/)
      .end();
    // 重新设置 alias
    config.resolve.alias.set('@api', resolve('src/api'));
    // node
    config.node.set('__dirname', true).set('__filename', true);
    config.plugin('monaco').use(new MonacoWebpackPlugin());
  },

  // 设为false打包时不生成.map文件
  productionSourceMap: false,
  // 这里写你调用接口的基础路径，来解决跨域，如果设置了代理，那你本地开发环境的axios的baseUrl要写为 '' ，即空字符串
  devServer: {
    port: 1617, // 端口
  },
  publicPath: '/admin',
  assetsDir: 'system_static',
  indexPath: 'index.html',
};
