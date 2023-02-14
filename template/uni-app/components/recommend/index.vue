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
						v-if="item.activity && item.activity.type === '1'">{{$t(`秒杀`)}}</span>
					<span class="pictrue_log_big pictrue_log_class"
						v-if="item.activity && item.activity.type === '2'">{{$t(`砍价`)}}</span>
					<span class="pictrue_log_big pictrue_log_class"
						v-if="item.activity && item.activity.type === '3'">{{$t(`拼团`)}}</span>
				</view>
				<view class='name line1'>{{item.store_name}}</view>
				<view class='money font-color'>{{$t(`￥`)}}<text class='num'>{{item.price}}</text></view>
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
		;
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
	}

	.recommend .recommendList .item .money {
		font-size: 20rpx;
		margin-top: 8rpx;
		padding: 0 10rpx 10rpx 10rpx;
	}

	.recommend .recommendList .item .money .num {
		font-size: 28rpx;
	}
</style>
