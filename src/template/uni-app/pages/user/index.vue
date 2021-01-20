<template>
	<view class="new-users copy-data">
		<view class="bg"></view>
		<view class="head">
			<view class="user-card">
				<view class="bg"></view>
				<view class="user-info">
					<view :class="{headwear: userInfo.is_money_level}">
						<!-- 注释这个是加的bnt -->
						<!-- #ifdef H5 -->
						<button class="bntImg" v-if="userInfo.is_complete == 0 && isWeixin" @click="getWechatuserinfo">
							<!-- <button class="bntImg" @click="getWechatuserinfo"> -->
							<image class="avatar" src='/static/images/f.png'></image>
							<view class="avatarName">获取头像</view>
						</button>
						<!-- #endif -->
						<!-- #ifdef MP -->
						<button class="bntImg" v-if="userInfo.is_complete == 0" open-type='getUserInfo' @getuserinfo="getRoutineUserInfo">
							<image class="avatar" src='/static/images/f.png'></image>
							<view class="avatarName">获取头像</view>
						</button>
						<!-- #endif -->
						<view v-else>
							<image class="avatar" :src='userInfo.avatar' v-if="userInfo.avatar" @click="goEdit()"></image>
							<image v-else class="avatar" src="/static/images/f.png" mode="" @click="goEdit()"></image>
						</view>
					</view>

					<view class="info">
						<!-- #ifdef MP -->
						<view class="name" v-if="!userInfo.uid" @tap="openAuto" style="display: flex; align-items: center;height: 100%; font-size: 30rpx;">
							请点击授权
						</view>
						<!-- #endif -->
						<view class="name" v-if="userInfo.uid">
							{{userInfo.nickname}}
							<image v-if="userInfo.vip" class="vip" :src="userInfo.vip_icon"></image>
							<image v-if="userInfo.is_money_level && userInfo.is_open_member" class="svip" src="/static/images/svip.png"></image>
							<!-- <view class="vip" v-if="userInfo.vip">
								<image :src="userInfo.vip_icon" alt="">
									<view style="margin-left: 10rpx;" class="vip-txt">{{userInfo.vip_name}}</view>
							</view> -->
						</view>
						<view class="num" v-if="userInfo.phone" @click="goEdit()">
							<view class="num-txt">ID：{{userInfo.uid}}</view>
							<view class="icon">
								<image src="/static/images/edit.png" mode=""></image>
							</view>
						</view>
						<view class="phone" v-if="!userInfo.phone && isLogin" @tap="bindPhone">绑定手机号</view>
					</view>
				</view>
				<!-- <view class="sign" @click="goSignIn">签到</view> -->
			</view>
			<view class="iconfont icon-shezhi" @click="goEdit()"></view>
		</view>
		<view class="main">
			<view class="num-wrapper">
				<!-- <view class="num-item" v-if="userInfo.balance_func_status" @click="goMenuPage('/pages/users/user_money/index')">
					<view class="txt">余额</view>
					<text class="num">{{userInfo.now_money || 0}}</text>
				</view> -->
				<view class="num-item" @click="goMenuPage('/pages/users/user_goods_collection/index')">
					<view class="txt">收藏</view>
					<text class="num">{{userInfo.collectCount || 0}}</text>
				</view>
				<view class="num-item" @click="goMenuPage('/pages/users/user_integral/index')">
					<view class="txt">积分</view>
					<text class="num">{{userInfo.integral || 0}}</text>
				</view>
				<view class="num-item" @click="goMenuPage('/pages/users/user_coupon/index')">
					<view class="txt">优惠券</view>
					<text class="num">{{userInfo.couponCount || 0}}</text>
				</view>
			</view>
			<view class="order-wrapper">
				<view class="order-hd flex">
					<view class="left">订单中心</view>
					<navigator class="right flex" hover-class="none" url="/pages/users/order_list/index" open-type="navigate">
						查看全部
						<text class="iconfont icon-xiangyou"></text>
					</navigator>
				</view>
				<view class="order-bd">
					<block v-for="(item,index) in orderMenu" :key="index">
						<navigator class="order-item" hover-class="none" :url="item.url">
							<view class="pic">
								<image :src="item.img" mode=""></image>
								<text class="order-status-num" v-if="item.num > 0">{{ item.num }}</text>
							</view>
							<view class="txt">{{item.title}}</view>
						</navigator>
					</block>
				</view>
			</view>
			<!-- 会员菜单 -->
			<div style="margin-top: 20rpx; border-radius: 6rpx; overflow: hidden;">
				<view class="title-box">我的服务</view>
				<view class="user-menus">
					<template v-for="(item, index) in MyMenus">
						<view v-if="item.url !== '#' && item.url !== '/pages/service/index'" class="item" :key="index" @click="goMenuPage(item.url, item.name)">
							<image :src="item.pic"></image>
							<text>{{ item.name }}</text>
						</view>
					</template>
				</view>
			</div>
		</view>
		<img src="/static/images/support.png" alt="" class='support'>
		<view style="height: 50rpx;"></view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<view class="uni-p-b-98"></view>
		<z_tabBar :pagePath="'/pages/user/index'"></z_tabBar>
	</view>
