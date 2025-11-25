<template>
	<view :style="colorStyle">
		<view class="my-order">
			<view class="header bg-color">
				<view class="picTxt acea-row row-between-wrapper">
					<view class="text">
						<view class="name">{{ $t(`订单信息`) }}</view>
						<view><text class="mr-40">{{ $t(`消费订单`) }}：{{ orderData.order_count || 0 }} 单 </text>{{ $t(`总消费`) }}：{{ $t(`￥`) }}{{ orderData.sum_price || 0 }}</view>
					</view>
					<view class="pictrue">
						<image src="../static/orderTime.png"></image>
					</view>
				</view>
			</view>
			<view class="nav acea-row row-around">
				<view class="item" :class="orderStatus == 9 ? 'on' : ''" @click="statusClick(9)">
					<view>{{ $t(`全部`) }}</view>
					<view class="num">{{ orderData.order_count || 0 }}</view>
				</view>
				<view class="item" :class="orderStatus == 0 ? 'on' : ''" @click="statusClick(0)">
					<view>{{ $t(`待付款`) }}</view>
					<view class="num">{{ orderData.unpaid_count || 0 }}</view>
				</view>
				<view class="item" :class="orderStatus == 1 ? 'on' : ''" @click="statusClick(1)">
					<view>{{ $t(`待发货`) }}</view>
					<view class="num">{{ orderData.unshipped_count || 0 }}</view>
				</view>
				<view class="item" :class="orderStatus == 2 ? 'on' : ''" @click="statusClick(2)">
					<view>{{ $t(`待收货`) }}</view>
					<view class="num">{{ orderData.received_count || 0 }}</view>
				</view>
				<view class="item" :class="orderStatus == 3 ? 'on' : ''" @click="statusClick(3)">
					<view>{{ $t(`待评价`) }}</view>
					<view class="num">{{ orderData.evaluated_count || 0 }}</view>
				</view>
			</view>
			<view class="list">
				<view class="item" v-for="(item, index) in orderList" :key="index">
					<view @click="goOrderDetails(item.order_id)">
						<view class="acea-row row-middle gift" v-if="item.is_gift == 1">
							<text class="iconfont icon-ic_gift2 mr-16"></text>
							<view v-if="item.gift_uid === 0">礼物未赠送</view>
							<view v-else-if="item.uid === uid">送给 {{ item.gift_user_info.gift_nickname }} 的礼物</view>
							<view v-else-if="item.gift_uid === uid">收到 {{ item.nickname }} 的礼物</view>
						</view>
						<view class="title acea-row row-between-wrapper">
							<view class="acea-row row-middle">
								<text class="sign cart-color acea-row row-center-wrapper" v-if="item.type == 2 && $permission('bargain')">{{ $t(`砍价`) }}</text>
								<text class="sign cart-color acea-row row-center-wrapper" v-else-if="item.type == 3 && $permission('combination')">{{ $t(`拼团`) }}</text>
								<text class="sign cart-color acea-row row-center-wrapper" v-else-if="item.type == 1 && $permission('seckill')">{{ $t(`秒杀`) }}</text>
								<text class="sign cart-color acea-row row-center-wrapper" v-else-if="item.type == 4">{{ $t(`预售`) }}</text>
								<view>{{ item._add_time }}</view>
							</view>
							<view v-if="item.is_cancel == 1" class="font-color">{{ $t(`已取消`) }}</view>
							<view v-else-if="item._status._type == 9" class="font-color">{{ $t(`线下付款,未支付`) }}</view>
							<view v-else-if="item._status._type == 0" class="font-color">{{ $t(`待付款`) }}</view>
							<view v-else-if="item._status._type == 1 && item.shipping_type == 1" class="font-color">
								{{ $t(`待发货`) }}
								<text v-if="item.refund.length">，{{ item.is_all_refund ? $t(`退款中`) : $t(`部分退款中`) }}</text>
							</view>
							<view v-else-if="item._status._type == 1 && item.shipping_type == 2" class="font-color">
								{{ $t(`待核销`) }}
								<text v-if="item.refund.length">，{{ item.is_all_refund ? $t(`退款中`) : $t(`部分退款中`) }}</text>
							</view>
							<view v-else-if="item._status._type == 2" class="font-color">
								{{ $t(`待收货`) }}
								<text v-if="item.refund.length">，{{ item.is_all_refund ? $t(`退款中`) : $t(`部分退款中`) }}</text>
							</view>
							<view v-else-if="item._status._type == 3" class="font-color">
								{{ $t(`待评价`) }}
								<text v-if="item.refund.length">，{{ item.is_all_refund ? $t(`退款中`) : $t(`部分退款中`) }}</text>
							</view>
							<view v-else-if="item._status._type == 4" class="font-color">
								{{ $t(`已完成`) }}
								<text v-if="item.refund.length">，{{ item.is_all_refund ? $t(`退款中`) : $t(`部分退款中`) }}</text>
							</view>
							<view v-else-if="item._status._type == 5 && item.status == 0" class="font-color">
								{{ $t(`未核销`) }}
								<text v-if="item.refund.length">，{{ item.is_all_refund ? $t(`退款中`) : $t(`部分退款中`) }}</text>
							</view>
							<view v-else-if="item._status._type == -2" class="font-color">{{ $t(`已退款`) }}</view>
						</view>
						<view class="item-info acea-row row-between row-top" v-for="(items, indexCat) in item.cartInfo" :key="indexCat">
							<view class="pictrue">
								<easy-loadimage mode="widthFix" :image-src="items.productInfo.image"></easy-loadimage>
								<!-- <image :src="items.productInfo.image"></image> -->
							</view>
							<view class="text row-between">
								<text class="name line2">{{ items.productInfo.store_name }}</text>
								<view class="money">
									<template v-if="item.gift_uid !== uid">
										<view v-if="items.productInfo.attrInfo">{{ $t(`￥`) }}{{ items.productInfo.attrInfo.price }}</view>
										<view v-else>{{ $t(`￥`) }}{{ items.productInfo.price }}</view>
									</template>
									<view>x{{ items.cart_num }}</view>
									<view v-if="items.refund_num && item._status._type != -2 && item.gift_uid !== uid" class="return">{{ items.refund_num }}{{ $t(`件退款中`) }}</view>
								</view>
							</view>
						</view>
						<view class="totalPrice" v-show="item.gift_uid !== uid">
							{{ $t(`共`) }}{{ item.total_num || 0 }}{{ $t(`件商品，总金额`) }}
							<text class="money">{{ $t(`￥`) }}{{ item.pay_price }}</text>
						</view>
					</view>
					<view class="bottom acea-row row-right row-middle">
						<view class="bnt cancelBnt" v-if="(item._status._type == 0 || item._status._type == 9) && item.is_cancel == 0" @click="cancelOrder(index, item.order_id)">
							{{ $t(`取消订单`) }}
						</view>
						<view class="bnt cancelBnt" v-if="item._status._type == 4 && item.is_cancel == 0" @click="delOrder(item.order_id, index)">{{ $t(`删除订单`) }}</view>
						<view class="bnt" :class="item._status._type == 0 && item.is_cancel == 0 ? 'cancelBnt' : 'bg-color'" @click="goOrderDetails(item.order_id)">{{ $t(`查看详情`) }}</view>
						<view class="bnt bg-color" v-if="item._status._type == 0 && item.is_cancel == 0" @click="goPay(item.pay_price, item.order_id)">{{ $t(`立即付款`) }}</view>
						
						<!-- <view class="bnt bg-color" v-else-if="item._status._type == 3"
							@click="goOrderDetails(item.order_id)">去评价</view> -->
						<!-- <view class="bnt bg-color"
							v-else-if="item.seckill_id < 1 && item.bargain_id < 1 && item.combination_id < 1 && item._status._type == 4"
							@click="goOrderDetails(item.order_id)">
							再次购买
						</view> -->
						
						
					</view>
				</view>
			</view>
			<view class="loadingicon acea-row row-center-wrapper" v-if="orderList.length > 0">
				<text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
				{{ loadTitle }}
			</view>
			<view v-if="orderList.length == 0">
				<emptyPage v-if="!loading" :title="$t(`暂无订单`)"></emptyPage>
				<view class="loadingicon acea-row row-center-wrapper">
					<text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
				</view>
			</view>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script>
