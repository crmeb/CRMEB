<template>
	<view class='productSort copy-data' :style="{height:pageHeight}">
		<view class='header acea-row row-center-wrapper'>
			<view class='acea-row row-between-wrapper input'>
				<text class='iconfont icon-sousuo'></text>
				<input type='text' :placeholder="$t('搜索商品名称')" @confirm="searchSubmitValue" confirm-type='search'
					name="search" placeholder-class='placeholder'></input>
			</view>
		</view>
		<view class="scroll-box">
			<view class='aside'>
				<scroll-view scroll-y="true" scroll-with-animation='true' style="height: calc(100% - 100rpx)">
					<view class='item acea-row row-center-wrapper' :class='index==navActive?"on":""'
						v-for="(item,index) in productList" :key="index" @click='tap(index,"b"+index)'>
						<text>{{$t(item.cate_name)}}</text>
					</view>
					<!-- #ifdef APP-PLUS -->
					<view class="item" v-if="newData.status && newData.status.status"></view>
					<!-- #endif -->
				</scroll-view>
			</view>


			<view class='conter'>
				<scroll-view scroll-y="true" :scroll-into-view="toView" @scroll="scroll" scroll-with-animation='true'
					style="height: 100%;" class="conterScroll">
					<block v-for="(item,index) in productList" :key="index">
						<view class='listw' :id="'b'+index">
							<view class='title acea-row row-center-wrapper'>
								<view class='line'></view>
								<view class='name'>{{$t(item.cate_name)}}</view>
								<view class='line'></view>
							</view>
							<view class='list acea-row'>
								<navigator :render-link="false" hover-class='none'
									:url='"/pages/goods/goods_list/index?cid="+item.id+"&title="+item.cate_name'
									class='item acea-row row-column row-middle'>
									<view class='picture'>
										<image :src="item.pic || defimg" mode="aspectFill" lazy-load></image>
									</view>
									<view class='name line1'>{{$t(`全部商品`)}}</view>
								</navigator>
								<block v-for="(itemn,indexn) in item.children" :key="indexn">
									<navigator :render-link="false" hover-class='none'
										:url='"/pages/goods/goods_list/index?sid="+itemn.id+"&title="+itemn.cate_name'
										class='item acea-row row-column row-middle'>
										<view class='picture'>
											<image :src="itemn.pic" mode="aspectFill" lazy-load></image>
										</view>
										<view class='name line1'>{{$t(itemn.cate_name)}}</view>
									</navigator>
								</block>
							</view>
						</view>
					</block>
					<view :style='"height:"+(height-300)+"rpx;"' v-if="number<15"></view>
				</scroll-view>
			</view>
		</view>
	</view>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick, getCurrentInstance } from 'vue';
import { storeToRefs } from 'pinia';
let sysHeight = uni.getWindowInfo().statusBarHeight + 'px';
import defimg from '@/static/images/all_cat.png';
import { getCategoryList } from '@/api/store.js';
import { getNavigation } from '@/api/public.js';
import { useIndexDataStore } from '@/store/modules/indexData';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
const app = getApp();
const instance = getCurrentInstance();

const indexDataStore = useIndexDataStore();
const { cartNum } = storeToRefs(indexDataStore);

// ---------- data ----------
const navlist = ref([]);
const productList = ref([]);
const navActive = ref(0);
const number = ref('');
const is_diy = ref(uni.getStorageSync('is_diy'));
const height = ref(0);
const hightArr = ref([]);
const toView = ref('');
const tabbarH = ref(0);
const footH = ref(0);
const windowHeight = ref(0);
const newData = ref({});
const activeRouter = ref('');
// 原 data 中 pageHeight 双声明（APP-PLUS 取 globalData，其余 '100%'）
// #ifdef APP-PLUS
const pageHeight = ref(app.globalData.windowHeight);
// #endif
// #ifndef APP-PLUS
const pageHeight = ref('100%');
// #endif
const lock = ref(false);

// ---------- mounted ----------
function onUploadCatData() {
	getAllCategory(1);
}
onMounted(() => {
	// #ifdef H5
	uni.getSystemInfo({
		success: function (res) {
			pageHeight.value = res.windowHeight + 'px';
		}
	});
	// #endif
	let routes = getCurrentPages();
	let curRoute = routes[routes.length - 1].route;
	activeRouter.value = '/' + curRoute;
	!productList.value.length && getAllCategory(1);
	uni.$on('uploadCatData', onUploadCatData);
});
onBeforeUnmount(() => {
	uni.$off('uploadCatData', onUploadCatData);
});

