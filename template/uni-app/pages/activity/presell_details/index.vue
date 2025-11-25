<template>
	<view class="product-con" :style="colorStyle">
		<view class="navbar" :style="{ height: navH + 'rpx', opacity: opacity }">
			<view class="navbarH" :style="'height:' + navH + 'rpx;'">
				<view class="navbarCon acea-row row-center-wrapper" :style="{ paddingRight: navbarRight + 'px' }">
					<view class="header acea-row row-center-wrapper">
						<view class="item" :class="navActive === index ? 'on' : ''" v-for="(item, index) in navList"
							:key="index" @tap="tap(index)">{{ item }}</view>
					</view>
				</view>
			</view>
		</view>
		<!-- <view class='iconfont icon-xiangzuo' :style="{top:navH/2+'rpx',opacity:(1-opacity)}" @tap='returns'></view> -->
		<view id="home" class="home acea-row row-center-wrapper iconfont icon-xiangzuo" :class="opacity>0.5?'on':''"
			:style="{ top: homeTop + 'rpx' }" v-if="returnShow" @tap="returns">
			<!-- <view class="line" v-if="returnShow"></view>
			<navigator url="/pages/index/index" class="iconfont icon-shouye4"></navigator> -->
		</view>
		<view>
			<scroll-view :scroll-top="scrollTop" scroll-y="true" scroll-with-animation="true"
				:style="'height:' + height + 'px;'" @scroll="scroll">
				<view id="past0">
					<productConSwiper :imgUrls="storeInfo.images">
					</productConSwiper>
					<view class="wrapper">
						<view class="share acea-row row-between row-bottom">
							<view class="money font-color">
								{{$(`￥`)}}
								<text class="num" v-text="storeInfo.price || 0"></text>
								<text v-if="storeInfo.spec_type">{{$t(`起`)}}</text>
								<text class="price_text">{{$t(`预售价`)}}</text>
							</view>
							<view class="iconfont icon-fenxiang" @click="listenerActionSheet"></view>
						</view>
						<view class="label acea-row row-between-wrapper" style="padding-bottom: 20rpx;">
							<view class="delete-line" v-text="$t(`￥`) + (storeInfo.ot_price || 0)"></view>
							<view v-text="$t(`已预订`)':' + (storeInfo.sales || 0) + (storeInfo.unit_name || '')"></view>
						</view>
						<view class="introduce" v-text="storeInfo.title"></view>
						<view v-if="!is_money_level && storeInfo.vip_price && storeInfo.is_vip"
							class="svip acea-row row-between-wrapper">
							<view class="">{{$t(`开通“超级会员”立省`)}}{{ diff }}{{$t(`元`)}}</view>
							<navigator url="/pages/annex/vip_paid/index">
								{{$t(`立即开通`)}}
								<text class="iconfont icon-jiantou"></text>
							</navigator>
						</view>
						<view class="presell_count">
							<view>
								<view>{{$t(`预售活动时间`)}}：</view>
								<view v-if="storeInfo.start_time && storeInfo.stop_time" class="presell_time">
									<view class='iconfont icon-shijian1'></view>
									{{storeInfo.start_time}}
									<span class='area_line'>~</span>
									{{storeInfo.stop_time}}
								</view>
							</view>
							<view>{{$t(`预售结束后`)}}{{ storeInfo.deliver_time }}{{$t(`天内发货`)}}</view>
						</view>
						<view v-if="couponList.length" class="coupon acea-row row-between-wrapper" @click="couponTap"
							style="margin-top: 0rpx;">
							<view class="hide line1 acea-row">
								{{$t(`优惠券`)}}：
								<template v-for="(item, index) in couponList">
									<view v-if="index < 2" class="activity" :key="index">
										{{$t(`满`)}}{{ item.use_min_price }}{{$t(`减`)}}{{ item.coupon_price }}</view>
								</template>
							</view>
							<view class="iconfont icon-jiantou"></view>
						</view>
						<view class="coupon acea-row row-between-wrapper" v-if="activity.length">
							<view class="line1 acea-row">
								<text>{{$t(`活动`)}}：</text>
								<view v-for="(item, index) in activity" :key="index" @click="goActivity(item)">
									<view v-if="item.type === '1' && $permission('seckill')"
										:class="index == 0 ? 'activity_pin' : '' || index == 1 ? 'activity_miao' : '' || index == 2 ? 'activity_kan' : ''">
										<text class="iconfonts iconfont icon-pintuan"></text>
										<text class="activity_title">{{$t(`参与秒杀`)}}</text>
									</view>
									<view
										:class="index == 0 ? 'activity_pin' : '' || index == 1 ? 'activity_miao' : '' || index == 2 ? 'activity_kan' : ''"
										v-if="item.type === '2' && $permission('bargain')">
										<text class="iconfonts iconfont icon-shenhezhong"></text>
										<text class="activity_title">{{$t(`参与砍价`)}}</text>
									</view>
									<view
										:class="index == 0 ? 'activity_pin' : '' || index == 1 ? 'activity_miao' : '' || index == 2 ? 'activity_kan' : ''"
										v-if="item.type === '3' && $permission('combination')">
										<text class="iconfonts iconfont icon-kanjia"></text>
										<text class="activity_title">{{$t(`参与拼团`)}}</text>
									</view>
								</view>
							</view>
						</view>
					</view>
					<view class="attribute acea-row row-between-wrapper" @click="selecAttr"
						v-if="attr.productAttr.length">
						<!-- <view style="display: flex; align-items: center; width: 90%;">
							{{ attrTxt }}：
							<view class="atterTxt line1" style="width: 82%;">{{ attrValue }}</view>
						</view>
						<view class="iconfont icon-jiantou"></view> -->
						<view class="flex">
							<view style="display: flex; align-items: center; width: 90%">
								<view class="attr-txt"> {{ attrTxt }}： </view>
								<view class="atterTxt line1" style="width: 82%">{{
						      attrValue
						    }}</view>
							</view>
							<view class="iconfont icon-jiantou"></view>
						</view>
						<view class="acea-row row-between-wrapper" style="margin-top: 7px; padding-left: 70px"
							v-if="skuArr.length > 1">
							<view class="flexs">
								<image :src="item.image" v-for="(item, index) in skuArr.slice(0, 4)" :key="index"
									class="attrImg"></image>
							</view>
							<view class="switchTxt">{{$t(`共`)}}{{ skuArr.length }}{{$t(`种规格可选`)}}</view>
						</view>
					</view>
				</view>
				<view class="userEvaluation" id="past1">
					<view class="title acea-row row-between-wrapper">
						<view>{{$t(`用户评价`)}}({{ replyCount }})</view>
						<navigator class="praise" hover-class="none"
							:url="'/pages/goods/goods_comment_list/index?product_id=' + storeInfo.product_id">
							<text class="font-color">{{ replyChance }}%</text>
							{{$t(`好评率`)}}
							<text class="iconfont icon-jiantou"></text>
						</navigator>
					</view>
					<block v-if="replyCount">
						<userEvaluation :reply="reply"></userEvaluation>
					</block>
				</view>
				<view class="product-intro" id="past3">
					<view class="title">{{$t(`产品介绍`)}}</view>
					<view class="conter">
						<jyf-parser :html="description" ref="article" :tag-style="tagStyle"></jyf-parser>
					</view>
					<!-- <rich-text :nodes="description" class="conter"></rich-text> -->
				</view>
				<view class="uni-p-b-98"></view>
			</scroll-view>
		</view>
		<view class="uni-p-b-98"></view>
		<view class="footer acea-row row-between-wrapper">
			<!-- <button open-type="contact" hover-class='none' class='item'>
				<view class='iconfont icon-kefu'></view>
				<view>客服</view>
			</button> -->

			<navigator hover-class="none" open-type="switchTab" class="item" url="/pages/index/index">
				<view class="iconfont icon-shouye6"></view>
				<view class="p_center">{{$t(`首页`)}}</view>
			</navigator>
			<view @click="setCollect" class="item">
				<view class="iconfont icon-shoucang1" v-if="storeInfo.userCollect"></view>
				<view class="iconfont icon-shoucang" v-else></view>
				<view class="p_center">{{$t(`收藏`)}}</view>
			</view>
			<view class="bnt acea-row" v-if="pay_status === 1 || pay_status === 3">
				<form @submit="goBuy" class="buy bnts bg-color-hui"><button class="buy bnts"
						form-type="submit">{{pay_status === 1?$t(`未开始`):$t(`已结束`)}}</button></form>
			</view>
			<view class="bnt acea-row"
				v-else-if="attr.productSelect.quota <= 0 || attr.productSelect.quota < attr.productSelect.cart_num">
				<form class="buy bnts bg-color-hui"><button class="buy bnts bg-color-hui"
						form-type="submit">{{$t(`已售罄`)}}</button></form>
			</view>
			<view class="bnt acea-row" v-else-if="pay_status === 2">
				<form @submit="goBuy" class="buy bnts"><button class="buy bnts" form-type="submit">{{$t(`立即购买`)}}</button></form>
			</view>
		</view>
		<!-- 		<shareRedPackets :sharePacket="sharePacket" @listenerActionSheet="listenerActionSheet"
			@closeChange="closeChange"></shareRedPackets> -->
		<!-- 组件 -->
		<productWindow :attr="attr" :isShow="0" :limitNum="1" :iSplus="1" @myevent="onMyEvent" @ChangeAttr="ChangeAttr"
			@ChangeCartNum="ChangeCartNum" @attrVal="attrVal" @iptCartNum="iptCartNum" id="product-window"
			:is_vip="is_vip" @getImg="showImg"></productWindow>
		<cus-previewImg ref="cusPreviewImg" :list="skuArr" @changeSwitch="changeSwitch"
			@shareFriend="listenerActionSheet" />
		<couponListWindow :coupon="coupon" v-if="coupon" @ChangCouponsClone="ChangCouponsClone"
			@ChangCoupons="ChangCoupons" @ChangCouponsUseState="ChangCouponsUseState" @tabCouponType="tabCouponType">
		</couponListWindow>
		<!-- 分享按钮 -->
		<view class="generate-posters acea-row row-middle" :class="posters ? 'on' : ''">
			<!-- #ifndef MP -->
			<button class="item" hover-class="none" v-if="weixinStatus === true" @click="H5ShareBox = true">
				<view class="iconfont icon-weixin3"></view>
				<view class="">{{$t(`发送给朋友`)}}</view>
			</button>
			<!-- #endif -->
			<!-- #ifdef MP -->
			<button class="item" open-type="share" hover-class="none" @click="goFriend">
				<view class="iconfont icon-weixin3"></view>
				<view class="">{{$t(`发送给朋友`)}}</view>
			</button>
			<!-- #endif -->
			<!-- #ifdef H5  -->
			<div class="item copy-data" v-if="storeInfo.command_word" :data-clipboard-text="storeInfo.command_word">
				<view class="iconfont icon-fuzhikouling"></view>
				<text>{{$t(`复制口令`)}}</text>
			</div>
			<!-- #endif -->
			<button class="item" hover-class="none" @click="goPoster">
				<view class="iconfont icon-haibao"></view>
				<view class="">{{$t(`生成海报`)}}</view>
			</button>
		</view>
		<view class="mask" v-if="posters" @click="listenerActionClose"></view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<!-- 海报展示 -->
		<view class="poster-pop" v-if="posterImageStatus">
			<image src="/static/images/poster-close.png" class="close" @click="posterImageClose"></image>
			<image class="poster-img" :src="posterImage"></image>
			<!-- #ifndef H5  -->
			<view class="save-poster" @click="savePosterPath">{{$t(`保存到手机`)}}</view>
			<!-- #endif -->
			<!-- #ifdef H5 -->
			<view class="keep">{{$t(`长按图片可以保存到手机`)}}</view>
			<!-- #endif -->
		</view>
		<view class="mask" v-if="posterImageStatus"></view>
		<canvas class="canvas" canvas-id="myCanvas" v-if="canvasStatus"></canvas>
		<!-- 发送给朋友图片 -->
		<view class="share-box" v-if="H5ShareBox">
			<image :src="imgHost + '/statics/images/share-info.png'" @click="H5ShareBox = false"></image>
		</view>
		<kefuIcon :ids='parseInt(id)' :routineContact="routineContact"></kefuIcon>
	</view>
