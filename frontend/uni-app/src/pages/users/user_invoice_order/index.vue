<template>
	<view :style="colorStyle">
		<view class='order-details'>
			<view v-if="orderInfo && orderInfo.invoice" class='header bg-color acea-row row-middle'>
				<view class='iconfont icon-fapiao1'></view>
				<view class='data'>
					<view class='state'>{{orderInfo.invoice.is_invoice ? $t(`已开票`) : $t(`未开票`)}}</view>
					<view>{{orderInfo.invoice.add_time}}</view>
				</view>
			</view>
			<view v-if="orderInfo && orderInfo.invoice" class="wrapper">
				<view class="item acea-row row-between-wrapper">
					<view>{{$t(`发票类型`)}}</view>
					<view class="conter">{{orderInfo.invoice.type === 1 ? $t(`增值税电子普通发票`) : $t(`增值税电子专用发票`)}}</view>
				</view>
				<view class="item acea-row row-between-wrapper">
					<view>{{$t(`发票抬头`)}}</view>
					<view class="conter">{{orderInfo.invoice.name}}</view>
				</view>
				<view v-if="orderInfo.invoice.duty_number" class="item acea-row row-between-wrapper">
					<view>{{$t(`税号`)}}</view>
					<view class="conter">{{orderInfo.invoice.duty_number}}</view>
				</view>
				<view class="item acea-row row-between-wrapper">
					<view>{{$t(`手机号`)}}</view>
					<view class="conter">{{orderInfo.invoice.drawer_phone}}</view>
				</view>
				<view class="item acea-row row-between-wrapper">
					<view>{{$t(`邮箱`)}}</view>
					<view class="conter">{{orderInfo.invoice.email}}</view>
				</view>
				<template v-if="orderInfo.invoice.type === 2">
					<view class="item acea-row row-between-wrapper">
						<view>{{$t(`开户银行`)}}</view>
						<view class="conter">{{orderInfo.invoice.bank}}</view>
					</view>
					<view class="item acea-row row-between-wrapper">
						<view>{{$t(`银行账号`)}}</view>
						<view class="conter">{{orderInfo.invoice.card_number}}</view>
					</view>
					<view class="item acea-row row-between-wrapper">
						<view>{{$t(`企业地址`)}}</view>
						<view class="conter">{{orderInfo.invoice.address}}</view>
					</view>
					<view class="item acea-row row-between-wrapper">
						<view>{{$t(`企业电话`)}}</view>
						<view class="conter">{{orderInfo.invoice.tell}}</view>
					</view>
				</template>
				<view class="item acea-row row-between-wrapper" v-if='orderInfo.invoice.invoice_number'>
					<view>{{$t(`发票编号`)}}</view>
					<view class="conter">{{orderInfo.invoice.invoice_number}}</view>
				</view>
				<view class="item acea-row row-between-wrapper" v-if='orderInfo.invoice.remark'>
					<view>{{$t(`发票备注`)}}</view>
					<view class="conter">{{orderInfo.invoice.remark}}</view>
				</view>
			</view>
			<orderGoods
				v-if="cartInfo && cartInfo.length"
				:evaluate="evaluate"
				:orderId="order_id"
				:cartInfo="cartInfo"
				:jump="true"
				:paid="orderInfo.paid"
				:oid="orderInfo.id"
				:isShow="false"
				:statusType="status.type"
			></orderGoods>
			<view class='wrapper'>
				<view class='item acea-row row-between'>
					<view>{{$t(`订单编号`)}}：</view>
					<view class='conter acea-row row-middle row-right'>{{orderInfo.order_id || orderInfo.order_no}}
						<!-- #ifndef H5 -->
						<text class='copy' @tap='copy'>{{$t(`复制`)}}</text>
						<!-- #endif -->
						<!-- #ifdef H5 -->
						<text class='copy copy-data' :data-clipboard-text="orderInfo.order_id || orderInfo.order_no">{{$t(`复制`)}}</text>
						<!-- #endif -->
					</view>
				</view>
				<view class='item acea-row row-between'>
					<view>{{$t(`下单时间`)}}：</view>
					<view class='conter'>{{(orderInfo.add_time_y || '') +' '+(orderInfo.add_time_h || '')}}</view>
				</view>
				<view class='item acea-row row-between'>
					<view>{{$t(`支付状态`)}}：</view>
					<view class='conter' v-if="orderInfo.paid">{{$t(`已支付`)}}</view>
					<view class='conter' v-else>{{$t(`未支付`)}}</view>
				</view>
				<view class='item acea-row row-between'>
					<view>{{$t(`支付方式`)}}：</view>
					<view class='conter'>{{ (orderInfo._status && orderInfo._status._payType) || orderInfo.pay_type_name || orderInfo.pay_type || '' }}</view>
				</view>
				<view class='item acea-row row-between' v-if="orderInfo.mark">
					<view>{{$t(`买家留言`)}}：</view>
					<view class='conter'>{{orderInfo.mark}}</view>
				</view>
				<view class='item acea-row row-between' v-if="orderInfo.fictitious_content">
					<view>{{$t(`备注`)}}：</view>
					<view class='conter'>{{orderInfo.fictitious_content}}</view>
				</view>
			</view>
			<!-- 退款订单详情 -->
			<view class='wrapper' v-if="isGoodsReturn">
				<view class='item acea-row row-between'>
					<view>{{$t(`收货人`)}}：</view>
					<view class='conter'>{{orderInfo.real_name}}</view>
				</view>
				<view class='item acea-row row-between'>
					<view>{{$t(`联系电话`)}}：</view>
					<view class='conter'>{{orderInfo.user_phone}}</view>
				</view>
				<view class='item acea-row row-between'>
					<view>{{$t(`收货地址`)}}：</view>
					<view class='conter'>{{orderInfo.user_address}}</view>
				</view>
			</view>
			<view v-if="orderInfo.status!=0">
				<view class='wrapper' v-if='orderInfo.delivery_type=="express"'>
					<view class='item acea-row row-between'>
						<view>{{$t(`配送方式`)}}：</view>
						<view class='conter'>{{$t(`发货`)}}</view>
					</view>
					<view class='item acea-row row-between'>
						<view>{{$t(`快递公司`)}}：</view>
						<view class='conter'>{{orderInfo.delivery_name || ''}}</view>
					</view>
					<view class='item acea-row row-between'>
						<view>{{$t(`快递号`)}}：</view>
						<view class='conter'>{{orderInfo.delivery_id || ''}}</view>
					</view>
				</view>
				<view class='wrapper' v-else-if='orderInfo.delivery_type=="send"'>
					<view class='item acea-row row-between'>
						<view>{{$t(`配送方式`)}}：</view>
						<view class='conter'>{{$t(`送货`)}}</view>
					</view>
					<view class='item acea-row row-between'>
						<view>{{$t(`配送人姓名`)}}：</view>
						<view class='conter'>{{orderInfo.delivery_name || ''}}</view>
					</view>
					<view class='item acea-row row-between'>
						<view>{{$t(`联系电话`)}}：</view>
						<view class='conter acea-row row-middle row-right'>{{orderInfo.delivery_id || ''}}<text class='copy' @tap='goTel'>{{$t(`dial`)}}</text></view>
					</view>
				</view>
				<view class='wrapper' v-else-if='orderInfo.delivery_type=="fictitious"'>
					<view class='item acea-row row-between'>
						<view>{{$t(`虚拟发货`)}}：</view>
						<view class='conter'>{{$t(`已发货，请注意查收`)}}</view>
					</view>
				</view>
			</view>
			<view class='wrapper'>
				<view class='item acea-row row-between'>
					<view>{{$t(`支付金额`)}}：</view>
					<view class='conter'>{{$t(`￥`)}}{{orderInfo.pay_price}}</view>
				</view>
				<!-- <view class='item acea-row row-between' v-if='orderInfo.coupon_id'>
					<view>{{$t(`优惠券抵扣`)}}：</view>
					<view class='conter'>-{{$t(`￥`)}}{{orderInfo.coupon_price}}</view>
				</view>
				<view class='item acea-row row-between' v-if="orderInfo.use_integral > 0">
					<view>{{$t(`积分抵扣`)}}：</view>
					<view class='conter'>-{{$t(`￥`)}}{{orderInfo.deduction_price}}</view>
				</view>
				<view class='item acea-row row-between' v-if="orderInfo.pay_postage > 0">
					<view>{{$t(`运费`)}}：</view>
					<view class='conter'>{{$t(`￥`)}}{{orderInfo.pay_postage}}</view>
				</view> -->
			</view>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>