import { getOrderList, orderData, orderCancel, orderDel, orderPay } from '@/api/order.js';
import { openOrderSubscribe } from '@/utils/SubscribeMessage.js';
import home from '@/components/home';
import { toLogin } from '@/libs/login.js';
import { mapGetters } from 'vuex';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
import emptyPage from '@/components/emptyPage.vue';
import colors from '@/mixins/color.js';
export default {
	components: {
		home,
		emptyPage,
		// #ifdef MP
		authorize
		// #endif
	},
	mixins: [colors],
	data() {
		return {
			loading: false, //是否加载中
			loadend: false, //是否加载完毕
			loadTitle: this.$t(`加载更多`), //提示语
			orderList: [], //订单数组
			orderData: {}, //订单详细统计
			orderStatus: 9, //订单状态
			page: 1,
			limit: 20,
			pay_close: false,
			pay_order_id: '',
			totalPrice: '0',
			initIn: false,
			isAuto: false, //没有授权的不会自动授权
			isShowAuth: false, //是否隐藏授权
			uid: 0
		};
	},
	computed: mapGetters(['isLogin']),
	/**
	 * 生命周期函数--监听页面加载
	 */
	onLoad: function (options) {
		if (options.status) this.orderStatus = options.status;
		let EnOptions = wx.getEnterOptionsSync();
		if (EnOptions.scene == '1038' && EnOptions.referrerInfo.appId == 'wxef277996acc166c3' && this.initIn) {
			// 代表从收银台小程序返回
			let extraData = EnOptions.referrerInfo.extraData;
			this.initIn = false;
			if (!extraData) {
				this.getOrderList();
				// "当前通过物理按键返回，未接收到返参，建议自行查询交易结果";
			} else {
				if (extraData.code == 'success') {
					this.getOrderList();
				} else if (extraData.code == 'cancel') {
				} else {
					// "支付失败：" + extraData.errmsg;
				}
			}
		}
	},
	onShow() {
		if (this.isLogin) {
			this.page = 1;
			this.orderList = [];
			this.loadend = false;
			this.pay_close = false;
			this.onLoadFun();
			this.getOrderList();
			this.uid = this.$store.state.app.uid;
		} else {
			toLogin();
		}
	},
	methods: {
		onLoadFun() {
			this.getOrderData();
		},
		// 授权关闭
		authColse: function (e) {
			this.isShowAuth = e;
		},
		/**
		 * 事件回调
		 *
		 */
		onChangeFun: function (e) {
			let opt = e;
			let action = opt.action || null;
			let value = opt.value != undefined ? opt.value : null;
			action && this[action] && this[action](value);
		},
		/**
		 * 关闭支付组件
		 *
		 */
		payClose: function () {
			this.pay_close = false;
		},

		/**
		 * 获取订单统计数据
		 *
		 */
		getOrderData: function () {
			let that = this;
			orderData().then((res) => {
				that.$set(that, 'orderData', res.data);
			});
		},
		/**
		 * 取消订单
		 *
		 */
		cancelOrder: function (index, order_id) {
			let that = this;
			if (!order_id)
				return that.$util.Tips({
					title: that.$t(`缺少订单号无法取消订单`)
				});
			uni.showModal({
				title: this.$t(`提示`),
				content: this.$t(`确认取消该订单`),
				success: function (res) {
					if (res.confirm) {
						orderCancel(order_id)
							.then((res) => {
								return that.$util.Tips(
									{
										title: res.msg,
										icon: 'success'
									},
									function () {
										that.orderList.splice(index, 1);
										that.$set(that, 'orderList', that.orderList);
										that.$set(that.orderData, 'unpaid_count', that.orderData.unpaid_count - 1);
										that.getOrderData();
									}
								);
							})
							.catch((err) => {
								return that.$util.Tips({
									title: err
								});
							});
					} else if (res.cancel) {
					}
				}
			});
		},
		/**
		 * 打开支付组件
		 *
		 */
		goPay: function (pay_price, order_id) {
			uni.navigateTo({
				url: `/pages/goods/cashier/index?order_id=${order_id}&from_type=order`
			});
		},
		/**
		 * 去订单详情
		 */
		goOrderDetails: function (order_id) {
			let that = this;
			if (!order_id)
				return that.$util.Tips({
					title: that.$t(`缺少订单号无法查看订单详情`)
				});
			// #ifdef MP
			uni.showLoading({
				title: that.$t(`正在加载中`)
			});
			openOrderSubscribe()
				.then(() => {
					uni.hideLoading();
					uni.navigateTo({
						url: '/pages/goods/order_details/index?order_id=' + order_id
					});
				})
				.catch((err) => {
					uni.hideLoading();
				});
			// #endif
			// #ifndef MP
			uni.navigateTo({
				url: '/pages/goods/order_details/index?order_id=' + order_id
			});
			// #endif
		},
		/**
		 * 切换类型
		 */
		statusClick: function (status) {
			if (status == this.orderStatus) return;
			this.orderStatus = status;
			this.loadend = false;
			this.page = 1;
			this.$set(this, 'orderList', []);
			this.getOrderList();
		},
		/**
		 * 获取订单列表
		 */
		getOrderList: function () {
			let that = this;
			if (that.loadend) return;
			if (that.loading) return;
			that.loading = true;
			that.loadTitle = that.$t(`加载更多`);
			getOrderList({
				type: that.orderStatus,
				page: that.page,
				limit: that.limit
			})
				.then((res) => {
					let list = res.data || [];
					let loadend = list.length < that.limit;
					that.orderList = that.$util.SplitArray(list, that.orderList);
					that.$set(that, 'orderList', that.orderList);
					that.loadend = loadend;
					that.loading = false;
					that.loadTitle = loadend ? that.$t(`没有更多内容啦~`) : that.$t(`加载更多`);
					that.page = that.page + 1;
				})
				.catch((err) => {
					that.loading = false;
					that.loadTitle = that.$t(`加载更多`);
				});
		},

		/**
		 * 删除订单
		 */
		delOrder: function (order_id, index) {
			let that = this;
			uni.showModal({
				title: that.$t(`删除订单`),
				content: that.$t(`确定删除该订单`),
				success: function (res) {
					if (res.confirm) {
						orderDel(order_id)
							.then((res) => {
								that.orderList.splice(index, 1);
								that.$set(that, 'orderList', that.orderList);
								that.$set(that.orderData, 'unpaid_count', that.orderData.unpaid_count - 1);
								that.getOrderData();
								return that.$util.Tips({
									title: that.$t(`删除成功`),
									icon: 'success'
								});
							})
							.catch((err) => {
								return that.$util.Tips({
									title: err
								});
							});
					} else if (res.cancel) {
						return that.$util.Tips({
							title: that.$t(`已取消`)
						});
					}
				}
			});
		}
	},
	onReachBottom: function () {
		this.getOrderList();
	},
	// 滚动监听
	onPageScroll(e) {
		// 传入scrollTop值并触发所有easy-loadimage组件下的滚动监听事件
		uni.$emit('scroll');
	}
};
</script>

