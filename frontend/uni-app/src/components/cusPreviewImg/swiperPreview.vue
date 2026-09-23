<template>
	<view class="previewImg" v-if="showBox" @touchmove.stop.prevent>
		<view class="mask" @click="close">
			<swiper @change="changeSwiper" class="mask-swiper" :current="currentIndex" :circular="circular"
				:duration="duration">
				<swiper-item v-for="(src, i) in list" :key="i" class="flex flex-column justify-center align-center">
					<image class="mask-swiper-img" :src="src" mode="widthFix" />
				</swiper-item>
			</swiper>
		</view>
		<view class="pagebox" v-if="list.length>0">{{ Number(currentIndex) + 1 }} / {{ list.length }}</view>
	</view>
</template>

<script setup>
	import { ref, watch } from 'vue';

	defineOptions({ name: 'cusPreviewImg' });

	const props = defineProps({
		list: {
			type: Array,
			required: true,
			default: () => {
				return [];
			}
		},
		circular: {
			type: Boolean,
			default: true
		},
		duration: {
			type: Number,
			default: 500
		}
	});

	const currentIndex = ref(0);
	const showBox = ref(false);

	watch(() => props.list, (val) => {})

	// 左右切换
	function changeSwiper(e) {
		currentIndex.value = e.target.current;
		// emit('changeSwitch', e.target.current)
	}
	function open(current) {
		if (!props.list.length) return;
		currentIndex.value = current;
		showBox.value = true;
	}
	function close() {
		showBox.value = false;
	}

	// 供父组件通过 $refs.open(...) 调用（轮播图图片预览）
	defineExpose({
		open,
		close
	});
</script>

<style lang="scss" scoped>
	@mixin full {
		width: 100%;
		height: 100%;
	}

	.previewImg {
		position: fixed;
		top: 0;
		left: 0;
		z-index: 300;
		@include full;

		.mask {
			display: flex;
			justify-content: center;
			align-items: center;
			background-color: #000;
			opacity: 1;
			z-index: 8;
			@include full;

			&-swiper {
				@include full;

				&-img {
					width: 100%;
				}
			}
		}

		.pagebox {
			position: absolute;
			width: 100%;
			bottom: 20rpx;
			z-index: 300;
			color: #fff;
			text-align: center;
		}
	}

	.mask_sku {
		color: #fff;
		max-width: 80%;
		z-index: 300;
		text-align: center;
		display: flex;
		flex-direction: column;
		align-items: center;
		margin-top: 30rpx;

		.sku_name {
			font-size: 12px;
			border: 1px solid #fff;
			padding: 10rpx 30rpx 10rpx;
			border-radius: 40px;
			box-sizing: border-box;
		}

		.sku_price {
			padding-top: 10px;
		}
	}

	.font12 {
		font-size: 24rpx;
	}

	.share_btn {
		position: absolute;
		top: 70rpx;
		right: 50rpx;
		font-size: 40rpx;
		color: #fff;
		z-index: 300;
	}

	.flex-column {
		flex-direction: column;
	}

	.justify-center {
		justify-content: center;
	}

	.align-center {
		align-items: center;
	}
</style>
