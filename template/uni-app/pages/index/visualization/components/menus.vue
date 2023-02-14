<template>
	<view class="menu-main">
		<view class='nav acea-row acea-row' v-if="isShow && menus.length">
			<block v-for="(item,index) in menus" :key="index">
				<view class='item' @click="menusTap(item.info[1].value)">
					<view class='pictrue'>
						<easy-loadimage mode="widthFix" :image-src='item.img'></easy-loadimage>
						<!-- <image class="skeleton-radius" :src='item.img'></image> -->
					</view>
					<view class="menu-txt">{{$t(item.info[0].value)}}</view>
				</view>
			</block>
		</view>
		<view class='nav acea-row acea-row' v-if="!isShow && isIframe && menus.length">
			<block v-for="(item,index) in menus" :key="index">
				<view class='item' @click="menusTap(item.info[1].value)">
					<view class='pictrue'>
						<image :src='item.img'></image>
					</view>
					<view class="menu-txt">{{item.info[0].value}}</view>
				</view>
			</block>
		</view>
		<view class="nav acea-row acea-row" v-if="isIframe && !menus.length">
			<view class='item'>
				<view class='pictrue default'>
					<text class="iconfont icon-icon25201"></text>
				</view>
				<view class="menu-txt">{{$t(`默认`)}}</view>
			</view>
		</view>
	</view>
</template>

<script>
	let app = getApp()
	import {
		goPage
	} from '@/libs/order.js'
	export default {
		name: 'menus',
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
						this.menus = nVal.imgList.list;
						this.isShow = nVal.isShow.val
					}
				}
			}
		},
		data() {
			return {
				menus: [],
				name: this.$options.name,
				isIframe: false,
				isShow: true
			};
		},
		created() {
			this.isIframe = app.globalData.isIframe;
		},
		mounted() {},
		methods: {
			menusTap(url) {
				goPage().then(res => {
					if (url.indexOf("http") != -1) {
						// #ifdef H5
						location.href = url
						// #endif
					} else {
						// uni.navigateTo({
						// 	url: url
						// })
						if (['/pages/goods_cate/goods_cate', '/pages/order_addcart/order_addcart',
								'/pages/user/index'
							].indexOf(url) == -1) {
							uni.navigateTo({
								url: url
							})
						} else {
							uni.switchTab({
								url: url
							})
						}
					}
				})
			}
		}
	}
</script>

<style lang="scss">
	.menu-main {
		background-color: #fff;
	}



	.nav {
		margin: 0 30rpx;
		// margin:  $uni-index-margin-row $uni-index-margin-col;
		border-radius: $uni-border-radius-index;

		// box-shadow: $uni-index-box-shadow;
		.item {
			margin-top: 26rpx;
			width: 20%;
			text-align: center;
			font-size: 24rpx;

			.pictrue {
				width: 90rpx;
				height: 90rpx;
				margin: 0 auto;

				&.default {
					background-color: #ccc;
					border-radius: 50%;
					text-align: center;
					line-height: 90rpx;

					.iconfont {
						font-size: 40rpx;
					}
				}

				/deep/,
				/deep/image,
				/deep/.easy-loadimage,
				/deep/uni-image {
					width: 90rpx;
					height: 90rpx;
					border-radius: 50%;
				}
			}

			.menu-txt {
				margin-top: 15rpx;
			}

			&.four {
				width: 25%;

				.pictrue {
					width: 90rpx;
					height: 90rpx;
				}
			}
		}
	}
</style>
