<template>
	<view :style="colorStyle">
		<view class="acea-row nav">
			<view class="acea-row row-center-wrapper" :class="{ on: nav === 1 }" @click="navTab(1)">{{ $t(`发票记录`) }}</view>
			<view class="acea-row row-center-wrapper" :class="{ on: nav === 2 }" @click="navTab(2)">{{ $t(`抬头管理`) }}</view>
		</view>
		<view v-show="nav === 1" class="record-wrapper">
			<view v-for="item in orderList" :key="item.id" class="item">
				<view class="item-hd acea-row">
					<image class="image" :src="item.order.cartInfo[0].productInfo.image"></image>
					<view class="text">{{ item.order.cartInfo[0].productInfo.store_name + item.order.cartInfo[0].productInfo.attrInfo.suk || '' }}</view>
				</view>
				<view class="item-bd acea-row row-between-wrapper">
					<view>
						<view class="name">{{ item.header_type === 1 ? $t(`个人`) : $t(`企业`) }}{{ item.type === 1 ? $t(`普通`) : $t(`专用`) }}{{ $t(`发票`) }}</view>
						<view>{{ $t(`申请时间`) }} {{ item.add_time }}</view>
					</view>
					<view class="money">
						{{ $t(`￥`) }}
						<text class="num">{{ item.order.pay_price }}</text>
					</view>
				</view>
				<view class="item-ft acea-row row-between-wrapper">
					<view>{{ item.is_invoice ? $t(`已开票`) : $t(`未开票`) }}</view>
					<view class="acea-row row-center-wrapper">
						<view class="link mr20" @click="getInvoiceLink(item.id)" v-if="item.is_invoice == 1 && item.unique_num != '' && item.red_invoice_num == ''">复制</view>
						<navigator class="link" :url="`/pages/users/user_invoice_order/index?order_id=${item.order.order_id}`">{{ $t(`查看详情`) }}</navigator>
					</view>
				</view>
			</view>
			<view v-show="page === 2 && !orderList.length" class="nothing">
				<image :src="imgHost + '/statics/images/noInvoice.png'"></image>
				<view>{{ $t(`没有发票信息哟~`) }}</view>
			</view>
		</view>
		<view v-show="nav === 2">
			<view v-if="invoiceList.length" class="list">
				<template v-for="item in invoiceList">
					<view v-if="item.type === 1 || (item.type === 2 && specialInvoice)" :key="item.id" class="item">
						<view class="acea-row item-hd">
							<view class="acea-row row-middle">
								<view class="name">{{ item.name }}</view>
								<view v-if="item.is_default" class="label">{{ $t(`默认`) }}</view>
							</view>
							<view class="type" :class="{ special: item.type === 2 }">
								{{ item.type === 1 && item.header_type === 1 ? $t(`个人普通发票`) : item.type === 1 && item.header_type === 2 ? $t(`企业普通发票`) : $t(`企业专用发票`) }}
							</view>
						</view>
						<view class="item-bd">
							<view class="cell">{{ $t(`联系邮箱`) }} {{ item.email }}</view>
							<view v-if="item.header_type === 2" class="cell">{{ $t(`企业税号`) }} {{ item.duty_number }}</view>
							<view v-if="item.header_type === 1 && item.drawer_phone" class="cell">{{ $t(`联系电话`) }} {{ item.drawer_phone }}</view>
						</view>
						<view class="acea-row row-right item-ft">
							<view class="btn" @click="editInvoice(item.id)">
								<text class="iconfont icon-bianji"></text>
								{{ $t(`编辑`) }}
							</view>
							<view class="btn" @click="deleteInvoice(item.id)">
								<text class="iconfont icon-shanchu"></text>
								{{ $t(`删除`) }}
							</view>
						</view>
					</view>
				</template>
			</view>
			<view v-show="page === 2 && !invoiceList.length" class="nothing">
				<image :src="imgHost + '/statics/images/noInvoice.png'"></image>
				<view>{{ $t(`没有发票信息哟~`) }}</view>
			</view>
			<navigator class="add-link" :url="`/pages/users/user_invoice_form/index?specialInvoice=${specialInvoice}`">
				<text class="iconfont icon-fapiao"></text>
				{{ $t(`添加新发票`) }}
			</navigator>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script>
