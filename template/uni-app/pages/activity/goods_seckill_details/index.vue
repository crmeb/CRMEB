<template>
	<view>
		<!-- 头部 -->
		<view class='navbar' :style="{height:navH+'rpx',opacity:opacity}">
			<view class='navbarH' :style='"height:"+navH+"rpx;"'>
				<view class='navbarCon acea-row row-center-wrapper'>
					<view class="header acea-row row-center-wrapper">
						<view class="item" :class="navActive === index ? 'on' : ''" v-for="(item,index) in navList" :key='index' @tap="tap(item,index)">
							{{ item }}
						</view>
					</view>
				</view>
			</view>
		</view>
		<!-- <view class='iconfont icon-xiangzuo' :style="'top:'+navH/2+'rpx'" @tap='returns'></view> -->
		<view id="home" class="home-nav acea-row row-center-wrapper iconfont icon-xiangzuo" :class="opacity>0.5?'on':''" :style="{ top: homeTop + 'rpx' }" v-if="returnShow" @tap="returns">
			<!-- <view class="line" v-if="returnShow"></view>
			<navigator url="/pages/index/index" class="iconfont icon-shouye4" hover-class="none"></navigator> -->
		</view>
		<view class='product-con'>
			<scroll-view :scroll-top="scrollTop" scroll-y='true' scroll-with-animation="true" :style="'height:'+height+'px;'"
			 @scroll="scroll">
				<view id="past0">
					<productConSwiper :imgUrls='imgUrls'></productConSwiper>
					<view class='nav acea-row row-between-wrapper'>
						<view class='money'>￥<text class='num'>{{storeInfo.price}}</text>
						<text v-if="attribute.productAttr.length && (attribute.productAttr.length?attribute.productAttr[0].attr_values.length:0) > 1">起</text>
						<text class='y-money'>￥{{storeInfo.ot_price}}</text></view>
						<view class='acea-row row-middle'>
							<view class='timeItem' v-if="status == 1">
								<view>距秒杀结束仅剩</view>
								<!-- <view class='timeCon'>
									<text class='num'>{{countDownHour}}</text>：
									<text class='num'>{{countDownMinute}}</text>：
									<text class='num'>{{countDownSecond}}</text>
								</view> -->
								<countDown :is-day="false" :tip-text="' '" :day-text="' '" :hour-text="' : '" :minute-text="' : '" :second-text="' '"
								 :datatime="datatime" style="margin-top: 4rpx;"></countDown>
							</view>
							<!-- <view class="timeState" wx:if="{{status == 0}}">已结束</view>
		        <view class="timeState" wx:if="{{status == 2}}">即将开始</view> -->
							<!-- <view class='iconfont icon-jiantou'></view> -->
						</view>
					</view>
					<view class='wrapper'>
						<view class='introduce acea-row row-between'>
							<view class='infor'> {{storeInfo.title}}</view>
							<!-- <button class='iconfont icon-fenxiang' open-type='share'></button> -->
							<view class='iconfont icon-fenxiang' @click="listenerActionSheet"></view>
						</view>
						<view class='label acea-row row-middle'>
							<!-- <view class='stock'>库存：{{storeInfo.stock}}{{storeInfo.unit_name}}</view> -->
							<view class='stock'>累计销售：{{storeInfo.total?storeInfo.total:0}}{{storeInfo.unit_name || ''}}</view>
							<view>限量: {{ storeInfo.quota_show ? storeInfo.quota_show : 0 }}{{storeInfo.unit_name || ''}}</view>
						</view>
					</view>
					<view class='attribute acea-row row-between-wrapper' @tap='selecAttr' v-if='attribute.productAttr.length'>
						<view>{{attr}}：<text class='atterTxt'>{{attrValue}}</text></view>
						<view class='iconfont icon-jiantou'></view>
					</view>
				</view>
				<view class='userEvaluation' id="past1">
					<view class='title acea-row row-between-wrapper'>
						<view>用户评价({{replyCount}})</view>
						<navigator class='praise' hover-class='none' :url="'/pages/users/goods_comment_list/index?product_id='+storeInfo.product_id">
							<text class='font-color'>{{replyChance}}%</text>好评率
							<text class='iconfont icon-jiantou'></text>
						</navigator>
					</view>
					<userEvaluation :reply="reply"></userEvaluation>
				</view>
				<view class='product-intro' id="past2">
					<view class='title'>产品介绍</view>
					<view class='conter'>
						<!-- <template is="wxParse" data="{{wxParseData:description.nodes}}" /> -->
						<jyf-parser :html="storeInfo.description" ref="article" :tag-style="tagStyle"></jyf-parser>
					</view>
				</view>
			</scroll-view>
			<view class="uni-p-b-98"></view>
			<view class='footer acea-row row-between-wrapper'>
				<!-- #ifdef MP -->
				<!-- <button open-type="contact" hover-class='none' class='item'>
					<view class='iconfont icon-kefu'></view>
					<view class="p_center">客服</view>
				</button> -->
				<!-- #endif -->
				<!-- #ifndef MP -->
			<!-- 	<navigator hover-class="none" class="item" url="/pages/customer_list/chat">
					<view class="iconfont icon-kefu"></view>
					<view class="p_center">客服</view>
				</navigator> -->
				<!-- #endif -->
				<navigator hover-class="none" class="item" url="/pages/index/index">
					<view class="iconfont icon-shouye6"></view>
					<view class="p_center">首页</view>
				</navigator>
				<view @tap='setCollect' class='item'>
					<view class='iconfont icon-shoucang1' v-if="storeInfo.userCollect"></view>
					<view class='iconfont icon-shoucang' v-else></view>
					<view class="p_center">收藏</view>
				</view>
				    <view class="bnt acea-row" v-if="status == 1 && attribute.productSelect.quota > 0 && attribute.productSelect.product_stock>0">
				    	<view class="joinCart bnts" @tap="openAlone">单独购买</view>
				    	<view class="buy bnts" @tap="goCat">立即购买</view>
				    </view>
				    <view class="bnt acea-row" v-if="(status == 1 && attribute.productSelect.quota <= 0) || (status == 3 && attribute.productSelect.quota <= 0) || (status == 1 && attribute.productSelect.product_stock <= 0) || (status == 3 && attribute.productSelect.product_stock <= 0)">
				    	<view class="joinCart bnts" @tap="openAlone">单独购买</view>
				    	<view class="buy bnts bg-color-hui">已售罄</view>
				    </view>
					<view class="bnt acea-row" v-if="!dataShow && status == 1">
					    <view class="joinCart bnts" @tap="openAlone">单独购买</view>
					 	<view class="buy bnts bg-color-hui">立即购买</view>
					</view>
					<view class="bnt acea-row" v-if="status == 2">
						<view class="joinCart bnts" @tap="openAlone">单独购买</view>
						<view class="buy bnts bg-color-hui">未开始</view>
					</view>
					<view class="bnt acea-row" v-if="status == 0">
						<view class="joinCart bnts" @tap="openAlone">单独购买</view>
						<view class="buy bnts bg-color-hui">已结束</view>
					</view>
			</view>
		</view>
		<product-window :attr='attribute' :limitNum='1' @myevent="onMyEvent" @ChangeAttr="ChangeAttr" @ChangeCartNum="ChangeCartNum"
		 @attrVal="attrVal" @iptCartNum="iptCartNum"></product-window>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth"></authorize> -->
		<!-- #endif -->
		<!-- 分享按钮 -->
		<view class="generate-posters acea-row row-middle" :class="posters ? 'on' : ''">
			<!-- #ifndef MP -->
			<button class="item" hover-class='none' v-if="weixinStatus === true" @click="H5ShareBox = true">
			<!-- <button class="item" hover-class='none' v-if="weixinStatus === true" @click="setShareInfoStatus"> -->
				<view class="iconfont icon-weixin3"></view>
				<view class="">发送给朋友</view>
			</button>
			<!-- #endif -->
			<!-- #ifdef MP -->
			<button class="item" open-type="share" hover-class='none' @click="goFriend">
				<view class="iconfont icon-weixin3"></view>
				<view class="">发送给朋友</view>
			</button>
			<!-- #endif -->
			<button class="item" hover-class='none' @tap="goPoster">
				<view class="iconfont icon-haibao"></view>
				<view class="">生成海报</view>
			</button>
		</view>
		<view class="mask" v-if="posters" @click="listenerActionClose"></view>

		<!-- 海报展示 -->
		<view class='poster-pop' v-if="posterImageStatus">
			<image src='/static/images/poster-close.png' class='close' @click="posterImageClose"></image>
			<image :src='posterImage'></image>
			<!-- #ifndef H5  -->
			<view class='save-poster' @click="savePosterPath">保存到手机</view>
			<!-- #endif -->
			<!-- #ifdef H5 -->
			<view class="keep">长按图片可以保存到手机</view>
			<!-- #endif -->
		</view>
		<view class='mask1' v-if="posterImageStatus"></view>
		<canvas class="canvas" canvas-id='myCanvas' v-if="canvasStatus"></canvas>
		<kefuIcon :ids='storeInfo.product_id'></kefuIcon>
		<!-- 发送给朋友图片 -->
		<view class="share-box" v-if="H5ShareBox"><image src="/static/images/share-info.png" @click="H5ShareBox = false"></image></view>
	</view>
