<template>
	<view>
		<view :style="{height:footHeight+'px'}"></view>
		<view v-if="newData.bgColor">
			<view class="page-footer" id="target" :style="{'background-color':newData.bgColor.color[0].item}">
				<view class="foot-item" v-for="(item,index) in newData.menuList" :key="index" @click="goRouter(item)">
					<block v-if="item.link == activeRouter">
						<image :src="item.imgList[0]"></image>
						<view class="txt" :style="{color:newData.activeTxtColor.color[0].item}">{{item.name}}</view>
					</block>
					<block v-else>
						<image :src="item.imgList[1]"></image>
						<view class="txt" :style="{color:newData.txtColor.color[0].item}">{{item.name}}</view>
					</block>
					<div class="count-num"
						v-if="item.link === '/pages/order_addcart/order_addcart' && $store.state.indexData.cartNum && $store.state.indexData.cartNum>0">
						{{$store.state.indexData.cartNum}}
					</div>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	import {
		mapState,
		mapGetters
	} from "vuex"
	import {
		getNavigation
	} from '@/api/public.js'
	import {
		getCartCounts,
	} from '@/api/order.js';
	export default {
		name: 'pageFooter',
		props: {
			noTop: {
				type: Boolean,
				default: true
			},
			status: {
				type: Number | String,
				default: 1
			},
			countNum: {
				type: Number | String,
				default: 0
			},
		},
		computed: {
			...mapState({
				configData: state => state.app.pageFooter
			})
		},
		computed: mapGetters(['isLogin', 'cartNum']),
		watch: {
			configData: {
				handler(nVal, oVal) {
					let self = this
					const query = uni.createSelectorQuery().in(this);
					this.newData = nVal
					this.$nextTick(() => {
						query.select('#target').boundingClientRect(data => {
							uni.$emit('footHeight', data.height)
							if (data) {
								self.footHeight = data.height + 50
							}
						}).exec();
					})
				},
				deep: true
			},
			cartNum(a, b) {
				this.$store.commit('indexData/setCartNum', a + '')
				if (a > 0) {
					uni.setTabBarBadge({
						index: Number(uni.getStorageSync('FOOTER_ADDCART')),
						text: a + ''
					})
				} else {
					wx.hideTabBarRedDot({
						index: Number(uni.getStorageSync('FOOTER_ADDCART'))
					})
				}
			}
		},
		created() {
			let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
			let curRoute = routes[routes.length - 1].route //获取当前页面路由
			this.activeRouter = '/' + curRoute
		},
		onShow() {
			getNavigation().then(res => {
				uni.setStorageSync('pageFoot', res.data)
				this.$store.commit('FOOT_UPLOAD', res.data)
				this.newData = res.data
				if (this.newData.status && this.newData.status.status) {
					// uni.hideTabBar()
				} else {
					uni.showTabBar()
				}
			})
		},
		mounted() {
			let that = this
			this.newData = this.$store.state.app.pageFooter
			if (this.newData.status && this.newData.status.status) {
				uni.hideTabBar()
			} else {
				uni.showTabBar()
			}
			console.log(this.newData)
			if (this.isLogin) {
				this.getCartNum()
			}
		},
		data() {
			return {
				newData: {},
				activeRouter: '/',
				footHeight: 0
			}
		},
		methods: {
			goRouter(item) {
				var pages = getCurrentPages();
				if (pages.length) {
					var page = (pages[pages.length - 1]).$page.fullPath;
				} else {
					page = ''
				}
				if (item.link == page) return
				uni.switchTab({
					url: item.link,
					fail(err) {
						uni.redirectTo({
							url: item.link
						})
					}
				})
			},
			getCartNum: function() {
				let that = this;
				getCartCounts().then(res => {
					that.cartCount = res.data.count;
					this.$store.commit('indexData/setCartNum', res.data.count > 99 ? '...' : res.data.count)
				});
			},
		}
	}
</script>

<style lang="scss" scoped>
	.page-footer {
		position: fixed;
		bottom: 0;
		z-index: 30;
		display: flex;
		align-items: center;
		justify-content: space-around;
		width: 100%;
		height: calc(98rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		height: calc(98rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		box-sizing: border-box;
		border-top: solid 1rpx #F3F3F3;
		background-color: #fff;
		box-shadow: 0px 0px 17rpx 1rpx rgba(206, 206, 206, 0.32);
		padding-bottom: constant(safe-area-inset-bottom); ///兼容 IOS<11.2/
		padding-bottom: env(safe-area-inset-bottom); ///兼容 IOS>11.2/

		.foot-item {
			display: flex;
			width: max-content;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			position: relative;

			.count-num {
				position: absolute;
				display: flex;
				justify-content: center;
				align-items: center;
				width: 40rpx;
				height: 40rpx;
				top: 0rpx;
				right: -15rpx;
				color: #fff;
				font-size: 20rpx;
				background-color: #FD502F;
				border-radius: 50%;
				padding: 4rpx;
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


			&.active {}
		}
	}
</style>
