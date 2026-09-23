<template>
	<view :style="colorStyle">
		<block v-if="bargain.length > 0">
			<view class="bargain-record" ref="container">
				<view class="item" v-for="(item, index) in bargain" :key="index">
				<view class="exchange_record-time">
					<view class="">{{ $t(`兑换时间`) }}：{{ item.add_time ? $util.formatDate(item.add_time, 'yyyy-MM-dd hh:mm') : '' }}</view>
					<view class="status">
						{{ $t(item.status_text) }}
					</view>
				</view>
				<view class="picTxt acea-row row-between-wrapper">
					<view class="pictrue">
						<image :src="item.image" />
					</view>
					<view class="text acea-row row-column-around">
						<view class="line1" style="width: 100%">{{ item.store_name }}</view>
						<view class="line1 gray-sty">{{ item.suk }}</view>
						<view class="line1 gray-sty">{{ $t(`积分`) }}:{{ item.total_price }}</view>
					</view>
				</view>
				<view class="bottom acea-row row-between-wrapper">
					<view class="end"></view>
					<view class="acea-row row-middle row-right">
						<view class="bnt cancel" v-if="item.status_code === 'processing'"
							@click="getLogistics(item.order_id)">
							{{ $t(`查看物流`) }}
						</view>
						<view class="bnt bg-color-red" @click="goDetail(item.order_id)">
							{{ $t(`查看详情`) }}
						</view>
					</view>
				</view>
			</view>
				<Loading :loaded="status" :loading="loadingList"></Loading>
			</view>
		</block>
		<block v-if="bargain.length == 0">
			<emptyPage :title="$t(`暂无兑换记录～`)"></emptyPage>
		</block>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>
<script setup>
	import { ref } from 'vue';
	import { onShow, onReachBottom } from '@dcloudio/uni-app';
	import CountDown from '@/components/countDown';
	import emptyPage from '@/components/emptyPage.vue';
	import {
		getCenterOrderList
	} from '@/api/order-center';
	import Loading from '@/components/Loading';
	import home from '@/components/home';
	import { useColorTheme } from '@/composables/useColorTheme';
	import util from '@/utils/util.js';

	defineOptions({ name: 'BargainRecord' });

	const { colorStyle } = useColorTheme();

	// ---------- data ----------
	const bargain = ref([]);
	const status = ref(false);
	const loadingList = ref(false);
	const page = ref(1);
	const limit = ref(20);
	const userInfo = ref({});

	onShow(() => {
		bargain.value = [];
		page.value = 1;
		status.value = false
		getExchangeRecordList();
	});

	// ---------- methods ----------
	function goDetail(orderNo) {
		uni.navigateTo({
			url: `/pages/points_mall/integral_order_details?order_id=${orderNo}`
		});
	}
	function getExchangeRecordList() {
		if (loadingList.value) return;
		if (status.value) return;
		loadingList.value = true
		getCenterOrderList({
				page: page.value,
				limit: limit.value,
				order_category: 'points'
			})
			.then((res) => {
				const list = (res.data.list || []).map(item => ({
					order_no: item.order_no,
					add_time: item.add_time,
					status_text: item.status_text,
					status_code: item.status_code,
					image: item.image,
					store_name: item.title,
					suk: item.extra_info?.sku_info?.suk || '',
					total_num: item.total_num,
					total_price: (item.extra_info?.activity_info?.integral_unit_price || 0) * (item.total_num || 1),
					delivery_type: item.extra_info?.delivery_type || '',
					order_id: item.order_no,
				}));
				status.value = list.length < limit.value;
				bargain.value.push.apply(bargain.value, list);
				page.value++;
				loadingList.value = false;
			})
			.catch((res) => {
				util.Tips({
					title: res
				});
			});
	}
	function getLogistics(order_id) {
		uni.navigateTo({
			url: `/pages/points_mall/logistics_details?order_id=${order_id}`
		});
	}

	onReachBottom(() => {
		if (!loadingList.value)
			getExchangeRecordList();
	})
</script>

<style lang="scss">
	/*砍价记录*/
	.bargain-record .item .picTxt .text .time .styleAll {
		color: #fc4141;
		font-size: 24rpx;
	}

	.bargain-record .item .picTxt .text .time .red {
		color: #999;
		font-size: 24rpx;
	}

	.bargain-record .item {
		background-color: #fff;
		margin: 15rpx 15rpx;
		border-radius: 6rpx;

		.exchange_record-time {
			color: #333333;
			border-bottom: 1px solid #eeeeee;
			padding: 22rpx 30rpx;
			display: flex;
			justify-content: space-between;

			.status {
				color: var(--view-theme);
			}
		}
	}

	.bargain-record .item .picTxt {
		border-bottom: 1px solid #f0f0f0;
		padding: 30rpx 30rpx;
	}

	.bargain-record .item .picTxt .pictrue {
		width: 120rpx;
		height: 120rpx;
		margin-right: 30rpx;
	}

	.bargain-record .item .picTxt .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6upx;
	}

	.bargain-record .item .picTxt .text {
		// flex:1;
		width: 77%;
		font-size: 30upx;
		color: #282828;
	}

	.bargain-record .item .picTxt .text .time {
		font-size: 24upx;
		color: #868686;
		justify-content: left !important;
	}

	.bargain-record .item .picTxt .text .successTxt {
		font-size: 24rpx;
	}

	.bargain-record .item .picTxt .text .endTxt {
		font-size: 24rpx;
		color: #999;
	}

	.bargain-record .item .picTxt .text .money {
		font-size: 24upx;
	}

	.bargain-record .item .picTxt .text .money .num {
		font-size: 32upx;
		font-weight: bold;
	}

	.bargain-record .item .picTxt .text .money .symbol {
		font-weight: bold;
	}

	.bargain-record .item .bottom {
		height: 100upx;
		padding: 0 30upx;
		font-size: 27upx;
	}

	.bargain-record .item .bottom .purple {
		color: #f78513;
	}

	.bargain-record .item .bottom .end {
		color: #999;
	}

	.bargain-record .item .bottom .success {
		color: #e93323;
	}

	.bargain-record .item .bottom .bnt {
		font-size: 27upx;
		color: #fff;
		width: 176upx;
		height: 60upx;
		border-radius: 32upx;
		text-align: center;
		line-height: 60upx;
	}

	.bargain-record .item .bottom .bnt.cancel {
		color: #aaa;
		border: 1px solid #ddd;
	}

	.bargain-record .item .bottom .bnt~.bnt {
		margin-left: 18upx;
	}

	.gray-sty {
		width: 100%;
		font-size: 24rpx;
		color: #999999;
	}
</style>