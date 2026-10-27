<template>
	<view :style="colorStyle">
		<form @submit="formSubmit">
			<view class="panel">
				<view class="acea-row row-middle">
					<view>{{$t(`抬头类型`)}}</view>
					<radio-group name="header_type" @change="changeTitleType">
						<label>
							<radio class="disabled" value="1" :checked="header_type === '1'" /><text>{{$t(`个人`)}}</text>
						</label>
						<label>
							<radio value="2" :checked="header_type === '2'" /><text>{{$t(`企业`)}}</text>
						</label>
					</radio-group>
				</view>
				<!-- 仅企业 + 后台开启专用发票时可选专票（个人只能普票） -->
				<view class="acea-row row-middle" v-if="specialInvoice && header_type === '2'">
					<view>{{$t(`发票类型`)}}</view>
					<text class="w-fill" @click="callType">
						<text>{{ type === '2' ? $t(`增值税电子专用发票`) : $t(`增值税电子普通发票`) }} </text>
						<text class="iconfont icon-xiangyou"></text>
					</text>

				</view>
				<view class="acea-row row-middle">
					<view>{{$t(`发票抬头`)}}</view>
					<input name="name" :value="name" :placeholder="header_type === '1' ? $t(`需要开具发票的姓名`) : $t(`需要开具发票的企业名称`)" />
				</view>
				<view v-show="header_type === '2'" class="acea-row row-middle">
					<view>{{$t(`税号`)}}</view>
					<input name="duty_number" :value="duty_number" :placeholder="$t(`纳税人识别号`)" />
				</view>
				<view class="acea-row row-middle">
					<view>{{$t(`手机号`)}}</view>
					<input name="drawer_phone" :value="drawer_phone" :placeholder="$t(`您的手机号`)" />
				</view>
				<view class="acea-row row-middle">
					<view>{{$t(`邮箱`)}}</view>
					<input name="email" :value="email" :placeholder="$t(`您的联系邮箱`)" />
				</view>
			</view>
			<view v-show="type === '2'" class="panel">
				<view class="acea-row row-middle">
					<view>{{$t(`开户银行`)}}</view>
					<input name="bank" :value="bank" :placeholder="$t(`您的开户银行`)" />
				</view>
				<view class="acea-row row-middle">
					<view>{{$t(`银行账号`)}}</view>
					<input name="card_number" :value="card_number" :placeholder="$t(`您的银行账号`)" />
				</view>
				<view class="acea-row row-middle">
					<view>{{$t(`企业地址`)}}</view>
					<input name="address" :value="address" :placeholder="$t(`您所在的企业地址`)" />
				</view>
				<view class="acea-row row-middle">
					<view>{{$t(`企业电话`)}}</view>
					<input name="tell" :value="tell" :placeholder="$t(`您的企业电话`)" />
				</view>
			</view>
			<checkbox-group class="acea-row row-middle panel" name="is_default">
				<label>
					<checkbox :checked="is_default.length !== 0" backgroundColor="#ffffff" activeBackgroundColor="var(--view-theme, #E93323)" activeBorderColor="var(--view-theme, #E93323)" iconColor="#ffffff" /><text>{{$t(`设置为默认抬头`)}}</text>
				</label>
			</checkbox-group>
			<view class="button-section">
				<button class="button" form-type="submit">{{$t(`保存`)}}</button>
				<navigator :render-link="false" class="navigator" :url="backUrl" hover-class="none">{{$t(`取消`)}}</navigator>
			</view>
		</form>
		<view :class="{ mask: popupType }"></view>
		<view class="popup" :class="{ on: popupType }">
			<view class="title">{{$t(`发票类型选择`)}}<text class="iconfont icon-guanbi" @click="closeType"></text></view>
			<scroll-view scroll-y="true">
				<radio-group name="invoice-type" @change="changeType">
					<template v-for="item in invoiceTypeList" :key="item.type">
						<label v-if="item.value === '1' || item.value === '2' && specialInvoice"
							class="acea-row row-middle">
							<view class="text">
								<view>{{ $t(item.name) }}</view>
								<view class="info">{{ $t(item.info) }}</view>
							</view>
							<radio :value="item.value" :checked="item.value === type" />
						</label>
					</template>
				</radio-group>
			</scroll-view>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref, computed } from 'vue';
