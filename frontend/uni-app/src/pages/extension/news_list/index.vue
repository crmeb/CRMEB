<template>
	<view :style="colorStyle">
		<view class='newsList'>
			<view class='swiper' v-if="imgUrls.length > 0">
				<swiper indicator-dots="true" :autoplay="autoplay" :circular="circular" :interval="interval"
					:duration="duration" indicator-color="rgba(102,102,102,0.3)" indicator-active-color="#666">
					<block v-for="(item,index) in imgUrls" :key="index">
						<swiper-item>
							<navigator :render-link="false" :url="'/pages/extension/news_details/index?id='+item.id">
								<image :src="item.image_input[0]" class="slide-image" />
							</navigator>
						</swiper-item>
					</block>
				</swiper>
			</view>
			<view class='nav' v-if="navList.length > 0">
				<scroll-view class="scroll-view_x" scroll-x scroll-with-animation :scroll-left="scrollLeft"
					style="width:auto;overflow:hidden;">
					<block v-for="(item,index) in navList" :key="index">
						<view class='item' :class='active==item.id?"on":""' @click='tabSelect(item.id, index, item)'
							:id="`news_${item.id}`">
							<view>{{item.title}}</view>
							<view class='line bg-color' v-if="active==item.id"></view>
						</view>
					</block>
				</scroll-view>
				<scroll-view v-if="coutList.length" class="scroll-view_x" scroll-x scroll-with-animation
					style="width:auto;overflow:hidden;">
					<view class="coutry-list">
						<view class="coutry" :class='activeCou==coutry.id?"on":""' v-for="(coutry,index) in coutList"
							:key="index" @click="getCidArticle(coutry.id,1)">
							{{coutry.title}}
						</view>
					</view>
				</scroll-view>
			</view>
			<view class='list'>
				<block v-for="(item,index) in articleList" :key="index">
					<navigator :render-link="false" :url='"/pages/extension/news_details/index?id="+item.id' hover-class='none'
						class='item acea-row row-between-wrapper' v-if="item.image_input.length == 1">
						<view class='text acea-row row-column-between'>
							<view class='name line2'>{{item.title}}</view>
							<view>{{item.add_time}}</view>
						</view>
						<view class='pictrue'>
							<image :src='item.image_input[0]'></image>
						</view>
					</navigator>
					<navigator :render-link="false" :url='"/pages/extension/news_details/index?id="+item.id' hover-class='none' class='item'
						v-else-if="item.image_input.length == 2">
						<view class='title line1'>{{item.title}}</view>
						<view class='picList acea-row row-between-wrapper'>
							<block v-for="(itemImg,indexImg) in item.image_input" :key="indexImg">
								<view class='pictrue'>
									<image :src='itemImg'></image>
								</view>
							</block>
						</view>
						<view class='time'>{{item.add_time}}</view>
					</navigator>
					<navigator :render-link="false" :url='"/pages/extension/news_details/index?id="+item.id' hover-class='none' class='item'
						v-else-if="item.image_input.length > 2">
						<view class='title line1'>{{item.title}}</view>
						<view class='picList on acea-row row-between-wrapper'>
							<block v-for="(itemImg,indexImg) in item.image_input" :key="indexImg">
								<view class='pictrue'>
									<image :src='itemImg'></image>
								</view>
							</block>
						</view>
						<view class='time'>{{item.add_time}}</view>
					</navigator>
				</block>
			</view>
		</view>
		<view class='noCommodity' v-if="articleList.length == 0 && (page != 1 || active== 0)">
			<view class='emptyBox'>
				<image :src="imgHost + '/statics/images/empty-box.png'"></image>
			</view>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onShow, onReachBottom } from '@dcloudio/uni-app';
import {
	getArticleCategoryList,
	getArticleList,
	getArticleHotList,
	getArticleBannerList
} from '@/api/api.js';
import home from '@/components/home';
import { useColorTheme } from '@/composables/useColorTheme';
import { HTTP_REQUEST_URL } from '@/config/app';

defineOptions({ name: 'newsList' });

const { colorStyle } = useColorTheme();

// ---------- data ----------
const imgHost = HTTP_REQUEST_URL;
const coutList = ref([]);
const imgUrls = ref([]);
const articleList = ref([]);
const indicatorDots = ref(false);
const circular = ref(true);
const autoplay = ref(true);
const interval = ref(3000);
const duration = ref(500);
const navList = ref([]);
const active = ref(0);
const page = ref(1);
const limit = ref(8);
const status = ref(false);
const scrollLeft = ref(0);
const activeCou = ref(0);

/**
 * 生命周期函数--监听页面显示
 */
onShow(() => {
	// this.getArticleHot();
	status.value = false;
	page.value = 1;
	articleList.value = [];
	getCidArticle(0);
	getArticleBanner();
	getArticleCate();
});
/**
 * 页面上拉触底事件的处理函数
 */
onReachBottom(() => {
	getCidArticle(activeCou.value);
});