</template>

<script>
	import {
		getPresellProductDetail,
		getProductCode,
		collectAdd,
		collectDel,
		postCartAdd
	} from '@/api/store.js';
	import {
		getUserInfo,
		userShare
	} from '@/api/user.js';
	import {
		getCoupons
	} from '@/api/api.js';
	import {
		getCartCounts
	} from '@/api/order.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from 'vuex';
	import {
		imageBase64
	} from '@/api/public';
	import productConSwiper from '@/components/productConSwiper';
	import couponListWindow from '@/components/couponListWindow';
	import productWindow from '@/components/productWindow';
	import userEvaluation from '@/components/userEvaluation';
	import shareRedPackets from '@/components/shareRedPackets';
	import kefuIcon from '@/components/kefuIcon';
	import parser from '@/components/jyf-parser/jyf-parser';
	import ClipboardJS from '@/plugin/clipboard/clipboard.js';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import colors from "@/mixins/color";
	import {HTTP_REQUEST_URL} from '@/config/app';
	import cusPreviewImg from "@/components/cusPreviewImg/index.vue";
	let app = getApp();
	export default {
		components: {
			productConSwiper,
			couponListWindow,
			productWindow,
			userEvaluation,
			shareRedPackets,
			kefuIcon,
			'jyf-parser': parser,
			cusPreviewImg,
			// #ifdef MP
			authorize
			// #endif
		},
		mixins: [colors],
		directives: {
			trigger: {
				inserted(el, binging) {
					el.click();
				}
			}
		},
		data() {
			let that = this;
			return {
				imgHost:HTTP_REQUEST_URL,
				//属性是否打开
				coupon: {
					coupon: false,
					type: -1,
					list: [],
					count: []
				},
				attrTxt: that.$t(`请选择`), //属性页面提示
				attrValue: '', //已选属性
				animated: false, //购物车动画
				id: 0, //商品id
				replyCount: 0, //总评论数量
				reply: [], //评论列表
				storeInfo: {}, //商品详情
				productValue: [], //系统属性
				couponList: [], //优惠券
				cart_num: 1, //购买数量
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				isOpen: false, //是否打开属性组件
				actionSheetHidden: true,
				posterImageStatus: false,
				storeImage: '', //海报产品图
				PromotionCode: '', //二维码图片
				canvasStatus: false, //海报绘图标签
				posterImage: '', //海报路径
				posterbackgd: '/static/images/posterbackgd.png',
				sharePacket: {
					isState: true //默认不显示
				}, //分销商详细
				uid: 0, //用户uid
				circular: false,
				autoplay: false,
				interval: 3000,
				duration: 500,
				clientHeight: '',
				systemStore: {}, //门店信息
				good_list: [],
				replyChance: 0,
				CartCount: 0,
				isDown: true,
				storeSelfMention: true,
				posters: false,
				weixinStatus: false,
				attr: {
					cartAttr: false,
					productAttr: [],
					productSelect: {}
				},
				description: '',
				navActive: 0,
				H5ShareBox: false, //公众号分享图片
				activity: [],
				navH: '',
				navList: [],
				opacity: 0,
				scrollY: 0,
				topArr: [],
				toView: '',
				height: 0,
				heightArr: [],
				lock: false,
				scrollTop: 0,
				tagStyle: {
					img: 'width:100%;display:block;',
					table: 'width:100%',
					video: 'width:100%'
				},
				returnShow: true, //判断顶部返回是否出现
				diff: '',
				is_money_level: 1,
				is_vip: 0, //是否是会员
				navbarRight: 0,
				homeTop: 20,
				routineContact: '0',
				pay_status: 1,
				skuArr: [],
				selectSku: {},
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV == true) {
						this.getCouponList();
						this.getCartCount();
					}
				},
				deep: true
			},
			storeInfo: {
				handler: function() {
					this.$nextTick(() => {});
				},
				immediate: true
			}
		},
		onLoad(options) {
			let that = this;
			var pages = getCurrentPages();
			that.returnShow = pages.length === 1 ? false : true;
			// #ifdef MP
			that.navH = app.globalData.navHeight;
			// #endif
			// #ifndef MP
			that.navH = 96;
			// #endif
			that.id = options.id;
			uni.getSystemInfo({
				success: function(res) {
					that.height = res.windowHeight;
					//res.windowHeight:获取整个窗口高度为px，*2为rpx；98为头部占据的高度；
					// #ifndef APP-PLUS || H5 || MP-ALIPAY
					that.navbarRight = res.windowWidth - uni.getMenuButtonBoundingClientRect().left;
					// #endif
				}
			});
			//扫码携带参数处理
			// #ifdef MP
			if (options.scene) {
				let value = that.$util.getUrlParams(decodeURIComponent(options.scene));
				if (value.id) options.id = value.id;
				//记录推广人uid
				if (value.pid) app.globalData.spid = value.pid;
			}
			if (!options.id) {
				return that.$util.Tips({
					title: that.$t(`缺少参数无法查看商品`)
				}, {
					tab: 3,
					url: 1
				});
			} else {
				that.id = options.id;
			}
			//记录推广人uid
			if (options.pid) app.globalData.spid = options.pid;
			if (options.spid) app.globalData.spid = options.spid;
			// #endif
			that.getGoodsDetails();
		},
		onReady: function() {
			this.$nextTick(function() {
				// #ifdef MP
				const menuButton = uni.getMenuButtonBoundingClientRect();
				const query = uni.createSelectorQuery().in(this);
				query
					.select('#home')
					.boundingClientRect(data => {
						this.homeTop = menuButton.top * 2 + menuButton.height - data.height;
					})
					.exec();
				// #endif
				// #ifdef H5
				const clipboard = new ClipboardJS('.copy-data');
				clipboard.on('success', () => {
					this.$util.Tips({
						title: this.$t(`复制成功`)
					});
				});
				// #endif
			});
		},
		/**
		 * 用户点击右上角分享
		 */
		// #ifdef MP
		onShareAppMessage: function() {
			let that = this;
			that.$set(that, 'actionSheetHidden', !that.actionSheetHidden);
			userShare();
			return {
				title: that.storeInfo.store_name || '',
				imageUrl: that.storeInfo.image || '',
				path: '/pages/goods_details/index?id=' + that.id + '&pid=' + that.uid
			};
		},
		// #endif
		methods: {
			closeChange: function() {
				this.$set(this.sharePacket, 'isState', true);
			},
			goActivity: function(e) {
				let item = e;
				if (item.type === '1') {
					uni.navigateTo({
						url: `/pages/activity/goods_seckill_details/index?id=${item.id}&time_id=${item.time_id}`
					});
				} else if (item.type === '2') {
					uni.navigateTo({
						url: `/pages/activity/goods_bargain_details/index?id=${item.id}&bargain=${this.uid}`
					});
				} else {
					uni.navigateTo({
						url: `/pages/activity/goods_combination_details/index?id=${item.id}`
					});
				}
			},
			/**
			 * 购物车手动填写
			 *
			 */
			iptCartNum: function(e) {
				this.$set(this.attr.productSelect, 'cart_num', e);
			},
			// 后退
			returns: function() {
				uni.navigateBack();
			},
			tap: function(index) {
				var id = 'past' + index;
				var index = index;
				var that = this;
				// if (!this.data.good_list.length && id == "past2") {
				//   id = "past3"
				// }
				this.$set(this, 'toView', id);
				this.$set(this, 'navActive', index);
				this.$set(this, 'lock', true);
				this.$set(this, 'scrollTop', index > 0 ? that.topArr[index] - app.globalData.navHeight / 2 : that
					.topArr[index]);
			},
			scroll: function(e) {
				var that = this,
					scrollY = e.detail.scrollTop;
				var opacity = scrollY / 200;
				opacity = opacity > 1 ? 1 : opacity;
				that.$set(that, 'opacity', opacity);
				that.$set(that, 'scrollY', scrollY);
				if (that.lock) {
					that.$set(that, 'lock', false);
					return;
				}
				for (var i = 0; i < that.topArr.length; i++) {
					if (scrollY < that.topArr[i] - app.globalData.navHeight / 2 + that.heightArr[i]) {
						that.$set(that, 'navActive', i);
						break;
					}
				}
			},
			/*
			 *去商品详情页
			 */
			goDetail(item) {
				if (item.activity.length == 0) {
					uni.redirectTo({
						url: '/pages/goods_details/index?id=' + item.id
					});
					return;
				}
				// 砍价
				if (item.activity && item.activity.type == 2) {
					uni.redirectTo({
						url: `/pages/activity/goods_bargain_details/index?id=${item.activity.id}&bargain=${this.uid}`
					});
					return;
				}
				// 拼团
				if (item.activity && item.activity.type == 3) {
					uni.redirectTo({
						url: `/pages/activity/goods_combination_details/index?id=${item.activity.id}`
					});
					return;
				}
				// 秒杀
				if (item.activity && item.activity.type == 1) {
					uni.redirectTo({
						url: `/pages/activity/goods_seckill_details/index?id=${item.activity.id}&time_id=${item.activity.time_id}`
					});
					return;
				}
			},
			// 微信登录回调
			onLoadFun: function(e) {
				// this.getUserInfo();
				// this.get_product_collect();
			},
			ChangCouponsClone: function() {
				this.$set(this.coupon, 'coupon', false);
			},
			/*
			 * 获取用户信息
			 */
			getUserInfo: function() {
				let that = this;
				getUserInfo().then(res => {
					that.$set(that.sharePacket, 'isState', that.sharePacket.priceName != 0 ? false : true);
					that.$set(that, 'uid', res.data.uid);
					that.$set(that, 'is_money_level', res.data.is_money_level);
				});
			},
			/**
			 * 购物车数量加和数量减
			 *
			 */
			ChangeCartNum: function(changeValue) {
				//changeValue:是否 加|减
				//获取当前变动属性
				let productSelect = this.productValue[this.attrValue];
				//如果没有属性,赋值给商品默认库存
				if (productSelect === undefined && !this.attr.productAttr.length) productSelect = this.attr
					.productSelect;
				//无属性值即库存为0；不存在加减；
				if (productSelect === undefined) return;
				let stock = productSelect.stock || 0;
				let num = this.attr.productSelect;
				if (changeValue) {
					num.cart_num++;
					if (num.cart_num > stock) {
						this.$set(this.attr.productSelect, 'cart_num', stock ? stock : 1);
						this.$set(this, 'cart_num', stock ? stock : 1);
					}
				} else {
					num.cart_num--;
					if (num.cart_num < 1) {
						this.$set(this.attr.productSelect, 'cart_num', 1);
						this.$set(this, 'cart_num', 1);
					}
				}
			},
			attrVal(val) {
				this.$set(this.attr.productAttr[val.indexw], 'index', this.attr.productAttr[val.indexw].attr_values[val
					.indexn]);
			},
			/**
			 * 属性变动赋值
			 *
			 */
			ChangeAttr: function(res) {
				let productSelect = this.productValue[res];
				this.$set(this, "selectSku", productSelect);
				if (productSelect && productSelect.stock > 0) {
					this.$set(this.attr.productSelect, 'image', productSelect.image);
					this.$set(this.attr.productSelect, 'price', productSelect.price);
					this.$set(this.attr.productSelect, 'stock', productSelect.stock);
					this.$set(this.attr.productSelect, 'unique', productSelect.unique);
					this.$set(this.attr.productSelect, 'quota', productSelect.quota);
					this.$set(this.attr.productSelect, 'cart_num', 1);
					this.$set(this.attr.productSelect, 'vip_price', productSelect.vip_price);
					this.$set(this, 'attrValue', res);
					this.$set(this, 'attrTxt', this.$t(`已选择`));
				} else {
					this.$set(this.attr.productSelect, 'image', productSelect.image);
					this.$set(this.attr.productSelect, 'price', this.storeInfo.price);
					this.$set(this.attr.productSelect, 'stock', 0);
					this.$set(this.attr.productSelect, 'unique', '');
					this.$set(this.attr.productSelect, 'cart_num', 0);
					this.$set(this.attr.productSelect, 'quota', productSelect.quota);
					this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
					this.$set(this, 'attrValue', '');
					this.$set(this, 'attrTxt', this.$t(`请选择`));
				}
			},
			/**
			 * 领取完毕移除当前页面领取过的优惠券展示
			 */
			ChangCoupons: function(e) {
				let coupon = e;
				let couponList = this.$util.ArrayRemove(this.couponList, 'id', coupon.id);
				this.$set(this, 'couponList', couponList);
				this.getCouponList();
			},

			setClientHeight: function() {
				let that = this;
				if (!that.good_list.length) return;
				let view = uni
					.createSelectorQuery()
					.in(this)
					.select('#list0');
				view.fields({
						size: true
					},
					data => {
						that.$set(that, 'clientHeight', data.height + 20);
					}
				).exec();
			},
			/**
			 * 获取产品详情
			 *
			 */
			getGoodsDetails: function() {
				let that = this;
				getPresellProductDetail(that.id)
					.then(res => {
						let storeInfo = res.data.storeInfo;
						let goodArray = new Array();
						that.$set(that, 'storeInfo', storeInfo);
						that.$set(that, 'description', storeInfo.description);
						that.$set(that, 'reply', res.data.reply ? [res.data.reply] : []);
						that.$set(that, 'replyCount', res.data.replyCount);
						that.$set(that, 'pay_status', res.data.pay_status); // 1未开始;2进行中;3已结束
						that.$set(that, 'replyChance', res.data.replyChance);
						that.$set(that.attr, 'productAttr', res.data.productAttr);
						that.$set(that, 'productValue', res.data.productValue);
						that.$set(that, 'PromotionCode', storeInfo.code_base);
						that.$set(that, 'routineContact', Number(res.data.routine_contact_type));
						uni.setNavigationBarTitle({
							title: storeInfo.title.substring(0, 7) + '...'
						});
						for (let key in res.data.productValue) {
							let obj = res.data.productValue[key];
							that.skuArr.push(obj);
						}
						that.$set(that, "selectSku", that.skuArr[0]);
						var navList = [that.$t(`商品`), that.$t(`评价`), that.$t(`详情`)];
						if (goodArray.length) {
							navList.splice(2, 0, that.$t(`推荐`));
						}
						that.$set(that, 'navList', navList);
						// #ifdef H5
						that.$set(that, 'storeImage', that.storeInfo.image);
						that.getImageBase64();
						if (that.isLogin) {
							that.ShareInfo();
						}
						// #endif
						if (that.isLogin) {
							that.getUserInfo();
						}
						this.$nextTick(() => {
							// if (good_list.length) {
							// 	that.setClientHeight();
							// }
						});
						setTimeout(function() {
							that.infoScroll();
						}, 500);
						// #ifdef APP-PLUS
						uni.downloadFile({
							url: that.setDomain(res.data.storeInfo.code_base),
							success: function(res) {
								that.PromotionCode = res.tempFilePath;
							},
							fail: function() {
								return that.$util.Tips({
									title: that.$t(`二维码获取失败`)
								});
							},
						});


						// that.PromotionCode = res.data.storeInfo.code_base

						that.downloadFilestoreImage();
						// #endif
						// #ifndef H5 || APP-PLUS
						that.downloadFilestoreImage();
						that.downloadFilePromotionCode();
						// #endif
						that.DefaultSelect();
						that.getCartCount();
					})
					.catch(err => {
						return that.$util.Tips({
							title: err.toString()
						}, {
							tab: 3,
							url: 1
						});
					});
			},
			infoScroll: function() {
				var that = this,
					topArr = [],
					heightArr = [];
				for (var i = 0; i < that.navList.length; i++) {
					//productList
					//获取元素所在位置
					var query = uni.createSelectorQuery().in(this);
					var idView = '#past' + i;
					// if (!that.data.good_list.length && i == 2) {
					//   var idView = "#past" + 3;
					// }
					query.select(idView).boundingClientRect();
					query.exec(function(res) {
						var top = res[0].top;
						var height = res[0].height;
						topArr.push(top);
						heightArr.push(height);
						that.$set(that, 'topArr', topArr);
						that.$set(that, 'heightArr', heightArr);
					});
				}
			},
			/**
			 * 拨打电话
			 */
			makePhone: function() {
				uni.makePhoneCall({
					phoneNumber: this.systemStore.phone
				});
			},
			/**
			 * 打开地图
			 *
			 */
			showMaoLocation: function() {
				if (!this.systemStore.latitude || !this.systemStore.longitude)
					return this.$util.Tips({
						title: this.$t(`缺少经纬度信息无法查看地图`)
					});
				uni.openLocation({
					latitude: parseFloat(this.systemStore.latitude),
					longitude: parseFloat(this.systemStore.longitude),
					scale: 8,
					name: this.systemStore.name,
					address: this.systemStore.address + this.systemStore.detailed_address,
					success: function() {}
				});
			},
			/**
			 * 默认选中属性
			 *
			 */
			DefaultSelect: function() {
				let productAttr = this.attr.productAttr;
				let value = [];
				for (var key in this.productValue) {
					if (this.productValue[key].stock > 0) {
						value = this.attr.productAttr.length ? key.split(',') : [];
						break;
					}
				}
				for (let i = 0; i < productAttr.length; i++) {
					this.$set(productAttr[i], 'index', value[i]);
				}
				//sort();排序函数:数字-英文-汉字；
				let productSelect = this.productValue[value.join(',')];
				if (productSelect && productAttr.length) {
					this.$set(this.attr.productSelect, 'store_name', this.storeInfo.store_name);
					this.$set(this.attr.productSelect, 'image', productSelect.image);
					this.$set(this.attr.productSelect, 'price', productSelect.price);
					this.$set(this.attr.productSelect, 'stock', productSelect.stock);
					this.$set(this.attr.productSelect, 'unique', productSelect.unique);
					this.$set(this.attr.productSelect, 'cart_num', 1);
					this.$set(this.attr.productSelect, 'quota', productSelect.quota);
					this.$set(this, 'attrValue', value.join(','));
					this.$set(this.attr.productSelect, 'vip_price', productSelect.vip_price);
					this.$set(this, 'attrTxt', this.$t(`已选择`));
				} else if (!productSelect && productAttr.length) {
					this.$set(this.attr.productSelect, 'store_name', this.storeInfo.store_name);
					this.$set(this.attr.productSelect, 'image', this.storeInfo.image);
					this.$set(this.attr.productSelect, 'price', this.storeInfo.price);
					this.$set(this.attr.productSelect, 'stock', 0);
					this.$set(this.attr.productSelect, 'unique', '');
					this.$set(this.attr.productSelect, 'cart_num', 0);
					this.$set(this.attr.productSelect, 'quota', productSelect.quota);
					this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
					this.$set(this, 'attrValue', '');
					this.$set(this, 'attrTxt', this.$t(`请选择`));
				} else if (!productSelect && !productAttr.length) {
					this.$set(this.attr.productSelect, 'store_name', this.storeInfo.store_name);
					this.$set(this.attr.productSelect, 'image', this.storeInfo.image);
					this.$set(this.attr.productSelect, 'price', this.storeInfo.price);
					this.$set(this.attr.productSelect, 'stock', this.storeInfo.stock);
					this.$set(this.attr.productSelect, 'unique', this.storeInfo.unique || '');
					this.$set(this.attr.productSelect, 'cart_num', 1);
					this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
					this.$set(this, 'attrValue', '');
					this.$set(this, 'attrTxt', this.$t(`请选择`));
				}
			},
			/**
			 * 获取优惠券
			 *
			 */
			getCouponList(type) {
				let that = this,
					obj = {
						page: 1,
						limit: 20,
						product_id: that.id
					};
				if (type !== undefined || type !== null) {
					obj.type = type;
				}
				getCoupons(obj).then(res => {
					that.$set(that.coupon, 'count', res.data.count);
					if (type === undefined || type === null) {
						let count = [...that.coupon.count],
							indexs = '';
						let index = count.findIndex(item => item);
						let delCount = that.coupon.count,
							newDelCount = [];
						let countIndex = 0;
						delCount.forEach((item, index) => {
							if (item === 0) {
								countIndex = index;
							} else {
								newDelCount.push(item)
							}
						});
						if (newDelCount.length == 3) {
							indexs = 2;
						} else if (newDelCount.length == 2) {
							if (countIndex === 2) {
								indexs = 1;
							} else {
								indexs = 2;
							}
						} else {
							indexs = delCount.findIndex(item => item === count[index]);
						}
						that.$set(that.coupon, 'type', indexs);
						that.getCouponList(indexs);
					} else {
						that.$set(that.coupon, 'list', res.data.list);
					}
				});
			},
			ChangCouponsUseState(index) {
				let that = this;
				that.coupon.list[index].is_use = true;
				that.$set(that.coupon, 'list', that.coupon.list);
				that.$set(that.coupon, 'coupon', false);
			},
			/**
			 *
			 *
			 * 收藏商品
			 */
			setCollect: function() {
				if (this.isLogin === false) {
					toLogin();
				} else {
					let that = this;
					if (this.storeInfo.userCollect) {
						collectDel([this.storeInfo.product_id]).then(res => {
							that.$set(that.storeInfo, 'userCollect', !that.storeInfo.userCollect);
							return that.$util.Tips({
								title: res.msg
							});
						});
					} else {
						collectAdd(this.storeInfo.product_id).then(res => {
							that.$set(that.storeInfo, 'userCollect', !that.storeInfo.userCollect);
							return that.$util.Tips({
								title: res.msg
							});
						});
					}
				}
			},
			/**
			 * 打开属性插件
			 */
			selecAttr: function() {
				this.$set(this.attr, 'cartAttr', true);
				this.$set(this, 'isOpen', true);
			},
			/**
			 * 打开优惠券插件
			 */
			couponTap: function() {
				let that = this;
				if (that.isLogin === false) {
					toLogin();
				} else {
					that.getCouponList();
					that.$set(that.coupon, 'coupon', true);
				}
			},
			onMyEvent: function() {
				this.$set(this.attr, 'cartAttr', false);
				this.$set(this, 'isOpen', false);
			},
			/**
			 * 打开属性加入购物车
			 *
			 */
			joinCart: function(e) {
				//是否登录
				if (this.isLogin === false) {
					toLogin();
				} else {
					this.goCat();
				}
			},
			/*
			 * 加入购物车
			 */
			goCat: function(news) {
				let that = this,
					productSelect = that.productValue[this.attrValue];
				//打开属性
				if (that.attrValue) {
					//默认选中了属性，但是没有打开过属性弹窗还是自动打开让用户查看默认选中的属性
					that.attr.cartAttr = !that.isOpen ? true : false;
				} else {
					if (that.isOpen) that.attr.cartAttr = true;
					else that.attr.cartAttr = !that.attr.cartAttr;
				}
				//只有关闭属性弹窗时进行加入购物车
				if (that.attr.cartAttr === true && that.isOpen === false) return (that.isOpen = true);
				//如果有属性,没有选择,提示用户选择
				if (that.attr.productAttr.length && productSelect === undefined && that.isOpen === true)
					return that.$util.Tips({
						title: that.$t(`产品库存不足，请选择其它属性`)
					});
				let q = {
					productId: that.storeInfo.product_id,
					advanceId: that.id,
					cartNum: that.attr.productSelect.cart_num,
					uniqueId: that.attr.productSelect !== undefined ? that.attr.productSelect.unique : '',
					'new': 1
				};
				postCartAdd(q)
					.then(function(res) {
						that.isOpen = false;
						that.attr.cartAttr = false;
						uni.navigateTo({
							url: '/pages/goods/order_confirm/index?new=1&cartId=' + res.data.cartId
						});
					})
					.catch(err => {
						that.isOpen = false;
						return that.$util.Tips({
							title: err
						});
					});
			},
			/**
			 * 获取购物车数量
			 * @param boolean 是否展示购物车动画和重置属性
			 */
			getCartCount: function(isAnima) {
				let that = this;
				const isLogin = that.isLogin;
				if (isLogin) {
					getCartCounts().then(res => {
						that.CartCount = res.data.count;
						//加入购物车后重置属性
						if (isAnima) {
							that.animated = true;
							setTimeout(function() {
								that.animated = false;
							}, 500);
						}
					});
				}
			},
			/**
			 * 立即购买
			 */
			goBuy: function(e) {
				if (this.isLogin === false) {
					toLogin();
				} else {
					this.goCat(true);
				}
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e;
			},
			/**
			 * 分享打开
			 *
			 */
			listenerActionSheet: function() {
				if (this.isLogin === false) {
					toLogin();
				} else {
					// #ifdef H5
					if (this.$wechat.isWeixin() === true) {
						this.weixinStatus = true;
					}
					// #endif
					this.posters = true;

				}
			},
			// 分享关闭
			listenerActionClose: function() {
				this.posters = false;
			},
			//隐藏海报
			posterImageClose: function() {
				this.posterImageStatus = false;
			},
			//替换安全域名
			setDomain: function(url) {
				url = url ? url.toString() : '';
				//本地调试打开,生产请注销
				if (url.indexOf('https://') > -1) return url;
				else return url.replace('http://', 'https://');
			},
			//获取海报产品图
			downloadFilestoreImage: function() {
				let that = this;
				uni.downloadFile({
					url: that.setDomain(that.storeInfo.image),
					success: function(res) {
						that.storeImage = res.tempFilePath;
					},
					fail: function() {
						return that.$util.Tips({
							title: ''
						});
						that.storeImage = '';
					}
				});
			},
			/**
			 * 获取产品分销二维码
			 * @param function successFn 下载完成回调
			 *
			 */
			downloadFilePromotionCode: function(successFn) {
				let that = this;
				getProductCode(that.storeInfo.product_id)
					.then(res => {
						uni.downloadFile({
							url: that.setDomain(res.data.code),
							success: function(res) {
								that.$set(that, 'isDown', false);
								if (typeof successFn == 'function') successFn && successFn(res
									.tempFilePath);
								else that.$set(that, 'PromotionCode', res.tempFilePath);
							},
							fail: function() {
								that.$set(that, 'isDown', false);
								that.$set(that, 'PromotionCode', '');
							}
						});
					})
					.catch(err => {
						that.$set(that, 'isDown', false);
						that.$set(that, 'PromotionCode', '');
					});
			},
			getImageBase64: function() {
				let that = this;
				imageBase64(that.storeImage, that.PromotionCode)
					.then(res => {
						that.storeImage = res.data.image;
						that.PromotionCode = res.data.code;
					})
					.catch(() => {});
			},
			// 小程序关闭分享弹窗；
			goFriend: function() {
				this.posters = false;
			},
			/**
			 * 生成海报
			 */
			goPoster: function() {
				let that = this;
				that.posters = false;
				that.$set(that, 'canvasStatus', true);
				let arr2 = [that.posterbackgd, that.storeImage, that.PromotionCode];
				uni.getImageInfo({
					src: that.PromotionCode,
					fail: function(res) {
						// #ifdef H5
						return that.$util.Tips({
							title: res,
						});
						// #endif
						// #ifdef MP
						return that.$util.Tips({
							title: that.$t(`正在下载海报,请稍后再试`),
						});
						// #endif
					},
					success() {
						if (arr2[2] == '') {
							//海报二维码不存在则从新下载
							that.downloadFilePromotionCode(function(msgPromotionCode) {
								arr2[2] = msgPromotionCode;
								if (arr2[2] == '')
									return that.$util.Tips({
										title: that.$t(`海报二维码生成失败`)
									});
								that.$util.PosterCanvas(arr2, that.storeInfo.title, that.storeInfo
									.price, that.storeInfo.ot_price,
									function(tempFilePath) {
										that.$set(that, 'posterImage', tempFilePath);
										that.$set(that, 'posterImageStatus', true);
										that.$set(that, 'canvasStatus', false);
										that.$set(that, 'actionSheetHidden', !that
											.actionSheetHidden);
									});
							});

						} else {
							//生成推广海报
							that.$util.PosterCanvas(arr2, that.storeInfo.title, that.storeInfo.price, that
								.storeInfo.ot_price,
								function(tempFilePath) {
									that.$set(that, 'posterImage', tempFilePath);
									that.$set(that, 'posterImageStatus', true);
									that.$set(that, 'canvasStatus', false);
									that.$set(that, 'actionSheetHidden', !that.actionSheetHidden);
								});
						}
					},
				});
			},

			/*
			 * 保存到手机相册
			 */
			// #ifdef MP
			copyCommand: function() {
				if (wx.navigateToMiniProgram) {
					wx.navigateToMiniProgram({
						appId: 'wxb036cafe2994d7d0',
						path: '/publish/ugc-publish/ugc-publish',
						extraData: {
							productInfo: {
								title: this.storeInfo.store_name,
								path: '/pages/goods_details/index?id=' + this.storeInfo.id,
								thumbUrl: this.storeInfo.image
							}
						}
					});
				}
			},
			savePosterPath: function() {
				let that = this;
				uni.getSetting({
					success(res) {
						if (!res.authSetting['scope.writePhotosAlbum']) {
							uni.authorize({
								scope: 'scope.writePhotosAlbum',
								success() {
									uni.saveImageToPhotosAlbum({
										filePath: that.posterImage,
										success: function(res) {
											that.posterImageClose();
											that.$util.Tips({
												title: that.$t(`保存成功`),
												icon: 'success'
											});
										},
										fail: function(res) {
											that.$util.Tips({
												title: that.$t(`保存失败`)
											});
										}
									});
								}
							});
						} else {
							uni.saveImageToPhotosAlbum({
								filePath: that.posterImage,
								success: function(res) {
									that.posterImageClose();
									that.$util.Tips({
										title: that.$t(`保存成功`),
										icon: 'success'
									});
								},
								fail: function(res) {
									that.$util.Tips({
										title: that.$t(`保存失败`)
									});
								}
							});
						}
					}
				});
			},
			// #endif
			//#ifdef H5
			ShareInfo() {
				let data = this.storeInfo;
				let href = location.href;
				if (this.$wechat.isWeixin()) {
					getUserInfo().then(res => {
						href = href.indexOf('?') === -1 ? href + '?spread=' + res.data.uid : href + '&spread=' +
							res.data.uid;

						let configAppMessage = {
							desc: data.store_info,
							title: data.store_name,
							link: href,
							imgUrl: data.image
						};
						this.$wechat
							.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData',
								'onMenuShareAppMessage', 'onMenuShareTimeline'
							], configAppMessage)
							.then(res => {
							})
							.catch(err => {
							});
					});
				}
			},
			//#endif
			tabCouponType: function(type) {
				this.$set(this.coupon, 'type', type);
				this.getCouponList(type);
			},
			//点击sku图片打开轮播图
			showImg(index) {
				this.$refs.cusPreviewImg.open(this.selectSku.suk);
			},
			//滑动轮播图选择商品
			changeSwitch(e) {
				let productSelect = this.skuArr[e];
				this.$set(this, "selectSku", productSelect);
				var skuList = productSelect.suk.split(",");
				this.$set(this.attr.productAttr[0], "index", skuList[0]);
				if (skuList.length == 2) {
					this.$set(this.attr.productAttr[0], "index", skuList[0]);
					this.$set(this.attr.productAttr[1], "index", skuList[1]);
				} else if (skuList.length == 3) {
					this.$set(this.attr.productAttr[0], "index", skuList[0]);
					this.$set(this.attr.productAttr[1], "index", skuList[1]);
					this.$set(this.attr.productAttr[2], "index", skuList[2]);
				} else if (skuList.length == 4) {
					this.$set(this.attr.productAttr[0], "index", skuList[0]);
					this.$set(this.attr.productAttr[1], "index", skuList[1]);
					this.$set(this.attr.productAttr[2], "index", skuList[2]);
			 	this.$set(this.attr.productAttr[3], "index", skuList[3]);
				}
				if (productSelect) {
					this.$set(this.attr.productSelect, "image", productSelect.image);
					this.$set(this.attr.productSelect, "price", productSelect.price);
			 	this.$set(this.attr.productSelect, "stock", productSelect.stock);
					this.$set(this.attr.productSelect, "unique", productSelect.unique);
					this.$set(this.attr.productSelect, "vipPrice", productSelect.vipPrice);
					this.$set(this, "attrTxt", this.$t(`已选择`));
					this.$set(this, "attrValue", productSelect.suk);
				}
			},
		}
	};
