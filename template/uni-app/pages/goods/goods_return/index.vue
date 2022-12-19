<template>
	<view :style="colorStyle">
		<form @submit="subRefund">
			<view class='apply-return'>
				<view class='goodsStyle acea-row row-between' v-for="(item,index) in refundCartInfo" :key="index">
					<view class='pictrue'>
						<image :src='item.productInfo.attrInfo?item.productInfo.attrInfo.image:item.productInfo.image'>
						</image>
					</view>
					<view class='text acea-row row-between'>
						<view class='name line2'>{{item.productInfo.store_name}}</view>
						<view class='money'>
							<view>{{$t(`￥`)}}{{item.truePrice}}</view>
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
						<picker class='num' @change="returnGoodsChange" :value="returnGoods" :range="returnGoodsData">
							<view class="picker acea-row row-between-wrapper">
								<view class='reason'>{{returnGoodsData[returnGoods]}}</view>
								<text class='iconfont icon-jiantou'></text>
							</view>
						</picker>
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
							<view class='tip'>{{$t(`上传图片`)}}</view>
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
<script>
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
				id: 0,
				cartIds: [],
				refund_reason_wap_img: [],
				status: {},
				RefundArray: [],
				refundCartInfo: [],
				returnGoodsData: [this.$t(`仅退款`), this.$t(`退货并退款`)],
				refund_total_num: 0,
				index: 0,
				returnGoods: 0,
				orderId: 0,
				refundNumData: [],
				refund_num_index: 0
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.refundGoodsInfo();
						this.getRefundReason();
					}
				},
				deep: true
			}
		},
		onLoad(options) {
			this.orderId = options.orderId;
			this.id = options.id;
			if (options.cartIds) {
				this.cartIds = JSON.parse(options.cartIds) || []
			}
			if (this.isLogin) {
				this.refundGoodsInfo();
				this.getRefundReason();
			} else {
				toLogin();
			}
		},
		methods: {
			refundGoodsInfo() {
				postRefundGoods({
					id: this.id,
					cart_ids: this.cartIds
				}).then(res => {
					let data = res.data;
					this.status = data._status;
					this.refundCartInfo = data.cartInfo;
					this.refundCartInfo.forEach(item => {
						this.refund_total_num = this.$util.$h.Add(this.refund_total_num, item.cart_num)
					})
					this.refundNumData = Array(this.refund_total_num).fill(0).map((e, i) => i + 1)
					this.refundCartInfo.length === 1 ? this.refund_num_index = this.refundNumData.length - 1 : ''
				}).catch(err => {
					return this.$util.Tips({
						title: err
					});
				})
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

			/**
			 * 申请退货
			 */
			subRefund: function(e) {
				uni.showLoading({
					title: this.$t(`申请中`)
				});
				let that = this,
					value = e.detail.value;
				//收集form表单
				if (!value.refund_reason_wap_explain) return this.$util.Tips({
					title: this.$t(`请输入备注`)
				});
				let cartInfo = this.refundCartInfo;
				if (cartInfo.length === 1) {
					this.cartIds = [{
						cart_id: cartInfo[0].id,
						cart_num: this.refund_num_index + 1
					}]
				}
				returnGoodsSubmit(this.id, {
					text: that.RefundArray[that.index] || '',
					refund_reason_wap_explain: value.refund_reason_wap_explain,
					refund_reason_wap_img: that.refund_reason_wap_img.join(','),
					refund_type: this.returnGoods ? 2 : 1,
					uni: that.orderId,
					cart_ids: this.cartIds
				}).then(res => {
					uni.hideLoading();
					return this.$util.Tips({
						title: this.$t(`申请成功`),
						icon: 'success'
					}, {
						tab: 5,
						url: '/pages/users/user_return_list/index?isT=1'
					});
				}).catch(err => {
					uni.hideLoading();
					return this.$util.Tips({
						title: err
					});
				})
			},
			bindPickerChange(e) {
				this.$set(this, 'index', e.detail.value);
			},
			returnGoodsChange(e) {
				this.$set(this, 'returnGoods', e.detail.value);
			},
			returnGoodsNum(e) {
				this.$set(this, 'refund_num_index', Number(e.detail.value));
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
	.acea-row {
		flex-wrap: nowrap;
	}
	.upload {
		flex-wrap: wrap;
	}
</style>
