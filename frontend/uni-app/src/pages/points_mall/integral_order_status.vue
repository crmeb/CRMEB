<template>
	<view :style="colorStyle">
		<view class='payment-status'>
			<!--失败时： 用icon-iconfontguanbi fail替换icon-duihao2 bg-color-->
			<view class='iconfont icons icon-duihao2 bg-color'></view>
			<!-- 失败时：商品兑换失败 -->
			<view class='status' v-if="order_pay_info.pay_type != 'offline'">{{$t(`商品兑换成功`)}}
			</view>
			<view class='status' v-else>{{$t(`订单创建成功`)}}</view>
			<view class='wrapper'>
				<view class='item acea-row row-between-wrapper'>
					<view>{{$t(`订单编号`)}}</view>
					<view class='itemCom'>{{orderId}}</view>
				</view>
				<view class='item acea-row row-between-wrapper'>
					<view>{{$t(`兑换时间`)}}</view>
					<view class='itemCom'>{{order_pay_info.add_time}}</view>
				</view>
				<view class='item acea-row row-between-wrapper'>
					<view>{{$t(`兑换方式`)}}</view>
					<view class='itemCom'>{{$t(`积分兑换`)}}</view>
				</view>
				<view class='item acea-row row-between-wrapper'>
					<view>{{$t(`支付积分`)}}</view>
					<view class='itemCom'>{{order_pay_info.total_price}}</view>
				</view>
				<!--失败时加上这个  -->
				<view class='item acea-row row-between-wrapper'
					v-if="order_pay_info.pay_status !== 1 && order_pay_info.pay_type != 'offline'">
					<view>{{$t(`失败原因`)}}</view>
					<view class='itemCom'>{{status==2 ? $t(`取消兑换`):msg}}</view>
				</view>
			</view>
			<!--失败时： 重新购买 -->
			<view @tap="goOrderDetails">
				<button formType="submit" class='returnBnt bg-color' hover-class='none'>{{$t(`查看详情`)}}</button>
			</view>
			<button @click="goIndex" class='returnBnt cart-color' formType="submit" hover-class='none'>{{$t(`返回首页`)}}</button>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
	</view>
</template>

<script setup>
	import { ref, watch } from 'vue';
	import { onLoad } from '@dcloudio/uni-app';
	import {
		getCenterOrderDetail,
	} from '@/api/order-center.js';
	import {
		openOrderSubscribe
	} from '@/utils/subscribeMessage.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		useAppStore
	} from '@/store/modules/app';
	import {
		storeToRefs
	} from 'pinia';
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
	const order_pay_info = ref({
		pay_status: 1,
		_status: {}
	});
	const isAuto = ref(false); //没有授权的不会自动授权
	const isShowAuth = ref(false); //是否隐藏授权
	const status = ref(0);
	const msg = ref('');
	const couponsHidden = ref(true);
	const couponList = ref([]);

	// ---------- watch ----------
	watch(isLogin, (newV, oldV) => {
		if (newV) {
			getOrderPayInfo();
		}
	}, { deep: true });

	onLoad((options) => {
		if (!options.order_id) return util.Tips({
			title: i18n.t(`缺少参数无法查看订单兑换状态`)
		}, {
			tab: 3,
			url: 1
		});
		orderId.value = options.order_id;
		status.value = options.status || 0;
		msg.value = options.msg || '';
		if (isLogin.value) {
			getOrderPayInfo();
		} else {
			toLogin();
		}
		// #ifdef H5
		document.addEventListener('visibilitychange', (e) => {
			let state = document.visibilityState
			if (state == 'hidden') {
				console.log(i18n.t(`用户离开了`));
			}
			if (state == 'visible') {
				getOrderPayInfo();
			}
		});
		// #endif
	});

	// ---------- methods ----------
	function openTap() {
		couponsHidden.value = !couponsHidden.value;
	}
	function onLoadFun() {
		getOrderPayInfo();
	}
	/**
	 *
	 * 兑换完成查询兑换状态
	 * 从统一订单中心查询
	 */
	function getOrderPayInfo() {
		uni.showLoading({
			title: i18n.t(`正在加载中`)
		});
		getCenterOrderDetail(orderId.value).then(res => {
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
			const unitPrice = activityInfo.integral_unit_price || 0;
			const productProps = propsMap['product'] || {};
			const product = productProps.product || {};
			const totalNum = product.total_num || 1;
			const orderInfoFields = (propsMap['order_info'] && propsMap['order_info'].fields) || {};
			const statusProps = propsMap['status'] || {};
			order_pay_info.value = {
				order_id: orderInfoFields.order_no,
				order_no: orderInfoFields.order_no,
				add_time: orderInfoFields.add_time ? util.formatDate(orderInfoFields.add_time, 'yyyy-MM-dd hh:mm') : '',
				pay_type: orderInfoFields.pay_type || '',
				pay_status: statusProps.code === 'pending_payment' ? 0 : 1,
				total_price: unitPrice * totalNum,
				product_image: product.image,
				title: product.title,
				suk: skuInfo.suk || '',
			};
			uni.setNavigationBarTitle({
				title: i18n.t(`兑换成功`)
			});
			getOrderCoupon();
		}).catch(err => {
			uni.hideLoading();
		});
	}
	function getOrderCoupon() {
		orderCoupon(orderId.value).then(res => {
			couponList.value = res.data;
		})
	}
	/**
	 * 去首页关闭当前所有页面
	 */
	function goIndex(e) {
		uni.switchTab({
			url: '/pages/index/index'
		})
	}
	/**
	 *
	 * 去订单详情页面
	 */
	function goOrderDetails(e) {
		const url = '/pages/points_mall/integral_order_details?order_id=' + orderId.value;
		// #ifdef MP
		uni.showLoading({
			title: i18n.t(`正在加载中`)
		});
		openOrderSubscribe().then(() => {
			uni.hideLoading();
			uni.navigateTo({ url });
		}).catch(() => {
			uni.hideLoading();
		});
		// #endif
		// #ifndef MP
		uni.navigateTo({ url });
		// #endif
	}
</script>

<style lang="scss">
	.coupons {
		.title {
			margin: 30rpx 0 25rpx 0;

			.line {
				width: 70rpx;
				height: 1px;
				background: #DCDCDC;
			}

			.name {
				font-size: 24rpx;
				color: #999;
				margin: 0 10rpx;
			}
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
		text-shadow: 0px 4px 0px rgba(255,255,255,0.5);
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

	.payment-status .wrapper .item~.item {
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
</style>
