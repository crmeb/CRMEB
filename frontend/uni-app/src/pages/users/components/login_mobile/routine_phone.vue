<template>
	<view v-if="isPhoneBox">
		<view class="mobile-bg"></view>
		<view class="mobile-mask animated" :class="{slideInUp:isUp}">
			<view class="info-box">
				<image :src="logoUrl"></image>
				<view class="title">{{$t(`获取授权`)}}</view>
				<view class="txt">{{$t(`获取手机号授权`)}}</view>
			</view>
			<button class="sub_btn" open-type="getPhoneNumber"
				@getphonenumber="getphonenumber">{{$t(`获取手机号`)}}</button>
		</view>
	</view>
</template>
<script setup>
import { ref, onMounted } from 'vue';
const app = getApp();
import Routine from '@/libs/routine';
import {
	loginMobile,
	getCodeApi,
	getUserInfo as getUserInfoApi
} from "@/api/user";
import {
	getLogo,
	silenceAuth,
	routineBindingPhone
} from '@/api/public';
import Cache from '@/utils/cache';
import { useAppStore } from '@/store/modules/app';
import i18n from '@/utils/lang.js';

defineOptions({ name: 'routine_phone' });

const props = defineProps({
	isPhoneBox: {
		type: Boolean,
		default: false,
	},
	logoUrl: {
		type: String,
		default: '',
	},
	authKey: {
		type: String,
		default: '',
	}
});

const emit = defineEmits(['loginSuccess', 'close']);

const appStore = useAppStore();

// ---------- data ----------
const keyCode = ref('');
const account = ref('');
const codeNum = ref('');
const isStatus = ref(false);
const userInfo = ref({});

// ---------- methods ----------
// #ifdef MP
// 小程序获取手机号码
function getphonenumber(e) {
	uni.showLoading({
		title: i18n.t(`正在登录中`)
	});
	Routine.getCode()
		.then(code => {
			getUserPhoneNumber(e.detail.encryptedData, e.detail.iv, code);
		})
		.catch(error => {
			uni.hideLoading();
		});
}
// 小程序获取手机号码回调
function getUserPhoneNumber(encryptedData, iv, code) {
	routineBindingPhone({
			encryptedData: encryptedData,
			iv: iv,
			code: code,
			spread_spid: app.globalData.spid,
			spread_code: app.globalData.code,
			key: props.authKey
		})
		.then(res => {
			let time = res.data.expires_time - Cache.time();
			appStore.LOGIN({
				token: res.data.token,
				time: time
			});
			// getUserInfo();
			emit('loginSuccess', {
				isStatus: true,
				new_user: res.data.userInfo.new_user
			})
		})
		.catch(res => {
			uni.hideLoading();
		});
}
/**
 * 获取个人用户信息
 */
function getUserInfo() {
	getUserInfoApi().then(res => {
		uni.hideLoading();
		userInfo.value = res.data
		appStore.SETUID(res.data.uid);
		appStore.UPDATE_USERINFO(res.data);
		isStatus.value = true
		close(res.data.new_user || 0)
	});
}
// #endif
function close(new_user) {
	emit('close', {
		isStatus: isStatus.value,
		new_user
	})
}
</script>

<style lang="scss">
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

		.info-box {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;

			image {
				width: 150rpx;
				height: 150rpx;
				border-radius: 10rpx;
			}

			.title {
				margin-top: 30rpx;
				margin-bottom: 20rpx;
				font-size: 36rpx;
			}

			.txt {
				font-size: 30rpx;
				color: #868686;
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
