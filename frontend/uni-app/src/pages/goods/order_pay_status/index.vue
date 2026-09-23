<template>
	<view :style="colorStyle">
		<view class="payment-status" v-if="(!orderLottery || !order_pay_info.paid || is_gift) && loading && lotteryLoading">
			<!--失败时： 用icon-iconfontguanbi fail替换icon-duihao2 bg-color-->
			<view class="iconfont icons icon-duihao2 bg-color" v-if="order_pay_info.paid || order_pay_info.pay_type == 'offline'"></view>
			<view class="iconfont icons icon-iconfontguanbi" v-else></view>
			<!-- 失败时：订单支付失败 -->
			<view class="status" v-if="order_pay_info.pay_type != 'offline'">
				{{ order_pay_info.paid ? $t(`订单支付成功`) : $t(payType ? `订单支付中` : `订单支付失败`) }}
			</view>
			<view class="status" v-else>{{ $t(`订单创建成功`) }}</view>
			<view class="wrapper">
				<view class="item acea-row row-between-wrapper">
					<view>{{ $t(`订单号`) }}</view>
					<view class="itemCom">{{ orderId }}</view>
				</view>
				<view class="item acea-row row-between-wrapper">
					<view>{{ $t(`下单时间`) }}</view>
					<view class="itemCom">{{ order_pay_info._add_time }}</view>
				</view>
				<view class="item acea-row row-between-wrapper">
					<view>{{ $t(`支付方式`) }}</view>
					<view class="itemCom">{{ $t(order_pay_info._status._payType) || $t(`暂未支付`) }}</view>
				</view>
				<view class="item acea-row row-between-wrapper">
					<view>{{ $t(`支付金额`) }}</view>
					<view class="itemCom">{{ order_pay_info.pay_price }}</view>
				</view>
				<!--失败时加上这个  -->
				<view class="item acea-row row-between-wrapper" v-if="order_pay_info.paid == 0 && order_pay_info.pay_type != 'offline'">
					<view>{{ $t(`失败原因`) }}</view>
					<view class="itemCom">{{ $t(`未支付`) }}</view>
				</view>
			</view>
			<view v-if="order_pay_info.paid != 0 && is_gift !== 0" @click="giftModalShow = true">
				<button class="returnBnt bg-color" hover-class="none">{{ $t(`送给好友`) }}</button>
			</view>
			<!--失败时： 重新购买 -->
			<view @tap="goOrderDetails" v-if="status == 0">
				<button formType="submit" class="returnBnt bg-color" hover-class="none">{{ $t(`查看订单`) }}</button>
			</view>
			<!-- #ifdef H5 -->
			<view @tap="getOrderPayInfo" v-if="order_pay_info.paid == 0">
				<button class="returnBnt bg-color" hover-class="none">{{ $t(`刷新支付状态`) }}</button>
			</view>
			<!-- #endif -->
			<view @tap="goOrderDetails" v-if="order_pay_info.paid == 0 && status == 1">
				<button class="returnBnt bg-color" hover-class="none">{{ $t(`重新购买`) }}</button>
			</view>
			<view @tap="goOrderDetails" v-if="order_pay_info.paid == 0 && status == 2">
				<button class="returnBnt bg-color" hover-class="none">{{ $t(`重新支付`) }}</button>
			</view>
			<button
				@click="goPink(order_pay_info.pink_id)"
				class="returnBnt cart-color"
				formType="submit"
				hover-class="none"
				v-if="order_pay_info.pink_id && order_pay_info.paid != 0 && status != 2 && status != 1"
			>
				{{ $t(`邀请好友参团`) }}
			</button>
			<button @click="goIndex" class="returnBnt cart-color" formType="submit" hover-class="none" v-else>{{ $t(`返回首页`) }}</button>
			<view class="coupons" v-if="couponList.length">
				<view class="title acea-row row-center-wrapper">
					<view class="line"></view>
					<view class="name">{{ $t(`赠送优惠券`) }}</view>
					<view class="line"></view>
				</view>
				<view class="list">
					<template v-for="(item, index) in couponList" :key="index">
						<view class="item acea-row row-between-wrapper" v-if="index < 2 || !couponsHidden">
						<view class="moneyCon acea-row row-between-wrapper">
							<view class="price acea-row row-center-wrapper">
								<view>
									{{ $t(`￥`) }}
									<text>{{ item.coupon_price }}</text>
								</view>
							</view>
						</view>
						<view class="text">
							<view class="name line1">{{ item.coupon_title }}</view>
							<view class="priceMin">{{ $t(`满`) }}{{ item.use_min_price }}{{ $t(`元可用`) }}</view>
							<view class="time">{{ $t(`有效期`) }}:{{ item.add_time ? item.add_time + '-' : '' }}{{ item.end_time }}</view>
						</view>
					</view>
					</template>
					<view class="open acea-row row-center-wrapper" @click="openTap" v-if="couponList.length > 2">
						{{ couponsHidden ? $t(`更多`) : $t(`关闭`) }}
						<text class="iconfont" :class="couponsHidden == true ? 'icon-xiangxia' : 'icon-xiangshang'"></text>
					</view>
				</view>
			</view>
		</view>
		<lotteryModel
			v-show="orderLottery && order_pay_info.paid && loading && lotteryLoading && !is_gift"
			:options="options"
			@orderDetails="goOrderDetails"
			@lotteryShow="getOrderLottery"
		></lotteryModel>
		<giftModal :aleartStatus="giftModalShow" :giftData="giftData" @shareH5="shareH5" @close="giftModalShow = false"></giftModal>
		<view class="mask" v-if="giftModalShow"></view>
		<canvas class="canvas" canvas-id="posterCanvas"></canvas>
		<view class="share-box" v-if="H5ShareBox">
			<image :src="imgHost + '/statics/images/share-info.png'" @click="H5ShareBox = false"></image>
		</view>
	</view>
