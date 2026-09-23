<template>
  <web-view
    class="web-view"
    :webview-styles="webviewStyles"
    :src="url"
    :style="{ width: windowW + 'px', height: windowH + 'px' }"
  ></web-view>
</template>

<script setup>
	import { ref } from 'vue';
	import { onLoad } from '@dcloudio/uni-app';

	// ---------- data ----------
	const windowH = ref(0);
	const windowW = ref(0);
	const webviewStyles = ref({
		progress: {
			color: 'transparent',
		},
	});
	const url = ref('');

	onLoad((option) => {
		url.value = option.url;
		try {
			const res = uni.getWindowInfo();
			windowW.value = res.windowWidth;
			windowH.value = res.windowHeight;
		} catch (e) {
			// error
		}
	});
</script>
