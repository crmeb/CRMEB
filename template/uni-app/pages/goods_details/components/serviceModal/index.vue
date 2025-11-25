<script>
// 服务保障
import baseDrawer from '@/components/tuiDrawer/tui-drawer.vue';
export default {
	props: {
		ensureInfo: {
			type: Array,
			default: () => []
		}
	},
	components: {
		baseDrawer
	},
	data() {
		return {
			isShow: false
		};
	},
	methods: {
		closeDrawer() {
			this.isShow = false;
		}
	}
};
</script>
<template>
	<base-drawer mode="bottom" :visible="isShow" background-color="transparent" mask maskClosable @close="closeDrawer">
		<view class="w-full bg--w111-fff rd-t-40rpx py-32">
			<view class="text-center fs-32 text--w111-333 fw-500 mb-34">{{ $t(`服务保障`) }}</view>
			<scroll-view scroll-y="true" class="h-400">
				<view class="px-32">
					<view class="mb-38" v-for="(item, index) in ensureInfo" :key="index">
						<view class="flex">
							<image class="w-34 h-34 mt-3" :src="item.image"></image>
							<view class="flex-1">
								<text class="pl-12 text--w111-333 fs-28 fw-500">{{ item.title }}</text>
								<view class="mt-6 pl-12 fs-22 text--w111-999">{{ item.content }}</view>
							</view>
						</view>
					</view>
				</view>
			</scroll-view>
			<view class="mx-20 pb-safe">
				<view class="mt-52 h-72 flex-center rd-36px bg-color fs-26 text--w111-fff" @click="closeDrawer">{{ $t(`知道了`) }}</view>
			</view>
		</view>
	</base-drawer>
</template>