// ---------- methods ----------
function getArticleHot() {
	getArticleHotList().then(res => {
		articleList.value = res.data;
	});
}
function getArticleBanner() {
	getArticleBannerList().then(res => {
		imgUrls.value = res.data;
	});
}
function getCidArticle(id, type) {
	if (type) {
		status.value = false
		activeCou.value = id
		articleList.value = []
		page.value = 1
	}
	// if (id == 0) return;
	// 原代码在函数内以局部变量缓存 limit/page/articleList，为避免遮蔽同名 ref 改名
	let limitVal = limit.value;
	let pageVal = page.value;
	let oldArticleList = articleList.value;
	if (status.value) return;
	getArticleList(activeCou.value, {
		page: pageVal,
		limit: limitVal
	}).then(res => {
		let articleListNew = [];
		let len = res.length;
		articleListNew = oldArticleList.concat(res.data);
		page.value++;
		articleList.value = articleListNew;
		status.value = limitVal > len;
		page.value = page.value;
	});
}
function getArticleCate() {
	getArticleCategoryList().then(res => {
		navList.value = res.data;
	});
}
function tabSelect(activeId, e, item) {
	active.value = activeId;
	scrollLeft.value = e * 60;
	coutList.value = [];
	articleList.value = [];
	if (active.value == 0) {
		getArticleHot();
	} else {
		page.value = 1;
		status.value = false;
		if (item.children.length) {
			activeCou.value = item.children[0].id || 0
			coutList.value = item.children || []
			getCidArticle(activeCou.value);
		} else {
			getCidArticle(item.id, true);
		}
	}
}
</script>

<style lang="scss">
	.noCommodity {
		border-top-width: 0;
		padding-bottom: 30rpx;

		.emptyBox {
			margin-top: 50rpx;
			text-align: center;

			.tips {
				color: #aaa;
				font-size: 26rpx;
			}

			image {
				width: 414rpx;
				height: 304rpx;
			}
		}
	}

	page {
		background-color: #fff !important;
	}

	.newsList .swiper {
		width: 100%;
		position: relative;
		box-sizing: border-box;
		padding: 30rpx 30rpx 0 30rpx;
	}

	.newsList .swiper swiper {
		width: 100%;
		height: 365rpx;
		position: relative;
	}

	.newsList .swiper .slide-image {
		width: 100%;
		height: 335rpx;
		border-radius: 6rpx;
	}

	// #ifdef APP-PLUS || H5
	.newsList :deep(uni-swiper .uni-swiper-dots-horizontal){
		bottom: 20px;
	}

	.newsList .swiper :deep(.uni-swiper-dot){
		width: 12rpx !important;
		height: 12rpx !important;
		border-radius: 0;
		transform: rotate(-45deg);
		transform-origin: 0 100%;
	}

	.newsList .swiper :deep(.uni-swiper-dot~.uni-swiper-dot){
		margin-left: 5rpx;
	}

	// #endif
	// #ifdef MP
	.newsList :deep(wx-swiper .wx-swiper-dots-horizontal){
		bottom: 0;
	}

	.newsList .swiper :deep(.wx-swiper-dot){
		width: 12rpx !important;
		height: 12rpx !important;
		border-radius: 0;
		transform: rotate(-45deg);
		transform-origin: 0 100%;
	}

	.newsList .swiper :deep(.wx-swiper-dot~.wx-swiper-dot){
		margin-left: 5rpx;
	}

	// #endif
	.newsList .nav {
		padding: 0 30rpx;
		width: 100%;
		white-space: nowrap;
		box-sizing: border-box;
		margin-top: 43rpx;
	}

	.newsList .nav .item {
		display: inline-block;
		font-size: 32rpx;
		color: #999999;
	}

	.newsList .nav .item.on {
		color: #000;
		font-size: 36rpx;
		font-weight: bold;
	}

	.newsList .nav .item~.item {
		margin-left: 46rpx;
	}

	.newsList .nav .item .line {
		width: 24rpx;
		height: 4rpx;
		border-radius: 2rpx;
		margin: 10rpx auto 0 auto;
	}

	.newsList .list {
		margin-top: 20rpx;
	}

	.newsList .list .item {
		margin: 0 30rpx;
		border-bottom: 1rpx solid #f0f0f0;
		padding: 35rpx 0;
	}

	.newsList .list .item .pictrue {
		width: 250rpx;
		height: 156rpx;
	}

	.newsList .list .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.newsList .list .item .text {
		width: 420rpx;
		height: 156rpx;
		font-size: 24rpx;
		color: #999;
	}

	.newsList .list .item .text .name {
		font-size: 30rpx;
		color: #282828;
	}

	.newsList .list .item .picList .pictrue {
		width: 335rpx;
		height: 210rpx;
		margin-top: 30rpx;
	}

	.newsList .list .item .picList.on .pictrue {
		width: 217rpx;
		height: 136rpx;
	}

	.newsList .list .item .picList .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.newsList .list .item .time {
		text-align: right;
		font-size: 24rpx;
		color: #999;
		margin-top: 22rpx;
	}

	.coutry-list {
		display: flex;
		align-items: center;
		margin: 10rpx 0;
		padding-top: 10rpx;
		border-top: 1px solid #F2F2F2;
		height: 80rpx;

		.coutry {
			// background-color: #F5F5F5;
			border-radius: 26rpx;
			padding: 4rpx 20rpx;
			color: #666666;
			font-size: 26rpx;
			margin-right: 20rpx;
		}

		.coutry.on {
			// background-color: rgba(220, 217, 236, 1);
			border-style: solid;
			border-width: 2rpx;
			color: #E93323;
		}
	}
</style>
