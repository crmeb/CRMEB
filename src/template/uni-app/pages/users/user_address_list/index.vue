<template>
	<view>
		<view class='address-management' :class='addressList.length < 1 && page > 1 ? "fff":""'>
			<view class='line'>
				<image src='../../../static/images/line.jpg' v-if="addressList.length"></image>
			</view>
			<radio-group class="radio-group" @change="radioChange" v-if="addressList.length">
				<view class='item' v-for="(item,index) in addressList" :key="index">
					<view class='address' @click='goOrder(item.id)'>
						<view class='consignee'>收货人：{{item.real_name}}<text class='phone'>{{item.phone}}</text></view>
						<view>收货地址：{{item.province}}{{item.city}}{{item.district}}{{item.detail}}</view>
					</view>
					<view class='operation acea-row row-between-wrapper'>
						<!-- #ifndef MP -->
						<radio class="radio" :value="index.toString()" :checked="item.is_default ? true : false">
							<text>设为默认</text>
						</radio>
						<!-- #endif -->
						<!-- #ifdef MP -->
						<radio class="radio" :value="index" :checked="item.is_default ? true : false">
							<text>设为默认</text>
						</radio>
						<!-- #endif -->
						<view class='acea-row row-middle'>
							<view @click='editAddress(item.id)'><text class='iconfont icon-bianji'></text>编辑</view>
							<view @click='delAddress(index)'><text class='iconfont icon-shanchu'></text>删除</view>
						</view>
					</view>
				</view>
			</radio-group>
			<view class='loadingicon acea-row row-center-wrapper' v-if="addressList.length">
				<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
			</view>
			<view class='noCommodity' v-if="addressList.length < 1 && page > 1">
				<view class='pictrue'>
					<image src='../../../static/images/noAddress.png'></image>
				</view>
			</view>
			<view style='height:120rpx;'></view>
			<view class='footer acea-row row-between-wrapper'>
				<!-- #ifdef APP-PLUS -->
				<view class='addressBnt bg-color on' @click='addAddress'><text class='iconfont icon-tianjiadizhi'></text>添加新地址</view>
				<!-- #endif -->
				<!-- #ifdef MP-->
				<view class='addressBnt bg-color' @click='addAddress'><text class='iconfont icon-tianjiadizhi'></text>添加新地址</view>
				<view class='addressBnt wxbnt' @click='getWxAddress'><text class='iconfont icon-weixin2'></text>导入微信地址</view>
				<!-- #endif -->
				<!-- #ifdef H5-->
				<view class='addressBnt bg-color' :class="this.$wechat.isWeixin()?'':'on'" @click='addAddress'><text class='iconfont icon-tianjiadizhi'></text>添加新地址</view>
				<view class='addressBnt wxbnt' @click='getAddress' v-if="this.$wechat.isWeixin()"><text class='iconfont icon-weixin2'></text>导入微信地址</view>
				<!-- #endif -->
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<home></home>
	</view>
</template>