</template>

<script setup>
import { ref, watch } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
// #ifdef MP
import { onShareAppMessage, onShareTimeline } from '@dcloudio/uni-app';
// #endif
import { storeToRefs } from 'pinia';
import { userShare } from '@/api/user.js';
import lotteryModel from './payLottery.vue';
import giftModal from './components/giftModal.vue';
import { getOrderDetail, orderCoupon } from '@/api/order.js';
import { openOrderSubscribe } from '@/utils/subscribeMessage.js';
import { toLogin } from '@/libs/login.js';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif
import { HTTP_REQUEST_URL } from '@/config/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';

defineOptions({ name: 'OrderPayStatus' });

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const imgHost = HTTP_REQUEST_URL;
const loading = ref(false);
const lotteryLoading = ref(false);
const orderLottery = ref(false);
const orderId = ref('');
const order_pay_info = ref({
	paid: 1,
	_status: {}
});
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const status = ref(0);
const msg = ref('');
const couponsHidden = ref(true);
const couponList = ref([]);
const options = ref(null);
const payType = ref('');
const giftData = ref({});
const giftModalShow = ref(false);
const H5ShareBox = ref(false);
const is_gift = ref(0);
const storeInfo = ref({});
const mpGiftImg = HTTP_REQUEST_URL + '/statics/images/gift_share.jpg';

// 遗留：onShareTimeline 原取 this.id / this.uid（均未在 data/computed 声明，恒为 undefined），以同名 undefined 变量保持同等语义
var id, uid;

// ---------- watch ----------
watch(
	isLogin,
	(newV, oldV) => {
		if (newV) {
			getOrderPayInfo();
		}
	},
	{ deep: true }
);

