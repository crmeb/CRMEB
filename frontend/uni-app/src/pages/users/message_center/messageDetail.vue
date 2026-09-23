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

<script setup>
import { ref } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import {
	getMsgDetails as getMsgDetailsApi
} from '@/api/user.js'
import home from '@/components/home';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';

// ---------- data ----------
const msgData = ref({});

// ---------- 生命周期 ----------
onLoad((option) => {
	getMsgDetails(option.id)
});

// ---------- methods ----------
function getMsgDetails(id) {
	uni.showLoading({
		title: i18n.t(`加载中`)
	});
	getMsgDetailsApi(id).then(res => {
		uni.hideLoading();
		msgData.value = res.data
	}).catch(err => {
		uni.hideLoading();
		return util.Tips({
			title: err
		});
	})
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