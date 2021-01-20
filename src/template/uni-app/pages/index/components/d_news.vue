<template>
	<view>
		<view class="news default" :class="{borderShow:isBorader}" v-if="isIframe && !itemNew.length">暂无新闻，请上传新闻</view>
		<view class='news acea-row row-between-wrapper' :class="{borderShow:isBorader}" v-if="isShow && itemNew.length">
			<view class='pictrue'>
				<image :src='img'></image>
			</view>
			<view class='swiperTxt'>
				<swiper :indicator-dots="indicatorDots" :autoplay="autoplay" interval="2500" :duration="duration" vertical="true"
				 circular="true">
					<block v-for="(item,index) in itemNew" :key='index'>
						<swiper-item catchtouchmove='catchTouchMove'>
							<view class='acea-row row-between-wrapper' hover-class='none' @click="gopage(item.chiild[1].val)">
								<view class='text acea-row row-between-wrapper'>
									<view class='newsTitle line1'>{{item.chiild[0].val}}</view>
								</view>
								<view class='iconfont icon-xiangyou'></view>
							</view>
						</swiper-item>
					</block>
				</swiper>
			</view>
		</view>
		<view class='news acea-row row-between-wrapper' :class="{borderShow:isBorader}" v-if="!isShow && isIframe && itemNew.length">
			<view class='pictrue'>
				<image :src='img'></image>
			</view>
			<view class='swiperTxt'>
				<swiper :indicator-dots="indicatorDots" :autoplay="autoplay" interval="2500" :duration="duration" vertical="true"
				 circular="true">
					<block v-for="(item,index) in itemNew" :key='index'>
						<swiper-item catchtouchmove='catchTouchMove'>
							<view class='acea-row row-between-wrapper' hover-class='none' @click="gopage(item.chiild[1].val)">
								<view class='text acea-row row-between-wrapper'>
									<view class='newsTitle line1'>{{item.chiild[0].val}}</view>
								</view>
								<view class='iconfont icon-xiangyou'></view>
							</view>
						</swiper-item>
					</block>
				</swiper>
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
		name: 'd_news',
		props: {
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
				indicatorDots: false,
				autoplay: true,
				duration: 500,
				img: this.dataConfig.imgUrl.url,
				itemNew: [],
				isBorader: false,
				name: this.$options.name,
				isIframe: false,
				isShow: true
				// logoConfig: this.dataConfig.logoConfig.url,
				// mbConfig: this.dataConfig.mbConfig.val,
				// txtStyle: this.dataConfig.txtStyle.type
			};
		},
		created() {
			this.isIframe = app.globalData.isIframe;
			if (this.dataConfig.isShow) {
				this.isShow = this.dataConfig.isShow.val;
			}
			if (this.dataConfig.newList) {
				this.itemNew = this.dataConfig.newList.list;
			}
		},
		mounted() {},
		methods: {
			gopage(url) {
				goPage().then(res => {
					if (url.indexOf("http") != -1) {
						// #ifdef H5
						location.href = url
						// #endif
					} else {
						if (['/pages/goods_cate/goods_cate', '/pages/order_addcart/order_addcart', '/pages/user/index'].indexOf(url) ==
							-1) {
							uni.navigateTo({
								url: url
							})
						} else {
							uni.navigateTo({
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
	.news {
		height: 77rpx;
		border-top: 1rpx solid #f4f4f4;
		padding: 0 30rpx;
		box-shadow: 0 10rpx 30rpx #f5f5f5;

		&.default {
			text-align: center;
			line-height: 77rpx;
		}
	}

	.news .pictrue {
		width: 124rpx;
		height: 28rpx;
		border-right: 1rpx solid #ddd;
		padding-right: 23rpx;
		box-sizing: content-box;
	}

	.news .pictrue image {
		width: 100%;
		height: 100%;
	}

	.news .swiperTxt {
		width: 523rpx;
		height: 100%;
		line-height: 77rpx;
		overflow: hidden;
	}

	.news .swiperTxt .text {
		width: 480rpx;
	}

	.news .swiperTxt .text .label {
		font-size: 20rpx;
		color: #ff4c48;
		width: 64rpx;
		height: 30rpx;
		border-radius: 40rpx;
		text-align: center;
		line-height: 28rpx;
		border: 2rpx solid #ff4947;
	}

	.news .swiperTxt .text .newsTitle {
		width: 397rpx;
		font-size: 24rpx;
		color: #666;
	}

	.news .swiperTxt .iconfont {
		font-size: 28rpx;
		color: #888;
	}

	.news .swiperTxt swiper {
		height: 100%;
	}
</style>
