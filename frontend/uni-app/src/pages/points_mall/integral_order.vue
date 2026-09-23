<template>
	<view :style="colorStyle">
		<view class='order-submission'>
			<view class="allAddress" :style="store_self_mention ? '':'padding-top:10rpx'">
				<view class='address acea-row row-between-wrapper' @tap='onAddress' v-if='shippingType == 0'>
					<view class='addressCon' v-if="addressInfo.real_name">
						<view class='name'>{{addressInfo.real_name}}
							<text class='phone'>{{addressInfo.phone}}</text>
						</view>
						<view class="line1">
							<text class='default font-num'
								v-if="addressInfo.is_default">{{$t(`[默认]`)}}</text>{{addressInfo.province}}{{addressInfo.city}}{{addressInfo.district}}{{addressInfo.detail}}
						</view>
						<!-- <view class='setaddress'>设置收货地址</view> -->
					</view>
					<view class='addressCon' v-else>
						<view class='setaddress'>{{$t(`设置收货地址`)}}</view>
					</view>
					<view class='iconfont icon-jiantou'></view>
				</view>
				<view class='line'>
					<image src='/static/images/line.jpg'></image>
				</view>
			</view>
			<view class="orderGoods">
				<view class='total'>{{$t(`共`)}}{{resData.num}}{{$t(`件商品`)}}</view>
				<view class='goodWrapper'>
					<view class='item acea-row row-between-wrapper' @click="jumpCon(cartInfo.product_id)">
						<view class='pictrue'>
							<image :src='cartInfo.image'></image>
						</view>
						<view class='text'>
							<view class='acea-row row-between-wrapper'>
								<view class='name line1'>{{cartInfo.store_name}}</view>
								<view class='num'>x {{resData.num}}</view>
							</view>
							<view class='attr line1' v-if="cartInfo.suk">{{cartInfo.suk}}
							</view>
							<view class='money font-color'>
								{{cartInfo.price}} {{$t(`积分`)}}
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class='wrapper'>
				<view class='item acea-row row-between-wrapper'>
					<view>{{$t(`可用积分`)}}</view>
					<view class='discount'>{{resData.integral}}
					</view>
				</view>
				<view class='item acea-row row-between-wrapper'>
					<view>{{$t(`快递费用`)}}</view>
					<view class='discount'>{{$t(`免运费`)}}
					</view>
				</view>
				<view class='item' v-if="textareaStatus">
					<view>{{$t(`备注信息`)}}</view>
					<view class="placeholder-textarea">
						<textarea ref='getFocus' v-if="coupon.coupon===false" :focus="textFocus" @input='bindHideKeyboard' value=""
							name="mark">
						</textarea>
						<view class="placeholder" @click="clickTextArea" v-show="!mark">
							{{$t(`请添加备注（150字以内）`)}}
						</view>
					</view>
				</view>
			</view>
			<view style='height:120rpx;'></view>
			<view class='footer acea-row row-between-wrapper'>
				<view>{{$t(`合计`)}}：
					<text class='font-color'>{{resData.total_price || 0}}{{$t(`积分`)}}</text>
				</view>
				<view class='settlement' style='z-index:100' @tap="goPay">{{$t(`立即兑换`)}}</view>
			</view>
		</view>
		<view class="alipaysubmit" v-html="formContent"></view>
		<couponListWindow :coupon='coupon' @ChangCouponsClone="ChangCouponsClone" :openType='openType' :cartId='cartId'
			@ChangCoupons="ChangCoupons"></couponListWindow>
		<addressWindow ref="addressWindowRef" @changeTextareaStatus="changeTextareaStatus" :news='news' :address='address'
			:pagesUrl="pagesUrl" @OnChangeAddress="OnChangeAddress" @changeClose="changeClose"></addressWindow>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<home v-show="!invShow"></home>
	</view>
