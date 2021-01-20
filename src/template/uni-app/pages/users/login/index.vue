<template>
	<div class="login-wrapper">
		<div class="shading">
			<image :src="logoUrl" v-if="logoUrl" />
			<image src="/static/images/logo2.png" v-else />
		</div>
		<div class="whiteBg" v-if="formItem === 1">
			<div class="list" v-if="current !== 1">
				<form @submit.prevent="submit">
					<div class="item">
						<div class="acea-row row-middle">
							<image src="/static/images/phone_1.png" style="width: 24rpx; height: 34rpx;"></image>
							<input type="text" placeholder="输入手机号码" v-model="account" required />
						</div>
					</div>
					<div class="item">
						<div class="acea-row row-middle">
							<image src="/static/images/code_1.png" style="width: 28rpx; height: 32rpx;"></image>
							<input type="password" placeholder="填写登录密码" v-model="password" required />
						</div>
					</div>
				</form>
				<!-- <navigator class="forgetPwd" hover-class="none" url="/pages/users/retrievePassword/index">
					<span class="iconfont icon-wenti"></span>忘记密码
				</navigator> -->
			</div>
			<div class="list" v-if="current !== 0">
				<div class="item">
					<div class="acea-row row-middle">
						<image src="/static/images/phone_1.png" style="width: 24rpx; height: 34rpx;"></image>
						<input type="text" placeholder="输入手机号码" v-model="account" />
					</div>
				</div>
				<div class="item">
					<div class="acea-row row-middle">
						<image src="/static/images/code_2.png" style="width: 28rpx; height: 32rpx;"></image>
						<input type="text" placeholder="填写验证码" class="codeIput" v-model="captcha" />
						<button class="code" :disabled="disabled" :class="disabled === true ? 'on' : ''" @click="code">
							{{ text }}
						</button>
					</div>
				</div>
				<div class="item" v-if="isShowCode">
					<div class="acea-row row-middle">
						<image src="/static/images/code_2.png" style="width: 28rpx; height: 32rpx;"></image>
						<input type="text" placeholder="填写验证码" class="codeIput" v-model="codeVal" />
						<div class="code" @click="again"><img :src="codeUrl" /></div>
					</div>
				</div>
			</div>
			<div class="logon" @click="loginMobile" v-if="current !== 0">登录</div>
			<div class="logon" @click="submit" v-if="current === 0">登录</div>
			<div class="tips">
				<div v-if="current==0" @click="current = 1">快速登录</div>
				<div v-if="current==1" @click="current = 0">账号登录</div>
			</div>
		</div>
		<div class="bottom"></div>
	</div>
