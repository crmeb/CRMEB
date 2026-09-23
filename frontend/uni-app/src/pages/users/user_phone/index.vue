<template>
	<view :style="colorStyle">
		<form @submit="editPwd">
			<view class="ChangePassword">
				<view class="list">
					<view class="item">
						<input type='number' :placeholder='$t(`填写手机号码`)' placeholder-class='placeholder'
							v-model="phone"></input>
					</view>
					<view class="item acea-row row-between-wrapper">
						<input type='number' :placeholder='$t(`填写验证码`)' placeholder-class='placeholder' class="codeIput"
							v-model="captcha"></input>
						<button class="code font-num" :class="disabled === true ? 'on' : ''" :disabled='disabled'
							@click="code">
							{{ codeText }}
						</button>
					</view>
				</view>
				<button form-type="submit" class="confirmBnt bg-color">{{$t(`确认绑定`)}}</button>
			</view>
		</form>

		<Verify @success="success" :captchaType="captchaType" :imgSize="{ width: '330px', height: '155px' }"
			ref="verifyRef"></Verify>

	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import Verify from '../components/verify/index.vue';
import {
	registerVerify,
	bindingUserPhone,
	verifyCode,
	updatePhone
} from '@/api/api.js';
import {
	toLogin
} from '@/libs/login.js';
import { useColorTheme } from '@/composables/useColorTheme';
import { useSendVerifyCode } from '@/composables/useSendVerifyCode';
import { useAppStore } from '@/store/modules/app';
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
const phone = ref('');
const captcha = ref('');
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const key = ref('');
const authKey = ref('');
const type = ref(0);
// 原代码未在 data 声明、直接挂到实例上的字段
const url = ref('');
const verifyRef = ref(null);

// ---------- 生命周期 ----------
onLoad((options) => {
	if (isLogin.value) {
		verifyCode().then(res => {
			key.value = res.data.key
		});
		authKey.value = options.key || '';
		url.value = options.url || '';
	} else {
		toLogin();
	}
	type.value = options.type || 0
});

// ---------- methods ----------
function onLoadFun() {}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e
}
function editPwd() {
	if (!phone.value) return util.Tips({
		title: i18n.t(`请填写手机号码`)
	});
	if (!(/^1(3|4|5|7|8|9|6)\d{9}$/i.test(phone.value))) return util.Tips({
		title: i18n.t(`请输入正确的手机号码`)
	});
	if (!captcha.value) return util.Tips({
		title: i18n.t(`请填写验证码`)
	});
	if (type.value == 0) {
		bindingUserPhone({
			phone: phone.value,
			captcha: captcha.value
		}).then(res => {
			if (res.data !== undefined && res.data.is_bind) {
				uni.showModal({
					title: i18n.t(`是否绑定账号`),
					content: res.msg,
					confirmText: i18n.t(`绑定`),
					success(res) {
						if (res.confirm) {
							bindingUserPhone({
								phone: phone.value,
								captcha: captcha.value,
								step: 1
							}).then(res => {
								return util.Tips({
									title: res.msg,
									icon: 'success'
								}, {
									tab: 5,
									url: '/pages/users/user_info/index'
								});
							}).catch(err => {
								return util.Tips({
									title: err
								});
							})
						} else if (res.cancel) {
							return util.Tips({
								title: i18n.t(`您已取消绑定！`)
							}, {
								tab: 5,
								url: '/pages/users/user_info/index'
							});
						}
					}
				});
			} else
				return util.Tips({
					title: i18n.t(`绑定成功`),
					icon: 'success'
				}, {
					tab: 5,
					url: '/pages/users/user_info/index'
				});
		}).catch(err => {
			return util.Tips({
				title: err
			});
		})
	} else {
		updatePhone({
			phone: phone.value,
			captcha: captcha.value,
		}).then(res => {
			return util.Tips({
				title: res.msg,
				icon: 'success'
			}, {
				tab: 5,
				url: '/pages/users/user_info/index'
			});
		}).catch(error => {
			return util.Tips({
				title: error,
			});
		})
	}
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
