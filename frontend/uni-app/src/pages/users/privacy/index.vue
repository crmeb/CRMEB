<template>
	<view class="content">
		<mp-html :content="content" :tag-style="tagStyle" />
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import {
	getUserAgreement,
} from '@/api/user.js';
import util from '@/utils/util.js';

// ---------- data ----------
const tagStyle = {
	img: 'width:100%;display:block;',
	table: 'width:100%',
	video: 'width:100%'
};
const content = ref(``);

// ---------- 生命周期 ----------
onLoad((options) => {
	getUserAgreement(options.type).then(res => {
		content.value = res.data.content
		uni.setNavigationBarTitle({
			title: res.data.title
		});
	}).catch(err => {
		// 原代码此处误写为 that.$util（未定义），此处修正为 util
		util.Tips({
			title: err
		});
	})
});
</script>

<style scoped>
	page {
		background-color: #fff;
	}

	.content {
		padding: 40rpx 30rpx;
		line-height: 2;
	}
</style>
