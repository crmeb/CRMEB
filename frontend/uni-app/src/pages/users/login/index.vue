<template>
	<view class="login-wrapper" :style="colorStyle">
		<view class="shading">
			<image :src="logoUrl" />
			<view v-if="configData && configData.site_name" class="name">
				{{ configData.site_name }}
			</view>
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
				<!-- <navigator :render-link="false" class="forgetPwd" hover-class="none" url="/pages/users/retrievePassword/index">
					<span class="iconfont icon-wenti"></span>{{$t(`忘记密码`)}}
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
							{{ codeText }}
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
				<!-- 用 detail.value 同步勾选，避免仅取反与原生勾选状态打架 -->
				<checkbox-group @change="ChangeIsDefault">
					<checkbox value="1" :class="inAnimation ? 'trembling' : ''" @animationend="inAnimation = false" :checked="protocol" backgroundColor="#ffffff" activeBackgroundColor="var(--view-theme, #E93323)" activeBorderColor="var(--view-theme, #E93323)" iconColor="#ffffff" />
					{{ $t(`已阅读并同意`) }}
					<text class="main-color" @click.stop="privacy(4)">{{ $t(`《用户协议》`) }}</text>
					{{ $t(`与`) }}
					<text class="main-color" @click.stop="privacy(3)">{{ $t(`《隐私协议》`) }}</text>
				</checkbox-group>
			</view>
		</view>
		<view class="bottom">
			<view class="ver" v-if="copyRight">{{ copyRight }}</view>
			<view v-else class="ver">
				<a href="https://www.crmeb.com">Copyright © 2016~2026 CRMEB. All Rights</a>
			</view>
		</view>
		<Verify @success="success" :captchaType="captchaType" :imgSize="{ width: '330px', height: '155px' }" ref="verifyRef"></Verify>
	</view>
</template>
<script setup>
import dayjs from '@/plugin/dayjs/dayjs.min.js';
import { HTTP_REQUEST_URL } from '@/config/app';
import { ref, watch, onMounted } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import { loginH5, loginMobile as loginMobileApi, registerVerify, register as registerApi, getCodeApi, getUserInfo, appleLogin as appleLoginRequest } from '@/api/user';
import { getLogo } from '@/api/public';
// import cookie from "@/utils/store/cookie";
// #ifdef APP-PLUS
import { wechatAppAuth } from '@/api/api.js';
// #endif
const BACK_URL = 'login_back_url';
import Verify from '../components/verify/index.vue';
import Cache from '@/utils/cache';
import { useColorTheme } from '@/composables/useColorTheme';
import { useSendVerifyCode } from '@/composables/useSendVerifyCode';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';

const { colorStyle } = useColorTheme();
const { disabled, codeText, captchaType, sendCode } = useSendVerifyCode();
const appStore = useAppStore();

// ---------- data ----------
const copyRight = ref('');
const inAnimation = ref(false);
const protocol = ref(false);
const navList = [i18n.t(`快速登录`), i18n.t(`账号登录`)];
const current = ref(1);
const account = ref('');
const password = ref('');
const captcha = ref('');
const formItem = ref(1);
const type = ref('login');
const logoUrl = ref('');
const keyCode = ref('');
const codeUrl = ref('');
const codeVal = ref('');
const isShowCode = ref(false);
const appLoginStatus = ref(false); // 微信登录强制绑定手机号码状态
const appUserInfo = ref({}); // 兼容旧版服务端的微信用户信息
const appAuthCode = ref(''); // 微信登录一次性授权码
const appBindKey = ref(''); // 微信登录绑定手机号缓存key
const wechatLoginLoading = ref(false);
const appleLoginStatus = ref(false); // 苹果登录强制绑定手机号码状态
const appleUserInfo = ref(null);
const appleShow = ref(false); // 苹果登录版本必须要求ios13以上的
const keyLock = ref(true);
const configData = Cache.get('BASIC_CONFIG');
const verifyRef = ref(null);

// ---------- watch ----------
watch(formItem, (nval, oVal) => {
	if (nval == 1) {
		type.value = 'login';
	} else {
		type.value = 'register';
	}
});

