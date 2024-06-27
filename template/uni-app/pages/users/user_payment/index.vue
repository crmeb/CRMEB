<template>
	<view>
		<form :style="colorStyle">
			<view class="payment-top acea-row row-column row-center-wrapper">
				<span class="name">{{$t(`我的余额`)}}</span>
				<view class="pic">
					<span class="pic-font"><span class="num"> {{$t(`￥`)}}</span>{{ userinfo.now_money || 0 }}</span>
				</view>
			</view>
			<view class="payment">
				<view class="nav acea-row row-around row-middle" v-if="navRecharge.length > 1">
					<view class="item" :class="active==index?'on':''" v-for="(item,index) in navRecharge" :key="index"
						@click="navRecharges(index)">{{$t(item)}}</view>
				</view>
				<view class='tip picList' v-if='!active'>
					<view class="pic-box pic-box-color acea-row row-center-wrapper row-column"
						:class="activePic == index ? 'pic-box-color-active' : ''" v-for="(item, index) in picList" :key="index"
						@click="picCharge(index, item)" v-if="item.price">
						<view class="pic-number-pic">
							{{ item.price }}<span class="pic-number"> {{$t(`元`)}}</span>
						</view>
						<view class="pic-number">{{$t(`赠送`)}}: {{ item.give_money }} {{$t(`元`)}} </view>
					</view>
					<view class="pic-box pic-box-color acea-row row-center-wrapper"
						:class="activePic == picList.length ? 'pic-box-color-active' : ''" @click="picCharge(picList.length)">
						<input type="digit" @input="replaceInput" :placeholder="$t(`其他`)" v-model="money"
							class="pic-box-money pic-number-pic" :placeholder-class="activePic == picList.length ? 'active' :''"
							:class="activePic == picList.length ? 'pic-box-color-active' : ''" />
					</view>
					<view class="tips-box">
						<view class="tips mt-30">{{$t(`注意事项`)}}：</view>
						<view class="tips-samll" v-for="item in rechargeAttention" :key="item">
							{{ $t(item) }}
						</view>
					</view>

				</view>
				<view class="tip" v-else>
					<view class='input'><text>{{$t(`￥`)}}</text><input v-model="number" placeholder="0.00" type='number'
							placeholder-class='placeholder' name="number"></input></view>
					<view class="tips-title">
						<view style="font-weight: bold; font-size: 26rpx;">{{$t(`提示`)}}：</view>
						<view style="margin-top: 10rpx;">{{$t(`当前可转入佣金为`)}} <text
								class='font-color'>{{$t(`￥`)}}{{userinfo.commissionCount || 0}}</text>{{$t(`冻结佣金为`)}}<text
								class='font-color'>{{$t(`￥`)}}{{userinfo.broken_commission}}</text></view>
					</view>
					<view class="tips-box">
						<view class="tips mt-30">{{$t(`注意事项`)}}：</view>
						<view class="tips-samll" v-for="item in rechargeAttention" :key="item">
							{{ $t(item) }}
						</view>
					</view>
				</view>
				<button class='but bg-color' @click="submitSub"> {{active ? $t(`立即转入`): $t(`立即充值`) }}</button>
			</view>
		</form>
		<payment :payMode="payMode" :pay_close="pay_close" :is-call="true" @onChangeFun="onChangeFun"
			:totalPrice="numberPic"></payment>
		<view v-show="false" v-html="formContent"></view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		getUserInfo,
		recharge,
		getRechargeApi
	} from '@/api/user.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import home from '@/components/home';
	import colors from "@/mixins/color";
	import payment from '@/components/payment';
	import {
		basicConfig
	} from '@/api/public.js'
	export default {
		components: {
			payment,
			// #ifdef MP
			authorize,
			// #endif
			home
		},
		mixins: [colors],
		data() {
			let that = this;
			return {
				now_money: 0,
				navRecharge: [this.$t(`账户充值`), this.$t(`佣金转入`)],
				active: 0,
				number: '',
				formContent: '',
				userinfo: {},
				placeholder: "0.00",
				from: '',
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				picList: [],
				activePic: 0,
				money: "",
				numberPic: '',
				rechar_id: 0,
				rechargeAttention: [],
				pay_close: false,
				payMode: [{
						name: this.$t(`微信支付`),
						icon: 'icon-weixinzhifu',
						value: 'weixin',
						title: this.$t(`微信支付`),
						payStatus: true
					},
					// #ifdef H5 ||APP-PLUS
					{
						name: this.$t(`支付宝支付`),
						icon: 'icon-zhifubao',
						value: 'alipay',
						title: this.$t(`支付宝支付`),
						payStatus: true
					},
					// #endif
				],
				totalPrice: 0
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.getUserInfo();
						this.getRecharge();
					}
				},
				deep: true
			}
		},
		onLoad(options) {
			// #ifdef H5
			this.from = this.$wechat.isWeixin() ? "weixin" : "weixinh5"
			// #endif
			if (this.isLogin) {
				this.getBasicConfig();
				this.getUserInfo();
				this.getRecharge();
			} else {
				toLogin();
			}
		},
		methods: {
			replaceInput(event) {
				// 必须在nextTick中
				this.$nextTick(() => {
					this.money = event.target.value.match(/^\d*(\.?\d{0,2})/g)[0]
				})
			},
			getBasicConfig() {
				basicConfig().then(res => {
					const {
						ali_pay_status,
						pay_weixin_open
					} = res.data;
					this.payMode[0].payStatus = pay_weixin_open;
					// #ifdef APP-PLUS || H5
					this.payMode[1].payStatus = ali_pay_status;
					// #endif
					//#ifdef MP
					this.payMode[1].payStatus = false;
					//#endif
				}).catch(err => {
					uni.showToast({
						title: err,
						icon: 'none'
					});
				});
			},

			/**
			 * 选择金额
			 */
			picCharge(idx, item) {
				this.activePic = idx;
				if (item === undefined) {
					this.rechar_id = 0;
					this.numberPic = "";
				} else {
					this.money = "";
					this.rechar_id = item.id;
					this.numberPic = item.price;
				}
			},

			/**
			 * 充值额度选择
			 */
			getRecharge() {
				getRechargeApi()
					.then(res => {
						this.picList = res.data.recharge_quota;
						if (this.picList[0]) {
							this.rechar_id = this.picList[0].id;
							this.numberPic = this.picList[0].price;
						}
						this.rechargeAttention = res.data.recharge_attention || [];
					})
					.catch(res => {
						this.$util.Tips({
							title: res
						})
					});
			},


			onLoadFun: function() {
				this.getUserInfo();
				this.getRecharge();
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			navRecharges: function(index) {
				this.active = index;
			},
			/**
			 * 获取用户信息
			 */
			getUserInfo: function() {
				let that = this;
				getUserInfo().then(res => {
					that.$set(that, 'userinfo', res.data);
					if (!res.data.extract_type.includes('3')) {
						this.$set(this, 'navRecharge', [this.$t(`账户充值`)])
					}
				})
			},
			onChangeFun: function(e) {
				let opt = e;
				let action = opt.action || null;
				let value = opt.value != undefined ? opt.value : null;
				this.pay_close = false
				action && this[action] && this[action](value);
			},
			payCheck(type) {
				let that = this
				uni.showLoading({
					title: that.$t(`正在支付`),
				})
				recharge({
					price: that.rechar_id == 0 ? that.money : that.numberPic,
					from: type,
					rechar_id: that.rechar_id,
					type: 0
				}).then(res => {
					let status = res.data.status,
						orderId = res.data.result.orderId,
						jsConfig = res.data.result.jsConfig
					switch (status) {
						case 'ORDER_EXIST':
						case 'EXTEND_ORDER':
							uni.hideLoading();
							return that.$util.Tips({
								title: res.msg
							});
							break;
						case 'ALLINPAY_PAY':
							uni.hideLoading();
							// #ifdef MP
							this.initIn = true
							wx.openEmbeddedMiniProgram({
								appId: 'wxef277996acc166c3',
								extraData: {
									cusid: jsConfig.cusid,
									appid: jsConfig.appid,
									version: jsConfig.version,
									trxamt: jsConfig.trxamt,
									reqsn: jsConfig.reqsn,
									notify_url: jsConfig.notify_url,
									body: jsConfig.body,
									remark: jsConfig.remark,
									validtime: jsConfig.validtime,
									randomstr: jsConfig.randomstr,
									paytype: jsConfig.paytype,
									sign: jsConfig.sign,
									signtype: jsConfig.signtype
								}
							})
							this.jumpData = {
								orderId: res.data.result.orderId,
								msg: res.msg,
							}
							// #endif
							// #ifdef APP-PLUS
							plus.runtime.openURL(jsConfig.payinfo);
							// #endif
							// #ifdef H5
							this.formpost(res.data.result.pay_url, jsConfig)
							// #endif
							break;
						case 'PAY_ERROR':
							uni.hideLoading();
							return that.$util.Tips({
								title: res.msg
							});
							break;
						case 'SUCCESS':
							uni.hideLoading();
							return that.$util.Tips({
								title: that.$t(`支付成功`),
								icon: 'success'
							}, {
								tab: 5,
								url: '/pages/users/user_money/index'
							});
							break;
						case 'WECHAT_PAY':
							that.toPay = true;
							// #ifdef MP
							/* that.toPay = true; */
							let mp_pay_name = ''
							if (uni.requestOrderPayment) {
								mp_pay_name = 'requestOrderPayment'
							} else {
								mp_pay_name = 'requestPayment'
							}
							uni[mp_pay_name]({
								timeStamp: jsConfig.timestamp,
								nonceStr: jsConfig.nonceStr,
								package: jsConfig.package,
								signType: jsConfig.signType,
								paySign: jsConfig.paySign,
								success: function(res) {
									uni.hideLoading();
									that.$set(that, 'userinfo.now_money', that.$util.$h.Add(this
										.number, that.userinfo
										.now_money));
									return that.$util.Tips({
										title: that.$t(`支付成功`),
										icon: 'success'
									}, {
										tab: 5,
										url: '/pages/users/user_money/index'
									});
								},
								fail: function(e) {
									uni.hideLoading();
									return that.$util.Tips({
										title: that.$t(`支付失败`)
									});
								},
								complete: function(e) {
									uni.hideLoading();
									//关闭当前页面跳转至订单状态
									if (res.errMsg == 'requestPayment:cancel' || e.errMsg ==
										'requestOrderPayment:cancel') return that.$util
										.Tips({
											title: that.$t(`取消支付`)
										});
								},
							})
							// #endif
							// #ifdef H5
							this.$wechat.pay(res.data.result.jsConfig).then(res => {
								that.$set(that, 'userinfo.now_money', that.$util.$h.Add(this
									.number, that.userinfo
									.now_money));
								return that.$util.Tips({
									title: that.$t(`支付成功`),
									icon: 'success'
								}, {
									tab: 5,
									url: '/pages/users/user_money/index'
								});
							}).catch(res => {
								if (!this.$wechat.isWeixin()) {
									return that.$util.Tips({
										title: that.$t(`支付失败`)
									});
								}
								if (res.errMsg == 'chooseWXPay:cancel') return that.$util.Tips({
									title: that.$t(`取消支付`)
								});
							})
							// #endif
							// #ifdef APP-PLUS
							uni.requestPayment({
								provider: 'wxpay',
								orderInfo: jsConfig,
								success: (e) => {
									that.$set(that, 'userinfo.now_money', that.$util.$h.Add(this
										.number,
										that.userinfo
										.now_money));
									return that.$util.Tips({
										title: that.$t(`支付成功`),
										icon: 'success'
									}, {
										tab: 5,
										url: '/pages/users/user_money/index'
									});
								},
								fail: (e) => {
									return that.$util.Tips({
										title: that.$t(`支付失败`)
									});
								},
								complete: () => {
									uni.hideLoading();
								},
							});
							// #endif
							break;
						case 'PAY_DEFICIENCY':
							uni.hideLoading();
							//余额不足
							return that.$util.Tips({
								title: res.msg
							}, {
								tab: 5,
								url: goPages + '&status=1'
							});
							break;

						case "WECHAT_H5_PAY":
							uni.hideLoading();
							setTimeout(() => {
								location.href = res.data.result.jsConfig.h5_url;
							}, 2000);
							break;

						case 'ALIPAY_PAY':
							//#ifdef H5
							uni.hideLoading();
							that.formContent = res.data.result.jsConfig;
							that.$nextTick(() => {
								document.getElementById('alipaysubmit').submit();
							})
							//#endif
							// #ifdef MP
							uni.navigateTo({
								url: `/pages/users/alipay_invoke/index?id=${orderId}&link=${jsConfig.qrCode}`
							});
							// #endif
							// #ifdef APP-PLUS
							uni.requestPayment({
								provider: 'alipay',
								orderInfo: jsConfig,
								success: (e) => {
									that.$set(that, 'userinfo.now_money', that.$util.$h.Add(this
										.number,
										that.userinfo
										.now_money));
									return that.$util.Tips({
										title: that.$t(`支付成功`),
										icon: 'success'
									}, {
										tab: 5,
										url: '/pages/users/user_money/index'
									});

								},
								fail: (e) => {
									return that.$util.Tips({
										title: that.$t(`支付失败`)
									});
								},
								complete: () => {
									uni.hideLoading();
								},
							});
							// #endif
							break;
					}
				}).catch(err => {
					uni.hideLoading();
					return that.$util.Tips({
						title: err
					})
				})
			},
			formpost(url, postData) {
				let tempform = document.createElement("form");
				tempform.action = url;
				tempform.method = "post";
				tempform.target = "_self";
				tempform.style.display = "none";
				for (let x in postData) {
					let opt = document.createElement("input");
					opt.name = x;
					opt.value = postData[x];
					tempform.appendChild(opt);
				}
				document.body.appendChild(tempform);
				this.$nextTick(e => {
					tempform.submit();
				})
			},
			pay() {
				this.pay_close = true;
			},
			/*
			 * 用户充值
			 */
			submitSub() {
				let that = this
				let value = this.number;
				// 转入余额
				if (that.active) {
					if (parseFloat(value) < 0 || parseFloat(value) == NaN || value == undefined || value == "") {
						return that.$util.Tips({
							title: that.$t(`请输入金额`)
						});
					}
					uni.showModal({
						title: that.$t(`转入余额`),
						content: that.$t(`转入余额后无法再次转出，确认是否转入余额`),
						success(res) {
							if (res.confirm) {
								recharge({
										price: parseFloat(value),
										type: 1
									})
									.then(res => {
										// that.$set(that, 'userinfo.now_money', that.$util.$h.Add(value, that.userinfo.now_money))
										return that.$util.Tips({
											title: that.$t(`转入成功`),
											icon: 'success'
										}, {
											tab: 5,
											url: '/pages/users/user_money/index'
										});
									}).catch(err => {
										return that.$util.Tips({
											title: err
										})
									});
							} else if (res.cancel) {
								return that.$util.Tips({
									title: that.$t(`已取消`)
								});
							}
						},
					})
				} else {
					if (this.numberPic == '') this.numberPic = this.money;
					this.pay()
				}
			}
		},
	}
