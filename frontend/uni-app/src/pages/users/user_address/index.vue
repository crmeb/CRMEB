<template>
	<view :style="colorStyle">
		<form @submit="formSubmit">
			<view class='addAddress'>
				<view class='list'>
					<view class='item acea-row row-between-wrapper'>
						<view class='name'>{{$t(`姓名`)}}</view>
						<input type='text' :placeholder='$t(`请输入姓名`)' name='real_name' :value="userAddress.real_name"
							placeholder-class='placeholder'></input>
					</view>
					<view class='item acea-row row-between-wrapper'>
						<view class='name'>{{$t(`联系电话`)}}</view>
						<input type='number' :placeholder='$t(`请输入联系电话`)' name="phone" :value='userAddress.phone'
							placeholder-class='placeholder' pattern="\d*"></input>
					</view>
					<view class='item acea-row row-between-wrapper'>
						<view class='name'>{{$t(`所在地区`)}}</view>
						<view class="address">
							<picker mode="multiSelector" @change="bindRegionChange"
								@columnchange="bindMultiPickerColumnChange" :value="valueRegion" :range="multiArray">
								<view class='acea-row'>
									<view class="picker">{{region[0]}}，{{region[1]}}，{{region[2]}}</view>
								</view>
							</picker>
						</view>
					</view>
					<view class='item acea-row row-between-wrapper'>
						<view class='name'>{{$t(`详细地址`)}}</view>
						<input type='text' :placeholder='$t(`请填写具体地址`)' name='detail' placeholder-class='placeholder'
							:value='userAddress.detail'></input>
					</view>
				</view>
				<view class='default acea-row row-middle' @click='ChangeIsDefault'>
					<checkbox-group>
						<checkbox :checked="userAddress.is_default ? true : false" backgroundColor="#ffffff" activeBackgroundColor="var(--view-theme, #E93323)" activeBorderColor="var(--view-theme, #E93323)" iconColor="#ffffff" />{{$t(`设置为默认地址`)}}
					</checkbox-group>
				</view>

				<button class='keepBnt bg-color' form-type="submit">{{$t(`立即保存`)}}</button>
				<!-- #ifdef MP -->
				<view class="wechatAddress" v-if="!id" @click="getWxAddress">{{$t(`导入微信地址`)}}</view>
				<!-- #endif -->
				<!-- #ifdef H5 -->
				<view class="wechatAddress" v-if="isWeixin && !id" @click="getAddress">{{$t(`导入微信地址`)}}
				</view>
				<!-- #endif -->
			</view>
		</form>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref, watch } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
	editAddress as editAddressApi,
	getAddressDetail
} from '@/api/user.js';
import {
	getCity
} from '@/api/api.js';
import {
	toLogin
} from '@/libs/login.js';
import home from '@/components/home';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
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
// #ifdef H5
const isWeixin = Auth.isWeixin();
// #endif

// ---------- data ----------
const regionDval = [i18n.t(`浙江省`), i18n.t(`杭州市`), i18n.t(`滨江区`)];
const cartId = ref(''); //购物车id
const pinkId = ref(0); //拼团id
const couponId = ref(0); //优惠券id
const id = ref(0); //地址id
const userAddress = ref({
	is_default: false
}); //地址详情
const region = ref([i18n.t(`省`), i18n.t(`市`), i18n.t(`区`)]);
const valueRegion = ref([0, 0, 0]);
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const district = ref([]);
const multiArray = ref([]);
const multiIndex = ref([0, 0, 0]);
const cityId = ref(0);
const defaultRegion = [i18n.t(`广东省`), i18n.t(`广州市`), i18n.t(`番禺区`)];
const defaultRegionCode = ref('110101');
const news = ref('');
const noCoupon = ref(0);
const is_gift = ref(0);
const order_id = ref(0);
// 原代码未在 data 声明、直接挂到实例上的字段（与 order_id 并存）
const orderId = ref('');
// 打开来源标记：order_create = 下单页地址链路（保存后经 uni.$emit 直跳回下单页）
const from = ref('');

// ---------- watch ----------
watch(isLogin, (newV, oldV) => {
	if (newV) {
		getUserAddress();
	}
}, { deep: true });

