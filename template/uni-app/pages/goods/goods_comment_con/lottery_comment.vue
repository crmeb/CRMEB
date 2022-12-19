<template>
	<view :style="colorStyle">
		<view class="header" v-show="lotteryShow">
			<view class="pay-status">
				<text class="iconfont icon-gou"></text>
				<view class="pay-status-r">
					<text class="pay-status-text">
						{{$t(`评价完成`)}}
					</text>
					<text class="date">
						{{new Date().toLocaleString()}}
					</text>
				</view>
			</view>
			<view class="jump">
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
				<gridsLottery class="" :prizeData="prize" @get_winingIndex='getWiningIndex'
					@luck_draw_finish='luck_draw_finish'>
				</gridsLottery>
			</view>
		</view>
		<lotteryAleart :aleartStatus="aleartStatus" @close="closeLottery" :alData="alData" :aleartType="aleartType">
		</lotteryAleart>
		<view class="mask" v-if="aleartStatus || addressModel" @click="lotteryAleartClose"></view>
		<userAddress :aleartStatus="addressModel" @getAddress="getAddress" @close="()=>{addressModel = false}">
		</userAddress>
	</view>
</template>

<script>
	import gridsLottery from '../components/lottery/index.vue'
	import lotteryAleart from './components/lotteryAleart.vue'
	import userAddress from './components/userAddress.vue'
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
	import colors from '@/mixins/color.js';
	export default {
		mixins: [colors],
		components: {
			// #ifdef MP
			authorize,
			// #endif
			gridsLottery,
			lotteryAleart,
			userAddress
		},
		computed: mapGetters(['isLogin']),
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
				date: '',
				prize: [],
				orderId: '',
				order_pay_info: {
					paid: 1,
					_status: {}
				},
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				couponsHidden: true,
				couponList: []
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						// this.getOrderPayInfo();
					}
				},
				deep: true
			}
		},
		onLoad(options) {
			this.orderId = options.order_id;
			this.type = options.type;
			// this.date = this.set_time(options.date);
			if (this.isLogin) {
				// this.getOrderPayInfo();
				this.getLotteryData(this.type)
			} else {
				toLogin();
			}
			// #ifdef H5
			document.addEventListener('visibilitychange', (e) => {
				let state = document.visibilityState
				if (state == 'hidden') {}
				if (state == 'visible') {
					// this.getOrderPayInfo();
				}
			});
			// #endif
		},
		methods: {
			set_time(str) {
				var n = parseInt(str);
				var D = new Date(n);
				var year = D.getFullYear(); //四位数年份

				var month = D.getMonth() + 1; //月份(0-11),0为一月份
				month = month < 10 ? ('0' + month) : month;

				var day = D.getDate(); //月的某一天(1-31)
				day = day < 10 ? ('0' + day) : day;

				var hours = D.getHours(); //小时(0-23)
				hours = hours < 10 ? ('0' + hours) : hours;

				var minutes = D.getMinutes(); //分钟(0-59)
				minutes = minutes < 10 ? ('0' + minutes) : minutes;

				// var seconds = D.getSeconds();//秒(0-59)
				// seconds = seconds<10?('0'+seconds):seconds;
				// var week = D.getDay();//周几(0-6),0为周日
				// var weekArr = ['周日','周一','周二','周三','周四','周五','周六'];

				var now_time = year + '-' + month + '-' + day + ' ' + hours + ':' + minutes;
				return now_time;
			},
			openTap() {
				this.$set(this, 'couponsHidden', !this.couponsHidden);
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
			},
			/**
			 * 去首页关闭当前所有页面
			 */
			goIndex: function(e) {
				uni.switchTab({
					url: '/pages/index/index'
				});
			},
			getLotteryData(type) {
				getLotteryData(type).then(res => {
					this.lotteryShow = true
					this.factor_num = res.data.lottery.factor_num
					this.id = res.data.lottery.id
					this.prize = res.data.lottery.prize
					this.lottery_num = res.data.lottery_num
					this.prize.push({
						a: 1
					})
				}).catch(err => {
					uni.redirectTo({
						url: '/pages/goods/order_details/index?order_id=' + this.orderId
					})
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
			flex-wrap: wrap;
			justify-content: flex-start;
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

	.date {
		font-size: 26rpx;
		color: #fff;
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
			width: 750rpx;
			height: 750rpx;
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
</style>
