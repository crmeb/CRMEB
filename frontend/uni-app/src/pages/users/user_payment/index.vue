<template>
	<view>
		<form :style="colorStyle">
			<view class="payment-top acea-row row-column row-center-wrapper">
				<span class="name">{{$t(`我的余额`)}}</span>
				<view class="pic">
					<span class="pic-font"><span class="num"> {{$t(`￥`)}}</span>{{ userinfo.now_money || 0 }}</span>
				</view>
			</view>
			<view class="payment">
				<view class="nav acea-row row-around row-middle" v-if="navRecharge.length > 1">
					<view class="item" :class="active==index?'on':''" v-for="(item,index) in navRecharge" :key="index"
						@click="navRecharges(index)">{{$t(item)}}</view>
				</view>
				<view class='tip picList' v-if='!active'>
					<view class="pic-box pic-box-color acea-row row-center-wrapper row-column"
						:class="activePic == index ? 'pic-box-color-active' : ''" v-for="(item, index) in picList" :key="index"
						@click="picCharge(index, item)" v-if="item.price">
						<view class="pic-number-pic">
							{{ item.price }}<span class="pic-number"> {{$t(`元`)}}</span>
						</view>
						<view class="pic-number">{{$t(`赠送`)}}: {{ item.give_money }} {{$t(`元`)}} </view>
					</view>
					<view class="pic-box pic-box-color acea-row row-center-wrapper"
						:class="activePic == picList.length ? 'pic-box-color-active' : ''" @click="picCharge(picList.length)">
						<input type="digit" @input="replaceInput" :placeholder="$t(`其他`)" v-model="money"
							class="pic-box-money pic-number-pic" :placeholder-class="activePic == picList.length ? 'active' :''"
							:class="activePic == picList.length ? 'pic-box-color-active' : ''" />
					</view>
					<view class="tips-box">
						<view class="tips mt-30">{{$t(`注意事项`)}}：</view>
						<view class="tips-samll" v-for="item in rechargeAttention" :key="item">
							{{ $t(item) }}
						</view>
					</view>

				</view>
				<view class="tip" v-else>
					<view class='input'><text>{{$t(`￥`)}}</text><input v-model="number" placeholder="0.00" type='number'
							placeholder-class='placeholder' name="number"></input></view>
					<view class="tips-title">
						<view style="font-weight: bold; font-size: 26rpx;">{{$t(`提示`)}}：</view>
						<view style="margin-top: 10rpx;">{{$t(`当前可转入佣金为`)}} <text
								class='font-color'>{{$t(`￥`)}}{{userinfo.commissionCount || 0}}</text>{{$t(`冻结佣金为`)}}<text
								class='font-color'>{{$t(`￥`)}}{{userinfo.broken_commission}}</text></view>
					</view>
					<view class="tips-box">
						<view class="tips mt-30">{{$t(`注意事项`)}}：</view>
						<view class="tips-samll" v-for="item in rechargeAttention" :key="item">
							{{ $t(item) }}
						</view>
					</view>
				</view>
				<button class='but bg-color' @click="submitSub"> {{active ? $t(`立即转入`): $t(`立即充值`) }}</button>
			</view>
		</form>
		<payment :payMode="payMode" :pay_close="pay_close" :is-call="true" @onChangeFun="onChangeFun"
			:totalPrice="numberPic"></payment>
		<view v-show="false" v-html="formContent"></view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
	getUserInfo as getUserInfoApi,
	recharge,
	getRechargeApi
} from '@/api/user.js';
import {
	toLogin
} from '@/libs/login.js';
import home from '@/components/home';
import payment from '@/components/payment';
import {
	getPayConfig
} from '@/api/public.js'
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import { getMiniPaymentFailure, requestMiniPayment } from '@/utils/miniPayment.js';
import i18n from '@/utils/lang.js';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const now_money = ref(0);
const navRecharge = ref([i18n.t(`账户充值`), i18n.t(`佣金转入`)]);
const active = ref(0);
const number = ref('');
const formContent = ref('');
const userinfo = ref({});
const placeholder = "0.00";
const from = ref('');
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const picList = ref([]);
const activePic = ref(0);
const money = ref("");
const numberPic = ref('');
const rechar_id = ref(0);
const rechargeAttention = ref([]);
const pay_close = ref(false);
const payMode = ref([{
		name: i18n.t(`微信支付`),
		icon: 'icon-weixinzhifu',
		value: 'weixin',
		title: i18n.t(`微信支付`),
		payStatus: true
	},
	// #ifdef H5 ||APP-PLUS
	{
		name: i18n.t(`支付宝支付`),
		icon: 'icon-zhifubao',
		value: 'alipay',
		title: i18n.t(`支付宝支付`),
		payStatus: true
	},
	// #endif
]);
const totalPrice = ref(0);
// 原代码未在 data 声明、直接挂到实例上的字段
const toPay = ref(false);
const initIn = ref(false);
const jumpData = ref({});

