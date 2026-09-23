<template>
	<div class="register absolute">
		<div class="shading">
			<div class="pictrue acea-row row-center-wrapper">
				<image src="../static/logo2.png" />
			</div>
		</div>
		<div class="whiteBg">
			<div class="title">{{$t(`找回密码`)}}</div>
			<div class="list">
				<div class="item">
					<div class="acea-row row-middle">
						<image src="../static/phone_1.png"></image>
						<input type="text" :placeholder="$t(`输入手机号码`)" v-model="account" />
					</div>
				</div>
				<div class="item">

					<div class="acea-row row-middle">
						<image src="../static/code_2.png"></image>
						<input type="text" :placeholder="$t(`填写验证码`)" class="codeIput" v-model="captcha" />
						<button class="code" :disabled="disabled" :class="disabled === true ? 'on' : ''" @click="code">
							{{ codeText }}
						</button>
					</div>
				</div>
				<div class="item">
					<div class="acea-row row-middle">
						<image src="../static//code_2.png"></image>
						<input type="password" :placeholder="$t(`填写您的新密码`)" v-model="password" />
					</div>
				</div>
				<div class="item" v-if="isShowCode">
					<div class="align-left">
						<input type="text" :placeholder="$t(`填写验证码`)" class="codeIput" v-model="codeVal" />
						<div class="code" @click="again"><img :src="codeUrl" /></div>
					</div>
				</div>
			</div>
			<div class="logon" @click="registerReset">{{$t(`确认`)}}</div>
			<div class="tip">
				<span class="font-color-red" @click="back">{{$t(`立即登录`)}}</span>
			</div>
		</div>
		<div class="bottom"></div>
	</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { registerVerify, registerReset as registerResetApi, getCodeApi } from "@/api/user";
import { useSendVerifyCode } from "@/composables/useSendVerifyCode";
import util from "@/utils/util.js";
import i18n from "@/utils/lang.js";
// import { validatorDefaultCatch } from "@/utils/dialog";
// import attrs, { required, alpha_num, chs_phone } from "@utils/validate";
// import { VUE_APP_API_URL } from "@utils";

// ---------- 验证码倒计时（原 SendVerifyCode mixin） ----------
const { disabled, codeText, sendCode } = useSendVerifyCode();

// ---------- data ----------
const account = ref("");
const password = ref("");
const captcha = ref("");
const keyCode = ref("");
const codeUrl = ref("");
const codeVal = ref("");
const isShowCode = ref(false);
// 原代码引用了未在 data 声明的 formItem/type（实例属性，恒为 undefined），为保持行为一致声明占位
const formItem = ref(undefined);
const type = ref(undefined);

// ---------- 生命周期 ----------
onMounted(() => {
	getCode();
});

// ---------- methods ----------
function back() {
	uni.navigateBack();
}
function again() {
	codeUrl.value =
		VUE_APP_API_URL + "/captcha?" + keyCode.value + Date.parse(new Date());
}
function getCode() {
	getCodeApi()
		.then(res => {
			keyCode.value = res.data.key;
		})
		.catch(res => {
			util.Tips({
				title: res.msg.msg || i18n.t(`加载失败`)
			})
		});
}
async function registerReset() {
	if (!account.value) return util.Tips({
		title: i18n.t(`请填写手机号码`)
	});
	if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(account.value)) return util.Tips({
		title: i18n.t(`请输入正确的手机号码`)
	});
	if (!captcha.value) return util.Tips({
		title: i18n.t(`请填写验证码`)
	});
	registerResetApi({
			account: account.value,
			captcha: captcha.value,
			password: password.value,
			code: codeVal.value
		})
		.then(res => {
			util.Tips({
				title: res.msg
			}, {
				tab: 3
			})
		})
		.catch(res => {
			util.Tips({
				title: res
			})
		});
}
async function code() {
	if (!account.value) return util.Tips({
		title: i18n.t(`请填写手机号码`)
	});
	if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(account.value)) return util.Tips({
		title: i18n.t(`请输入正确的手机号码`)
	});
	if (formItem.value == 2) type.value = "register";
	await registerVerify({
			phone: account.value,
			type: type.value,
			key: keyCode.value,
			code: codeVal.value
		})
		.then(res => {
			util.Tips({
				title: res.msg || i18n.t(`加载失败`)
			})
			sendCode();
		})
		.catch(res => {
			util.Tips({
				title: res
			});
		});
}
</script>
<style scoped>
	.code img {
		width: 100%;
		height: 100%;
	}
</style>
