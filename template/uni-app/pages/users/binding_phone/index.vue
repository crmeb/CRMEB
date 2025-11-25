<template>
	<view class="wrapper" :style="colorStyle">
		<view class="bag"></view>
		<view class="system-height" :style="{ height: statusBarHeight }"></view>
		<!-- #ifdef MP -->
		<view class="title-bar" style="height: 43px;">
			<view class="icon" @click="back" v-if="!isHome">
				<image src="../static/left.png"></image>
			</view>
			<view class="icon" @click="home" v-else>
				<image src="../static/home.png"></image>
			</view>
			{{$t(pageTitle)}}
		</view>
		<!-- #endif -->
		<view class="page-msg">
			<view class="title">
				{{pageType == 1?$t('绑定手机号'):$t('手机号登录')}}
			</view>
			<view class="tip">
				{{pageType == 1?$t('登录注册需绑定手机号'):$t('首次登录会自动注册')}}
			</view>
		</view>
		<view class="page-form">
			<view class="item">
				<input type='number' :placeholder='$t(`填写手机号码`)' placeholder-class='placeholder' v-model="phone"
					:maxlength="11"></input>
			</view>
			<view class="item acea-row row-between-wrapper">
				<input type='number' :placeholder='$t(`填写验证码`)' placeholder-class='placeholder' :maxlength="6"
					class="codeIput" v-model="captcha"></input>
				<view class="line">

				</view>
				<button class="code font-num" :class="disabled === true ? 'on' : ''" :disabled='disabled' @click="code">
					{{ text }}
				</button>
			</view>
			<view class="btn" @click="submitData">
				{{$t(`${pageType == 1 ? '绑定手机号' : '立即登录'}`)}}
			</view>
		</view>
		<view class="protocol" v-if="pageType == 0 && !canGetPrivacySetting">
			<checkbox-group @click.stop='ChangeIsDefault'>
				<checkbox :class="inAnimation?'trembling':''" @animationend='inAnimation=false'
					:checked="protocol ? true : false" /> <text @click.stop='ChangeIsDefault'>{{$t(`已阅读并同意`)}}</text>
				<text class="main-color" @click.stop="privacy(4)">{{$t(`《用户协议》`)}}</text>
				{{$t(`与`)}}<text class="main-color" @click.stop="privacy(3)">{{$t(`《隐私协议》`)}}</text>
			</checkbox-group>
		</view>
		<Verify @success="success" :captchaType="'clickWord'" :imgSize="{ width: '330px', height: '155px' }"
			ref="verify"></Verify>
		<editUserModal :isShow="isShow" @closeEdit="closeEdit" @editSuccess="editSuccess">
		</editUserModal>
		<!-- #ifdef MP -->
		<privacyAgreementPopup v-if="canGetPrivacySetting" @onReject="onReject" @onAgree="onAgree">
		</privacyAgreementPopup>
		<!-- #endif -->

	</view>
</template>

