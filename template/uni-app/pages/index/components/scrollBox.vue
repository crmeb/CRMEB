<template>
	<view>
		<view class='index-wrapper' v-if="isShow && fastList.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>快速选择</view>
					<view class='line1'>诚意推荐品质商品</view>
				</view>
				<navigator class='more' url="/pages/goods_cate/goods_cate">更多<text class='iconfont icon-jiantou'></text></navigator>
			</view>
			<view class='scroll-product'>
				<scroll-view class="scroll-view_x" scroll-x style="width:auto;overflow:hidden;">
					<block v-for="(item,index) in fastList" :key='index'>
						<view hover-class="none" class='item' @click="gopage('/pages/goods_list/index?sid='+item.id+'&title='+item.cate_name)">
							<view class='img-box'>
								<image :src='item.pic'></image>
							</view>
							<view class='pro-info line1'>{{item.cate_name}}</view>
						</view>
					</block>
				</scroll-view>
			</view>
		</view>
		<view class='index-wrapper' v-if="!isShow && isIframe && fastList.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>快速选择</view>
					<view class='line1'>诚意推荐品质商品</view>
				</view>
				<navigator class='more' url="/pages/goods_cate/goods_cate">更多<text class='iconfont icon-jiantou'></text></navigator>
			</view>
			<view class='scroll-product'>
				<scroll-view class="scroll-view_x" scroll-x style="width:auto;overflow:hidden;">
					<block v-for="(item,index) in fastList" :key='index'>
						<view hover-class="none" class='item' @click="gopage('/pages/goods_list/index?sid='+item.id+'&title='+item.cate_name)">
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
					<view class='name line1'>快速选择</view>
					<view class='line1'>诚意推荐品质商品</view>
				</view>
				<navigator class='more' url="/pages/goods_cate/goods_cate">更多<text class='iconfont icon-jiantou'></text></navigator>
			</view>
			<view class='scroll-product'>
				<view class="empty-img">快速选择，暂无数据</view>
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
					if(nVal){
						this.numConfig = nVal.numConfig.val;
						this.isShow = nVal.isShow.val;
						this.tabConfig = nVal.tabConfig?nVal.tabConfig.tabVal:0;
						this.selectConfig = nVal.selectConfig.activeValue;
						if (this.tabConfig) {
							this.fastList = nVal.goodsList.list
						} else {
							this.category();
						}
					}
				}
			}
		},
		created() {
		},
		mounted() {
		},
		data() {
			return {
				fastInfo: '上百种商品分类任您选择',
				fastList: [],
				name: this.$options.name,
				isShow: true,
				isIframe: app.globalData.isIframe,
				numConfig: 0,
				selectConfig: 0,
				tabConfig: 0
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
	.scroll-product {
		white-space: nowrap;
		margin-top: 38rpx;
		padding: 0 30rpx 37rpx 30rpx;
	}

	.scroll-product .item {
		width: 180rpx;
		display: inline-block;
		margin-right: 19rpx;
		border-bottom: 4rpx solid #47b479;
		box-shadow: 0 40rpx 30rpx -10rpx #eee;
	}

	.scroll-product .item:nth-of-type(3n) {
		border-bottom: 4rpx solid #ff6960;
	}

	.scroll-product .item:nth-of-type(3n-1) {
		border-bottom: 4rpx solid #579afe;
	}

	.scroll-product .item:nth-last-child(1) {
		margin-right: 0;
	}

	.scroll-product .item .img-box {
		width: 100%;
		height: 180rpx;
	}

	.scroll-product .item .img-box image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx 6rpx 0 0;
	}

	.scroll-product .item .pro-info {
		font-size: 24rpx;
		color: #282828;
		text-align: center;
		height: 60rpx;
		line-height: 60rpx;
		border: 1rpx solid #f5f5f5;
		border-bottom: 0;
		border-top: 0;
		padding: 0 10rpx;
	}

	.empty-img {
		width: 690rpx;
		height: 300rpx;
		border-radius: 14rpx;
		margin: 26rpx auto 0 auto;
		background-color: #ccc;
		text-align: center;
		line-height: 300rpx;
		.iconfont{
			font-size: 50rpx;
		}
	}
</style>