</script>
<style lang="scss">
	.tip .pic-box-color .active {
		color: #fff !important;
	}
</style>
<style lang="scss" scoped>
	page {
		width: 100%;
		height: 100%;
		background-color: #fff;
	}

	.bgcolor {
		background-color: var(--view-theme)
	}

	.payment {
		position: relative;
		top: -60rpx;
		width: 100%;
		background-color: #fff;
		border-radius: 10rpx;
		padding-top: 1rpx;
		border-top-right-radius: 39rpx;
		border-top-left-radius: 39rpx;
	}

	.payment .nav {
		height: 75rpx;
		line-height: 75rpx;
		padding: 8rpx 100rpx;
	}

	.payment .nav .item {
		font-size: 30rpx;
		color: #333;
	}

	.payment .nav .item.on {
		font-weight: bold;
		border-bottom: 4rpx solid var(--view-theme);
	}

	.payment .input {
		display: flex;
		align-items: center;
		justify-content: center;
		border-bottom: 1px dashed #dddddd;
		margin: 60rpx auto 0 auto;
		padding-bottom: 20rpx;
		font-size: 56rpx;
		color: #333333;
		flex-wrap: nowrap;

	}

	.payment .input text {
		padding-left: 106rpx;
	}

	.payment .input input {
		padding-right: 106rpx;
		width: 300rpx;
		height: 94rpx;
		text-align: center;
		font-size: 70rpx;
	}

	.payment .placeholder {
		color: #d0d0d0;
		height: 100%;
		line-height: 94rpx;
	}

	.payment .tip {
		font-size: 26rpx;
		color: #888888;
		padding: 0 30rpx;
		margin-top: 25rpx;
	}

	.payment .but {
		color: #fff;
		font-size: 30rpx;
		width: 700rpx;
		height: 86rpx;
		border-radius: 50rpx;
		margin: 46rpx auto 0 auto;
		line-height: 86rpx;
	}

	.payment-top {
		width: 100%;
		height: 350rpx;
		background-color: var(--view-theme);

		.name {
			font-size: 26rpx;
			color: rgba(255, 255, 255, 0.8);
			margin-top: -38rpx;
			margin-bottom: 30rpx;
		}

		.pic {
			font-size: 32rpx;
			color: #fff;

			.num {
				font-size: 56rpx;
			}
		}

		.pic-font {
			font-size: 78rpx;
			color: #fff;
		}
	}

	.picList {
		display: flex;
		flex-wrap: wrap;
		margin: 30rpx 0;

		.pic-box {
			width: 32%;
			height: auto;
			border-radius: 20rpx;
			margin-top: 21rpx;
			padding: 20rpx 0;
			margin-right: 12rpx;

			&:nth-child(3n) {
				margin-right: 0;
			}
		}

		.pic-box-color {
			background-color: #f4f4f4;
			color: #656565;
		}

		.pic-number {
			font-size: 22rpx;
		}

		.pic-number-pic {
			font-size: 38rpx;
			margin-right: 10rpx;
			text-align: center;
		}

		.active {
			color: #fff !important;
		}

		.pic-box-color-active {
			background-color: var(--view-theme) !important;
			color: #fff !important;
		}
	}

	.tips-box {
		.tips {
			font-size: 28rpx;
			color: #333333;
			font-weight: 800;
			margin-bottom: 14rpx;
			margin-top: 20rpx;
		}

		.tips-samll {
			font-size: 24rpx;
			color: #333333;
			margin-bottom: 14rpx;
		}

		.tip-box {
			margin-top: 30rpx;
		}
	}

	.tips-title {
		margin-top: 20rpx;
		font-size: 24rpx;
		color: #333;
	}
</style>