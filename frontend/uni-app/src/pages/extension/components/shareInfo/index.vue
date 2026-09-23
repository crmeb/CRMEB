<template>
	<view v-if="shareInfoStatus" class="poster-first">
		<view class="mask-share">
			<image :src="imgHost + '/statics/images/share-info.png'" @click="shareInfoClose"
				@touchmove.stop.prevent="false"></image>
		</view>
	</view>
</template>

<script setup>
import { HTTP_REQUEST_URL } from '@/config/app';

defineOptions({ name: 'shareInfo' });

const props = defineProps({
	shareInfoStatus: {
		type: Boolean,
		default: false,
	}
});

// ---------- data ----------
const imgHost = HTTP_REQUEST_URL;

// ---------- methods ----------
const emit = defineEmits(["setShareInfoStatus"]);
function shareInfoClose() {
	emit("setShareInfoStatus");
}
</script>

<style scoped lang="scss">
	.poster-first {
		overscroll-behavior: contain;
	}

	.mask-share {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		z-index: 99;
	}

	.mask-share image {
		width: 100%;
		height: 100%;
	}
</style>
