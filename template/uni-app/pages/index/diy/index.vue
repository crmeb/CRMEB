<template>
	<view v-if="pageShow" class="page"
		:class="bgTabVal==2?'fullsize noRepeat':bgTabVal==1?'repeat ysize':'noRepeat ysize'"
		:style="'background-color:'+bgColor+';background-image: url('+bgPic+');min-height:'+windowHeight+'px;'">
		<view v-if="!errorNetwork" :style="colorStyle">
			<skeleton :show="showSkeleton" :isNodes="isNodes" ref="skeleton" loading="chiaroscuro" selector="skeleton"
				bgcolor="#FFF"></skeleton>
			<view class="index skeleton" :style="{visibility: showSkeleton ? 'hidden' : 'visible'}">
				<!-- #ifdef H5 -->
				<view v-for="(item, index) in styleConfig" :key="index">
					<component :is="item.name" :index="index" :dataConfig="item" @changeBarg="changeBarg"
						@changeTab="changeTab" :tempArr="tempArr" :iSshowH="iSshowH" @detail="goDetail"
						:isSortType="isSortType" @bindSortId="bindSortId" @bindHeight="bindHeight" :isFixed="isFixed">
					</component>
				</view>
				<!-- #endif -->
				<!-- #ifdef MP || APP-PLUS -->
				<block v-for="(item, index) in styleConfig" :key="index">
					<activeParty v-if="item.name == 'activeParty'" :dataConfig="item" :isSortType="isSortType">
					</activeParty>
					<articleList v-if="item.name == 'articleList'" :dataConfig="item" :isSortType="isSortType">
					</articleList>
					<bargain v-if="item.name == 'bargain'" :dataConfig="item" @changeBarg="changeBarg"
						:isSortType="isSortType"></bargain>
					<blankPage v-if="item.name == 'blankPage'" :dataConfig="item" :isSortType="isSortType"></blankPage>
					<combination v-if="item.name == 'combination'" :dataConfig="item" :isSortType="isSortType">
					</combination>
					<coupon v-if="item.name == 'coupon'" :dataConfig="item" :isSortType="isSortType"></coupon>
					<customerService v-if="item.name == 'customerService'" :dataConfig="item" :isSortType="isSortType">
					</customerService>
					<goodList v-if="item.name == 'goodList'" :dataConfig="item" @detail="goDetail"
						:isSortType="isSortType"></goodList>
					<guide v-if="item.name == 'guide'" :dataConfig="item" :isSortType="isSortType"></guide>
					<headerSerch v-if="item.name == 'headerSerch'" :dataConfig="item"></headerSerch>
					<liveBroadcast v-if="item.name == 'liveBroadcast'" :dataConfig="item" :isSortType="isSortType">
					</liveBroadcast>
					<menus v-if="item.name == 'menus'" :dataConfig="item" :isSortType="isSortType"></menus>
					<news v-if="item.name == 'news'" :dataConfig="item" :isSortType="isSortType"></news>
					<pictureCube v-if="item.name == 'pictureCube'" :dataConfig="item" :isSortType="isSortType">
					</pictureCube>
					<promotionList v-if="item.name == 'promotionList'" :dataConfig="item" @changeTab="changeTab"
						:tempArr="tempArr" :iSshowH="iSshowH" @detail="goDetail" :isSortType="isSortType">
					</promotionList>
					<richText v-if="item.name == 'richText'" :dataConfig="item" :isSortType="isSortType"></richText>
					<seckill v-if="item.name == 'seckill'" :dataConfig="item" :isSortType="isSortType"></seckill>
					<swiperBg v-if="item.name == 'swiperBg'" :dataConfig="item" :isSortType="isSortType"></swiperBg>
					<swipers v-if="item.name == 'swipers'" :dataConfig="item" :isSortType="isSortType"></swipers>
					<tabNav v-if="item.name == 'tabNav'" :dataConfig="item" @bindHeight="bindHeighta"
						@bindSortId="bindSortId" :isFixed="isFixed"></tabNav>
					<titles v-if="item.name == 'titles'" :dataConfig="item" :isSortType="isSortType"></titles>
				</block>
				<!-- #endif -->
				<!-- 分类商品模块 -->
				<!-- #ifdef  APP-PLUS -->
				<view class="sort-product" v-if="isSortType == 1" style="margin-top: 0;">
					<scroll-view scroll-x="true" style="background: #fff;">
						<view class="sort-box" v-if="sortList.children && sortList.children.length">
							<view class="sort-item" v-for="(item, index) in sortList.children" :key="index"
								@click="changeSort(item, index)" :class="{ on: curSort == index }">
								<image :src="item.pic" mode="" v-if="item.pic"></image>
								<image src="/static/images/sort-img.png" mode="" v-else></image>
								<view class="txt">{{ item.cate_name }}</view>
							</view>
						</view>
					</scroll-view>
					<view class="product-list" v-if="goodList.length">
						<view class="product-item" v-for="(item, index) in goodList" @click="goGoodsDetail(item)">
							<image :src="item.image"></image>
							<span class="pictrue_log_big pictrue_log_class"
								v-if="item.activity && item.activity.type === '1'">秒杀</span>
							<span class="pictrue_log_big pictrue_log_class"
								v-if="item.activity && item.activity.type === '2'">砍价</span>
							<span class="pictrue_log_big pictrue_log_class"
								v-if="item.activity && item.activity.type === '3'">拼团</span>
							<view class="info">
								<view class="title line1">{{ item.store_name }}</view>
								<view class="price-box">
									<text>￥</text>
									{{ item.price }}
								</view>
							</view>
						</view>
					</view>
					<Loading :loaded="loaded" :loading="loading"></Loading>
					<view class="" v-if="goodList.length == 0 && loaded">
						<view class="empty-box">
							<image src="/static/images/noShopper.png"></image>
						</view>
						<recommend :hostProduct="hostProduct"></recommend>
					</view>
				</view>
				<!-- #endif -->
				<!-- #ifndef  APP-PLUS -->
				<view class="sort-product" v-if="isSortType == 1" :style="{ marginTop: sortMpTop + 'px' }">
					<scroll-view scroll-x="true" style="background: #fff;">
						<view class="sort-box" v-if="sortList.children && sortList.children.length">
							<view class="sort-item" v-for="(item, index) in sortList.children" :key="index"
								@click="changeSort(item, index)" :class="{ on: curSort == index }">
								<image :src="item.pic" mode="" v-if="item.pic"></image>
								<image src="/static/images/sort-img.png" mode="" v-else></image>
								<view class="txt">{{ item.cate_name }}</view>
							</view>
						</view>
					</scroll-view>
					<view class="product-list" v-if="goodList.length">
						<view class="product-item" v-for="(item, index) in goodList" @click="goGoodsDetail(item)">
							<image :src="item.image"></image>
							<span class="pictrue_log_big pictrue_log_class"
								v-if="item.activity && item.activity.type === '1'">秒杀</span>
							<span class="pictrue_log_big pictrue_log_class"
								v-if="item.activity && item.activity.type === '2'">砍价</span>
							<span class="pictrue_log_big pictrue_log_class"
								v-if="item.activity && item.activity.type === '3'">拼团</span>
							<span class="pictrue_log_big pictrue_log_class" v-if="item.checkCoupon">券</span>
							<view class="info">
								<view class="title line2">{{ item.store_name }}</view>
								<view class="price-box">
									<text>￥</text>
									{{ item.price }}
								</view>
							</view>
						</view>
					</view>
					<Loading :loaded="loaded" :loading="loading"></Loading>
					<view class="" v-if="goodList.length == 0 && loaded">
						<view class="empty-box">
							<image src="/static/images/noShopper.png"></image>
						</view>
						<recommend :hostProduct="hostProduct"></recommend>
					</view>
				</view>
				<!-- #endif -->
				<!-- 	<view class="loadingicon acea-row row-center-wrapper" v-if="tempArr.length && styleConfig[styleConfig.length - 1].name == 'promotionList'">
					<text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
					{{ loadTitle }}
				</view> -->
				<!-- #ifdef MP -->
				<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse" :isGoIndex="false"></authorize> -->
				<!-- #endif -->
				<couponWindow :window="isCouponShow" @onColse="couponClose" :couponImage="couponObj.image"
					:couponList="couponObj.list"></couponWindow>
				<view class="uni-p-b-98" v-if="footerStatus"></view>
				<view v-if="site_config" class="site-config" @click="goICP">{{ site_config }}</view>
				<!-- <pageFooter v-if="footerStatus"></pageFooter> -->
				<view class="foot" v-if="newData.status && newData.status.status">
					<view class="page-footer" id="target" :style="{'background-color':newData.bgColor.color[0].item}">
						<view class="foot-item" v-for="(item,index) in newData.menuList" :key="index"
							@click="goRouter(item)">
							<block v-if="item.link == activeRouter">
								<image :src="item.imgList[0]"></image>
								<view class="txt" :style="{color:newData.activeTxtColor.color[0].item}">{{item.name}}
								</view>
							</block>
							<block v-else>
								<image :src="item.imgList[1]"></image>
								<view class="txt" :style="{color:newData.txtColor.color[0].item}">{{item.name}}</view>
							</block>
							<div class="count-num"
								v-if="item.link === '/pages/order_addcart/order_addcart' && countNum > 0">
								{{countNum}}
							</div>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view v-else>
			<view class="error-network">
				<image src="/static/images/error-network.png"></image>
				<view class="title">网络连接断开</view>
				<view class="con">
					<view class="label">请检查情况：</view>
					<view class="item">· 在设置中是否已开启网络权限</view>
					<view class="item">· 当前是否处于弱网环境</view>
					<view class="item">· 版本是否过低，升级试试吧</view>
				</view>
				<view class="btn" @click="reconnect">重新连接</view>
			</view>
		</view>
		<!-- #ifdef APP-PLUS -->
		<app-update v-if="!privacyStatus" ref="appUpdate" :force="true" :tabbar="false"></app-update>
		<view class="privacy-wrapper" v-if="privacyStatus">
			<view class="privacy-box">
				<view class="title">服务协议与隐私政策</view>
				<view class="content">
					请务必审慎阅读、充分理解“服务协议与 隐私政策”各条款，包括但不限于：为了 向你提供即时通讯、内容分享等服务，我 们需要收集你的设备信息、操作日志等个 人信息。你可以在“设置”中查看、变更、
					删除个人信息并管理你的授权。<br>
					你可以阅读<navigator url="/pages/users/privacy/index?type=3">《服务协议与隐私政策》</navigator>了解
					详细信息。如你同意，请点击“我同意”开始接受我们的服务。
				</view>
				<view class="btn-box">
					<view class="btn-item" @click="confirmApp">我同意</view>
					<view class="btn" @click="closeModel">残忍拒绝</view>
				</view>
			</view>
		</view>
		<!-- #endif -->
	</view>
