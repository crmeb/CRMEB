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
		<lotteryAlert :aleartStatus="aleartStatus" @close="closeLottery" :alData="alData" :aleartType="aleartType">
		</lotteryAlert>
		<view class="mask" v-if="aleartStatus || addressModel"></view>
		<userAddress :aleartStatus="addressModel" @getAddress="getAddress" @close="()=>{addressModel = false}">
		</userAddress>
	</view>
</template>

<script setup>
	import { ref, watch } from 'vue';
	import { storeToRefs } from 'pinia';
	import gridsLottery from '../components/lottery/index.vue'
	import lotteryAlert from './components/lotteryAlert.vue'
	import userAddress from './components/userAddress.vue'
	import {
		getOrderDetail
	} from '@/api/order.js';
	import {
		openOrderSubscribe
	} from '@/utils/subscribeMessage.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		getLotteryData as getLotteryDataApi,
		startLottery,
		receiveLottery
	} from '@/api/lottery.js'
	import { useAppStore } from '@/store/modules/app';
	import { useColorTheme } from '@/composables/useColorTheme';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif

	const props = defineProps({
		options: {
			type: Object
		}
	});

	const emit = defineEmits(['orderDetails', 'lotteryShow']);

	const { colorStyle } = useColorTheme();

	const appStore = useAppStore();
	const { isLogin } = storeToRefs(appStore);

	const lotteryShow = ref(false);
	const addressModel = ref(false);
	const lottery_num = ref(0);
	const aleartType = ref(0);
	const aleartStatus = ref(false);
	const lottery_draw_param = ref({
		startIndex: 3, //开始抽奖位置，从0开始
		totalCount: 3, //一共要转的圈数
		winingIndex: 1, //中奖的位置，从0开始
		speed: 100 //抽奖动画的速度 [数字越大越慢,默认100]
	});
	const alData = ref({});
	const type = ref('');
	const prize = ref([]);
	const orderId = ref('');
	const order_pay_info = ref({
		paid: 1,
		_status: {}
	});
	const isAuto = ref(false); //没有授权的不会自动授权
	const isShowAuth = ref(false); //是否隐藏授权
	const couponsHidden = ref(true);
	const couponList = ref([]);
	const totalPrice = ref(0);
	// 原代码未在 data 声明、直接挂到实例上的字段
	let factor_num;
	let id;

	watch(isLogin, (newV, oldV) => {
		if (newV) {}
	}, { deep: true });

	watch(() => props.options, (newV, oldV) => {
		orderId.value = newV.order_id;
		totalPrice.value = newV.totalPrice;
		type.value = newV.type
		getLotteryData(newV.type)
	}, { deep: true });

	// created
	// #ifdef H5 || APP-PLUS
	orderId.value = props.options.order_id;
	totalPrice.value = props.options.totalPrice;
	type.value = props.options.type;
	// #endif
	if (isLogin.value) {
		getLotteryData(type.value)
	} else {
		toLogin();
	}

	function openTap() {
		couponsHidden.value = !couponsHidden.value;
	}
	function orderDetails() {
		emit('orderDetails')
	}
	// 原代码 getWiningIndex 重复定义两次（两处实现相同），按 Vue2 取后者语义合并为一个
	function getWiningIndex(callback) {
		aleartType.value = 0
		startLottery({
			id: id
		}).then(res => {
			prize.value.forEach((item, index) => {
				if (res.data.id === item.id) {
					alData.value = res.data
					lottery_draw_param.value.winingIndex = index;
					callback(lottery_draw_param.value);
				}
			})
		}).catch(err => {
			util.Tips({
				title: err
			});
		})
		// //props修改在小程序和APP端不成功，所以在这里使用回调函数传参，
	}
	/**
	 * 去首页关闭当前所有页面
	 */
	function goIndex(e) {
		uni.switchTab({
			url: '/pages/index/index'
		});
	}
	/**
	 *
	 * 去订单详情页面
	 */
	function goOrderDetails(e) {
		// #ifdef MP
		uni.showLoading({
			title: i18n.t(`正在加载中`),
		})
		openOrderSubscribe().then(res => {
			uni.hideLoading();
			uni.navigateTo({
				url: '/pages/goods/order_details/index?order_id=' + orderId.value
			});
		}).catch(() => {
			uni.hideLoading();
		});
		// #endif
	}
	function getLotteryData(type) {
		getLotteryDataApi(type).then(res => {
			factor_num = res.data.lottery.factor_num
			id = res.data.lottery.id
			prize.value = res.data.lottery.prize
			lottery_num.value = res.data.lottery_num
			prize.value.push({
				a: 1
			})
			emit('lotteryShow', true)
			lotteryShow.value = true
		}).catch(err => {
			emit('lotteryShow', false)
			lotteryShow.value = false
		})
	}
	function closeLottery(status) {
		aleartStatus.value = false
		getLotteryData(type.value)
		if (alData.value.type === 6) {
			addressModel.value = true
		}
	}
	function getAddress(data) {
		let addData = data
		addData.id = alData.value.lottery_record_id
		addData.address = data.address.province + data.address.city + data.address.district + data.detail
		receiveLottery(addData).then(res => {
			util.Tips({
				title: i18n.t(`领取成功`)
			});
			addressModel.value = false
		}).catch(err => {
			util.Tips({
				title: err
			});
		})
	}
	// 抽奖完成
	function luck_draw_finish(param) {
		aleartType.value = 2
		aleartStatus.value = true
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

		.grids :deep(.grid_wrap .lottery_wrap .lottery_grid li:nth-of-type(9)){
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

:deep(.lottery_grid){
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
