<template>
	<view v-if="pageShow" class="page"
		:class="bgTabVal==2?'fullsize noRepeat':bgTabVal==1?'repeat ysize':'noRepeat ysize'"
		:style="'background-color:'+bgColor+';background-image: url('+bgPic+');min-height:'+windowHeight+'px;'">
		<view :style="{ marginTop: sortMpTop + 'px' }">
			<!-- #ifdef H5 -->
			<view v-for="(item, index) in styleConfig" :key="index">
				<component :is="item.name" :index="index" :dataConfig="item" @changeBarg="changeBarg"
					@changeTab="changeTab" :tempArr="tempArr" :iSshowH="iSshowH" @detail="goDetail"></component>
			</view>
			<!-- #endif -->
			<!-- #ifdef MP || APP-PLUS-->
			<block v-for="(item, index) in styleConfig" :key="index">
				<activeParty v-if="item.name == 'activeParty'" :dataConfig="item"></activeParty>
				<articleList v-if="item.name == 'articleList'" :dataConfig="item"></articleList>
				<bargain v-if="item.name == 'bargain'" :dataConfig="item" @changeBarg="changeBarg"></bargain>
				<blankPage v-if="item.name == 'blankPage'" :dataConfig="item"></blankPage>
				<combination v-if="item.name == 'combination'" :dataConfig="item"></combination>
				<coupon v-if="item.name == 'coupon'" :dataConfig="item"></coupon>
				<customerService v-if="item.name == 'customerService'" :dataConfig="item"></customerService>
				<goodList v-if="item.name == 'goodList'" :dataConfig="item" @detail="goDetail"></goodList>
				<guide v-if="item.name == 'guide'" :dataConfig="item"></guide>
				<headerSerch v-if="item.name == 'headerSerch'" :dataConfig="item" :special="1"></headerSerch>
				<liveBroadcast v-if="item.name == 'liveBroadcast'" :dataConfig="item"></liveBroadcast>
				<menus v-if="item.name == 'menus'" :dataConfig="item"></menus>
				<news v-if="item.name == 'news'" :dataConfig="item"></news>
				<pictureCube v-if="item.name == 'pictureCube'" :dataConfig="item" :isSortType="isSortType">
				</pictureCube>
				<promotionList v-if="item.name == 'promotionList'" :dataConfig="item" @changeTab="changeTab"
					:tempArr="tempArr" :iSshowH="iSshowH" @detail="goDetail"></promotionList>
				<richText v-if="item.name == 'richText'" :dataConfig="item"></richText>
				<seckill v-if="item.name == 'seckill'" :dataConfig="item"></seckill>
				<swiperBg v-if="item.name == 'swiperBg'" :dataConfig="item"></swiperBg>
				<swipers v-if="item.name == 'swipers'" :dataConfig="item"></swipers>
				<tabNav v-if="item.name == 'tabNav'" :dataConfig="item"></tabNav>
				<titles v-if="item.name == 'titles'" :dataConfig="item"></titles>
			</block>
			<!-- #endif -->
			<view class="loadingicon acea-row row-center-wrapper"
				v-if="tempArr.length && styleConfig[styleConfig.length - 1].name == 'promotionList'">
				<text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
				{{ loadTitle }}
			</view>
			<view class="foot" v-if="newData.menuList && newData.status.status">
				<view class="page-footer" id="target" :style="{'background-color':newData.bgColor.color[0].item}">
					<view class="foot-item" v-for="(item,index) in newData.menuList" :key="index"
						@click="goRouter(item)">
						<block v-if="item.link == activeRouter">
							<image :src="item.imgList[0]"></image>
							<view class="txt" :style="{color:newData.activeTxtColor.color[0].item}">{{item.name}}</view>
						</block>
						<block v-else>
							<image :src="item.imgList[1]"></image>
							<view class="txt" :style="{color:newData.txtColor.color[0].item}">{{item.name}}</view>
						</block>
						<div class="count-num"
							v-if="item.link === '/pages/order_addcart/order_addcart' && $store.state.indexData.cartNum && $store.state.indexData.cartNum > 0">
							{{$store.state.indexData.cartNum}}
						</div>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	const app = getApp();
	import couponWindow from '@/components/couponWindow/index'
	import {
		getCouponV2,
		getCouponNewUser
	} from '@/api/api.js'
	import {
		getShare
	} from '@/api/public.js';
	// #ifdef H5
	import mConfig from '@/pages/index/diy/components/index.js';
	// #endif
	// #ifdef MP || APP-PLUS
	import authorize from '@/components/Authorize';
	import activeParty from '@/pages/index/diy/components/activeParty';
	import headerSerch from '@/pages/index/diy/components/headerSerch';
	import swipers from '@/pages/index/diy/components/swipers';
	import coupon from '@/pages/index/diy/components/coupon';
	import articleList from '@/pages/index/diy/components/articleList';
	import bargain from '@/pages/index/diy/components/bargain';
	import blankPage from '@/pages/index/diy/components/blankPage';
	import combination from '@/pages/index/diy/components/combination';
	import customerService from '@/pages/index/diy/components/customerService';
	import goodList from '@/pages/index/diy/components/goodList';
	import guide from '@/pages/index/diy/components/guide';
	import liveBroadcast from '@/pages/index/diy/components/liveBroadcast';
	import menus from '@/pages/index/diy/components/menus';
	import news from '@/pages/index/diy/components/news';
	import promotionList from '@/pages/index/diy/components/promotionList';
	import richText from '@/pages/index/diy/components/richText';
	import seckill from '@/pages/index/diy/components/seckill';
	import swiperBg from '@/pages/index/diy/components/swiperBg';
	import tabNav from '@/pages/index/diy/components/tabNav';
	import titles from '@/pages/index/diy/components/titles';
	import pictureCube from '@/pages/index/diy/components/pictureCube';

	import {
		getTempIds
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
		getGroomList
	} from '@/api/store.js';
	import {
		goShopDetail
	} from '@/libs/order.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import pageFooter from '@/components/pageFooter/index.vue'
	export default {
		computed: mapGetters(['isLogin', 'uid']),
		components: {
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
			news,
			promotionList,
			richText,
			seckill,
			swiperBg,
			tabNav,
			titles,
			pictureCube
			// #endif
		},
		computed: mapGetters(['isLogin']),
		data() {
			return {
				styleConfig: [],
				tempArr: [],
				goodType: 3,
				loading: false,
				loadend: false,
				loadTitle: this.$t(`加载更多`), //提示语
				page: 1,
				limit: this.$config.LIMIT,
				iSshowH: false,
				numConfig: 0,
				code: '',
				isCouponShow: false,
				couponObj: {},
				couponObjs: {},
				shareInfo: {},
				footConfig: {},
				pageId: '',
				sortMpTop: 0,
				newData: {},
				activeRouter: '',
				bgColor: '',
				bgPic: '',
				bgTabVal: '',
				pageShow: true,
				windowHeight: 0
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
				title: this.$t(`专题栏`)
			});

			// #ifdef APP-PLUS
			this.sortMpTop = -50
			// #endif
			uni.getLocation({
				type: 'wgs84',
				success: function(res) {
					try {
						uni.setStorageSync('user_latitude', res.latitude);
						uni.setStorageSync('user_longitude', res.longitude);
					} catch {}
				}
			});
			this.diyData();
			this.getIndexData();
			// #ifdef H5
			this.setOpenShare();
			// #endif
			// #ifdef MP || APP-PLUS
			this.getTempIds();
			// #endif
			getShare().then(res => {
				this.shareInfo = res.data;
			})
			let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
			let curRoute = routes[routes.length - 1].route //获取当前页面路由
			this.activeRouter = '/' + curRoute + '?id=' + this.pageId
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
		mounted() {
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
			onLoadFun() {},
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
					getTempIds().then(res => {
						if (res.data) wx.setStorageSync(SUBSCRIBE_MESSAGE, JSON.stringify(res.data));
					});
				}
			},
			// #endif
			// 对象转数组
			objToArr(data) {
				if (!data) return
				const keys = Object.keys(data)
				keys.sort((a, b) => a - b)
				const m = keys.map(key => data[key]);
				return m;
			},
			diyData() {
				let that = this;
				getDiy(this.pageId).then(res => {
					let data = res.data;
					if (res.data.length == 0) {
						return this.$util.Tips({
							title: this.$t(`暂无数据`)
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
						title: res.data.title
					})
					let temp = []
					let lastArr = that.objToArr(res.data.value)
					lastArr.forEach((item, index, arr) => {
						if (item.name == 'pageFoot') {
							uni.setStorageSync('pageFoot', item)
							that.$store.commit('FOOT_UPLOAD', item)
							arr.splice(index, 1)
							this.newData = item
						}
						if (item.name == 'promotionList') {
							that.numConfig = item.numConfig.val;
							that.getGroomList();
						}
						temp = arr
					});
					that.styleConfig = temp;
				});
			},
			getIndexData() {},
			changeBarg(item) {
				if (!this.isLogin) {
					toLogin();
				} else {
					uni.navigateTo({
						url: `/pages/activity/goods_bargain_details/index?id=${item.id}&bargain=${this.uid}`
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
						limit: that.limit
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
						that.loadTitle = loadend ? that.$t(`没有更多内容啦~`) : that.$t(`加载更多`);
						that.page = that.page + 1;
						that.loading = false;
					})
					.catch(res => {
						that.loading = false;
						that.loadTitle = that.$t(`加载更多`);
					});
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
		onReachBottom: function() {
			this.getGroomList();
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
