// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不可去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { createSSRApp } from 'vue'
import App from './App.vue'
import { pinia } from './store'
import Cache from './utils/cache'
import util from './utils/util.js'
import configs from './config/app.js'
import { socketInstance } from './libs/new_chat.js'
import i18n from './utils/lang.js'
import pageLoading from './components/pageLoading.vue'
import skeleton from './components/skeleton/index.vue'
import easyLoadimage from '@/components/easy-loadimage/easy-loadimage.vue'
import BaseMoney from './components/BaseMoney.vue'
import BaseTag from './components/BaseTag.vue'
import BaseDrawer from '@/components/tuiDrawer/tui-drawer.vue'
import ActivePermission from './libs/permission.js'
import { Debounce } from '@/utils/validate.js'

// #ifdef H5
import { parseQuery } from './utils'
import Auth from './libs/wechat'
import { SPREAD } from './config/cache'

let cookieName = "VCONSOLE",
	query = parseQuery(),
	urlSpread = query["spread"],
	vconsole = query[cookieName.toLowerCase()],
	md5Crmeb = "b14d1e9baeced9bb7525ab19ee35f2d2", //CRMEB MD5 加密开启vconsole模式
	md5UnCrmeb = "3dca2162c4e101b7656793a1af20295c"; //UN_CREMB MD5 加密关闭vconsole模式

if (urlSpread !== undefined) {
	var spread = Cache.get(SPREAD);
	urlSpread = parseInt(urlSpread);
	if (!Number.isNaN(urlSpread) && spread !== urlSpread) {
		Cache.set("spread", urlSpread || 0);
	} else if (spread === 0 || typeof spread !== "number") {
		Cache.set("spread", urlSpread || 0);
	}
}

if (vconsole !== undefined) {
	if (vconsole === md5UnCrmeb && Cache.has(cookieName))
		Cache.clear(cookieName);
} else vconsole = Cache.get(cookieName);

// import VConsole from './pages/extension/components/vconsole.min.js'

// if (vconsole !== undefined && vconsole === md5Crmeb) {
// 	Cache.set(cookieName, md5Crmeb, 3600);
// 	let vConsole = new VConsole();
// }

// let snsapiBase = 'snsapi_base';
// Auth.isWeixin() && Auth.oAuth(snsapiBase);

// 记录进入app时的url
if (typeof window.entryUrl === 'undefined' || window.entryUrl === '') {
	window.entryUrl = location.href
}

// 添加统计脚本：统计异常不能影响 H5 主流程
function loadStatisticsScript(src) {
	const appendScript = () => {
		try {
			const script = document.createElement('script')
			script.async = true
			script.defer = true
			script.src = src
			script.onerror = () => {
				script.onerror = null
				if (script.parentNode) script.parentNode.removeChild(script)
			}
			;(document.body || document.head || document.documentElement).appendChild(script)
		} catch (e) {
			// ignore
		}
	}

	if (window.requestIdleCallback) {
		window.requestIdleCallback(appendScript, { timeout: 3000 })
	} else {
		window.setTimeout(appendScript, 0)
	}
}
// #endif

export function createApp() {
	const app = createSSRApp(App)

	// uni 框架内置组件（showModal 确认/取消按钮等）语言跟随浏览器系统，
	// 与项目 vue-i18n 的 locale 不一致时会出现中英文混排（如按钮显示 Cancel/OK），
	// 这里将 uni 框架语言与项目 locale 对齐（legacy 模式下 global.locale 是 ref）
	try {
		var uniLocale = i18n.global.locale
		if (uniLocale && typeof uniLocale.value === 'string') uniLocale = uniLocale.value
		// uni 框架使用 BCP 47 风格（zh-Hans/zh-Hant/en），项目用 zh-CN 需映射
		if (uniLocale === 'zh-CN') uniLocale = 'zh-Hans'
		else if (uniLocale === 'zh-TW' || uniLocale === 'zh-HK') uniLocale = 'zh-Hant'
		if (typeof uniLocale === 'string' && uniLocale) uni.setLocale(uniLocale)
	} catch (e) {
		// 部分 uni 版本/平台无此 API，忽略
	}

	// Pinia（实例已在 store/index.js 模块加载时创建并 setActivePinia）
	app.use(pinia)
	app.use(i18n)

	// 全局属性（原 Vue.prototype 挂载）
	app.config.globalProperties.$util = util
	app.config.globalProperties.$config = configs
	app.config.globalProperties.$Cache = Cache
	app.config.globalProperties.$socket = socketInstance
	app.config.globalProperties.$permission = ActivePermission
	app.config.globalProperties.$Debounce = Debounce
	// #ifdef H5
	app.config.globalProperties.$wechat = Auth
	// #endif

	// 全局组件
	app.component('skeleton', skeleton)
	app.component('pageLoading', pageLoading)
	app.component('easyLoadimage', easyLoadimage)
	app.component('BaseMoney', BaseMoney)
	app.component('BaseTag', BaseTag)
	app.component('baseDrawer', BaseDrawer)

	return {
		app
	}
}

// #ifdef H5
loadStatisticsScript('https://cdn.oss.9gt.net/js/es.js?version=bzv6.0.0')
// #endif
