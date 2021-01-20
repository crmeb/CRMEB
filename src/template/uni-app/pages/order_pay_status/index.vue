<template>
	<view>
		<view class='payment-status'>
			<!--失败时： 用icon-iconfontguanbi fail替换icon-duihao2 bg-color-->
			<view class='iconfont icons icon-duihao2 bg-color' v-if="order_pay_info.paid || order_pay_info.pay_type == 'offline'"></view>
			<view class='iconfont icons icon-iconfontguanbi bg-color' v-else></view>
			<!-- 失败时：订单支付失败 -->
			<view class='status' v-if="order_pay_info.pay_type != 'offline'">{{order_pay_info.paid ? '订单支付成功':'订单支付失败'}}</view>
			<view class='status' v-else>订单创建成功</view>
			<view class='wrapper'>
				<view class='item acea-row row-between-wrapper'>
					<view>订单编号</view>
					<view class='itemCom'>{{orderId}}</view>
				</view>
				<view class='item acea-row row-between-wrapper'>
					<view>下单时间</view>
					<view class='itemCom'>{{order_pay_info._pay_time}}</view>
				</view>
				<view class='item acea-row row-between-wrapper'>
					<view>支付方式</view>
					<view class='itemCom'>{{order_pay_info._status._payType}}</view>
				</view>
				<view class='item acea-row row-between-wrapper'>
					<view>支付金额</view>
					<view class='itemCom'>{{order_pay_info.pay_price}}</view>
				</view>
				<!--失败时加上这个  -->
				<view class='item acea-row row-between-wrapper' v-if="order_pay_info.paid==0 && order_pay_info.pay_type != 'offline'">
					<view>失败原因</view>
					<view class='itemCom'>{{status==2 ? '取消支付':msg}}</view>
				</view>
			</view>
			<!--失败时： 重新购买 -->
			<view @tap="goOrderDetails" v-if="status==0">
				<button formType="submit" class='returnBnt bg-color' hover-class='none'>查看订单</button>
			</view>
			<view @tap="goOrderDetails" v-if="order_pay_info.paid==0 && status==1">
				<button class='returnBnt bg-color' hover-class='none'>重新购买</button>
			</view>
			<view @tap="goOrderDetails" v-if="order_pay_info.paid==0 && status==2">
				<button class='returnBnt bg-color' hover-class='none'>重新支付</button>
			</view>
			<button @click="goPink(order_pay_info.pink_id)" class='returnBnt cart-color' formType="submit" hover-class='none'
			 v-if="order_pay_info.pink_id && order_pay_info.paid!=0 && status!=2 && status!=1">邀请好友参团</button>
			<button @click="goIndex" class='returnBnt cart-color' formType="submit" hover-class='none' v-else>返回首页</button>
			<view class="coupons" v-if='couponList.length'>
				<view class="title acea-row row-center-wrapper">
					<view class="line"></view>
					<view class="name">赠送优惠券</view>
					<view class="line"></view>
				</view>
				<view class="list">
					<view class="item acea-row row-between-wrapper" v-for="(item,index) in couponList" :key='index' v-if="index<2 || !couponsHidden">
						<view class="price acea-row row-center-wrapper">
							<view>
								￥<text>{{item.coupon_price}}</text>
							</view>
						</view>
						<view class="text">
							<view class="name line1">{{item.coupon_title}}</view>
							<view class="priceMin">满{{item.use_min_price}}元可用</view>
							<view class="time">有效期:{{ item.add_time ? item.add_time + "-" : ""}}{{ item.end_time }}</view>
						</view>
					</view>
					<view class="open acea-row row-center-wrapper" @click="openTap">{{couponsHidden?'展开更多':'关闭展开'}}<text class="iconfont" :class='couponsHidden==true?"icon-xiangxia":"icon-xiangshang"'></text></view>
				</view>
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		getOrderDetail,
		orderCoupon
	} from '@/api/order.js';
	import {
		openOrderSubscribe
	} from '@/utils/SubscribeMessage.js';
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
			// #ifdef MP
			authorize
			// #endif
		},
		data() {
			return {
				orderId: '',
				order_pay_info: {
					paid: 1,
					_status: {}
				},
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				status: 0,
				msg: '',
				couponsHidden: true,
				couponList: []
			};
		},
		computed: mapGetters(['isLogin']),
		watch:{
			isLogin:{
				handler:function(newV,oldV){
					if(newV){
						this.getOrderPayInfo();
					}
				},
				deep:true
			}
		},
		onLoad: function(options) {
			if (!options.order_id) return this.$util.Tips({
				title: '缺少参数无法查看订单支付状态'
			}, {
				tab: 3,
				url: 1
			});
			this.orderId = options.order_id;
			this.status = options.status || 0;
			this.msg = options.msg || '';
			if (this.isLogin) {
				this.getOrderPayInfo();
			} else {
				toLogin();
			}
		},
		methods: {
			openTap() {
				this.$set(this,'couponsHidden',!this.couponsHidden);
			},
			onLoadFun: function() {
				this.getOrderPayInfo();
			},
			/**
			 * 
			 * 支付完成查询支付状态
			 * 
			 */
			getOrderPayInfo: function() {
				let that = this;
				uni.showLoading({
					title: '正在加载中'
				});
				getOrderDetail(that.orderId).then(res => {
					uni.hideLoading();
					that.$set(that, 'order_pay_info', res.data);
					uni.setNavigationBarTitle({
						title: res.data.paid ? '支付成功' : '支付失败'
					});
					that.getOrderCoupon();
				}).catch(err => {
					uni.hideLoading();
				});
			},
			getOrderCoupon() {
				let that = this;
				console.log('88888888888');
				console.log(that.orderId);
				orderCoupon(that.orderId).then(res => {
					console.log(res.data);
					that.couponList = res.data;
				})
			},
			/**
			 * 去首页关闭当前所有页面
			 */
			goIndex: function(e) {
				uni.navigateTo({
					url: '/pages/index/index'
				});
			},
			/**
			 * 
			 * 去订单详情页面
			 */
			goOrderDetails: function(e) {
				let that = this;
				// #ifdef MP
				uni.showLoading({
					title: '正在加载',
				})
				openOrderSubscribe().then(res => {
					uni.hideLoading();
					uni.navigateTo({
						url: '/pages/users/order_details/index?order_id=' + that.orderId
					});
				}).catch(() => {
					nui.hideLoading();
				});
				// #endif
				// #ifndef MP
				uni.navigateTo({
					url: '/pages/users/order_details/index?order_id=' + that.orderId
				})
				// #endif
			}

		}
	}