import home from '@/components/home';
import { mapGetters } from 'vuex';
import { invoiceList, invoiceDelete, getUserInfo } from '@/api/user.js';
import { orderInvoiceList, getInvoiceLink } from '@/api/order.js';
import colors from '@/mixins/color.js';
import { HTTP_REQUEST_URL } from '@/config/app';
export default {
	components: {
		home
	},
	mixins: [colors],
	data() {
		return {
			imgHost: HTTP_REQUEST_URL,
			orderList: [],
			invoiceList: [],
			nav: 1, // 1：发票记录 2：抬头管理
			page: 1,
			limit: 30,
			loading: false,
			finished: false,
			specialInvoice: true
		};
	},
	watch: {
		nav: {
			immediate: true,
			handler(value) {
				this.page = 1;
				switch (value) {
					case 1:
						this.orderList = [];
						this.getOrderList();
						break;
					case 2:
						this.invoiceList = [];
						this.getInvoiceList();
						break;
				}
			}
		}
	},
	computed: mapGetters(['isLogin']),
	onLoad(option) {
		if (option.from === 'invoice_form') {
			this.nav = 2;
		}
		this.getUserInfo();
	},
	methods: {
		getInvoiceLink(id) {
			uni.showLoading({
				title: '复制中'
			});
			getInvoiceLink(id).then((res) => {
				// #ifdef APP-PLUS
				plus.runtime.openWeb(res.data.downloadBase64.pdfUrl);
				// #endif
				// #ifndef APP-PLUS
				uni.setClipboardData({
					data: res.data.downloadBase64.pdfUrl,
					success: () =>
						uni.showToast({
							title: '链接已复制'
						})
				});
				// #endif
			});
		},
		getUserInfo() {
			getUserInfo().then((res) => {
				const { special_invoice } = res.data;
				this.specialInvoice = special_invoice;
			});
		},
		// 菜单切换
		navTab(nav) {
			if (this.nav !== nav) {
				this.nav = nav;
			}
		},
		// 记录列表
		getOrderList() {
			uni.showLoading({
				title: this.$t(`加载中`)
			});
			orderInvoiceList({
				page: this.page,
				limit: this.limit
			})
				.then((res) => {
					const { data } = res;
					uni.hideLoading();
					this.orderList = this.orderList.concat(data);
					this.finished = data.length < this.limit;
					this.page += 1;
				})
				.catch((err) => {
					uni.showToast({
						title: err.msg,
						icon: 'none'
					});
				});
		},
		// 发票列表
		getInvoiceList() {
			uni.showLoading({
				title: this.$t(`加载中`)
			});
			invoiceList({
				page: this.page,
				limit: this.limit
			})
				.then((res) => {
					const { data } = res;
					uni.hideLoading();
					this.invoiceList = this.invoiceList.concat(data);
					this.finished = data.length < this.limit;
					this.page += 1;
				})
				.catch((err) => {
					uni.showToast({
						title: err.msg,
						icon: 'none'
					});
				});
		},
		// 编辑发票
		editInvoice(id) {
			uni.navigateTo({
				url: `/pages/users/user_invoice_form/index?id=${id}`
			});
		},
		// 删除发票
		deleteInvoice(id) {
			let that = this;
			uni.showModal({
				content: that.$t(`删除该发票？`),
				confirmColor: '#E93323',
				success(res) {
					if (res.confirm) {
						invoiceDelete(id)
							.then(() => {
								that.$util.Tips(
									{
										title: that.$t(`删除成功`),
										icon: 'success'
									},
									() => {
										let index = that.invoiceList.findIndex((value) => {
											return value.id == id;
										});
										that.invoiceList.splice(index, 1);
									}
								);
							})
							.catch((err) => {
								return that.$util.Tips({
									title: err
								});
							});
					}
				}
			});
		}
	}
};
</script>

<style lang="scss" scoped>
.mr20 {
	margin-right: 20rpx;
}
.nav {
	position: fixed;
	top: 0;
	left: 0;
	z-index: 9;
	width: 100%;
	height: 90rpx;
	background-color: #ffffff;
}

.nav .acea-row {
	flex: 1;
	border-top: 3rpx solid transparent;
	border-bottom: 3rpx solid transparent;
	font-size: 30rpx;
	color: #282828;
}

