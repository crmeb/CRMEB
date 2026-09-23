<template>
	<view :style="colorStyle">
		<view class='address-management' :class='addressList.length < 1 && page > 1 ? "fff":""'>
			<view class='line'>
				<image src='../../../static/images/line.jpg' v-if="addressList.length"></image>
			</view>
			<radio-group class="radio-group" @change="radioChange" v-if="addressList.length">
				<view class='item' v-for="(item,index) in addressList" :key="index">
					<view class='address' @click='goOrder(item.id)'>
						<view class='consignee'>{{$t(`收货人`)}}：{{item.real_name}}<text
								class='phone'>{{item.phone}}</text></view>
						<view>{{$t(`收货地址`)}}：{{item.province}}{{item.city}}{{item.district}}{{item.detail}}</view>
					</view>
					<view class='operation acea-row row-between-wrapper'>
						<!-- #ifndef MP -->
						<radio class="radio" :value="index.toString()" :checked="item.is_default ? true : false">
							<text>{{$t(`设为默认`)}}</text>
						</radio>
						<!-- #endif -->
						<!-- #ifdef MP -->
						<radio class="radio" :value="index" :checked="item.is_default ? true : false">
							<text>{{$t(`设为默认`)}}</text>
						</radio>
						<!-- #endif -->
						<view class='acea-row row-middle'>
							<view @click='editAddress(item.id)'><text class='iconfont icon-bianji'></text>{{$t(`编辑`)}}
							</view>
							<view @click='delAddress(index)'><text class='iconfont icon-shanchu'></text>{{$t(`删除`)}}
							</view>
						</view>
					</view>
				</view>
			</radio-group>
			<view class='loadingicon acea-row row-center-wrapper' v-if="addressList.length">
				<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
			</view>
			<view class='noCommodity' v-if="addressList.length < 1 && page > 1">
				<view class='pictrue'>
					<image :src="imgHost + '/statics/images/noAddress.png'"></image>
				</view>
			</view>
			<view style='height:120rpx;'></view>
			<view class='footer acea-row row-between-wrapper'>
				<!-- #ifdef APP-PLUS -->
				<view class='addressBnt on' @click='addAddress'><text
						class='iconfont icon-tianjiadizhi'></text>{{$t(`添加新地址`)}}</view>
				<!-- #endif -->
				<!-- #ifdef MP-->
				<view class='addressBnt wxbnt' @click='addAddress'><text
						class='iconfont icon-tianjiadizhi'></text>{{$t(`添加新地址`)}}</view>
				<view class='addressBnt' @click='getWxAddress'><text
						class='iconfont icon-weixin2'></text>{{$t(`导入微信地址`)}}
				</view>
				<!-- #endif -->
				<!-- #ifdef H5-->
				<view class='addressBnt' :class="isWeixin?'wxbnt':'on'" @click='addAddress'><text
						class='iconfont icon-tianjiadizhi'></text>{{$t(`添加新地址`)}}</view>
				<view class=""></view>
				<view class='addressBnt' @click='getAddress' v-if="isWeixin"><text
						class='iconfont icon-weixin2'></text>{{$t(`导入微信地址`)}}</view>
				<!-- #endif -->
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow, onReachBottom } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
	getAddressList as getAddressListApi,
	setAddressDefault,
	delAddress as delAddressApi,
	editAddress as editAddressApi
} from '@/api/user.js';
import {
	toLogin
} from '@/libs/login.js';
import home from '@/components/home';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
import {
	HTTP_REQUEST_URL
} from '@/config/app';
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
const imgHost = HTTP_REQUEST_URL;
const addressList = ref([]);
const cartId = ref('');
const pinkId = ref(0);
const couponId = ref(0);
const loading = ref(false);
const loadend = ref(false);
const loadTitle = ref(i18n.t(`加载更多`));
const page = ref(1);
const limit = ref(20);
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const news = ref('');
const noCoupon = ref(0);
const is_gift = ref(0);
const order_id = ref(0);
// 原代码未在 data 声明、直接挂到实例上的字段（与 order_id 并存）
const orderId = ref(0);
// 打开来源标记：order_create = 下单页引擎（走 uni.$emit 事件回跳），空 = 地址管理
const from = ref('');
// #ifdef H5
const isWeixin = ref(Auth.isWeixin());
// #endif
// #ifndef H5
const isWeixin = ref(false);
// #endif