</script>

<style lang="scss">
	.coupons {
		.title {
			margin: 30rpx 0 25rpx 0;

			.line {
				width: 70rpx;
				height: 1px;
				background: #DCDCDC;
			}

			.name {
				font-size: 24rpx;
				color: #999;
				margin: 0 10rpx;
			}
		}

		.list {
			padding: 0 20rpx;

			.item {
				margin-bottom: 20rpx;
				box-shadow: 0px 2px 10px 0px rgba(0, 0, 0, 0.06);

				.price {
					width: 236rpx;
					height: 160rpx;
					font-size: 26rpx;
					color: #fff;
					font-weight: 800;

					text {
						font-size: 64rpx;
					}
				}

				.text {
					width: 385rpx;

					.name {
						font-size: #282828;
						font-size: 30rpx;
					}

					.priceMin {
						font-size: 24rpx;
						color: #999;
						margin-top: 10rpx;
					}

					.time {
						font-size: 24rpx;
						color: #999;
						margin-top: 15rpx;
					}
				}
			}

			.open {
				font-size: 24rpx;
				color: #999;
				margin-top: 30rpx;

				.iconfont {
					font-size: 25rpx;
					margin: 5rpx 0 0 10rpx;
				}
			}
		}
	}

	.payment-status {
		background-color: #fff;
		margin: 195rpx 30rpx 0 30rpx;
		border-radius: 10rpx;
		padding: 1rpx 0 28rpx 0;
	}

	.payment-status .icons {
		font-size: 70rpx;
		width: 140rpx;
		height: 140rpx;
		border-radius: 50%;
		color: #fff;
		text-align: center;
		line-height: 140rpx;
		text-shadow: 0px 4px 0px #df1e14;
		border: 6rpx solid #f5f5f5;
		margin: -76rpx auto 0 auto;
		background-color: #999;
	}

	.payment-status .iconfont.fail {
		text-shadow: 0px 4px 0px #7a7a7a;
	}

	.payment-status .status {
		font-size: 32rpx;
		font-weight: bold;
		text-align: center;
		margin: 25rpx 0 37rpx 0;
	}

	.payment-status .wrapper {
		border: 1rpx solid #eee;
		margin: 0 30rpx 47rpx 30rpx;
		padding: 35rpx 0;
		border-left: 0;
		border-right: 0;
	}

	.payment-status .wrapper .item {
		font-size: 28rpx;
		color: #282828;
	}

	.payment-status .wrapper .item~.item {
		margin-top: 20rpx;
	}

	.payment-status .wrapper .item .itemCom {
		color: #666;
	}

	.payment-status .returnBnt {
		width: 630rpx;
		height: 86rpx;
		border-radius: 50rpx;
		color: #fff;
		font-size: 30rpx;
		text-align: center;
		line-height: 86rpx;
		margin: 0 auto 20rpx auto;
	}
</style>
