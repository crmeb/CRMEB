<template>
	<view class="load" v-if="status">
		{{$t(`正在加载中`)}}
	</view>
</template>

<script setup>
	import { ref, onMounted } from 'vue';

	const status = ref(false);

	onMounted(() => {
		status.value = uni.getStorageSync('loadStatus')
		uni.$once('loadClose',()=>{
			status.value = false
		})
	})
</script>

<style lang="scss">
.load{
	z-index: 99999;
	position: fixed;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	background-color: #fff;
}
</style>