// ---------- watch ----------
watch(isLogin, (newV, oldV) => {
	if (newV) {
		getUserInfo();
		getRecharge();
	}
}, { deep: true });

// ---------- 生命周期 ----------
onLoad((options) => {
	// #ifdef H5
	from.value = Auth.isWeixin() ? "weixin" : "weixinh5"
	// #endif
	if (isLogin.value) {
		getBasicConfig();
		getUserInfo();
		getRecharge();
	} else {
		toLogin();
	}
});

// ---------- methods ----------
function replaceInput(event) {
	// 必须在nextTick中
	nextTick(() => {
		money.value = event.target.value.match(/^\d*(\.?\d{0,2})/g)[0]
	})
}
function getBasicConfig() {
	getPayConfig().then(res => {
		payMode.value = res.data.filter(item => ['weixin', 'weixin_v3', 'alipay', 'allinpay'].includes(item.value) && item.payStatus);
	}).catch(err => {
		uni.showToast({
			title: err,
			icon: 'none'
		});
	});
}

/**
 * 选择金额
 */
function picCharge(idx, item) {
	activePic.value = idx;
	if (item === undefined) {
		rechar_id.value = 0;
		numberPic.value = "";
	} else {
		money.value = "";
		rechar_id.value = item.id;
		numberPic.value = item.price;
	}
}

/**
 * 充值额度选择
 */
function getRecharge() {
	getRechargeApi()
		.then(res => {
			picList.value = res.data.recharge_quota;
			if (picList.value[0]) {
				rechar_id.value = picList.value[0].id;
				numberPic.value = picList.value[0].price;
			}
			rechargeAttention.value = res.data.recharge_attention || [];
		})
		.catch(res => {
			util.Tips({
				title: res
			})
		});
}


function onLoadFun() {
	getUserInfo();
	getRecharge();
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e
}
function navRecharges(index) {
	active.value = index;
}
/**
 * 获取用户信息
 */
function getUserInfo() {
	getUserInfoApi().then(res => {
		userinfo.value = res.data;
		if (!res.data.extract_type.includes('3')) {
			navRecharge.value = [i18n.t(`账户充值`)]
		}
	})
}
/**
 * 事件回调（原 this[action] 动态派发改为查表）
 */
