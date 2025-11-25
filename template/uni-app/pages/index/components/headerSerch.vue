<template>
	<view>
		<!-- 搜索框 -->
		<!-- #ifdef H5  -->
		<view class="header" :style="[headerStyle]">
			<view class="serch-wrapper acea-row row-middle" :style="[serchWrapperStyle,txtPosition]">
				<view class="logo skeleton-rect" v-if="styleConfig == 0 && styleTypeConfig == 1 && logoConfig">
					<image :src="logoConfig" mode="heightFix"></image>
				</view>
				<view class="title" :style="[txtStyle]" v-if="titleConfig && (styleConfig==1 || (styleConfig==0 && styleTypeConfig == 0))" @click="goLink">{{ titleConfig }}</view>
				<navigator v-if="styleConfig===0" url="/pages/goods/goods_search/index" class="input acea-row row-middle skeleton-rect" hover-class="none">
					<view class="search acea-row row-middle" :style="[searchStyle]">
						<text class="iconfont icon-ic_search"></text>
						<swiper v-if="hotWords.length" :autoplay="true" :interval="3000" :duration="1000" :vertical="true" :circular="true" class="swiper"
							:style="{ color: dataConfig.hotWordsColor.color[0].item }">
							<swiper-item v-for="(item, index) in hotWords" :key="index">
								{{ item.val }}
							</swiper-item>
						</swiper>
						<text v-else>{{ dataConfig.tipConfig.value }}</text>
					</view>
				</navigator>
			</view>
		</view>
		<!-- #endif -->
		<!-- #ifdef MP || APP-PLUS -->
		<view>
			<view class="mp-header" :class="special?'on':''" :style="[headerStyle]">
				<view class="sys-head" :style="{ height: statusBarHeight + 'px'}" v-if="!special"></view>
				<view class="serch-box" :style="[serchWrapperStyle, {  height: serchHeight + 'px' }, { paddingRight: (!special?serchRight:0) + 'px' }]">
					<view class="serch-wrapper acea-row row-middle" :style="[txtPosition]">
						<view class="logo skeleton-rect" v-if="styleConfig == 0 && styleTypeConfig == 1 && logoConfig">
							<image :src="logoConfig" mode="heightFix"></image>
						</view>
						<view class="title" :style="[txtStyle]" v-if="titleConfig && (styleConfig==1 || (styleConfig==0 && styleTypeConfig == 0))" @click="goLink">{{ titleConfig }}</view>
						<navigator v-if="styleConfig===0" url="/pages/goods/goods_search/index" class="input acea-row row-middle skeleton-rect" hover-class="none">
							<view class="search acea-row row-middle line1" :style="[searchStyle]">
								<text class="iconfont icon-ic_search"></text>
								<swiper v-if="hotWords.length" :autoplay="true" :interval="3000" :duration="1000" :vertical="true" :circular="true" class="swiper"
									:style="{ color: dataConfig.hotWordsColor.color[0].item }">
									<swiper-item v-for="(item, index) in hotWords" :key="index">
										{{ item.val }}
									</swiper-item>
								</swiper>
								<text class="line1 flex-1" v-else>{{ dataConfig.tipConfig.value }}</text>
							</view>
						</navigator>
					</view>
				</view>
			</view>
			<view :style="'height:'+(statusBarHeight+serchHeight)+'px;'" v-if="!special"></view>
		</view>
		<!-- #endif -->
	</view>
	
</template>

