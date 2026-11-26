<template>
	<web-view
		v-if="url"
		class="web-view"
		:webview-styles="webviewStyles"
		:src="url"
		:style="{ width: windowW + 'px', height: windowH + 'px' }"
	></web-view>
	<view v-else class="plugin-webview-status">
		<text>{{ errorText || $t(`正在打开插件`) }}</text>
	</view>
</template>

<script setup>
	import { ref } from 'vue';
	import { onLoad, onShow } from '@dcloudio/uni-app';
	import { createWebviewTicket } from '@/api/webview.js';
	import { HTTP_REQUEST_URL } from '@/config/app.js';
	import { checkLogin, toLogin } from '@/libs/login.js';
	import i18n from '@/utils/lang.js';

	const windowH = ref(0);
	const windowW = ref(0);
	const webviewStyles = ref({
		progress: {
			color: 'transparent',
		},
	});
	const path = ref('');
	const url = ref('');
	const errorText = ref('');
	const opening = ref(false);

	function h5Origin() {
		// #ifdef H5
		return window.location.origin;
		// #endif
		// #ifndef H5
		return HTTP_REQUEST_URL;
		// #endif
	}

	function readPath(value) {
		if (!value) return '';
		try {
			return decodeURIComponent(value);
		} catch (e) {
			return value;
		}
	}

	function isPluginPath(value) {
		const pathname = value.split('?')[0];
		return /^\/pages-modules\/[a-z0-9_]+(?:\/[a-z0-9_]+)+$/.test(pathname);
	}

	async function openPlugin() {
		if (url.value || opening.value) return;
		if (!isPluginPath(path.value)) {
			errorText.value = i18n.t(`插件页面路径不正确`);
			return;
		}
		if (!checkLogin()) {
			toLogin();
			return;
		}
		opening.value = true;
		errorText.value = '';
		try {
			const res = await createWebviewTicket(path.value);
			const ticket = res.data.ticket;
			const base = String(h5Origin() || '').replace(/\/$/, '');
			const join = path.value.includes('?') ? '&' : '?';
			url.value = `${base}${path.value}${join}ticket=${encodeURIComponent(ticket)}`;
		} catch (e) {
			errorText.value = typeof e === 'string' ? e : i18n.t(`打开失败`);
		} finally {
			opening.value = false;
		}
	}

	onLoad((option) => {
		path.value = readPath(option.path || '');
		try {
			const res = uni.getWindowInfo();
			windowW.value = res.windowWidth;
			windowH.value = res.windowHeight;
		} catch (e) {
			// 尺寸取不到时 webview 仍按默认铺满
		}
	});

	onShow(() => {
		openPlugin();
	});
</script>

<style scoped>
	.plugin-webview-status {
		padding: 48rpx 32rpx;
		color: #666;
		font-size: 28rpx;
	}
</style>
