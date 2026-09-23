<template>
	<view :style="colorStyle">
		<view class='logistics'>
			<view class='header acea-row row-between row-top'>
				<view class='pictrue'>
					<image :src='orderInfo.image'></image>
				</view>
				<view class='text acea-row row-between'>
					<view class='name line2'>{{orderInfo.store_name}}</view>
					<view class='money'>
						<view v-if="orderInfo.total_price != null">{{orderInfo.total_price}}{{$t(`积分`)}}</view>
						<view>x{{orderInfo.total_num}}</view>
					</view>
					<view class='line1 gray-sty'>{{orderInfo.suk}}</view>
				</view>
			</view>
			<view class='logisticsCon'>
				<view class='company acea-row row-between-wrapper'>
					<view class='picTxt acea-row row-between-wrapper'>
						<view class='iconfont icon-wuliu'></view>
						<view class='text'>
							<view><text class='name line1'>{{$t(`物流公司`)}}：</text> {{orderInfo.delivery_name}}</view>
							<view class='express line1'><text class='name'>{{$t(`快递单号`)}}：</text>
								{{orderInfo.delivery_id}}</view>
						</view>
					</view>
					<!-- #ifndef H5 -->
					<view class='copy' @tap='copyOrderId'>{{$t(`复制单号`)}}</view>
					<!-- #endif -->
					<!-- #ifdef H5 -->
					<view class='copy copy-data' :data-clipboard-text="orderInfo.delivery_id">{{$t(`复制单号`)}}</view>
					<!-- #endif -->
				</view>
				<view class='item' v-for="(item,index) in expressList" :key="index">
					<view class='circular' :class='index === 0 ? "on":""'></view>
					<view class='text' :class='index===0 ? "on-font on":""'>
						<view>{{item.status}}</view>
						<view class='data' :class='index===0 ? "on-font on":""'>{{item.time}}</view>
					</view>
				</view>
			</view>
			<recommend :hostProduct='hostProduct'></recommend>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
	</view>
</template>

