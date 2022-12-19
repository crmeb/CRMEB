<template>
	<!-- #ifdef H5  -->
	<view class="header"
		:style="'background: linear-gradient(90deg, '+ bgColor[0].item +' 50%, '+ bgColor[1].item +' 100%);margin-top:'+ mbConfig*2 +'rpx;'">
		<view class="serch-wrapper acea-row row-middle" :style="'padding-left:'+ prConfig*2 +'rpx;'">
			<view class="logo skeleton-rect" v-if="logoConfig">
				<image :src="logoConfig" mode="heightFix"></image>
			</view>
			<navigator url="/pages/goods/goods_search/index" class="input acea-row row-middle skeleton-rect"
				:class="[boxStyle?'':'fillet',logoConfig?'':'on',txtStyle?'row-center':'']" hover-class="none"><text
					class="iconfont icon-sousuo"></text>
				{{$t(`搜索商品`)}}</navigator>
		</view>
	</view>
	<!-- #endif -->
	<!-- #ifdef MP || APP-PLUS -->
	<view v-if="special" class="header"
		:style="'background: linear-gradient(90deg, '+ bgColor[0].item +' 50%, '+ bgColor[1].item +' 100%);margin-top:'+ mbConfig*2 +'rpx;'">
		<view class="serch-wrapper acea-row row-middle" :style="'padding-left:'+ prConfig*2 +'rpx;'">
			<view class="logo skeleton-rect" v-if="logoConfig">
				<image :src="logoConfig" mode="heightFix"></image>
			</view>
			<navigator url="/pages/goods/goods_search/index" class="input acea-row row-middle skeleton-rect"
				:class="[boxStyle?'':'fillet',logoConfig?'':'on',txtStyle?'row-center':'']" hover-class="none"><text
					class="iconfont icon-sousuo"></text>
				{{$t(`搜索商品名称`)}}</navigator>
		</view>
	</view>
	<view v-else>
		<view class="mp-header"
			:style="'background: linear-gradient(90deg, '+ bgColor[0].item +' 50%, '+ bgColor[1].item +' 100%);margin-top:'+ mbConfig*2 +'rpx;'">
			<view class="sys-head" :style="{ height: statusBarHeight }"></view>
			<view class="serch-box" style="height: 43px;">
				<view class="serch-wrapper acea-row row-middle" :style="'padding-left:'+ prConfig*2 +'rpx;'">
					<view class="logo skeleton-rect" v-if="logoConfig">
						<image :src="logoConfig" mode="heightFix"></image>
					</view>
					<navigator url="/pages/goods/goods_search/index" class="input acea-row row-middle skeleton-rect"
						:class="[boxStyle?'':'fillet',logoConfig?'':'on',txtStyle?'row-center':'']" hover-class="none">
						<text class="iconfont icon-sousuo"></text>
						{{$t(`搜索商品名称`)}}
					</navigator>
				</view>
			</view>
		</view>
		<view :style="'height:'+marTop+'px;'"></view>
	</view>
	<!-- #endif -->
</template>

<script>
	let statusBarHeight = uni.getSystemInfoSync().statusBarHeight + 'px';
	export default {
		name: 'headerSerch',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			special: {
				type: Number,
				default: 0
			}
		},
		data() {
			return {
				statusBarHeight: statusBarHeight,
				marTop: 0,
				bgColor: this.dataConfig.bgColor.color,
				boxStyle: this.dataConfig.boxStyle.type,
				logoConfig: this.dataConfig.logoConfig.url,
				mbConfig: this.dataConfig.mbConfig.val,
				txtStyle: this.dataConfig.txtStyle.type,
				hotWords: this.dataConfig.hotWords.list,
				prConfig: this.dataConfig.prConfig.val
			};
		},
		mounted() {
			let that = this;
			uni.setStorageSync('hotList', that.hotWords);
			that.$store.commit('hotWords/setHotWord', that.hotWords);
			// #ifdef MP || APP-PLUS
			setTimeout(() => {
				// 获取小程序头部高度
				let info = uni.createSelectorQuery().in(this).select(".mp-header");
				info.boundingClientRect(function(data) {
					that.marTop = data.height
				}).exec()
			}, 100)
			// #endif
		},
		methods: {

		}
	}
</script>

<style lang="scss">
	.header {
		width: 100%;
		height: 100rpx;
		background: linear-gradient(90deg, $bg-star 50%, $bg-end 100%);

		.serch-wrapper {
			padding: 20rpx 20rpx 0 0;

			.logo {
				height: 60rpx;
				margin-right: 20rpx;
				width: 154rpx;
				text-align: center;
				image {
					width: 100%;
					height: 100%;
				}
			}

			.input {
				flex: 1;
				height: 58rpx;
				padding: 0 0 0 30rpx;
				background: rgba(247, 247, 247, 1);
				border: 1px solid rgba(241, 241, 241, 1);
				color: #BBBBBB;
				font-size: 28rpx;

				.iconfont {
					margin-right: 20rpx;
				}

				// 没有logo，直接搜索框
				&.on {
					width: 100%;
				}

				// 设置圆角
				&.fillet {
					border-radius: 29rpx;
				}

				// 文本框文字居中
				&.row-center {
					padding: 0;
				}
			}
		}
	}
	/* #ifdef MP || APP-PLUS */
	.mp-header {
		z-index: 30;
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		background: linear-gradient(90deg, $bg-star 50%, $bg-end 100%);

		.serch-wrapper {
			height: 100%;
			/* #ifdef MP */
			padding: 0 220rpx 0 53rpx;
			/* #endif */
			/* #ifdef APP-PLUS */
			padding: 0 50rpx 0 40rpx;
      /* #endif */
			.logo {
				height: 60rpx;
				margin-right: 30rpx;
				image {
					width: 100%;
					height: 100%;
				}
			}

			.input {
				flex: 1;
				height: 50rpx;
				padding: 0 0 0 30rpx;
				background: rgba(247, 247, 247, 1);
				border: 1px solid rgba(241, 241, 241, 1);
				color: #BBBBBB;
				font-size: 28rpx;

				.iconfont {
					margin-right: 20rpx;
				}

				// 没有logo，直接搜索框
				&.on {
					/* #ifdef MP */
					width: 70%;
					/* #endif */
					/* #ifdef APP-PLUS */
					width: 100%;
					/* #endif */
				}

				// 设置圆角
				&.fillet {
					border-radius: 29rpx;
				}

				// 文本框文字居中
				&.row-center {
					padding: 0;
				}
			}
		}
	}

	/* #endif */
</style>
