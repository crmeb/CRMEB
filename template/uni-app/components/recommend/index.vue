<template>
	<view class='recommend' :style="colorStyle">
		<view class='title acea-row row-center-wrapper'>
			<text class='iconfont icon-zhuangshixian'></text>
			<text class='name'>{{$t(`热门推荐`)}}</text>
			<text class='iconfont icon-zhuangshixian lefticon'></text>
		</view>
		<view class='recommendList acea-row row-between-wrapper'>
			<view class='item' v-for="(item,index) in hostProduct" :key="index" hover-class='none'
				@tap="goDetail(item)">
				<view class='pictrue'>
					<easy-loadimage mode="widthFix" :image-src="item.image"></easy-loadimage>
					<span class="pictrue_log_big pictrue_log_class"
						v-if="item.activity && item.activity.type === '1' && $permission('seckill')">{{$t(`秒杀`)}}</span>
					<span class="pictrue_log_big pictrue_log_class"
						v-if="item.activity && item.activity.type === '2' && $permission('bargain')">{{$t(`砍价`)}}</span>
					<span class="pictrue_log_big pictrue_log_class"
						v-if="item.activity && item.activity.type === '3' && $permission('combination')">{{$t(`拼团`)}}</span>
				</view>
				<view class='name line2'>{{item.store_name}}</view>
				<view class='money font-color'>{{$t(`￥`)}}<text class='num'>{{item.price}}</text></view>
				<!-- <view class='vip-money' v-if="item.vip_price && item.vip_price > 0 && item.base">
					{{$t(`￥`)}}{{item.vip_price}}
					<image src='/static/images/jvip.png' class="jvip"></image>
				</view>
				<view class='vip-money' v-if="item.vip_price && item.vip_price > 0 && item.is_vip">
					{{$t(`￥`)}}{{item.vip_price}}
					<image src='/static/images/vip.png'></image>
				</view> -->
			</view>
		</view>
	</view>
</template>

<script>
	import {
		mapGetters
	} from "vuex";
	import {
		goShopDetail
	} from '@/libs/order.js'
	import colors from "@/mixins/color";
	export default {
		computed: mapGetters(['uid']),
		props: {
			hostProduct: {
				type: Array,
				default: function() {
					return [];
				}
			}
		},
		mixins: [colors],
		data() {
			return {

			};
		},
		methods: {
			goDetail(item) {
				goShopDetail(item, this.uid).then(res => {
					uni.navigateTo({
						url: `/pages/goods_details/index?id=${item.id}`
					})
				})
			}
		}
	}
</script>

<style scoped lang="scss">
	.recommend {
		background-color: #fff;
	}

	.recommend .title {
		height: 135rpx;
		font-size: 28rpx;
		color: #282828;
	}

	.recommend .title .name {
		margin: 0 28rpx;
	}

	.recommend .title .iconfont {
		font-size: 170rpx;
		color: #454545;
	}

	.recommend .title .iconfont.lefticon {
		transform: rotate(180deg);
	}

	.recommend .recommendList {
		padding: 0 30rpx;
	}

	.recommend .recommendList .item {
		width: 335rpx;
		margin-bottom: 30rpx;
		border-radius: 20rpx 20rpx 0 0;
		box-shadow: 0rpx 3rpx 10rpx 2rpx rgba(0, 0, 0, 0.03);
		padding-bottom: 10rpx;
	}

	.recommend .recommendList .item .pictrue {
		position: relative;
		width: 100%;
		height: 335rpx;
	}


	.recommend .recommendList .item .pictrue {

		/deep/,
		/deep/image,
		/deep/.easy-loadimage,
		/deep/uni-image {

			width: 100%;
			height: 335rpx;
			border-radius: 20rpx;
		}
	}

	.recommend .recommendList .item .name {
		font-size: 28rpx;
		color: #282828;
		margin-top: 20rpx;
		padding: 0 10rpx;
		line-height: 34rpx;
		height: 68rpx;
	}

	.recommend .recommendList .item .money {
		font-size: 20rpx;
		margin-top: 8rpx;
		padding: 0 10rpx 0rpx 10rpx;
	}

	.recommend .vip-money {
		font-size: 24rpx;
		color: #282828;
		font-weight: bold;
		display: flex;
		align-items: center;
		padding: 0rpx 0 0 10rpx;

		image {
			width: 46rpx;
			height: 21rpx;
			margin-left: 4rpx;
		}
	}

	.recommend .recommendList .item .money .num {
		font-size: 28rpx;
	}
</style>