const actionMap = {
	pay,
	payCheck,
	pay_complete,
	pay_fail
};
function onChangeFun(e) {
	let opt = e;
	let action = opt.action || null;
	let value = opt.value != undefined ? opt.value : null;
	pay_close.value = false
	action && actionMap[action] && actionMap[action](value);
}
function payCheck(type) {
	uni.showLoading({
		title: i18n.t(`正在支付`),
	})
	recharge({
		price: rechar_id.value == 0 ? money.value : numberPic.value,
		from: type,
		recharge_id: rechar_id.value,
		type: 0
	}).then(res => {
		const payment = res.data.result || res.data.data || res.data;
		let status = res.data.status || payment.pay_type,
			orderId = payment.orderId || payment.order_id,
			jsConfig = payment.jsConfig;
		switch (status) {
			case 'ORDER_EXIST':
			case 'EXTEND_ORDER':
				uni.hideLoading();
				return util.Tips({
					title: res.msg
				});
				break;
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
					orderId,
					msg: res.msg,
				}
				// #endif
				// #ifdef APP-PLUS
				plus.runtime.openURL(jsConfig.payinfo);
				// #endif
				// #ifdef H5
				formpost(payment.pay_url, jsConfig)
				// #endif
				break;
			case 'PAY_ERROR':
				uni.hideLoading();
				return util.Tips({
					title: res.msg
				});
				break;
			case 'SUCCESS':
				uni.hideLoading();
				return util.Tips({
					title: i18n.t(`支付成功`),
					icon: 'success'
				}, {
					tab: 5,
					url: '/pages/users/user_money/index'
				});
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
						// 原代码误写为 this['userinfo.now_money']（键名带点，实际无效赋值），此处按原语义保留
						userinfo.value['userinfo.now_money'] = util.$h.Add(number
							.value, userinfo.value
							.now_money);
						return util.Tips({
							title: i18n.t(`支付成功`),
							icon: 'success'
						}, {
							tab: 5,
							url: '/pages/users/user_money/index'
						});
					},
					fail: function(e) {
						uni.hideLoading();
						const failure = getMiniPaymentFailure(e);
						return util.Tips({
							title: failure.canceled ? i18n.t(`取消支付`) : (failure.message || i18n.t(`支付失败，请稍后重试`))
						});
					},
					complete: function() {
						uni.hideLoading();
					},
				})
				// #endif
				// #ifdef H5
				Auth.pay(jsConfig).then(res => {
					userinfo.value['userinfo.now_money'] = util.$h.Add(number
						.value, userinfo.value
						.now_money);
					return util.Tips({
						title: i18n.t(`支付成功`),
						icon: 'success'
					}, {
						tab: 5,
						url: '/pages/users/user_money/index'
					});
				}).catch(res => {
					if (!Auth.isWeixin()) {
						return util.Tips({
							title: i18n.t(`支付失败`)
						});
					}
					if (res.errMsg == 'chooseWXPay:cancel') return util.Tips({
						title: i18n.t(`取消支付`)
					});
				})
				// #endif
				// #ifdef APP-PLUS
				uni.requestPayment({
					provider: 'wxpay',
					orderInfo: jsConfig,
					success: (e) => {
						userinfo.value['userinfo.now_money'] = util.$h.Add(number
							.value,
							userinfo.value
							.now_money);
						return util.Tips({
							title: i18n.t(`支付成功`),
							icon: 'success'
						}, {
							tab: 5,
							url: '/pages/users/user_money/index'
						});
					},
					fail: (e) => {
						return util.Tips({
							title: i18n.t(`支付失败`)
						});
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
				// 原代码此处引用了未定义的 goPages，保持原样
				return util.Tips({
					title: res.msg
				}, {
					tab: 5,
					url: goPages + '&status=1'
				});
				break;

			case "WECHAT_H5_PAY":
				uni.hideLoading();
				setTimeout(() => {
					location.href = res.data.result.jsConfig.h5_url;
				}, 2000);
				break;

			case 'ALIPAY_PAY':
				//#ifdef H5
				uni.hideLoading();
				formContent.value = res.data.result.jsConfig;
				nextTick(() => {
					document.getElementById('alipaysubmit').submit();
				})
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
						userinfo.value['userinfo.now_money'] = util.$h.Add(number
							.value,
							userinfo.value
							.now_money);
						return util.Tips({
							title: i18n.t(`支付成功`),
							icon: 'success'
						}, {
							tab: 5,
							url: '/pages/users/user_money/index'
						});

					},
					fail: (e) => {
						return util.Tips({
							title: i18n.t(`支付失败`)
						});
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
		})
	})
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
function pay() {
	pay_close.value = true;
}
/*
 * 用户充值
 */
function submitSub() {
	let value = number.value;
	// 转入余额
	if (active.value) {
		if (parseFloat(value) < 0 || parseFloat(value) == NaN || value == undefined || value == "") {
			return util.Tips({
				title: i18n.t(`请输入金额`)
			});
		}
		uni.showModal({
			title: i18n.t(`转入余额`),
			content: i18n.t(`转入余额后无法再次转出，确认是否转入余额`),
			success(res) {
				if (res.confirm) {
					recharge({
							price: parseFloat(value),
							type: 1
						})
						.then(res => {
							// userinfo.value['userinfo.now_money'] = util.$h.Add(value, userinfo.value.now_money)
							return util.Tips({
								title: i18n.t(`转入成功`),
								icon: 'success'
							}, {
								tab: 5,
								url: '/pages/users/user_money/index'
							});
						}).catch(err => {
							return util.Tips({
								title: err
							})
						});
				} else if (res.cancel) {
					return util.Tips({
						title: i18n.t(`已取消`)
					});
				}
			},
		})
	} else {
		if (numberPic.value == '') numberPic.value = money.value;
		pay()
	}
}
</script>
<style lang="scss">
	.tip .pic-box-color .active {
		color: #fff !important;
	}
