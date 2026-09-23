<!-- #ifdef H5 -->
<template>
	<view v-if="bindPhone">
		<form @submit="editPwd" report-submit='true'>
			<view class="ChangePassword">
				<view class="list">
					<view class="item">
						<input type='number' :placeholder='$t(`请输入手机号`)' placeholder-class='placeholder'
							v-model="phone"></input>
					</view>
					<view class="item acea-row row-between-wrapper">
						<input type='number' :placeholder='$t(`填写验证码`)' placeholder-class='placeholder' class="codeIput"
							v-model="captcha"></input>
						<button class="code font-color" :class="disabled === true ? 'on' : ''" :disabled='disabled'
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
	<view class="lottie-bg" v-else>
		<view id="lottie">
		</view>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import wechat from "@/libs/wechat";
import {
	getUserInfo
} from "@/api/user";
import {
	WX_AUTH,
	STATE_KEY,
	LOGINTYPE,
	BACK_URL
} from '@/config/cache';
import {
	silenceAuth
} from '@/api/public';
import {
	bindingPhone,
	verifyCode
} from '@/api/api.js';
import {
	registerVerify
} from '@/api/user.js'
import Verify from '../components/verify/index.vue';
import { useSendVerifyCode } from '@/composables/useSendVerifyCode';
import { useAppStore } from '@/store/modules/app';
import Cache from '@/utils/cache';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';

const { disabled, codeText, captchaType, sendCode } = useSendVerifyCode();
const appStore = useAppStore();

// ---------- data ----------
const phone = ref('');
const captcha = ref('');
const key = ref('');
const authKey = ref('');
const scope = ref('');
const bindPhone = ref(false);
// 原代码未在 data 声明、直接挂到实例上的字段
const url = ref('');
const verifyRef = ref(null);

// ---------- 生命周期 ----------
onLoad((options) => {
	const {
		code,
		state,
		scope
	} = options;
	if (scope === 'snsapi_base') {
		url.value = options.url || options.back_url || '';
		let spread = Cache.get("spread");
		let loginType = Cache.get(LOGINTYPE);
		// silenceAuth({
		// 	code: options.code || '',
		// 	spread: spread
		// }).then(res => {
		// 	if (res.data.key !== undefined) {
		// 		this.bindPhone = true;
		// 		this.authKey = res.data.key;
		// 	} else {
		// 		this.$store.commit("LOGIN", {
		// 			token: res.data.token,
		// 			time: parseInt(res.data.expires_time) - this.$Cache.time()
		// 		});
		// 		this.$Cache.set(WX_AUTH, options.code);
		// 		this.$Cache.clear(STATE_KEY);
		// 		loginType && this.$Cache.clear(LOGINTYPE);
		// 		location.href = decodeURIComponent(
		// 			decodeURIComponent(options.back_url)
		// 		);
		// 	}
		// })
	} else {
		wechat.auth(code, state).then(() => {
			location.href = decodeURIComponent(
				decodeURIComponent(options.back_url)
			);
			getUserInfo().then(res => {
				appStore.SETUID(res.data.uid);
			});
		}).catch(() => {
			location.replace("/");
		});
	}
});

// ---------- methods ----------
function editPwd() {
	if (!phone.value) {
		return util.Tips({
			title: i18n.t(`请输入手机号`)
		});
	}
	if (!(/^1(3|4|5|7|8|9|6)\d{9}$/i.test(phone.value))) {
		return util.Tips({
			title: i18n.t(`请输入正确的手机号码`)
		});
	}
	if (!captcha.value) {
		return util.Tips({
			title: i18n.t(`填写验证码`)
		});
	}
	bindingPhone({
		phone: phone.value,
		captcha: captcha.value,
		key: authKey.value
	}).then(res => {
		let time = res.data.expires_time - Cache.time();
		appStore.LOGIN({
			token: res.data.token,
			time: time
		});
		if (url.value && url.value.indexOf('http') !== -1) {
			location.href = url.value;
		} else {
			return util.Tips({
				title: i18n.t(`绑定成功`),
				icon: 'success'
			}, {
				tab: 4,
				url: '/pages/index/index'
			});
		}
	}).catch(err => {
		return util.Tips({
			title: err
		});
	})
}
/**
 * 发送验证码
 *
 */
function code() {
	if (!phone.value) return util.Tips({
		title: i18n.t(`请输入手机号`)
	});
	if (!(/^1(3|4|5|7|8|9|6)\d{9}$/i.test(phone.value))) return util.Tips({
		title: i18n.t(`请输入正确的手机号码`)
	});
	verifyRef.value.show()
}
function success(data) {
	verifyRef.value.hide()
	verifyCode().then(res => {
		registerVerify({
			// 原代码此处误用未定义的 that.phone，修正为直接读取
			phone: phone.value,
			type: 'reset',
			key: res.data.key,
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
	});
}
</script>

<style scoped lang="scss">
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

	.lottie-bg {
		position: fixed;
		left: 0;
		top: 0;
		background-color: #fff;
		width: 100%;
		height: 100%;
		z-index: 999;

		display: flex;
		align-items: center;
		justify-content: center;
	}

	#lottie {
		display: block;
		width: 100%;
		height: 100%;
		display: flex;
		align-items: center;
		justify-content: center;
		overflow: hidden;
		transform: translate3d(0, 0, 0);
		margin: auto;

		image {
			width: 200rpx;
			height: 200rpx;
		}
	}
</style>
<!-- #endif -->