// ---------- methods ----------
function getNav() {
	getNavigation().then((res) => {
		newData.value = res.data;
	});
}
function goRouter(item) {
	var pages = getCurrentPages();
	var page = pages[pages.length - 1].$page.fullPath;
	if (item.link == page) return;
	uni.switchTab({
		url: item.link,
		fail(err) {
			uni.redirectTo({
				url: item.link
			});
		}
	});
}
function footHeight(data) {
	footH.value = data;
}
function infoScroll() {
	let len = productList.value.length;
	number.value = productList.value[len - 1].children.length;
	//设置商品列表高度
	uni.getSystemInfo({
		success: function (res) {
			height.value = res.windowHeight * (750 / res.windowWidth) - 98;
		},
	});
	let heightLocal = 0;
	let hightArrLocal = [];
	for (let i = 0; i < len; i++) {
		//获取元素所在位置
		let query = uni.createSelectorQuery().in(instance.proxy);
		let idView = '#b' + i;
		query.select(idView).boundingClientRect();
		query.exec(function (res) {
			let top = res[0].top;
			hightArrLocal.push(top);
			hightArr.value = hightArrLocal;
		});
	};
}
function tap(index, id) {
	toView.value = id;
	navActive.value = index;
	lock.value = true;
	uni.$emit('scroll');
}
function getAllCategory(type) {
	if (type || !uni.getStorageSync('CAT1_DATA')) {
		getCategoryList().then((res) => {
			uni.setStorageSync('CAT1_DATA', res.data);
			productList.value = res.data;
			nextTick((res) => {
				infoScroll();
			});
		});
	} else {
		productList.value = uni.getStorageSync('CAT1_DATA');
		nextTick((res) => {
			infoScroll();
		});
	}
}
function scroll(e) {
	console.log(e);
	let scrollTop = e.detail.scrollTop;
	let scrollArr = hightArr.value;
	uni.$emit('scroll');
	if (lock.value) {
		lock.value = false;
		return;
	}
	for (let i = 0; i < scrollArr.length; i++) {
		if (scrollTop >= 0 && scrollTop < scrollArr[1] - scrollArr[0]) {
			navActive.value = 0;
		} else if (scrollTop >= scrollArr[i] - scrollArr[0] && scrollTop < scrollArr[i + 1] - scrollArr[0]) {
			navActive.value = i;
		} else if (scrollTop >= scrollArr[scrollArr.length - 1] - scrollArr[0]) {
			navActive.value = scrollArr.length - 1;
		}
	}
}
function searchSubmitValue(e) {
	if (util.trim(e.detail.value).length > 0)
		uni.navigateTo({
			url: '/pages/goods/goods_list/index?searchValue=' + e.detail.value
		});
	else
		return util.Tips({
			title: i18n.t(`搜索商品名称`)
		});
}

// 暴露给父组件 goods_cate.vue（classOneRef.is_diy / classOneRef.getNav()）
defineExpose({
	is_diy,
	getNav
});
</script>
<style scoped lang="scss">
:deep(uni-scroll-view){
	padding-bottom: 0 !important;
}

	.sys-title {
		z-index: 10;
		position: relative;
		height: 40px;
		line-height: 40px;
		font-size: 30rpx;
		color: #333;
		background-color: #fff;
		// #ifdef APP-PLUS
		text-align: center;
		// #endif
		// #ifdef MP
		text-align: left;
		padding-left: 30rpx;
		// #endif
	}

	.sys-head {
		background-color: #fff;
	}

	.productSort {
		display: flex;
		flex-direction: column;
		//#ifdef MP
		height: calc(100vh - var(--window-top)) !important;
		//#endif
		//#ifndef MP
		height: 100vh //#endif
	}

	.productSort .header {
		width: 100%;
		height: 96rpx;
		background-color: #fff;
		border-bottom: 1rpx solid #f5f5f5;
	}

	.productSort .header .input {
		width: 700rpx;
		height: 60rpx;
		background-color: #f5f5f5;
		border-radius: 50rpx;
		box-sizing: border-box;
		padding: 0 25rpx;
	}

	.productSort .header .input .iconfont {
		font-size: 35rpx;
		color: #555;
	}

	.productSort .header .input .placeholder {
		color: #999;
	}

	.productSort .header .input input {
		font-size: 26rpx;
		height: 100%;
		width: 597rpx;
	}

	.productSort .scroll-box {
		flex: 1;
		overflow: hidden;
		display: flex;
	}

	// #ifndef MP
	uni-scroll-view {
		padding-bottom: 100rpx;
	}

	// #endif

	.productSort .aside {
		width: 180rpx;
		height: 100%;
		overflow: hidden;
		background-color: #f7f7f7;
	}

	.productSort .aside .item {
		height: 100rpx;
		width: 100%;
		font-size: 26rpx;
		color: #424242;
		text-align: center;
	}

	.productSort .aside .item.on {
		background-color: #fff;
		border-left: 4rpx solid var(--view-theme);
		width: 100%;
		color: var(--view-theme);
		font-weight: bold;
	}

	.productSort .conter {
		flex: 1;
		height: 100%;
		overflow: hidden;
		padding: 0 14rpx;
		background-color: #fff;
		position: relative;
		padding-bottom: 100rpx;
	}

	.productSort .conter .listw {
		padding-top: 20rpx;
	}

	.productSort .conter .listw .title {
		height: 90rpx;
	}

	.productSort .conter .listw .title .line {
		width: 100rpx;
		height: 2rpx;
		background-color: #f0f0f0;
	}

	.productSort .conter .listw .title .name {
		font-size: 28rpx;
		color: #333;
		margin: 0 30rpx;
		font-weight: bold;
	}

	.productSort .conter .list {
		flex-wrap: wrap;
	}

	.productSort .conter .list .item {
		width: 177rpx;
		margin-top: 26rpx;
	}

	.productSort .conter .list .item .picture {
		width: 120rpx;
		height: 120rpx;
		border-radius: 50%;
	}

	.productSort .conter .list .item .picture image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
	}

	.productSort .conter .list .item .picture {

/* #ifndef MP */
/* WXSS/ACSS 不支持 * 选择符，小程序端由下方显式元素选择器覆盖 */
:deep(*),
/* #endif */
:deep(image),
:deep(.easy-loadimage),
:deep(uni-image){

			width: 120rpx;
			height: 120rpx;
			border-radius: 50%;
		}
	}

	.productSort .conter .list .item .name {
		font-size: 24rpx;
		color: #333;
		height: 56rpx;
		line-height: 56rpx;
		width: 120rpx;
		text-align: center;
	}
</style>
