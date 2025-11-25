<template>
	<view class="page" v-if="payPriceShow">
		<view class="pay-price">
			<view class="price">
				<text class="unit">{{$t(`￥`)}}</text>
				<numberScroll :num='payPriceShow' color="#E93323" width='30' height='50' fontSize='50'></numberScroll>
			</view>
			<view class="count-down">
				{{$t(`支付剩余时间`)}}：
				<countDown :is-day="false" :tip-text="' '" :day-text="' '" :hour-text="' : '" :minute-text="' : '"
					:second-text="' '" :datatime="invalidTime"></countDown>
			</view>
		</view>
		<view class="payment">
			<view class="title">
				{{$t(`支付方式`)}}
			</view>
			<view class="item acea-row row-between-wrapper" v-for="(item,index) in cartArr" :key="index"
				v-show='item.payStatus' @click="payType(item.number || 0, item.value, index)">
				<view class="left acea-row row-between-wrapper">
					<view class="iconfont" :class="item.icon"></view>
					<view class="text">
						<view class="name">{{$t(item.name)}}</view>
						<view class="info" v-if="item.value == 'yue'">
							{{$t(item.title)}} <span class="money">{{$t(`￥`)}}{{ item.number }}</span>
						</view>
						<view class="info" v-else>{{$t(item.title)}}</view>
					</view>
				</view>
				<view class="iconfont" :class="active==index?'icon-xuanzhong11 font-num':'icon-weixuan'"></view>
			</view>
		</view>
		<view class="btn">
			<view class="button acea-row row-center-wrapper" @click='goPay(number, paytype)'>{{$t(`确认支付`)}}</view>
			<view class="wait-pay" @click="waitPay">{{$t(`暂不支付`)}}</view>
		</view>
		<view v-show="false" v-html="formContent"></view>
	</view>
	</view>
</template>

