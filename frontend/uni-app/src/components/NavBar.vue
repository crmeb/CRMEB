<template>
	<view class="navbar">
		<view class="content" :style="{ background: isScrolling ? '#fff' : bagColor,'padding-top': sysHeight + 'px' }">
			<view class="flex-center h-80 px-20">
				<view class="w-40 h-40 flex-center">
					<view
						v-show="showBack"
						@click="back"
						class="iconfont icon-fanhui"
						:style="{ color: `${iconColor}`, fontSize: `${iconSize}`, fontWeight: `${iconWeight}` }"
					></view>
				</view>
				<view class="flex-1 text-center"
				:style="{ color: `${textColor}`, fontSize: `${textSize}`, fontWeight: `${textWeight}` }"
				v-if="!custom">{{ titleText }}</view>
				<view class="flex-1" v-else>
					<slot></slot>
				</view>
				<view class="w-40 h-40 flex-center">
					<view v-show="showRight" class="right-icon"></view>
				</view>
			</view>
		</view>
		<view :style="{'padding-top': sysHeight + 'px'}" v-if="showEmpty">
			<view class="h-80"></view>
		</view>
	</view>
</template>

<script setup>
import { ref } from 'vue';

defineOptions({ name: 'navbar' });

let statusBarHeight = uni.getWindowInfo().statusBarHeight;

defineProps({
	custom:{
		type: Boolean,
		default: false
	},
	fixed: {
		type: Boolean,
		default: true
	},
	showEmpty:{
		type: Boolean,
		default: true
	},
	// 滚动至下部
	isScrolling: {
		type: Boolean,
		default: false
	},
	// 是否显示返回icon
	showBack: {
		type: Boolean,
		default: false
	},
	// Title
	titleText: {
		type: String,
		default: ''
	},
	// icon 颜色
	iconColor: {
		type: String,
		default: '#000000'
	},
	// icon 字号
	iconSize: {
		type: String,
		default: '40rpx'
	},
	// icon 字重
	iconWeight: {
		type: String,
		default: 'bold'
	},
	// Title 颜色
	textColor: {
		type: String,
		default: '#333'
	},
	// Title 字号
	textSize: {
		type: String,
		default: '34rpx'
	},
	// Title 字重
	textWeight: {
		type: String,
		default: '500'
	},
	// 背景色
	bagColor: {
		type: String,
		default: 'transparent'
	},
	showRight:{
		type: Boolean,
		default: false
	}
});

const sysHeight = ref(statusBarHeight);

function back() {
	let pages = getCurrentPages(); // 获取当前打开过的页面路由数，
	if (pages.length > 1) {
		uni.navigateBack()
	} else {
		uni.switchTab({
			url: '/pages/index/index'
		});
	}
}
</script>

<style lang="scss">
.navbar {
	position: relative;
	color: #333;
	.content {
		position: fixed;
		top: 0;
		right: 0;
		left: 0;
		z-index: 998;
		background-color: var(--view-theme);
		font-weight: 500;
		font-size: 34rpx;
		color: #ffffff;
	}
}
</style>
