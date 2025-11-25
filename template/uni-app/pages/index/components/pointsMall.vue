<template>
	<view :style="[pointsMallWrapStyle]" v-show="!isSortType">
		<view class="pointsMall" :style="[pointsMallStyle,goodsWrapperStyle]">
			<view class="acea-row row-middle row-between header" :style="[headerStyle]">
				<view v-if="dataConfig.titleConfig.tabVal" class="title" :style="[titleStyle]">{{ dataConfig.titleTxtConfig.value }}</view>
				<easy-loadimage v-else mode="widthFix" :image-src="titleImage" width="176rpx" height="32rpx"></easy-loadimage>
				<view class="more" :style="[buttonStyle]" @click="goPointsMall">
					<text>{{ $t(`更多`) }}</text>
					<text class="iconfont icon-ic_rightarrow" :style="[buttonStyle]"></text>
				</view>
			</view>
			<view>
				<view v-if="dataConfig.goodStyleConfig.tabVal == 0" class="goods-wrapper0">
					<scroll-view class="scroll-view" scroll-x="true">
						<view class="item" v-for="item in goodsList" :key="item.id" @click="goGoodsDetails(item.id)">
							<easy-loadimage mode="widthFix" :image-src="item.image" width="224rpx" height="224rpx" :borderRadius="goodsImage"></easy-loadimage>
							<view class="price-box acea-row row-middle" :style="[priceBoxStyle]">
								<view class="point">{{ item.price }}</view>
								<view class="">{{ $t(`积分`) }}</view>
							</view>
						</view>
					</scroll-view>
				</view>
				<view v-else-if="dataConfig.goodStyleConfig.tabVal == 1" class="goods-wrapper">
					<view class="acea-row goods-list">
						<view class="item" v-for="item in goodsList" :key="item.id" @click="goGoodsDetails(item.id)">
							<easy-loadimage :image-src="item.image" width="100%" height="212rpx" :borderRadius="goodsImage"></easy-loadimage>
							<view class="price-box acea-row row-middle" :style="[priceBoxStyle]">
								<view class="acea-row row-middle">
									<image class="image" :src="`${imgHost}/statics/images/newVip3.png`" mode="aspectFit"></image>
									<text class="num" :style="[numStyle]">{{ item.price }}</text>
								</view>
							</view>
							<view class="title line1" :style="[goodsTitleStyle]">{{ item.title }}</view>
						</view>
					</view>
				</view>
				<view v-else-if="dataConfig.goodStyleConfig.tabVal == 2" class="goods-wrapper2">
					<view class="acea-row goods-list">
						<view class="item" v-for="item in goodsList" :key="item.id" @click="goGoodsDetails(item.id)">
							<easy-loadimage :image-src="item.image" width="100%" height="324rpx" :borderRadius="goodsImage"></easy-loadimage>
							<view class="title line2" :style="[goodsTitleStyle]">{{ item.title }}</view>
							<view class="price-box acea-row row-middle" :style="[priceBoxStyle]">
								<view class="">
									<image class="image" :src="`${imgHost}/statics/images/newVip3.png`" mode="aspectFit"></image>
									<text class="num point" :style="[numStyle]">{{ item.price }}</text>
								</view>
							</view>
						</view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	import {
		getStoreIntegralList
	} from '@/api/activity.js';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	export default {
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
				imgHost: HTTP_REQUEST_URL,
				goodsList: []
			}
		},
		computed: {
			headerStyle() {
				let background = `linear-gradient(90deg, ${this.dataConfig.headerBgColor.color[0].item} 0%, ${this.dataConfig.headerBgColor.color[1].item} 100%)`;
				if (this.dataConfig.styleConfig.tabVal) {
					background = `url(${this.dataConfig.imgBgConfig.url})`;
				}
				return {
					'background-image': background,
				};
			},
			goodsWrapperStyle() {
				let color = this.dataConfig.moduleColor2.color;
				if (this.dataConfig.styleConfig.tabVal) {
					color = this.dataConfig.moduleColor.color;
				}
				return {
					'background-image': `linear-gradient(270deg, ${color[0].item} 0%, ${color[1].item} 100%)`,
				};
			},
			pointsMallStyle() {
				let borderRadius = `${this.dataConfig.fillet.val * 2}rpx`;
				if (this.dataConfig.fillet.type) {
					borderRadius =
						`${this.dataConfig.fillet.valList[0].val * 2}rpx ${this.dataConfig.fillet.valList[1].val * 2}rpx ${this.dataConfig.fillet.valList[3].val * 2}rpx ${this.dataConfig.fillet.valList[2].val * 2}rpx`;
				}
				return {
					'border-radius': borderRadius,
				};
			},
			buttonStyle() {
				let color = this.dataConfig.headerBntColor2.color[0].item;
				if (this.dataConfig.styleConfig.tabVal) {
					color = this.dataConfig.headerBntColor.color[0].item;
				}
				return {
					'font-size': this.dataConfig.bntNumber.val*2+'rpx',
					'color': color,
				};
			},
			titleImage() {
				let url = this.dataConfig.imgConfig2.url;
				if (this.dataConfig.styleConfig.tabVal) {
					url = this.dataConfig.imgConfig.url;
				}
				return url;
			},
			titleStyle(){
				let titleText = this.dataConfig.titleText
				return {
					fontStyle: !titleText.tabVal?'normal':titleText.tabList[titleText.tabVal].style,
					fontWeight: !titleText.tabVal?'bold':'normal',
					color: this.dataConfig.titleColor.color[0].item,
					fontSize: this.dataConfig.titleNumber.val*2+'rpx'
				}
			},
			goodsImage() {
				let borderRadius = `${this.dataConfig.filletImg.val * 2}rpx`;
				if (this.dataConfig.filletImg.type) {
					borderRadius =
						`${this.dataConfig.filletImg.valList[0].val * 2}rpx ${this.dataConfig.filletImg.valList[1].val * 2}rpx ${this.dataConfig.filletImg.valList[3].val * 2}rpx ${this.dataConfig.filletImg.valList[2].val * 2}rpx`;
				}
				return borderRadius;
			},
			priceBoxStyle() {
				let styleConfig = this.dataConfig.styleConfig.tabVal;
				let goodStyleConfig = this.dataConfig.goodStyleConfig.tabVal;
				let goodsUnitPriceColor2  = this.dataConfig.goodsUnitPriceColor2.color[0].item;
				let goodsUnitPriceColor = this.dataConfig.goodsUnitPriceColor.color[0].item;
				let styleObject = {};
				if (this.dataConfig.toneConfig.tabVal) {
					if (goodStyleConfig) {
						styleObject['color'] = !styleConfig?goodsUnitPriceColor2:goodsUnitPriceColor;
					} else {
						styleObject['background'] = `linear-gradient(90deg, ${this.dataConfig.priceBgColor.color[0].item} 0%, ${this.dataConfig.priceBgColor.color[1].item} 100%)`;
						styleObject['color'] = this.dataConfig.goodsPriceColor.color[0].item;
					}
				}else{
					if(goodStyleConfig && !styleConfig){
						styleObject['color'] = '#282828';
					}else{
						styleObject['color'] = '#fff'
					}
				}
				return styleObject;
			},
			goodsTitleStyle() {
				let styleConfig = this.dataConfig.styleConfig.tabVal;
				let goodStyleConfig = this.dataConfig.goodStyleConfig.tabVal;
				let color1 = this.dataConfig.goodsNameColor.color[0].item;
				let color2 = this.dataConfig.goodsNameColor2.color[0].item;
				let color = color2;
				if(!styleConfig){
					color = !goodStyleConfig?color2:color1
				}
				return {
					'color': color
				};
			},
			// 数字样式
			numStyle() {
				let styleConfig = this.dataConfig.styleConfig.tabVal;
				let goodStyleConfig = this.dataConfig.goodStyleConfig.tabVal;
				let color1 = this.dataConfig.goodsPriceColor.color[0].item;
				let color2 = this.dataConfig.goodsPriceColor2.color[0].item;
				let styleObject = {};
				let color = color1;
				if(!styleConfig){
					color = !goodStyleConfig?color1:color2
				}
				if (this.dataConfig.toneConfig.tabVal) {
					styleObject['color'] = color;
				}else{
					if(!goodStyleConfig || styleConfig){
						styleObject['color'] = '#fff'
					}else{
						styleObject['color'] = 'var(--view-theme)'
					}
				}
				return styleObject;
			},
			pointsMallWrapStyle() {
				return {
					'padding': `${this.dataConfig.topConfig.val*2}rpx ${this.dataConfig.prConfig.val*2}rpx ${this.dataConfig.bottomConfig.val*2}rpx`,
					'margin-top': `${this.dataConfig.mbConfig.val*2}rpx`,
					background: this.dataConfig.bottomBgColor.color[0].item
				};
			},
		},
		mounted() {
			this.getStoreIntegralList();
		},
		methods: {
			getStoreIntegralList() {
				let limit = this.$config.LIMIT;
				getStoreIntegralList({
					page: 1,
					limit: this.dataConfig.numberConfig.val >= limit ? limit : this.dataConfig.numberConfig.val
				}).then(res => {
					this.goodsList = res.data;
				});
			},
			goPointsMall() {
				uni.navigateTo({
					url: `/pages/points_mall/index`
				});
			},
			goGoodsDetails(id) {
				uni.navigateTo({
					url: `/pages/points_mall/integral_goods_details?id=${id}`
				});
			},
		},
	}
