// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
// 请求接口地址：优先取环境变量 VITE_APP_API_URL，留空时自动使用当前域名同源 /adminapi
const VITE_APP_API_URL = import.meta.env.VITE_APP_API_URL || `${location.origin}/adminapi`;
// H5 商城地址：优先取环境变量 VITE_APP_H5_URL（开发环境指向 uni-app H5 dev server，
// 如 http://localhost:5173），留空回退当前域名（生产同域部署时 H5 与后台同源）
const VITE_APP_H5_URL = import.meta.env.VITE_APP_H5_URL || '';

const Setting = {
  // 路由前缀
  routePre: '/admin',
  // 接口请求地址
  apiBaseURL: VITE_APP_API_URL,
  // H5 商城基础地址（含协议端口，不带末尾斜杠；空串表示同源）
  h5BaseURL: VITE_APP_H5_URL,
  // 路由模式，可选值为 history 或 hash
  routerMode: 'history',
  // 页面切换时，是否显示模拟的进度条
  showProgressBar: true,
};

export default Setting;
