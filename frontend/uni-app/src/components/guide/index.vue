<template>
	<view class="content">
		<swiper class="swiper" :autoplay="autoplay" indicator-dots="true" indicator-color="rgba(255,255,255,0.6)"
			:duration="duration" v-if="advData.type == 'pic' && advData.value.length">
			<swiper-item v-for="(item,index) in advData.value" :key="index" @click="jump(item.link)">
				<view class="swiper-item">
					<view class="swiper-item-img">
						<image :src="item.img" mode="scaleToFill"></image>
					</view>
				</view>
			</swiper-item>
		</swiper>
		<view class="video-box" v-else-if="advData.type == 'video' && advData.video_link">
			<video class="vid" :src="advData.video_link" :autoplay="true" :loop="true" :muted="true"
				:controls="false"></video>
		</view>
		<view class="jump-over" @click.stop="launchFlag()">{{$t(`跳过`)}}<text v-if="closeType == 1">{{times}}</text>
		</view>
	</view>
</template>

<script setup>
	import { ref, onMounted } from 'vue';
	import { onHide } from '@dcloudio/uni-app';
	import i18n from '@/utils/lang.js';
	import util from '@/utils/util.js';

	const props = defineProps({
		advData: {
			type: Object,
			default: () => {}
		},
		// 1 倒计时 2 手动关闭(预留)
		closeType: {
			type: Number,
			default: 1
		},
	});

	const autoplay = ref(true);
	const duration = ref(500);
	const jumpover = ref(i18n.t(`跳过`));
	const experience = ref(i18n.t(`立即体验`));
	const timecount = ref(undefined);
	const times = ref(0);
	const hasLaunched = ref(false);

	onMounted(() => {
		timer()
	})

	onHide(() => {
		clearInterval(timecount.value)
	})

	function timer() {
		times.value = props.advData.time
		let t = props.advData.time || 5
		timecount.value = setInterval(() => {
			t--
			times.value = t
			if (t <= 0) {
				clearInterval(timecount.value)
				launchFlag()
			}
		}, 1000)
	}
	function launchFlag() {
		if (hasLaunched.value) return
		hasLaunched.value = true
		clearInterval(timecount.value)
		uni.switchTab({
			url: '/pages/index/index',
			fail: () => {
				hasLaunched.value = false
			}
		});
	}
	function jump(url) {
		if (url) {
			clearInterval(timecount.value)
			util.JumpPath(url);
		}
	}
</script>
<style lang="scss" scoped>
	page,
	.content {
		width: 100%;
		height: 100%;
		background-size: 100% auto;
		padding: 0;
	}

	.swiper {
		width: 100%;
		height: 100vh;
		background: #FFFFFF;
	}

	.swiper-item {
		width: 100%;
		height: 100%;
		text-align: center;
		position: relative;
		display: flex;
		/* justify-content: center; */
		align-items: flex-end;
		flex-direction: column-reverse
	}

	.swiper-item-img {
		width: 100%;
		height: 100vh;
		margin: 0 auto;
	}

	.swiper-item-img image {
		width: 100%;
		height: 100%;
	}

	.jump-over {
		position: absolute;
		height: 45rpx;
		line-height: 45rpx;
		padding: 0 15rpx;
		border-radius: 30rpx;
		font-size: 24rpx;
		color: #b09e9a;
		border: 1px solid #b09e9a;
		z-index: 999;
	}

	.jump-over {
		right: 30rpx;
		top: 80rpx;
	}

	.video-box {
		width: 100vw;
		height: 100vh;

		.vid {
			width: 100%;
			height: 100%;
		}
	}
</style>
