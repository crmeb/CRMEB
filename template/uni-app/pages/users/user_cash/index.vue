<template>
	<view :style="colorStyle">
		<view class='cash-withdrawal'>
			<view class='nav acea-row'>
				<view v-for="(item,index) in navList" :key="index" class='item fontcolor' @click="swichNav(item.id)">
					<view class='line bg-color' :class='currentTab==item.id ? "on":""'></view>
					<view class='iconfont' :class='item.icon+" "+(currentTab==item.id ? "on":"")'></view>
					<view>{{item.name}}</view>
				</view>
			</view>
			<view class='wrapper'>
				<view :hidden='currentTab != 0' class='list'>
					<form @submit="subCash">
						<view class='item acea-row row-between-wrapper'>
							<view class='name'><text class='red'>*</text> {{$t(`持卡人`)}}</view>
							<view class='input'><input :placeholder='$t(`请输入持卡人姓名`)' placeholder-class='placeholder'
									name="name"></input></view>
						</view>
						<view class='item acea-row row-between-wrapper'>
							<view class='name'><text class='red'>*</text> {{$t(`卡号`)}}</view>
							<view class='input'><input type='number' :placeholder='$t(`请填写卡号`)' placeholder-class='placeholder'
									name="cardnum"></input></view>
						</view>
						<view class='item acea-row row-between-wrapper'>
							<view class='name'><text class='red'>*</text> {{$t(`银行`)}}</view>
							<view class='input'>
								<picker @change="bindPickerChange" :value="index" :range="array">
									<text class='Bank'>{{array[index]}}</text>
									<text class='iconfont icon-qiepian38'></text>
								</picker>
							</view>
						</view>
						<view class='item acea-row row-between-wrapper'>
							<view class='name'><text class='red'>*</text> {{$t(`提现`)}}</view>
							<view class='input'><input @input='inputNum' :placeholder='$t(`最低提现金额`)+minPrice'
									placeholder-class='placeholder' name="money" type='digit'></input></view>
						</view>
						<view class='tip'>
							{{$t(`当前可提现金额`)}}: <text class="price">{{$t(`￥`)}}{{userInfo.commissionCount}}</text>，{{$t(`冻结佣金`)}}:
							{{$t(`￥`)}}{{userInfo.broken_commission}}
						</view>
						<view class='tip'>
							{{$t(`提现手续费: `)}}<text class="price">{{withdrawal_fee}}%</text>，{{$t(`实际到账: `)}}<text
								class="price">{{$t(`￥`)}}{{true_money}}</text>
						</view>
						<view class='tip'>
							{{$t(`说明: 每笔佣金的冻结期为`)}}{{userInfo.broken_day}}{{$t(`天，到期后可提现`)}}
						</view>
						<button formType="submit" class='bnt bg-color'>{{$t(`提现`)}}</button>
					</form>
				</view>
				<view :hidden='currentTab != 1' class='list'>
					<form @submit="subCash">
						<view class='item acea-row row-between-wrapper'>
							<view class='name'><text class='red'>*</text> {{$t(`姓名`)}}</view>
							<view class='input'><input :placeholder='$t(`请填写您的真实姓名`)' placeholder-class='placeholder'
									name="user_name"></input></view>
						</view>
						<view class='item acea-row row-between-wrapper' v-if="!weixinExtractType">
							<view class='name'><text class='red'>*</text> {{$t(`账号`)}}</view>
							<view class='input'><input :placeholder='$t(`请填写您的微信账号`)' placeholder-class='placeholder'
									name="name"></input></view>
						</view>
						<view class='item acea-row row-between-wrapper'>
							<view class='name'><text class='red'>*</text> {{$t(`提现`)}}</view>
							<view class='input'><input @input='inputNum' :placeholder='$t(`提现最低`)+minPrice+$t(`元，最高500元`)'
									placeholder-class='placeholder' name="money" type='digit'></input></view>
						</view>
						<view class='item acea-row row-top row-between' v-if="!weixinExtractType">
							<view class='name pos'>{{$t(`收款码`)}}</view>
							<view class="input acea-row">
								<view class="picEwm" v-if="qrcodeUrlW">
									<image :src="qrcodeUrlW"></image>
									<text class='iconfont icon-guanbi1 fontcolor' @click='DelPicW'></text>
								</view>
								<view class='pictrue acea-row row-center-wrapper row-column' @click='uploadpic("W")' v-else>
									<text class='iconfont icon-icon25201'></text>
									<view>{{$t(`上传图片`)}}</view>
								</view>
							</view>
						</view>
						<view class='tip'>
							{{$t(`当前可提现金额`)}}: <text class="price">{{$t(`￥`)}}{{userInfo.commissionCount}}</text>，{{$t(`冻结佣金`)}}:
							{{$t(`￥`)}}{{userInfo.broken_commission}}
						</view>
						<view class='tip'>
							{{$t(`提现手续费: `)}}<text class="price">{{withdrawal_fee}}%</text>，{{$t(`实际到账: `)}}<text
								class="price">{{$t(`￥`)}}{{true_money}}</text>
						</view>
						<view class='tip'>
							{{$t(`说明: 每笔佣金的冻结期为`)}}{{userInfo.broken_day}}{{$t(`天，到期后可提现`)}}
						</view>
						<button formType="submit" class='bnt bg-color'>{{$t(`提现`)}}</button>
					</form>
				</view>
				<view :hidden='currentTab != 2' class='list'>
					<form @submit="subCash">
						<view class='item acea-row row-between-wrapper'>
							<view class='name'><text class='red'>*</text> {{$t(`账号`)}}</view>
							<view class='input'><input :placeholder='$t(`请填写您的支付宝账号`)' placeholder-class='placeholder'
									name="name"></input></view>
						</view>
						<view class='item acea-row row-between-wrapper'>
							<view class='name'><text class='red'>*</text> {{$t(`姓名`)}}</view>
							<view class='input'><input :placeholder='$t(`请填写支付宝绑定的真实姓名`)' placeholder-class='placeholder'
									name="user_name"></input></view>
						</view>
						<view class='item acea-row row-between-wrapper'>
							<view class='name'><text class='red'>*</text> {{$t(`提现`)}}</view>
							<view class='input'><input @input='inputNum' :placeholder='$t(`最低提现金额`)+minPrice'
									placeholder-class='placeholder' name="money" type='digit'></input></view>
						</view>
						<view class='item acea-row row-top row-between'>
							<view class='name pos'>{{$t(`收款码`)}}</view>
							<view class="input acea-row">
								<view class="picEwm" v-if="qrcodeUrlZ">
									<image :src="qrcodeUrlZ"></image>
									<text class='iconfont icon-guanbi1 fontcolor' @click='DelPicZ'></text>
								</view>
								<view class='pictrue acea-row row-center-wrapper row-column' @click='uploadpic("Z")' v-else>
									<text class='iconfont icon-icon25201'></text>
									<view>{{$t(`上传图片`)}}</view>
								</view>
							</view>
						</view>
						<view class='tip'>
							{{$t(`当前可提现金额`)}}: <text class="price">{{$t(`￥`)}}{{userInfo.commissionCount}}</text>，{{$t(`冻结佣金`)}}:
							{{$t(`￥`)}}{{userInfo.broken_commission}}
						</view>
						<view class='tip'>
							{{$t(`提现手续费: `)}}<text class="price">{{withdrawal_fee}}%</text>，{{$t(`实际到账: `)}}<text
								class="price">{{$t(`￥`)}}{{true_money}}</text>
						</view>
						<view class='tip'>
							{{$t(`说明: 每笔佣金的冻结期为`)}}{{userInfo.broken_day}}{{$t(`天，到期后可提现`)}}
						</view>
						<button formType="submit" class='bnt bg-color'>{{$t(`提现`)}}</button>
					</form>
				</view>
				<view :hidden='currentTab != 3' class='list'>
					<form @submit="importNowMoney">
						<view class='item acea-row row-between-wrapper'>
							<view class='name'><text class='red'>*</text> {{$t(`提现`)}}</view>
							<view class='input'><input @input='inputNum' placeholder='请输入提现金额' placeholder-class='placeholder'
									name="money" type='digit'></input></view>
						</view>
						<view class='tip'>
							{{$t(`当前可提现金额`)}}: <text class="price">{{$t(`￥`)}}{{userInfo.commissionCount}}</text>，{{$t(`冻结佣金`)}}:
							{{$t(`￥`)}}{{userInfo.broken_commission}}
						</view>
						<button formType="submit" class='bnt bg-color'>{{$t(`提现`)}}</button>
					</form>
				</view>
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		extractCash,
		extractBank,
		getUserInfo,
		recharge
	} from '@/api/user.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	// #ifdef MP
	import authorize from '@/components/Authorize';
	import {
		openRevenueSubscribe
	} from '@/utils/SubscribeMessage.js';
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
				navList: [],
				currentTab: 0,
				index: 0,
				array: [], //提现银行
				minPrice: 0.00, //最低提现金额
				userInfo: [],
				isClone: false,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				qrcodeUrlW: "",
				qrcodeUrlZ: "",
				prevent: false, //避免重复提交成功多次
				weixinExtractType: 0, // 佣金到账方式
				alipayExtractType: 0, // 佣金到账方式
				withdrawal_fee: 0, //提现手续费
				true_money: 0
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.getUserInfo();
						this.getUserExtractBank();
					}
				},
				deep: true
			}
		},
		onLoad() {
			if (this.isLogin) {
				this.getUserInfo();
				this.getUserExtractBank();
			} else {
				toLogin();
			}
		},
		methods: {
			inputNum: function(e) {
				let val = e.detail.value;
				let dot = val.indexOf('.');
				if (dot > -1) {
					this.moneyMaxLeng = dot + 3;
				} else {
					this.moneyMaxLeng = 8
				}
				this.true_money = Math.floor((this.$util.$h.Mul(val, this.$util.$h.Div(this.$util.$h.Sub(100, this
					.withdrawal_fee), 100))) * 100) / 100 || 0;
			},
			// uploadpicW(){
			// 	this.uploadpic(this.qrcodeUrlW);
			// },
			// uploadpicZ(){
			// 	this.uploadpic(this.qrcodeUrlZ);
			// },
			/**
			 * 上传文件
			 * 
			 */
			uploadpic: function(type) {
				let that = this;
				that.$util.uploadImageOne('upload/image', function(res) {
					if (type === 'W') {
						that.qrcodeUrlW = res.data.url;
					} else {
						that.qrcodeUrlZ = res.data.url;
					}
				});
			},
			/**
			 * 删除图片
			 * 
			 */
			DelPicW: function() {
				this.qrcodeUrlW = "";
			},
			DelPicZ: function() {
				this.qrcodeUrlZ = "";
			},
			onLoadFun: function() {
				this.getUserInfo();
				this.getUserExtractBank();
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			getUserExtractBank: function() {
				let that = this;
				extractBank().then(res => {
					let array = res.data.extractBank;
					array.unshift('请选择银行');
					array.forEach((v, i) => {
						array.splice(i, 1, that.$t(v))
					})
					that.$set(that, 'array', array);
					that.minPrice = res.data.minPrice;
					that.withdrawal_fee = res.data.withdrawal_fee;
					that.alipayExtractType = res.data.alipayExtractType ? parseInt(res.data.alipayExtractType) : 0;
					that.weixinExtractType = res.data.weixinExtractType ? parseInt(res.data.weixinExtractType) : 0;
				});
			},
			/**
			 * 获取个人用户信息
			 */
			getUserInfo: function() {
				let that = this;
				getUserInfo().then(res => {
					that.navList = [{
							'name': that.$t(`银行卡`),
							'icon': 'icon-yinhangqia',
							'id': 0
						},
						{
							'name': that.$t(`微信`),
							'icon': 'icon-weixin2',
							'id': 1
						},
						{
							'name': that.$t(`支付宝`),
							'icon': 'icon-icon34',
							'id': 2
						},
						{
							'name': that.$t(`余额`),
							'icon': 'icon-qiandai',
							'id': 3
						}
					]
					let list = [];
					that.userInfo = res.data;
					that.navList.forEach((item, index) => {
						if (that.userInfo.extract_type.includes(item.id.toString())) {
							list.push(item)
						}
					})
					this.navList = list
					this.swichNav(this.navList[0].id)
				});
			},
			swichNav: function(current) {
				this.currentTab = current;
			},
			bindPickerChange: function(e) {
				this.index = e.detail.value;
			},
			subCash(e) {
				let that = this,
					value = e.detail.value;
				if (this.prevent) return
				if (that.currentTab == 0) { //银行卡
					if (!value.name.trim()) return this.$util.Tips({
						title: this.$t(`请填写持卡人姓名`)
					});
					if (!value.cardnum.trim()) return this.$util.Tips({
						title: this.$t(`请填写卡号`)
					});
					if (that.index == 0) return this.$util.Tips({
						title: this.$t(`请选择银行`)
					});
					value.extract_type = 'bank';
					value.bankname = that.array[that.index];
				} else if (that.currentTab == 1) { //微信
					value.extract_type = 'weixin';

					if (!value.user_name.trim()) return this.$util.Tips({
						title: this.$t(`请填写姓名`)
					});
					// 自动提现隐藏账号
					if (!that.weixinExtractType && !value.name.trim()) return this.$util.Tips({
						title: this.$t(`请填写微信号`)
					});
					value.weixin = value.name;
					value.qrcode_url = that.qrcodeUrlW;
					
				} else if (that.currentTab == 2) { //支付宝
					value.extract_type = 'alipay';
					if (value.name.length == 0) return this.$util.Tips({
						title: this.$t(`请填写支付宝号`)
					});
					value.alipay_code = value.name;
					value.qrcode_url = that.qrcodeUrlZ;
				}
				if (!value.money.trim()) return this.$util.Tips({
					title: this.$t(`请填写提现金额`)
				});
				if (Number(value.money) < Number(that.minPrice)) return this.$util.Tips({
					title: this.$t(`提现金额不能低于`) + that.minPrice
				});
				if (Number(value.money) > 500) return this.$util.Tips({
					title: this.$t(`提现金额不能高于500`)
				});
				this.prevent = true
				extractCash(value).then(res => {
					that.getUserInfo();
					if(this.weixinExtractType == 1 && this.currentTab == 1){
						// #ifdef MP
							this.openSubscribe('/pages/users/user_spread_user/index')
						// #endif
						return
					}
					return this.$util.Tips({
						title: res.msg,
						icon: 'success'
					}, {
						url: '/pages/users/user_spread_user/index',
						tab: 2
					});
					setTimeout(e => {
						this.prevent = false
					}, 1000)
				}).catch(err => {
					setTimeout(e => {
						this.prevent = false
					}, 1000)
					return this.$util.Tips({
						title: err
					});
				});
			},
			// #ifdef MP
			openSubscribe(page) {
				// uni.showLoading({
				// 	title: this.$t(`正在加载`),
				// })
				openRevenueSubscribe().then(res => {
					uni.hideLoading();
					uni.navigateTo({
						url: page,
					});
				}).catch(() => {
					uni.hideLoading();
				});
			},
			// #endif
			importNowMoney(e) {
				let that = this
				let value = e.detail.value.money;
				if (parseFloat(value) < 0 || parseFloat(value) == NaN || value == undefined || value == "") {
					return that.$util.Tips({
						title: that.$t(`请输入金额`)
					});
				}
				uni.showModal({
					title: that.$t(`提现到余额`),
					content: that.$t(`提现到余额后无法再次转出，确认是否提现到余额`),
					success(res) {
						if (res.confirm) {
							recharge({
									price: parseFloat(value),
									type: 1
								})
								.then(res => {
									return that.$util.Tips({
										title: that.$t(`提现到余额成功`),
										icon: 'success'
									}, {
										tab: 5,
										url: '/pages/users/user_spread_user/index'
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
			}
		}
	}
</script>

<style lang="scss">
	page {
		background-color: #fff !important;
	}

	.fontcolor {
		color: var(--view-theme) !important;
	}

	.cash-withdrawal .nav {
		height: 130rpx;
		box-shadow: 0 10rpx 10rpx #f8f8f8;
	}

	.cash-withdrawal .nav .item {
		font-size: 26rpx;
		flex: 1;
		text-align: center;
	}

	.cash-withdrawal .nav .item~.item {
		border-left: 1px solid #f0f0f0;
	}

	.cash-withdrawal .nav .item .iconfont {
		width: 40rpx;
		height: 40rpx;
		border-radius: 50%;
		border: 2rpx solid var(--view-theme);
		text-align: center;
		line-height: 37rpx;
		margin: 0 auto 6rpx auto;
		font-size: 22rpx;
		box-sizing: border-box;
	}

	.cash-withdrawal .nav .item .iconfont.on {
		background-color: var(--view-theme);
		color: #fff;
		border-color: var(--view-theme);
	}

	.cash-withdrawal .nav .item .line {
		width: 2rpx;
		height: 20rpx;
		margin: 0 auto;
		transition: height 0.3s;
	}

	.cash-withdrawal .nav .item .line.on {
		height: 39rpx;
	}

	.cash-withdrawal .wrapper .list {
		padding: 0 30rpx;
	}

	.cash-withdrawal .wrapper .list .item {
		border-bottom: 1rpx solid #eee;
		min-height: 28rpx;
		font-size: 30rpx;
		color: #333;
		padding: 39rpx 0;
	}

	.cash-withdrawal .wrapper .list .item .name {
		width: 130rpx;
	}

	.cash-withdrawal .wrapper .list .item .input {
		width: 505rpx;
	}

	.cash-withdrawal .wrapper .list .item .input .placeholder {
		color: #bbb;
	}

	.cash-withdrawal .wrapper .list .item .picEwm,
	.cash-withdrawal .wrapper .list .item .pictrue {
		width: 140rpx;
		height: 140rpx;
		border-radius: 3rpx;
		position: relative;
		margin-right: 23rpx;
	}

	.cash-withdrawal .wrapper .list .item .picEwm image {
		width: 100%;
		height: 100%;
		border-radius: 3rpx;
	}

	.cash-withdrawal .wrapper .list .item .picEwm .icon-guanbi1 {
		position: absolute;
		right: -14rpx;
		top: -16rpx;
		font-size: 40rpx;
	}

	.cash-withdrawal .wrapper .list .item .pictrue {
		border: 1px solid rgba(221, 221, 221, 1);
		font-size: 22rpx;
		color: #BBBBBB;
	}

	.cash-withdrawal .wrapper .list .item .pictrue .icon-icon25201 {
		font-size: 47rpx;
		color: #DDDDDD;
		margin-bottom: 3px;
	}

	.cash-withdrawal .wrapper .list .tip {
		font-size: 26rpx;
		color: #999;
		margin-top: 25rpx;
	}

	.cash-withdrawal .wrapper .list .bnt {
		font-size: 32rpx;
		color: #fff;
		width: 690rpx;
		height: 90rpx;
		text-align: center;
		border-radius: 50rpx;
		line-height: 90rpx;
		margin: 64rpx auto;
	}

	.cash-withdrawal .wrapper .list .tip2 {
		font-size: 26rpx;
		color: #999;
		text-align: center;
		margin: 44rpx 0 20rpx 0;
	}

	.cash-withdrawal .wrapper .list .value {
		height: 135rpx;
		line-height: 135rpx;
		border-bottom: 1rpx solid #eee;
		width: 690rpx;
		margin: 0 auto;
	}

	.cash-withdrawal .wrapper .list .value input {
		font-size: 80rpx;
		color: #282828;
		height: 135rpx;
		text-align: center;
	}

	.cash-withdrawal .wrapper .list .value .placeholder2 {
		color: #bbb;
	}

	.price {
		color: var(--view-priceColor);
	}
	.pos{
		padding-left: 26rpx;
	}
	.red{
		padding-right: 10rpx;
		color: var(--view-theme) !important;
	}
</style>