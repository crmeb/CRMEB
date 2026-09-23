<template>
	<view :style="colorStyle">
		<view class="ChangePassword">
			<form @submit="editPwd">
				<view class="phone">{{$t(`当前手机号`)}}：{{phone}}</view>
				<view class="list">
					<view class="item">
						<input type='password' :placeholder='$t(`设置新密码`)' placeholder-class='placeholder'
							name="password" :value="password"></input>
					</view>
					<view class="item">
						<input type='password' :placeholder='$t(`确认新密码`)' placeholder-class='placeholder'
							name="qr_password" :value="qr_password"></input>
					</view>
					<view class="item acea-row row-between-wrapper">
						<input type='number' :placeholder='$t(`填写验证码`)' placeholder-class='placeholder' class="codeIput"
							name="captcha" :value="captcha"></input>
						<button class="code font-num" :class="disabled === true ? 'on' : ''" :disabled='disabled'
							@click="code">
							{{ codeText }}
						</button>
					</view>
				</view>
				<button form-type="submit" class="confirmBnt bg-color">{{$t(`确认修改`)}}</button>
			</form>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<Verify @success="success" :captchaType="captchaType" :imgSize="{ width: '330px', height: '155px' }"
			ref="verifyRef"></Verify>
	</view>
</template>

<script setup>
import { ref, watch } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
	phoneRegisterReset,
	verifyCode
} from '@/api/api.js';
import {
	getUserInfo as getUserInfoApi,
	registerVerify
} from '@/api/user.js';
import {
	toLogin
} from '@/libs/login.js';
import { useColorTheme } from '@/composables/useColorTheme';
import { useSendVerifyCode } from '@/composables/useSendVerifyCode';
import { useAppStore } from '@/store/modules/app';
import Verify from '../components/verify/index.vue';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif

const { colorStyle } = useColorTheme();
const { disabled, codeText, captchaType, sendCode } = useSendVerifyCode();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const userInfo = ref({});
const phone = ref('');
const password = ref('');
const captcha = ref('');
const qr_password = ref('');
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const key = ref('');
const verifyRef = ref(null);

// ---------- watch ----------
watch(isLogin, (newV, oldV) => {
	if (newV) {
		getUserInfo();
	}
}, { deep: true });

// ---------- 生命周期 ----------
onLoad(() => {
	if (isLogin.value) {
		getUserInfo();
		verifyCode().then(res => {
			key.value = res.data.key
		});
	} else {
		toLogin()
	}
});

// ---------- methods ----------
/**
 * 授权回调
 */
function onLoadFun(e) {
	getUserInfo();
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e
}
/**
 * 获取个人用户信息
 */
function getUserInfo() {
	getUserInfoApi().then(res => {
		let tel = res.data.phone;
		let phoneStr = tel.substr(0, 3) + "****" + tel.substr(7);
		userInfo.value = res.data;
		phone.value = phoneStr;
	});
}
/**
 * 发送验证码
 *
 */
async function code() {
	if (!userInfo.value.phone) return util.Tips({
		title: i18n.t(`手机号码不存在,无法发送验证码！`)
	});
	verifyRef.value.show()

}
async function success(data) {
	verifyRef.value.hide()
	await registerVerify({
		phone: userInfo.value.phone,
		type: 'reset',
		key: key.value,
		captchaType: captchaType,
		captchaVerification: data.captchaVerification
	}).then(res => {
		sendCode()
		util.Tips({
			title: res.msg
		});
	}).catch(err => {
		return util.Tips({
			title: err
		});
	});
}
/**
 * H5登录 修改密码
 *
 */
function editPwd(e) {
	let passwordVal = e.detail.value.password,
		qr_passwordVal = e.detail.value.qr_password,
		captchaVal = e.detail.value.captcha;
	if (!passwordVal) return util.Tips({
		title: i18n.t(`请输入新密码`)
	});
	if (qr_passwordVal != passwordVal) return util.Tips({
		title: i18n.t(`两次输入的密码不一致！`)
	});
	if (!captchaVal) return util.Tips({
		title: i18n.t(`请输入验证码`)
	});
	phoneRegisterReset({
		account: userInfo.value.phone,
		captcha: captchaVal,
		password: passwordVal
	}).then(res => {
		return util.Tips({
			title: res.msg
		}, {
			tab: 3,
			url: 1
		});
	}).catch(err => {
		return util.Tips({
			title: err
		});
	});
}
</script>

<style lang="scss">
	page {
		background-color: #fff !important;
	}

	.ChangePassword .phone {
		font-size: 32rpx;
		font-weight: bold;
		text-align: center;
		margin-top: 55rpx;
	}

	.ChangePassword .list {
		width: 580rpx;
		margin: 53rpx auto 0 auto;
	}

	.ChangePassword .list .item {
		width: 100%;
		height: 110rpx;
		border-bottom: 2rpx solid #f0f0f0;
	}

	.ChangePassword .list .item input {
		width: 100%;
		height: 100%;
		font-size: 32rpx;
	}

	.ChangePassword .list .item .placeholder {
		color: #b9b9bc;
	}

	.ChangePassword .list .item input.codeIput {
		width: 340rpx;
	}

	.ChangePassword .list .item .code {
		font-size: 32rpx;
		background-color: #fff;
	}

	.ChangePassword .list .item .code.on {
		color: #b9b9bc !important;
	}

	.ChangePassword .confirmBnt {
		font-size: 32rpx;
		width: 580rpx;
		height: 90rpx;
		border-radius: 45rpx;
		color: #fff;
		margin: 92rpx auto 0 auto;
		text-align: center;
		line-height: 90rpx;
	}
</style>
