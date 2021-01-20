// 请求接口地址 如果没有配置自动获取当前网址路径
const Url = ''
const VUE_APP_API_URL = Url || process.env.VUE_APP_API_URL || `${location.origin}/adminapi`
// 管理端ws
const VUE_APP_WS_ADMIN_URL = process.env.VUE_APP_WS_ADMIN_URL || `ws:${location.hostname}:20082`

// 客服端ws
const VUE_APP_WS_KEFU_URL = process.env.VUE_APP_WS_KEFU_URL || `ws:${location.hostname}:20083`




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
