<template>
	<view :style="colorStyle">
		<form @submit="subRefund">
			<view class='apply-return'>
				<view class='goodsStyle acea-row row-between' v-for="(item,index) in orderInfo.cartInfo" :key="index">
					<view class='pictrue'>
						<image :src='item.productInfo.image'></image>
					</view>
					<view class='text acea-row row-between'>
						<view class='name line2'>{{item.productInfo.store_name}}</view>
						<view class='money' v-if="item.productInfo.attrInfo">
							<view>￥{{item.productInfo.attrInfo.price}}</view>
							<view class='num'>x{{item.cart_num}}</view>
						</view>
						<view class='money' v-else>
							<view>￥{{item.productInfo.price}}</view>
							<view class='num'>x{{item.cart_num}}</view>
						</view>
					</view>
				</view>
				<view class='list' v-if="orderInfo.total_num">
					<view class='item acea-row row-between-wrapper'>
						<view>退货件数</view>
						<view class='num'
							v-if="orderInfo.total_num == 1 || orderInfo.cartInfo.length > 1 || orderInfo.pid > 0">
							{{orderInfo.total_num}}
						</view>
						<input type="number" v-model="refund_num" @input="inputNumber" name="refund_num" v-else />
					</view>
					<view class='item acea-row row-between-wrapper'>
						<view>退款金额</view>
						<view v-if="orderInfo.total_num == 1 || orderInfo.cartInfo.length > 1 || orderInfo.pid > 0" class='num'>￥{{orderInfo.pay_price}}</view>
						<view v-else class='num'>￥{{(one_price * (refund_num || 1)/10000).toFixed(2)}}</view>
					</view>
					<view class='item acea-row row-between-wrapper'
						v-if="orderInfo._status && orderInfo._status._type !== 1 && orderInfo.delivery_type !== 'fictitious' && orderInfo.virtual_type <= 0">
						<view>退款类型</view>
						<picker class='num' @change="returnGoodsChange" :value="returnGoods" :range="returnGoodsData">
							<view class="picker acea-row row-between-wrapper">
								<view class='reason'>{{returnGoodsData[returnGoods]}}</view>
								<text class='iconfont icon-jiantou'></text>
							</view>
						</picker>
					</view>
					<view class='item acea-row row-between-wrapper'>
						<view>退款原因</view>
						<picker class='num' @change="bindPickerChange" :value="index" :range="RefundArray">
							<view class="picker acea-row row-between-wrapper">
								<view class='reason'>{{RefundArray[index]}}</view>
								<text class='iconfont icon-jiantou'></text>
							</view>
						</picker>
					</view>
					<view class='item textarea acea-row row-between'>
						<view>备注说明</view>
						<textarea placeholder='填写备注信息，100字以内' class='num' name="refund_reason_wap_explain"
							placeholder-class='填写备注信息，100字以内'></textarea>
					</view>
					<view class='item acea-row row-between'>
						<view class='title acea-row row-between-wrapper'>
							<view>上传凭证</view>
							<view class='tip'>( 最多可上传3张 )</view>
						</view>
						<view class='upload acea-row row-middle'>
							<view class='pictrue' v-for="(item,index) in refund_reason_wap_img" :key="index">
								<image :src='item'></image>
								<view class='iconfont icon-guanbi1 font-num' @tap='DelPic(index)'></view>
							</view>
							<view class='pictrue acea-row row-center-wrapper row-column' @tap='uploadpic'
								v-if="refund_reason_wap_img.length < 3">
								<text class='iconfont icon-icon25201'></text>
								<view>上传凭证</view>
							</view>
						</view>
					</view>
				</view>
				<button class='returnBnt bg-color' form-type="submit">申请退款</button>
			</view>
		</form>
		<!-- #ifdef MP -->
		<authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize>
		<!-- #endif -->
	</view>
