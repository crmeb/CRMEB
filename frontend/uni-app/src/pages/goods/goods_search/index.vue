<template>
	<view class="wrapper" :style="colorStyle">
		<scroll-view :scroll-top="scrollTop" scroll-y="true" class="scroll-Y" @scroll="scroll" @scrolltolower="scrollLower">
			<view class='searchGood'>
				<view class='search acea-row row-between-wrapper'>
					<view class='input acea-row row-between-wrapper'>
						<text class='iconfont icon-sousuo'></text>
						<input type='text' v-model='searchValue' @confirm="inputConfirm" focus
							:placeholder='$t(`搜索商品名称`)' placeholder-class='placeholder' @input="setValue"></input>
					</view>
					<view class='bnt' @tap='searchBut'>{{$t(`搜索`)}}</view>
				</view>
				<template v-if="history.length">
					<view class='title acea-row row-between-wrapper'>
						<view>{{$t(`搜索历史`)}}</view>
						<view class="iconfont icon-shanchu" @click="clear"></view>
					</view>
					<view class='list acea-row'>
						<block v-for="(item,index) in history" :key="index">
							<view class='item history-item line1' @tap='setHotSearchValue(item.keyword)'
								v-if="item.keyword">{{item.keyword}}</view>
						</block>
					</view>
				</template>
				<view class='title'>{{$t(`热门搜索`)}}</view>
				<view class='list acea-row'>
					<block v-for="(item,index) in hotSearchList" :key="index">
						<view class='item line1' @tap='setHotSearchValue(item.val)' v-if="item.val">{{item.val}}</view>
					</block>
				</view>
				<view class='line' v-if='bastList.length'></view>
				<goodList :bastList="bastList" v-if="bastList.length > 0"></goodList>
				<view class='loadingicon acea-row row-center-wrapper' v-if="bastList.length > 0">
					<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
				</view>
			</view>
			<view class='noCommodity'>
				<view class='pictrue' v-if="bastList.length == 0">
					<image :src="imgHost + '/statics/images/noSearch.png'"></image>
				</view>
				<recommend :hostProduct='hostProduct' v-if="bastList.length == 0 && page > 1"></recommend>
			</view>
		</scroll-view>
		<home></home>
		<view v-if="scrollTopShow" class="back-top" @click="goTop">
			<text class="iconfont icon-xiangshang"></text>
		</view>
	</view>
</template>

<script setup>
	import { ref, nextTick } from 'vue';
	import { onShow, onPageScroll } from '@dcloudio/uni-app';
	import {
		getSearchKeyword,
		getProductslist,
		getProductHot
	} from '@/api/store.js';
	import {
		searchList as searchListApi,
		clearSearch
	} from '@/api/api.js';
	import goodList from '@/components/goodList';
	import recommend from '@/components/recommend';
	import home from '@/components/home';
	import { useColorTheme } from '@/composables/useColorTheme';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';

	const { colorStyle } = useColorTheme();

	const imgHost = HTTP_REQUEST_URL;
	const hostProduct = ref([]);
	const searchValue = ref('');
	const focus = ref(true);
	const bastList = ref([]);
	const hotSearchList = ref([]);
	const first = ref(0);
	const limit = ref(8);
	const page = ref(1);
	const loading = ref(false);
	const loadend = ref(false);
	const loadTitle = ref(i18n.t(`加载更多`));
	const hotPage = ref(1);
	const isScroll = ref(true);
	const history = ref([]);
	const scrollTop = ref(0);
	const old = ref({
		scrollTop: 0
	});
	const scrollTopShow = ref(false);

	onShow(() => {
		// getRoutineHotSearch();
		getHostProduct();
		searchList();
		try {
			hotSearchList.value = uni.getStorageSync('hotList');
		} catch (err) {}
	});

	// 滚动监听
	onPageScroll((e) => {
		// 传入scrollTop值并触发所有easy-loadimage组件下的滚动监听事件
		uni.$emit('scroll');
	});

	function scrollLower(){
		if (bastList.value.length > 0) {
			getProductList();
		} else {
			getHostProduct();
		}
	}
	function scroll(e) {
		scrollTopShow.value = e.detail.scrollTop > 150
		old.value.scrollTop = e.detail.scrollTop
	}
	function goTop(e) {
		// 解决view层不同步的问题
		scrollTop.value = old.value.scrollTop
		nextTick(() => {
			scrollTop.value = 0
		});
	}
	function searchList() {
		searchListApi({
			page: 1,
			limit: 10
		}).then(res => {
			history.value = res.data;
		});
	}
	function clear() {
		clearSearch().then(res => {
			uni.showToast({
				title: res.msg,
				success() {
					history.value = [];
				}
			});
		});
	}
	function inputConfirm(event) {
		if (event.detail.value) {
			uni.hideKeyboard();
			setHotSearchValue(event.detail.value);
		}
	}
	function getRoutineHotSearch() {
		getSearchKeyword().then(res => {
			hotSearchList.value = res.data;
		});
	}
	function getProductList() {
		if (loadend.value) return;
		if (loading.value) return;
		loading.value = true;
		loadTitle.value = '';
		getProductslist({
			keyword: searchValue.value.trim(),
			page: page.value,
			limit: limit.value
		}).then(res => {
			let list = res.data,
				loadendVal = list.length < limit.value;
			bastList.value = util.SplitArray(list, bastList.value);
			bastList.value = bastList.value;
			loading.value = false;
			loadend.value = loadendVal;
			loadTitle.value = loadendVal ? i18n.t(`没有更多内容啦~`) : i18n.t(`加载更多`);
			page.value = page.value + 1;
		}).catch(err => {
			loading.value = false,
				loadTitle.value = i18n.t(`加载更多`)
		});
	}
	function getHostProduct() {
		if (!isScroll.value) return
		getProductHot(hotPage.value, limit.value).then(res => {
			isScroll.value = res.data.length >= limit.value
			hostProduct.value = hostProduct.value.concat(res.data)
			hotPage.value += 1;
		});
	}
	function setHotSearchValue(event) {
		searchValue.value = event;
		page.value = 1;
		loadend.value = false;
		bastList.value = [];
		getProductList();
	}
	function setValue(event) {
		searchValue.value = event.detail.value;
	}
	function searchBut() {
		if (!searchValue.value.trim()) return util.Tips({
			title: i18n.t(`请输入要搜索的商品`)
		});
		focus.value = false;
		// if (that.searchValue.length > 0) {
		page.value = 1;
		loadend.value = false;
		bastList.value = [];
		uni.showLoading({
			title: i18n.t(`正在搜索中`)
		});
		getProductList();
		uni.hideLoading();
		// } else {
		// 	return this.$util.Tips({
		// 		title: '请输入要搜索的商品',
		// 		icon: 'none',
		// 		duration: 1000,
		// 		mask: true,
		// 	});
		// }
	}
