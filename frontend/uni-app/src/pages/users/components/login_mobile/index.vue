<template>
	<view v-if="isUp">
		<view class="mobile-bg" @click="close"></view>
		<view class="mobile-mask animated" :class="{slideInUp:isUp}">
			<view class="input-item">
				<input type="text" v-model="account" :placeholder="$t(`输入手机号`)" maxlength="11" />
			</view>
			<view class="input-item">
				<input type="text" v-model="codeNum" :placeholder="$t(`输入验证码`)" maxlength="6" />
				<button class="code" :disabled="disabled" @click="code">{{codeText}}</button>
			</view>
			<view class="sub_btn" @click="loginBtn">{{$t(`立即登录`)}}</view>
		</view>

		<Verify @success="success" :captchaType="captchaType" :imgSize="{ width: '330px', height: '155px' }"
			ref="verifyRef"></Verify>
	</view>
</template>

<script setup>
const app = getApp();
import { ref, onMounted } from 'vue';
import Routine from '@/libs/routine';
import Verify from '../verify/index.vue';
import Cache from '@/utils/cache';
import {
	loginMobile,
	registerVerify,
	getCodeApi,
	getUserInfo as getUserInfoApi,
	phoneSilenceAuth,
	phoneWxSilenceAuth
} from "@/api/user";
import {
	bindingPhone
} from '@/api/api.js'
import {
	silenceAuth
} from '@/api/public.js'
import { useSendVerifyCode } from '@/composables/useSendVerifyCode';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';

defineOptions({ name: 'login_mobile' });

const props = defineProps({
	isUp: {
		type: Boolean,
		default: false,
	},
	canClose: {
		type: Boolean,
		default: true,
	},
	authKey: {
		type: String,
		default: '',
	}
});

const emit = defineEmits(['close', 'wechatPhone']);

const { disabled, codeText, captchaType, sendCode } = useSendVerifyCode();
const appStore = useAppStore();

// ---------- data ----------
const keyCode = ref('');
const account = ref('');
const codeNum = ref('');
// 原代码未在 data 声明、直接挂到实例上的字段
const userInfo = ref({});
const verifyRef = ref(null);

// ---------- 生命周期 ----------
onMounted(() => {
	getCode();
});

