<template>
	<div class="quality-recommend" :style="colorStyle">
		<div class="slider-banner swiper">
			<view class="swiper">
				<swiper indicator-dots="true" :autoplay="autoplay" :circular="circular" :interval="interval"
					:duration="duration" indicator-color="rgba(255,255,255,0.6)" indicator-active-color="#fff">
					<block v-for="(item, index) in imgUrls" :key="index">
						<swiper-item>
							<image :src="item.img" class="slide-image" @click="goPages(item)"></image>
						</swiper-item>
					</block>
				</swiper>
			</view>
		</div>
		<div class="title acea-row row-center-wrapper">
			<div class="line"></div>
			<div class="name">
				<span class="iconfont" :class="icon"></span>
				{{ typeName[type]}}
			</div>
			<div class="line"></div>
		</div>
		<view class="wrapper">
			<GoodList :bastList="goodsList" :is-sort="false"></GoodList>
			<emptyPage v-if="goodsList.length == 0 && !isScroll" :title="$t(`暂无数据`)"></emptyPage>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
		<!-- <pageFooter></pageFooter> -->
	</div>
</template>
<script>
	import emptyPage from '@/components/emptyPage.vue';
	import GoodList from '@/components/goodList';
	import pageFooter from '@/components/pageFooter/index.vue';
	import {
		getGroomList
	} from '@/api/store';
	import {
		goPage
	} from '@/libs/order.js';
	import home from '@/components/home/index.vue'
	import colors from "@/mixins/color";
	export default {
		name: 'HotNewGoods',
		components: {
			GoodList,
			emptyPage,
			home,
			pageFooter
		},
		props: {},
		mixins: [colors],
		data: function() {
			return {
				imgUrls: [],
				goodsList: [],
				name: '',
				icon: '',
				type: 0,
				typeName: ['', this.$t(`精品推荐`), this.$t(`热门榜单`), this.$t(`首发新品`),
					this.$t(`促销单品`)
				],
				autoplay: true,
				circular: true,
				interval: 3000,
				duration: 500,
				page: 1,
				limit: 8,
				isScroll: true,
			};
		},
		onLoad: function(option) {
			this.type = option.type;
			this.titleInfo();
			this.name = option.name;
			// document.title = "精品推荐";
			uni.setNavigationBarTitle({
				title: option.name
			});
			this.getIndexGroomList();
		},
		methods: {
			titleInfo: function() {
				if (this.type === '1') {
					this.icon = 'icon-jingpintuijian';
				} else if (this.type === '2') {
					this.icon = 'icon-remen';
				} else if (this.type === '3') {
					this.icon = 'icon-xinpin';
				} else if (this.type === '4') {
					this.icon = 'icon-xinpin';
				}
			},
			goPages(item) {
				let url = item.link || '';
				goPage().then(res => {
					this.$util.JumpPath(url);
				});
			},
			getIndexGroomList() {
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
						that.$util.Tips({
							title: res
						});
					});
			},
			onReachBottom() {
				this.getIndexGroomList();
			}
		}
	}
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

			.name {
				margin: 0 20rpx;

				.iconfont {
					margin-right: 10rpx;
				}
			}

			.line {
				width: 190rpx;
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

	.acea-row {
		flex-wrap: nowrap !important;
	}
</style>
