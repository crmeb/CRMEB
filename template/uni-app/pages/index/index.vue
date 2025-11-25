<template>
	<!-- 首页 -->
	<view v-if="pageShow" class="page" :class="bgTabVal == 2 ? 'fullsize noRepeat' : bgTabVal == 1 ? 'repeat ysize' : 'noRepeat ysize'" :style="[pageStyle]">
		<view v-if="!errorNetwork" :style="colorStyle">
			<!-- #ifdef MP -->
			<view class="fixed z-1000" :style="[appletStyle]" v-if="myApplet">
				<view class="myApplet w-324 h-62 text-center rd-12rpx lh-62rpx fs-24 bg--w111-fff text-w111-303133">
					点击添加到我的小程序
					<text class="iconfont icon-ic_close2 text--w111-ccc ml-16" @click="myApplet = false"></text>
				</view>
			</view>
			<!-- #endif -->
			<!-- 轮播搜索 -->
			<homeComb v-if="showHomeComb" :dataConfig="homeCombData" :belongIndex="belongIndex" @bindSortId="bindSortId" :isScrolled="isScrolled" @storeTap="storeTap"></homeComb>
			<!-- 顶部搜索框 -->
			<headerSerch v-if="isHeaderSerch" :dataConfig="headerSerchCombData" :belongIndex="belongIndex" @storeTap="storeTap"></headerSerch>
			<tabNav v-if="showCateNav" :dataConfig="cateNavData" @bindHeight="bindHeighta" @bindSortId="bindSortId" :isFixed="isFixed && !cateNavData.stickyConfig.tabVal"></tabNav>
			<view class="index">
				<!-- 自定义样式 -->
				<block v-for="(item, index) in styleConfig" :key="index">
					<userInfor v-if="item.name == 'userInfor'" :dataConfig="item" @changeLogin="changeLogin"></userInfor>
					<newVip v-if="item.name == 'newVip'" :dataConfig="item"></newVip>
					<!-- 文章列表 -->
					<articleList v-if="item.name == 'articleList'" :dataConfig="item"></articleList>
					<bargain v-if="item.name == 'bargain'" :dataConfig="item" @changeBarg="changeBarg"></bargain>
					<blankPage v-if="item.name == 'blankPage'" :dataConfig="item"></blankPage>
					<combination v-if="item.name == 'combination'" :dataConfig="item"></combination>
					<!-- 优惠券 -->
					<coupon v-if="item.name == 'coupon'" :dataConfig="item" @changeLogin="changeLogin"></coupon>
					<!-- 客户服务 -->
					<customerService v-if="item.name == 'customerService'" :dataConfig="item"></customerService>
					<!-- 商品列表 -->
					<goodList ref="goodLists" v-if="item.name == 'goodList'" :dataConfig="item"></goodList>
					<guide v-if="item.name == 'guide'" :dataConfig="item"></guide>
					<!-- 直播模块 -->
					<!-- #ifdef  MP-WEIXIN -->
					<liveBroadcast v-if="item.name == 'liveBroadcast'" :dataConfig="item"></liveBroadcast>
					<!-- #endif -->
					<menus v-if="item.name == 'menus'" :dataConfig="item"></menus>
					<!-- 实时消息 -->
					<news v-if="item.name == 'news'" :dataConfig="item"></news>
					<!-- 图片库 -->
					<pictureCube v-if="item.name == 'pictureCube'" :dataConfig="item"></pictureCube>
					<!-- 促销列表 -->
					<promotionList
						ref="promotionLists"
						v-if="item.name == 'promotionList'"
						:dataConfig="item"
						:productVideoStatus="product_video_status"
						:positionTop="positionTop"
					></promotionList>
					<seckill v-if="item.name == 'seckill'" :dataConfig="item"></seckill>
					<!-- 轮播图-->
					<swiperBg v-if="item.name == 'swiperBg'" :dataConfig="item"></swiperBg>
					<swipers v-if="item.name == 'swipers'" :dataConfig="item"></swipers>
					<!-- 顶部选项卡 -->

					<!-- 标题 -->
					<titles v-if="item.name == 'titles'" :dataConfig="item"></titles>
					<ranking v-if="item.name == 'ranking'" :dataConfig="item"></ranking>
					<presale v-if="item.name == 'presale'" :dataConfig="item"></presale>
					<pointsMall v-if="item.name == 'pointsMall'" :dataConfig="item"></pointsMall>
					<!-- #ifndef APP -->
					<richText v-if="item.name == 'richText'" :dataConfig="item"></richText>
					<videos v-if="item.name == 'videos'" :dataConfig="item"></videos>
					<!-- #endif -->
					<signIn v-if="item.name == 'signIn'" :dataConfig="item"></signIn>
					<hotspot v-if="item.name == 'hotspot'" :dataConfig="item"></hotspot>
					<follow v-if="item.name == 'follow'" :dataConfig="item"></follow>
				</block>
				<!-- 分类商品模块 -->
				<!-- #ifndef  APP-PLUS -->
				<view class="sort-product px-20" v-if="!styleConfig.length">
					<!-- #endif -->
					<!-- #ifdef  APP-PLUS -->
					<!-- 商品排序 -->
					<view class="sort-product px-20" :style="{ marginTop: sortMpTop + 'px' }" v-if="!styleConfig.length">
						<!-- #endif -->
						<view class="rd-24rpx bg--w111-fff p-24 mb-24" v-if="sortList.children && sortList.children.length">
							<scroll-view scroll-x="true" class="white-nowrap vertical-middle w-full" show-scrollbar="false">
								<view class="inline-block mr-24" v-for="(item, index) in sortList.children" :key="index">
									<view class="flex-col flex-center" @tap="changeSort(item, index)">
										<view class="picture w-90 h-90 rd-50-p111-" :class="{ select: curSort == index }">
											<image :src="item.pic" class="w-full h-full rd-50-p111-"></image>
										</view>
										<text class="fs-24 pt-14" :class="{ 'font-num': curSort == index }">{{ item.cate_name }}</text>
									</view>
								</view>
							</scroll-view>
						</view>
						<waterfallsFlow ref="waterfallsFlow" :wfList="goodList" :goDetail="'goDetail'" @itemTap="goDetail"></waterfallsFlow>
						<Loading :loaded="loaded" :loading="loading"></Loading>
						<view v-if="goodList.length == 0 && loaded">
							<emptyPage title="暂无商品，去看点别的吧～"></emptyPage>
						</view>
					</view>
					<couponWindow :window="isCouponShow" @onColse="couponClose" :couponImage="couponObj.image" :couponList="couponObj.list"></couponWindow>
					<view class="">
						{{ site_config }}
					</view>
					<!-- #ifndef APP-PLUS -->
					<view v-if="configData && configData.record_No" class="site-config" @click="goICP(configData.icp_url)">{{ configData.record_No }}</view>
					<view class="site-config" v-if="configData && configData.network_security" @click="goICP(configData.network_security_url)">
						<image class="ban" src="/static/images/beian.png" alt="" srcset="" />
						{{ configData.network_security }}
					</view>
					<!-- #endif -->
					<view class="pb-safe" :style="[pdHeights]" v-if="isFooter"></view>
					<pageFooter :configData="footerConfigData" @newDataStatus="newDataStatus"></pageFooter>
				</view>
			</view>
			<!-- #ifdef APP -->
			<app-update ref="appUpdate" :force="true" :tabbar="false"></app-update>
			<!-- #endif -->
		</view>
		<view v-else>
			<view class="error-network">
				<image :src="imgHost + '/statics/images/error-network.gif'"></image>
				<view class="title">{{ $t(`网络连接断开`) }}</view>
				<view class="con">
					<view class="label">{{ $t(`请检查情况`) }}：</view>
					<view class="item">· {{ $t(`在设置中是否已开启网络权限`) }}</view>
					<view class="item">· {{ $t(`当前是否处于弱网环境`) }}</view>
					<view class="item">· {{ $t(`版本是否过低，升级试试吧`) }}</view>
				</view>
				<view class="btn" @click="reconnect">{{ $t(`重新连接`) }}</view>
			</view>
		</view>
	</view>
