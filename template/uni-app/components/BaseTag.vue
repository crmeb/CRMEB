<template>
	<view class="base-tag" :class="{middle: size == 'middle'}" v-if="!imgSrc" :style="[tagStyle]" >{{text}}</view>
	<image class="img-tag" :class="{'middle-img-tag': size == 'middle'}" :src="imgSrc" mode="heightFix" v-else></image>
</template>

<script>
	export default {
		name: "tag",
		props: {
			size: {
				// 标签大小 normal, small
				type: String,
				default: "normal"
			},
			// 标签内容
			text: {
				type: String,
				default: ""
			},
			circle: {
				type: [Boolean, String],
				default: false
			},
			background: {
				type: String,
				default: '#e93323'
			},
			color: {
				type: String,
				default: '#ffffff'
			},
			borderColor:{
				type: String,
				default: ''
			},
			imgSrc:{
				type: String,
				default: ''
			}
		},
		computed:{
			tagStyle(){
				return {
					background: this.background,
					color: this.color,
					border: this.borderColor ? `1rpx solid ${this.borderColor}` : 'none'
				}
			}
		}
	};
</script>

<style lang="scss">
	.base-tag {
		display: flex;
		justify-content: center;
		align-items: center;
		height: 30rpx;
		font-size: 18rpx;
		padding: 0 8rpx;
		color: #333;
		border-radius: 4rpx;
		background-color: #fff;
		margin-right: 8rpx;
		box-sizing: border-box;
		margin-bottom: 8rpx;
	}
	.middle{
		height: 36rpx;
		padding: 0 12rpx;
		border-radius: 8rpx;
		font-size: 20rpx;
		margin-right: 16rpx;
	}
	.img-tag{
		display: block;
		height: 30rpx;
		border-radius: 4rpx;
		margin-right: 14rpx;
		box-sizing: border-box;
		margin-bottom: 8rpx;
	}
	.middle-img-tag{
		height: 36rpx;
		border-radius: 8rpx;
		margin-right: 8rpx;
	}
</style>
