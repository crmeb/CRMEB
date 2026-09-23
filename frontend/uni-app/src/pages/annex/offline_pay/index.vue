<template>
	<view>
		<form class="form" @submit="checkForm" :style="colorStyle">
			<view class="input-section">
				<view class="section-hd">{{ $t(`支付金额`) }}</view>
				<view class="section-bd">
					<view class="input-group">
						{{ $t(`￥`) }}
						<input v-model.number="money" class="input" name="money" type="digit" @input="inputChange" placeholder="0.00" />
					</view>
					<view v-if="payPrice && show" class="discount">{{ $t(`会员优惠价`) }}：{{ $t(`￥`) }}{{ payPrice || 0 }}</view>
				</view>
			</view>
			<view class="radio-section">
				<view class="section-hd">{{ $t(`支付方式`) }}</view>
				<radio-group class="section-bd" name="method">
					<label class="item" v-if="yuePay">
						<text class="iconfont icon-yue"></text>
						<view class="name">
							<text>{{ $t(`余额支付`) }}</text>
							<text class="money">{{ $t(`可用余额`) }}:{{ $t(`￥`) }}{{ now_money || 0 }}</text>
						</view>
						<radio value="yue" :checked="payType === 'yue'" />
					</label>
					<label v-for="channel in wechatChannels" :key="channel.value" class="item">
						<text class="iconfont icon-weixinzhifu"></text>
						<text class="name">{{ channel.name }}</text>
						<radio :value="channel.value" :checked="payType === channel.value" />
					</label>
				</radio-group>
			</view>
			<button class="button" form-type="submit">{{ $t(`确认`) }}</button>
			<view class="alipay" v-html="alipayHtml"></view>
		</form>
	</view>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import { offlineCheckPrice, offlineCreate, orderOfflinePayType } from '@/api/order.js';
import { getPayConfig, wechatAuthV2 } from '@/api/public.js';
import { getMiniPaymentFailure, requestMiniPayment } from '@/utils/miniPayment.js';
import { toLogin } from '@/libs/login.js';
import { useAppStore } from '@/store/modules/app';
import { storeToRefs } from 'pinia';
import { useColorTheme } from '@/composables/useColorTheme';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif

const app = getApp();
const { colorStyle } = useColorTheme();

const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const money = ref('');
const payPrice = ref('');
const payType = ref('weixin');
const alipayHtml = ref('');
const alipay = ref(false);
const wxpay = ref(false);
const wechatChannels = ref([]);
const yuePay = ref(false);
const paying = ref(false);
const now_money = ref(0);
const isWeixin = ref(false);
const site_name = ref('');
const isCommitted = ref(false);
const show = ref(false);
// 原实现中挂在本组件实例上的临时数据（无渲染用途），保留为 ref
const initIn = ref(false);
const jumpData = ref(null);

onLoad((options) => {
	if (!isLogin.value) {
		toLogin();
	}
	// #ifdef H5
	if (options.code) {
		let spread = app.globalData.spid ? app.globalData.spid : '';
		wechatAuthV2(options.code, spread).then((res) => {
			location.href = decodeURIComponent(decodeURIComponent(options.back_url));
		});
	}
	// #endif
});

onShow(() => {
	if (isLogin.value) {
		getPayType();
	}
	//#ifdef H5
	isWeixin.value = Auth.isWeixin();
	//#endif
});

