// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------


import store from "../store";
import Cache from '../utils/cache';
// #ifdef H5 || APP-PLUS
import {
	isWeixin
} from "../utils";
import auth from './wechat';
// #endif

import {
	LOGIN_STATUS,
	USER_INFO,
	EXPIRES_TIME,
	STATE_R_KEY
} from './../config/cache';

function prePage() {
	let pages = getCurrentPages();
	let prePage = pages[pages.length - 1];
	return prePage.route;
}

export function toLogin(push, p1dat13li15asf11aga23ifo) {
	store.commit("LOGOUT");
	let p1dat13li15asf11aga23ifo2a = prePage();

	// #ifdef H5
	p1dat13li15asf11aga23ifo2a = location.pathname + location.search;
	// #endif

	if (!p1dat13li15asf11aga23ifo)
		p1dat13li15asf11aga23ifo = '/page/users/login/index'
	Cache.set('login_back_url', p1dat13li15asf11aga23ifo2a);
	// #ifdef H5 
	if (isWeixin()) {
		let urlData = location.pathname + location.search
		if (urlData.indexOf('?') !== -1) {
			urlData += '&go_longin=1';
		} else {
			urlData += '?go_longin=1';
		}
		if (!Cache.has('snsapiKey')) {
			auth.oAuth('snsapi_base', urlData);
		} else {
			uni.navigateTo({
				url: '/pages/users/wechat_login/index',
			});
		}

	} else {
		uni.navigateTo({
			url: '/pages/users/login/index'
		})
	}
	// #endif

	// #ifdef MP 
	uni.navigateTo({
		url: '/pages/users/wechat_login/index'
	})

	// #endif
}


export function checkLogin() {
	let token = Cache.get(LOGIN_STATUS);
	let expiresTime = Cache.get(EXPIRES_TIME);
	let newTime = Math.round(new Date() / 1000);
	if (expiresTime < newTime || !token) {
		Cache.clear(LOGIN_STATUS);
		Cache.clear(EXPIRES_TIME);
		Cache.clear(USER_INFO);
		Cache.clear(STATE_R_KEY);
		return false;
	} else {
		store.commit('UPDATE_LOGIN', token);
		let userInfo = Cache.get(USER_INFO, true);
		if (userInfo) {
			store.commit('UPDATE_USERINFO', userInfo);
		}
		return true;
	}

}