</template>
<script>
	import z_tabBar from "@/pages/index/components/z_tabBar.vue";
	import {
		getMenuList,
		getUserInfo,
		setVisit,
		updateUserInfo
	} from '@/api/user.js';
	import {
		wechatAuthV2
	} from '@/api/public.js'
	import {
		toLogin
	} from '@/libs/login.js';
	import dayjs from '@/plugin/dayjs/dayjs.min.js';
	import {
		mapGetters
	} from "vuex";
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	// #ifdef H5
	import Auth from '@/libs/wechat';
	// #endif
	const app = getApp();
	export default {
		components: {
			z_tabBar,
			// #ifdef MP
			authorize
			// #endif
		},
		computed: {
			...mapGetters(['isLogin'])
		},
		data() {
			return {
				orderMenu: [{
						img: '/static/images/order1.png',
						title: '待付款',
						url: '/pages/users/order_list/index?status=0'
					},
					{
						img: '/static/images/order2.png',
						title: '待发货',
						url: '/pages/users/order_list/index?status=1'
					},
					{
						img: '/static/images/order3.png',
						title: '待收货',
						url: '/pages/users/order_list/index?status=2'
					},
					{
						img: '/static/images/order4.png',
						title: '待评价',
						url: '/pages/users/order_list/index?status=3'
					},
					{
						img: '/static/images/order5.png',
						title: '售后/退款',
						url: '/pages/users/user_return_list/index'
					},
				],
				imgUrls: [],
				autoplay: true,
				circular: true,
				interval: 3000,
				duration: 500,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				orderStatusNum: {},
				userInfo: {},
				MyMenus: [],
				// #ifdef H5
				isWeixin: Auth.isWeixin()
				//#endif
			}
		},
		filters: {
			dateFormat: function(value) {
				return dayjs(value * 1000).format('YYYY-MM-DD HH:mm:ss');
			}
		},
		watch: {
			MyMenus(newValue, oldValue) {
				for (let key in newValue) {
					if (newValue[key].url === '/pages/users/user_spread_user/index') {
						uni.setStorageSync('user_spread_user', newValue[key].url);
						break;
					}
				}
			}
		},
		computed: mapGetters(['isLogin']),
		onLoad(options) {
			let that = this;
			that.$set(that, 'MyMenus', app.globalData.MyMenus);
			if (that.isLogin == false) {
				// #ifdef H5 || APP-PLUS
				toLogin()
				// #endif
			}
			// #ifdef H5
			if (options.code) {
				let spread = app.globalData.spid ? app.globalData.spid : '';
				wechatAuthV2(options.code, spread).then(res => {
					location.href = decodeURIComponent(
						decodeURIComponent(options.back_url)
					)
				})
			}
			// #endif
		},
		onShow: function() {
			let that = this;
			if (that.isLogin) {
				this.getUserInfo();
				this.setVisit();
			};
			this.getMyMenus();
			if (!app.globalData.isIframe) {
				uni.showTabBar();
			}
		},
		methods: {
			getWechatuserinfo() {
				//#ifdef H5
				Auth.isWeixin() && Auth.oAuth('snsapi_userinfo');
				//#endif
			},
			getRoutineUserInfo(e) {
				updateUserInfo({
					userInfo: e.detail.userInfo
				}).then(res => {
					this.getUserInfo();
					return this.$util.Tips('更新用户信息成功');
				}).catch(res => {

				})
			},
			// 记录会员访问
			setVisit() {
				setVisit({
					url: '/pages/user/index'
				}).then(res => {})
			},
			// 打开授权
			openAuto() {
				toLogin();
			},
			// 授权回调
			onLoadFun() {
				this.getUserInfo();
				this.getMyMenus();
				this.setVisit();
			},
			Setting: function() {
				uni.openSetting({
					success: function(res) {}
				});
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			// 绑定手机
			bindPhone() {
				uni.navigateTo({
					url: '/pages/users/user_phone/index'
				})
			},
			/**
			 * 获取个人用户信息
			 */
			getUserInfo: function() {
				let that = this;
				getUserInfo().then(res => {
					that.userInfo = res.data
					that.$store.commit("SETUID", res.data.uid);
					that.orderMenu.forEach((item, index) => {
						switch (item.title) {
							case '待付款':
								item.num = res.data.orderStatusNum.unpaid_count
								break
							case '待发货':
								item.num = res.data.orderStatusNum.unshipped_count
								break
							case '待收货':
								item.num = res.data.orderStatusNum.received_count
								break
							case '待评价':
								item.num = res.data.orderStatusNum.evaluated_count
								break
							case '售后/退款':
								item.num = res.data.orderStatusNum.refund_count
								break
						}
					})
				});
			},
			/**
			 * 
			 * 获取个人中心图标
			 */
			getMyMenus: function() {
				let that = this;
				if (this.MyMenus.length) return;
				getMenuList().then(res => {
					that.$set(that, 'MyMenus', res.data.routine_my_menus);
					this.imgUrls = res.data.routine_my_banner
				});
			},
			// 编辑页面
			goEdit() {
				uni.navigateTo({
					url: '/pages/users/user_info/index'
				})
			},
			// 签到
			goSignIn() {
				uni.navigateTo({
					url: '/pages/users/user_sgin/index'
				})
			},
			// goMenuPage
			goMenuPage(url, name) {
				if (this.isLogin) {
					if (url.indexOf('http') === -1) {
						// #ifdef H5
						if (name && name === '客服接待') {
							return uni.navigateTo({
								url: `/pages/annex/web_view/index?url=${location.origin}${url}`
							});
						}
						// #endif
						uni.navigateTo({
							url: url
						})
					} else {
						uni.navigateTo({
							url: `/pages/annex/web_view/index?url=${url}`
						});
					}
				} else {
					// #ifdef H5
					toLogin();
					// #endif
					// #ifdef MP
					this.openAuto()
					// #endif
				}
			}
		}
	}
</script>
<style lang="scss">
	.bg {
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		z-index: -1;
		background: #f5f5f5;
	}

	.new-users {
		.head {
			.user-card {
				position: relative;
				width: 100%;
				height: 190rpx;
				margin: 0 auto;
				padding: 35rpx 28rpx;
				background: #e93323;

				&::after {
					position: absolute;
					left: 0;
					right: 0;
					bottom: -98rpx;
					content: '';
					height: 100rpx;
					width: 100%;
					border-radius: 0 0 50% 50%;
					background-color: #e93323;
				}

				.user-info {
					z-index: 20;
					position: relative;
					display: flex;

					.headwear {
						display: flex;
						align-items: flex-end;
						width: 128rpx;
						height: 138rpx;
						padding: 0 4rpx 4rpx;
						background: url("data:image/png;base64,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") center/cover no-repeat;
					}

					.bntImg {
						width: 120rpx;
						height: 120rpx;
						border-radius: 50%;
						text-align: center;
						line-height: 120rpx;
						background-color: unset;
						position: relative;

						.avatarName {
							font-size: 16rpx;
							color: #fff;
							text-align: center;
							background-color: rgba(0, 0, 0, 0.6);
							height: 37rpx;
							line-height: 37rpx;
							position: absolute;
							bottom: 0;
							left: 0;
							width: 100%;
						}
					}

					.avatar {
						display: block;
						width: 120rpx;
						height: 120rpx;
						border-radius: 50%;
						margin: 0 auto;
					}

					.info {
						flex: 1;
						display: flex;
						flex-direction: column;
						justify-content: space-between;
						margin-left: 20rpx;
						padding: 15rpx 0;

						.name {
							display: flex;
							align-items: center;
							color: #fff;
							font-size: 31rpx;

							.vip {
								width: 28rpx;
								height: 28rpx;
								margin-left: 14rpx;
							}

							.svip {
								width: 78rpx;
								height: 30rpx;
								margin-left: 12rpx;
							}
						}

						.num {
							display: flex;
							align-items: center;
							font-size: 26rpx;
							color: rgba(255, 255, 255, 0.6);

							image {
								width: 22rpx;
								height: 23rpx;
								margin-left: 20rpx;
							}
						}
					}
				}



				.sign {
					z-index: 200;
					position: absolute;
					right: -12rpx;
					top: 80rpx;
					display: flex;
					align-items: center;
					justify-content: center;
					width: 120rpx;
					height: 60rpx;
					background: linear-gradient(90deg, rgba(255, 225, 87, 1) 0%, rgba(238, 193, 15, 1) 100%);
					border-radius: 29rpx 4rpx 4rpx 29rpx;
					color: #282828;
					font-size: 28rpx;
					font-weight: bold;
				}
			}
		}

		.main {
			position: relative;
			padding: 0 20rpx;

			.num-wrapper {
				z-index: 30;
				position: relative;
				display: flex;
				align-items: center;
				justify-content: space-between;
				margin-top: 20rpx;
				height: 140rpx;
				border-radius: 6rpx;
				// padding: 0 47rpx;
				color: #333;
				background: #fff;

				.num-item {
					width: 33.33%;

					text-align: center;

					.num {
						font-size: 32rpx;
					}

					.txt {
						margin-top: 8rpx;
						font-size: 26rpx;
						color: #aaa;
					}
				}
			}

			.order-wrapper {
				background-color: #fff;
				border-radius: 6rpx;

				.order-hd {
					justify-content: space-between;
					padding: 20rpx 28rpx;
					margin-top: 20rpx;
					border-bottom: 1px solid #F5F5F5;
					font-size: 30rpx;
					color: #282828;

					.right {
						align-items: center;
						color: #666666;
						font-size: 26rpx;

						.icon-xiangyou {
							margin-left: 5rpx;
							margin-top: 6rpx;
							font-size: 26rpx;
						}
					}
				}

				.order-bd {
					display: flex;

					.order-item {
						display: flex;
						flex-direction: column;
						justify-content: center;
						align-items: center;
						width: 20%;
						height: 160rpx;

						.pic {
							position: relative;
							text-align: center;

							image {
								width: 49rpx;
								height: 42rpx;
							}
						}

						.txt {
							margin-top: 15rpx;
							font-size: 26rpx;
							color: #454545;
						}
					}
				}
			}
		}

		.slider-wrapper {
			margin: 20rpx 0;
			height: 130rpx;

			swiper,
			swiper-item {
				height: 100%;
			}

			image {
				width: 100%;
				height: 130rpx;
			}
		}

		.user-menus {
			background-color: #fff;
			display: flex;
			flex-wrap: wrap;
			padding: 20rpx 0;

			.item {
				position: relative;
				display: flex;
				align-items: center;
				justify-content: center;
				flex-direction: column;
				width: 25%;
				height: 120rpx;
				font-size: 24rpx;

				image {
					width: 46rpx;
					height: 46rpx;
					margin-bottom: 10rpx;
				}

			}

			button {
				font-size: 28rpx;
			}
		}

		.phone {
			color: #fff;
		}

		.order-status-num {

			min-width: 12rpx;
			background-color: #fff;
			color: #ee5a52;
			border-radius: 15px;
			position: absolute;
			right: -14rpx;
			top: -15rpx;
			font-size: 20rpx;
			padding: 0 8rpx;
			border: 1px solid #ee5a52;
		}

		.support {
			width: 219rpx;
			height: 74rpx;
			margin: 54rpx auto;
			display: block;
		}
	}

	.title-box {
		height: 80rpx;
		line-height: 80rpx;
		padding: 0 30rpx;
		border-bottom: 1px dashed #eee;
		background-color: #fff;
	}

	.icon-shezhi {
		z-index: 99;
		color: #fff;
		font-size: 40rpx;
		position: absolute;
		right: 22rpx;
		top: 60rpx;
	}

	.member-wrapper {
		display: flex;
		align-items: center;
		height: 110rpx;
		padding-right: 34rpx;
		padding-left: 96rpx;
		margin-top: 15rpx;
		background: url("data:image/png;base64,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") center/cover no-repeat;
		font-size: 26rpx;
		color: #AE5A2A;

		.text {
			flex: 1;

			.small {
				margin-top: 4rpx;
				font-size: 22rpx;
			}
		}

		.link {
			width: 154rpx;
			height: 50rpx;
			border-radius: 25rpx;
			background: #FFFFFF;
			font-size: 24rpx;
			line-height: 50rpx;
			text-align: center;
		}
	}
</style>
