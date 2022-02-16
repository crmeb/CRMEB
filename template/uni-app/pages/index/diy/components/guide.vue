<template>
	<view v-show="!isSortType">
		<view class="lines" :style="'padding:0 '+ lrEdge*2 +'rpx;margin-top:'+ mbConfig*2 +'rpx;'" v-if="heightConfig">
			<view class="item" :style="'border-bottom-width:'+ heightConfig*2 +'rpx;border-bottom-color:'+ lineColor +';border-bottom-style:'+ lineStyle +';'"></view>
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
			isSortType:{
				type: String | Number,
				default:0
			}
		},
		data() {
			return {
				heightConfig:this.dataConfig.heightConfig.val,
				lineColor:this.dataConfig.lineColor.color[0].item,
				lineStyle:this.dataConfig.lineStyle.list[this.dataConfig.lineStyle.type].style,
				lrEdge:this.dataConfig.lrEdge.val,
				mbConfig:this.dataConfig.mbConfig.val
			};
		},
		created() {},
		methods: {
			
		}
	}
</script>

<style lang="scss">
	.lines{
		padding: 0 20rpx;
		margin-top: 20rpx;
		.item{
			width: 100%;
			box-sizing: border-box;
			border-bottom-color: red;
			border-bottom-width: 1px;
			// dashed
			border-bottom-style: dotted;
		}
	}
</style>