import { onLoad, onHide } from '@dcloudio/uni-app';
import home from '@/components/home';
import {
	invoiceSave,
	invoiceDetail
} from '@/api/user.js';
import { useColorTheme } from '@/composables/useColorTheme';
import i18n from '@/utils/lang.js';

const { colorStyle } = useColorTheme();

// ---------- data ----------
const invoiceTypeList = [{
		name: i18n.t(`增值税电子普通发票`),
		value: '1',
		info: i18n.t(`纸质发票开出后将以邮寄形式交付`)
	},
	{
		name: i18n.t(`增值税电子专用发票`),
		value: '2',
		info: i18n.t(`纸质发票开出后将以邮寄形式交付`)
	}
];
const id = ref(''); // 修改时为必须参数
const header_type = ref('1'); // 抬头类型1: 个人2： 企业
const type = ref('1'); // 发票类型1：普通2：专用
const drawer_phone = ref(''); // 开票人手机号
const name = ref(''); // 名称（发票抬头）
const duty_number = ref(''); // 税号（个人为非必需，企业是必需参数）
const tell = ref(''); // 公司注册电话
const address = ref(''); // 注册地址
const bank = ref(''); // 开户行
const card_number = ref(''); // 银行卡号
const is_default = ref([]); // 是否默认
const email = ref(''); // 邮箱
const popupType = ref(false);
const typeName = ref('');
const urlQuery = ref('');
const from = ref('');
/** 是否允许企业选择专用发票（由 BASIC_CONFIG / 路由参数共同决定） */
const specialInvoice = ref(false);
const order_id = ref('');
const basicConfigData = uni.getStorageSync('BASIC_CONFIG') || {};

/**
 * 配置开关真值判断（兼容 1/'1'/true/'true'）
 * @param {*} value
 * @return {boolean}
 */
function isConfigEnabled(value) {
	return value === true || value === 1 || value === '1' || value === 'true';
}

// ---------- computed ----------
const backUrl = computed(() => {
	switch (from.value) {
		case 'order_confirm':
			return `/pages/center/order/create/index${urlQuery.value}`;
			break;
		default:
			return '/pages/users/user_invoice_list/index?from=invoice_form';
			break;
	}
});

// ---------- 生命周期 ----------
onHide(() => {
	from.value = ''
});
onLoad((options) => {
	// 先读基础配置；路由参数可覆盖（下单/详情页会显式传入）
	specialInvoice.value = isConfigEnabled(basicConfigData.special_invoice_status);

	if (options.id) uni.setNavigationBarTitle({
		title: i18n.t(`编辑发票`)
	})
	for (let key in options) {
		switch (key) {
			case 'couponTitle':
			case 'new':
			case 'cartId':
			case 'pinkId':
			case 'couponId':
			case 'addressId':
				urlQuery.value += `${urlQuery.value ? '&' : '?'}${key}=${options[key]}`;
				break;
			case 'from':
				from.value = options[key];
				break;
			case 'header_type':
				header_type.value = options[key];
				break;
			case 'id':
				id.value = options[key];
				getInvoiceDetail();
				break;
			case 'specialInvoice':
				specialInvoice.value = isConfigEnabled(options[key]);
				break;
		}
	}
	if (options.order_id)
		order_id.value = options.order_id
	const invoiceItem = invoiceTypeList.find(item => item.value === type.value);
	typeName.value = invoiceItem.name;
});

