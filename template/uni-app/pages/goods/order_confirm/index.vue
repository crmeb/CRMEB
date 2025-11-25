<template>
	<view :style="colorStyle">
		<view class='order-submission'>
			<view class="allAddress" :style="store_self_mention && is_shipping ? '':'padding-top:10rpx'"
				v-if="!virtual_type && (!is_gift || is_gift == 2)">
				<view class="nav acea-row">
					<view class="item font-num" :class="shippingType == 0 ? 'on' : 'on2'" @tap="addressType(0)"
						v-if='store_self_mention && is_shipping'>
						<view class="before">
							{{$t(`快递配送`)}}
						</view>
					</view>
					<view class="item font-num" :class="shippingType == 1 ? 'on' : 'on2'" @tap="addressType(1)"
						v-if='store_self_mention && is_shipping'>
						<view class="before">
							{{$t(`到店自提`)}}
						</view>
					</view>
				</view>
				<view class="add-title acea-row row-between-wrapper" v-if="!store_self_mention || !is_shipping"
					@click.prevent="openList">
					<view class="acea-row row-middle">
						<view class="icon" :class="shippingType==1?'orange':'red'">
							{{shippingType==0?'商城配送':'门店自提'}}
						</view>
						<view class="text add-text line1" v-if="shippingType==0">{{$t(`由平台为您提供配送服务`)}}</view>
						<view class="text add-text line1" v-if="shippingType==1">{{$t(`线上下单，到店自提`)}}</view>
					</view>

					<view class="text">{{shippingType == 0 ? $t('切换地址') : $t('切换门店')}} <text class='iconfont icon-jiantou'></text>
					</view>
				</view>
				<view class='address acea-row row-between-wrapper' @tap='onAddress' v-if='shippingType == 0'>
					<view class='addressCon' v-if="addressInfo.real_name || ''">
						<view class='name'>{{addressInfo.real_name || ''}}
							<text class='phone'>{{addressInfo.phone || ''}}</text>
						</view>
						<view class="line1">
							<text class='default font-num'
								v-if="addressInfo.is_default">[{{$t(`默认`)}}]</text>{{addressInfo.province}}{{addressInfo.city}}{{addressInfo.district}}{{addressInfo.detail}}
						</view>
						<!-- <view class='setaddress'>设置收货地址</view> -->
					</view>
					<view class='addressCon' v-else>
						<view class='setaddress'>{{$t(`设置收货地址`)}}</view>
					</view>
					<view v-if="store_self_mention && is_shipping" class='iconfont icon-jiantou'></view>
				</view>
				<view class='address acea-row row-between-wrapper' v-else @tap="showStoreList">
					<block v-if="storeList.length>0">
						<view class='addressCon'>
							<view class='name'>{{system_store.name || ''}}
								<text class='phone'>{{system_store.phone || ''}}</text>
							</view>
							<view class="line1"> {{system_store.address}}{{", " + system_store.detailed_address}}</view>
						</view>
						<!-- <view class='iconfont icon-jiantou'></view> -->
					</block>
					<block v-else>
						<view>{{$t(`暂无门店信息`)}}</view>
					</block>
					<view class="icon acea-row row-middle" v-if="storeList.length>0">
						<view class="iconfont icon-dianhua" @click.stop="call(system_store.phone)"></view>
						<view class="iconfont icon-dingwei2" @click.stop="showMaoLocation(system_store)"></view>
					</view>
				</view>
				<view class='line'>
					<image src='/static/images/line.jpg'></image>
				</view>
			</view>
			<view v-if="giftData" class="gift-box">
				<view class="acea-row row-middle user-msg">
					<image class="avatar mr-12" :src="giftData.avatar" mode=""></image>
					<text class="nickname">{{ giftData.nickname}} 赠您一份礼物，请查收！</text>
				</view>
				<view class="line"></view>
				<view class="gift-mark" v-if="giftData.gift_mark">
					{{giftData.gift_mark}}
				</view>
			</view>
			<orderGoods :cartInfo="cartInfo" :is_confirm='true' :is_gift='is_gift' :shipping_type="shippingType"></orderGoods>
			<view v-if="is_gift == 1" class="gift-msg acea-row row-between-wrapper">
				<view>{{$t(`好友留言`)}}</view>
				<view class="discount">
					<input style="text-align: right;" v-model="gift_mark" type="text" :placeholder="$t(`填写好友留言，最多40字`)"
						placeholder-class="placeholder"></input>
				</view>
			</view>
			<view class='wrapper' v-if="(!is_gift && is_gift == 2) && shippingType == 1">
				<view class="item acea-row row-between-wrapper">
					<view>{{$t(`用户姓名`)}}</view>
					<view class="discount">
						<input style="text-align: right;" v-model="contacts" type="text" :placeholder="$t(`请输入姓名`)"
							placeholder-class="placeholder"></input>
					</view>
				</view>
				<view class="item acea-row row-between-wrapper">
					<view>{{$t(`联系电话`)}}</view>
					<view class="discount">
						<input style="text-align: right;" v-model="contactsTel" type="text" :placeholder="$t(`请输入手机号`)"
							placeholder-class="placeholder"></input>
					</view>
				</view>
			</view>
			<view v-if="is_gift == 2" class="receive-btn" @click="receiveGift">
				立即领取
			</view>
			<view class='wrapper' v-if="!is_gift || is_gift == 1">
				<view class='item acea-row row-between-wrapper' @tap='couponTap'
					v-if="!pinkId && !BargainId && !combinationId && !seckillId&& !noCoupon && !discountId && !advanceId">
					<view>{{$t(`优惠券`)}}</view>
					<view class='discount'>
						{{couponTitle}}
						<text class='iconfont icon-jiantou'></text>
					</view>
				</view>
				<view class='item acea-row row-between-wrapper'
					v-if="!pinkId && !BargainId && !combinationId && !seckillId && !advanceId && integral_open">
					<view>{{$t(`积分抵扣`)}}</view>
					<view class='discount acea-row row-middle'>
						<view> {{useIntegral ? $t(`剩余积分`):$t(`当前积分`)}}
							<text class='num font-color'>{{integral || 0}}</text>
						</view>
						<checkbox-group @change="ChangeIntegral">
							<checkbox :disabled="integral<=0 && !useIntegral" :checked='useIntegral ? true : false' />
						</checkbox-group>
					</view>
				</view>
				<view v-if="invoice_func || special_invoice" class='item acea-row row-between-wrapper' @tap="goInvoice">
					<view>{{$t(`开具发票`)}}</view>
					<view class='discount'>
						{{invTitle}}
						<text class='iconfont icon-jiantou'></text>
					</view>
				</view>
				<view v-if="shippingType == 1">
					<view class="item acea-row row-between-wrapper">
						<view>{{$t(`用户姓名`)}}</view>
						<view class="discount">
							<input style="text-align: right;" v-model="contacts" type="text" :placeholder="$t(`请输入姓名`)"
								placeholder-class="placeholder"></input>
						</view>
					</view>
					<view class="item acea-row row-between-wrapper">
						<view>{{$t(`联系电话`)}}</view>
						<view class="discount">
							<input style="text-align: right;" v-model="contactsTel" type="text" :placeholder="$t(`请输入手机号`)"
								placeholder-class="placeholder"></input>
						</view>
					</view>
				</view>
				<view class='item' v-if="textareaStatus">
					<view>{{$t(`备注说明`)}}</view>
					<view class="mark" v-if="!coupon.coupon && !inputTrip" @click="inputTripClick">
						<view :class="{'mark-msg': mark}" v-text="mark || $t(`填写备注信息，100字以内`)"></view>
					</view>
					<textarea placeholder-class='placeholder' :placeholder="$t(`填写备注信息，100字以内`)"
						v-if="!coupon.coupon && inputTrip" @input='bindHideKeyboard' :focus="focus" @blur="inputTrip = false"
						:value="mark" :maxlength="150" name="mark">
						</textarea>
				</view>
			</view>
			<view class='wrapper' v-if="confirm.length && (!is_gift || is_gift == 1)">
				<view class='item acea-row row-between-wrapper' v-for="(item,index) in confirm" :key="index">
					<view>
						<span v-if="item.status" style="color: red;">*</span>
						<span v-else style="marginLeft: 8px;"></span>
						{{ item.title }}
					</view>
					<!-- text -->
					<view v-if="item.label=='text'" class="confirm">
						<input type="text" :placeholder="$t(`请填写${item.title}`)" v-model="item.value" />
					</view>
					<!-- number -->
					<view v-if="item.label=='number'" class="confirm">
						<input type="number" :placeholder="$t(`请填写${item.title}`)" v-model="item.value" />
					</view>
					<!-- email -->
					<view v-if="item.label=='email'" class="confirm">
						<input type="text" :placeholder="$t(`请填写${item.title}`)" v-model="item.value" />
					</view>
					<!-- data -->
					<view v-if="item.label=='data'" class="uni-list">
						<view class="uni-list-cell">
							<view class="uni-list-cell-db">
								<picker mode="date" :value="item.value" @change="bindDateChange($event,index)">
									<view v-if="item.value == ''" class="fontC">{{date+item.title}}<text
											class='iconfont icon-jiantou'></text></view>
									<view v-else class="uni-input">{{item.value}}</view>
								</picker>
							</view>
						</view>
					</view>
					<!-- time -->
					<view v-if="item.label=='time'">
						<view>
							<view>
								<picker mode="time" :value="item.value" start="00:00" end="23:59"
									@change="bindTimeChange($event,index)">
									<view v-if="item.value == ''" class="fontC">{{time+item.title}}<text
											class='iconfont icon-jiantou'></text></view>
									<view>{{item.value}}</view>
								</picker>
							</view>
						</view>
					</view>
					<!-- id -->
					<view v-if="item.label=='id'" class="confirm">
						<input type="idcard" :placeholder="$t(`请填写`)+item.title" v-model="item.value" />
					</view>
					<!-- phone -->
					<view v-if="item.label=='phone'" class="confirm">
						<input type="tel" :placeholder="$t(`请填写`)+item.title" v-model="item.value" />
					</view>
					<!-- img -->
					<view v-if="item.label=='img'" class="confirmImg">
						<view class='list acea-row row-middle'>
							<view class='pictrue' v-for="(items,indexs) in item.value" :key="indexs">
								<image :src='items' class="img"></image>
								<text class='iconfont icon-guanbi1 font-num del' @click='DelPic(index,indexs)'></text>
							</view>
							<view class='pictrue acea-row row-center-wrapper row-column bor' @click='uploadpic(index,item)'
								v-if="item.value.length < 8">
								<text class='iconfont icon-icon25201'></text>
								<view>{{$t(`上传图片`)}}</view>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class='moneyList' v-if="!is_gift || is_gift == 1">
				<view class='item acea-row row-between-wrapper'>
					<view>{{$t(`商品总价`)}}：</view>
					<view class='money'>
						{{$t(`￥`)}}{{allPrice || 0}}
					</view>
				</view>
				<view v-if="is_gift && priceGroup.giftPrice > 0" class='item acea-row row-between-wrapper'>
					<view>{{$t(`礼品附加费`)}}：</view>
					<view class='money'>
						{{$t(`￥`)}}{{parseFloat(priceGroup.giftPrice)}}
					</view>
				</view>
				<view class='item acea-row row-between-wrapper'
					v-if="priceGroup.storePostage > 0 || priceGroup.storePostageDiscount > 0">
					<view>{{$t(`配送运费`)}}：</view>
					<view class='money'>
						{{$t(`￥`)}}{{(parseFloat(priceGroup.storePostage)+parseFloat(priceGroup.storePostageDiscount)).toFixed(2)}}
					</view>
				</view>
				<view class='item acea-row row-between-wrapper'
					v-if="priceGroup.levelPrice > 0 && userInfo.vip && !pinkId && !BargainId && !combinationId && !seckillId && !discountId">
					<view>{{$t(`用户等级优惠`)}}：</view>
					<view class='money'>-{{$t(`￥`)}}{{parseFloat(priceGroup.levelPrice).toFixed(2)}}</view>
				</view>
				<view class='item acea-row row-between-wrapper'
					v-if="priceGroup.memberPrice > 0 && userInfo.vip && !pinkId && !BargainId && !combinationId && !seckillId && !discountId">
					<view>{{$t(`付费会员优惠`)}}：</view>
					<view class='money'>-{{$t(`￥`)}}{{parseFloat(priceGroup.memberPrice).toFixed(2)}}</view>
				</view>
				<view class='item acea-row row-between-wrapper' v-if="priceGroup.storePostageDiscount > 0">
					<view>{{$t(`会员运费优惠`)}}：</view>
					<view class='money'>-{{$t(`￥`)}}{{parseFloat(priceGroup.storePostageDiscount).toFixed(2)}}</view>
				</view>
				<view class='item acea-row row-between-wrapper' v-if="coupon_price > 0">
					<view>{{$t(`优惠券抵扣`)}}：</view>
					<view class='money'>-{{$t(`￥`)}}{{parseFloat(coupon_price).toFixed(2)}}</view>
				</view>
				<view class='item acea-row row-between-wrapper' v-if="integral_price > 0">
					<view>{{$t(`积分抵扣`)}}：</view>
					<view class='money'>-{{$t(`￥`)}}{{parseFloat(integral_price).toFixed(2)}}</view>
				</view>
			</view>
			<view style='height:120rpx;'></view>
			<view class='footer acea-row row-between-wrapper' v-if="!is_gift || is_gift == 1">
				<view>{{$t(`合计`)}}:
					<text class='font-color'>{{$t(`￥`)}}{{totalPrice || 0}}</text>
				</view>
				<view class='settlement' style='z-index:100' @tap.stop="Debounce(SubOrder())"
					v-if="(valid_count>0&&!discount_id) || (valid_count==cartInfo.length&&discount_id)">{{$t(`提交订单`)}}
				</view>
				<view class='settlement bg-color-hui' style='z-index:100' v-else>{{$t(`提交订单`)}}</view>
			</view>
		</view>
		<view class="alipaysubmit" v-html="formContent"></view>
		<couponListWindow :coupon='coupon' @ChangCouponsClone="ChangCouponsClone" :openType='openType' :cartId='cartId'
			@ChangCoupons="ChangCoupons"></couponListWindow>
		<addressWindow ref="addressWindow" @changeTextareaStatus="changeTextareaStatus" :news='news' :address='address'
			:pagesUrl="pagesUrl" @OnChangeAddress="OnChangeAddress" @changeClose="changeClose"
			@onHaveAddressList="onHaveAddressList"></addressWindow>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<home v-show="!invShow"></home>
		<invoice-picker :inv-show="invShow" :inv-list="invList" :inv-checked="invChecked" :is-special="special_invoice"
			:url-query="urlQuery" @inv-close="invClose" @inv-change="invChange" @inv-cancel="invCancel">
		</invoice-picker>
		<canvas canvas-id="canvas" v-if="canvasStatus"
			:style="{width: canvasWidth + 'px', height: canvasHeight + 'px',position: 'absolute',left:'-100000px',top:'-100000px'}"></canvas>
	</view>
