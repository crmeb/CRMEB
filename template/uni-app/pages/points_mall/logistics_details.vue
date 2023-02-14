<template>
	<view :style="colorStyle">
		<view class='logistics'>
			<view class='header acea-row row-between row-top'>
				<view class='pictrue'>
					<image :src='orderInfo.image'></image>
				</view>
				<view class='text acea-row row-between'>
					<view class='name line2'>{{orderInfo.store_name}}</view>
					<view class='money'>
						<view>{{orderInfo.total_price}}{{$t(`积分`)}}</view>
						<view>x{{orderInfo.total_num}}</view>
					</view>
				</view>
			</view>
			<view class='logisticsCon'>
				<view class='company acea-row row-between-wrapper'>
					<view class='picTxt acea-row row-between-wrapper'>
						<view class='iconfont icon-wuliu'></view>
						<view class='text'>
							<view><text class='name line1'>{{$t(`物流公司`)}}：</text> {{orderInfo.delivery_name}}</view>
							<view class='express line1'><text class='name'>{{$t(`快递单号`)}}：</text>
								{{orderInfo.delivery_id}}</view>
						</view>
					</view>
					<!-- #ifndef H5 -->
					<view class='copy' @tap='copyOrderId'>{{$t(`复制单号`)}}</view>
					<!-- #endif -->
					<!-- #ifdef H5 -->
					<view class='copy copy-data' :data-clipboard-text="orderInfo.delivery_id">{{$t(`复制单号`)}}</view>
					<!-- #endif -->
				</view>
				<view class='item' v-for="(item,index) in expressList" :key="index">
					<view class='circular' :class='index === 0 ? "on":""'></view>
					<view class='text' :class='index===0 ? "on-font on":""'>
						<view>{{item.status}}</view>
						<view class='data' :class='index===0 ? "on-font on":""'>{{item.time}}</view>
					</view>
				</view>
			</view>
			<recommend :hostProduct='hostProduct'></recommend>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		getProductHot
	} from '@/api/store.js';
	import {
		getLogisticsDetails
	} from '@/api/activity.js'
	import ClipboardJS from "@/plugin/clipboard/clipboard.js";
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	import recommend from '@/components/recommend';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import colors from '@/mixins/color.js';
	export default {
		components: {
			recommend,
			// #ifdef MP
			authorize
			// #endif
		},
		mixins: [colors],
		data() {
			return {
				orderId: '',
				product: {
					productInfo: {}
				},
				orderInfo: {},
				expressList: [],
				hostProduct: []
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.getExpress();
						this.get_host_product();
					}
				},
				deep: true
			}
		},
		onLoad: function(options) {
			if (!options.order_id) return this.$util.Tips({
				title: this.$t(`缺少订单号`)
			});
			this.orderId = options.order_id;
			if (this.isLogin) {
				this.getExpress();
				this.get_host_product();
			} else {
				toLogin();
			}
		},
		onReady: function() {
			// #ifdef H5
			this.$nextTick(function() {
				const clipboard = new ClipboardJS(".copy-data");
				clipboard.on("success", () => {
					this.$util.Tips({
						title: this.$t(`复制成功`)
					});
				});
			});
			// #endif
		},
		// 滚动监听
		onPageScroll(e) {
			// 传入scrollTop值并触发所有easy-loadimage组件下的滚动监听事件
			uni.$emit('scroll');
		},
		methods: {
			/**
			 * 授权回调
			 */
			onLoadFun: function() {
				this.getExpress();
				this.get_host_product();
			},
			copyOrderId: function() {
				uni.setClipboardData({
					data: this.orderInfo.delivery_id
				});
			},
			getExpress: function() {
				let that = this;
				getLogisticsDetails(that.orderId).then(function(res) {
					let result = res.data.express.result || {};
					// that.$set(that, 'product', res.data.order.cartInfo[0] || {});
					that.$set(that, 'orderInfo', res.data.order);
					that.$set(that, 'expressList', result.list || []);
				}).catch((error) => {
					this.$util.Tips({
						title: error
					});
				});
			},
			/**
			 * 获取我的推荐
			 */
			get_host_product: function() {
				let that = this;
				getProductHot().then(function(res) {
					that.$set(that, 'hostProduct', res.data);
				});
			},
		}
	}
</script>

<style scoped lang="scss">
	.logistics .header {
		padding: 23rpx 30rpx;
		background-color: #fff;
		height: 166rpx;
		box-sizing: border-box;
	}

	.logistics .header .pictrue {
		width: 120rpx;
		height: 120rpx;
	}

	.logistics .header .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.logistics .header .text {
		width: 540rpx;
		font-size: 28rpx;
		color: #999;
		margin-top: 6rpx;
	}

	.logistics .header .text .name {
		width: 365rpx;
		color: #282828;
	}

	.logistics .header .text .money {
		text-align: right;
	}

	.logistics .logisticsCon {
		background-color: #fff;
		margin: 12rpx 0;
	}

	.logistics .logisticsCon .company {
		height: 120rpx;
		margin: 0 0 45rpx 30rpx;
		padding-right: 30rpx;
		border-bottom: 1rpx solid #f5f5f5;
	}

	.logistics .logisticsCon .company .picTxt {
		width: 520rpx;
	}

	.logistics .logisticsCon .company .picTxt .iconfont {
		width: 50rpx;
		height: 50rpx;
		background-color: #666;
		text-align: center;
		line-height: 50rpx;
		color: #fff;
		font-size: 35rpx;
	}

	.logistics .logisticsCon .company .picTxt .text {
		width: 450rpx;
		font-size: 26rpx;
		color: #282828;
	}

	.logistics .logisticsCon .company .picTxt .text .name {
		color: #999;
	}

	.logistics .logisticsCon .company .picTxt .text .express {
		margin-top: 5rpx;
	}

	.logistics .logisticsCon .company .copy {
		font-size: 20rpx;
		width: 106rpx;
		text-align: center;
		border-radius: 3rpx;
		border: 1px solid #999;
		padding: 3rpx 0;
	}

	.logistics .logisticsCon .item {
		padding: 0 40rpx;
		position: relative;
	}

	.logistics .logisticsCon .item .circular {
		width: 20rpx;
		height: 20rpx;
		border-radius: 50%;
		position: absolute;
		top: -1rpx;
		left: 31.5rpx;
		background-color: #ddd;
	}

	.logistics .logisticsCon .item .circular.on {
		background-color: var(--view-theme);
	}

	.logistics .logisticsCon .item .text.on-font {
		color: var(--view-theme);
	}

	.logistics .logisticsCon .item .text .data.on-font {
		color: var(--view-theme);
	}

	.logistics .logisticsCon .item .text {
		font-size: 26rpx;
		color: #666;
		width: 615rpx;
		border-left: 1rpx solid #e6e6e6;
		padding: 0 0 60rpx 38rpx;
	}

	.logistics .logisticsCon .item .text.on {
		border-left-color: var(--view-minorColor);
	}

	.logistics .logisticsCon .item .text .data {
		font-size: 24rpx;
		color: #999;
		margin-top: 10rpx;
	}

	.logistics .logisticsCon .item .text .data .time {
		margin-left: 15rpx;
	}
</style>
