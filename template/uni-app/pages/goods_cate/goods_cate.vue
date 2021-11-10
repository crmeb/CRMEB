<template>
	<view :style="colorStyle">
		<goodsCate1 v-show="category==1" ref="classOne"></goodsCate1>
		<goodsCate2 v-show="category==2" ref="classTwo"></goodsCate2>
		<goodsCate3 v-show="category==3" ref="classThree"></goodsCate3>
		<tabBar v-show="category == 1" :pagePath="'/pages/goods_cate/goods_cate'"></tabBar>
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
	import tabBar from "@/pages/index/components/tabBar.vue"
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
				category:''
			}
		},
		onLoad() {
		},
		onReady() {
		
		},
		onShow() {
			this.classStyle();
		},
		methods: {
			classStyle(){
				colorChange('category').then(res=>{
					let status = res.data.status;
					this.category = status
					if(status==2){
						if(this.isLogin){
							this.$refs.classTwo.getCartNum();
							this.$refs.classTwo.getCartList(1);
						}
						this.$refs.classTwo.getAllCategory()
					}
					if(status==3){
						if(this.isLogin){
							this.$refs.classThree.getCartNum();
							this.$refs.classThree.getCartList(1);
						}
						this.$refs.classThree.getAllCategory()
					}
					if(status==2 || status==3){
						uni.hideTabBar()
					}else{
						this.$refs.classOne.getNav();
						// uni.showTabBar()
					}
				})
			}
		},
		onReachBottom: function() {
			if(this.category==2){
				this.$refs.classTwo.productslist();
			}
			if(this.category==3){
				this.$refs.classThree.productslist();
			}
		}
	}
</script>
<style scoped lang="scss">
	/deep/.mask{
		z-index: 99;
	}
</style>
