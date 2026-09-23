<template>
	<view :style="colorStyle">
		<view class="px-20">
			<template v-if="!showBack">
				<view class="w-full h-120 rd-24rpx bg--w111-fff px-32 mt-40 flex-between-center fs-28">
					<text>{{ $t(`收款方式`) }}</text>
					<view class="flex-y-center">
						<image src="../static/weixin.png" class="w-42 h-42"></image>
						<text class="pl-16">{{ $t(`收款至微信`) }}</text>
					</view>
				</view>
				<view class="w-full rd-24rpx bg--w111-fff px-32 mt-20 fs-28">
					<view class="fs-28 lh-40rpx pt-40">{{ $t(`收款金额`) }}</view>
					<view class="mt-36 pb-40">
						<baseMoney :money="infoData.true_extract_price" symbolSize="48" integerSize="72" decimalSize="72" color="#333333" weight></baseMoney>
					</view>

				</view>
				<view v-if="isFailed" class="mt-40 fs-28 lh-40rpx">{{ $t(type === 1 ? `转账已失效，佣金已返还` : `转账已失效`) }}</view>
				<view v-else-if="!canReceive && !loading" class="mt-40 fs-28 lh-40rpx">{{ $t(loadError ? `转账状态查询失败` : `转账处理中`) }}</view>
			</template>
			<view class="w-full rd-24rpx bg--w111-fff mt-40 py-82 flex-col flex-between-center fs-28" v-if="showBack">
				<view class="flex-y-center">
					<image src="../static/receiving_success.png" class="w-60 h-60"></image>
					<text class="pl-16 fs-32 fw-500">{{ $t(`收款成功`) }}</text>
				</view>
				<view class="mt-40 pb-32">
					<baseMoney :money="infoData.true_extract_price" symbolSize="48" integerSize="72" decimalSize="72" color="#333333" weight></baseMoney>
				</view>
				<view class="fs-26 text--w111-999">{{ $t(`可在“微信支付-服务-钱包-账单”查看明细`) }}</view>
				<view class="w-504 h-80 rd-40rpx flex-center bg-color fs-28 text--w111-fff mt-52" @tap="backList">{{ $t(`返回列表`) }}</view>
			</view>
			<view class="fixed-lb w-full pb-safe" v-if="!showBack">
				<view class="w-full h-128 px-20 flex-center">
					<button v-if="isFailed" class="w-full h-80 rd-40rpx flex-center bg-color fs-28 text--w111-fff" @tap="backList">{{ $t(`返回列表`) }}</button>
					<button v-else-if="canReceive" class="w-full h-80 rd-40rpx flex-center bg-color fs-28 text--w111-fff" :disabled="loading || submitting" :loading="loading || submitting" @tap="confrimTap">{{ $t(`立即收款`) }}</button>
					<button v-else class="w-full h-80 rd-40rpx flex-center bg-color fs-28 text--w111-fff" :disabled="loading || submitting" :loading="loading || submitting" @tap="getInfo">{{ $t(`查询转账状态`) }}</button>
				</view>
			</view>
		</view>
	</view>
</template>
<script setup>
import { computed, ref } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import { useColorTheme } from '@/composables/useColorTheme';
import { toLogin } from '@/libs/login.js';
import { checkExtractTransferApi, transferInfoApi } from '@/api/user';
import routes from '@/dynamic-pages/api/routes';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const id = ref(0);
const type = ref(1);
const infoData = ref({
	true_extract_price: '',
	package_info: '',
	mchid: '',
	wechat_appid: '',
	can_receive: false,
	status: 0,
	state: ''
});
const showBack = ref(false);
const loading = ref(false);
const submitting = ref(false);
const loadError = ref(false);
const confirmationPending = ref(false);
const isFailed = computed(() => type.value === 1 ? infoData.value.status === -1 : ['FAIL', 'CANCELLED'].includes(infoData.value.state));
const canReceive = computed(() => !loadError.value && !confirmationPending.value &&
	(type.value === 1 ? infoData.value.can_receive === true : infoData.value.state === 'WAIT_USER_CONFIRM'));

