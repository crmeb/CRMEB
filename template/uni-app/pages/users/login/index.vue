<template>
	<view class="login-wrapper" :style="colorStyle">
		<view class="shading">
			<image :src="logoUrl" />
		</view>
		<view class="whiteBg" v-if="formItem === 1">
			<view class="list" v-if="current !== 1">
				<form @submit.prevent="submit">
					<view class="item">
						<view class="acea-row row-middle">
							<image src="../static/phone_1.png" style="width: 24rpx; height: 34rpx"></image>
							<input type="text" :placeholder="$t(`输入手机号码`)" v-model="account" maxlength="11" required />
						</view>
					</view>
					<view class="item">
						<view class="acea-row row-middle">
							<image src="../static/code_1.png" style="width: 28rpx; height: 32rpx"></image>
							<input type="password" :placeholder="$t(`填写登录密码`)" v-model="password" required />
						</view>
					</view>
				</form>
				<!-- <navigator class="forgetPwd" hover-class="none" url="/pages/users/retrievePassword/index">
					<span class="iconfont icon-wenti"></span>忘记密码
				</navigator> -->
			</view>
			<view class="list" v-if="current !== 0 || appLoginStatus || appleLoginStatus">
				<view class="item">
					<view class="acea-row row-middle">
						<image src="../static/phone_1.png" style="width: 24rpx; height: 34rpx"></image>
						<input type="text" :placeholder="$t(`输入手机号码`)" v-model="account" :maxlength="11" />
					</view>
				</view>
				<view class="item">
					<view class="acea-row row-middle">
						<image src="../static/code_2.png" style="width: 28rpx; height: 32rpx"></image>
						<input type="text" :placeholder="$t(`填写验证码`)" :maxlength="6" class="codeIput" v-model="captcha" />
						<button class="code" :disabled="disabled" :class="disabled === true ? 'on' : ''" @click="code">
							{{ text }}
						</button>
					</view>
				</view>
				<!-- 	<view class="item" v-if="isShowCode">
					<view class="acea-row row-middle">
						<image src="../static/code_2.png" style="width: 28rpx; height: 32rpx;"></image>
						<input type="text" :placeholder="$t(`填写验证码`)" class="codeIput" v-model="codeVal" />
						<view class="code" @click="again"><img :src="codeUrl" /></view>
					</view>
				</view> -->
			</view>
			<view class="logon" @click="loginMobile" v-if="current !== 0">{{ $t(`登录`) }}</view>
			<view class="logon" @click="submit" v-if="current === 0">{{ $t(`登录`) }}</view>
			<!-- #ifndef APP-PLUS -->
			<view class="tips">
				<view v-if="current == 0" @click="current = 1">{{ $t(`快速登录`) }}</view>
				<view v-if="current == 1" @click="current = 0">{{ $t(`账号登录`) }}</view>
			</view>
			<!-- #endif -->
			<!-- #ifdef APP-PLUS -->
			<view class="appLogin" v-if="!appLoginStatus && !appleLoginStatus">
				<view class="hds">
					<span class="line"></span>
					<p>{{ $t(`其他方式登录`) }}</p>
					<span class="line"></span>
				</view>
				<view class="btn-wrapper">
					<view class="btn wx" @click="wxLogin">
						<span class="iconfont icon-s-weixindenglu1"></span>
					</view>
					<view class="btn mima" v-if="current == 1" @click="current = 0">
						<span class="iconfont icon-s-mimadenglu1"></span>
					</view>
					<view class="btn yanzheng" v-if="current == 0" @click="current = 1">
						<span class="iconfont icon-s-yanzhengmadenglu1"></span>
					</view>
					<view class="apple-btn" @click="appleLogin" v-if="appleShow">
						<view class="iconfont icon-s-pingguo"></view>
					</view>
				</view>
			</view>
			<!-- #endif -->
			<view class="protocol">
				<checkbox-group @change="ChangeIsDefault">
					<checkbox :class="inAnimation ? 'trembling' : ''" @animationend="inAnimation = false" :checked="protocol ? true : false" />
					{{ $t(`已阅读并同意`) }}
					<text class="main-color" @click="privacy(4)">{{ $t(`《用户协议》`) }}</text>
					{{ $t(`与`) }}
					<text class="main-color" @click="privacy(3)">{{ $t(`《隐私协议》`) }}</text>
				</checkbox-group>
			</view>
		</view>
		<view class="bottom">
			<view class="ver" v-if="copyRight">{{ copyRight }}</view>
			<view v-else class="ver">
				<a href="https://www.crmeb.com">Copyright ©2024 CRMEB. All Rights</a>
			</view>
		</view>
		<Verify @success="success" :captchaType="captchaType" :imgSize="{ width: '330px', height: '155px' }" ref="verify"></Verify>
	</view>
