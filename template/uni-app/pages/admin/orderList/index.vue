<template>
	<view class="pos-order-list" ref="container">
		<view class="nav acea-row row-around row-middle">
			<view class="item" :class="where.status == 0 ? 'on' : ''" @click="changeStatus(0)">
				待付款
			</view>
			<view class="item" :class="where.status == 1 ? 'on' : ''" @click="changeStatus(1)">
				待发货
			</view>
			<view class="item" :class="where.status == 2 ? 'on' : ''" @click="changeStatus(2)">
				待收货
			</view>
			<view class="item" :class="where.status == 3 ? 'on' : ''" @click="changeStatus(3)">
				待评价
			</view>
			<view class="item" :class="where.status == 4 ? 'on' : ''" @click="changeStatus(4)">
				已完成
			</view>
			<view class="item" :class="where.status == -3 ? 'on' : ''" @click="changeStatus(-3)">
				退款
			</view>
		</view>
		<view class="list">
			<view class="item" v-for="(item, index) in list" :key="index">
				<view class="order-num acea-row row-middle" @click="toDetail(item)">
					订单号：{{ item.order_id }}
					<span class="time">下单时间：{{ item.add_time }}</span>
				</view>
				<view class="pos-order-goods" v-for="(val, key) in item._info" :key="key">
					<view class="goods acea-row row-between-wrapper" @click="toDetail(item)">
						<view class="picTxt acea-row row-between-wrapper">
							<view class="pictrue">
								<image :src="val.cart_info.productInfo.image" />
							</view>
							<view class="text acea-row row-between row-column">
								<view class="info line2">
									{{ val.cart_info.productInfo.store_name }}
								</view>
								<view class="attr" v-if="val.cart_info.productInfo.attrInfo">
									{{ val.cart_info.productInfo.attrInfo.suk }}
								</view>
							</view>
						</view>
						<view class="money">
							<view class="x-money">￥{{ val.cart_info.productInfo.price }}</view>
							<view class="num">x{{ val.cart_info.cart_num }}</view>
							<view class="y-money">
								￥{{ val.cart_info.productInfo.ot_price }}
							</view>
						</view>
					</view>
				</view>
				<view class="public-total">
					共{{ item.total_num }}件商品，应支付
					<span class="money">￥{{ item.pay_price }}</span> ( 邮费 ¥{{
	            item.total_postage
	          }}
					)
				</view>
				<view class="operation acea-row row-between-wrapper">
					<view class="more">
						<!--            <view class="iconfont icon-gengduo" @click="more(index)"></view>-->
						<!--            <view class="order" v-show="current === index">-->
						<!--              <view class="items">-->
						<!--                {{ where.status > 0 ? "删除" : "取消" }}订单-->
						<!--              </view>-->
						<!--              <view class="arrow"></view>-->
						<!--            </view>-->
					</view>
					<view class="acea-row row-middle">
						<view class="bnt" @click="modify(item, 0)" v-if="where.status == 0">
							一键改价
						</view>
						<view class="bnt" @click="modify(item, 1)">订单备注</view>
						<view class="bnt" @click="modify(item, 0)" v-if="where.status == -3 && item.refund_status === 1">
							立即退款
						</view>
						<view class="bnt cancel" v-if="item.pay_type === 'offline' && item.paid === 0" @click="offlinePay(item)">
							确认付款
						</view>
						<navigator class="bnt" v-if="where.status == 1 && item.shipping_type === 1" :url="'/pages/admin/delivery/index?id='+item.order_id">去发货
						</navigator>
					</view>
				</view>
			</view>
		</view>
		<Loading :loaded="loaded" :loading="loading"></Loading>
		<PriceChange :change="change" :orderInfo="orderInfo" v-on:closechange="changeclose($event)" v-on:savePrice="savePrice"
		 :status="status"></PriceChange>
	</view>
</template>

