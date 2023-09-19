<template>
	<view>
		<view class="Popup" v-if="isShowAuth">
			<image :src="logoUrl"></image>
			<view class="title">授权提醒</view>
			<view class="tip">请授权头像等信息，以便为您提供更好的服务</view>
			<view class="bottom flex">
				<view class="item" @click="close">随便逛逛</view>
				<!-- #ifdef APP-PLUS -->
				<button class="item grant" @click="setUserInfo">去授权</button>
				<!-- #endif -->
				<!-- #ifdef MP -->
				<button class="item grant" type="primary" open-type="getPhoneNumber" lang="zh_CN" @getphonenumber="setUserInfo">去授权</button>
				<!-- #endif -->
			</view>
		</view>
		<view class="mask" v-if="isShowAuth" @click="close"></view>
	</view>
</template>

<script>
const app = getApp();
import Cache from '../utils/cache';
import { getLogo, silenceAuth, routineBindingPhone } from '../api/public';
import { LOGO_URL, EXPIRES_TIME, USER_INFO, STATE_R_KEY } from '../config/cache';
import { mapGetters } from 'vuex';
import Routine from '../libs/routine';
import store from '../store';

export default {
	name: 'Authorize',
	props: {
		isAuto: {
			type: Boolean,
			default: true
		},
		isGoIndex: {
			type: Boolean,
			default: true
		},
		isShowAuth: {
			type: Boolean,
			default: false
		}
	},
	data() {
		return {
			logoUrl: '',
			authKey: ''
		};
	},
	computed: mapGetters(['isLogin', 'userInfo']),
	watch: {
		isLogin(n) {
			n === true && this.$emit('onLoadFun', this.userInfo);
		}
	},
	mounted() {
		this.getLogoUrl();
		let that = this;
		if (!this.isLogin && !Cache.has(STATE_R_KEY)) {
			wx.login({
				success(res) {
					Cache.set(STATE_R_KEY, res.code, 10800);
					let spread = app.globalData.spid ? app.globalData.spid : '';
					// silenceAuth({ code: res.code, spread: spread, spid: app.globalData.code })
					// 	.then(res => {
					// 		if (res.data.key !== undefined && res.data.key) {
					// 			that.authKey = res.data.key;
					// 		} else {
					// 			app.globalData.code = 0;
					// 			let time = res.data.expires_time - Cache.time();
					// 			// store.commit('UPDATE_USERINFO', res.data.userInfo);
					// 			store.commit('LOGIN', { token: res.data.token, time: time });
					// 			// store.commit('SETUID', res.data.userInfo.uid);
					// 			// Cache.set(EXPIRES_TIME,res.data.expires_time,time);
					// 			// Cache.set(USER_INFO,res.data.userInfo,time);
					// 		}
					// 	})
					// 	.catch(res => {
					// 	});
				}
			});
		} else {
			this.setAuthStatus();
		}
	},
	methods: {
		setAuthStatus() {
			Routine.authorize()
				.then(res => {
					if (res.islogin === false) this.setUserInfo();
					else this.$emit('onLoadFun', this.userInfo);
				})
				.catch(res => {
					if (this.isAuto) this.$emit('authColse', true);
				});
		},
		getUserInfo(code) {
			Routine.getUserInfo()
				.then(res => {
					let userInfo = res.userInfo;
					userInfo.code = code;
					userInfo.spread_spid = app.globalData.spid; //获取推广人ID
					userInfo.spread_code = app.globalData.code; //获取推广人分享二维码ID
					Routine.authUserInfo(userInfo)
						.then(res => {
							uni.hideLoading();
							this.$emit('authColse', false);
							this.$emit('onLoadFun', this.userInfo);
						})
						.catch(res => {
							uni.hideLoading();
							uni.showToast({
								title: res.msg,
								icon: 'none',
								duration: 2000
							});
						});
				})
				.catch(res => {
					uni.hideLoading();
				});
		},
		getUserPhoneNumber(encryptedData, iv, code) {
			routineBindingPhone({
				encryptedData: encryptedData,
				iv: iv,
				code: code,
				spid: app.globalData.spid,
				spread: app.globalData.code
			})
				.then(res => {
					let time = res.data.expires_time - this.$Cache.time();
					this.$store.commit('LOGIN', {
						token: res.data.token,
						time: time
					});
					this.$emit('authColse', false);
					this.$emit('onLoadFun', res.data.userInfo);
					uni.hideLoading();
				})
				.catch(res => {
					uni.hideLoading();
				});
		},
		setUserInfo(e) {
			uni.showLoading({ title: '正在登录中' });
			Routine.getCode()
				.then(code => {
					this.getUserPhoneNumber(e.detail.encryptedData, e.detail.iv, code);
				})
				.catch(res => {
					uni.hideLoading();
				});
		},
		getLogoUrl() {
			let that = this;
			if (Cache.has(LOGO_URL)) {
				this.logoUrl = Cache.get(LOGO_URL);
				return;
			}
			getLogo().then(res => {
				that.logoUrl = res.data.logo_url;
				Cache.set(LOGO_URL, that.logoUrl);
			});
		},
		close() {
			let pages = getCurrentPages(),
				currPage = pages[pages.length - 1];
			if (this.isGoIndex) {
				uni.navigateTo({ url: '/pages/index/index' });
			} else {
				this.$emit('authColse', false);
			}
			// if (currPage && currPage.isShowAuth != undefined){
			// 	currPage.isShowAuth = true;
			// }
		}
	}
};
</script>

<style scoped lang="scss">
.Popup {
	width: 500rpx;
	background-color: #fff;
	position: fixed;
	top: 50%;
	left: 50%;
	margin-left: -250rpx;
	transform: translateY(-50%);
	z-index: 320;
}
.Popup image {
	width: 150rpx;
	height: 150rpx;
	margin: -67rpx auto 0 auto;
	display: block;
	border: 8rpx solid #fff;
	border-radius: 50%;
}
.Popup .title {
	font-size: 28rpx;
	color: #000;
	text-align: center;
	margin-top: 30rpx;
}
.Popup .tip {
	font-size: 22rpx;
	color: #555;
	padding: 0 24rpx;
	margin-top: 25rpx;
}
.Popup .bottom .item {
	width: 50%;
	height: 80rpx;
	background-color: #eeeeee;
	text-align: center;
	line-height: 80rpx;
	font-size: 24rpx;
	color: #666;
	margin-top: 54rpx;
}
.Popup .bottom .item.on {
	width: 100%;
}
.flex {
	display: flex;
}
.Popup .bottom .item.grant {
	font-size: 28rpx;
	color: #fff;
	font-weight: bold;
	background-color: var(--view-theme);
	border-radius: 0;
	padding: 0;
}
.mask {
	position: fixed;
	top: 0;
	right: 0;
	left: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.65);
	z-index: 310;
}
</style>
