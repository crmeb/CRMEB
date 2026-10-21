<template>
	<view class="warpper">
		<view class="aleart">
			<view class="aleart-body">
				<image v-if="!status" class="goods-img" src="../static/receive_gifts_error.png" mode=""></image>
				<image v-else class="goods-img" src="../static/receive_gifts_success.png" mode=""></image>
			</view>
			<view class="title">
				{{ $t(status ? '礼物领取成功' : '领取失败，礼物已失效') }}
			</view>
			<view v-if="status" class="btn" @click="goPage(0)">
				{{ $t('查看礼物详情') }}
			</view>
			<view v-if="status" class="btn-clear" @click="goPage(1)">
				{{ $t('返回商城首页') }}
			</view>
			<view v-if="!status" class="btn" @click="goPage(1)">
				{{ $t('返回商城首页') }}
			</view>
		</view>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import i18n from '@/utils/lang.js';

const status = ref(0);
const orderNo = ref('');

onLoad((options) => {
	status.value = options.status;
	// order_no 为现行参数；order_id 兼容历史链接（历史链接中携带的同样是订单号）
	orderNo.value = options.order_no || options.order_id || '';
});

function goPage(type) {
	if (type) {
		uni.reLaunch({
			url: '/pages/index/index'
		});
		return;
	}
	// 订单详情按订单号跳转（中台主键 id 不能作为 order_no 使用）
	if (!orderNo.value) {
		uni.showToast({ title: i18n.t(`订单信息异常`), icon: 'none' });
		return;
	}
	uni.reLaunch({
		url: `/pages/center/order/detail/index?order_no=${orderNo.value}`
	});
}
</script>

<style lang="scss" scoped>
.warpper {
	display: flex;
	flex-direction: column;
	align-items: center;
}
.aleart {
	width: 670rpx;
	z-index: 7;
	background-color: #fff;
	padding: 74rpx 30rpx 50rpx;
	border-radius: 12rpx;
	background-size: 100% 100%;
	margin-top: 24rpx;
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
		margin-bottom: 32rpx;
		padding: 0 30rpx;
		.goods-img {
			width: 206rpx;
			height: 152rpx;
			margin-top: 24rpx;
		}
	}
	.title {
		font-weight: 500;
		font-size: 32rpx;
		color: #282828;
		margin-bottom: 48rpx;
	}
}
.btn,
.btn-clear,
.btn-n {
	width: 100%;
	height: 84rpx;
	line-height: 84rpx;
	border-radius: 20px;
	text-align: center;
	margin-bottom: 30rpx;
}
.btn {
	color: #fff;
	background: #e93323;
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
