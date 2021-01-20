<template>
	<view class="swiperBg" :class="{borderShow:isBorader}">
		<block>
			<view class="swiper square" v-if="isShow && imgUrls.length">
				<swiper indicator-dots="true" :autoplay="true" :circular="circular" :interval="interval" :duration="duration"
				 indicator-color="rgba(255,255,255,0.6)" indicator-active-color="#fff">
					<block v-for="(item,index) in imgUrls" :key="index">
						<swiper-item>
							<view @click="goDetail(item)" class='slide-navigator acea-row row-between-wrapper'>
								<image :src="item.img" class="slide-image aa"></image>
							</view>
						</swiper-item>
					</block>
				</swiper>
			</view>
			<view class="swiper square" v-if="!isShow && isIframe && imgUrls.length">
				<swiper indicator-dots="true" :autoplay="true" :circular="circular" :interval="interval" :duration="duration"
				 indicator-color="rgba(255,255,255,0.6)" indicator-active-color="#fff">
					<block v-for="(item,index) in imgUrls" :key="index">
						<swiper-item>
							<view @click="goDetail(item)" class='slide-navigator acea-row row-between-wrapper'>
								<image :src="item.img" class="slide-image aa"></image>
							</view>
						</swiper-item>
					</block>
				</swiper>
			</view>
		</block>
		<block v-if="isIframe && imgUrls.length==0">
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
		name: 'i_m_banner',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			activeName: {
				type: String,
				default: ''
			},
		},
		data() {
			return {
				indicatorDots: false,
				circular: true,
				autoplay: true,
				interval: 3000,
				duration: 500,
				imgUrls: [], //图片轮播数据
				isBorader: false,
				name: this.$options.name,
				isIframe: false,
				isShow: true
			};
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
		created() {
			this.isIframe = app.globalData.isIframe
			if (this.dataConfig.imgList) {
				this.imgUrls = this.dataConfig.imgList.list
			}
			if (this.dataConfig.isShow) {
				this.isShow = this.dataConfig.isShow.val
			}
			//this.imgUrls = []
		},
		mounted() {
			let that = this;
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
					let urls = url.info[0].value
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
		width: 100%;
		height: 375rpx;
		line-height: 375rpx;
		background: #ccc;
		font-size: 40rpx;
		color: #666;
		text-align: center;
	}

	.swiperBg {
		position: relative;
		margin: 20rpx 0;

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
				height: 190rpx;
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
</style>
