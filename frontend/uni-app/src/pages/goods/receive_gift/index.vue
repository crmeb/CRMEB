<template>
	<view class="warpper">
		<image class="top-bg" src="../static/receive-gift-bag.png" mode=""></image>
		<image class="top-bow" src="../static/bow.png" mode=""></image>
		<view class="aleart">
			<view class="from">{{ nickname }} {{ $t(`送给您的一份礼物`) }}</view>
			<view class="message">{{ gift_mark }}</view>
			<view class="aleart-body">
				<image class="goods-img" :src="image" mode=""></image>
			</view>
			<view class="title line1">
				{{ $t(store_name) }}
			</view>
		</view>
		<view v-if="(giftStatus == 1 || giftStatus == 2) && refund_status == 0" class="btn" @click="goGetGift()">
			{{ giftStatusText }}
		</view>
		<view v-if="giftStatus == 3 && refund_status == 0" class="btn-n">
			{{ $t('礼物已被领取') }}
		</view>
		<view v-if="refund_status != 0" class="btn-n">
			{{ $t('礼物已失效') }}
		</view>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import { getGiftOrderDetail } from '@/api/order.js';
import { HTTP_REQUEST_URL } from '@/config/app';
import { toLogin } from '@/libs/login.js';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
let app = getApp();

const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);
const { uid } = storeToRefs(appStore);

const order_id = ref('');
const giftStatus = ref(0);
const refund_status = ref(0);
const gift_mark = ref('');
const image = ref('');
const store_name = ref('');
const nickname = ref('');
const giftStatusText = ref('');
// 订单号：已领取后跳转订单详情使用
const orderNo = ref('');
// 原代码未在 data 声明、直接挂到实例上的字段
let gift_uid;
// 原代码 onLoad 中 this.uid = this.$store.state.app.uid 的快照
let uidSnapshot;

onLoad((options) => {
	if (isLogin.value) {
		order_id.value = options.id;
		// #ifdef MP
		if (options.scene) {
			let value = util.getUrlParams(decodeURIComponent(options.scene));
			if (value.id) order_id.value = value.id;
			//记录推广人uid
			if (value.pid) app.globalData.spid = value.pid;
		}
		// #endif
		uidSnapshot = uid.value;
		getOrderInfo();
	} else {
		toLogin();
	}
});

function goGetGift() {
	if (giftStatus.value == 2) {
		// 已领取：查看礼物订单详情，按订单号跳转（中台主键 id 不能作为 order_no）
		if (!orderNo.value) {
			uni.showToast({ title: i18n.t(`订单信息异常`), icon: 'none' });
			return;
		}
		uni.reLaunch({
			url: `/pages/center/order/detail/index?order_no=${orderNo.value}`
		});
	} else {
		uni.reLaunch({
			url: `/pages/goods/order_confirm/index?order_id=${order_id.value}&is_gift=2`
		});
	}
}
function getOrderInfo() {
	getGiftOrderDetail(order_id.value).then((res) => {
		gift_uid = res.data.gift_uid;
		gift_mark.value = res.data.gift_mark;
		nickname.value = res.data.nickname;
		refund_status.value = res.data.refund_status;
		// order_items 为扁平商品快照；兼容旧结构 productInfo.*
		const first = (res.data.cartInfo && res.data.cartInfo[0]) || {};
		const product = first.productInfo || first;
		image.value = product.image || first.image || '';
		store_name.value = product.store_name || first.store_name || '';
		orderNo.value = res.data.order_no || '';
		if (gift_uid === 0) {
			giftStatus.value = 1;
			giftStatusText.value = i18n.t('收下礼物');
		} else if (gift_uid === uidSnapshot) {
			giftStatus.value = 2;
			giftStatusText.value = i18n.t('查看礼物');
		} else if (gift_uid !== uidSnapshot) {
			giftStatus.value = 3;
		}
	}).catch((err) => {
		uni.showToast({ title: (err && err.msg) || i18n.t('加载失败'), icon: 'none' });
	});
}
</script>

<style lang="scss" scoped>
.warpper {
	display: flex;
	flex-direction: column;
	align-items: center;
	.top-bg {
		width: 100%;
		height: 206rpx;
	}
	.top-bow {
		width: 342rpx;
		height: 78rpx;
		margin-top: -120rpx;
		z-index: 9;
	}
}
.aleart {
	width: 670rpx;
	z-index: 7;
	margin-top: -20rpx;
	background-color: #fff;
	padding: 30rpx;
	border-radius: 12rpx;
	background-size: 100% 100%;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;

	.from {
		font-size: 28rpx;
		font-weight: 500;
		color: #333333;
		margin-bottom: 16rpx;
	}
	.message {
		font-weight: 400;
		font-size: 26rpx;
		color: #999999;
		margin-bottom: 42rpx;
	}
	.aleart-body {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 396rpx;
		height: 419rpx;
		background-image: url('@/pages/goods/static/gift-border.png');
		background-size: 100% 100%;
		margin-bottom: 40rpx;
		.goods-img {
			width: 360rpx;
			height: 360rpx;
			margin-top: 24rpx;
		}
	}
	.title {
		width: 396rpx;
		font-weight: 400;
		font-size: 28rpx;
		color: #333333;
		margin-bottom: 34rpx;
		text-align: center;
	}
}
.btn,
.btn-clear,
.btn-n {
	width: 670rpx;
	height: 84rpx;
	line-height: 84rpx;
	border-radius: 20px;
	text-align: center;
	margin-top: 56rpx;
}
.btn {
	color: #fff;
	background: linear-gradient(90deg, #ff7931 0%, #e93323 100%);
}
.btn-clear {
	color: #e93323;
	border: 1px solid #e93323;
}
.btn-n {
	color: #fff;
	background: #cccccc;
}
</style>