</template>

<script>
	const app = getApp();
	import colors from "@/mixins/color";
	import couponWindow from '@/components/couponWindow/index';
	import {
		getCouponV2,
		getCouponNewUser,
		siteConfig
	} from '@/api/api.js';
	import {
		getNavigation
	} from '@/api/public.js';
	// #ifdef H5
	import mConfig from './components/index.js';
	import {
		silenceAuth
	} from '@/api/public.js';
	// #endif
	// #ifdef MP || APP-PLUS
	import authorize from '@/components/Authorize';
	import activeParty from './components/activeParty';
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
	import richText from './components/richText';
	import seckill from './components/seckill';
	import swiperBg from './components/swiperBg';
	import tabNav from './components/tabNav';
	import titles from './components/titles';
	import appUpdate from "@/components/update/app-update.vue";
	import {
		getTemlIds
	} from '@/api/api.js';
	import {
		SUBSCRIBE_MESSAGE,
		TIPS_KEY
	} from '@/config/cache';

	// #endif
	import {
		mapGetters
	} from 'vuex';
	import {
		getDiy,
		getIndexData
	} from '@/api/api.js';
	import {
		getGroomList,
		getCategoryList,
		getProductslist,
		getProductHot
	} from '@/api/store.js';
	import {
		goShopDetail
	} from '@/libs/order.js';
	import {
		getCartCounts,
	} from '@/api/order.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import pageFooter from '@/components/pageFooter/index.vue';
	import Loading from '@/components/Loading/index.vue';
	import recommend from '@/components/recommend';
	export default {
		computed: mapGetters(['isLogin', 'uid']),
		mixins: [colors],
		components: {
			recommend,
			Loading,
			pageFooter,
			couponWindow,
			// #ifdef H5
			...mConfig,
			// #endif
			// #ifdef MP || APP-PLUS
			authorize,
			activeParty,
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
			richText,
			seckill,
			swiperBg,
			tabNav,
			titles,
			appUpdate, //APP更新
			// #endif
		},
		computed: mapGetters(['isLogin', 'cartNum']),
		data() {
			return {
				showSkeleton: true, //骨架屏显示隐藏
				isNodes: 0, //控制什么时候开始抓取元素节点,只要数值改变就重新抓取
				styleConfig: [],
				tempArr: [],
				goodType: 3,
				loading: false,
				loadend: false,
				loadTitle: '下拉加载更多', //提示语
				page: 1,
				limit: this.$config.LIMIT,
				iSshowH: false,
				numConfig: 0,
				code: '',
				isCouponShow: false,
				couponObj: {},
				couponObjs: {
					show: false
				},
				shareInfo: {},
				footConfig: {},
				isSortType: 0,
				sortList: '',
				sortAll: [],
				goodPage: 1,
				goodList: [],
				newData: {},
				sid: 0,
				curSort: 0,
				sortMpTop: 0,
				loaded: false,
				loading: false,
				hostProduct: [],
				hotScroll: false,
				hotPage: 1,
				hotLimit: 10,
				domOffsetTop: 50,
				// #ifdef APP-PLUS || MP
				isFixed: true,
				// #endif

				// #ifdef H5
				isFixed: false,
				// #endif
				site_config: '',
				errorNetwork: false, // 是否断网
				privacyStatus: false, // 隐私政策是否同意过
				footerStatus: false,
				isHeaderSerch: false,
				bgColor: '',
				bgPic: '',
				bgTabVal: '',
				pageShow: true,
				windowHeight: 0,
				activeRouter: '',
				countNum: 0
			};
		},
		onPullDownRefresh() {
			this.diyData();
		},
		created(options) {
			let that = this
			this.$nextTick(function() {
				uni.getSystemInfo({
					success: function(res) {
						that.windowHeight = res.windowHeight;
					}
				});
			})
			getNavigation().then(res => {
				this.newData = res.data
				if (this.newData.status && this.newData.status.status) {
					uni.hideTabBar()
				} else {
					uni.showTabBar()
				}
			})
			let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
			let curRoute = routes[routes.length - 1].route //获取当前页面路由
			this.activeRouter = '/' + curRoute
			// #ifdef APP-PLUS
			uni.setStorageSync("privacyStatus", true);
			// try {
			// 	let val = uni.getStorageSync('privacyStatus') || false
			// 	if (!val) {
			// 		this.privacyStatus = true
			// 	}
			// } catch (e) {}
			// #endif
			this.diyData();
			this.getIndexData();
			// #ifdef MP
			this.getTemlIds();
			// #endif
			// #ifndef APP-PLUS
			siteConfig().then(res => {
				this.site_config = res.data.record_No
			}).catch(err => {
				console.error(err.msg);
			});
			// #endif

			// 优惠券弹窗
			// var newDates = new Date().toLocaleDateString();
			if (this.isLogin) {
				this.getCoupon();
				getCartCounts().then(res => {
					this.countNum = res.data.count
					this.$store.commit('indexData/setCartNum', res.data.count > 99 ? '..' : res.data.count +
						'')
					if (res.data.count > 0) {
						wx.setTabBarBadge({
							index: Number(uni.getStorageSync('FOOTER_ADDCART')) || 2,
							text: res.data.count + ''
						})
					} else {
						wx.hideTabBarRedDot({
							index: Number(uni.getStorageSync('FOOTER_ADDCART')) || 2
						})
					}
				});
			}
		},
		// onReady() {
		// 	let that = this
		// 	uni.getSystemInfo({
		// 		success: function(res) { // res - 各种参数
		// 			let info = uni.createSelectorQuery().select(".hander"); // 获取某个元素
		// 			info.boundingClientRect(function(data) { //data - 各种参数
		// 				let view = res.windowHeight - data.height
		// 				that.heightHome = view
		// 			}).exec()
		// 		}
		// 	});
		// },
		watch: {
			isLogin: {
				deep: true, //深度监听设置为 true
				handler: function(newV, oldV) {
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
		onReady() {},
		methods: {
			// #ifdef APP-PLUS
			// 同意隐私协议
			confirmApp() {
				uni.setStorageSync('privacyStatus', true)
				this.privacyStatus = false
			},
			// 关闭Model
			closeModel() {
				//退出app
				uni.getSystemInfo({
					success: function(res) { // 判断为安卓的手机 
						if (res.platform == 'android') { // 安卓退出app      
							plus.runtime.quit();
						} else { // 判断为ios的手机，退出App      
							plus.ios.import("UIApplication").sharedApplication().performSelector("exit");

						}
					}
				})
			},
			// #endif
			// 重新链接
			reconnect() {
				uni.showLoading({
					title: '加载中'
				})
				this.diyData();
				this.getIndexData();
				getShare().then(res => {
					this.shareInfo = res.data;
				});
			},
			goICP() {
				// #ifdef H5
				window.open('http://beian.miit.gov.cn/');
				// #endif
				// #ifdef MP
				uni.navigateTo({
					url: `/pages/annex/web_view/index?url=https://beian.miit.gov.cn/`
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
				goShopDetail(item, this.uid).then(res => {
					uni.navigateTo({
						url: `/pages/goods_details/index?id=${item.id}`
					});
				});
			},
			/**
			 * 获取我的推荐
			 */
			get_host_product: function() {
				let that = this;
				if (that.hotScroll) return;
				getProductHot(that.hotPage, that.hotLimit).then(res => {
					that.hotPage++;
					that.hotScroll = res.data.length < that.hotLimit;
					that.hostProduct = that.hostProduct.concat(res.data);
					// that.$set(that, 'hostProduct', res.data)
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
			// 获取分类id
			bindSortId(data) {
				this.isSortType = data == -99 ? 0 : 1;
				this.getProductList(data);
				if (this.hostProduct.length == 0) {
					this.get_host_product();
				}
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
					getCategoryList().then(res => {
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
				}).then(res => {
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
					getCouponNewUser().then(res => {
						const {
							data
						} = res;
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
					getCouponV2().then(res => {
						const {
							data
						} = res;
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
			onLoadFun() {},
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
			getTemlIds() {
				let messageTmplIds = wx.getStorageSync(SUBSCRIBE_MESSAGE);
				if (!messageTmplIds) {
					getTemlIds().then(res => {
						if (res.data) wx.setStorageSync(SUBSCRIBE_MESSAGE, JSON.stringify(res.data));
					});
				}
			},
			// #endif
			// 对象转数组
			objToArr(data) {
				let obj = Object.keys(data);
				let m = obj.map(key => data[key]);
				return m;
			},
			diyData() {
				let that = this;
				getDiy(0).then(res => {
					setTimeout(() => {
						this.isNodes++;
					}, 0);
					this.errorNetwork = false
					let data = res.data;
					if (data.is_bg_color) {
						this.bgColor = data.color_picker
					}
					if (data.is_bg_pic) {
						this.bgPic = data.bg_pic
						this.bgTabVal = data.bg_tab_val
					}
					this.pageShow = data.is_show
					uni.setNavigationBarTitle({
						title: res.data.title
					});
					let temp = [];
					let lastArr = that.objToArr(res.data.value);
					lastArr.forEach((item, index, arr) => {
						if (item.name == 'headerSerch') {
							this.isHeaderSerch = true
						}
						if (item.name == 'pageFoot') {
							console.log(item.status)
							if (item.status && item.status.status) {
								this.newData = item
								setTimeout((e) => {
									that.$set(that, 'footerStatus', true);
									console.log(this.footerStatus)
								}, 50)
							}
							uni.setStorageSync('FOOTER_BAR', item.status && item.status.status ? true :
								false)
							item.menuList.map((path, index) => {
								if (path.link === '/pages/order_addcart/order_addcart') {
									uni.setStorageSync('FOOTER_ADDCART', index)
								}
							})
							arr.splice(index, 1);

						}
						if (item.name == 'promotionList') {
							that.numConfig = item.numConfig.val;
							that.goodType = item.tabConfig.list[0].link.activeVal;
							that.getGroomList();
						}
						if (item.name == 'tabNav') {
							// #ifndef APP-PLUS
							// uni.showLoading({
							// 	title: '加载中',
							// 	mask: true,
							// });
							// #endif
							// setTimeout(function() {
							// 	uni.hideLoading();
							// }, 8000);
						}
						temp = arr;
					});

					function sortNumber(a, b) {
						return a.timestamp - b.timestamp;
					}
					temp.sort(sortNumber)
					that.styleConfig = temp;
					setTimeout(() => {
						this.showSkeleton = false
					}, 300)
					uni.stopPullDownRefresh({
						success: (e) => {},
					});

				}).catch(error => {
					// #ifdef APP-PLUS
					if (error.status) {
						uni.hideLoading()
						if (that.errorNetwork) {
							uni.showToast({
								title: '连接失败',
								icon: 'none',
								duration: 2000
							})
						}
						this.errorNetwork = true
						this.showSkeleton = false;
					}
					// #endif
				});
			},
			getIndexData() {},
			changeBarg(item) {
				if (!this.isLogin) {
					toLogin();
				} else {
					uni.navigateTo({
						url: `/pages/activity/goods_bargain_details/index?id=${item.id}&bargain=${this.$store.state.app.uid}`
					});
				}
			},
			// 促销列表的点击事件；
			changeTab(type) {
				this.goodType = type;
				this.tempArr = [];
				this.page = 1;
				this.loadend = false;
				let onloadH = true;
				this.getGroomList(onloadH);
			},
			// 精品推荐
			getGroomList(onloadH) {
				let that = this;
				let type = that.goodType;
				if (that.loadend) return false;
				if (that.loading) return false;
				if (onloadH) {
					that.$set(that, 'iSshowH', true);
				}
				getGroomList(type, {
						page: that.page,
						limit: this.numConfig
					})
					.then(({
						data
					}) => {
						that.$set(that, 'iSshowH', false);
						let maxPage = Math.ceil(this.numConfig / this.limit);
						let list = data.list,
							loadend = list.length < that.limit || that.page >= maxPage;
						let tempArr = that.$util.SplitArray(list, that.tempArr);
						that.$set(that, 'tempArr', tempArr.slice(0, this.numConfig));
						that.loadend = loadend;
						that.loadTitle = loadend ? '没有更多内容啦~' : '加载更多';
						that.page = that.page + 1;
						that.loading = false;
					})
					.catch(res => {
						that.loading = false;
						that.loadTitle = '加载更多';
					});
			},
			goRouter(item) {
				var pages = getCurrentPages();
				var page = (pages[pages.length - 1]).$page.fullPath;
				if (item.link == page) return
				uni.switchTab({
					url: item.link,
					fail(err) {
						uni.redirectTo({
							url: item.link
						})
					}
				})
			},
			goDetail(item) {
				goShopDetail(item, this.$store.state.app.uid).then(res => {
					uni.navigateTo({
						url: `/pages/goods_details/index?id=${item.id}`
					});
				});
			},
			onsollBotton() {
				console.log('????', this.isSortType)
				if (this.isSortType == 0) {
					// this.getGroomList();
				} else {
					this.getGoodsList();
				}
			}
		},

		onReachBottom: function() {

		},
		onPageScroll(e) {
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
				this.isFixed = false
			}
			// #endif
		},
		//#ifdef MP
		onShareAppMessage() {
			return {
				title: this.shareInfo.title,
				path: '/pages/index/index'
			};
		},
		//分享到朋友圈
		onShareTimeline: function() {
			return {
				title: this.shareInfo.title,
				imageUrl: this.shareInfo.img
			};
		}
		//#endif
	};
</script>

<style lang="scss">
	// page {
	// 	padding-bottom: 50px;
	// }
	.pictrue_log_class {
		background-color: var(--view-theme);
	}

	.page {
		padding-bottom: 50px;
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

	.privacy-wrapper {
		z-index: 999;
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		background: #7F7F7F;


		.privacy-box {
			position: absolute;
			left: 50%;
			top: 50%;
			transform: translate(-50%, -50%);
			width: 560rpx;
			padding: 50rpx 45rpx 0;
			background: #fff;
			border-radius: 20rpx;

			.title {
				text-align: center;
				font-size: 32rpx;
				text-align: center;
				color: #333;
				font-weight: 700;
			}

			.content {
				margin-top: 20rpx;
				line-height: 1.5;
				font-size: 26rpx;
				color: #666;

				navigator {
					display: inline-block;
					color: #E93323;
				}
			}

			.btn-box {
				margin-top: 40rpx;
				text-align: center;
				font-size: 30rpx;

				.btn-item {
					height: 82rpx;
					line-height: 82rpx;
					background: linear-gradient(90deg, #F67A38 0%, #F11B09 100%);
					color: #fff;
					border-radius: 41rpx;
				}

				.btn {
					padding: 30rpx 0;
				}
			}
		}
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
			border: 1px solid #D74432;
			color: #E93323;
			font-size: 30rpx;
			border-radius: 120rpx;
		}
	}

	.sort-product {
		margin-top: 20rpx;

		.sort-box {
			display: flex;
			width: 100%;
			border-radius: 16rpx;
			padding: 30rpx 0;

			.sort-item {
				width: 20%;
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				flex-shrink: 0;

				image {
					width: 90rpx;
					height: 90rpx;
					border-radius: 50%;
				}

				.txt {
					color: #272727;
					font-size: 24rpx;
					margin-top: 10rpx;
					overflow: hidden;
					white-space: nowrap;
					text-overflow: ellipsis;
					width: 140rpx;
					text-align: center;
				}

				.pictrues {
					width: 90rpx;
					height: 90rpx;
					background: #f8f8f8;
					border-radius: 50%;
					margin: 0 auto;
				}

				.icon-gengduo1 {
					color: #333;
				}

				&.on {
					.txt {
						color: #fc4141;
					}

					image {
						border: 1px solid #fc4141;
					}
				}
			}
		}

		.product-list {
			display: flex;
			flex-wrap: wrap;
			justify-content: space-between;
			margin-top: 30rpx;
			padding: 0 20rpx;

			.product-item {
				position: relative;
				width: 344rpx;
				background: #fff;
				border-radius: 10rpx;
				margin-bottom: 20rpx;
				display: flex;
				flex-direction: column;

				image {
					width: 100%;
					height: 344rpx;
					border-radius: 10rpx 10rpx 0 0;
				}

				.info {
					flex: 1;
					padding: 14rpx 16rpx;
					display: flex;
					flex-direction: column;
					justify-content: space-between;

					.title {
						font-size: 28rpx;
					}

					.price-box {
						font-size: 34rpx;
						font-weight: 700;
						margin-top: 8px;
						color: #fc4141;

						text {
							font-size: 26rpx;
						}
					}
				}
			}
		}
	}

	.empty-box {
		text-align: center;

		image {
			width: 414rpx;
			height: 336rpx;
		}
	}

	.site-config {
		margin-top: 40rpx;
		font-size: 24rpx;
		text-align: center;
		color: #666;

		&.fixed {
			position: fixed;
			bottom: 69px;
			left: 0;
			width: 100%;
		}
	}

	.page-footer {
		position: fixed;
		bottom: 0;
		z-index: 30;
		display: flex;
		align-items: center;
		justify-content: space-around;
		width: 100%;
		height: calc(98rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		height: calc(98rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		box-sizing: border-box;
		border-top: solid 1rpx #F3F3F3;
		background-color: #fff;
		box-shadow: 0px 0px 17rpx 1rpx rgba(206, 206, 206, 0.32);
		padding-bottom: constant(safe-area-inset-bottom); ///兼容 IOS<11.2/
		padding-bottom: env(safe-area-inset-bottom); ///兼容 IOS>11.2/

		.foot-item {
			display: flex;
			width: max-content;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			position: relative;

			.count-num {
				position: absolute;
				display: flex;
				justify-content: center;
				align-items: center;
				width: 40rpx;
				height: 40rpx;
				top: 0rpx;
				right: -15rpx;
				color: #fff;
				font-size: 20rpx;
				background-color: #FD502F;
				border-radius: 50%;
				padding: 4rpx;
			}
		}

		.foot-item image {
			height: 50rpx;
			width: 50rpx;
			text-align: center;
			margin: 0 auto;
		}

		.foot-item .txt {
			font-size: 24rpx;


			&.active {}
		}
	}
</style>
