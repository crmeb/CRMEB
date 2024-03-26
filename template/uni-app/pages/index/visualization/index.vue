<template>
	<view id="pageIndex" :style="colorStyle">
		<!-- <skeleton v-if="!isIframe" :show="showSkeleton" :isNodes="isNodes" ref="skeleton" loading="chiaroscuro"
			selector="skeleton" bgcolor="#FFF"></skeleton> -->
		<skeletons :show="showSkeleton" loading="chiaroscuro" bgcolor="#FFF"></skeletons>
		<!-- #ifdef H5 -->
		<view class="followMe" v-if="$wechat.isWeixin()">
			<view class="follow acea-row row-between-wrapper" v-if="followHid && followUrl && !subscribe">
				<view>{{$t(`点击“立即关注”即可关注公众号`)}}</view>
				<view class="acea-row row-middle">
					<view class="bnt" @click="followTap">{{$t(`立即关注`)}}</view>
					<span class="iconfont icon-guanbi" @click="closeFollow"></span>
				</view>
			</view>
			<view class="followCode" v-if="followCode">
				<view class="pictrue"><img :src="followUrl" /></view>
				<view class="mask" @click="closeFollowCode"></view>
			</view>
		</view>
		<!-- #endif -->
		<!-- #ifdef MP -->
		<view class="indexTip" :style="'top:' + (navH + 50) + 'px'" :hidden="iShidden">
			<view class="tip acea-row row-between-wrapper">
				<view class="text">{{$t(`点击`)}}“<image src="/static/images/spot.png"></image>
					”{{$t(`添加到我的小程序， 微信首页下拉即可访问商城。`)}}</view>
				<view class="iconfont icon-guanbi1" @click="closeTip"></view>
			</view>
		</view>
		<!-- #endif -->
		<!-- 顶部搜索 -->
		<view class="skeleton" id="pageIndexs" :style="{ visibility: showSkeleton ? 'hidden' : 'visible' }">
			<headerSerch class="mp-header skeleton" :dataConfig="headerSerch.default"
				@click.native="bindEdit('headerSerch', 'default')"></headerSerch>
			<!-- 轮播 -->
			<swiperBg :dataConfig="swiperBg.default" @click.native="bindEdit('swiperBg', 'default')"></swiperBg>
			<!-- 金刚区 -->
			<menus :dataConfig="menus.default" @click.native="bindEdit('menus', 'default')"></menus>
			<!-- 新闻简报 -->
			<news :dataConfig="news.default" @click.native="bindEdit('news', 'default')"></news>
			<!-- 活动魔方 -->
			<activity :dataConfig="activity.default" @click.native="bindEdit('activity', 'default')"></activity>
			<!-- 		<seckill :dataConfig="seckill.default" @click.native="bindEdit('seckill','default')"></seckill>
			<adsRecommend :dataConfig="adsRecommend.default" @click.native="bindEdit('adsRecommend','default')"></adsRecommend>
			<combination :dataConfig="combination.default" @click.native="bindEdit('combination','default')"></combination>
			<bargain :dataConfig="bargain.default" @click.native="bindEdit('bargain','default')"></bargain>
			<picTxt :dataConfig="picTxt.default" @click.native="bindEdit('picTxt','default')"></picTxt>	 -->
			<alive :dataConfig="alive.default" @click.native="bindEdit('alive', 'default')"></alive>
			<!-- 优惠券 -->
			<coupon :dataConfig="coupon.default" @click.native="bindEdit('coupon', 'default')"></coupon>
			<!-- 快速选择 -->
			<scrollBox :dataConfig="scrollBox.default" @click.native="bindEdit('scrollBox', 'default')"></scrollBox>
			<!-- 促销精品 -->
			<recommend :dataConfig="goodList.aa" @click.native="bindEdit('goodList', 'aa')"></recommend>
			<!-- 排行榜 -->
			<popular :dataConfig="goodList.bb" @click.native="bindEdit('goodList', 'bb')"></popular>
			<!-- 商品轮播 -->
			<mBanner :dataConfig="swiperBg.aa" @click.native="bindEdit('swiperBg', 'aa')"></mBanner>
			<!-- 首发新品 -->
			<newGoods :dataConfig="goodList.cc" @click.native="bindEdit('goodList', 'cc')"></newGoods>
			<!-- 精品推荐 -->
			<!-- <mBanner :dataConfig="swiperBg.cc" @click.native="bindEdit('swiperBg','cc')"></mBanner> -->
			<!-- <titles :dataConfig="titles.default" :sty="'off'" @click.native="bindEdit('titles','default')"></titles> -->
			<!-- 商品轮播 -->
			<!-- 		<customerService :dataConfig="customerService.default" @click.native="bindEdit('customerService','default')"></customerService> -->
			<!-- 精选单品 -->
			<promotion :dataConfig="goodList.dd" @click.native="bindEdit('goodList', 'dd')"></promotion>
			<!-- 商品分类 -->
			<tabNav class="sticky-box" :style="'top:' + isTop + 'px;'" :dataConfig="tabNav.default"
				@click.native="bindEdit('tabNav', 'default')" @bindSortId="bindSortId" @bindHeight="bindHeighta">
			</tabNav>
			<!-- 商品列表 -->
			<indexGoods v-if="!isIframe && tabNav.default && tabNav.default.isShow.val" :dataLists="goodLists"
				@click.native="bindEdit('List')"></indexGoods>
			<!-- <recommend :dataConfig="goodList.aa" @click.native="bindEdit('goodList','aa')"></recommend> -->
			<!-- <Loading class="loading-sty" :loaded="loaded" :loading="loading"></Loading> -->
			<view class="" v-if="
          !isIframe &&
          tabNav.default &&
          tabNav.default.isShow.val &&
          goodLists.length == 0
        ">
				<view class='emptyBox'>
					<image :src="imgHost + '/statics/images/no-thing.png'"></image>
					<view class="tips">{{$t(`暂无商品，去看点别的吧`)}}</view>
				</view>
			</view>
		</view>

		<tabBar :dataConfig="tabBar.default" :pagePath="'/pages/index/index'"
			@click.native="bindEdit('tabBar', 'default')"></tabBar>
		<!-- #ifdef H5 -->
		<view v-if="site_config.record_No" class="site-config" @click="goICP(1)">{{ site_config.record_No }}</view>
		<view v-if="site_config.network_security" class="site-config" @click="goICP(2)">{{ site_config.network_security }}</view>
		<!-- #endif -->
		<view class="uni-p-b-98"></view>
		<couponWindow style="position: relative; z-index: 10000" :window="isCouponShow" @onColse="couponClose"
			:couponImage="couponObj.image" :couponList="couponObj.list"></couponWindow>
		<!-- #ifdef APP-PLUS -->
		<app-update v-if="!privacyStatus" ref="appUpdate" :force="true" :tabbar="false"></app-update>
		<view class="privacy-wrapper" v-if="privacyStatus">
			<view class="privacy-box">
				<view class="title">{{$t(`服务协议与隐私政策`)}}</view>
				<view class="content">
					{{$t(`请务必审慎阅读、充分理解“服务协议与 隐私政策”各条款，包括但不限于：为了 向你提供即时通讯、内容分享等服务，我 们需要收集你的设备信息、操作日志等个 人信息。你可以在“设置”中查看、变更、删除个人信息并管理你的授权。`)}}<br>
					{{$t(`你可以阅读`)}}
					<navigator url="/pages/users/privacy/index?type=3">{{$t(`《服务协议与隐私政策》`)}}</navigator>
					{{$t(`了解详细信息。如你同意，请点击“我同意”开始接受我们的服务。`)}}
				</view>
				<view class="btn-box">
					<view class="btn-item" @click="confirmApp">{{$t(`我同意`)}}</view>
					<view class="btn" @click="closeModel">{{$t(`残忍拒绝`)}}</view>
				</view>
			</view>
		</view>
		<!-- #endif -->
	</view>
