<template>
	<view>
		<view class='quality-recommend'>
			<swipers :imgUrls="imgUrls"></swipers>
			<view class='title acea-row row-center-wrapper'>
				<view class='line'></view>
				<view class='name'><text class='iconfont' :class="icon"></text>{{name}}</view>
				<view class='line'></view>
			</view>
			<goodList :bastList="bastList" :status="status"></goodList>
			<view class='loadingicon acea-row row-center-wrapper' v-if="bastList.length">
				<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
			</view>
		</view>
		<home></home>
	</view>
</template>

<script>
	import goodList from '@/components/goodList';
	import home from '@/components/home';
	import swipers from '@/components/swipers';
	import {
		getGroomList
	} from '@/api/store.js';
	export default {
		components: {
			goodList,
			swipers,
			home
		},
		data() {
			return {
				imgUrls: [],
				bastList: [],
				name: '',
				icon: '',
				type: 0,
				status: 0,
				loading: false,
				loadend: false,
				loadTitle: '加载更多', //提示语
				page: 1,
				limit: 20,
			};
		},
		onLoad: function(options) {
			this.type = options.type;
		},
		onShow: function() {
			let type = this.type;
			if (type == 1) {
				uni.setNavigationBarTitle({
					title: "精品推荐"
				});
				this.name = '精品推荐';
				this.icon = 'icon-jingpintuijian';
			} else if (type == 2) {
				uni.setNavigationBarTitle({
					title: "热门榜单"
				});
				this.name = '热门榜单';
				this.icon = 'icon-remen';
				this.status = 1;
			} else if (type == 3) {
				uni.setNavigationBarTitle({
					title: "首发新品"
				});
				this.name = '首发新品';
				this.icon = 'icon-xinpin';
			} else if (type == 4) {
				uni.setNavigationBarTitle({
					title: "促销单品"
				});
				this.name = '促销单品';
				this.icon = 'icon-cuxiaoguanli';
			} else {
				uni.setNavigationBarTitle({
					title: "首发新品"
				});
				this.name = '首发新品';
				this.icon = 'icon-xinpin';
			}
			this.getIndexGroomList();
		},
		onReachBottom: function() {
			this.getUseCoupons();
		},
		methods: {
			getIndexGroomList: function() {
				let that = this
				if (this.loadend) return false;
				if (this.loading) return false;
				getGroomList(that.type, {
					page: that.page,
					limit: that.limit
				}).then(res => {
					that.imgUrls = res.data.banner;
					let list = res.data.list,
						loadend = list.length < that.limit;
					let bastList = that.$util.SplitArray(list, that.bastList);
					that.$set(that, 'bastList', bastList);
					that.loadend = loadend;
					that.loadTitle = loadend ? '我也是有底线的' : '加载更多';
					that.page = that.page + 1;
					that.loading = false;
				}).catch(err => {
					that.loading = false;
					that.loadTitle = '加载更多';
				});
			}
		}
	}
</script>

<style lang="scss">
	page {
		background-color: #fff;
	}

	.quality-recommend .title {
		height: 120rpx;
		font-size: 32rpx;
		color: #282828;
		background-color: #f5f5f5;
	}

	.quality-recommend .title .line {
		width: 230rpx;
		height: 2rpx;
		background-color: #e9e9e9;
	}

	.quality-recommend .title .name {
		margin: 0 20rpx;
	}

	.quality-recommend .title .name .iconfont {
		margin-right: 13rpx;
		font-size: 38rpx;
		vertical-align: -4rpx;
		color: #343434;
	}
</style>
