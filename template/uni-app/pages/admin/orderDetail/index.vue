<template>
	<view class="order-details pos-order-details">
		<view class="header acea-row row-middle">
			<view class="state">{{ title }}</view>
			<view class="data">
				<view class="order-num">{{$t(`订单`)}}：{{ orderInfo.order_id }}</view>
				<view>
					<span class="time">{{ orderInfo._add_time }}</span>
				</view>
			</view>
		</view>
		<view class="remarks acea-row row-between-wrapper" v-if="goname != 'looks'">
			<span class="iconfont icon-zhinengkefu-"></span>
			<input class="line1" style="text-align: left;" :value="
          orderInfo.remark ? orderInfo.remark : $t(`订单未备注，点击添加备注信息`)
        " disabled @click="modify('1')" />
		</view>
		<view class="orderingUser acea-row row-middle">
			<span class="iconfont icon-yonghu2"></span>{{ orderInfo.nickname }}
		</view>
		<view class="address">
			<view class="name">
				{{ orderInfo.real_name
        }}<span class="phone">{{ orderInfo.user_phone }}</span>
				<!-- #ifdef H5 -->
				<span class="copy copy-data"
					:data-clipboard-text="`${orderInfo.real_name} ${orderInfo.user_phone} ${orderInfo.user_address}`">{{$t(`复制`)}}</span>
				<!-- #endif -->
				<!-- #ifndef H5 -->
				<span class="copy copy-data"
					@click="copyNum(`${orderInfo.real_name} ${orderInfo.user_phone} ${orderInfo.user_address}`)">{{$t(`复制`)}}</span>
				<!-- #endif -->
			</view>
			<view>{{ orderInfo.user_address }}</view>
		</view>
		<view class="line">
			<image src="/static/images/line.jpg" />
		</view>
		<view class="pos-order-goods">
			<navigator :url="`/pages/goods_details/index?id=${item.productInfo.product_id ? item.productInfo.product_id : item.productInfo.id}`" hover-class="none"
				class="goods acea-row row-between-wrapper" v-for="(item, index) in orderInfo.cartInfo" :key="index">
				<view class="picTxt acea-row row-between-wrapper">
					<view class="pictrue">
						<image :src="item.productInfo.image" />
					</view>
					<view class="text acea-row row-between row-column">
						<view class="info line2">
							{{ item.productInfo.store_name }}
						</view>
						<view class="attr">{{ item.productInfo.attrInfo.suk }}</view>
					</view>
				</view>
				<view class="money">
					<view class="x-money">{{$t(`￥`)}}{{ item.productInfo.price }}</view>
					<view class="num">x{{ item.cart_num }}</view>
					<view class="y-money" v-if='item.productInfo.attrInfo'>{{$t(`￥`)}}{{ item.productInfo.attrInfo.ot_price }}</view>
					<view class="y-money" v-else>{{$t(`￥`)}}{{ item.productInfo.ot_price }}</view>
				</view>
			</navigator>
		</view>
		<view class="public-total" v-if="orderInfo.total_num">
			{{$t(`共`)}}{{ orderInfo.total_num }}{{$t(`件商品，应支付`)}}
			<span class="money">{{$t(`￥`)}}{{ orderInfo.pay_price }}</span> ( {{$t(`邮费`)}} {{$t(`￥`)}}{{
        orderInfo.pay_postage
      }}
			)
		</view>
		<view class="wrapper" v-if="orderInfo.order_id">
			<view class="item acea-row row-between">
				<view>{{$t(`订单编号`)}}：</view>
				<view class="conter acea-row row-middle row-right">
					{{ orderInfo.order_id
          }}
					<!-- #ifdef H5 -->
					<span class="copy copy-data" :data-clipboard-text="orderInfo.order_id">{{$t(`复制`)}}</span>
					<!-- #endif -->
					<!-- #ifndef H5 -->
					<span class="copy copy-data" @click="copyNum(orderInfo.order_id)">{{$t(`复制`)}}</span>
					<!-- #endif -->
				</view>
			</view>
			<view class="item acea-row row-between">
				<view>{{$t(`下单时间`)}}：</view>
				<view class="conter">{{ orderInfo._add_time }}</view>
			</view>
			<view class="item acea-row row-between">
				<view>{{$t(`支付状态`)}}：</view>
				<view class="conter">
					{{ orderInfo.paid == 1 ? $t(`已支付`) : $t(`未支付`) }}
				</view>
			</view>
			<view class="item acea-row row-between">
				<view>{{$t(`支付方式`)}}：</view>
				<view class="conter">{{ payType }}</view>
			</view>
			<view class="item acea-row row-between">
				<view>{{$t(`买家留言`)}}：</view>
				<view class="conter">{{ orderInfo.mark }}</view>
			</view>
		</view>
		<view class='wrapper' v-if="customForm && customForm.length">
			<view class='item acea-row row-between' v-for="(item,index) in customForm" :key="index">
				<view class='upload' v-if="item.label == 'img'">
					<view>{{item.title}}：</view>
					<view class='pictrue' v-for="(img,index) in item.value" :key="index">
						<image :src='img'></image>
					</view>
				</view>
				<view v-if="item.label !== 'img'">{{item.title}}：</view>
				<view v-if="item.label !== 'img'" class='conter'>{{item.value}}</view>
			</view>
		</view>
		<view class="wrapper">
			<view class='item acea-row row-between'>
				<view>{{$t(`商品总价`)}}：</view>
				<view class='conter'>
					{{$t(`￥`)}}{{(parseFloat(orderInfo.total_price || 0)+parseFloat(orderInfo.vip_true_price || 0)).toFixed(2)}}
				</view>
			</view>
			<view class='item acea-row row-between' v-if="orderInfo.pay_postage > 0">
				<view>{{$t(`配送运费`)}}：</view>
				<view class='conter'>{{$t(`￥`)}}{{parseFloat(orderInfo.pay_postage).toFixed(2)}}</view>
			</view>
			<view v-if="orderInfo.levelPrice > 0" class='item acea-row row-between'>
				<view>{{$t(`用户等级优惠`)}}：</view>
				<view class='conter'>-{{$t(`￥`)}}{{parseFloat(orderInfo.levelPrice).toFixed(2)}}</view>
			</view>
			<view v-if="orderInfo.memberPrice > 0" class='item acea-row row-between'>
				<view>{{$t(`付费会员优惠`)}}：</view>
				<view class='conter'>-{{$t(`￥`)}}{{parseFloat(orderInfo.memberPrice).toFixed(2)}}</view>
			</view>
			<view class='item acea-row row-between' v-if='orderInfo.coupon_price > 0'>
				<view>{{$t(`优惠券抵扣`)}}：</view>
				<view class='conter'>-{{$t(`￥`)}}{{parseFloat(orderInfo.coupon_price).toFixed(2)}}</view>
			</view>
			<view class='item acea-row row-between' v-if="orderInfo.use_integral > 0">
				<view>{{$t(`积分抵扣`)}}：</view>
				<view class='conter'>-{{$t(`￥`)}}{{parseFloat(orderInfo.deduction_price).toFixed(2)}}</view>
			</view>
			<view class='actualPay acea-row row-right'>{{$t(`实付款`)}}：<text class='money'>{{$t(`￥`)}}{{parseFloat(orderInfo.pay_price || 0).toFixed(2)}}</text></view>
		</view>

		<view class="wrapper" v-if="
        orderInfo.delivery_type != 'fictitious' && orderInfo._status._type === 2
      ">
			<view class="item acea-row row-between">
				<view>{{$t(`配送方式`)}}：</view>
				<view class="conter" v-if="orderInfo.delivery_type === 'express'">
					{{$t(`快递`)}}
				</view>
				<view class="conter" v-if="orderInfo.delivery_type === 'send'">{{$t(`送货`)}}</view>
			</view>
			<view class="item acea-row row-between">
				<view v-if="orderInfo.delivery_type === 'express'">{{$t(`快递公司`)}}：</view>
				<view v-if="orderInfo.delivery_type === 'send'">{{$t(`送货人`)}}：</view>
				<view class="conter">{{ orderInfo.delivery_name }}</view>
			</view>
			<view class="item acea-row row-between">
				<view v-if="orderInfo.delivery_type === 'express'">{{$t(`快递单号`)}}：</view>
				<view v-if="orderInfo.delivery_type === 'send'">{{$t(`送货人电话`)}}：</view>
				<view class="conter">
					{{ orderInfo.delivery_id}}
					<!-- #ifdef H5 -->
					<span class="copy copy-data" :data-clipboard-text="orderInfo.delivery_id">{{$t(`复制`)}}</span>
					<!-- #endif -->
					<!-- #ifndef H5 -->
					<span class="copy copy-data" @click="copyNum(orderInfo.delivery_id)">{{$t(`复制`)}}</span>
					<!-- #endif -->
				</view>
			</view>
		</view>
		<view style="height:120upx;"></view>
		<view class="footer acea-row row-right row-middle" v-if="goname != 'looks'">
			<view class="more"></view>
			<view class="bnt cancel" @click="modify('0')" v-if="types == 0">
				{{$t(`一键改价`)}}
			</view>
			<view class="bnt cancel" @click="modify('2')" v-if="types == -1 && orderInfo.refund_type == 1">
				{{$t(`立即退款`)}}
			</view>
			<view class="bnt cancel" @click="agreeExpress(orderInfo.id)"
				v-if="types == -1 && orderInfo.refund_type == 2">
				{{$t(`同意退货`)}}
			</view>
			<view class="wait" v-if="types == -1 && orderInfo.refund_type == 4">{{$t(`待用户发货`)}}</view>
			<view class="bnt cancel" @click="modify('1')">{{$t(`订单备注`)}}</view>
			<view class="bnt cancel" v-if="orderInfo.pay_type === 'offline' && orderInfo.paid === 0"
				@click="offlinePay">
				{{$t(`确认付款`)}}
			</view>
			<navigator class='bnt cancel'
				v-if="orderInfo.delivery_type == 'express' && orderInfo.status==1"
				hover-class='none' :url="'/pages/goods/goods_logistics/index?is_admin=1&orderId='+ orderInfo.order_id">
				{{$t(`查看物流`)}}
			</navigator>
			<navigator class="bnt delivery"
				v-if="types == 1 && orderInfo.shipping_type === 1 && (orderInfo.pinkStatus === null || orderInfo.pinkStatus === 2)"
				:url="'/pages/admin/delivery/index?id='+orderInfo.order_id">{{$t(`去发货`)}}</navigator>
		</view>
		<PriceChange :change="change" :orderInfo="orderInfo" v-on:closechange="changeclose($event)"
			v-on:savePrice="savePrice" :status="status"></PriceChange>
	</view>
