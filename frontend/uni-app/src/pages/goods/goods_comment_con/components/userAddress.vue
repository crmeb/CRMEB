<template>
	<view class="aleart" v-if="aleartStatus">
		<image src="../../../../static/images/poster-close.png" class="close" @click="posterImageClose"></image>
		<view class="title">
			<image class="title-img" src="../../static/address-aleart-header.png" mode=""></image>
		</view>
		<view class="aleart-body">
			<form @submit="formSubmit" class="form-data">
				<view class='addAddress'>
					<view class='list'>
						<view class='item acea-row row-between-wrapper'>
							<input type='text' :placeholder='$t(`请输入姓名`)' name='name' :value="userAddress.name"
								placeholder-class='placeholder'></input>
						</view>
						<view class='item acea-row row-between-wrapper'>
							<input type='number' :placeholder='$t(`请输入手机号`)' name="phone" :value='userAddress.phone'
								placeholder-class='placeholder' pattern="\d*"></input>
						</view>
						<view class='item acea-row row-between-wrapper'>
							<view class="address">
								<picker mode="multiSelector" @change="bindRegionChange"
									@columnchange="bindMultiPickerColumnChange" :value="valueRegion"
									:range="multiArray">
									<view class='acea-row'>
										<view class="picker">{{region[0]}}，{{region[1]}}，{{region[2]}}</view>
									</view>
								</picker>
							</view>
						</view>
						<view class='item acea-row row-between-wrapper'>
							<input type='text' :placeholder='$t(`请填写具体地址`)' name='detail' placeholder-class='placeholder'
								:value='userAddress.detail'></input>
						</view>
					</view>
					<button class='keepBnt' form-type="submit">{{$t(`提交`)}}</button>
					<!-- #ifdef MP -->
					<!-- <view class="wechatAddress" v-if="!id" @click="getWxAddress">导入微信地址</view> -->
					<!-- #endif -->
					<!-- #ifdef H5 -->
					<!-- <view class="wechatAddress" v-if="this.$wechat.isWeixin() && !id" @click="getAddress">导入微信地址</view> -->
					<!-- #endif -->
				</view>
			</form>
		</view>
	</view>
</template>

