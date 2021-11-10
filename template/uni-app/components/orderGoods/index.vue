<template>
	<view class="orderGoods">
		<view class='total' v-if="!split">共{{totalNmu}}件商品</view>
		<view class='total' v-if="split">
			<text>订单包裹{{index + 1}}</text>
			<!-- <view class="rig-btn" v-if="status_type === 2">
				<view class="logistics sure" @click="confirmOrder(orderId)">确认收货</view>
				<view v-if="delivery_type === 'express'" class="logistics" @click="logistics(orderId)">查看物流</view>
			</view> -->
			<view class="rig-btn" v-if="status_type === -1">
				<view class="refund">申请退款中</view>
			</view>
			<view class="rig-btn" v-else-if="status_type === -2">
				<view class="refund">已退款</view>
			</view>
			<view class="rig-btn" v-else-if="status_type === 4">
				<view class="done">已完成</view>
			</view>
		</view>

		<view class='goodWrapper'>
			<view class='' :class="{op:!item.is_valid}" v-for="(item,index) in cartInfo" :key="index"
				@click="jumpCon(item.product_id,item.advance_id)">
				<view class="item acea-row row-between-wrapper">
					<view class='pictrue'>
						<image :src='item.productInfo.attrInfo.image' v-if="item.productInfo.attrInfo"></image>
						<image :src='item.productInfo.image' v-else></image>
					</view>
					<view class='text'>
						<view class='acea-row row-between-wrapper'>
							<view class='name line1'>{{item.productInfo.store_name}}</view>
							<view class='num'>x {{item.cart_num}}</view>
						</view>
						<view class='attr line1' v-if="item.productInfo.attrInfo">{{item.productInfo.attrInfo.suk}}
						</view>
						<view class='money font-color pic' v-if="item.productInfo.attrInfo">
							<text>
								￥{{item.productInfo.attrInfo.price}}
							</text>
							<text class="valid" v-if="!item.is_valid">不送达</text>
						</view>
						<view class='money font-color pic' v-else>
							<text>￥{{item.productInfo.price}}</text>
							<text class="valid" v-if="!item.is_valid">不送达</text>
						</view>
						<view class='evaluate' v-if='item.is_reply==0 && evaluate==3 && pid != -1'
							@click.stop="evaluateTap(item.unique,orderId)">评价</view>
						<view class='evaluate' v-else-if="item.is_reply==1">已评价</view>
					</view>
				</view>
				<view class="botton-btn">
					<view class='logistics' v-if="cartInfo.length > 1 && !is_confirm && !split && !is_confirm  && pid <= 0"
						@click.stop="openSubcribe(item.id)">
						申请退款</view>
					<view class='logistics'
						v-else-if="!is_confirm && index === cartInfo.length - 1 && split  && refund_status === 0"
						@click.stop="openSubcribeSplit()">
						申请退款</view>
					<view class="rig-btn" v-if="status_type === 2 && index === cartInfo.length - 1 || !split">
						<view v-if="delivery_type === 'express'" class="logistics" @click.stop="logistics(orderId)">查看物流
						</view>
						<view class="logistics sure" v-if="status_type === 2" @click.stop="confirmOrder(orderId)">确认收货
						</view>
					</view>
				</view>
			</view>

		</view>
	</view>
</template>