<style scoped lang="scss">
.my-order .header {
	height: 260rpx;
	padding: 0 30rpx;
}

.my-order .header .picTxt {
	height: 190rpx;
}

.my-order .header .picTxt .text {
	color: rgba(255, 255, 255, 0.8);
	font-size: 26rpx;
	font-family: 'Guildford Pro';
}

.my-order .header .picTxt .text .name {
	font-size: 34rpx;
	font-weight: bold;
	color: #fff;
	margin-bottom: 20rpx;
}

.my-order .header .picTxt .pictrue {
	width: 122rpx;
	height: 109rpx;
}

.my-order .header .picTxt .pictrue image {
	width: 100%;
	height: 100%;
}

.my-order .nav {
	background-color: #fff;
	width: 690rpx;
	height: 140rpx;
	border-radius: 6rpx;
	margin: -73rpx auto 0 auto;
}

.my-order .nav .item {
	text-align: center;
	font-size: 26rpx;
	color: #282828;
	width: 3rem;
	padding: 24rpx 0;
	border-bottom: 5rpx solid transparent;
}

.my-order .nav .item.on {
	/* #ifdef H5 || MP */
	font-weight: bold;
	/* #endif */
	/* #ifdef APP-PLUS */
	color: #000;
	/* #endif */
	border-color: var(--view-theme);
}

