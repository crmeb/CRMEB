<template>
	<view :style="assetStyle" class="">
		<view class="card-section">
			<view class="acea-row row-middle">
				<image class="image" :src="userInfo.avatar"></image>
				<view class="text">
					<view class="name line1">{{ userInfo.nickname || '' }}</view>
					<view v-if="userInfo.is_ever_level">{{ $t(`永久SVIP会员`) }}</view>
					<view v-else-if="userInfo.is_money_level">
						{{ $t(`SVIP会员`) }} {{ dateFormat(userInfo.overdue_time) }}
						{{ $t(`到期`) }}
					</view>
					<view v-else>
						{{ $t(`您与`) }} {{ userInfo.shop_name || '' }} {{ $t(`商城的第`) }} {{ userInfo.register_days || '' }}
						{{ $t(`天.`) }}
					</view>
				</view>
			</view>
			<view class="acea-row row-between-wrapper info">
				<view v-if="userInfo.is_money_level">
					{{ $t(`已累积为您节省`) }} {{ $t(`￥`) }}
					<text class="num">{{ userInfo.economize_money || '' }}</text>
				</view>
				<view v-else>{{ $t(`开通即享会员权益`) }}</view>
				<view class="btn" @click="scrollToCard" v-if="!userInfo.is_ever_level">
					{{ userInfo.is_money_level ? $t(`续费会员`) : $t(`开通会员`) }}
				</view>
			</view>
		</view>
		<view class="right-section" v-if="memberRights.length">
			<view class="section-hd acea-row row-center-wrapper">
				<view class="title acea-row row-center row-bottom">
					<text class="iconfont icon-huiyuan2"></text>
					{{ $t(`SVIP会员尊享权`) }}
				</view>
			</view>
			<view class="section-bd acea-row row-between-wrapper">
				<view v-for="item in memberRights" :key="item.id" class="acea-row row-middle item">
					<image class="image" :src="item.pic"></image>
					<view class="text">
						<view class="name">{{ item.title }}</view>
						<view>{{ item.explain }}</view>
					</view>
				</view>
			</view>
		</view>
		<view class="type-section" id="card" v-if="!userInfo.is_ever_level && memberType.length > 0">
			<view class="title">
				<view class="bold">{{ userInfo.is_money_level ? $t(`续费会员`) : $t(`开通会员`) }}</view>
				<view>
					{{ $t(`有效期至`) }}
					<text class="time">{{ memberEndTime }}</text>
				</view>
			</view>
			<scroll-view class="scroll" scroll-x="true">
				<view v-for="item in memberType" :key="item.type" class="item" :class="{ on: item.mc_id === mc_id }" @click="checkType(item)">
					<view>{{ item.title }}</view>
					<view class="new">
						{{ $t(`￥`) }}
						<text class="num">{{ moneyFormat(item.pre_price) }}</text>
					</view>
					<view v-if="item.type === 'free'" class="info">{{ $t(`试用`) }}{{ item.vip_day }}{{ $t(`天`) }}</view>
					<view v-else class="old">{{ $t(`￥`) }}{{ moneyFormat(item.price) }}</view>
				</view>
			</scroll-view>
			<view v-if="memberExplain" class="agree">
				<navigator :render-link="false" class="link" url="/pages/annex/vip_clause/index" hover-class="none">
					{{ $t(`购买即视为同意`) }}《
					<text class="mark">{{ $t(`会员用户协议`) }}</text>
					》
				</navigator>
			</view>
			<view class="buy" @click="pay">{{ type === 'free' ? $t(`立即试用`) : $t(`立即支付`) }}</view>
			<view class="cash">
				<text @click="drawMemberCard">{{ $t(`点击兑换卡密`) }}</text>
			</view>
		</view>
		<view v-if="memberCoupons.length" class="coupon-section">
			<view class="section-hd acea-row row-between-wrapper">
				<view class="title">{{ $t(`会员优惠券`) }}</view>
				<navigator :render-link="false" class="link" url="/pages/annex/vip_coupon/index">
					{{ $t(`查看更多`) }}
					<text class="iconfont icon-xiangyou"></text>
				</navigator>
			</view>
			<scroll-view class="section-bd" scroll-x="true">
				<view v-for="item in memberCoupons" :key="item.id" class="item acea-row row-center-wrapper" :class="{ gray: item.is_fail || item.status === $t(`已使用`) }">
					<view class="money">
						{{ $t(`￥`) }}
						<text class="num">{{ moneyFormat(item.coupon_price) }}</text>
					</view>
					<view class="text">
						<view class="name">{{ item.coupon_title }}</view>
						<view v-if="item.use_min_price === '0.00'" class="mark">{{ $t(`无门槛券`) }}</view>
						<view v-else class="mark">{{ $t(`满`) }}{{ moneyFormat(item.use_min_price) }}{{ $t(`元可用`) }}</view>
					</view>
					<!-- <view class="btn">{{item.status}}</view> -->
				</view>
			</scroll-view>
		</view>
		<view v-if="goodsList.length" class="goods-section">
			<view class="section-hd">{{ $t(`SVIP商品推荐`) }}</view>
			<view class="section-bd acea-row">
				<view v-for="item in goodsList" :key="item.id" class="item" @click="goDetail(item.id)">
					<image class="image" :src="item.image"></image>
					<view class="name">{{ item.store_name }}</view>
					<view class="svip-price">
						{{ $t(`￥`) }}{{ item.vip_price }}
						<image src="../../../static/images/vip.png"></image>
					</view>
					<view class="shop-price">{{ $t(`商城价`) }}：{{ $t(`￥`) }}{{ item.price }}</view>
				</view>
			</view>
		</view>
		<view :class="{ mask: popupShow }" @touchmove.prevent></view>
		<view class="popup" :class="{ on: popupShow }">
			<view class="cont">
				<view class="title">
					<view class="line"></view>
					<view class="name">{{ $t(`激活会员卡`) }}</view>
					<view class="line"></view>
				</view>
				<form class="form" @submit="checkForm">
					<input v-model="account" class="input" name="account" type="text" :placeholder="$t(`请输入卡号`)" placeholder-style="color:#C49D89" />
					<input v-model="password" class="input" name="password" type="text" :placeholder="$t(`请输入卡密`)" placeholder-style="color:#C49D89" password />
					<button class="button" form-type="submit">{{ $t(`确认激活`) }}</button>
				</form>
			</view>
			<button class="iconfont icon-guanbi2" @click="closePopup"></button>
		</view>
		<payment :payMode="payMode" :pay_close="pay_close" :is-call="true" @onChangeFun="onChangeFun" :order_id="pay_order_id" :totalPrice="totalPrice"></payment>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
		<view v-show="false" v-html="formContent"></view>
	</view>
