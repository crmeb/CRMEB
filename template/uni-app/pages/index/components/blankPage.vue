<template>
	<!-- 辅助空白 -->
	<view v-show="!isSortType">
		<view class="blankPage" :style="[blankWrapStyle]">
			<view class="bankCon" :style="[blankStyle]"></view>
		</view>
	</view>

</template>

<script>
	export default {
		name: 'blankPage',
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
				bgColor: '',
				heightConfig: 0
			};
		},
		computed: {
			blankWrapStyle() {
				return {
					'padding': `${this.dataConfig.topConfig.val * 2}rpx ${this.dataConfig.lrEdge.val * 2}rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
					'background': this.dataConfig.bottomBgColor.color[0].item,
				};
			},
			blankStyle() {
				let borderRadius = `${this.dataConfig.fillet.val * 2}rpx`;
				if (this.dataConfig.fillet.type) {
					borderRadius = `${this.dataConfig.fillet.valList[0].val * 2}rpx ${this.dataConfig.fillet.valList[1].val * 2}rpx ${this.dataConfig.fillet.valList[3].val * 2}rpx ${this.dataConfig.fillet.valList[2].val * 2}rpx`;
				}
				return {
					'height': `${this.dataConfig.heightConfig.val * 2}rpx`,
					'border-radius': borderRadius,
					'background': this.dataConfig.bgColor.color[0].item
				};
			},
		},
		created() {},
		methods: {

		}
	}
</script>

<style lang="scss">
	.blankPage {
		.bankCon {
			width: 100%;
			height: 100%;
			background-color: blue;
		}
	}
</style>