</template>
<script setup>
	import { ref, watch, nextTick } from 'vue';
	import { onLoad, onShow } from '@dcloudio/uni-app';
	import {
		integralOrderConfirm,
		integralOrderCreate,
	} from '@/api/activity.js';
	import {
		getAddressDefault,
		getAddressDetail,
		invoiceList,
		invoiceOrder
	} from '@/api/user.js';
	import {
		storeListApi
	} from '@/api/store.js';
	import {
		CACHE_LONGITUDE,
		CACHE_LATITUDE
	} from '@/config/cache.js';
	import couponListWindow from '@/components/couponListWindow';
	import addressWindow from '@/components/addressWindow';
	import orderGoods from '@/components/orderGoods';
	import home from '@/components/home';
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
	// #ifdef H5
	import Auth from '@/libs/wechat';
	// #endif
	// 统一支付结果页路由常量
	import routes from '@/dynamic-pages/api/routes.js';

	const { colorStyle } = useColorTheme();

	const appStore = useAppStore();
	const { isLogin } = storeToRefs(appStore);

	// ---------- data ----------
	const textFocus = ref(false);
	const textareaStatus = ref(true);
	//支付方式
	const cartArr = ref([{
			"name": i18n.t(`微信支付`),
			"icon": "icon-weixin2",
			value: 'weixin',
			title: i18n.t(`使用微信快捷支付`),
			payStatus: 1,
		},
		{
			"name": i18n.t(`支付宝支付`),
			"icon": "icon-zhifubao",
			value: 'alipay',
			title: i18n.t(`使用线上支付宝支付`),
			payStatus: 1,
		},
		{
			"name": i18n.t(`余额支付`),
			"icon": "icon-yuezhifu",
			value: 'yue',
			title: i18n.t(`可用余额:`),
			payStatus: 1,
		},
		{
			"name": i18n.t(`线下支付`),
			"icon": "icon-yuezhifu1",
			value: 'offline',
			title: i18n.t(`选择线下付款方式`),
			payStatus: 2,
		}
	]);
	const formContent = ref('');
	const payType = ref('weixin'); //支付方式
	const openType = ref(1); //优惠券打开方式 1=使用
	const active = ref(0); //支付方式切换
	const coupon = ref({
		coupon: false,
		list: [],
		statusTile: i18n.t(`立即使用`)
	}); //优惠券组件
	const address = ref({
		address: false
	}); //地址组件
	const addressInfo = ref({}); //地址信息
	const pinkId = ref(0); //拼团id
	const addressId = ref(0); //地址id
	const couponId = ref(0); //优惠券id
	const cartId = ref(''); //购物车id
	const BargainId = ref(0);
	const combinationId = ref(0);
	const seckillId = ref(0);
	const userInfo = ref({}); //用户信息
	const coupon_price = ref(0); //优惠券抵扣金额
	const useIntegral = ref(false); //是否使用积分
	const integral_price = ref(0); //积分抵扣金额
	const integral = ref(0);
	const ChangePrice = ref(0); //使用积分抵扣变动后的金额
	const formIds = ref([]); //收集formid
	const status = ref(0);
	const is_address = ref(false);
	const toPay = ref(false); //修复进入支付时页面隐藏从新刷新页面
	const shippingType = ref(0);
	const system_store = ref({});
	const storePostage = ref(0);
	const contacts = ref('');
	const contactsTel = ref('');
	const mydata = ref({});
	const storeList = ref([]);
	const store_self_mention = ref(0);
	const cartInfo = ref({});
	const priceGroup = ref({});
	const animated = ref(false);
	const totalPrice = ref(0);
	const integralRatio = ref("0");
	const pagesUrl = ref("");
	const orderKey = ref("");
	// usableCoupon: {},
	const offlinePostage = ref("");
	const isAuto = ref(false); //没有授权的不会自动授权
	const isShowAuth = ref(false); //是否隐藏授权
	const from = ref('');
	const news = ref(1);

	const invTitle = ref(i18n.t(`不开发票`));
	const special_invoice = ref(false);
	const invoice_func = ref(false);
	const header_type = ref('');
	const invShow = ref(false);
	const invList = ref([]);
	const invChecked = ref('');
	const urlQuery = ref('');
	const pay_close = ref(false);
	const resData = ref({});
	const mark = ref('');
	// 原 this.unique/this.num/this.couponTitle（未在 data 声明的实例属性），保留为 ref
	const unique = ref('');
	const num = ref('');
	const couponTitle = ref('');
	// 模板 ref：地址弹窗组件
	const addressWindowRef = ref(null);

	onLoad((options) => {
		// #ifdef H5
		from.value = Auth.isWeixin() ? 'weixin' : 'weixinh5'
		// #endif
		// #ifdef MP
		from.value = 'routine'
		// #endif
		if (!options.unique) return util.Tips({
			title: i18n.t(`请选择要购买的商品`)
		}, {
			tab: 3,
			url: 1
		});
		unique.value = options.unique
		num.value = options.num
		couponId.value = options.couponId || 0;
		pinkId.value = options.pinkId ? parseInt(options.pinkId) : 0;
		addressId.value = options.addressId || 0;
		cartId.value = options.cartId;
		is_address.value = options.is_address ? true : false;
		news.value = !options.new || options.new === '0' ? 0 : 1;
		invChecked.value = options.invoice_id || '';
		header_type.value = options.header_type || '1';
		couponTitle.value = options.couponTitle || i18n.t(`请选择`)
		// #ifndef APP-PLUS
		textareaStatus.value = true;
		// #endif
		if (isLogin.value && toPay.value == false) {
			getaddressInfo();
			getConfirm();
			nextTick(function() {
				addressWindowRef.value.getAddressList();
			})
		} else {
			toLogin();
		}
	});
	/**
	 * 生命周期函数--监听页面显示
	 */
	onShow(() => {
		uni.$on("handClick", res => {
			if (res) {
				system_store.value = res.address
			}
			// 清除监听
			uni.$off('handClick');
		})
	});

	// ---------- methods ----------
	function getInvoiceList() {
		uni.showLoading({
			title: i18n.t(`正在加载…`)
		})
		invoiceList().then(res => {
			uni.hideLoading();
			invList.value = res.data.map(item => {
				item.id = item.id.toString();
				return item;
			});
			const result = invList.value.find(item => item.id == invChecked.value);
			if (result) {
				let name = '';
				name += result.header_type === 1 ? i18n.t(`个人`) : i18n.t(`企业`);
				name += result.type === 1 ? i18n.t(`普通`) : i18n.t(`专用`);
				name += i18n.t(`发票`);
				invTitle.value = name;
			}
		}).catch(err => {
			uni.showToast({
				title: err,
				icon: 'none'
			});
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
			goPay,
			changeClose,
			computedPrice,
			ChangCouponsClone,
			changeTextareaStatus,
			OnChangeAddress,
			bindHideKeyboard,
			getInvoiceList,
			couponTap,
			car,
			onAddress,
			clickTextArea
		};
		action && handlers[action] && handlers[action](value);
	}
	function payClose() {
		pay_close.value = false;
	}
	function goPay() {
		if (!addressId.value) {
			return util.Tips({
				title: i18n.t(`请选择收货地址`)
			});
		}
		if (parseFloat(resData.value.integral) < parseFloat(cartInfo.value.price))
			return util.Tips({
				title: i18n.t(`可用积分不足！`)
			});
		let data = {
			addressId: addressId.value,
			mark: mark.value,
			unique: cartInfo.value.unique,
			num: resData.value.num
		}
		integralOrderCreate(data).then(res => {
			// 积分兑换统一跳转至支付结果页，由引擎查询订单状态自动渲染
			uni.redirectTo({
				url: `${routes.payStatus}?order_no=${res.data.result.orderId}`
			})
		}).catch(err => {
			uni.hideLoading();
			return util.Tips({
				title: err
			});
		});
	}
	// 关闭地址弹窗；
	function changeClose() {
		address.value['address'] = false;
	}
	function computedPrice() {
		let shippingTypeVal = shippingType.value;
		postOrderComputed(orderKey.value, {
			addressId: addressId.value,
			useIntegral: useIntegral.value ? 1 : 0,
			couponId: couponId.value,
			shipping_type: parseInt(shippingTypeVal) + 1,
			payType: payType.value
		}).then(res => {
			let result = res.data.result;
			if (result) {
				totalPrice.value = result.pay_price;
				integral_price.value = result.deduction_price;
				coupon_price.value = result.coupon_price;
				integral.value = useIntegral.value ? result.SurplusIntegral : userInfo.value.integral;
				priceGroup.value['payPostage'] = shippingTypeVal == 1 ? 0 : result.pay_postage;
				priceGroup.value['postageDiscount'] = result.storePostageDiscount;
			}
		})
	}
	function ChangCouponsClone() {
		coupon.value['coupon'] = false;
	}
	function changeTextareaStatus() {
		for (let i = 0, len = coupon.value.list.length; i < len; i++) {
			coupon.value.list[i].use_title = '';
			coupon.value.list[i].is_use = 0;
		}
		textareaStatus.value = true;
		status.value = 0;
		coupon.value['list'] = coupon.value.list;
	}
	/**
	 * 选择地址后改变事件
	 * @param object e
	 */
	function OnChangeAddress(e) {
		textareaStatus.value = true;
		addressId.value = e;
		address.value.address = false;
		getaddressInfo();
	}
	function bindHideKeyboard(e) {
		mark.value = e.detail.value;
	}
	/**
	 * 获取当前订单详细信息
	 *
	 */
	function getConfirm() {
		// return;
		integralOrderConfirm({
			unique: unique.value,
			num: num.value
		}).then(res => {
			resData.value = res.data;
			cartInfo.value = res.data.productInfo;
		}).catch(err => {
			return util.Tips({
				title: err
			});
		});
	}
	/*
	 * 提取砍价和拼团id
	 */
	function getBargainId() {
		let cartINfo = cartInfo.value;
		let BargainIdVal = 0;
		let combinationIdVal = 0;
		cartINfo.forEach(function(value, index, cartINfo) {
			BargainIdVal = cartINfo[index].bargain_id,
				combinationIdVal = cartINfo[index].combination_id
		})
		BargainId.value = parseInt(BargainIdVal);
		combinationId.value = parseInt(combinationIdVal);
		if (cartArr.value.length == 3 && (BargainIdVal || combinationIdVal || seckillId.value)) {
			cartArr.value[2].payStatus = 0;
		}
	}
	/*
	 * 获取默认收货地址或者获取某条地址信息
	 */
	function getaddressInfo() {
		if (addressId.value) {
			getAddressDetail(addressId.value).then(res => {
				res.data.is_default = parseInt(res.data.is_default);
				addressInfo.value = res.data || {};
				addressId.value = res.data.id || 0;
				address.value.addressId = res.data.id || 0;
			})
		} else {
			getAddressDefault().then(res => {
				res.data.is_default = parseInt(res.data.is_default);
				addressInfo.value = res.data || {};
				addressId.value = res.data.id || 0;
				address.value.addressId = res.data.id || 0;
			})
		}
	}
	function couponTap() {
		coupon.value.coupon = true;
		coupon.value.list.forEach((item, index) => {
			if (item.id == couponId.value) {
				item.is_use = 1
			} else {
				item.is_use = 0
			}
		})
		coupon.value['list'] = coupon.value.list;
	}
	function car() {
		animated.value = false;
	}
	function onAddress() {
		textareaStatus.value = false;
		address.value.address = true;
		pagesUrl.value = `/pages/points_mall/user_address?unique=${unique.value}&num=${num.value}`
	}
	function clickTextArea() {
		textFocus.value = true
		// nextTick(() => {
		// 	getFocus.value.focus()
		// })
	}
</script>

<style lang="scss" scoped>
:deep(uni-checkbox[disabled] .uni-checkbox-input){
		background-color: #eee;
	}

	.alipaysubmit {
		display: none;
	}

	.order-submission .line {
		width: 100%;
		height: 3rpx;
	}

	.order-submission .line image {
		width: 100%;
		height: 100%;
		display: block;
	}

	.order-submission .address {
		padding: 28rpx 30rpx;
		background-color: #fff;
		box-sizing: border-box;
	}

	.order-submission .address .addressCon {
		width: 610rpx;
		font-size: 26rpx;
		color: #666;
	}

	.order-submission .address .addressCon .name {
		font-size: 30rpx;
		color: #282828;
		font-weight: bold;
		margin-bottom: 10rpx;
	}

	.order-submission .address .addressCon .name .phone {
		margin-left: 50rpx;
	}

	.order-submission .address .addressCon .default {
		margin-right: 12rpx;
	}

	.order-submission .address .addressCon .setaddress {
		color: #333;
		font-size: 28rpx;
	}

	.order-submission .address .iconfont {
		font-size: 35rpx;
		color: #707070;
	}

	.order-submission .allAddress {
		width: 100%;
		background: linear-gradient(to bottom, var(--view-theme) 0%, #f5f5f5 100%);
		padding-top: 100rpx;
	}

	.order-submission .allAddress .nav {
		width: 710rpx;
		margin: 0 auto;
	}

	.order-submission .allAddress .nav .item {
		width: 355rpx;
	}

	.order-submission .allAddress .nav .item.on {
		position: relative;
		width: 250rpx;
	}

	.order-submission .allAddress .nav .item.on::before {
		position: absolute;
		bottom: 0;
		content: "快递配送";
		font-size: 28rpx;
		display: block;
		height: 0;
		width: 336rpx;
		border-width: 0 20rpx 80rpx 0;
		border-style: none solid solid;
		border-color: transparent transparent #fff;
		z-index: 2;
		border-radius: 7rpx 30rpx 0 0;
		text-align: center;
		line-height: 80rpx;
	}

	.order-submission .allAddress .nav .item:nth-of-type(2).on::before {
		content: "到店自提";
		border-width: 0 0 80rpx 20rpx;
		border-radius: 30rpx 7rpx 0 0;
	}

	.order-submission .allAddress .nav .item.on2 {
		position: relative;
	}

	.order-submission .allAddress .nav .item.on2::before {
		position: absolute;
		bottom: 0;
		content: "到店自提";
		font-size: 28rpx;
		display: block;
		height: 0;
		width: 400rpx;
		border-width: 0 0 60rpx 60rpx;
		border-style: none solid solid;
		border-color: transparent transparent #f7c1bd;
		border-radius: 40rpx 6rpx 0 0;
		text-align: center;
		line-height: 60rpx;
	}

	.order-submission .allAddress .nav .item:nth-of-type(1).on2::before {
		content: "快递配送";
		border-width: 0 60rpx 60rpx 0;
		border-radius: 6rpx 40rpx 0 0;
	}

	.order-submission .allAddress .address {
		width: 710rpx;
		height: 150rpx;
		margin: 0 auto;
	}

	.order-submission .allAddress .line {
		width: 710rpx;
		margin: 0 auto;
	}

	.order-submission .wrapper .item .discount .placeholder {
		color: #ccc;
	}

	.placeholder-textarea {
		position: relative;

		.placeholder {
			position: absolute;
			color: #ccc;
			top: 26rpx;
			left: 30rpx;
		}
	}

	.order-submission .wrapper {
		background-color: #fff;
		margin-top: 13rpx;
	}

	.order-submission .wrapper .item {
		padding: 27rpx 30rpx;
		font-size: 30rpx;
		color: #282828;
		border-bottom: 1px solid #f0f0f0;
	}

	.order-submission .wrapper .item .discount {
		font-size: 30rpx;
		color: #999;
	}

	.order-submission .wrapper .item .discount input {
		text-align: end;
	}

	.order-submission .wrapper .item .discount .iconfont {
		color: #515151;
		font-size: 30rpx;
		margin-left: 15rpx;
	}

	.order-submission .wrapper .item .discount .num {
		font-size: 32rpx;
		margin-right: 20rpx;
	}

	.order-submission .wrapper .item .shipping {
		font-size: 30rpx;
		color: #999;
		position: relative;
		padding-right: 58rpx;
	}

	.order-submission .wrapper .item .shipping .iconfont {
		font-size: 35rpx;
		color: #707070;
		position: absolute;
		right: 0;
		top: 50%;
		transform: translateY(-50%);
		margin-left: 30rpx;
	}

	.order-submission .wrapper .item textarea {
		background-color: #f9f9f9;
		width: 690rpx;
		height: 140rpx;
		border-radius: 3rpx;
		margin-top: 30rpx;
		padding: 25rpx 28rpx;
		box-sizing: border-box;
	}

	.order-submission .wrapper .item .placeholder {
		color: #ccc;
	}

	.order-submission .wrapper .item .list {
		margin-top: 35rpx;
	}


	.order-submission .moneyList {
		margin-top: 12rpx;
		background-color: #fff;
		padding: 30rpx;
	}

	.order-submission .moneyList .item {
		font-size: 28rpx;
		color: #282828;
	}

	.order-submission .moneyList .item~.item {
		margin-top: 20rpx;
	}

	.order-submission .moneyList .item .money {
		color: #868686;
	}

	.order-submission .footer {
		width: 100%;
		height: 100rpx;
		background-color: #fff;
		padding: 0 30rpx;
		font-size: 28rpx;
		color: #333;
		box-sizing: border-box;
		position: fixed;
		bottom: 0;
		left: 0;
	}

	.order-submission .footer .settlement {
		font-size: 30rpx;
		color: #fff;
		width: 240rpx;
		height: 70rpx;
		background-color: var(--view-theme);
		border-radius: 50rpx;
		text-align: center;
		line-height: 70rpx;
	}

	.footer .transparent {
		opacity: 0
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

	.pictrue image {
		width: 130rpx;
		height: 130rpx;
	}
</style>