</template>
<script>
	import couponWindow from "@/components/couponWindow/index";
	import indexGoods from "@/components/indexGoods/index";
	import headerSerch from "./components/headerSerch";
	import swiperBg from "./components/swiperBg";
	import menus from "./components/menus";
	import news from "./components/news";
	import activity from "./components/activity";
	import scrollBox from "./components/scrollBox";
	import recommend from "./components/recommend";
	import popular from "./components/popular";
	import mBanner from "./components/mBanner";
	import newGoods from "./components/newGoods";
	import promotion from "./components/promotion";
	import alive from "./components/alive";
	import adsRecommend from "./components/adsRecommend";
	import coupon from "./components/coupon";
	import seckill from "./components/seckill";
	import combination from "./components/combination";
	import bargain from "./components/bargain";
	import goodList from "./components/goodList";
	import picTxt from "./components/picTxt";
	import titles from "./components/titles";
	import customerService from "./components/customerService";
	import tabBar from "./components/tabBar";
	import tabNav from "./components/tabNav";
	import appUpdate from "@/components/update/app-update.vue";
	import Loading from "@/components/Loading/index.vue";
	import {
		getShare,
		follow
	} from "@/api/public.js";
	// #ifdef MP || APP-PLUS
	import {
		SUBSCRIBE_MESSAGE,
		TIPS_KEY
	} from "@/config/cache";
	// #endif
	import {
		getTempIds,
		siteConfig
	} from "@/api/api.js";
	import {
		mapGetters
	} from "vuex";
	import {
		getDiy,
		getIndexData,
		getCouponV2,
		getCouponNewUser,
	} from "@/api/api.js";
	import {
		getGroomList,
		getCategoryList,
		getProductslist,
		getProductHot,
	} from "@/api/store.js";
	import {
		goShopDetail,
		goPage
	} from "@/libs/order.js";
	import {
		toLogin
	} from "@/libs/login.js";
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	import colors from "@/mixins/color";
	import skeletons from "./components/skeleton.vue";
	let app = getApp();
	let statusBarHeight = uni.getSystemInfoSync().statusBarHeight;
	export default {
		computed: mapGetters(["isLogin", "uid"]),
		components: {
			couponWindow,
			headerSerch,
			swiperBg,
			menus,
			news,
			activity,
			scrollBox,
			recommend,
			popular,
			mBanner,
			newGoods,
			promotion,
			alive,
			adsRecommend,
			coupon,
			seckill,
			combination,
			bargain,
			goodList,
			picTxt,
			titles,
			customerService,
			tabBar,
			tabNav,
			Loading,
			skeletons,
			indexGoods,
			appUpdate, //APP更新
		},
		mixins: [colors],
		data() {
			return {
				imgHost: HTTP_REQUEST_URL,
				showSkeleton: true, //骨架屏显示隐藏
				isNodes: 0, //控制什么时候开始抓取元素节点,只要数值改变就重新抓取
				isSortType: 0,
				sortList: {},
				sortAll: [],
				goodPage: 1,
				goodLists: [],
				curSort: 0,
				sortMpTop: 0,
				loaded: false,
				hostProduct: [],
				hotScroll: false,
				hotPage: 1,
				hotLimit: 10,
				followHid: true,
				followUrl: "",
				followCode: false,
				navH: statusBarHeight,
				subscribe: false,
				iShidden: false,
				goodType: 3,
				loading: false,
				loadend: false,
				loadTitle: this.$t(`下拉加载更多`), //提示语
				page: 1,
				limit: this.$config.LIMIT,
				numConfig: 0,
				couponObj: {},
				isCouponShow: false,
				shareInfo: {},
				site_config: "",
				isIframe: app.globalData.isIframe,
				headerSerch: {}, //头部搜索
				swiperBg: {}, //轮播
				menus: {}, //导航
				news: {}, //消息公告
				activity: {}, //活动魔方
				alive: {},
				scrollBox: {}, //快速选择分类
				titles: {}, //标题
				goodList: {}, //商品列表(商品列表、首发新品、热门榜单、促销单品、精品推荐)
				tabBar: {}, //导航
				customerService: {}, //客服
				picTxt: {}, //图文详情
				bargain: {}, //砍价
				combination: {}, //拼团
				adsRecommend: {}, //广告位
				seckill: {}, //秒杀
				coupon: {}, //优惠券
				tabNav: {}, //分类tab
				isBorader: "",
				domOffsetTop: 50,
				isTop: 0,
				privacyStatus: false, // 隐私政策是否同意过
				isFixed: false,
			};
		},

		created() {
			uni.hideTabBar();
			// #ifdef APP-PLUS
			uni.setStorageSync("privacyStatus", true);
			// try {
			// 	let val = uni.getStorageSync("privacyStatus") || false;
			// 	if (!val) {
			// 		this.privacyStatus = true;
			// 	}
			// } catch (e) {}
			// this.$nextTick(() => {
			// 	// this.$refs.appUpdate.update(); //调用子组件 检查更新
			// });

			// #endif
			let that = this;
			// #ifdef H5
			if (app.globalData.isIframe) {
				this.showSkeleton = false;
				setTimeout(() => {
					let active;
					document.getElementById("pageIndexs").children.forEach((dom) => {
						dom.addEventListener("click", (e) => {
							e.stopPropagation();
							e.preventDefault();
							if (dom === active) return;
							dom.classList.add("borderShow");
							active && active.classList.remove("borderShow");
							active = dom;
						});
					});
				}, 1000);
			}
			if (app.globalData.isIframe) {
				uni.hideTabBar();
			}
			this.getFollow();
			// #endif

			this.diyData();
			this.getIndexData();
			// #ifdef MP
			if (this.$Cache.get(TIPS_KEY)) this.iShidden = true;
			this.getTempIds();
			// #endif
			siteConfig()
				.then((res) => {
					this.site_config = res.data;
				})
				.catch((err) => {
					return this.$util.Tips({
						title: err.msg,
					});
				});
			// #ifdef APP-PLUS
			this.isTop = uni.getSystemInfoSync().statusBarHeight + 85;
			// #endif
			// #ifdef MP
			const query = uni.createSelectorQuery().in(this);
			query.select('.mp-header').boundingClientRect(data => {
				this.isTop = data.top;
			}).exec();
			// #endif
			// #ifdef H5
			this.isTop = 0;
			// #endif
			if (!app.globalData.isIframe) {
				if (this.isLogin) {
					this.getCoupon();
				}
			}
		},
		methods: {
			// #ifdef APP-PLUS
			// 同意隐私协议
			confirmApp() {
				uni.setStorageSync("privacyStatus", true);
				this.privacyStatus = false;
			},
			// 关闭Model
			closeModel() {
				//退出app
				uni.getSystemInfo({
					success: function(res) {
						// 判断为安卓的手机
						if (res.platform == "android") {
							// 安卓退出app
							plus.runtime.quit();
						} else {
							// 判断为ios的手机，退出App
							plus.ios
								.import("UIApplication")
								.sharedApplication()
								.performSelector("exit");
						}
					},
				});
			},
			// #endif
			bindEdit(name, dataName) {
				if (app.globalData.isIframe) {
					window.parent.postMessage({
							name: name,
							dataName: dataName,
							params: {},
						},
						"*"
					);
					return;
				}
			},
			getFollow() {
				follow()
					.then((res) => {
						this.followUrl = res.data.path;
					})
					.catch((err) => {
						return this.$util.Tips({
							title: err.msg,
						});
					});
			},
			followTap() {
				this.followCode = true;
				this.followHid = false;
			},
			closeFollow() {
				this.followHid = false;
			},
			closeFollowCode() {
				this.followCode = false;
				this.followHid = true;
			},
			closeTip: function() {
				this.$Cache.set(TIPS_KEY, true);
				this.iShidden = true;
			},
			bindHeighta(data) {
				// #ifdef APP-PLUS
				this.sortMpTop = data.top + data.height;
				// #endif
				// #ifdef H5
				this.domOffsetTop = data.top;
				// #endif
				// #ifndef H5
				this.domOffsetTop = data.top - 110;
				// #endif
			},
			// 优惠券弹窗
			getCoupon() {
				const tagDate = uni.getStorageSync("tagDate") || "",
					nowDate = new Date().toLocaleDateString();
				if (tagDate === nowDate) {
					this.getNewCoupon();
				} else {
					getCouponV2().then((res) => {
						const {
							data
						} = res;
						if (data.list.length) {
							this.isCouponShow = true;
							this.couponObj = data;
							uni.setStorageSync("tagDate", new Date().toLocaleDateString());
						} else {
							this.getNewCoupon();
						}
					});
				}
			},
			// 新用户优惠券
			getNewCoupon() {
				const oldUser = uni.getStorageSync("oldUser") || 0;
				if (!oldUser) {
					getCouponNewUser().then((res) => {
						const {
							data
						} = res;
						if (data.show) {
							if (data.list.length) {
								this.isCouponShow = true;
								this.couponObj = data;
								uni.setStorageSync("oldUser", 1);
							}
						} else {
							uni.setStorageSync("oldUser", 1);
						}
					});
				}
			},
			// 优惠券弹窗关闭
			couponClose() {
				this.isCouponShow = false;
				if (!uni.getStorageSync("oldUser")) {
					this.getNewCoupon();
				}
			},
			// #ifdef MP
			getTempIds() {
				getTempIds().then((res) => {
					if (res.data) {
						wx.setStorageSync(SUBSCRIBE_MESSAGE, JSON.stringify(res.data));
					}
				});
			},
			// #endif
			goICP(type) {
				let url = type == 1 ? this.site_config.icp_url : this.site_config.network_security_url;
				window.open(url);
			},
			onLoadFun() {},
			reconnect() {
				this.diyData();
				this.getIndexData();
			},
			diyData() {
				let that = this;
				getDiy().then((res) => {
					let data = res.data;
					that.headerSerch = data.headerSerch;
					that.swiperBg = data.swiperBg;
					that.menus = data.menus;
					that.news = data.news;
					that.activity = data.activity;
					that.alive = data.alive;
					that.scrollBox = data.scrollBox;
					that.titles = data.titles;
					that.goodList = data.goodList;
					that.tabNav = data.tabNav;
					that.tabBar = data.tabBar;
					that.customerService = data.customerService;
					that.picTxt = data.picTxt;
					that.bargain = data.bargain;
					that.combination = data.combination;
					that.adsRecommend = data.adsRecommend;
					that.seckill = data.seckill;
					that.coupon = data.coupon;
					this.$Cache.set("TAB_BAR", data.tabBar.default.tabBarList);
					setTimeout(() => {
						this.showSkeleton = false;
					}, 300);
					uni.setStorageSync('VIS_DATA', res.data)
				}).catch(error => {
					// #ifdef APP-PLUS
					if (error.status) {
						uni.showToast({
							title: this.$t(`连接失败`),
							icon: 'none',
							duration: 2000
						})
					}
					// #endif
				});
			},
			getIndexData() {
				getIndexData().then((res) => {
					this.subscribe = res.data.subscribe;
					// #ifdef H5
					localStorage.setItem("itemName", res.data.site_name);
					// #endif
					uni.setNavigationBarTitle({
						title: res.data.site_name,
					});
				});
			},
			/**
			 * 获取我的推荐
			 */
			get_host_product: function() {
				let that = this;
				if (that.hotScroll) return;
				getProductHot(that.hotPage, that.hotLimit).then((res) => {
					that.hotPage++;
					that.hotScroll = res.data.length < that.hotLimit;
					that.hostProduct = that.hostProduct.concat(res.data);
					// that.$set(that, 'hostProduct', res.data)
				});
			},
			// 获取分类id
			bindSortId(data) {
				this.isSortType = data == -99 ? 0 : 1;
				// this.goodLists = [];
				this.getProductList(data);
			},
			getProductList(data) {
				let tempObj = "";
				this.curSort = 0;
				this.loaded = false;
				this.goodPage = 1;
				this.getGoodsList(data);
			},
			getGoodsList(data) {
				if (this.loading || this.loaded) return;
				this.loading = true;
				getProductslist({
					keyword: "",
					priceOrder: "",
					salesOrder: "",
					news: 0,
					page: this.goodPage,
					limit: 10,
					cid: data,
				}).then((res) => {
					this.goodLists = res.data;
					this.loading = false;
					this.loaded = res.data.length < 10;
					this.goodPage++;
				});
			},
			goGoodsDetail(item) {
				goPage().then((res) => {
					goShopDetail(item, this.uid).then((res) => {
						uni.navigateTo({
							url: `/pages/goods_details/index?id=${item.id}`,
						});
					});
				});
			},
		},
		onReachBottom: function() {
			// if (this.isSortType == 0) {
			// 	// this.getGroomList();
			// } else {
			// 	this.getGoodsList();
			// }
		},
		onPageScroll(e) {
			// if (this.headerSerch.default.isShow.val) {
			//   // if (this.domOffsetTop == 50) return
			//   if (e.scrollTop > this.isTop) {
			//     this.isFixed = true;
			//   }
			//   if (e.scrollTop < this.isTop) {
			//     this.$nextTick(() => {
			//       this.isFixed = false;
			//     });
			//   }
			// } else {
			//   this.isFixed = false;
			// }
		},
	};