.my-order .nav .item .num {
	margin-top: 18rpx;
}

.my-order .list {
	width: 690rpx;
	margin: 14rpx auto 0 auto;
}

.my-order .list .item {
	background-color: #fff;
	border-radius: 6rpx;
	margin-bottom: 14rpx;
	.gift {
		padding: 26rpx 32rpx;
		background-color: #fefaf3;
		color: #ae5a2a;
	}
}

.my-order .list .item .title {
	height: 84rpx;
	padding: 0 30rpx;
	border-bottom: 1rpx solid #eee;
	font-size: 28rpx;
	color: #282828;
}

.my-order .list .item .title .sign {
	font-size: 24rpx;
	padding: 0 7rpx;
	height: 36rpx;
	margin-right: 15rpx;
}

.my-order .list .item .item-info {
	padding: 0 30rpx;
	margin-top: 22rpx;
}

.my-order .list .item .item-info .pictrue {
	width: 120rpx;
	height: 120rpx;

	/deep/,
	/deep/image,
	/deep/.easy-loadimage,
	/deep/uni-image {
		width: 120rpx;
		height: 120rpx;
		border-radius: 6rpx;
	}
}

.my-order .list .item .item-info .pictrue image {
	width: 100%;
	height: 100%;
	border-radius: 6rpx;
}