</template>

<script setup>
import { ref, watch, nextTick, getCurrentInstance } from 'vue';
import { onLoad, onShow, onReachBottom } from '@dcloudio/uni-app';
import home from '@/components/home';
import payment from '@/components/payment';
import { useAppStore } from '@/store/modules/app';
import { storeToRefs } from 'pinia';
import { memberCard as memberCardApi, memberCardDraw, memberCardCreate, groomList as groomListApi, memberOverdueTime } from '@/api/user.js';
import { toLogin } from '@/libs/login.js';
import { openPaySubscribe } from '@/utils/subscribeMessage.js';
import dayjs from '@/plugin/dayjs/dayjs.min.js';
import { getPayConfig } from '@/api/public.js';
import util from '@/utils/util.js';
import { requestMiniPayment } from '@/utils/miniPayment.js';
import i18n from '@/utils/lang.js';
import { HTTP_REQUEST_URL } from '@/config/app';
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif

const assetStyle = `--ui-vip-paid-card:url(${HTTP_REQUEST_URL}/statics/images/vip-paid-card.png);--ui-vip-section-title:url(${HTTP_REQUEST_URL}/statics/images/vip-section-title.png);--ui-vip-paid-coupon:url(${HTTP_REQUEST_URL}/statics/images/vip-paid-coupon.png);--ui-vip-paid-coupon-gray:url(${HTTP_REQUEST_URL}/statics/images/vip-paid-coupon-gray.png);--ui-vip-paid-popup:url(${HTTP_REQUEST_URL}/statics/images/vip-paid-popup.png);`;