// ---------- 生命周期 ----------
onLoad((options) => {
	if (isLogin.value) {
		cartId.value = options.cartId || '';
		noCoupon.value = options.noCoupon || 0;
		pinkId.value = options.pinkId || 0;
		couponId.value = options.couponId || 0;
		news.value = options.news || 0;
		orderId.value = options.order_id || 0;
		is_gift.value = options.is_gift || 0;
		from.value = options.from || '';
		getAddressList(true);
	} else {
		toLogin();
	}
});
onShow(() => {
	getAddressList(true);
});
onReachBottom(() => {
	getAddressList();
});

// ---------- methods ----------
function onLoadFun() {
	getAddressList();
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e
}
/*
 * 导入微信地址（小程序）
 */
function getWxAddress() {
	uni.authorize({
		scope: 'scope.address',
		success: function(res) {
			uni.chooseAddress({
				success: function(res) {
					let addressP = {};
					addressP.province = res.provinceName;
					addressP.city = res.cityName;
					addressP.district = res.countyName;

					editAddressApi({
						address: addressP,
						is_default: 1,
						real_name: res.userName,
						post_code: res.postalCode,
						phone: res.telNumber,
						detail: res.detailInfo,
						id: 0,
						type: 1
					}).then(res => {
						util.Tips({
							title: i18n.t(`添加成功`),
							icon: 'success'
						}, function() {
							getAddressList(true);
						});
					}).catch(err => {
						return util.Tips({
							title: err
						});
					});
				},
				fail: function(err) {
					if (err.errMsg == 'chooseAddress:cancel') return util
						.Tips({
							title: i18n.t(`取消选择`)
						});
				},
			})
		},
		fail: function(res) {
			uni.showModal({
				title: i18n.t(`您已拒绝导入微信地址权限`),
				content: i18n.t(`是否进入权限管理，调整授权？`),
				success(res) {
					if (res.confirm) {
						uni.openSetting({
							success: function(res) {}
						});
					} else if (res.cancel) {
						return util.Tips({
							title: i18n.t(`已取消！`)
						});
					}
				}
			})
		}
	})
}
/*
 * 导入微信地址（公众号）
 */
function getAddress() {
	Auth.openAddress().then(userInfo => {
		// open();
		editAddressApi({
				real_name: userInfo.userName,
				phone: userInfo.telNumber,
				address: {
					province: userInfo.provinceName,
					city: userInfo.cityName,
					district: userInfo.countryName
				},
				detail: userInfo.detailInfo,
				post_code: userInfo.postalCode,
				is_default: 1,
				type: 1
			})
			.then(() => {
				util.Tips({
					title: i18n.t(`添加成功`),
					icon: 'success'
				}, function() {
					// close();
					getAddressList(true);
				});
			})
			.catch(err => {
				// close();
				return util.Tips({
					title: err || i18n.t(`添加失败`)
				});
			});
	});
}
/**
 * 获取地址列表
 *
 */
function getAddressList(isPage) {
	if (isPage) {
		loadend.value = false;
		page.value = 1;
		addressList.value = [];
	};
	if (loading.value) return;
	if (loadend.value) return;
	loading.value = true;
	loadTitle.value = '';
	getAddressListApi({
		page: page.value,
		limit: limit.value
	}).then(res => {
		let list = res.data;
		let loadendNew = list.length < limit.value;
		addressList.value = util.SplitArray(list, addressList.value);
		loadend.value = loadendNew;
		loadTitle.value = loadendNew ? i18n.t(`我也是有底线的`) : i18n.t(`加载更多`);
		page.value = page.value + 1;
		loading.value = false;
	}).catch(err => {
		loading.value = false;
		loadTitle.value = i18n.t(`加载更多`);
	});
}
/**
 * 设置默认地址
 */
function radioChange(e) {
	let index = parseInt(e.detail.value);
	let address = addressList.value[index];
	if (address == undefined) return util.Tips({
		title: i18n.t(`您设置的默认地址不存在!`)
	});
	setAddressDefault(address.id).then(res => {
		for (let i = 0, len = addressList.value.length; i < len; i++) {
			if (i == index) addressList.value[i].is_default = true;
			else addressList.value[i].is_default = false;
		}
		util.Tips({
			title: i18n.t(`设置成功`),
			icon: 'success'
		}, function() {});
	}).catch(err => {
		return util.Tips({
			title: err
		});
	});
}
/**
 * 编辑地址
 */
function editAddress(id) {
	let cartIdVal = cartId.value,
		pinkIdVal = pinkId.value,
		couponIdVal = couponId.value;
	cartId.value = '';
	pinkId.value = '';
	couponId.value = '';
	uni.navigateTo({
		url: '/pages/users/user_address/index?id=' + id + '&cartId=' + cartIdVal + '&pinkId=' +
			pinkIdVal + '&couponId=' +
			couponIdVal + '&new=' + news.value + '&is_gift=' + is_gift.value + '&order_id=' + orderId.value +
			(from.value === 'order_create' ? '&from=order_create' : '')
	})
}
/**
 * 删除地址
 */
