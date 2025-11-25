<template>
	<!-- 标题 -->
	<view v-show="!isSortType" :style="[titleWrapperStyle]">
		<view :style="[titleWrapStyle]">
			<view @click="goLink" class="title acea-row row-middle row-between" :style="[titleLocation]">
				<view :style="[titleStyle]">{{dataConfig.titleConfig.value}}</view>
				<view class="more" v-if="!dataConfig.buttonConfig.tabVal" :style="[moreStyle]">
					{{dataConfig.titleConfigRight.value}}
					<text class="iconfont icon-ic_rightarrow"></text>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: 'titles',
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
		computed: {
			titleWrapperStyle() {
				return {
					'padding': `${this.dataConfig.topConfig.val * 2}rpx ${this.dataConfig.prConfig.val * 2}rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
					'margin-top': `${this.dataConfig.mbConfig.val * 2}rpx`,
					'background': this.dataConfig.bottomBgColor.color[0].item,
				};
			},
			titleWrapStyle() {
				let borderRadius = `${this.dataConfig.fillet.val * 2}rpx`;
				if (this.dataConfig.fillet.type) {
					borderRadius =
						`${this.dataConfig.fillet.valList[0].val * 2}rpx ${this.dataConfig.fillet.valList[1].val * 2}rpx ${this.dataConfig.fillet.valList[3].val * 2}rpx ${this.dataConfig.fillet.valList[2].val * 2}rpx`;
				}
				return {
					'border-radius': borderRadius,
					'background': `linear-gradient(90deg, ${this.dataConfig.titleColor.color[0].item} 0%, ${this.dataConfig.titleColor.color[1].item} 100%)`,
				};
			},
			titleStyle() {
				let style = {
					'font-size': `${this.dataConfig.fontSize.val * 2}rpx`,
					'color': this.dataConfig.themeColor.color[0].item,
				};
				switch (this.dataConfig.textStyle.tabVal) {
					case 1:
						style['font-style'] = 'italic';
						break;
					case 2:
						style['font-weight'] = 'bold';
						break;
				}
				return style;
			},
			titleLocation() {
				if(this.dataConfig.buttonConfig.tabVal){
					let style = {}
					switch (this.dataConfig.textPosition.tabVal) {
						case 1:
							style['justify-content'] = 'center';
							break;
						case 2:
							style['justify-content'] = 'flex-end';
							break;
					}
					return style;
				}
			},
			moreStyle() {
				return {
					'font-size': `${this.dataConfig.buttonText.val * 2}rpx`,
					'color': this.dataConfig.buttonColor.color[0].item,
				};
			},
		},
		methods: {
			goLink() {
				this.$util.JumpPath(this.dataConfig.linkConfig.value);
			}
		}
	}
</script>

<style lang="scss">
	.title {
		justify-content: space-between;
		padding: 26rpx 24rpx;
		border-radius: 16rpx 16rpx 0rpx 0rpx;
		font-weight: 500;
		font-size: 32rpx;
		line-height: 44rpx;
		color: #333333;

		.more {
			font-weight: 400;
			font-size: 24rpx;
			line-height: 34rpx;
			color: #999999;
		}

		.iconfont {
			font-size: 24rpx;
		}
	}
</style>