<script>
	const app = getApp();
	let statusBarHeight = uni.getSystemInfoSync().statusBarHeight + 'px';
	import sendVerifyCode from "@/mixins/SendVerifyCode";
	import colors from '@/mixins/color.js';
	import editUserModal from '@/components/eidtUserModal/index.vue'
	import privacyAgreementPopup from '@/components/privacyAgreementPopup/index.vue'
	import {
		bindingUserPhone,
		verifyCode,
		registerVerify,
		updatePhone
	} from '@/api/api.js';
	import {
		loginMobile,
		getCodeApi,
		getUserInfo,
		phoneSilenceAuth
	} from "@/api/user.js";
	import {
		phoneLogin,
		wechatBindingPhone
	} from '@/api/public.js'
	import Routine from '@/libs/routine';
	import Verify from '../components/verify/index.vue';
	import Cache from '@/utils/cache';
	export default {
		mixins: [sendVerifyCode, colors],
		components: {
			Verify,
			editUserModal,
			privacyAgreementPopup
		},
		data() {
			return {
				statusBarHeight: statusBarHeight,
				pageType: 1, // 0 登录 1 绑定手机
				phone: '',
				captcha: '',
				text: '获取验证码',
				isShow: false,
				protocol: false,
				inAnimation: false,
				authKey: "",
				backUrl: "",
				pageTitle: '绑定手机号',
				configData: Cache.get('BASIC_CONFIG'),
				canGetPrivacySetting: false,
			}
		},
		onLoad(options) {
			if (options.authKey) {
				this.authKey = options.authKey
			}
			// #ifdef MP
			if (wx.getPrivacySetting) {
				this.canGetPrivacySetting = true
			}
			// #endif

			this.backUrl = options.backUrl || ''
			if (options.pageType) {
				this.pageType = options.pageType || 1
				this.pageTitle = options.pageType == 1 ? '绑定手机号' : '手机号登录'
			}
			let pages = getCurrentPages();
			let prePage = pages[pages.length - 2];
			if (prePage && prePage.route == 'pages/order_addcart/order_addcart') {
				this.isHome = true;
			} else {
				this.isHome = false;
			}
		},
		methods: {
			onAgree() {
				this.protocol = true
			},
			submitData() {
				let that = this;
				if (this.pageType == 0) {
					this.isLogin()
					return
				}
				if (!this.rules()) return
				if (!this.authKey) {
					let key = this.$Cache.get('snsapiKey');
					this.phoneAuth(key)
				} else {
					this.phoneAuth(this.authKey)
				}
			},
			rules() {
				let that = this;
				if (!this.protocol && this.pageType == 0) {
					uni.showToast({
						title: this.$t('请先阅读并同意协议'),
						icon: 'none',
						duration: 2000
					});
					return false
				}
				if (!that.phone) {
					that.$util.Tips({
						title: that.$t(`请填写手机号码`)
					});
					return false
				}
				if (!(/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.phone))) {
					that.$util.Tips({
						title: that.$t(`请输入正确的手机号码`)
					});
					return false
				}
				if (!that.captcha) {
					return that.$util.Tips({
						title: that.$t(`请填写验证码`)
					});
					return false
				}
				return true
			},
			isLogin() {
				if (!this.rules()) return

				uni.showLoading({
					title: this.$t(`正在登录中`)
				});
				Routine.getCode()
					.then(code => {
						phoneLogin({
								code,
								spread_spid: app.globalData.spid,
								spread_code: app.globalData.code,
								phone: this.phone,
								captcha: this.captcha,
							}).then(res => {
								uni.hideLoading();
								let time = res.data.expires_time - this.$Cache.time();
								this.$store.commit('LOGIN', {
									token: res.data.token,
									time: time
								});
								this.getUserInfo(res.data.bindName);
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
			phoneAuth(key) {
				uni.showLoading({
					title: this.$t(`正在登录中`)
				});
				let met
				// #ifdef MP
				met = phoneLogin
				// #endif
				// #ifndef MP
				met = wechatBindingPhone
				// #endif
				met({
					phone: this.phone,
					captcha: this.captcha,
					key
				}).then(res => {
					let time = res.data.expires_time - this.$Cache.time();
					this.$store.commit('LOGIN', {
						token: res.data.token,
						time: time
					});
					this.getUserInfo(res.data.bindName);
				}).catch(error => {
					uni.hideLoading()
					this.$util.Tips({
						title: error
					})
				})
			},
			/**
			 * 获取个人用户信息
			 */
			getUserInfo(new_user) {
				let that = this;
				getUserInfo().then(res => {
					uni.hideLoading();
					that.userInfo = res.data;
					that.$store.commit('SETUID', res.data.uid);
					that.$store.commit('UPDATE_USERINFO', res.data);
					if (new_user) {
						this.isShow = true
					} else {
						// #ifdef MP
						that.$util.Tips({
							title: that.$t(`登录成功`),
							icon: 'success'
						}, {
							tab: 3,
							url: this.configData.wechat_auth_switch ? 2 : 1
						});
						// #endif
						// #ifndef MP
						that.$util.Tips({
							title: that.$t(`登录成功`),
							icon: 'success'
						}, {
							tab: 4,
							url: this.backUrl || 'pages/user/index'
						});
						// #endif

					}
				});
			},
			success(data) {
				this.$refs.verify.hide()
				let that = this;
				verifyCode().then(res => {
					registerVerify(that.phone, 'reset', res.data.key, this.captchaType, data.captchaVerification)
						.then(res => {
							that.$util.Tips({
								title: res.msg
							});
							that.sendCode();
						}).catch(err => {
							return that.$util.Tips({
								title: err
							});
						});
				});
			},
			/**
			 * 发送验证码
			 *
			 */
			async code() {
				let that = this;
				if (!that.phone) return that.$util.Tips({
					title: that.$t(`请填写手机号码`)
				});
				if (!(/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.phone))) return that.$util.Tips({
					title: that.$t(`请输入正确的手机号码`)
				});
				this.$refs.verify.show();
				return;
			},
			ChangeIsDefault() {
				this.$set(this, 'protocol', !this.protocol);
			},
			closeEdit() {
				this.isShow = false
				this.$util.Tips({
					title: this.$t(`登录成功`),
					icon: 'success'
				}, {
					tab: 3,
					url: 2
				});
			},
			editSuccess() {
				this.isShow = false
			},
			back() {
				uni.navigateBack({
					delta: this.configData.wechat_auth_switch ? 2 : 1
				})
			},
			privacy(type) {
				uni.navigateTo({
					url: "/pages/users/privacy/index?type=" + type
				})
			},
		}
	}
</script>

<style lang="scss" scoped>
	.wrapper {
		background-color: #fff;
		min-height: 100vh;
		position: relative;

		.bag {
			position: absolute;
			top: 0;
			left: 0;
			width: 750rpx;
			height: 460rpx;
			background: var(--view-linear);
		}

		.page-msg {
			padding-top: 160rpx;
			margin-left: 72rpx;

			.title {
				font-size: 48rpx;
				font-weight: 500;
				color: #333333;
				line-height: 68rpx;
			}

			.tip {
				font-size: 28rpx;
				font-weight: 400;
				color: #333333;
				line-height: 40rpx;
			}
		}

		.page-form {
			width: 606rpx;
			margin: 100rpx auto 0 auto;

			.item {
				width: 100%;
				height: 88rpx;
				background: #F5F5F5;
				border-radius: 45rpx;
				padding: 24rpx 48rpx;
				margin-bottom: 32rpx;

				input {
					width: 100%;
					height: 100%;
					font-size: 32rpx;
				}

				.placeholder {
					color: #BBBBBB;
					font-size: 28rpx;
				}

				input.codeIput {
					width: 300rpx;
				}

				.line {
					width: 2rpx;
					height: 28rpx;
					background: #CCCCCC;
				}

				.code {
					font-size: 28rpx;
					color: var(--view-theme);
					background-color: rgba(255, 255, 255, 0);
				}

				.code.on {
					color: #BBBBBB !important;
				}
			}

			.btn {
				width: 606rpx;
				height: 88rpx;
				background: var(--view-theme);
				border-radius: 200rpx 200rpx 200rpx 200rpx;
				display: flex;
				justify-content: center;
				align-items: center;
				font-size: 32rpx;
				font-family: PingFang SC-Regular, PingFang SC;
				font-weight: 400;
				color: #FFFFFF;
				line-height: 44rpx;
				margin-top: 48rpx;
				letter-spacing: 1px;
			}
		}
	}

	.title-bar {
		position: relative;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 34rpx;
		font-weight: 500;
		color: #333333;
		line-height: 48rpx;
	}

	.icon {
		position: absolute;
		left: 30rpx;
		top: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		width: 80rpx;
		height: 80rpx;

		image {
			width: 35rpx;
			height: 35rpx;
		}
	}

	.protocol {
		position: fixed;
		bottom: 52rpx;
		left: 0;
		width: 100%;
		margin: 0 auto;
		color: #999999;
		font-size: 24rpx;
		line-height: 22rpx;
		text-align: center;
		bottom: calc(52rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		bottom: calc(52rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/

		.main-color {
			color: var(--view-theme);
		}

		.trembling {
			animation: shake 0.6s;
		}
	}

	/deep/ uni-checkbox .uni-checkbox-input {
		width: 28rpx;
		height: 28rpx;
	}

	/deep/ uni-checkbox .uni-checkbox-input.uni-checkbox-input-checked::before {
		font-size: 24rpx;
	}

	/deep/ uni-checkbox .uni-checkbox-wrapper {
		margin-bottom: 1px;
	}

	/*checkbox 选项框大小  */
	/deep/ checkbox .wx-checkbox-input {
		width: 28rpx;
		height: 28rpx;
	}

	/*checkbox选中后样式  */
	/deep/ checkbox .wx-checkbox-input.wx-checkbox-input-checked {
		background: white;
	}

	/*checkbox选中后图标样式  */
	/deep/ checkbox .wx-checkbox-input.wx-checkbox-input-checked::before {
		width: 28rpx;
		height: 28rpx;
		line-height: 28rpx;
		text-align: center;
		font-size: 22rpx;
		background: transparent;
		transform: translate(-50%, -50%) scale(1);
		-webkit-transform: translate(-50%, -50%) scale(1);
	}
</style>
