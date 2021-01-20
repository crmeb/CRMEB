<template>
	<view>
		<view class="system-height" :style="{ height: statusBarHeight }"></view>
		<!-- #ifdef MP -->
		<view class="title-bar" style="height: 43px;">
			<view class="icon" @click="back" v-if="!isHome"><image src="../static/left.png"></image></view>
			<view class="icon" @click="home" v-else><image src="../static/home.png"></image></view>
			账户登录
		</view>
		<!-- #endif -->
		<view class="wechat_login">
			<view class="img"><image src="../static/wechat_login.png" mode="widthFix"></image></view>
			<view class="btn-wrapper">
				<!-- #ifdef H5 -->
				<button hover-class="none" @click="wechatLogin" class="bg-green btn1">微信登录</button>
				<!-- #endif -->
				<!-- #ifdef MP -->
				<button hover-class="none" open-type="getUserInfo" @getuserinfo="setUserInfo" class="bg-green btn1">微信登录</button>
				<!-- #endif -->
				<button hover-class="none" @click="isUp = true" class="btn2">手机号登录</button>
			</view>
		</view>
		<block v-if="isUp"><mobileLogin :isUp="isUp" @close="maskClose" :authKey="authKey" @wechatPhone="wechatPhone"></mobileLogin></block>
		<block v-if="isPhoneBox"><routinePhone :logoUrl="logoUrl" :isPhoneBox="isPhoneBox" @close="bindPhoneClose" :authKey="authKey"></routinePhone></block>
	</view>
</template>

