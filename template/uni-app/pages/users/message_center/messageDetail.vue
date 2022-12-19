<template>
	<view class="msg-det">
		<view class="title">
			{{$t(msgData.title)}}
		</view>

		<view class="content">
			{{msgData.content}}
		</view>
		<view class="add-time">
			{{$t(`通知于`)}}{{msgData.add_time}}
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		getMsgDetails
	} from '@/api/user.js'
	import home from '@/components/home';
	export default {
		components: {
			home
		},
		data() {
			return {
				msgData: {}
			}
		},
		onLoad(option) {
			this.getMsgDetails(option.id)
		},
		methods: {
			getMsgDetails(id) {
				uni.showLoading({
					title: this.$t(`加载中`)
				});
				getMsgDetails(id).then(res => {
					uni.hideLoading();
					this.msgData = res.data
				}).catch(err => {
					uni.hideLoading();
					return this.$util.Tips({
						title: err
					});
				})
			}
		}
	}
</script>

<style scoped lang="scss">
	.msg-det {
		background-color: #fff;
		padding: 20rpx;

		.title {
			padding: 20rpx;
			font-size: 32rpx;
			font-weight: bold;
			padding-bottom: 20rpx;
			border-bottom: 1px solid #f2f2f2;
		}

		.add-time {
			color: #ababab;
			text-align: right;
			padding-right: 30rpx;
			margin-top: 30rpx;
		}

		.content {
			padding: 20rpx;
			color: #333;
		}
	}
</style>