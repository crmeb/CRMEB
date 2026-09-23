<template>
	<view class="main" :style="colorStyle">
		<view class="head" v-if="!resData.type && !resData.paid">
			<view class="user-img">
				<image class="" :src="resData.avatar" mode=""></image>
			</view>
			<!-- paid: 0 未支付 1 已支付 type:0 本人 1 好友-->
			<view class="order-status" v-if="!resData.paid && !resData.type">
				{{ $t(`代付订单创建成功，发给好友帮你付款吧~`) }}
			</view>
		</view>
		<view class="head-other" v-else>
			<view class="user-img">
				<image class="" :src="resData.paid && !resData.type && resData.pay_uid === uid ? resData.pay_avatar : resData.avatar" mode=""></image>
			</view>
			<view class="order-status">
				<view class="head-other-nickname">
					{{ resData.paid && !resData.type && resData.pay_uid === uid ? resData.pay_nickname : resData.nickname }}
				</view>
				<view class="head-other-trip" v-if="!resData.paid && resData.type">
					{{ $t(`帮我付一下这件商品了，谢谢~`) }}
				</view>
				<view class="head-other-trip" v-if="resData.pay_uid !== uid && resData.paid && resData.type">
					{{ $t(`已经有人替我代付，谢谢啦~`) }}
				</view>
				<view class="head-other-trip" v-if="resData.pay_uid === uid && resData.paid && resData.type">
					{{ $t(`谢谢你帮我支付，么么哒~`) }}
				</view>
				<view class="head-other-trip" v-if="resData.pay_uid !== resData.uid && resData.paid && !resData.type">
					{{ $t(`我已为你代付成功，商家正在努力发货中~`) }}
				</view>
			</view>
		</view>
		<view class="order-msg">
			<view class="pay-success" v-if="resData.paid && !resData.type">
				{{ $t(`好友代付成功，商家正在努力发货中~`) }}
			</view>
			<view v-else class="pay--box">
				<view class="order-top">
					{{ $t(`代付金额`) }}
				</view>
				<view class="order-num">
					<text class="icon">{{ $t(`￥`) }}</text>
					{{ resData.pay_price || 0 }}
				</view>
			</view>
			<!-- #ifdef APP-PLUS -->
			<view v-if="!resData.paid && !resData.type" class="order-btn" @click="appShare('WXSceneSession')">
				{{ $t(`发送给微信好友`) }}
			</view>
			<!-- #endif -->
			<!-- #ifdef H5 -->
			<view v-if="!resData.paid && !resData.type" class="order-btn" @click="shareFriend">
				{{ $t(`发送给微信好友`) }}
			</view>
			<!-- #endif -->
			<!-- #ifdef MP -->
			<button v-if="!resData.paid && !resData.type" class="order-btn" open-type="share" hover-class="none" @click="shareModal = false">
				{{ $t(`发送给微信好友`) }}
			</button>
			<!-- #endif -->
			<button v-if="!resData.paid && !resData.type" class="order-btn detail" @click="goOrderDetail()">
				{{ $t(`查看订单详情`) }}
			</button>
			<button class="order-btn" v-if="!resData.paid && resData.type" @tap="payOpen()">{{ $t(`立即付款`) }}</button>
			<button class="order-btn on-pay" v-if="resData.paid && resData.type">{{ $t(`订单已支付`) }}</button>
			<button class="order-btn" v-if="resData.paid && !resData.type" @tap="goOrderDetail()">{{ $t(`查看订单详情`) }}</button>
			<view class="order-trip" v-if="resData.pay_uid === uid && resData.type">
				{{ $t(`如果订单申请退款，已支付金额将原路退还给您`) }}
			</view>
		</view>
		<view class="order-list">
			<orderGoods :cartInfo="resData.cartInfo" :is_confirm="true" :pay_price="resData.pay_price" :is_behalf="resData.paid && !resData.type ? true : false"></orderGoods>
		</view>
		<view class="share-box" v-if="shareModal">
			<image src="../static/share-info2.png" @click="shareModal = false"></image>
		</view>
		<payment :payMode="payMode" :pay_close="pay_close" :friendPay="true" @onChangeFun="onChangeFun" :order_id="order_id" :totalPrice="resData.pay_price"></payment>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { onLoad, onShow, onReady, onShareAppMessage } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import orderGoods from '@/components/orderGoods';
