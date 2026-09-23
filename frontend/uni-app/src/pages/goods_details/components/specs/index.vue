<template>
	<!-- 产品参数 -->
	<base-drawer mode="bottom" :visible="isShow" background-color="transparent" mask maskClosable @close="closeSpecs">
		<view class="w-full bg--w111-fff rd-t-40rpx py-32">
			<view class="text-center fs-32 text--w111-333 fw-500 mb-34">{{ $t(`参数`) }}</view>
			<scroll-view scroll-y="true" class="h-400">
				<view class="px-32 scroll-content">
					<view class="item flex break_word" v-for="(item, index) in specsInfo" :key="index">
						<view class="w-160 text--w111-999 mr-12">{{ item.name }}</view>
						<view class="flex-1">{{ item.value }}</view>
					</view>
				</view>
			</scroll-view>
			<view class="mx-20 pb-safe">
				<view class="mt-52 h-72 flex-center rd-36px bg-color fs-26 text--w111-fff" @click="closeSpecs">{{ $t(`知道了`) }}</view>
			</view>
		</view>
	</base-drawer>
</template>

<script setup>
import { ref } from 'vue';
import baseDrawer from '@/components/tuiDrawer/tui-drawer.vue';

defineProps({
	specsInfo: {
		type: Array,
		default: () => []
	}
});

// 父组件 goods_details 通过 modalRefs 映射表以 `xxxRef.value.isShow = true` 打开弹窗
const isShow = ref(false);

function closeSpecs() {
	isShow.value = false;
}

defineExpose({
	isShow
});
</script>

<style scoped lang="scss">
.item ~ .item {
	margin-top: 40rpx;
}
</style>
