<template>
	<view :style="colorStyle">
		<form @submit="subRefund">
			<view class='apply-return'>
				<view class='goodsStyle acea-row row-between' v-for="(item,index) in orderInfo.cart_info" :key="index">
					<view class='pictrue'>
						<image :src='item.productInfo.attrInfo?item.productInfo.attrInfo.image:item.productInfo.image'>
						</image>
					</view>
					<view class='text acea-row row-between'>
						<view class='name line2'>{{item.productInfo.store_name}}</view>
						<view class='money'>
							<view>{{$t(`￥`)}}{{item.truePrice }}</view>
							<view class='num'>x{{item.cart_num }}</view>
						</view>
					</view>
				</view>
				<view class='list'>
					<view class='item acea-row row-between-wrapper' v-if="expressList.length">
						<view>{{$t(`快递公司`)}}</view>
						<picker class='num' @change="bindPickerChange" :value="seIndex" :range="expressList"
							range-key="name">
							<view class="picker acea-row row-between-wrapper">
								<view class='reason'>{{expressList[seIndex].name}}</view>
								<text class='iconfont icon-jiantou'></text>
							</view>
						</picker>
					</view>
					<view class='item acea-row row-between-wrapper'>
						<view>{{$t(`快递单号`)}}</view>
						<input class="input" type="text" :placeholder="$t(`填写快递单号`)" placeholder-class='placeholder'
							v-model="refundInfo.refund_express" />
					</view>
					<view class='item acea-row row-between-wrapper'>
						<view>{{$t(`联系电话`)}}</view>
						<input class="input" type="number" :placeholder="$t(`请输入手机号`)" placeholder-class='placeholder'
							v-model="refundInfo.refund_phone" />
					</view>
					<view class='item textarea acea-row row-between'>
						<view>{{$t(`备注说明`)}}</view>
						<textarea :placeholder='$t(`填写备注信息，100字以内`)' class='num'
							v-model="refundInfo.refund_explain"></textarea>
					</view>
					<view class='item acea-row row-between'>
						<view class='title acea-row row-between-wrapper'>
							<view>{{$t(`上传图片`)}}</view>
							<view class='tip'>{{$t(`最多可上传3张`)}}</view>
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
				<button class='returnBnt bg-color' form-type="submit">{{$t(`提交`)}}</button>
			</view>
		</form>
	</view>
</template>
<script setup>
	import { ref, watch } from 'vue';
	import { onLoad } from '@dcloudio/uni-app';
	import { storeToRefs } from 'pinia';
	import {
		refundOrderDetail,
		refundExpress
	} from '@/api/order.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import { useAppStore } from '@/store/modules/app';
	import { useColorTheme } from '@/composables/useColorTheme';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';

	const { colorStyle } = useColorTheme();

	const appStore = useAppStore();
	const { isLogin } = storeToRefs(appStore);

	const expressList = ref([]);
	const orderInfo = ref({});
	const seIndex = ref(0);
	const refund_reason_wap_img = ref([]);
	const refundInfo = ref({
		refund_express: '',
		refund_phone: '',
		refund_explain: '',
		id: '',
		refund_express_name: '',
		refund_img: ''
	});
	// 原代码未在 data 声明、直接挂到实例上的字段
	let orderId;

	watch(isLogin, (newV, oldV) => {
		if (newV) {
			getOrderInfo();
		}
	}, { deep: true });

	onLoad((options) => {
		orderId = options.orderId;
		if (isLogin.value) {
			getOrderInfo();
		} else {
			toLogin();
		}
	});

	/**
	 * 申请退货
	 */
	function subRefund(e) {
		if (!refundInfo.value.refund_express) return util.Tips({
			title: i18n.t(`填写快递单号`)
		});
		if (!refundInfo.value.refund_phone) return util.Tips({
			title: i18n.t(`请输入手机号`)
		});
		if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(refundInfo.value.refund_phone)) return util.Tips({
			title: i18n.t(`请输入正确的手机号码`)
		});
		refundInfo.value.refund_express_name = expressList.value[seIndex.value].name;
		refundInfo.value.refund_img = refund_reason_wap_img.value.join(',');
		refundExpress(refundInfo.value).then(res => {
			return util.Tips({
				title: res.msg,
				icon: 'success'
			}, {
				tab: 5,
				url: '/pages/users/user_return_list/index?isT=1'
			});
		}).catch(err => {
			return util.Tips({
				title: err
			});
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
	 * 获取订单详情
	 *
	 */
	function getOrderInfo() {
		refundOrderDetail(orderId).then(res => {
			orderInfo.value = res.data;
			expressList.value = res.data.express_list;
			refundInfo.value.id = res.data.id;
		});
	}
	function bindPickerChange(e) {
		seIndex.value = e.detail.value;
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

		.input {
			text-align: right;
		}
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
		font-size: 30rpx;
		text-align: right;
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
</style>
