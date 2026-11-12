<template>
	<view :style="assetStyle">
		<view class='coupon-list' v-if="couponsList.length">
			<view class='item acea-row row-center-wrapper' :class="{gray: item.is_fail || item.status === $t(`已使用`)}" v-for='(item,index) in couponsList'
			 :key="index">
				<view class='money' :class='item.is_fail ? "moneyGray" : ""'>
					<view>{{$t(`￥`)}}<text class='num'>{{ money(item.coupon_price) }}</text></view>
					<view class="pic-num" v-if="item.use_min_price > 0">{{$t(`满`)}}{{ money(item.use_min_price) }}{{$t(`元可用`)}}</view>
					<view class="pic-num" v-else>{{$t(`无门槛券`)}}</view>
				</view>
				<view class='text'>
					<view class='condition'>
						<view class="name line2">
							<view class="line-title" :class="item.is_fail? 'bg-color-huic' : 'bg-color-check'" v-if="item.applicable_type === 0">{{$t(`通用劵`)}}</view>
							<view class="line-title" :class="item.is_fail? 'bg-color-huic' : 'bg-color-check'" v-else-if="item.applicable_type === 1">{{$t(`品类券`)}}</view>
							<view class="line-title" :class="item.is_fail? 'bg-color-huic' : 'bg-color-check'" v-else>{{$t(`商品券`)}}</view>
							<text>{{item.coupon_title}}</text>
						</view>
					</view>
					<view class='data acea-row row-between-wrapper'>
						<!-- item.start_time | format -->
						<view>{{item.add_time}}-{{item.end_time}}</view>
						<!-- is_fail:1为失效；0为可用 -->
						<view class='bnt gray' v-if="item.is_fail">{{item.status}}</view>
						<view class='bnt' v-else>{{item.status}}</view>
					</view>
				</view>
			</view>
		</view>
		<view class='noCommodity' v-if="!couponsList.length && loading==true">
			<view class='pictrue'>
				<image :src="imgHost + '/statics/images/noCoupon.png'"></image>
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
	import {
		ref,
		watch
	} from 'vue';
	import {
		onLoad
	} from '@dcloudio/uni-app';
	import {
		memberCouponsList
	} from '@/api/user.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import dayjs from '@/plugin/dayjs/dayjs.min.js';
	import {
		useAppStore
	} from '@/store/modules/app';
	import {
		storeToRefs
	} from 'pinia';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import home from '@/components/home';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';

	const assetStyle = `--ui-vip-coupon-money:url(${HTTP_REQUEST_URL}/statics/images/vip-coupon-money.png);--ui-vip-coupon-money-gray:url(${HTTP_REQUEST_URL}/statics/images/vip-coupon-money-gray.png);`;

	const appStore = useAppStore();
	const {
		isLogin
	} = storeToRefs(appStore);

	// ---------- data ----------
	const imgHost = HTTP_REQUEST_URL;
	const couponsList = ref([]);
	const loading = ref(false);
	const isAuto = ref(false); //没有授权的不会自动授权
	const isShowAuth = ref(false); //是否隐藏授权

	// ---------- watch ----------
	watch(isLogin, (newV, oldV) => {
		if (newV) {
			getUseCoupons();
		}
	}, {
		deep: true
	});

	onLoad(() => {
		if (isLogin.value) {
			getUseCoupons();
		} else {
			toLogin();
		}
	});

	// ---------- methods ----------
	function format(value) {
		if (!value) return ''
		return dayjs(value * 1000).format('YYYY-MM-DD');
	}

	function money(value) {
		if (!value) return '0'
		return parseFloat(value);
	}
	/**
	 * 授权回调
	 */
	function onLoadFun() {
		getUseCoupons();
	}
	// 授权关闭
	function authColse(e) {
		isShowAuth.value = e
	}
	/**
	 * 获取领取优惠券列表
	 */
	function getUseCoupons() {
		memberCouponsList().then(res => {
			loading.value = true;
			couponsList.value = res.data;
		})
	}
</script>

<style scoped>
	.coupon-list .moneyGray.item .text .data .bnt {
		background: #B5B5B5;
	}

	.coupon-list .moneyGray.item .money {
		color: #7D7D7D;
	}

	.coupon-list .moneyGray.item .text {
		background: linear-gradient(-90deg, #DADADA 0%, #E9E9E9 100%);
	}

	.coupon-list .moneyGray.item .text .condition {
		border-color: #F0F0F0;
	}

	.coupon-list .moneyGray.item .text .condition {
		color: #7D7D7D;
	}

	.coupon-list .moneyGray.item .text .data {
		color: #999999;
	}

	.moneyGray .condition .line-title {
		border-color: #7D7D7D;
		background: #EFEFEF;
		color: #7D7D7D;
	}

	.coupon-list .item .money {
		background-image: var(--ui-vip-coupon-money);
		background-position: left top;
		background-size: 100% 100%;
		background-repeat: no-repeat;
	}

	.money {
		display: flex;
		flex-direction: column;
		justify-content: center;
	}

	.pic-num {
		font-size: 24rpx;
	}
	
	.coupon-list .item .text {
		padding: 14rpx 24rpx;
	}

	.coupon-list .item .text .condition {
		display: flex;
		align-items: center;
	}
	
	.coupon-list .item .text .condition .name {
		font-size: 26rpx;
		font-weight: 500;
	}
	
	.coupon-list .item .text .condition .pic {
		width: 30rpx;
		height: 30rpx;
		display: block;
		margin-right: 10rpx;
		display: inline-block;
		vertical-align: middle;
	}
	
	.condition .line-title {
		width: 70rpx;
		height: 32rpx !important;
		line-height: 30rpx;
		text-align: center;
		box-sizing: border-box;
		background: #FEF7EC;
		border: 1px solid #EEC181;
		opacity: 1;
		border-radius: 20rpx;
		font-size: 18rpx !important;
		color: #EEC181;
		margin-right: 12rpx;
		text-align: center;
		display: inline-block;
		vertical-align: middle;
	}
	
	.condition .line-title.bg-color-huic {
		border-color: #BBB;
		color: #bbb;
		background-color: #F5F5F5;
	}

	.coupon-list .item .text .data .bnt {
		background: linear-gradient(90deg, #EDC98A 0%, #EDBB75 100%);
	}

	.coupon-list .item.gray .line-title {
		border: 1px solid #CCCCCC;
		background: #F7F7F7;
		color: #CCCCCC;
	}

	.coupon-list .item.gray .text .condition {
		color: #CCCCCC;
	}

	.coupon-list .item.gray .text .data {
		color: #CCCCCC;
	}

	.coupon-list .item.gray .text .data .bnt {
		background: #CCCCCC;
	}

	.coupon-list .item.gray .money {
		background-image: var(--ui-vip-coupon-money-gray);
	}
</style>
