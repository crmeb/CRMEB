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
							{{shippingType==0 ? $t(`商城配送`) : $t(`门店自提`)}}
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
					<text class="nickname">{{ giftData.nickname}} {{ $t(`赠您一份礼物，请查收！`) }}</text>
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
				{{ $t(`立即领取`) }}
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
							<checkbox :disabled="integral<=0 && !useIntegral" :checked='useIntegral ? true : false' backgroundColor="#ffffff" activeBackgroundColor="var(--view-theme, #E93323)" activeBorderColor="var(--view-theme, #E93323)" iconColor="#ffffff" />
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
					v-if="priceGroup.payPostage > 0 || priceGroup.postageDiscount > 0">
					<view>{{$t(`配送运费`)}}：</view>
					<view class='money'>
						{{$t(`￥`)}}{{(parseFloat(priceGroup.payPostage)+parseFloat(priceGroup.postageDiscount)).toFixed(2)}}
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
				<view class='item acea-row row-between-wrapper' v-if="priceGroup.postageDiscount > 0">
					<view>{{$t(`会员运费优惠`)}}：</view>
					<view class='money'>-{{$t(`￥`)}}{{parseFloat(priceGroup.postageDiscount).toFixed(2)}}</view>
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
		<addressWindow ref="addressWindowRef" @changeTextareaStatus="changeTextareaStatus" :news='news' :address='address'
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
<script setup>
	import { ref, reactive, nextTick } from 'vue';
	import { onLoad, onShow } from '@dcloudio/uni-app';
	import { storeToRefs } from 'pinia';
	import {
			centerOrderConfirm,
			getCouponsOrderPrice,
			centerOrderCreate,
			orderPay,
			centerOrderComputed,
			// 导入重命名 checkShippingApi：与本地方法 checkShipping 同名冲突
			checkShipping as checkShippingApi,
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
	} from '@/utils/subscribeMessage.js';
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
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	// #ifdef H5
	import Auth from '@/libs/wechat';
	// #endif
	// 组件导入重命名 paymentComponent：与本地方法 payment 同名冲突（原 Options API 组件/方法分属不同命名空间）
	import paymentComponent from '@/components/payment';
	import { useColorTheme } from '@/composables/useColorTheme';
	// 原 mixins/debounce 的 this.Debounce 改为直接导入（二者实现一致：每次调用生成新的防抖包装）
	import { Debounce } from '@/utils/validate.js';
	// 统一支付结果页路由常量
	import routes from '@/dynamic-pages/api/routes.js';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';
	import { useAppStore } from '@/store/modules/app';

	defineOptions({ name: 'OrderConfirm' });

	const { colorStyle } = useColorTheme();
	const appStore = useAppStore();
	const { isLogin } = storeToRefs(appStore);

	// ---------- data ----------
	// 原 data() 中 this.getDate({ format: true }) 的求值结果（未被使用，保持原样）
	const currentDate = getDate({
		format: true
	});
	const confirm = ref(''); //自定义留言
	const date = ref(i18n.t(`请选择`));
	const time = ref(i18n.t(`请选择`));

	const canvasWidth = ref("");
	const canvasHeight = ref("");
	const canvasStatus = ref(false);
	const newImg = ref([]);


	const textareaStatus = ref(true);
	//支付方式
	const cartArr = ref([{
			"name": i18n.t(`微信支付`),
			"icon": "icon-weixin2",
			value: 'weixin',
			title: i18n.t(`使用微信快捷支付`),
			payStatus: 1,
		},
		{
			"name": i18n.t(`支付宝支付`),
			"icon": "icon-zhifubao",
			value: 'alipay',
			title: i18n.t(`使用支付宝支付`),
			payStatus: 1,
		},
		{
			"name": i18n.t(`余额支付`),
			"icon": "icon-yuezhifu",
			value: 'yue',
			title: i18n.t(`可用余额`),
			payStatus: 1,
		},
		{
			"name": i18n.t(`线下支付`),
			"icon": "icon-yuezhifu1",
			value: 'offline',
			title: i18n.t(`使用线下付款`),
			payStatus: 2,
		}, {
			"name": i18n.t(`好友代付`),
			"icon": "icon-haoyoudaizhifu",
			value: 'friend',
			title: i18n.t(`找微信好友支付`),
			payStatus: 1,
		}

	]);
	const virtual_type = ref(0);
	const allPrice = ref(0);
	const formContent = ref('');
	const payType = ref(''); //支付方式
	const openType = ref(1); //优惠券打开方式 1=使用
	const active = ref(0); //支付方式切换
	const coupon = reactive({
		coupon: false,
		list: [],
		statusTile: i18n.t(`立即使用`)
	}); //优惠券组件
	const address = reactive({
		address: false
	}); //地址组件
	const addressInfo = ref({}); //地址信息
	const pinkId = ref(0); //拼团id
	const addressId = ref(0); //地址id
	const couponId = ref(0); //优惠券id
	const cartId = ref(''); //购物车id
	const orderId = ref(''); // 订单id, 领取礼物页面传参
	const BargainId = ref(0);
	const combinationId = ref(0);
	const seckillId = ref(0);
	const discountId = ref(0);
	const userInfo = ref({}); //用户信息
	const mark = ref(''); //备注信息
	const couponTitle = ref(i18n.t(`请选择`)); //优惠券
	const coupon_price = ref(0); //优惠券抵扣金额
	const useIntegral = ref(false); //是否使用积分
	const integral_price = ref(0); //积分抵扣金额
	const integral = ref(0);
	const usable_integral = ref(0);
	const ChangePrice = ref(0); //使用积分抵扣变动后的金额
	const formIds = ref([]); //收集formid
	const status = ref(0);
	const is_address = ref(false);
	const toPay = ref(false); //修复进入支付时页面隐藏从新刷新页面
	const shippingType = ref(0);
	const system_store = ref({});
	const storePostage = ref(0);
	const advanceId = ref(0);
	const gift_mark = ref(i18n.t(`这是送您的一份礼物~`)); // 礼物留言
	const contacts = ref('');
	const contactsTel = ref('');
	const mydata = ref({});
	const storeList = ref([]);
	const store_self_mention = ref(0);
	const cartInfo = ref([]);
	const priceGroup = ref({});
	const animated = ref(false);
	const totalPrice = ref(0);
	const integralRatio = ref("0");
	const pagesUrl = ref("");
	const orderKey = ref("");
	// usableCoupon: {},
	const offlinePostage = ref("");
	const isAuto = ref(false); //没有授权的不会自动授权
	const isShowAuth = ref(false); //是否隐藏授权
	const from = ref('');
	const news = ref(1);

	// invTitle: '不开发票',
	const invTitle = ref(i18n.t(`不开发票`));
	const special_invoice = ref(false);
	const invoice_func = ref(false);
	const header_type = ref('');
	const invShow = ref(false);
	const invList = ref([]);
	const invChecked = ref('');
	const urlQuery = ref('');
	const pay_close = ref(false);
	const noCoupon = ref(0);
	const valid_count = ref(0);
	const discount_id = ref(0);
	const is_shipping = ref(true);
	const inputTrip = ref(false);
	const focus = ref(true);
	const integral_open = ref(false);
	const jumpData = ref({});
	const is_gift = ref(0); // 1 购买的礼品 2领取礼品
	const giftData = ref(null);
	// 中台直接购买参数
	const isDirectBuy = ref(false); // 是否为中台直接购买模式
	const productId = ref(0); // 中台商品ID
	const skuUnique = ref(''); // SKU唯一码
	const productType = ref('normal'); // 产品类型
	const activityType = ref('normal'); // 活动类型
	const activityId = ref(0); // 活动ID
	const buyNum = ref(1); // 购买数量
	const activityInfo = ref({}); // 活动信息
	const features = ref({}); // 功能开关

	// 原 data 未声明、由 onHaveAddressList 动态写入的实例属性（初始 undefined，等效 falsy），补声明为 ref
	const haveAddressList = ref(false);
	// 模板中无 ref="textarea" 节点，原 this.$refs.textarea 恒为 undefined（clickTextArea 未被模板调用，保持同等报错语义）
	const textareaRef = ref(null);
	// 与组件导入 addressWindow 同名的模板 ref 改名加 Ref 后缀
	const addressWindowRef = ref(null);

	// 遗留：SubOrder APP-PLUS 分支原取 this.order_id（data 中只有 orderId，恒为 undefined，拼接为 "undefined"），以同名 undefined 变量保持同等语义
	var order_id;

	// watch: {
	// 	startDate() {
	// 		return this.getDate('start');
	// 	},
	// 	endDate() {
	// 		return this.getDate('end');
	// 	}
	// },

	// ---------- 生命周期 ----------
	onLoad((options) => {
		// #ifdef H5
		from.value = Auth.isWeixin() ? 'weixin' : 'weixinh5'
		// #endif
		// #ifdef MP
		from.value = 'routine'
		// #endif
		// #ifdef APP-PLUS
		from.value = 'app'
		// #endif
		// 中台直接购买模式：通过 product_id 等参数直接购买
			if (options.product_id) {
				isDirectBuy.value = true;
				productId.value = parseInt(options.product_id) || 0;
				skuUnique.value = options.sku_unique || '';
				productType.value = options.product_type || 'normal';
				activityType.value = options.activity_type || 'normal';
				activityId.value = parseInt(options.activity_id) || 0;
				buyNum.value = parseInt(options.num) || 1;
				addressId.value = options.addressId || 0;
				if (isLogin.value && toPay.value == false) {
					getaddressInfo();
					getConfirm();
				} else {
					toLogin();
				}
				return;
			}

			if (!options.cartId && !options.order_id) return util.Tips({
				title: i18n.t(`请选择要购买的商品`)
			}, {
				tab: 3,
				url: 1
			});
			if(options.is_gift){
				is_gift.value = Number(options.is_gift);
			}
			couponId.value = options.couponId || 0;
			noCoupon.value = Number(options.noCoupon) || 0;
			pinkId.value = options.pinkId ? parseInt(options.pinkId) : 0;
			addressId.value = options.addressId || 0;
			cartId.value = options.cartId;
			orderId.value = options.order_id || 0
			is_address.value = options.is_address ? true : false;
			news.value = !options.new || options.new === '0' ? 0 : 1;
			invChecked.value = options.invoice_id || '';
			header_type.value = options.header_type || '1';
			couponTitle.value = options.couponTitle || i18n.t(`请选择`)
			if (options.invoice_id) {
				let name = ''
				name += options.header_type == 1 ? i18n.t(`个人`) : i18n.t(`企业`);
				name += options.invoice_type == 1 ? i18n.t(`普通`) : i18n.t(`专用`);
				name += i18n.t(`发票`);
				invTitle.value = name;
			}
			// #ifndef APP-PLUS
			textareaStatus.value = true;
			// #endif
			if (isLogin.value && toPay.value == false && (is_gift.value == 0 || is_gift.value == 1)) {
				checkShipping();
			}else if(is_gift.value && isLogin.value){
				getOrderDetail()
			} else {
				toLogin();
			}
	});
	/**
	 * 生命周期函数--监听页面显示
	 */
	onShow(() => {
		uni.$on("handClick", res => {
			if (res) {
				system_store.value = res.address
			}
			// 清除监听
			uni.$off('handClick');
		})

		// 如果当前是门店自提模式，重新获取定位以确保门店列表准确
		if (shippingType.value == 1 && !system_store.value.name) {
			refreshLocationAndStores();
		}

	});

	// ---------- methods ----------
	/**
	 * 刷新定位并更新门店列表
	 */
	function refreshLocationAndStores() {
		// #ifdef H5
		if (Auth.isWeixin()) {
			Auth.location().then(res => {
				uni.setStorageSync('user_latitude', res.latitude);
				uni.setStorageSync('user_longitude', res.longitude);
				getList()
			}).catch(err => {
				// 如果获取定位失败，仍然尝试用缓存的定位获取门店列表
				getList()
			})
		} else {
			// #endif
			uni.getLocation({
				type: 'wgs84',
				success: (res) => {
					uni.setStorageSync('user_latitude', res.latitude);
					uni.setStorageSync('user_longitude', res.longitude);
				},
				fail: (err) => {
					// 获取定位失败时的处理，仍然使用缓存定位
					console.log('获取定位失败:', err);
				},
				complete: () => {
					getList()
				}
			})
			// #ifdef H5
		}
		// #endif
	}
	function checkShipping() {
		checkShippingApi(cartId.value, news.value).then(res => {
			if (res.data.type == 0) {
				is_shipping.value = true;
				shippingType.value = 0;
				getaddressInfo();
				getConfirm();
				nextTick(function() {
					addressWindowRef.value.getAddressList();
				})
			} else {
				if (res.data.type == 1) {
					is_shipping.value = false;
					shippingType.value = 0;
					getaddressInfo();
					getConfirm();
					nextTick(function() {
						addressWindowRef.value.getAddressList();
					})
				} else if (res.data.type == 2) {
					is_shipping.value = false;
					shippingType.value = 1;
					addressType(1)
					getConfirm();
					getList();
				}
			}
		}).catch(err => {
			uni.showToast({
				title: err,
				icon: 'none'
			});
		});
	}

	// 不开发票
	function invCancel() {
		invChecked.value = '';
		invTitle.value = i18n.t(`不开发票`)
		invShow.value = false;
	}
	// 选择发票
	function invChange(id) {
		let name = '';
		invChecked.value = id;
		invShow.value = false;
		const result = invList.value.find(item => item.id === id);
		name += result.header_type === 1 ? i18n.t(`个人`) : i18n.t(`企业`);
		name += result.type === 1 ? i18n.t(`普通`) : i18n.t(`专用`);
		name += i18n.t(`发票`);
		invTitle.value = name;
	}
	function openList() {
		if (shippingType.value == 0) {
			onAddress()
		} else {
			showStoreList()
		}
	}
	// 关闭发票
	function invClose() {
		invShow.value = false;
		getInvoiceList()
	}
	function getInvoiceList() {
		uni.showLoading({
			title: i18n.t(`正在加载中`)
		})
		invoiceList().then(res => {
			uni.hideLoading();
			invList.value = res.data.map(item => {
				item.id = item.id.toString();
				return item;
			});
			const result = invList.value.find(item => item.id == invChecked.value);
			if (result) {
				let name = '';
				name += result.header_type === 1 ? i18n.t(`个人`) : i18n.t(`企业`);
				name += result.type === 1 ? i18n.t(`普通`) : i18n.t(`专用`);
				name += i18n.t(`发票`);
				invTitle.value = name;
			}
		}).catch(err => {
			uni.showToast({
				title: err,
				icon: 'none'
			});
		});
	}
	/**
	 * 开发票
	 */
	function goInvoice() {
		getInvoiceList()
		invShow.value = true;
		urlQuery.value =
			`new=${news.value}&cartId=${cartId.value}&pinkId=${pinkId.value}&couponId=${couponId.value}&addressId=${addressId.value}&specialInvoice=${special_invoice.value}&couponTitle=${couponTitle.value}`;
	}
	/**
	 * 授权回调事件
	 *
	 */
	function onLoadFun() {
		getaddressInfo();
		getConfirm();
		//调用子页面方法授权后执行获取地址列表
		// this.$scope.selectComponent('#address-window').getAddressList();
	}
	/**
	 * 事件回调（原 this[action] 动态派发改为查表；当前页面未绑定 @onChangeFun，保持可派发的支付回调方法）
	 *
	 */
	const actionMap = {
		payClose,
		goPay,
		payCheck
	};
	function onChangeFun(e) {
		let opt = e;
		let action = opt.action || null;
		let value = opt.value != undefined ? opt.value : null;
		action && actionMap[action] && actionMap[action](value);
	}
	function payClose() {
		pay_close.value = false;
	}
	function goPay() {
		pay_close.value = true;
	}
	function payCheck(type) {
		payType.value = type;
		SubOrder();
	}
	/**
	 * 获取门店列表数据
	 */
	function getList() {
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
			storeList.value = list;
			system_store.value = list[0];
		}).catch(err => {})
	}
	// 关闭地址弹窗；
	function changeClose() {
		address['address'] = false;
	}
	/*
	 * 跳转门店列表
	 */
	function showStoreList() {
		if (storeList.value.length > 0) {
			uni.navigateTo({
				url: '/pages/goods/goods_details_store/index'
			})
		}
	}
	function changePayType(type) {
		payType.value = type
		computedPrice()
	}
	function computedPrice() {
		// 局部变量重命名 shippingTypeLocal：避免与同名 ref（shippingType）互相遮蔽
		let shippingTypeLocal = shippingType.value;
		let data = {
			address_id: addressId.value,
			use_integral: useIntegral.value ? 1 : 0,
			coupon_id: couponId.value,
			delivery_type: shippingTypeLocal == 1 ? 'pickup' : 'delivery',
			payType: payType.value
		}
		if (is_gift.value) data.is_gift = is_gift.value
		centerOrderComputed(orderKey.value, data).then(res => {
			// 中台 computed 接口直接返回计算结果（无 result 包装层）
			let result = res.data;
			if (result) {
				totalPrice.value = result.pay_price;
				integral_price.value = result.deduction_price;
				coupon_price.value = result.coupon_price;
				integral.value = useIntegral.value ? result.SurplusIntegral : usable_integral.value;
				priceGroup.value['payPostage'] = shippingTypeLocal == 1 ? 0 : (result.pay_postage ?? 0);
				priceGroup.value['postageDiscount'] = result.storePostageDiscount ?? 0;
			}
		})
	}
	function addressType(e) {
		let index = e;
		if (shippingType.value == parseInt(index)) return
		shippingType.value = parseInt(index);
		if (index == 1) {
			// #ifdef H5
			if (Auth.isWeixin()) {
				Auth.location().then(res => {
					uni.setStorageSync('user_latitude', res.latitude);
					uni.setStorageSync('user_longitude', res.longitude);
					getList()
				}).catch(err => {
					getList()
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
						getList()
					}
				})
				// #ifdef H5
			}
			// #endif
		};
		nextTick(e => {
			if(!is_gift.value){
				getConfirm();
				computedPrice();
			}
		})
	}
	function bindPickerChange(e) {
		let value = e.detail.value;
		shippingType.value = value;
		computedPrice();
	}
	function ChangCouponsClone() {
		coupon['coupon'] = false;
	}
	function changeTextareaStatus() {
		for (let i = 0, len = coupon.list.length; i < len; i++) {
			coupon.list[i].use_title = '';
			coupon.list[i].is_use = 0;
		}
		textareaStatus.value = true;
		status.value = 0;
		coupon['list'] = coupon.list;
	}
	/**
	 * 处理点击优惠券后的事件
	 *
	 */
	function ChangCoupons(e) {
		// usableCoupon = e
		// coupon.coupon = false
		let index = e,
			list = coupon.list,
			// 局部变量重命名 xxxLocal：避免与同名 ref（couponTitle/couponId）互相遮蔽
			couponTitleLocal = i18n.t(`请选择`),
			couponIdLocal = 0;
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
			list[index].use_title = i18n.t(`不使用`);
			list[index].is_use = 1;
			couponTitleLocal = list[index].coupon_title;
			couponIdLocal = list[index].id;
		}
		couponTitle.value = couponTitleLocal;
		couponId.value = couponIdLocal;
		coupon['coupon'] = false;
		coupon['list'] = list;
		computedPrice();
	}
	/**
	 * 使用积分抵扣
	 */
	function ChangeIntegral() {
		useIntegral.value = !useIntegral.value;
		computedPrice();
	}
	/**
	 * 选择地址后改变事件
	 * @param object e
	 */
	function OnChangeAddress(e) {
		textareaStatus.value = true;
		addressId.value = e;
		address.address = false;
		getaddressInfo();
		if(!is_gift.value){
			getConfirm()
			computedPrice();
		}
	}
	function bindHideKeyboard(e) {
		mark.value = e.detail.value;
	}
	function getOrderDetail() {
		getGiftOrderDetail(orderId.value).then(res => {
			giftData.value = res.data
			cartInfo.value = res.data.cartInfo;
			store_self_mention.value = res.data.store_self_mention
			if (res.data.type == 0) {
				is_shipping.value = true;
				shippingType.value = 0;
				getaddressInfo();
				nextTick(()=> {
					addressWindowRef.value.getAddressList();
				})
			} else {
				if (res.data.type == 1) {
					is_shipping.value = false;
					shippingType.value = 0;
					getaddressInfo();
					nextTick(()=> {
						addressWindowRef.value.getAddressList();
					})
				} else if (res.data.type == 2) {
					is_shipping.value = false;
					shippingType.value = 1;
					addressType(1)
					getList();
				}
			}
		})
	}
	/**
	 * 获取当前订单详细信息
	 *
	 */
	function getConfirm() {
			// return;
			uni.showLoading({
				title: i18n.t(`正在加载中`),
				mask: true
			});

			// 中台直接购买模式
			if (isDirectBuy.value) {
				let data = {
					product_id: productId.value,
					sku_unique: skuUnique.value,
					product_type: productType.value,
					activity_type: activityType.value,
					activity_id: activityId.value,
					num: buyNum.value,
					address_id: addressId.value
				}
				centerOrderConfirm(data).then(res => {
					handleConfirmResponse(res);
				}).catch(err => {
					uni.hideLoading()
					return util.Tips({
						title: err
					});
				});
				return;
			}

			let data = {
				cartId: cartId.value,
				new: news.value,
				address_id: addressId.value,
				shipping_type: shippingType.value + 1
			}
			if (is_gift.value) data.is_gift = is_gift.value
			centerOrderConfirm(data).then(res => {
				handleConfirmResponse(res);
			}).catch(err => {
				uni.hideLoading()
				return util.Tips({
					title: err
				});
			});
		}
		/**
		 * 统一处理订单确认响应（兼容购物车模式与中台直接购买模式）
		 */
	function handleConfirmResponse(res) {
			userInfo.value = res.data.userInfo;
			// 中台 confirm 接口返回 {label:标题, field, type, required}；
			// 本页模板/校验按 {title, label:类型, status, value} 使用，此处统一归一化
			confirm.value = (res.data.custom_form || []).map(e => {
				const type = e.type || e.label || 'text';
				return {
					title: e.title || e.label || '',
					label: type,
					status: e.required !== undefined ? !!e.required : !!e.status,
					field: e.field || '',
					value: type === 'img' ? [] : '',
				};
			});
			integral.value = res.data.usable_integral;
			usable_integral.value = res.data.usable_integral;
			contacts.value = res.data.userInfo.real_name;
			contactsTel.value = res.data.userInfo.record_phone === '0' ? res.data.userInfo.phone : res
				.data
				.userInfo.record_phone;
			cartInfo.value = res.data.cartInfo;
			integralRatio.value = res.data.integralRatio || res.data.integral_ratio;
			offlinePostage.value = res.data.offlinePostage;
			orderKey.value = res.data.orderKey || res.data.order_key;
			valid_count.value = res.data.valid_count;
			discount_id.value = res.data.discount_id
			priceGroup.value = res.data.priceGroup;
			totalPrice.value = util.$h.Add(parseFloat(res.data.priceGroup.totalPrice),
				parseFloat(res.data
					.priceGroup.payPostage));
			allPrice.value = (() => {
				const pg = res.data.priceGroup || {};
				// 商品总价展示规格原价之和；sumPrice 缺失时回补等级/会员优惠
				const originalTotal = pg.sumPrice !== undefined && pg.sumPrice !== null
					? pg.sumPrice
					: util.$h.Add(
						util.$h.Add(parseFloat(pg.totalPrice || 0), parseFloat(pg.levelPrice || 0)),
						parseFloat(pg.memberPrice || 0)
					);
				return parseFloat(originalTotal).toFixed(2);
			})();
			seckillId.value = parseInt(res.data.seckill_id);
			invoice_func.value = res.data.invoice_func;
			special_invoice.value = res.data.special_invoice;
			store_self_mention.value = res.data.store_self_mention;
			virtual_type.value = res.data.virtual_type || 0;
			integral_open.value = res.data.integral_open;
			// 中台直接购买模式额外字段
			if (isDirectBuy.value) {
				features.value = res.data.features || {};
				activityInfo.value = res.data.activity_info || {};
				productType.value = res.data.product_type || productType.value;
				activityType.value = res.data.activity_type || activityType.value;
				activityId.value = res.data.activity_id || activityId.value;
			}
			uni.hideLoading()
			//微信支付是否开启
			cartArr.value[0].payStatus = res.data.pay_weixin_open || 0
			//支付宝是否开启
			cartArr.value[1].payStatus = res.data.ali_pay_status || 0;
			//#ifdef MP
			cartArr.value[1].payStatus = 0;
			//#endif
			//余额支付是否开启
			// cartArr.value[2].title = '可用余额:' + res.data.userInfo.now_money;
			cartArr.value[2].number = res.data.userInfo.now_money;
			cartArr.value[2].payStatus = res.data.yue_pay_status == 1 ? res.data.yue_pay_status : 0
			if (res.data.offline_pay_status == 2 || res.data.deduction) {
				cartArr.value[3].payStatus = 0
			} else {
				cartArr.value[3].payStatus = 1
			}
			//好友代付是否开启
			cartArr.value[4].payStatus = res.data.friend_pay_status || 0;
			// cartArr.value = cartArr.value;
			ChangePrice.value = totalPrice.value;
			getBargainId();
			setTimeout(() => {
				getCouponList();
			}, 500);
			if (addressId.value && !is_gift.value && !isDirectBuy.value) {
				computedPrice();
			}
		}
	/*
	 * 提取砍价和拼团id
	 */
	function getBargainId() {
		let cartINfo = cartInfo.value;
		// 局部变量重命名 xxxLocal：避免与同名 ref（BargainId/combinationId/discountId/advanceId）互相遮蔽
		let bargainIdLocal = 0;
		let combinationIdLocal = 0;
		let discountIdLocal = 0;
		let advanceIdLocal = 0;
		cartINfo.forEach(function(value, index, cartINfo) {
			bargainIdLocal = cartINfo[index].bargain_id,
				combinationIdLocal = cartINfo[index].combination_id,
				discountIdLocal = cartINfo[index].discount_id,
				advanceIdLocal = cartINfo[index].advance_id
		})
		BargainId.value = parseInt(bargainIdLocal);
		combinationId.value = parseInt(combinationIdLocal);
		discountId.value = parseInt(discountIdLocal);
		advanceId.value = parseInt(advanceIdLocal);
		if (cartArr.value.length == 3 && (bargainIdLocal || combinationIdLocal || seckillId.value || discountIdLocal)) {
			cartArr.value[2].payStatus = 0;
			cartArr.value = cartArr.value;
		}
	}
	/**
	 * 获取当前金额可用优惠券
	 *
	 */
	function getCouponList() {
		// 局部变量重命名 shippingTypeLocal：避免与同名 ref（shippingType）互相遮蔽
		let shippingTypeLocal = shippingType.value;
		let data = {
			cartId: cartId.value,
			'new': news.value,
			'shippingType': parseInt(shippingTypeLocal) + 1
		}
		getCouponsOrderPrice(totalPrice.value, data).then(res => {
			coupon['list'] = res.data;
			if (!pinkId.value && !BargainId.value && !combinationId.value && !seckillId.value && !noCoupon.value && !discountId.value && !advanceId.value) {
				coupon.list.length && ChangCoupons(0)
			}
			openType.value = 1;
		});
	}
	/*
	 * 获取默认收货地址或者获取某条地址信息
	 */
	function getaddressInfo() {
		let fnc = addressId.value ? getAddressDetail : getAddressDefault
		fnc(addressId.value).then(res => {
			if (!Array.isArray(res.data)) {
				res.data.is_default = parseInt(res.data.is_default);
				addressInfo.value = res.data || {};
				addressId.value = res.data.id || 0;
				address.addressId = res.data.id || 0;
			}
		})
	}
	function onHaveAddressList() {
		haveAddressList.value = true
	}
	function payItem(e) {
		// 局部变量重命名 activeParam：避免与同名 ref（active）互相遮蔽
		let activeParam = e;
		active.value = activeParam;
		animated.value = true;
		payType.value = cartArr.value[activeParam].value;
		computedPrice();
		setTimeout(function() {
			car();
		}, 500);
	}
	function couponTap() {
		coupon.coupon = true;
		coupon.list.forEach((item, index) => {
			if (item.id == couponId.value) {
				item.is_use = 1
			} else {
				item.is_use = 0
			}
		})
		coupon['list'] = coupon.list;
	}
	function car() {
		animated.value = false;
	}
	function onAddress() {
		if (addressInfo.value.real_name || haveAddressList.value) {
			addressWindowRef.value.getAddressList();
			textareaStatus.value = false;
			address.address = true;
			pagesUrl.value = '/pages/users/user_address_list/index?news=' + news.value + '&cartId=' + cartId.value +
				'&pinkId=' +
				pinkId.value +
				'&couponId=' +
				couponId.value +
				'&is_gift=' +
				is_gift.value +
				'&order_id=' + orderId.value
		} else {
			let url = '/pages/users/user_address/index?cartId=' + cartId.value + '&pinkId=' +
					pinkId.value +
					'&couponId=' + couponId.value + '&new=' + news.value + '&is_gift=' + is_gift.value + '&order_id=' + orderId.value
			uni.navigateTo({
				url: url
			})
		}
	}
	function formpost(url, postData) {
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
		nextTick(e => {
			tempform.submit();
		})
	}
	function payment(data) {
			// 中台直接购买模式
			if (isDirectBuy.value) {
				let postData = {
					// 确认阶段生成的一次性订单凭证，创建成功后由后端消费
					key: orderKey.value,
					product_id: productId.value,
					sku_unique: skuUnique.value,
					product_type: productType.value,
					activity_type: activityType.value,
					activity_id: activityId.value,
					num: buyNum.value,
					order_data: {
						address_id: addressId.value,
						real_name: contacts.value,
						phone: contactsTel.value,
						mark: mark.value,
						custom_form: buildCustomFormPayload(),
						invoice_id: invChecked.value,
					},
					coupon_id: couponId.value,
					use_integral: useIntegral.value,
				};
				centerOrderCreate(postData).then(res => {
					const orderNo = res.data.order_no || res.data.order_id;
					// 积分兑换等免费订单，直接完成支付，跳过收银台
					if (activityType.value === 'integral') {
						orderPay({
							uni: orderNo,
							paytype: 'free',
							type: 0,
						}).then(payRes => {
						// 积分兑换统一跳转至支付结果页，由引擎查询订单状态自动渲染
						let url = `${routes.payStatus}?order_no=${orderNo}`;
						uni.reLaunch({ url });
					}).catch(err => {
							return util.Tips({ title: err });
						});
						return;
					}
					let url = `/pages/goods/cashier/index?order_id=${orderNo}&from_type=order`
					uni.reLaunch({
						url
					})
				}).catch(err => {
					uni.hideLoading();
					return util.Tips({
						title: err
					});
				});
				return;
			}
			// 购物车模式：多商品合并下单，走中台 center/order/create
			let postData = {
				key: orderKey.value,
				order_data: {
					address_id: addressId.value,
					real_name: contacts.value,
					phone: contactsTel.value,
					mark: mark.value,
					custom_form: buildCustomFormPayload(),
					invoice_id: invChecked.value,
					store_id: system_store.value ? system_store.value.id : 0,
					delivery_type: shippingType.value == 1 ? 'pickup' : 'delivery',
				},
				coupon_id: couponId.value,
				use_integral: useIntegral.value ? 1 : 0,
			};
			centerOrderCreate(postData).then(res => {
				const orderNo = res.data.order_no || res.data.order_id;
				let url = `/pages/goods/cashier/index?order_id=${orderNo}&from_type=order`
				uni.reLaunch({
					url
				})
			}).catch(err => {
				uni.hideLoading();
				return util.Tips({
					title: err
				});
			});
		}
	function clickTextArea() {
		textareaRef.value.focus()
	}
	/**
	 * 自定义表单提交格式：{custom_field_N: 值}
	 * 后端按商品定义回填标题后结构化存储，与下单引擎 OrderCreateEngine 提交口径一致
	 */
	function buildCustomFormPayload() {
		const payload = {};
		confirm.value.forEach((item, index) => {
			payload[item.field || 'custom_field_' + index] = item.value;
		});
		return payload;
	}
	function SubOrder(e) {
			let data = {};
			// 地址校验：中台直接购买模式按 virtual_type 判断；旧模式保持原有逻辑
			let needAddress = isDirectBuy.value
				? (!virtual_type.value && !shippingType.value)
				: (!addressId.value && !shippingType.value && !virtual_type.value && !is_gift.value);
			if (!addressId.value && needAddress) return util.Tips({
				title: i18n.t(`请选择收货地址`)
			});
		if (shippingType.value == 1) {
			if (contacts.value == "" || contactsTel.value == "") {
				return util.Tips({
					title: i18n.t(`请填写联系人或联系人电话`)
				});
			}
			if (!/^1(3|4|5|7|8|9|6)\d{9}$/.test(contactsTel.value)) {
				return util.Tips({
					title: i18n.t(`请输入正确的手机号码`)
				});
			}
			if (!contacts.value) {
				return util.Tips({
					title: i18n.t(`请输入姓名`)
				});
			}
			if (storeList.value.length == 0) return util.Tips({
				title: i18n.t(`暂无门店,请选择其他方式`)
			});
		}
		for (var i = 0; i < confirm.value.length; i++) {
			let data = confirm.value[i]
			if (data.status) {
				if (data.label === 'text' || data.label === 'data' || data.label === 'time' || data.label ===
					'id') {
					if (!data.value.trim()) {
						return uni.showToast({
							title: i18n.t(`请输入`) + `${data.title}`,
							icon: 'none'
						});
					}
				}
				if (data.label === 'number') {
					if (data.value <= 0) {
						return uni.showToast({
							title: i18n.t(`请输入`) + `${data.title}`,
							icon: 'none'
						});
					}
				}
				if (data.label === 'email') {
					if (!/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/.test(data.value)) {
						return uni.showToast({
							title: i18n.t(`请输入正确的`) + `${data.title}`,
							icon: 'none'
						});
					}
				}
				if (data.label === 'phone') {
					if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(data.value)) {
						return uni.showToast({
							title: i18n.t(`请输入正确的`) + `${data.title}`,
							icon: 'none'
						});
					}
				}
				if (data.label === 'img') {
					if (!data.value.length) {
						return uni.showToast({
							title: i18n.t(`请上传`) + `${data.title}`,
							icon: 'none'
						});
					}
				}
			}
		}
		data = {
			custom_form: confirm.value,
			gift_mark: gift_mark.value, // 礼物留言
			real_name: contacts.value,
			phone: contactsTel.value,
			addressId: addressId.value,
			formId: '',
			couponId: couponId.value,
			useIntegral: useIntegral.value,
			bargainId: BargainId.value,
			combinationId: combinationId.value,
			discountId: discountId.value,
			pinkId: pinkId.value,
			advanceId: advanceId.value,
			seckill_id: seckillId.value,
			mark: mark.value,
			store_id: system_store.value ? system_store.value.id : 0,
			'from': from.value,
			shipping_type: util.$h.Add(shippingType.value, 1),
			'new': news.value,
			'invoice_id': invChecked.value,
			// #ifdef H5
			quitUrl: location.protocol + '//' + location.hostname +
				'/pages/goods/order_pay_status/index?' +
				'&type=3' + '&totalPrice=' + totalPrice.value
			// #endif
			// #ifdef APP-PLUS
			quitUrl: '/pages/goods/order_details/index?order_id=' + order_id
			// #endif
		};
		if (is_gift.value) data.is_gift = is_gift.value
		if (data.payType == 'yue' && parseFloat(userInfo.value.now_money) < parseFloat(totalPrice.value))
			return util.Tips({
				title: i18n.t(`余额不足`)
			});
		// uni.showLoading({
		// 	title: i18n.t(`订单支付中`)
		// });
		// #ifdef MP
		openPaySubscribe().then(() => {
			payment(data);
		});
		// #endif
		// #ifndef MP
		payment(data);
		// #endif
	}
	function receiveGift() {
		let data = {
			gift_key: giftData.value.gift_key,
			shipping_type: util.$h.Add(shippingType.value, 1),
			name: contacts.value,
			phone: contactsTel.value,
			address_id: addressId.value,
			store_id: system_store.value ? system_store.value.id : 0,
		}
		orderReceiveGift(orderId.value, data).then(res => {
			// 订单详情页按订单号（order_no）跳转，不能传中台主键 id
			const receivedOrderNo = giftData.value.order_no || '';
			uni.reLaunch({
				url: `/pages/goods/receive_gifts_status/index?status=${res.data.status}&order_no=${receivedOrderNo}`
			})
		}).catch(err => {
			uni.showToast({
				icon: 'none',
				title: err
			})
		})
	}
	function bindDateChange(e, index) {
		confirm.value[index].value = e.target.value
	}
	function bindTimeChange(e, index) {
		confirm.value[index].value = e.target.value
	}
	function getDate(type) {
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
	}
	function uploadpic(index, item) {
		canvasStatus.value = true
		util.uploadImageChange('upload/image', function(res) {
			item.value.push(res.data.url);
		}, (res) => {
			canvasStatus.value = false
		}, (res) => {
			canvasWidth.value = res.w
			canvasHeight.value = res.h
		});
	}
	function DelPic(index, indexs) {
		let pic = confirm.value[index].value;
		confirm.value[index].value.splice(indexs, 1);
		// that['pics'] = that.pics;
	}
	function inputTripClick() {
		inputTrip.value = true
		// this.$refs.trip.foucs()
	}
	function showMaoLocation(e) {
		// #ifdef H5
		if (Auth.isWeixin()) {
			Auth.seeLocation({
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
	}
	function call(phone) {
		uni.makePhoneCall({
			phoneNumber: phone,
		});
	}
</script>

<style lang="scss" scoped>
:deep(uni-checkbox[disabled] .uni-checkbox-input){
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