</style>
<style lang="scss" scoped>
	page {
		width: 100%;
		height: 100%;
		background-color: #fff;
	}

	.bgcolor {
		background-color: var(--view-theme)
	}

	.payment {
		position: relative;
		top: -60rpx;
		width: 100%;
		background-color: #fff;
		border-radius: 10rpx;
		padding-top: 1rpx;
		border-top-right-radius: 39rpx;
		border-top-left-radius: 39rpx;
	}

	.payment .nav {
		height: 75rpx;
		line-height: 75rpx;
		padding: 8rpx 100rpx;
	}

	.payment .nav .item {
		font-size: 30rpx;
		color: #333;
	}

	.payment .nav .item.on {
		font-weight: bold;
		border-bottom: 4rpx solid var(--view-theme);
	}

	.payment .input {
		display: flex;
		align-items: center;
		justify-content: center;
		border-bottom: 1px dashed #dddddd;
		margin: 60rpx auto 0 auto;
		padding-bottom: 20rpx;
		font-size: 56rpx;
		color: #333333;
		flex-wrap: nowrap;

	}

	.payment .input text {
		padding-left: 106rpx;
	}

	.payment .input input {
		padding-right: 106rpx;
		width: 300rpx;
		height: 94rpx;
		text-align: center;
		font-size: 70rpx;
	}

	.payment .placeholder {
		color: #d0d0d0;
		height: 100%;
		line-height: 94rpx;
	}

	.payment .tip {
		font-size: 26rpx;
		color: #888888;
		padding: 0 30rpx;
		margin-top: 25rpx;
	}

	.payment .but {
		color: #fff;
		font-size: 30rpx;
		width: 700rpx;
		height: 86rpx;
		border-radius: 50rpx;
		margin: 46rpx auto 0 auto;
		line-height: 86rpx;
	}

	.payment-top {
		width: 100%;
		height: 350rpx;
		background-color: var(--view-theme);

		.name {
			font-size: 26rpx;
			color: rgba(255, 255, 255, 0.8);
			margin-top: -38rpx;
			margin-bottom: 30rpx;
		}

		.pic {
			font-size: 32rpx;
			color: #fff;

			.num {
				font-size: 56rpx;
			}
		}

		.pic-font {
			font-size: 78rpx;
			color: #fff;
		}
	}

	.picList {
		display: flex;
		flex-wrap: wrap;
		margin: 30rpx 0;

		.pic-box {
			width: 32%;
			height: auto;
			border-radius: 20rpx;
			margin-top: 21rpx;
			padding: 20rpx 0;
			margin-right: 12rpx;

			&:nth-child(3n) {
				margin-right: 0;
			}
		}

		.pic-box-color {
			background-color: #f4f4f4;
			color: #656565;
		}

		.pic-number {
			font-size: 22rpx;
		}

		.pic-number-pic {
			font-size: 38rpx;
			margin-right: 10rpx;
			text-align: center;
		}

		.active {
			color: #fff !important;
		}

		.pic-box-color-active {
		background-color: var(--view-theme) !important;
		color: #fff !important;
	}

	// 注意事项区块独占一行，避免在 flex 容器内被挤压到金额选择框旁
	.tips-box {
		width: 100%;
	}
}

.tips-box {
		.tips {
			font-size: 28rpx;
			color: #333333;
			font-weight: 800;
			margin-bottom: 14rpx;
			margin-top: 20rpx;
		}

		.tips-samll {
			font-size: 24rpx;
			color: #333333;
			margin-bottom: 14rpx;
		}

		.tip-box {
			margin-top: 30rpx;
		}
	}

	.tips-title {
		margin-top: 20rpx;
		font-size: 24rpx;
		color: #333;
	}
</style>
