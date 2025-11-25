<template>
	<view :style="colorStyle">
		<!-- 头部 -->

		<!-- #ifndef APP-PLUS -->
		<view class="navbar" :style="{ height: navH + 'rpx', opacity: opacity }">
			<view class="navbarH" :style="'height:' + navH + 'rpx;'">
				<view class="navbarCon acea-row row-center-wrapper">
					<view class="header acea-row row-center-wrapper">
						<view class="item" :class="navActive === index ? 'on' : ''" v-for="(item, index) in navList" :key="index" @tap="tap(item, index)">
							{{ item }}
						</view>
					</view>
				</view>
			</view>
		</view>
		<view id="home" class="home-nav acea-row row-center-wrapper" :class="[opacity > 0.5 ? 'on' : '']" :style="{ top: homeTop + 'rpx' }">
			<view class="iconfont icon-fanhui2" @tap="returns"></view>
			<!-- #ifdef MP -->
			<view class="line"></view>
			<view class="iconfont icon-gengduo5" @click="moreNav"></view>
			<!-- #endif -->
		</view>
		<!-- #endif -->

		<!-- #ifdef H5 -->
		<view id="home" class="home-nav right acea-row row-center-wrapper" :class="[opacity > 0.5 ? 'on' : '']" :style="{ top: homeTop + 'rpx' }">
			<!-- #ifdef  H5 -->
			<view class="iconfont icon-gengduo2" @click="moreNav"></view>
		</view>
		<!-- #endif -->
		<!-- #endif -->
		<homeList :navH="navH" :returnShow="returnShow" :currentPage="currentPage" :sysHeight="sysHeight"></homeList>
		<view class="product-con">
			<scroll-view :scroll-top="scrollTop" scroll-y="true" scroll-with-animation="true" :style="'height:' + height + 'px;'" @scroll="scroll">
				<view id="past0">
					<!-- #ifdef APP-PLUS || MP -->
					<view class="" :style="'width:100%;' + 'height:' + sysHeight"></view>
					<!-- #endif -->
					<productConSwiper :imgUrls="imgUrls" @showSwiperImg="showSwiperImg"></productConSwiper>
					<view class="bg-color">
						<view class="nav acea-row row-between-wrapper">
							<view class="money">
								{{ $t(`￥`) }}
								<text class="num">{{ storeInfo.price || '' }}</text>
								<text v-if="attribute.productAttr.length && (attribute.productAttr.length ? attribute.productAttr[0].attr_values.length : 0) > 1">{{ $t(`起`) }}</text>
								<text class="y-money">{{ $t(`￥`) }}{{ storeInfo.product_price || '' }}</text>
							</view>
							<view class="acea-row row-middle">
								<view class="timeItem" v-if="status == 1">
									<view>{{ $t(`距秒杀结束仅剩`) }}</view>
									<countDown
										:is-day="false"
										:tip-text="' '"
										:day-text="' '"
										:hour-text="' : '"
										:minute-text="' : '"
										:second-text="' '"
										:datatime="datatime"
										style="margin-top: 4rpx"
									></countDown>
								</view>
								<!-- <view class="timeState" wx:if="{{status == 0}}">已结束</view>
						  <view class="timeState" wx:if="{{status == 2}}">即将开始</view> -->
								<!-- <view class='iconfont icon-jiantou'></view> -->
							</view>
						</view>
					</view>
					<view class="wrapper">
						<view class="introduce acea-row row-between">
							<view class="infor">{{ storeInfo.title || '' }}</view>
							<!-- <button class='iconfont icon-fenxiang' open-type='share'></button> -->
							<view class="iconfont icon-fenxiang" @click="listenerActionSheet"></view>
						</view>
						<view class="label acea-row row-middle">
							<!-- <view class='stock'>库存：{{storeInfo.stock}}{{storeInfo.unit_name}}</view> -->
							<view class="stock">{{ $t(`累计销售`) }}：{{ storeInfo.total ? storeInfo.total : 0 }}{{ $t(storeInfo.unit_name) || '' }}</view>
							<view>{{ $t(`限量剩余`) }}: {{ storeInfo.quota ? storeInfo.quota : 0 }}{{ $t(storeInfo.unit_name) || '' }}</view>
						</view>
					</view>
					<view class="attribute acea-row row-between-wrapper" @tap="selecAttr" v-if="attribute.productAttr.length">
						<!-- 	<view>{{attr}}：<text class='atterTxt'>{{attrValue}}</text></view>
						<view class='iconfont icon-jiantou'></view> -->
						<view class="flex">
							<view style="display: flex; align-items: center; width: 90%">
								<view class="attr-txt">{{ attr }}：</view>
								<view class="atterTxt line1" style="width: 82%">{{ attrValue }}</view>
							</view>
							<view class="iconfont icon-jiantou"></view>
						</view>
						<view class="acea-row row-between-wrapper" style="margin-top: 7px; padding-left: 70px" v-if="skuArr.length > 1">
							<view class="flexs">
								<image :src="item.image" v-for="(item, index) in skuArr.slice(0, 4)" :key="index" class="attrImg"></image>
							</view>
							<view class="switchTxt">{{ $t(`共`) }}{{ skuArr.length }}{{ $t(`种规格可选`) }}</view>
						</view>
					</view>
				</view>
				<view class="userEvaluation" id="past1" v-if="replyCount">
					<view class="title acea-row row-between-wrapper">
						<view>{{ $t(`用户评价`) }}({{ replyCount }})</view>
						<navigator class="praise" hover-class="none" :url="'/pages/goods/goods_comment_list/index?product_id=' + storeInfo.product_id">
							<text class="font-color">{{ replyChance }}%</text>
							{{ $t(`好评率`) }}
							<text class="iconfont icon-jiantou"></text>
						</navigator>
					</view>
					<userEvaluation :reply="reply"></userEvaluation>
				</view>
				<view class="product-intro" id="past2">
					<view class="title">{{ $t(`产品介绍`) }}</view>
					<view class="conter">
						<!-- <view class="" v-html="storeInfo.description">
						</view> -->

						<!-- #ifndef APP-PLUS -->
						<parser :html="storeInfo.description" ref="article" :tag-style="tagStyle"></parser>
						<!-- #endif -->
						<!-- #ifdef APP-PLUS -->
						<view class="description" v-html="storeInfo.description"></view>
						<!-- #endif -->
					</view>
				</view>
				<view class="uni-p-b-98"></view>
			</scroll-view>
			<view class="footer acea-row row-between-wrapper" :class="{ eject: storeInfo.id }">
				<navigator hover-class="none" open-type="switchTab" class="item" url="/pages/index/index">
					<view class="iconfont icon-shouye6"></view>
					<view class="p_center">{{ $t(`首页`) }}</view>
				</navigator>
				<view @tap="setCollect" class="item">
					<view class="iconfont icon-shoucang1" v-if="storeInfo.userCollect"></view>
					<view class="iconfont icon-shoucang" v-else></view>
					<view class="p_center">{{ $t(`收藏`) }}</view>
				</view>
				<view class="bnt acea-row" v-if="status == 1 && attribute.productSelect.quota > 0 && attribute.productSelect.product_stock > 0">
					<view class="joinCart bnts" @tap="openAlone" v-if="storeInfo.product_is_show">{{ $t(`单独购买`) }}</view>
					<view class="buy bnts" :class="!storeInfo.product_is_show ? 'long-btn' : ''" @tap="goCat">{{ $t(`立即购买`) }}</view>
				</view>
				<view
					class="bnt acea-row"
					v-if="
						(status == 1 && attribute.productSelect.quota <= 0) ||
						(status == 3 && attribute.productSelect.quota <= 0) ||
						(status == 1 && attribute.productSelect.product_stock <= 0) ||
						(status == 3 && attribute.productSelect.product_stock <= 0)
					"
				>
					<view class="joinCart bnts" @tap="openAlone" v-if="storeInfo.product_is_show">{{ $t(`单独购买`) }}</view>
					<view class="buy bnts bg-color-hui" :class="!storeInfo.product_is_show ? 'long-btn' : ''">{{ $t(`已售罄`) }}</view>
				</view>
				<view class="bnt acea-row" v-if="!dataShow && status == 1">
					<view class="joinCart bnts" @tap="openAlone" v-if="storeInfo.product_is_show">{{ $t(`单独购买`) }}</view>
					<view class="buy bnts bg-color-hui" :class="!storeInfo.product_is_show ? 'long-btn' : ''">{{ $t(`立即购买`) }}</view>
				</view>
				<view class="bnt acea-row" v-if="status == 2">
					<view class="joinCart bnts" @tap="openAlone" v-if="storeInfo.product_is_show">{{ $t(`单独购买`) }}</view>
					<view class="buy bnts bg-color-hui" :class="!storeInfo.product_is_show ? 'long-btn' : ''">{{ $t(`未开始`) }}</view>
				</view>
				<view class="bnt acea-row" v-if="status == 0">
					<view class="joinCart bnts" @tap="openAlone" v-if="storeInfo.product_is_show">{{ $t(`单独购买`) }}</view>
					<view class="buy bnts bg-color-hui" :class="!storeInfo.product_is_show ? 'long-btn' : ''">{{ $t(`已结束`) }}</view>
				</view>
			</view>
		</view>
		<cus-previewImg ref="cusPreviewImg" :list="skuArr" @changeSwitch="changeSwitch" @shareFriend="listenerActionSheet" />
		<product-window
			:attr="attribute"
			:limitNum="1"
			@myevent="onMyEvent"
			@ChangeAttr="ChangeAttr"
			:type="'seckill'"
			@ChangeCartNum="ChangeCartNum"
			@attrVal="attrVal"
			@iptCartNum="iptCartNum"
			@getImg="showImg"
		></product-window>

		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth"></authorize> -->
		<!-- #endif -->
		<!-- 分享按钮 -->
		<view class="generate-posters acea-row row-middle" :class="posters ? 'on' : ''">
			<!-- #ifndef MP -->
			<button class="item" hover-class="none" v-if="weixinStatus === true" @click="H5ShareBox = true">
				<!-- <button class="item" hover-class='none' v-if="weixinStatus === true" @click="setShareInfoStatus"> -->
				<view class="iconfont icon-weixin3"></view>
				<view class="">{{ $t(`发送给朋友`) }}</view>
			</button>
			<!-- #endif -->
			<!-- #ifdef MP -->
			<button class="item" open-type="share" hover-class="none" @click="goFriend">
				<view class="iconfont icon-weixin3"></view>
				<view class="">{{ $t(`发送给朋友`) }}</view>
			</button>
			<!-- #endif -->
			<!-- #ifdef APP-PLUS -->
			<view class="item" @click="appShare('WXSceneSession')">
				<view class="iconfont icon-weixin3"></view>
				<view class="">{{ $t(`微信好友`) }}</view>
			</view>
			<view class="item" @click="appShare('WXSenceTimeline')">
				<view class="iconfont icon-pengyouquan"></view>
				<view class="">{{ $t(`微信朋友圈`) }}</view>
			</view>
			<!-- #endif -->
			<button class="item" hover-class="none" @tap="goPoster('seckill')">
				<view class="iconfont icon-haibao"></view>
				<view class="">{{ $t(`生成海报`) }}</view>
			</button>
		</view>
		<view class="mask" v-if="posters" @click="listenerActionClose"></view>

		<!-- 海报展示 -->
		<view class="poster-pop" v-if="posterImageStatus">
			<image src="/static/images/poster-close.png" class="close" @click="posterImageClose"></image>
			<image class="poster-img" :src="posterImage"></image>
			<!-- #ifndef H5  -->
			<view class="save-poster" @click="savePosterPath">{{ $t(`保存到手机`) }}</view>
			<!-- #endif -->
			<!-- #ifdef H5 -->
			<view class="keep">{{ $t(`长按图片可以保存到手机`) }}</view>
			<!-- #endif -->
		</view>
		<view class="mask1" v-if="posterImageStatus"></view>
		<canvas class="canvas" canvas-id="myCanvas" v-if="canvasStatus"></canvas>
		<kefuIcon :ids="storeInfo.product_id" :routineContact="routineContact"></kefuIcon>
		<!-- 发送给朋友图片 -->
		<view class="share-box" v-if="H5ShareBox">
			<image :src="imgHost + '/statics/images/share-info.png'" @click="H5ShareBox = false"></image>
		</view>
		<swiperPrevie ref="cusSwiperImg" :list="storeInfo.images"></swiperPrevie>
		<!-- #ifdef H5 || APP-PLUS -->
		<zb-code
			ref="qrcode"
			:show="codeShow"
			:cid="cid"
			:val="codeVal"
			:size="size"
			:unit="unit"
			:background="background"
			:foreground="foreground"
			:pdground="pdground"
			:icon="codeIcon"
			:iconSize="iconsize"
			:onval="onval"
			:loadMake="loadMake"
			@result="qrR"
		/>
		<!-- #endif -->
	</view>