<script>
	import {
		getAdminOrderList,
		setAdminOrderPrice,
		setAdminOrderRemark,
		setOfflinePay,
		setOrderRefund
	} from "@/api/admin";
	import Loading from '@/components/Loading/index'
	import PriceChange from '@/components/PriceChange/index'
	import { isMoney } from '@/utils/validate.js'
	export default {
		name: "AdminOrderList",
		components: {
			Loading,
			PriceChange
		},
		data() {
			return {
				current: "",
				change: false,
				types: 0,
				where: {
					page: 1,
					limit: 10,
					status: 0
				},
				list: [],
				loaded: false,
				loading: false,
				orderInfo: {},
				status: ""
			};
		},
		watch: {
			"$route": function (n){
				if (n.params.types) {
					this.where.status = n.params.types;
				}
				this.init();
			},
			types: function() {
				this.getIndex();
			}
		},
		onLoad(option) {
			this.where.status = option.types
			this.current = "";
			this.getIndex();
		},
		methods: {
			// 获取数据
			getIndex: function() {
				let that = this;
				if (that.loading || that.loaded) return;
				that.loading = true;
				getAdminOrderList(that.where).then(
					res => {
						that.loading = false;
						that.loaded = res.data.length < that.where.limit;
						that.list.push.apply(that.list, res.data);
						that.where.page = that.where.page + 1;
					},
					err => {
						that.$util.Tips({
							title: err
						})
					}
				);
			},
			// 初始化
			init: function() {
				this.list = [];
				this.where.page = 1;
				this.loaded = false;
				this.loading = false;
				this.getIndex();
				this.current = "";
			},
			// 导航切换
			changeStatus(val) {
				if (this.where.status != val) {
					this.where.status = val;
					this.init();
				}
			},
			// 商品操作
			modify: function(item, status) {
				let temp = status.toString()
				this.change = true;
				this.orderInfo = item;
				this.status = temp;
			},
			changeclose: function(msg) {
				this.change = msg;
			},
			async savePrice(opt) {
				let that = this,
					data = {},
					price = opt.price,
					refund_price = opt.refund_price,
					refund_status = that.orderInfo.refund_status,
					remark = opt.remark;
				data.order_id = that.orderInfo.order_id;
				if (that.status == 0 && refund_status === 0) {
					if(!isMoney(price)){
						return that.$util.Tips({title: '请输入正确的金额'});
					}
					data.price = price;
					setAdminOrderPrice(data).then(
						res => {
							that.change = false;
							that.$util.Tips({
								title:'改价成功',
								icon:'success'
							})
							that.init();
						},
						err => {
							that.change = false;
							that.$util.Tips({
								title:'改价失败',
								icon:'none'
							})
						}
					);
				} else if (that.status == 0 && refund_status === 1) {
					if(!isMoney(refund_price)){
						return that.$util.Tips({title: '请输入正确的金额'});
					}
					data.price = refund_price;
					data.type = opt.type;
					setOrderRefund(data).then(
						res => {
							that.change = false;
							that.$util.Tips({title: res.msg});
							that.init();
						},
						err => {
							console.log(err,'err')
							that.change = false;
							that.$util.Tips({title: err});
						}
					);
				} else {
					
					if(!remark){
						return this.$util.Tips({
							title:'请输入备注'
						})
					}
					data.remark = remark;
					setAdminOrderRemark(data).then(
						res => {
							that.change = false;
							this.$util.Tips({
								title:res.msg,
								icon:'success'
							})
							that.init();
						},
						err => {
							that.change = false;
							that.$util.Tips({title: err});
						}
					);
				}
			},
			toDetail(item){
				uni.navigateTo({
					url:`/pages/admin/orderDetail/index?id=${item.order_id}`
				})
			},
			offlinePay: function(item) {
			      console.log(item);
			      setOfflinePay({ order_id: item.order_id }).then(
			        res => {
			          this.$util.Tips({title:res.msg,icon:"success"});
			          this.init();
			        },
			        error => {
			          this.$util.Tips(error);
			        }
			      );
			    }
		},
		onReachBottom() {
			this.getIndex()
		}
	}
</script>

<style>
	.pos-order-list .nav {
		width: 100%;
		height: 96upx;
		background-color: #fff;
		font-size: 30upx;
		color: #282828;
		position: fixed;
		top: 0;
		left: 0;
		z-index: 9999;
	}

	.pos-order-list .nav .item.on {
		color: #2291f8;
	}

	.pos-order-list .list {
		margin-top: 120upx;
	}

	.pos-order-list .list .item {
		background-color: #fff;
		width: 100%;
	}

	.pos-order-list .list .item~.item {
		margin-top: 24upx;
	}

	.pos-order-list .list .item .order-num {
		height: 124upx;
		border-bottom: 1px solid #eee;
		font-size: 30upx;
		font-weight: bold;
		color: #282828;
		padding: 0 30upx;
	}

	.pos-order-list .list .item .order-num .time {
		font-size: 26upx;
		font-weight: normal;
		color: #999;
		margin-top: -40upx;
	}

	.pos-order-list .list .item .operation {
		padding: 20upx 30upx;
		margin-top: 3upx;
	}

	.pos-order-list .list .item .operation .more {
		position: relative;
	}

	.pos-order-list .list .item .operation .icon-gengduo {
		font-size: 50upx;
		color: #aaa;
	}

	.pos-order-list .list .item .operation .order .arrow {
		width: 0;
		height: 0;
		border-left: 11upx solid transparent;
		border-right: 11upx solid transparent;
		border-top: 20upx solid #e5e5e5;
		position: absolute;
		left: 15upx;
		bottom: -18upx;
	}

	.pos-order-list .list .item .operation .order .arrow:before {
		content: '';
		width: 0;
		height: 0;
		border-left: 7upx solid transparent;
		border-right: 7upx solid transparent;
		border-top: 20upx solid #fff;
		position: absolute;
		left: -7upx;
		bottom: 0;
	}

	.pos-order-list .list .item .operation .order {
		width: 200upx;
		background-color: #fff;
		border: 1px solid #eee;
		border-radius: 10upx;
		position: absolute;
		top: -100upx;
		z-index: 9;
	}

	.pos-order-list .list .item .operation .order .items {
		height: 77upx;
		line-height: 77upx;
		text-align: center;
	}

	.pos-order-list .list .item .operation .order .items~.items {
		border-top: 1px solid #f5f5f5;
	}

	.pos-order-list .list .item .operation .bnt {
		font-size: 28upx;
		color: #5c5c5c;
		width: 170upx;
		height: 60upx;
		border-radius: 30upx;
		border: 1px solid #bbb;
		text-align: center;
		line-height: 60upx;
	}

	.pos-order-list .list .item .operation .bnt~.bnt {
		margin-left: 14upx;
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
		width: 100%;
		font-size: 28upx;
		color: #282828;
	}

	.pos-order-goods .goods .picTxt .text .attr {
		width: 100%;
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
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