// ---------- methods ----------
// 获取发票数据
function getInvoiceDetail() {
	uni.showLoading({
		title: i18n.t(`加载中`)
	});
	invoiceDetail(id.value).then(res => {
		uni.hideLoading();
		header_type.value = res.data.header_type.toString();
		type.value = res.data.type.toString();
		const invoiceItem = invoiceTypeList.find(item => item.value === type.value);
		typeName.value = invoiceItem.name;
		name.value = res.data.name;
		drawer_phone.value = res.data.drawer_phone;
		email.value = res.data.email;
		duty_number.value = res.data.duty_number;
		bank.value = res.data.bank;
		card_number.value = res.data.card_number;
		address.value = res.data.address;
		tell.value = res.data.tell;
		is_default.value = res.data.is_default ? [''] : [];
	}).catch(err => {
		uni.showToast({
			title: err,
			icon: 'none'
		});
	});
}
// 保存
function formSubmit(e) {
	const formData = e.detail.value;
	formData.type = type.value;
	if (formData.header_type === '1') {
		if (!formData.name) {
			return uni.showToast({
				title: i18n.t(`请输入需要开具发票的姓名`),
				icon: 'none'
			});
		}
		if (!formData.drawer_phone) {
			return uni.showToast({
				title: i18n.t(`请输入您的手机号`),
				icon: 'none'
			});
		}
		if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(formData.drawer_phone)) {
			return uni.showToast({
				title: i18n.t(`请正确输入您的手机号`),
				icon: 'none'
			});
		}
		if (!formData.email) {
			return uni.showToast({
				title: i18n.t(`请输入您的联系邮箱`),
				icon: 'none'
			});
		}
		if (!/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(formData.email)) {
			return uni.showToast({
				title: i18n.t(`请正确输入您的联系邮箱`),
				icon: 'none'
			});
		}
	}
	if (formData.header_type === '2') {
		if (formData.type === '1') {
			if (!formData.name) {
				return uni.showToast({
					title: i18n.t(`请输入需要开具发票的企业名称`),
					icon: 'none'
				});
			}
			if (!formData.duty_number) {
				return uni.showToast({
					title: i18n.t(`请输入纳税人识别号`),
					icon: 'none'
				});
			}
			if (!/[0-9A-HJ-NPQRTUWXY]{2}\d{6}[0-9A-HJ-NPQRTUWXY]{10}/.test(formData.duty_number)) {
				return uni.showToast({
					title: i18n.t(`请正确输入纳税人识别号`),
					icon: 'none'
				});
			}
			if (!formData.drawer_phone) {
				return uni.showToast({
					title: i18n.t(`请输入您的手机号`),
					icon: 'none'
				});
			}
			if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(formData.drawer_phone)) {
				return uni.showToast({
					title: i18n.t(`请正确输入您的手机号`),
					icon: 'none'
				});
			}
			if (!formData.email) {
				return uni.showToast({
					title: i18n.t(`请输入您的联系邮箱`),
					icon: 'none'
				});
			}
			if (!/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(formData.email)) {
				return uni.showToast({
					title: i18n.t(`请正确输入您的联系邮箱`),
					icon: 'none'
				});
			}
		}
		if (formData.type === '2') {
			if (!formData.name) {
				return uni.showToast({
					title: i18n.t(`请输入需要开具发票的企业名称`),
					icon: 'none'
				});
			}
			if (!formData.duty_number) {
				return uni.showToast({
					title: i18n.t(`请输入纳税人识别号`),
					icon: 'none'
				});
			}
			if (!/[0-9A-HJ-NPQRTUWXY]{2}\d{6}[0-9A-HJ-NPQRTUWXY]{10}/.test(formData.duty_number)) {
				return uni.showToast({
					title: i18n.t(`请正确输入纳税人识别号`),
					icon: 'none'
				});
			}
			if (!formData.drawer_phone) {
				return uni.showToast({
					title: i18n.t(`请输入您的手机号`),
					icon: 'none'
				});
			}
			if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(formData.drawer_phone)) {
				return uni.showToast({
					title: i18n.t(`请正确输入您的手机号`),
					icon: 'none'
				});
			}
			if (!formData.email) {
				return uni.showToast({
					title: i18n.t(`请输入您的联系邮箱`),
					icon: 'none'
				});
			}
			if (!/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(formData.email)) {
				return uni.showToast({
					title: i18n.t(`请正确输入您的联系邮箱`),
					icon: 'none'
				});
			}
			if (!formData.bank) {
				return uni.showToast({
					title: i18n.t(`请输入您的开户银行`),
					icon: 'none'
				});
			}
			if (!formData.card_number) {
				return uni.showToast({
					title: i18n.t(`请输入您的银行账号`),
					icon: 'none'
				});
			}
			if (!/^\d{16}|\d{19}$/.test(formData.card_number)) {
				return uni.showToast({
					title: i18n.t(`请正确输入您的银行账号`),
					icon: 'none'
				});
			}
			if (!formData.address) {
				return uni.showToast({
					title: i18n.t(`请输入您所在的企业地址`),
					icon: 'none'
				});
			}
			if (!formData.tell) {
				return uni.showToast({
					title: i18n.t(`请输入您的企业电话`),
					icon: 'none'
				});
			}
		}
	}
	formData.is_default = formData.is_default.length;
	formData.id = id.value;

	uni.showLoading({
		title: i18n.t(`保存中`)
	});
	invoiceSave(formData).then(res => {
		uni.showToast({
			title: res.msg,
			icon: 'success',
		});
		setTimeout(e => {
			switch (from.value) {
				case 'order_confirm':
					if (id.value) {
						uni.navigateTo({
							url: `/pages/center/order/create/index${urlQuery.value}&invoice_id=${id.value}&invoice_type=${formData.type}&header_type=${header_type.value}`
						})
					} else {
						uni.navigateTo({
							url: `/pages/center/order/create/index${urlQuery.value}&invoice_id=${res.data.id}&invoice_type=${formData.type}&header_type=${header_type.value}`
						})
					}
					break;
				case 'order_details': {
					// 订单详情已迁中台引擎，勿再跳旧 order_details（会请求已废弃的 order/detail）
					const invoiceId = id.value || (res.data && res.data.id) || '';
					uni.redirectTo({
						url: `/pages/center/order/detail/index?order_no=${order_id.value}&invoice_id=${invoiceId}&header_type=${header_type.value}`
					});
					break;
				}
				default:
					uni.navigateTo({
						url: '/pages/users/user_invoice_list/index?from=invoice_form'
					});
					break;
			}
		}, 1000)
	}).catch(err => {
		uni.showToast({
			title: err,
			icon: 'none'
		});
	});
}
// 调起发票类型弹窗
function callType() {
	if (header_type.value == 2) {
		popupType.value = true;
	} else {
		uni.showToast({
			title: i18n.t(`个人仅支持普通发票`),
			icon: 'none'
		});
	}
}
// 选择发票类型
function changeType(e) {
	const typeVal = e.detail.value,
		invoiceItem = invoiceTypeList.find(item => item.value === typeVal);
	if (typeVal === '2' && header_type.value === '1') {
		header_type.value = '2';
	}
	typeName.value = invoiceItem.name;
	type.value = typeVal;
	popupType.value = false;
}
// 关闭发票弹窗
function closeType() {
	popupType.value = false;
}
// 选择抬头类型
function changeTitleType(e) {
	header_type.value = e.detail.value;
	type.value = '1';
}
</script>

