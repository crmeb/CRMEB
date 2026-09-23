<template>
	<view v-if="sharePacket.isState" class='sharing-packets' :class='sharePacket.isState && showAnimate ? "":"right"'>
		<view class='sharing-con' @click='goShare'>
			<image :src="imgHost + '/statics/images/red-packets.png'" />
			<view class='text font-color'>
				<view>{{$t(`最高返佣`)}}</view>
				<view class='money'><text class='label'>{{$t(`￥`)}}</text>{{sharePacket.priceName}}</view>
				<view class='tip'>{{$t(`推广享佣金`)}}</view>
				<view class='shareBut'>{{$t(`立即分享`)}}</view>
			</view>
		</view>
	</view>
</template>

<script setup>
	import { ref, watch } from 'vue';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';

	const props = defineProps({
		sharePacket: {
			type: Object,
			default: function() {
				return {
					isState: true,
					priceName: ''
				}
			}
		},
		showAnimate: {
			type: Boolean,
			default: true
		},
	});

	const emit = defineEmits(['closeChange', 'listenerActionSheet', 'boxStatus']);

	const imgHost = ref(HTTP_REQUEST_URL);
	const isAnimate = ref(true);

	watch(() => props.showAnimate, (nVal, oVal) => {
		setTimeout(res => {
			isAnimate.value = nVal
		}, 1000)
	})

	function closeShare() {
		emit('closeChange');
	}
	function goShare() {
		if (isAnimate.value) {
			emit('listenerActionSheet');
		} else {
			isAnimate.value = true
			emit('boxStatus', true);
		}
	}
</script>

<style scoped lang="scss">
	.sharing-packets {
		position: fixed;
		left: 30rpx;
		bottom: 200rpx;
		z-index: 15;
		transition: all 0.3s ease-in-out 0s;
		opacity: 1;
		transform: scale(1);

		&.right {
			left: -170rpx;
		}
	}

	// .sharing-packets.on {
	// 	transform: scale(0);
	// 	opacity: 0;
	// }

	.sharing-packets .iconfont {
		width: 44rpx;
		height: 44rpx;
		border-radius: 50%;
		text-align: center;
		line-height: 44rpx;
		background-color: #999;
		font-size: 20rpx;
		color: #fff;
		margin: 0 auto;
		box-sizing: border-box;
		padding-left: 1px;
	}

	.sharing-packets .line {
		width: 2rpx;
		height: 40rpx;
		background-color: #999;
		margin: 0 auto;
	}

	.sharing-packets .sharing-con {
		width: 187rpx;
		height: 210rpx;
		position: relative;
	}

	.sharing-packets .sharing-con image {
		width: 100%;
		height: 100%;
	}

	.sharing-packets .sharing-con .text {
		position: absolute;
		top: 30rpx;
		font-size: 20rpx;
		line-height: 30rpx;
		width: 100%;
		text-align: center;
	}

	.sharing-packets .sharing-con .text .money {
		font-size: 32rpx;
		line-height: 42rpx;
		font-weight: bold;
		margin-top: 5rpx;
	}

	.sharing-packets .sharing-con .text .money .label {
		font-size: 20rpx;
	}

	.sharing-packets .sharing-con .text .tip {
		font-size: 18rpx;
		line-height: 18rpx;
		color: #999;
		margin-top: 5rpx;
	}

	.sharing-packets .sharing-con .text .shareBut {
		font-size: 22rpx;
		line-height: 48rpx;
		color: #fff;
		/* #ifdef H5 */
		margin-top: 28rpx;
		/* #endif */

		/* #ifndef H5 */
		margin-top: 26rpx;
		/* #endif */

	}
</style>