<script>
	export default {
		props: {
			evaluate: {
				type: Number,
				default: 0,
			},
			cartInfo: {
				type: Array,
				default: function() {
					return [];
				}
			},
			orderId: {
				type: String,
				default: '',
			},
			delivery_type: {
				type: String,
				default: '',
			},
			jump: {
				type: Boolean,
				default: false,
			},
			is_confirm: {
				type: Boolean,
				default: false,
			},
			split: {
				type: Boolean,
				default: false,
			},
			jumpDetail: {
				type: Boolean,
				default: false,
			},
			index: {
				type: Number,
				default: 0,
			},
			pid: {
				type: Number,
				default: 0,
			},
			refund_status: {
				type: Number,
				default: 0,
			},
			status_type: {
				type: Number,
				default: 0,
			}
		},
		data() {
			return {
				totalNmu: 0,
				operationModel: false,
				status: ""
			};
		},
		watch: {
			cartInfo: function(nVal, oVal) {
				let num = 0
				nVal.forEach((item, index) => {
					num += item.cart_num
				})
				this.totalNmu = num
			}
		},
		mounted() {
			let num = 0
			this.$nextTick(() => {
				this.cartInfo.forEach((item, index) => {
					num += item.cart_num
				})
				this.$set(this, 'totalNmu', num)
			})

		},
		methods: {
			evaluateTap: function(unique, orderId) {
				uni.navigateTo({
					url: "/pages/users/goods_comment_con/index?unique=" + unique + "&uni=" + orderId
				})
			},
			jumpCon(id, advance_id) {
				if (advance_id) {
					uni.navigateTo({
						url: `/pages/activity/presell_details/index?id=${advance_id}`
					})
				} else if (this.jump) {
					uni.navigateTo({
						url: `/pages/goods_details/index?id=${id}`
					})
				} else if (this.jumpDetail) {
					uni.navigateTo({
						url: `/pages/users/order_details/index?order_id=${this.orderId}`
					})
				}
			},
			logistics(order_id) {
				uni.navigateTo({
					url: '/pages/users/goods_logistics/index?orderId=' + order_id
				})
			},
			confirmOrder(orderId) {
				this.$emit('confirmOrder', orderId)
			},
			changeOperation() {
				this.operationModel = !this.operationModel
			},
			openSubcribe(id) {
				this.$emit('openSubcribe', `/pages/users/goods_return/index?orderId=${this.orderId}&cart_id=${id}`)
			},
			openSubcribeSplit() {
				this.$emit('openSubcribe', `/pages/users/goods_return/index?orderId=${this.orderId}`)
			},
		}
	}
</script>

<style scoped lang="scss">
	.fontcolor {
		color: #e93323;
	}

	.orderGoods {
		background-color: #fff;
		margin-top: 12rpx;
	}

	.orderGoods .total {
		display: flex;
		justify-content: space-between;
		align-items: center;
		width: 100%;
		// height: 86rpx;
		padding: 0 30rpx;
		border-bottom: 2rpx solid #f0f0f0;
		font-size: 30rpx;
		color: #282828;
		line-height: 86rpx;
		box-sizing: border-box;


	}

	.botton-btn {
		display: flex;
		align-items: right;
		justify-content: flex-end;
		padding: 20rpx 20rpx;
	}

	.rig-btn {
		display: flex;
		align-items: center;

		.refund {
			font-size: 26rpx;
			color: #e93323;
		}

		.done {
			font-size: 26rpx;
			color: #F19D2F;
		}
	}

	.logistics {
		// height: 46rpx;
		line-height: 30rpx;
		color: #999999;
		font-size: 20rpx;
		border: 1px solid;
		border-radius: 30rpx;
		padding: 6rpx 12rpx;
		margin-left: 10rpx;
	}

	.sure {
		color: #e93323;
		border: 1px solid #e93323;
	}

	.more-operation {
		display: flex;
		justify-content: center;
		align-items: center;
		padding: 10rpx 0;
		color: #bbb;
	}

	.b-top {
		margin-left: 30rpx;
		margin-right: 30rpx;
		border-top: 1px solid #f0f0f0
	}

	.fade-enter-active,
	.fade-leave-active {
		transition: all 0.1s;
	}

	.fade-enter,
	.fade-leave-to

	/* .fade-leave-active below version 2.1.8 */
		{
		opacity: 0;
		transform: translateY(-10px);
	}

	.op {
		opacity: 0.5;
	}

	.pic {
		display: flex;
		justify-content: space-between;
	}

	.valid {
		font-size: 24rpx;
	}
</style>
