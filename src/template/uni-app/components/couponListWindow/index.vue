<template>
	<view>
		<view class='coupon-list-window' :class='coupon.coupon==true?"on":""'>
			<view v-if="coupon.count" class="nav acea-row row-around">
				<view v-if="coupon.count[2]" :class="['acea-row', 'row-middle', coupon.type === 2 ? 'on' : '']" @click="setType(2)">商品券</view>
				<view v-if="coupon.count[1]" :class="['acea-row', 'row-middle', coupon.type === 1 ? 'on' : '']" @click="setType(1)">品类券</view>
				<view v-if="coupon.count[0]" :class="['acea-row', 'row-middle', coupon.type === 0 ? 'on' : '']" @click="setType(0)">通用券</view>
			</view>
			<view class='title' v-else>优惠券<text class='iconfont icon-guanbi' @click='close'></text></view>
			<view v-if="coupon.count" class="occupy"></view>
			<view class='coupon-list' v-if="coupon.list.length">
				<view class='item acea-row row-center-wrapper' v-for="(item,index) in coupon.list" @click="getCouponUser(index,item.id)"
				 :key='index' :class="{svip: item.receive_type === 4}">
					<view class='money acea-row row-column row-center-wrapper' :class='item.is_use?"moneyGray":""'>
						<view>￥<text class='num'>{{item.coupon_price}}</text></view>
						<view class="pic-num" v-if="item.use_min_price > 0">满{{item.use_min_price}}元可用</view>
						<view class="pic-num" v-else>无门槛券</view>
					</view>
					<view class='text'>
						<view class='condition line1'>
							<span class='line-title' :class='item.is_use?"gray":""' v-if='item.type===0'>通用劵</span>
							<span class='line-title' :class='item.is_use?"gray":""' v-else-if='item.type===1'>品类券</span>
							<span class='line-title' :class='item.is_use?"gray":""' v-else>商品券</span>
							<image src='../../static/images/fvip.png' class="pic" v-if="item.receive_type===4"></image>
							<span class='name'>{{item.title}}</span>
						</view>
						<view class='data acea-row row-between-wrapper'>
							<view>{{ item.start_time ? item.start_time + "-" : ""}}{{ item.end_time }}</view>
							<view class='bnt gray' v-if="item.is_use">{{item.use_title || '已领取'}}</view>
							<view class='bnt bg-color' v-else>{{coupon.statusTile || '立即领取'}}</view>
						</view>
					</view>
				</view>
			</view>
			<!-- 无优惠券 -->
			<view class='pictrue' v-else>
				<image src='../../static/images/noCoupon.png'></image>
			</view>
		</view>
		<view class='mask' catchtouchmove="true" :hidden='coupon.coupon==false' @click='close'></view>
	</view>
</template>

<script>
	import {
		setCouponReceive
	} from '@/api/api.js';
	export default {
		props: {
			//打开状态 0=领取优惠券,1=使用优惠券
			openType: {
				type: Number,
				default: 0,
			},
			coupon: {
				type: Object,
				default: function() {
					return {};
				}
			}
		},
		data() {
			return {
				type: 0
			};
		},
		methods: {
			close: function() {
				this.$emit('ChangCouponsClone');
				this.type = 0;
			},
			getCouponUser: function(index, id) {
				let that = this;
				let list = that.coupon.list;
				if (list[index].is_use == true && this.openType == 0) return true;
				switch (this.openType) {
					case 0:
						//领取优惠券
						setCouponReceive(id).then(res => {
							that.$emit('ChangCouponsUseState', index);
							that.$util.Tips({
								title: "领取成功"
							});
							that.$emit('ChangCoupons', list[index]);
						}).catch(err => {
							uni.showToast({
								title: err,
								icon: 'none'
							});
						})
						break;
					case 1:
						that.$emit('ChangCoupons', index);
						break;
				}
			},
			setType: function(type) {
				this.type = type;
				this.$emit('tabCouponType', type);
			}
		}
	}
</script>

<style scoped lang="scss">
	.coupon-list-window .coupon-list .text .condition .pic {
		width: 30rpx;
		height: 30rpx;
		margin-right: 10rpx;
		vertical-align: middle;
	}

	.coupon-list-window .coupon-list .text .condition .name {
		vertical-align: middle;
	}

	.coupon-list-window {
		position: fixed;
		bottom: 0;
		left: 0;
		width: 100%;
		background-color: #FFFFFF;
		border-radius: 16rpx 16rpx 0 0;
		z-index: 555;
		transform: translate3d(0, 100%, 0);
		transition: all .3s cubic-bezier(.25, .5, .5, .9);
	}

	.coupon-list-window.on {
		transform: translate3d(0, 0, 0);
	}

	.coupon-list-window .title {
		height: 124rpx;
		width: 100%;
		text-align: center;
		line-height: 124rpx;
		font-size: 32rpx;
		font-weight: bold;
		position: relative;
	}

	.coupon-list-window .title .iconfont {
		position: absolute;
		right: 30rpx;
		top: 50%;
		transform: translateY(-50%);
		font-size: 35rpx;
		color: #8a8a8a;
		font-weight: normal;
	}

	.coupon-list-window .coupon-list {
		margin: 0 0 50rpx 0;
		height: 721rpx;
		padding-top: 28rpx;
		overflow: auto;
	}

	.coupon-list-window .pictrue {
		width: 414rpx;
		height: 336rpx;
		margin: 192rpx auto 243rpx auto;
	}

	.coupon-list-window .pictrue image {
		width: 100%;
		height: 100%;
	}

	.pic-num {
		color: #fff;
		font-size: 24rpx;
	}

	.line-title {
		width: 90rpx;
		padding: 0 10rpx;
		box-sizing: border-box;
		background: rgba(255, 244, 243, 1);
		border: 1px solid rgba(233, 51, 35, 1);
		opacity: 1;
		border-radius: 20rpx;
		font-size: 20rpx;
		color: #E93323;
		margin-right: 12rpx;
	}

	.line-title.gray {
		border-color: #C1C1C1;
		color: #C1C1C1;
		background-color: #F7F7F7;
	}

	.nav {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
		height: 106rpx;
		border-bottom: 2rpx solid #F5F5F5;
		border-top-left-radius: 16rpx;
		border-top-right-radius: 16rpx;
		background-color: #FFFFFF;
		font-size: 30rpx;
		color: #999999;
	}

	.nav .acea-row {
		border-top: 5rpx solid transparent;
		border-bottom: 5rpx solid transparent;
	}

	.nav .acea-row.on {
		border-bottom-color: #E93323;
		color: #282828;
	}

	.nav .acea-row:only-child {
		border-bottom-color: transparent;
	}

	.occupy {
		height: 106rpx;
	}

	.coupon-list .item {
		margin-bottom: 18rpx;
		box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.06);
	}

	.coupon-list .item .money {
		font-weight: normal;
	}
</style>
