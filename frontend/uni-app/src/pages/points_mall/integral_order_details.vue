<template>
	<view :style="colorStyle">
		<view class='order-details'>
			<view>
				<view class='address'>
					<view class='name'>{{cartInfo.real_name}}<text class='phone'>{{cartInfo.user_phone}}</text></view>
					<view>{{cartInfo.user_address}}</view>
				</view>
				<view class='line'>
					<image src='@/static/images/line.jpg'></image>
				</view>
			</view>
			<view class="orderGoods">
				<view class='total'>{{$t(`共`)}}{{cartInfo.total_num}}{{$t(`件商品`)}}</view>
				<view class='goodWrapper'>
					<view class='item acea-row row-between-wrapper' @click="jumpCon(cartInfo.product_id, cartInfo.activity_id)">
						<view class='pictrue'>
							<image :src='cartInfo.image'></image>
						</view>
						<view class='text'>
							<view class='acea-row row-between-wrapper'>
								<view class='name line1'>{{cartInfo.store_name}}</view>
								<view class='num'>x {{cartInfo.total_num}}</view>
							</view>
							<view class='attr line1'>{{cartInfo.suk}}
							</view>
							<view class='money font-num'>
								{{cartInfo.price}}{{$t(`积分`)}}
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class='wrapper'>
				<view class='item acea-row row-between'>
					<view>{{$t(`订单编号`)}}：</view>
					<view class='conter acea-row row-middle row-right'>{{cartInfo.order_id}}
						<!-- #ifndef H5 -->
						<text class='copy' @tap='copy'>{{$t(`复制`)}}</text>
						<!-- #endif -->
						<!-- #ifdef H5 -->
						<text class='copy copy-data' :data-clipboard-text="cartInfo.order_id">{{$t(`复制`)}}</text>
						<!-- #endif -->
					</view>
				</view>
				<view class='item acea-row row-between'>
					<view>{{$t(`订单状态`)}}：</view>
					<view class='conter'>{{$t(cartInfo.status_name)}}</view>
				</view>
				<view class='item acea-row row-between'>
					<view>{{$t(`下单时间`)}}：</view>
					<view class='conter'>{{cartInfo.add_time}}</view>
				</view>
				<view class='item acea-row row-between'>
					<view>{{$t(`支付积分`)}}：</view>
					<view class='conter'>{{cartInfo.total_price}}</view>
				</view>
				<view class='item acea-row row-between' v-if="cartInfo.mark">
					<view>{{$t(`订单备注`)}}：</view>
					<view class='conter'>{{cartInfo.mark}}</view>
				</view>
				<view class='item acea-row row-between' v-if="cartInfo.remark">
					<view>{{$t(`商家备注`)}}：</view>
					<view class='conter'>{{cartInfo.remark}}</view>
				</view>
				<view class='item acea-row row-between' v-if="cartInfo.delivery_type === 'express'">
					<view>{{$t(`快递单号`)}}：</view>
					<view class='conter'>{{cartInfo.delivery_id}}</view>
				</view>
				<view class='item acea-row row-between' v-if="cartInfo.delivery_type === 'express'">
					<view>{{$t(`快递公司`)}}：</view>
					<view class='conter'>{{cartInfo.delivery_name}}</view>
				</view>
				<view class='item acea-row row-between' v-if="cartInfo.delivery_type === 'send'">
					<view>{{$t(`送货人电话`)}}：</view>
					<view class='conter'>{{cartInfo.delivery_id}}</view>
				</view>
				<view class='item acea-row row-between' v-if="cartInfo.delivery_type === 'send'">
					<view>{{$t(`配送人姓名`)}}：</view>
					<view class='conter'>{{cartInfo.delivery_name}}</view>
				</view>
				<view class='item acea-row row-between' v-if="cartInfo.delivery_type === 'fictitious'">
					<view>{{$t(`虚拟发货`)}}：</view>
					<view class='conter'>{{$t(`已发货，请注意查收`)}}</view>
				</view>
				<view class='item acea-row row-between' v-if="cartInfo.fictitious_content">
					<view>{{$t(`虚拟备注`)}}：</view>
					<view class='conter'>{{cartInfo.fictitious_content}}</view>
				</view>
				<view class='item acea-row row-between' v-if="cartInfo.delivery_type === 'send'">
					<view>{{$t(`配送核销码`)}}：</view>
					<view class='conter'>{{cartInfo.verify_code}}</view>
				</view>
			</view>

			<view style='height:120rpx;'></view>
			<view class='footer acea-row row-right row-middle'>
				<view class='bnt bg-color' v-if="cartInfo.status==3" @tap='delOrder'>{{$t(`删除订单`)}}</view>
				<navigator :render-link="false" class='bnt cancel' hover-class='none'
					v-if="cartInfo.delivery_id && cartInfo.delivery_type === 'express'"
					:url="'/pages/points_mall/logistics_details?order_id='+ cartInfo.order_id">{{$t(`查看物流`)}}
				</navigator>
				<view class='bnt bg-color' v-if="cartInfo.status==2" @tap='confirmOrder'>{{$t(`确认收货`)}}</view>
			</view>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
	import { ref, nextTick } from 'vue';
	import { onLoad, onShow, onHide, onReady } from '@dcloudio/uni-app';
	import {
		getCenterOrderDetail,
		takeOrder,
		delOrder as delOrderApi
	} from '@/api/order-center.js'
	import {
		openOrderRefundSubscribe
	} from '@/utils/subscribeMessage.js';
	import {
		getUserInfo as getUserInfoApi
	} from '@/api/user.js';
	import home from '@/components/home';
	import orderGoods from "@/components/orderGoods";
	import ClipboardJS from "@/plugin/clipboard/clipboard.js";
	import {
		toLogin
	} from '@/libs/login.js';
	import { useColorTheme } from '@/composables/useColorTheme';
	import { useAppStore } from '@/store/modules/app';
	import { storeToRefs } from 'pinia';
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
	const payMode = ref([
		// #ifdef H5 || APP-PLUS
		{
			name: i18n.t(`微信支付`),
			icon: "icon-weixinzhifu",
			value: 'weixin',
			title: i18n.t(`使用微信快捷支付`),
			payStatus: true,
		},
		{
			name: i18n.t(`支付宝支付`),
			icon: 'icon-zhifubao',
			value: 'alipay',
			title: i18n.t(`使用线上支付宝支付`),
			payStatus: true
		},
		// #endif
		// #ifndef H5 || APP-PLUS
		{
			name: i18n.t(`微信支付`),
			icon: "icon-weixinzhifu",
			value: 'weixin',
			title: i18n.t(`使用微信快捷支付`),
			payStatus: true,
		},
		// #endif
		{
			name: i18n.t(`余额支付`),
			icon: "icon-yuezhifu",
			value: 'yue',
			title: i18n.t(`当前可用余额：`),
			number: 0,
			payStatus: true
		},
	]);
	const pay_close = ref(false);
	const pay_order_id = ref('');
	const totalPrice = ref('0');
	const isAuto = ref(false); //没有授权的不会自动授权
	const isShowAuth = ref(false); //是否隐藏授权
	const routineContact = ref('0')

	onLoad((options) => {
		if (options.order_id) {
			order_id.value = options.order_id;
		}
	});

	onShow(() => {
		if (isLogin.value) {
			getOrderInfo();
			// getUserInfo();
		} else {
			toLogin();
		}
	});

	onHide(() => {
		isClose.value = true;
	});

	onReady(() => {
		// #ifdef H5 || APP-PLUS
		nextTick(function() {
			const clipboard = new ClipboardJS(".copy-data");
			clipboard.on("success", () => {
				util.Tips({
					title: i18n.t(`复制成功`)
				});
			});
		});
		// #endif
	});

	// ---------- methods ----------
	function jumpCon(id, activityId) {
		uni.navigateTo({
			url: `/pages/activity/integral/index?id=${id}&activity_id=${activityId || 0}`
		})
	}
	function goGoodCall() {
		uni.navigateTo({
			url: `/pages/extension/customer_list/chat?orderId=${order_id.value}`
		})
	}
	function openSubcribe(e) {
		let page = e;
		uni.showLoading({
			title: i18n.t(`正在加载`),
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
	 * 事件回调（原 this[action] 动态分发改为显式映射）
	 *
	 */
	function onChangeFun(e) {
		let opt = e;
		let action = opt.action || null;
		let value = opt.value != undefined ? opt.value : null;
		const handlers = {
			payClose,
			pay_open,
			pay_complete,
			pay_fail,
			onLoadFun,
			makePhone,
			showMaoLocation,
			goJoinPink,
			confirmOrder,
			delOrder,
			openSubcribe,
			goGoodCall,
			jumpCon,
			goTel,
			copy,
			getOrderStatus,
			getUserInfo
		};
		(action && handlers[action]) && handlers[action](value);
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
	 * 关闭支付组件
	 *
	 */
	function payClose() {
		pay_close.value = false;
	}
	/**
	 * 打开支付组件
	 *
	 */
	function pay_open() {
		pay_close.value = true;
		pay_order_id.value = orderInfo.value.order_id;
		totalPrice.value = orderInfo.value.pay_price;
	}
	/**
	 * 支付成功回调
	 *
	 */
	function pay_complete() {
		pay_close.value = false;
		pay_order_id.value = '';
		getOrderInfo();
	}
	/**
	 * 支付失败回调
	 *
	 */
	function pay_fail() {
		pay_close.value = false;
		pay_order_id.value = '';
	}
	/**
	 * 登录授权回调
	 *
	 */
	function onLoadFun() {
		getOrderInfo();
		getUserInfo();
	}
	/**
	 * 获取用户信息
	 *
	 */
	function getUserInfo() {
		getUserInfoApi().then(res => {
			// #ifdef H5 || APP-PLUS
			payMode.value[2].number = res.data.now_money;
			// #endif
			// #ifdef MP
			payMode.value[1].number = res.data.now_money;
			// #endif
		})
	}
	/**
	 * 获取订单详细信息
	 * 从统一订单中心查询
	 */
	function getOrderInfo() {
		uni.showLoading({
			title: i18n.t(`正在加载中`)
		});
		getCenterOrderDetail(order_id.value).then(res => {
			uni.hideLoading();
			// v8.1 单层结构：接口只返回 {page_config: {sections, extensions}}，渲染数据直填在 sections.props
			const pageConfig = (res.data && res.data.page_config) || { sections: [], extensions: {} };
			const propsMap = {};
			(pageConfig.sections || []).forEach(s => {
				propsMap[s.key] = s.props || {};
			});
			const extraInfo = (pageConfig.extensions && pageConfig.extensions.extra_info) || {};
			const activityInfo = extraInfo.activity_info || {};
			const skuInfo = extraInfo.sku_info || {};
			const addressInfo = extraInfo.address_info || {};
			const deliveryInfo = extraInfo.delivery_info || {};
			const unitPrice = activityInfo.integral_unit_price || 0;
			const productProps = propsMap['product'] || {};
			const product = productProps.product || {};
			const totalNum = product.total_num || 1;
			const orderInfoFields = (propsMap['order_info'] && propsMap['order_info'].fields) || {};
			const statusProps = propsMap['status'] || {};
			// 适配旧模板需要的数据格式
			cartInfo.value = {
				order_id: orderInfoFields.order_no,
				order_no: orderInfoFields.order_no,
				status_code: statusProps.code,
				status_text: statusProps.text,
				status: statusProps.code === 'processing' ? 2 :
					statusProps.code === 'completed' ? 3 : 1,
				status_name: statusProps.text,
				store_name: product.title,
				image: product.image,
				total_num: totalNum,
				real_name: addressInfo.real_name || addressInfo.name || '',
				user_phone: addressInfo.phone || '',
				user_address: addressInfo.address || '',
				suk: skuInfo.suk || '',
				price: unitPrice,
				total_price: unitPrice * totalNum,
				delivery_type: extraInfo.delivery_type || deliveryInfo.delivery_type || '',
				delivery_id: extraInfo.delivery_id || deliveryInfo.delivery_id || '',
				delivery_name: extraInfo.delivery_name || deliveryInfo.delivery_name || '',
				add_time: orderInfoFields.add_time ? util.formatDate(orderInfoFields.add_time, 'yyyy-MM-dd hh:mm') : '',
				mark: orderInfoFields.remark || extraInfo.mark || '',
				remark: extraInfo.remark || '',
				fictitious_content: extraInfo.fictitious_content || '',
				verify_code: extraInfo.verify_code || '',
				product_id: orderInfoFields.source_id || '',
				activity_id: activityInfo.activity_id || 0,
			};
		}).catch(err => {
			uni.hideLoading();
			util.Tips({
				title: err
			}, '/pages/points_mall/exchange_record');
		});
	}
	/**
	 *
	 * 剪切订单号
	 */
	// #ifndef H5
	function copy() {
		uni.setClipboardData({
			data: cartInfo.value.order_id
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
		let orderInfoVal = orderInfo.value || {},
			_status = orderInfoVal._status || {
				_type: 0
			},
			statusVal = {};
		let type = parseInt(_status._type),
			delivery_type = orderInfoVal.delivery_type,
			seckill_id = orderInfoVal.seckill_id ? parseInt(orderInfoVal.seckill_id) : 0,
			bargain_id = orderInfoVal.bargain_id ? parseInt(orderInfoVal.bargain_id) : 0,
			combination_id = orderInfoVal.combination_id ? parseInt(orderInfoVal.combination_id) : 0;
		statusVal = {
			type: type == 9 ? -9 : type,
			class_status: 0
		};
		if (type == 1 && combination_id > 0) statusVal.class_status = 1; //查看拼团
		if (type == 2 && delivery_type == 'express') statusVal.class_status = 2; //查看物流
		if (type == 2) statusVal.class_status = 3; //确认收货
		if (type == 4 || type == 0) statusVal.class_status = 4; //删除订单
		if (!seckill_id && !bargain_id && !combination_id && (type == 3 || type == 4)) statusVal.class_status =
			5; //再次购买
		status.value = statusVal;
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
	function confirmOrder() {
		uni.showModal({
			title: i18n.t(`确认收货`),
			content: i18n.t(`为保障权益，请收到货确认无误后，再确认收货`),
			success: (res) => {
				if (res.confirm) {
					takeOrder(order_id.value).then(res => {
						return util.Tips({
							title: i18n.t(`操作成功`),
							icon: 'success'
						}, () => {
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
		delOrderApi(order_id.value).then(res => {
			return util.Tips({
				title: i18n.t(`删除成功`),
				icon: 'success'
			}, {
				tab: 5,
				url: '/pages/points_mall/exchange_record'
			});
		}).catch(err => {
			return util.Tips({
				title: err
			});
		});
	}
</script>

<style scoped lang="scss">
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

	.order-details .header .pictrue {
		width: 110rpx;
		height: 110rpx;
	}

	.order-details .header .pictrue image {
		width: 100%;
		height: 100%;
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
		max-width: 460rpx;
		height: max-content;
		text-align: right;
		display: flex;
		flex-wrap: wrap;
		white-space: normal;
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

	.orderGoods {
		background-color: #fff;
		margin-top: 12rpx;
	}

	.orderGoods .total {
		width: 100%;
		height: 86rpx;
		padding: 0 30rpx;
		border-bottom: 2rpx solid #f0f0f0;
		font-size: 30rpx;
		color: #282828;
		line-height: 86rpx;
		box-sizing: border-box;
	}
</style>