<script setup>
	import { ref, watch, nextTick } from 'vue';
	import { onLoad, onReady, onPageScroll } from '@dcloudio/uni-app';
	import {
		getProductHot
	} from '@/api/store.js';
	import { getLogisticsDetails } from '@/api/activity.js';
	import { getCenterOrderDetail } from '@/api/order-center.js';
	import ClipboardJS from "@/plugin/clipboard/clipboard.js";
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		useAppStore
	} from '@/store/modules/app';
	import {
		storeToRefs
	} from 'pinia';
	import recommend from '@/components/recommend';
	import { useColorTheme } from '@/composables/useColorTheme';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif

	const { colorStyle } = useColorTheme();

	const appStore = useAppStore();
	const { isLogin } = storeToRefs(appStore);

	// ---------- data ----------
	const orderId = ref('');
	const product = ref({
		productInfo: {}
	});
	const orderInfo = ref({});
	const expressList = ref([]);
	const hostProduct = ref([]);

	// ---------- watch ----------
	watch(isLogin, (newV, oldV) => {
		if (newV) {
			getExpress();
			get_host_product();
		}
	}, { deep: true });

	onLoad((options) => {
		if (!options.order_id) return util.Tips({
			title: i18n.t(`缺少订单号`)
		});
		orderId.value = options.order_id;
		if (isLogin.value) {
			getExpress();
			get_host_product();
		} else {
			toLogin();
		}
	});

	onReady(() => {
		// #ifdef H5
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
	// 滚动监听
	onPageScroll((e) => {
		// 传入scrollTop值并触发所有easy-loadimage组件下的滚动监听事件
		uni.$emit('scroll');
	});

	// ---------- methods ----------
	/**
	 * 授权回调
	 */
	function onLoadFun() {
		getExpress();
		get_host_product();
	}
	function copyOrderId() {
		uni.setClipboardData({
			data: orderInfo.value.delivery_id
		});
	}
	async function getExpress() {
		try {
			// 详情先行，复用其物流缓存，避免冷缓存时重复调用物流服务商。
			const detail = await getCenterOrderDetail(orderId.value).catch(() => null);
			const logistics = await getLogisticsDetails(orderId.value);
			const logisticsData = logistics.data || {};
			const order = logisticsData.order || {};
			const item = order.cartInfo?.[0] || {};
			const productInfo = item.productInfo || {};
			const pageConfig = detail?.data?.page_config || {};
			const sections = Array.isArray(pageConfig.sections) ? pageConfig.sections : [];
			const productSection = sections.find(section => section.key === 'product') || {};
			const product = productSection.props?.product || {};
			const extraInfo = pageConfig.extensions?.extra_info || {};
			const totalNum = product.total_num || item.cart_num || 1;
			const unitPrice = extraInfo.activity_info?.integral_unit_price;
			const numericUnitPrice = unitPrice == null ? null : Number(unitPrice);
			orderInfo.value = {
				...order,
				image: product.image || productInfo.image || '',
				store_name: product.title || productInfo.store_name || '',
				suk: extraInfo.sku_info?.suk || productInfo.suk || '',
				total_num: totalNum,
				total_price: Number.isFinite(numericUnitPrice) ? numericUnitPrice * totalNum : null,
			};
			expressList.value = logisticsData.express?.result?.list || [];
		} catch (error) {
			util.Tips({
				title: error
			});
		}
	}
	/**
	 * 获取我的推荐
	 */
	function get_host_product() {
		getProductHot().then(function(res) {
			hostProduct.value = res.data;
		});
	}
</script>

<style scoped lang="scss">
	.logistics .header {
		padding: 23rpx 30rpx;
		background-color: #fff;
		height: 166rpx;
		box-sizing: border-box;
	}

	.logistics .header .pictrue {
		width: 120rpx;
		height: 120rpx;
	}

	.logistics .header .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.logistics .header .text {
		width: 540rpx;
		font-size: 28rpx;
		color: #999;
		margin-top: 6rpx;
	}

	.logistics .header .text .name {
		width: 365rpx;
		color: #282828;
	}

	.logistics .header .text .money {
		text-align: right;
	}

	.logistics .logisticsCon {
		background-color: #fff;
		margin: 12rpx 0;
	}

	.logistics .logisticsCon .company {
		height: 120rpx;
		margin: 0 0 45rpx 30rpx;
		padding-right: 30rpx;
		border-bottom: 1rpx solid #f5f5f5;
	}

	.logistics .logisticsCon .company .picTxt {
		width: 520rpx;
	}

	.logistics .logisticsCon .company .picTxt .iconfont {
		width: 50rpx;
		height: 50rpx;
		background-color: #666;
		text-align: center;
		line-height: 50rpx;
		color: #fff;
		font-size: 35rpx;
	}

	.logistics .logisticsCon .company .picTxt .text {
		width: 450rpx;
		font-size: 26rpx;
		color: #282828;
	}

	.logistics .logisticsCon .company .picTxt .text .name {
		color: #999;
	}

	.logistics .logisticsCon .company .picTxt .text .express {
		margin-top: 5rpx;
	}

	.logistics .logisticsCon .company .copy {
		font-size: 20rpx;
		width: 106rpx;
		text-align: center;
		border-radius: 3rpx;
		border: 1px solid #999;
		padding: 3rpx 0;
	}

	.logistics .logisticsCon .item {
		padding: 0 40rpx;
		position: relative;
	}

	.logistics .logisticsCon .item .circular {
		width: 20rpx;
		height: 20rpx;
		border-radius: 50%;
		position: absolute;
		top: -1rpx;
		left: 31.5rpx;
		background-color: #ddd;
	}

	.logistics .logisticsCon .item .circular.on {
		background-color: var(--view-theme);
	}

	.logistics .logisticsCon .item .text.on-font {
		color: var(--view-theme);
	}

	.logistics .logisticsCon .item .text .data.on-font {
		color: var(--view-theme);
	}

	.logistics .logisticsCon .item .text {
		font-size: 26rpx;
		color: #666;
		width: 615rpx;
		border-left: 1rpx solid #e6e6e6;
		padding: 0 0 60rpx 38rpx;
	}

	.logistics .logisticsCon .item .text.on {
		border-left-color: var(--view-minorColor);
	}

	.logistics .logisticsCon .item .text .data {
		font-size: 24rpx;
		color: #999;
		margin-top: 10rpx;
	}

	.logistics .logisticsCon .item .text .data .time {
		margin-left: 15rpx;
	}
	.gray-sty {
		width: 100%;
		font-size: 24rpx;
		color: #999999;
	}
</style>
