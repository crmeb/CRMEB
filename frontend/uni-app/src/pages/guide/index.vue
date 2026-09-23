<template>
	<view class="main">
		<guide v-if="guidePages" :advData="advData" :time="advData.time"></guide>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow, onHide } from '@dcloudio/uni-app';
import guide from '@/components/guide/index.vue';
import Cache from '@/utils/cache';
import { getOpenAdv } from '@/api/api.js';

const guidePages = ref(false);
const advData = ref([]);
const indexUrl = ref('/pages/index/index');
const isPageActive = ref(false);
const hasNavigated = ref(false);

function navigateToIndex() {
	if (!isPageActive.value || hasNavigated.value) return;
	hasNavigated.value = true;
	uni.switchTab({
		url: indexUrl.value,
		fail: () => {
			hasNavigated.value = false;
		}
	});
}

function loadExecution() {
	const tagDate = uni.getStorageSync('guideDate') || '',
		nowDate = new Date().toLocaleDateString();
	if (tagDate === nowDate) {
		navigateToIndex();
		return;
	}
	getOpenAdv()
		.then((res) => {
			if (res.data.status == 0 || res.data.value.length == 0) {
				navigateToIndex();
			} else if (res.data.status && (res.data.value.length || res.data.video_link)) {
				advData.value = res.data;
				uni.setStorageSync('guideDate', new Date().toLocaleDateString());
				guidePages.value = true;
			}
		})
		.catch((err) => {
			navigateToIndex();
		});
}

onLoad((options) => {
	if (options.spid) {
		indexUrl.value = indexUrl.value + '?spid=' + options.spid;
	}
});
onShow(() => {
	isPageActive.value = true;
	hasNavigated.value = false;
	loadExecution();
});
onHide(() => {
	isPageActive.value = false;
	guidePages.value = false;
});
</script>

<style>
page,
.main {
	width: 100%;
	height: 100%;
}
</style>
