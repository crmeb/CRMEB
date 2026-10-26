<template>
	<view :style="colorStyle">
		<view class="OrderCancellation">
			<view class="header">
				<view class="header-title">{{ $t(`订单核销`) }}</view>
			</view>
			<view class="whiteBg">
				<view class="input">
					<input
						type="number"
						:placeholder="$t(`请输入核销码`)"
						v-model="verify_code"
					/>
				</view>
				<view class="bnt" @click="codeChange">{{ $t(`立即核销`) }}</view>
			</view>
			<!-- #ifdef MP || APP-PLUS -->
			<view class="scan" @click="scanCode">
				<image src="../static/scan.gif"></image>
			</view>
			<!-- #endif -->
			<!-- #ifdef H5 -->
			<view v-if="isWeixin" class="scan" @click="scanCode">
				<image src="../static/scan.gif"></image>
			</view>
			<!-- #endif -->
		</view>
		<view v-if="iShidden">
			<view class="WriteOff">
				<view class="pictrue">
					<image :src="orderInfo.image" mode="aspectFill" />
				</view>
				<view class="num acea-row row-center-wrapper">
					<text>{{ orderInfo.order_id || orderInfo.order_no }}</text>
				</view>
				<view v-if="orderInfo.title" class="title line1">{{ orderInfo.title }}</view>
				<view class="tip">{{ $t(`确定要核销此订单吗`) }}</view>
				<view class="btn sure" @click="confirm">{{ $t(`确定核销`) }}</view>
				<view class="btn cancel" @click="cancel">{{ $t(`取消`) }}</view>
			</view>
			<view class="mask"></view>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import { orderVerific } from '@/api/writeoff.js';
import home from '@/components/home/index.vue';
import { toLogin } from '@/libs/login.js';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
// #ifdef H5
import wechat from '@/libs/wechat.js';
// #endif

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

const iShidden = ref(false);
const verify_code = ref('');
const isWeixin = ref(false);
const orderInfo = ref({});
const submitting = ref(false);

/**
 * 从扫码结果解析核销码（兼容纯数字码 / URL 参数）
 */
function parseVerifyCode(raw) {
	const s = decodeURIComponent(String(raw || '')).trim();
	if (/^\d{8,12}$/.test(s)) return s;
	const byParam = s.match(/(?:verify_code|code)=(\d{8,12})/i);
	if (byParam) return byParam[1];
	const digits = s.match(/\d{8,12}/);
	return digits ? digits[0] : '';
}

onLoad((options = {}) => {
	if (!isLogin.value) {
		toLogin();
		return;
	}
	// #ifdef H5
	isWeixin.value = wechat.isWeixin();
	// #endif
	if (options.verify_code) {
		verify_code.value = String(options.verify_code);
	}
	// #ifdef MP
	if (options.scene) {
		const value = util.getUrlParams(decodeURIComponent(options.scene));
		verify_code.value = value.verify_code || verify_code.value;
	}
	// #endif
});

function codeChange() {
	if (submitting.value) return;
	const code = String(verify_code.value || '').trim();
	if (!code) {
		return util.Tips({ title: i18n.t(`请输入核销码`) });
	}
	if (!/^\d{8,12}$/.test(code)) {
		return util.Tips({ title: i18n.t(`请输入正确的核销码`) });
	}
	submitting.value = true;
	util.Tips({ title: i18n.t(`查询中`) });
	orderVerific(code, 0)
		.then((res) => {
			orderInfo.value = res.data || {};
			iShidden.value = true;
		})
		.catch((err) => {
			verify_code.value = '';
			util.Tips({ title: err });
		})
		.finally(() => {
			submitting.value = false;
		});
}