</template>
<script>
	import PriceChange from "../components/PriceChange/index.vue";
	// #ifdef H5
	import ClipboardJS from "@/plugin/clipboard/clipboard.js";
	// #endif
	import {
		getAdminOrderDetail,
		getAdminRefundOrderDetail,
		setAdminOrderPrice,
		setAdminOrderRemark,
		setAdminRefundRemark,
		setOfflinePay,
		setOrderRefund,
		agreeExpress
	} from "@/api/admin";
	// import { required, num } from "@utils/validate";
	// import { validatorDefaultCatch } from "@utils/dialog";
	import {
		isMoney
	} from '@/utils/validate.js'

	export default {
		name: "AdminOrder",
		components: {
			PriceChange
		},
		props: {},
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
				order_type: "",
				clickNum: 1,
				goname: '',
				customForm: []
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
		onShow() {
			this.getIndex();
		},
		onLoad(option) {
			let self = this
			this.order_id = option.id;
			this.goname = option.goname
			this.order_type = option.types
			// #ifdef H5
			this.$nextTick(function() {
				var clipboard = new ClipboardJS('.copy-data');
				// var copybtn = document.getElementsByClassName("copy-data");
				// var clipboard = new Clipboard(copybtn);
				clipboard.on('success', function(e) {
					self.$util.Tips({
						title: self.$t(`复制成功`)
					})
				});
				clipboard.on('error', function(e) {
					self.$util.Tips({
						title: self.$t(`复制失败`)
					})
				});
			});
			// #endif

		},
		methods: {
			more: function() {
				this.order = !this.order;
			},
			modify(status) {
				this.change = true;
				this.status = status;
			},
			changeclose: function(msg) {
				this.change = msg;
			},
			getIndex: function() {
				let that = this;
				let fn = this.order_type == -3 ? getAdminRefundOrderDetail : getAdminOrderDetail
				fn(that.order_id).then(
					res => {
						that.orderInfo = res.data;
						//处理自定义留言非必填项的数据展示

						that.types = res.data._status._type;
						that.title = res.data._status._title;
						that.payType = res.data._status._payType;
						if (that.orderInfo.custom_form && that.orderInfo.custom_form.length) {
							let arr = []
							that.orderInfo.custom_form.map(i => {
								if (i.value != '') {
									arr.push(i)
								}
							})
							that.$set(that, 'customForm', arr);
						}
					},
					err => {
						// that.$util.Tips({
						// 	title: err
						// }, {
						// 	tab: 3,
						// 	url: 1
						// });
					}
				);
			},
			agreeExpress(id) {
				let that = this;
				agreeExpress({
					id
				}).then(res => {
					that.$util.Tips({
						title: res.msg
					});
					that.init();
				}).catch(err => {
					that.$util.Tips({
						title: err
					});
				})
			},
			async savePrice(opt) {
				let that = this,
					data = {},
					price = opt.price,
					refund_price = opt.refund_price,
					refund_status = that.orderInfo.refund_status,
					remark = opt.remark;
				data.order_id = that.orderInfo.order_id;
				if (that.status == 0) {
					if (!isMoney(price)) {
						return that.$util.Tips({
							title: that.$t(`请输入正确的金额`)
						});
					}
					data.price = price;
					setAdminOrderPrice(data).then(
						res => {
							that.change = false;
							that.$util.Tips({
								title: that.$t(`改价成功`),
								icon: 'success'
							}, '/pages/admin/orderDetail/index?id=' + res.data.order_id + '&types=0')
						},
						err => {
							that.change = false;
							that.$util.Tips({
								title: that.$t(`改价失败`),
								icon: 'none'
							})
						}
					);
				} else if (that.status == 2) {
					if (!isMoney(refund_price)) {
						return that.$util.Tips({
							title: that.$t(`请输入正确的金额`)
						});
					}
					data.price = refund_price;
					data.type = opt.type;
					setOrderRefund(data).then(
						res => {
							that.change = false;
							that.$util.Tips({
								title: res.msg
							});
							that.getIndex();
						},
						err => {
							that.change = false;
							that.$util.Tips({
								title: err
							});
						}
					);
				} else {
					if (!remark) {
						return that.$util.Tips({
							title: that.$t(`请输入备注`)
						})
					}
					data.remark = remark;
					let obj
					if (that.order_type == -3) {
						obj = setAdminRefundRemark(data);
					} else {
						obj = setAdminOrderRemark(data);
					}
					obj.then(
						res => {
							that.change = false;
							that.$util.Tips({
								title: res.msg,
								icon: 'success'
							})
							that.getIndex();
						},
						err => {
							that.change = false;
							that.$util.Tips({
								title: err
							});
						}
					);
				}
			},
			offlinePay: function() {
				setOfflinePay({
					order_id: this.orderInfo.order_id
				}).then(
					res => {
						this.$util.Tips({
							title: res.msg,
							icon: 'success'
						});
						this.getIndex();
					},
					err => {
						this.$util.Tips({
							title: err
						});
					}
				);
			},
			// #ifndef H5
			copyNum(id) {

				uni.setClipboardData({
					data: id,
					success: function() {}
				});
			},
			// #endif
			// #ifdef H5
			webCopy(item, index) {
				let items = item
				let indexs = index
				let self = this

				if (self.clickNum == 1) {
					self.clickNum += 1
					self.webCopy(items, indexs)
				}
			}
			// #endif
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
		display: flex;
		align-items: center;
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
		text-align: right;
	}

	.order-details .wrapper .item .conter .copy {
		font-size: 20rpx;
		color: #333;
		border-radius: 3rpx;
		border: 1px solid #666;
		padding: 2rpx 15rpx;
		margin-left: 24rpx;
	}

	.order-details .wrapper .actualPay {
		border-top: 1upx solid #eee;
		margin-top: 30upx;
		padding-top: 30upx;
	}

	.order-details .wrapper .actualPay .money {
		font-weight: bold;
		font-size: 30upx;
		color: #e93323;
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

	.wait {
		margin-right: 30rpx;
		color: orangered;
	}

	.order-details .footer .bnt~.bnt {
		margin-left: 18upx;
	}

	.pos-order-goods {
		padding: 0 30upx;
		background-color: #fff;
	}

	.pos-order-goods .goods {
		min-height: 185upx;
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
		display: flex;
		justify-content: space-between;
		flex-direction: column;
		flex-wrap: nowrap;
		/* height: 132upx; */
	}

	.pos-order-goods .goods .picTxt .text .info {
		font-size: 28upx;
		color: #282828;
	}

	.pos-order-goods .goods .picTxt .text .attr {
		font-size: 24upx;
		color: #999;
		width: 100%;
		word-break: break-all;
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

	.copy-data {
		font-size: 10px;
		color: #333;
		-webkit-border-radius: 1px;
		border-radius: 1px;
		border: 1px solid #666;
		padding: 0px 7px;
		margin-left: 12px;
	}

	.upload .pictrue {
		display: inline-block;
		margin: 22rpx 17rpx 20rpx 0;
		width: 156rpx;
		height: 156rpx;
		color: #bbb;
	}

	.upload .pictrue image {
		width: 100%;
		height: 100%;
	}
</style>
