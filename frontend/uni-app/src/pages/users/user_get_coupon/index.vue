<template>
	<view :style="colorStyle">
		<view v-if="count > 1" class="acea-row row-around nav">
			<template v-for="item in navList" :key="item.type">
				<view v-if="item.count" :class="['acea-row', 'row-middle', type === item.type ? 'on' : '']" @click="setType(item.type)">
					{{ item.name }}
				</view>
			</template>
		</view>
		<view v-if="count > 1" style="height: 106rpx"></view>
		<view class="coupon-list" v-if="couponsList.length">
			<view class="item acea-row row-center-wrapper" v-for="(item, index) in couponsList" :key="index" :class="{ svip: item.receive_type === 4 }">
				<view class="moneyCon acea-row row-center-wrapper">
					<view class="money" :class="item.is_use >= item.receive_limit ? 'moneyGray' : ''">
						<view>
							{{ $t(`￥`) }}
							<text class="num">{{ item.coupon_price }}</text>
						</view>
						<view class="pic-num" v-if="item.use_min_price > 0">
							{{ $t(`满`) }} {{ item.use_min_price }}
							{{ $t(`元可用`) }}
						</view>
						<view class="pic-num" v-else>{{ $t(`无门槛券`) }}</view>
					</view>
				</view>
				<view class="text">
					<view class="condition">
						<view class="name line2">
							<view class="line-title" :class="item.is_use >= item.receive_limit ? 'bg-color-huic' : ''" v-if="item.type === 0">{{ $t(`通用劵`) }}</view>
							<view class="line-title" :class="item.is_use >= item.receive_limit ? 'bg-color-huic' : ''" v-else-if="item.type === 1">{{ $t(`品类券`) }}</view>
							<view class="line-title" :class="item.is_use >= item.receive_limit ? 'bg-color-huic' : ''" v-else>{{ $t(`商品券`) }}</view>
							<image v-if="item.receive_type === 4" class="pic" src="/static/images/fvip.png"></image>
							<text class="title">{{ $t(item.title) }}</text>
						</view>
					</view>
					<view class="data acea-row row-between-wrapper">
						<view v-if="item.coupon_time">{{ $t(`领取后`) }} {{ item.coupon_time }} {{ $t(`天内可用`) }}</view>
						<view v-else-if="item.start_use_time || item.end_use_time">{{ item.start_use_time ? item.start_use_time + '-' : '' }}{{ item.end_use_time }}</view>
						<view v-else></view>
						<view class="bnt gray" v-if="item.is_use >= item.receive_limit">{{ $t(`已领取`) }}</view>
						<view class="bnt gray" v-else-if="item.is_permanent == 0 && item.remain_count == 0">
							{{ $t(`已领完`) }}
						</view>
						<view class="bnt bg-color" v-else @click="getCoupon(item.id, item)">{{ $t(`立即领取`) }}</view>
					</view>
				</view>
			</view>
		</view>
		<view class="loadingicon acea-row row-center-wrapper" v-if="couponsList.length">
			<text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
			{{ loadTitle }}
		</view>
		<view class="noCommodity" v-else-if="!couponsList.length && page === 2">
			<view class="pictrue">
				<image :src="imgHost + '/statics/images/noCoupon.png'"></image>
			</view>
		</view>
		<!-- #ifdef MP -->
		<authorize @onLoadFun="onLoadFun" @authColse="authColse"></authorize>
		<!-- #endif -->
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref, watch } from 'vue';
import { onLoad, onReachBottom } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import { getCoupons, setCouponReceive } from '@/api/api.js';
import { toLogin } from '@/libs/login.js';
import home from '@/components/home';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
import { HTTP_REQUEST_URL } from '@/config/app';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const imgHost = HTTP_REQUEST_URL;
const couponsList = ref([]);
const loading = ref(false);
const loadend = ref(false);
const loadTitle = ref(i18n.t(`加载更多`)); //提示语
const page = ref(1);
const limit = ref(20);
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const type = ref(0);
const navList = ref([
	{
		type: 0,
		name: i18n.t(`通用券`),
		count: 0
	},
	{
		type: 1,
		name: i18n.t(`品类券`),
		count: 0
	},
	{
		type: 2,
		name: i18n.t(`商品券`),
		count: 0
	}
]);
const count = ref(0);
const receiveLoading = ref(false);

