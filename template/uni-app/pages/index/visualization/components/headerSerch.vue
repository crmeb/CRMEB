<template>
	<!-- #ifdef H5 -->
	<view v-if="isShow" class="header">
		<view class="serch-wrapper row-middle">
			<view class="logo">
				<image class="skeleton-rect" :src="logoConfig" mode="heightFix"></image>
				<view class="swiger">{{titleInfo.length ? $t(titleInfo[0].val) : ''}}</view>

			</view>
			<view class="input acea-row row-middle fillet skeleton-rect" hover-class="none" @click="goPage"><text
					class="iconfont icon-sousuo"></text>
				{{$t('搜索商品')}}</view>
		</view>
	</view>
	<view v-else-if="isIframe" class="header">
		<view class="serch-wrapper acea-row row-middle">
			<view class="logo">
				<image :src="logoConfig" mode="heightFix"></image>
				<view class="swiger">{{titleInfo.length ? titleInfo[0].val : ''}}</view>
			</view>
			<view class="input acea-row row-middle fillet" hover-class="none" @click="goPage"><text
					class="iconfont icon-sousuo"></text>
				{{$t('搜索商品')}}</view>
		</view>
	</view>
	<!-- #endif -->
	<!-- #ifdef MP  || APP-PLUS -->
	<view v-if="isShow">
		<view class="mp-header skeleton-rect" :style="{height:headH}">
			<view class="sys-head" :style="{height:sysHeight}"></view>
			<view class="serch-box" style="height: 48px;">
				<view class="serch-wrapper row-middle">
					<view class="logo">
						<image class="skeleton-rect" :src="logoConfig" mode="heightFix"></image>
						<view class="swiger">{{titleInfo.length ? titleInfo[0].val : ''}}</view>
					</view>
					<navigator url="/pages/goods/goods_search/index" class="input acea-row row-middle fillet"
						hover-class="none"><text class="iconfont icon-sousuo"></text>
						{{$t('搜索商品')}}</navigator>
				</view>
			</view>
		</view>
	</view>
	<!-- #endif -->
</template>

<script>
	let app = getApp();
	let statusBarHeight = uni.getSystemInfoSync().statusBarHeight * 2 + 'rpx';
	let headHeight = uni.getSystemInfoSync().statusBarHeight * 2 + 170 + 'rpx';
	import {
		goPage
	} from '@/libs/order.js'
	export default {
		name: 'headerSerch',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			}
		},
		data() {
			return {
				logoConfig: '',
				hotWords: [],
				sysHeight: statusBarHeight,
				headH: headHeight,
				name: this.$options.name,
				isShow: true,
				isIframe: app.globalData.isIframe,
				titleInfo: []
			};
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if (nVal) {
						this.logoConfig = nVal ? nVal.imgUrl.url : '';
						this.hotWords = nVal.hotList.list || []
						this.isShow = nVal.isShow.val
						this.titleInfo = nVal.titleInfo && nVal.titleInfo.list.length ? nVal.titleInfo.list : [];
						uni.setStorageSync('hotList', this.hotWords || []);
					}
				}
			}
		},
		mounted() {
			let that = this;
			// #ifdef MP
			this.$nextTick(function() {
				// 获取小程序头部高度
				let info = uni.createSelectorQuery().in(this).select(".mp-header");
				info.boundingClientRect(function(data) {
					that.marTop = data.height
				}).exec()
			})
			// #endif
		},
		methods: {
			goPage() {
				goPage().then(res => {
					uni.navigateTo({
						url: '/pages/goods/goods_search/index'
					})
				})
			}
		}
	}
</script>

<style lang="scss">
	/* #ifdef H5 */
	.header {
		width: 100%;
		height: 210rpx;
		background: #fff;
		background: linear-gradient(90deg, var(--view-main-start) 0%, var(--view-main-over) 100%);
		border-bottom-left-radius: 60rpx;
		border-bottom-right-radius: 60rpx;

		.serch-wrapper {
			padding: 20rpx 30rpx 0 30rpx;

			.logo {
				margin-right: 30rpx;
				display: flex;
				align-items: flex-end;

				image {
					width: 144rpx;
					height: 50rpx;
				}

				.swiger {
					color: #fff;
					font-size: 24rpx;
					margin-left: 20rpx;
					margin-bottom: -2rpx;
				}
			}

			.input {
				display: flex;
				height: 60rpx;
				padding: 0 0 0 30rpx;
				background: rgba(247, 247, 247, 1);
				border: 1px solid rgba(241, 241, 241, 1);
				color: #999;
				font-size: 28rpx;
				flex: 1;
				z-index: 99;
				margin: 14rpx 0;

				.iconfont {
					margin-right: 20rpx;
					color: #555555;
				}

				// 没有logo，直接搜索框
				&.on {
					width: 100%;
				}

				// 设置圆角
				&.fillet {
					border-radius: 40rpx;
				}

				// 文本框文字居中
				&.row-center {
					padding: 0;
				}
			}
		}
	}

	/* #endif */
	/* #ifdef MP || APP-PLUS */
	.mp-header {
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		background: linear-gradient(90deg, var(--view-main-start) 0%, var(--view-main-over) 100%);
		z-index: 999;

		// height: 250rpx;
		.serch-box {
			padding-bottom: 10rpx;
		}

		.serch-wrapper {
			height: 100%;
			padding: 15rpx 30rpx 20rpx 30rpx;

			.logo {
				margin-right: 30rpx;
				display: flex;
				align-items: flex-end;

				image {
					width: 144rpx;
					height: 50rpx;
				}

				.swiger {
					color: #fff;
					font-size: 24rpx;
					margin-left: 20rpx;
					margin-bottom: -2rpx;
				}
			}

			.input {
				height: 60rpx;
				padding: 0 0 0 30rpx;
				background: rgba(247, 247, 247, 1);
				border: 1px solid rgba(241, 241, 241, 1);
				color: #999;
				font-size: 28rpx;
				// margin: 14rpx 0;
				margin-top: 20rpx;
				margin-bottom: 20rpx;
				flex: 1;

				.iconfont {
					margin-right: 20rpx;
					color: #555555;
				}

				// 没有logo，直接搜索框
				&.on {
					width: 70%;
				}

				// 设置圆角
				&.fillet {
					border-radius: 40rpx;
				}

				// 文本框文字居中
				&.row-center {
					padding: 0;
				}
			}
		}
	}

	/* #endif */
</style>
