<template>
	<view v-if="pageShow" class="page"
		:class="bgTabVal==2?'fullsize noRepeat':bgTabVal==1?'repeat ysize':'noRepeat ysize'" :style="[pageStyle]">
		<view :style="colorStyle">
			<!-- 轮播搜索 -->
			<homeComb v-if="showHomeComb" :dataConfig="homeCombData" :belongIndex='belongIndex' :special='1' @bindSortId="bindSortId" :isScrolled="isScrolled"  @storeTap="storeTap"></homeComb>
			<!-- 顶部搜索框 -->
			<headerSerch v-if="isHeaderSerch" :dataConfig="headerSerchCombData" :belongIndex='belongIndex' :special='1'  @storeTap="storeTap"></headerSerch>
			<tabNav v-if="showCateNav" :dataConfig="cateNavData" @bindHeight="bindHeighta"
				@bindSortId="bindSortId" :special='1' :isFixed="isFixed && !cateNavData.stickyConfig.tabVal"></tabNav>
			<view class="index">
				<!-- 自定义样式 -->
				<block v-for="(item, index) in styleConfig" :key="index">
					<!-- <homeComb v-if="item.name == 'homeComb'" :dataConfig="item" @bindSortId="bindSortId"
						:isScrolled="isScrolled" :special='1'></homeComb> -->
					<!-- <headerSerch v-if="item.name == 'headerSerch'" :dataConfig="item" :special='1'></headerSerch> -->
					<!-- 顶部选项卡 -->
					<!-- <tabNav v-if="item.name == 'tabNav'" :dataConfig="item" @bindHeight="bindHeighta"
						@bindSortId="bindSortId" :special='1' :isFixed="isFixed && !item.stickyConfig.tabVal"></tabNav> -->
					<userInfor v-if="item.name == 'userInfor'" :dataConfig="item" @changeLogin="changeLogin">
					</userInfor>
					<newVip v-if="item.name == 'newVip'" :dataConfig="item"></newVip>
					<!-- 文章列表 -->
					<articleList v-if="item.name == 'articleList'" :dataConfig="item"></articleList>
					<bargain v-if="item.name == 'bargain'" :dataConfig="item" @changeBarg="changeBarg"></bargain>
					<blankPage v-if="item.name == 'blankPage'" :dataConfig="item"></blankPage>
					<combination v-if="item.name == 'combination'" :dataConfig="item">
					</combination>
					<!-- 优惠券 -->
					<coupon v-if="item.name == 'coupon'" :dataConfig="item" @changeLogin="changeLogin"></coupon>
					<!-- 客户服务 -->
					<customerService v-if="item.name == 'customerService'" :dataConfig="item">
					</customerService>
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
					<pictureCube v-if="item.name == 'pictureCube'" :dataConfig="item">
					</pictureCube>
					<!-- 促销列表 -->
					<promotionList ref="promotionLists" v-if="item.name == 'promotionList'" :dataConfig="item"
					:productVideoStatus='product_video_status' :positionTop="positionTop">
					</promotionList>
					<richText v-if="item.name == 'richText'" :dataConfig="item"></richText>
					<videos v-if="item.name == 'videos'" :dataConfig="item"></videos>
					<seckill v-if="item.name == 'seckill'" :dataConfig="item"></seckill>
					<!-- 轮播图-->
					<swiperBg v-if="item.name == 'swiperBg'" :dataConfig="item"></swiperBg>
					<swipers v-if="item.name == 'swipers'" :dataConfig="item"></swipers>
					<!-- 标题 -->
					<titles v-if="item.name == 'titles'" :dataConfig="item"></titles>
					<ranking v-if="item.name == 'ranking'" :dataConfig="item"></ranking>
					<presale v-if="item.name == 'presale'" :dataConfig="item"></presale>
					<pointsMall v-if="item.name == 'pointsMall'" :dataConfig="item"></pointsMall>
					<signIn v-if="item.name == 'signIn'" :dataConfig="item"></signIn>
					<hotspot v-if="item.name == 'hotspot'" :dataConfig="item"></hotspot>
					<follow v-if="item.name == 'follow'" :dataConfig="item"></follow>
				</block>
				<!-- 分类商品模块 -->
				<!-- #ifndef  APP-PLUS -->
				<view class="sort-product px-20" v-if="sortList.children && sortList.children.length">
				<!-- #endif -->
					<!-- #ifdef  APP-PLUS -->
					<!-- 商品排序 -->
					<view class="sort-product px-20" :style="{ marginTop: sortMpTop + 'px' }"
						v-if="sortList.children && sortList.children.length">
					<!-- #endif -->
						<waterfallsFlow ref="waterfallsFlow" :wfList="goodList" :goDetail="'goDetail'" @itemTap="goDetail"></waterfallsFlow>
						<Loading :loaded="loaded" :loading="loading"></Loading>
						<view v-if="goodList.length == 0 && loaded" class="sort-scroll rd-16rpx">
							<view class="empty-box pb-24">
								<image :src="imgHost + '/statics/images/no-thing.png'"></image>
								<view class="tips">暂无商品，去看点别的吧</view>
							</view>
						</view>
					</view>
					<view :style="[pdHeights]" v-if="isFooter"></view>
					<pageFooter :isTabBar="false" :configData="tabBarData"></pageFooter>
				</view>
			</view>
		</view>
