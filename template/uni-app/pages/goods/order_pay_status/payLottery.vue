<template>
	<view :style="colorStyle">
		<view class="header" v-show="lotteryShow">
			<view class="pay-status">
				<text class="iconfont icon-gou"></text>
				<view class="pay-status-r">
					<text class="pay-status-text">
						{{$t(`支付成功`)}}
					</text>
					<text>
						{{$t(`支付金额`)}}：{{$t(`￥`)}}{{totalPrice}}
					</text>
				</view>
			</view>
			<view class="jump">
				<view class="jump-det" @click="orderDetails">
					{{$t(`查看订单`)}}
				</view>
				<view class="jump-index" @click="goIndex">
					{{$t(`返回首页`)}}
				</view>
			</view>
		</view>
		<view class="grids-top" v-show="lotteryShow">
			<image src="../static/pay-lottery-l.png" mode=""></image>
			<view class="grids-title">
				<view>{{$t(`恭喜您`)}}，{{$t(`获得`)}} {{lottery_num}} {{$t(`机会`)}}</view>
			</view>
			<image src="../static/pay-lottery-r.png" mode=""></image>
		</view>
		<view class="grids" v-show="lotteryShow">
			<image class="grids-bag" src="../static/pay-lottery-bag.png" mode=""></image>
			<view class="grids-box">
				<gridsLottery class="" :lotteryNum="lottery_num" :prizeData="prize" @get_winingIndex='getWiningIndex'
					@luck_draw_finish='luck_draw_finish'>
				</gridsLottery>
			</view>
		</view>
		<lotteryAleart :aleartStatus="aleartStatus" @close="closeLottery" :alData="alData" :aleartType="aleartType">
		</lotteryAleart>
		<view class="mask" v-if="aleartStatus || addressModel"></view>
		<userAddress :aleartStatus="addressModel" @getAddress="getAddress" @close="()=>{addressModel = false}">
		</userAddress>
	</view>
</template>

<script>
	import gridsLottery from '../components/lottery/index.vue'
	import lotteryAleart from './components/lotteryAleart.vue'
	import userAddress from './components/userAddress.vue'
	import {
		getOrderDetail
	} from '@/api/order.js';
	import {
		openOrderSubscribe
	} from '@/utils/SubscribeMessage.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		getLotteryData,
		startLottery,
		receiveLottery
	} from '@/api/lottery.js'
	import {
		mapGetters
	} from "vuex";
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import colors from "@/mixins/color";
	export default {
		components: {
			// #ifdef MP
			authorize,
			// #endif
			gridsLottery,
			lotteryAleart,
			userAddress
		},
		mixins: [colors],
		props: {
			options: {
				type: Object
			}
		},
		data() {
			return {
				lotteryShow: false,
				addressModel: false,
				lottery_num: 0,
				aleartType: 0,
				aleartStatus: false,
				lottery_draw_param: {
					startIndex: 3, //开始抽奖位置，从0开始
					totalCount: 3, //一共要转的圈数
					winingIndex: 1, //中奖的位置，从0开始
					speed: 100 //抽奖动画的速度 [数字越大越慢,默认100]
				},
				alData: {},
				type: '',
				prize: [],
				orderId: '',
				order_pay_info: {
					paid: 1,
					_status: {}
				},
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				couponsHidden: true,
				couponList: [],
				totalPrice: 0
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {}
				},
				deep: true
			},
			options: {
				handler: function(newV, oldV) {
					this.orderId = newV.order_id;
					this.totalPrice = newV.totalPrice;
					this.type = newV.type
					this.getLotteryData(newV.type)
				},
				deep: true
			}
		},
		created(options) {
			// #ifdef H5 || APP-PLUS
			this.orderId = this.options.order_id;
			this.totalPrice = this.options.totalPrice;
			this.type = this.options.type;
			// #endif
			if (this.isLogin) {
				this.getLotteryData(this.type)
			} else {
				toLogin();
			}
		},
		methods: {
			openTap() {
				this.$set(this, 'couponsHidden', !this.couponsHidden);
			},
			orderDetails() {
				this.$emit('orderDetails')
			},
			getWiningIndex(callback) {
				this.aleartType = 0
				startLottery({
					id: this.id
				}).then(res => {
					this.prize.forEach((item, index) => {
						if (res.data.id === item.id) {
							this.alData = res.data
							this.lottery_draw_param.winingIndex = index;
							callback(this.lottery_draw_param);
						}
					})
				}).catch(err => {
					this.$util.Tips({
						title: err
					});
				})
				// //props修改在小程序和APP端不成功，所以在这里使用回调函数传参，
			},
			/**
			 * 去首页关闭当前所有页面
			 */
			goIndex: function(e) {
				uni.switchTab({
					url: '/pages/index/index'
				});
			},
			/**
			 * 
			 * 去订单详情页面
			 */
			goOrderDetails: function(e) {
				// #ifdef MP
				uni.showLoading({
					title: this.$t(`正在加载中`),
				})
				openOrderSubscribe().then(res => {
					uni.hideLoading();
					uni.navigateTo({
						url: '/pages/goods/order_details/index?order_id=' + this.orderId
					});
				}).catch(() => {
					nui.hideLoading();
				});
				// #endif
			},
			getLotteryData(type) {
				getLotteryData(type).then(res => {
					this.factor_num = res.data.lottery.factor_num
					this.id = res.data.lottery.id
					this.prize = res.data.lottery.prize
					this.lottery_num = res.data.lottery_num
					this.prize.push({
						a: 1
					})
					this.$emit('lotteryShow', true)
					this.lotteryShow = true
				}).catch(err => {
					this.$emit('lotteryShow', false)
					this.lotteryShow = false
				})
			},
			closeLottery(status) {
				this.aleartStatus = false
				this.getLotteryData(this.type)
				if (this.alData.type === 6) {
					this.addressModel = true
				}
			},
			getAddress(data) {
				let addData = data
				addData.id = this.alData.lottery_record_id
				addData.address = data.address.province + data.address.city + data.address.district + data.detail
				receiveLottery(addData).then(res => {
					this.$util.Tips({
						title: this.$t(`领取成功`)
					});
					this.addressModel = false
				}).catch(err => {
					this.$util.Tips({
						title: err
					});
				})
			},
			getWiningIndex(callback) {
				this.aleartType = 0
				startLottery({
					id: this.id
				}).then(res => {
					this.prize.forEach((item, index) => {
						if (res.data.id === item.id) {
							this.alData = res.data
							this.lottery_draw_param.winingIndex = index;
							callback(this.lottery_draw_param);
						}
					})
				}).catch(err => {
					this.$util.Tips({
						title: err
					});
				})
				// //props修改在小程序和APP端不成功，所以在这里使用回调函数传参，
			},
			// 抽奖完成
			luck_draw_finish(param) {
				this.aleartType = 2
				this.aleartStatus = true
			},

		}
	}
