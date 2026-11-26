<template>
	<view
		v-if="sharePacket.isState"
		class="sharing-packets"
		:class="sharePacket.isState && showAnimate ? '' : 'right'"
	>
		<view
			v-if="showAnimate"
			class="fold-btn"
			@click.stop="foldShare"
		>
			<text class="iconfont icon-guanbi"></text>
		</view>
		<view class="sharing-con" @click="goShare">
			<view class="packet-shell">
				<view class="packet-card">
					<view class="packet-card-dash"></view>
					<view class="text-top">
						<view class="title font-color">{{ $t(`最高返佣`) }}</view>
						<view class="money font-color"
							><text class="label">{{ $t(`￥`) }}</text
							>{{ sharePacket.priceName }}</view
						>
						<view class="tip">{{ $t(`推广享佣金`) }}</view>
					</view>
				</view>
				<view class="packet-foot">
					<text class="shareBut">{{ $t(`立即分享`) }}</text>
				</view>
			</view>
		</view>
	</view>
</template>

<script setup>
	import { ref, watch } from "vue";

	const props = defineProps({
		sharePacket: {
			type: Object,
			default: function () {
				return {
					isState: true,
					priceName: "",
				};
			},
		},
		showAnimate: {
			type: Boolean,
			default: true,
		},
	});

	const emit = defineEmits(["closeChange", "listenerActionSheet", "boxStatus"]);

	const isAnimate = ref(true);

	watch(
		() => props.showAnimate,
		(nVal) => {
			setTimeout(() => {
				isAnimate.value = nVal;
			}, 300);
		}
	);

	function foldShare() {
		emit("closeChange");
	}

	function goShare() {
		if (isAnimate.value) {
			emit("listenerActionSheet");
		} else {
			isAnimate.value = true;
			emit("boxStatus", true);
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

	.fold-btn {
		position: absolute;
		top: -12rpx;
		right: -12rpx;
		z-index: 4;
		width: 40rpx;
		height: 40rpx;
		border-radius: 50%;
		background-color: rgba(0, 0, 0, 0.45);
		display: flex;
		align-items: center;
		justify-content: center;

		.iconfont {
			font-size: 20rpx;
			color: #fff;
			line-height: 1;
		}
	}

	.sharing-con {
		width: 187rpx;
		height: 210rpx;
		position: relative;
	}

	.packet-shell {
		position: absolute;
		left: 0;
		right: 0;
		top: 0;
		bottom: 0;
		border-radius: 20rpx;
		overflow: hidden;
		display: flex;
		flex-direction: column;
		background: linear-gradient(
			180deg,
			var(--view-gradient, #ff7931) 0%,
			var(--view-theme, #e93323) 100%
		);
		box-shadow: 0 8rpx 24rpx rgba(0, 0, 0, 0.14);
		padding: 12rpx 12rpx 0;
		box-sizing: border-box;
	}

	.packet-card {
		position: relative;
		flex: 1;
		border-radius: 12rpx;
		background: #fff;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.packet-card-dash {
		position: absolute;
		left: 8rpx;
		right: 8rpx;
		top: 8rpx;
		bottom: 8rpx;
		border-radius: 8rpx;
		border: 2rpx dashed var(--view-theme, #e93323);
		opacity: 0.3;
		pointer-events: none;
	}

	.text-top {
		position: relative;
		z-index: 1;
		text-align: center;
		padding: 0 8rpx;
	}

	.title {
		font-size: 20rpx;
		line-height: 28rpx;
	}

	.money {
		font-size: 32rpx;
		line-height: 42rpx;
		font-weight: bold;
		margin-top: 4rpx;

		.label {
			font-size: 20rpx;
			font-weight: bold;
		}
	}

	.tip {
		font-size: 18rpx;
		line-height: 24rpx;
		color: #999;
		margin-top: 4rpx;
	}

	.packet-foot {
		height: 64rpx;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.shareBut {
		font-size: 22rpx;
		line-height: 32rpx;
		font-weight: 600;
		color: #fff;
	}
</style>
