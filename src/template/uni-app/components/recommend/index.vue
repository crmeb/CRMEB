<template>
	<view class='recommend'>
		<view class='title acea-row row-center-wrapper'>
			<text class='iconfont icon-zhuangshixian'></text>
			<text class='name'>热门推荐</text>
			<text class='iconfont icon-zhuangshixian lefticon'></text>
		</view>
		<view class='recommendList acea-row row-between-wrapper'>
			<view class='item' v-for="(item,index) in hostProduct" :key="index" hover-class='none' @tap="goDetail(item)">
				<view class='pictrue'>
					<image :src='item.image'></image>
					<span class="pictrue_log_big pictrue_log_class" v-if="item.activity && item.activity.type === '1'">秒杀</span>
					<span class="pictrue_log_big pictrue_log_class" v-if="item.activity && item.activity.type === '2'">砍价</span>
					<span class="pictrue_log_big pictrue_log_class" v-if="item.activity && item.activity.type === '3'">拼团</span>
				</view>
				<view class='name line1'>{{item.store_name}}</view>
				<view class='money font-color'>￥<text class='num'>{{item.price}}</text></view>
			</view>
		</view>
	</view>
</template>

<script>
	import {mapGetters} from "vuex";
	import { goShopDetail } from '@/libs/order.js'
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
		data() {
			return {

			};
		},

		methods: {
			goDetail(item){
				goShopDetail(item,this.uid).then(res=>{
					uni.navigateTo({
						url:`/pages/goods_details/index?id=${item.id}`
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
	}

	.recommend .recommendList .item .pictrue {
		position: relative;
		width: 100%;
		height: 335rpx;
	}

	.recommend .recommendList .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.recommend .recommendList .item .name {
		font-size: 28rpx;
		color: #282828;
		margin-top: 20rpx;
	}

	.recommend .recommendList .item .money {
		font-size: 20rpx;
		margin-top: 8rpx;
	}

	.recommend .recommendList .item .money .num {
		font-size: 28rpx;
	}
</style>