</script>

<style lang="scss" scoped>
	.header {
		color: #fff;
		background-color: var(--view-theme);
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		padding: 80rpx 0;

		.pay-status {
			display: flex;
			align-items: center;

			.iconfont {
				font-size: 74rpx;
				background: rgba(#000, 0.08);
				border-radius: 50%;
				margin-right: 30rpx;
				padding: 9rpx;
			}

			.pay-status-r {
				display: flex;
				flex-direction: column;

				.pay-status-text {
					font-size: 38rpx;
					font-weight: bold;
					padding-bottom: 10rpx;
				}
			}
		}

		.grids /deep/ .grid_wrap .lottery_wrap .lottery_grid li:nth-of-type(9) {
			background: rgba(#fff, 0.2) !important;
		}

		.jump {
			display: flex;
			padding-top: 40rpx;

			.jump-det {
				background: #FFFFFF;
				opacity: 1;
				border-radius: 22px;
				color: #E93323;
				padding: 10rpx 38rpx;
				margin-right: 30rpx;
			}

			.jump-index {
				border: 1px solid #FEFFFF;
				opacity: 1;
				padding: 10rpx 38rpx;
				border-radius: 22px;
			}
		}
	}

	.grids-top {
		display: flex;
		justify-content: center;
		padding: 30rpx 0 0 0;

		image {
			width: 40rpx;
			height: 40rpx;
		}

		.grids-title {
			display: flex;
			justify-content: center;
			font-size: 20px;
			color: #E93323;
			z-index: 999;
			padding: 0 14rpx;
			font-weight: bold;

			.grids-frequency {}
		}
	}

	/deep/ .lottery_grid {
		background-color: #E93323;
		border-radius: 12rpx;
	}

	.grids {
		width: 100%;
		// height: 800rpx;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		margin-top: 20rpx;
		position: relative;
		padding: 30rpx;

		.grids-bag {
			position: absolute;
			top: 0;
			left: 0;
			// #ifdef MP
			width: 95%;
			height: 95%;
			// #endif
			// #ifdef H5 || APP-PLUS
			width: 750rpx;
			height: 750rpx;
			// #endif
			padding: 20rpx;
			display: flex;
			justify-content: center;
			align-items: center;
		}

		.grids-box {
			width: 700rpx;
			height: 700rpx;
			// z-index: 10000;
			padding: 20rpx;
			background-color: #E74435;
		}

		.winning-tips-list {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 50%;
			font-size: 20rpx;
			line-height: 40rpx;
			height: 40rpx;
			font-weight: 400;
			color: #FFF8F8;
			margin: 30rpx 0;
			z-index: 999;
			background: linear-gradient(90deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.3) 51%, rgba(255, 255, 255, 0) 100%);

			.iconfont {
				font-size: 20rpx;
				margin-right: 10rpx;
			}
		}
	}

	.mask {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: rgba(0, 0, 0, 0.8);
		z-index: 9;
	}
</style>
