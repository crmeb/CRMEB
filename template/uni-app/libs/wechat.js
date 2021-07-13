// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------


// #ifdef H5
import WechatJSSDK from "@/plugin/jweixin-module/index.js";


import {
	getWechatConfig,
	wechatAuth,
	getShopConfig,
	wechatAuthV2
} from "@/api/public";
import {
	WX_AUTH,
	STATE_KEY,
	LOGINTYPE,
	BACK_URL
} from '@/config/cache';
import {
	parseQuery
} from '@/utils';
import store from '@/store';
import Cache from '@/utils/cache';

class AuthWechat {

	constructor() {
		//微信实例化对象
		this.instance = WechatJSSDK;
		//是否实例化
		this.status = false;

		this.initConfig = {};

	}

	isAndroid() {
		let u = navigator.userAgent;
		return u.indexOf('Android') > -1 || u.indexOf('Adr') > -1;
	}

	signLink() {
		if (typeof window.entryUrl === 'undefined' || window.entryUrl === '') {
			window.entryUrl = document.location.href
		}
		return /(Android)/i.test(navigator.userAgent) ? document.location.href : window.entryUrl;
	}

	/**
	 * 初始化wechat(分享配置)
	 */
	wechat() {
		return new Promise((resolve, reject) => {
			// if (this.status && !this.isAndroid()) return resolve(this.instance);
			getWechatConfig()
				.then(res => {
					this.instance.config(res.data);
					this.initConfig = res.data;
					this.status = true;
					this.instance.ready(() => {
						resolve(this.instance);
					})
				}).catch(err => {
					console.log(err);
					this.status = false;
					reject(err);
				});
		});
	}

	/**
	 * 验证是否初始化
	 */
	verifyInstance() {
		let that = this;
		return new Promise((resolve, reject) => {
			if (that.instance === null && !that.status) {
				that.wechat().then(res => {
					resolve(that.instance);
				}).catch(() => {
					return reject();
				})
			} else {
				return resolve(that.instance);
			}
		})
	}
	// 微信公众号的共享地址
	openAddress() {
		return new Promise((resolve, reject) => {
			this.wechat().then(wx => {
				this.toPromise(wx.openAddress).then(res => {
					resolve(res);
				}).catch(err => {
					reject(err);
				});
			}).catch(err => {
				reject(err);
			})
		});
	}

	// 获取经纬度；
	location() {
		return new Promise((resolve, reject) => {
			this.wechat().then(wx => {
				this.toPromise(wx.getLocation, {
					type: 'wgs84'
				}).then(res => {
					resolve(res);
				}).catch(err => {
					reject(err);
				});
			}).catch(err => {
				reject(err);
			})
		});
	}

	// 使用微信内置地图查看位置接口；
	seeLocation(config) {
		return new Promise((resolve, reject) => {
			this.wechat().then(wx => {
				this.toPromise(wx.openLocation, config).then(res => {
					resolve(res);
				}).catch(err => {
					reject(err);
				});
			}).catch(err => {
				reject(err);
			})
		});
	}

	/**
	 * 微信支付
	 * @param {Object} config
	 */
	pay(config) {
		return new Promise((resolve, reject) => {
			this.wechat().then((wx) => {
				this.toPromise(wx.chooseWXPay, config).then(res => {
					resolve(res);
				}).catch(res => {
					reject(res);
				});
			}).catch(res => {
				reject(res);
			});
		});
	}

	toPromise(fn, config = {}) {
		return new Promise((resolve, reject) => {
			fn({
				...config,
				success(res) {
					resolve(res);
				},
				fail(err) {
					reject(err);
				},
				complete(err) {
					reject(err);
				},
				cancel(err) {
					reject(err);
				}
			});
		});
	}

	/**
	 * 自动去授权
	 */
	oAuth(asdasfqtrgadasdq, fggqtadasdasuil) {
		if (uni.getStorageSync(WX_AUTH) && store.state.app.token && asdasfqtrgadasdq == 'snsapi_base') return;
		const {
			code
		} = parseQuery();
		if (!code || code == uni.getStorageSync('snsapiCode')) {
			return this.toAuth(asdasfqtrgadasdq, fggqtadasdasuil);
		} else {
			if (Cache.has('snsapiKey'))
				return this.auth(code).catch(error => {
					uni.showToast({
						title: error,
						icon: 'none'
					})
				})
		}
	}

