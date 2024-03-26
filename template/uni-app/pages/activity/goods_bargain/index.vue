<template>
	<view :style="colorStyle" style="background-color: var(--view-theme); min-height: 100vh; padding-bottom: 1rpx">
		<view class="bargain-list">
			<!-- #ifndef APP-PLUS -->
			<!-- 	<view class='iconfont icon-xiangzuo' @tap='goBack' :style="'top:'+ (navH/2  - 12) +'rpx'" v-if="returnShow">
			</view> -->
			<!-- #endif -->
			<view class="header" :style="'background-image: url(' + hostUrl + picUrl.bgList + ');'"></view>
			<view class="list">
				<block v-for="(item, index) in bargainList" :key="index">
					<view class="item acea-row row-between-wrapper" @tap="openSubscribe(item)">
						<view class="pictrue">
							<image :src="item.image"></image>
						</view>
						<view class="text acea-row row-column-around">
							<view class="name line1">{{ item.title || '' }}</view>
							<view class="num">
								<text class="iconfont icon-pintuan"></text>
								{{ item.people }}{{ $t(`人正在参与`) }}
							</view>
							<view class="money">
								{{ $t(`最低`) }} : {{ $t(`￥`) }}
								<text class="price">{{ item.min_price }}</text>
							</view>
						</view>
						<view class="cutBnt">
							<text class="iconfont icon-kanjia"></text>
							{{ $t(`参与砍价`) }}
						</view>
					</view>
				</block>
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
<script>
let app = getApp();
import { getBargainList } from '@/api/activity.js';
import { openBargainSubscribe } from '@/utils/SubscribeMessage.js';
import { getUserInfo } from '@/api/user.js';
import home from '@/components/home';
import { toLogin } from '@/libs/login.js';
import { colorChange } from '@/api/api.js';
import { mapGetters } from 'vuex';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
import colors from '@/mixins/color';
import { HTTP_REQUEST_URL } from '@/config/app.js';
export default {
	mixins: [colors],
	components: {
		home,
		// #ifdef MP
		authorize
		// #endif
	},
	data() {
		return {
			hostUrl: HTTP_REQUEST_URL,
			bargainList: [],
			page: 1,
			limit: 20,
			loading: false,
			loadend: false,
			userInfo: {},
			navH: '',
			isAuto: false, //没有授权的不会自动授权
			isShowAuth: false, //是否隐藏授权
			returnShow: true,
			picUrl: {},
			picList: [
				{
					bgList: '/statics/system_images/bargain_bg_0.jpeg'
				},
				{
					bgList: '/statics/system_images/bargain_bg_1.jpeg'
				},
				{
					bgList: '/statics/system_images/bargain_bg_2.jpeg'
				},
				{
					bgList: '/statics/system_images/bargain_bg_3.jpeg'
				},
				{
					bgList: '/statics/system_images/bargain_bg_4.jpeg'
				}
			]
		};
	},
	computed: mapGetters(['isLogin']),
	watch: {
		isLogin: {
			handler: function (newV, oldV) {
				if (newV) {
					this.getUserInfo();
					this.getBargainList();
				}
			},
			deep: true
		},
		colorStatus(newValue, oldValue) {
			if (newValue) {
				this.colorShow(newValue);
			}
		}
	},
	onLoad(options) {
		if (!this.colorStatus) {
			colorChange('color_change').then((res) => {
				this.colorShow(res.data.status);
			});
		}
		var pages = getCurrentPages();
		this.returnShow = pages.length === 1 ? false : true;
		uni.setNavigationBarTitle({
			title: this.$t(`砍价列表`)
		});
		// #ifdef MP
		this.navH = app.globalData.navH * 2.5;
		// #endif
		// #ifdef H5
		this.navH = app.globalData.navHeight;
		// #endif
		this.getBargainList();
		if (this.isLogin) {
			this.getUserInfo();
		}
	},
	methods: {
		colorShow(colorStatus) {
			switch (colorStatus) {
				case 1:
					this.picUrl = this.picList[0];
					break;
				case 2:
					this.picUrl = this.picList[1];
					break;
				case 3:
					this.picUrl = this.picList[2];
					break;
				case 4:
					this.picUrl = this.picList[3];
					break;
				case 5:
					this.picUrl = this.picList[4];
					break;
				default:
					this.picUrl = this.picList[2];
					break;
			}
		},
		// 授权关闭
		authColse: function (e) {
			this.isShowAuth = e;
		},
		/*
		 * 获取用户信息
		 */
		getUserInfo: function () {
			let that = this;
			getUserInfo().then((res) => {
				that.$set(that, 'userInfo', res.data);
			});
		},
		goBack: function () {
			uni.navigateBack({
				delta: 1
			});
		},
		onLoadFun: function (e) {
			this.getBargainList();
			this.userInfo = e;
		},
		openSubscribe: function (item) {
			if (!this.isLogin) {
				toLogin();
			}
			let page = '/pages/activity/goods_bargain_details/index?id=' + item.id + '&bargain=' + this.userInfo.uid;
			// #ifndef MP
			uni.navigateTo({
				url: page
			});
			// #endif
			// #ifdef MP
			uni.showLoading({
				title: this.$t(`正在加载中`)
			});
			openBargainSubscribe()
				.then((res) => {
					uni.hideLoading();
					uni.navigateTo({
						url: page
					});
				})
				.catch((err) => {
					uni.hideLoading();
				});
			// #endif
		},
		getBargainList: function () {
			let that = this;
			if (that.loadend) return;
			if (that.loading) return;
			that.loading = true;
			getBargainList({
				page: that.page,
				limit: that.limit
			})
				.then(function (res) {
					that.$set(that, 'bargainList', that.bargainList.concat(res.data));
					that.$set(that, 'page', that.page + 1);
					that.$set(that, 'loadend', that.limit > res.data.length);
					that.$set(that, 'loading', false);
				})
				.catch((res) => {
					that.loading = false;
				});
		}
	},
	onReachBottom: function () {
		this.getBargainList();
	}
};
</script>

