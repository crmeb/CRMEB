<template>
	<view :style="colorStyle">
		<goodsCate1 v-if="category == 1" ref="classOne" :isNew="isNew"></goodsCate1>
		<goodsCate2 v-if="category == 2" ref="classTwo" :isNew="isNew" @jumpIndex="jumpIndex"></goodsCate2>
		<goodsCate3 v-if="category == 3" ref="classThree" :isNew="isNew" @jumpIndex="jumpIndex"></goodsCate3>
		<pageFooter v-if="category == 1" @newDataStatus="newDataStatus" v-show="showBar"></pageFooter>
	</view>
</template>

<script>
import colors from '@/mixins/color';
import goodsCate1 from './goods_cate1';
import goodsCate2 from './goods_cate2';
import goodsCate3 from './goods_cate3';
import { colorChange } from '@/api/api.js';
import { mapGetters } from 'vuex';
import { getCategoryVersion } from '@/api/public.js';
import pageFooter from '@/components/pageFooter/index.vue';
export default {
	computed: mapGetters(['isLogin', 'uid']),
	components: {
		goodsCate1,
		goodsCate2,
		goodsCate3,
		pageFooter
	},
	mixins: [colors],
	data() {
		return {
			category: '',
			is_diy: uni.getStorageSync('is_diy'),
			status: 0,
			version: '',
			isNew: false,
			isFooter: false,
			showBar: false
		};
	},
	onLoad() {},
	onReady() {},
	onShow() {
		this.getCategoryVersion();
	},
	methods: {
		newDataStatus(val, num) {
			this.isFooter = val ? true : false;
			this.showBar = val ? true : false;
			this.pdHeight = num;
		},
		getCategoryVersion() {
			uni.$emit('uploadFooter');
			getCategoryVersion().then((res) => {
				if (!uni.getStorageSync('CAT_VERSION') || res.data.version != uni.getStorageSync('CAT_VERSION')) {
					uni.setStorageSync('CAT_VERSION', res.data.version);
					uni.$emit('uploadCatData');
				}
				this.classStyle();
			});
		},
		jumpIndex() {
			uni.reLaunch({
				url: '/pages/index/index'
			})
		},
		classStyle() {
			colorChange('category').then((res) => {
				let status = res.data.status;
				this.category = status;
				uni.setStorageSync('is_diy', res.data.is_diy);
				this.$nextTick((e) => {
					if (status == 2 || status == 3) {
						uni.hideTabBar();
					} else {
						this.$refs.classOne.is_diy = res.data.is_diy;
						if (!this.is_diy) {
							uni.hideTabBar();
						} else {
							this.$refs.classOne.getNav();
						}
					}
				});
			});
		}
	},
	onReachBottom: function () {
		if (this.category == 2) {
			this.$refs.classTwo.productslist();
		}
		if (this.category == 3) {
			this.$refs.classThree.productslist();
		}
	}
};
</script>
<style scoped lang="scss">
/deep/.mask {
	z-index: 99;
}
::-webkit-scrollbar {
	width: 0;
	height: 0;
	color: transparent;
	display: none;
}
</style>
