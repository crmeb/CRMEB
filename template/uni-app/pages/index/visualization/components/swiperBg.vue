<template>
	<view class="swiperBg" :style="{marginTop:mt +'rpx'}">
		<view class="bag" v-if="isIframe || (imgUrls.length && isShow)">
		</view>
		<block v-if="isShow && imgUrls.length">
			<view class="swiper square" v-if="imgUrls.length">
				<swiper class="skeleton-rect" :style="'height:'+ (imageH) +'rpx;'" indicator-dots="true"
					:autoplay="true" :circular="circular" :interval="interval" :duration="duration"
					indicator-color="rgba(255,255,255,0.6)" indicator-active-color="#fff" :current="swiperCur"
					@change="swiperChange">
					<block v-for="(item,index) in imgUrls" :key="index">
						<swiper-item class="" :class="{active:index == swiperCur}">
							<view @click="goDetail(item)" class='slide-navigator acea-row row-between-wrapper'>
								<image :src="item.img" class="slide-image" mode="widthFix"
									:style="'height:'+ (imageH) +'rpx;'">
								</image>
							</view>
						</swiper-item>
					</block>

				</swiper>
			</view>
		</block>
		<block v-if="!isShow && isIframe && imgUrls.length && imageH">
			<view class="swiper square" v-if="imgUrls.length && imageH" :style="'height:'+ (imageH) +'rpx;'">
				<swiper :style="'height:'+ (imageH) +'rpx;'" indicator-dots="true" :autoplay="true" :circular="circular"
					:interval="interval" :duration="duration" indicator-color="rgba(255,255,255,0.6)"
					indicator-active-color="#fff">
					<block v-for="(item,index) in imgUrls" :key="index">
						<swiper-item>
							<view @click="goDetail(item)" class='slide-navigator acea-row row-between-wrapper'>
								<image :src="item.img" class="slide-image" mode="widthFix"
									:style="'height:'+ (imageH) +'rpx;'">
								</image>
							</view>
						</swiper-item>
					</block>
				</swiper>

			</view>
		</block>
		<block v-if="isIframe && (!imgUrls.length || !imageH)">
			<view class="empty-img">{{$t(`暂无图片，请上传图片`)}}</view>
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
					if (nVal) {
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
									this.$set(this, 'imageH', 320);
								}
							},
							fail: (error) => {
								this.$set(this, 'imageH', 320);
							}
						})
					}
				}
			},
			imageH(nVal, oVal) {
				let self = this
			},
		},
		data() {
			return {
				indicatorDots: false,
				circular: true,
				autoplay: true,
				interval: 4000,
				duration: 500,
				imgUrls: [], //图片轮播数据
				name: this.$options.name,
				isIframe: false,
				mt: -55,
				isShow: true,
				imageH: 320,
				swiperCur: 0,
			};
		},
		created() {
			// #ifdef MP || APP-PLUS
			const res = uni.getSystemInfoSync()
			const system = res.platform
			this.statusBarHeight = res.statusBarHeight
			if (system === 'android') {
				this.mt = parseFloat(statusBarHeight) * 2 + 170
			} else {
				this.mt = parseFloat(statusBarHeight) * 2 + 168
			}

			// #endif
			this.isIframe = app.globalData.isIframe;
		},
		mounted() {},
		methods: {
			goDetail(url) {
				goPage().then(res => {
					let urls = url.info[1].value
					this.$util.JumpPath(urls);
				})
			},
			//替换安全域名
			setDomain: function(url) {
				url = url ? url.toString() : '';
				//本地调试打开,生产请注销
				if (url.indexOf("https://") > -1) return url;
				else return url.replace('http://', 'https://');
			},
			swiperChange(e) {
				// this.swiperCur = e.detail.current
				let {
					current,
					source
				} = e.detail
				if (source === 'autoplay' || source === 'touch') {
					//根据官方 source 来进行判断swiper的change事件是通过什么来触发的，autoplay是自动轮播。touch是用户手动滑动。其他的就是未知问题。抖动问题主要由于未知问题引起的，所以做了限制，只有在自动轮播和用户主动触发才去改变current值，达到规避了抖动bug
					this.swiperCur = e.detail.current
				}
			},
		}
	}
</script>

<style lang="scss">
	.swiperBg /deep/ .uni-swiper-slides {
		overflow: hidden;
		border-radius: 10rpx;
	}

	.swiperBg {
		background-color: #fff;
		position: relative;
		margin-top: -20rpx;
		padding-top: 4rpx;

		.bag {
			position: absolute;
			top: 0;
			width: 100%;
			height: 140rpx;
			background: linear-gradient(90deg, var(--view-main-start) 0%, var(--view-main-over) 100%);
			border-bottom-left-radius: 40rpx;
			border-bottom-right-radius: 40rpx;
		}

		/* #ifdef APP-PLUS */
		/* #endif */
		.colorBg {
			position: absolute;
			left: 0;
			top: 0;
			height: 130rpx;
			width: 100%;
		}

		.swiper {
			z-index: 100;
			position: relative;
			min-height: 200rpx;
			padding: 0 $uni-index-margin-col;
			/* #ifdef APP-PLUS */
			// margin: 0rpx auto 0 auto;
			/* #endif */
			overflow: hidden;

			/* #ifdef MP */
			/* #endif */
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
				overflow: hidden;
				border-radius: 10rpx;
			}

			.slide-navigator {}

			image {
				transform: scale(1);
				// transition: all .3s ease;
			}

			swiper-item.active {
				image {
					transform: scale(1);
				}
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
		border-radius: 10rpx;
	}

	.empty-img {
		width: 690rpx;
		height: 300rpx;
		border-radius: 14rpx;
		margin: 26rpx auto 0 auto;
		background-color: #ccc;
		text-align: center;
		line-height: 300rpx;
		position: relative;

		.iconfont {
			font-size: 50rpx;
		}
	}
</style>