const instance = getCurrentInstance();

const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const memberType = ref([]);
const userInfo = ref({});
const memberRights = ref([]);
const memberExplain = ref([]);
const memberCoupons = ref([]);
const isGetFree = ref(null);
const popupShow = ref(false);
const account = ref('');
const password = ref('');
const goodsList = ref([]);
const pay_order_id = ref('');
const payMode = ref([
	{
		name: i18n.t(`微信支付`),
		icon: 'icon-weixinzhifu',
		value: 'weixin',
		title: i18n.t(`微信支付`),
		payStatus: true
	},
	{
		name: i18n.t(`支付宝支付`),
		icon: 'icon-zhifubao',
		value: 'alipay',
		title: i18n.t(`支付宝支付`),
		payStatus: true
	},
	{
		name: i18n.t(`余额支付`),
		icon: 'icon-qiandai',
		value: 'yue',
		title: i18n.t(`余额支付`),
		payStatus: true,
		number: 0
	}
]);
const pay_close = ref(false);
const totalPrice = ref('0');
const formContent = ref('');
const page = ref(1);
const limit = ref(15);
const finished = ref(false);
const memberEndTime = ref('');
// #ifdef H5
const isWeixin = Auth.isWeixin();
// #endif
const type = ref('');
const svip = ref(null);
const mc_id = ref(0);
const initIn = ref(false);
const memberSelected = ref(null);
// 原实现中挂在本组件实例上的临时数据（无渲染用途），保留为 ref
const jumpData = ref(null);

// ---------- watch ----------
watch(popupShow, () => {
	account.value = '';
	password.value = '';
}, { immediate: true });

watch(isLogin, (newV, oldV) => {
	if (newV) {
		memberCard();
		groomList();
		getOrderPayType();
	}
}, { deep: true });

onLoad(() => {
	if (isLogin.value) {
		memberCard();
		groomList();
		getOrderPayType();
	} else {
		toLogin();
	}
});

onShow(() => {
	payClose();
	// #ifdef MP-WEIXIN
	if (typeof wx.getEnterOptionsSync !== 'function') return;
	let options = wx.getEnterOptionsSync();
	if (options.scene == '1038' && options.referrerInfo.appId == 'wxef277996acc166c3' && initIn.value) {
		// 代表从收银台小程序返回
		let extraData = options.referrerInfo.extraData;
		initIn.value = false;
		if (!extraData) {
			memberCard();
			// "当前通过物理按键返回，未接收到返参，建议自行查询交易结果";
		} else {
			if (extraData.code == 'success') {
				memberCard();
			} else if (extraData.code == 'cancel') {
				memberCard();
			} else {
				memberCard();
				// "支付失败：" + extraData.errmsg;
			}
		}
	}
	// #endif
});

onReachBottom(() => {
	groomList();
});

