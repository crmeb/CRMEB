<template>
	<view class='promotionGood' :style="colorStyle">
		<block v-for="(item,index) in benefit" :key="index">
			<view class='item' @tap="goDetail(item)" hover-class="none">
				<view class='pictrue'>
					<easy-loadimage mode="widthFix" :image-src="item.image"></easy-loadimage>
				</view>
				<view class='money'>
					<text class="rmb">{{$t(`￥`)}} </text><text class="price"> {{item.price}}</text>
					<!-- <text class="ot-price">{{item.ot_price}}</text> -->
				</view>
			</view>
		</block>
	</view>
</template>
<script>
	import {
		mapGetters
	} from "vuex";
	import {
		goPage,
		goShopDetail
	} from '@/libs/order.js'
	import colors from "@/mixins/color";
	export default {
		computed: mapGetters(['uid']),
		mixins: [colors],
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
			goDetail(item) {
				goPage().then(res => {
					goShopDetail(item, this.uid).then(res => {
						uni.navigateTo({
							url: `/pages/goods_details/index?id=${item.id}`
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
		display: flex;
		flex-wrap: wrap;
		padding: 15rpx 24rpx;

		.item {
			width: 215rpx;
			display: flex;
			flex-direction: column;
			justify-content: center;
			padding: 9rpx;

			.pictrue {
				height: 198rpx;
				border-radius: 12rpx;



				/deep/,
				/deep/image,
				/deep/.easy-loadimage,
				/deep/uni-image {
					width: 100%;
					height: 198rpx;
					border-radius: 12rpx;
				}

				image {
					width: 100%;
					height: 100%;
					border-radius: 12rpx;
				}
			}

			.money {
				font-size: 30rpx;
				color: var(--view-priceColor);
				margin-top: 10rpx;
				overflow: hidden; //超出的文本隐藏
				text-overflow: ellipsis; //溢出用省略号显示
				white-space: nowrap; //溢出不换行
				margin: 0 auto;

				.rmb {
					font-weight: bold;
					color: var(--view-priceColor);
					font-size: 20rpx;
					margin-right: 2rpx;
				}

				.price {
					font-weight: bold;
				}

				.ot-price {
					color: #999;
					text-decoration: line-through;
					font-size: 20rpx;
					margin-left: 4rpx;
				}
			}
		}
	}
</style>
