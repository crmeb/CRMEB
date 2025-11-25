<template>
	<!-- 辅助线 -->
	<view v-show="!isSortType">
		<view class="lines" :style="[lineWrapStyle]">
			<view class="item" :style="[lineStyle]"></view>
		</view>
	</view>

</template>

<script>
	export default {
		name: 'guide',
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
				heightConfig: 0,
				lineColor: '',
				// lineStyle: 0,
				lrEdge: 0,
				mbConfig: 0
			};
		},
		computed: {
			lineWrapStyle() {
				return {
					padding: `${this.dataConfig.topConfig.val * 2}rpx ${this.dataConfig.lrEdge.val * 2}rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
					'margin-top': `${this.dataConfig.mbConfig.val * 2}rpx`,
					'background-color': this.dataConfig.lineBgColor.color[0].item
				};
			},
			lineStyle() {
				let borderStyle = '';
				switch (this.dataConfig.lineStyle.tabVal) {
					case 1:
						borderStyle = 'solid';
						break;
					case 2:
						borderStyle = 'dotted';
						break;
					default:
						borderStyle = 'dashed';
						break;
				}
				return {
					'border-style': borderStyle,
					'border-color': this.dataConfig.lineColor.color[0].item,
				};
			},
		},
		created() {},
		methods: {

		}
	}
</script>

<style lang="scss">
	.lines {
		padding: 0 20rpx;
		margin-top: 20rpx;

		.item {
			box-sizing: border-box;
			border-bottom-color: red;
			border-width: 0 0 1rpx;
			border-bottom-style: solid;
		}
	}
</style>