<template>
	<!-- <view class='product-bg'>
	        <swiper  :indicator-dots="indicatorDots"
	            :autoplay="autoplay" :circular="circular" :interval="interval" :duration="duration" @change="change">
	            <block v-for="(item,index) in imgUrls" :key="index"> 
	                <swiper-item>
	                   <image :src="item" class="slide-image"/>
					   
	                </swiper-item>
	            </block>
	        </swiper>
	        <view class='pages'>{{currents}}/{{imgUrls.length || 1}}</view>
	    </view> -->
	<view class='product-bg'>
		<swiper :indicator-dots="indicatorDots" indicator-active-color="#e93323" :autoplay="autoplay" :circular="circular"
		 :interval="interval" :duration="duration" @change="change">
			<swiper-item v-if="videoline">
				<view class="item">
					<video id="myVideo" :src='videoline' objectFit="cover" controls style="width:100%;height:100% "
					 show-center-play-btn show-mute-btn="true" auto-pause-if-navigate :custom-cache="false"></video>
					<view class="poster" v-if="controls">
						<image class="image" :src="imgUrls[0]"></image>
					</view>
					<view class="stop" v-if="controls" @tap="bindPause">
						<image class="image" src="../../static/images/stop.png"></image>
					</view>
				</view>
			</swiper-item>
			<block v-for="(item,index) in imgUrls" :key='index'>
				<swiper-item>
					<image :src="item" class="slide-image" />
				</swiper-item>
			</block>
		</swiper>
	</view>
</template>

<script>
	export default {
		props: {
			imgUrls: {
				type: Array,
				default: function() {
					return [];
				}
			},
			videoline: {
				type: String,
				value: ""
			}
		},
		data() {
			return {
				indicatorDots: true,
				circular: true,
				autoplay: true,
				interval: 3000,
				duration: 500,
				currents: "1",
				controls: true
			};
		},
		mounted() {
			this.videoContext = uni.createVideoContext('myVideo', this);
		},
		methods: {
			bindPause: function() {
				this.videoContext.play();
				this.$set(this, 'controls', false)
				this.autoplay = false
			},
			change: function(e) {
				this.$set(this, 'currents', e.detail.current + 1);
			}
		}
	}
</script>

<style scoped lang="scss">
	.product-bg {
		width: 100%;
		height: 750rpx;
		position: relative;
	}

	.product-bg swiper {
		width: 100%;
		height: 100%;
		position: relative;
	}

	.product-bg .slide-image {
		width: 100%;
		height: 100%;
	}

	.product-bg .pages {
		position: absolute;
		background-color: #fff;
		height: 34rpx;
		padding: 0 10rpx;
		border-radius: 3rpx;
		right: 30rpx;
		bottom: 30rpx;
		line-height: 34rpx;
		font-size: 24rpx;
		color: #050505;
	}

	#myVideo {
		width: 100%;
		height: 100%
	}

	.product-bg .item {
		position: relative;
		width: 100%;
		height: 100%;
	}

	.product-bg .item .poster {
		position: absolute;
		top: 0;
		left: 0;
		height: 750rpx;
		width: 100%;
		z-index: 9;
	}

	.product-bg .item .poster .image {
		width: 100%;
		height: 100%;
	}

	.product-bg .item .stop {
		position: absolute;
		top: 50%;
		left: 50%;
		width: 136rpx;
		height: 136rpx;
		margin-top: -68rpx;
		margin-left: -68rpx;
		z-index: 9;
	}

	.product-bg .item .stop .image {
		width: 100%;
		height: 100%;
	}
</style>