</script>

<style lang="scss" scoped>
	.pointsMall {
		background: #FFFFFF;
		overflow: hidden;
	}

	.header {
		height: 96rpx;
		padding: 0 24rpx;
		background-repeat: no-repeat;
		background-size: 100% 100%;

		.title {
			font-weight: 500;
			font-size: 32rpx;
			color: #333333;
		}

		.more {
			font-size: 24rpx;
			color: #999999;

			.iconfont {
				font-size: 24rpx;
			}
		}
	}

	.goods-wrapper {
		.goods-list {
			padding: 20rpx 10rpx 12rpx;

			.item {
				flex: 0 0 33.33%;
				min-width: 0;
				padding: 0 10rpx 20rpx;
				margin: 0;
			}

			.price-box {
				width: auto;
				height: auto;
				margin: 16rpx 0 0;
				background: none;
				font-family: SemiBold;
				font-size: 24rpx;
				line-height: 40rpx;
				color: #666666;
			}

			.image {
				width: 28rpx;
				height: 28rpx;
				margin-right: 8rpx;
			}

			.num {
				color: var(--view-theme);
			}

			.title {
				margin-top: 4rpx;
				font-size: 26rpx;
				line-height: 36rpx;
				color: #282828;
			}
		}
	}

	.goods-wrapper2 {
		padding: 0 10rpx 8rpx;

		.item {
			flex: 0 0 50%;
			min-width: 0;
			padding: 0 10rpx 20rpx;
			margin: 0;
		}

		.title {
			margin-top: 16rpx;
			font-size: 28rpx;
			line-height: 28rpx;
			color: #282828;
		}

		.price-box {
			width: auto;
			height: auto;
			margin: 16rpx 0 0;
			background: none;
			font-family: SemiBold;
			font-size: 24rpx;
			line-height: 40rpx;
			color: #666666;
		}

		.image {
			width: 28rpx;
			height: 28rpx;
			margin-right: 4rpx;
		}

		.num {
			color: var(--view-theme);
		}

		.point {
			font-weight: 600;
			font-size: 40rpx;
		}
	}

	.goods-wrapper0 {
		padding: 0 0 32rpx 20rpx;
	}

	.scroll-view {
		box-sizing: border-box;
		white-space: nowrap;
		padding: 20rpx;
		border-radius: 16rpx 0 0 16rpx;
		background: #FFFFFF;

		.item {
			display: inline-block;
			width: 224rpx;
			margin: 0 20rpx 0 0;
			
			&:last-child {
				margin: 0;
			}
		}

		.price-box {
			display: inline-flex;
			width: auto;
			height: 36rpx;
			padding: 0 12rpx;
			border-radius: 2rpx 20rpx 20rpx 20rpx;
			margin: 16rpx 0 0;
			background: linear-gradient(90deg, var(--view-theme) 0%, var(--view-gradient) 100%);
			font-family: SemiBold;
			font-size: 22rpx;
			color: #FFFFFF;
		}

		.point {
			font-weight: 600;
			font-size: 26rpx;
		}
	}
</style>