</template>
<script>
import dayjs from '@/plugin/dayjs/dayjs.min.js';
import sendVerifyCode from '@/mixins/SendVerifyCode';
import { loginH5, loginMobile, registerVerify, register, getCodeApi, getUserInfo, appleLogin } from '@/api/user';
import attrs, { required, alpha_num, chs_phone } from '@/utils/validate';
import { getLogo } from '@/api/public';
// import cookie from "@/utils/store/cookie";
import { VUE_APP_API_URL } from '@/utils';
// #ifdef APP-PLUS
import { wechatAppAuth } from '@/api/api.js';
// #endif
const BACK_URL = 'login_back_url';
import colors from '@/mixins/color.js';
import Verify from '../components/verify/index.vue';
export default {
	name: 'Login',
	components: {
		Verify
	},
	mixins: [sendVerifyCode, colors],
	data: function () {
		return {
			copyRight: '',
			inAnimation: false,
			protocol: false,
			navList: [this.$t(`快速登录`), this.$t(`账号登录`)],
			current: 1,
			account: '',
			password: '',
			captcha: '',
			formItem: 1,
			type: 'login',
			logoUrl: '',
			keyCode: '',
			codeUrl: '',
			codeVal: '',
			isShowCode: false,
			appLoginStatus: false, // 微信登录强制绑定手机号码状态
			appUserInfo: null, // 微信登录保存的用户信息
			appleLoginStatus: false, // 苹果登录强制绑定手机号码状态
			appleUserInfo: null,
			appleShow: false, // 苹果登录版本必须要求ios13以上的
			keyLock: true,
			captchaType: 'clickWord',
		};
	},
	watch: {
		formItem: function (nval, oVal) {
			if (nval == 1) {
				this.type = 'login';
			} else {
				this.type = 'register';
			}
		}
	},
	onLoad() {
		let self = this;
		uni.getSystemInfo({
			success: (res) => {
				if (res.platform.toLowerCase() == 'ios' && this.getSystem(res.system)) {
					self.appleShow = true;
				}
			}
		});
		if (uni.getStorageSync('copyRight').copyrightContext) {
			this.copyRight = uni.getStorageSync('copyRight').copyrightContext;
		}
	},
	mounted() {
		// this.getCode();
		this.getLogoImage();
	},
	methods: {
		ChangeIsDefault(e) {
			this.$set(this, 'protocol', !this.protocol);
		},
		privacy(type) {
			uni.navigateTo({
				url: '/pages/users/privacy/index?type=' + type
			});
		},
		// IOS 版本号判断
		getSystem(system) {
			let str;
			system.toLowerCase().indexOf('ios') === -1 ? (str = system) : (str = system.split(' ')[1]);
			if (str.indexOf('.')) return str.split('.')[0] >= 13;
			return str >= 13;
		},
		// 苹果登录
		appleLogin() {
			let self = this;
			this.account = '';
			this.captcha = '';
			if (!self.protocol) {
				this.inAnimation = true;
				return self.$util.Tips({
					title: '请先阅读并同意协议'
				});
			}
			uni.showLoading({
				title: this.$t(`登录中`)
			});
			uni.login({
				provider: 'apple',
				timeout: 10000,
				success(loginRes) {
					uni.getUserInfo({
						provider: 'apple',
						success: function (infoRes) {
							self.appleUserInfo = infoRes.userInfo;
							self.appleLoginApi();
						},
						fail() {
							uni.showToast({
								title: self.$t(`获取用户信息失败`),
								icon: 'none',
								duration: 2000
							});
						},
						complete() {
							uni.hideLoading();
						}
					});
				},
				fail(error) {
					console.log(error);
				}
			});
		},
		// 苹果登录Api
		appleLoginApi() {
			let self = this;
			appleLogin({
				openId: self.appleUserInfo.openId,
				email: self.appleUserInfo.email || '',
				phone: this.account,
				captcha: this.captcha
			})
				.then(({ data }) => {
					if (data.isbind) {
						uni.showModal({
							title: self.$t(`提示`),
							content: self.$t(`请绑定手机号后，继续操作`),
							showCancel: false,
							success: function (res) {
								if (res.confirm) {
									self.current = 1;
									self.appleLoginStatus = true;
								}
							}
						});
					} else {
						self.$store.commit('LOGIN', {
							token: data.token,
							time: data.expires_time - self.$Cache.time()
						});
						let backUrl = self.$Cache.get(BACK_URL) || '/pages/index/index';
						self.$Cache.clear(BACK_URL);
						self.$store.commit('SETUID', data.userInfo.uid);
						uni.reLaunch({
							url: backUrl
						});
					}
				})
				.catch((error) => {
					uni.showModal({
						title: self.$t(`提示`),
						content: self.$t(`错误信息`) + `${error}`,
						success: function (res) {
							if (res.confirm) {
								console.log(self.$t(`用户点击确定`));
							} else if (res.cancel) {
								console.log(self.$t(`用户点击取消`));
							}
						}
					});
				});
		},
		// App微信登录
		wxLogin() {
			let self = this;
			this.account = '';
			this.captcha = '';
			if (!self.protocol) {
				this.inAnimation = true;
				return self.$util.Tips({
					title: '请先阅读并同意协议'
				});
			}
			uni.showLoading({
				title: self.$t(`登录中`)
			});
			uni.login({
				provider: 'weixin',
				success: function (loginRes) {
					// 获取用户信息
					uni.getUserInfo({
						provider: 'weixin',
						success: function (infoRes) {
							self.appUserInfo = infoRes.userInfo;
							self.wxLoginApi();
						},
						fail() {
							uni.showToast({
								title: self.$t(`获取用户信息失败`),
								icon: 'none',
								duration: 2000
							});
						},
						complete() {
							uni.hideLoading();
						}
					});
				},
				fail() {
					uni.showToast({
						title: self.$t(`登录失败`),
						icon: 'none',
						duration: 2000
					});
				}
			});
		},

		wxLoginApi() {
			let self = this;
			wechatAppAuth({
				userInfo: self.appUserInfo,
				phone: this.account,
				code: this.captcha
			})
				.then(({ data }) => {
					if (data.isbind) {
						uni.showModal({
							title: self.$t(`提示`),
							content: self.$t(`请绑定手机号后，继续操作`),
							showCancel: false,
							success: function (res) {
								if (res.confirm) {
									self.current = 1;
									self.appLoginStatus = true;
								}
							}
						});
					} else {
						self.$store.commit('LOGIN', {
							token: data.token,
							time: data.expires_time - self.$Cache.time()
						});
						let backUrl = self.$Cache.get(BACK_URL) || '/pages/index/index';
						self.$Cache.clear(BACK_URL);
						self.$store.commit('SETUID', data.userInfo.uid);
						uni.reLaunch({
							url: backUrl
						});
					}
				})
				.catch((error) => {
					uni.showModal({
						title: self.$t(`提示`),
						content: self.$t(`错误信息`) + `${error}`,
						success: function (res) {
							if (res.confirm) {
								console.log(self.$t(`用户点击确定`));
							} else if (res.cancel) {
								console.log(self.$t(`用户点击取消`));
							}
						}
					});
				});
		},
		again() {
			this.codeUrl = VUE_APP_API_URL + '/sms_captcha?' + 'key=' + this.keyCode + Date.parse(new Date());
		},
		success(data) {
			this.$refs.verify.hide();
			getCodeApi()
				.then((res) => {
					this.keyCode = res.data.key;
					this.getCode(data);
				})
				.catch((res) => {
					this.$util.Tips({
						title: res
					});
				});
		},
		code() {
			let that = this;
			if (!that.protocol) {
				this.inAnimation = true;
				return that.$util.Tips({
					title: '请先阅读并同意协议'
				});
			}
			if (!that.account)
				return that.$util.Tips({
					title: that.$t(`请填写手机号码`)
				});
			if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account))
				return that.$util.Tips({
					title: that.$t(`请输入正确的手机号码`)
				});
			this.$refs.verify.show();
		},
		async getLogoImage() {
			let that = this;
			getLogo(2).then((res) => {
				that.logoUrl = res.data.logo_url;
			});
		},
		async loginMobile() {
			let that = this;
			if (!that.protocol) {
				this.inAnimation = true;
				return that.$util.Tips({
					title: '请先阅读并同意协议'
				});
			}
			if (!that.account)
				return that.$util.Tips({
					title: that.$t(`请填写手机号码`)
				});
			if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account))
				return that.$util.Tips({
					title: that.$t(`请输入正确的手机号码`)
				});
			if (!that.captcha)
				return that.$util.Tips({
					title: that.$t(`请填写验证码`)
				});
			if (!/^[\w\d]+$/i.test(that.captcha))
				return that.$util.Tips({
					title: that.$t(`请输入正确的验证码`)
				});
			if (that.appLoginStatus) {
				that.wxLoginApi();
			} else if (that.appleLoginStatus) {
				that.appleLoginApi();
			} else {
				if (this.keyLock) {
					this.keyLock = !this.keyLock;
				} else {
					return that.$util.Tips({
						title: that.$t(`请勿重复点击`)
					});
				}
				loginMobile({
					phone: that.account,
					captcha: that.captcha,
					spread: that.$Cache.get('spread'),
					agent_id: that.$Cache.get('agent_id') || 0
				})
					.then((res) => {
						let data = res.data;
						that.$store.commit('LOGIN', {
							token: data.token,
							time: data.expires_time - this.$Cache.time()
						});
						let backUrl = that.$Cache.get(BACK_URL) || '/pages/index/index';
						that.$Cache.clear(BACK_URL);
						getUserInfo().then((res) => {
							this.keyLock = true;
							that.$store.commit('SETUID', res.data.uid);
							if (backUrl.indexOf('/pages/users/login/index') !== -1) {
								backUrl = '/pages/index/index';
							}
							uni.reLaunch({
								url: backUrl
							});
						});
					})
					.catch((res) => {
						this.keyLock = true;
						that.$util.Tips({
							title: res
						});
					});
			}
		},
		async register() {
			let that = this;
			if (!that.protocol) {
				this.inAnimation = true;
				return that.$util.Tips({
					title: '请先阅读并同意协议'
				});
			}
			if (!that.account)
				return that.$util.Tips({
					title: that.$t(`请填写手机号码`)
				});
			if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account))
				return that.$util.Tips({
					title: that.$t(`请输入正确的手机号码`)
				});
			if (!that.captcha)
				return that.$util.Tips({
					title: that.$t(`请填写验证码`)
				});
			if (!/^[\w\d]+$/i.test(that.captcha))
				return that.$util.Tips({
					title: that.$t(`请输入正确的验证码`)
				});
			if (!that.password)
				return that.$util.Tips({
					title: that.$t(`请填写密码`)
				});
			if (/^([0-9]|[a-z]|[A-Z]){0,6}$/i.test(that.password))
				return that.$util.Tips({
					title: that.$t(`您输入的密码过于简单`)
				});
			register({
				account: that.account,
				captcha: that.captcha,
				password: that.password,
				spread: that.$Cache.get('spread')
			})
				.then((res) => {
					that.$util.Tips({
						title: res
					});
					that.formItem = 1;
				})
				.catch((res) => {
					that.$util.Tips({
						title: res
					});
				});
		},
		async getCode(data) {
			let that = this;
			if (!that.protocol) {
				this.inAnimation = true;
				return that.$util.Tips({
					title: '请先阅读并同意协议'
				});
			}
			if (!that.account)
				return that.$util.Tips({
					title: that.$t(`请填写手机号码`)
				});
			if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account))
				return that.$util.Tips({
					title: that.$t(`请输入正确的手机号码`)
				});
			if (that.formItem == 2) that.type = 'register';

			await registerVerify({
				phone: that.account,
				type: that.type,
				key: that.keyCode,
				captchaType: this.captchaType,
				captchaVerification: data.captchaVerification
			})
				.then((res) => {
					this.sendCode();
					that.$util.Tips({
						title: res.msg
					});
				})
				.catch((res) => {
					that.$util.Tips({
						title: res
					});
				});
		},
		navTap: function (index) {
			this.current = index;
		},
		async submit() {
			let that = this;
			if (!that.protocol) {
				this.inAnimation = true;
				return that.$util.Tips({
					title: '请先阅读并同意协议'
				});
			}
			if (!that.account)
				return that.$util.Tips({
					title: that.$t(`请填写账号`)
				});
			if (!/^[\w\d]{5,16}$/i.test(that.account))
				return that.$util.Tips({
					title: that.$t(`请输入正确的账号`)
				});
			if (!that.password)
				return that.$util.Tips({
					title: that.$t(`请填写密码`)
				});
			if (this.keyLock) {
				this.keyLock = !this.keyLock;
			} else {
				return that.$util.Tips({
					title: that.$t(`请勿重复点击`)
				});
			}
			loginH5({
				account: that.account,
				password: that.password,
				spread: that.$Cache.get('spread'),
				agent_id: that.$Cache.get('agent_id') || 0
			})
				.then(({ data }) => {
					that.$store.commit('LOGIN', {
						token: data.token,
						time: data.expires_time - this.$Cache.time()
					});
					let backUrl = that.$Cache.get(BACK_URL) || '/pages/index/index';
					that.$Cache.clear(BACK_URL);
					getUserInfo()
						.then((res) => {
							this.keyLock = true;
							that.$store.commit('SETUID', res.data.uid);
							uni.reLaunch({
								url: backUrl
							});
						})
						.catch((error) => {
							this.keyLock = true;
						});
				})
				.catch((e) => {
					this.keyLock = true;
					that.$util.Tips({
						title: e
					});
				});
		}
	}
};
</script>
<style>
page {
	background: #fff;
}
</style>
<style lang="scss">
.appLogin {
	margin-top: 60rpx;

	.hds {
		display: flex;
		justify-content: center;
		align-items: center;
		font-size: 24rpx;
		color: #b4b4b4;

		.line {
			width: 68rpx;
			height: 1rpx;
			background: #cccccc;
		}

		p {
			margin: 0 20rpx;
		}
	}

	.btn-wrapper {
		display: flex;
		align-items: center;
		justify-content: center;
		margin-top: 30rpx;

		.btn {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 68rpx;
			height: 68rpx;
			border-radius: 50%;
		}

		.apple-btn {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 68rpx;
			height: 68rpx;
			border-radius: 50%;
			background: #000;

			.icon-s-pingguo {
				color: #fff;
				font-size: 44rpx;
			}
		}

		.iconfont {
			font-size: 40rpx;
			color: #fff;
		}

		.wx {
			margin-right: 30rpx;
			background-color: #61c64f;
		}

		.mima {
			margin-right: 30rpx;
			background-color: #28b3e9;
		}

		.yanzheng {
			margin-right: 30rpx;
			background-color: #f89c23;
		}
	}
}

