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
</template>

<script setup>
	import { ref, watch, nextTick } from 'vue';
	import { onLoad, onShow } from '@dcloudio/uni-app';
	import { storeToRefs } from 'pinia';
	import countDown from '@/components/countDown';
	import numberScroll from '@/components/numberScroll.vue'
	import {
		getCashierOrder as getCashierOrderApi,
		orderPay
	} from '@/api/order.js';
	import {
		basicConfig
	} from '@/api/public.js'
	// #ifdef H5
	import Auth from '@/libs/wechat';
	// #endif
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';
	import { openPaySubscribe } from '@/utils/subscribeMessage.js';
	import { useAppStore } from '@/store/modules/app';

	defineOptions({ name: 'Cashier' });

	const emit = defineEmits(['onChangeFun']);

	const appStore = useAppStore();
	const { uid } = storeToRefs(appStore);

	// ---------- data ----------
	const checked = ref(false);
	const datatime = ref(1676344056);
	//支付方式
	const cartArr = ref([{
			"name": i18n.t(`微信支付`),
			"icon": "icon-weixin2",
			value: 'weixin',
			title: i18n.t(`使用微信快捷支付`),
			payStatus: 1,
		},
		{
			"name": i18n.t(`支付宝支付`),
			"icon": "icon-zhifubao",
			value: 'alipay',
			title: i18n.t(`使用支付宝支付`),
			payStatus: 1,
		},
		{
			"name": i18n.t(`余额支付`),
			"icon": "icon-yuezhifu",
			value: 'yue',
			title: i18n.t(`可用余额`),
			payStatus: 1,
		},
		{
			"name": i18n.t(`线下支付`),
			"icon": "icon-yuezhifu1",
			value: 'offline',
			title: i18n.t(`使用线下付款`),
			payStatus: 2,
		}, {
			"name": i18n.t(`好友代付`),
			"icon": "icon-haoyoudaizhifu",
			value: 'friend',
			title: i18n.t(`找微信好友支付`),
			payStatus: 1,
		}
	]);
	const orderId = ref(0);
	const fromType = ref('');
	const active = ref(0);
	// 当前选中的支付方式 value（原 data 未声明，由 watch 动态写入的实例属性，补声明为 ref）
	const paytype = ref('');
	// 当前选中支付方式对应的余额数字（原 data 未声明，由 getCashierOrder 动态写入，补声明为 ref）
	const number = ref(0);
	// 是否正在拉起支付（原 data 未声明，恒为 undefined 的实例属性写入）
	const toPay = ref(false);
	// 是否好友代付场景（原 data 未声明，恒为 undefined 的实例属性读取）
	const friendPay = ref(false);
	const payPrice = ref(0);
	const payPriceShow = ref(0);
	const payPostage = ref(0);
	const offlinePostage = ref(false);
	const invalidTime = ref(0);
	const initIn = ref(false);
	const jumpData = ref({
		orderId: '',
		msg: ''
	});
	const formContent = ref('');
	const oid = ref(0);
	const is_gift = ref(0);

	// 遗留判断：本页从未声明 BargainId/combinationId/pinkId/seckillId/discountId，
	// 原 this.xxx 属性访问恒为 undefined（分支恒不进入），以同名 undefined 变量保持同等语义
	var BargainId, combinationId, pinkId, seckillId, discountId;

	// ---------- watch ----------
	watch(cartArr, (newV, oldValue) => {
		let newPayList = [];
		newV.forEach((item, index) => {
			if (item.payStatus) {
				item.index = index;
				newPayList.push(item)
			}
		});
		nextTick(e => {
			active.value = newPayList[0].index;
			paytype.value = newPayList[0].value;
		})

	}, {
		immediate: true,
		deep: true
	});

	// ---------- 生命周期 ----------
	onLoad((options) => {
		if (options.order_id) orderId.value = options.order_id
		if (options.from_type) fromType.value = options.from_type
		getBasicConfig()
	});
	onShow(() => {
		let options = wx.getEnterOptionsSync();
		if (options.scene == '1038' && options.referrerInfo.appId == 'wxef277996acc166c3' && initIn.value) {
			// 代表从收银台小程序返回
			let extraData = options.referrerInfo.extraData;
			initIn.value = false
			if (!extraData) {
				// "当前通过物理按键返回，未接收到返参，建议自行查询交易结果";
				util.Tips({
					title: i18n.t(`取消支付`)
				}, {
					tab: 5,
					url: `/pages/goods/order_pay_status/index?order_id=${orderId.value}&msg=${i18n.t(`取消支付`)}&type=3&totalPrice=${payPriceShow.value}&status=2`
				});
			} else {
				if (extraData.code == 'success') {
					let url = `/pages/goods/order_pay_status/index?order_id=${orderId.value}&msg=${jumpData.value.msg}&type=3&totalPrice=${payPriceShow.value}`
					if (is_gift.value) url += '&is_gift=1'
					util.Tips({
						title: i18n.t(`支付成功`),
						icon: 'success'
					}, {
						tab: 5,
						url
					});
				} else if (extraData.code == 'cancel') {
					// "支付已取消";
					util.Tips({
						title: i18n.t(`取消支付`)
					}, {
						tab: 5,
						url: `/pages/goods/order_pay_status/index?order_id=${orderId.value}&msg=${i18n.t(`取消支付`)}&type=3&totalPrice=${payPriceShow.value}&status=2`
					});
				} else {
					// "支付失败：" + extraData.errmsg;
					uni.reLaunch({
						url: `/pages/goods/order_pay_status/index?order_id=${orderId.value}&msg=${i18n.t(`支付失败`)}&totalPrice=${payPriceShow.value}`
					})
				}
			}
		}
	});

	// ---------- methods ----------
	function getBasicConfig() {
		basicConfig().then(res => {
			//微信支付是否开启
			cartArr.value[0].payStatus = res.data.pay_weixin_open || 0
			//支付宝是否开启
			cartArr.value[1].payStatus = res.data.ali_pay_status || 0;
			//#ifdef MP
			cartArr.value[1].payStatus = 0;
			//#endif
			//余额支付是否开启
			cartArr.value[2].payStatus = res.data.yue_pay_status
			if (res.data.offline_pay_status) {
				cartArr.value[3].payStatus = 1
			} else {
				cartArr.value[3].payStatus = 0
			}
			//好友代付是否开启
			cartArr.value[4].payStatus = res.data.friend_pay_status || 0;
			getCashierOrder()
		}).catch(err => {
			uni.hideLoading();
			return util.Tips({
				title: err
			})
		})
	}
	function getCashierOrder() {
		uni.showLoading({
			title: i18n.t(`创建订单中`)
		});
		getCashierOrderApi(orderId.value, fromType.value).then(res => {
			payPrice.value = payPriceShow.value = res.data.pay_price
			payPostage.value = res.data.pay_postage
			offlinePostage.value = res.data.offline_postage
			invalidTime.value = res.data.invalid_time
			cartArr.value[2].number = res.data.now_money;
			number.value = Number(res.data.now_money) || 0;
			oid.value = res.data.oid
			is_gift.value = res.data.is_gift
			uni.hideLoading();
			// 积分兑换等免费订单，支付金额为0，自动完成支付
			if (parseFloat(payPrice.value) <= 0) {
				autoZeroPay();
			}
		}).catch(err => {
			uni.hideLoading();
			return util.Tips({
				title: err
			})
		})
	}
	/**
	 * 自动完成0元支付（积分兑换等免费订单）
	 */
	function autoZeroPay() {
		uni.showLoading({
			title: i18n.t(`支付中`)
		});
		orderPay({
			uni: orderId.value,
			paytype: 'free',
			type: 0,
			// #ifdef H5
			quitUrl: location.port ? location.protocol + '//' + location.hostname + ':' + location
				.port +
				'/pages/goods/order_details/index?order_id=' + orderId.value : location.protocol +
				'//' + location.hostname +
				'/pages/goods/order_details/index?order_id=' + orderId.value
			// #endif
			// #ifdef APP-PLUS
			quitUrl: '/pages/goods/order_details/index?order_id=' + orderId.value
			// #endif
		}).then(res => {
			let goPage = '/pages/goods/order_pay_status/index?order_id=' + orderId.value + '&msg=' + res.msg + '&type=3' + '&totalPrice=' + payPriceShow.value
			if (is_gift.value) goPage += '&is_gift=1'
			uni.hideLoading();
			if (res.data.status === 'success') {
				util.Tips({
					title: i18n.t(`支付成功`),
					icon: 'success'
				}, {
					tab: 5,
					url: goPage
				});
			} else {
				util.Tips({
					title: res.msg || i18n.t(`支付失败`)
				}, {
					tab: 5,
					url: goPage
				});
			}
		}).catch(err => {
			uni.hideLoading();
			return util.Tips({
				title: err
			});
		});
	}
	// 入参重命名 selNumber/selPaytype：避免与同名 ref（number/paytype）在函数内互相遮蔽
	function payType(selNumber, selPaytype, index) {
		active.value = index;
		paytype.value = selPaytype;
		number.value = selNumber;
		if (offlinePostage.value) {
			if (selPaytype == 'offline') {
				payPriceShow.value = util.$h.Sub(payPrice.value, payPostage.value);
			} else {
				payPriceShow.value = payPrice.value;
			}

		}
	}
	function formpost(url, postData) {
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
		nextTick(e => {
			tempform.submit();
		})
	}
	function waitPay() {
		uni.reLaunch({
			url: '/pages/goods/order_pay_status/index?order_id=' + orderId.value + '&msg=' + i18n.t(`取消支付`) + '&type=3' +
				'&status=2&totalPrice=' + payPriceShow.value
		})
	}
	// 入参 number/paytype 为模板传入的当前选中值（与同名 ref 无关）
	async function goPay(number, paytype) {
		if (!orderId.value) return util.Tips({
			title: i18n.t(`请选择要支付的订单`)
		});
		if (paytype == 'yue' && parseFloat(number) < parseFloat(payPriceShow.value)) return util.Tips({
			title: i18n.t(`余额不足`)
		});
		uni.showLoading({
			title: i18n.t(`支付中`)
		});
		if (paytype == 'friend' && orderId.value) {
			uni.hideLoading();
			return uni.navigateTo({
				url: '/pages/users/payment_on_behalf/index?oid=' + oid.value + '&spread=' +
					uid.value,
				success: res => {},
				fail: () => {},
				complete: () => {}
			});
		}
		await openPaySubscribe().catch(() => {});
		orderPay({
			uni: orderId.value,
			paytype: paytype,
			type: friendPay.value ? 1 : 0,
			// #ifdef H5
			quitUrl: location.port ? location.protocol + '//' + location.hostname + ':' + location
				.port +
				'/pages/goods/order_details/index?order_id=' + orderId.value : location.protocol +
				'//' + location.hostname +
				'/pages/goods/order_details/index?order_id=' + orderId.value
			// #endif
			// #ifdef APP-PLUS
			quitUrl: '/pages/goods/order_details/index?order_id=' + orderId.value
			// #endif
		}).then(res => {
			let goPage = '/pages/goods/order_pay_status/index?order_id=' + orderId.value + '&msg=' + res.msg + '&type=3' + '&totalPrice=' + payPriceShow.value
			if (is_gift.value) goPage += '&is_gift=1'
			let status = res.data.status,
				// 局部变量重命名 resultOrderId：避免与同名 ref（orderId）在回调内遮蔽
				resultOrderId = res.data.result.order_id,
				jsConfig = res.data.result.jsConfig,
				goPages = goPage,
				// 局部变量重命名 friendPayUrl：避免与同名 ref（friendPay）在回调内遮蔽
				friendPayUrl = '/pages/users/payment_on_behalf/index?order_id=' + orderId.value +
				'&spread=' +
				uid.value
			switch (status) {
				case 'ORDER_EXIST':
				case 'EXTEND_ORDER':
					uni.hideLoading();
					return util.Tips({
						title: res.msg
					}, {
						tab: 5,
						url: goPages
					});
				case 'ALLINPAY_PAY':
					uni.hideLoading();
					// #ifdef MP
					initIn.value = true
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
					jumpData.value = {
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
					formpost(res.data.result.pay_url, jsConfig)
					// #endif
					break;
				case 'PAY_ERROR':
					uni.hideLoading();
					return util.Tips({
						title: res.msg
					}, {
						tab: 5,
						url: goPages
					});
					break;
				case 'SUCCESS':
					uni.hideLoading();
					if (paytype !== 'friend') {
						return util.Tips({
							title: res.msg,
							icon: 'success'
						}, {
							tab: 4,
							url: goPages
						});
					} else {
						return util.Tips({
							title: res.msg,
							icon: 'success'
						}, {
							tab: 4,
							url: friendPayUrl
						});
					}


					break;
				case 'WECHAT_PAY':
					toPay.value = true;
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
							if (BargainId || combinationId || pinkId ||
								seckillId || discountId)
								return util.Tips({
									title: i18n.t(`支付成功`),
									icon: 'success'
								}, {
									tab: 4,
									url: goPages
								});
							return util.Tips({
								title: i18n.t(`支付成功`),
								icon: 'success'
							}, {
								tab: 5,
								url: goPages
							});
						},
						fail: function(e) {
							uni.hideLoading();
							return util.Tips({
								title: i18n.t(`取消支付`)
							}, {
								tab: 5,
								url: goPages + '&status=2'
							});
						},
						complete: function(e) {
							uni.hideLoading();
							//关闭当前页面跳转至订单状态
							if (res.errMsg == 'requestPayment:cancel' || e.errMsg ==
								'requestOrderPayment:cancel') return util
								.Tips({
									title: i18n.t(`取消支付`)
								}, {
									tab: 5,
									url: goPages + '&status=2'
								});
						},
					})
					// #endif
					// #ifdef H5
					Auth.pay(res.data.result.jsConfig).then(res => {
						return util.Tips({
							title: i18n.t(`支付成功`),
							icon: 'success'
						}, {
							tab: 5,
							url: goPages
						});
					}).catch(res => {
						if (!Auth.isWeixin()) {
							uni.redirectTo({
								url: goPages + '&msg=' + i18n.t(`支付失败`) +
									'&status=2'
								// '&msg=支付失败&status=2'
							})
						}
						if (res.errMsg == 'chooseWXPay:cancel') return util.Tips({
							title: i18n.t(`取消支付`)
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
								title: i18n.t(`支付成功`)
							})
							setTimeout(res => {
								uni.redirectTo({
									url: url
								})
							}, 2000)
						},
						fail: (e) => {
							let url = '/pages/goods/order_pay_status/index?order_id=' +
								resultOrderId +
								'&msg=' + i18n.t(`支付失败`);
							uni.showModal({
								content: i18n.t(`支付失败`),
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
					return util.Tips({
						title: res.msg
					}, {
						tab: 5,
						url: goPages + '&status=1'
					});
					break;

				case "WECHAT_H5_PAY":
					uni.hideLoading();
					util.Tips({
						title: i18n.t(`等待支付中`)
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
					util.Tips({
						title: i18n.t(`等待支付中`)
					}, {
						tab: 4,
						url: goPages + '&status=0'
					});
					formContent.value = res.data.result.jsConfig;
					setTimeout(() => {
						document.getElementById('alipaysubmit').submit();
					}, 1500);
					//#endif
					// #ifdef MP
					uni.navigateTo({
						url: `/pages/users/alipay_invoke/index?id=${resultOrderId}&link=${jsConfig.qrCode}`
					});
					// #endif
					// #ifdef APP-PLUS
					uni.requestPayment({
						provider: 'alipay',
						orderInfo: jsConfig,
						success: (e) => {
							uni.showToast({
								title: i18n.t(`支付成功`)
							})
							let url = '/pages/goods/order_pay_status/index?order_id=' +
								resultOrderId +
								'&msg=' + i18n.t(`支付成功`);
							setTimeout(res => {
								uni.redirectTo({
									url: url
								})
							}, 2000)

						},
						fail: (e) => {
							let url = '/pages/goods/order_pay_status/index?order_id=' +
								resultOrderId +
								'&msg=' + i18n.t(`支付失败`);
							uni.showModal({
								content: i18n.t(`支付失败`),
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
			return util.Tips({
				title: err
			}, () => {
				emit('onChangeFun', {
					action: 'pay_fail'
				});
			});
		})
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

:deep(.red){
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
