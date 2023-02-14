<template>
	<view>
		<view class="priceChange" :class="change === true ? 'on' : ''">
			<view class="priceTitle">
				{{ status == 0 ? $t(`一键改价`) : status == 1 ? $t(`订单备注`) : $t(`立即退款`) }}
				<span class="iconfont icon-guanbi" @click="close"></span>
			</view>
			<view class="listChange" v-if="status == 0 || status == 2">
				<view class="item acea-row row-between-wrapper" v-if="orderInfo.refund_status === 0">
					<view>{{$t(`商品总价`)}}({{$t(`￥`)}})</view>
					<view class="money">
						{{ orderInfo.total_price }}<span class="iconfont icon-suozi"></span>
					</view>
				</view>
				<view class="item acea-row row-between-wrapper" v-if="orderInfo.refund_status === 0">
					<view>{{$t(`原始邮费`)}}({{$t(`￥`)}})</view>
					<view class="money">
						{{ orderInfo.pay_postage }}<span class="iconfont icon-suozi"></span>
					</view>
				</view>
				<view class="item acea-row row-between-wrapper" v-if="orderInfo.refund_status === 0">
					<view>{{$t(`实际支付`)}}({{$t(`￥`)}})</view>
					<view class="money">
						<input type="text" v-model="price" :class="focus === true ? 'on' : ''" @focus="priceChange" />
					</view>
				</view>
				<view class="item acea-row row-between-wrapper" v-if="orderInfo.refund_status === 1">
					<view>{{$t(`实际支付`)}}({{$t(`￥`)}})</view>
					<view class="money">
						{{ orderInfo.pay_price }}<span class="iconfont icon-suozi"></span>
					</view>
				</view>
				<view class="item acea-row row-between-wrapper" v-if="orderInfo.refund_status === 1">
					<view>{{$t(`退款金额`)}}({{$t(`￥`)}})</view>
					<view class="money">
						<input type="text" v-model="refund_price" :class="focus === true ? 'on' : ''"
							@focus="priceChange" />
					</view>
				</view>
			</view>
			<view class="listChange" v-else>
				<textarea class="pd10" :placeholder="
            orderInfo.remark ? orderInfo.remark : $t(`请填写备注信息`)
          " v-model="remark"></textarea>
			</view>
			<view class="modify" @click="save">
				{{
          status == 1 || orderInfo.refund_status == 0 ? $t(`立即修改`) : $t(`确认退款`)
        }}
			</view>
			<view class="modify1" @click="refuse" v-if="orderInfo.refund_status == 1 && status == 0">
				{{$t(`拒绝退款`)}}
			</view>
		</view>
		<view class="mask" @touchmove.prevent v-show="change === true"></view>
	</view>
</template>
<style>
	.mask{
		z-index: 99;
	}
	.priceChange {
		position: fixed;
		width: 580upx;
		/* height: 670upx; */
		padding-bottom: 30rpx;
		background-color: #fff;
		border-radius: 10upx;
		top: 50%;
		left: 50%;
		margin-left: -290upx;
		margin-top: -335upx;
		z-index: 666;
		transition: all 0.3s ease-in-out 0s;
		transform: scale(0);
		opacity: 0;
	}

	.priceChange.on {
		opacity: 1;
		transform: scale(1);
	}

	.priceChange .priceTitle {
		background: url("../../static/pricetitle.jpg") no-repeat;
		background-size: 100% 100%;
		width: 100%;
		height: 160upx;
		border-radius: 10upx 10upx 0 0;
		text-align: center;
		font-size: 40upx;
		color: #fff;
		line-height: 160upx;
		position: relative;
	}

	.priceChange .priceTitle .iconfont {
		position: absolute;
		font-size: 40upx;
		right: 26upx;
		top: 23upx;
		width: 40upx;
		height: 40upx;
		line-height: 40upx;
	}

	.priceChange .listChange {
		width: 100%;
		padding: 0 20rpx;
	}

	.priceChange .listChange textarea {
		box-sizing: border-box;
	}

	.priceChange .listChange .item {
		height: 103upx;
		border-bottom: 1px solid #e3e3e3;
		font-size: 32upx;
		color: #333;
	}

	.priceChange .listChange .item .money {
		color: #666;
		width: 300upx;
		text-align: right;
	}

	.priceChange .listChange .item .money .iconfont {
		font-size: 32upx;
		margin-left: 20upx;
	}

	.priceChange .listChange .item .money input {
		width: 100%;
		height: 100%;
		text-align: right;
		color: #ccc;
	}

	.priceChange .listChange .item .money input.on {
		color: #666;
	}

	.priceChange .modify {
		font-size: 32upx;
		color: #fff;
		width: 490upx;
		height: 90upx;
		text-align: center;
		line-height: 90upx;
		border-radius: 45upx;
		background-color: #2291f8;
		margin: 53upx auto 0 auto;
	}

	.priceChange .modify1 {
		font-size: 32upx;
		color: #312b2b;
		width: 490upx;
		height: 90upx;
		text-align: center;
		line-height: 90upx;
		border-radius: 45upx;
		background-color: #eee;
		margin: 30upx auto 0 auto;
	}

	.priceChange .listChange textarea {
		border: 1px solid #eee;
		width: 100%;
		height: 200upx;
		margin-top: 50upx;
		border-radius: 10upx;
		color: #333;

	}
</style>
<script>
	export default {
		name: "PriceChange",
		components: {},
		props: {
			change: Boolean,
			orderInfo: Object,
			status: String
		},
		data: function() {
			return {
				focus: false,
				price: 0,
				refund_price: 0,
				remark: ""
			};
		},
		watch: {
			orderInfo: function(nVal) {
				this.price = this.orderInfo.pay_price;
				this.refund_price = this.orderInfo.pay_price;
				this.remark = "";
			}
		},
		mounted: function() {},
		methods: {
			priceChange: function() {
				this.focus = true;
			},
			close: function() {
				this.price = this.orderInfo.pay_price;
				this.$emit("closechange", false);
			},
			save: function() {
				let that = this;
				that.$emit("savePrice", {
					price: that.price,
					refund_price: that.refund_price,
					type: 1,
					remark: that.remark
				});
			},
			refuse: function() {
				let that = this;
				that.$emit("savePrice", {
					price: that.price,
					refund_price: that.refund_price,
					type: 2,
					remark: that.remark
				});
			}
		}
	};
</script>