</script>

<style scoped lang="scss">
	.activity_pin {
		width: auto;
		height: 44rpx;
		line-height: 44rpx;
		background: linear-gradient(90deg, rgba(233, 51, 35, 1) 0%, rgba(250, 101, 20, 1) 100%);
		opacity: 1;
		border-radius: 22rpx;
		padding: 0 15rpx;
		margin-left: 19rpx;
	}

	.activity_miao {
		width: auto;
		height: 44rpx;
		line-height: 44rpx;
		padding: 0 15rpx;
		background: linear-gradient(90deg, rgba(250, 102, 24, 1) 0%, rgba(254, 161, 15, 1) 100%);
		opacity: 1;
		border-radius: 22rpx;
		margin-left: 19rpx;
	}

	.iconfonts {
		color: #fff !important;
		font-size: 28rpx;
	}

	.activity_title {
		font-size: 24rpx;
		color: #fff;
	}

	.activity_kan {
		width: auto;
		height: 44rpx;
		line-height: 44rpx;
		padding: 0 15rpx;
		background: linear-gradient(90deg, rgba(254, 159, 15, 1) 0%, rgba(254, 178, 15, 1) 100%);
		opacity: 1;
		border-radius: 22rpx;
		margin-left: 19rpx;
	}

	.mask {
		z-index: 300 !important;
	}

	.head-bar {
		background: #fff;
	}

	.generate-posters {
		width: 100%;
		height: 170rpx;
		background-color: #fff;
		position: fixed;
		left: 0;
		bottom: 0;
		z-index: 388;
		transform: translate3d(0, 100%, 0);
		transition: all 0.3s cubic-bezier(0.25, 0.5, 0.5, 0.9);
		border-top: 1rpx solid #eee;
	}

	.generate-posters.on {
		transform: translate3d(0, 0, 0);
	}

	.generate-posters .item {
		flex: 1;
		text-align: center;
		font-size: 30rpx;
	}

	.generate-posters .item .iconfont {
		font-size: 80rpx;
		color: #5eae72;
	}

	.generate-posters .item .iconfont.icon-haibao {
		color: #5391f1;
	}

	.generate-posters .item .iconfont.icon-haowuquan1 {
		color: #ff954d;
	}

	.product-con .footer {
		padding: 0 20rpx 0 0rpx;
		position: fixed;
		bottom: 0;
		width: 100%;
		box-sizing: border-box;
		background-color: #fff;
		z-index: 277;
		border-top: 1rpx solid #f0f0f0;
		height: 100rpx;
		display: flex;
		flex-wrap: nowrap;
		height: calc(100rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		height: calc(100rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
	}

	.product-con .footer .item {
		font-size: 18rpx;
		padding: 0 20rpx;
		color: #666;
	}

	.product-con .footer .item .iconfont {
		text-align: center;
		font-size: 40rpx;
	}

	.product-con .footer .item .iconfont.icon-shoucang1 {
		color: var(--view-theme);
	}

	.product-con .footer .item .iconfont.icon-gouwuche1 {
		font-size: 40rpx;
		position: relative;
	}

	.product-con .footer .item .iconfont.icon-gouwuche1 .num {
		color: #fff;
		position: absolute;
		font-size: 18rpx;
		padding: 2rpx 10rpx 3rpx;
		border-radius: 200rpx;
		top: -10rpx;
		right: -10rpx;
	}

	.product-con .footer .bnt {
		flex: 1;
		height: 76rpx;
	}

	.product-con .footer .bnt .bnts {
		width: 100%;
		text-align: center;
		line-height: 76rpx;
		color: #fff;
		font-size: 28rpx;
		border-radius: 50rpx;
		background-color: var(--view-theme);
	}

	.product-con .store-info {
		margin-top: 20rpx;
		background-color: #fff;
	}

	.product-con .store-info .title {
		padding: 0 30rpx;
		font-size: 28rpx;
		color: #282828;
		height: 80rpx;
		line-height: 80rpx;
		border-bottom: 1px solid #f5f5f5;
	}

	.product-con .store-info .info {
		padding: 0 30rpx;
		height: 126rpx;
	}

	.product-con .store-info .info .picTxt {
		width: 615rpx;
	}

	.product-con .store-info .info .picTxt .pictrue {
		width: 76rpx;
		height: 76rpx;
	}

	.product-con .store-info .info .picTxt .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.product-con .store-info .info .picTxt .text {
		width: 522rpx;
	}

	.product-con .store-info .info .picTxt .text .name {
		font-size: 30rpx;
		color: #282828;
	}

	.product-con .store-info .info .picTxt .text .address {
		font-size: 24rpx;
		color: #666;
		margin-top: 3rpx;
	}

	.product-con .store-info .info .picTxt .text .address .iconfont {
		color: #707070;
		font-size: 18rpx;
		margin-left: 10rpx;
	}

	.product-con .store-info .info .picTxt .text .address .addressTxt {
		max-width: 480rpx;
	}

	.product-con .store-info .info .iconfont {
		font-size: 40rpx;
	}

	.product-con .superior {
		background-color: #fff;
		margin-top: 20rpx;
		padding-bottom: 10rpx;
	}

	.product-con .superior .title {
		height: 98rpx;
	}

	.product-con .superior .title image {
		width: 30rpx;
		height: 30rpx;
	}

	.product-con .superior .title .titleTxt {
		margin: 0 20rpx;
		font-size: 30rpx;
		background-image: linear-gradient(to right, #f57a37 0%, #f21b07 100%);
		-webkit-background-clip: text;
		-webkit-text-fill-color: transparent;
	}

	.product-con .superior .slider-banner {
		width: 690rpx;
		margin: 0 auto;
		position: relative;
	}

	.product-con .superior .slider-banner swiper {
		height: 100%;
		width: 100%;
	}

	.product-con .superior .slider-banner swiper-item {
		height: 100%;
	}

	.product-con .superior .slider-banner .list {
		width: 100%;
	}

	.product-con .superior .slider-banner .list .item {
		width: 215rpx;
		margin: 0 22rpx 30rpx 0;
		font-size: 26rpx;
	}

	.product-con .superior .slider-banner .list .item:nth-of-type(3n) {
		margin-right: 0;
	}

	.product-con .superior .slider-banner .list .item .pictrue {
		position: relative;
		width: 100%;
		height: 215rpx;
		border-radius: 20rpx;
	}

	.product-con .superior .slider-banner .list .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 20rpx;
	}

	.product-con .superior .slider-banner .list .item .name {
		color: #282828;
		margin-top: 12rpx;
	}

	.product-con .superior .slider-banner .swiper-pagination-bullet {
		background-color: #999;
	}

	.product-con .superior .slider-banner .swiper-pagination-bullet-active {
		background-color: #e93323;
	}

	button {
		padding: 0;
		margin: 0;
		line-height: normal;
		background-color: #fff;
	}

	button::after {
		border: 0;
	}

	action-sheet-item {
		padding: 0;
		height: 240rpx;
		align-items: center;
		display: flex;
	}

	.contact {
		font-size: 16px;
		width: 50%;
		background-color: #fff;
		padding: 8rpx 0;
		border-radius: 0;
		margin: 0;
		line-height: 2;
	}

	.contact::after {
		border: none;
	}

	.action-sheet {
		font-size: 17px;
		line-height: 1.8;
		width: 50%;
		position: absolute;
		top: 0;
		right: 0;
		padding: 25rpx 0;
	}

	.canvas {
		z-index: 300;
		width: 750px;
		height: 1190px;
	}

	.poster-pop {
		width: 450rpx;
		height: 714rpx;
		position: fixed;
		left: 50%;
		transform: translateX(-50%);
		z-index: 399;
		top: 50%;
		margin-top: -357rpx;
	}

	.poster-pop image {
		width: 100%;
		height: 100%;
		display: block;
	}

	.poster-pop .close {
		width: 46rpx;
		height: 75rpx;
		position: fixed;
		right: 0;
		top: -73rpx;
		display: block;
	}

	.poster-pop .save-poster {
		background-color: #df2d0a;
		font-size: ：22rpx;
		color: #fff;
		text-align: center;
		height: 76rpx;
		line-height: 76rpx;
		width: 100%;
	}

	.poster-pop .keep {
		color: #fff;
		text-align: center;
		font-size: 25rpx;
		margin-top: 10rpx;
	}

	.mask {
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: rgba(0, 0, 0, 0.6);
		z-index: 9;
	}
	.navbar .header {
		height: 96rpx;
		font-size: 30rpx;
		color: #050505;
		background-color: #fff;
	}

	.home {
		/* #ifdef H5 */
		top: 20rpx !important;
		/* #endif */
	}

	.navbar .header .item {
		position: relative;
		margin: 0 25rpx;
	}

	.navbar .header .item.on:before {
		position: absolute;
		width: 60rpx;
		height: 5rpx;
		background-repeat: no-repeat;
		content: '';
		background-image: linear-gradient(to right, #ff3366 0%, #ff6533 100%);
		bottom: -10rpx;
		left: 50%;
		margin-left: -28rpx;
	}

	.navbar {
		position: fixed;
		background-color: #fff;
		top: 0;
		left: 0;
		z-index: 99;
		width: 100%;
	}

	.navbar .navbarH {
		position: relative;
	}

	.navbar .navbarH .navbarCon {
		position: absolute;
		bottom: 0;
		height: 100rpx;
		width: 100%;
		/* #ifndef APP-PLUS || H5 || MP-ALIPAY */
		justify-content: flex-end;
		/* #endif */
	}

	.home {
		color: #fff;
		position: fixed;
		width: 56rpx;
		height: 56rpx;
		z-index: 99;
		left: 33rpx;
		background: rgba(190, 190, 190, 0.5);
		border-radius: 50%;
		font-size: 33rpx;

		&.on {
			background: unset;
			color: #333;
		}
	}

	.home .line {
		width: 1rpx;
		height: 24rpx;
		background: rgba(255, 255, 255, 0.25);
	}

	.home .icon-xiangzuo {
		font-size: 28rpx;
	}

	.share-box {
		z-index: 1000;
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;

		image {
			width: 100%;
			height: 100%;
		}
	}

	.product-con .conter {
		display: block;
	}

	.product-con .conter img {
		display: block;
	}

	.svip {
		height: 64rpx;
		padding: 0 26rpx 0 60rpx;
		margin: 24rpx 30rpx 0;
		background: url('data:image/png;base64,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') center/100% 100% no-repeat;
		font-size: 24rpx;
		color: #ae5a2a;
	}

	.svip .iconfont {
		margin-left: 12rpx;
		font-size: 24rpx;
	}

	.product-con .wrapper .share .money .jvip {
		width: 46rpx;
		height: 22rpx;
	}

	.product-con .wrapper .share .money image {
		width: 66rpx;
		height: 26rpx;
	}

	.presell_count {
		margin-top: 20rpx;
		font-size: 26rpx;
		color: #999999;
		padding: 20rpx 30rpx;

		.presell_time {
			margin: 8rpx 0 4rpx;

			.area_line {
				display: inline-block;
				margin: 0 6rpx;
			}
		}

		.icon-shijian1 {
			display: inline-block;
			margin-right: 4rpx;
		}
	}

	.price_text {
		width: 90rpx;
		height: 30rpx;
		background-size: 100%;
		color: var(--view-bntColor);
		font-size: 22rpx;
		line-height: 30rpx;
		text-align: center;
		margin-left: 27rpx;
		padding: 4rpx 6rpx;
		border-radius: 4rpx;
		background-color: var(--view-op-ten);
	}

	.attrImg {
		width: 66rpx;
		height: 66rpx;
		border-radius: 6rpx;
		display: block;
		margin-right: 14rpx;
	}

	.switchTxt {
		height: 60rpx;
		flex: 1;
		line-height: 60rpx;
		box-sizing: border-box;
		background: #eeeeee;
		padding: 0 10rpx;
		border-radius: 8rpx;
		text-align: center;
	}

	.attribute {
		padding: 10rpx 30rpx;

		.line1 {
			width: 600rpx;
		}
	}

	.flex {
		display: flex;
		justify-content: space-between;
		width: 100%;
	}

	.flexs {
		display: flex;
	}

	.attr-txt {
		display: flex;
		flex-wrap: nowrap;
		width: 130rpx;
	}
	
	.delete-line {
		text-decoration: line-through;
	}
</style>