function scanCode() {
	// #ifdef MP || APP-PLUS
	uni.scanCode({
		success(res) {
			const code = parseVerifyCode(res.path || res.result);
			if (!code) {
				return util.Tips({ title: i18n.t(`请输入正确的核销码`) });
			}
			verify_code.value = code;
			codeChange();
		},
	});
	// #endif
	// #ifdef H5
	wechat
		.wechatEvevt('scanQRCode', {
			needResult: 1,
			scanType: ['qrCode', 'barCode'],
		})
		.then((res) => {
			let raw = '';
			if (res.scanType == 'WX_CODE') {
				raw = res.path || '';
			} else {
				raw = res.resultStr || res.result || '';
			}
			const code = parseVerifyCode(raw);
			if (!code) {
				return util.Tips({ title: i18n.t(`请输入正确的核销码`) });
			}
			verify_code.value = code;
			codeChange();
		})
		.catch(() => {});
	// #endif
}

function confirm() {
	if (submitting.value) return;
	const code = String(verify_code.value || '').trim();
	if (!code) return;
	submitting.value = true;
	orderVerific(code, 1)
		.then((res) => {
			verify_code.value = '';
			iShidden.value = false;
			orderInfo.value = {};
			util.Tips({ title: res.msg || i18n.t(`核销成功`) });
		})
		.catch((err) => {
			util.Tips({ title: err });
		})
		.finally(() => {
			submitting.value = false;
		});
}

function cancel() {
	iShidden.value = false;
}
</script>

<style lang="scss">
page {
	background-color: #fff;
}

.OrderCancellation {
	width: 100%;
	height: 100%;
	background: #fff;

	.header {
		width: 100%;
		height: 300rpx;
		background-color: var(--view-theme);
		background-image: linear-gradient(180deg, var(--view-theme) 0%, #fff 100%);
		display: flex;
		align-items: flex-start;
		justify-content: flex-start;
		padding: 72rpx 48rpx 0;
		box-sizing: border-box;

		.header-title {
			font-size: 56rpx;
			font-weight: 600;
			color: #fff;
			letter-spacing: 4rpx;
			line-height: 1.2;
			text-align: left;
		}
	}

	.whiteBg {
		width: 690rpx;
		background: #fff;
		border-radius: 20rpx;
		margin: -120rpx auto 0;
		padding: 50rpx 40rpx;
		box-shadow: 0 10rpx 40rpx rgba(0, 0, 0, 0.06);
		position: relative;
		z-index: 2;

		.input {
			background: #f5f5f5;
			border-radius: 12rpx;
			padding: 24rpx 28rpx;

			input {
				font-size: 30rpx;
				text-align: center;
			}
		}

		.bnt {
			margin-top: 40rpx;
			height: 86rpx;
			line-height: 86rpx;
			text-align: center;
			border-radius: 43rpx;
			background: var(--view-theme);
			color: #fff;
			font-size: 30rpx;
			font-weight: 500;
		}
	}

	.scan {
		margin: 80rpx auto 0;
		width: 280rpx;
		height: 280rpx;

		image {
			width: 100%;
			height: 100%;
		}
	}
}

.WriteOff {
	position: fixed;
	left: 50%;
	top: 50%;
	transform: translate(-50%, -50%);
	width: 560rpx;
	background: #fff;
	border-radius: 20rpx;
	z-index: 100;
	padding: 40rpx 36rpx 48rpx;
	text-align: center;

	.pictrue {
		width: 160rpx;
		height: 160rpx;
		margin: 0 auto 24rpx;
		border-radius: 12rpx;
		overflow: hidden;

		image {
			width: 100%;
			height: 100%;
		}
	}

	.num {
		font-size: 28rpx;
		color: #333;
		font-weight: 500;
	}

	.title {
		margin-top: 12rpx;
		font-size: 26rpx;
		color: #666;
	}

	.tip {
		margin: 36rpx 0 28rpx;
		font-size: 30rpx;
		color: #333;
	}

	.btn {
		height: 80rpx;
		line-height: 80rpx;
		border-radius: 40rpx;
		font-size: 28rpx;

		&.sure {
			background: var(--view-theme);
			color: #fff;
		}

		&.cancel {
			margin-top: 20rpx;
			background: #f5f5f5;
			color: #666;
		}
	}
}

.mask {
	position: fixed;
	left: 0;
	top: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.5);
	z-index: 99;
}
</style>