</template>

<script>
const app = getApp();
import colors from '@/mixins/color';
import couponWindow from '@/components/couponWindow/index';
import { getCouponV2, getCouponNewUser, getCrmebCopyRight } from '@/api/api.js';
import { getShare } from '@/api/public.js';
import userInfor from './components/userInfor';
import homeComb from './components/homeComb';
import newVip from './components/newVip';
import headerSerch from './components/headerSerch';
import swipers from './components/swipers';
import coupon from './components/coupon';
import articleList from './components/articleList';
import bargain from './components/bargain';
import blankPage from './components/blankPage';
import combination from './components/combination';
import customerService from './components/customerService';
import goodList from './components/goodList';
import guide from './components/guide';
import liveBroadcast from './components/liveBroadcast';
import menus from './components/menus';
import news from './components/news';
import pictureCube from './components/pictureCube';
import promotionList from './components/promotionList';
import seckill from './components/seckill';
import swiperBg from './components/swiperBg';
import tabNav from './components/tabNav';
import titles from './components/titles';
import ranking from './components/ranking';
import presale from './components/presale';
import pointsMall from './components/pointsMall';
// #ifndef APP
import richText from './components/richText';
import videos from './components/videos';
// #endif
import signIn from './components/signIn';
import hotspot from './components/hotspot';
import follow from './components/follow';
import waterfallsFlow from '@/components/WaterfallsFlow/WaterfallsFlow.vue';
import emptyPage from '@/components/emptyPage.vue';
// #ifdef MP
import { getTempIds } from '@/api/api.js';
import { SUBSCRIBE_MESSAGE } from '@/config/cache';
// #endif
import { mapGetters, mapMutations } from 'vuex';
import { getDiy, getDiyVersion } from '@/api/api.js';
import { getCartCounts } from '@/api/order.js';
import { getCategoryList, getProductslist } from '@/api/store.js';
import { goShopDetail } from '@/libs/order.js';
import { toLogin } from '@/libs/login.js';
import { HTTP_REQUEST_URL } from '@/config/app';
import pageFooter from '@/components/pageFooter/index.vue';
import Loading from '@/components/Loading/index.vue';
import Cache from '@/utils/cache';
import appUpdate from '@/components/update/app-update.vue';

