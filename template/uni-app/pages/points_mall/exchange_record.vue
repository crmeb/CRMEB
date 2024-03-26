<template>
	<view :style="colorStyle">
		<block v-if="bargain.length > 0">
			<view class="bargain-record" ref="container">
				<view class="item" v-for="(item, index) in bargain" :key="index">
					<view class="exchange_record-time">
						<view class="">{{ $t(`兑换时间`) }}：{{ item.add_time }}</view>
						<view class="status">
							{{ $t(item.status_name) }}
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
							<view class="bnt cancel" v-if="item.status === 2 && item.delivery_type === 'express'"
								@click="getLogistics(item.order_id)">
								{{ $t(`查看物流`) }}
							</view>
							<view class="bnt bg-color-red" @click="goDetail(item.order_id)">
								{{ $t(`查看详情`) }}
							</view>
							<!-- <view class="bnt bg-color-red" v-else @click="goList">重开一个</view> -->
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
<script>
	import CountDown from '@/components/countDown';
	import emptyPage from '@/components/emptyPage.vue';
	import {
		getIntegralOrderList
	} from '@/api/activity';
	import Loading from '@/components/Loading';
	import home from '@/components/home';
	import colors from '@/mixins/color.js';
	export default {
		name: 'BargainRecord',
		components: {
			CountDown,
			Loading,
			emptyPage,
			home
		},
		props: {},
		mixins: [colors],
		data() {
			return {
				bargain: [],
				status: false, //砍价列表是否获取完成 false 未完成 true 完成
				loadingList: false, //当前接口是否请求完成 false 完成 true 未完成
				page: 1, //页码
				limit: 20, //数量
				userInfo: {}
			};
		},
		onShow() {
			this.bargain = [];
			this.page = 1;
			this.status = false
			this.getIntegralOrderList();
		},
		methods: {
			goDetail: function(id) {
				uni.navigateTo({
					url: `/pages/points_mall/integral_order_details?order_id=${id}`
				});
			},
			getIntegralOrderList: function() {
				var that = this;
				if (that.loadingList) return;
				if (that.status) return;
				that.loadingList = true
				getIntegralOrderList({
						page: that.page,
						limit: that.limit
					})
					.then((res) => {
						that.status = res.data.length < that.limit;
						that.bargain.push.apply(that.bargain, res.data);
						that.page++;
						that.loadingList = false;
					})
					.catch((res) => {
						that.$util.Tips({
							title: res
						});
					});
			},
			getLogistics(order_id) {
				uni.navigateTo({
					url: `/pages/points_mall/logistics_details?order_id=${order_id}`
				});
			}
		},
		onReachBottom() {
			if (!this.loadingList)
				this.getIntegralOrderList();
		}
	};
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