<script>
const app = getApp();
let statusBarHeight = uni.getSystemInfoSync().statusBarHeight + 'px';
import mobileLogin from '@/components/login_mobile/index.vue';
import routinePhone from '@/components/login_mobile/routine_phone.vue';
import { getLogo, silenceAuth, getUserPhone, wechatAuthV2 } from '@/api/public';
import { LOGO_URL, EXPIRES_TIME, USER_INFO, STATE_R_KEY } from '@/config/cache';
import { getUserInfo } from '@/api/user.js';
import Routine from '@/libs/routine';
import wechat from '@/libs/wechat';
export default {
	data() {
		return {
			isUp: false,
			phone: '',
			statusBarHeight: statusBarHeight,
			isHome: false,
			isPhoneBox: false,
			logoUrl: '',
			code: '',
			authKey: '',
			options: '',
			userInfo: {},
			codeNum: 0
		};
	},
	components: {
		mobileLogin,
		routinePhone
	},
	onLoad(options) {
		getLogo().then(res => {
			this.logoUrl = res.data.logo_url;
		});
		let that = this;
		// #ifdef H5
		document.body.addEventListener('focusout', () => {
			setTimeout(() => {
				const scrollHeight = document.documentElement.scrollTop || document.body.scrollTop || 0;
				window.scrollTo(0, Math.max(scrollHeight - 1, 0));
			}, 100);
		});
		const { code, state, scope } = options;
		this.options = options;
		// 获取确认授权code
		this.code = code || '';
		if (code && this.options.scope !== 'snsapi_base') {
			let spread = app.globalData.spid ? app.globalData.spid : '';
			//公众号授权登录回调
			wechat
				.auth(code, state)
				.then(res => {
					if (res.key !== undefined && res.key) {
						that.authKey = res.key;
						that.isUp = true;
					} else {
						let time = res.expires_time - that.$Cache.time();
						that.$store.commit('LOGIN', {
							token: res.token,
							time: time
						});
						that.userInfo = res.userInfo;
						that.$store.commit('SETUID', res.userInfo.uid);
						that.$store.commit('UPDATE_USERINFO', res.userInfo);
						that.wechatPhone();
					}
				})
				.catch(error => {
					// location.replace("/");
				});
		}
		// #endif
		let pages = getCurrentPages();
		let prePage = pages[pages.length - 2];
		if (prePage && prePage.route == 'pages/order_addcart/order_addcart') {
			this.isHome = true;
		} else {
			this.isHome = false;
		}
	},
	methods: {
		back() {
			uni.navigateBack();
		},
		home() {
			uni.navigateTo({
				url: '/pages/index/index'
			});
		},
		// 弹窗关闭
		maskClose() {
			this.isUp = false;
		},
		bindPhoneClose(data) {
			if (data.isStatus) {
				this.isPhoneBox = false;
				this.$util.Tips(
					{
						title: '登录成功',
						icon: 'success'
					},
					{
						tab: 3
					}
				);
			} else {
				this.isPhoneBox = false;
			}
		},
		// #ifdef MP
		// 小程序获取手机号码
		getphonenumber(e) {
			uni.showLoading({
				title: '正在登录中'
			});
			Routine.getCode()
				.then(code => {
					this.getUserPhoneNumber(e.detail.encryptedData, e.detail.iv, code);
				})
				.catch(error => {
					uni.$emit('closePage', false);
					uni.hideLoading();
				});
		},
		// 小程序获取手机号码回调
		getUserPhoneNumber(encryptedData, iv, code) {
			getUserPhone({
				encryptedData: encryptedData,
				iv: iv,
				code: code,
				spread_spid: app.globalData.spid,
				spread_code: app.globalData.code
			})
				.then(res => {
					let time = res.data.expires_time - this.$Cache.time();
					this.$store.commit('LOGIN', {
						token: res.data.token,
						time: time
					});
					this.userInfo = res.data.userInfo;
					this.$store.commit('SETUID', res.data.userInfo.uid);
					this.$store.commit('UPDATE_USERINFO', res.data.userInfo);
					this.$Cache.clear('snsapiKey');
					this.$util.Tips(
						{
							title: '登录成功',
							icon: 'success'
						},
						{
							tab: 3
						}
					);
				})
				.catch(res => {
					console.log(res);
					uni.hideLoading();
				});
		},
		/**
		 * 获取个人用户信息
		 */
		getUserInfo: function() {
			let that = this;
			getUserInfo().then(res => {
				uni.hideLoading();
				that.userInfo = res.data;
				that.$store.commit('SETUID', res.data.uid);
				that.$store.commit('UPDATE_USERINFO', res.data);
				that.$util.Tips(
					{
						title: '登录成功',
						icon: 'success'
					},
					{
						tab: 3
					}
				);
			});
		},
		setUserInfo(e) {
			uni.showLoading({ title: '正在登录中' });
			Routine.getCode()
				.then(code => {
					this.getWxUser(code);
				})
				.catch(res => {
					uni.hideLoading();
				});
		},
		getWxUser(code) {
			let self = this;
			Routine.getUserInfo()
				.then(res => {
					let userInfo = res.userInfo;
					userInfo.code = code;
					userInfo.spread_spid = app.globalData.spid; //获取推广人ID
					userInfo.spread_code = app.globalData.code; //获取推广人分享二维码ID
					Routine.authUserInfo(userInfo)
						.then(res => {
							if (res.data.key !== undefined && res.data.key) {
								uni.hideLoading();
								self.authKey = res.data.key;
								self.isPhoneBox = true;
							} else {
								uni.hideLoading();
								let time = res.data.expires_time - self.$Cache.time();
								self.$store.commit('LOGIN', {
									token: res.data.token,
									time: time
								});
								self.$util.Tips(
									{
										title: res.msg,
										icon: 'success'
									},
									{
										tab: 3
									}
								);
							}
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

		// #endif
		// #ifdef H5
		// 获取url后面的参数
		getQueryString(name) {
			var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
			var reg_rewrite = new RegExp('(^|/)' + name + '/([^/]*)(/|$)', 'i');
			var r = window.location.search.substr(1).match(reg);
			var q = window.location.pathname.substr(1).match(reg_rewrite);
			if (r != null) {
				return unescape(r[2]);
			} else if (q != null) {
				return unescape(q[2]);
			} else {
				return null;
			}
		},
		// 公众号登录
		wechatLogin() {
			if (!this.code || this.options.scope !== 'snsapi_base') {
				this.$wechat.oAuth('snsapi_userinfo', '/pages/users/wechat_login/index');
			} else {
				if (this.authKey) {
					this.isUp = true;
				}
			}
		},
		// 输入手机号后的回调
		wechatPhone() {
			this.$Cache.clear('snsapiKey');
			if (this.options.back_url) {
				let url = uni.getStorageSync('snRouter');
				url = url.indexOf('/pages/index/index') != -1 ? '/' : url;
				if (url.indexOf('/pages/users/wechat_login/index') !== -1) {
					url = '/';
				}
				if (!url) {
					url = '/pages/index/index';
				}
				let self = this;
				this.isUp = false;
				uni.showToast({
					title: '登录成功',
					icon: 'none'
				});
				console.log(url);
				setTimeout(res => {
					location.href = url;
				}, 800);
			} else {
				uni.navigateBack();
			}
		}
		// #endif
	}
};
</script>

<style lang="scss">
page {
	background: #fff;
}

.wechat_login {
	padding: 72rpx 34rpx;

	.img image {
		width: 100%;
	}

	.btn-wrapper {
		margin-top: 86rpx;
        padding: 0 66rpx;
		button {
			width: 100%;
			height: 86rpx;
			line-height: 86rpx;
			margin-bottom: 40rpx;
			border-radius: 120rpx;
			font-size: 30rpx;
			&.btn1 {
				color: #fff;
			}
			&.btn2 {
				color: #666666;
				border: 1px solid #666666;
			}
		}
	}
}

.title-bar {
	position: relative;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 36rpx;
}
.icon {
	position: absolute;
	left: 30rpx;
	top: 0;
	display: flex;
	align-items: center;
	justify-content: center;
	width: 86rpx;
	height: 86rpx;
	image {
		width: 50rpx;
		height: 50rpx;
	}
}
</style>