// ---------- 生命周期 ----------
onLoad((options) => {
	if (isLogin.value) {
		cartId.value = options.cartId || '';
		pinkId.value = options.pinkId || 0;
		couponId.value = options.couponId || 0;
		id.value = options.id || 0;
		noCoupon.value = options.noCoupon || 0;
		news.value = options.new || '';
		is_gift.value = options.is_gift || '';
		orderId.value = options.order_id || '';
		from.value = options.from || '';
		uni.setNavigationBarTitle({
			title: options.id ? i18n.t(`修改地址`) : i18n.t(`添加地址`)
		})
		getUserAddress();
		getCityList();
	} else {
		toLogin();
	}
});

// ---------- methods ----------
// from=order_create 链路：保存成功后通知下单引擎选中该地址，并跨过地址列表页直接返回下单页
function backToOrderCreate(addressId) {
	uni.$emit('orderCreateAddressSelect', { addressId: addressId });
	uni.navigateBack({ delta: 2 });
}

/**
 * 是否领取礼物选地址链路（is_gift=2）
 * 与正常购买 / 下单引擎回跳互斥，避免误进 center/order/create
 */
function isGiftReceiveFlow() {
	return Number(is_gift.value) === 2 && !!orderId.value;
}

/** 回跳礼物确认页并带回新建/编辑后的地址 */
function redirectGiftConfirm(addressId) {
	uni.redirectTo({
		url: '/pages/goods/order_confirm/index?order_id=' + orderId.value +
			'&is_gift=2&addressId=' + addressId + '&is_address=1'
	});
}

/**
 * 保存地址后的统一回跳：礼物领取 → 礼物确认页；有 cartId → 正常下单页；其余原逻辑
 * @param {number|string} addressId
 */