<style scoped lang="scss">
	.goodCall {
		color: #e93323;
		text-align: center;
		width: 100%;
		height: 86rpx;
		padding: 0 30rpx;
		border-bottom: 1rpx solid #eee;
		font-size: 30rpx;
		line-height: 86rpx;
		background: #fff;

		.icon-kefu {
			font-size: 36rpx;
			margin-right: 15rpx;
		}

		/* #ifdef MP */
		button {
			display: flex;
			align-items: center;
			justify-content: center;
			height: 86rpx;
			font-size: 30rpx;
			color: #e93323;
		}

		/* #endif */
	}

	.order-details .header {
		padding: 0 30rpx;
		height: 150rpx;
	}

	.order-details .header.on {
		background-color: #666 !important;
	}

	.order-details .header .iconfont {
		font-size: 70rpx;
		color: #fff;
	}

	.order-details .header .data {
		color: rgba(255, 255, 255, 0.8);
		font-size: 24rpx;
		margin-left: 27rpx;
	}

	.order-details .header .data.on {
		margin-left: 0;
	}

	.order-details .header .data .state {
		font-size: 30rpx;
		font-weight: bold;
		color: #fff;
		margin-bottom: 7rpx;
	}

	.order-details .header .data .time {
		margin-left: 20rpx;
	}

	.order-details .nav {
		background-color: #fff;
		font-size: 26rpx;
		color: #282828;
		padding: 25rpx 0;
	}

	.order-details .nav .navCon {
		padding: 0 40rpx;
	}

	.order-details .nav .on {
		color: #e93323;
	}

	.order-details .nav .progress {
		padding: 0 65rpx;
		margin-top: 10rpx;
	}

	.order-details .nav .progress .line {
		width: 100rpx;
		height: 2rpx;
		background-color: #939390;
	}

	.order-details .nav .progress .iconfont {
		font-size: 25rpx;
		color: #939390;
		margin-top: -2rpx;
	}

	.order-details .address {
		font-size: 26rpx;
		color: #868686;
		background-color: #fff;
		margin-top: 13rpx;
		padding: 35rpx 30rpx;
	}

	.order-details .address .name {
		font-size: 30rpx;
		color: #282828;
		margin-bottom: 15rpx;
	}

	.order-details .address .name .phone {
		margin-left: 40rpx;
	}

	.order-details .line {
		width: 100%;
		height: 3rpx;
	}

	.order-details .line image {
		width: 100%;
		height: 100%;
		display: block;
	}

	.order-details .wrapper {
		background-color: #fff;
		margin-top: 12rpx;
		padding: 30rpx;
	}

	.order-details .wrapper .item {
		font-size: 28rpx;
		color: #282828;
	}

	.order-details .wrapper .item~.item {
		margin-top: 20rpx;
	}

	.order-details .wrapper .item .conter {
		color: #868686;
		width: 460rpx;
		text-align: right;
	}

	.order-details .wrapper .item .conter .copy {
		font-size: 20rpx;
		color: #333;
		border-radius: 3rpx;
		border: 1rpx solid #666;
		padding: 3rpx 15rpx;
		margin-left: 24rpx;
	}

	.order-details .wrapper .actualPay {
		border-top: 1rpx solid #eee;
		margin-top: 30rpx;
		padding-top: 30rpx;
	}

	.order-details .wrapper .actualPay .money {
		font-weight: bold;
		font-size: 30rpx;
	}

	.order-details .footer {
		width: 100%;
		height: 100rpx;
		position: fixed;
		bottom: 0;
		left: 0;
		background-color: #fff;
		padding: 0 30rpx;
		box-sizing: border-box;
	}

	.order-details .footer .bnt {
		width: 176rpx;
		height: 60rpx;
		text-align: center;
		line-height: 60rpx;
		border-radius: 50rpx;
		color: #fff;
		font-size: 27rpx;
	}

	.order-details .footer .bnt.cancel {
		color: #aaa;
		border: 1rpx solid #ddd;
	}

	.order-details .footer .bnt~.bnt {
		margin-left: 18rpx;
	}

	.order-details .writeOff {
		background-color: #fff;
		margin-top: 13rpx;
		padding-bottom: 30rpx;
	}

	.order-details .writeOff .title {
		font-size: 30rpx;
		color: #282828;
		height: 87rpx;
		border-bottom: 1px solid #f0f0f0;
		padding: 0 30rpx;
		line-height: 87rpx;
	}

	.order-details .writeOff .grayBg {
		background-color: #f2f5f7;
		width: 590rpx;
		height: 384rpx;
		border-radius: 20rpx 20rpx 0 0;
		margin: 50rpx auto 0 auto;
		padding-top: 55rpx;
		position: relative;
	}

	.order-details .writeOff .grayBg .written {
		position: absolute;
		top: 0;
		right: 0;
		width: 60rpx;
		height: 60rpx;
	}

	.order-details .writeOff .grayBg .written image {
		width: 100%;
		height: 100%;
	}

	.order-details .writeOff .grayBg .pictrue {
		width: 290rpx;
		height: 290rpx;
		margin: 0 auto;
	}

	.order-details .writeOff .grayBg .pictrue image {
		width: 100%;
		height: 100%;
		display: block;
	}

	.order-details .writeOff .gear {
		width: 590rpx;
		height: 30rpx;
		margin: 0 auto;
	}

	.order-details .writeOff .gear image {
		width: 100%;
		height: 100%;
		display: block;
	}

	.order-details .writeOff .num {
		background-color: #f0c34c;
		width: 590rpx;
		height: 84rpx;
		color: #282828;
		font-size: 48rpx;
		margin: 0 auto;
		border-radius: 0 0 20rpx 20rpx;
		text-align: center;
		padding-top: 4rpx;
	}

	.order-details .writeOff .rules {
		margin: 46rpx 30rpx 0 30rpx;
		border-top: 1px solid #f0f0f0;
		padding-top: 10rpx;
	}

	.order-details .writeOff .rules .item {
		margin-top: 20rpx;
	}

	.order-details .writeOff .rules .item .rulesTitle {
		font-size: 28rpx;
		color: #282828;
	}

	.order-details .writeOff .rules .item .rulesTitle .iconfont {
		font-size: 30rpx;
		color: #333;
		margin-right: 8rpx;
		margin-top: 5rpx;
	}

	.order-details .writeOff .rules .item .info {
		font-size: 28rpx;
		color: #999;
		margin-top: 7rpx;
	}

	.order-details .writeOff .rules .item .info .time {
		margin-left: 20rpx;
	}

	.order-details .map {
		height: 86rpx;
		font-size: 30rpx;
		color: #282828;
		line-height: 86rpx;
		border-bottom: 1px solid #f0f0f0;
		margin-top: 13rpx;
		background-color: #fff;
		padding: 0 30rpx;
	}

	.order-details .map .place {
		font-size: 26rpx;
		width: 176rpx;
		height: 50rpx;
		border-radius: 25rpx;
		line-height: 50rpx;
		text-align: center;
	}

	.order-details .map .place .iconfont {
		font-size: 27rpx;
		height: 27rpx;
		line-height: 27rpx;
		margin: 2rpx 3rpx 0 0;
	}

	.order-details .address .name .iconfont {
		font-size: 34rpx;
		margin-left: 10rpx;
	}

	.refund {
		padding: 0 30rpx 30rpx;
		margin-top: 24rpx;
		background-color: #fff;

		.title {
			display: flex;
			align-items: center;
			font-size: 30rpx;
			color: #333;
			height: 86rpx;
			border-bottom: 1px solid #f5f5f5;

			image {
				width: 32rpx;
				height: 32rpx;
				margin-right: 10rpx;
			}
		}

		.con {
			padding-top: 25rpx;
			font-size: 28rpx;
			color: #868686;
		}
	}