</template>
<script>
	import {
		orderConfirm,
		getCouponsOrderPrice,
		orderCreate,
		postOrderComputed,
		checkShipping,
		getGiftOrderDetail,
		orderReceiveGift
	} from '@/api/order.js';
	import {
		getAddressDefault,
		getAddressDetail,
		invoiceList,
		invoiceOrder
	} from '@/api/user.js';
	import {
		openPaySubscribe
	} from '@/utils/SubscribeMessage.js';
	import {
		storeListApi
	} from '@/api/store.js';
	import {
		CACHE_LONGITUDE,
		CACHE_LATITUDE
	} from '@/config/cache.js';
	import couponListWindow from '@/components/couponListWindow';
	import addressWindow from '@/components/addressWindow';
	import orderGoods from '@/components/orderGoods';
	import home from '@/components/home';
	import invoicePicker from '../components/invoicePicker/index.vue';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import payment from '@/components/payment';
	import colors from "@/mixins/color";
	import Debounce from "@/mixins/debounce";
	export default {
		components: {
			payment,
			invoicePicker,
			couponListWindow,
			addressWindow,
			orderGoods,
			home,
			// #ifdef MP
			authorize
			// #endif
		},
		mixins: [colors, Debounce],
		data() {
			const currentDate = this.getDate({
				format: true
			})
			return {
				confirm: '', //自定义留言
				date: this.$t(`请选择`),
				time: this.$t(`请选择`),

				canvasWidth: "",
				canvasHeight: "",
				canvasStatus: false,
				newImg: [],


				textareaStatus: true,
				//支付方式
				cartArr: [{
						"name": this.$t(`微信支付`),
						"icon": "icon-weixin2",
						value: 'weixin',
						title: this.$t(`使用微信快捷支付`),
						payStatus: 1,
					},
					{
						"name": this.$t(`支付宝支付`),
						"icon": "icon-zhifubao",
						value: 'alipay',
						title: this.$t(`使用支付宝支付`),
						payStatus: 1,
					},
					{
						"name": this.$t(`余额支付`),
						"icon": "icon-yuezhifu",
						value: 'yue',
						title: this.$t(`可用余额`),
						payStatus: 1,
					},
					{
						"name": this.$t(`线下支付`),
						"icon": "icon-yuezhifu1",
						value: 'offline',
						title: this.$t(`使用线下付款`),
						payStatus: 2,
					}, {
						"name": this.$t(`好友代付`),
						"icon": "icon-haoyoudaizhifu",
						value: 'friend',
						title: this.$t(`找微信好友支付`),
						payStatus: 1,
					}

				],
				virtual_type: 0,
				allPrice: 0,
				formContent: '',
				payType: '', //支付方式
				openType: 1, //优惠券打开方式 1=使用
				active: 0, //支付方式切换
				coupon: {
					coupon: false,
					list: [],
					statusTile: this.$t(`立即使用`)
				}, //优惠券组件
				address: {
					address: false
				}, //地址组件
				addressInfo: {}, //地址信息
				pinkId: 0, //拼团id
				addressId: 0, //地址id
				couponId: 0, //优惠券id
				cartId: '', //购物车id
				orderId: '', // 订单id, 领取礼物页面传参
				BargainId: 0,
				combinationId: 0,
				seckillId: 0,
				discountId: 0,
				userInfo: {}, //用户信息
				mark: '', //备注信息
				couponTitle: this.$t(`请选择`), //优惠券
				coupon_price: 0, //优惠券抵扣金额
				useIntegral: false, //是否使用积分
				integral_price: 0, //积分抵扣金额
				integral: 0,
				usable_integral: 0,
				ChangePrice: 0, //使用积分抵扣变动后的金额
				formIds: [], //收集formid
				status: 0,
				is_address: false,
				toPay: false, //修复进入支付时页面隐藏从新刷新页面
				shippingType: 0,
				system_store: {},
				storePostage: 0,
				advanceId: 0,
				gift_mark: '这是送您的一份礼物~', // 礼物留言
				contacts: '',
				contactsTel: '',
				mydata: {},
				storeList: [],
				store_self_mention: 0,
				cartInfo: [],
				priceGroup: {},
				animated: false,
				totalPrice: 0,
				integralRatio: "0",
				pagesUrl: "",
				orderKey: "",
				// usableCoupon: {},
				offlinePostage: "",
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				from: '',
				news: 1,

				// invTitle: '不开发票',
				invTitle: this.$t(`不开发票`),
				special_invoice: false,
				invoice_func: false,
				header_type: '',
				invShow: false,
				invList: [],
				invChecked: '',
				urlQuery: '',
				pay_close: false,
				noCoupon: 0,
				valid_count: 0,
				discount_id: 0,
				is_shipping: true,
				inputTrip: false,
				focus: true,
				integral_open: false,
				jumpData: {},
				is_gift: 0, // 1 购买的礼品 2领取礼品
				giftData: null
			};
		},
		computed: mapGetters(['isLogin']),
		// watch: {
		// 	startDate() {
		// 		return this.getDate('start');
		// 	},
		// 	endDate() {
		// 		return this.getDate('end');
		// 	}
		// },
		onLoad(options) {
			// #ifdef H5
			this.from = this.$wechat.isWeixin() ? 'weixin' : 'weixinh5'
			// #endif
			// #ifdef MP
			this.from = 'routine'
			// #endif
			// #ifdef APP-PLUS
			this.from = 'app'
			// #endif
			if (!options.cartId && !options.order_id) return this.$util.Tips({
				title: this.$t(`请选择要购买的商品`)
			}, {
				tab: 3,
				url: 1
			});
			if(options.is_gift){
				this.is_gift = Number(options.is_gift);
			}
			this.couponId = options.couponId || 0;
			this.noCoupon = Number(options.noCoupon) || 0;
			this.pinkId = options.pinkId ? parseInt(options.pinkId) : 0;
			this.addressId = options.addressId || 0;
			this.cartId = options.cartId;
			this.orderId = options.order_id || 0
			this.is_address = options.is_address ? true : false;
			this.news = !options.new || options.new === '0' ? 0 : 1;
			this.invChecked = options.invoice_id || '';
			this.header_type = options.header_type || '1';
			this.couponTitle = options.couponTitle || this.$t(`请选择`)
			if (options.invoice_id) {
				let name = ''
				name += options.header_type == 1 ? this.$t(`个人`) : this.$t(`企业`);
				name += options.invoice_type == 1 ? this.$t(`普通`) : this.$t(`专用`);
				name += this.$t(`发票`);
				this.invTitle = name;
			}
			// #ifndef APP-PLUS
			this.textareaStatus = true;
			// #endif
			if (this.isLogin && this.toPay == false && (this.is_gift == 0 || this.is_gift == 1)) {
				this.checkShipping();
			}else if(this.is_gift && this.isLogin){
				this.getOrderDetail()
			} else {
				toLogin();
			}
		},
		/**
		 * 生命周期函数--监听页面显示
		 */
		onShow: function() {
			let _this = this

			uni.$on("handClick", res => {
				if (res) {
					_this.system_store = res.address
				}
				// 清除监听
				uni.$off('handClick');
			})

		},
		methods: {
			checkShipping() {
				let that = this;
				checkShipping(that.cartId, that.news).then(res => {
					if (res.data.type == 0) {
						that.is_shipping = true;
						that.shippingType = 0;
						this.getaddressInfo();
						this.getConfirm();
						this.$nextTick(function() {
							this.$refs.addressWindow.getAddressList();
						})
					} else {
						if (res.data.type == 1) {
							that.is_shipping = false;
							that.shippingType = 0;
							this.getaddressInfo();
							this.getConfirm();
							this.$nextTick(function() {
								this.$refs.addressWindow.getAddressList();
							})
						} else if (res.data.type == 2) {
							that.is_shipping = false;
							that.shippingType = 1;
							this.addressType(1)
							this.getConfirm();
							this.getList();
						}
					}
				}).catch(err => {
					uni.showToast({
						title: err,
						icon: 'none'
					});
				});
			},

			// 不开发票
			invCancel() {
				this.invChecked = '';
				this.invTitle = this.$t(`不开发票`)
				this.invShow = false;
			},
			// 选择发票
			invChange(id) {
				let name = '';
				this.invChecked = id;
				this.invShow = false;
				const result = this.invList.find(item => item.id === id);
				name += result.header_type === 1 ? this.$t(`个人`) : this.$t(`企业`);
				name += result.type === 1 ? this.$t(`普通`) : this.$t(`专用`);
				name += this.$t(`发票`);
				this.invTitle = name;
			},
			openList() {
				if (this.shippingType == 0) {
					this.onAddress()
				} else {
					this.showStoreList()
				}
			},
			// 关闭发票
			invClose() {
				this.invShow = false;
				this.getInvoiceList()
			},
			getInvoiceList() {
				uni.showLoading({
					title: this.$t(`正在加载中`)
				})
				invoiceList().then(res => {
					uni.hideLoading();
					this.invList = res.data.map(item => {
						item.id = item.id.toString();
						return item;
					});
					const result = this.invList.find(item => item.id == this.invChecked);
					if (result) {
						let name = '';
						name += result.header_type === 1 ? this.$t(`个人`) : this.$t(`企业`);
						name += result.type === 1 ? this.$t(`普通`) : this.$t(`专用`);
						name += this.$t(`发票`);
						this.invTitle = name;
					}
				}).catch(err => {
					uni.showToast({
						title: err,
						icon: 'none'
					});
				});
			},
			/**
			 * 开发票
			 */
			goInvoice: function() {
				this.getInvoiceList()
				this.invShow = true;
				this.urlQuery =
					`new=${this.news}&cartId=${this.cartId}&pinkId=${this.pinkId}&couponId=${this.couponId}&addressId=${this.addressId}&specialInvoice=${this.special_invoice}&couponTitle=${this.couponTitle}`;
			},
			/**
			 * 授权回调事件
			 * 
			 */
			onLoadFun: function() {
				this.getaddressInfo();
				this.getConfirm();
				//调用子页面方法授权后执行获取地址列表
				// this.$scope.selectComponent('#address-window').getAddressList();
			},
			/**
			 * 事件回调
			 *
			 */
			onChangeFun: function(e) {
				let opt = e;
				let action = opt.action || null;
				let value = opt.value != undefined ? opt.value : null;
				action && this[action] && this[action](value);
			},
			payClose: function() {
				this.pay_close = false;
			},
			goPay() {
				this.pay_close = true;
			},
			payCheck(type) {
				this.payType = type;
				this.SubOrder();
			},
			/**
			 * 获取门店列表数据
			 */
			getList: function() {
				let longitude = uni.getStorageSync("user_longitude") || ''; //经度
				let latitude = uni.getStorageSync("user_latitude") || ''; //纬度
				let data = {
					latitude: latitude, //纬度
					longitude: longitude, //经度
					page: 1,
					limit: 10
				}
				storeListApi(data).then(res => {
					let list = res.data.list.list || [];
					this.$set(this, 'storeList', list);
					this.$set(this, 'system_store', list[0]);
				}).catch(err => {})
			},
			// 关闭地址弹窗；
			changeClose: function() {
				this.$set(this.address, 'address', false);
			},
			/*
			 * 跳转门店列表
			 */
			showStoreList: function() {
				let _this = this
				if (this.storeList.length > 0) {
					uni.navigateTo({
						url: '/pages/goods/goods_details_store/index'
					})
				}
			},
			changePayType(type) {
				this.payType = type
				this.computedPrice()
			},
			computedPrice() {
				let shippingType = this.shippingType;
				let data = {
					addressId: this.addressId,
					useIntegral: this.useIntegral ? 1 : 0,
					couponId: this.couponId,
					shipping_type: parseInt(shippingType) + 1,
					payType: this.payType
				}
				if (this.is_gift) data.is_gift = this.is_gift
				postOrderComputed(this.orderKey, data).then(res => {
					let result = res.data.result;
					if (result) {
						this.totalPrice = result.pay_price;
						this.integral_price = result.deduction_price;
						this.coupon_price = result.coupon_price;
						this.integral = this.useIntegral ? result.SurplusIntegral : this.usable_integral;
						this.$set(this.priceGroup, 'storePostage', shippingType == 1 ? 0 : result.pay_postage);
						this.$set(this.priceGroup, 'storePostageDiscount', result.storePostageDiscount);
					}
				})
			},
			addressType(e) {
				let index = e;
				let that = this;
				if (this.shippingType == parseInt(index)) return
				this.shippingType = parseInt(index);
				if (index == 1) {
					// #ifdef H5
					if (that.$wechat.isWeixin()) {
						that.$wechat.location().then(res => {
							uni.setStorageSync('user_latitude', res.latitude);
							uni.setStorageSync('user_longitude', res.longitude);
							this.getList()
						}).catch(err => {
							this.getList()
						})
					} else {
						// #endif	
						uni.getLocation({
							type: 'wgs84',
							success: (res) => {
								uni.setStorageSync('user_latitude', res.latitude);
								uni.setStorageSync('user_longitude', res.longitude);
							},
							complete: () => {
								this.getList()
							}
						})
						// #ifdef H5	
					}
					// #endif
				};
				this.$nextTick(e => {
					if(!this.is_gift){
						this.getConfirm();
						this.computedPrice();
					}
				})
			},
			bindPickerChange: function(e) {
				let value = e.detail.value;
				this.shippingType = value;
				this.computedPrice();
			},
			ChangCouponsClone: function() {
				this.$set(this.coupon, 'coupon', false);
			},
			changeTextareaStatus: function() {
				for (let i = 0, len = this.coupon.list.length; i < len; i++) {
					this.coupon.list[i].use_title = '';
					this.coupon.list[i].is_use = 0;
				}
				this.textareaStatus = true;
				this.status = 0;
				this.$set(this.coupon, 'list', this.coupon.list);
			},
			/**
			 * 处理点击优惠券后的事件
			 * 
			 */
			ChangCoupons: function(e) {
				// this.usableCoupon = e
				// this.coupon.coupon = false
				let index = e,
					list = this.coupon.list,
					couponTitle = this.$t(`请选择`),
					couponId = 0;
				for (let i = 0, len = list.length; i < len; i++) {
					if (i != index) {
						list[i].use_title = '';
						list[i].is_use = 0;
					}
				}
				if (list[index].is_use) {
					//不使用优惠券
					list[index].use_title = '';
					list[index].is_use = 0;
				} else {
					//使用优惠券
					list[index].use_title = this.$t(`不使用`);
					list[index].is_use = 1;
					couponTitle = list[index].coupon_title;
					couponId = list[index].id;
				}
				this.couponTitle = couponTitle;
				this.couponId = couponId;
				this.$set(this.coupon, 'coupon', false);
				this.$set(this.coupon, 'list', list);
				this.computedPrice();
			},
			/**
			 * 使用积分抵扣
			 */
			ChangeIntegral: function() {
				this.useIntegral = !this.useIntegral;
				this.computedPrice();
			},
			/**
			 * 选择地址后改变事件
			 * @param object e
			 */
			OnChangeAddress: function(e) {
				this.textareaStatus = true;
				this.addressId = e;
				this.address.address = false;
				this.getaddressInfo();
				if(!this.is_gift){
					this.getConfirm()
					this.computedPrice();
				}
			},
			bindHideKeyboard: function(e) {
				this.mark = e.detail.value;
			},
			getOrderDetail() {
				getGiftOrderDetail(this.orderId).then(res => {
					this.giftData = res.data
					this.$set(this, 'cartInfo', res.data.cartInfo);
					this.virtual_type = res.data.type
					this.store_self_mention = res.data.store_self_mention
					if (res.data.type == 0) {
						this.is_shipping = true;
						this.shippingType = 0;
						this.getaddressInfo();
						this.$nextTick(()=> {
							this.$refs.addressWindow.getAddressList();
						})
					} else {
						if (res.data.type == 1) {
							this.is_shipping = false;
							this.shippingType = 0;
							this.getaddressInfo();
							this.$nextTick(()=> {
								this.$refs.addressWindow.getAddressList();
							})
						} else if (res.data.type == 2) {
							this.is_shipping = false;
							this.shippingType = 1;
							this.addressType(1)
							this.getList();
						}
					}
				})
			},
			/**
			 * 获取当前订单详细信息
			 * 
			 */
			getConfirm: function() {
				let that = this;
				// return;
				uni.showLoading({
					title: that.$t(`正在加载中`),
					mask: true
				});
				let data = {
					cartId: that.cartId,
					new: that.news,
					addressId: that.addressId,
					shipping_type: that.shippingType + 1
				}
				if (that.is_gift) data.is_gift = that.is_gift
				orderConfirm(data).then(res => {
					that.$set(that, 'userInfo', res.data.userInfo);
					that.$set(that, 'confirm', res.data.custom_form || []);
					this.confirm.map(e => {
						if (e.label === 'img') e.value = []
					})
					that.$set(that, 'integral', res.data.usable_integral);
					that.$set(that, 'usable_integral', res.data.usable_integral);
					that.$set(that, 'contacts', res.data.userInfo.real_name);
					that.$set(that, 'contactsTel', res.data.userInfo.record_phone === '0' ? res.data.userInfo.phone : res
						.data
						.userInfo.record_phone);
					that.$set(that, 'cartInfo', res.data.cartInfo);
					that.$set(that, 'integralRatio', res.data.integralRatio);
					that.$set(that, 'offlinePostage', res.data.offlinePostage);
					that.$set(that, 'orderKey', res.data.orderKey);
					that.$set(that, 'valid_count', res.data.valid_count);
					that.$set(that, 'discount_id', res.data.discount_id)
					that.$set(that, 'priceGroup', res.data.priceGroup);
					that.$set(that, 'totalPrice', that.$util.$h.Add(parseFloat(res.data.priceGroup.totalPrice),
						parseFloat(res.data
							.priceGroup.storePostage)));
					that.$set(that, 'allPrice', that.$util.$h.Add(parseFloat(res.data.priceGroup.totalPrice),
						parseFloat(res.data
							.priceGroup.vipPrice)).toFixed(2));
					that.$set(that, 'seckillId', parseInt(res.data.seckill_id));
					that.$set(that, 'invoice_func', res.data.invoice_func);
					that.$set(that, 'special_invoice', res.data.special_invoice);
					that.$set(that, 'store_self_mention', res.data.store_self_mention);
					that.$set(that, 'virtual_type', res.data.virtual_type || 0);
					that.$set(that, 'integral_open', res.data.integral_open);
					uni.hideLoading()
					//微信支付是否开启
					that.cartArr[0].payStatus = res.data.pay_weixin_open || 0
					//支付宝是否开启
					that.cartArr[1].payStatus = res.data.ali_pay_status || 0;
					//#ifdef MP
					that.cartArr[1].payStatus = 0;
					//#endif
					//余额支付是否开启
					// that.cartArr[2].title = '可用余额:' + res.data.userInfo.now_money;
					that.cartArr[2].number = res.data.userInfo.now_money;
					that.cartArr[2].payStatus = res.data.yue_pay_status == 1 ? res.data.yue_pay_status : 0
					if (res.data.offline_pay_status == 2 || res.data.deduction) {
						that.cartArr[3].payStatus = 0
					} else {
						that.cartArr[3].payStatus = 1
					}
					//好友代付是否开启
					that.cartArr[4].payStatus = res.data.friend_pay_status || 0;
					// that.$set(that, 'cartArr', that.cartArr);
					that.$set(that, 'ChangePrice', that.totalPrice);
					that.getBargainId();
					setTimeout(() => {
						that.getCouponList();
					}, 500);
					if (this.addressId && !this.is_gift) {
						this.computedPrice();
					}
				}).catch(err => {
					uni.hideLoading()
					return this.$util.Tips({
						title: err
					});
				});
			},
			/*
			 * 提取砍价和拼团id
			 */
			getBargainId: function() {
				let that = this;
				let cartINfo = that.cartInfo;
				let BargainId = 0;
				let combinationId = 0;
				let discountId = 0;
				let advanceId = 0;
				cartINfo.forEach(function(value, index, cartINfo) {
					BargainId = cartINfo[index].bargain_id,
						combinationId = cartINfo[index].combination_id,
						discountId = cartINfo[index].discount_id,
						advanceId = cartINfo[index].advance_id
				})
				that.$set(that, 'BargainId', parseInt(BargainId));
				that.$set(that, 'combinationId', parseInt(combinationId));
				that.$set(that, 'discountId', parseInt(discountId));
				that.$set(that, 'advanceId', parseInt(advanceId));
				if (that.cartArr.length == 3 && (BargainId || combinationId || that.seckillId || discountId)) {
					that.cartArr[2].payStatus = 0;
					that.$set(that, 'cartArr', that.cartArr);
				}
			},
			/**
			 * 获取当前金额可用优惠券
			 * 
			 */
			getCouponList: function() {
				let shippingType = this.shippingType;
				let that = this;
				let data = {
					cartId: this.cartId,
					'new': this.news,
					'shippingType': parseInt(shippingType) + 1
				}
				getCouponsOrderPrice(this.totalPrice, data).then(res => {
					this.$set(this.coupon, 'list', res.data);
					if (!this.pinkId && !this.BargainId && !this.combinationId && !this.seckillId && !this.noCoupon && !this.discountId && !this.advanceId) {
						this.coupon.list.length && this.ChangCoupons(0)
					}
					this.openType = 1;
				});
			},
			/*
			 * 获取默认收货地址或者获取某条地址信息
			 */
			getaddressInfo: function() {
				let that = this;
				let fnc = that.addressId ? getAddressDetail : getAddressDefault
				fnc(that.addressId).then(res => {
					if (!Array.isArray(res.data)) {
						res.data.is_default = parseInt(res.data.is_default);
						that.addressInfo = res.data || {};
						that.addressId = res.data.id || 0;
						that.address.addressId = res.data.id || 0;
					}
				})
			},
			onHaveAddressList() {
				this.haveAddressList = true
			},
			payItem: function(e) {
				let that = this;
				let active = e;
				that.active = active;
				that.animated = true;
				that.payType = that.cartArr[active].value;
				that.computedPrice();
				setTimeout(function() {
					that.car();
				}, 500);
			},
			couponTap: function() {
				this.coupon.coupon = true;
				this.coupon.list.forEach((item, index) => {
					if (item.id == this.couponId) {
						item.is_use = 1
					} else {
						item.is_use = 0
					}
				})
				this.$set(this.coupon, 'list', this.coupon.list);
			},
			car: function() {
				let that = this;
				that.animated = false;
			},
			onAddress: function() {
				let that = this;
				if (this.addressInfo.real_name || this.haveAddressList) {
					this.$refs.addressWindow.getAddressList();
					that.textareaStatus = false;
					that.address.address = true;
					that.pagesUrl = '/pages/users/user_address_list/index?news=' + this.news + '&cartId=' + this
						.cartId +
						'&pinkId=' +
						this.pinkId +
						'&couponId=' +
						this.couponId + 
						'&is_gift=' +
						that.is_gift + 
						'&order_id=' + that.orderId
				} else {
					let url = '/pages/users/user_address/index?cartId=' + this.cartId + '&pinkId=' + this
							.pinkId +
							'&couponId=' + this.couponId + '&new=' + this.news + '&is_gift=' + that.is_gift + '&order_id=' + that.orderId
					uni.navigateTo({
						url: url
					})
				}
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
			payment(data) {
				let that = this;
				orderCreate(that.orderKey, data).then(res => {
					let url = `/pages/goods/cashier/index?order_id=${res.data.result.orderId}&from_type=order`
					uni.reLaunch({
						url
					})
				}).catch(err => {
					uni.hideLoading();
					return that.$util.Tips({
						title: err
					});
				});
			},
			clickTextArea() {
				this.$refs.textarea.focus()
			},
			SubOrder(e) {
				let that = this,
					data = {};
				if (!that.addressId && !that.shippingType && !that.virtual_type && !that.is_gift) return that.$util.Tips({
					title: that.$t(`请选择收货地址`)
				});
				if (that.shippingType == 1) {
					if (that.contacts == "" || that.contactsTel == "") {
						return that.$util.Tips({
							title: that.$t(`请填写联系人或联系人电话`)
						});
					}
					if (!/^1(3|4|5|7|8|9|6)\d{9}$/.test(that.contactsTel)) {
						return that.$util.Tips({
							title: that.$t(`请输入正确的手机号码`)
						});
					}
					if (!that.contacts) {
						return that.$util.Tips({
							title: that.$t(`请输入姓名`)
						});
					}
					if (that.storeList.length == 0) return that.$util.Tips({
						title: that.$t(`暂无门店,请选择其他方式`)
					});
				}
				for (var i = 0; i < that.confirm.length; i++) {
					let data = that.confirm[i]
					if (data.status) {
						if (data.label === 'text' || data.label === 'data' || data.label === 'time' || data.label ===
							'id') {
							if (!data.value.trim()) {
								return uni.showToast({
									title: that.$t(`请输入`) + `${data.title}`,
									icon: 'none'
								});
							}
						}
						if (data.label === 'number') {
							if (data.value <= 0) {
								return uni.showToast({
									title: that.$t(`请输入`) + `${data.title}`,
									icon: 'none'
								});
							}
						}
						if (data.label === 'email') {
							if (!/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(data.value)) {
								return uni.showToast({
									title: that.$t(`请输入正确的`) + `${data.title}`,
									icon: 'none'
								});
							}
						}
						if (data.label === 'phone') {
							if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(data.value)) {
								return uni.showToast({
									title: that.$t(`请输入正确的`) + `${data.title}`,
									icon: 'none'
								});
							}
						}
						if (data.label === 'img') {
							if (!data.value.length) {
								return uni.showToast({
									title: that.$t(`请上传`) + `${data.title}`,
									icon: 'none'
								});
							}
						}
					}
				}
				data = {
					custom_form: that.confirm,
					gift_mark: that.gift_mark, // 礼物留言
					real_name: that.contacts,
					phone: that.contactsTel,
					addressId: that.addressId,
					formId: '',
					couponId: that.couponId,
					useIntegral: that.useIntegral,
					bargainId: that.BargainId,
					combinationId: that.combinationId,
					discountId: that.discountId,
					pinkId: that.pinkId,
					advanceId: that.advanceId,
					seckill_id: that.seckillId,
					mark: that.mark,
					store_id: that.system_store ? that.system_store.id : 0,
					'from': that.from,
					shipping_type: that.$util.$h.Add(that.shippingType, 1),
					'new': that.news,
					'invoice_id': that.invChecked,
					// #ifdef H5
					quitUrl: location.protocol + '//' + location.hostname +
						'/pages/goods/order_pay_status/index?' +
						'&type=3' + '&totalPrice=' + this.totalPrice
					// #endif
					// #ifdef APP-PLUS
					quitUrl: '/pages/goods/order_details/index?order_id=' + this.order_id
					// #endif
				};
				if (that.is_gift) data.is_gift = that.is_gift
				if (data.payType == 'yue' && parseFloat(that.userInfo.now_money) < parseFloat(that.totalPrice))
					return that.$util.Tips({
						title: that.$t(`余额不足`)
					});
				// uni.showLoading({
				// 	title: that.$t(`订单支付中`)
				// });
				// #ifdef MP
				openPaySubscribe().then(() => {
					that.payment(data);
				});
				// #endif
				// #ifndef MP
				that.payment(data);
				// #endif
			},
			receiveGift() {
				let data = {
					gift_key: this.giftData.gift_key,
					shipping_type: this.$util.$h.Add(this.shippingType, 1),
					name: this.contacts,
					phone: this.contactsTel,
					address_id: this.addressId,
					store_id: this.system_store ? this.system_store.id : 0,
				}
				orderReceiveGift(this.orderId, data).then(res => {
					uni.reLaunch({
						url: `/pages/goods/receive_gifts_status/index?status=${res.data.status}&order_id=${this.giftData.order_id}`
					})
				}).catch(err => {
					uni.showToast({
						title: err.msg
					})
				})
			},
			bindDateChange: function(e, index) {
				this.confirm[index].value = e.target.value
			},
			bindTimeChange: function(e, index) {
				this.confirm[index].value = e.target.value
			},
			getDate(type) {
				const date = new Date();
				let year = date.getFullYear();
				let month = date.getMonth() + 1;
				let day = date.getDate();

				if (type === 'start') {
					year = year - 60;
				} else if (type === 'end') {
					year = year + 2;
				}
				month = month > 9 ? month : '0' + month;
				day = day > 9 ? day : '0' + day;
				return `${year}-${month}-${day}`;
			},
			uploadpic: function(index, item) {
				let that = this;
				this.canvasStatus = true
				that.$util.uploadImageChange('upload/image', function(res) {
					item.value.push(res.data.url);
				}, (res) => {
					this.canvasStatus = false
				}, (res) => {
					this.canvasWidth = res.w
					this.canvasHeight = res.h
				});
			},
			DelPic: function(index, indexs) {
				let that = this,
					pic = this.confirm[index].value;
				that.confirm[index].value.splice(indexs, 1);
				// that.$set(that, 'pics', that.pics);
			},
			inputTripClick() {
				this.inputTrip = true
				// this.$refs.trip.foucs()
			},
			showMaoLocation(e) {
				let self = this;
				// #ifdef H5
				if (self.$wechat.isWeixin()) {
					self.$wechat.seeLocation({
						latitude: Number(e.latitude),
						longitude: Number(e.longitude),
						name: e.name,
						scale: 13,
						address: `${e.address}-${e.detailed_address}`,
					}).then(res => {})
				} else {
					// #endif	
					uni.openLocation({
						latitude: Number(e.latitude),
						longitude: Number(e.longitude),
						name: e.name,
						address: `${e.address}-${e.detailed_address}`,
						success: function() {
							Number
						}
					});
					// #ifdef H5	
				}
				// #endif
			},
			call(phone) {
				uni.makePhoneCall({
					phoneNumber: phone,
				});
			},
		}
	}