<script>
	let statusBarHeight = uni.getSystemInfoSync().statusBarHeight;
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
			},
			belongIndex: {
				type: Number,
				default: 0
			}
		},
		data() {
			return {
				statusBarHeight: statusBarHeight,
				marTop: 63,
				styleConfig: this.dataConfig.styleConfig.tabVal,
				styleTypeConfig:this.dataConfig.styleTypeConfig.tabVal,
				bgColor: this.dataConfig.moduleColor.color,
				titleConfig: this.dataConfig.titleConfig.value,
				txtColor: this.dataConfig.txtColor.color[0].item,
				txtStyleConfig: this.dataConfig.txtStyleConfig.tabList[this.dataConfig.txtStyleConfig.tabVal].style,
				txtSize: this.dataConfig.txtSize.val,
				// fixConfig: this.dataConfig.fixConfig.tabVal,
				logoConfig: this.dataConfig.logoConfig.url,
				txtFixConfig: this.dataConfig.txtFixConfig.tabVal,
				boxStyle: '',
				mbConfig: '',
				hotWords: [],
				prConfig: '',
				tabVal: '',
				radioVal: '',
				textColor: '',
				textStyle: '',
				serchHeight: 43,
				serchRight: ''
			};
		},
		computed: {
			txtStyle() {
				let num = 0
				if(this.styleConfig==0 && this.styleTypeConfig != 2){
					num = 30
				}
				return {
					color:`${this.txtColor}`,
					fontStyle:`${this.txtStyleConfig!='bold'?this.txtStyleConfig:''}`,
					fontWeight:`${this.txtStyleConfig=='bold'?this.txtStyleConfig:''}`,
					fontSize:`${this.txtSize*2}rpx`,
					marginRight:`${num}rpx`
				};
			},
			headerStyle() {
				return {
					padding: `${this.dataConfig.topConfig.val*2}rpx ${this.dataConfig.prConfig.val*2}rpx ${this.dataConfig.bottomConfig.val*2}rpx`,
					background: this.dataConfig.bottomBgColor.color[0].item,
				};
			},
			serchWrapperStyle() {
				let borderRadius = `${this.dataConfig.fillet.val * 2}rpx`;
				if(this.dataConfig.fillet.type){
					borderRadius = `${this.dataConfig.fillet.valList[0].val * 2}rpx ${this.dataConfig.fillet.valList[1].val * 2}rpx ${this.dataConfig.fillet.valList[3].val * 2}rpx ${this.dataConfig.fillet.valList[2].val * 2}rpx`;
				}
				return {
					borderRadius: borderRadius,
					background: `linear-gradient(90deg, ${this.dataConfig.moduleColor.color[0].item} 0%, ${this.dataConfig.moduleColor.color[1].item} 100%)`,
				};
			},
			txtPosition(){
				return {
					justifyContent:this.styleConfig !=0 && this.txtFixConfig===1?'center':this.styleConfig !=0 && this.txtFixConfig===2?'flex-end':'flex-start',
					paddingLeft:this.styleConfig !=0 && this.txtFixConfig===1?this.serchRight+'px !important':0
				}
			},
			searchStyle() {
				return {
					background: this.dataConfig.searchBoxColor.color[0].item,
					color: this.dataConfig.tipColor.color[0].item,
					justifyContent:this.txtFixConfig==0?'flex-start':this.txtFixConfig==2?'flex-end':'center'
				};
			},
		},
		mounted() {
			let that = this;
			that.hotWords = that.dataConfig.hotWords.list.filter(item => {
				if (item.val) {
					return item;
				}
			});
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
			// #ifdef MP
			const {
				windowWidth,
				statusBarHeight,
			} = uni.getSystemInfoSync();
			const {
				top,
				left,
				width,
				height,
			} = uni.getMenuButtonBoundingClientRect();
			that.serchHeight = (top - statusBarHeight) * 2 + height;
			that.serchRight = windowWidth - left;
			// #endif
		},
		methods: {
			goLink() {
				let url = this.dataConfig.linkConfig.value;
				this.$util.JumpPath(url);
			}
		}
	}
</script>

<style lang="scss" scoped>
	.serch-wrapper {

		&.center {
			justify-content: center;
		}

		&.right {
			justify-content: flex-end;
			/* #ifdef MP */
			padding-right: 185rpx !important;
			/* #endif */
		}
	}

	.title {
		margin-right: 30rpx;
		font-weight: 400;
		font-size: 30rpx;
		color: #333333;
	}

	.map {
		color: #fff;
		font-size: 28rpx;
		margin-right: 20rpx;
		max-width: 100%;

		.info {
			&.on {
				max-width: 260rpx;
			}

			&.on1 {
				max-width: 156rpx;
			}
		}

		.iconfont {
			font-size: 28rpx;
		}

		.icon-ic_downarrow {
			opacity: 0.8;
		}

		.icon-ic_location51{
			margin-right: 6rpx;
		}
	}

	.header {
		width: 100%;
		// height: 100rpx;
		// background: linear-gradient(90deg, $bg-star 50%, $bg-end 100%);

		.serch-wrapper {
			height: 96rpx;
			padding: 18rpx 30rpx !important;

			.logo {
				height: 60rpx;
				margin-right: 20rpx;

				image {
					width: 100%;
					height: 100%;
				}
			}

			.input {
				position: relative;
				flex: 1;

				.search {
					flex: 1;
					height: 60rpx;
					padding: 0 32rpx;
					border-radius: 30rpx;
					background: #F5F5F5;
					font-size: 28rpx;
					line-height: 32rpx;
				}

				.iconfont {
					margin-right: 16rpx;
					font-size: 32rpx;
				}

				.swiper {
					flex: 1;
					height: 32rpx;
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
		
		&.on{
			position: unset;
		}

		.serch-wrapper {
			height: 100%;
			/* #ifdef MP */
			padding: 0 30rpx !important;
			/* #endif */
			/* #ifdef APP-PLUS */
			padding: 0 30rpx !important;
			/* #endif */
			.logo {
				height: 60rpx;
				margin-right: 20rpx;

				image {
					width: 100%;
					height: 100%;
				}
			}

			.input {
				position: relative;
				flex: 1;

				.search {
					flex: 1;
					height: 60rpx;
					padding: 0 32rpx;
					border-radius: 30rpx;
					background: #F5F5F5;
					font-size: 28rpx;
					line-height: 32rpx;
				}

				.iconfont {
					font-size: 32rpx;
					margin-right: 18rpx;
				}

				.swiper {
					flex: 1;
					height: 32rpx;
				}

				.button {
					position: absolute;
					top: 4rpx;
					right: 4rpx;
					height: 52rpx;
					padding: 0 24rpx;
					border-radius: 26rpx;
					background: var(--view-theme);
					font-weight: 500;
					line-height: 52rpx;
					font-size: 22rpx;
					color: #FFFFFF;
				}

				.button2 {
					margin-left: 20rpx;
					font-size: 30rpx;
					color: var(--view-theme);
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
  .row-center uni-swiper-item, .row-center swiper-item{
    text-align: center;
  }
</style>