// ---------- 生命周期 ----------
onLoad((options) => {
	if (!isLogin.value) return toLogin();
	if (options.id) {
		id.value = options.id;
		type.value = Number(options.type || 1);
		getInfo();
	}
});
onShow(() => {
	if (isLogin.value && id.value && !loading.value && !submitting.value) getInfo();
});

// ---------- methods ----------
async function getInfo() {
	if (loading.value) return null;
	loading.value = true;
	try {
		const query = type.value === 1 ? checkExtractTransferApi : transferInfoApi;
		const res = await query({ order_id: id.value, type: type.value });
		infoData.value = res.data;
		loadError.value = false;
		if (type.value === 1) {
			showBack.value = res.data.status === 1 && res.data.state === 'SUCCESS';
		}
		return res.data;
	} catch (err) {
		loadError.value = true;
		util.Tips({ title: err });
		return null;
	} finally {
		loading.value = false;
	}
}
async function completeReceipt() {
	if (type.value === 1) {
		// 客户端确认仅表示调起完成，到账必须由服务端查单或通知确认。
		confirmationPending.value = true;
		await getInfo();
	} else {
		showBack.value = true;
	}
	submitting.value = false;
}
function failReceipt(res) {
	submitting.value = false;
	if (!/cancel/i.test(res?.errMsg || res?.err_msg || '')) {
		util.Tips({ title: i18n.t(`收款未完成，请稍后重试`) });
	}
}
async function confrimTap() {
	if (loading.value || submitting.value || !canReceive.value) return;
	submitting.value = true;
	// 打开页面后可能长时间停留，调起微信前再次核验有效状态。
	if (type.value === 1 && (!await getInfo() || !canReceive.value)) {
		submitting.value = false;
		return;
	}
	// #ifdef MP-WEIXIN
	if (wx.canIUse('requestMerchantTransfer')) {
		if (type.value === 1 && wx.getAccountInfoSync().miniProgram.appId !== infoData.value.wechat_appid) {
			submitting.value = false;
			return util.Tips({ title: i18n.t(`收款应用不一致，请联系管理员`) });
		}
		wx.requestMerchantTransfer({
			mchId: infoData.value.mchid,
			appId: wx.getAccountInfoSync().miniProgram.appId,
			package: infoData.value.package_info,
			success: completeReceipt,
			fail: failReceipt
		});
	} else {
		submitting.value = false;
		uni.showToast({
			title: i18n.t(`你的微信版本过低，请更新至最新版本。`)
		});
	}
	// #endif
	// #ifdef H5
	if (Auth.isWeixin()) {
		let configAppMessage = {
			mchId: infoData.value.mchid,
			appId: infoData.value.wechat_appid,
			package: infoData.value.package_info
		};
		if (typeof WeixinJSBridge !== 'undefined') {
			WeixinJSBridge.invoke('requestMerchantTransfer', configAppMessage, function (res) {
				if (res.err_msg === 'requestMerchantTransfer:ok') {
					completeReceipt();
				} else {
					failReceipt(res);
				}
			});
		} else {
			submitting.value = false;
			uni.showToast({
				title: i18n.t(`你的微信版本过低，请更新至最新版本。`)
			});
		}
	} else {
		submitting.value = false;
		util.Tips({ title: i18n.t(`请在微信内打开收款页面`) });
	}
	// #endif
	// #ifndef MP-WEIXIN || H5
	submitting.value = false;
	util.Tips({ title: i18n.t(`请在微信小程序内收款`) });
	// #endif
}
function backList() {
	let backUrl;
	if (type.value == 1) {
		backUrl = routes.withdrawalList + '?type=1';
	} else {
		backUrl = routes.lotteryRecord;
	}
	uni.reLaunch({
		url: backUrl
	});
}
</script>
<style lang="scss">
.bb-e {
	border-bottom: 1px solid #eee;
}
.py-82 {
	padding: 82rpx 0;
}
</style>