// ---------- methods ----------
function success(data) {
	verifyRef.value.hide()
	getCodeApi().then(res => {
		registerVerify({
			phone: account.value,
			key: res.data.key,
			captchaType: captchaType,
			captchaVerification: data.captchaVerification
		}).then(res => {
			util.Tips({
				title: res.msg
			});
			sendCode();
		}).catch(err => {
			return util.Tips({
				title: err
			})
		})
	})
}
// 获取验证码
function code() {
	if (!account.value) return util.Tips({
		title: i18n.t(`请填写手机号码`)
	});
	if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(account.value)) return util.Tips({
		title: i18n.t(`请输入正确的手机号码`)
	});
	verifyRef.value.show();
}
// 获取验证码api
function getCode() {
	getCodeApi().then(res => {
		keyCode.value = res.data.key;
	}).catch(res => {
		util.Tips({
			title: res
		});
	});
}
function close(new_user) {
	if (props.canClose) {
		emit('close', new_user)
	}
}
// 登录
function loginBtn() {
	// #ifdef MP
	if (!account.value) return util.Tips({
		title: i18n.t(`请填写手机号码`)
	});
	if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(account.value)) return util.Tips({
		title: i18n.t(`请输入正确的手机号码`)
	});
	if (!codeNum.value) return util.Tips({
		title: i18n.t(`请填写验证码`)
	});
	if (!/^[\w\d]+$/i.test(codeNum.value)) return util.Tips({
		title: i18n.t(`请输入正确的验证码`)
	});
	uni.showLoading({
		title: i18n.t(`正在登录中`)
	});
	Routine.getCode()
		.then(code => {
			phoneSilenceAuthFn(code);
		})
		.catch(error => {
			uni.hideLoading();
		});
	// #endif
	// #ifdef H5
	if (!account.value) return util.Tips({
		title: i18n.t(`请填写手机号码`)
	});
	if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(account.value)) return util.Tips({
		title: i18n.t(`请输入正确的手机号码`)
	});
	if (!codeNum.value) return util.Tips({
		title: i18n.t(`请填写验证码`)
	});
	if (!/^[\w\d]+$/i.test(codeNum.value)) return util.Tips({
		title: i18n.t(`请输入正确的验证码`)
	});
	uni.showLoading({
		title: i18n.t(`正在登录中`)
	});
	if (!props.authKey) {
		let key = Cache.get('snsapiKey');
		phoneAuth(key)
	} else {
		phoneAuth(props.authKey)
	}
	// #endif
}
function phoneAuth(key) {
	phoneWxSilenceAuth({
		spid: app.globalData.spid,
		spread: app.globalData.code,
		phone: account.value,
		captcha: codeNum.value,
		key
	}).then(res => {
		let time = res.data.expires_time - Cache.time();
		appStore.LOGIN({
			token: res.data.token,
			time: time
		});
		getUserInfo();
	}).catch(error => {
		uni.hideLoading()
		util.Tips({
			title: error
		})
	})
}
// #ifdef MP
function phoneSilenceAuthFn(code) {
	phoneSilenceAuth({
		code: code,
		spread_spid: app.globalData.spid,
		spread_code: app.globalData.code,
		phone: account.value,
		captcha: codeNum.value
	}).then(res => {
		let time = res.data.expires_time - Cache.time();
		appStore.LOGIN({
			token: res.data.token,
			time: time
		});
		getUserInfo(res.data.new_user);
	}).catch(error => {
		util.Tips({
			title: error
		})
	})
}
// #endif
/**
 * 获取个人用户信息
 */
function getUserInfo(new_user) {
	getUserInfoApi().then(res => {
		uni.hideLoading();
		userInfo.value = res.data
		appStore.SETUID(res.data.uid);
		appStore.UPDATE_USERINFO(res.data);
		// #ifdef MP
		if (!new_user) {
			util.Tips({
				title: i18n.t(`登录成功`),
				icon: 'success'
			}, {
				tab: 3
			})
		} else {
			util.Tips({
				title: i18n.t(`登录成功`),
				icon: 'success'
			})
		}
		close(new_user || 0)
		// #endif
		// #ifdef H5
		emit('wechatPhone', true)
		// #endif
	});
}
</script>

<style lang="stylus">
	.mobile-bg {
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		background: rgba(0, 0, 0, 0.5);
	}

	.mobile-mask {
		z-index: 20;
		position: fixed;
		left: 0;
		bottom: 0;
		width: 100%;
		padding: 67rpx 30rpx;
		background: #fff;

		.input-item {
			display: flex;
			justify-content: space-between;
			width: 100%;
			height: 86rpx;
			margin-bottom: 38rpx;

			input {
				flex: 1;
				display: block;
				height: 100%;
				padding-left: 40rpx;
				border-radius: 43rpx;
				border: 1px solid #DCDCDC;
			}

			.code {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 220rpx;
				height: 86rpx;
				margin-left: 30rpx;
				background: var(--view-minorColorT);
				font-size: 28rpx;
				color: var(--view-theme);
				border-radius: 43rpx;

				&[disabled] {
					background: rgba(0, 0, 0, 0.05);
					color: #999;
				}
			}
		}

		.sub_btn {
			width: 690rpx;
			height: 86rpx;
			line-height: 86rpx;
			margin-top: 60rpx;
			background: var(--view-theme);
			border-radius: 43rpx;
			color: #fff;
			font-size: 28rpx;
			text-align: center;
		}
	}

	.animated {
		animation-duration: .4s
	}
</style>