// ---------- 生命周期 ----------
onLoad(() => {
	uni.getSystemInfo({
		success: (res) => {
			if (res.platform.toLowerCase() == 'ios' && getSystem(res.system)) {
				appleShow.value = true;
			}
		}
	});
	if (uni.getStorageSync('copyRight').copyrightContext) {
		copyRight.value = uni.getStorageSync('copyRight').copyrightContext;
	}
});
onMounted(() => {
	// getCode();
	getLogoImage();
});

// ---------- methods ----------
function ChangeIsDefault(e) {
	const values = (e && e.detail && e.detail.value) || [];
	protocol.value = values.length > 0;
}
function privacy(type) {
	uni.navigateTo({
		url: '/pages/users/privacy/index?type=' + type
	});
}
// IOS 版本号判断
function getSystem(system) {
	let str;
	system.toLowerCase().indexOf('ios') === -1 ? (str = system) : (str = system.split(' ')[1]);
	if (str.indexOf('.')) return str.split('.')[0] >= 13;
	return str >= 13;
}
// 苹果登录
function appleLogin() {
	account.value = '';
	captcha.value = '';
	if (!protocol.value) {
		inAnimation.value = true;
		return util.Tips({
			title: i18n.t(`请先阅读并同意协议`)
		});
	}
	uni.showLoading({
		title: i18n.t(`登录中`)
	});
	uni.login({
		provider: 'apple',
		timeout: 10000,
		success(loginRes) {
			uni.getUserInfo({
				provider: 'apple',
				success: function (infoRes) {
					appleUserInfo.value = infoRes.userInfo;
					appleLoginApi();
				},
				fail() {
					uni.showToast({
						title: i18n.t(`获取用户信息失败`),
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
}
// 苹果登录Api
function appleLoginApi() {
	appleLoginRequest({
		openId: appleUserInfo.value.openId,
		email: appleUserInfo.value.email || '',
		phone: account.value,
		captcha: captcha.value
	})
		.then(({ data }) => {
			if (data.isbind) {
				uni.showModal({
					title: i18n.t(`提示`),
					content: i18n.t(`请绑定手机号后，继续操作`),
					showCancel: false,
					success: function (res) {
						if (res.confirm) {
							current.value = 1;
							appleLoginStatus.value = true;
						}
					}
				});
			} else {
				appStore.LOGIN({
					token: data.token,
					time: data.expires_time - Cache.time()
				});
				let backUrl = Cache.get(BACK_URL) || '/pages/index/index';
				Cache.clear(BACK_URL);
				appStore.SETUID(data.userInfo.uid);
				uni.reLaunch({
					url: backUrl
				});
			}
		})
		.catch((error) => {
			uni.showModal({
				title: i18n.t(`提示`),
				content: i18n.t(`错误信息`) + `${error}`,
				success: function (res) {
					if (res.confirm) {
						console.log(i18n.t(`用户点击确定`));
					} else if (res.cancel) {
						console.log(i18n.t(`用户点击取消`));
					}
				}
			});
		});
}
// App微信登录
function wxLogin() {
	if (wechatLoginLoading.value) {
		return;
	}
	account.value = '';
	captcha.value = '';
	if (!protocol.value) {
		inAnimation.value = true;
		return util.Tips({
			title: i18n.t(`请先阅读并同意协议`)
		});
	}
	wechatLoginLoading.value = true;
	uni.showLoading({
		title: i18n.t(`登录中`)
	});
	uni.login({
		provider: 'weixin',
		onlyAuthorize: true,
		success: function (loginRes) {
			appAuthCode.value = loginRes.code || (loginRes.authResult && loginRes.authResult.code) || '';
			appBindKey.value = '';
			if (!appAuthCode.value) {
				finishWechatLogin();
				return uni.showToast({
					title: i18n.t(`微信授权失败`),
					icon: 'none',
					duration: 2000
				});
			}
			wxLoginApi();
		},
		fail() {
			finishWechatLogin();
			uni.showToast({
				title: i18n.t(`登录失败`),
				icon: 'none',
				duration: 2000
			});
		}
	});
}

function wxLoginApi() {
	wechatAppAuth({
		userInfo: appUserInfo.value,
		authCode: appAuthCode.value,
		bindKey: appBindKey.value,
		phone: account.value,
		code: captcha.value
	})
		.then(({ data }) => {
			if (data.isbind) {
				appAuthCode.value = '';
				appBindKey.value = data.key || '';
				uni.showModal({
					title: i18n.t(`提示`),
					content: i18n.t(`请绑定手机号后，继续操作`),
					showCancel: false,
					success: function (res) {
						if (res.confirm) {
							current.value = 1;
							appLoginStatus.value = true;
						}
					}
				});
			} else {
				appAuthCode.value = '';
				appBindKey.value = '';
				appStore.LOGIN({
					token: data.token,
					time: data.expires_time - Cache.time()
				});
				let backUrl = Cache.get(BACK_URL) || '/pages/index/index';
				Cache.clear(BACK_URL);
				appStore.SETUID(data.userInfo.uid);
				uni.reLaunch({
					url: backUrl
				});
			}
			finishWechatLogin();
		})
		.catch((error) => {
			finishWechatLogin();
			uni.showModal({
				title: i18n.t(`提示`),
				content: i18n.t(`错误信息`) + `${error}`,
				success: function (res) {
					if (res.confirm) {
						console.log(i18n.t(`用户点击确定`));
					} else if (res.cancel) {
						console.log(i18n.t(`用户点击取消`));
					}
				}
			});
		});
}
function finishWechatLogin() {
	wechatLoginLoading.value = false;
	uni.hideLoading();
}
function again() {
	codeUrl.value = HTTP_REQUEST_URL + '/sms_captcha?' + 'key=' + keyCode.value + Date.parse(new Date()); // 原 VUE_APP_API_URL 从未定义（undefined 拼接），按意图改为接口域名
}
function success(data) {
	verifyRef.value.hide();
	getCodeApi()
		.then((res) => {
			keyCode.value = res.data.key;
			getCode(data);
		})
		.catch((res) => {
			util.Tips({
				title: res
			});
		});
}
function code() {
	if (!protocol.value) {
		inAnimation.value = true;
		return util.Tips({
			title: i18n.t(`请先阅读并同意协议`)
		});
	}
	if (!account.value)
		return util.Tips({
			title: i18n.t(`请填写手机号码`)
		});
	if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(account.value))
		return util.Tips({
			title: i18n.t(`请输入正确的手机号码`)
		});
	verifyRef.value.show();
}
async function getLogoImage() {
	getLogo(2).then((res) => {
		logoUrl.value = res.data.logo_url;
	});
}
async function loginMobile() {
	if (!protocol.value) {
		inAnimation.value = true;
		return util.Tips({
			title: i18n.t(`请先阅读并同意协议`)
		});
	}
	if (!account.value)
		return util.Tips({
			title: i18n.t(`请填写手机号码`)
		});
	if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(account.value))
		return util.Tips({
			title: i18n.t(`请输入正确的手机号码`)
		});
	if (!captcha.value)
		return util.Tips({
			title: i18n.t(`请填写验证码`)
		});
	if (!/^[\w\d]+$/i.test(captcha.value))
		return util.Tips({
			title: i18n.t(`请输入正确的验证码`)
		});
	if (appLoginStatus.value) {
		if (wechatLoginLoading.value) {
			return;
		}
		wechatLoginLoading.value = true;
		uni.showLoading({
			title: i18n.t(`登录中`)
		});
		wxLoginApi();
	} else if (appleLoginStatus.value) {
		appleLoginApi();
	} else {
		if (keyLock.value) {
			keyLock.value = !keyLock.value;
		} else {
			return util.Tips({
				title: i18n.t(`请勿重复点击`)
			});
		}
		loginMobileApi({
			phone: account.value,
			captcha: captcha.value,
			spread: Cache.get('spread'),
			agent_id: Cache.get('agent_id') || 0
		})
			.then((res) => {
				let data = res.data;
				appStore.LOGIN({
					token: data.token,
					time: data.expires_time - Cache.time()
				});
				let backUrl = Cache.get(BACK_URL) || '/pages/index/index';
				Cache.clear(BACK_URL);
				getUserInfo().then((res) => {
					keyLock.value = true;
					appStore.SETUID(res.data.uid);
					if (backUrl.indexOf('/pages/users/login/index') !== -1) {
						backUrl = '/pages/index/index';
					}
					uni.reLaunch({
						url: backUrl
					});
				});
			})
			.catch((res) => {
				keyLock.value = true;
				util.Tips({
					title: res
				});
			});
	}
}
async function register() {
	if (!protocol.value) {
		inAnimation.value = true;
		return util.Tips({
			title: i18n.t(`请先阅读并同意协议`)
		});
	}
	if (!account.value)
		return util.Tips({
			title: i18n.t(`请填写手机号码`)
		});
	if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(account.value))
		return util.Tips({
			title: i18n.t(`请输入正确的手机号码`)
		});
	if (!captcha.value)
		return util.Tips({
			title: i18n.t(`请填写验证码`)
		});
	if (!/^[\w\d]+$/i.test(captcha.value))
		return util.Tips({
			title: i18n.t(`请输入正确的验证码`)
		});
	if (!password.value)
		return util.Tips({
			title: i18n.t(`请填写密码`)
		});
	if (/^([0-9]|[a-z]|[A-Z]){0,6}$/i.test(password.value))
		return util.Tips({
			title: i18n.t(`您输入的密码过于简单`)
		});
	registerApi({
		account: account.value,
		captcha: captcha.value,
		password: password.value,
		spread: Cache.get('spread')
	})
		.then((res) => {
			util.Tips({
				title: res
			});
			formItem.value = 1;
		})
		.catch((res) => {
			util.Tips({
				title: res
			});
		});
}
async function getCode(data) {
	if (!protocol.value) {
		inAnimation.value = true;
		return util.Tips({
			title: i18n.t(`请先阅读并同意协议`)
		});
	}
	if (!account.value)
		return util.Tips({
			title: i18n.t(`请填写手机号码`)
		});
	if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(account.value))
		return util.Tips({
			title: i18n.t(`请输入正确的手机号码`)
		});
	if (formItem.value == 2) type.value = 'register';

	await registerVerify({
		phone: account.value,
		type: type.value,
		key: keyCode.value,
		captchaType: captchaType,
		captchaVerification: data.captchaVerification
	})
		.then((res) => {
			sendCode();
			util.Tips({
				title: res.msg
			});
		})
		.catch((res) => {
			util.Tips({
				title: res
			});
		});
}
function navTap(index) {
	current.value = index;
}
async function submit() {
	if (!protocol.value) {
		inAnimation.value = true;
		return util.Tips({
			title: i18n.t(`请先阅读并同意协议`)
		});
	}
	if (!account.value)
		return util.Tips({
			title: i18n.t(`请填写账号`)
		});
	if (!/^[\w\d]{5,16}$/i.test(account.value))
		return util.Tips({
			title: i18n.t(`请输入正确的账号`)
		});
	if (!password.value)
		return util.Tips({
			title: i18n.t(`请填写密码`)
		});
	if (keyLock.value) {
		keyLock.value = !keyLock.value;
	} else {
		return util.Tips({
			title: i18n.t(`请勿重复点击`)
		});
	}
	loginH5({
		account: account.value,
		password: password.value,
		spread: Cache.get('spread'),
		agent_id: Cache.get('agent_id') || 0
	})
		.then(({ data }) => {
			appStore.LOGIN({
				token: data.token,
				time: data.expires_time - Cache.time()
			});
			let backUrl = Cache.get(BACK_URL) || '/pages/index/index';
			Cache.clear(BACK_URL);
			getUserInfo()
				.then((res) => {
					keyLock.value = true;
					appStore.SETUID(res.data.uid);
					uni.reLaunch({
						url: backUrl
					});
				})
				.catch((error) => {
					keyLock.value = true;
				});
		})
		.catch((e) => {
			keyLock.value = true;
			util.Tips({
				title: e
			});
		});
}
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
		flex-direction: column;
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
		.name {
			font-size: 40rpx;
			font-weight: 500;
			color: #333333;
			margin-top: 22rpx;
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
