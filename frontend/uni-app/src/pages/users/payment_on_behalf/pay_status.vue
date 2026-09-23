<template>
	<view :style="colorStyle" class="main">
		<view class='payment-status'>
			<view class='iconfont icons icon-duihao2 bg-color'></view>
			<view class='status'>{{$t(`代付成功`)}}</view>
			<view class='wrapper'>
				<view class='itemCom'> <text class="rmb">{{$t(`￥`)}}</text> {{resData.pay_price || 0.00}}</view>
			</view>
			<view class="head-other">
				<view class="user-img">
					<image class="" :src="resData.avatar" mode=""></image>
				</view>
				<view class="order-status">
					{{$t(`谢谢你为我付款，还可以再去看看其他商品哟~`)}}
				</view>
			</view>
		</view>
		<button @click="goIndex" class='returnBnt' formType="submit" hover-class='none'>{{$t(`返回首页`)}}</button>
	</view>
</template>

<script setup>
import { ref, watch } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
	getOrderDetail
} from '@/api/order.js';
import {
	toLogin
} from '@/libs/login.js';
import {
	friendDetail
} from '@/api/user.js'
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const loading = ref(false);
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const resData = ref({});
// 原代码未在 data 声明、直接挂到实例上的字段
const pageOptions = ref({});
const orderId = ref('');
const couponsHidden = ref(false);
const order_pay_info = ref({});

// ---------- watch ----------
watch(isLogin, (newV, oldV) => {
	if (newV) {
		getDetail();
	}
}, { deep: true });

// ---------- 生命周期 ----------
onLoad((options) => {
	pageOptions.value = options
	if (!options.order_id) return util.Tips({
		title: i18n.t(`缺少参数无法查看订单支付状态`)
	}, {
		tab: 3,
		url: 1
	});
	orderId.value = options.order_id;
});
onShow(() => {
	if (isLogin.value) {
		getDetail();
	} else {
		toLogin();
	}
});

// ---------- methods ----------
function getDetail() {
	friendDetail(orderId.value).then(res => {
		if (resData.value.paid == 0) {
			return util.Tips({
				title: i18n.t(`该订单暂未支付`)
			}, {
				tab: 3,
				url: 1
			});
		}
		resData.value = res.data.info

	})
}
function openTap() {
	couponsHidden.value = !couponsHidden.value;
}
function onLoadFun() {
	getDetail();
}
/**
 * 支付完成查询支付状态
 */
function getOrderPayInfo() {
	uni.showLoading({
		title: i18n.t(`正在加载中`)
	});
	getOrderDetail(orderId.value).then(res => {
		uni.hideLoading();
		order_pay_info.value = res.data;
		uni.setNavigationBarTitle({
			title: res.data.paid ? i18n.t(`支付成功`) : i18n.t(`未支付`)
		});
		loading.value = true
	}).catch(err => {
		loading.value = true
		uni.hideLoading();
	});
}
/**
 * 去首页关闭当前所有页面
 */
function goIndex(e) {
	uni.switchTab({
		url: '/pages/index/index'
	});
}
</script>

<style lang="scss" scoped>
	.main {
		width: 100%;
		height: 100vh;
		background-color: #fff;

		.payment-status {
			background-color: #fff;
			margin: 0rpx 30rpx 0 30rpx;
			border-radius: 10rpx;
			padding: 94rpx 0 60rpx 0;
			color: #333;

			.icons {
				font-size: 70rpx;
				width: 140rpx;
				height: 140rpx;
				border-radius: 50%;
				color: #fff;
				text-align: center;
				line-height: 140rpx;
				border: 6rpx solid #f5f5f5;
				margin: 0rpx auto 0 auto;
				background-color: #999;
			}

			.rmb {
				font-size: 33rpx;
			}

			.status {
				font-size: 32rpx;
				font-weight: bold;
				text-align: center;
				margin: 25rpx 0 7rpx 0;
			}

			.wrapper {
				text-align: center;
				color: #333;
				font-size: 66rpx;
				font-weight: bold;
			}

			.head-other {
				display: flex;
				align-items: center;
				justify-content: center;
				padding-top: 20rpx;

				.user-img {
					display: flex;
					align-items: center;

					image {
						width: 50rpx;
						height: 50rpx;
						border-radius: 50%;
					}
				}

				.order-status {
					margin-left: 20rpx;
					color: #666666;
					padding: 6rpx 13rpx;
					font-size: 24rpx;
				}
			}

		}

		.returnBnt {
			width: 80%;
			height: 86rpx;
			border-radius: 50rpx;
			color: #fff;
			font-size: 30rpx;
			text-align: center;
			line-height: 86rpx;
			margin: 0 auto;
			background-color: var(--view-theme);
		}
	}
</style>
