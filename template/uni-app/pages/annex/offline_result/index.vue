<template>
	<view>
		<view class="result">
			<view class="image-wrap"><image class="image" src="../static/offline-result.png"></image></view>
			<view class="text">{{$t(`支付成功`)}}</view>
		</view>
		<navigator class="link"  url="/pages/index/index" open-type="switchTab">{{$t(`进入商城`)}}</navigator>
	</view>
</template>

<script>
export default {
	onLoad(options) {
		let site_name = options.site_name || '';
		if (site_name) {
			uni.setNavigationBarTitle({
				title: site_name
			});
		}
	}
};
</script>

<style>
page {
	background-color: #fff;
}
</style>

<style lang="scss" scoped>
.result {
	margin-top: 200rpx;

	.image-wrap {
		width: 267rpx;
		height: 223rpx;
		margin: 0 auto;
	}

	.image {
		width: 100%;
		height: 100%;
	}

	.text {
		margin-top: 46rpx;
		font-size: 34rpx;
		text-align: center;
		color: #282828;
	}
}

.link {
	width: 560rpx;
	height: 86rpx;
	border: 1rpx solid #F19D2F;
	border-radius: 43rpx;
	margin: 90rpx auto 0;
	font-size: 30rpx;
	line-height: 86rpx;
	text-align: center;
	color: #F19D2F;
}
</style>
