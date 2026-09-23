<template>
	<view :style="colorStyle">
		<form @submit="subRefund">
			<view class='apply-return'>
				<view class='goodsStyle acea-row row-between' v-for="(item,index) in refundCartInfo" :key="index">
					<view class='pictrue'>
						<image :src='item.productInfo.attrInfo ? item.productInfo.attrInfo.image : item.productInfo.image'>
						</image>
					</view>
					<view class='text acea-row row-between'>
						<view class='name line2'>{{item.productInfo.store_name}}</view>
						<view class='money'>
							<view>
								{{$t(`￥`)}}{{(parseFloat(item.truePrice)+parseFloat(item.postage_price/item.cart_num)).toFixed(2)}}
							</view>
							<view class='num'>x{{item.cart_num}}</view>
						</view>
					</view>
				</view>
				<view class='list'>
					<view class='item acea-row row-between-wrapper'>
						<view>{{$t(`退货件数`)}}</view>
						<view class='num' v-if="refundCartInfo.length !== 1 || refund_total_num == 1">
							{{refund_total_num}}
						</view>
						<picker v-else class='num' @change="returnGoodsNum" :value="refund_num_index"
							:range="refundNumData">
							<view class="picker acea-row row-between-wrapper">
								<view class='reason'>{{refundNumData[refund_num_index]}}</view>
								<text class='iconfont icon-jiantou'></text>
							</view>
						</picker>
					</view>
					<view class='item acea-row row-between-wrapper' v-if="status && status._type !== 1">
						<view>{{$t(`退款类型`)}}</view>
						<picker v-if="status._is_back" class='num' @change="returnGoodsChange" :value="returnGoods"
							:range="returnGoodsData">
							<view class="picker acea-row row-between-wrapper">
								<view class='reason'>{{returnGoodsData[returnGoods]}}</view>
								<text class='iconfont icon-jiantou'></text>
							</view>
						</picker>
						<view class="" v-else>
							{{ $t(`仅退款`) }}
						</view>
					</view>
					<view class='item acea-row row-between-wrapper'>
						<view>{{$t(`退款原因`)}}</view>
						<picker class='num' @change="bindPickerChange" :value="index" :range="RefundArray">
							<view class="picker acea-row row-between-wrapper">
								<view class='reason'>{{RefundArray[index]}}</view>
								<text class='iconfont icon-jiantou'></text>
							</view>
						</picker>
					</view>
					<view class='item textarea acea-row row-between'>
						<view>{{$t(`备注说明`)}}</view>
						<textarea :placeholder='$t(`填写备注信息，100字以内`)' class='num' name="refund_reason_wap_explain"
							:placeholder-class='$t(`填写备注信息，100字以内`)'></textarea>
					</view>
					<view class='item acea-row row-between upload'>
						<view class='title acea-row row-between-wrapper'>
							<view>{{$t(`上传图片`)}}</view>
							<view class='tip'></view>
						</view>
						<view class='upload acea-row row-middle'>
							<view class='pictrue' v-for="(item,index) in refund_reason_wap_img" :key="index">
								<image :src='item'></image>
								<view class='iconfont icon-guanbi1 font-num' @tap='DelPic(index)'></view>
							</view>
							<view class='pictrue acea-row row-center-wrapper row-column' @tap='uploadpic'
								v-if="refund_reason_wap_img.length < 3">
								<text class='iconfont icon-icon25201'></text>
								<view>{{$t(`上传图片`)}}</view>
							</view>
						</view>
					</view>
				</view>
				<button class='returnBnt bg-color' form-type="submit">{{$t(`申请退款`)}}</button>
			</view>
		</form>
	</view>
