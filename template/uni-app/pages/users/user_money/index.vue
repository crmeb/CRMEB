<template>
	<view :style="colorStyle">
		<view class='my-account'>
			<view class='wrapper'>
				<view class='header'>
					<view class='headerCon'>
						<view class='account acea-row row-top row-between'>
							<view class='assets'>
								<view>{{$t(`总资产(元)`)}}</view>
								<view class='money'>{{userInfo.now_money || 0}}</view>
							</view>
							<!-- #ifdef APP-PLUS || H5 -->
							<navigator url="/pages/users/user_payment/index" hover-class="none" class='recharge'>
								{{$t(`充值`)}}
							</navigator>
							<!-- #endif -->
							<!-- #ifdef MP -->
							<view v-if="recharge_switch" @click="openSubscribe('/pages/users/user_payment/index')"
								class='recharge'>{{$t(`充值`)}}</view>
							<!-- #endif -->
						</view>
						<view class='cumulative acea-row row-top'>
							<!-- #ifdef APP-PLUS || H5 -->
							<view class='item'>
								<view>{{$t(`累计充值(元)`)}}</view>
								<view class='money'>{{userInfo.recharge || 0}}</view>
							</view>
							<!-- #endif -->
							<!-- #ifdef MP -->
							<view class='item' v-if="recharge_switch">
								<view>{{$t(`累计充值(元)`)}}</view>
								<view class='money'>{{userInfo.recharge || 0}}</view>
							</view>
							<!-- #endif -->
							<view class='item'>
								<view>{{$t(`累计消费(元)`)}}</view>
								<view class='money'>{{userInfo.orderStatusSum || 0}}</view>
							</view>
						</view>
					</view>
				</view>
				<view class='nav acea-row row-middle'>
					<navigator class='item' hover-class='none' url='/pages/users/user_bill/index'>
						<view class='iconfont icon-s-zhangdanjilu'></view>
						<view>{{$t(`账单记录`)}}</view>
					</navigator>
					<navigator class='item' hover-class='none' url='/pages/users/user_bill/index?type=1'>
						<view class='iconfont icon-s-xiaofeijilu'></view>
						<view>{{$t(`消费记录`)}}</view>
					</navigator>
					<navigator class='item' hover-class='none' url='/pages/users/user_bill/index?type=2'
						v-if="recharge_switch">
						<view class='iconfont icon-s-chongzhijilu'></view>
						<view>{{$t(`充值记录`)}}</view>
					</navigator>
					<navigator class='item' hover-class='none' url='/pages/users/user_integral/index'>
						<view class='iconfont icon-jifenzhongxin'></view>
						<view>{{$t(`积分中心`)}}</view>
					</navigator>
				</view>
				<view class='advert acea-row row-between-wrapper'>
					<navigator class='item acea-row row-between-wrapper' hover-class='none'
						url='/pages/users/user_sgin/index'>
						<view class='text'>
							<view class='name'>{{$t(`签到领积分`)}}</view>
							<view>{{$t(`赚积分抵现金`)}}</view>
						</view>
						<view class='pictrue'>
							<image src='../static/gift.png'></image>
						</view>
					</navigator>
					<navigator class='item on acea-row row-between-wrapper' hover-class='none'
						url='/pages/users/user_get_coupon/index'>
						<view class='text'>
							<view class='name'>{{$t(`领取优惠券`)}}</view>
							<view>{{$t(`满减享优惠`)}}</view>
						</view>
						<view class='pictrue'>
							<image src='../static/money.png'></image>
						</view>
					</navigator>
				</view>
				<view class='list'>
					<view class='item acea-row row-between-wrapper' v-if="$permission('combination')">
						<view class='picTxt acea-row row-between-wrapper'>
							<view class='iconfont icon-hebingxingzhuang'></view>
							<view class='text'>
								<view class='line1'>{{$t(`最新拼团活动`)}}</view>
								<view class='infor line1'>{{$t(`最新的优惠商品上架拼团`)}}</view>
							</view>
						</view>
						<navigator hover-class='none' url='/pages/activity/goods_combination/index' class='bnt'
							v-if="activity.is_pink">{{$t(`立即参与`)}}</navigator>
						<view class='bnt end' v-else>{{$t(`已结束`)}}</view>
					</view>
					<view class='item acea-row row-between-wrapper' v-if="$permission('seckill')">
						<view class='picTxt acea-row row-between-wrapper'>
							<view class='iconfont icon-miaosha yellow'></view>
							<view class='text'>
								<view class='line1'>{{$t(`当前限时秒杀`)}}</view>
								<view class='infor line1'>{{$t(`最新商品秒杀进行中`)}}</view>
							</view>
						</view>
						<navigator hover-class='none' url='/pages/activity/goods_seckill/index' class='bnt'
							v-if="activity.is_seckill">{{$t(`立即参与`)}}</navigator>
						<view class='bnt end' v-else>{{$t(`已结束`)}}</view>
					</view>
					<view class='item acea-row row-between-wrapper' v-if="$permission('bargain')">
						<view class='picTxt acea-row row-between-wrapper'>
							<view class='iconfont icon-kanjia1 green'></view>
							<view class='text'>
								<view class='line1'>{{$t(`砍价活动`)}}</view>
								<view class='infor line1'>{{$t(`呼朋唤友来砍价`)}}</view>
							</view>
						</view>
						<navigator hover-class='none' url='/pages/activity/goods_bargain/index' class='bnt'
							v-if="activity.is_bargin">{{$t(`立即参与`)}}</navigator>
						<view class='bnt end' v-else>{{$t(`已结束`)}}</view>
					</view>
				</view>
			</view>
			<recommend :hostProduct="hostProduct"></recommend>
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
	import {
		getProductHot
	} from '@/api/store.js';
	import {
		openRechargeSubscribe
	} from '@/utils/SubscribeMessage.js';
	import {
		getUserInfo,
		userActivity
	} from '@/api/user.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	import recommend from '@/components/recommend/index';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import home from '@/components/home';
	import colors from "@/mixins/color";
	export default {
		components: {
			recommend,
			// #ifdef MP
			authorize,
			// #endif
			home
		},
		mixins: [colors],
		data() {
			return {
				userInfo: {},
				hostProduct: [],
				isClose: false,
				recharge_switch: 0,
				activity: {},
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				hotScroll: false,
				hotPage: 1,
				hotLimit: 10
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.getUserInfo();
						this.get_host_product();
						this.get_activity();
					}
				},
				deep: true
			}
		},
		onLoad() {
			if (this.isLogin) {
				this.getUserInfo();
				this.get_host_product();
				this.get_activity();
			} else {
				toLogin();
			}
		},
		methods: {
			onLoadFun: function() {
				this.getUserInfo();
				this.get_host_product();
				this.get_activity();
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			// #ifdef MP
			openSubscribe: function(page) {
				uni.showLoading({
					title: this.$t(`正在加载`),
				})
				openRechargeSubscribe().then(res => {
					uni.hideLoading();
					uni.navigateTo({
						url: page,
					});
				}).catch(() => {
					uni.hideLoading();
				});
			},
			// #endif
			/**
			 * 获取用户详情
			 */
			getUserInfo: function() {
				let that = this;
				getUserInfo().then(res => {
					that.$set(that, 'userInfo', res.data);
					that.recharge_switch = res.data.recharge_switch;
				});
			},
			/**
			 * 获取活动可参与否
			 */
			get_activity: function() {
				let that = this;
				userActivity().then(res => {
					that.$set(that, "activity", res.data);
				})
			},
			/**
			 * 获取我的推荐
			 */
			get_host_product: function() {
				let that = this;
				if (that.hotScroll) return
				getProductHot(
					that.hotPage,
					that.hotLimit,
				).then(res => {
					that.hotPage++
					that.hotScroll = res.data.length < that.hotLimit
					that.hostProduct = that.hostProduct.concat(res.data)
				});
			}
		},
		onReachBottom() {
			this.get_host_product();
		},
		// 滚动监听
		onPageScroll(e) {
			// 传入scrollTop值并触发所有easy-loadimage组件下的滚动监听事件
			uni.$emit('scroll');
		},
	}