<script>
	import countDown from '@/components/countDown';
	import numberScroll from '@/components/numberScroll.vue'
	import {
		getCashierOrder,
		orderPay
	} from '@/api/order.js';
	import {
		basicConfig
	} from '@/api/public.js'
	export default {
		components: {
			countDown,
			numberScroll
		},
		data() {
			return {
				checked: false,
				datatime: 1676344056,
				//支付方式
				cartArr: [{
						"name": this.$t(`微信支付`),
						"icon": "icon-weixin2",
						value: 'weixin',
						title: this.$t(`使用微信快捷支付`),
						payStatus: 1,
					},
					{
						"name": this.$t(`支付宝支付`),
						"icon": "icon-zhifubao",
						value: 'alipay',
						title: this.$t(`使用支付宝支付`),
						payStatus: 1,
					},
					{
						"name": this.$t(`余额支付`),
						"icon": "icon-yuezhifu",
						value: 'yue',
						title: this.$t(`可用余额`),
						payStatus: 1,
					},
					{
						"name": this.$t(`线下支付`),
						"icon": "icon-yuezhifu1",
						value: 'offline',
						title: this.$t(`使用线下付款`),
						payStatus: 2,
					}, {
						"name": this.$t(`好友代付`),
						"icon": "icon-haoyoudaizhifu",
						value: 'friend',
						title: this.$t(`找微信好友支付`),
						payStatus: 1,
					}
				],
				orderId: 0,
				fromType: '',
				active: 0,
				payPrice: 0,
				payPriceShow: 0,
				payPostage: 0,
				offlinePostage: false,
				invalidTime: 0,
				initIn: false,
				jumpData: {
					orderId: '',
					msg: ''
				},
				formContent: '',
				oid: 0,
				is_gift: 0
			}
		},
		watch: {
			cartArr: {
				handler(newV, oldValue) {
					let newPayList = [];
					newV.forEach((item, index) => {
						if (item.payStatus) {
							item.index = index;
							newPayList.push(item)
						}
					});
					this.$nextTick(e => {
						this.active = newPayList[0].index;
						this.paytype = newPayList[0].value;
					})

				},
				immediate: true,
				deep: true
			}
		},
		onLoad(options) {
			if (options.order_id) this.orderId = options.order_id
			if (options.from_type) this.fromType = options.from_type
			this.getBasicConfig()
		},
		onShow() {
			let options = wx.getEnterOptionsSync();
			if (options.scene == '1038' && options.referrerInfo.appId == 'wxef277996acc166c3' && this.initIn) {
				// 代表从收银台小程序返回
				let extraData = options.referrerInfo.extraData;
				this.initIn = false
				if (!extraData) {
					// "当前通过物理按键返回，未接收到返参，建议自行查询交易结果";
					this.$util.Tips({
						title: this.$t(`取消支付`)
					}, {
						tab: 5,
						url: `/pages/goods/order_pay_status/index?order_id=${this.orderId}&msg=${this.$t(`取消支付`)}&type=3&totalPrice=${this.payPriceShow}&status=2`
					});
				} else {
					if (extraData.code == 'success') {
						let url = `/pages/goods/order_pay_status/index?order_id=${this.orderId}&msg=${this.jumpData.msg}&type=3&totalPrice=${this.payPriceShow}`
						if(this.is_gift) url += '&is_gift=1'
						this.$util.Tips({
							title: this.$t(`支付成功`),
							icon: 'success'
						}, {
							tab: 5,
							url
						});
					} else if (extraData.code == 'cancel') {
						// "支付已取消";
						this.$util.Tips({
							title: this.$t(`取消支付`)
						}, {
							tab: 5,
							url: `/pages/goods/order_pay_status/index?order_id=${this.orderId}&msg=${this.$t(`取消支付`)}&type=3&totalPrice=${this.payPriceShow}&status=2`
						});
					} else {
						// "支付失败：" + extraData.errmsg;
						uni.reLaunch({
							url: `/pages/goods/order_pay_status/index?order_id=${this.orderId}&msg=${this.$t(`支付失败`)}&totalPrice=${this.payPriceShow}`
						})
					}
				}
			}
		},
		methods: {
			getBasicConfig() {
				basicConfig().then(res => {
					//微信支付是否开启
					this.cartArr[0].payStatus = res.data.pay_weixin_open || 0
					//支付宝是否开启
					this.cartArr[1].payStatus = res.data.ali_pay_status || 0;
					//#ifdef MP
					this.cartArr[1].payStatus = 0;
					//#endif
					//余额支付是否开启
					this.cartArr[2].payStatus = res.data.yue_pay_status
					if (res.data.offline_pay_status) {
						this.cartArr[3].payStatus = 1
					} else {
						this.cartArr[3].payStatus = 0
					}
					//好友代付是否开启
					this.cartArr[4].payStatus = res.data.friend_pay_status || 0;
					this.getCashierOrder()
				}).catch(err => {
					uni.hideLoading();
					return this.$util.Tips({
						title: err
					})
				})
			},
			getCashierOrder() {
				uni.showLoading({
					title: this.$t(`创建订单中`)
				});
				getCashierOrder(this.orderId, this.fromType).then(res => {
					this.payPrice = this.payPriceShow = res.data.pay_price
					this.payPostage = res.data.pay_postage
					this.offlinePostage = res.data.offline_postage
					this.invalidTime = res.data.invalid_time
					this.cartArr[2].number = res.data.now_money;
					this.number = Number(res.data.now_money) || 0;
					this.oid = res.data.oid
					this.is_gift = res.data.is_gift
					uni.hideLoading();
				}).catch(err => {
					uni.hideLoading();
					return this.$util.Tips({
						title: err
					})
				})
			},
			payType(number, paytype, index) {
				this.active = index;
				this.paytype = paytype;
				this.number = number;
				if (this.offlinePostage) {
					if (paytype == 'offline') {
						this.payPriceShow = this.$util.$h.Sub(this.payPrice, this.payPostage);
					} else {
						this.payPriceShow = this.payPrice;
					}

				}
			},
			formpost(url, postData) {
				let tempform = document.createElement("form");
				tempform.action = url;
				tempform.method = "post";
				tempform.target = "_self";
				tempform.style.display = "none";
				for (let x in postData) {
					let opt = document.createElement("input");
					opt.name = x;
					opt.value = postData[x];
					tempform.appendChild(opt);
				}
				document.body.appendChild(tempform);
				this.$nextTick(e => {
					tempform.submit();
				})
			},
			waitPay() {
				uni.reLaunch({
					url: '/pages/goods/order_pay_status/index?order_id=' + this.orderId + '&msg=取消支付&type=3' +
						'&status=2&totalPrice=' + this.payPriceShow
				})
			},
			goPay(number, paytype) {
				let that = this;
				if (!that.orderId) return that.$util.Tips({
					title: that.$t(`请选择要支付的订单`)
				});
				if (paytype == 'yue' && parseFloat(number) < parseFloat(that.payPriceShow)) return that.$util.Tips({
					title: that.$t(`余额不足`)
				});
				uni.showLoading({
					title: that.$t(`支付中`)
				});
				if (paytype == 'friend' && that.orderId) {
					uni.hideLoading();
					return uni.navigateTo({
						url: '/pages/users/payment_on_behalf/index?oid=' + that.oid + '&spread=' +
							this.$store.state.app.uid,
						success: res => {},
						fail: () => {},
						complete: () => {}
					});
				}
				orderPay({
					uni: that.orderId,
					paytype: paytype,
					type: that.friendPay ? 1 : 0,
					// #ifdef H5
					quitUrl: location.port ? location.protocol + '//' + location.hostname + ':' + location
						.port +
						'/pages/goods/order_details/index?order_id=' + this.orderId : location.protocol +
						'//' + location.hostname +
						'/pages/goods/order_details/index?order_id=' + this.orderId
					// #endif
					// #ifdef APP-PLUS
					quitUrl: '/pages/goods/order_details/index?order_id=' + this.orderId
					// #endif
				}).then(res => {
					let goPage = '/pages/goods/order_pay_status/index?order_id=' + this.orderId + '&msg=' + res.msg + '&type=3' + '&totalPrice=' + this.payPriceShow
					if(this.is_gift) goPage += '&is_gift=1'
					let status = res.data.status,
						orderId = res.data.result.order_id,
						jsConfig = res.data.result.jsConfig,
						goPages = goPage,
						friendPay = '/pages/users/payment_on_behalf/index?order_id=' + this.orderId +
						'&spread=' +
						this
						.$store.state.app.uid
					switch (status) {
						case 'ORDER_EXIST':
						case 'EXTEND_ORDER':
							uni.hideLoading();
							return that.$util.Tips({
								title: res.msg
							}, {
								tab: 5,
								url: goPages
							});
						case 'ALLINPAY_PAY':
							uni.hideLoading();
							// #ifdef MP
							this.initIn = true
							wx.openEmbeddedMiniProgram({
								appId: 'wxef277996acc166c3',
								extraData: {
									cusid: jsConfig.cusid,
									appid: jsConfig.appid,
									version: jsConfig.version,
									trxamt: jsConfig.trxamt,
									reqsn: jsConfig.reqsn,
									notify_url: jsConfig.notify_url,
									body: jsConfig.body,
									remark: jsConfig.remark,
									validtime: jsConfig.validtime,
									randomstr: jsConfig.randomstr,
									paytype: jsConfig.paytype,
									sign: jsConfig.sign,
									signtype: jsConfig.signtype
								}
							})
							this.jumpData = {
								orderId: res.data.result.order_id,
								msg: res.msg,
							}
							// #endif
							// #ifdef APP-PLUS
							plus.runtime.openURL(jsConfig.payinfo);
							setTimeout(e => {
								uni.reLaunch({
									url: goPages
								})
							}, 1000)
							// #endif
							// #ifdef H5
							this.formpost(res.data.result.pay_url, jsConfig)
							// #endif
							break;
						case 'PAY_ERROR':
							uni.hideLoading();
							return that.$util.Tips({
								title: res.msg
							}, {
								tab: 5,
								url: goPages
							});
							break;
						case 'SUCCESS':
							uni.hideLoading();
							if (paytype !== 'friend') {
								return that.$util.Tips({
									title: res.msg,
									icon: 'success'
								}, {
									tab: 4,
									url: goPages
								});
							} else {
								return that.$util.Tips({
									title: res.msg,
									icon: 'success'
								}, {
									tab: 4,
									url: friendPay
								});
							}


							break;
						case 'WECHAT_PAY':
							that.toPay = true;
							// #ifdef MP
							/* that.toPay = true; */
							let mp_pay_name = ''
							if (uni.requestOrderPayment) {
								mp_pay_name = 'requestOrderPayment'
							} else {
								mp_pay_name = 'requestPayment'
							}
							uni[mp_pay_name]({
								timeStamp: jsConfig.timestamp,
								nonceStr: jsConfig.nonceStr,
								package: jsConfig.package,
								signType: jsConfig.signType,
								paySign: jsConfig.paySign,
								success: function(res) {
									uni.hideLoading();
									if (that.BargainId || that.combinationId || that.pinkId ||
										that
										.seckillId || that.discountId)
										return that.$util.Tips({
											title: that.$t(`支付成功`),
											icon: 'success'
										}, {
											tab: 4,
											url: goPages
										});
									return that.$util.Tips({
										title: that.$t(`支付成功`),
										icon: 'success'
									}, {
										tab: 5,
										url: goPages
									});
								},
								fail: function(e) {
									uni.hideLoading();
									return that.$util.Tips({
										title: that.$t(`取消支付`)
									}, {
										tab: 5,
										url: goPages + '&status=2'
									});
								},
								complete: function(e) {
									uni.hideLoading();
									//关闭当前页面跳转至订单状态
									if (res.errMsg == 'requestPayment:cancel' || e.errMsg ==
										'requestOrderPayment:cancel') return that.$util
										.Tips({
											title: that.$t(`取消支付`)
										}, {
											tab: 5,
											url: goPages + '&status=2'
										});
								},
							})
							// #endif
							// #ifdef H5
							this.$wechat.pay(res.data.result.jsConfig).then(res => {
								return that.$util.Tips({
									title: that.$t(`支付成功`),
									icon: 'success'
								}, {
									tab: 5,
									url: goPages
								});
							}).catch(res => {
								if (!this.$wechat.isWeixin()) {
									uni.redirectTo({
										url: goPages + '&msg=' + that.$t(`支付失败`) +
											'&status=2'
										// '&msg=支付失败&status=2'
									})
								}
								if (res.errMsg == 'chooseWXPay:cancel') return that.$util.Tips({
									title: that.$t(`取消支付`)
								}, {
									tab: 5,
									url: goPages + '&status=2'
								});
							})
							// #endif
							// #ifdef APP-PLUS
							uni.requestPayment({
								provider: 'wxpay',
								orderInfo: jsConfig,
								success: (e) => {
									let url = goPages;
									uni.showToast({
										title: that.$t(`支付成功`)
									})
									setTimeout(res => {
										uni.redirectTo({
											url: url
										})
									}, 2000)
								},
								fail: (e) => {
									let url = '/pages/goods/order_pay_status/index?order_id=' +
										orderId +
										'&msg=' + that.$t(`支付失败`);
									uni.showModal({
										content: that.$t(`支付失败`),
										showCancel: false,
										success: function(res) {
											if (res.confirm) {
												uni.redirectTo({
													url: url
												})
											} else if (res.cancel) {}
										}
									})
								},
								complete: () => {
									uni.hideLoading();
								},
							});
							// #endif
							break;
						case 'PAY_DEFICIENCY':
							uni.hideLoading();
							//余额不足
							return that.$util.Tips({
								title: res.msg
							}, {
								tab: 5,
								url: goPages + '&status=1'
							});
							break;

						case "WECHAT_H5_PAY":
							uni.hideLoading();
							that.$util.Tips({
								title: that.$t(`等待支付中`)
							}, {
								tab: 4,
								url: goPages + '&status=0'
							});
							setTimeout(() => {
								location.href = res.data.result.jsConfig.h5_url;
							}, 1500);
							break;

						case 'ALIPAY_PAY':
							//#ifdef H5
							uni.hideLoading();
							that.$util.Tips({
								title: that.$t(`等待支付中`)
							}, {
								tab: 4,
								url: goPages + '&status=0'
							});
							that.formContent = res.data.result.jsConfig;
							setTimeout(() => {
								document.getElementById('alipaysubmit').submit();
							}, 1500);
							//#endif
							// #ifdef MP
							uni.navigateTo({
								url: `/pages/users/alipay_invoke/index?id=${orderId}&link=${jsConfig.qrCode}`
							});
							// #endif
							// #ifdef APP-PLUS
							uni.requestPayment({
								provider: 'alipay',
								orderInfo: jsConfig,
								success: (e) => {
									uni.showToast({
										title: that.$t(`支付成功`)
									})
									let url = '/pages/goods/order_pay_status/index?order_id=' +
										orderId +
										'&msg=' + that.$t(`支付成功`);
									setTimeout(res => {
										uni.redirectTo({
											url: url
										})
									}, 2000)

								},
								fail: (e) => {
									let url = '/pages/goods/order_pay_status/index?order_id=' +
										orderId +
										'&msg=' + that.$t(`支付失败`);
									uni.showModal({
										content: that.$t(`支付失败`),
										showCancel: false,
										success: function(res) {
											if (res.confirm) {
												uni.redirectTo({
													url: url
												})
											} else if (res.cancel) {}
										}
									})
								},
								complete: () => {
									uni.hideLoading();
								},
							});
							// #endif
							break;
					}

				}).catch(err => {
					uni.hideLoading();
					return that.$util.Tips({
						title: err
					}, () => {
						that.$emit('onChangeFun', {
							action: 'pay_fail'
						});
					});
				})
			}
		}

	}