export default {
	computed: {
		// #ifdef MP
		appletStyle() {
			return {
				top: this.getHeight.menuButtonInfo.bottom + 8 + 'px',
				right: '10px'
			};
		},
		// #endif
		pageStyle() {
			return {
				backgroundColor: this.bgColor,
				backgroundImage: this.bgPic ? `url(${this.bgPic})` : '',
				minHeight: this.windowHeight + 'px'
			};
		},
		pdHeights() {
			let H = `${this.pdHeight * 2 + 100}rpx`;
			return {
				height: this.isFooter ? H : '100rpx'
			};
		},
		...mapGetters(['isLogin', 'uid', 'cartNum'])
	},
	mixins: [colors],
	components: {
		Loading,
		pageFooter,
		couponWindow,
		homeComb,
		newVip,
		userInfor,
		headerSerch,
		swipers,
		coupon,
		articleList,
		bargain,
		blankPage,
		combination,
		customerService,
		goodList,
		guide,
		liveBroadcast,
		menus,
		pictureCube,
		news,
		promotionList,
		seckill,
		swiperBg,
		tabNav,
		titles,
		ranking,
		presale,
		pointsMall,
		// #ifndef APP
		richText,
		videos,
		// #endif
		signIn,
		hotspot,
		follow,
		waterfallsFlow,
		emptyPage,
		// #ifdef APP
		appUpdate
		// #endif
	},
	data() {
		return {
			styleConfig: [],
			loading: false,
			loadend: false,
			loadTitle: '下拉加载更多', //提示语
			page: 1,
			limit: this.$config.LIMIT,
			numConfig: 0,
			code: '',
			isCouponShow: false,
			couponObj: {},
			couponObjs: {
				show: false
			},
			shareInfo: {},
			sortList: '',
			sortAll: [],
			goodPage: 1,
			goodList: [],
			sid: 0,
			curSort: 0,
			sortMpTop: 0,
			loaded: false,
			loading: false,
			domOffsetTop: 50,
			// #ifdef APP-PLUS || MP
			isFixed: true,
			// #endif
			// #ifdef H5
			isFixed: false,
			// #endif
			site_config: '',
			errorNetwork: false, // 是否断网
			isHeaderSerch: false,
			showHomeComb: false,
			showCateNav: false,
			homeCombData: {},
			headerSerchCombData: {},
			cateNavData: {},
			footerConfigData: {},
			bgColor: '',
			bgPic: '',
			bgTabVal: '',
			pageShow: true,
			windowHeight: 0,
			imgHost: HTTP_REQUEST_URL,
			isShowAuth: false,
			isScrolled: false,
			product_video_status: false,
			confirm_video_status: false,
			positionTop: 0,
			isFooter: false,
			pdHeight: 0, //自定义底部导航上下边距和
			entryData: {
				store_id: '',
				latitude: '',
				longitude: '',
				select_store_id: ''
			},
			goodsIndex: [],
			promotionIndex: [],
			belongIndex: 0, // 进店规则归属门店排序位置；
			isBelongStore: false, //判断是否为归属门店；
			getHeight: this.$util.getWXStatusHeight(),
			myApplet: true,
			configData: Cache.get('BASIC_CONFIG')
		};
	},
	onLoad(options) {
		let that = this;
		uni.hideTabBar();
		that.getOptions(options);
		this.$nextTick(function () {
			uni.getSystemInfo({
				success: function (res) {
					that.windowHeight = res.windowHeight;
				}
			});
		});
		const { state, scope } = options;
		this.diyData();
		// #ifdef H5
		this.setOpenShare();
		// #endif
		// #ifdef MP
		this.getTempIds();
		// #endif
		getShare().then((res) => {
			this.shareInfo = res.data;
		});
		this.getCopyRight();
		this.$eventHub.$on('confirm_video_status', () => {
			if (this.confirm_video_status) {
				return;
			}
			this.confirm_video_status = true;
			let flag = true;
			// #ifdef H5
			flag = window.self == window.top;
			// #endif
			if (!flag) {
				return;
			}
			uni.showModal({
				content: '当前使用移动网络，是否继续播放视频？',
				success: (res) => {
					if (res.confirm) {
						// 监听
						this.SET_AUTOPLAY(true);
						this.$eventHub.$emit('product_video_observe');
					}
				}
			});
		});
	},
	onUnload() {
		// 清除监听
		uni.$off('activeFn');
	},
	watch: {
		isLogin: {
			deep: true, //深度监听设置为 true
			handler: function (newV, oldV) {
				// 优惠券弹窗
				var newDates = new Date().toLocaleDateString();
				if (newV) {
					try {
						var oldDate = uni.getStorageSync('oldDate') || '';
					} catch {}
					if (oldDate != newDates) {
						this.getCoupon();
					}
				}
			}
		}
	},
	onShow() {
		uni.removeStorageSync('form_type_cart');
		// 优惠券弹窗
		if (this.isLogin) {
			this.getCoupon();
			this.getCartNum();
		}
		// #ifdef MP
		if (wx.canIUse('checkIsAddedToMyMiniProgram')) {
			this.checkMyApplet();
		} else {
			this.myApplet = true;
		}
		// #endif
	},
	onPullDownRefresh() {
		this.diyData();
		uni.stopPullDownRefresh();
	},
	methods: {
		...mapMutations(['SET_AUTOPLAY', 'SET_NEARBY']),
		checkMyApplet() {
			wx.checkIsAddedToMyMiniProgram({
				success: (res) => {
					if (res.added) {
						this.myApplet = false;
					} else {
						this.myApplet = true;
					}
				},
				fail: () => {
					this.myApplet = true;
				}
			});
		},
		getCartNum: function () {
			getCartCounts()
				.then((res) => {
					this.$store.commit('indexData/setCartNum', res.data.count + '');
					let cartNum = res.data.count;
					if (cartNum > 0) {
						uni.setTabBarBadge({
							index: 3,
							text: cartNum > 99 ? '99+' : cartNum + ''
						});
					} else {
						uni.hideTabBarRedDot({
							index: 3
						});
					}
				})
				.catch((err) => {
					return this.$util.Tips({
						title: err.msg
					});
				});
		},
		storeTap(id) {
			this.entryData.select_store_id = id;
			this.entryData.store_id = '';
			uni.removeStorageSync('rulesStoreId');
		},
		getCopyRight() {
			getCrmebCopyRight()
				.then((res) => {
					let data = res.data;
					uni.setStorageSync('wechatStatus', data.wechat_status);
					if (!data.copyrightContext && !data.copyrightImage) {
						data.copyrightImage = '/static/images/support.png';
					}
					uni.setStorageSync('copyNameInfo', data.copyrightContext);
					uni.setStorageSync('copyImageInfo', data.copyrightImage);
					// #ifdef MP
					uni.setStorageSync(
						'MPSiteData',
						JSON.stringify({
							site_logo: data.site_logo,
							site_name: data.site_name
						})
					);
					// #endif
				})
				.catch((err) => {
					return this.$util.Tips({
						title: err.msg
					});
				});
		},
		getOptions(options) {
			let that = this;
			// #ifdef MP
			if (options.scene) {
				let value = that.$util.getUrlParams(decodeURIComponent(options.scene));
				//记录推广人uid
				if (value.spid) app.globalData.spid = value.spid;
			}
			// #endif
			if (options.spid) app.globalData.spid = options.spid;
		},
		// 重新链接
		reconnect() {
			this.diyData();
			getShare().then((res) => {
				this.shareInfo = res.data;
			});
		},
		goICP(url) {
			// #ifdef H5
			window.open(url);
			// #endif
			// #ifdef MP
			uni.navigateTo({
				url: `/pages/annex/web_view/index?url=${url}`
			});
			// #endif
		},
		bindHeighta(data) {
			// #ifdef APP-PLUS
			this.sortMpTop = data.top + data.height;
			// #endif
		},
		bindHeight(data) {
			uni.hideLoading();
			this.domOffsetTop = data.top;
		},
		// 去商品详情
		goGoodsDetail(item) {
			goShopDetail(item, this.uid).then((res) => {
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${item.id}`
				});
			});
		},
		// 分类点击
		changeSort(item, index) {
			if (this.curSort == index) return;
			this.curSort = index;
			this.sid = item.id;
			this.goodList = [];
			this.goodPage = 1;
			this.loaded = false;
			this.getGoodsList();
		},
		/**
			 * @param data {
				classPage: 0 分类id
				microPage: 0 微页面id
				type: 1   0 微页面 1 商品分类
			 }*/
		bindSortId(data) {
			this.styleConfig = [];
			if (data.type == 1) {
				this.getProductList(data.classPage);
			} else {
				this.sortList = [];
				this.getMicroPage(data.microPage, true);
			}
		},
		/**
		 * 获取DIY
		 * @param {number} id
		 * @param {boolean} type 区分是否是微页面
		 */
		getMicroPage(id, type) {
			let that = this;
			that.styleConfig = [];
			uni.showLoading({
				title: '加载中...'
			});
			getDiy(id)
				.then((res) => {
					uni.hideLoading();
					let data = res.data;
					let diyArr = that.objToArr(res.data.value);
					diyArr = diyArr.filter((item) => !item.isHide);
					diyArr.forEach((item, index) => {
						if (['headerSerch', 'homeComb'].includes(item.name)) {
							diyArr.splice(index, 1);
						}
					});
					this.styleConfig = diyArr;
				})
				.catch((err) => {
					return that.$util.Tips({
						title: err
					});
					uni.hideLoading();
				});
		},
		getProductList(data) {
			let tempObj = '';
			this.curSort = 0;
			this.loaded = false;
			if (this.sortAll.length > 0) {
				this.sortAll.forEach((el, index) => {
					if (el.id == data) {
						this.$set(this, 'sortList', el);
						this.sid = el.children.length ? el.children[0].id : '';
					}
				});
				this.goodList = [];
				this.goodPage = 1;
				this.$nextTick(() => {
					if (this.sortList != '') this.getGoodsList();
				});
			} else {
				getCategoryList().then((res) => {
					this.sortAll = res.data;
					res.data.forEach((el, index) => {
						if (el.id == data) {
							this.sortList = el;
							this.sid = el.children.length ? el.children[0].id : '';
						}
					});
					this.goodList = [];
					this.goodPage = 1;

					this.$nextTick(() => {
						if (this.sortList != '') this.getGoodsList();
					});
				});
			}
		},
		// 商品列表
		getGoodsList() {
			if (this.loading || this.loaded) return;
			this.loading = true;
			getProductslist({
				sid: this.sid,
				keyword: '',
				priceOrder: '',
				salesOrder: '',
				news: 0,
				page: this.goodPage,
				limit: 10,
				cid: this.sortList.id
			}).then((res) => {
				this.loading = false;
				this.loaded = res.data.length < 10;
				this.goodPage++;
				this.goodList = this.goodList.concat(res.data);
			});
		},
		// 新用户优惠券
		getNewCoupon() {
			const oldUser = uni.getStorageSync('oldUser') || 0;
			if (!oldUser) {
				getCouponNewUser().then((res) => {
					const { data } = res;
					if (data.show) {
						if (data.list.length) {
							this.isCouponShow = true;
							this.couponObj = data;
							uni.setStorageSync('oldUser', 1);
						}
					} else {
						uni.setStorageSync('oldUser', 1);
					}
				});
			}
		},
		// 优惠券弹窗
		getCoupon() {
			const tagDate = uni.getStorageSync('tagDate') || '',
				nowDate = new Date().toLocaleDateString();
			if (tagDate === nowDate) {
				this.getNewCoupon();
			} else {
				getCouponV2().then((res) => {
					const { data } = res;
					if (data.list.length) {
						this.isCouponShow = true;
						this.couponObj = data;
						uni.setStorageSync('tagDate', new Date().toLocaleDateString());
					} else {
						this.getNewCoupon();
					}
				});
			}
		},
		// 优惠券弹窗关闭
		couponClose() {
			this.isCouponShow = false;
			if (!uni.getStorageSync('oldUser')) {
				this.getNewCoupon();
			}
		},
		onLoadFun() {
			this.isShowAuth = false;
		},
		// #ifdef H5
		// 获取url后面的参数
		getQueryString(name) {
			var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
			var reg_rewrite = new RegExp('(^|/)' + name + '/([^/]*)(/|$)', 'i');
			var r = window.location.search.substr(1).match(reg);
			var q = window.location.pathname.substr(1).match(reg_rewrite);
			if (r != null) {
				return unescape(r[2]);
			} else if (q != null) {
				return unescape(q[2]);
			} else {
				return null;
			}
		},
		// #endif

		// #ifdef MP
		getTempIds() {
			let messageTmplIds = wx.getStorageSync(SUBSCRIBE_MESSAGE);
			if (!messageTmplIds) {
				getTempIds().then((res) => {
					if (res.data) wx.setStorageSync(SUBSCRIBE_MESSAGE, JSON.stringify(res.data));
				});
			}
		},
		// #endif
		// 对象转数组
		objToArr(data) {
			let obj = Object.keys(data).sort();
			let m = obj.map((key) => data[key]);
			return m;
		},
		setDiyData(data) {
			this.errorNetwork = false;
			if (data.is_bg_color) {
				this.bgColor = data.color_picker;
			}
			if (data.is_bg_pic) {
				this.bgPic = data.bg_pic;
				this.bgTabVal = data.bg_tab_val;
			}
			this.pageShow = data.is_show;
			uni.setNavigationBarTitle({
				title: data.title
			});
			let temp = [];
			let goodsIndex = [];
			let promotionIndex = [];
			let lastArr = this.objToArr(data.value);
			lastArr.forEach((item, index, arr) => {
				if (item.name == 'pageFoot') {
					this.footerConfigData = item;
				}
				if (item.name === 'homeComb' && !item.isHide) {
					this.showHomeComb = true;
					this.homeCombData = item;
					if (item.searchConfig.tabVal) {
						this.positionTop = uni.getSystemInfoSync().statusBarHeight + 43;
					}
				}
				if (item.name == 'headerSerch' && !item.isHide) {
					this.isHeaderSerch = true;
					this.headerSerchCombData = item;
				}
				if (item.name == 'tabNav' && !item.isHide) {
					this.showCateNav = true;
					this.cateNavData = item;
				}
				if (item.name == 'goodList' && !item.isHide) {
					goodsIndex.push(index);
				}
				if (item.name == 'promotionList' && !item.isHide) {
					promotionIndex.push(index);
				}
				if (!item.isHide) {
					temp.push(item);
				}
			});

			function sortNumber(a, b) {
				return a.timestamp - b.timestamp;
			}
			temp.sort(sortNumber);
			this.styleConfig = temp;
			this.goodsIndex = goodsIndex;
			this.promotionIndex = promotionIndex;
		},
		getDiyData() {
			getDiy(0)
				.then((res) => {
					uni.setStorageSync('diyData', JSON.stringify(res.data));
					this.setDiyData(res.data);
				})
				.catch((error) => {
					// #ifdef APP-PLUS
					if (error.status) {
						uni.hideLoading();
						if (this.errorNetwork) {
							uni.showToast({
								title: '请开启网络连接',
								icon: 'none',
								duration: 2000
							});
						}
						this.errorNetwork = true;
					}
					// #endif
				});
		},
		diyData() {
			let diyData = uni.getStorageSync('diyData');
			if (diyData) {
				getDiyVersion(0).then((res) => {
					let diyVersion = uni.getStorageSync('diyVersion');
					if (res.data.version + '0' === diyVersion) {
						this.setDiyData(JSON.parse(diyData));
					} else {
						uni.setStorageSync('diyVersion', res.data.version + '0');
						this.getDiyData();
					}
				});
			} else {
				this.getDiyData();
			}
		},

		changeLogin() {
			this.getIsLogin();
		},
		getIsLogin() {
			toLogin();
		},
		changeBarg(item) {
			if (!this.isLogin) {
				this.getIsLogin();
			} else {
				uni.navigateTo({
					url: `/pages/activity/goods_bargain_details/index?id=${item.id}&spid=${this.$store.state.app.uid}`
				});
			}
		},
		goDetail(item) {
			goShopDetail(item, this.$store.state.app.uid).then((res) => {
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${item.id}`
				});
			});
		},
		newDataStatus(val, num) {
			this.isFooter = val ? true : false;
			this.pdHeight = num;
		},
		// #ifdef H5
		// 微信分享；
		setOpenShare: function () {
			let that = this;
			let uid = this.uid ? this.uid : 0;
			if (that.$wechat.isWeixin()) {
				getShare().then((res) => {
					let data = res.data;
					let configAppMessage = {
						desc: data.synopsis,
						title: data.title,
						link: location.href + '?spid=' + uid,
						imgUrl: data.img
					};
					that.$wechat.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareAppMessage', 'onMenuShareTimeline'], configAppMessage);
				});
			}
		}
		// #endif
	},
	onReachBottom() {
		if (this.goodList.length) {
			this.getGoodsList();
		}
	},
	onPageScroll(e) {
		if (e.scrollTop > 20) {
			this.myApplet = false;
		}
		// #ifdef H5
		if (this.isHeaderSerch) {
			if (e.scrollTop > this.domOffsetTop) {
				this.isFixed = true;
			}
			if (e.scrollTop < this.domOffsetTop) {
				this.$nextTick(() => {
					this.isFixed = false;
				});
			}
		} else {
			this.isFixed = false;
		}
		// #endif
		if (e.scrollTop > 10) {
			this.isScrolled = true;
		} else {
			this.isScrolled = false;
		}
		uni.$emit('scroll');
		uni.$emit('onPageScroll', e.scrollTop);
	},
	//#ifdef MP
	onShareAppMessage() {
		let uid = this.uid ? this.uid : 0;
		if (this.shareInfo.img) {
			return {
				title: this.shareInfo.title,
				path: '/pages/index/index?spid=' + uid,
				imageUrl: this.shareInfo.img,
				desc: this.shareInfo.synopsis
			};
		} else {
			return {
				title: this.shareInfo.title,
				path: '/pages/index/index?spid=' + uid
				// imageUrl: this.shareInfo.img,
				// desc: this.shareInfo.synopsis
			};
		}
	},
	//分享到朋友圈
	onShareTimeline: function () {
		return {
			title: this.shareInfo.title,
			path: '/pages/index/index',
			imageUrl: this.shareInfo.img,
			desc: this.shareInfo.synopsis
		};
	}
	//#endif
};
</script>

