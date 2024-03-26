<template>
	<view class="goodsList">
		<view class="item acea-row row-between-wrapper" v-for="(item,index) in tempArr" :key='index'
			@click="goDetail(item)">
			<view class="pictrue">
				<span class="pictrue_log pictrue_log_class" v-if="item.activity && item.activity.type === '1' && $permission('seckill')">{{$t(`秒杀`)}}</span>
				<span class="pictrue_log pictrue_log_class" v-if="item.activity && item.activity.type === '2' && $permission('bargain')">{{$t(`砍价`)}}</span>
				<span class="pictrue_log pictrue_log_class" v-if="item.activity && item.activity.type === '3' && $permission('combination')">{{$t(`拼团`)}}</span>
				<image :src="item.image" mode=""></image>
			</view>
			<view class="pictxt">
				<view class="text line2">{{item.store_name}}</view>
				<view class="bottom acea-row row-between-wrapper">
					<view class="money font-color">
						<text class="sign">{{$t(`￥`)}}</text>{{item.price}}
						<!-- <span class="vip" v-if="item.vip_price">
							<image src="../../static/images/vip01.png"></image>
							￥{{item.vip_price}}
						</span>
						<text class="y_money" v-else>￥{{item.ot_price}}</text> -->
					</view>
					<view v-if="item.stock>0">
						<view class="iconfont icon-gouwuche6 acea-row row-center-wrapper"
							v-if="item.activity && (item.activity.type === '1' || item.activity.type === '2' || item.activity.type === '3')">
						</view>
						<view v-else>
							<!-- 多规格 -->
							<view class="bnt acea-row row-center-wrapper" @click.stop="goCartDuo(item)"
								v-if="item.spec_type">
								{{$t(`选规格`)}}
								<text class="num" v-if="isLogin && item.cart_num">{{item.cart_num}}</text>
							</view>
							<!-- 单规格 -->
							<view class="iconfont icon-gouwuche6 acea-row row-center-wrapper"
								v-if="!item.spec_type && !item.cart_num" @click.stop="goCartDan(item,index)"></view>
							<view class="cart acea-row row-middle" v-if="!item.spec_type && item.cart_num">
								<view class="pictrue iconfont icon-jianhao acea-row row-center-wrapper"
									@click.stop="CartNumDes(index,item)"></view>
								<view class="num">{{item.cart_num}}</view>
								<view class="pictrue iconfont icon-jiahao acea-row row-center-wrapper"
									@click.stop="CartNumAdd(index,item)"></view>
							</view>
						</view>
					</view>
					<view class="bnt acea-row row-center-wrapper end" v-else>{{$t(`已售罄`)}}</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: 'd_goodList',
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
				if (this.addIng) return
				this.addIng = true
				this.$emit('ChangeCartNumDan', false, index, item);
			},
			CartNumAdd(index, item) {
				if (this.addIng) return
				this.addIng = true
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
				width: 140rpx;
				height: 140rpx;
				border-radius: 10rpx;
				position: relative;
				border-radius: 22rpx;

				image {
					width: 100%;
					height: 100%;
					border-radius: 22rpx;
				}
			}

			.pictxt {
				width: 372rpx;

				.text {
					font-size: 26rpx;
					font-family: PingFang SC;
					font-weight: 500;
					color: #333333;
				}

				.bottom {
					margin-top: 22rpx;

					.money {
						font-size: 34rpx;
						font-weight: 800;

						.sign {
							font-size: 24rpx;
						}

						.y_money {
							font-size: 20rpx;
							color: #999999;
							margin-left: 14rpx;
							font-weight: normal;
							text-decoration: line-through;
						}

						.vip {
							font-size: 22rpx;
							color: #333333;
							font-weight: normal;
							margin-left: 14rpx;

							image {
								width: 38rpx;
								height: 18rpx;
								margin-right: 6rpx;
							}
						}
					}

					.cart {
						height: 46rpx;

						.pictrue {
							color: var(--view-theme);
							font-size: 46rpx;
							width: 46rpx;
							height: 46rpx;
							text-align: center;
							line-height: 46rpx;

							&.icon-jiahao {
								color: var(--view-theme);
							}
						}

						.num {
							font-size: 30rpx;
							color: #333333;
							font-weight: bold;
							width: 60rpx;
							text-align: center;
						}
					}

					.icon-gouwuche6 {
						width: 46rpx;
						height: 46rpx;
						background-color: var(--view-theme);
						border-radius: 50%;
						color: #fff;
						font-size: 30rpx;
					}

					.bnt {
						padding: 0 20rpx;
						height: 45rpx;
						background: var(--view-theme);
						border-radius: 23rpx;
						font-size: 22rpx;
						color: #fff;
						position: relative;

						&.end {
							background: #cccccc;
						}

						.num {
							min-width: 14rpx;
							background-color: #fff;
							color: var(--view-theme);
							border-radius: 15px;
							position: absolute;
							right: -13rpx;
							top: -11rpx;
							font-size: 16rpx;
							padding: 0 10rpx;
							border: 1px solid var(--view-theme);
						}
					}
				}
			}
		}
	}
</style>
