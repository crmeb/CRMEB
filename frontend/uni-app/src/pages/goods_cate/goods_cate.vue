<template>
	<view :style="colorStyle">
		<goodsCate1 v-if="category == 1" ref="classOneRef" :isNew="isNew"></goodsCate1>
		<goodsCate2 v-if="category == 2" ref="classTwoRef" :isNew="isNew" @jumpIndex="jumpIndex"></goodsCate2>
		<goodsCate3 v-if="category == 3" ref="classThreeRef" :isNew="isNew" @jumpIndex="jumpIndex"></goodsCate3>
		<pageFooter v-if="category == 1" @newDataStatus="newDataStatus" v-show="showBar"></pageFooter>
	</view>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { onLoad, onReady, onShow, onPageScroll, onReachBottom } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import goodsCate1 from './goods_cate1';
import goodsCate2 from './goods_cate2';
import goodsCate3 from './goods_cate3';
import { getThemeInfo } from '@/api/api.js';
import { useAppStore } from '@/store/modules/app';
import { getCategoryVersion } from '@/api/public.js';
import pageFooter from '@/components/pageFooter/index.vue';
import { useColorTheme } from '@/composables/useColorTheme';

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin, uid } = storeToRefs(appStore);

const category = ref('');
const is_diy = ref(uni.getStorageSync('is_diy'));
const status = ref(0);
const version = ref('');
const isNew = ref(false);
const isFooter = ref(false);
const showBar = ref(false);
// 原 newDataStatus 里 this.pdHeight 未在 data 声明（Vue2 非响应式写法），此处补为 ref
const pdHeight = ref(0);

const classOneRef = ref(null);
const classTwoRef = ref(null);
const classThreeRef = ref(null);

function newDataStatus(val, num) {
	isFooter.value = val ? true : false;
	showBar.value = val ? true : false;
	pdHeight.value = num;
}
function getCategoryVersionData() {
	uni.$emit('uploadFooter');
	getCategoryVersion().then((res) => {
		if (!uni.getStorageSync('CAT_VERSION') || res.data.version != uni.getStorageSync('CAT_VERSION')) {
			uni.setStorageSync('CAT_VERSION', res.data.version);
			uni.$emit('uploadCatData');
		}
		classStyle();
	});
}
function jumpIndex() {
	uni.reLaunch({
		url: '/pages/index/index'
	});
}
function classStyle() {
	let previewThemeId = uni.getStorageSync('previewThemeId');
	let data = {};
	if (previewThemeId) data.theme_id = previewThemeId;
	getThemeInfo('category', data).then((res) => {
		let status = res.data.status;
		category.value = status;
		uni.setStorageSync('is_diy', 1);
		nextTick((e) => {
			if (status == 2 || status == 3) {
				uni.hideTabBar();
			} else if (status == 1 && classOneRef.value) {
				// goodsCate1 由 v-if 异步挂载，小程序端 ref 可能暂时为空。
				classOneRef.value.is_diy = 1;
				if (!is_diy.value) {
					uni.hideTabBar();
				} else {
					classOneRef.value.getNav();
				}
			}
		});
	});
}

onLoad(() => {});
onReady(() => {});
onShow(() => {
	getCategoryVersionData();
});
onPageScroll((e) => {
	console.log(e);
	uni.$emit('scroll');
});
onReachBottom(function () {
	if (category.value == 2) {
		classTwoRef.value?.productslist();
	}
	if (category.value == 3) {
		classThreeRef.value?.productslist();
	}
});
</script>
<style scoped lang="scss">
:deep(.mask){
		z-index: 99;
	}

	::-webkit-scrollbar {
		width: 0;
		height: 0;
		color: transparent;
		display: none;
	}
</style>
