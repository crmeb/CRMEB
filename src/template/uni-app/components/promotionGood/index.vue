<template>
	<view class='promotionGood'>
		<block v-for="(item,index) in benefit" :key="index">
			<view class='item acea-row row-between-wrapper' @tap="goDetail(item)" hover-class="none">
				<view class='pictrue'>
					<image :src='item.image'></image>
					<span class="pictrue_log pictrue_log_class" v-if="item.activity && item.activity.type === '1'">秒杀</span>
					<span class="pictrue_log pictrue_log_class" v-if="item.activity && item.activity.type === '2'">砍价</span>
					<span class="pictrue_log pictrue_log_class" v-if="item.activity && item.activity.type === '3'">拼团</span>
				</view>
				<view class='text'>
					<view class='name line1'>{{item.store_name}}</view>
					<view class='sp-money acea-row'>
						<view class='moneyCon'>促销价: ￥<text class='num'>{{item.price}}</text></view>
					</view>
					<view class='acea-row row-between-wrapper'>
						<view class='money'>日常价：￥{{item.ot_price}}</view>
						<view>仅剩：{{item.stock}}{{item.unit_name}}</view>
					</view>
				</view>
			</view>
		</block>
	</view>
</template>
<script>
	import {mapGetters} from "vuex";
	import {goPage, goShopDetail } from '@/libs/order.js'
	export default {
		computed: mapGetters(['uid']),
		props: {
			benefit: {
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
				goPage().then(res=>{
					goShopDetail(item,this.uid).then(res=>{
						uni.navigateTo({
							url:`/pages/goods_details/index?id=${item.id}`
						})
					})
				})
			}
		}
	}
</script>

<style scoped lang='scss'>
	.promotionGood {
		padding: 0 30rpx;
	}

	.promotionGood .item {
		border-bottom: 1rpx solid #eee;
		height: 250rpx;
	}

	.promotionGood .item .pictrue {
		position: relative;
		width: 188rpx;
		height: 188rpx;
	}

	.promotionGood .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 8rpx;
	}

	.promotionGood .item .text {
		font-size: 24rpx;
		color: #999;
		width: 472rpx;
	}

	.promotionGood .item .text .name {
		font-size: 30rpx;
		color: #333;
	}

	.promotionGood .item .text .sp-money {
		margin: 34rpx 0 20rpx 0;
	}

	.promotionGood .item .text .sp-money .moneyCon {
		padding: 0 18rpx;
		background-color: red;
		height: 46rpx;
		line-height: 46rpx;
		background-image: linear-gradient(to right, #ff6248 0%, #ff3e1e 100%);
		font-size: 20rpx;
		color: #fff;
		border-radius: 24rpx 3rpx 24rpx 3rpx;
	}

	.promotionGood .item .text .sp-money .moneyCon .num {
		font-size: 24rpx;
	}

	.promotionGood .item .text .money {
		text-decoration: line-through;
	}
</style>
