<template>
	<view>
		<view class="title">{{agreement.title || ''}}</view>
		<view class="cont" v-html="agreement.content"></view>
	</view>
</template>

<script setup>
	import { ref } from 'vue';
	import { onLoad } from '@dcloudio/uni-app';
	import {
		memberCard as memberCardApi
	} from '@/api/user.js';
	import i18n from '@/utils/lang.js';

	// ---------- data ----------
	const agreement = ref('');

	onLoad(() => {
		memberCard();
	});

	// ---------- methods ----------
	function memberCard() {
		uni.showLoading({
			title: i18n.t(`正在加载中`)
		});
		memberCardApi().then(res => {
			uni.hideLoading();
			const {
				member_explain
			} = res.data;
			agreement.value = member_explain;
		}).catch(err => {
			uni.hideLoading();
		});
	}
</script>

<style>
	page {
		background-color: #FFFFFF;
	}
</style>

<style scoped lang="scss">
	.title {
		padding-top: 10rpx;
		font-size: 30rpx;
		text-align: center;
		font-weight: bold;
	}

	.cont {
		padding: 50rpx 30rpx;

:deep(img){
			max-width: 100% !important;
		}
	}
</style>
