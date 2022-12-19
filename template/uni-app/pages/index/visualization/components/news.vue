<template>
	<view class="new" :style="colorStyle">
		<view class="news default" v-if="isIframe && !itemNew.length">{{$t(`暂无新闻，请上传新闻`)}}</view>
		<view class='news acea-row row-between-wrapper skeleton-rect' v-if="isShow && itemNew.length">
			<view class='pictrue'>
				<image :src='img'></image>
			</view>
			<view class='swiperTxt'>
				<swiper :indicator-dots="indicatorDots" :autoplay="autoplay" interval="2500" :duration="duration"
					vertical="true" circular="true" disable-touch="true">
					<block v-for="(item,index) in itemNew" :key='index'>
						<swiper-item catchtouchmove='catchTouchMove'>
							<view class='acea-row row-between-wrapper' hover-class='none'
								@click="gopage(item.chiild[1].val)">
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
		<view class='news acea-row row-between-wrapper' v-if="!isShow && isIframe && itemNew.length">
			<view class='pictrue'>
				<image :src='img'></image>
			</view>
			<view class='swiperTxt'>
				<swiper :indicator-dots="indicatorDots" :autoplay="autoplay" interval="2500" :duration="duration"
					vertical="true" circular="true">
					<block v-for="(item,index) in itemNew" :key='index'>
						<swiper-item catchtouchmove='catchTouchMove'>
							<view class='acea-row row-between-wrapper' hover-class='none'
								@click="gopage(item.chiild[1].val)">
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
	import colors from '@/mixins/color.js';
	import {
		goPage
	} from '@/libs/order.js'
	export default {
		name: 'news',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
		},
		mixins:[colors],
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if (nVal) {
						this.img = nVal.imgUrl.url;
						this.itemNew = nVal.newList.list;
						this.isShow = nVal.isShow.val;
					}
				}
			}
		},
		data() {
			return {
				indicatorDots: false,
				autoplay: true,
				duration: 500,
				img: '',
				itemNew: [],
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
			gopage(url) {
				goPage().then(res => {
					if (url.indexOf("http") != -1) {
						// #ifdef H5
						location.href = url
						// #endif
					} else {
						if (['/pages/goods_cate/goods_cate', '/pages/order_addcart/order_addcart',
								'/pages/user/index'
							].indexOf(url) ==
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
	.new{
		background-color: #fff;
		padding: 20rpx 0;
	}
	.news {
		height: 60rpx;
		padding: 0 26rpx;
		// box-shadow: 0 10rpx 30rpx #f5f5f5;
		background-color: var(--view-op-point-four);
		margin: 0 $uni-index-margin-col;
		border-radius: 60rpx;
		// box-shadow: $uni-index-box-shadow;

		&.default {
			text-align: center;
			line-height: 77rpx;
		}
	}

	.news .pictrue {
		width: 116rpx;
		height: 28rpx;
		line-height: 28rpx;
		border-right: 1rpx solid #ddd;
		padding-right: 23rpx;
		box-sizing: content-box;
	}

	.news .pictrue image {
		width: 100%;
		height: 100%;
	}

	.news .swiperTxt {
		width: 470rpx;
		height: 100%;
		line-height: 60rpx;
		overflow: hidden;
	}

	.news .swiperTxt .text {
		line-height: 28rpx;
		margin-bottom: 4rpx;
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
		width: 400rpx;
		font-size: 24rpx;
		line-height: 28rpx;
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
