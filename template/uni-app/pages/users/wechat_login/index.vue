<template>
	<view :style="colorStyle">
		<view class="system-height" :style="{ height: statusBarHeight }"></view>
		<!-- #ifdef MP -->
		<view class="title-bar" style="height: 43px;">
			<view class="icon" @click="back" v-if="!isHome">
				<image src="../static/left.png"></image>
			</view>
			<view class="icon" @click="home" v-else>
				<image src="../static/home.png"></image>
			</view>
			{{$t(`账户登录`)}}
		</view>
		<!-- #endif -->
		<view class="wechat_login">
			<view class="img">
				<image src="../static/wechat_login.png" mode="widthFix"></image>
			</view>
			<view class="btn-wrapper">
				<!-- #ifdef H5 -->
				<button hover-class="none" @click="wechatLogin" class="bg-green btn1">{{$t(`微信登录`)}}</button>
				<!-- #endif -->
				<!-- #ifdef MP -->
				<button hover-class="none" v-if="mp_is_new" @tap="userLogin"
					class="bg-green btn1">{{$t(`微信登录`)}}</button>
				<button v-else-if="canUseGetUserProfile && code" hover-class="none" @tap="getUserProfile"
					class="bg-green btn1">{{$t(`微信登录`)}}</button>
				<button v-else hover-class="none" open-type="getUserInfo" @getuserinfo="setUserInfo"
					class="bg-green btn1">{{$t(`微信登录`)}}</button>
				<!-- #endif -->
				<!-- <button hover-class="none" @click="phoneLogin" class="btn2">{{$t(`手机号登录`)}}</button> -->
			</view>
		</view>
		<block v-if="isUp">
			<mobileLogin :isUp="isUp" :canClose="canClose" @close="maskClose" :authKey="authKey"
				@wechatPhone="wechatPhone"></mobileLogin>
		</block>
		<block v-if="isPhoneBox">
			<routinePhone :logoUrl="logoUrl" :isPhoneBox="isPhoneBox" @loginSuccess="bindPhoneClose" :authKey="authKey">
			</routinePhone>
		</block>
		<block>
			<editUserModal :isShow="isShow" @closeEdit="closeEdit" @editSuccess="editSuccess">
			</editUserModal>
		</block>
	</view>
</template>