</style>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow, onHide, onReady } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
	orderInvoiceDetail,
	orderAgain,
	orderTake,
	orderDel,
	orderCancel
} from '@/api/order.js';
import {
	openOrderRefundSubscribe
} from '@/utils/subscribeMessage.js';
import home from '@/components/home';
import payment from '@/components/payment';
import orderGoods from "@/components/orderGoods";
import ClipboardJS from "@/plugin/clipboard/clipboard.js";
import {
	toLogin
} from '@/libs/login.js';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const order_id = ref('');
const evaluate = ref(0);
const cartInfo = ref([]); //购物车产品
const orderInfo = ref({
	system_store: {},
	_status: {}
}); //订单详情
const system_store = ref({});
const isGoodsReturn = ref(false); //是否为退款订单
const status = ref({}); //订单底部按钮状态
const isClose = ref(false);
const pay_close = ref(false);
const pay_order_id = ref('');
const totalPrice = ref('0');
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权

// ---------- 生命周期 ----------
onLoad((options) => {
	if (options.order_id) {
		order_id.value = options.order_id;
	}
});
onShow(() => {
	if (isLogin.value) {
		getOrderInfo();
	} else {
		toLogin();
	}
});
onHide(() => {
	isClose.value = true;
});
onReady(() => {
	// #ifdef H5
	nextTickH5Clipboard();
	// #endif
});

