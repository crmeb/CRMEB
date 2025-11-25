// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import {
	getUserInfo
} from "../../api/user.js";
import {
	LOGIN_STATUS,
	UID,
	USER_INFO
} from '../../config/cache';
import Cache from '../../utils/cache';

const state = {
	token: Cache.get(LOGIN_STATUS) || false,
	backgroundColor: "#fff",
	userInfo: {},
	uid: Cache.get(UID) || 0,
	homeActive: false,
	phoneStatus: true,
	pageFooter: uni.getStorageSync('pageFoot') || {},
	activityTab: ''
};

const mutations = {
	SETPHONESTATUS(state, val) {
		state.phoneStatus = val;
	},
	LOGIN(state, opt) {
		state.token = opt.token;
		Cache.set(LOGIN_STATUS, opt.token, opt.time);
	},
	SETUID(state, val) {
		state.uid = val;
		Cache.set(UID, val);
	},
	UPDATE_LOGIN(state, token) {
		state.token = token;
	},
	ACTIVITYTAB(state, tab) {
		state.activityTab = tab;
	},
	LOGOUT(state) {
		state.token = false;
		state.uid = 0
		Cache.clear(LOGIN_STATUS);
		Cache.clear(USER_INFO);
		Cache.clear(UID);
		Cache.clear('snsapiCode');
	},
	BACKGROUND_COLOR(state, color) {
		state.color = color;
		document.body.style.backgroundColor = color;
	},
	UPDATE_USERINFO(state, userInfo) {
		state.userInfo = userInfo;
		Cache.set(USER_INFO, userInfo);
	},
	OPEN_HOME(state) {
		state.homeActive = true;
	},
	CLOSE_HOME(state) {
		state.homeActive = false;
	},
	FOOT_UPLOAD(state, data) {
		state.pageFooter = data
	}
};

const actions = {

	USERINFO({
		state,
		commit
	}, force) {
		if (state.userInfo !== null && !force)
			return Promise.resolve(state.userInfo);
		else
			return new Promise(reslove => {
				getUserInfo().then(res => {
					commit("UPDATE_USERINFO", res.data);
					Cache.set(USER_INFO, res.data);
					reslove(res.data);
				});
			}).catch(() => {

			});
	}
};

export default {
	state,
	mutations,
	actions
};
