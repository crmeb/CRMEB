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
					{{ $t(codeText) }}
				</button>
			</view>
			<view class="btn" @click="submitData">
				{{$t(`${pageType == 1 ? '绑定手机号' : '立即登录'}`)}}
			</view>
		</view>
		<view class="protocol" v-if="pageType == 0 && !canGetPrivacySetting">
			<checkbox-group @click.stop='ChangeIsDefault'>
				<checkbox :class="inAnimation?'trembling':''" @animationend='inAnimation=false'
					:checked="protocol ? true : false" backgroundColor="#ffffff" activeBackgroundColor="var(--view-theme, #E93323)" activeBorderColor="var(--view-theme, #E93323)" iconColor="#ffffff" /> <text @click.stop='ChangeIsDefault'>{{$t(`已阅读并同意`)}}</text>
				<text class="main-color" @click.stop="privacy(4)">{{$t(`《用户协议》`)}}</text>
				{{$t(`与`)}}<text class="main-color" @click.stop="privacy(3)">{{$t(`《隐私协议》`)}}</text>
			</checkbox-group>
		</view>
		<Verify @success="success" :captchaType="captchaType" :imgSize="{ width: '330px', height: '155px' }"
			ref="verifyRef"></Verify>
		<editUserModal :isShow="isShow" @closeEdit="closeEdit" @editSuccess="editSuccess">
		</editUserModal>
		<!-- #ifdef MP -->
		<privacyAgreementPopup v-if="canGetPrivacySetting" @onReject="onReject" @onAgree="onAgree">
		</privacyAgreementPopup>
		<!-- #endif -->

	</view>
</template>

<script setup>
const app = getApp();
let statusBarHeight = uni.getWindowInfo().statusBarHeight + 'px';
import { ref } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import editUserModal from '@/components/editUserModal/index.vue'
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
	getUserInfo as getUserInfoApi,
	phoneSilenceAuth
} from "@/api/user.js";
import {
	phoneLogin,
	wechatBindingPhone
} from '@/api/public.js'
import Routine from '@/libs/routine';
import Verify from '../components/verify/index.vue';
import Cache from '@/utils/cache';
import { useColorTheme } from '@/composables/useColorTheme';
import { useSendVerifyCode } from '@/composables/useSendVerifyCode';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';

const { colorStyle } = useColorTheme();
const { disabled, codeText, captchaType, sendCode } = useSendVerifyCode();
// 原 data.text = '获取验证码' 覆盖了 mixin 的初始文案，保持一致
codeText.value = '获取验证码';
const appStore = useAppStore();

// ---------- data ----------
const pageType = ref(1); // 0 登录 1 绑定手机
const phone = ref('');
const captcha = ref('');
const isShow = ref(false);
const protocol = ref(false);
const inAnimation = ref(false);
const authKey = ref("");
const backUrl = ref("");
const pageTitle = ref('绑定手机号');
const configData = Cache.get('BASIC_CONFIG');
const canGetPrivacySetting = ref(false);
// 原代码未在 data 声明、直接挂到实例上的字段
const userInfo = ref({});
const isHome = ref(false);
const verifyRef = ref(null);

// ---------- 生命周期 ----------
onLoad((options) => {
	if (options.authKey) {
		authKey.value = options.authKey
	}
	// #ifdef MP
	if (wx.getPrivacySetting) {
		canGetPrivacySetting.value = true
	}
	// #endif

	backUrl.value = options.backUrl || ''
	if (options.pageType) {
		pageType.value = options.pageType || 1
		pageTitle.value = options.pageType == 1 ? '绑定手机号' : '手机号登录'
	}
	let pages = getCurrentPages();
	let prePage = pages[pages.length - 2];
	if (prePage && prePage.route == 'pages/order_addcart/order_addcart') {
		isHome.value = true;
	} else {
		isHome.value = false;
	}
});

