<template>
	<view class="order-details pos-order-details">
		<view class="header acea-row row-middle">
			<view class="state">{{ title }}</view>
			<view class="data">
				<view class="order-num">订单：{{ orderInfo.order_id }}</view>
				<view>
					<span class="time">{{ orderInfo._add_time }}</span>
				</view>
			</view>
		</view>
		<view class="orderingUser acea-row row-middle">
			<span class="iconfont icon-yonghu2"></span>{{ orderInfo.nickname }}
		</view>
		<view class="address">
			<view class="name">
				{{ orderInfo.real_name
        }}<span class="phone">{{ orderInfo.user_phone }}</span>
			</view>
			<view>{{ orderInfo.user_address }}</view>
		</view>
		<view class="line">
			<image src="/static/images/line.jpg" />
		</view>
		<view class="pos-order-goods">
			<navigator :url="`/pages/goods_details/index?id=${item.productInfo.id}`" hover-class="none" class="goods acea-row row-between-wrapper"
			 v-for="(item, index) in orderInfo.cartInfo" :key="index">
				<view class="picTxt acea-row row-between-wrapper">
					<view class="pictrue">
						<image :src="item.productInfo.image" />
					</view>
					<view class="text acea-row row-between row-column">
						<view class="info line2">
							{{ item.productInfo.store_name }}
						</view>
						<view class="attr">{{ item.productInfo.suk }}</view>
					</view>
				</view>
				<view class="money">
					<view class="x-money">￥{{ item.productInfo.attrInfo.price }}</view>
					<view class="num">x{{ item.cart_num }}</view>
					<view class="y-money">￥{{ item.productInfo.attrInfo.ot_price }}</view>
				</view>
			</navigator>
		</view>
		<view class="public-total">
			共{{ orderInfo.total_num }}件商品，应支付
			<span class="money">￥{{ orderInfo.pay_price }}</span> ( 邮费 ¥{{
        orderInfo.pay_postage
      }}
			)
		</view>
		<view class="wrapper">
			<view class="item acea-row row-between">
				<view>订单编号：</view>
				<view class="conter acea-row row-middle row-right">
					{{ orderInfo.order_id
          }}
				</view>
			</view>
			<view class="item acea-row row-between">
				<view>下单时间：</view>
				<view class="conter">{{ orderInfo._add_time }}</view>
			</view>
			<view class="item acea-row row-between">
				<view>支付状态：</view>
				<view class="conter">
					{{ orderInfo.paid == 1 ? "已支付" : "未支付" }}
				</view>
			</view>
			<view class="item acea-row row-between">
				<view>支付方式：</view>
				<view class="conter">{{ payType }}</view>
			</view>
			<view class="item acea-row row-between">
				<view>买家留言：</view>
				<view class="conter">{{ orderInfo.mark }}</view>
			</view>
		</view>
		<view class="wrapper">
			<view class="item acea-row row-between">
				<view>支付金额：</view>
				<view class="conter">￥{{ orderInfo.total_price }}</view>
			</view>
			<view class='item acea-row row-between' v-if='orderInfo.coupon_id'>
				<view>优惠券抵扣：</view>
				<view class='conter'>-￥{{orderInfo.coupon_price}}</view>
			</view>
			<view class='item acea-row row-between' v-if="orderInfo.use_integral > 0">
				<view>积分抵扣：</view>
				<view class='conter'>-￥{{orderInfo.deduction_price}}</view>
			</view>
			<view class='item acea-row row-between' v-if="orderInfo.pay_postage > 0">
				<view>运费：</view>
				<view class='conter'>￥{{orderInfo.pay_postage}}</view>
			</view>
			<view class="actualPay acea-row row-right">
				实付款：<span class="money font-color-red">￥{{ orderInfo.pay_price }}</span>
			</view>
		</view>
		<view class="wrapper" v-if="
        orderInfo.delivery_type != 'fictitious' && orderInfo._status._type === 2
      ">
			<view class="item acea-row row-between">
				<view>配送方式：</view>
				<view class="conter" v-if="orderInfo.delivery_type === 'express'">
					快递
				</view>
				<view class="conter" v-if="orderInfo.delivery_type === 'send'">送货</view>
			</view>
			<view class="item acea-row row-between">
				<view v-if="orderInfo.delivery_type === 'express'">快递公司：</view>
				<view v-if="orderInfo.delivery_type === 'send'">送货人：</view>
				<view class="conter">{{ orderInfo.delivery_name }}</view>
			</view>
			<view class="item acea-row row-between">
				<view v-if="orderInfo.delivery_type === 'express'">快递单号：</view>
				<view v-if="orderInfo.delivery_type === 'send'">送货人电话：</view>
				<view class="conter">
					{{ orderInfo.delivery_id}}
				</view>
			</view>
		</view>
	</view>
