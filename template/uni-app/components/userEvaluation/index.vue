<template>
	<view class="evaluateWtapper">
		<view class="evaluateItem" v-for="(item, indexw) in reply" :key="indexw">
			<view class="pic-text acea-row row-middle">
				<view class="pictrue">
					<image :src="item.avatar"></image>
				</view>
				<view class="acea-row row-middle">
					<view class="acea-row row-middle" style="margin-right: 15rpx;">
						<view class="name line1">{{ item.nickname }}</view>
						<view class="vipImg" v-if="item.is_money_level>0"><image src="../../static/images/svip.gif"></image></view>
					</view>
					<view class="start" :class="'star' + item.star"></view>
				</view>
			</view>
			<view class="time">{{ item.add_time }} {{ item.suk }}</view>
			<view class="evaluate-infor">{{ item.comment }}</view>
			<view class="imgList acea-row">
				<view class="pictrue" v-for="(itemn, indexn) in item.pics" :key="indexn">
					<image :src="itemn" class="image" @click='getpreviewImage(indexw, indexn)'></image>
				</view>
			</view>
			<view class="reply" v-if="item.merchant_reply_content">
				<text class="font-num">{{$t(`店小二`)}}</text>：{{
          item.merchant_reply_content
        }}
			</view>
		</view>
	</view>
</template>
<script>
	export default {
		props: {
			reply: {
				type: Array,
				default: () => []
			}
		},
		data: function() {
			return {};
		},
		methods: {
			getpreviewImage: function(indexw, indexn) {
				uni.previewImage({
					urls: this.reply[indexw].pics,
					current: this.reply[indexw].pics[indexn]
				});
			}
		}
	}
</script>
<style scoped lang='scss'>
	.vipImg{
		width: 68rpx;
		height: 27rpx;
		image{
			width: 100%;
			height: 100%;
			margin-left: 10rpx;
		}
	}
	.evaluateWtapper .evaluateItem {
		background-color: #fff;
		padding-bottom: 25rpx;
	}

	.evaluateWtapper .evaluateItem~.evaluateItem {
		border-top: 1rpx solid #f5f5f5;
	}

	.evaluateWtapper .evaluateItem .pic-text {
		font-size: 26rpx;
		color: #282828;
		height: 95rpx;
		padding: 0 30rpx;
	}

	.evaluateWtapper .evaluateItem .pic-text .pictrue {
		width: 60rpx;
		height: 60rpx;
		margin-right: 20rpx;
		border-radius: 50%;
	}

	.evaluateWtapper .evaluateItem .pic-text .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
	}

	.evaluateWtapper .evaluateItem .pic-text .name {
		max-width: 450rpx;
		font-size: 30rpx;
	}

	.evaluateWtapper .evaluateItem .time {
		font-size: 24rpx;
		color: #82848f;
		padding: 0 30rpx;
	}

	.evaluateWtapper .evaluateItem .evaluate-infor {
		font-size: 28rpx;
		color: #282828;
		margin-top: 19rpx;
		padding: 0 30rpx;
	}

	.evaluateWtapper .evaluateItem .imgList {
		padding: 0 30rpx 0 15rpx;
		margin-top: 25rpx;
	}

	.evaluateWtapper .evaluateItem .imgList .pictrue {
		width: 156rpx;
		height: 156rpx;
		margin: 0 0 15rpx 15rpx;
	}

	.evaluateWtapper .evaluateItem .imgList .pictrue image {
		width: 100%;
		height: 100%;
		background-color: #f7f7f7;
	}

	.evaluateWtapper .evaluateItem .reply {
		font-size: 26rpx;
		color: #454545;
		background-color: #f7f7f7;
		border-radius: 5rpx;
		margin: 20rpx 30rpx 0 30rpx;
		padding: 20rpx;
		position: relative;
	}

	.evaluateWtapper .evaluateItem .reply::before {
		content: "";
		width: 0;
		height: 0;
		border-left: 20rpx solid transparent;
		border-right: 20rpx solid transparent;
		border-bottom: 30rpx solid #f7f7f7;
		position: absolute;
		top: -14rpx;
		left: 40rpx;
	}
</style>
