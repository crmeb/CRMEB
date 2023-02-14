<template>
	<view>
		<view class='index-wrapper skeleton-rect' v-if="isShow && fastList.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>{{$t(titleInfo[0].val)}}</view>
					<view class='line1 txt-btn'>{{$t(titleInfo[1].val)}}</view>
				</view>
				<navigator class='more' open-type="switchTab" :url="titleInfo[2].val">{{$t(`更多`)}}<text
						class='iconfont icon-jiantou'></text></navigator>
			</view>
			<view class='scroll-product'>
				<scroll-view class="scroll-view_x" scroll-x style="width:auto;overflow:hidden;">
					<block v-for="(item,index) in fastList" :key='index'>
						<view hover-class="none" class='item'
							@click="gopage('/pages/goods/goods_list/index?sid='+item.id+'&title='+item.cate_name)">
							<view class='img-box'>
								<easy-loadimage mode="widthFix" :image-src="item.pic"></easy-loadimage>
							</view>
							<view class='pro-info line1'>{{$t(item.cate_name)}}</view>
						</view>
					</block>
				</scroll-view>
			</view>
		</view>
		<view class='index-wrapper' v-if="!isShow && isIframe && fastList.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>{{titleInfo[0].val}}</view>
					<view class='line1 txt-btn'>{{titleInfo[1].val}}</view>
				</view>
				<navigator class='more' open-type="switchTab" :url="titleInfo[2].val">{{$t(`更多`)}}<text
						class='iconfont icon-jiantou'></text></navigator>
			</view>
			<view class='scroll-product'>
				<scroll-view class="scroll-view_x" scroll-x style="width:auto;overflow:hidden;">
					<block v-for="(item,index) in fastList" :key='index'>
						<view hover-class="none" class='item'
							@click="gopage('/pages/goods/goods_list/index?sid='+item.id+'&title='+item.cate_name)">
							<view class='img-box'>
								<image :src='item.pic'></image>
							</view>
							<view class='pro-info line1'>{{item.cate_name}}</view>
						</view>
					</block>
				</scroll-view>
			</view>
		</view>
		<view class='index-wrapper' v-if="isIframe && !fastList.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>{{titleInfo[0].val}}</view>
					<view class='line1 txt-btn'>{{titleInfo[1].val}}</view>
				</view>
				<navigator class='more' open-type="switchTab" :url="titleInfo[2].val">{{$t(`更多`)}}<text
						class='iconfont icon-jiantou'></text></navigator>
			</view>
			<view class='scroll-product'>
				<view class="empty-img">{{$t(`快速选择，暂无数据`)}}</view>
			</view>
		</view>
	</view>
</template>

<script>
	let app = getApp()
	// import {
	// 	mapState
	// } from 'vuex'
	import {
		goPage
	} from '@/libs/order.js'
	import {
		category
	} from '@/api/api.js';
	export default {
		name: 'scrollBox',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			}
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if (nVal) {
						this.numConfig = nVal.numConfig.val;
						this.isShow = nVal.isShow.val;
						this.tabConfig = nVal.tabConfig ? nVal.tabConfig.tabVal : 0;
						this.selectConfig = nVal.selectConfig.activeValue || '';
						this.titleInfo = nVal.titleInfo.list;
						if (this.tabConfig) {
							this.fastList = nVal.goodsList.list
						} else {
							this.category();
						}
					}
				}
			}
		},
		created() {},
		mounted() {},
		data() {
			return {
				fastInfo: this.$t(`上百种商品分类任您选择`),
				fastList: [],
				name: this.$options.name,
				isShow: true,
				isIframe: app.globalData.isIframe,
				numConfig: 0,
				selectConfig: 0,
				tabConfig: 0,
				titleInfo: []
			}
		},
		methods: {
			gopage(url) {
				goPage().then(res => {
					uni.navigateTo({
						url: url,
					});

				})
			},
			category() {
				category({
					pid: this.selectConfig,
					limit: this.numConfig
				}).then(res => {
					this.fastList = res.data;
				});
			}
		}
	}
</script>

<style lang="scss">
	.index-wrapper {
		background-color: $uni-bg-color;
		margin: $uni-index-margin-row $uni-index-margin-col;
		border-radius: $uni-border-radius-index;
		// box-shadow: $uni-index-box-shadow;
	}

	.text {
		display: flex;

		.name {
			font-size: $uni-index-title-font-size;
			font-weight: bold;
		}

		.txt-btn {
			display: flex;
			align-items: flex-end;
			margin-bottom: 8rpx;
			margin-left: 12rpx;
		}

	}

	.scroll-product {
		white-space: nowrap;
		margin-top: 30rpx;
		padding: 0 20rpx 20rpx 20rpx;
	}

	.scroll-product .item {
		display: inline-block;
		margin-right: 24rpx;
		border-radius: 0 0 10rpx 10rpx;
		// box-shadow: 0 40rpx 30rpx -10rpx #eee;
		// background: linear-gradient(180deg, #fff 0%, #fff 30%, rgba(255, 96, 16, 0.2) 100%);
		background: linear-gradient(180deg, rgba(255, 255, 255, 0) 0%, rgba(255, 207, 183, 0.65) 100%);
		color: #FF7E00;
	}

	.scroll-product .item:nth-of-type(3n) {
		color: #1DB0FC;
		background: linear-gradient(180deg, rgba(255, 255, 255, 0) 0%, rgba(187, 221, 255, 0.65) 100%);
	}

	.scroll-product .item:nth-of-type(3n-1) {
		color: #FF448F;
		background: linear-gradient(180deg, rgba(255, 255, 255, 0) 0%, rgba(253, 199, 255, 0.65) 100%);
	}

	.scroll-product .item:nth-last-child(1) {
		margin-right: 0;
	}

	.scroll-product .item .img-box {
		width: 160rpx;
		height: 160rpx;
		margin: 0 10rpx;
		border-radius: 10px;
	}

	.scroll-product .item .img-box image {
		width: 100%;
		height: 100%;
		border-radius: 10rpx;
	}

	.scroll-product .item .img-box {

		/deep/,
		/deep/image,
		/deep/.easy-loadimage,
		/deep/uni-image {

			width: 160rpx;
			height: 160rpx;
			border-radius: 10rpx;
		}
	}

	.scroll-product .item .pro-info {
		max-width: 180rpx;
		font-size: 24rpx;
		text-align: center;
		height: 60rpx;
		line-height: 60rpx;
		border-bottom: 0;
		border-top: 0;
		padding: 0 10rpx;
		font-weight: bold;
	}

	.empty-img {
		height: 300rpx;
		border-radius: 14rpx;
		margin: 26rpx auto 0 auto;
		background-color: #ccc;
		text-align: center;
		line-height: 300rpx;

		.iconfont {
			font-size: 50rpx;
		}
	}

	.more {
		color: #999;
		font-size: 24rpx;
	}
</style>
