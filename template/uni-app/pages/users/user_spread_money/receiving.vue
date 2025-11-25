<template>
	<view :style="colorStyle">
		<view class="px-20">
			<template v-if="!showBack">
				<view class="w-full h-120 rd-24rpx bg--w111-fff px-32 mt-40 flex-between-center fs-28">
					<text>收款方式</text>
					<view class="flex-y-center">
						<image src="../static/weixin.png" class="w-42 h-42"></image>
						<text class="pl-16">收款至微信</text>
					</view>
				</view>
				<view class="w-full rd-24rpx bg--w111-fff px-32 mt-20 fs-28">
					<view class="fs-28 lh-40rpx pt-40">收款金额</view>
					<view class="mt-36 pb-40">
						<baseMoney :money="infoData.true_extract_price" symbolSize="48" integerSize="72" decimalSize="72" color="#333333" weight></baseMoney>
					</view>

					<!-- <view class="pt-20 pb-24 fs-26 text--w111-999">提现手续费{{withdraw_fee}}%</view> -->
				</view>
			</template>
			<view class="w-full rd-24rpx bg--w111-fff mt-40 py-82 flex-col flex-between-center fs-28" v-if="showBack">
				<view class="flex-y-center">
					<image src="../static/receiving_success.png" class="w-60 h-60"></image>
					<text class="pl-16 fs-32 fw-500">收款成功</text>
				</view>
				<view class="mt-40 pb-32">
					<baseMoney :money="infoData.true_extract_price" symbolSize="48" integerSize="72" decimalSize="72" color="#333333" weight></baseMoney>
				</view>
				<view class="fs-26 text--w111-999">可在“微信支付-服务-钱包-账单”查看明细</view>
				<view class="w-504 h-80 rd-40rpx flex-center bg-color fs-28 text--w111-fff mt-52" @tap="backList">返回列表</view>
			</view>
			<view class="fixed-lb w-full pb-safe" v-if="!showBack">
				<view class="w-full h-128 px-20 flex-center">
					<view class="w-full h-80 rd-40rpx flex-center bg-color fs-28 text--w111-fff" @tap="confrimTap">立即收款</view>
				</view>
			</view>
		</view>
	</view>
</template>
<script>
import colors from '@/mixins/color.js';
import { toLogin } from '@/libs/login.js';
import { transferInfoApi } from '@/api/user';
import { mapGetters } from 'vuex';
export default {
	mixins: [colors],
	data() {
		return {
			id: 0,
			type: 1,
			infoData: {
				true_extract_price: '',
				package_info: '',
				mchid: '',
				wechat_appid: ''
			},
			withdraw_fee: '',
			showBack: false
		};
	},
	computed: mapGetters(['isLogin']),
	onLoad(options) {
		if (options.id) {
			this.id = options.id;
			this.type = options.type;
			this.getInfo();
		}
	},
	methods: {
		getInfo() {
			transferInfoApi({
				order_id: this.id,
				type: this.type
			})
				.then((res) => {
					this.infoData = res.data;
				})
				.catch((err) => {
					return this.$util.Tips(
						{
							title: err
						},
						{
							tab: 3
						}
					);
				});
		},
		confrimTap() {
			if (this.infoData.state === 'FAIL')
				return wx.showToast({
					title: '转账已失效,请从重新发起'
				});
			let that = this;
			// #ifdef MP-WEIXIN
			if (wx.canIUse('requestMerchantTransfer')) {
				wx.requestMerchantTransfer({
					mchId: that.infoData.mchid,
					appId: wx.getAccountInfoSync().miniProgram.appId,
					package: that.infoData.package_info,
					success: (res) => {
						that.showBack = true;
						// res.err_msg将在页面展示成功后返回应用时返回ok，并不代表付款成功
						console.log('success:', res);
					},
					fail: (res) => {
						console.log('fail:', res);
					}
				});
			} else {
				wx.showToast({
					title: '你的微信版本过低，请更新至最新版本。'
				});
			}
			// #endif
			// #ifdef H5
			if (that.$wechat.isWeixin()) {
				let configAppMessage = {
					mchId: that.infoData.mchid,
					appId: that.infoData.wechat_appid,
					package: that.infoData.package_info
				};
				if (WeixinJSBridge) {
					WeixinJSBridge.invoke('requestMerchantTransfer', configAppMessage, function (res) {
						if (res.err_msg === 'requestMerchantTransfer:ok') {
							// res.err_msg将在页面展示成功后返回应用时返回success，并不代表付款成功
							that.showBack = true;
						}
					});
				} else {
					uni.showToast({
						title: '你的微信版本过低，请更新至最新版本。'
					});
				}
			}
			// #endif
		},
		backList() {
			let backUrl;
			if (this.type == 1) {
				backUrl = '/pages/users/user_spread_money/index?type=1';
			} else {
				backUrl = '/pages/goods/lottery/grids/record';
			}
			uni.reLaunch({
				url: backUrl
			});
		}
	}
};
</script>
<style lang="scss">
.bb-e {
	border-bottom: 1px solid #eee;
}
.py-82 {
	padding: 82rpx 0;
}
</style>