</template>
<script>
	import {
		ordeRefundReason,
		orderRefundVerify,
		getRefundOrderDetail
	} from '@/api/order.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import colors from '@/mixins/color.js';
	export default {
		components: {
			// #ifdef MP
			authorize
			// #endif
		},
		mixins: [colors],
		data() {
			return {
				refund_reason_wap_img: [],
				orderInfo: {},
				RefundArray: [],
				returnGoodsData: ['仅退款', '退货并退款'],
				index: 0,
				returnGoods: 0,
				orderId: 0,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				cart_id: 0,
				refund_num: 0,
				one_price: 0,
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.getOrderInfo();
						this.getRefundReason();
					}
				},
				deep: true
			}
		},
		onLoad: function(options) {
			if (!options.orderId) return this.$util.Tips({
				title: '缺少订单id,无法退款'
			}, {
				tab: 3,
				url: 1
			});
			if (options.cart_id) this.cart_id = options.cart_id
			this.orderId = options.orderId;
			if (this.isLogin) {
				this.getOrderInfo();
				this.getRefundReason();
			} else {
				toLogin();
			}
		},
		methods: {
			onLoadFun: function() {
				this.getOrderInfo();
				this.getRefundReason();
			},
			/**
			 * 获取订单详情
			 * 
			 */
			getOrderInfo: function() {
				let that = this;
				getRefundOrderDetail(that.orderId, that.cart_id).then(res => {
					that.$set(that, 'orderInfo', res.data);
					that.$set(that, 'refund_num', res.data.total_num);
					that.one_price = (Number(res.data.cartInfo[0].truePrice) + Number(res.data.cartInfo[0]
						.one_postage_price)) * 10000
				});
			},
			/**
			 * 获取退款理由
			 */
			getRefundReason: function() {
				let that = this;
				ordeRefundReason().then(res => {
					that.$set(that, 'RefundArray', res.data);
				})
			},

			/**
			 * 删除图片
			 * 
			 */
			DelPic: function(e) {
				let index = e,
					that = this,
					pic = this.refund_reason_wap_img[index];
				that.refund_reason_wap_img.splice(index, 1);
				that.$set(that, 'refund_reason_wap_img', that.refund_reason_wap_img);
			},

			/**
			 * 上传文件
			 * 
			 */
			uploadpic: function() {
				let that = this;
				this.$util.uploadImageOne('upload/image', function(res) {
					that.refund_reason_wap_img.push(res.data.url);
					that.$set(that, 'refund_reason_wap_img', that.refund_reason_wap_img);
				});
			},
			checkRate(input) {
				var re = /^[0-9]+?[0-9]*/; //判断字符串是否为数字//判断正整数/[1−9]+[0−9]∗]∗/;//判断字符串是否为数字//判断正整数/[1−9]+[0−9]∗]∗/
				if (!re.test(nubmer)) {
					return this.$util.Tips({
						title: '请输入整数'
					});
				}
			},
			inputNumber(e) {
				let v = e.detail.value
				v.indexOf('.') > -1 ? v = v.split('.')[0] : ''
				if (v > this.orderInfo.total_num) {
					this.$nextTick(() => {
						this.refund_num = this.orderInfo.total_num
					})
				} else {
					this.$nextTick(() => {
						this.refund_num = v
					})
				}
			},
			/**
			 * 申请退货
			 */
			subRefund: function(e) {
				let that = this,
					value = e.detail.value;
				//收集form表单
				if (!value.refund_reason_wap_explain) return this.$util.Tips({
					title: '请输入备注说明'
				});
				if (that.orderInfo.total_num > 1 && that.orderInfo.cartInfo.length == 1 && !that.refund_num)
				return this.$util.Tips({
						title: '请输入退货数量'
					});
				orderRefundVerify({
					text: that.RefundArray[that.index] || '',
					refund_reason_wap_explain: value.refund_reason_wap_explain,
					refund_reason_wap_img: that.refund_reason_wap_img.join(','),
					refund_type: this.returnGoods ? 2 : 1,
					uni: that.orderId,
					cart_id: that.cart_id,
					refund_num: that.refund_num,
				}).then(res => {
					return this.$util.Tips({
						title: '申请成功',
						icon: 'success'
					}, {
						tab: 5,
						url: '/pages/users/user_return_list/index?isT=1'
					});
				}).catch(err => {
					return this.$util.Tips({
						title: err
					});
				})
			},
			bindPickerChange: function(e) {
				this.$set(this, 'index', e.detail.value);
			},
			returnGoodsChange: function(e) {
				this.$set(this, 'returnGoods', e.detail.value);
			}
		}
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

	.list /deep/ .uni-input-input {
		text-align: right;
	}
</style>
