<template>
	<view :style="colorStyle">
		<view style="touch-action: none;">
			<view class="home" style="position:fixed;" :style="{ top: top + 'px'}" id="right-nav"
				@touchmove.stop.prevent="setTouchMove">
				<view class="homeCon bg-color" :class="homeActive === true ? 'on' : ''" v-if="homeActive">
					<navigator :render-link="false" hover-class='none' open-type="switchTab" url='/pages/index/index'
						class='iconfont icon-shouye-xianxing'>
					</navigator>
					<navigator :render-link="false" hover-class='none' open-type="switchTab" url='/pages/order_addcart/order_addcart'
						class='iconfont icon-caigou-xianxing'></navigator>
					<navigator :render-link="false" hover-class='none' open-type="switchTab" url='/pages/user/index'
						class='iconfont icon-yonghu1'></navigator>
				</view>
				<view @click="open" class="pictrueBox">
					<view class="pictrue">
						<image :src="
						  homeActive === true
						    ? imgHost + '/statics/images/close.gif'
						    : imgHost + '/statics/images/open.gif'
						" class="image" />
					</view>
				</view>
			</view>
		</view>
	</view>
</template>
<script setup>
	import { ref, onBeforeUnmount } from 'vue';
	import { storeToRefs } from 'pinia';
	import { useAppStore } from '@/store/modules/app';
	import { useColorTheme } from '@/composables/useColorTheme';
	import {HTTP_REQUEST_URL} from '@/config/app';

	defineOptions({ name: "Home" });

	const { colorStyle } = useColorTheme();

	const appStore = useAppStore();
	const { homeActive } = storeToRefs(appStore);

	const top = ref("545");
	// 可拖动的最大 Y 坐标，created 中按屏幕高度动态计算
	const maxTop = ref(545);
	const imgHost = ref(HTTP_REQUEST_URL);

	function setTouchMove(e) {
		// 上边界 66 避开状态栏/导航栏，下边界用屏幕高度动态计算
		if (e.touches[0].clientY < maxTop.value && e.touches[0].clientY > 66) {
			top.value = e.touches[0].clientY
		}
	}
	function open() {
		homeActive.value ?
			appStore.CLOSE_HOME() :
			appStore.OPEN_HOME();
	}

	// created：根据屏幕实际高度动态计算可拖动范围，避免大屏手机下半屏无法拖到
	try {
		var winHeight = uni.getSystemInfoSync().windowHeight;
		// 预留底部 120px 给安全区/tab 栏/组件自身高度
		maxTop.value = Math.max(545, winHeight - 120);
		// 默认位置放在屏幕约 70% 处，更贴近拇指操作区；小屏保底 545 不变
		top.value = Math.min(maxTop.value, Math.max(545, Math.round(winHeight * 0.7)));
	} catch (e) {}

	onBeforeUnmount(() => {
		appStore.CLOSE_HOME()
	})
</script>

<style scoped>
	.pictrueBox {
		width: 130rpx;
		height: 120rpx;
	}

	/*返回主页按钮*/
	.home {
		position: fixed;
		color: white;
		text-align: center;
		z-index: 999;
		right: 15rpx;
		display: flex;
	}

	.home .homeCon {
		border-radius: 50rpx;
		opacity: 0;
		height: 0;
		color: var(--view-theme);
		width: 0;
	}

	.home .homeCon.on {
		opacity: 1;
		animation: bounceInRight 0.5s cubic-bezier(0.215, 0.610, 0.355, 1.000);
		width: 300rpx;
		height: 86rpx;
		margin-bottom: 20rpx;
		display: flex;
		justify-content: center;
		align-items: center;
		background: var(--view-theme) !important;
		opacity: 0.8;
		backdrop-filter: blur(10px);
	}

	.home .homeCon .iconfont {
		font-size: 48rpx;
		color: #fff;
		display: inline-block;
		margin: 0 auto;
	}

	.home .pictrue {
		width: 86rpx;
		height: 86rpx;
		border-radius: 50%;
		margin: 0 auto;
		background-color: var(--view-theme);
	}

	.home .pictrue .image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
		transform: rotate(90deg);
		ms-transform: rotate(90deg);
		moz-transform: rotate(90deg);
		webkit-transform: rotate(90deg);
		o-transform: rotate(90deg);
	}
</style>