// ---------- methods ----------
function onAgree() {
	protocol.value = true
}
function submitData() {
	if (pageType.value == 0) {
		isLoginFn()
		return
	}
	if (!rules()) return
	if (!authKey.value) {
		let key = Cache.get('snsapiKey');
		phoneAuth(key)
	} else {
		phoneAuth(authKey.value)
	}
}
function rules() {
	if (!protocol.value && pageType.value == 0) {
		uni.showToast({
			title: i18n.t('请先阅读并同意协议'),
			icon: 'none',
			duration: 2000
		});
		return false
	}
	if (!phone.value) {
		util.Tips({
			title: i18n.t(`请填写手机号码`)
		});
		return false
	}
	if (!(/^1(3|4|5|7|8|9|6)\d{9}$/i.test(phone.value))) {
		util.Tips({
			title: i18n.t(`请输入正确的手机号码`)
		});
		return false
	}
	if (!captcha.value) {
		return util.Tips({
			title: i18n.t(`请填写验证码`)
		});
		return false
	}
	return true
}
function isLoginFn() {
	if (!rules()) return

	uni.showLoading({
		title: i18n.t(`正在登录中`)
	});
	Routine.getCode()
		.then(code => {
			phoneLogin({
					code,
					spread_spid: app.globalData.spid,
					spread_code: app.globalData.code,
					phone: phone.value,
					captcha: captcha.value,
				}).then(res => {
					uni.hideLoading();
					let time = res.data.expires_time - Cache.time();
					appStore.LOGIN({
						token: res.data.token,
						time: time
					});
					getUserInfo(res.data.bindName);
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
}
function phoneAuth(key) {
	uni.showLoading({
		title: i18n.t(`正在登录中`)
	});
	let met
	// #ifdef MP
	met = phoneLogin
	// #endif
	// #ifndef MP
	met = wechatBindingPhone
	// #endif
	met({
		phone: phone.value,
		captcha: captcha.value,
		key
	}).then(res => {
		let time = res.data.expires_time - Cache.time();
		appStore.LOGIN({
			token: res.data.token,
			time: time
		});
		getUserInfo(res.data.bindName);
	}).catch(error => {
		uni.hideLoading()
		util.Tips({
			title: error
		})
	})
}
/**
 * 获取个人用户信息
 */
function getUserInfo(new_user) {
	getUserInfoApi().then(res => {
		uni.hideLoading();
		userInfo.value = res.data;
		appStore.SETUID(res.data.uid);
		appStore.UPDATE_USERINFO(res.data);
		if (new_user) {
			isShow.value = true
		} else {
			// #ifdef MP
			util.Tips({
				title: i18n.t(`登录成功`),
				icon: 'success'
			}, {
				tab: 3,
				url: configData.wechat_auth_switch ? 2 : 1
			});
			// #endif
			// #ifndef MP
			util.Tips({
				title: i18n.t(`登录成功`),
				icon: 'success'
			}, {
				tab: 4,
				url: backUrl.value || 'pages/user/index'
			});
			// #endif

		}
	});
}
function success(data) {
	verifyRef.value.hide()
	verifyCode().then(res => {
		registerVerify(phone.value, 'reset', res.data.key, captchaType, data.captchaVerification)
			.then(res => {
				util.Tips({
					title: res.msg
				});
				sendCode();
			}).catch(err => {
				return util.Tips({
					title: err
				});
			});
	});
}
/**
 * 发送验证码
 *
 */
async function code() {
	if (!phone.value) return util.Tips({
		title: i18n.t(`请填写手机号码`)
	});
	if (!(/^1(3|4|5|7|8|9|6)\d{9}$/i.test(phone.value))) return util.Tips({
		title: i18n.t(`请输入正确的手机号码`)
	});
	verifyRef.value.show();
	return;
}
function ChangeIsDefault() {
	protocol.value = !protocol.value;
}
function closeEdit() {
	isShow.value = false
	util.Tips({
		title: i18n.t(`登录成功`),
		icon: 'success'
	}, {
		tab: 3,
		url: 2
	});
}
function editSuccess() {
	isShow.value = false
}
function back() {
	uni.navigateBack({
		delta: configData.wechat_auth_switch ? 2 : 1
	})
}
function privacy(type) {
	uni.navigateTo({
		url: "/pages/users/privacy/index?type=" + type
	})
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

:deep(uni-checkbox .uni-checkbox-input){
		width: 28rpx;
		height: 28rpx;
	}

:deep(uni-checkbox .uni-checkbox-input.uni-checkbox-input-checked::before){
		font-size: 24rpx;
	}

:deep(uni-checkbox .uni-checkbox-wrapper){
		margin-bottom: 1px;
	}

	/*checkbox 选项框大小  */
:deep(checkbox .wx-checkbox-input){
		width: 28rpx;
		height: 28rpx;
	}

	/*checkbox选中后样式  */
:deep(checkbox .wx-checkbox-input.wx-checkbox-input-checked){
		background: white;
	}

	/*checkbox选中后图标样式  */
:deep(checkbox .wx-checkbox-input.wx-checkbox-input-checked::before){
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