</script>
<style lang="scss" scoped>
	page {
		// background: linear-gradient(180deg, #fff, #fff 20%, #f5f5f5);
		// overflow-x: hidden;
		// overflow-y: scroll;
		// height: max-content;
	}

	.bac-col {
		width: 100%;
		height: 300rpx;
		position: absolute;
		background-image: linear-gradient(135deg, #f97794 10%, #623aa2 100%);
		top: 0;
		background: linear-gradient(90deg, #f62c2c 0%, #f96e29 100%);
	}

	.swiperCon {
		margin: 20rpx 0 !important;

		/* #ifdef MP */
		/deep/.swiperBg {
			margin: 20rpx 0 !important;
		}

		/* #endif */
		/deep/.swiper {
			swiper,
			.swiper-item,
			image {
				height: 190rpx !important;
			}
		}
	}

	.site-config {
		margin: 40rpx 0;
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

	/* #ifdef MP */
	.indexTip {
		position: fixed;
		right: 42rpx;
		z-index: 10000;

		.tip {
			width: 400rpx;
			border-radius: 6rpx;
			background-color: #fff;
			padding: 15rpx 22rpx;
			position: relative;

			&::before {
				content: "";
				width: 0;
				height: 0;
				border-left: 15rpx solid transparent;
				border-right: 15rpx solid transparent;
				border-bottom: 17rpx solid #fff;
				position: absolute;
				top: -14rpx;
				right: 95rpx;
			}
		}

		.text {
			font-size: 22rpx;
			color: #333;
			width: 320rpx;

			image {
				width: 30rpx;
				height: 16rpx;
				display: inline-block;
			}
		}

		.iconfont {
			color: #cdcdcd;
			font-size: 32rpx;
		}
	}

	/* #endif */

	/* #ifdef H5 */
	.follow {
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		background-color: rgba(0, 0, 0, 0.36);
		height: 80rpx;
		font-size: 28rpx;
		color: #fff;
		padding: 0 30rpx;
		z-index: 100000;

		.iconfont {
			font-size: 30rpx;
			margin-left: 29rpx;
		}

		.bnt {
			width: 160rpx;
			height: 50rpx;
			background-color: #e93323;
			border-radius: 25rpx;
			font-size: 24rpx;
			text-align: center;
			line-height: 50rpx;
		}
	}

	.followCode {
		.mask {
			z-index: 10000;
		}

		.pictrue {
			width: 500rpx;
			height: 720rpx;
			border-radius: 12px;
			left: 50%;
			top: 50%;
			margin-left: -250rpx;
			margin-top: -360rpx;
			position: fixed;
			z-index: 10001;

			img {
				width: 100%;
				height: 100%;
			}
		}
	}

	/* #endif */
	.privacy-wrapper {
		z-index: 9999;
		position: fixed;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		background: #7f7f7f;

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
					color: #e93323;
				}
			}

			.btn-box {
				margin-top: 40rpx;
				text-align: center;
				font-size: 30rpx;

				.btn-item {
					height: 82rpx;
					line-height: 82rpx;
					background: linear-gradient(90deg, #f67a38 0%, #f11b09 100%);
					color: #fff;
					border-radius: 41rpx;
				}

				.btn {
					padding: 30rpx 0;
				}
			}
		}
	}

	.sort-product {
		margin: 20rpx;
	}

	.emptyBox {
		text-align: center;
		padding: 150rpx 0;

		.tips {
			color: #aaa;
			font-size: 26rpx;
		}

		image {
			width: 414rpx;
			height: 304rpx;
		}
	}

	.nothing {
		// min-height: 800rpx;
	}

	.product-list {
		display: flex;
		flex-wrap: wrap;
		justify-content: space-between;
		margin-top: 0rpx;
		padding: 0 20rpx;

		.product-item {
			position: relative;
			width: 324rpx;
			background: #fff;
			border-radius: 10rpx;
			margin-bottom: 20rpx;

			image {
				width: 100%;
				// height: 344rpx;
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

	.sticky-box {
		// /* #ifndef APP-PLUS-NVUE */
		// display: flex;
		// position: -webkit-sticky;
		// /* #endif */
		// position: sticky;
		// /* #ifdef H5*/
		// top: var(--window-top);
		// /* #endif */

		// z-index: 99;
		// flex-direction: row;
		// margin: 0px;
		// background: #f5f5f5;
		// padding: 30rpx 0;
		// /* #ifdef MP || APP-PLUS*/
		// //top: 110rpx;
		// /* #endif

		/* #ifndef H5 */
		display: flex;
		position: -webkit-sticky;
		overflow-x: scroll;
		/* #endif */
		position: sticky;
		z-index: 998;
		flex-direction: row;
		margin: 0px;
		background: #f5f5f5;
		padding: 24rpx 0 24rpx 0;
		/* #ifdef MP || APP-PLUS*/
		//top: 110rpx;
		/* #endif */
		// overflow-x: scroll;
	}
</style>