function delAddress(index) {
	let address = addressList.value[index];
	if (address == undefined) return util.Tips({
		title: i18n.t(`您删除的地址不存在!`)
	});
	delAddressApi(address.id).then(res => {
		util.Tips({
			title: i18n.t(`删除成功`),
			icon: 'success'
		}, function() {
			addressList.value.splice(index, 1);
		});
	}).catch(err => {
		return util.Tips({
			title: err
		});
	});
}
/**
 * 新增地址
 */
function addAddress() {
	let cartIdVal = cartId.value,
		pinkIdVal = pinkId.value,
		couponIdVal = couponId.value;
	cartId.value = '';
	pinkId.value = '';
	couponId.value = '';
	uni.navigateTo({
		url: '/pages/users/user_address/index?cartId=' + cartIdVal + '&pinkId=' + pinkIdVal +
			'&couponId=' + couponIdVal + '&new=' + news.value + '&is_gift=' + is_gift.value + '&order_id=' + orderId.value +
			(from.value === 'order_create' ? '&from=order_create' : '')
	})
}
function goOrder(id) {
	let cartIdVal = '';
	let pinkIdVal = '';
	let couponIdVal = '';
	// 下单页引擎打开：通知引擎选中地址后返回（OrderCreateEngine 监听该事件），保留下单页已选状态
	if (from.value === 'order_create') {
		if (!id) return;
		uni.$emit('orderCreateAddressSelect', { addressId: id });
		uni.navigateBack();
		return;
	}
	if (cartId.value && id) {
		cartIdVal = cartId.value;
		pinkIdVal = pinkId.value;
		couponIdVal = couponId.value;
		cartId.value = '';
		pinkId.value = '';
		couponId.value = '';
		uni.redirectTo({
			url: '/pages/center/order/create/index?is_address=1&new=' + news.value + '&cartId=' +
				cartIdVal + '&address_id=' + id + '&pinkId=' +
				pinkIdVal + '&couponId=' + couponIdVal + '&noCoupon=' + noCoupon.value + '&is_gift=' + is_gift.value + '&order_id=' + orderId.value

		})
	}
}
</script>

<style>
	.address-management.fff {
		background-color: #fff;
		height: 1300rpx
	}

	.address-management .line {
		width: 100%;
		height: 3rpx;
	}

	.address-management .line image {
		width: 100%;
		height: 100%;
		display: block;
	}

	.address-management .item {
		background-color: #fff;
		padding: 0 30rpx;
		margin-bottom: 12rpx;
	}

	.address-management .item .address {
		padding: 30rpx 0;
		border-bottom: 1rpx solid #eee;
		font-size: 28rpx;
		color: #282828;
	}

	.address-management .item .address .consignee {
		font-size: 28rpx;
		font-weight: bold;
		margin-bottom: 8rpx;
	}

	.address-management .item .address .consignee .phone {
		margin-left: 25rpx;
	}

	.address-management .item .operation {
		height: 83rpx;
		font-size: 28rpx;
		color: #282828;
	}

	.address-management .item .operation .radio text {
		margin-left: 13rpx;
	}

	.address-management .item .operation .iconfont {
		color: #2c2c2c;
		font-size: 35rpx;
		vertical-align: -2rpx;
		margin-right: 10rpx;
	}

	.address-management .item .operation .iconfont.icon-shanchu {
		margin-left: 40rpx;
		font-size: 38rpx;
	}

	.address-management .footer {
		position: fixed;
		width: 100%;
		background-color: #fff;
		bottom: 0;
		height: 106rpx;
		padding: 0 30rpx;
		box-sizing: border-box;
	}

	.address-management .footer .addressBnt {
		width: 330rpx;
		height: 76rpx;
		border-radius: 50rpx;
		text-align: center;
		line-height: 76rpx;
		font-size: 30rpx;
		color: #fff;
		background-color: var(--view-theme);
	}

	.address-management .footer .addressBnt.on {
		width: 690rpx;
		margin: 0 auto;
	}

	.address-management .footer .addressBnt .iconfont {
		font-size: 35rpx;
		margin-right: 8rpx;
		vertical-align: -1rpx;
	}

	.address-management .footer .addressBnt.wxbnt {
		background-color: #FE960F;
	}
</style>
