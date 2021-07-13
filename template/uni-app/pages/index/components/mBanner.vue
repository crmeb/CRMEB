<template>
	<view class="swiperBg">
		<view class='boutique' v-if="isShow && bastBanner.length && !isIframe">
			<swiper autoplay="true" indicator-dots="true" :circular="circular" :interval="interval" :duration="duration"
			 indicator-color="rgba(252,65,65,0.3)" indicator-active-color="#fc4141">
				<block v-for="(item,index) in bastBanner">
					<swiper-item :key='index'>
						<view style='width:100%;height:100%;' hover-class='none' @click="goDetail(item)">
							<image :src="item.img" class="slide-image" />
						</view>
					</swiper-item>
				</block>
			</swiper>
		</view>
		<view class='boutique' v-if="bastBanner.length && isIframe">
			<swiper autoplay="true" indicator-dots="true" :circular="circular" :interval="interval" :duration="duration"
			 indicator-color="rgba(252,65,65,0.3)" indicator-active-color="#fc4141">
				<block v-for="(item,index) in bastBanner">
					<swiper-item :key='index'>
						<view style='width:100%;height:100%;' hover-class='none' @click="goDetail(item)">
							<image :src="item.img" class="slide-image" />
						</view>
					</swiper-item>
				</block>
			</swiper>
		</view>
		<block v-if="isIframe && !bastBanner.length">
			<view class="empty-img">暂无图片，请上传图片</view>
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
				isShow: true
			};
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if(nVal){
						this.bastBanner = nVal.imgList.list;
						this.isShow = nVal.isShow.val;
					}
				}
			}
		},
		created() {
			this.isIframe = app.globalData.isIframe
		},
		mounted() {
		},
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
					console.log('hhhhhh',url);
					let urls = url.info[1].value
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
				})
			}
		}
	}
</script>

<style lang="scss">
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
	.boutique {
		width: 690rpx;
		height: 300rpx;
		margin: 28rpx auto 0 auto;
	}
	
	.boutique swiper {
		width: 100%;
		height: 100%;
		position: relative;
	}
	
	.boutique image {
		width: 100%;
		height: 260rpx;
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