</template>

<script>
	const app = getApp();
	import colors from "@/mixins/color";
	import couponWindow from '@/components/couponWindow/index'
	import {
		getCouponV2,
		getCouponNewUser
	} from '@/api/api.js'
	import {
		getShare
	} from '@/api/public.js';
	// #ifdef H5
	import {
		silenceAuth
	} from '@/api/public.js';
	// #endif

	import userInfor from '@/pages/index/components/userInfor';
	import homeComb from '@/pages/index/components/homeComb';
	import newVip from '@/pages/index/components/newVip';
	import headerSerch from '@/pages/index/components/headerSerch';
	import swipers from '@/pages/index/components/swipers';
	import coupon from '@/pages/index/components/coupon';
	import articleList from '@/pages/index/components/articleList';
	import bargain from '@/pages/index/components/bargain';
	import blankPage from '@/pages/index/components/blankPage';
	import combination from '@/pages/index/components/combination';
	import customerService from '@/pages/index/components/customerService';
	import goodList from '@/pages/index/components/goodList';
	import guide from '@/pages/index/components/guide';
	import liveBroadcast from '@/pages/index/components/liveBroadcast';
	import menus from '@/pages/index/components/menus';
	import news from '@/pages/index/components/news';
	import pictureCube from '@/pages/index/components/pictureCube';
	import promotionList from '@/pages/index/components/promotionList';
	import richText from '@/pages/index/components/richText';
	import seckill from '@/pages/index/components/seckill';
	import swiperBg from '@/pages/index/components/swiperBg';
	import tabNav from '@/pages/index/components/tabNav';
	import titles from '@/pages/index/components/titles';
	import ranking from '@/pages/index/components/ranking';
	import presale from '@/pages/index/components/presale'
	import pointsMall from '@/pages/index/components/pointsMall';
	import videos from '@/pages/index/components/videos';
	import signIn from '@/pages/index/components/signIn';
	import hotspot from '@/pages/index/components/hotspot';
	import follow from '@/pages/index/components/follow';
	import waterfallsFlow from "@/components/WaterfallsFlow/WaterfallsFlow.vue";
	// #ifdef MP
	import {
		getTempIds
	} from '@/api/api.js';
	import {
		SUBSCRIBE_MESSAGE,
		TIPS_KEY
	} from '@/config/cache';
	// #endif
	import {
		mapGetters,
		mapMutations
	} from 'vuex';
	import {
		getDiy,
		getDiyVersion,
		getEntryStore
	} from '@/api/api.js';
	import {
		getCategoryList,
		getProductslist,
		getProductHot,
	} from '@/api/store.js';
	import {
		goShopDetail
	} from '@/libs/order.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	import pageFooter from '@/components/pageFooter/index.vue'
	import Loading from '@/components/Loading/index.vue';
	import Cache from '@/utils/cache';
	export default {
		computed: {
			pageStyle() {
				return {
					backgroundColor: this.bgColor,
					backgroundImage: this.bgPic ? `url(${this.bgPic})` : '',
					minHeight: this.windowHeight + 'px'
				}
			},
			pdHeights(){
				let H = `${this.pdHeight*2 + 100}rpx`
				return{
					height: this.isFooter?H:'100rpx'
				}
			},
			...mapGetters(['isLogin', 'uid']),
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
			richText,
			seckill,
			swiperBg,
			tabNav,
			titles,
			ranking,
			presale,
			pointsMall,
			videos,
			signIn,
			hotspot,
			follow,
			waterfallsFlow
		},
		data() {
			return {
				isFixed: false,
				isHeaderSerch: false,
				showHomeComb: false,
				showCateNav: false,
				homeCombData:{},
				headerSerchCombData:{},
				cateNavData:{},
				domOffsetTop: 50,
				styleConfig: [],
				loading: false,
				loadend: false,
				loadTitle: '加载更多', //提示语
				page: 1,
				limit: this.$config.LIMIT,
				numConfig: 0,
				code: '',
				isCouponShow: false,
				couponObj: {},
				couponObjs: {},
				shareInfo: {},
				footConfig: {},
				pageId: '',
				sortMpTop: 0,
				bgColor: '',
				bgPic: '',
				bgTabVal: '',
				pageShow: true,
				windowHeight: 0,
				isShowAuth: false,
				isScrolled: false,
				sortList: '',
				sortAll: [],
				isSortType: 0,
				hostProduct: [],
				hotScroll: false,
				hotPage: 1,
				hotLimit: 10,
				curSort: 0,
				loaded: false,
				goodPage: 1,
				goodList: [],
				sid: 0,
				positionTop: 0,
				imgHost: HTTP_REQUEST_URL,
                product_video_status: false,
				isFooter: false,
				pdHeight:0, //自定义底部导航上下边距和
				entryData:{
					store_id:'',
					latitude:'',
					longitude:'',
					select_store_id:''
				},
				goodsIndex: [],
				promotionIndex: [],
				belongIndex:0, // 进店规则归属门店排序位置；
				isBelongStore: false, //判断是否为归属门店；
				tabBarData:{},
			};
		},
		onLoad(options) {
			let that = this
			this.$nextTick(function() {
				uni.getSystemInfo({
					success: function(res) {
						that.windowHeight = res.windowHeight;
					}
				});
			})
			const {
				state,
				scope
			} = options;
			this.pageId = options.id
			// #ifdef MP
			if (options.scene) {
				let value = that.$util.getUrlParams(decodeURIComponent(options.scene));
				this.pageId = value.id
			}
			// #endif
			uni.setNavigationBarTitle({
				title: '专题栏'
			});

			// #ifdef APP-PLUS
			this.sortMpTop = -50
			// #endif
			this.diyData();
			// #ifdef H5
			this.setOpenShare();
			// #endif
			// #ifdef MP || APP-PLUS
			this.getTempIds();
			// #endif
			getShare().then(res => {
				this.shareInfo = res.data;
			})
		},
		onUnload() {
			// 清除监听
			uni.$off('activeFn');
		},
		watch: {
			isLogin: {
				deep: true, //深度监听设置为 true
				handler: function(newV, oldV) {
					// 优惠券弹窗
					var newDates = new Date().toLocaleDateString();
					if (newV) {
						try {
							var oldDate = uni.getStorageSync('oldDate') || ''
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
			var newDates = new Date().toLocaleDateString();
			if (this.isLogin) {
				try {
					var oldDate = uni.getStorageSync('oldDate') || ''
				} catch {}
				if (oldDate != newDates) {
					this.getCoupon();
				}
				let oldUser = uni.getStorageSync('oldUser') || 0;
				if (!oldUser) {
					this.getCouponOnce();
				}
			}
		},
		mounted() {},
		methods: {
			...mapMutations(['SET_NEARBY']),
			locationTap(val){
				this.entryData.latitude = val.latitude;
				this.entryData.longitude = val.longitude;
				this.entryStore(1);
			},
			storeTap(id){
				this.entryData.select_store_id = id;
				this.entryData.store_id = '';
				uni.removeStorageSync('rulesStoreId');
				this.entryStore(1);
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
				type: 1   0 商品分类 1 微页面
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
				that.styleConfig = []
				uni.showLoading({
					title: '加载中...'
				});
				getDiy(id).then(res => {
					uni.hideLoading();
					let data = res.data;
					let diyArr = that.objToArr(res.data.value);
					diyArr = diyArr.filter(item => !item.isHide);
					diyArr.forEach((item,index) => {
					  if(['headerSerch','homeComb'].includes(item.name)){
					    diyArr.splice(index, 1);
					  }
					});
					this.styleConfig = diyArr;
				}).catch(err => {
					return that.$util.Tips({
						title: err
					});
					uni.hideLoading();
				});
			},
			getProductList(data) {
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
				}).then(res => {
					this.loading = false;
					this.loaded = res.data.length < 10;
					this.goodPage++;
					this.goodList = this.goodList.concat(res.data);
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
				});
			},
			// 新用户优惠券
			getCouponOnce() {
				getCouponNewUser().then(res => {
					this.couponObjs = res.data;
				});
			},
			couponCloses() {
				this.couponObjs.show = false;
				try {
					uni.setStorageSync('oldUser', 1);
				} catch (e) {

				}
			},
			// 优惠券弹窗
			getCoupon() {
				getCouponV2().then(res => {
					this.couponObj = res.data
					if (res.data.list.length > 0) {
						this.isCouponShow = true
					}
				})
			},
			// 优惠券弹窗关闭
			couponClose() {
				this.isCouponShow = false
				try {
					uni.setStorageSync('oldDate', new Date().toLocaleDateString());
				} catch {}
			},
			// #ifdef H5
			// 获取url后面的参数
			getQueryString(name) {
				var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
				var reg_rewrite = new RegExp("(^|/)" + name + "/([^/]*)(/|$)", "i");
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

			// #ifdef MP || APP-PLUS
			getTempIds() {
				let messageTmplIds = wx.getStorageSync(SUBSCRIBE_MESSAGE);
				if (!messageTmplIds) {
					// getTempIds().then(res => {
					// 	if (res.data) wx.setStorageSync(SUBSCRIBE_MESSAGE, JSON.stringify(res.data));
					// });
				}
			},
			// #endif
			// 对象转数组
			objToArr(data) {
				const keys = Object.keys(data)
				keys.sort((a, b) => a - b)
				const m = keys.map(key => data[key]);
				return m;
			},
			setDiyData(data) {
				if (data.length == 0) {
					return this.$util.Tips({
						title: '暂无数据'
					}, {
						tab: 3
					})
				}

				if (data.is_bg_color) {
					this.bgColor = data.color_picker
				}
				if (data.is_bg_pic) {
					this.bgPic = data.bg_pic
					this.bgTabVal = data.bg_tab_val
				}
				this.pageShow = data.is_show
				uni.setNavigationBarTitle({
					title: data.title
				})
				let temp = []
				let goodsIndex = [];
				let promotionIndex = [];
				let lastArr = this.objToArr(data.value)
				lastArr.forEach((item, index, arr) => {
					if (item.name === 'homeComb' && !item.isHide) {
						this.showHomeComb = true;
						this.homeCombData = item;
						if (item.searchConfig.tabVal) {
							this.positionTop = 43
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
					if(item.name == 'goodList' && !item.isHide){
						goodsIndex.push(index)
					}
					if(item.name == 'promotionList' && !item.isHide){
						promotionIndex.push(index)
					}
					if (item.name == 'pageFoot') {
						this.tabBarData = item;
						this.isFooter = item.effectConfig.tabVal?true:false
						this.pdHeight = item.topConfig.val + item.bottomConfig.val
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
				this.entryStore();
			},
			getDiyData() {
				getDiy(this.pageId).then(res => {
					uni.setStorageSync('specialDiyData', JSON.stringify(res.data));
					this.setDiyData(res.data);
				});
			},
			diyData() {
				this.getDiyData();
				// let that = this;
				// let diyData = uni.getStorageSync('specialDiyData');
				// if (diyData) {
				// 	getDiyVersion(this.pageId).then(res => {
				// 		let diyVersion = uni.getStorageSync('specialDiyVersion');
				// 		if ((res.data.version + this.pageId) === diyVersion) {
				// 			this.setDiyData(JSON.parse(diyData));
				// 		} else {
				// 			uni.setStorageSync('specialDiyVersion', (res.data.version + this.pageId));
				// 			this.getDiyData();
				// 		}
				// 	});
				// } else {
				// 	this.getDiyData();
				// }
			},
			entryStore(num){
				// num 更新门店或是位置时需要重新获取门店商品数据（针对得是单店模式）
				// this.entryData.store_id = Cache('rulesStoreId');
				// getEntryStore(this.entryData).then(res=>{
				// 	if(res.data.user_entry_name == 'user_belong_store'){
				// 		this.isBelongStore = true;
				// 	}
				// 	let storeId= res.data.store_id;
				// 	uni.setStorageSync('user_store_id', storeId);
				// 	uni.setStorageSync('shop_operation_type', res.data.shop_operation_type);
				// 	this.SET_NEARBY(storeId);
				// 	let data = {
				// 		store_id:storeId
				// 	}
				// 	let changeStore = true;
				// 	let entryRules = res.data.user_entry_rules;
				// 	entryRules.forEach((item,index)=>{
				// 		if(item.name == 'user_belong_store'){
				// 			this.belongIndex = index;
				// 			changeStore = item.is_change_store;
				// 		}
				// 	})
				// 	if(res.data.shop_operation_type !=1 && num){
				// 		this.goodsIndex.forEach((item,index)=>{
				// 			this.$refs.goodLists[index].productslist();
				// 		})
				// 		this.promotionIndex.forEach((item,index)=>{
				// 			this.$refs.promotionLists[index].$refs.goodLists.productslist();
				// 		})
				// 	}
				// }).catch(err=>{
				//    this.$util.Tips({
				// 	   title: err
				//    });
				// })
			},
			changeBarg(item) {
				if (!this.isLogin) {
					toLogin()
				} else {
					uni.navigateTo({
						url: `/pages/activity/goods_bargain_details/index?id=${item.id}&spid=${this.uid}`
					});
				}
			},
			goDetail(item) {
				goShopDetail(item, this.uid).then(res => {
					uni.navigateTo({
						url: `/pages/goods_details/index?id=${item.id}`
					});
				});
			},
			// #ifdef H5
			// 微信分享；
			setOpenShare: function() {
				let that = this;
				if (that.$wechat.isWeixin()) {
					getShare().then(res => {
						let data = res.data.data;
						let configAppMessage = {
							desc: data.synopsis,
							title: data.title,
							link: location.href,
							imgUrl: data.img
						};
						that.$wechat.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData'],
							configAppMessage);
					});
				}
			}
			// #endif
		},
		onReachBottom: function() {},
		onPageScroll(e) {
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
			if (e.scrollTop > 10) {
				this.isScrolled = true;
			} else {
				this.isScrolled = false;
			}
			uni.$emit('scroll');
			uni.$emit('onPageScroll', e.scrollTop);
		},
		//#ifdef MP || APP-PLUS
		onShareAppMessage() {
			return {
				title: this.shareInfo.title,
				path: '/pages/index/index',
				imageUrl: this.storeInfo.img,
			};
		},
		//#endif
	};
</script>

<style lang="scss">
	.sort-scroll {
		background-color: #fff;
	}

	.empty-box {
		text-align: center;
		padding-top: 50rpx;

		.tips {
			color: #aaa;
			font-size: 26rpx;
		}

		image {
			width: 414rpx;
			height: 304rpx;
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
				overflow: hidden;

				.pictrue {
					position: relative;
				}

				image {
					width: 100%;
					height: 344rpx;
					border-radius: 10rpx 10rpx 0 0;
				}

				.info {
					padding: 14rpx 16rpx;

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
</style>