</template>
<script setup>
	import { ref, watch } from 'vue';
	import { onLoad } from '@dcloudio/uni-app';
	import { storeToRefs } from 'pinia';
	import {
		ordeRefundReason,
		orderRefundVerify,
		getOrderDetail,
		returnGoodsSubmit,
		postRefundGoods
	} from '@/api/order.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import { useAppStore } from '@/store/modules/app';
	import { useColorTheme } from '@/composables/useColorTheme';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif

	const { colorStyle } = useColorTheme();

	const appStore = useAppStore();
	const { isLogin } = storeToRefs(appStore);

	const id = ref(0);
	const cartIds = ref([]);
	const refund_reason_wap_img = ref([]);
	const status = ref({});
	const RefundArray = ref([]);
	const refundCartInfo = ref([]);
	const returnGoodsData = ref([i18n.t(`仅退款`), i18n.t(`退货并退款`)]);
	const refund_total_num = ref(0);
	const index = ref(0);
	const returnGoods = ref(0);
	const orderId = ref(0);
	const refundNumData = ref([]);
	const refund_num_index = ref(0);
	const isRes = ref(false);

	watch(isLogin, (newV, oldV) => {
		if (newV) {
			refundGoodsInfo();
			getRefundReason();
		}
	}, { deep: true });

	onLoad((options) => {
		orderId.value = options.orderId;
		id.value = options.id;
		if (options.cartIds) {
			cartIds.value = JSON.parse(options.cartIds) || []
		}
		if (isLogin.value) {
			refundGoodsInfo();
			getRefundReason();
		} else {
			toLogin();
		}
	});

	function refundGoodsInfo() {
		postRefundGoods({
			id: id.value,
			cart_ids: cartIds.value
		}).then(res => {
			let data = res.data;
			status.value = data._status;
			refundCartInfo.value = data.cartInfo;
			refundCartInfo.value.forEach(item => {
				refund_total_num.value = util.$h.Add(refund_total_num.value, item.cart_num)
			})
			refundNumData.value = Array(refund_total_num.value).fill(0).map((e, i) => i + 1)
			refundCartInfo.value.length === 1 ? refund_num_index.value = refundNumData.value.length - 1 : ''
		}).catch(err => {
			return util.Tips({
				title: err
			});
		})
	}
	/**
	 * 获取退款理由
	 */
	function getRefundReason() {
		ordeRefundReason().then(res => {
			RefundArray.value = res.data;
		})
	}

	/**
	 * 删除图片
	 *
	 */
	function DelPic(e) {
		let idx = e,
			pic = refund_reason_wap_img.value[idx];
		refund_reason_wap_img.value.splice(idx, 1);
		refund_reason_wap_img.value = refund_reason_wap_img.value;
	}

	/**
	 * 上传文件
	 *
	 */
	function uploadpic() {
		util.uploadImageOne('upload/image', function(res) {
			refund_reason_wap_img.value.push(res.data.url);
			refund_reason_wap_img.value = refund_reason_wap_img.value;
		});
	}

	/**
	 * 申请退货
	 */
	function subRefund(e) {
		if (isRes.value) return
		uni.showLoading({
			title: i18n.t(`申请中`)
		});
		let value = e.detail.value;
		//收集form表单
		if (!value.refund_reason_wap_explain) return util.Tips({
			title: i18n.t(`请输入备注`)
		});
		let cartInfo = refundCartInfo.value;
		if (cartInfo.length === 1) {
			cartIds.value = [{
				cart_id: cartInfo[0].id,
				cart_num: refund_num_index.value + 1
			}]
		}
		isRes.value = true
		returnGoodsSubmit(id.value, {
			text: RefundArray.value[index.value] || '',
			refund_reason_wap_explain: value.refund_reason_wap_explain,
			refund_reason_wap_img: refund_reason_wap_img.value.join(','),
			refund_type: returnGoods.value == 1 ? 2 : 1,
			uni: orderId.value,
			cart_ids: cartIds.value
		}).then(res => {
			uni.hideLoading();
			isRes.value = false
			return util.Tips({
				title: i18n.t(`申请成功`),
				icon: 'success'
			}, {
				tab: 5,
				url: '/pages/users/user_return_list/index?isT=1'
			});
		}).catch(err => {
			uni.hideLoading();
			isRes.value = false
			return util.Tips({
				title: err
			});
		})
	}
	function bindPickerChange(e) {
		index.value = e.detail.value;
	}
	function returnGoodsChange(e) {
		returnGoods.value = e.detail.value;
	}
	function returnGoodsNum(e) {
		refund_num_index.value = Number(e.detail.value);
	}
</script>

<style scoped lang="scss">
	.apply-return .list {
		background-color: #fff;
		margin-top: 18rpx;
	}

	.apply-return .list .item {
		margin-left: 30rpx;
		padding-right: 30rpx;
		min-height: 90rpx;
		border-bottom: 1rpx solid #eee;
		font-size: 30rpx;
		color: #333;
	}

	.apply-return .list .item .num {
		color: #282828;
		width: 427rpx;
		text-align: right;
	}

	.apply-return .list .item .num .picker .reason {
		width: 385rpx;
	}

	.apply-return .list .item .num .picker .iconfont {
		color: #666;
		font-size: 30rpx;
		margin-top: 2rpx;
	}

	.apply-return .list .item.textarea {
		padding: 30rpx 30rpx 30rpx 0;
	}

	.apply-return .list .item textarea {
		height: 100rpx;
		font-size: 30rpx;
	}

	.apply-return .list .item .placeholder {
		color: #bbb;
	}

	.apply-return .list .item .title {
		height: 95rpx;
		width: 100%;
	}

	.apply-return .list .item .title .tip {
		font-size: 30rpx;
		color: #bbb;
	}

	.apply-return .list .item .upload {
		padding-bottom: 36rpx;
	}

	.apply-return .list .item .upload .pictrue {
		margin: 22rpx 23rpx 0 0;
		width: 156rpx;
		height: 156rpx;
		position: relative;
		font-size: 24rpx;
		color: #bbb;
	}

	.apply-return .list .item .upload .pictrue:nth-of-type(4n) {
		margin-right: 0;
	}

	.apply-return .list .item .upload .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 3rpx;
	}

	.apply-return .list .item .upload .pictrue .icon-guanbi1 {
		position: absolute;
		font-size: 45rpx;
		top: -10rpx;
		right: -10rpx;
	}

	.apply-return .list .item .upload .pictrue .icon-icon25201 {
		color: #bfbfbf;
		font-size: 50rpx;
	}

	.apply-return .list .item .upload .pictrue:nth-last-child(1) {
		border: 1rpx solid #ddd;
		box-sizing: border-box;
	}

	.apply-return .returnBnt {
		font-size: 32rpx;
		color: #fff;
		width: 690rpx;
		height: 86rpx;
		border-radius: 50rpx;
		text-align: center;
		line-height: 86rpx;
		margin: 43rpx auto;
	}

	.goodsStyle .text .name {
		align-self: flex-start;
	}

	.list :deep(.uni-input-input){
		text-align: right;
	}

	.acea-row {
		flex-wrap: nowrap;
	}

	.upload {
		flex-wrap: wrap;
	}
</style>
