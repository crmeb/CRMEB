// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不可去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import {
	getUserInfo
} from '../../api/user.js';
import {
	LOGIN_STATUS,
	UID,
	USER_INFO
} from '../../config/cache';
import Cache from '../../utils/cache';

/**
 * 主 store（原 Vuex app module + 根 getters 合并）
 * 方法名保留原 mutation/action 名（LOGIN/SETUID 等），方便历史代码按名直调
 */
export const useAppStore = defineStore('app', () => {
	// state
	const token = ref(Cache.get(LOGIN_STATUS) || false);
	const backgroundColor = ref('#fff');
	const userInfo = ref({});
	const uid = ref(Cache.get(UID) || 0);
	const homeActive = ref(false);
	const phoneStatus = ref(true);
	const pageFooter = ref(uni.getStorageSync('pageFoot') || {});
	const activityTab = ref('');

	// 原根 getters（store/getters.js 折叠）
	const isLogin = computed(() => !!token.value);

	// 原 mutations
	function SETPHONESTATUS(val) {
		phoneStatus.value = val;
	}

	function LOGIN(opt) {
		token.value = opt.token;
		Cache.set(LOGIN_STATUS, opt.token, opt.time);
	}

	function SETUID(val) {
		uid.value = val;
		Cache.set(UID, val);
	}

	function UPDATE_LOGIN(newToken) {
		token.value = newToken;
	}

	function ACTIVITYTAB(tab) {
		activityTab.value = tab;
	}

	function LOGOUT() {
		token.value = false;
		uid.value = 0;
		Cache.clear(LOGIN_STATUS);
		Cache.clear(USER_INFO);
		Cache.clear(UID);
		Cache.clear('snsapiCode');
	}

	function BACKGROUND_COLOR(color) {
		backgroundColor.value = color;
		// #ifdef H5
		document.body.style.backgroundColor = color;
		// #endif
	}

	function UPDATE_USERINFO(newUserInfo) {
		userInfo.value = newUserInfo;
		Cache.set(USER_INFO, newUserInfo);
	}

	function OPEN_HOME() {
		homeActive.value = true;
	}

	function CLOSE_HOME() {
		homeActive.value = false;
	}

	function FOOT_UPLOAD(data) {
		pageFooter.value = data;
	}

	// 原 actions
	function USERINFO(force) {
		if (userInfo.value !== null && !force)
			return Promise.resolve(userInfo.value);
		else
			return new Promise(reslove => {
				getUserInfo().then(res => {
					UPDATE_USERINFO(res.data);
					Cache.set(USER_INFO, res.data);
					reslove(res.data);
				});
			}).catch(() => {

			});
	}

	return {
		token,
		backgroundColor,
		userInfo,
		uid,
		homeActive,
		phoneStatus,
		pageFooter,
		activityTab,
		isLogin,
		SETPHONESTATUS,
		LOGIN,
		SETUID,
		UPDATE_LOGIN,
		ACTIVITYTAB,
		LOGOUT,
		BACKGROUND_COLOR,
		UPDATE_USERINFO,
		OPEN_HOME,
		CLOSE_HOME,
		FOOT_UPLOAD,
		USERINFO
	};
});