</script>

<style lang="scss" scoped>
	/deep/uni-checkbox[disabled] .uni-checkbox-input {
		background-color: #eee;
	}

	.alipaysubmit {
		display: none;
	}

	.order-submission {
		/* #ifndef H5 */
		padding-bottom: 70rpx;
		/* #endif */
	}

	.order-submission .line {
		width: 100%;
		height: 3rpx;
	}

	.order-submission .line image {
		width: 100%;
		height: 100%;
		display: block;
	}

	.order-submission .address {
		padding: 28rpx 30rpx;
		background-color: #fff;
		box-sizing: border-box;
		flex-wrap: nowrap;

		.icon {
			.iconfont {
				width: 44rpx;
				height: 44rpx;
				background: var(--view-minorColorT);
				font-size: 20rpx;
				border-radius: 50%;
				text-align: center;
				line-height: 44rpx;
				color: var(--view-theme);
				margin-left: 26rpx;
			}
		}
	}

	.order-submission .address .addressCon {
		max-width: 510rpx;
		font-size: 26rpx;
		color: #666;
	}

	.order-submission .address .addressCon .name {
		font-size: 30rpx;
		color: #282828;
		font-weight: bold;
		margin-bottom: 10rpx;
	}

	.order-submission .address .addressCon .name .phone {
		margin-left: 50rpx;
	}

	.order-submission .address .addressCon .default {
		margin-right: 12rpx;
	}

	.order-submission .address .addressCon .setaddress {
		color: #333;
		font-size: 28rpx;
	}

	.order-submission .address .iconfont {
		font-size: 35rpx;
		color: #707070;
	}

	.gift-box {
		background: #FFFFFF;
		margin: 20rpx 0;

		.user-msg {
			padding: 28rpx 30rpx;
		}

		image {
			width: 36rpx;
			height: 36rpx;
			border-radius: 70rpx 70rpx 70rpx 70rpx;
			border: 2rpx solid #FFFFFF;
		}

		.nickname {
			font-weight: 400;
			font-size: 28rpx;
			color: #AE5A2A;
		}

		.gift-mark {
			border-top: 1px solid #F0F0F0;
			font-weight: 400;
			font-size: 28rpx;
			color: #333333;
			padding: 28rpx 30rpx;
		}
	}

	.receive-btn {
		color: #fff;
		text-align: center;
		height: 84rpx;
		line-height: 84rpx;
		border-radius: 80rpx;
		margin: 66rpx 40rpx;
		background-color: var(--view-theme);
	}

	.gift-msg {
		padding: 0rpx 30rpx 20rpx 30rpx;
		background-color: #fff;

		input {
			font-size: 30rpx;
		}

		.placeholder {
			font-size: 28rpx;
		}
	}

	.order-submission .allAddress {
		width: 100%;
		background: linear-gradient(to bottom, var(--view-theme) 0%, #f5f5f5 100%);
		padding-top: 100rpx;
		margin-bottom: 12rpx;

		.add-title {
			height: 72rpx;
			margin: 0 26rpx;
			border-bottom: 1px solid #eee;
			background-color: #fff;
			width: 710rpx;
			margin: 0 auto;
			padding: 0 26rpx;
			border-radius: 12rpx 12rpx 0 0;

			.icon {
				height: 32rpx;
				background: #1890FF;
				border-radius: 4rpx;
				font-size: 20rpx;
				font-weight: 400;
				color: #FFFFFF;
				text-align: center;
				line-height: 32rpx;
				padding: 0 6rpx;

				&.orange {
					background: #FE960F;
				}

				&.red {
					background: #E93323;
				}
			}

			.add-text {
				margin-left: 14rpx;
				width: 360rpx;
			}

			.text {
				font-size: 24rpx;
				font-weight: 400;
				color: #999999;

				.icon-jiantou {
					display: inline-block;
					font-size: 20rpx;
				}
			}
		}
	}

	.order-submission .allAddress .nav {
		width: 710rpx;
		margin: 0 auto;
	}

	.order-submission .allAddress .nav .item {
		width: 355rpx;
	}

	.order-submission .allAddress .nav .item.on {
		position: relative;
		width: 270rpx;
	}

	.order-submission .allAddress .nav .item.on .before {
		position: absolute;
		bottom: 0;
		// content: "快递配送";
		left: 0;
		font-size: 28rpx;
		display: block;
		height: 0;
		width: 356rpx;
		border-width: 0 20rpx 80rpx 0;
		border-style: none solid solid;
		border-color: transparent transparent #fff;
		z-index: 2;
		border-radius: 7rpx 30rpx 0 0;
		text-align: center;
		line-height: 80rpx;
	}

	.order-submission .allAddress .nav .item:nth-of-type(2).on .before {
		// content: "到店自提";
		border-width: 0 0 80rpx 20rpx;
		border-radius: 30rpx 7rpx 0 0;
	}

	.order-submission .allAddress .nav .item.on2 {
		position: relative;
	}

	.order-submission .allAddress .nav .item.on2 .before {
		position: absolute;
		bottom: 0;
		left: 0;
		// content: "到店自提";
		font-size: 28rpx;
		display: block;
		height: 0;
		width: 440rpx;
		border-width: 0 0 60rpx 60rpx;
		border-style: none solid solid;
		border-color: transparent transparent rgba(255, 255, 255, 0.6);
		border-radius: 40rpx 6rpx 0 0;
		text-align: center;
		line-height: 60rpx;
	}

	.order-submission .allAddress .nav .item:nth-of-type(1).on2 .before {
		// content: "快递配送";
		border-width: 0 60rpx 60rpx 0;
		border-radius: 6rpx 40rpx 0 0;
	}

	.order-submission .allAddress .address {
		width: 710rpx;
		height: 150rpx;
		margin: 0 auto;
	}

	.order-submission .allAddress .line {
		width: 710rpx;
		margin: 0 auto;
	}

	.order-submission .wrapper .item .discount .placeholder {
		color: #ccc;
	}

	.placeholder-textarea {
		position: relative;

		.placeholder {
			position: absolute;
			color: #ccc;
			top: 26rpx;
			left: 30rpx;
		}
	}

	.order-submission .wrapper {
		background-color: #fff;
		margin-top: 13rpx;
	}

	.order-submission .wrapper .item {
		padding: 27rpx 30rpx;
		font-size: 30rpx;
		color: #282828;
		border-bottom: 1px solid #f0f0f0;

		.mark {
			background-color: #f9f9f9;
			// width: 345px;
			min-height: 70px;
			border-radius: 1px;
			margin-top: 15px;
			padding: 12px 14px;
			color: #ccc;
			font-size: 28rpx;
			box-sizing: border-box;
		}

		.mark-msg {
			color: #333;
			font-size: 28rpx;
		}
	}

	.order-submission .wrapper .item .discount {
		font-size: 30rpx;
		color: #999;
	}

	.order-submission .wrapper .item .discount input {
		text-align: end;
	}

	.order-submission .wrapper .item .discount .iconfont {
		color: #515151;
		font-size: 30rpx;
		margin-left: 15rpx;
	}

	.order-submission .wrapper .item .discount .num {
		font-size: 32rpx;
		margin-right: 20rpx;
	}

	.order-submission .wrapper .item .shipping {
		font-size: 30rpx;
		color: #999;
		position: relative;
		padding-right: 58rpx;
	}

	.order-submission .wrapper .item .shipping .iconfont {
		font-size: 35rpx;
		color: #707070;
		position: absolute;
		right: 0;
		top: 50%;
		transform: translateY(-50%);
		margin-left: 30rpx;
	}

	.order-submission .wrapper .item textarea {
		background-color: #f9f9f9;
		width: 100%;
		height: 135rpx;
		border-radius: 3rpx;
		margin-top: 30rpx;
		padding: 25rpx 28rpx;
		font-size: 28rpx;
		box-sizing: border-box;
	}

	.order-submission .wrapper .item .placeholder {
		color: #ccc;
		font-size: 28rpx;
	}

	.order-submission .wrapper .item .list {
		margin-top: 35rpx;
	}

	.order-submission .wrapper .item .list .payItem {
		border: 1px solid #eee;
		border-radius: 6rpx;
		height: 86rpx;
		width: 100%;
		box-sizing: border-box;
		margin-top: 20rpx;
		font-size: 28rpx;
		color: #282828;
	}

	.order-submission .wrapper .item .list .payItem.on {
		border-color: #fc5445;
		color: #e93323;
	}

	.order-submission .wrapper .item .list .payItem .name {
		width: 50%;
		text-align: center;
		border-right: 1px solid #eee;
		padding-left: 80rpx;
	}

	.order-submission .wrapper .item .list .payItem .name .iconfont {
		width: 44rpx;
		height: 44rpx;
		border-radius: 50%;
		text-align: center;
		line-height: 44rpx;
		background-color: #fe960f;
		color: #fff;
		font-size: 30rpx;
		margin-right: 15rpx;
	}

	.order-submission .wrapper .item .list .payItem .name .iconfont.icon-weixin2 {
		background-color: #41b035;
	}

	.order-submission .wrapper .item .list .payItem .name .iconfont.icon-zhifubao {
		background-color: #1677FF;
	}

	.order-submission .wrapper .item .list .payItem .tip {
		width: 49%;
		text-align: center;
		font-size: 26rpx;
		color: #aaa;
	}

	.order-submission .moneyList {
		margin-top: 12rpx;
		background-color: #fff;
		padding: 30rpx;
	}

	.order-submission .moneyList .item {
		font-size: 28rpx;
		color: #282828;
	}

	.order-submission .moneyList .item~.item {
		margin-top: 20rpx;
	}

	.order-submission .moneyList .item .money {
		color: #868686;
	}

	.order-submission .footer {
		width: 100%;
		background-color: #fff;
		font-size: 28rpx;
		color: #333;
		box-sizing: border-box;
		position: fixed;
		left: 0;
		bottom: 0;
		padding: 15rpx 30rpx;
		padding-bottom: calc(15rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		padding-bottom: calc(15rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		z-index: 9;
	}

	.order-submission .footer .settlement {
		font-size: 30rpx;
		color: #fff;
		width: 240rpx;
		height: 70rpx;
		background-color: var(--view-theme);
		border-radius: 50rpx;
		text-align: center;
		line-height: 70rpx;
	}

	.footer .transparent {
		opacity: 0
	}

	.confirm {
		text-align: right;
		font-size: 22rpx;
	}

	.confirmImg {
		width: 100%;

		.img {
			width: 136rpx;
			height: 136rpx;
		}

		.pictrue {
			width: 136rpx;
			height: 136rpx;
			box-sizing: border-box;
			margin: 18rpx;
			margin-bottom: 35rpx;
			position: relative;
			font-size: 22rpx;
			color: #bbb;

			.del {
				position: absolute;
				top: 0;
				right: 0;
			}
		}

		.bor {
			border: 1rpx solid #ddd;
		}

	}

	.fontC {
		color: grey;
	}
</style>