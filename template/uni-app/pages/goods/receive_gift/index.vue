<template>
	<view class="warpper">
		<image class="top-bg" src="../static/receive-gift-bag.png" mode=""></image>
		<image class="top-bow" src="../static/bow.png" mode=""></image>
		<view class="aleart">
			<view class="from">{{ nickname }} 送给您的一份礼物</view>
			<view class="message">{{ gift_mark }}</view>
			<view class="aleart-body">
				<image class="goods-img" :src="image" mode=""></image>
			</view>
			<view class="title line1">
				{{ $t(store_name) }}
			</view>
		</view>
		<view v-if="(giftStatus == 1 || giftStatus == 2) && refund_status == 0" class="btn" @click="goGetGift()">
			{{ giftStatusText }}
		</view>
		<view v-if="giftStatus == 3 && refund_status == 0" class="btn-n">
			{{ $t('礼物已被领取') }}
		</view>
		<view v-if="refund_status != 0" class="btn-n">
			{{ $t('礼物已失效') }}
		</view>
	</view>
</template>

<script>
import { getGiftOrderDetail } from '@/api/order.js';
import { HTTP_REQUEST_URL } from '@/config/app';
import { toLogin } from '@/libs/login.js';
import { mapGetters } from 'vuex';
let app = getApp();
export default {
	computed: mapGetters(['isLogin']),
	data() {
		return {
			order_id: '',
			giftStatus: 0,
			refund_status: 0,
			gift_mark: '',
			image: '',
			store_name: '',
			nickname: '',
			giftStatusText: '',
			o_id: 0
		};
	},
	props: {},
	watch: {
		// aleartStatus(status) {
		// 	if (!status) {
		// 		this.aleartData = {};
		// 	}
		// }
	},
	onLoad(options) {
		if (this.isLogin) {
			this.order_id = options.id;
			// #ifdef MP
			if (options.scene) {
				let value = this.$util.getUrlParams(decodeURIComponent(options.scene));
				if (value.order_id) options.id = value.id;
				//记录推广人uid
				if (value.pid) app.globalData.spid = value.pid;
			}
			// #endif
			this.uid = this.$store.state.app.uid;
			this.getOrderInfo();
		} else {
			toLogin();
		}
	},
	methods: {
		goGetGift() {
			if (this.giftStatus == 2) {
				uni.reLaunch({
					url: `/pages/goods/order_details/index?is_gift=2&order_id=${this.o_id}&is_gift=2`
				});
			} else {
				uni.reLaunch({
					url: `/pages/goods/order_confirm/index?order_id=${this.order_id}&is_gift=2`
				});
			}
		},
		getOrderInfo() {
			getGiftOrderDetail(this.order_id).then((res) => {
				this.gift_uid = res.data.gift_uid;
				this.gift_mark = res.data.gift_mark;
				this.nickname = res.data.nickname;
				this.refund_status = res.data.refund_status;
				this.image = res.data.cartInfo[0].productInfo.image;
				this.store_name = res.data.cartInfo[0].productInfo.store_name;
				this.o_id = res.data.order_id
				if (this.gift_uid === 0) {
					this.giftStatus = 1;
					this.giftStatusText = this.$t('收下礼物');
				} else if (this.gift_uid === this.uid) {
					this.giftStatus = 2;
					this.giftStatusText = this.$t('查看礼物');
				} else if (this.gift_uid !== this.uid) {
					this.giftStatus = 3;
				}
			});
		}
	}
};
</script>

<style lang="scss" scoped>
.warpper {
	display: flex;
	flex-direction: column;
	align-items: center;
	.top-bg {
		width: 100%;
		height: 206rpx;
	}
	.top-bow {
		width: 342rpx;
		height: 78rpx;
		margin-top: -120rpx;
		z-index: 9;
	}
}
.aleart {
	width: 670rpx;
	z-index: 7;
	margin-top: -20rpx;
	background-color: #fff;
	padding: 30rpx;
	border-radius: 12rpx;
	background-size: 100% 100%;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;

	.from {
		font-size: 28rpx;
		font-weight: 500;
		color: #333333;
		margin-bottom: 16rpx;
	}
	.message {
		font-weight: 400;
		font-size: 26rpx;
		color: #999999;
		margin-bottom: 42rpx;
	}
	.aleart-body {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 396rpx;
		height: 419rpx;
		background-image: url('/pages/goods/static/gift-border.png');
		background-size: 100% 100%;
		margin-bottom: 40rpx;
		.goods-img {
			width: 360rpx;
			height: 360rpx;
			margin-top: 24rpx;
		}
	}
	.title {
		width: 396rpx;
		font-weight: 400;
		font-size: 28rpx;
		color: #333333;
		margin-bottom: 34rpx;
		text-align: center;
	}
}
.btn,
.btn-clear,
.btn-n {
	width: 670rpx;
	height: 84rpx;
	line-height: 84rpx;
	border-radius: 20px;
	text-align: center;
	margin-top: 56rpx;
}
.btn {
	color: #fff;
	background: linear-gradient(90deg, #ff7931 0%, #e93323 100%);
}
.btn-clear {
	color: #e93323;
	border: 1px solid #e93323;
}
.btn-n {
	color: #fff;
	background: #cccccc;
}
</style>