</script>

<style scoped lang="scss">
	.my-account .wrapper {
		background-color: #fff;
		padding: 32rpx 0 34rpx 0;
		margin-bottom: 14rpx;
	}

	.my-account .wrapper .header {
		width: 690rpx;
		height: 330rpx;
		// background-image: linear-gradient(to right, #f33b2b 0%, #f36053 100%);
		// background-image: var(--view-theme);
		background: var(--view-theme);
		border-radius: 16rpx;
		margin: 0 auto;
		box-sizing: border-box;
		color: rgba(255, 255, 255, 0.6);
		font-size: 24rpx;
	}

	.my-account .wrapper .header .headerCon {
		background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAArIAAAFKCAYAAADhULxpAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQyIDc5LjE2MDkyNCwgMjAxNy8wNy8xMy0wMTowNjozOSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTggKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkEzMUM4RDlEM0YxNTExRTk4OUJFQ0Q4Qjg0RDBCMzQ1IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkEzMUM4RDlFM0YxNTExRTk4OUJFQ0Q4Qjg0RDBCMzQ1Ij4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6QTMxQzhEOUIzRjE1MTFFOTg5QkVDRDhCODREMEIzNDUiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6QTMxQzhEOUMzRjE1MTFFOTg5QkVDRDhCODREMEIzNDUiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6ymvxvAAAIhklEQVR42uzd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');
		background-repeat: no-repeat;
		background-size: 100%;
		height: 100%;
		width: 100%;
		padding: 36rpx 0 29rpx 0;
		box-sizing: border-box;
	}

	.my-account .wrapper .header .headerCon .account {
		padding: 0 35rpx;
	}

	.my-account .wrapper .header .headerCon .account .assets .money {
		font-size: 72rpx;
		color: #fff;
		margin-top: 6rpx;
		font-family: 'Guildford Pro';
	}

	.my-account .wrapper .header .headerCon .account .recharge {
		font-size: 28rpx;
		width: 150rpx;
		height: 54rpx;
		border-radius: 27rpx;
		background-color: #fff9f8;
		text-align: center;
		line-height: 54rpx;
		color: var(--view-theme);
	}

	.my-account .wrapper .header .headerCon .cumulative {
		margin-top: 46rpx;
	}

	.my-account .wrapper .header .headerCon .cumulative .item {
		flex: 1;
		padding-left: 35rpx;
	}

	.my-account .wrapper .header .headerCon .cumulative .item .money {
		font-size: 48rpx;
		font-family: 'Guildford Pro';
		color: #fff;
		margin-top: 6rpx;
	}

	.my-account .wrapper .nav {
		height: 155rpx;
		border-bottom: 1rpx solid #f5f5f5;
	}

	.my-account .wrapper .nav .item {
		flex: 1;
		text-align: center;
		font-size: 26rpx;
		color: #999;
	}

	.my-account .wrapper .nav .item .iconfont {
		font-size: 44rpx;
		margin: 0 auto;
		margin-bottom: 14rpx;
		color: var(--view-theme);
	}

	.my-account .wrapper .advert {
		padding: 0 30rpx;
		margin-top: 30rpx;
	}

	.my-account .wrapper .advert .item {
		background-color: #fff6d1;
		width: 332rpx;
		height: 118rpx;
		border-radius: 10rpx;
		padding: 0 27rpx 0 25rpx;
		box-sizing: border-box;
		font-size: 24rpx;
		color: #e44609;
		box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1),
	}

	.my-account .wrapper .advert .item.on {
		background-color: #fff3f3;
		color: #e96868;
	}

	.my-account .wrapper .advert .item .pictrue {
		width: 78rpx;
		height: 78rpx;
	}

	.my-account .wrapper .advert .item .pictrue image {
		width: 100%;
		height: 100%;
	}

	.my-account .wrapper .advert .item .text .name {
		font-size: 30rpx;
		font-weight: bold;
		color: #f33c2b;
		margin-bottom: 7rpx;
	}

	.my-account .wrapper .advert .item.on .text .name {
		color: #f64051;
	}

	.my-account .wrapper .list {
		padding: 0 30rpx;
	}

	.my-account .wrapper .list .item {
		margin-top: 44rpx;
	}

	.my-account .wrapper .list .item .picTxt .iconfont {
		width: 82rpx;
		height: 82rpx;
		border-radius: 50%;
		background-image: linear-gradient(to right, #ff9389 0%, #f9776b 100%);
		text-align: center;
		line-height: 82rpx;
		color: #fff;
		font-size: 40rpx;
	}

	.my-account .wrapper .list .item .picTxt .iconfont.yellow {
		background-image: linear-gradient(to right, #ffccaa 0%, #fea060 100%);
	}

	.my-account .wrapper .list .item .picTxt .iconfont.green {
		background-image: linear-gradient(to right, #a1d67c 0%, #9dd074 100%);
	}

	.my-account .wrapper .list .item .picTxt {
		width: 428rpx;
		font-size: 30rpx;
		color: #282828;
	}

	.my-account .wrapper .list .item .picTxt .text {
		width: 317rpx;
	}

	.my-account .wrapper .list .item .picTxt .text .infor {
		font-size: 24rpx;
		color: #999;
		margin-top: 5rpx;
	}

	.my-account .wrapper .list .item .bnt {
		font-size: 26rpx;
		color: #282828;
		width: 156rpx;
		height: 52rpx;
		border: 1rpx solid #ddd;
		border-radius: 26rpx;
		text-align: center;
		line-height: 52rpx;
	}

	.my-account .wrapper .list .item .bnt.end {
		font-size: 26rpx;
		color: #aaa;
		background-color: #f2f2f2;
		border-color: #f2f2f2;
	}
</style>
