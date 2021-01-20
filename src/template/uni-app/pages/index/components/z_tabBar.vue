<template>
	<view id="tabbar" class="uni-tabbar acea-row row-around row-middle" :class="{borderShow:isBorader}">
		<template v-if="isShow && tabbar.length">
			<view class="uni-tabbar_item" v-for="(item,index) in tabbar" :key="index" @tap="changeTab(item)">
				<view class="uni-tabbar_icon">
					<image v-if="item.pagePath == pagePath" mode="aspectFit" :src="item.selectedIconPath"></image>
					<image v-else mode="aspectFit" :src="item.iconPath"></image>
				</view>
				<view class="uni-tabbar_label" :class="{'active': item.pagePath == pagePath}">
					{{item.name}}
				</view>
			</view>
		</template>
		<template v-if="!isShow && isIframe && tabbar.length">
			<view class="uni-tabbar_item" v-for="(item,index) in tabbar" :key="index" @tap="changeTab(item)">
				<view class="uni-tabbar_icon">
					<image v-if="item.pagePath == pagePath" mode="aspectFit" :src="item.selectedIconPath"></image>
					<image v-else mode="aspectFit" :src="item.iconPath"></image>
				</view>
				<view class="uni-tabbar_label" :class="{'active': item.pagePath == pagePath}">
					{{item.name}}
				</view>
			</view>
		</template>
		<view v-else-if="isIframe && !tabbar.length" class="empty-img">暂无数据，请设置</view>
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
	export default {
		name: 'z_tabBar',
		props: {
			pagePath: null,
			dataConfig: {
				type: Object,
				default: () => {}
			},
			activeName: {
				type: String,
				default: ''
			}
		},
		watch: {
			activeName: {
				handler(nVal, oVal) {
					if (nVal == this.name && app.globalData.isIframe) {
						this.isBorader = true
					} else {
						this.isBorader = false
					}
				},
				deep: true
			}
		},
		data() {
			return {
				isBorader: false,
				name: this.$options.name,
				page: '/pages/index/index',
				showPage: false,
				containerHeight: 400,
				tabbar: [],
				isShow: app.globalData.tabbarShow,
				isIframe: false
			};
		},
		created() {
			if (this.dataConfig && this.dataConfig.tabBarList) {
				uni.setStorageSync('tabbar', this.dataConfig.tabBarList.list);
			}
			this.isIframe = app.globalData.isIframe;
		},
		mounted() {
			this.$nextTick(() => {
				const query = uni.createSelectorQuery().in(this);
				query.select('#tabbar').boundingClientRect(data => {
					this.$emit('tabbar-h', data.height);
				}).exec();
			});
			try {
				const value = uni.getStorageSync('tabbar');
				if (value) {
					this.tabbar = value;
				} else {
					getDiy().then(res => {
						const {
							list
						} = res.data.z_tabBar.tabBarList;
						this.tabbar = list;
						uni.setStorageSync('tabbar', list);
					}).catch(err => {
						uni.showToast({
							title: err,
							icon: 'none'
						});
					});
				}
			} catch (e) {
				uni.showToast({
					title: e,
					icon: 'none'
				});
			}
		},
		methods: {
			changeTab(item) {
				goPage().then(res => {
					this.page = item.pagePath;
					// 这里使用reLaunch关闭所有的页面，打开新的栏目页面
					uni.reLaunch({
						url: this.page
					});
				})
			},
		}
	}
</script>

<style lang="scss" scoped>
	.uni-tabbar {
		position: fixed;
		bottom: 0;
		z-index: 9999;
		width: 100%;
		height: calc(98rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		height: calc(98rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		box-sizing: border-box;
		border-top: solid 1rpx #F3F3F3;
		background-color: #fff;
		box-shadow: 0px 0px 17rpx 1rpx rgba(206, 206, 206, 0.32);
		padding-bottom: constant(safe-area-inset-bottom); ///兼容 IOS<11.2/
		padding-bottom: env(safe-area-inset-bottom); ///兼容 IOS>11.2/

		.uni-tabbar_item {
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
			color: #282828;

			&.active {
				color: #ff3366;
			}
		}
	}
</style>