function afterAddressSaved(addressId) {
	if (isGiftReceiveFlow() && addressId) {
		redirectGiftConfirm(addressId);
		return true;
	}
	return false;
}
// #ifdef APP-PLUS
// 获取选择的地区
function handleGetRegion(regionData) {
	region.value = regionData
}
// #endif
// 回去地址数据
function getCityList() {
	getCity().then(res => {
		district.value = res.data
		initialize();
	})
}
function initialize() {
	let province = [],
		city = [],
		area = [];
	let cityChildren = district.value[0].c || [];
	let areaChildren = cityChildren.length ? (cityChildren[0].c || []) : [];
	district.value.forEach((item, i) => {
		province.push(item.n);
		if (item.n === region.value[0]) {
			valueRegion.value[0] = i
			multiIndex.value[0] = i
		}
	});
	district.value[valueRegion.value[0]].c.forEach((item, i) => {
		if (region.value[1] == item.c) {
			valueRegion.value[1] = i
			multiIndex.value[1] = i
		}
		city.push(item.n);
	});
	district.value[valueRegion.value[0]].c[valueRegion.value[1]].c.forEach((item, i) => {
		if (region.value[2] == item.c) {
			valueRegion.value[2] = i
			multiIndex.value[2] = i
		}
		area.push(item.n);
	});
	multiArray.value = [province, city, area]

}
function bindRegionChange(e) {
	let multiIndexCur = multiIndex.value,
		province = district.value[multiIndexCur[0]] || {
			c: []
		},
		city = province.c[multiIndexCur[1]] || {
			v: 0
		},
		multiArrayCur = multiArray.value,
		value = e.detail.value;

	region.value = [multiArrayCur[0][value[0]], multiArrayCur[1][value[1]], multiArrayCur[2][value[2]]]
	// region.value[0] = multiArrayCur[0][value[0]];
	// region.value[1] = multiArrayCur[1][value[1]];
	// region.value[2] = multiArrayCur[2][value[2]];
	cityId.value = city.v
	valueRegion.value = [0, 0, 0]
	initialize();
}
function bindMultiPickerColumnChange(e) {
	let column = e.detail.column,
		value = e.detail.value,
		currentCity = district.value[value] || {
			c: []
		},
		multiArrayCur = multiArray.value,
		multiIndexCur = multiIndex.value;
	multiIndexCur[column] = value;
	switch (column) {
		case 0:
			let areaList = currentCity.c[0] || {
				c: []
			};
			multiArrayCur[1] = currentCity.c.map((item) => {
				return item.n;
			});
			multiArrayCur[2] = areaList.c.map((item) => {
				return item.n;
			});
			break;
		case 1:
			let cityList = district.value[multiIndexCur[0]].c[multiIndexCur[1]].c || [];
			multiArrayCur[2] = cityList.map((item) => {
				return item.n;
			});
			break;
		case 2:

			break;
	}
	// #ifdef MP || APP-PLUS
	multiArray.value[0] = multiArrayCur[0];
	multiArray.value[1] = multiArrayCur[1];
	multiArray.value[2] = multiArrayCur[2];
	// #endif
	// #ifdef H5
	multiArray.value = multiArrayCur;
	// #endif

	multiIndex.value = multiIndexCur
	// setData({ multiArray: multiArrayCur, multiIndex: multiIndexCur});
}
// 授权回调
function onLoadFun() {
	getUserAddress();
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e
}
// 原 this.$refs[str].show() 动态调用；当前模板已无对应 ref，保留占位实现
const refMap = {};
function toggleTab(str) {
	refMap[str] && refMap[str].show();
}
// bindRegionChange: function(e) {
// 	region.value = e.detail.value;
// },
function onConfirm(val) {
	region.value = val.checkArr[0] + '-' + val.checkArr[1] + '-' + val.checkArr[2];
}
function getUserAddress() {
	if (!id.value) return false;
	getAddressDetail(id.value).then(res => {
		// let region = [res.data.province, res.data.city, res.data.district];
		let regionNew = [res.data.province, res.data.city, res.data.district];
		userAddress.value = res.data;
		region.value = regionNew;
		cityId.value = res.data.city_id
	});
}
// 导入共享地址（小程序）
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
						type: 1,
					}).then(res => {
						setTimeout(function() {
							const savedId = id.value ? id.value : res.data.id;
							if (afterAddressSaved(savedId)) {
								return;
							}
							if (cartId.value) {
								let cartIdVal = cartId.value;
								let pinkIdVal = pinkId.value;
								let couponIdVal = couponId.value;
								cartId.value = '';
								pinkId.value = '';
								couponId.value = '';
								uni.navigateTo({
									url: '/pages/center/order/create/index?cartId=' +
										cartIdVal +
										'&address_id=' + savedId +
										'&pinkId=' +
										pinkIdVal +
										'&couponId=' +
										couponIdVal +
										'&new=' + news
										.value +
										'&noCoupon=' + noCoupon
										.value + '&is_gift=' + is_gift.value
										+
										'&order_id=' + orderId.value
									});
								} else if (from.value === 'order_create') {
									backToOrderCreate(savedId);
								} else {
									uni.navigateBack({
										delta: 1
									});
								}
						}, 1000);
						return util.Tips({
							title: i18n.t(`添加成功`),
							icon: 'success'
						});
					}).catch(err => {
						return util.Tips({
							title: err
						});
					});
				},
				fail: function(res) {
					if (res.errMsg == 'chooseAddress:cancel') return util
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
		},
	})
}
// 导入共享地址（微信）；
function getAddress() {
	Auth.openAddress().then(userInfo => {
		editAddressApi({
				id: id.value,
				real_name: userInfo.userName,
				phone: userInfo.telNumber,
				address: {
					province: userInfo.provinceName,
					city: userInfo.cityName,
					district: userInfo.countryName
				},
				detail: userInfo.detailInfo,
				is_default: 1,
				post_code: userInfo.postalCode,
				type: 1,
			})
			.then((res) => {
				// close();
				util.Tips({
					title: i18n.t(`添加成功`),
					icon: 'success'
				}, () => {
					const savedId = id.value ? id.value : res.data.id;
					if (afterAddressSaved(savedId)) {
						return;
					}
					if (cartId.value) {
						let cartIdVal = cartId.value;
						let pinkIdVal = pinkId.value;
						let couponIdVal = couponId.value;
						cartId.value = '';
						pinkId.value = '';
						couponId.value = '';
						uni.navigateTo({
							url: '/pages/center/order/create/index?cartId=' +
								cartIdVal + '&address_id=' + savedId + '&pinkId=' + pinkIdVal +
								'&couponId=' +
								couponIdVal + '&new=' + news.value + '&is_gift=' + is_gift.value +
								'&order_id=' + orderId.value
							});
						} else if (from.value === 'order_create') {
							backToOrderCreate(savedId);
						} else {
							uni.navigateTo({
								url: '/pages/users/user_address_list/index'
							})
							// history.back();
						}
				});
			})
			.catch(err => {
				// close();
				return util.Tips({
					title: err || i18n.t(`添加失败`)
				});
			});
	}).catch(err => {});
}
/**
 * 提交用户添加地址
 *
 */