	clearAuthStatus() {

	}

	/**
	 * 授权登陆获取token
	 * @param {Object} code
	 */
	auth(a21fasd34feg23gada1e) {
		return new Promise((resolve, reject) => {
			let loginType = Cache.get(LOGINTYPE);
			wechatAuthV2(a21fasd34feg23gada1e, parseInt(Cache.get("spread")))
				.then(({
					data
				}) => {
					// store.commit("LOGIN", {
					// 	token: data.token,
					// 	time: Cache.strTotime(data.expires_time) - Cache.time()
					// });
					Cache.set(WX_AUTH, a21fasd34feg23gada1e);
					Cache.clear(STATE_KEY);
					resolve(data);
				})
				.catch(reject);
		});
	}

	/**
	 * 获取跳转授权后的地址
	 * @param {Object} appId
	 */
	getAuthUrl(appId, s1an34bsca24d6pei9fBg1ah21si2e, backUrl) {
		let url = `${location.origin}${backUrl}`
		if (url.indexOf('?') == -1) {
			url = url + '?'
		} else {
			url = url + '&'
		}
		const redirect_uri = encodeURIComponent(
			`${url}scope=${s1an34bsca24d6pei9fBg1ah21si2e}&back_url=` +
			encodeURIComponent(
				encodeURIComponent(
					uni.getStorageSync(BACK_URL) ?
					uni.getStorageSync(BACK_URL) :
					location.pathname + location.search
				)
			)
		);
		uni.removeStorageSync(BACK_URL);
		const fg1jpsd815xn1n1w6s4a2s3ts1c = encodeURIComponent(
			("" + Math.random()).split(".")[1] + "authorizestate"
		);
		uni.setStorageSync(STATE_KEY, fg1jpsd815xn1n1w6s4a2s3ts1c);
		if (s1an34bsca24d6pei9fBg1ah21si2e === 'snsapi_base') {
			return `https://open.weixin.qq.com/connect/oauth2/authorize?appid=${appId}&redirect_uri=${redirect_uri}&response_type=code&scope=snsapi_base&state=${fg1jpsd815xn1n1w6s4a2s3ts1c}#wechat_redirect`;
		} else {
			return `https://open.weixin.qq.com/connect/oauth2/authorize?appid=${appId}&redirect_uri=${redirect_uri}&response_type=code&scope=snsapi_userinfo&state=${fg1jpsd815xn1n1w6s4a2s3ts1c}#wechat_redirect`;
		}

	}

	/**
	 * 跳转自动登陆
	 */
	toAuth(sanbscadpeifBgahsieg, l4ba3abcck85dU6e9rfl1g) {
		let g214fs1gsf3h5fs1g = this;
		this.wechat().then(wx => {
			location.href = this.getAuthUrl(g214fs1gsf3h5fs1g.initConfig.appId, sanbscadpeifBgahsieg, l4ba3abcck85dU6e9rfl1g);
		})
	}

	/**
	 * 绑定事件
	 */
	wechatEvevt(jsmzwoybzd, yhqfzzzafqtta) {
		let that = this;
		return new Promise((resolve, reject) => {
			let c3o7n1fi9gD9e2fa5u4lt = {
				fail(res) {
					if (that.instance) return reject({
						is_ready: true,
						wx: that.instance
					});
					that.verifyInstance().then(wx => {
						return reject({
							is_ready: true,
							wx: wx
						});
					})
				},
				success(res) {
					return resolve(res, 2222);
				}
			};
			Object.assign(c3o7n1fi9gD9e2fa5u4lt, yhqfzzzafqtta);
			that.wechat().then(wx => {
				if (typeof jsmzwoybzd === 'object') {
					jsmzwoybzd.forEach(item => {
						wx[item] && wx[item](c3o7n1fi9gD9e2fa5u4lt)
					})
				} else {
					wx[jsmzwoybzd] && wx[jsmzwoybzd](c3o7n1fi9gD9e2fa5u4lt)
				}
			})
		});
	}


	isWeixin() {
		return navigator.userAgent.toLowerCase().indexOf("micromessenger") !== -1;
	}

}

export default new AuthWechat();
// #endif
