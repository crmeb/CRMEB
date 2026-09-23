<template>
	<view :style="colorStyle" style="background-color: var(--view-theme); min-height: 100vh; padding-bottom: 1rpx">
		<view class="bargain-list">
			<!-- #ifndef APP-PLUS -->
			<!-- 	<view class='iconfont icon-xiangzuo' @tap='goBack' :style="'top:'+ (navH/2  - 12) +'rpx'" v-if="returnShow">
			</view> -->
			<!-- #endif -->
			<view class="header" :style="'background-image: url(' + hostUrl + picUrl.bgList + ');'"></view>
			<view class="list">
				<block v-for="(item, index) in bargainList" :key="index">
					<view class="item acea-row row-between-wrapper" @tap="openSubscribe(item)">
						<view class="pictrue">
							<image :src="item.image"></image>
						</view>
						<view class="text acea-row row-column-around">
							<view class="name line1">{{ item.title || '' }}</view>
							<view class="num">
								<text class="iconfont icon-pintuan"></text>
								{{ item.people }}{{ $t(`人正在参与`) }}
							</view>
							<view class="money">
								{{ $t(`最低`) }} : {{ $t(`￥`) }}
								<text class="price">{{ item.min_price }}</text>
							</view>
						</view>
						<view class="cutBnt">
							<text class="iconfont icon-kanjia"></text>
							{{ $t(`参与砍价`) }}
						</view>
					</view>
				</block>
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
let app = getApp();
import { ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { getBargainList as getBargainListApi } from '@/api/activity.js';
import { openBargainSubscribe } from '@/utils/subscribeMessage.js';
import { getUserInfo as getUserInfoApi } from '@/api/user.js';
import home from '@/components/home';
import { toLogin } from '@/libs/login.js';
import { colorChange } from '@/api/api.js';
import { useAppStore } from '@/store/modules/app';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
import { useColorTheme } from '@/composables/useColorTheme';
import { HTTP_REQUEST_URL } from '@/config/app.js';
import i18n from '@/utils/lang.js';
import { onLoad, onReachBottom } from '@dcloudio/uni-app';

const { colorStyle, colorStatus } = useColorTheme();

const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const hostUrl = HTTP_REQUEST_URL;
const bargainList = ref([]);
const page = ref(1);
const limit = ref(20);
const loading = ref(false);
const loadend = ref(false);
const userInfo = ref({});
const navH = ref('');
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const returnShow = ref(true);
const picUrl = ref({});
const picList = [
	{
		bgList: '/statics/system_images/bargain_bg_0.jpeg'
	},
	{
		bgList: '/statics/system_images/bargain_bg_1.jpeg'
	},
	{
		bgList: '/statics/system_images/bargain_bg_2.jpeg'
	},
	{
		bgList: '/statics/system_images/bargain_bg_3.jpeg'
	},
	{
		bgList: '/statics/system_images/bargain_bg_4.jpeg'
	}
];

// ---------- watch ----------
watch(isLogin, {
	handler: function (newV, oldV) {
		if (newV) {
			getUserInfo();
			getBargainList();
		}
	},
	deep: true
});
watch(colorStatus, (newValue, oldValue) => {
	if (newValue) {
		colorShow(newValue);
	}
});

// ---------- methods ----------
function colorShow(status) {
	switch (status) {
		case 1:
			picUrl.value = picList[0];
			break;
		case 2:
			picUrl.value = picList[1];
			break;
		case 3:
			picUrl.value = picList[2];
			break;
		case 4:
			picUrl.value = picList[3];
			break;
		case 5:
			picUrl.value = picList[4];
			break;
		default:
			picUrl.value = picList[2];
			break;
	}
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e;
}
/*
 * 获取用户信息
 */
function getUserInfo() {
	getUserInfoApi().then((res) => {
		userInfo.value = res.data;
	});
}
function goBack() {
	uni.navigateBack({
		delta: 1
	});
}
function onLoadFun(e) {
	getBargainList();
	userInfo.value = e;
}
function openSubscribe(item) {
	if (!isLogin.value) {
		toLogin();
	}
	let page = '/pages/activity/goods_bargain_details/index?id=' + item.id + '&bargain=' + userInfo.value.uid;
	// #ifndef MP
	uni.navigateTo({
		url: page
	});
	// #endif
	// #ifdef MP
	uni.showLoading({
		title: i18n.t(`正在加载中`)
	});
	openBargainSubscribe()
		.then((res) => {
			uni.hideLoading();
			uni.navigateTo({
				url: page
			});
		})
		.catch((err) => {
			uni.hideLoading();
		});
	// #endif
}
function getBargainList() {
	if (loadend.value) return;
	if (loading.value) return;
	loading.value = true;
	getBargainListApi({
		page: page.value,
		limit: limit.value
	})
		.then(function (res) {
			bargainList.value = bargainList.value.concat(res.data);
			page.value = page.value + 1;
			loadend.value = limit.value > res.data.length;
			loading.value = false;
		})
		.catch((res) => {
			loading.value = false;
		});
}

// ---------- 生命周期 ----------
onLoad((options) => {
	if (!colorStatus.value) {
		colorChange('color_change').then((res) => {
			colorShow(res.data.status);
		});
	}
	var pages = getCurrentPages();
	returnShow.value = pages.length === 1 ? false : true;
	uni.setNavigationBarTitle({
		title: i18n.t(`砍价列表`)
	});
	// #ifdef MP
	navH.value = app.globalData.navH * 2.5;
	// #endif
	// #ifdef H5
	navH.value = app.globalData.navHeight;
	// #endif
	getBargainList();
	if (isLogin.value) {
		getUserInfo();
	}
});

onReachBottom(() => {
	getBargainList();
});
</script>

<style lang="scss">
// page {
// 	background-color: #e93323 !important;
// }

.bargain-list .icon-xiangzuo {
	font-size: 40rpx;
	color: #fff;
	position: fixed;
	left: 30rpx;
	z-index: 99;
	transform: translateY(-20%);
}

.bargain-list .header {
	background-repeat: no-repeat;
	background-size: 100% 100%;
	width: 750rpx;
	height: 713rpx;
}

.bargain-list .list {
	background-color: #fff;
	border: 6rpx solid #fc8b42;
	border-radius: 30rpx;
	margin: -140rpx 30rpx 66rpx 30rpx;
	padding: 0 24rpx;
}

.bargain-list .list .item {
	border-bottom: 1rpx solid #eee;
	position: relative;
	height: 223rpx;
}

.bargain-list .list .item .pictrue {
	width: 160rpx;
	height: 160rpx;
}

.bargain-list .list .item .pictrue image {
	width: 100%;
	height: 100%;
	border-radius: 6rpx;
}

.bargain-list .list .item .text {
	width: 450rpx;
	font-size: 30rpx;
	color: #282828;
	height: 160rpx;
}

.bargain-list .list .item .text .name {
	width: 100%;
}

.bargain-list .list .item .text .num {
	font-size: 26rpx;
	color: #999;
}

.bargain-list .list .item .text .num .iconfont {
	font-size: 35rpx;
	margin-right: 7rpx;
}

.bargain-list .list .item .text .money {
	font-size: 24rpx;
	font-weight: bold;
	color: var(--view-theme);
}

.bargain-list .list .item .text .money .price {
	font-size: 32rpx;
}

.bargain-list .list .item .cutBnt {
	position: absolute;
	width: 180rpx;
	height: 50rpx;
	border-radius: 50rpx;
	font-size: 24rpx;
	color: #fff;
	text-align: center;
	line-height: 46rpx;
	right: 24rpx;
	bottom: 28rpx;
	box-shadow: 0 7rpx 0 var(--view-minorColor);
	background-color: var(--view-theme);
}

.bargain-list .list .item .cutBnt .iconfont {
	margin-right: 8rpx;
	font-size: 30rpx;
}

.bargain-list .list .load {
	font-size: 24rpx;
	height: 85rpx;
	text-align: center;
	line-height: 85rpx;
}
</style>