</template>

<script>
const app = getApp();
import { mapGetters } from 'vuex';
import { getSeckillDetail, seckillCode } from '@/api/activity.js';
import { postCartAdd, collectAdd, collectDel } from '@/api/store.js';
import productConSwiper from '@/components/productConSwiper/index.vue';
import swiperPrevie from '@/components/cusPreviewImg/swiperPrevie.vue';
import productWindow from '@/components/productWindow/index.vue';
import userEvaluation from '@/components/userEvaluation/index.vue';
import kefuIcon from '@/components/kefuIcon';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
import countDown from '@/components/countDown';
import { imageBase64 } from '@/api/public';
import { toLogin } from '@/libs/login.js';
import { getUserInfo } from '@/api/user.js';
// #ifdef APP-PLUS
import { TOKENNAME } from '@/config/app.js';
// #endif
import colors from '@/mixins/color.js';
import menuIcon from '@/components/menuIcon.vue';
import parser from '@/components/jyf-parser/jyf-parser';
import cusPreviewImg from '@/components/cusPreviewImg/index.vue';
import { sharePoster } from '@/mixins/sharePoster';
import { HTTP_REQUEST_URL } from '@/config/app';
import homeList from '@/components/homeList';
let sysHeight = uni.getSystemInfoSync().statusBarHeight + 'px';

