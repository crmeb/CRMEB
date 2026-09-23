<template>
	<view class="page" v-if="payPriceShow" :style="colorStyle">
		<view class="pay-price">
			<view class="price">
				<text class="unit">{{$t(`￥`)}}</text>
				<numberScroll :num='payPriceShow' color="var(--view-theme, #E93323)" width='30' height='50' fontSize='50'></numberScroll>
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
					<image v-if="isImageUrl(item.icon)" :src="item.icon" class="pay-icon-img"></image>
					<view v-else-if="item.icon" class="iconfont" :class="item.icon"></view>
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
	import { storeToRefs } from 'pinia';
	import routes from '../api/routes.js';
	import countDown from '@/components/countDown';
	import numberScroll from '@/components/numberScroll.vue'
	import {
		getCashierOrder as getCashierOrderApi,
		orderPay
	} from '@/api/order.js';
	import {
		getPayConfig
	} from '@/api/public.js'
	import util from '@/utils/util.js';
	import { getMiniPaymentFailure, requestMiniPayment } from '@/utils/miniPayment.js';
	import i18n from '@/utils/lang.js';
	import { openPaySubscribe } from '@/utils/subscribeMessage.js';
	import Auth from '@/libs/wechat';
	import { useAppStore } from '@/store/modules/app';
	import { useColorTheme } from '@/composables/useColorTheme';

	defineOptions({ name: 'CashierEngine' });

	const emit = defineEmits(['onChangeFun']);

	const { colorStyle } = useColorTheme();

	const appStore = useAppStore();
	const { uid } = storeToRefs(appStore);

	const checked = ref(false);
	const datatime = ref(1676344056);
	//支付方式（从 pay/config 驱动动态获取）
	const cartArr = ref([]);
	const orderId = ref(0);
	const fromType = ref('');
	const active = ref(0);
	// 当前选中的支付方式 value（如 'weixin'/'yue'/'offline'/'friend'）
	const paytype = ref('');
	// 当前选中支付方式对应的余额或附加数字（余额支付时为用户余额，其他为 0）
	const number = ref(0);
	// 是否正在拉起支付（防止重复点击）
	const toPay = ref(false);
	// 是否好友代付场景
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

	// 遗留判断：本引擎从未声明 BargainId/combinationId/pinkId/seckillId/discountId（继承自旧收银页），
	// 原 this.xxx 属性访问恒为 undefined（分支恒不进入），以同名 undefined 变量保持同等语义
	var BargainId, combinationId, pinkId, seckillId, discountId;

	watch(cartArr, (newV, oldValue) => {
		let newPayList = [];
		newV.forEach((item, index) => {
			if (item.payStatus) {
				item.index = index;
				newPayList.push(item)
			}
		});
		if (newPayList.length) {
			nextTick(e => {
				active.value = newPayList[0].index;
				paytype.value = newPayList[0].value;
			})
		}
	}, {
		immediate: true,
		deep: true
	});

	// 从页面 onLoad 参数初始化引擎状态（供页面组件外部调用）
	function initFromOptions(options) {
		if (options.order_no) orderId.value = options.order_no
		if (options.from_type) fromType.value = options.from_type
		getCashierOrder()
	}
	// 处理页面 onShow：从收银台小程序返回的场景（供页面组件外部调用）
	function handlePageShow() {
		// #ifdef MP-WEIXIN
		if (typeof wx.getEnterOptionsSync !== 'function') return;
		let options = wx.getEnterOptionsSync();
		if (options.scene == '1038' && options.referrerInfo.appId == 'wxef277996acc166c3' && initIn.value) {
			// 代表从收银台小程序返回
			let extraData = options.referrerInfo.extraData;
			initIn.value = false
			if (!extraData) {
				// "当前通过物理按键返回，未接收到返参，建议自行查询交易结果";
				util.Tips({
					title: i18n.global.t(`取消支付`)
				}, {
					tab: 5,
					url: `${routes.payStatus}?order_no=${orderId.value}&msg=${i18n.global.t(`取消支付`)}&type=3&totalPrice=${payPriceShow.value}&status=2`
				});
			} else {
				if (extraData.code == 'success') {
					// 支付成功跳转至支付结果页
					let url = `${routes.payStatus}?order_no=${orderId.value}&msg=${i18n.global.t(`支付成功`)}&type=3&totalPrice=${payPriceShow.value}&status=0`
					if (is_gift.value) url += '&is_gift=1'
					util.Tips({
						title: i18n.global.t(`支付成功`),
						icon: 'success'
					}, {
						tab: 5,
						url
					});
				} else if (extraData.code == 'cancel') {
					// "支付已取消";
					util.Tips({
						title: i18n.global.t(`取消支付`)
					}, {
						tab: 5,
						url: `${routes.payStatus}?order_no=${orderId.value}&msg=${i18n.global.t(`取消支付`)}&type=3&totalPrice=${payPriceShow.value}&status=2`
					});
				} else {
					// "支付失败：" + extraData.errmsg;
					uni.reLaunch({
						url: `${routes.payStatus}?order_no=${orderId.value}&msg=${i18n.global.t(`支付失败`)}&totalPrice=${payPriceShow.value}`
					})
				}
			}
		}
		// #endif
	}
	// 判断 icon 字段是否为图片地址，空值不渲染图标
	function isImageUrl(icon) {
		if (!icon) return false;
		return /^(https?:)?\/\//.test(icon) || /^\//.test(icon);
	}
	function getCashierOrder() {
		uni.showLoading({
			title: i18n.global.t(`创建订单中`)
		});
		// 并行获取支付方式列表和订单信息
		Promise.all([
			getPayConfig(),
			getCashierOrderApi(orderId.value, fromType.value)
		]).then(([payConfigRes, orderRes]) => {
			// 从支付驱动获取支付方式列表
			cartArr.value = payConfigRes.data || [];
			// 更新余额支付方式的余额
			const yueItem = cartArr.value.find(item => item.value === 'yue');
			if (yueItem) {
				yueItem.number = orderRes.data.now_money;
			}
			payPrice.value = payPriceShow.value = orderRes.data.pay_price
			payPostage.value = orderRes.data.pay_postage
			offlinePostage.value = orderRes.data.offline_postage
			invalidTime.value = orderRes.data.invalid_time
			number.value = Number(orderRes.data.now_money) || 0;
			oid.value = orderRes.data.oid
			is_gift.value = orderRes.data.is_gift
			uni.hideLoading();
		}).catch(err => {
			uni.hideLoading();
			return util.Tips({
				title: err
			})
		})
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
			url: routes.payStatus + '?order_no=' + orderId.value + '&msg=取消支付&type=3' +
				'&status=2&totalPrice=' + payPriceShow.value
		})
	}
	async function goPay(number, paytype) {
		if (!orderId.value) return util.Tips({
			title: i18n.global.t(`请选择要支付的订单`)
		});
		if (paytype == 'yue' && parseFloat(number) < parseFloat(payPriceShow.value)) return util.Tips({
			title: i18n.global.t(`余额不足`)
		});
		uni.showLoading({
			title: i18n.global.t(`支付中`)
		});
		if (paytype == 'friend' && orderId.value) {
			uni.hideLoading();
			return uni.navigateTo({
				url: routes.paymentOnBehalf + '?oid=' + oid.value + '&spread=' +
					uid.value,
				success: res => {},
				fail: () => {},
				complete: () => {}
			});
		}
		// 支付完成后的回跳地址：H5 需带完整域名，APP-PLUS 仅需路由路径
		let quitUrl = '';
		// #ifdef H5
		quitUrl = location.port
			? location.protocol + '//' + location.hostname + ':' + location.port +
				routes.orderDetail + '?order_no=' + orderId.value
			: location.protocol + '//' + location.hostname +
				routes.orderDetail + '?order_no=' + orderId.value;
		// #endif
		// #ifdef APP-PLUS
		quitUrl = routes.orderDetail + '?order_no=' + orderId.value;
		// #endif
		await openPaySubscribe().catch(() => {});
		orderPay({
			uni: orderId.value,
			paytype: paytype,
			type: friendPay.value ? 1 : 0,
			quitUrl: quitUrl
		}).then(res => {
			// 局部变量重命名 resultOrderId/friendPayUrl：避免与同名 ref（orderId/friendPay）在回调内遮蔽
			let goPage = routes.payStatus + '?order_no=' + orderId.value + '&msg=' + res.msg + '&type=3' + '&totalPrice=' + payPriceShow.value
			if (is_gift.value) goPage += '&is_gift=1'
			let status = res.data.status,
				resultOrderId = res.data.result.order_id,
				jsConfig = res.data.result.jsConfig,
				goPages = goPage,
				friendPayUrl = routes.paymentOnBehalf + '?order_no=' + orderId.value +
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
							url: `${routes.payStatus}?order_no=${orderId.value}&type=3&totalPrice=${payPriceShow.value}&status=0`
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
						// 支付成功跳转至支付结果页
						return util.Tips({
							title: res.msg,
							icon: 'success'
						}, {
							tab: 5,
							url: `${routes.payStatus}?order_no=${orderId.value}&msg=${res.msg}&type=3&totalPrice=${payPriceShow.value}&status=0`
						});
					} else {
						return util.Tips({
							title: res.msg,
							icon: 'success'
						}, {
							tab: 5,
							url: friendPayUrl
						});
					}


					break;
				case 'WECHAT_PAY':
					toPay.value = true;
					// #ifdef MP-WEIXIN
					requestMiniPayment(jsConfig, {
						timeStamp: String(jsConfig.timestamp),
						nonceStr: jsConfig.nonceStr,
						package: jsConfig.package,
						signType: jsConfig.signType,
						paySign: jsConfig.paySign,
						success: function(res) {
							uni.hideLoading();
							if (BargainId || combinationId || pinkId ||
								seckillId || discountId)
								return util.Tips({
									title: i18n.global.t(`支付成功`),
									icon: 'success'
								}, {
									tab: 5,
									url: `${routes.payStatus}?order_no=${orderId.value}&msg=${i18n.global.t(`支付成功`)}&type=3&totalPrice=${payPriceShow.value}&status=0`
								});
							return util.Tips({
								title: i18n.global.t(`支付成功`),
								icon: 'success'
							}, {
								tab: 5,
								url: `${routes.payStatus}?order_no=${orderId.value}&msg=${i18n.global.t(`支付成功`)}&type=3&totalPrice=${payPriceShow.value}&status=0`
							});
						},
						fail: function(e) {
							uni.hideLoading();
							const failure = getMiniPaymentFailure(e);
							if (!failure.canceled) {
								return util.Tips({ title: failure.message || i18n.global.t(`支付失败，请稍后重试`) });
							}
							return util.Tips({
								title: i18n.global.t(`取消支付`)
							}, {
								tab: 5,
								url: goPages + '&status=2'
							});
						},
						complete: function() {
							uni.hideLoading();
						},
					})
					// #endif
					// #ifdef H5
					Auth.pay(res.data.result.jsConfig).then(res => {
						return util.Tips({
							title: i18n.global.t(`支付成功`),
							icon: 'success'
						}, {
							tab: 5,
							url: `${routes.payStatus}?order_no=${orderId.value}&msg=${i18n.global.t(`支付成功`)}&type=3&totalPrice=${payPriceShow.value}&status=0`
						});
					}).catch(res => {
						if (!Auth.isWeixin()) {
							uni.redirectTo({
								url: goPages + '&msg=' + i18n.global.t(`支付失败`) +
									'&status=2'
								// '&msg=支付失败&status=2'
							})
						}
						if (res.errMsg == 'chooseWXPay:cancel') return util.Tips({
							title: i18n.global.t(`取消支付`)
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
							// 支付成功跳转至支付结果页
							let url = `${routes.payStatus}?order_no=${orderId.value}&msg=${i18n.global.t(`支付成功`)}&type=3&totalPrice=${payPriceShow.value}&status=0`;
							uni.showToast({
								title: i18n.global.t(`支付成功`)
							})
							setTimeout(res => {
								uni.redirectTo({
									url: url
								})
							}, 2000)
						},
						fail: (e) => {
							let url = routes.payStatus + '?order_no=' +
								resultOrderId +
								'&msg=' + i18n.global.t(`支付失败`);
							uni.showModal({
								content: i18n.global.t(`支付失败`),
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
						title: i18n.global.t(`等待支付中`)
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
						title: i18n.global.t(`等待支付中`)
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
						url: `${routes.alipayInvoke}?id=${resultOrderId}&link=${jsConfig.qrCode}`
					});
					// #endif
					// #ifdef APP-PLUS
					uni.requestPayment({
						provider: 'alipay',
						orderInfo: jsConfig,
						success: (e) => {
							uni.showToast({
								title: i18n.global.t(`支付成功`)
							})
							// 支付成功跳转至支付结果页
							let url = `${routes.payStatus}?order_no=${resultOrderId}&msg=${i18n.global.t(`支付成功`)}&type=3&totalPrice=${payPriceShow.value}&status=0`;
							setTimeout(res => {
								uni.redirectTo({
									url: url
								})
							}, 2000)

						},
						fail: (e) => {
							let url = routes.payStatus + '?order_no=' +
								resultOrderId +
								'&msg=' + i18n.global.t(`支付失败`);
							uni.showModal({
								content: i18n.global.t(`支付失败`),
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

	// 暴露给包装页（pages/center/pay/index.vue）通过 $refs 调用的入口方法
	defineExpose({
		initFromOptions,
		handlePageShow
	});
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
				color: var(--view-theme, #e93323);
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
				color: var(--view-theme, #e93323);

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
			color: var(--view-theme, #e93323) !important;
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

		.payment .item .left .pay-icon-img {
			width: 50rpx;
			height: 50rpx;
			margin-right: 28rpx;
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
			background-color: var(--view-theme, #e93323);

		}

	}
</style>