<style lang="scss">
.page {
	// padding-bottom: 50px;
	overflow-y: scroll;
	overflow-x: hidden;
}
.myApplet {
	position: relative;
	&::after {
		position: absolute;
		right: 55px;
		top: -5px;
		content: '';
		width: 0;
		height: 0;
		border-left: 7px solid transparent;
		border-right: 7px solid transparent;
		border-bottom: 7px solid #fff;
	}
}
.pictrue_log_class {
	background-color: var(--view-theme);
}

.ysize {
	background-size: 100%;
}

.fullsize {
	background-size: 100% 100%;
}

.repeat {
	background-repeat: repeat;
}

.noRepeat {
	background-repeat: no-repeat;
}

.error-network {
	position: fixed;
	left: 0;
	top: 0;
	display: flex;
	flex-direction: column;
	align-items: center;
	width: 100%;
	height: 100%;
	padding-top: 40rpx;
	background: #fff;

	image {
		width: 414rpx;
		height: 336rpx;
	}

	.title {
		position: relative;
		top: -40rpx;
		font-size: 32rpx;
		color: #666;
	}

	.con {
		font-size: 24rpx;
		color: #999;

		.label {
			margin-bottom: 20rpx;
		}

		.item {
			margin-bottom: 20rpx;
		}
	}

	.btn {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 508rpx;
		height: 86rpx;
		margin-top: 100rpx;
		border: 1px solid #d74432;
		color: #e93323;
		font-size: 30rpx;
		border-radius: 120rpx;
	}
}

.sort-scroll {
	background-color: #fff;
}

.sort-product {
	margin-top: 20rpx;
}

.site-config {
	margin: 40rpx 0;
	font-size: 24rpx;
	text-align: center;
	color: #666;
	display: flex;
	align-items: center;
	justify-content: center;
	.ban {
		width: 22rpx;
		height: 24rpx;
		margin-right: 10rpx;
	}
	&.fixed {
		position: fixed;
		bottom: 69px;
		left: 0;
		width: 100%;
	}
}
.select {
	border: 1px solid var(--view-theme);
}
</style>