export default {
	computed: mapGetters(['isLogin']),
	mixins: [colors, sharePoster],
	components: {
		productConSwiper,
		productWindow: productWindow,
		userEvaluation,
		kefuIcon,
		menuIcon,
		countDown,
		cusPreviewImg,
		swiperPrevie,
		parser,
		homeList,
		// #ifdef MP
		authorize
		// #endif
	},
	data() {
		return {
			imgHost: HTTP_REQUEST_URL,
			showMenuIcon: false,
			dataShow: 0,
			id: 0,
			time: 0,
			countDownHour: '00',
			countDownMinute: '00',
			countDownSecond: '00',
			storeInfo: [],
			imgUrls: [],
			parameter: {
				navbar: '1',
				return: '1',
				title: this.$t(`抢购详情页`),
				color: false
			},
			attribute: {
				cartAttr: false,
				productAttr: [],
				productSelect: {}
			},
			productValue: [],
			isOpen: false,
			attr: this.$t(`请选择`),
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
			navH: '',
			navList: [this.$t(`商品`), this.$t(`评价`), this.$t(`详情`)],
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
			datatime: 0,
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
			routineContact: 0,
			skuArr: [],
			selectSku: {},
			currentPage: false,
			sysHeight: sysHeight,
			time_id: 0
		};
	},

	computed: mapGetters(['isLogin']),
	watch: {
		isLogin: {
			handler: function (newV, oldV) {
				if (newV) {
					this.getSeckillDetail();
				}
			},
			deep: true
		}
	},
	onLoad(options) {
		let that = this;
		let statusBarHeight = '';
		var pages = getCurrentPages();
		if (options.id) {
			this.id = options.id;
			//记录推广人uid
			if (options.pid) app.globalData.spid = options.pid;
			if (options.time_id) this.time_id = options.time_id;
			// if (options.time) this.datatime = Number(options.time);
		}
		that.returnShow = pages.length === 1 ? false : true;

		//设置商品列表高度
		uni.getSystemInfo({
			success: function (res) {
				that.height = res.windowHeight;
				statusBarHeight = res.statusBarHeight;
				//res.windowHeight:获取整个窗口高度为px，*2为rpx；98为头部占据的高度；
			}
		});
		// #ifdef MP
		this.navH = app.globalData.navHeight;
		// #endif
		// #ifdef H5
		that.navH = 96;
		// #endif
		// #ifdef APP-PLUS
		that.navH = 30;
		// #endif
		// #ifdef MP
		let menuButtonInfo = uni.getMenuButtonBoundingClientRect();
		this.meunHeight = menuButtonInfo.height;
		this.backH = that.navH / 2 + this.meunHeight / 2;

		//扫码携带参数处理
		if (options.scene) {
			let value = this.$util.getUrlParams(decodeURIComponent(options.scene));
			if (value.id) {
				this.id = value.id;
			} else {
				return this.$util.Tips(
					{
						title: this.$t(`缺少参数无法查看商品`)
					},
					{
						tab: 3,
						url: 1
					}
				);
			}
			//记录推广人uid
			if (value.pid) app.globalData.spid = value.pid;
			if (value.time_id) this.time_id = value.time_id;
			// if (value.time) this.datatime = value.time
		}
		// #endif
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
				.boundingClientRect((data) => {
					this.homeTop = menuButton.top * 2 + menuButton.height - data.height;
				})
				.exec();
			// #endif
		});
	},
	onNavigationBarButtonTap(e) {
		this.currentPage = !this.currentPage;
	},
	methods: {
		moreNav() {
			this.currentPage = !this.currentPage;
		},
		// app分享
		// #ifdef APP-PLUS
		appShare(scene) {
			let that = this;
			let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
			let curRoute = routes[routes.length - 1].$page.fullPath; // 获取当前页面路由，也就是最后一个打开的页面路由
			uni.share({
				provider: 'weixin',
				scene: scene,
				type: 0,
				href: `${HTTP_REQUEST_URL}${curRoute}`,
				title: that.storeInfo.title,
				summary: that.storeInfo.info,
				imageUrl: that.storeInfo.small_image,
				success: function (res) {
					uni.showToast({
						title: this.$t(`分享成功`),
						icon: 'success'
					});
					that.posters = false;
				},
				fail: function (err) {
					uni.showToast({
						title: this.$t(`分享失败`),
						icon: 'none',
						duration: 2000
					});
					that.posters = false;
				}
			});
		},
		// #endif
		/**
		 * 购物车手动填写
		 *
		 */
		iptCartNum: function (e) {
			this.$set(this.attribute.productSelect, 'cart_num', e);
			this.$set(this, 'cart_num', e);
		},
		// 后退
		returns() {
			// #ifdef H5
			return history.back();
			// #endif
			// #ifndef H5
			return uni.navigateBack({
				delta: 1
			});
			// #endif
		},
		onLoadFun: function (data) {
			if (this.isAuto) {
				this.isAuto = false;
				this.isShowAuth = false;
				this.getSeckillDetail();
			}
		},
		getSeckillDetail: function () {
			let that = this;
			getSeckillDetail(that.id, { time_id: this.time_id })
				.then((res) => {
					this.dataShow = 1;
					this.status = res.data.storeInfo.status;
					let title = res.data.storeInfo.title;
					this.storeInfo = res.data.storeInfo;
					this.datatime = Number(res.data.storeInfo.last_time);
					this.imgUrls = res.data.storeInfo.images;
					this.storeInfo.description = this.storeInfo.description.replace(/<img/gi, '<img style="max-width:100%;height:auto;float:left;display:block" ');
					this.storeInfo.description = this.storeInfo.description.replace(/<video/gi, '<video style="width:100%;height:300px;display:block" ');
					// this.attribute.productAttr = res.data.productAttr;
					that.$set(that.attribute, 'productAttr', res.data.productAttr);
					this.productValue = res.data.productValue;
					this.attribute.productSelect.num = res.data.storeInfo.num;
					this.attribute.productSelect.once_num = res.data.storeInfo.once_num;
					this.replyCount = res.data.replyCount;
					this.reply = res.data.reply ? [res.data.reply] : [];
					this.replyChance = res.data.replyChance;
					that.routineContact = Number(res.data.routine_contact_type);
					uni.setNavigationBarTitle({
						title: title.substring(0, 7) + '...'
					});
					for (let key in res.data.productValue) {
						let obj = res.data.productValue[key];
						that.skuArr.push(obj);
					}
					this.$set(this, 'selectSku', that.skuArr[0]);
					var navList = [that.$t(`商品`), that.$t(`详情`)];
					if (res.data.replyCount) {
						navList.splice(1, 0, that.$t(`评价`));
					}
					that.$set(that, 'navList', navList);
					// #ifdef H5 || APP-PLUS
					// this.PromotionCode = res.data.storeInfo.code_base
					that.storeImage = that.storeInfo.image;
					that.getImageBase64();
					// #endif
					if (!this.storeInfo.wechat_code) {
						// #ifdef H5
						this.codeVal = window.location.origin + '/pages/activity/goods_seckill_details/index?id=' + that.id + '&spid=' + that.storeInfo.uid + '&time_id=' + that.time_id;
						// #endif
						// #ifdef APP-PLUS
						this.codeVal = HTTP_REQUEST_URL + '/pages/activity/goods_seckill_details/index?id=' + that.id + '&spid=' + that.storeInfo.uid + '&time_id=' + that.time_id;
						// #endif
					} else {
						that.$set(that, 'PromotionCode', this.storeInfo.wechat_code);
					}
					// #ifdef APP-PLUS
					uni.downloadFile({
						url: that.setDomain(res.data.storeInfo.wechat_code),
						success: function (res) {
							that.PromotionCode = res.tempFilePath;
						},
						fail: function () {
							return that.$util.Tips({
								title: that.$t(`二维码获取失败`)
							});
						}
					});

					that.downloadFilestoreImage();
					// #endif
					// #ifdef H5
					that.setShare();
					// #endif
					// #ifndef H5 || APP-PLUS
					that.downloadFilestoreImage();
					// that.downloadFilePromotionCode();
					// #endif
					that.DefaultSelect();
					setTimeout(() => {
						that.infoScroll();
					}, 500);
				})
				.catch((err) => {
					that.$util.Tips({
						title: err
					});
				});
		},
		/**
		 * 获取产品分销二维码
		 * @param function successFn 下载完成回调
		 *
		 */
		downloadFilePromotionCode: function (successFn) {
			let that = this;
			// #ifdef MP
			seckillCode(that.id, { time_id: that.time_id })
				.then((res) => {
					uni.downloadFile({
						url: that.setDomain(res.data.code),
						success: function (res) {
							that.$set(that, 'isDown', false);
							that.$set(that, 'PromotionCode', res.tempFilePath);
							if (typeof successFn == 'function') successFn && successFn(res.tempFilePath);
						},
						fail: function () {
							that.$set(that, 'isDown', false);
							that.$set(that, 'PromotionCode', '');
						}
					});
				})
				.catch((err) => {
					that.$set(that, 'isDown', false);
					that.$set(that, 'PromotionCode', '');
				});
			// #endif
			// #ifdef APP-PLUS
			uni.downloadFile({
				url: that.setDomain(that.PromotionCode),
				success: function (res) {
					that.$set(that, 'isDown', false);
					if (typeof successFn == 'function') successFn && successFn(res.tempFilePath);
					else that.$set(that, 'PromotionCode', res.tempFilePath);
				},
				fail: function () {
					that.$set(that, 'isDown', false);
					that.$set(that, 'PromotionCode', '');
				}
			});
			// #endif
		},
		setShare() {
			this.$wechat.isWeixin() &&
				this.$wechat
					.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareAppMessage', 'onMenuShareTimeline'], {
						desc: this.storeInfo.info,
						title: this.storeInfo.title,
						link: location.href,
						imgUrl: this.storeInfo.image
					})
					.then((res) => {})
					.catch((err) => {});
		},
		/**
		 * 默认选中属性
		 *
		 */
		DefaultSelect: function () {
			let self = this;
			let productAttr = self.attribute.productAttr;
			let value = [];
			for (var key in this.productValue) {
				if (this.productValue[key].quota > 0) {
					value = this.attribute.productAttr.length ? key.split(',') : [];
					break;
				}
			}
			for (let i = 0; i < productAttr.length; i++) {
				this.$set(productAttr[i], 'index', value[i]);
			}
			//sort();排序函数:数字-英文-汉字；
			let productSelect = this.productValue[value.join(',')];
			if (productSelect && productAttr.length) {
				self.$set(self.attribute.productSelect, 'store_name', self.storeInfo.title);
				self.$set(self.attribute.productSelect, 'image', productSelect.image);
				self.$set(self.attribute.productSelect, 'price', productSelect.price);
				self.$set(self.attribute.productSelect, 'stock', productSelect.stock);
				self.$set(self.attribute.productSelect, 'unique', productSelect.unique);
				self.$set(self.attribute.productSelect, 'quota', productSelect.quota);
				self.$set(self.attribute.productSelect, 'quota_show', productSelect.quota_show);
				self.$set(self.attribute.productSelect, 'product_stock', productSelect.product_stock);
				self.$set(self.attribute.productSelect, 'cart_num', 1);
				self.$set(self, 'attrValue', value.join(','));
				self.attrValue = value.join(',');
			} else if (!productSelect && productAttr.length) {
				self.$set(self.attribute.productSelect, 'store_name', self.storeInfo.title);
				self.$set(self.attribute.productSelect, 'image', self.storeInfo.image);
				self.$set(self.attribute.productSelect, 'price', self.storeInfo.price);
				self.$set(self.attribute.productSelect, 'quota', 0);
				self.$set(self.attribute.productSelect, 'quota_show', 0);
				self.$set(self.attribute.productSelect, 'product_stock', 0);
				self.$set(self.attribute.productSelect, 'stock', 0);
				self.$set(self.attribute.productSelect, 'unique', '');
				self.$set(self.attribute.productSelect, 'cart_num', 0);
				self.$set(self, 'attrValue', '');
				self.$set(self, 'attrTxt', this.$t(`请选择`));
			} else if (!productSelect && !productAttr.length) {
				self.$set(self.attribute.productSelect, 'store_name', self.storeInfo.title);
				self.$set(self.attribute.productSelect, 'image', self.storeInfo.image);
				self.$set(self.attribute.productSelect, 'price', self.storeInfo.price);
				self.$set(self.attribute.productSelect, 'stock', self.storeInfo.stock);
				self.$set(self.attribute.productSelect, 'quota', self.storeInfo.quota);
				self.$set(self.attribute.productSelect, 'product_stock', self.storeInfo.product_stock);
				self.$set(self.attribute.productSelect, 'unique', self.storeInfo.unique || '');
				self.$set(self.attribute.productSelect, 'cart_num', 1);
				self.$set(self.attribute.productSelect, 'quota', productSelect.quota);
				self.$set(self.attribute.productSelect, 'product_stock', productSelect.product_stock);
				self.$set(self, 'attrValue', '');
				self.$set(self, 'attrTxt', this.$t(`请选择`));
			}
		},
		selecAttr: function () {
			this.attribute.cartAttr = true;
		},
		onMyEvent: function () {
			this.$set(this.attribute, 'cartAttr', false);
			this.$set(this, 'isOpen', false);
		},
		/**
		 * 购物车数量加和数量减
		 *
		 */
		ChangeCartNum: function (changeValue) {
			//changeValue:是否 加|减
			//获取当前变动属性
			let productSelect = this.productValue[this.attrValue];
			if (this.cart_num) {
				productSelect.cart_num = this.cart_num;
				this.attribute.productSelect.cart_num = this.cart_num;
			}
			//如果没有属性,赋值给商品默认库存
			if (productSelect === undefined && !this.attribute.productAttr.length) productSelect = this.attribute.productSelect;
			//无属性值即库存为0；不存在加减；
			if (productSelect === undefined) return;
			let stock = productSelect.stock || 0;
			let quotaShow = productSelect.quota_show || 0;
			let quota = productSelect.quota || 0;
			let productStock = productSelect.product_stock || 0;
			let num = this.attribute.productSelect;
			let nums = this.storeInfo.num || 0;
			let onceNum = this.storeInfo.once_num || 0;
			//设置默认数据
			if (productSelect.cart_num == undefined) productSelect.cart_num = 1;
			if (changeValue) {
				num.cart_num++;
				let arrMin = [];
				arrMin.push(nums);
				arrMin.push(onceNum);
				arrMin.push(quota);
				arrMin.push(productStock);
				let minN = Math.min.apply(null, arrMin);
				if (num.cart_num >= minN) {
					this.$set(this.attribute.productSelect, 'cart_num', minN ? minN : 1);
					this.$set(this, 'cart_num', minN ? minN : 1);
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
				this.$set(this, 'cart_num', num.cart_num);
				this.$set(this.attribute.productSelect, 'cart_num', num.cart_num);
			} else {
				num.cart_num--;
				if (num.cart_num < 1) {
					this.$set(this.attribute.productSelect, 'cart_num', 1);
					this.$set(this, 'cart_num', 1);
				}
				this.$set(this, 'cart_num', num.cart_num);
				this.$set(this.attribute.productSelect, 'cart_num', num.cart_num);
			}
		},
		attrVal(val) {
			this.attribute.productAttr[val.indexw].index = this.attribute.productAttr[val.indexw].attr_values[val.indexn];
		},
		/**
		 * 属性变动赋值
		 *
		 */
		ChangeAttr: function (res) {
			this.$set(this, 'cart_num', 1);
			let productSelect = this.productValue[res];
			this.$set(this, 'selectSku', productSelect);
			if (productSelect) {
				this.$set(this.attribute.productSelect, 'image', productSelect.image);
				this.$set(this.attribute.productSelect, 'price', productSelect.price);
				this.$set(this.attribute.productSelect, 'stock', productSelect.stock);
				this.$set(this.attribute.productSelect, 'unique', productSelect.unique);
				this.$set(this.attribute.productSelect, 'cart_num', 1);
				this.$set(this.attribute.productSelect, 'quota', productSelect.quota);
				this.$set(this.attribute.productSelect, 'quota_show', productSelect.quota_show);
				this.$set(this, 'attrValue', res);

				this.attrTxt = this.$t(`已选择`);
			} else {
				this.$set(this.attribute.productSelect, 'image', this.storeInfo.image);
				this.$set(this.attribute.productSelect, 'price', this.storeInfo.price);
				this.$set(this.attribute.productSelect, 'stock', 0);
				this.$set(this.attribute.productSelect, 'unique', '');
				this.$set(this.attribute.productSelect, 'cart_num', 0);
				this.$set(this.attribute.productSelect, 'quota', 0);
				this.$set(this.attribute.productSelect, 'quota_show', 0);
				this.$set(this, 'attrValue', '');
				this.attrTxt = this.$t(`已选择`);
			}
		},
		scroll: function (e) {
			var that = this,
				scrollY = e.detail.scrollTop;
			var opacity = scrollY / 200;
			opacity = opacity > 1 ? 1 : opacity;
			that.opacity = opacity;
			that.scrollY = scrollY;
			that.currentPage = false;
			that.$set(that, 'showMenuIcon', false);
			if (that.lock) {
				that.lock = false;
				return;
			}
			for (var i = 0; i < that.topArr.length; i++) {
				if (scrollY < that.topArr[i] - app.globalData.navHeight / 2 + that.heightArr[i]) {
					that.navActive = i;
					break;
				}
			}
		},
		open(data) {
			this.showMenuIcon = data;
		},
		tap: function (item, index) {
			var id = item.id;
			var index = index;
			var that = this;
			if (!this.replyCount && id == 'past1') {
				id = 'past2';
			}
			this.toView = id;
			this.navActive = index;
			this.lock = true;
			this.scrollTop = index > 0 ? that.topArr[index] - app.globalData.navHeight / 2 : that.topArr[index];
		},
		infoScroll: function () {
			var that = this,
				topArr = [],
				heightArr = [];
			for (var i = 0; i < that.navList.length; i++) {
				//productList
				//获取元素所在位置
				var query = wx.createSelectorQuery().in(this);
				var idView = '#past' + i;
				if (!this.replyCount && i == 1) {
					idView = '#past' + 2;
				}
				query.select(idView).boundingClientRect();
				query.exec(function (res) {
					var top = res[0].top;
					var height = res[0].height;
					topArr.push(top);
					heightArr.push(height);
					that.topArr = topArr;
					that.heightArr = heightArr;
				});
			}
		},
		/**
		 * 收藏商品
		 */
		setCollect: function () {
			var that = this;
			if (this.storeInfo.userCollect) {
				collectDel([this.storeInfo.product_id]).then((res) => {
					that.storeInfo.userCollect = !that.storeInfo.userCollect;
				});
			} else {
				collectAdd(this.storeInfo.product_id).then((res) => {
					that.storeInfo.userCollect = !that.storeInfo.userCollect;
				});
			}
		},
		/*
		 *  单独购买
		 */
		openAlone: function () {
			uni.navigateTo({
				url: `/pages/goods_details/index?id=${this.storeInfo.product_id}`
			});
		},
		/*
		 *  下订单
		 */
		goCat: function () {
			var that = this;
			that.currentPage = false;
			var productSelect = this.productValue[this.attrValue];
			//打开属性
			if (this.isOpen) this.attribute.cartAttr = true;
			else this.attribute.cartAttr = !this.attribute.cartAttr;
			//只有关闭属性弹窗时进行加入购物车
			if (this.attribute.cartAttr === true && this.isOpen == false) return (this.isOpen = true);
			//如果有属性,没有选择,提示用户选择
			if (this.attribute.productAttr.length && productSelect === undefined && this.isOpen == true)
				return app.$util.Tips({
					title: that.$t(`请选择属性`)
				});
			postCartAdd({
				productId: that.storeInfo.product_id,
				secKillId: that.id,
				bargainId: 0,
				combinationId: 0,
				cartNum: that.cart_num,
				uniqueId: productSelect !== undefined ? productSelect.unique : '',
				new: 1
			})
				.then((res) => {
					this.isOpen = false;
					uni.navigateTo({
						url: '/pages/goods/order_confirm/index?new=1&cartId=' + res.data.cartId
					});
				})
				.catch((err) => {
					return this.$util.Tips({
						title: err
					});
				});
		},
		/**
		 * 分享打开
		 *
		 */
		listenerActionSheet: function () {
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
		listenerActionClose: function () {
			this.posters = false;
		},
		//隐藏海报
		posterImageClose: function () {
			this.posterImageStatus = false;
		},
		//替换安全域名
		setDomain: function (url) {
			url = url ? url.toString() : '';
			//本地调试打开,生产请注销
			if (url.indexOf('https://') > -1) return url;
			else return url.replace('http://', 'https://');
		},
		// 小程序关闭分享弹窗；
		goFriend: function () {
			this.posters = false;
		},
		/*
		 * 保存到手机相册
		 */
		// #ifdef MP
		savePosterPath: function () {
			let that = this;
			uni.getSetting({
				success(res) {
					if (!res.authSetting['scope.writePhotosAlbum']) {
						uni.authorize({
							scope: 'scope.writePhotosAlbum',
							success() {
								uni.saveImageToPhotosAlbum({
									filePath: that.posterImage,
									success: function (res) {
										that.posterImageClose();
										that.$util.Tips({
											title: that.$t(`保存成功`),
											icon: 'success'
										});
									},
									fail: function (res) {
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
							success: function (res) {
								that.posterImageClose();
								that.$util.Tips({
									title: that.$t(`保存成功`),
									icon: 'success'
								});
							},
							fail: function (res) {
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
		//#ifdef APP-PLUS
		savePosterPath() {
			let that = this;
			uni.saveImageToPhotosAlbum({
				filePath: that.posterImage,
				success: function (res) {
					that.posterImageClose();
					that.$util.Tips({
						title: that.$t(`保存成功`),
						icon: 'success'
					});
				},
				fail: function (res) {
					that.$util.Tips({
						title: that.$t(`保存失败`)
					});
				}
			});
		},
		// #endif
		setShareInfoStatus: function () {
			let data = this.storeInfo;
			let href = location.href;
			if (this.$wechat.isWeixin()) {
				this.posters = true;
				getUserInfo().then((res) => {
					href = href.indexOf('?') === -1 ? href + '?spread=' + res.data.uid : href + '&spread=' + res.data.uid;

					let configAppMessage = {
						desc: data.store_info,
						title: data.store_name,
						link: href,
						imgUrl: data.image
					};
					this.$wechat.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData'], configAppMessage);
				});
			}
		},
		//点击sku图片打开轮播图
		showImg(index) {
			this.$refs.cusPreviewImg.open(this.selectSku.suk);
		},
		//滑动轮播图选择商品
		changeSwitch(e) {
			let productSelect = this.skuArr[e];
			this.$set(this, 'selectSku', productSelect);
			var skuList = productSelect.suk.split(',');
			this.$set(this.attribute.productAttr[0], 'index', skuList[0]);
			if (skuList.length == 2) {
				this.$set(this.attribute.productAttr[0], 'index', skuList[0]);
				this.$set(this.attribute.productAttr[1], 'index', skuList[1]);
			} else if (skuList.length == 3) {
				this.$set(this.attribute.productAttr[0], 'index', skuList[0]);
				this.$set(this.attribute.productAttr[1], 'index', skuList[1]);
				this.$set(this.attribute.productAttr[2], 'index', skuList[2]);
			} else if (skuList.length == 4) {
				this.$set(this.attribute.productAttr[0], 'index', skuList[0]);
				this.$set(this.attribute.productAttr[1], 'index', skuList[1]);
				this.$set(this.attribute.productAttr[2], 'index', skuList[2]);
				this.$set(this.attribute.productAttr[3], 'index', skuList[3]);
			}
			if (productSelect) {
				this.$set(this.attribute.productSelect, 'image', productSelect.image);
				this.$set(this.attribute.productSelect, 'price', productSelect.price);
				this.$set(this.attribute.productSelect, 'stock', productSelect.stock);
				this.$set(this.attribute.productSelect, 'unique', productSelect.id);
				this.$set(this.attribute.productSelect, 'vipPrice', productSelect.vipPrice);
				this.$set(this, 'attrTxt', this.$t(`已选择`));
				this.$set(this, 'attrValue', productSelect.suk);
			}
		},
		showSwiperImg(index) {
			this.$refs.cusSwiperImg.open(index);
		}
	},
	//#ifdef MP
	onShareAppMessage() {
		return {
			title: this.storeInfo.title,
			path: '/pages/activity/goods_seckill_details/index?id=' + this.id + '&spid=' + this.$store.state.app.uid + '&time_id=' + this.time_id,
			imageUrl: this.storeInfo.image
		};
	}
	//#endif
};
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

.navbar .header {
	height: 96rpx;
	font-size: 30rpx;
	color: #050505;
	background-color: #fff;
	/* #ifdef APP-PLUS */
	width: 100%;
	/* #endif */
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
	// background-image: linear-gradient(to right, #ff3366 0%, #ff6533 100%);
	background-color: var(--view-theme);
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
	// justify-content: flex-end;
	// padding-left: 48px;
	/* #endif */
}

.home {
	color: #333;
	position: fixed;
	/* #ifdef MP */
	width: 126rpx;
	left: 15rpx;
	/* #endif */
	/* #ifndef MP */
	width: 56rpx;
	left: 33rpx;
	/* #endif */
	height: 56rpx;
	z-index: 99;
	background: rgba(255, 255, 255, 0.3);
	border: 1px solid rgba(0, 0, 0, 0.1);
	border-radius: 40rpx;
	font-size: 33rpx;

	&.right {
		right: 33rpx;
		left: unset;
	}

	&.on {
		background: unset;
		color: #333;
	}

	&.homeIndex {
		/* #ifdef MP */
		width: 98rpx;
		/* #endif */
		/* #ifndef MP */
		border-color: rgba(255, 255, 255, 0);
		/* #endif */
	}
}

.home .iconfont {
	width: 58rpx;
	text-align: center;
}

.home .line {
	width: 1rpx;
	height: 34rpx;
	background: #b3b3b3;
}

.home .icon-xiangzuo {
	font-size: 28rpx;
}

.product-con .nav {
	background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAu4AAABkCAMAAADud0VvAAAAeFBMVEUAAAD////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////GqOSsAAAAKHRSTlMAAQIDBAUGBwgJCgsMDQ4PEBESExQVFhcYGRobHB0eHyAhIiMkJSYnEt6j2gAACYZJREFUeNrtnel2qzoMhSUPzOnpef+HvD1NmonB90eShoTRBoMBaXWltV06wNfdLSEAYaMhdjtEAAQAfHtTAAraX04/KVB0BCLCbffeXyofdk/0Xev3AzGxzSPh7yJAUAiqvLfUfUrBDfqGF/XzkxPM3aEUw3YcsXOi51ov1hFgk7hHO6+O8vK+aYY9OxwVodwvCobGMKPhX0Uz67BF3DFJRBPl3fJ+OZyIYg3egY8r4ybkM/z9pK3hzpOYNVLeJe/FaX8lhPUiZ8ySjPeT+BLrm8PdSwJspbxN3vPDniy7gcArPpWMd7C+MdzD2ANspbxF3tPvA1l2s4w1uwv8aDLeU9dZdW4zuGMU8xvYrZTfZ9+JP30fidshAo+WqjPYyDrWqdNGcOdR9Py/1ibv99nHvrq9P3xfiNlhAs/Z2DKObbretLgJ3GXs9zYxv/L+IL74/keWfXjGWiPwdqx7M+vbwN2PRW/KX+UdFaRfe7LsoxiaQjDr1h2Rtf9ZrB13DEOuYWLe5P309UOgjhVZ7Sn88cjHSh1ma7jzMEA9E1OSd7X/jyz7qAKfCrRl3bGam9blqmvGXQYeNvPcIe/511dGhI6csaa85ODHs+7IWE9rv17cvVDcd5KBvF//+ybLbiNjLQQb2bo/WUe1VdzRD9gvv1omBgDg9B9ZdnsCL0a07v11fcW4M99HNDQxCg5fZNmtCrzEcaw7cqbbWbBC3IUvjU0MFP+oym5b4K9CDLfuPVivMTerw136HE1NDKT/qMo+QWS5N6wPHm8pL2ofrHXhjtJnpiYG4UiNMVMJ/EWa98Ejr2v+2h7ueHcxRoV2dfhHl59OF+mbwPclnzHeXLvvVvv14M49jgBmJgby/XdBDE4ZxVkKXeuOnA+8YHUtuAvJQafl8WU2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');
	background-repeat: no-repeat;
	background-size: 100% 100%;
	width: 100%;
	height: 100rpx;
	padding: 0 30rpx;
	box-sizing: border-box;
}

.product-con .nav /deep/.time .styleAll {
	padding: 0 6rpx;
	font-size: 22rpx;
	color: var(--view-theme);
	background-color: #fff;
	border-radius: 2rpx;
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
	color: #fff;
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
	display: flex;
	justify-content: space-between;
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
	background-color: rgba(255, 255, 255, 0.85);
	backdrop-filter: blur(10px);
	z-index: 277;
	border-top: 1rpx solid #f0f0f0;
	height: 100rpx;
	height: calc(100rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
	height: calc(100rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
	transform: translate3d(0, 100%, 0);
	transition: all 0.3s cubic-bezier(0.25, 0.5, 0.5, 0.9);
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
	padding: 2rpx 8rpx 3rpx;
	border-radius: 200rpx;
	top: -10rpx;
	right: -10rpx;
}

.product-con .footer .bnt {
	width: 540rpx;
	height: 76rpx;
}
.product-con .footer .bnt .bnts.long-btn {
	width: 540rpx;
	border-radius: 50px;
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
	// background-image: linear-gradient(to right, #fea10f 0%, #fa8013 100%);
	background-color: var(--view-bntColor);
}

.product-con .footer .bnt .buy {
	border-radius: 0 50rpx 50rpx 0;
	background-color: var(--view-theme);
	// background-image: linear-gradient(to right, #fa6514 0%, #e93323 100%);
}

.product-con .conter {
	display: block;
	padding-bottom: 100rpx;
}

.product-con .conter img {
	display: block;
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
	margin-top: -377rpx;
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
	z-index: 99 !important;
}

.mask1 {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #000;
	opacity: 0.5;
	z-index: 288;
}

.home-nav {
	/* #ifdef H5 */
	top: 20rpx !important;
	/* #endif */
}

.home-nav {
	color: #333;
	position: fixed;
	/* #ifdef MP */
	width: 126rpx;
	left: 15rpx;
	/* #endif */
	/* #ifndef MP */
	width: 56rpx;
	left: 33rpx;
	/* #endif */
	height: 56rpx;
	font-size: 33rpx;
	z-index: 99;
	background: rgba(255, 255, 255, 0.3);
	border: 1px solid rgba(0, 0, 0, 0.1);
	border-radius: 40rpx;

	&.right {
		right: 33rpx;
		left: unset;
	}

	&.on {
		background: unset;
		color: #333;
	}

	&.homeIndex {
		/* #ifdef MP */
		width: 98rpx;
		/* #endif */
		/* #ifndef MP */
		border-color: rgba(255, 255, 255, 0);
		/* #endif */
	}
}

.home-nav .iconfont {
	width: 58rpx;
	text-align: center;
}

.home-nav .line {
	width: 1rpx;
	height: 34rpx;
	background: #b3b3b3;
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
</style>
