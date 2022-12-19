<template>
	<view class="index-wrapper" :class="{'off': sty ==='off'}">
		<view class='titles' v-if="isIframe && !titleConfig.length">{{$t(`暂无标题`)}}</view>
		<view class='title acea-row row-between-wrapper' v-if="isShow && titleConfig.length && !isIframe">
			<view class='text'>
				<view class='name line1'>{{titleConfig[0].val}}</view>
				<view class='line1 txt-btn'>{{$t(`诚意推荐品质商品`)}}</view>
			</view>
			<navigator hover-class="none" :url="titleConfig[1].val" class='more'>
				{{$t(`更多`)}}
				<text class='iconfont icon-jiantou'></text>
			</navigator>
		</view>
		<view class='title acea-row row-between-wrapper' v-if="isIframe && titleConfig.length">
			<view class='text'>
				<view class='name line1'>{{titleConfig[0].val}}</view>
				<view class='line1 txt-btn'>{{$t(`诚意推荐品质商品`)}}</view>
			</view>
			<view class='more'>
				{{$t(`更多`)}}
				<text class='iconfont icon-jiantou'></text>
			</view>
		</view>
	</view>
</template>

<script>
	let app = getApp()
	export default {
		name: 'titles',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			sty: {
				type: String,
				default: 'on'
			}
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if (nVal) {
						this.titleConfig = nVal.titleInfo.list;
						this.isShow = nVal.isShow.val;
					}
				}
			}
		},
		data() {
			return {
				titleConfig: {},
				name: this.$options.name,
				isIframe: false,
				isShow: true
			};
		},
		created() {
			this.isIframe = app.globalData.isIframe;
		},
		mounted() {},
		methods: {}
	}
</script>

<style lang="scss">
	.titles {
		font-size: 35rpx;
		color: #282828;
		text-align: center;
		width: 100%;
	}

	.title {
		padding: 30rpx 0;

		.text {
			display: flex;
			.name{
				font-size: 32rpx;
				font-weight: bold;
			}
			.txt-btn {
				display: flex;
				align-items: flex-end;
				margin-bottom: 6rpx;
				margin-left: 12rpx;
			}

		}
	}

	.index-wrapper {
		background-color: $uni-bg-color;
		margin: $uni-index-margin-row $uni-index-margin-col;
		border-radius: $uni-border-radius-index;
	}

	.off {
		border-radius: $uni-border-radius-index $uni-border-radius-index 0 0 !important;
		margin-bottom: 0 !important;
	}
</style>
