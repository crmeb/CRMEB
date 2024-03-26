<template>
	<view class="goodsList">
		<view class="item" v-for="(item, index) in tempArr" :key="index" @click="goDetail(item)">
			<view class="pictrue">
				<span class="pictrue_log pictrue_log_class" v-if="item.activity && item.activity.type === '1' && $permission('seckill')">{{ $t(`秒杀`) }}</span>
				<span class="pictrue_log pictrue_log_class" v-if="item.activity && item.activity.type === '2' && $permission('bargain')">{{ $t(`砍价`) }}</span>
				<span class="pictrue_log pictrue_log_class" v-if="item.activity && item.activity.type === '3'  && $permission('combination')">{{ $t(`拼团`) }}</span>
				<image :src="item.recommend_image" mode="" v-if="item.recommend_image"></image>
				<image :src="item.image" mode="" v-else></image>
			</view>
			<view class="text line2">{{ item.store_name }}</view>
			<view class="bottom acea-row row-between-wrapper">
				<view class="sales acea-row row-middle">
					<view class="money font-color">
						<text>{{ $t(`￥`) }}</text>
						{{ item.price }}
					</view>
					<view>{{ $t(`已售`) }}{{ item.sales }}</view>
				</view>
				<view v-if="item.stock > 0">
					<view
						class="bnt"
						v-if="(item.activity && (item.activity.type === '1' || item.activity.type === '2' || item.activity.type === '3')) || item.is_virtual || !item.cart_button"
					>
						{{ $t(`立即购买`) }}
					</view>
					<view v-else>
						<view class="bnt" v-if="!item.spec_type && !item.cart_num" @click.stop="goCartDan(item, index)">
							{{ $t(`加入购物车`) }}
						</view>
						<view class="cart acea-row row-middle" v-else-if="!item.spec_type && item.cart_num">
							<view class="iconfont icon-jianhao" @click.stop="CartNumDes(index, item)"></view>
							<view class="num">{{ item.cart_num }}</view>
							<view class="iconfont icon-jiahao" @click.stop="CartNumAdd(index, item)"></view>
						</view>
						<!-- 多规格 -->
						<view class="bnt" @click.stop="goCartDuo(item)" v-else-if="item.spec_type">
							{{ $t(`加入购物车`) }}
							<view class="num" v-if="isLogin && item.cart_num">{{ item.cart_num }}</view>
						</view>
						<!-- 单规格 -->
					</view>
				</view>
				<view class="bnt end" v-else>{{ $t(`已售罄`) }}</view>
			</view>
		</view>
	</view>
</template>

<script>
export default {
	name: 'catGoodList',
	props: {
		dataConfig: {
			type: Object,
			default: () => {}
		},
		tempArr: {
			type: Array,
			default: []
		},
		isLogin: {
			type: Boolean,
			default: false
		}
	},
	data() {
		return {
			addIng: false
		};
	},
	created() {},
	mounted() {},
	methods: {
		goDetail(item) {
			this.$emit('detail', item);
		},
		goCartDuo(item) {
			this.$emit('gocartduo', item);
		},
		goCartDan(item, index) {
			this.$emit('gocartdan', item, index);
		},
		CartNumDes(index, item) {
			if (this.addIng) return;
			this.addIng = true;
			this.$emit('ChangeCartNumDan', false, index, item);
		},
		CartNumAdd(index, item) {
			if (this.addIng) return;
			this.addIng = true;
			this.$emit('ChangeCartNumDan', true, index, item);
		}
	}
};
</script>

<style lang="scss">
.goodsList {
	padding: 0 30rpx;

	.item {
		width: 100%;
		box-sizing: border-box;
		margin-bottom: 63rpx;

		.pictrue {
			// width: 100%;
			// height: 290rpx;
			border-radius: 16rpx;
			position: relative;
			padding-top: 40%;

			image {
				border-radius: 10rpx;
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				object-fit: cover;
			}
		}

		.text {
			font-size: 30rpx;
			font-family: PingFang SC;
			font-weight: bold;
			color: #282828;
			margin: 20rpx 0;
		}

		.bottom {
			.sales {
				font-size: 22rpx;
				color: #8e8e8e;

				.money {
					font-size: 42rpx;
					font-weight: bold;
					margin-right: 18rpx;

					text {
						font-size: 28rpx;
					}
				}
			}

			.cart {
				height: 56rpx;

				.pictrue {
					color: var(--view-theme);
					font-size: 46rpx;
					width: 50rpx;
					height: 50rpx;
					text-align: center;
					line-height: 50rpx;
				}

				.num {
					font-size: 30rpx;
					color: #282828;
					font-weight: bold;
					width: 80rpx;
					text-align: center;
				}
			}

			.bnt {
				padding: 0 30rpx;
				height: 56rpx;
				line-height: 56rpx;
				background: var(--view-theme);
				border-radius: 42rpx;
				font-size: 26rpx;
				color: #fff;
				position: relative;

				&.end {
					background: rgba(203, 203, 203, 1);
				}

				.num {
					background-color: var(--view-priceColor);
					min-width: 12rpx;
					color: #fff;
					border-radius: 15px;
					position: absolute;
					right: -14rpx;
					top: -15rpx;
					font-size: 22rpx;
					padding: 0 10rpx;
					height: 34rpx;
					line-height: 34rpx;
				}
			}
		}
	}
}
</style>
