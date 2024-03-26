<template>
	<view class="pos-order-list" ref="container">
		<view class='search acea-row row-between-wrapper'>
			<view class='input acea-row row-between-wrapper'>
				<text class='iconfont icon-sousuo'></text>
				<input type='text' v-model='where.keywords' @confirm="inputConfirm" :focus='focus'
					:placeholder='$t(`搜索用户名/订单号/电话`)' placeholder-class='placeholder' @input="setValue"></input>
			</view>
			<view class='bnt' @tap='searchBut'>{{$t(`搜索`)}}</view>
		</view>
		<view class="nav acea-row row-around row-middle">
			<view class="item" :class="where.status == 0 ? 'on' : ''" @click="changeStatus(0)">
				{{$t(`待付款`)}}
			</view>
			<view class="item" :class="where.status == 1 ? 'on' : ''" @click="changeStatus(1)">
				{{$t(`待发货`)}}
			</view>
			<view class="item" :class="where.status == 2 ? 'on' : ''" @click="changeStatus(2)">
				{{$t(`待收货`)}}
			</view>
			<view class="item" :class="where.status == 3 ? 'on' : ''" @click="changeStatus(3)">
				{{$t(`待评价`)}}
			</view>
			<view class="item" :class="where.status == 4 ? 'on' : ''" @click="changeStatus(4)">
				{{$t(`已完成`)}}
			</view>
			<view class="item" :class="where.status == -3 ? 'on' : ''" @click="changeStatus(-3)">
				{{$t(`退款`)}}
			</view>
		</view>
		<view class="list" v-if="list.length">
			<view class="item" v-for="(item, index) in list" :key="index">
				<view class="order-num acea-row row-between-wrapper" @click="toDetail(item)">
					<view>
						<view>{{$t(`订单号`)}}：{{ item.order_id }}</view>
						<view class="time">{{$t(`下单时间`)}}：{{ item.add_time }}</view>
					</view>
					<view class="state"
						:class="(item.refund_status==0 && where.status != 0 && item.refund.length)?'on':''">
						{{item.refund_status==1?$t(`退款中`):item.refund_status==2?$t(`已退款`):item.refund_status==3?$t(`拒绝退款`):$t(item.status_name.status_name)}}
						<text
							v-if="item.refund_status==0 && where.status != 0 && item.refund.length">{{item.is_all_refund?$t(`退款中`):$t(`部分退款中`)}}</text>
					</view>
				</view>
				<view class="pos-order-goods" v-for="(val, key) in item._info" :key="key">
					<view class="goods acea-row row-between row-top" @click="toDetail(item)">
						<view class="picTxt acea-row row-between-wrapper">
							<view class="pictrue">
								<image
									:src="val.cart_info.productInfo.attrInfo?val.cart_info.productInfo.attrInfo.image:val.cart_info.productInfo.image" />
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
							<view class="x-money">
								{{$t(`￥`)}}{{ val.cart_info.productInfo.attrInfo?val.cart_info.productInfo.attrInfo.price:val.cart_info.productInfo.price }}
							</view>
							<view class="num">x{{ val.cart_info.cart_num }}</view>
							<view class="info" v-if="val.cart_info.refund_num && item._status._type !=-2">
								{{val.cart_info.refund_num}}{{$t(`件退款中`)}}
							</view>
						</view>
					</view>
				</view>
				<view class="public-total">
					{{$t(`共`)}}{{ item.total_num }}{{$t(`件商品，实付款`)}}
					<span class="money">{{$t(`￥`)}}{{ item.pay_price }}</span> ( {{$t(`邮费`)}} {{$t(`￥`)}}{{
	            item.pay_postage
	          }}
					)
				</view>
				<view class="operation acea-row row-between-wrapper">
					<view class="acea-row row-middle">
						<view class="bnt" @click="modify(item, 0)" v-if="where.status == 0">
							{{$t(`一键改价`)}}
						</view>
						<view class="bnt" @click="modify(item, 1)">{{$t(`订单备注`)}}</view>
						<view class="bnt" @click="modify(item, 2)"
							v-if="(item.refund_type == 0 || item.refund_type == 1 || item.refund_type == 5 ) && where.status == -3 && parseFloat(item.pay_price) > 0">
							{{$t(`立即退款`)}}
						</view>
						<view class="bnt" @click="agreeExpress(item)"
							v-if="where.status == -3 && item.refund_type == 2">{{$t(`同意退货`)}}</view>
						<view class="wait" v-if="where.status == -3 && item.refund_type == 4">{{$t(`待用户发货`)}}</view>
						<view class="bnt cancel" v-if="item.pay_type === 'offline' && item.paid === 0"
							@click="offlinePay(item)">
							{{$t(`确认付款`)}}
						</view>
						<navigator class="bnt"
							v-if="where.status == 1 && item.shipping_type === 1 && (item.pinkStatus === null || item.pinkStatus === 2) && !item.refund.length"
							:url="'/pages/admin/delivery/index?id='+item.order_id+'&listId='+item.id+'&totalNum='+item.total_num+'&orderStatus='+item._status+'&comeType=1'+'&virtualType='+item.virtual_type">
							{{$t(`去发货`)}}
						</navigator>
					</view>
				</view>
			</view>
		</view>
		<view v-else class="nothing">
			<image v-if="!loading" :src="imgHost + '/statics/images/no-thing.png'" alt="">
				<view v-if="!loading">{{$t(`暂无记录`)}}</view>
		</view>
		<Loading :loaded="loaded" :loading="loading"></Loading>
		<PriceChange :change="change" :orderInfo="orderInfo" :isRefund="isRefund" v-on:closechange="changeclose($event)"
			v-on:savePrice="savePrice" :status="status"></PriceChange>
	</view>
