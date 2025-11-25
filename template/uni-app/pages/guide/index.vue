<template>
	<view class="main">
		<guide v-if="guidePages" :advData="advData" :time="advData.time"></guide>
	</view>
</template>

<script>
import guide from '@/components/guide/index.vue';
import Cache from '@/utils/cache';
import { getOpenAdv } from '@/api/api.js';
export default {
	components: {
		guide
	},
	data() {
		return {
			guidePages: false,
			advData: [],
			indexUrl: '/pages/index/index'
		};
	},
	onLoad(options) {
		if (options.spid) {
			this.indexUrl = this.indexUrl + '?spid=' + options.spid;
		}
	},
	onShow() {
		this.loadExecution();
	},
	methods: {
		loadExecution() {
			const tagDate = uni.getStorageSync('guideDate') || '',
				nowDate = new Date().toLocaleDateString();
			if (tagDate === nowDate) {
				uni.switchTab({
					url: this.indexUrl
				});
				return;
			}
			getOpenAdv()
				.then((res) => {
					if (res.data.status == 0 || res.data.value.length == 0) {
						uni.switchTab({
							url: this.indexUrl
						});
					} else if (res.data.status && (res.data.value.length || res.data.video_link)) {
						this.advData = res.data;
						uni.setStorageSync('guideDate', new Date().toLocaleDateString());
						this.guidePages = true;
					}
				})
				.catch((err) => {
					uni.switchTab({
						url: this.indexUrl
					});
				});
		}
	},
	onHide() {
		this.guidePages = false;
	}
};
</script>

<style>
page,
.main {
	width: 100%;
	height: 100%;
}
</style>