</template>

<script>
	const app = getApp();
	import {
		mapGetters
	} from "vuex";
	import {
		getSeckillDetail,
		seckillCode
	} from '@/api/activity.js';
	import {
		postCartAdd,
		collectAdd,
		collectDel
	} from '@/api/store.js';
	import productConSwiper from '@/components/productConSwiper/index.vue'
	import productWindow from '@/components/productWindow/index.vue'
	import userEvaluation from '@/components/userEvaluation/index.vue'
	import kefuIcon from '@/components/kefuIcon';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import parser from "@/components/jyf-parser/jyf-parser";
	import countDown from '@/components/countDown';
	import {
		imageBase64
	} from "@/api/public";
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		silenceBindingSpread
	} from "@/utils";
	import { getUserInfo } from '@/api/user.js';
	export default {
		computed: mapGetters(['isLogin']),
		data() {
			return {
				dataShow:0,
				id: 0,
				time: 0,
				countDownHour: "00",
				countDownMinute: "00",
				countDownSecond: "00",
				storeInfo: [],
				imgUrls: [],
				parameter: {
					'navbar': '1',
					'return': '1',
					'title': '抢购详情页',
					'color': false
				},
				attribute: {
					cartAttr: false,
					productAttr: [],
					productSelect: {}
				},
				productValue: [],
				isOpen: false,
				attr: '请选择',
				attrValue: '',
				status: 1,
				isAuto: false,
				isShowAuth: false,
				iShidden: false,
				limitNum: 1, //限制本属性产品的个数；
				iSplus: false,
				replyCount: 0, //总评论数量
				reply: [], //评论列表
				replyChance: 0,
				navH: "",
				navList: ['商品', '评价', '详情'],
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
				datatime: '',
				navActive: 0,
				meunHeight: 0,
				backH: '',
				posters: false,
				weixinStatus: false,
				posterImageStatus: false,
				canvasStatus: false, //海报绘图标签
				storeImage: '', //海报产品图
				PromotionCode: '', //二维码图片
				posterImage: '', //海报路径
				posterbackgd: '/static/images/posterbackgd.png',
				actionSheetHidden: false,
				cart_num: '',
				homeTop: 20,
				returnShow: true,
				H5ShareBox: false, //公众号分享图片
			}
		},
		components: {
			productConSwiper,
			'productWindow': productWindow,
			userEvaluation,
			kefuIcon,
			"jyf-parser": parser,
			countDown,
			// #ifdef MP
			authorize
			// #endif
		},
		computed: mapGetters(['isLogin']),
		watch:{
			isLogin:{
				handler:function(newV,oldV){
					if(newV){
						this.getSeckillDetail();
					}
				},
				deep:true
			}
		},
		onLoad(options) {

			let that = this
			let statusBarHeight = ''
			var pages = getCurrentPages();
			that.returnShow = pages.length === 1 ? false : true;
			//设置商品列表高度
			uni.getSystemInfo({
				success: function(res) {
					that.height = res.windowHeight
					statusBarHeight = res.statusBarHeight
					//res.windowHeight:获取整个窗口高度为px，*2为rpx；98为头部占据的高度；
				},
			});
			this.isLogin && silenceBindingSpread();
			this.navH = app.globalData.navHeight
			// #ifdef MP
			let menuButtonInfo = uni.getMenuButtonBoundingClientRect()
			this.meunHeight = menuButtonInfo.height
			this.backH = (that.navH / 2) + (this.meunHeight / 2)

			//扫码携带参数处理
			if (options.scene) {
				let value = this.$util.getUrlParams(decodeURIComponent(options.scene));
				console.log(value, 'options')
				if (value.id) {
					this.id = value.id;
				} else {
					return this.$util.Tips({
						title: '缺少参数无法查看商品'
					}, {
						tab: 3,
						url: 1
					});
				}
				//记录推广人uid
				if (value.pid) app.globalData.spid = value.pid;
				if (value.time) this.datatime = value.time
			}
			// #endif

			if (options.id) {
				this.id = options.id
				this.datatime = Number(options.time)
				this.status = options.status
			}
			if (this.isLogin) {
				this.getSeckillDetail();
			} else {
				toLogin();
			}
			this.$nextTick(() => {
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
			})
		},
		methods: {
			/**
			 * 购物车手动填写
			 * 
			 */
			iptCartNum: function(e) {
				this.$set(this.attribute.productSelect, 'cart_num', e);
				this.$set(this, "cart_num", e);
			},
			// 后退
			returns: function() {
				uni.navigateBack()
			},
			onLoadFun: function(data) {
				if (this.isAuto) {
					this.isAuto = false;
					this.isShowAuth = false;
					this.getSeckillDetail();
				}
			},
			getSeckillDetail: function() {
				let that = this;
				getSeckillDetail(that.id,{time:that.datatime,status:that.status}).then(res => {
					this.dataShow = 1;
					let title = res.data.storeInfo.title;
					this.storeInfo = res.data.storeInfo;
					this.imgUrls = res.data.storeInfo.images;
					this.attribute.productAttr = res.data.productAttr;
					this.productValue = res.data.productValue;
					this.attribute.productSelect.num = res.data.storeInfo.num;
					this.replyCount = res.data.replyCount;
					this.reply = res.data.reply ? [res.data.reply] : [];
					this.replyChance = res.data.replyChance
					// #ifdef H5
					this.PromotionCode = res.data.storeInfo.code_base
					that.storeImage = that.storeInfo.image
					that.getImageBase64();
					that.setShare();
					// #endif
					// #ifndef H5
					that.downloadFilestoreImage();
					that.downloadFilePromotionCode();
					// #endif
					that.DefaultSelect();
					setTimeout(function() {
						that.infoScroll();
					}, 500);
					app.globalData.openPages = '/pages/activity/goods_seckill_details/index?id=' + that.id + '&time=' + that.datatime +
						'&status=' + that.status + '&pid=' + that.storeInfo.uid;
					// wxParse.wxParse('description', 'html', that.data.storeInfo.description || '', that, 0);
					// wxh.time(that.data.time, that);
				}).catch(err => {
					that.$util.Tips({
						title: err
					}, {
						tab: 3
					})
				});
			},
			setShare: function() {
				this.$wechat.isWeixin() &&
					this.$wechat.wechatEvevt([
						"updateAppMessageShareData",
						"updateTimelineShareData",
						"onMenuShareAppMessage",
						"onMenuShareTimeline"
					], {
						desc: this.storeInfo.info,
						title: this.storeInfo.title,
						link: location.href,
						imgUrl: this.storeInfo.image
					}).then(res => {
						console.log(res);
					}).catch(err => {
						console.log(err);
					});
			},
			/**
			 * 默认选中属性
			 * 
			 */
			DefaultSelect: function() {
				let self = this
				let productAttr = self.attribute.productAttr;
				let value = [];
				for (var key in this.productValue) {
					if (this.productValue[key].quota > 0) {
						value = this.attribute.productAttr.length ? key.split(",") : [];
						break;
					}
				}
				for (let i = 0; i < productAttr.length; i++) {
					this.$set(productAttr[i], "index", value[i]);
				}
				//sort();排序函数:数字-英文-汉字；
				let productSelect = this.productValue[value.join(",")];
				if (productSelect && productAttr.length) {
					self.$set(
						self.attribute.productSelect,
						"store_name",
						self.storeInfo.title
					);
					self.$set(self.attribute.productSelect, "image", productSelect.image);
					self.$set(self.attribute.productSelect, "price", productSelect.price);
					self.$set(self.attribute.productSelect, "stock", productSelect.stock);
					self.$set(self.attribute.productSelect, "unique", productSelect.unique);
					self.$set(self.attribute.productSelect, "quota", productSelect.quota);
					self.$set(self.attribute.productSelect, "quota_show", productSelect.quota_show);
					self.$set(self.attribute.productSelect, "product_stock", productSelect.product_stock);
					self.$set(self.attribute.productSelect, "cart_num", 1);
					self.$set(self, "attrValue", value.join(","));
					self.attrValue = value.join(",")
				} else if (!productSelect && productAttr.length) {
					self.$set(
						self.attribute.productSelect,
						"store_name",
						self.storeInfo.title
					);
					self.$set(self.attribute.productSelect, "image", self.storeInfo.image);
					self.$set(self.attribute.productSelect, "price", self.storeInfo.price);
					self.$set(self.attribute.productSelect, "quota", 0);
					self.$set(self.attribute.productSelect, "quota_show", 0);
					self.$set(self.attribute.productSelect, "product_stock", 0);
					self.$set(self.attribute.productSelect, "stock", 0);
					self.$set(self.attribute.productSelect, "unique", "");
					self.$set(self.attribute.productSelect, "cart_num", 0);
					self.$set(self, "attrValue", "");
					self.$set(self, "attrTxt", "请选择");
				} else if (!productSelect && !productAttr.length) {
					self.$set(
						self.attribute.productSelect,
						"store_name",
						self.storeInfo.title
					);
					self.$set(self.attribute.productSelect, "image", self.storeInfo.image);
					self.$set(self.attribute.productSelect, "price", self.storeInfo.price);
					self.$set(self.attribute.productSelect, "stock", self.storeInfo.stock);
					self.$set(self.attribute.productSelect, "quota", self.storeInfo.quota);
					self.$set(self.attribute.productSelect, "product_stock", self.storeInfo.product_stock);
					self.$set(
						self.attribute.productSelect,
						"unique",
						self.storeInfo.unique || ""
					);
					self.$set(self.attribute.productSelect, "cart_num", 1);
					self.$set(self.attribute.productSelect, "quota", productSelect.quota);
					self.$set(self.attribute.productSelect, "product_stock", productSelect.product_stock);
					self.$set(self, "attrValue", "");
					self.$set(self, "attrTxt", "请选择");
				}
			},
			selecAttr: function() {
				this.attribute.cartAttr = true
			},
			onMyEvent: function() {
				this.$set(this.attribute, 'cartAttr', false);
				this.$set(this, 'isOpen', false);
			},
			/**
			 * 购物车数量加和数量减
			 * 
			 */
			ChangeCartNum: function(changeValue) {
				//changeValue:是否 加|减
				//获取当前变动属性
				let productSelect = this.productValue[this.attrValue];
				if (this.cart_num) {
					productSelect.cart_num = this.cart_num;
					this.attribute.productSelect.cart_num = this.cart_num;
				}
				//如果没有属性,赋值给商品默认库存
				if (productSelect === undefined && !this.attribute.productAttr.length)
					productSelect = this.attribute.productSelect;
				//无属性值即库存为0；不存在加减；
				if (productSelect === undefined) return;
				let stock = productSelect.stock || 0;
				let quotaShow = productSelect.quota_show || 0;
				let quota = productSelect.quota || 0;
				let productStock = productSelect.product_stock || 0;
				let num = this.attribute.productSelect;
				let nums = this.storeInfo.num || 0;
				//设置默认数据
				if (productSelect.cart_num == undefined) productSelect.cart_num = 1;
				if (changeValue) {
					num.cart_num++;
					let arrMin = [];
					arrMin.push(nums);
					arrMin.push(quota);
					arrMin.push(productStock);
					let minN = Math.min.apply(null, arrMin);
					if (num.cart_num >= minN) {
						this.$set(this.attribute.productSelect, "cart_num", minN ? minN : 1);
						this.$set(this, "cart_num", minN ? minN : 1);
					}
					// if(quotaShow >= productStock){
					// 	 if (num.cart_num > productStock) {
					// 	 	this.$set(this.attribute.productSelect, "cart_num", productStock);
					// 	 	this.$set(this, "cart_num", productStock);
					// 	 }
					// }else{
					// 	if (num.cart_num > quotaShow) {
					// 		this.$set(this.attribute.productSelect, "cart_num", quotaShow);
					// 		this.$set(this, "cart_num", quotaShow);
					// 	}
					// }
					this.$set(this, "cart_num", num.cart_num);
					this.$set(this.attribute.productSelect, "cart_num", num.cart_num);

				} else {
					num.cart_num--;
					if (num.cart_num < 1) {
						this.$set(this.attribute.productSelect, "cart_num", 1);
						this.$set(this, "cart_num", 1);
					}
					this.$set(this, "cart_num", num.cart_num);
					this.$set(this.attribute.productSelect, "cart_num", num.cart_num);
				}
			},
			attrVal(val) {
				this.attribute.productAttr[val.indexw].index = this.attribute.productAttr[val.indexw].attr_values[val.indexn];
			},
			/**
			 * 属性变动赋值
			 * 
			 */
			ChangeAttr: function(res) {
				this.$set(this, 'cart_num', 1);
				let productSelect = this.productValue[res];
				if (productSelect) {
					this.$set(this.attribute.productSelect, "image", productSelect.image);
					this.$set(this.attribute.productSelect, "price", productSelect.price);
					this.$set(this.attribute.productSelect, "stock", productSelect.stock);
					this.$set(this.attribute.productSelect, "unique", productSelect.unique);
					this.$set(this.attribute.productSelect, "cart_num", 1);
					this.$set(this.attribute.productSelect, "quota", productSelect.quota);
					this.$set(this.attribute.productSelect, "quota_show", productSelect.quota_show);
					this.$set(this, "attrValue", res);

					this.attrTxt = "已选择"
				} else {
					this.$set(this.attribute.productSelect, "image", this.storeInfo.image);
					this.$set(this.attribute.productSelect, "price", this.storeInfo.price);
					this.$set(this.attribute.productSelect, "stock", 0);
					this.$set(this.attribute.productSelect, "unique", "");
					this.$set(this.attribute.productSelect, "cart_num", 0);
					this.$set(this.attribute.productSelect, "quota", 0);
					this.$set(this.attribute.productSelect, "quota_show", 0);
					this.$set(this, "attrValue", "");
					this.attrTxt = "已选择"

				}
			},
			scroll: function(e) {
				var that = this,
					scrollY = e.detail.scrollTop;
				var opacity = scrollY / 200;
				opacity = opacity > 1 ? 1 : opacity;
				that.opacity = opacity
				that.scrollY = scrollY
				if (that.lock) {
					that.lock = false
					return;
				}
				for (var i = 0; i < that.topArr.length; i++) {
					if (scrollY < that.topArr[i] - (app.globalData.navHeight / 2) + that.heightArr[i]) {
						that.navActive = i
						break
					}
				}
			},
			tap: function(item, index) {
				var id = item.id;
				var index = index;
				var that = this;
				// if (!this.data.good_list.length && id == "past2") {
				//   id = "past3"
				// }
				this.toView = id;
				this.navActive = index;
				this.lock = true;
				this.scrollTop = index > 0 ? that.topArr[index] - (app.globalData.navHeight / 2) : that.topArr[index]
			},
			infoScroll: function() {
				var that = this,
					topArr = [],
					heightArr = [];
				for (var i = 0; i < that.navList.length; i++) { //productList
					//获取元素所在位置
					var query = wx.createSelectorQuery().in(this);
					var idView = "#past" + i;
					// if (!that.data.good_list.length && i == 2) {
					//   var idView = "#past" + 3;
					// }
					query.select(idView).boundingClientRect();
					query.exec(function(res) {
						var top = res[0].top;
						var height = res[0].height;
						topArr.push(top);
						heightArr.push(height);
						that.topArr = topArr
						that.heightArr = heightArr
					});
				};
			},
			/**
			 * 收藏商品
			 */
			setCollect: function() {
				var that = this;
				if (this.storeInfo.userCollect) {
					collectDel(this.storeInfo.product_id).then(res => {
						that.storeInfo.userCollect = !that.storeInfo.userCollect
					})
				} else {
					collectAdd(this.storeInfo.product_id).then(res => {
						that.storeInfo.userCollect = !that.storeInfo.userCollect
					})
				}
			},
			/*
			 *  单独购买
			 */
			openAlone: function() {
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${this.storeInfo.product_id}`
				})
			},
			/*
			 *  下订单
			 */
			goCat: function() {
				var that = this;
				var productSelect = this.productValue[this.attrValue];
				//打开属性
				if (this.isOpen)
					this.attribute.cartAttr = true
				else
					this.attribute.cartAttr = !this.attribute.cartAttr
				//只有关闭属性弹窗时进行加入购物车
				if (this.attribute.cartAttr === true && this.isOpen == false) return this.isOpen = true
				//如果有属性,没有选择,提示用户选择
				if (this.attribute.productAttr.length && productSelect === undefined && this.isOpen == true) return app.$util.Tips({
					title: '请选择属性'
				});
				postCartAdd({
					productId: that.storeInfo.product_id,
					secKillId: that.id,
					bargainId: 0,
					combinationId: 0,
					cartNum: that.cart_num,
					uniqueId: productSelect !== undefined ? productSelect.unique : '',
					'new': 1
				}).then(res => {
					this.isOpen = false
					uni.navigateTo({
						url: '/pages/users/order_confirm/index?new=1&cartId=' + res.data.cartId
					});
				}).catch(err => {
					return this.$util.Tips({
						title: err
					});
				});
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
				this.posterImageStatus = false
			},
			//替换安全域名
			setDomain: function(url) {
				url = url ? url.toString() : '';
				//本地调试打开,生产请注销
				if (url.indexOf("https://") > -1) return url;
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
					},
				});
			},
			/**
			 * 获取产品分销二维码
			 * @param function successFn 下载完成回调
			 * 
			 */
			downloadFilePromotionCode: function(successFn) {
				let that = this;
				seckillCode(that.id, {
					stop_time: that.datatime
				}).then(res => {
					uni.downloadFile({
						url: that.setDomain(res.data.code),
						success: function(res) {
							that.$set(that, 'isDown', false);
							if (typeof successFn == 'function')
								successFn && successFn(res.tempFilePath);
							else
								that.$set(that, 'PromotionCode', res.tempFilePath);
						},
						fail: function() {
							that.$set(that, 'isDown', false);
							that.$set(that, 'PromotionCode', '');
						},
					});
				}).catch(err => {
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
				if (that.isDown) return that.$util.Tips({
					title: '正在下载海报,请稍后再试！'
				});
				uni.getImageInfo({
					src: that.PromotionCode,
					fail: function(res) {
						return that.$util.Tips({
							title: '小程序二维码需要发布正式版后才能获取到'
						});
					},
					success() {
						if (arr2[2] == '') {
							//海报二维码不存在则从新下载
							that.downloadFilePromotionCode(function(msgPromotionCode) {
								arr2[2] = msgPromotionCode;
								if (arr2[2] == '')
									return that.$util.Tips({
										title: '海报二维码生成失败！'
									});
								that.$util.PosterCanvas(arr2, that.storeInfo.title, that.storeInfo.price,that.storeInfo.ot_price, function(tempFilePath) {
									that.$set(that, 'posterImage', tempFilePath);
									that.$set(that, 'posterImageStatus', true);
									that.$set(that, 'canvasStatus', false);
									that.$set(that, 'actionSheetHidden', !that.actionSheetHidden);
								});
							});
			
						} else {
							//生成推广海报
							that.$util.PosterCanvas(arr2, that.storeInfo.title, that.storeInfo.price,that.storeInfo.ot_price, function(tempFilePath) {
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
												title: '保存成功',
												icon: 'success'
											});
										},
										fail: function(res) {
											that.$util.Tips({
												title: '保存失败'
											});
										}
									})
								}
							})
						} else {
							uni.saveImageToPhotosAlbum({
								filePath: that.posterImage,
								success: function(res) {
									that.posterImageClose();
									that.$util.Tips({
										title: '保存成功',
										icon: 'success'
									});
								},
								fail: function(res) {
									that.$util.Tips({
										title: '保存失败'
									});
								},
							})
						}
					}
				})
			},
			// #endif
			setShareInfoStatus: function() {
				let data = this.storeInfo;
				let href = location.href;
				if (this.$wechat.isWeixin()) {
					this.posters = true;
					getUserInfo().then(res => {
						href =
							href.indexOf("?") === -1 ?
							href + "?spread=" + res.data.uid :
							href + "&spread=" + res.data.uid;

						let configAppMessage = {
							desc: data.store_info,
							title: data.store_name,
							link: href,
							imgUrl: data.image
						};
						this.$wechat.wechatEvevt(["updateAppMessageShareData", "updateTimelineShareData"], configAppMessage)
					});
				}
			}
		},
		//#ifdef MP
		onShareAppMessage() {
			return {
				title: this.storeInfo.title,
				path: app.globalData.openPages,
				imageUrl: this.storeInfo.image
			};
		}
		//#endif
	}
</script>

<style lang="scss">
	.generate-posters {
		width: 100%;
		height: 170rpx;
		background-color: #fff;
		position: fixed;
		left: 0;
		bottom: 0;
		z-index: 300;
		transform: translate3d(0, 100%, 0);
		transition: all 0.3s cubic-bezier(0.25, 0.5, 0.5, 0.9);
		border-top: 1rpx solid #eee;
	}

	.generate-posters.on {
		transform: translate3d(0, 0, 0);
	}

	.generate-posters .item {
		flex: 50%;
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

	.navbar .header {
		height: 96rpx;
		font-size: 30rpx;
		color: #050505;
		background-color: #fff;
		/* #ifdef MP */
		padding-right: 95rpx;
		/* #endif */
	}

	.icon-xiangzuo {
		/* #ifdef H5 */
		top: 30rpx !important;
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
		content: "";
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
	}

	.icon-xiangzuo {
		/* color: #000;
		position: fixed;
		font-size: 40rpx;
		width: 100rpx;
		height: 56rpx;
		line-height: 54rpx;
		z-index: 1000;
		left: 33rpx; */
	}

	.product-con .nav {
		background-image: url('data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAAA8AAD/4QN/aHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjYtYzE0MiA3OS4xNjA5MjQsIDIwMTcvMDcvMTMtMDE6MDY6MzkgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9InhtcC5kaWQ6NDNlZTU0ZDMtNjEwZS03ZjQ4LWEwODgtNTZlMTZiNzI3NTQwIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjI1NEI2MDUyM0ZDMjExRTk5OTg1REI1OUM1NjNEMUZCIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjI1NEI2MDUxM0ZDMjExRTk5OTg1REI1OUM1NjNEMUZCIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE4IChXaW5kb3dzKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjUyMDIzNWNmLTIwNGYtOTQ0My05YTBiLWNmMmZlMTJmMDk3NCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo0M2VlNTRkMy02MTBlLTdmNDgtYTA4OC01NmUxNmI3Mjc1NDAiLz4gPC9yZGY6RGVzY3JpcHRpb24+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');
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 100%;
		height: 100rpx;
		padding: 0 30rpx;
		box-sizing: border-box;
	}

	.product-con .nav .money {
		font-size: 28rpx;
		color: #fff;
	}

	.product-con .nav .money .num {
		font-size: 48rpx;
	}

	.product-con .nav .money .y-money {
		font-size: 26rpx;
		margin-left: 10rpx;
		text-decoration: line-through;
	}

	.product-con .nav .timeItem {
		font-size: 20rpx;
		color: #fff;
		text-align: center;
	}

	.product-con .nav .timeItem .timeCon {
		margin-top: 10rpx;
	}

	.product-con .nav .timeItem .timeCon .num {
		padding: 0 7rpx;
		font-size: 22rpx;
		color: #ff3d3d;
		background-color: #fff;
		border-radius: 2rpx;
	}

	.product-con .nav .timeState {
		font-size: 28RPX;
		color: #FFF;
	}

	.product-con .nav .iconfont {
		color: #fff;
		font-size: 30rpx;
		margin-left: 20rpx;
	}

	.product-con .wrapper {
		padding: 32rpx;
		width: 100%;
		box-sizing: border-box;
	}

	.product-con .wrapper .introduce {
		margin: 0;
	}

	.product-con .wrapper .introduce .infor {
		width: 570rpx;
	}

	.product-con .wrapper .introduce .iconfont {
		font-size: 37rpx;
		color: #515151;
	}

	.product-con .wrapper .label {
		margin: 18rpx 0 0 0;
		font-size: 24rpx;
		color: #82848f;
	}

	.product-con .wrapper .label .stock {
		width: 255rpx;
		margin-right: 28rpx;
	}

	.product-con .footer {
		padding: 0 20rpx 0 30rpx;
		position: fixed;
		bottom: 0;
		width: 100%;
		box-sizing: border-box;
		background-color: #fff;
		z-index: 277;
		border-top: 1rpx solid #f0f0f0;
		height: 100rpx;
		height: calc(100rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		height: calc(100rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
	}

	.product-con .footer .item {
		font-size: 18rpx;
		color: #666;
	}

	.product-con .footer .item .iconfont {
		text-align: center;
		font-size: 40rpx;
	}

	.product-con .footer .item .iconfont.icon-shoucang1 {
		color: #f00;
	}

	.product-con .footer .item .iconfont.icon-gouwuche1 {
		font-size: 40rpx;
		position: relative;
	}

	.product-con .footer .item .iconfont.icon-gouwuche1 .num {
		color: #fff;
		position: absolute;
		font-size: 18rpx;
		padding: 2rpx 8rpx 3rpx;
		border-radius: 200rpx;
		top: -10rpx;
		right: -10rpx;
	}

	.product-con .footer .bnt {
		width: 540rpx;
		height: 76rpx;
	}

	.product-con .footer .bnt .bnts {
		width: 270rpx;
		text-align: center;
		line-height: 76rpx;
		color: #fff;
		font-size: 28rpx;
	}

	.product-con .footer .bnt .joinCart {
		border-radius: 50rpx 0 0 50rpx;
		background-image: linear-gradient(to right, #fea10f 0%, #fa8013 100%);
	}

	.product-con .footer .bnt .buy {
		border-radius: 0 50rpx 50rpx 0;
		background-image: linear-gradient(to right, #fa6514 0%, #e93323 100%);
	}

	.setCollectBox {
		font-size: 18rpx;
		color: #666;
	}

	.bg-color-hui {
		background: #bbbbbb !important;
	}

	.canvas {
		width: 750px;
		height: 1190px;
	}

	.poster-pop {
		width: 450rpx;
		height: 714rpx;
		position: fixed;
		left: 50%;
		transform: translateX(-50%);
		z-index: 300;
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

	/deep/.mask {
		z-index: 99!important;
	}
	
	.mask1{
		    position: fixed;
		    top: 0;
		    left: 0;
		    right: 0;
		    bottom: 0;
		    background-color: #000;
		    opacity: .5;
			z-index: 288;
	}
	
	.home-nav {
		/* #ifdef H5 */
		top: 20rpx !important;
		/* #endif */
	}
	
	.home-nav {
		color: #fff;
		position: fixed;
		font-size: 33rpx;
		width: 56rpx;
		height: 56rpx;
		z-index: 99;
		left: 33rpx;
		background: rgba(190, 190, 190, 0.5);
		border-radius: 50%;
		&.on{
			background: unset;
			color: #333;
		}
	}
	
	.home-nav .line {
		width: 1rpx;
		height: 24rpx;
		background: rgba(255, 255, 255, 0.25);
	}
	
	.home-nav .icon-xiangzuo {
		width: auto;
		font-size: 28rpx;
	}
	
	.share-box {
		z-index: 1000;
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
	}
	
	.share-box image {
		width: 100%;
		height: 100%;
	}
</style>