.code img {
	width: 100%;
	height: 100%;
}

.acea-row.row-middle {
	input {
		margin-left: 20rpx;
		display: block;
	}
}

.login-wrapper {
	padding: 30rpx;

	.shading {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 100%;

		/* #ifdef APP-VUE */
		margin-top: 50rpx;
		/* #endif */
		/* #ifndef APP-VUE */

		margin-top: 200rpx;
		/* #endif */

		image {
			width: 240rpx;
			height: 240rpx;
		}
	}

	.whiteBg {
		margin-top: 100rpx;

		.list {
			border-radius: 16rpx;
			overflow: hidden;

			.item {
				border-bottom: 1px solid #f0f0f0;
				background: #fff;

				.row-middle {
					position: relative;
					padding: 16rpx 45rpx;

					input {
						flex: 1;
						font-size: 28rpx;
						height: 80rpx;
					}

					.code {
						position: absolute;
						right: 30rpx;
						top: 50%;
						color: var(--view-theme);
						font-size: 26rpx;
						transform: translateY(-50%);
					}
				}
			}
		}

		.logon {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 100%;
			height: 86rpx;
			margin-top: 80rpx;
			background-color: var(--view-theme);
			border-radius: 120rpx;
			color: #ffffff;
			font-size: 30rpx;
		}

		.tips {
			margin: 30rpx;
			text-align: center;
			color: #999;
		}
	}
}

.protocol {
	margin-top: 40rpx;
	color: #999999;
	font-size: 24rpx;
	text-align: center;
	bottom: 20rpx;
}
/* #ifdef H5 */
@media (min-aspect-ratio: 13/20) {
	.bottom {
		display: none !important;
	}
}
/* #endif */
.bottom {
	position: fixed;
	bottom: 30rpx;
	left: 0;
	display: flex;
	width: 100%;
	justify-content: center;
	color: #999999;

	.ver {
		font-size: 20rpx;
	}

	.ver-msg {
		margin-left: 10rpx;
	}

	a {
		color: #999999;
		margin-left: 10rpx;
		text-decoration: none;
	}
}

.trembling {
	animation: shake 0.6s;
}

.main-color {
	color: var(--view-theme);
}
</style>
