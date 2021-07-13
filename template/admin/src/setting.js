// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

// 请求接口地址 如果没有配置自动获取当前网址路径
const VUE_APP_API_URL = process.env.VUE_APP_API_URL || `${location.origin}/adminapi`
// 管理端ws
const VUE_APP_WS_ADMIN_URL = process.env.VUE_APP_WS_ADMIN_URL || `ws:${location.hostname}:20002`

// 客服端ws
const VUE_APP_WS_KEFU_URL = process.env.VUE_APP_WS_KEFU_URL || `ws:${location.hostname}:20003`




const Setting = {
    // 接口请求地址
    apiBaseURL: VUE_APP_API_URL,
    // adminsocket连接
    wsAdminSocketUrl: VUE_APP_WS_ADMIN_URL,
    // kefusocket连接
    wsKefuSocketUrl: VUE_APP_WS_KEFU_URL,
    // 路由模式，可选值为 history 或 hash
    routerMode: 'history',
    // 页面切换时，是否显示模拟的进度条
    showProgressBar: true
}

export default Setting

// CRMEB [ CRMEB赋能开发者，助力企业发展 ] Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权 Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved. Author: CRMEB Team <admin@crmeb.com>