import payment from '@/components/payment';
import home from '@/components/home/index.vue';
import { friendDetail } from '@/api/user.js';
import { getPayConfig } from '@/api/public.js';
import { HTTP_REQUEST_URL } from '@/config/app.js';
import { toLogin } from '@/libs/login.js';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);
const uid = computed(() => appStore.uid);

// ---------- data ----------
const shareModal = ref(false);
const cartInfo = ref([]);
const resData = ref({});
const payMode = ref([
	{
		name: i18n.t(`微信支付`),
		icon: 'icon-weixinzhifu',
		value: 'weixin',
		title: i18n.t(`使用微信快捷支付`),
		payStatus: true
	}
	// #ifdef H5 || APP-PLUS
	// {
	// 	name: '支付宝支付',
	// 	icon: 'icon-zhifubao',
	// 	value: 'alipay',
	// 	title: '使用线上支付宝支付',
	// 	payStatus: true,
	// },
	// #endif
]);
const pay_close = ref(false);
const oid = ref('');
const order_id = ref('');

// ---------- watch ----------
watch(isLogin, (newV, oldV) => {
	if (newV == true) {
		getDetail();
	}
}, { deep: true });

// ---------- 生命周期 ----------
onReady(() => {
	// uni.setNavigationBarTitle({
	// 	title: '好友代付' || '支付成功'
	// });
});
onLoad((option) => {
	oid.value = option.oid;
});
onShow(() => {
	if (isLogin.value) {
		getDetail();
	} else {
		toLogin();
	}
});
// #ifdef MP
onShareAppMessage(() => {
	return {
		title: '',
		imageUrl: '',
		path: '/pages/users/payment_on_behalf/index?oid=' + oid.value + '&spread=' + appStore.uid
	};
});
// #endif

// ---------- methods ----------
/**
 * 打开支付组件
 *
 */
function payOpen() {
	getPayConfig().then(res => {
		payMode.value = res.data.filter(item => ['weixin', 'weixin_v3'].includes(item.value) && item.payStatus);
		pay_close.value = true;
	}).catch(err => util.Tips({ title: err }));
}
function getDetail() {
	friendDetail(oid.value)
		.then((res) => {
			resData.value = res.data.info;
			order_id.value = res.data.info.order_id;
			if (resData.value.paid && !resData.value.type) {
				goOrderDetail();
			}
			//#ifdef H5
			ShareInfo(resData.value);
			//#endif
		})
		.catch((err) => {
			util.Tips(
				{
					title: err
				},
				{
					tab: 4,
					url: '/pages/index/index'
				}
			);
		});
}
//#ifdef H5
function ShareInfo(data) {
	let href = location.href;
	if (Auth.isWeixin()) {
		let configAppMessage = {
			desc: i18n.t(`帮我付一下这件商品了，谢谢~`),
			title: i18n.t(`好友代付`),
			link: href,
			imgUrl: data.avatar
		};
		Auth
			.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareAppMessage', 'onMenuShareTimeline'], configAppMessage)
			.then((res) => {})
			.catch((err) => {});
	}
}
//#endif
// #ifdef APP-PLUS
function appShare(scene) {
	let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
	let curRoute = routes[routes.length - 1].$page.fullPath; // 获取当前页面路由，也就是最后一个打开的页面路由
	uni.share({
		provider: 'weixin',
		scene: scene,
		type: 0,
		href: `${HTTP_REQUEST_URL}${curRoute}`,
		title: i18n.t(`好友代付`),
		summary: i18n.t(`帮我付一下这件商品了，谢谢~`),
		imageUrl: resData.value.paid && !resData.value.type && resData.value.pay_uid === appStore.uid ? resData.value.pay_avatar : resData.value.avatar,
		success: function (res) {
			uni.showToast({
				title: i18n.t(`分享成功`),
				icon: 'success',
				duration: 2000
			});
		},
		fail: function (err) {
			uni.showToast({
				title: i18n.t(`分享失败`),
				icon: 'none',
				duration: 2000
			});
		}
	});
}
// #endif
function shareFriend() {
	// #ifndef MP
	shareModal.value = true;
	// #endif
}
/**
 * 事件回调（原 this[action] 动态派发改为查表）
 *
 */