<style lang="scss">
// page {
// 	background-color: #e93323 !important;
// }

.bargain-list .icon-xiangzuo {
	font-size: 40rpx;
	color: #fff;
	position: fixed;
	left: 30rpx;
	z-index: 99;
	transform: translateY(-20%);
}

.bargain-list .header {
	background-repeat: no-repeat;
	background-size: 100% 100%;
	width: 750rpx;
	height: 713rpx;
}

.bargain-list .list {
	background-color: #fff;
	border: 6rpx solid #fc8b42;
	border-radius: 30rpx;
	margin: -140rpx 30rpx 66rpx 30rpx;
	padding: 0 24rpx;
}

.bargain-list .list .item {
	border-bottom: 1rpx solid #eee;
	position: relative;
	height: 223rpx;
}

.bargain-list .list .item .pictrue {
	width: 160rpx;
	height: 160rpx;
}

.bargain-list .list .item .pictrue image {
	width: 100%;
	height: 100%;
	border-radius: 6rpx;
}

.bargain-list .list .item .text {
	width: 450rpx;
	font-size: 30rpx;
	color: #282828;
	height: 160rpx;
}

.bargain-list .list .item .text .name {
	width: 100%;
}

.bargain-list .list .item .text .num {
	font-size: 26rpx;
	color: #999;
}

.bargain-list .list .item .text .num .iconfont {
	font-size: 35rpx;
	margin-right: 7rpx;
}

.bargain-list .list .item .text .money {
	font-size: 24rpx;
	font-weight: bold;
	color: var(--view-theme);
}

.bargain-list .list .item .text .money .price {
	font-size: 32rpx;
}

.bargain-list .list .item .cutBnt {
	position: absolute;
	width: 180rpx;
	height: 50rpx;
	border-radius: 50rpx;
	font-size: 24rpx;
	color: #fff;
	text-align: center;
	line-height: 46rpx;
	right: 24rpx;
	bottom: 28rpx;
	box-shadow: 0 7rpx 0 var(--view-minorColor);
	background-color: var(--view-theme);
}

.bargain-list .list .item .cutBnt .iconfont {
	margin-right: 8rpx;
	font-size: 30rpx;
}

.bargain-list .list .load {
	font-size: 24rpx;
	height: 85rpx;
	text-align: center;
	line-height: 85rpx;
}
</style>
