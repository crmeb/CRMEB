// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
/**
 * iView Admin Pro 开发配置
 * */

const env = process.env.NODE_ENV;

const Setting = {
  // 是否使用 Mock 的数据，默认 开发环境为 true，生产环境为 false
  isMock: env === 'development',
  // 部署应用包时的基本 URL
  publicPath: env === 'development' ? '/admin/' : '/admin/',
  // 生产环境构建文件的目录名
  outputDir: 'dist',
  // 放置生成的静态资源 (js、css、img、fonts) 的 (相对于 outputDir 的) 目录。
  assetsDir: 'view_admin',
  // 开发环境每次保存时 lint 代码，会将 lint 错误输出为编译警告
  // true || false || error
  lintOnSave: true,
  // iView Loader 的选项
  // 详见 https://www.iviewui.com/docs/guide/iview-loader
  iviewLoaderOptions: {
    prefix: false,
  },
};

module.exports = Setting;