<script>
	import {
		getAddressList,
		setAddressDefault,
		delAddress,
		editAddress,
		postAddress
	} from '@/api/user.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import home from '@/components/home';
	export default {
		components: {
			// #ifdef MP
			authorize,
			// #endif
			home
		},
		data() {
			return {
				addressList: [],
				cartId: '',
				pinkId: 0,
				couponId: 0,
				loading: false,
				loadend: false,
				loadTitle: '加载更多',
				page: 1,
				limit: 20,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false ,//是否隐藏授权
				news:''
			};
		},
		computed: mapGetters(['isLogin']),
		onLoad(options) {
			if (this.isLogin) {
				this.cartId = options.cartId || '';
				this.pinkId = options.pinkId || 0;
				this.couponId = options.couponId || 0;
				this.news = options.news || 0;
				this.getAddressList(true);
			} else {
				toLogin();
			}
		},
		onShow: function() {
			let that = this;
			that.getAddressList(true);
		},
		methods: {
			onLoadFun: function() {
				this.getAddressList();
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			/*
			 * 导入微信地址（小程序）
			 */
			getWxAddress: function() {
				let that = this;
				uni.authorize({
					scope: 'scope.address',
					success: function(res) {
						uni.chooseAddress({
							success: function(res) {
								console.log(res,'res')
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
									type: 1
								}).then(res => {
									that.$util.Tips({
										title: "添加成功",
										icon: 'success'
									}, function() {
										that.getAddressList(true);
									});
								}).catch(err => {
									return that.$util.Tips({
										title: err
									});
								});
							},
							fail: function(res) {
								if (res.errMsg == 'chooseAddress:cancel') return that.$util.Tips({
									title: '取消选择'
								});
							},
						})
					},
					fail: function(res) {
						uni.showModal({
							title: '您已拒绝导入微信地址权限',
							content: '是否进入权限管理，调整授权？',
							success(res) {
								if (res.confirm) {
									uni.openSetting({
										success: function(res) {
											console.log(res.authSetting)
										}
									});
								} else if (res.cancel) {
									return that.$util.Tips({
										title: '已取消！'
									});
								}
							}
						})
					}
				})
			},
			/*
			 * 导入微信地址（公众号）
			 */
			getAddress() {
				let that = this;
				that.$wechat.openAddress().then(userInfo => {
					// open();
					editAddress({
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
							that.$util.Tips({
								title: "添加成功",
								icon: 'success'
							}, function() {
								// close();
								that.getAddressList(true);
							});
						})
						.catch(err => {
							// close();
							return that.$util.Tips({
								title: err || "添加失败"
							});
						});
				});
			},
			/**
			 * 获取地址列表
			 * 
			 */
			getAddressList: function(isPage) {
				let that = this;
				if (isPage) {
					that.loadend = false;
					that.page = 1;
					that.$set(that, 'addressList', []);
				};
				if (that.loading) return;
				if (that.loadend) return;
				that.loading = true;
				that.loadTitle = '';
				getAddressList({
					page: that.page,
					limit: that.limit
				}).then(res => {
					let list = res.data;
					let loadend = list.length < that.limit;
					that.addressList = that.$util.SplitArray(list, that.addressList);
					that.$set(that, 'addressList', that.addressList);
					that.loadend = loadend;
					that.loadTitle = loadend ? '我也是有底线的' : '加载更多';
					that.page = that.page + 1;
					that.loading = false;
				}).catch(err => {
					that.loading = false;
					that.loadTitle = '加载更多';
				});
			},
			/**
			 * 设置默认地址
			 */
			radioChange: function(e) {
				let index = parseInt(e.detail.value),
					that = this;
				let address = this.addressList[index];
				if (address == undefined) return that.$util.Tips({
					title: '您设置的默认地址不存在!'
				});
				setAddressDefault(address.id).then(res => {
					for (let i = 0, len = that.addressList.length; i < len; i++) {
						if (i == index) that.addressList[i].is_default = true;
						else that.addressList[i].is_default = false;
					}
					that.$util.Tips({
						title: '设置成功',
						icon: 'success'
					}, function() {
						that.$set(that, 'addressList', that.addressList);
					});
				}).catch(err => {
					return that.$util.Tips({
						title: err
					});
				});
			},
			/**
			 * 编辑地址
			 */
			editAddress: function(id) {
				let cartId = this.cartId,
					pinkId = this.pinkId,
					couponId = this.couponId;
				this.cartId = '';
				this.pinkId = '';
				this.couponId = '';
				uni.navigateTo({
					url: '/pages/users/user_address/index?id=' + id + '&cartId=' + cartId + '&pinkId=' + pinkId + '&couponId=' +
						couponId+'&new='+this.news
				})
			},
			/**
			 * 删除地址
			 */
			delAddress: function(index) {
				let that = this,
					address = this.addressList[index];
				if (address == undefined) return that.$util.Tips({
					title: '您删除的地址不存在!'
				});
				delAddress(address.id).then(res => {
					that.$util.Tips({
						title: '删除成功',
						icon: 'success'
					}, function() {
						that.addressList.splice(index, 1);
						that.$set(that, 'addressList', that.addressList);
					});
				}).catch(err => {
					return that.$util.Tips({
						title: err
					});
				});
			},
			/**
			 * 新增地址
			 */
			addAddress: function() {
				let cartId = this.cartId,
					pinkId = this.pinkId,
					couponId = this.couponId;
				this.cartId = '';
				this.pinkId = '';
				this.couponId = '';
				uni.navigateTo({
					url: '/pages/users/user_address/index?cartId=' + cartId + '&pinkId=' + pinkId + '&couponId=' + couponId+'&new='+this.news
				})
			},
			goOrder: function(id) {
				let cartId = '';
				let pinkId = '';
				let couponId = '';
				if (this.cartId && id) {
					cartId = this.cartId;
					pinkId = this.pinkId;
					couponId = this.couponId;
					this.cartId = '';
					this.pinkId = '';
					this.couponId = '';
					uni.redirectTo({
						url: '/pages/users/order_confirm/index?is_address=1&new='+ this.news +'&cartId=' + cartId + '&addressId=' + id + '&pinkId=' +
							pinkId + '&couponId=' + couponId
					})
				}
			}
		},
		onReachBottom: function() {
			this.getAddressList();
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
		background-color: #fe960f;
	}
</style>