</script>

<style lang="scss">
	page {
		background-color: #fff !important;
	}

	.scroll-Y {
		height: 100vh;
	}

	.wrapper {
		position: relative;
		max-height: 100vh;
		overflow: hidden;

		.back-top {
			position: absolute;
			right: 40rpx;
			bottom: 60rpx;
			width: 60rpx;
			height: 60rpx;
			border-radius: 50%;
			display: flex;
			justify-content: center;
			align-items: center;
			border: 1rpx solid #ccc;
			background-color: #fff;

			.icon-xiangshang {
				color: #ccc;
				font-weight: bold;
			}
		}
	}

	.noCommodity {
		border-top-width: 0;
	}

	.searchGood .search {
		padding-left: 30rpx;
	}

	.searchGood .search {
		padding-top: 20rpx;
	}

	.searchGood .search .input {
		width: 598rpx;
		background-color: #f7f7f7;
		border-radius: 33rpx;
		padding: 0 35rpx;
		box-sizing: border-box;
		height: 66rpx;
	}

	.searchGood .search .input input {
		width: 472rpx;
		font-size: 28rpx;
	}

	.searchGood .search .input .placeholder {
		color: #999;
	}

	.searchGood .search .input .iconfont {
		color: #555;
		font-size: 35rpx;
	}

	.searchGood .search .bnt {
		width: 120rpx;
		text-align: center;
		height: 66rpx;
		line-height: 66rpx;
		font-size: 30rpx;
		color: #282828;
	}

	.searchGood .title {
		font-size: 28rpx;
		color: #999;
		margin: 50rpx 30rpx 25rpx 30rpx;
	}

	.searchGood .title .iconfont {
		font-size: 28rpx;
	}

	.searchGood .list {
		padding-left: 10rpx;
	}

	.searchGood .list .item {
		font-size: 26rpx;
		color: #454545;
		padding: 0 21rpx;
		height: 60rpx;
		border-radius: 3rpx;
		line-height: 60rpx;
		border: 1rpx solid #aaa;
		margin: 0 0 20rpx 20rpx;
	}

	.searchGood .list .item.history-item {
		height: 50rpx;
		border: none;
		border-radius: 25rpx;
		background-color: #F7F7F7;
		line-height: 50rpx;
	}

	.searchGood .line {
		border-bottom: 1rpx solid #eee;
		margin: 20rpx 30rpx 0 30rpx;
	}
</style>