// #ifdef H5
function nextTickH5Clipboard() {
	const clipboard = new ClipboardJS(".copy-data");
	clipboard.on("success", () => {
		// 原代码此处误用 this.$util（普通函数中 this 为 undefined），修正为直接调用
		util.Tips({
			title: i18n.t(`复制成功`)
		});
	});
}
// #endif

// ---------- methods ----------
function goGoodCall() {
	uni.navigateTo({
		url: `/pages/extension/customer_list/chat?orderId=${order_id.value}`
	})
}
function openSubcribe(e) {
	let page = e;
	uni.showLoading({
		title: i18n.t(`正在加载中`),
	})
	openOrderRefundSubscribe().then(res => {
		uni.hideLoading();
		uni.navigateTo({
			url: page,
		});
	}).catch(() => {
		uni.hideLoading();
	});
}
/**
 * 事件回调（原 this[action] 动态派发改为查表；当前页面未定义支付回调方法，保持空表即可）
 *
 */
const actionMap = {};
function onChangeFun(e) {
	let opt = e;
	let action = opt.action || null;
	let value = opt.value != undefined ? opt.value : null;
	(action && actionMap[action]) && actionMap[action](value);
}
/**
 * 拨打电话
 */
function makePhone() {
	uni.makePhoneCall({
		phoneNumber: system_store.value.phone
	})
}
/**
 * 打开地图
 *
 */