<script setup>
	import { ref, watch, getCurrentInstance } from 'vue';
	import { storeToRefs } from 'pinia';
	import {
		editAddress,
		getAddressDetail
	} from '@/api/user.js';
	import {
		getCity
	} from '@/api/api.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import { useAppStore } from '@/store/modules/app';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';

	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	// #ifdef H5
	import Auth from '@/libs/wechat';
	// #endif
	import home from '@/components/home';

	const props = defineProps({
		aleartStatus: {
			type: Boolean,
			default: false
		}
	});

	const emit = defineEmits(['getAddress', 'close']);

	const appStore = useAppStore();
	const { isLogin } = storeToRefs(appStore);

	// 动态 $refs（toggleTab 使用，原样保留）
	const instance = getCurrentInstance();

	const regionDval = ['浙江省', '杭州市', '滨江区'];
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
	const defaultRegion = ['广东省', '广州市', '番禺区'];
	const defaultRegionCode = '110101';
	// 原代码未在 data 声明、直接挂到实例上的字段（getWxAddress 中判断使用，恒为 undefined）
	let cartId, pinkId, couponId;

	watch(isLogin, (newV, oldV) => {
		if (newV) {
			getUserAddress();
		}
	}, { deep: true });

	// created
	if (isLogin.value) {
		getCityList();
		getUserAddress();
	} else {
		toLogin();
	}

	// #ifdef APP-PLUS
	// 获取选择的地区
	function handleGetRegion(regionVal) {
		region.value = regionVal
	}
	// #endif
	// 获取地址数据
	function getCityList() {
		getCity().then(res => {
			district.value = res.data
			initialize();
		})
	}
	// 处理地址数据
	function initialize() {
		let province = [],
			city = [],
			area = [];
		if (district.value.length) {
			let cityChildren = district.value[0].c || [];
			let areaChildren = cityChildren.length ? (cityChildren[0].c || []) : [];
			district.value.forEach(function(item) {
				province.push(item.n);
			});
			cityChildren.forEach(function(item) {
				city.push(item.n);
			});
			areaChildren.forEach(function(item) {
				area.push(item.n);
			});
			multiArray.value = [province, city, area]
		}
	}
	function bindRegionChange(e) {
		let multiIndex = multiIndex.value,
			province = district.value[multiIndex[0]] || {
				c: []
			},
			city = province.c[multiIndex[1]] || {
				v: 0
			},
			multiArray = multiArray.value,
			value = e.detail.value;

		region.value = [multiArray[0][value[0]], multiArray[1][value[1]], multiArray[2][value[2]]]
		// this.region[0] = multiArray[0][value[0]];
		// this.region[1] = multiArray[1][value[1]];
		// this.region[2] = multiArray[2][value[2]];
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
			multiArray = multiArray.value,
			multiIndex = multiIndex.value;
		multiIndex[column] = value;
		switch (column) {
			case 0:
				let areaList = currentCity.c[0] || {
					c: []
				};
				multiArray[1] = currentCity.c.map((item) => {
					return item.n;
				});
				multiArray[2] = areaList.c.map((item) => {
					return item.n;
				});
				break;
			case 1:
				let cityList = district.value[multiIndex[0]].c[multiIndex[1]].c || [];
				multiArray[2] = cityList.map((item) => {
					return item.n;
				});
				break;
			case 2:

				break;
		}
		// #ifdef MP || APP-PLUS
		multiArray.value[0] = multiArray[0];
		multiArray.value[1] = multiArray[1];
		multiArray.value[2] = multiArray[2];
		// #endif
		// #ifdef H5
		multiArray.value = multiArray;
		// #endif


		multiIndex.value = multiIndex
		// this.setData({ multiArray: multiArray, multiIndex: multiIndex});
	}
	// 授权回调
	function onLoadFun() {
		getUserAddress();
	}
	// 授权关闭
	function authColse(e) {
		isShowAuth.value = e
	}
	function toggleTab(str) {
		instance.proxy.$refs[str].show();
	}
	// bindRegionChange: function(e) {
	// 	this['region'] = e.detail.value;
	// },
	function onConfirm(val) {
		region.value = val.checkArr[0] + '-' + val.checkArr[1] + '-' + val.checkArr[2];
	}
	function getUserAddress() {
		if (!id.value) return false;
		getAddressDetail(id.value).then(res => {
			let region = [res.data.province, res.data.city, res.data.district];
			userAddress.value = res.data;
			region.value = region;
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
						editAddress({
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
									if (cartId) {
										cartId = '';
										pinkId = '';
										couponId = '';
										uni.navigateTo({
											url: '/pages/goods/lottery/grids/index?addressId=' +
												id.value ? id
												.value : res.data
												.id
										});
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
								title: i18n.t(`取消`)
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
								title: i18n.t(`已取消`)
							});
						}
					}
				})
			},
		})
	}
	// 导入共享地址（微信）；
	// #ifdef H5
	function getAddress() {
		Auth.openAddress().then(userInfo => {
			editAddress({
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
				.then(() => {
					setTimeout(() => {
						uni.navigateTo({
							url: '/pages/goods/lottery/grids/index?addressId=' +
								id.value ? id.value : res.data.id
						})
					}, 1000);
					// close();
					util.Tips({
						title: i18n.t(`添加成功`),
						icon: 'success'
					});
				})
				.catch(err => {
					return util.Tips({
						title: err || i18n.t(`添加失败`)
					});
				});
		}).catch(err => {});
	}
	// #endif
	/**
	 * 提交用户添加地址
	 *
	 */
	function formSubmit(e) {
		let value = e.detail.value;
		if (!value.name) return util.Tips({
			title: i18n.t(`请填写收货人姓名`)
		});
		if (!value.phone) return util.Tips({
			title: i18n.t(`请输入手机号`)
		});
		if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(value.phone)) return util.Tips({
			title: i18n.t(`请输入正确的手机号码`)
		});
		if (region.value[0] == '省') return util.Tips({
			title: i18n.t(`请选择所在地区`)
		});
		if (!value.detail) return util.Tips({
			title: i18n.t(`请填写详细地址`)
		});
		let regionArray = region.value;
		value.address = {
			province: regionArray[0],
			city: regionArray[1],
			district: regionArray[2],
			city_id: cityId.value,
		};
		emit('getAddress', value)
	}
	//隐藏弹窗
	function posterImageClose() {
		emit("close", false)
	}
</script>

<style lang="scss" scoped>
	.aleart {
		width: 500rpx;
		// height: 714rpx;
		position: fixed;
		left: 50%;
		transform: translateX(-50%);
		z-index: 999;
		top: 50%;
		margin-top: -357rpx;
		border-radius: 12rpx;

		.title {
			padding: 0;
			margin: 0;
			height: 110rpx;

			.title-img {
				width: 100%;
				height: 100%;
			}
		}

		.aleart-body {
			display: flex;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			background-color: #FDF7E7;

			.form-data {
				width: 100%;
			}

			.goods-img {
				width: 150rpx;
				height: 150rpx;
			}

			.msg {
				font-size: 30rpx;
				color: #282828;
			}
		}

		.close {
			width: 46rpx;
			height: 75rpx;
			position: fixed;
			right: 20rpx;
			top: -73rpx;
			display: block;
		}
	}

	.addAddress {
		width: 100%;
		padding: 30rpx 30rpx;
	}

	.addAddress .list {
		background-color: #FDF7E7;
	}

	.addAddress .list .item {
		border: 1rpx solid #A05644;
		margin-bottom: 24rpx;
		padding: 10rpx;
		border-radius: 6rpx;
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
		// width: 475rpx;
		font-size: 30rpx;

	}

	.addAddress .list .item .placeholder {
		color: #ccc;
	}

	.addAddress .list .item picker {
		// width: 475rpx;
	}

	.addAddress .list .item picker .picker {
		// width: 410rpx;
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
		width: 100%;
		height: 86rpx;
		border-radius: 6rpx;
		text-align: center;
		line-height: 86rpx;
		font-size: 32rpx;
		color: #452015;
		font-weight: bold;
		background: #EEBE6B;
		box-shadow: 0px 5px 9px 0px rgba(220, 166, 72, 0.24);
	}

	.addAddress .wechatAddress {
		height: 86rpx;
		border-radius: 6rpx;
		text-align: center;
		line-height: 86rpx;
		margin: 0 auto;
		font-size: 32rpx;
		color: #fe960f;
		border: 1px solid #fe960f;
	}
</style>