// ---------- watch ----------
watch(isLogin, (newV, oldV) => {
	if (newV) {
		getUseCoupons();
	}
}, { deep: true });

// ---------- 生命周期 ----------
onLoad(() => {
	if (isLogin.value) {
		// #ifdef H5 || APP-PLUS
		getUseCoupons();
		// #endif
	} else {
		toLogin();
	}
});
/**
 * 页面上拉触底事件的处理函数
 */
onReachBottom(() => {
	getUseCoupons();
});

// ---------- methods ----------
function onLoadFun() {
	getUseCoupons();
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e;
}
function getCoupon(id, item) {
	if (receiveLoading.value) return;
	receiveLoading.value = true;
	//领取优惠券
	setCouponReceive(id)
		.then((res) => {
			item.is_use += 1;
			util.Tips({
				title: i18n.t(`领取成功`)
			});
			setTimeout((e) => {
				receiveLoading.value = false;
			}, 500);
		})
		.catch((error) => {
			receiveLoading.value = false;
			return util.Tips({
				title: error
			});
		});
}
/**
 * 获取领取优惠券列表
 */
function getUseCoupons() {
	if (loadend.value) return false;
	if (loading.value) return false;
	loading.value = true;
	loadTitle.value = i18n.t(`加载更多`);
	getCoupons({
		type: type.value,
		page: page.value,
		limit: limit.value
	})
		.then((res) => {
			let list = res.data.list,
				loadendNew = list.length < limit.value;
			let couponsListNew = util.SplitArray(list, couponsList.value);
			res.data.count.forEach((value, index) => {
				navList.value[index].count = value;
				if (value) {
					count.value++;
				}
			});
			couponsList.value = couponsListNew;
			loadend.value = loadendNew;
			loading.value = false;
			loadTitle.value = loadendNew ? i18n.t(`我也是有底线的`) : i18n.t(`加载更多`);
			page.value = page.value + 1;
		})
		.catch((err) => {
			loading.value = false;
			loadTitle.value = i18n.t(`加载更多`);
		});
}
function setType(typeNum) {
	if (type.value !== typeNum) {
		type.value = typeNum;
		couponsList.value = [];
		page.value = 1;
		loadend.value = false;
		getUseCoupons();
	}
}
</script>

<style scoped lang="scss">
.nav {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 106rpx;
	background-color: #ffffff;
	font-size: 30rpx;
	color: #999999;
	z-index: 9;
}

.coupon-list .item .money .num {
	font-size: 48rpx;
}

.coupon-list .item .text .data {
	margin-top: 10rpx;
}

.coupon-list .item .text {
	padding-bottom: 12rpx;
}

.nav .acea-row {
	border-top: 5rpx solid transparent;
	border-bottom: 5rpx solid transparent;
}

.nav .acea-row.on {
	border-bottom-color: var(--view-theme);
	color: #282828;
}

.coupon-list .pic-num {
	color: #ffffff;
	font-size: 24rpx;
}

.coupon-list .item .text .condition {
	display: flex;
	align-items: center;
	.title {
		font-size: 26rpx;
		font-weight: 500;
		line-height: 30rpx;
	}
}

.coupon-list .item .text .condition .name {
	font-size: 28rpx;
	font-weight: 500;
	/* 
	display: flex;
	align-items: center; */
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
	width: 90rpx;
	height: 40rpx !important;
	line-height: 40rpx;
	text-align: center;
	box-sizing: border-box;
	background: rgba(255, 247, 247, 1);
	border: 1rpx solid var(--view-theme);
	opacity: 1;
	border-radius: 20rpx;
	font-size: 18rpx !important;
	color: var(--view-theme);
	margin-right: 12rpx;
	text-align: center;
	display: inline-block;
	vertical-align: middle;
}

.condition .line-title.bg-color-huic {
	border-color: #bbb !important;
	color: #bbb !important;
	background-color: #f5f5f5 !important;
}
</style>
