<template>
	<view v-show="!isSortType">
		<view class="swipers" :class="[docConfig?'square':'circular',imgConfig?'':'fillet']" v-if="imgUrls.length && tabConfig" :style="'padding:0 '+lrConfig+'rpx;margin-top:' + mbConfig +'rpx;'">
			<swiper :style="'height:'+(imageH+10)+'rpx;'" indicator-dots="true" :autoplay="true" :circular="circular" :interval="interval" :duration="duration"
			 indicator-color="#E4E4E4" indicator-active-color="#E93323" :previous-margin="itemEdge+'rpx'" :next-margin="itemEdge+'rpx'" :current="swiperCur"
			 @change="swiperChange">
				<block v-for="(item,index) in imgUrls" :key="index">
					<swiper-item :class="{active:index == swiperCur}">
						<navigator :url='item.info[1].title' class='slide-navigator acea-row row-between-wrapper' hover-class='none'>
							<image :src="item.img" class="slide-image" mode="widthFix"></image>
						</navigator>
					</swiper-item>
				</block>
			</swiper>
		</view>
		<view :style="'margin-top:' + mbConfig +'rpx;'" v-if="!tabConfig">
			<navigator :url="item.info[1].title" hover-class="none" class="advert" :class="imgConfig?'':'fillet'" :style="'padding:0 '+lrConfig+'rpx;'" v-for="(item,index) in imgUrls" :key="index">
				<image :src="item.img"></image>
			</navigator>
		</view>
	</view>
</template>

<script>
	export default {
		name: 'swiper',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isSortType:{
				type: String | Number,
				default:0
			}
		},
		data() {
			return {
				indicatorDots: false,
				circular: true,
				autoplay: true,
				interval: 2500,
				duration: 500,
				swiperCur: 0,
				imgUrls: [],
				docConfig: this.dataConfig.docConfig.type,
				imgConfig: this.dataConfig.imgConfig.type,
				itemEdge: this.dataConfig.itemEdge.val,
				lrConfig: this.dataConfig.lrConfig.val,
				mbConfig: this.dataConfig.mbConfig.val,
				tabConfig: this.dataConfig.tabConfig.tabVal,
				imageH: 0
			};
		},
		watch:{
			imageH(nVal,oVal){
				this.imageH = nVal
			}
		},
		created() {
			this.imgUrls = this.dataConfig.swiperConfig.list
		},
		mounted() {
			let that = this;
			uni.getImageInfo({
				src: that.setDomain(that.imgUrls[0].img),
				success: function(res) {
					that.$set(that, 'imageH', res.height);
				}
			})
		},
		methods: {
			// swiper
			swiperChange(e) {
				this.swiperCur = e.detail.current
			},
			//替换安全域名
			setDomain: function(url) {
				url = url ? url.toString() : '';
				//本地调试打开,生产请注销
				if (url.indexOf("https://") > -1) return url;
				else return url.replace('http://', 'https://');
			},
		}
	}
</script>

<style lang="scss">
	.advert{
		box-sizing: border-box;
		image{
			width: 100%;
			box-sizing: border-box;
			display: block;
		}
	}
	.swipers {
		position: relative;
		width: 100%;
		margin: 0 auto;
		border-radius: 10rpx;
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
		}
		image{
			transform: scale(.93);
			transition: all .6s ease;
		}
		swiper-item.active {
			image {
				transform: scale(1);
			}
		}
		// 圆形指示点
		&.circular{
			/deep/.uni-swiper-dot{
				width: 10rpx!important;
				height: 10rpx!important;
				background: rgba(0,0,0,.4)!important
			}
			/deep/.uni-swiper-dot-active{
				background: #fff!important
			}
		}
		// 方形指示点
		&.square{
			/deep/.uni-swiper-dot{
				width: 20rpx!important;
				height: 5rpx!important;
				border-radius: 3rpx;
				background: rgba(0,0,0,.4)!important
			}
			/deep/.uni-swiper-dot-active{
				background: #fff!important
			}
		}
	}
</style>
