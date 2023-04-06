<template>
	<view :style="colorStyle">
		<goodsCate1 v-if="category==1" ref="classOne" :isNew="isNew"></goodsCate1>
		<goodsCate2 v-if="category==2" ref="classTwo" :isNew="isNew" @jumpIndex="jumpIndex"></goodsCate2>
		<goodsCate3 v-if="category==3" ref="classThree" :isNew="isNew" @jumpIndex="jumpIndex"></goodsCate3>
	</view>
</template>

<script>
	import colors from "@/mixins/color";
	import goodsCate1 from './goods_cate1';
	import goodsCate2 from './goods_cate2';
	import goodsCate3 from './goods_cate3';
	import {
		colorChange
	} from '@/api/api.js';
	import {
		mapGetters
	} from 'vuex';
	import {
		getCategoryVersion
	} from '@/api/public.js'
	import tabBar from "@/pages/index/visualization/components/tabBar.vue"
	export default {
		computed: mapGetters(['isLogin', 'uid']),
		components: {
			goodsCate1,
			goodsCate2,
			goodsCate3,
			tabBar
		},
		mixins: [colors],
		data() {
			return {
				category: '',
				is_diy: uni.getStorageSync('is_diy'),
				status: 0,
				version: '',
				isNew: false
			}
		},
		onLoad() {},
		onReady() {},
		onShow() {
			this.getCategoryVersion()
		},
		methods: {
			getCategoryVersion() {
				getCategoryVersion().then(res => {
					if (!uni.getStorageSync('CAT_VERSION') || res.data.version != uni.getStorageSync(
							'CAT_VERSION')) {
						this.isNew = !this.isNew
						uni.setStorageSync('CAT_VERSION', res.data.version)
					} else {
						// this.isNew = false
					}
					this.classStyle();
				})
			},
			jumpIndex() {
				// uni.reLaunch({
				// 	url: '/pages/index/index'
				// })
			},
			classStyle() {
				colorChange('category').then(res => {
					let status = res.data.status;
					this.category = status
					uni.setStorageSync('is_diy', res.data.is_diy)
					this.status = res.data.status
					this.$nextTick(e => {
						if (this.status == 2 || this.status == 3) {
							uni.hideTabBar();
						} else if (this.status == 1) {
							this.$refs.classOne.is_diy = res.data.is_diy
							this.$refs.classOne.getNav();
						}
						if (status == 2) {
							if (this.isLogin) {
								this.$refs.classTwo.getCartNum();
								this.$refs.classTwo.getCartList(1);
							}
							this.$refs.classTwo.getAllCategory()
						}
						if (status == 3) {
							if (this.isLogin) {
								this.$refs.classThree.getCartNum();
								this.$refs.classThree.getCartList(1);
							}
							this.$refs.classThree.getAllCategory()
						}
						if (status == 2 || status == 3) {
							uni.hideTabBar()
						} else {
							if (!this.is_diy) {
								uni.hideTabBar()
							} else {}
							this.$refs.classOne.getNav();
						}
					})

				})
			}
		},
		onReachBottom: function() {
			if (this.category == 2) {
				this.$refs.classTwo.productslist();
			}
			if (this.category == 3) {
				this.$refs.classThree.productslist();
			}
		}
	}
</script>
<style scoped lang="scss">
	/deep/.mask {
		z-index: 99;
	}
</style>
