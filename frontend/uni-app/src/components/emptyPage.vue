<template>
	<view class="empty-box">
		<image :src="imgHost + '/statics/images/empty-box.png'"></image>
		<view class="txt">{{title || $t(`暂无记录`)}}</view>
	</view>
</template>

<script setup>
	import { ref } from 'vue';
	import {HTTP_REQUEST_URL} from '@/config/app';

	defineProps({
		title: {
			type: String,
			default: '',
		},
	});

	const imgHost = ref(HTTP_REQUEST_URL);
</script>

<style lang="scss">
	.empty-box{
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		padding-top: 200rpx;
		image{
			width: 414rpx;
			height: 240rpx;
		}
		.txt{
			font-size: 26rpx;
			color: #999;
		}
	}
</style>