</template>

<script>
	import {
		getAdminOrderList,
		setAdminOrderPrice,
		setAdminOrderRemark,
		setAdminRefundRemark,
		setOfflinePay,
		setOrderRefund,
		agreeExpress,
		orderRefund_order
	} from "@/api/admin";
	import Loading from '@/components/Loading/index'
	import PriceChange from '../components/PriceChange/index.vue'
	import {
		HTTP_REQUEST_URL
	} from '@/config/app'
	import {
		isMoney
	} from '@/utils/validate.js'
	export default {
		name: "AdminOrderList",
		components: {
			Loading,
			PriceChange
		},
		data() {
			return {
				imgHost: HTTP_REQUEST_URL,
				current: "",
				change: false,
				types: 0,
				where: {
					keywords: '',
					page: 1,
					limit: 10,
					status: 0
				},
				list: [],
				loaded: false,
				loading: false,
				focus: false,
				orderInfo: {},
				status: "",
				isRefund: 0 //1是仅退款;0是退货退款
			};
		},
		onLoad(option) {
			let type = option.types;
			this.where.status = type;
		},
		onShow() {
			this.init();
		},
		methods: {
			setValue(event) {
				this.$set(this.where, 'keywords', event.detail.value);
			},
			inputConfirm(event) {
				if (event.detail.value) {
					uni.hideKeyboard();
					this.getIndex();
				}
			},
			searchBut() {
				let that = this;
				that.focus = false;
				that.where.page = 1;
				that.loading = false;
				that.loaded = false;
				that.$set(that, 'list', []);
				uni.showLoading({
					title: that.$t(`正在搜索中`)
				});
				that.getIndex();
			},
			// 获取数据
			getIndex() {
				let that = this;
				if (that.loading || that.loaded) return;
				that.loading = true;
				let fn
				that.where.status == -3 ? fn = orderRefund_order : fn = getAdminOrderList
				fn(that.where).then(
					res => {
						that.loading = false;
						that.loaded = res.data.length < that.where.limit;
						that.list.push.apply(that.list, res.data);
						that.where.page = that.where.page + 1;
						uni.hideLoading();
					},
					err => {
						uni.hideLoading();
						that.$util.Tips({
							title: err
						})
					}
				);
			},
			// 初始化
			init() {
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
			modify(item, status) {
				this.change = true;
				this.status = status.toString();
				this.orderInfo = item;
				if (status == 2) {
					this.isRefund = 1
				}
			},
			changeclose(msg) {
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
							})
							that.init();
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
							that.init();
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
						return this.$util.Tips({
							title: that.$t(`请输入备注`)
						})
					}
					data.remark = remark;
					let obj = '';
					if (that.where.status == -3) {
						obj = setAdminRefundRemark(data);
					} else {
						obj = setAdminOrderRemark(data);
					}
					obj.then(
						res => {
							that.change = false;
							this.$util.Tips({
								title: res.msg,
								icon: 'success'
							})
							that.init();
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
			agreeExpress(item) {
				let that = this;
				agreeExpress({
					id: item.id
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
			toDetail(item) {
				uni.navigateTo({
					url: `/pages/admin/orderDetail/index?id=${item.order_id}&types=${this.where.status}`
				})
			},
			offlinePay(item) {
				setOfflinePay({
					order_id: item.order_id
				}).then(
					res => {
						this.$util.Tips({
							title: res.msg,
							icon: "success"
						});
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

<style lang="scss" scoped>
	.pos-order-list {
		padding-top: 96rpx;

		.search {
			padding-left: 30rpx;
			padding-top: 30rpx;
			background-color: #fff;
			position: fixed;
			top: 0;
			left: 0;
			z-index: 99;

			.input {
				width: 598rpx;
				background-color: #f7f7f7;
				border-radius: 33rpx;
				padding: 0 35rpx;
				box-sizing: border-box;
				height: 66rpx;
			}

			.input input {
				width: 472rpx;
				font-size: 28rpx;
			}

			.input .placeholder {
				color: #999;
			}

			.input .iconfont {
				color: #555;
				font-size: 35rpx;
			}

			.bnt {
				width: 120rpx;
				text-align: center;
				height: 66rpx;
				line-height: 66rpx;
				font-size: 30rpx;
				color: #282828;
			}
		}
	}

	.pos-order-list .nav {
		width: 100%;
		height: 96upx;
		background-color: #fff;
		font-size: 28rpx;
		color: #282828;
		position: fixed;
		top: 96rpx;
		left: 0;
		z-index: 99;
	}

	.pos-order-list .nav .item.on {
		color: #2291f8;
	}

	.pos-order-list .list {
		margin-top: 120upx;
	}

	.pos-order-list .nothing {
		margin-top: 220upx;
		text-align: center;
		color: #cfcfcf;
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

	.pos-order-list .list .item .order-num .state {
		color: #2291f8;
		font-weight: normal;
		font-size: 24rpx;
	}

	.pos-order-list .list .item .order-num .state.on {
		font-size: 24rpx;
		width: 150rpx;
		text-align: right;
	}

	.pos-order-list .list .item .order-num .time {
		font-size: 26upx;
		font-weight: normal;
		color: #999;
	}

	.pos-order-list .list .item .operation {
		padding: 20upx 30upx;
		margin-top: 3upx;
		display: flex;
		justify-content: right;
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

	.pos-order-list .list .item .operation .wait {
		margin-left: 30rpx;
		color: orangered;
	}

	.pos-order-goods {
		padding: 0 30upx;
		background-color: #fff;
	}

	.pos-order-goods .goods {
		padding: 28rpx 0;
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

	.pos-order-goods .goods .money .info {
		margin-top: 18rpx;
		font-size: 24rpx;
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