function showMaoLocation() {
	if (!system_store.value.latitude || !system_store.value.longitude) return util.Tips({
		title: i18n.t(`缺少经纬度信息无法查看地图！`)
	});
	uni.openLocation({
		latitude: parseFloat(system_store.value.latitude),
		longitude: parseFloat(system_store.value.longitude),
		scale: 8,
		name: system_store.value.name,
		address: system_store.value.address + system_store.value.detailed_address,
		success: function() {

		},
	});
}
/**
 * 登录授权回调
 *
 */
function onLoadFun() {
	getOrderInfo();
}
/**
 * 获取订单详细信息
 *
 */
function getOrderInfo() {
	uni.showLoading({
		title: i18n.t(`正在加载中`)
	});
	orderInvoiceDetail(order_id.value).then(res => {
		const data = res.data || {};
		const statusInfo = data._status || {};
		uni.hideLoading();
		orderInfo.value = Object.assign({ system_store: {}, _status: {} }, data);
		cartInfo.value = Array.isArray(data.cartInfo) ? data.cartInfo : [];
		evaluate.value = statusInfo._type == 3 ? 3 : 0;
		system_store.value = data.system_store || {};
		if (Number(orderInfo.value.refund_status) !== 0) {
			isGoodsReturn.value = true;
		}
		getOrderStatus();
	}).catch(err => {
		uni.hideLoading();
		util.Tips({
			title: err
		});
	});
}
/**
 *
 * 剪切订单号
 */