<style scoped lang="scss">
:deep(.disabled .uni-radio-input){
		background-color: #F8F8F8;
	}

	form {
		font-size: 28rpx;
		color: #282828;
	}

	form input,
	form radio-group {
		flex: 1;
		margin-left: 30rpx;
		text-align: right;
	}

	form input {
		font-size: 26rpx;
	}

	form label {
		margin-right: 50rpx;
	}

	form radio {
		margin-right: 8rpx;
	}

	form checkbox-group {
		height: 90rpx;
	}

	form checkbox {
		margin-right: 20rpx;
	}

	.panel {
		padding-right: 30rpx;
		padding-left: 30rpx;
		background-color: #FFFFFF;

		.w-fill {
			margin-left: auto;
			font-size: 26rpx;
		}
	}

	.panel~.panel {
		margin-top: 14rpx;
	}

	.panel .acea-row {
		height: 90rpx;
	}

	.panel .acea-row~.acea-row {
		border-top: 1rpx solid #EEEEEE;
	}

	.input-placeholder {
		font-size: 26rpx;
		color: #BBBBBB;
	}

	.icon-xiangyou {
		margin-left: 5rpx;
		font-size: 26rpx;
		color: #BFBFBF;
		margin-top: 2rpx;
	}

	.popup {
		position: fixed;
		bottom: 0;
		left: 0;
		z-index: 99;
		width: 100%;
		padding-bottom: 100rpx;
		border-top-left-radius: 16rpx;
		border-top-right-radius: 16rpx;
		background-color: #F5F5F5;
		overflow: hidden;
		transform: translateY(100%);
		transition: 0.3s;
	}

	.popup.on {
		transform: translateY(0);
	}

	.popup .title {
		position: relative;
		height: 137rpx;
		font-size: 32rpx;
		line-height: 137rpx;
		text-align: center;
	}

	.popup scroll-view {
		height: 466rpx;
		padding-right: 30rpx;
		padding-left: 30rpx;
		box-sizing: border-box;
	}

	.popup label {
		padding: 35rpx 30rpx;
		border-radius: 16rpx;
		margin-bottom: 20rpx;
		background-color: #FFFFFF;
	}

	.popup .text {
		flex: 1;
		min-width: 0;
		font-size: 28rpx;
		color: #282828;
	}

	.popup .info {
		margin-top: 10rpx;
		font-size: 22rpx;
		color: #909090;
	}

	.popup .icon-guanbi {
		position: absolute;
		top: 50%;
		right: 30rpx;
		z-index: 2;
		transform: translateY(-50%);
		font-size: 30rpx;
		color: #707070;
	}

	.popup .text .acea-row {
		display: inline-flex;
		max-width: 100%;
	}

	.popup .name {
		flex: 1;
		overflow: hidden;
		white-space: nowrap;
		text-overflow: ellipsis;
		font-size: 30rpx;
	}

	.popup .label {
		width: 56rpx;
		height: 28rpx;
		border: 1rpx solid #E93323;
		margin-left: 18rpx;
		font-size: 20rpx;
		line-height: 26rpx;
		text-align: center;
		color: #E93323;
	}

	.popup .type {
		width: 124rpx;
		height: 42rpx;
		margin-top: 14rpx;
		background-color: #FCF0E0;
		font-size: 24rpx;
		line-height: 42rpx;
		text-align: center;
		color: #D67300;
	}

	.popup .type.special {
		background-color: #FDE9E7;
		color: #E93323;
	}

	.button-section {
		/* position: fixed;
		bottom: 0;
		left: 0;
		width: 100%; */
		padding: 58rpx 30rpx;
	}

	.button-section .button {
		height: 86rpx;
		border-radius: 43rpx;
		background-color: var(--view-theme);
		font-size: 30rpx;
		line-height: 86rpx;
		color: #FFFFFF;
	}

	.button-section .navigator {
		height: 86rpx;
		border: 1rpx solid var(--view-theme);
		border-radius: 43rpx;
		margin-top: 26rpx;
		font-size: 30rpx;
		line-height: 86rpx;
		text-align: center;
		color: var(--view-theme);
	}
</style>