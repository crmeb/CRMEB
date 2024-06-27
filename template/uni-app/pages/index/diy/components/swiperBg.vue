<template>
	<view class="swiperBg skeleton-rect" :style="'margin-top:' + marginTop*2 +'rpx;'" v-show="!isSortType">
		<block v-if="imgUrls.length">
			<view class="colorBg"
				:style="'background: linear-gradient(90deg, '+ bgColor[0].item +' 50%, '+ bgColor[1].item +' 100%);'"
				v-if="isColor"></view>
			<view class="swiper" :class="[imgConfig?'':'fillet']" :style="'padding: 0 '+ paddinglr +'rpx;'">
				<swiper :style="'height:'+ imageH +'rpx;'" :autoplay="true" :circular="circular" :interval="interval"
					:duration="duration" indicator-color="rgba(255,255,255,0.6)" indicator-active-color="#fff"
					@change='bannerfun'>
					<block v-for="(item,index) in imgUrls" :key="index">
						<swiper-item>
							<view @click="goDetail(item)" class='slide-navigator acea-row row-between-wrapper'>
								<image :src="item.img" mode="aspectFill" class="slide-image aa"
									:style="'height:'+ imageH +'rpx;'">
								</image>
							</view>
						</swiper-item>
					</block>
				</swiper>
				<view v-if="docConfig==0" class="dot acea-row"
					:style="{paddingLeft: paddinglr+20 + 'rpx',paddingRight: paddinglr+20 + 'rpx',justifyContent: (txtStyle==1?'center':txtStyle==2?'flex-end':'flex-start')}">
					<view class="dot-item" :style="active==index?'background:'+ dotColor:''"
						v-for="(item,index) in imgUrls"></view>
				</view>
				<view v-if="docConfig==1" class="dot acea-row"
					:style="{paddingLeft: paddinglr+20 + 'rpx',paddingRight: paddinglr+20 + 'rpx',justifyContent: (txtStyle==1?'center':txtStyle==2?'flex-end':'flex-start')}">
					<view class="dot-item line_dot-item" :style="active==index?'background:'+ dotColor:''"
						v-for="(item,index) in imgUrls"></view>
				</view>
				<view v-if="docConfig==2" class="dot acea-row"
					:style="{paddingLeft: paddinglr+20 + 'rpx',paddingRight: paddinglr+20 + 'rpx',justifyContent: (txtStyle==1?'center':txtStyle==2?'flex-end':'flex-start')}">
					<view class="instruct">{{current}}/{{imgUrls.length}}</view>
				</view>
			</view>
		</block>
	</view>
</template>

<script>
	export default {
		name: 'swiperBg',
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
				circular: true,
				autoplay: true,
				interval: 3000,
				duration: 500,
				imgUrls: [], //图片轮播数据
				bgColor: this.dataConfig.bgColor.color, //轮播背景颜色
				marginTop: this.dataConfig.mbConfig.val, //组件上边距
				paddinglr: (this.dataConfig.lrConfig.val) * 2, //轮播左右边距
				docConfig: this.dataConfig.docConfig.type, //指示点样式
				imgConfig: this.dataConfig.imgConfig.type, //是否为圆角
				imageH: 280,
				isColor: this.dataConfig.isShow.val,
				txtStyle: this.dataConfig.txtStyle.type,
				dotColor: this.dataConfig.dotColor.color[0].item,
				current: 1, //数字指示器当前
				active: 0 //一般指示器当前
			};
		},
		watch: {
			imageH(nVal, oVal) {
				let self = this
				this.imageH = nVal
			},
		},
		created() {
			this.imgUrls = this.dataConfig.swiperConfig.list
		},
		mounted() {
			if (this.imgUrls.length) {
				let that = this;
				this.$nextTick((e) => {
					uni.getImageInfo({
						src: that.imgUrls[0].img,
						success: (res) => {
							if (res && res.height > 0) {
								// that.$set(that, 'imageH',
								// 	res.height / res
								// 	.width * 750)
								let height = res.height * ((750 - this.paddinglr * 2) / res.width)
								that.$set(that, 'imageH', height);
							} else {
								that.$set(that, 'imageH', 375);
							}
						},
						fail: function(error) {
							that.$set(that, 'imageH', 375);
						}
					})
				})
			}
		},
		methods: {
			bannerfun(e) {
				this.active = e.detail.current;
				this.current = e.detail.current + 1;
			},
			//替换安全域名
			setDomain: function(url) {
				url = url ? url.toString() : '';
				//本地调试打开,生产请注销
				if (url.indexOf("https://") > -1) return url;
				else return url.replace('http://', 'https://');
			},
			goDetail(url) {
				let urls = url.info[1].value
				this.$util.JumpPath(urls);
			}
		}
	}
</script>

<style lang="scss">
	.swiperBg {
		position: relative;
		// #ifdef APP-PLUS
		// padding-top: 100rpx;

		// #endif
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
			overflow: hidden;

			.dot {
				position: absolute;
				left: 0;
				bottom: 20rpx;
				width: 100%;

				.instruct {
					width: 50rpx;
					height: 36rpx;
					line-height: 36rpx;
					background-color: #bfc1c4;
					color: #fff;
					border-radius: 16rpx;
					font-size: 24rpx;
					text-align: center;
				}

				.dot-item {
					width: 10rpx;
					height: 10rpx;
					background: rgba(0, 0, 0, .4);
					border-radius: 50%;
					margin: 0 4px;

					&.line_dot-item {
						width: 20rpx;
						height: 5rpx;
						border-radius: 3rpx;
					}
				}
			}

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