</template>
<script>
	import {
		getAdminOrderDetail
	} from "@/api/admin";

	export default {
		name: "AdminOrder",
		data: function() {
			return {
				order: false,
				change: false,
				order_id: "",
				orderInfo: {
					_status: {}
				},
				status: "",
				title: "",
				payType: "",
				types: "",
				clickNum: 1,
				goname: ''
			};
		},
		watch: {
			"$route.params.oid": function(newVal) {
				let that = this;
				if (newVal != undefined) {
					that.order_id = newVal;
					that.getIndex();
				}
			}
		},
		onLoad: function(option) {
			let self = this
			this.order_id = option.id;
			this.goname = option.goname
			this.getIndex();
		},
		methods: {
			getIndex: function() {
				let that = this;
				getAdminOrderDetail(that.order_id).then(
					res => {
						that.orderInfo = res.data;
						that.types = res.data._status._type;
						that.title = res.data._status._title;
						that.payType = res.data._status._payType;
					},
					err => {
						that.$util.Tips({
							title: err
						}, {
							tab: 3,
							url: 1
						});
					}
				);
			}
		}
	};
</script>

<style>
	/*商户管理订单详情*/
	.pos-order-details .header {
		background: linear-gradient(to right, #2291f8 0%, #1cd1dc 100%);
		background: -webkit-linear-gradient(to right, #2291f8 0%, #1cd1dc 100%);
		background: -moz-linear-gradient(to right, #2291f8 0%, #1cd1dc 100%);
	}

	.pos-order-details .header .state {
		font-size: 36upx;
		color: #fff;
	}

	.pos-order-details .header .data {
		margin-left: 35upx;
		font-size: 28upx;
	}

	.pos-order-details .header .data .order-num {
		font-size: 30upx;
		margin-bottom: 8upx;
	}

	.pos-order-details .remarks {
		width: 100%;
		height: 86upx;
		background-color: #fff;
		padding: 0 30upx;
	}

	.pos-order-details .remarks .iconfont {
		font-size: 40upx;
		color: #2a7efb;
	}

	.pos-order-details .remarks input {
		width: 630upx;
		height: 100%;
		font-size: 30upx;
	}

	.pos-order-details .remarks input::placeholder {
		color: #666;
	}

	.pos-order-details .orderingUser {
		font-size: 26upx;
		color: #282828;
		padding: 0 30upx;
		height: 67upx;
		background-color: #fff;
		margin-top: 16upx;
		border-bottom: 1px solid #f5f5f5;
	}

	.pos-order-details .orderingUser .iconfont {
		font-size: 40upx;
		color: #2a7efb;
		margin-right: 15upx;
	}

	.pos-order-details .address {
		margin-top: 0;
	}

	.pos-order-details .pos-order-goods {
		margin-top: 17upx;
	}

	.pos-order-details .footer .more {
		font-size: 27upx;
		color: #aaa;
		width: 100upx;
		height: 64upx;
		text-align: center;
		line-height: 64upx;
		margin-right: 25upx;
		position: relative;
	}

	.pos-order-details .footer .delivery {
		background: linear-gradient(to right, #2291f8 0%, #1cd1dc 100%);
		background: -webkit-linear-gradient(to right, #2291f8 0%, #1cd1dc 100%);
		background: -moz-linear-gradient(to right, #2291f8 0%, #1cd1dc 100%);
	}

	.pos-order-details .footer .more .order .arrow {
		width: 0;
		height: 0;
		border-left: 11upx solid transparent;
		border-right: 11upx solid transparent;
		border-top: 20upx solid #e5e5e5;
		position: absolute;
		left: 15upx;
		bottom: -18upx;
	}

	.pos-order-details .footer .more .order .arrow:before {
		content: '';
		width: 0;
		height: 0;
		border-left: 9upx solid transparent;
		border-right: 9upx solid transparent;
		border-top: 19upx solid #fff;
		position: absolute;
		left: -10upx;
		bottom: 0;
	}

	.pos-order-details .footer .more .order {
		width: 200upx;
		background-color: #fff;
		border: 1px solid #eee;
		border-radius: 10upx;
		position: absolute;
		top: -200upx;
		z-index: 9;
	}

	.pos-order-details .footer .more .order .item {
		height: 77upx;
		line-height: 77upx;
	}

	.pos-order-details .footer .more .order .item~.item {
		border-top: 1px solid #f5f5f5;
	}

	.pos-order-details .footer .more .moreName {
		width: 100%;
		height: 100%;
	}

	/*订单详情*/
	.order-details .header {
		padding: 0 30upx;
		height: 150upx;
	}

	.order-details .header.on {
		background-color: #666 !important;
	}

	.order-details .header .pictrue {
		width: 110upx;
		height: 110upx;
	}

	.order-details .header .pictrue image {
		width: 100%;
		height: 100%;
	}

	.order-details .header .data {
		color: rgba(255, 255, 255, 0.8);
		font-size: 24upx;
		margin-left: 27upx;
	}

	.order-details .header.on .data {
		margin-left: 0;
	}

	.order-details .header .data .state {
		font-size: 30upx;
		font-weight: bold;
		color: #fff;
		margin-bottom: 7upx;
	}

	/* .order-details .header .data .time{margin-left:20upx;} */
	.order-details .nav {
		background-color: #fff;
		font-size: 26upx;
		color: #282828;
		padding: 25upx 0;
	}

	.order-details .nav .navCon {
		padding: 0 40upx;
	}

	.order-details .nav .navCon .on {
		font-weight: bold;
		color: #e93323;
	}

	.order-details .nav .progress {
		padding: 0 65upx;
		margin-top: 10upx;
	}

	.order-details .nav .progress .line {
		width: 100upx;
		height: 2upx;
		background-color: #939390;
	}

	.order-details .nav .progress .iconfont {
		font-size: 25upx;
		color: #939390;
		margin-top: -2upx;
		width: 30upx;
		height: 30upx;
		line-height: 33upx;
		text-align: center;
		margin-right: 0 !important;
	}

	.order-details .address {
		font-size: 26upx;
		color: #868686;
		background-color: #fff;
		padding: 25upx 30upx 30upx 30upx;
	}

	.order-details .address .name {
		font-size: 30upx;
		color: #282828;
		margin-bottom: 0.1rem;
	}

	.order-details .address .name .phone {
		margin-left: 40upx;
	}

	.order-details .line {
		width: 100%;
		height: 3upx;
	}

	.order-details .line image {
		width: 100%;
		height: 100%;
		display: block;
	}

	.order-details .wrapper {
		background-color: #fff;
		margin-top: 12upx;
		padding: 30upx;
	}

	.order-details .wrapper .item {
		font-size: 28upx;
		color: #282828;
	}

	.order-details .wrapper .item~.item {
		margin-top: 20upx;
	}

	.order-details .wrapper .item .conter {
		color: #868686;
		width: 500upx;
		text-align: right;
	}

	.order-details .wrapper .item .conter .copy {
		font-size: 20rpx;
		color: #333;
		border-radius: 3rpx;
		border: 1px solid #666;
		padding: 0rpx 15rpx;
		margin-left: 24rpx;
		height: 40rpx;
	}

	.order-details .wrapper .actualPay {
		border-top: 1upx solid #eee;
		margin-top: 30upx;
		padding-top: 30upx;
	}

	.order-details .wrapper .actualPay .money {
		font-weight: bold;
		font-size: 30upx;
	}

	.order-details .footer {
		width: 100%;
		height: 100upx;
		position: fixed;
		bottom: 0;
		left: 0;
		background-color: #fff;
		padding: 0 30upx;
		border-top: 1px solid #eee;
	}

	.order-details .footer .bnt {
		width: auto;
		height: 60upx;
		line-height: 60upx;
		text-align: center;
		line-height: upx;
		border-radius: 50upx;
		color: #fff;
		font-size: 27upx;
		padding: 0 3%;
	}

	.order-details .footer .bnt.cancel {
		color: #aaa;
		border: 1px solid #ddd;
	}

	.order-details .footer .bnt.default {
		color: #444;
		border: 1px solid #444;
	}

	.order-details .footer .bnt~.bnt {
		margin-left: 18upx;
	}

	.pos-order-goods {
		padding: 0 30upx;
		background-color: #fff;
	}

	.pos-order-goods .goods {
		height: 185upx;
	}

	.pos-order-goods .goods~.goods {
		border-top: 1px dashed #e5e5e5;
	}

	.pos-order-goods .goods .picTxt {
		width: 515upx;
	}

	.pos-order-goods .goods .picTxt .pictrue {
		width: 130upx;
		height: 130upx;
	}

	.pos-order-goods .goods .picTxt .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6upx;
	}

	.pos-order-goods .goods .picTxt .text {
		width: 365upx;
		height: 130upx;
	}

	.pos-order-goods .goods .picTxt .text .info {
		font-size: 28upx;
		color: #282828;
	}

	.pos-order-goods .goods .picTxt .text .attr {
		font-size: 24upx;
		color: #999;
	}

	.pos-order-goods .goods .money {
		width: 164upx;
		text-align: right;
		font-size: 28upx;
	}

	.pos-order-goods .goods .money .x-money {
		color: #282828;
	}

	.pos-order-goods .goods .money .num {
		color: #ff9600;
		margin: 5upx 0;
	}

	.pos-order-goods .goods .money .y-money {
		color: #999;
		text-decoration: line-through;
	}

	.public-total {
		font-size: 28upx;
		color: #282828;
		border-top: 1px solid #eee;
		height: 92upx;
		line-height: 92upx;
		text-align: right;
		padding: 0 30upx;
		background-color: #fff;
	}

	.public-total .money {
		color: #ff4c3c;
	}
</style>