// #ifndef H5
function copy() {
	uni.setClipboardData({
		data: orderInfo.value.order_id || orderInfo.value.order_no || ''
	});
}
// #endif
/**
 * 打电话
 */
function goTel() {
	uni.makePhoneCall({
		phoneNumber: orderInfo.value.delivery_id
	})
}
/**
 * 设置底部按钮
 *
 */
function getOrderStatus() {
	let orderInfoCur = orderInfo.value || {},
		_status = orderInfoCur._status || {
			_type: 0
		},
		statusCur = {};
	let type = parseInt(_status._type),
		delivery_type = orderInfoCur.delivery_type,
		seckill_id = orderInfoCur.seckill_id ? parseInt(orderInfoCur.seckill_id) : 0,
		bargain_id = orderInfoCur.bargain_id ? parseInt(orderInfoCur.bargain_id) : 0,
		combination_id = orderInfoCur.combination_id ? parseInt(orderInfoCur.combination_id) : 0;
	statusCur = {
		type: type == 9 ? -9 : type,
		class_status: 0
	};
	if (type == 1 && combination_id > 0) statusCur.class_status = 1; //查看拼团
	if (type == 2 && delivery_type == 'express') statusCur.class_status = 2; //查看物流
	if (type == 2) statusCur.class_status = 3; //确认收货
	if (type == 4 || type == 0) statusCur.class_status = 4; //删除订单
	if (!seckill_id && !bargain_id && !combination_id && (type == 3 || type == 4)) statusCur.class_status = 5; //再次购买
	status.value = statusCur;
}
/**
 * 去拼团详情
 *
 */
function goJoinPink() {
	uni.navigateTo({
		url: '/pages/activity/goods_combination_status/index?id=' + orderInfo.value.pink_id,
	});
}
/**
 * 再此购买
 *
 */
function goOrderConfirm() {
	orderAgain(orderInfo.value.order_id).then(res => {
		return uni.navigateTo({
			url: '/pages/center/order/create/index?new=1&cartId=' + res.data.cateId
		});
	});
}
function confirmOrder() {
	uni.showModal({
		title: i18n.t(`确认收货`),
		content: i18n.t(`为保障权益，请收到货确认无误后，再确认收货`),
		success: function(res) {
			if (res.confirm) {
				orderTake(order_id.value).then(res => {
					return util.Tips({
						title: i18n.t(`操作成功`),
						icon: 'success'
					}, function() {
						getOrderInfo();
					});
				}).catch(err => {
					return util.Tips({
						title: err
					});
				})
			}
		}
	})
}
/**
 *
 * 删除订单
 */
function delOrder() {
	orderDel(order_id.value).then(res => {
		return util.Tips({
			title: i18n.t(`删除成功`),
			icon: 'success'
		}, {
			tab: 3,
			url: 1
		});
	}).catch(err => {
		return util.Tips({
			title: err
		});
	});
}
function cancelOrder() {
	// 原代码此处误用未定义的 that.$t，修正为直接读取
	uni.showModal({
		title: i18n.t(`提示`),
		content: i18n.t(`确认取消该订单`),
		success: function(res) {
			if (res.confirm) {
				orderCancel(orderInfo.value.order_id)
					.then((data) => {
						util.Tips({
							title: data.msg
						}, {
							tab: 3
						})
					})
					.catch(() => {
						getDetail();
					});
			} else if (res.cancel) {
			}
		}
	});
}
</script>

<style>
	.qs-btn {
		width: auto;
		height: 60rpx;
		text-align: center;
		line-height: 60rpx;
		border-radius: 50rpx;
		color: #fff;
		font-size: 27rpx;
		padding: 0 3%;
		color: #aaa;
		border: 1px solid #ddd;
		margin-right: 20rpx;
	}
	.acea-row {
		flex-wrap: nowrap;
	}
	.wrapper .item .conter {
		width: 396rpx;
	}
</style>