// ---------- 生命周期 ----------
// 入参重命名 onLoadOptions：避免与同名 ref（options）在函数内互相遮蔽
onLoad((onLoadOptions) => {
	options.value = onLoadOptions;
	if (!onLoadOptions.order_id)
		return util.Tips(
			{
				title: i18n.t(`缺少参数无法查看订单支付状态`)
			},
			{
				tab: 3,
				url: 1
			}
		);
	orderId.value = onLoadOptions.order_id;
	status.value = onLoadOptions.status || 0;
	msg.value = onLoadOptions.msg || '';
	payType.value = onLoadOptions.payType || '';

	// // #ifdef H5
	// document.addEventListener('visibilitychange', (e) => {
	// 	let state = document.visibilityState
	// 	if (state == 'hidden') {
	// 		console.log('用户离开了');
	// 	}
	// 	if (state == 'visible') {
	// 		this.getOrderPayInfo();
	// 	}
	// });
	// // #endif
});
onShow(() => {
	if (isLogin.value) {
		getOrderPayInfo();
	} else {
		toLogin();
	}
});
/**
 * 用户点击右上角分享
 */
// #ifdef MP
onShareAppMessage(() => {
	userShare();
	return {
		title: giftData.value.message || '',
		imageUrl: mpGiftImg || '',
		path: '/pages/goods/receive_gift/index?id=' + giftData.value.id + '&spid=' + appStore.uid
	};
});
onShareTimeline(() => {
	userShare();
	return {
		title: giftData.value.message,
		query: {
			id: id,
			spid: uid || 0
		},
		imageUrl: mpGiftImg
	};
});
// #endif

// ---------- methods ----------
// #ifdef H5
function setOpenShare() {
	if (Auth.isWeixin()) {
		let configAppMessage = {
			desc: giftData.value.message,
			title: giftData.value.title,
			link: window.location.protocol + '//' + window.location.host + '/pages/goods/receive_gift/index?id=' + giftData.value.id + '&spid=' + appStore.uid,
			imgUrl: mpGiftImg
		};
		Auth.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareAppMessage', 'onMenuShareTimeline'], configAppMessage)
			.then((res) => {})
			.catch((res) => {
				if (res.is_ready) {
					res.wx.updateAppMessageShareData(configAppMessage);
					res.wx.updateTimelineShareData(configAppMessage);
					res.wx.onMenuShareAppMessage(configAppMessage);
					res.wx.onMenuShareTimeline(configAppMessage);
				}
			});
	}
}
// #endif
function shareH5() {
	// giftModalShow.value = false;
	H5ShareBox.value = true;
}
function getOrderLottery(statusParam) {
	orderLottery.value = statusParam;
	lotteryLoading.value = true;
}
function openTap() {
	couponsHidden.value = !couponsHidden.value;
}
function onLoadFun() {
	getOrderPayInfo();
}
/**
 *
 * 支付完成查询支付状态
 *
 */