// ---------- methods ----------
function inputChange(e) {
	e.target.value = e.target.value.match(/^\d*(.?\d{0,2})/g)[0] || '';
	nextTick(() => {
		money.value = e.target.value;
		checkPrice();
	});
}
function getPayType() {
	getPayConfig().then(res => {
		wechatChannels.value = res.data.filter(item => ['weixin', 'weixin_v3'].includes(item.value) && item.payStatus);
		payType.value = wechatChannels.value[0]?.value || 'yue';
	}).catch(err => util.Tips({ title: err }));
	orderOfflinePayType()
		.then((res) => {
			const { ali_pay_status, pay_weixin_open, yue_pay_status, offline_pay_status, site_name, now_money } = res.data;
			alipay.value = ali_pay_status;
			wxpay.value = pay_weixin_open;
			yuePay.value = yue_pay_status;
			now_money.value = now_money;
			site_name.value = site_name;
			if (!offline_pay_status) {
				uni.showModal({
					title: i18n.t(`支付提醒`),
					content: i18n.t(`线下支付已关闭，请点击确认按钮返回主页`),
					showCancel: false,
					success() {
						uni.switchTab({
							url: '/pages/index/index'
						});
					}
				});
			}
			if (site_name) {
				uni.setNavigationBarTitle({
					title: site_name
				});
			}
		})
		.catch((err) => {
			uni.showToast({
				title: err,
				icon: 'none'
			});
		});
}
function checkForm(e) {
	const { money, method } = e.detail.value;
	if (!method || paying.value) return;
	if (money) {
		combData(method);
	} else {
		uni.showToast({
			title: i18n.t(`请输入支付金额`),
			icon: 'none'
		});
	}
}
// 优惠价
function checkPrice() {
	offlineCheckPrice({
		pay_price: money.value
	})
		.then((res) => {
			payPrice.value = res.data.pay_price;
			show.value = res.data.show;
		})
		.catch((err) => {
			uni.showToast({
				title: err,
				icon: 'none'
			});
		});
}
// 组合数据
function combData(payType) {
	let data = {
		type: 3,
		pay_type: payType,
		// #ifdef H5
		from: isWeixin.value ? 'weixin' : 'weixinh5',
		// #endif
		// #ifdef MP
		from: 'routine',
		// #endif
		// #ifdef APP-PLUS
		from: 'weixin',
		// #endif
		price: payPrice.value || money.value,
		money: money.value
	};

	// #ifdef H5
	if (isWeixin.value) {
		data.from = 'weixin';
	}
	// #endif
	// #ifdef MP
	data.from = 'routine';
	// #endif
	if (paying.value) {
		return;
	}
	paying.value = true;
	uni.showLoading({
		title: i18n.t(`正在确认`)
	});
	offlineCreate(data)
		.then((res) => {
			uni.hideLoading();
			callPay(res);
		})
		.catch((err) => {
			paying.value = false;
			uni.showToast({
				title: err,
				icon: 'none'
			});
		});
}
function formpost(url, postData) {
	let tempform = document.createElement('form');
	tempform.action = url;
	tempform.method = 'post';
	tempform.target = '_self';
	tempform.style.display = 'none';
	for (let x in postData) {
		let opt = document.createElement('input');
		opt.name = x;
		opt.value = postData[x];
		tempform.appendChild(opt);
	}
	document.body.appendChild(tempform);
	nextTick((e) => {
		tempform.submit();
	});
}
// 调用支付
function callPay(res) {
	const { status, result } = res.data,
		{ orderId, jsConfig } = result,
		goPages = '/pages/annex/offline_result/index?site_name=' + site_name.value;
	switch (status) {
		case 'ORDER_EXIST':
		case 'EXTEND_ORDER':
		case 'ALLINPAY_PAY':
			uni.hideLoading();
			// #ifdef MP-WEIXIN
			initIn.value = true;
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
			});
			jumpData.value = {
				orderId: res.data.result.orderId,
				msg: res.msg
			};
			// #endif
			// #ifdef APP-PLUS
			plus.runtime.openURL(jsConfig.payinfo);
			setTimeout((e) => {
				uni.reLaunch({
					url: '/pages/annex/offline_pay/index'
				});
			}, 1000);
			// #endif
			// #ifdef H5
			formpost(res.data.result.pay_url, jsConfig);
			// #endif
			break;
		case 'PAY_ERROR':
			paying.value = false;
			util.Tips(
				{
					title: res.msg
				},
				{
					tab: 5,
					url: goPages
				}
			);
			break;
		case 'SUCCESS':
			paying.value = false;
			money.value = '';
			util.Tips(
				{
					title: res.msg,
					icon: 'success'
				},
				{
					tab: 5,
					url: goPages
				}
			);
			break;
		case 'WECHAT_PAY':
			// #ifdef MP-WEIXIN
			requestMiniPayment(jsConfig, {
				timeStamp: jsConfig.timestamp,
				nonceStr: jsConfig.nonceStr,
				package: jsConfig.package,
				signType: jsConfig.signType,
				paySign: jsConfig.paySign,
				success: function (res) {
					util.Tips(
						{
							title: i18n.t(`支付成功`),
							icon: 'success'
						},
						{
							tab: 5,
							url: '/pages/annex/offline_result/index'
						}
					);
				},
				fail: function (error) {
					uni.showToast({
						title: getMiniPaymentFailure(error).message,
						icon: 'none',
						success: function () {
							paying.value = false;
						}
					});
				},
				complete: function () {
					paying.value = false;
					uni.hideLoading();
				}
			});
			// #endif
			// #ifdef H5
			Auth.pay(result.jsConfig)
				.then((res) => {
					paying.value = false;
					util.Tips(
						{
							title: i18n.t(`支付成功`),
							icon: 'success'
						},
						{
							tab: 5,
							url: '/pages/annex/offline_result/index'
						}
					);
				})
				.catch((err) => {
					paying.value = false;
					if (err.errMsg == 'chooseWXPay:cancel') {
						uni.showToast({
							title: i18n.t(`取消支付`),
							icon: 'none'
						});
					}
				});
			// #endif
			// #ifdef APP-PLUS
			uni.requestPayment({
				provider: 'wxpay',
				orderInfo: jsConfig,
				success: () => uni.navigateTo({ url: '/pages/annex/offline_result/index' }),
				fail: error => util.Tips({ title: getMiniPaymentFailure(error).message }),
				complete: () => { paying.value = false; uni.hideLoading(); }
			});
			// #endif
			break;
		case 'PAY_DEFICIENCY':
			paying.value = false;
			util.Tips({
				title: res.msg
			});
			break;
		case 'WECHAT_H5_PAY':
			paying.value = false;
			uni.showToast({
				title: res.msg,
				success() {
					location.href = jsConfig.h5_url;
				}
			});
			break;
		case 'ALIPAY_PAY':
			paying.value = false;
			// #ifdef H5
			if (Auth.isWeixin()) {
				uni.navigateTo({
					url: `/pages/users/alipay_invoke/index?id=${orderId}&link=${jsConfig.qrCode}`
				});
			} else {
				alipayHtml.value = jsConfig;
				nextTick(() => {
					document.getElementById('alipaysubmit').submit();
				});
			}
			// #endif
			// #ifdef MP
			uni.navigateTo({
				url: `/pages/users/alipay_invoke/index?id=${orderId}&link=${jsConfig.qrCode}`
			});
			// #endif
			break;
	}
}
</script>

