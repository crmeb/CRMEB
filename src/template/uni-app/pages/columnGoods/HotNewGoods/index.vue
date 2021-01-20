<template>
	<div class="quality-recommend">
		<div class="slider-banner swiper">
			<view class="swiper">
				<swiper
					indicator-dots="true"
					:autoplay="autoplay"
					:circular="circular"
					:interval="interval"
					:duration="duration"
					indicator-color="rgba(255,255,255,0.6)"
					indicator-active-color="#fff"
				>
					<block v-for="(item, index) in imgUrls" :key="index">
						<swiper-item><image :src="item.img" class="slide-image" @click="goPages(item)"></image></swiper-item>
					</block>
				</swiper>
			</view>
		</div>
		<div class="title acea-row row-center-wrapper">
			<div class="line"></div>
			<div class="name">
				<span class="iconfont" :class="icon"></span>
				{{ name }}
			</div>
			<div class="line"></div>
		</div>
		<view class="wrapper">
			<GoodList :bastList="goodsList" :is-sort="false"></GoodList>
			<view class="txt-bar" v-if="goodsList.length > 0 && !isScroll">我是有底线的~</view>
			<emptyPage v-if="goodsList.length == 0 && !isScroll" title="暂无数据~"></emptyPage>
		</view>
	</div>
</template>
<script>
import emptyPage from '@/components/emptyPage.vue';
import GoodList from '@/components/goodList';
import { getGroomList } from '@/api/store';
import { goPage } from '@/libs/order.js';

export default {
	name: 'HotNewGoods',
	components: {
		GoodList,
		emptyPage
	},
	props: {},
	data: function() {
		return {
			imgUrls: [],
			goodsList: [],
			name: '',
			icon: '',
			type: 0,
			autoplay: true,
			circular: true,
			interval: 3000,
			duration: 500,
			page: 1,
			limit: 8,
			isScroll: true
		};
	},
	onLoad: function(option) {
		this.type = option.type;
		console.log(option);
		this.titleInfo();
		this.getIndexGroomList();
	},
	methods: {
		titleInfo: function() {
			if (this.type === '1') {
				this.name = '精品推荐';
				this.icon = 'icon-jingpintuijian';
				// document.title = "精品推荐";
				uni.setNavigationBarTitle({
					title: '精品推荐'
				});
			} else if (this.type === '2') {
				this.name = '热门榜单';
				this.icon = 'icon-remen';
				uni.setNavigationBarTitle({
					title: '热门榜单'
				});
			} else if (this.type === '3') {
				this.name = '首发新品';
				this.icon = 'icon-xinpin';
				uni.setNavigationBarTitle({
					title: '首发新品'
				});
			} else if (this.type === '4') {
				this.name = '促销单品';
				this.icon = 'icon-xinpin';
				uni.setNavigationBarTitle({
					title: '促销单品'
				});
			}
		},
		goPages(item) {
			let url = item.link || '';
			goPage().then(res => {
				if (url.indexOf('http') != -1) {
					// #ifdef H5
					location.href = url;
					// #endif
				} else {
					if (['/pages/goods_cate/goods_cate', '/pages/order_addcart/order_addcart', '/pages/user/index'].indexOf(url) == -1) {
						uni.navigateTo({
							url: url
						});
					} else {
						uni.navigateTo({
							url: url
						});
					}
				}
			});
		},
		getIndexGroomList: function() {
			if (!this.isScroll) return;
			let that = this;
			let type = this.type;
			getGroomList(type, {
				page: this.page,
				limit: this.limit
			})
				.then(res => {
					that.imgUrls = res.data.banner;
					that.goodsList = that.goodsList.concat(res.data.list);
					that.isScroll = res.data.list.length >= that.limit;
					that.page++;
				})
				.catch(function(res) {
					that.$util.Tips({ title: res });
				});
		}
	},
	onReachBottom() {
		this.getIndexGroomList();
	}
};
</script>
<style lang="scss">
/deep/ .empty-box {
	background-color: #f5f5f5;
}
.swiper,
swiper,
swiper-item,
.slide-image {
	width: 100%;
	height: 280rpx;
}
.quality-recommend {
	.wrapper {
		background: #fff;
	}
	.title {
		height: 120rpx;
		font-size: 32rpx;
		color: #282828;
		background-color: #f5f5f5;
		.line {
			width: 230rpx;
			height: 2rpx;
			background-color: #e9e9e9;
		}
	}
}
.txt-bar {
	padding: 20rpx 0;
	text-align: center;
	font-size: 26rpx;
	color: #666;
	background-color: #f5f5f5;
}
</style>
