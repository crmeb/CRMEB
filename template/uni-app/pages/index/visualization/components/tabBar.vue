<template>
	<view :style="colorStyle">
		<view class="uni-tabbar acea-row row-around row-middle" v-if="isShow && tabbar.length && !isIframe">
			<view class="uni-tabbar_item" v-for="(item,index) in tabbar" :key="index" @tap="changeTab(item)">
				<view class="uni-tabbar_icon">
					<image v-if="item.link == pagePath" mode="aspectFit" :src="item.imgList[0]"></image>
					<image v-else mode="aspectFit" :src="item.imgList[1]"></image>
				</view>
				<view class="uni-tabbar_label" :class="{'active': item.link == pagePath}">
					{{$t(item.name)}}
				</view>
			</view>
		</view>
		<view class="uni-tabbar acea-row row-around row-middle" v-if="isIframe && tabbar.length">
			<view class="uni-tabbar_item" v-for="(item,index) in tabbar" :key="index" @tap="changeTab(item)">
				<view class="uni-tabbar_icon">
					<image v-if="item.link == pagePath" mode="aspectFit" :src="item.imgList[0]"></image>
					<image v-else mode="aspectFit" :src="item.imgList[1]"></image>
				</view>
				<view class="uni-tabbar_label" :class="{'active': item.link == pagePath}">
					{{$t(item.name)}}
				</view>
			</view>
		</view>
		<view v-if="isIframe && !tabbar.length" class="empty-img uni-tabbar acea-row row-around row-middle">
			{{$t(`暂无数据，请设置`)}}
		</view>
	</view>
</template>

<script>
	let app = getApp();
	import {
		getDiy
	} from '@/api/api.js';
	import {
		goPage
	} from '@/libs/order.js'
	import colors from '@/mixins/color.js';
	export default {
		name: 'tabBar',
		props: {
			pagePath: null,
			dataConfig: {
				type: Object,
				default: () => {}
			},
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if (nVal) {
						this.isShow = nVal.isShow.val;
					}
				}
			}
		},
		mixins: [colors],
		data() {
			return {
				name: this.$options.name,
				page: '/pages/index/index',
				tabbar: this.$Cache.get('TAB_BAR') ? JSON.parse(this.$Cache.get('TAB_BAR')) : [],
				isShow: true, //true前台显示
				isIframe: app.globalData.isIframe //true后台显示
			};
		},
		mounted() {
			if (!this.tabbar.length) this.getDiyData()
		},
		methods: {
			getDiyData() {
				getDiy().then(res => {
					const {
						list
					} = res.data.tabBar.default.tabBarList;
					this.$Cache.set('TAB_BAR', list)
					this.tabbar = list;
				}).catch(err => {
					uni.showToast({
						title: err,
						icon: 'none'
					});
				});
			},
			changeTab(item) {
				goPage().then(res => {
					this.page = item.link;
					// 这里使用reLaunch关闭所有的页面，打开新的栏目页面
					uni.switchTab({
						url: this.page,
						fail: () => {
							uni.navigateTo({
								url: this.page
							})
						}
					});
				})
			},
		}
	}
</script>

<style lang="scss" scoped>
	.borderShow {
		position: fixed;
	}

	.borderShow .uni-tabbar::after {
		content: ' ';
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		border: 1px dashed #007AFF;
		box-sizing: border-box;
	}

	.uni-tabbar {
		position: fixed;
		bottom: 0;
		left: 0;
		z-index: 999;
		width: 100%;
		height: 98rpx;
		height: calc(98rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		height: calc(98rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		padding-bottom: calc(constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		padding-bottom: calc(env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		box-sizing: border-box;
		border-top: solid 1rpx #F3F3F3;
		background-color: #fff;
		box-shadow: 0px 0px 17rpx 1rpx rgba(206, 206, 206, 0.32);
		display: flex;
		flex-wrap: nowrap;
		align-items: center;
		justify-content: space-around;

		.uni-tabbar_item {
			width: 100%;
			font-size: 20rpx;
			text-align: center;
		}

		.uni-tabbar_icon {
			height: 50rpx;
			width: 50rpx;
			text-align: center;
			margin: 0 auto;

			image {
				width: 100%;
				height: 100%;
			}
		}

		.uni-tabbar_label {
			font-size: 24rpx;
			color: rgb(40, 40, 40);

			&.active {
				color: var(--view-theme);
			}
		}
	}
</style>
