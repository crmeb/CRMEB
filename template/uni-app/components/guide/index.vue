<template>
	<view class="content">
		<swiper class="swiper" :autoplay="autoplay" :duration="duration">
			<swiper-item v-for="(item,index) in advList" :key="index">
				<view class="swiper-item">
					<view class="swiper-item-img">
						<image :src="item" mode="aspectFit"></image>
					</view>
				</view>
			</swiper-item>
		</swiper>
		<view class="jump-over" @tap="launchFlag()">跳过 {{time}}</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {
				autoplay: false,
				duration: 500,
				jumpover: '跳过',
				experience: '立即体验',
				time: 5,
				timecount:undefined
			}
		},
		props: {
			advList: {
				type: Array,
				default: () => {
					[]
				}
			}
		},
		mounted() {
			this.timer()
		},
		methods: {
			timer() {
				var t = 5
				this.timecount = setInterval(() => {
					t--
					this.time = t
					if (t <= 0) {
						clearInterval(this.timecount)
						this.launchFlag()
					}
				}, 1000)
			},
			launchFlag: function() {
				/**
				 * 向本地存储中设置launchFlag的值，即启动标识；
				 */
				clearInterval(this.timecount)
				// uni.setStorage({
				// 	key: 'launchFlag',
				// 	data: true,
				// });
				uni.switchTab({
					url: '/pages/index/index'
				});

			}
		}
	}
</script>
<style>
	page,
	.content {
		width: 100%;
		height: 100%;
		background-size: 100% auto;
		padding: 0;
		z-index: 999;
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

	.uniapp-img {
		height: 50%;
		background: #FFFFFF;
		display: flex;
		justify-content: center;
		align-items: center;
		overflow: hidden;
	}

	.uniapp-img image {
		width: 40%;
	}

	.jump-over,
	.experience {
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

	.experience {
		right: 50%;
		margin-right: -105upx;
		bottom: 80rpx;
	}
</style>
