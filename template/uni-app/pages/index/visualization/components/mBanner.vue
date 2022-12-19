<template>
	<view class="swiperBg">
		<view class='boutique' :class="{'off': sty ==='off'}" v-if="isShow && bastBanner.length && !isIframe">
			<swiper autoplay="true" indicator-dots="true" :circular="circular" :interval="interval" :duration="duration"
				indicator-color="rgba(255,255,255,0.6)" indicator-active-color="#fff" :style="'height:'+ (imageH) +'rpx;'">
				<block v-for="(item,index) in bastBanner">
					<swiper-item :key='index'>
						<view style='width:100%;height:100%;' hover-class='none' @click="goDetail(item)">
							<image :src="item.img" class="slide-image" :style="'height:'+ (imageH) +'rpx;'" />
						</view>
					</swiper-item>
				</block>
			</swiper>
		</view>
		<view class='boutique' :style="'height:'+ (imageH) +'rpx;'" v-if="bastBanner.length && isIframe">
			<swiper autoplay="true" indicator-dots="true" :style="'height:'+ (imageH) +'rpx;'" :circular="circular" :interval="interval" :duration="duration"
				indicator-color="rgba(255,255,255,0.6)" indicator-active-color="#fff">
				<block v-for="(item,index) in bastBanner">
					<swiper-item :key='index'>
						<view style='width:100%;height:100%;' hover-class='none' @click="goDetail(item)">
							<image :src="item.img" class="slide-image" :style="'height:'+ (imageH) +'rpx;'"/>
						</view>
					</swiper-item>
				</block>
			</swiper>
		</view>
		<block v-if="isIframe && !bastBanner.length">
			<view class="empty-img">{{$t(`暂无图片，请上传图片`)}}</view>
		</block>
	</view>
</template>

<script>
	let app = getApp()
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
			sty: {
				type: String,
				default: 'on'
			}
		},
		data() {
			return {
				indicatorDots: false,
				circular: true,
				autoplay: true,
				interval: 3000,
				duration: 500,
				bastBanner: [], //图片轮播数据
				name: this.$options.name,
				isIframe: false,
				isShow: true,
				imageH: 375
			};
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if (nVal) {
						this.bastBanner = nVal.imgList.list;
						this.isShow = nVal.isShow.val;
						this.imgUrls = nVal.imgList ? nVal.imgList.list : [];
						this.isShow = nVal.isShow ? nVal.isShow.val : true
						uni.getImageInfo({
							src: this.imgUrls.length ? this.imgUrls[0].img : '',
							success: (res) => {
								if (res && res.height > 0) {
									this.$set(this, 'imageH',
										res.height / res
										.width * 690)
								} else {
									this.$set(this, 'imageH', 375);
								}
							},
							fail: (error) => {
								this.$set(this, 'imageH', 375);
							}
						})
					}
				}
			}
		},
		created() {
			this.isIframe = app.globalData.isIframe
		},
		mounted() {},
		methods: {
			//替换安全域名
			setDomain: function(url) {
				url = url ? url.toString() : '';
				//本地调试打开,生产请注销
				if (url.indexOf("https://") > -1) return url;
				else return url.replace('http://', 'https://');
			},
			goDetail(url) {
				goPage().then(res => {
					let urls = url.info[1].value
					if (urls.indexOf("http") != -1) {
						// #ifdef H5 || APP-PLUS
						location.href = urls
						// #endif
					} else {
						if (['/pages/goods_cate/goods_cate', '/pages/order_addcart/order_addcart',
								'/pages/user/index'
							].indexOf(urls) ==
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
				})
			}
		}
	}
</script>

<style lang="scss">
	.swiperBg {
		// background-color: $uni-bg-color;
		width: 100%;
	}

	.empty-img {
		width: 690rpx;
		// height: 300rpx;
		// border-radius: 14rpx;
		margin: 26rpx auto 0 auto;
		background-color: #ccc;
		text-align: center;
		line-height: 300rpx;

		.iconfont {
			font-size: 50rpx;
		}
	}

	.boutique {
		margin: 0 $uni-index-margin-col;
		// width: 711rpx;
		// height: 300rpx;
		// margin: 0rpx auto 0 auto;
		// padding: 28rpx 0;
	}

	.boutique swiper {
		width: 100%;
		position: relative;
	}

	.boutique image {
		width: 100%;
		border-radius: 10rpx;
	}
	.off{
		padding: 0rpx 20rpx;
		background-color: #fff;
	}
	.off image {
		// border-radius: 0 0 10rpx 10rpx;
	}

	.boutique .wx-swiper-dot {
		width: 7rpx;
		height: 7rpx;
		border-radius: 50%;
	}

	.boutique .wx-swiper-dot-active {
		width: 20rpx;
		border-radius: 5rpx;
	}

	.boutique .wx-swiper-dots.wx-swiper-dots-horizontal {
		margin-bottom: -8rpx;
	}
</style>
