<template>
	<view>
		<view class='return-list' v-if="orderList.length">
			<view class='goodWrapper' v-for="(item,index) in orderList" :key="index" @click='goOrderDetails(item.order_id)'>
				<view class='iconfont icon-tuikuanzhong powder' v-if="item._status._type==-1"></view>
				<view class='iconfont icon-yituikuan' v-if="item._status._type==-2"></view>
				<view class='orderNum'>订单号：{{item.order_id}}</view>
				<view class='item acea-row row-between-wrapper' v-for="(item,index) in item.cartInfo" :key="index">
					<view class='pictrue'>
						<image :src='item.productInfo.image'></image>
					</view>
					<view class='text'>
						<view class='acea-row row-between-wrapper'>
							<view class='name line1'>{{item.productInfo.store_name}}</view>
							<view class='num'>x {{item.cart_num}}</view>
						</view>
						<view class='attr line1' v-if="item.productInfo.attrInfo">{{item.productInfo.attrInfo.suk}}</view>
						<view class='attr line1' v-else>{{item.productInfo.store_name}}</view>
						<view class='money'>￥{{item.productInfo.price}}</view>
					</view>
				</view>
				<view class='totalSum'>共{{item.cartInfo.length || 0}}件商品，总金额 <text class='font-color price'>￥{{item.pay_price}}</text></view>
			</view>
		</view>
		<view class='loadingicon acea-row row-center-wrapper'>
			<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<home></home>
	</view>
</template>

<script>
	import home from '@/components/home';
	import {
		getOrderList
	} from '@/api/order.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	export default {
		components: {
			home,
			// #ifdef MP
			authorize
			// #endif
		},
		data() {
			return {
				loading: false,
				loadend: false,
				loadTitle: '加载更多', //提示语
				orderList: [], //订单数组
				orderStatus: -3, //订单状态
				page: 1,
				limit: 20,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false //是否隐藏授权
			};
		},
		computed: mapGetters(['isLogin']),
		watch:{
			isLogin:{
				handler:function(newV,oldV){
					if(newV){
						this.getOrderList();
					}
				},
				deep:true
			}
		},
		onLoad() {
			if (this.isLogin) {
				this.getOrderList();
			} else {
				toLogin();
			}
		},
		/**
		 * 页面上拉触底事件的处理函数
		 */
		onReachBottom: function() {
			this.getOrderList();
		},
		methods: {
			onLoadFun() {
				this.getOrderList();
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			/**
			 * 去订单详情
			 */
			goOrderDetails: function(order_id) {
				if (!order_id) return that.$util.Tips({
					title: '缺少订单号无法查看订单详情'
				});
				uni.navigateTo({
					url: '/pages/users/order_details/index?order_id=' + order_id + '&isReturen=1'
				})
			},

			/**
			 * 获取订单列表
			 */
			getOrderList: function() {
				let that = this;
				if (that.loadend) return;
				if (that.loading) return;
				that.loading = true;
				that.loadTitle = "";
				getOrderList({
					type: that.orderStatus,
					page: that.page,
					limit: that.limit,
				}).then(res => {
					let list = res.data || [];
					let loadend = list.length < that.limit;
					that.orderList = that.$util.SplitArray(list, that.orderList);
					that.$set(that,'orderList',that.orderList);
					that.loadend = loadend;
					that.loading = false;
					that.loadTitle = loadend ? "我也是有底线的" : '加载更多';
					that.page = that.page + 1;
				}).catch(err => {
					that.loading = false;
					that.loadTitle = "加载更多";
				});
			}
		}
	}
</script>

<style lang="scss" scoped>
	.return-list .goodWrapper {
		background-color: #fff;
		margin-top: 13rpx;
		position: relative;
	}

	.return-list .goodWrapper .orderNum {
		padding: 0 30rpx;
		border-bottom: 1px solid #eee;
		height: 87rpx;
		line-height: 87rpx;
		font-size: 30rpx;
		color: #282828;
	}

	.return-list .goodWrapper .item {
		border-bottom: 0;
	}

	.return-list .goodWrapper .totalSum {
		padding: 0 30rpx 32rpx 30rpx;
		text-align: right;
		font-size: 26rpx;
		color: #282828;
	}

	.return-list .goodWrapper .totalSum .price {
		font-size: 28rpx;
		font-weight: bold;
	}

	.return-list .goodWrapper .iconfont {
		position: absolute;
		font-size: 109rpx;
		top: 7rpx;
		right: 30rpx;
		color: #ccc;
	}

	.return-list .goodWrapper .iconfont.powder {
		color: #f8c1bd;
	}
</style>