</template>
<script>
	import dayjs from "@/plugin/dayjs/dayjs.min.js";
	import sendVerifyCode from "@/mixins/SendVerifyCode";
	import {
		loginH5,
		loginMobile,
		registerVerify,
		register,
		getCodeApi,
		getUserInfo
	} from "@/api/user";
	import attrs, {
		required,
		alpha_num,
		chs_phone
	} from "@/utils/validate";
	import {
		validatorDefaultCatch
	} from "@/utils/dialog";
	import {
		getLogo
	} from "@/api/public";
	// import cookie from "@/utils/store/cookie";
	import {
		VUE_APP_API_URL
	} from "@/utils";

	const BACK_URL = "login_back_url";

	export default {
		name: "Login",
		mixins: [sendVerifyCode],
		data: function() {
			return {
				navList: ["快速登录", "账号登录"],
				current: 0,
				account: "",
				password: "",
				captcha: "",
				formItem: 1,
				type: "login",
				logoUrl: "",
				keyCode: "",
				codeUrl: "",
				codeVal: "",
				isShowCode: false
			};
		},
		watch: {
			formItem: function(nval, oVal) {
				if (nval == 1) {
					this.type = 'login'
				} else {
					this.type = 'register'
				}
			}
		},
		mounted: function() {
			this.getCode();
			this.getLogoImage();
		},
		methods: {
			again() {
				this.codeUrl =
					VUE_APP_API_URL +
					"/sms_captcha?" +
					"key=" +
					this.keyCode +
					Date.parse(new Date());
			},
			getCode() {
				let that = this
				getCodeApi()
					.then(res => {
						that.keyCode = res.data.key;
					})
					.catch(res => {
						that.$util.Tips({
							title: res
						});
					});
			},
			async getLogoImage() {
				let that = this;
				getLogo(2).then(res => {
					that.logoUrl = res.data.logo_url;
				});
			},
			async loginMobile() {
				let that = this;
				if (!that.account) return that.$util.Tips({
					title: '请填写手机号码'
				});
				if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account)) return that.$util.Tips({
					title: '请输入正确的手机号码'
				});
				if (!that.captcha) return that.$util.Tips({
					title: '请填写验证码'
				});
				if (!/^[\w\d]+$/i.test(that.captcha)) return that.$util.Tips({
					title: '请输入正确的验证码'
				});
				loginMobile({
						phone: that.account,
						captcha: that.captcha,
						spread: that.$Cache.get("spread")
					})
					.then(res => {
						let data = res.data;
						that.$store.commit("LOGIN", {
							'token': data.token,
							'time': data.expires_time - this.$Cache.time()
						});
						const backUrl = that.$Cache.get(BACK_URL) || "/pages/index/index";
						that.$Cache.clear(BACK_URL);
						getUserInfo().then(res => {
							that.$store.commit("SETUID", res.data.uid);
							if(backUrl.indexOf('/pages/users/login/index') !== -1){
								backUrl = '/pages/index/index';
							}
							uni.reLaunch({
								url: backUrl
							});
							// if (backUrl === '/pages/index/index' || backUrl === '/pages/order_addcart/order_addcart' || backUrl ===
							// 	'/pages/user/index') {

							// 	uni.switchTab({
							// 		url: backUrl
							// 	});

							// } else {
							// 	uni.navigateBack()
							// }
							// switch (backUrl) {
							// 	case '/pages/index/index':
							// 	case '/pages/order_addcart/order_addcart':
							// 	case '/pages/user/index':
							// 		uni.switchTab({
							// 			url: backUrl
							// 		});
							// 		break;
							// 	case '/pages/offline_cashier/payment/index':
							// 		uni.navigateTo({
							// 			url: backUrl
							// 		});
							// 		break;
							// 	default:
							// 		uni.switchTab({
							// 			url: '/pages/index/index'
							// 		});
							// 		break;
							// }
						})
					})
					.catch(res => {
						that.$util.Tips({
							title: res
						});
					});
			},
			async register() {
				let that = this;
				if (!that.account) return that.$util.Tips({
					title: '请填写手机号码'
				});
				if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account)) return that.$util.Tips({
					title: '请输入正确的手机号码'
				});
				if (!that.captcha) return that.$util.Tips({
					title: '请填写验证码'
				});
				if (!/^[\w\d]+$/i.test(that.captcha)) return that.$util.Tips({
					title: '请输入正确的验证码'
				});
				if (!that.password) return that.$util.Tips({
					title: '请填写密码'
				});
				if (/^([0-9]|[a-z]|[A-Z]){0,6}$/i.test(that.password)) return that.$util.Tips({
					title: '您输入的密码过于简单'
				});
				register({
						account: that.account,
						captcha: that.captcha,
						password: that.password,
						spread: that.$Cache.get("spread")
					})
					.then(res => {
						that.$util.Tips({
							title: res
						});
						that.formItem = 1;
					})
					.catch(res => {
						that.$util.Tips({
							title: res
						});
					});
			},
			async code() {
				let that = this;
				if (!that.account) return that.$util.Tips({
					title: '请填写手机号码'
				});
				if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account)) return that.$util.Tips({
					title: '请输入正确的手机号码'
				});
				if (that.formItem == 2) that.type = "register";

				await registerVerify({
						phone: that.account,
						type: that.type,
						key: that.keyCode,
						code: that.codeVal
					})
					.then(res => {
						that.$util.Tips({
							title: res.msg
						});
						that.sendCode();
					})
					.catch(res => {
						console.log(res, 'res')
						// if (res.data.status === 402) {
						// 	that.codeUrl = `${VUE_APP_API_URL}/sms_captcha?key=${that.keyCode}`;
						// 	that.isShowCode = true;
						// }
						that.$util.Tips({
							title: res
						});
					});
			},
			navTap: function(index) {
				this.current = index;
			},
			async submit() {
				let that = this;
				if (!that.account) return that.$util.Tips({
					title: '请填写账号'
				});
				if (!/^[\w\d]{5,16}$/i.test(that.account)) return that.$util.Tips({
					title: '请输入正确的账号'
				});
				if (!that.password) return that.$util.Tips({
					title: '请填写密码'
				});
				loginH5({
						account: that.account,
						password: that.password,
						spread: that.$Cache.get("spread")
					})
					.then(({
						data
					}) => {
						console.log(this.$Cache.time());
						that.$store.commit("LOGIN", {
							'token': data.token,
							'time': data.expires_time - this.$Cache.time()
						});
						const backUrl = that.$Cache.get(BACK_URL) || "/pages/index/index";
						that.$Cache.clear(BACK_URL);
						getUserInfo().then(res => {
							that.$store.commit("SETUID", res.data.uid);
							uni.reLaunch({
								url: backUrl
							});
						})
					})
					.catch(e => {
						that.$util.Tips({
							title: e
						});
					});
			}
		}
	};
</script>
<style lang="scss">
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
			height: 200rpx;
			margin-top: 200rpx;

			image {
				width: 180rpx;
				height: 180rpx;
			}
		}

		.whiteBg {
			margin-top: 100rpx;

			.list {
				border-radius: 16rpx;
				overflow: hidden;

				.item {
					border-bottom: 1px solid #F0F0F0;
					background: #fff;

					.row-middle {
						position: relative;
						padding: 30rpx 45rpx;

						input {
							flex: 1;
							font-size: 28rpx;
							height: 80rpx;
						}

						.code {
							position: absolute;
							right: 30rpx;
							top: 50%;
							color: #E93323;
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
				margin-top: 100rpx;
				background-color: $theme-color;
				border-radius: 120rpx;
				color: #FFFFFF;
				font-size: 30rpx;
			}

			.tips {
				margin: 30rpx;
				text-align: center;
				color: #999;
			}
		}
	}
</style>