<style>
page {
	background-color: #ffffff;
}
</style>

<style lang="scss" scoped>
:deep(uni-radio .uni-radio-input.uni-radio-input-checked){
	border: 1px solid #fdc383 !important;
	background-color: #fdc383 !important;
}
.input-section {
	.section-hd {
		padding: 30rpx;
		font-size: 28rpx;
		color: #666666;
	}

	.section-bd {
		padding-right: 30rpx;
		padding-left: 30rpx;
	}

	.input-group {
		display: flex;
		align-items: flex-end;
		padding: 45rpx 20rpx 47rpx;
		font-size: 80rpx;
		color: #999999;
	}

	.input {
		flex: 1;
		height: 110rpx;
		margin-left: 15rpx;
		font-size: 100rpx;
		color: #282828;
	}

	.discount {
		padding: 27rpx 20rpx;
		border-top: 1rpx solid #eeeeee;
		font-size: 28rpx;
		color: #e93323;
	}
}

.radio-section {
	border-top: 20rpx solid #f5f5f5;

	.section-hd {
		padding: 30rpx;
		font-size: 28rpx;
		color: #666666;
	}

	.section-bd {
		padding-left: 50rpx;
	}

	.item {
		display: flex;
		align-items: center;
		padding-top: 30rpx;
		padding-right: 30rpx;
		padding-bottom: 30rpx;
		border-bottom: 1rpx solid #f5f5f5;
		.name {
			display: flex;
			align-items: center;
			justify-content: space-between;
		}
	}

	.iconfont {
		font-size: 44rpx;
	}

	.icon-yue {
		color: #fe960f;
	}

	.icon-weixinzhifu {
		color: #41b035;
	}

	.icon-zhifubao {
		color: #099bdf;
	}

	.name {
		flex: 1;
		margin-left: 30rpx;
		font-size: 30rpx;
		color: #333333;
	}

	.money {
		float: right;
		padding-right: 20rpx;
		font-size: 20rpx;
	}
}

.button {
	height: 86rpx;
	border-radius: 43rpx;
	margin: 114rpx 30rpx 30rpx;
	background: linear-gradient(90deg, #fee2b7 0%, #fdc383 100%);
	font-size: 30rpx;
	line-height: 86rpx;
	color: #5d3324;
}
.alipay {
	display: none;
}
</style>
