<template>
	<view v-if="newData && ((isSpe && newData.status.status) || !isSpe)">
		<view class="page-footer" id="target">
			<view class="foot-item" v-for="(item, index) in newData.menuList" :key="index" @click="goRouter(item)" :style="{ 'background-color': newData.bgColor.color[0].item }">
				<block v-if="item.link == activityTab">
					<image :src="item.imgList[0]" class="active"></image>
					<view class="txt" :style="{ color: newData.activeTxtColor.color[0].item }">{{ $t(item.name) }}</view>
				</block>
				<block v-else>
					<image :src="item.imgList[1]"></image>
					<view class="txt" :style="{ color: newData.txtColor.color[0].item }">{{ $t(item.name) }}</view>
				</block>
				<div class="count-num" v-if="item.link === '/pages/order_addcart/order_addcart' && cartNum > 0">
					{{ cartNum > 99 ? '99+' : cartNum }}
				</div>
			</view>
		</view>
	</view>
</template>

<script>
import { mapState, mapGetters } from 'vuex';
import { getNavigation } from '@/api/public.js';
import { getCartCounts } from '@/api/order.js';
export default {
	name: 'pageFooter',
	props: {
		status: {
			type: Number | String,
			default: 1
		},
		countNum: {
			type: Number | String,
			default: 0
		},
		isSpe: {
			type: Number,
			default: 0
		},
		dataConfig: {
			type: Object,
			default: () => {}
		}
	},
	data() {
		return {
			newData: undefined,
			footHeight: 0,
			isShow: false // 弹出动画
		};
	},
	computed: {},
	computed: mapGetters(['isLogin', 'cartNum', 'activityTab']),
	watch: {
		activityTab: {
			handler(nVal, oVal) {},
			deep: true
		},
		configData: {
			handler(nVal, oVal) {
				let self = this;
				const query = uni.createSelectorQuery().in(this);
				console.log(nVal, 'nVal');
				this.newData = nVal;
				this.$nextTick(() => {
					query
						.select('#target')
						.boundingClientRect((data) => {
							uni.$emit('footHeight', data.height);
							if (data) {
								self.footHeight = data.height + 50;
							}
						})
						.exec();
				});
			},
			deep: true
		}
	},
	created() {
		let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
		let curRoute = routes[routes.length - 1].route; //获取当前页面路由
		this.$store.commit('ACTIVITYTAB', '/' + curRoute);
		uni.$on('uploadFooter', () => {
			let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
			let curRoute = routes[routes.length - 1].route; //获取当前页面路由
			this.$store.commit('ACTIVITYTAB', '/' + curRoute);
		});
	},
	onShow() {},
	mounted() {
		if (this.isSpe) {
			this.newData = this.dataConfig;
		} else {
			getNavigation().then((res) => {
				uni.setStorageSync('pageFoot', res.data);
				this.$store.commit('FOOT_UPLOAD', res.data);
				this.newData = res.data;
			});
			let that = this;
			uni.hideTabBar();
			this.newData = this.$store.state.app.pageFooter;
			if (this.isLogin) {
				this.getCartNum();
			}
		}
	},
	onHide() {
		uni.$off(['uploadFooter']);
	},
	methods: {
		goRouter(item) {
			var pages = getCurrentPages();
			var page = pages[pages.length - 1].route;
			this.$store.commit('ACTIVITYTAB', item.link);
			if (item.link == '/' + page) return;
			uni.switchTab({
				url: item.link,
				fail(err) {
					uni.redirectTo({
						url: item.link
					});
				}
			});
		},
		getCartNum: function () {
			let that = this;
			getCartCounts().then((res) => {
				that.cartCount = res.data.count;
				this.$store.commit('indexData/setCartNum', res.data.count > 99 ? '...' : res.data.count);
			});
		}
	}
};
</script>

<style lang="scss" scoped>
.page-footer {
	position: fixed;
	left: 0;
	bottom: 0;
	z-index: 999;
	display: flex;
	align-items: center;
	justify-content: space-around;
	flex-wrap: nowrap;
	width: 100%;
	height: 98rpx;
	height: calc(98rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
	height: calc(98rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
	box-sizing: border-box;
	border-top: solid 1rpx #f3f3f3;
	backdrop-filter: blur(10px);
	background-color: #fff;
	box-shadow: 0px 0px 17rpx 1rpx rgba(206, 206, 206, 0.32);
	padding-bottom: constant(safe-area-inset-bottom); ///兼容 IOS<11.2/
	padding-bottom: env(safe-area-inset-bottom); ///兼容 IOS>11.2/
	// transform: translate3d(0, 100%, 0);
	// transition: all .3s cubic-bezier(.25, .5, .5, .9);

	.foot-item {
		display: flex;
		width: max-content;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		position: relative;
		width: 100%;
		height: 100%;

		.count-num {
			position: absolute;
			display: flex;

			justify-content: center;
			align-items: center;
			width: 40rpx;
			height: 40rpx;
			top: 0rpx;
			right: calc(50% - 55rpx);
			color: #fff;
			font-size: 20rpx;
			background-color: #fd502f;
			border-radius: 50%;
			padding: 4rpx;
		}

		.active {
			animation: mymove 1s 1;
		}

		@keyframes mymove {
			0% {
				transform: scale(1);
				/*开始为原始大小*/
			}

			10% {
				transform: scale(0.8);
			}

			30% {
				transform: scale(1.1);
				/*放大1.1倍*/
			}

			50% {
				transform: scale(0.9);
				/*放大1.1倍*/
			}

			70% {
				transform: scale(1.05);
			}

			90% {
				transform: scale(1);
			}
		}
	}

	.foot-item image {
		height: 50rpx;
		width: 50rpx;
		text-align: center;
		margin: 0 auto;
	}

	.foot-item .txt {
		font-size: 24rpx;
	}
}
</style>