const actionMap = {
	payOpen,
	payClose,
	pay_fail,
	pay_complete
};
function onChangeFun(e) {
	let opt = e;
	let action = opt.action || null;
	let value = opt.value != undefined ? opt.value : null;
	action && actionMap[action] && actionMap[action](value);
}
/**
 * 关闭支付组件
 */
function payClose() {
	pay_close.value = false;
}
/**
 * 支付失败回调
 */
function pay_fail() {
	pay_close.value = false;
}
/**
 * 支付成功回调
 */
function pay_complete() {
	pay_close.value = false;
	getDetail();
	uni.navigateTo({
		url: '/pages/users/payment_on_behalf/pay_status?order_id=' + oid.value
	});
}
function goOrderDetail() {
	uni.navigateTo({
		url: '/pages/center/order/detail/index?order_no=' + order_id.value
	});
}
</script>

<style lang="scss" scoped>
.main {
	background-color: #f5f5f5;

	.head {
		background-color: var(--view-theme);
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		padding: 60rpx 0 62rpx 0;

		.user-img {
			image {
				width: 68rpx;
				height: 68rpx;
				border-radius: 50%;
			}
		}

		.order-status {
			margin-top: 20rpx;
			font-size: 28rpx;
			color: #fff;
		}
	}

	.head-other {
		background-color: var(--view-theme);
		display: flex;
		align-items: center;
		padding: 60rpx 0 60rpx 30rpx;

		.user-img {
			display: flex;
			align-items: center;

			image {
				width: 100rpx;
				height: 100rpx;
				border-radius: 50%;
			}
		}

		.order-status {
			margin-left: 20rpx;
			color: #fff;
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			height: 100rpx;
			position: relative;

			.head-other-nickname {
				font-size: 28rpx;
			}

			.head-other-trip {
				padding: 6rpx 13rpx;
				font-size: 26rpx;
				background-color: rgba(255, 255, 255, 0.2);
				border-radius: 6rpx;
			}

			.head-other-trip::after {
				content: '';
				width: 0rpx;
				height: 0rpx;
				border: 10rpx solid rgba(255, 255, 255, 0.2);
				border-bottom: 10rpx solid transparent;
				border-left: 10rpx solid transparent;
				border-top: 10rpx solid transparent;
				position: absolute;
				left: -20rpx;
				bottom: 15rpx;
			}
		}
	}

	.order-msg {
		background-color: #fff;
		border-radius: 14rpx 14rpx 0 0;
		display: flex;
		flex-direction: column;
		align-items: center;
		padding: 40rpx 0;
		margin: -30rpx 30rpx 0 30rpx;

		.pay--box {
			text-align: center;
		}

		.pay-success {
			font-size: 30rpx;
			color: #333;
			font-weight: bold;
		}

		.order-num {
			font-size: 66rpx;
			color: #333;
			font-weight: bold;

			.icon {
				font-size: 30rpx;
			}
		}

		.order-btn {
			width: 90%;
			background-color: var(--view-theme);
			border-radius: 80rpx;
			padding: 26rpx 0;
			color: #fff;
			font-size: 30rpx;
			text-align: center;
			margin-top: 60rpx;
		}

		.order-btn.detail {
			margin-top: 20rpx;
			color: var(--view-theme);
			background-color: #fff;
			border: 1px solid var(--view-theme);
		}

		.order-btn.on-pay {
			background-color: #ccc;
		}

		.order-trip {
			color: #999;
			font-size: 26rpx;
			margin-top: 32rpx;
		}
	}

	.order-list {
		margin: 30rpx;
		border-radius: 14rpx;
		overflow: hidden;

		.orderGoods {
			margin-top: 0;
		}
:deep(.text .name){
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}
	}

	.share-box {
		z-index: 1000;
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;

		image {
			width: 100%;
			height: 100%;
		}
	}
}
</style>