function getOrderPayInfo() {
	uni.showLoading({
		title: i18n.t(`正在加载中`)
	});
	getOrderDetail(orderId.value)
		.then((res) => {
			uni.hideLoading();
			order_pay_info.value = res.data;
			uni.setNavigationBarTitle({
				title: res.data.paid ? i18n.t(`支付成功`) : i18n.t(`未支付`)
			});
			loading.value = true;
			if (res.data.paid && res.data.is_gift) {
				storeInfo.value = res.data.cartInfo[0].productInfo;
				is_gift.value = res.data.is_gift;
			}
			giftData.value = {
				image: res.data.cartInfo[0].productInfo.image,
				title: res.data.cartInfo[0].productInfo.store_name,
				message: res.data.gift_mark,
				id: res.data.id,
				avatar: res.data.avatar,
				nickname: res.data.nickname,
				code: res.data.gift_code
			};
			// #ifdef H5
			if (is_gift.value) setOpenShare();
			// #endif
			// 非礼品禁用分享
			if (!is_gift.value) {
				uni.hideShareMenu();
			}
			getOrderCoupon();
		})
		.catch((err) => {
			loading.value = true;
			uni.hideLoading();
		});
}
function getOrderCoupon() {
	orderCoupon(orderId.value).then((res) => {
		couponList.value = res.data;
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
// 去参团页面；
function goPink(idParam) {
	uni.navigateTo({
		url: '/pages/activity/goods_combination_status/index?id=' + idParam
	});
}
/**
 *
 * 去订单详情页面
 */
function goOrderDetails(e) {
	// #ifdef MP
	uni.showLoading({
		title: i18n.t(`正在加载中`)
	});
	openOrderSubscribe()
		.then((res) => {
			uni.hideLoading();
			uni.redirectTo({
				url: '/pages/goods/order_details/index?order_id=' + orderId.value
			});
		})
		.catch(() => {
			uni.hideLoading();
		});
	// #endif
	// #ifndef MP
	uni.redirectTo({
		url: '/pages/goods/order_details/index?order_id=' + orderId.value
	});
	// #endif
}
</script>

<style lang="scss">
.canvas {
	width: 750rpx;
	height: 1108rpx;
	z-index: 9999;
	position: absolute;
	bottom: 40000rpx;
	right: 30000rpx;
}
.coupons {
	.title {
		margin: 30rpx 0 25rpx 0;
		font-size: 28rpx;
		color: #333333;
	}

	.list {
		padding: 0 20rpx;

		.item {
			margin-bottom: 20rpx;
			box-shadow: 0px 2px 10px 0px rgba(0, 0, 0, 0.06);

			.price {
				width: 236rpx;
				height: 160rpx;
				font-size: 26rpx;
				color: #fff;
				font-weight: 800;

				text {
					font-size: 54rpx;
				}
			}

			.text {
				width: 385rpx;

				.name {
					font-size: #282828;
					font-size: 30rpx;
				}

				.priceMin {
					font-size: 24rpx;
					color: #999;
					margin-top: 10rpx;
				}

				.time {
					font-size: 24rpx;
					color: #999;
					margin-top: 15rpx;
				}
			}
		}

		.open {
			font-size: 24rpx;
			color: #999;
			margin-top: 30rpx;

			.iconfont {
				font-size: 25rpx;
				margin: 5rpx 0 0 10rpx;
			}
		}
	}
}

.payment-status {
	background-color: #fff;
	margin: 195rpx 30rpx 0 30rpx;
	border-radius: 10rpx;
	padding: 1rpx 0 28rpx 0;
}

.payment-status .icons {
	font-size: 70rpx;
	width: 140rpx;
	height: 140rpx;
	border-radius: 50%;
	color: #fff;
	text-align: center;
	line-height: 140rpx;
	text-shadow: 0px 4px 0px rgba(255, 255, 255, 0.5);
	border: 6rpx solid #f5f5f5;
	margin: -76rpx auto 0 auto;
	background-color: #999;
}

.payment-status .icons.icon-iconfontguanbi {
	text-shadow: 0px 4px 0px #6c6d6d;
}

.payment-status .iconfont.fail {
	text-shadow: 0px 4px 0px #7a7a7a;
}

.payment-status .status {
	font-size: 32rpx;
	font-weight: bold;
	text-align: center;
	margin: 25rpx 0 37rpx 0;
}

.payment-status .wrapper {
	border: 1rpx solid #eee;
	margin: 0 30rpx 47rpx 30rpx;
	padding: 35rpx 0;
	border-left: 0;
	border-right: 0;
}

.payment-status .wrapper .item {
	font-size: 28rpx;
	color: #282828;
}

.payment-status .wrapper .item ~ .item {
	margin-top: 20rpx;
}

.payment-status .wrapper .item .itemCom {
	color: #666;
}

.payment-status .returnBnt {
	width: 630rpx;
	height: 86rpx;
	border-radius: 50rpx;
	color: #fff;
	font-size: 30rpx;
	text-align: center;
	line-height: 86rpx;
	margin: 0 auto 20rpx auto;
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
</style>