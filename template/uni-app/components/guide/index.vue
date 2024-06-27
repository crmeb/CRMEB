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

<script>
	export default {
		data() {
			return {
				autoplay: true,
				duration: 500,
				jumpover: this.$t(`跳过`),
				experience: this.$t(`立即体验`),
				timecount: undefined,
				times: 0
			}
		},
		props: {
			advData: {
				type: Object,
				default: () => {}
			},
			// 1 倒计时 2 手动关闭(预留)
			closeType: {
				type: Number,
				default: 1
			},
		},
		mounted() {
			this.timer()
		},
		onHide() {
			clearInterval(this.timecount)
		},
		methods: {
			timer() {
				this.times = this.advData.time
				let t = this.advData.time || 5
				this.timecount = setInterval(() => {
					t--
					this.times = t
					if (t <= 0) {
						clearInterval(this.timecount)
						this.launchFlag()
					}
				}, 1000)
			},
			launchFlag() {
				clearInterval(this.timecount)
				uni.switchTab({
					url: '/pages/index/index'
				});
			},
			jump(url) {
				if (url) {
					clearInterval(this.timecount)
					this.$util.JumpPath(url);
				}
			},
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