<script>
	const app = getApp();
	let statusBarHeight = uni.getSystemInfoSync().statusBarHeight + 'px';
	import mobileLogin from '../components/login_mobile/index.vue';
	import routinePhone from '../components/login_mobile/routine_phone.vue';
	import editUserModal from '@/components/eidtUserModal/index.vue'
	import {
		getLogo,
		silenceAuth,
		getUserPhone,
		wechatAuthV2,
		authLogin
	} from '@/api/public';
	import {
		LOGO_URL,
		EXPIRES_TIME,
		USER_INFO,
		STATE_R_KEY
	} from '@/config/cache';
	import {
		getUserInfo
	} from '@/api/user.js';
	import Routine from '@/libs/routine';
	import wechat from '@/libs/wechat';
	import colors from '@/mixins/color.js';
	import Auth from '@/libs/wechat.js';
	export default {
		mixins: [colors],
		data() {
			return {
				isUp: false,
				canClose: true,
				phone: '',
				statusBarHeight: statusBarHeight,
				isHome: false,
				isPhoneBox: false,
				isShow: false,
				logoUrl: '',
				code: '',
				authKey: '',
				options: '',
				userInfo: {},
				codeNum: 0,
				canUseGetUserProfile: false,
				mp_is_new: this.$Cache.get('MP_VERSION_ISNEW') || false
			};
		},
		components: {
			mobileLogin,
			routinePhone,
			editUserModal
		},
		onLoad(options) {
			if (uni.getUserProfile) {
				this.canUseGetUserProfile = true
			}
			getLogo().then(res => {
				this.logoUrl = res.data.logo_url;
			});
			let that = this;
			// #ifdef MP
			Routine.getCode()
				.then(code => {
					this.code = code
				})
			// #endif
			// #ifdef H5
			document.body.addEventListener('focusout', () => {
				setTimeout(() => {
					const scrollHeight = document.documentElement.scrollTop || document.body
						.scrollTop ||
						0;
					window.scrollTo(0, Math.max(scrollHeight - 1, 0));
				}, 100);
			});
			const {
				code,
				state,
				scope
			} = options;
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
							that.canClose = false;
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
					.catch(err => {
						uni.hideLoading();
						uni.showToast({
							title: err,
							icon: 'none',
							duration: 2000
						});
					});
			} else if (code && this.options.scope == 'snsapi_base' && !this.$Cache.has('snsapiKey')) {
				//公众号静默授权
				let snsapiBase = 'snsapi_base';
				let urlData = location.pathname + location.search;
				// if (!that.$store.getters.isLogin && uni.getStorageSync('authIng')) {
				// 	uni.setStorageSync('authIng', false)
				// }
				if (options.back_url) {
					uni.setStorageSync('snRouter', options.back_url);
				}
				if (!that.$store.getters.isLogin && Auth.isWeixin()) {
					let code
					if (options.code instanceof Array) {
						code = options.code[options.code.length - 1]
					} else {
						code = options.code
					}
					if (code && code != uni.getStorageSync('snsapiCode') && !this.$Cache.has('snsapiKey')) {
						// 存储静默授权code
						uni.setStorageSync('snsapiCode', code);
						uni.setStorageSync('authIng', true)
						silenceAuth({
								code: code,
								spread: that.$Cache.get('spread'),
								spid: that.$Cache.get('spread')
							})
							.then(res => {
								// uni.setStorageSync('authIng', false)
								// uni.setStorageSync('snRouter', decodeURIComponent(decodeURIComponent(options.query
								// 	.back_url)));
								if (res.data.key !== undefined && res.data.key) {
									this.$Cache.set('snsapiKey', res.data.key);
									uni.navigateTo({
										url: '/pages/users/wechat_login/index'
									})
								}
							})
							.catch(error => {
								uni.setStorageSync('authIng', false)
								let url = ''
								if (options.back_url instanceof Array) {
									url = options.back_url[options.back_url.length - 1]
								} else {
									url = options.back_url
								}
								if (!that.$Cache.has('snsapiKey')) {
									Auth.oAuth(snsapiBase, url);
								}
							});
					} else {
						Auth.oAuth(snsapiBase, urlData)
					}
				} else {
					if (options.query.back_url) {
						location.replace(uni.getStorageSync('snRouter'));
					}
				}
			} else if (!this.$Cache.has('snsapiKey')) {
				let urlData = location.pathname + location.search;
				Auth.oAuth('snsapi_base', urlData)
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
			// 小程序 22.11.8日删除getUserProfile 接口获取用户昵称头像
			userLogin() {
				Routine.getCode()
					.then(code => {
						uni.showLoading({
							title: this.$t(`正在登录中`)
						});
						authLogin({
								code,
								spread_spid: app.globalData.spid,
								spread_code: app.globalData.code
							}).then(res => {
								if (res.data.key !== undefined && res.data.key) {
									uni.hideLoading();
									this.authKey = res.data.key;
									this.isPhoneBox = true;
								} else {
									uni.hideLoading();
									let time = res.data.expires_time - this.$Cache.time();
									this.$store.commit('LOGIN', {
										token: res.data.token,
										time: time
									});
									this.getUserInfo(res.data.new_user || 0)
								}
							})
							.catch(err => {
								uni.hideLoading();
								uni.showToast({
									title: err,
									icon: 'none',
									duration: 2000
								});
							});
					})
					.catch(err => {
						console.log(err)
					});
			},
			editSuccess() {
				this.isShow = false
			},
			phoneLogin() {
				this.canClose = true
				this.isUp = true;
			},
			closeEdit() {
				this.isShow = false
				this.$util.Tips({
					title: this.$t(`登录成功`),
					icon: 'success'
				}, {
					tab: 3
				});
			},
			back() {
				uni.navigateBack();
			},
			home() {
				uni.switchTab({
					url: '/pages/index/index'
				})
			},
			// 弹窗关闭
			maskClose(new_user) {
				this.isUp = false;
				// #ifdef MP
				if (new_user) {
					this.isShow = true
				}
				// #endif
			},
			bindPhoneClose(data) {
				this.isPhoneBox = false;
				if (data.isStatus) {
					// #ifdef MP
					this.getUserInfo(data.new_user)
					// #endif
					// #ifndef MP
					this.$util.Tips({
						title: this.$t(`登录成功`),
						icon: 'success'
					}, {
						tab: 3
					});
					// #endiff
				}
			},
			// #ifdef MP
			// 小程序获取手机号码
			getphonenumber(e) {
				uni.showLoading({
					title: this.$t(`正在登录中`)
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
						this.getUserInfo(res.data.userInfo.new_user || 0)
						// this.$util.Tips({
						// 	title: this.$t(`登录成功`),
						// 	icon: 'success'
						// }, {
						// 	tab: 3
						// });
					})
					.catch(res => {
						uni.hideLoading();
					});
			},
			/**
			 * 获取个人用户信息
			 */
			getUserInfo: function(new_user) {
				let that = this;
				getUserInfo().then(res => {
					uni.hideLoading();
					that.userInfo = res.data;
					that.$store.commit('SETUID', res.data.uid);
					that.$store.commit('UPDATE_USERINFO', res.data);
					if (new_user) {
						this.isShow = true
					} else {
						that.$util.Tips({
							title: that.$t(`登录成功`),
							icon: 'success'
						}, {
							tab: 3
						});
					}

				});
			},
			setUserInfo(e) {
				uni.showLoading({
					title: this.$t(`正在登录中`)
				});
				Routine.getCode()
					.then(code => {
						this.getWxUser(code);
					})
					.catch(res => {
						uni.hideLoading();
					});
			},
			//小程序授权api替换 getUserInfo
			getUserProfile() {
				uni.showLoading({
					title: this.$t(`正在登录中`)
				});
				let self = this;
				Routine.getUserProfile()
					.then(res => {
						let userInfo = res.userInfo;
						userInfo.code = this.code;
						userInfo.spread_spid = app.globalData.spid || this.$Cache.get('spread'); //获取推广人ID
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
									this.getUserInfo()
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
									self.$util.Tips({
										title: res.msg,
										icon: 'success'
									}, {
										tab: 3
									});
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
					this.isUp = false;
					uni.showToast({
						title: this.$t(`登录成功`),
						icon: 'none'
					});
					setTimeout(res => {
						location.href = url;
					}, 800);
				} else {
					this.isUp = false;
					uni.showToast({
						title: this.$t(`登录成功`),
						icon: 'none'
					});
					setTimeout(res => {
						location.href = '/pages/index/index';
					}, 800);
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