.nav .on {
	border-bottom-color: var(--view-theme);
	color: var(--view-theme);
}

.list {
	padding: 14rpx 32rpx;
	margin-top: 90rpx;
	padding-bottom: 180rpx;
}

.list .item {
	padding: 28rpx 32rpx;
	background-color: #ffffff;
}

.list .item ~ .item {
	margin-top: 14rpx;
}

.list .item-hd .acea-row {
	flex: 1;
	min-width: 0;
}

.list .name {
	font-weight: 600;
	font-size: 30rpx;
	color: #282828;
}

.list .label {
	width: 56rpx;
	height: 28rpx;
	border: 1rpx solid var(--view-theme);
	margin-left: 18rpx;
	font-size: 20rpx;
	line-height: 26rpx;
	text-align: center;
	color: var(--view-theme);
}

.list .type {
	// width: 172rpx;
	height: 42rpx;
	margin-left: 30rpx;
	background-color: #fcf0e0;
	font-size: 24rpx;
	line-height: 42rpx;
	text-align: center;
	color: #d67300;
	padding: 0 8rpx;
}

.list .type.special {
	background-color: #fde9e7;
	color: #e93323;
}

.list .item-bd {
	margin-top: 18rpx;
}

.list .cell {
	font-size: 26rpx;
	color: #666666;
}

.list .cell ~ .cell {
	margin-top: 12rpx;
}

.list .item-ft {
	margin-top: 11rpx;
	.copy {
		margin-right: 20rpx;
		font-size: 24rpx;
	}
}

.list .btn {
	font-size: 26rpx;
	color: #282828;
}

.list .btn ~ .btn {
	margin-left: 35rpx;
}

.list .btn .iconfont {
	margin-right: 10rpx;
	font-size: 24rpx;
	color: #000000;
}

.add-link {
	position: fixed;
	right: 30rpx;
	bottom: 53rpx;
	left: 30rpx;
	height: 86rpx;
	border-radius: 43rpx;
	background-color: var(--view-theme);
	font-size: 30rpx;
	line-height: 86rpx;
	text-align: center;
	color: #ffffff;

	.iconfont {
		margin-right: 14rpx;
		font-size: 28rpx;
	}
}

.nothing {
	margin-top: 254rpx;
	font-size: 26rpx;
	text-align: center;
	color: #999999;

	.image {
		width: 400rpx;
		height: 260rpx;
		margin-bottom: 20rpx;
	}
}

.record-wrapper {
	margin-top: 110rpx;

	.item {
		padding-right: 30rpx;
		padding-left: 30rpx;
		border-radius: 6rpx;
		margin: 30rpx;
		background-color: #ffffff;

		.item-hd {
			padding-top: 36rpx;
			padding-bottom: 36rpx;

			.image {
				width: 78rpx;
				height: 78rpx;
				border-radius: 6rpx;
			}

			.text {
				flex: 1;
				display: -webkit-box;
				-webkit-box-orient: vertical;
				-webkit-line-clamp: 2;
				overflow: hidden;
				margin-left: 24rpx;
				font-size: 26rpx;
				line-height: 37rpx;
				color: #282828;
			}
		}

		.item-bd {
			padding: 26rpx 30rpx 25rpx 32rpx;
			border-radius: 20rpx;
			background-color: #f5f6f7;
			font-size: 26rpx;
			line-height: 37rpx;
			color: #818181;

			.name {
				margin-bottom: 8rpx;
				font-weight: bold;
				font-size: 26rpx;
				color: #282828;
			}

			.money {
				font-weight: bold;
				font-size: 24rpx;
				color: #282828;

				.num {
					font-size: 32rpx;
				}
			}
		}

		.item-ft {
			padding-top: 30rpx;
			padding-bottom: 30rpx;
			font-weight: bold;
			font-size: 28rpx;
			color: #282828;

			.link {
				width: 150rpx;
				height: 57rpx;
				border: 1rpx solid #707070;
				border-radius: 29rpx;
				font-weight: normal;
				font-size: 26rpx;
				line-height: 57rpx;
				text-align: center;
				color: #282828;
			}
		}
	}
}
</style>
