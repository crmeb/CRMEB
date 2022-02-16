<template>
	<view v-show="!isSortType">
		<view class="blankPage" :style="'height:'+ heightConfig*2 +'rpx;'" v-if="heightConfig>0">
			<view class="bankCon" :style="'background-color:'+ bgColor +';'"></view>
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
			isSortType:{
				type: String | Number,
				default:0
			}
		},
		data() {
			return {
				bgColor:this.dataConfig.bgColor.color[0].item,
				heightConfig:this.dataConfig.heightConfig.val
			};
		},
		created() {},
		methods: {
			
		}
	}
</script>

<style lang="scss">
	.blankPage{
		.bankCon{
			width: 100%;
			height: 100%;
			background-color: blue;
		}
	} 
</style>
