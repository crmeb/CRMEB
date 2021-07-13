<template>
	<view class="swiperBg" :style="{marginTop:mt+'px'}">
		<block v-if="isShow && imgUrls.length">
			<view class="swiper square" v-if="imgUrls.length">
				<swiper indicator-dots="true" :autoplay="true" :circular="circular" :interval="interval" :duration="duration"
				 indicator-color="rgba(255,255,255,0.6)" indicator-active-color="#fff">
					<block v-for="(item,index) in imgUrls" :key="index">
						<swiper-item>
							<view @click="goDetail(item)" class='slide-navigator acea-row row-between-wrapper'>
								<image :src="item.img" class="slide-image"></image>
							</view>
						</swiper-item>
					</block>
				</swiper>
			</view>
		</block>
		<block v-if="!isShow && isIframe && imgUrls.length">
			<view class="swiper square" v-if="imgUrls.length">
				<swiper indicator-dots="true" :autoplay="true" :circular="circular" :interval="interval" :duration="duration"
				 indicator-color="rgba(255,255,255,0.6)" indicator-active-color="#fff">
					<block v-for="(item,index) in imgUrls" :key="index">
						<swiper-item>
							<view @click="goDetail(item)" class='slide-navigator acea-row row-between-wrapper'>
								<image :src="item.img" class="slide-image"></image>
							</view>
						</swiper-item>
					</block>
				</swiper>
			</view>
		</block>
		<block v-if="isIframe && !imgUrls.length">
			<view class="empty-img">暂无图片，请上传图片</view>
		</block>
	</view>
</template>

<script>
	let statusBarHeight = uni.getSystemInfoSync().statusBarHeight + 'px';
	let app = getApp();
	import {
		goPage
	} from '@/libs/order.js'
	export default {
		name: 'swiperBg',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if(nVal){
						this.imgUrls = nVal.imgList?nVal.imgList.list:[];
						this.isShow = nVal.isShow?nVal.isShow.val:true
					}
				}
			}
		},
		data() {
			return {
				indicatorDots: false,
				circular: true,
				autoplay: true,
				interval: 3000,
				duration: 500,
				imgUrls: [], //图片轮播数据
				name: this.$options.name,
				isIframe: false,
				mt: 0,
				isShow: true
			};
		},
		created() {
			// #ifdef MP
			this.mt = parseFloat(statusBarHeight) + 43
			// #endif
			this.isIframe = app.globalData.isIframe;
		},
		mounted() {
			let that = this;
		},
		methods: {
			goDetail(url) {
				goPage().then(res => {
					let urls = url.info[1].value
					if (urls) {
						if (urls.indexOf("http") != -1) {
							// #ifdef H5
							location.href = urls
							// #endif
						} else {
							if (['/pages/goods_cate/goods_cate', '/pages/order_addcart/order_addcart', '/pages/user/index'].indexOf(urls) ==
								-1) {
								uni.navigateTo({
									url: urls
								})
							} else {
								uni.navigateTo({
									url: urls
								})
							}
						}
					}
				})
			}
		}
	}
</script>

<style lang="scss">
	.swiperBg {
		position: relative;

		/* #ifdef MP */
		/* #endif */
		.colorBg {
			position: absolute;
			left: 0;
			top: 0;
			height: 130rpx;
			width: 100%;
		}

		.swiper {
			z-index: 20;
			position: relative;
			// margin: -130rpx auto 0 auto;
			overflow: hidden;
			// image {
			//   width: 100%;
			//   height: auto;
			//   display: block;
			// }

			/* 设置圆角 */
			&.fillet {
				border-radius: 10rpx;

				image {
					border-radius: 10rpx;
				}
			}

			swiper,
			.swiper-item,
			image {
				width: 100%;
				height: 375rpx;
				display: block;
			}

			// 圆形指示点
			&.circular {
				/deep/.uni-swiper-dot {
					width: 10rpx !important;
					height: 10rpx !important;
					background: rgba(0, 0, 0, .4) !important
				}

				/deep/.uni-swiper-dot-active {
					background: #fff !important
				}
			}

			// 方形指示点
			&.square {
				/deep/.uni-swiper-dot {
					width: 20rpx !important;
					height: 5rpx !important;
					border-radius: 3rpx;
					background: rgba(0, 0, 0, .4) !important
				}

				/deep/.uni-swiper-dot-active {
					background: #fff !important
				}
			}
		}
	}

	.item-img image {
		display: block;
		width: 100%;
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
