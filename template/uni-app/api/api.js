// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import request from "@/utils/request.js";
/**
 * 公共接口 ，优惠券接口 , 行业此讯 , 手机号码注册
 *
 */
export function getAjcaptcha(data) {
	return request.get("ajcaptcha", data, {
		noAuth: true
	});
}

export function ajcaptchaCheck(data) {
	return request.post("ajcheck", data, {
		noAuth: true
	});
}

/**
 * 获取主页数据 无需授权
 *
 */
export function getIndexData() {
	return request.get("v2/index", {}, {
		noAuth: true
	});
}
/**
 * 获取服务器类型
 *
 */
export function getServerType() {
	return request.get("v2/site_serve", {}, {
		noAuth: true
	});
}

/**
 * 获取登录授权login
 *
 */
export function getLogo() {
	return request.get('wechat/get_logo', {}, {
		noAuth: true
	});
}


/**
 * 保存form_id
 * @param string formId
 */
export function setFormId(formId) {
	return request.post("wechat/set_form_id", {
		formId: formId
	});
}

/**
 * 领取优惠卷
 * @param int couponId
 *
 */
export function setCouponReceive(couponId) {
	return request.post('coupon/receive', {
		couponId: couponId
	});
}
/**
 * 优惠券列表
 * @param object data
 */
export function getCoupons(data) {
	return request.get('v2/coupons', data, {
		noAuth: true
	})
}
/**
 * 首页优惠券列表组件数据
 * @param object data
 */
export function getCouponsIndex(data) {
	return request.get('coupons', data, {
		noAuth: true
	})
}

/**
 * 我的优惠券
 * @param int types 0全部  1未使用 2已使用
 */
export function getUserCoupons(types, data) {
	return request.get('coupons/user/' + types, data)
}

/**
 * 首页新人优惠券
 *
 */
export function getNewCoupon() {
	return request.get('v2/new_coupon')
}

/**
 * 文章分类列表
 *
 */
export function getArticleCategoryList() {
	return request.get('article/category/list', {}, {
		noAuth: true
	})
}

/**
 * 文章列表
 * @param int cid
 *
 */
export function getArticleList(cid, data) {
	return request.get('article/list/' + cid, data, {
		noAuth: true
	})
}

/**
 * 文章 热门列表
 *
 */
export function getArticleHotList() {
	return request.get('article/hot/list', {}, {
		noAuth: true
	});
}

/**
 * 文章 轮播列表
 *
 */
export function getArticleBannerList() {
	return request.get('article/banner/list', {}, {
		noAuth: true
	})
}

/**
 * 文章详情
 * @param int id
 *
 */
export function getArticleDetails(id) {
	return request.get('article/details/' + id, {}, {
		noAuth: true
	});
}

/**
 * 手机号+验证码登录接口
 * @param object data
 */
export function loginMobile(data) {
	return request.post('login/mobile', data, {
		noAuth: true
	})
}

/**
 * 获取短信KEY
 * @param object phone
 */
export function verifyCode() {
	return request.get('verify_code', {}, {
		noAuth: true
	})
}

/**
 * 验证码发送
 * @param object phone
 */
export function registerVerify(phone, reset, key, captchaType, captchaVerification) {
	return request.post('register/verify', {
		phone: phone,
		type: reset === undefined ? 'reset' : reset,
		key: key,
		captchaType: captchaType,
		captchaVerification: captchaVerification
	}, {
		noAuth: true
	})
}

/**
 * 手机号注册
 * @param object data
 *
 */
export function phoneRegister(data) {
	return request.post('register', data, {
		noAuth: true
	});
}

/**
 * 手机号修改密码
 * @param object data
 *
 */
export function phoneRegisterReset(data) {
	return request.post('register/reset', data, {
		noAuth: true
	})
}

/**
 * 手机号+密码登录
 * @param object data
 *
 */
export function phoneLogin(data) {
	return request.post('login', data, {
		noAuth: true
	})
}

/**
 * 切换H5登录
 * @param object data
 */
// #ifdef MP
export function switchH5Login() {
	return request.post('switch_h5', {
		'from': 'routine'
	});
}
// #endif

/*
 * h5切换公众号登陆
 * */
// #ifdef H5
export function switchH5Login() {
	return request.post("switch_h5", {
		'from': "wechat"
	});
}
// #endif

/**
 * 绑定手机号
 *
 */
export function bindingPhone(data) {
	return request.post('binding', data, {
		noAuth: true
	});
}



/**
 * 绑定手机号
 *
 */
export function bindingUserPhone(data) {
	return request.post('user/binding', data);
}

/**
 * 退出登錄
 *
 */
export function logout() {
	return request.get('logout');
}

/**
 * 获取订阅消息id
 */
export function getTempIds() {
	return request.get('wechat/temp_ids', {}, {
		noAuth: true
	});
}

/**
 * 首页拼团数据
 */
export function pink() {
	return request.get('pink', {}, {
		noAuth: true
	});
}

/**
 * 获取城市信息
 */
export function getCity() {
	return request.get('city_list', {}, {
		noAuth: true
	});
}

/**
 * 获取列表
 */
export function getLiveList(page, limit) {
	return request.get('wechat/live', {
		page,
		limit
	}, {
		noAuth: true
	});
}

/**
 * 获取首页DIY；
 */
export function getDiy(id) {
	return request.get(`v2/diy/get_diy/default${id ? '?id='+id:''}`, {}, {
		noAuth: true
	});
}

/**
 * 一键换色；
 */
export function colorChange(name) {
	return request.get('v2/diy/color_change/' + name, {}, {
		noAuth: true
	});
}

/**
 * 获取公众号关注
 * @returns {*}
 */
export function follow() {
	return request.get("wechat/follow", {}, {
		noAuth: true
	});
}

/**
 * 更换手机号码
 * @returns {*}
 */
export function updatePhone(data) {
	return request.post("user/updatePhone", data, {
		noAuth: true
	});
}

/**
 * 首页优惠券弹窗
 * @returns {*}
 */
export function getCouponV2() {
	return request.get("v2/get_today_coupon", {}, {
		noAuth: true
	});
}

/**
 * 新用户优惠券弹窗
 * @returns {*}
 */
export function getCouponNewUser() {
	return request.get("v2/new_coupon", {}, {
		noAuth: true
	});
}

/**
 * 首页快速选择数据
 * @param {Object} data
 */
export function category(data) {
	return request.get("category", data, {
		noAuth: true
	});
}

/**
 * 个人搜索历史
 * @param {Object} data
 */
export function searchList(data) {
	return request.get('v2/user/search_list', data, {
		noAuth: true
	});
}

/**
 * 删除搜索历史
 */
export function clearSearch() {
	return request.get('v2/user/clean_search');
}
/**
 * 获取网站基础配置
 */
export function siteConfig(data) {
	return request.get('site_config', data, {
		noAuth: true
	});
}

/**
 * App微信登录
 * @returns {*}
 */
export function wechatAppAuth(data) {
	return request.post("wechat/app_auth", data, {
		noAuth: true
	});
}
/**
 * 获取客服类型
 * @returns {*}
 */
export function getCustomerType(data) {
	return request.get("get_customer_type", {}, {
		noAuth: true
	});
}

/**
 * 获取开屏广告
 * @returns {*}
 */
export function getOpenAdv(data) {
	return request.get("get_open_adv", {}, {
		noAuth: true
	});
}

/**
 * 获取版权信息
 */
export function getCrmebCopyRight() {
	return request.get('copyright', {}, {
		noAuth: true
	})
}