</script>

<style lang="scss" scoped>
	.page {
		.pay-price {
			display: flex;
			justify-content: center;
			flex-direction: column;
			align-items: center;
			padding: 50rpx 0 40rpx 0;

			.price {
				color: #E93323;
				margin-bottom: 20rpx;
				display: flex;
				align-items: flex-end;

				.unit {
					font-size: 34rpx;
					font-weight: 500;
					line-height: 41rpx;
				}

				.num {
					font-size: 50rpx;
					font-weight: 600;
				}
			}

			.count-down {
				display: flex;
				align-items: center;
				background-color: #fff;
				padding: 8rpx 28rpx;
				border-radius: 40rpx;
				font-size: 24rpx;
				color: #E93323;

				.time {
					margin-top: 0 !important;
				}

				/deep/.red {
					margin: 0 !important;
				}
			}
		}

		.payment {
			width: 690rpx;
			border-radius: 14rpx 14rpx;
			background-color: #fff;
			z-index: 999;
			margin: 0 30rpx;

			.title {
				color: #666666;
				font-size: 26rpx;
				padding: 30rpx 0 0 30rpx;
			}

			.payMoney {
				font-size: 28rpx;
				color: #333333;
				text-align: center;
				margin-top: 50rpx;

				.font-color {
					margin-left: 10rpx;

					.money {
						font-size: 40rpx;
					}
				}
			}


		}

		.payment.on {
			transform: translate3d(0, 0, 0);
		}

		.icon-xuanzhong11 {
			color: #E93323 !important;
		}

		.payment .item {
			border-bottom: 1rpx solid #eee;
			height: 130rpx;
			margin-left: 30rpx;
			padding-right: 30rpx;
		}

		.payment .item:last-child {
			border-bottom: none;
		}

		.payment .item .left {
			flex: 1;
		}

		.payment .item .left .text {
			flex: 1;
		}

		.payment .item .left .text .name {
			font-size: 30rpx;
			color: #333;
		}

		.payment .item .left .text .info {
			font-size: 22rpx;
			color: #999;
		}

		.payment .item .left .text .info .money {
			color: #ff9900;
		}

		.payment .item .left .iconfont {
			font-size: 50rpx;
			color: #09bb07;
			margin-right: 28rpx;
		}

		.payment .item .left .iconfont.icon-zhifubao {
			color: #00aaea;
		}

		.payment .item .left .iconfont.icon-yuezhifu {
			color: #ff9900;
		}

		.payment .item .left .iconfont.icon-yuezhifu1 {
			color: #eb6623;
		}

		.payment .item .left .iconfont.icon-tonglianzhifu1 {
			color: #305fd8;
		}

		.payment .item .iconfont {
			font-size: 40rpx;
			color: #ccc;
		}

		.icon-haoyoudaizhifu {
			color: #F34C3E !important;
		}

		.btn {
			position: fixed;
			left: 30rpx;
			display: flex;
			flex-direction: column;
			align-items: center;
			bottom: 30rpx;
			bottom: calc(30rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
			bottom: calc(30rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		}

		.wait-pay {
			color: #aaa;
			font-size: 24rpx;
			padding-top: 20rpx;
		}

		.button {
			width: 690rpx;
			height: 90rpx;
			border-radius: 45rpx;
			color: #FFFFFF;
			background-color: #E93323;

		}

	}
</style>
