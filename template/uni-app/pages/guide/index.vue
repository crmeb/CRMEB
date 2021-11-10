<template>
	<view class="main">
		<guide v-if="guidePages" :advList="advList"></guide>
	</view>
</template>

<script>
	import guide from '@/components/guide/index.vue'
	import {
		getOpenAdv
	} from '@/api/api.js'
	export default {
		components: {
			guide
		},
		data() {
			return {
				guidePages: false,
				advList: []
			}
		},
		onLoad() {
			this.loadExecution()
		},
		methods: {
			loadExecution() {
				const tagDate = uni.getStorageSync('guideDate') || '',
					nowDate = new Date().toLocaleDateString();
				if (tagDate === nowDate) {
					uni.switchTab({
						url: '/pages/index/index'
					});
					return
				}
				getOpenAdv().then(res => {
					console.log(res)
					if (!res.data.length) {
						uni.switchTab({
							url: '/pages/index/index'
						});
					} else {
						this.advList = res.data
						uni.setStorageSync('guideDate', new Date().toLocaleDateString());
						this.guidePages = true
					}
				}).catch(err=>{
					
				})
			}
		}
	}
</script>

<style>
	page,
	.main {
		width: 100%;
		height: 100%;
	}
</style>