.my-order .list .item .item-info .text {
	width: 486rpx;
	font-size: 28rpx;
	color: #999;
	margin-top: 6rpx;
	display: flex;
	line-height: 39rpx;
}

.my-order .list .item .item-info .text .name {
	width: 306rpx;
	color: #282828;
	height: 78rpx;
}

.my-order .list .item .item-info .text .money {
	text-align: right;
	flex: 1;
}

.my-order .list .item .totalPrice {
	font-size: 26rpx;
	color: #282828;
	text-align: right;
	margin: 27rpx 0 0 30rpx;
	padding: 0 30rpx 30rpx 0;
	border-bottom: 1rpx solid #eee;
}

.my-order .list .item .totalPrice .money {
	font-size: 28rpx;
	font-weight: bold;
	color: var(--view-priceColor);
}

.my-order .list .item .bottom {
	height: 107rpx;
	padding: 0 30rpx;
}

.my-order .list .item .bottom .bnt {
	width: 176rpx;
	height: 60rpx;
	text-align: center;
	line-height: 60rpx;
	color: #fff;
	border-radius: 50rpx;
	font-size: 27rpx;
}

.my-order .list .item .bottom .bnt.cancelBnt {
	border: 1rpx solid #ddd;
	color: #aaa;
}

.my-order .list .item .bottom .bnt ~ .bnt {
	margin-left: 17rpx;
}

.noCart {
	margin-top: 171rpx;
	padding-top: 0.1rpx;
}

.noCart .pictrue {
	width: 414rpx;
	height: 336rpx;
	margin: 78rpx auto 56rpx auto;
}

.noCart .pictrue image {
	width: 100%;
	height: 100%;
}

.my-order .list .item .item-info .text .money .return {
	// color: var(--view-priceColor);
	margin-top: 10rpx;
	font-size: 24rpx;
}
</style>