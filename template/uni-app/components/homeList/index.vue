<template>
	<!-- #ifdef APP-PLUS -->
	<view class="animated dialog_nav" :style="{ top: (navH+65) + 'rpx', marginTop: sysHeight}"
		:class="[goodList?'dialogIndex':'',currentPage?'':'']" v-show="currentPage">
		<!-- #endif -->
		<!-- #ifndef APP-PLUS -->
		<view class="animated dialog_nav" :style="{ top: (navH+15) + 'rpx' }"
			:class="[goodList?'dialogIndex':'',goodsShow?'dialogGoods':'',currentPage?'':'']" v-show="currentPage">
			<!-- #endif -->
			<view class="dialog_nav_item" :class="item.after" v-for="(item,index) in selectNavList" :key="index"
				@click="linkPage(item.url)">
				<text class="iconfont" :class="item.icon"></text>
				<text class="pl-20">{{item.name}}</text>
			</view>
		</view>
</template>
<script>
	export default {
		name: "homeIdex",
		props: {
			navH: {
				type: String | Number,
				default: ""
			},
			returnShow: {
				type: Boolean,
				default: true
			},
			goodList: {
				type: Boolean,
				default: false
			},
			currentPage: {
				type: Boolean,
				default: false
			},
			goodsShow: {
				type: Boolean,
				default: false
			},
			sysHeight: {
				type: String | Number,
				default: ""
			}
		},
		data: function() {
			return {
				selectNavList: [{
						name: this.$t(`首页`),
						icon: 'icon-shouye8',
						url: '/pages/index/index',
						after: 'dialog_after'
					},
					{
						name: this.$t(`搜索`),
						icon: 'icon-sousuo6',
						url: '/pages/goods/goods_search/index',
						after: 'dialog_after'
					},
					{
						name: this.$t(`购物车`),
						icon: 'icon-gouwuche7',
						url: '/pages/order_addcart/order_addcart',
						after: 'dialog_after'
					},
					{
						name: this.$t(`我的收藏`),
						icon: 'icon-shoucang3',
						url: '/pages/users/user_goods_collection/index',
						after: 'dialog_after'
					},
					{
						name: this.$t(`个人中心`),
						icon: 'icon-gerenzhongxin1',
						url: '/pages/user/index'
					},
				]
			};
		},
		methods: {
			linkPage(url) {
				if (['/pages/goods_cate/goods_cate', '/pages/order_addcart/order_addcart', '/pages/user/index',
						'/pages/index/index'
					]
					.indexOf(url) == -1) {
					uni.navigateTo({
						url: url
					})
				} else {
					uni.switchTab({
						url: url
					})
				}
			}
		},
		created() {},
		beforeDestroy() {}
	};
</script>

<style scoped lang="scss">
	.dialog_nav {
		position: absolute;
		/* #ifdef MP */
		left: 14rpx;
		/* #endif */
		/* #ifndef MP */
		right: 14rpx;
		/* #endif */
		width: 240rpx;
		background: #FFFFFF;
		box-shadow: 0px 0px 16rpx rgba(0, 0, 0, 0.08);
		z-index: 310;
		border-radius: 14rpx;

		&::before {
			content: "";
			width: 0;
			height: 0;
			border-left: 15rpx solid transparent;
			border-right: 15rpx solid transparent;
			border-bottom: 30rpx solid #fff;
			position: absolute;
			top: -20rpx;
			/* #ifdef APP-PLUS || H5 */
			right: 32rpx;
			/* #endif */
			/* #ifdef MP */
			left: 80rpx;
			/* #endif */
			border-bottom-color: #f2f2f2;
		}

		&::after {
			content: "";
			width: 0;
			height: 0;
			border-left: 15rpx solid transparent;
			border-right: 15rpx solid transparent;
			border-bottom: 30rpx solid #fff;
			position: absolute;
			top: -20rpx;
			/* #ifdef APP-PLUS || H5 */
			right: 32rpx;
			/* #endif */
			/* #ifdef MP */
			left: 80rpx;
			/* #endif */

		}

		&.dialogIndex {
			left: 14rpx;

			&::before {
				left: -160rpx !important;
			}
		}

		&.dialogGoods {
			&::before {
				left: -170rpx;
			}
		}
	}

	.dialog_nav_item {
		width: 100%;
		height: 84rpx;
		line-height: 84rpx;
		padding: 0 20rpx 0;
		box-sizing: border-box;
		border-bottom: #eee;
		font-size: 28rpx;
		color: #333;
		position: relative;
		display: flex;

		.iconfont {
			font-size: 32rpx;
			margin-right: 26rpx;
		}
	}

	.dialog_after {
		::after {
			content: '';
			position: absolute;
			width: 90px;
			height: 1px;
			background-color: #EEEEEE;
			bottom: 0;
			right: 0;
		}
	}
</style>
