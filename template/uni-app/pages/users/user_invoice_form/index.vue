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
				<view class="acea-row row-middle">
					<view>{{$t(`发票类型`)}}</view>
					<input name="type" :value="type === '2' && header_type === '2' ? $t(`增值税电子专用发票`) : $t(`增值税电子普通发票`)" disabled
						@click="callType" />
					<text class="iconfont icon-xiangyou"></text>
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
					<checkbox :checked="is_default.length !== 0" /><text>{{$t(`设置为默认抬头`)}}</text>
				</label>
			</checkbox-group>
			<view class="button-section">
				<button class="button" form-type="submit">{{$t(`保存`)}}</button>
				<navigator class="navigator" :url="backUrl" hover-class="none">{{$t(`取消`)}}</navigator>
			</view>
		</form>
		<view :class="{ mask: popupType }"></view>
		<view class="popup" :class="{ on: popupType }">
			<view class="title">{{$t(`发票类型选择`)}}<text class="iconfont icon-guanbi" @click="closeType"></text></view>
			<scroll-view scroll-y="true">
				<radio-group name="invoice-type" @change="changeType">
					<template v-for="item in invoiceTypeList">
						<label v-if="item.value === '1' || item.value === '2' && specialInvoice" :key="item.type"
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

<script>
	import home from '@/components/home';
	import {
		invoiceSave,
		invoiceDetail
	} from '@/api/user.js';
	import colors from '@/mixins/color.js';
	export default {
		components: {
			home
		},
		mixins: [colors],
		data() {
			return {
				invoiceTypeList: [{
						name: this.$t(`增值税电子普通发票`),
						value: '1',
						info: this.$t(`纸质发票开出后将以邮寄形式交付`)
					},
					{
						name: this.$t(`增值税电子专用发票`),
						value: '2',
						info: this.$t(`纸质发票开出后将以邮寄形式交付`)
					}
				],
				id: '', // 修改时为必须参数
				header_type: '1', // 抬头类型1: 个人2： 企业
				type: '1', // 发票类型1：普通2：专用
				drawer_phone: '', // 开票人手机号
				name: '', // 名称（发票抬头）
				duty_number: '', // 税号（个人为非必需，企业是必需参数）
				tell: '', // 公司注册电话
				address: '', // 注册地址
				bank: '', // 开户行
				card_number: '', // 银行卡号
				is_default: [], // 是否默认
				email: '', // 邮箱
				popupType: false,
				typeName: '',
				urlQuery: '',
				from: '',
				specialInvoice: true,
				order_id: ''
			};
		},
		computed: {
			backUrl() {
				switch (this.from) {
					case 'order_confirm':
						return `/pages/goods/order_confirm/index${this.urlQuery}`;
						break;
					default:
						return '/pages/users/user_invoice_list/index?from=invoice_form';
						break;

				}
			}
		},
		onLoad(options) {
			for (let key in options) {
				switch (key) {
					case 'couponTitle':
					case 'new':
					case 'cartId':
					case 'pinkId':
					case 'couponId':
					case 'addressId':
						this.urlQuery += `${this.urlQuery ? '&' : '?'}${key}=${options[key]}`;
						break;
					case 'from':
						this.from = options[key];
						break;
					case 'header_type':
						this.header_type = options[key];
						break;
					case 'id':
						this.id = options[key];
						this.getInvoiceDetail();
						break;
					case 'specialInvoice':
						if (options[key] === 'false') {
							this.specialInvoice = false;
						}
						break;
				}
			}
			if (options.order_id)
				this.order_id = options.order_id
			const invoiceItem = this.invoiceTypeList.find(item => item.value === this.type);
			this.typeName = invoiceItem.name;
		},
		methods: {
			// 获取发票数据
			getInvoiceDetail() {
				uni.showLoading({
					title: this.$t(`加载中`)
				});
				invoiceDetail(this.id).then(res => {
					uni.hideLoading();
					this.header_type = res.data.header_type.toString();
					this.type = res.data.type.toString();
					const invoiceItem = this.invoiceTypeList.find(item => item.value === this.type);
					this.typeName = invoiceItem.name;
					this.name = res.data.name;
					this.drawer_phone = res.data.drawer_phone;
					this.email = res.data.email;
					this.duty_number = res.data.duty_number;
					this.bank = res.data.bank;
					this.card_number = res.data.card_number;
					this.address = res.data.address;
					this.tell = res.data.tell;
					this.is_default = res.data.is_default ? [''] : [];
				}).catch(err => {
					uni.showToast({
						title: err,
						icon: 'none'
					});
				});
			},
			// 保存
			formSubmit(e) {
				let that = this;
				const formData = e.detail.value;
				formData.type = this.type;
				if (formData.header_type === '1') {
					if (!formData.name) {
						return uni.showToast({
							title: that.$t(`请输入需要开具发票的姓名`),
							icon: 'none'
						});
					}
					if (!formData.drawer_phone) {
						return uni.showToast({
							title: that.$t(`请输入您的手机号`),
							icon: 'none'
						});
					}
					if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(formData.drawer_phone)) {
						return uni.showToast({
							title: that.$t(`请正确输入您的手机号`),
							icon: 'none'
						});
					}
					if (!formData.email) {
						return uni.showToast({
							title: that.$t(`请输入您的联系邮箱`),
							icon: 'none'
						});
					}
					if (!/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(formData.email)) {
						return uni.showToast({
							title: that.$t(`请正确输入您的联系邮箱`),
							icon: 'none'
						});
					}
				}
				if (formData.header_type === '2') {
					if (formData.type === '1') {
						if (!formData.name) {
							return uni.showToast({
								title: that.$t(`请输入需要开具发票的企业名称`),
								icon: 'none'
							});
						}
						if (!formData.duty_number) {
							return uni.showToast({
								title: that.$t(`请输入纳税人识别号`),
								icon: 'none'
							});
						}
						if (!/[0-9A-HJ-NPQRTUWXY]{2}\d{6}[0-9A-HJ-NPQRTUWXY]{10}/.test(formData.duty_number)) {
							return uni.showToast({
								title: that.$t(`请正确输入纳税人识别号`),
								icon: 'none'
							});
						}
						if (!formData.drawer_phone) {
							return uni.showToast({
								title: that.$t(`请输入您的手机号`),
								icon: 'none'
							});
						}
						if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(formData.drawer_phone)) {
							return uni.showToast({
								title: that.$t(`请正确输入您的手机号`),
								icon: 'none'
							});
						}
						if (!formData.email) {
							return uni.showToast({
								title: that.$t(`请输入您的联系邮箱`),
								icon: 'none'
							});
						}
						if (!/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(formData.email)) {
							return uni.showToast({
								title: that.$t(`请正确输入您的联系邮箱`),
								icon: 'none'
							});
						}
					}
					if (formData.type === '2') {
						if (!formData.name) {
							return uni.showToast({
								title: that.$t(`请输入需要开具发票的企业名称`),
								icon: 'none'
							});
						}
						if (!formData.duty_number) {
							return uni.showToast({
								title: that.$t(`请输入纳税人识别号`),
								icon: 'none'
							});
						}
						if (!/[0-9A-HJ-NPQRTUWXY]{2}\d{6}[0-9A-HJ-NPQRTUWXY]{10}/.test(formData.duty_number)) {
							return uni.showToast({
								title: that.$t(`请正确输入纳税人识别号`),
								icon: 'none'
							});
						}
						if (!formData.drawer_phone) {
							return uni.showToast({
								title: that.$t(`请输入您的手机号`),
								icon: 'none'
							});
						}
						if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(formData.drawer_phone)) {
							return uni.showToast({
								title: that.$t(`请正确输入您的手机号`),
								icon: 'none'
							});
						}
						if (!formData.email) {
							return uni.showToast({
								title: that.$t(`请输入您的联系邮箱`),
								icon: 'none'
							});
						}
						if (!/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(formData.email)) {
							return uni.showToast({
								title: that.$t(`请正确输入您的联系邮箱`),
								icon: 'none'
							});
						}
						if (!formData.bank) {
							return uni.showToast({
								title: that.$t(`请输入您的开户银行`),
								icon: 'none'
							});
						}
						if (!formData.card_number) {
							return uni.showToast({
								title: that.$t(`请输入您的银行账号`),
								icon: 'none'
							});
						}
						if (!/^\d{16}|\d{19}$/.test(formData.card_number)) {
							return uni.showToast({
								title: that.$t(`请正确输入您的银行账号`),
								icon: 'none'
							});
						}
						if (!formData.address) {
							return uni.showToast({
								title: that.$t(`请输入您所在的企业地址`),
								icon: 'none'
							});
						}
						if (!formData.tell) {
							return uni.showToast({
								title: that.$t(`请输入您的企业电话`),
								icon: 'none'
							});
						}
					}
				}
				formData.is_default = formData.is_default.length;
				formData.id = this.id;
				uni.showLoading({
					title: that.$t(`保存中`)
				});
				invoiceSave(formData).then(res => {
					uni.showToast({
						title: res.msg,
						icon: 'success',
						success() {
							switch (that.from) {
								case 'order_confirm':
									if (that.id) {
										uni.navigateTo({
											url: `/pages/goods/order_confirm/index${that.urlQuery}&invoice_id=${that.id}&invoice_type=${formData.type}`
										})
									} else {
										uni.navigateTo({
											url: `/pages/goods/order_confirm/index${that.urlQuery}&invoice_id=${res.data.id}&invoice_type=${formData.type}`
										})
									}
									break;
								case 'order_details':
									if (that.id) {
										uni.navigateTo({
											url: `/pages/goods/order_details/index?order_id=${that.order_id}&invoice_id=${that.id}`
										})
									} else {
										uni.navigateTo({
											url: `/pages/goods/order_details/index?order_id=${that.order_id}&invoice_id=${res.data.id}`
										})
									}
									break;
								default:
									uni.navigateTo({
										url: '/pages/users/user_invoice_list/index?from=invoice_form'
									});
									break;
							}

						}
					});
				}).catch(err => {
					uni.showToast({
						title: err,
						icon: 'none'
					});
				});
			},
			// 调起发票类型弹窗
			callType() {
				this.popupType = true;
			},
			// 选择发票类型
			changeType(e) {
				const type = e.detail.value,
					invoiceItem = this.invoiceTypeList.find(item => item.value === type);
				if (type === '2' && this.header_type === '1') {
					this.header_type = '2';
				}
				this.typeName = invoiceItem.name;
				this.type = type;
				this.popupType = false;
			},
			// 关闭发票弹窗
			closeType() {
				this.popupType = false;
			},
			// 选择抬头类型
			changeTitleType(e) {
				this.header_type = e.detail.value;
				this.type = '1';
			}
		}
	}
</script>

<style scoped>
	/deep/.disabled .uni-radio-input {
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
		margin-left: 25rpx;
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
		cursor: pointer;
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