function formSubmit(e) {
	let value = e.detail.value;
	if (!value.real_name.trim()) return util.Tips({
		title: i18n.t(`请填写收货人姓名`)
	});
	if (!value.phone) return util.Tips({
		title: i18n.t(`请填写联系电话`)
	});
	if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(value.phone)) return util.Tips({
		title: i18n.t(`请输入正确的手机号码`)
	});
	if (region.value[0] == i18n.t(`省`)) return util.Tips({
		title: i18n.t(`请选择所在地区`)
	});
	if (!value.detail.trim()) return util.Tips({
		title: i18n.t(`请填写详细地址`)
	});
	value.id = id.value;
	let regionArray = region.value;
	value.address = {
		province: regionArray[0],
		city: regionArray[1],
		district: regionArray[2],
		city_id: cityId.value,
	};
	value.is_default = userAddress.value.is_default ? 1 : 0;

	uni.showLoading({
		title: i18n.t(`保存中`),
		mask: true
	})
	editAddressApi(value).then(res => {
		if (id.value)
			util.Tips({
				title: i18n.t(`修改成功`),
				icon: 'success'
			});
		else
			util.Tips({
				title: i18n.t(`添加成功`),
				icon: 'success'
			});
		setTimeout(function() {
			const savedId = id.value ? id.value : res.data.id;
			if (afterAddressSaved(savedId)) {
				return;
			}
			if (cartId.value) {
				let cartIdVal = cartId.value;
				let pinkIdVal = pinkId.value;
				let couponIdVal = couponId.value;
				cartId.value = '';
				pinkId.value = '';
				couponId.value = '';
				uni.navigateTo({
					url: '/pages/center/order/create/index?new=' + news.value +
						'&cartId=' + cartIdVal + '&address_id=' + savedId + '&pinkId=' + pinkIdVal + '&couponId=' +
						couponIdVal +
				'&noCoupon=' + noCoupon
							.value + '&is_gift=' + is_gift.value +
							'&order_id=' + orderId.value
					});
				} else if (from.value === 'order_create') {
					backToOrderCreate(savedId);
				} else {
				// #ifdef H5
				return history.back();
				// #endif
				// #ifndef H5
				return uni.navigateBack({
					delta: 1,
				})
				// #endif
			}
		}, 1000);
	}).catch(err => {
		return util.Tips({
			title: err
		});
	})
}
function ChangeIsDefault(e) {
	userAddress.value['is_default'] = !userAddress.value.is_default;
}
</script>

<style scoped lang="scss">
	.fontcolor {
		color: var(--view-theme);
	}

	.addAddress .list {
		background-color: #fff;
	}

	.addAddress .list .item {
		padding: 30rpx;
		border-top: 1rpx solid #eee;
	}

	.addAddress .list .item .name {
		width: 195rpx;
		font-size: 30rpx;
		color: #333;
	}

	.addAddress .list .item .address {
		// width: 412rpx;
		flex: 1;
		margin-left: 20rpx;
	}

	.addAddress .list .item input {
		width: 475rpx;
		font-size: 30rpx;
	}

	.addAddress .list .item .placeholder {
		color: #ccc;
	}

	.addAddress .list .item picker {
		width: 475rpx;
	}

	.addAddress .list .item picker .picker {
		width: 410rpx;
		font-size: 30rpx;
	}

	.addAddress .list .item picker .iconfont {
		font-size: 43rpx;
	}

	.addAddress .default {
		padding: 0 30rpx;
		height: 90rpx;
		background-color: #fff;
		margin-top: 23rpx;
	}

	.addAddress .default checkbox {
		margin-right: 15rpx;
	}

	.addAddress .keepBnt {
		width: 690rpx;
		height: 86rpx;
		border-radius: 50rpx;
		text-align: center;
		line-height: 86rpx;
		margin: 50rpx auto;
		font-size: 32rpx;
		color: #fff;
	}

	.addAddress .wechatAddress {
		width: 690rpx;
		height: 86rpx;
		border-radius: 50rpx;
		text-align: center;
		line-height: 86rpx;
		margin: 0 auto;
		font-size: 32rpx;
		color: var(--view-theme);
		border: 1px solid var(--view-theme);
	}
</style>
