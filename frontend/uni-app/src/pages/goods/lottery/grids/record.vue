<template>
	<view :style="colorStyle">
		<view class="record" v-if="lotteryList.length">
			<view class="record-list" v-for="item in lotteryList" :key="item.id">
				<image class="goods-img" :src="item.prize.image" mode=""></image>
				<view class="right-data">
					<view class="title line1">
						{{ item.prize.name }}
					</view>
					<view class="goods-msg">
						{{ $t(`奖品类型`) }}：
						<text class="num">
							{{ $t(typeName(item.prize.type)) }}
						</text>
					</view>
					<view class="goods-msg exchange" v-if="item.type == 6 && !item.receive_time" @click="fromAddress(item)">
						{{ $t(`立即兑换`) }}
					</view>
					<view class="goods-msg exchange" v-if="item.type == 4 && item.state == 'WAIT_USER_CONFIRM'"
						@click="goDetail(item)">
						{{ $t(`去领取`) }}
					</view>
					<view class="goods-msg fail-btn" v-if="item.type == 4 && item.state == 'FAIL'">
						{{ $t(`已失效`) }}
					</view>
					<view class="goods-msg" v-else>
						{{ $t(`兑换时间`) }}：
						{{ item.receive_time || '--' }}
					</view>

					<view class="goods-msg" v-if="item.deliver_info.deliver_name">
						{{ $t(`快递公司`) }}：
						{{ item.deliver_info.deliver_name || '--' }}
					</view>
					<view class="goods-msg" v-if="item.deliver_info.deliver_number">
						{{ $t(`快递单号`) }}：
						{{ item.deliver_info.deliver_number || '--' }}
						<!-- #ifndef H5 -->
						<view v-if="item.deliver_info.deliver_number" class="copy"
							@tap="copyOrderId(item.deliver_info.deliver_number)">{{ $t(`复制`) }}</view>
						<!-- #endif -->
						<!-- #ifdef H5 -->
						<view v-if="item.deliver_info.deliver_number" class="copy copy-data"
							:data-clipboard-text="item.deliver_info.deliver_number">{{ $t(`复制`) }}</view>
						<!-- #endif -->
						<!-- <view v-if="item.deliver_info.deliver_number" class='copy' @tap='copyOrderId(item.deliver_info.deliver_number)'>复制</view> -->
					</view>
				</view>
			</view>
			<view class="loadingicon acea-row row-center-wrapper" v-if="lotteryList.length > 0">
				<text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
				{{ loadTitle }}
			</view>
		</view>
		<block v-if="lotteryList.length === 0 && !loading">
			<emptyPage :title="$t(`暂无中奖记录`)"></emptyPage>
		</block>
		<userAddress :aleartStatus="addressModel" @getAddress="getAddress" @close="
				() => {
					addressModel = false;
				}
			"></userAddress>
		<view v-if="addressModel" class="mask" @close="
				() => {
					addressModel = false;
				}
			"></view>
	</view>
</template>

<script setup>
	import { ref, nextTick } from 'vue';
	import { onShow, onReady, onReachBottom } from '@dcloudio/uni-app';
	import ClipboardJS from '@/plugin/clipboard/clipboard.js';
	import {
		getLotteryList as getLotteryListApi,
		receiveLottery
	} from '@/api/lottery.js';
	import userAddress from '../components/userAddress.vue';
	import emptyPage from '@/components/emptyPage.vue';
	import { useColorTheme } from '@/composables/useColorTheme';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';

	const { colorStyle } = useColorTheme();

	const loading = ref(false);
	const loadend = ref(false);
	const addressModel = ref(false);
	const where = ref({
		page: 1,
		limit: 20
	});
	const lotteryList = ref([]);
	const loadTitle = ref('');
	// 原代码未在 data 声明、直接挂到实例上的字段
	let addid;

	onShow(() => {
		where.value.page = 1
		loadend.value = false
		loading.value = false
		lotteryList.value = [];
		getLotteryList();
	});

	onReady(() => {
		// #ifdef H5 || APP-PLUS
		nextTick(() => {
			const clipboard = new ClipboardJS('.copy-data');
			clipboard.on('success', () => {
				util.Tips({
					title: i18n.t(`复制成功`)
				});
			});
		});
		// #endif
	});

	function typeName(type) {
		if (type == 2) {
			return '积分';
		} else if (type == 3) {
			return '余额';
		} else if (type == 4) {
			return '红包';
		} else if (type == 5) {
			return '优惠券';
		} else if (type == 6) {
			return '商品';
		}
	}
	function getAddress(data) {
		let addData = data;
		addData.id = addid;
		addData.address = data.address.province + data.address.city + data.address.district + data.detail;
		receiveLottery(addData)
			.then((res) => {
				util.Tips({
					title: i18n.t(`领取成功`)
				});
				addressModel.value = false;
				where.value.page = 1;
				loadend.value = false;
				lotteryList.value = [];
				getLotteryList();
			})
			.catch((err) => {
				util.Tips({
					title: err
				});
			});
	}
	function goDetail(item) {
		if (item.state == 'WAIT_USER_CONFIRM') {
			uni.navigateTo({
				url: '/pages/users/user_spread_money/receiving?type=2&id=' + item.wechat_order_id
			});
		}
	}
	function fromAddress(item) {
		addid = item.id;
		addressModel.value = true;
	}
	// #ifndef H5
	function copyOrderId(data) {
		uni.setClipboardData({
			data: data
		});
	}
	// #endif
	function getLotteryList() {
		if (loadend.value) return;
		if (loading.value) return;
		loading.value = true;
		loadTitle.value = '';
		getLotteryListApi(where.value)
			.then((res) => {
				let list = res.data;
				let newList = util.SplitArray(list, lotteryList.value);
				let loadendVal = list.length < where.value.limit;
				loadend.value = loadendVal;
				loading.value = false;
				loadTitle.value = loadendVal ? i18n.t(`没有更多内容啦~`) : i18n.t(`加载更多`);
				lotteryList.value = newList;
				where.value['page'] = where.value.page + 1;
			})
			.catch((err) => {
				loading.value = false;
				loadTitle.value = i18n.t(`加载更多`);
			});
	}

	onReachBottom(() => {
		if (lotteryList.value.length > 0) {
			getLotteryList();
		} else {
			getLotteryList();
		}
	});
</script>

<style lang="scss" scoped>
	.record {
		background-color: #eee;
	}

	.record-list {
		display: flex;
		align-items: center;
		background-color: #fff;
		padding: 30rpx;
		border-bottom: 1px solid #eeeeee;
		height: 100%;

		.goods-img {
			width: 170rpx;
			height: 170rpx;
			border-radius: 6rpx;
			margin-right: 15rpx;
		}

		.right-data {
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			min-height: 170rpx;

			.title {
				font-size: 28rpx;
			}

			.goods-msg {
				font-size: 24rpx;
				color: #999;

				.num {
					color: var(--view-theme);
				}

				.copy {
					display: -webkit-inline-box;
					display: -webkit-inline-flex;
					width: 60rpx;
					margin-left: 10rpx;
					padding: 0rpx 4rpx;
					border: 2rpx solid;
				}
			}
			.fail-btn{
				color: #fff;
				background-color: #999;
				border-radius: 30rpx;
				text-align: center;
				padding: 4rpx 0;
			}
			.exchange {
				color: #fff;
				background-color: var(--view-theme);
				border-radius: 30rpx;
				text-align: center;
				padding: 4rpx 0;
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
	}
</style>