// ---------- methods ----------
function dateFormat(value) {
	return dayjs(value * 1000).format('YYYY-MM-DD');
}
function moneyFormat(value) {
	return parseFloat(value);
}
function pay() {
	if (type.value === 'free') {
		createMemberCard('weixin');
	} else {
		pay_close.value = true;
	}
}
function scrollToCard() {
	const query = uni.createSelectorQuery().in(instance.proxy);
	query
		.select('#card')
		.boundingClientRect((data) => {
			uni.pageScrollTo({
				scrollTop: data.top
			});
		})
		.exec();
}
function goDetail(id) {
	uni.navigateTo({
		url: `/pages/goods_details/index?id=${id}`
	});
}
// 付费会员数据
function memberCard() {
	uni.showLoading({
		title: i18n.t(`正在加载中`)
	});
	memberCardApi()
		.then((res) => {
			uni.hideLoading();
			const { is_get_free, member_coupons, member_explain, member_rights, member_type } = res.data;
			isGetFree.value = is_get_free;
			userInfo.value = is_get_free.user_info;
			memberRights.value = member_rights;
			memberType.value = member_type;
			memberCoupons.value = member_coupons;
			memberExplain.value = member_explain;
			if (is_get_free.is_record) {
				memberType.value = memberType.value.filter((item) => item.type !== 'free');
			}
			totalPrice.value = memberType.value[0].pre_price;
			type.value = memberType.value[0].type;
			svip.value = memberType.value[0];
			mc_id.value = memberType.value[0].mc_id;
			const balance = payMode.value.find(item => item.value === 'yue');
			if (balance) balance.number = is_get_free.user_info.now_money;
			memberOverdueTime({
				member_type: svip.value.type,
				vip_day: svip.value.vip_day
			}).then((res) => {
				memberEndTime.value = res.data.data;
			});
		})
		.catch((err) => {
			uni.showToast({
				title: err,
				icon: 'none'
			});
		});
}
function groomList() {
	if (finished.value) {
		return;
	}
	uni.showLoading({
		title: i18n.t(`正在加载中`)
	});
	groomListApi(5, {
		page: page.value,
		limit: limit.value
	})
		.then((res) => {
			uni.hideLoading();
			goodsList.value = goodsList.value.concat(res.data.list);
			finished.value = res.data.list.length < limit.value;
			page.value += 1;
		})
		.catch((err) => {
			uni.hideLoading();
		});
}
function checkType(svipItem) {
	type.value = svipItem.type;
	svip.value = svipItem;
	totalPrice.value = svipItem.pre_price;
	mc_id.value = svipItem.mc_id;
	memberOverdueTime({
		member_type: svipItem.type,
		vip_day: svipItem.vip_day,
		mc_id: svipItem.mc_id
	}).then((res) => {
		memberEndTime.value = res.data.data;
	});
}
function closePopup() {
	popupShow.value = false;
}
function getMemberCard() {
	memberCardApi()
		.then((res) => {
			let data = res.data;
			memberType.value = data.member_type;
			memberRights.value = data.member_rights;
			isGetFree.value = data.is_get_free;
		})
		.catch((err) => {});
}
function getOrderPayType() {
	getPayConfig()
		.then((res) => {
			payMode.value = res.data.filter(item => ['weixin', 'weixin_v3', 'alipay', 'allinpay', 'yue'].includes(item.value) && item.payStatus);
			const balance = payMode.value.find(item => item.value === 'yue');
			if (balance) balance.number = userInfo.value.now_money || balance.number;
		})
		.catch((err) => {
			uni.showToast({
				title: err,
				icon: 'none'
			});
		});
}
function memberSelect(index) {
	memberSelected.value = index;
}
// 卡密兑换
function drawMemberCard() {
	let data = {
		member_card_code: '',
		member_card_pwd: '',
		from: 'weixinh5'
	};
	if (!popupShow.value) {
		return (popupShow.value = true);
	}
	if (!account.value) {
		return uni.showToast({
			title: i18n.t(`请输入卡号`),
			icon: 'none'
		});
	}
	if (!password.value) {
		return uni.showToast({
			title: i18n.t(`请输入卡密`),
			icon: 'none'
		});
	}

	// #ifdef H5
	let ua = navigator.userAgent.toLowerCase();
	if (ua.match(/MicroMessenger/i) == 'micromessenger') {
		data.from = 'weixin';
	}
	// #endif
	// #ifdef MP
	data.from = 'routine';
	// #endif
	uni.showLoading({
		title: i18n.t(`正在加载中`)
	});
	// #ifdef MP
	memberCardDraw(data)
		.then((res) => {
			uni.showToast({
				title: res.msg,
				success() {
					popupShow.value = false;
					getMemberCard();
				}
			});
		})
		.catch((err) => {
			uni.showToast({
				title: err,
				icon: 'none'
			});
		});
	// #endif
}
// 立即购买
async function createMemberCard(payType) {
	uni.showLoading({
		title: i18n.t(`正在加载中`)
	});
	let query = {
		pay_type: payType,
		type: '1',
		// #ifdef H5
		from: isWeixin ? 'weixin' : 'weixinh5',
		// #endif
		// #ifdef MP
		from: 'routine',
		// #endif
		// #ifdef APP-PLUS
		from: 'weixin',
		// #endif
		member_type: svip.value.type,
		mc_id: mc_id.value,
		price: svip.value.pre_price,
		money: svip.value.price,
		// #ifdef H5
		quitUrl: location.port
			? location.protocol + '//' + location.hostname + ':' + location.port + '/pages/annex/vip_paid/index'
			: location.protocol + '//' + location.hostname + '/pages/annex/vip_paid/index'
		// #endif
	};
	await openPaySubscribe().catch(() => {});
	memberCardCreate(query)
		.then((res) => {
			if (res.data.status === 'PAY_DEFICIENCY') {
				return uni.showToast({
					icon: 'none',
					title: i18n.t(`${res.msg}`)
				});
			}
			if (parseFloat(svip.value.pre_price) > 0) {
				callPay(res);
			} else {
				memberCard();
				groomList();
				uni.hideLoading();
				uni.showToast({
					title: i18n.t(`成功开启试用`)
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
function callPay(data) {
	let { status, result } = data.data,
		{ orderId, jsConfig } = result;
	switch (status) {
		case 'ORDER_EXIST':
		case 'EXTEND_ORDER':
		case 'ALLINPAY_PAY':
			uni.hideLoading();
			// #ifdef MP
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
				orderId: data.data.result.orderId,
				msg: data.msg
			};
			// #endif
			// #ifdef APP-PLUS
			plus.runtime.openURL(jsConfig.payinfo);
			setTimeout((e) => {
				uni.reLaunch({
					url: '/pages/annex/vip_paid/index'
				});
			}, 1000);
			// #endif
			// #ifdef H5
			formpost(data.data.result.pay_url, jsConfig);
			// #endif
			break;
		case 'PAY_ERROR':
			uni.hideLoading();
			util.Tips(
				{
					title: data.msg
				},
				{
					tab: 5,
					url: '/pages/annex/vip_paid/index'
				}
			);
			break;
		case 'SUCCESS':
			uni.hideLoading();
			util.Tips(
				{
					title: data.msg,
					icon: 'success'
				},
				{
					tab: 5,
					url: '/pages/annex/vip_paid/index'
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
							url: '/pages/annex/vip_paid/index'
						}
					);
				},
				fail: function () {
					uni.showToast({
						title: i18n.t(`取消支付`),
						icon: 'none'
					});
				},
				complete: function () {
					uni.hideLoading();
				}
			});
			// #endif
			// #ifdef H5
			Auth.pay(result.jsConfig)
				.then((res) => {
					util.Tips(
						{
							title: i18n.t(`支付成功`),
							icon: 'success'
						},
						{
							tab: 5,
							url: '/pages/annex/vip_paid/index'
						}
					);
				})
				.catch((err) => {
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
				success: (e) => {
					uni.showToast({
						title: i18n.t(`支付成功`)
					});
					setTimeout((res) => {
						uni.navigateBack();
					}, 2000);
				},
				fail: (e) => {
					uni.showToast({
						title: i18n.t(`支付失败`),
						icon: 'none',
						duration: 2000
					});
				},
				complete: () => {
					uni.hideLoading();
				}
			});
			// #endif
			break;
		case 'WECHAT_H5_PAY':
			uni.showToast({
				title: data.msg,
				success() {
					location.href = jsConfig.h5_url;
				}
			});
			break;
		case 'ALIPAY_PAY':
			uni.hideLoading();
			//#ifdef H5
			formContent.value = jsConfig;
			nextTick(() => {
				document.getElementById('alipaysubmit').submit();
			});
			//#endif
			// #ifdef MP
			uni.navigateTo({
				url: `/pages/users/alipay_invoke/index?id=${data.data.result.order_id}&link=${jsConfig.qrCode}&from=member`
			});
			// #endif
			// #ifdef APP-PLUS
			uni.requestPayment({
				provider: 'alipay',
				orderInfo: jsConfig,
				success: (e) => {
					uni.showToast({
						title: i18n.t(`支付成功`)
					});
					setTimeout((res) => {
						uni.navigateBack();
					}, 2000);
				},
				fail: (e) => {
					uni.showModal({
						content: i18n.t(`支付失败`),
						showCancel: false,
						success: function (res) {
							if (res.confirm) {
							} else if (res.cancel) {
							}
						}
					});
				},
				complete: () => {
					uni.hideLoading();
				}
			});
			// #endif
			break;
	}
}
// payment 组件事件回调（原 this[action] 动态分发改为显式映射）
function onChangeFun(e) {
	let opt = e;
	let action = opt.action || null;
	let value = opt.value != undefined ? opt.value : null;
	pay_close.value = false;
	const handlers = {
		payClose,
		payCheck
	};
	action && handlers[action] && handlers[action](value);
}
function payClose() {
	pay_close.value = false;
}
function payCheck(payType) {
	createMemberCard(payType);
}
// 激活
function checkForm(e) {
	let formData = e.detail.value,
		data = {
			member_card_code: '',
			member_card_pwd: '',
			from: 'H5'
		};
	if (!formData.account) {
		return uni.showToast({
			title: i18n.t(`请输入卡号`),
			icon: 'none'
		});
	}
	if (!formData.password) {
		return uni.showToast({
			title: i18n.t(`请输入卡密`),
			icon: 'none'
		});
	}
	data.member_card_code = formData.account;
	data.member_card_pwd = formData.password;
	// #ifdef H5
	let ua = navigator.userAgent.toLowerCase();
	if (ua.match(/MicroMessenger/i) == 'micromessenger') {
		data.from = 'weixin';
	}
	// #endif
	// #ifdef MP
	data.from = 'routine';
	// #endif
	uni.showLoading({
		title: i18n.t(`激活中`)
	});
	memberCardDraw(data)
		.then((res) => {
			uni.showToast({
				title: res.msg,
				success() {
					memberCard();
					groomList();
					getOrderPayType();
					popupShow.value = false;
				}
			});
		})
		.catch((err) => {
			uni.showToast({
				title: err,
				icon: 'none'
			});
		});
}
</script>

<style lang="scss" scoped>
.card-section {
	height: 328rpx;
	padding: 88rpx 66rpx 0;
	background-color: #ffffff;
	background-image: var(--ui-vip-paid-card);
	background-position: center;
	background-size: 100% 100%;
	background-repeat: no-repeat;

	.image {
		width: 80rpx;
		height: 80rpx;
		border-radius: 50%;
	}

	.text {
		flex: 1;
		margin-left: 15rpx;
		font-size: 22rpx;
		line-height: 30rpx;
		color: #89735b;
	}

	.name {
		margin-bottom: 2rpx;
		font-weight: bold;
		font-size: 30rpx;
		line-height: 42rpx;
		color: #333333;
		max-width: 360rpx;
	}

	.info {
		margin-top: 74rpx;
		font-size: 24rpx;
		color: #ae5a2a;
	}

	.num {
		font-size: 36rpx;
	}

	.btn {
		width: 146rpx;
		height: 44rpx;
		border-radius: 22rpx;
		background-color: #ffffff;
		line-height: 44rpx;
		text-align: center;
	}
}

.right-section {
	background-color: #ffffff;

	.section-hd {
		padding-top: 34rpx;
		padding-bottom: 34rpx;

		.title {
			width: 543rpx;
			height: 90rpx;
			background-image: var(--ui-vip-section-title);
			background-position: center;
			background-size: cover;
			background-repeat: no-repeat;
			font-weight: bold;
			font-size: 34rpx;
			line-height: 1.1;
			color: #89735b;
		}

		.iconfont {
			font-size: 34rpx;
			color: #89735b;
		}
	}

	.section-bd {
		padding: 0 30rpx 15rpx;

		.item {
			width: 338rpx;
			height: 140rpx;
			padding-left: 30rpx;
			border-radius: 6rpx;
			margin-bottom: 14rpx;
			background-color: #f7f7f7;
		}

		.image {
			width: 80rpx;
			height: 80rpx;
		}

		.text {
			flex: 1;
			min-width: 0;
			margin-left: 22rpx;
			overflow: hidden;
			white-space: nowrap;
			font-size: 24rpx;
			line-height: 33rpx;
			color: #9a856d;
		}

		.name {
			margin-bottom: 2rpx;
			font-size: 26rpx;
			line-height: 37rpx;
			color: #333333;
		}
	}
}

.type-section {
	margin-top: 20rpx;
	background-color: #ffffff;

	.title {
		padding: 26rpx 30rpx 0;
		font-size: 24rpx;
		color: #797979;

		.bold {
			display: inline-block;
			margin-right: 14rpx;
			font-weight: bold;
			font-size: 32rpx;
			line-height: 45rpx;
			color: #333333;

			+ view {
				display: inline-block;
			}
		}

		.time {
			margin-left: 14rpx;
			font-size: 24rpx;
			color: #ae5a2a;
		}
	}

	.scroll {
		white-space: nowrap;
	}

	.item {
		display: inline-flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		width: 210rpx;
		height: 232rpx;
		border-radius: 12rpx;
		margin: 32rpx 30rpx 30rpx 0;
		box-shadow: 0 3rpx 30rpx rgba(0, 0, 0, 0.1);
		font-size: 30rpx;
		line-height: 42rpx;
		color: #754e19;

		&:first-child {
			margin-left: 30rpx;
		}

		&.on {
			border: 3rpx solid #fcc282;
			background-color: #fef7ec;
		}
	}

	.new {
		margin-top: 22rpx;
		font-weight: 600;
		font-size: 34rpx;

		.num {
			font-size: 48rpx;
			line-height: 48rpx;
		}
	}

	.old {
		margin-top: 13rpx;
		text-decoration: line-through;
		font-size: 24rpx;
	}

	.info {
		margin-top: 13rpx;
		font-size: 24rpx;
	}

	.agree {
		font-size: 22rpx;
		text-align: center;
		color: #797979;

		.link {
			display: inline-block;
		}

		.mark {
			color: #ae5a2a;
		}
	}

	.buy {
		height: 80rpx;
		border-radius: 12rpx;
		margin: 30rpx 30rpx 0;
		background: linear-gradient(90deg, #fee2b7 0%, #fdc383 100%);
		font-size: 30rpx;
		line-height: 80rpx;
		text-align: center;
		color: #5d3324;
	}

	.cash {
		padding-top: 26rpx;
		padding-bottom: 29rpx;
		font-size: 28rpx;
		text-align: center;
		color: #754e19;
	}
}

.coupon-section {
	margin-top: 20rpx;
	background-color: #ffffff;

	.section-hd {
		padding: 26rpx 30rpx 0;

		.title {
			font-weight: bold;
			font-size: 32rpx;
			line-height: 45rpx;
			color: #333333;
		}

		.link {
			display: flex;
			align-items: center;
			font-size: 24rpx;
			color: #89735b;

			.iconfont {
				margin-left: 6rpx;
				font-size: 14rpx;
			}
		}
	}

	.section-bd {
		white-space: nowrap;

		.item {
			display: inline-flex;
			width: 360rpx;
			height: 148rpx;
			margin: 26rpx 20rpx 30rpx 0;
			background-image: var(--ui-vip-paid-coupon);
			background-position: center;
			background-size: cover;
			background-repeat: no-repeat;

			&:first-child {
				margin-left: 30rpx;
			}

			&:last-child {
				margin-right: 30rpx;
			}

			&.gray {
				background-image: var(--ui-vip-paid-coupon-gray);

				.btn {
					color: #9f9f9f;
				}

				.money {
					color: #fff;

					.num {
						color: #fff;
					}
				}

				.name {
					color: #fff;
				}

				.text {
					color: #9f9f9f;
				}

				.mark {
					color: #a8a8a8;
				}
			}

			.btn {
				width: 52rpx;
				padding-right: 14rpx;
				padding-left: 14rpx;
				white-space: pre-wrap;
				font-size: 24rpx;
				color: #5d3324;
			}

			.text {
				max-width: 160rpx;
				margin-left: 14rpx;
			}

			.name {
				overflow: hidden;
				white-space: nowrap;
				text-overflow: ellipsis;
				font-weight: bold;
				font-size: 28rpx;
				line-height: 40rpx;
				color: #5d3324;
			}

			.mark {
				display: inline-block;
				height: 35rpx;
				padding-right: 14rpx;
				padding-left: 14rpx;
				border-radius: 19rpx;
				margin-top: 6rpx;
				background-color: rgba(255, 255, 255, 0.3);
				font-size: 22rpx;
				line-height: 35rpx;
				color: #5f3426;
			}

			.money {
				max-width: 170rpx;
				text-align: center;
				font-size: 30rpx;
				font-weight: 800;
			}

			.num {
				position: relative;
				font-size: 54rpx;
				color: #333333;
			}
		}
	}
}

.goods-section {
	margin-top: 20rpx;
	background-color: #ffffff;

	.section-hd {
		padding: 26rpx 30rpx 0;
		font-weight: bold;
		font-size: 32rpx;
		line-height: 45rpx;
		color: #333333;
	}

	.section-bd {
		padding: 0 30rpx 0 7rpx;
		margin-top: 26rpx;

		.item {
			width: 215rpx;
			padding-bottom: 24rpx;
			margin-left: 23rpx;
		}

		.image {
			width: 215rpx;
			height: 215rpx;
			border-radius: 6rpx;
		}

		.name {
			margin-top: 10rpx;
			overflow: hidden;
			white-space: nowrap;
			text-overflow: ellipsis;
			font-size: 26rpx;
			line-height: 37rpx;
			color: #333333;
		}

		.svip-price {
			margin-top: 6rpx;
			font-size: 26rpx;
			color: #333333;

			image {
				width: 60rpx;
				height: 24rpx;
				margin-left: 6rpx;
			}
		}

		.shop-price {
			margin-top: 4rpx;
			font-size: 20rpx;
			color: #999999;
		}
	}
}

.popup {
	position: fixed;
	top: 50%;
	left: 50%;
	z-index: 10;
	width: 600rpx;
	height: 634rpx;
	padding: 60rpx 40rpx 0;
	margin-top: -79rpx;
	background-image: var(--ui-vip-paid-popup);
	background-position: center;
	background-size: 100% 100%;
	background-repeat: no-repeat;
	transform: translate(-50%, -50%) scale(0);
	transition: 0.3s;

	&.on {
		transform: translate(-50%, -50%) scale(1);
	}

	.cont {
		padding: 20rpx;
	}

	.title {
		display: flex;
		justify-content: center;
		align-items: center;
		padding-top: 45rpx;
		padding-bottom: 45rpx;

		.line {
			width: 70rpx;
			height: 2rpx;
			background-color: #e6c3a5;
		}

		.name {
			margin-right: 25rpx;
			margin-left: 25rpx;
			font-size: 40rpx;
			color: #89735b;
		}
	}

	.form {
		.input {
			padding-right: 50rpx;
			padding-left: 50rpx;
			height: 98rpx;
			border-radius: 12rpx;
			margin-bottom: 35rpx;
			background: rgba(255, 187, 129, 0.15);
			font-size: 30rpx;
		}

		.button {
			height: 72rpx;
			border-radius: 36rpx;
			margin: 45rpx 80rpx 20rpx;
			background: #353841;
			font-size: 32rpx;
			line-height: 72rpx;
			color: #ffffff;
		}
	}

	.image {
		position: absolute;
		top: -57rpx;
		left: 50%;
		width: 146rpx;
		height: 114rpx;
		transform: translateX(-50%);
	}

	.iconfont {
		position: absolute;
		bottom: -110rpx;
		left: 50%;
		background: none;
		transform: translateX(-50%);
		font-size: 48rpx;
		line-height: 1;
		color: #ffffff;
	}
}
</style>
