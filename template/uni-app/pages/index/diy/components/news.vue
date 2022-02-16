<template>
	<view v-show="!isSortType">
		<view class='news acea-row row-middle' :class="{pageOn:bgStyle===1}" v-if="itemNew.length" :style="'margin:0 '+prConfig*2+'rpx;margin-top:'+mbConfig*2+'rpx;color:'+txtColor+';background-color:'+bgColor+';'">
			<!-- ../../../static/images/news.png -->
			<view class='pictrue skeleton-rect'>
				<image :src='logoConfig'></image>
			</view>
			<view class='swiperTxt skeleton-rect'>
				<swiper :indicator-dots="indicatorDots" :autoplay="autoplay" interval="2500" :duration="duration"
					vertical="true" circular="true">
					<block v-for="(item,index) in itemNew" :key='index'>
						<swiper-item catchtouchmove='catchTouchMove'>
							<navigator :url='item.chiild[1].val' class='acea-row row-between-wrapper'
								hover-class='none'>
								<view class='text acea-row row-between-wrapper'>
									<view class='newsTitle line1'
										:style="'text-align:'+ (txtStyle==1?'center':txtStyle==2?'right':'left') +';color:'+txtColor+';'">
										{{item.chiild[0].val}}</view>
								</view>
								<view class='iconfont icon-xiangyou'></view>
							</navigator>
						</swiper-item>
					</block>
				</swiper>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: 'news',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isSortType: {
				type: String | Number,
				default: 0
			}
		},
		data() {
			return {
				indicatorDots: false,
				autoplay: true,
				duration: 500,
				itemNew: this.dataConfig.listConfig.list,
				logoConfig: this.dataConfig.logoConfig.url,
				mbConfig: this.dataConfig.mbConfig.val,
				txtStyle: this.dataConfig.txtStyle.type,
				txtColor: this.dataConfig.txtColor.color[0].item,
				bgColor: this.dataConfig.bgColor.color[0].item,
				bgStyle: this.dataConfig.bgStyle.type,
				prConfig: this.dataConfig.prConfig.val
			};
		},
		created() {},
		mounted() {},
		methods: {}
	}
</script>

<style lang="scss">
	.pageOn{
	    border-radius: 12rpx!important;
	}
	.news {
		display: flex;
		align-items: center;
		flex-wrap: nowrap;
		height: 77rpx;
		border-top: 1rpx solid #f4f4f4;
		padding: 0 20rpx;
		box-shadow: 0 10rpx 30rpx #f5f5f5;
	}

	.news .pictrue {
		width: 130rpx;
		height: 36rpx;
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
		margin-left: 22rpx;
	}

	.news .swiperTxt .text {
		width: 89%;
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
		width: 100%;
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
