<template>
	<view id="pageIndex">
		<!-- #ifdef H5 -->
		<view class="followMe" v-if="$wechat.isWeixin()">
			<view class="follow acea-row row-between-wrapper" v-if="followHid && followUrl && !subscribe">
				<view>点击“立即关注”即可关注公众号</view>
				<view class="acea-row row-middle">
					<view class="bnt" @click="followTap">立即关注</view>
					<span class="iconfont icon-guanbi" @click="closeFollow"></span>
				</view>
			</view>
			<view class="followCode" v-if="followCode">
				<view class="pictrue"><img :src="followUrl"/></view>
				<view class="mask" @click="closeFollowCode"></view>
			</view>
		</view>
		<!-- #endif -->
		<!-- #ifdef MP -->
		<view class="indexTip" :style="'top:' + (navH+50) + 'px'" :hidden="iShidden">
			<view class="tip acea-row row-between-wrapper">
				<view class="text">点击“<image src="../../static/images/spot.png"></image>”添加到我的小程序， 微信首页下拉即可访问商城。</view>
				<view class="iconfont icon-guanbi1" @click="closeTip"></view>
			</view>
		</view>
		<!-- #endif -->
		<headerSerch :dataConfig="headerSerch.default" @click.native="bindEdit('headerSerch','default')"></headerSerch>
		<swiperBg :dataConfig="swiperBg.default" @click.native="bindEdit('swiperBg','default')"></swiperBg>
		<menus :dataConfig="menus.default" @click.native="bindEdit('menus','default')"></menus>
		<news :dataConfig="news.default" @click.native="bindEdit('news','default')"></news>
		<activity :dataConfig="activity.default" @click.native="bindEdit('activity','default')"></activity>
		<!-- <coupon :dataConfig="coupon.default" @click.native="bindEdit('coupon','default')"></coupon>
		<seckill :dataConfig="seckill.default" @click.native="bindEdit('seckill','default')"></seckill>
		<adsRecommend :dataConfig="adsRecommend.default" @click.native="bindEdit('adsRecommend','default')"></adsRecommend>
		<combination :dataConfig="combination.default" @click.native="bindEdit('combination','default')"></combination>
		<bargain :dataConfig="bargain.default" @click.native="bindEdit('bargain','default')"></bargain>
		<goodList :dataConfig="goodList.default" @click.native="bindEdit('goodList','default')"></goodList>
		<picTxt :dataConfig="picTxt.default" @click.native="bindEdit('picTxt','default')"></picTxt>	 -->
		<alive :dataConfig="alive.default" @click.native="bindEdit('alive','default')"></alive>
		<scrollBox :dataConfig="scrollBox.default" @click.native="bindEdit('scrollBox','default')"></scrollBox>
		<promotion :dataConfig="goodList.dd" @click.native="bindEdit('goodList','dd')"></promotion>
		<popular :dataConfig="goodList.bb" @click.native="bindEdit('goodList','bb')"></popular>
		<swiperBg :dataConfig="swiperBg.aa" @click.native="bindEdit('swiperBg','aa')" class='swiperCon'></swiperBg>
	  <newGoods :dataConfig="goodList.cc" @click.native="bindEdit('goodList','cc')"></newGoods>
		<titles :dataConfig="titles.default" @click.native="bindEdit('titles','default')"></titles>
		<mBanner :dataConfig="swiperBg.cc" @click.native="bindEdit('swiperBg','cc')"></mBanner>
		<recommend :dataConfig="goodList.aa" @click.native="bindEdit('goodList','aa')"></recommend>
		<!-- <customerService :dataConfig="customerService.default" @click.native="bindEdit('customerService','default')"></customerService> -->
		<tabBar :dataConfig="tabBar.default" :pagePath="'/pages/index/index'" @click.native="bindEdit('tabBar','default')"></tabBar>
		<view v-if="site_config && !isIframe" class="site-config" @click="goICP">{{ site_config }}</view>
		<view class="uni-p-b-98" v-if="!isIframe"></view>
		<couponWindow
			style="position:relative;z-index:10000;"
			:window="isCouponShow"
			@onColse="couponClose"
			:couponImage="couponObj.image"
			:couponList="couponObj.list"
		></couponWindow>
		
	</view>
</template>

<script>
import couponWindow from '@/components/couponWindow/index';
import headerSerch from './components/headerSerch';
import swiperBg from './components/swiperBg';
import menus from './components/menus';
import news from './components/news';
import activity from './components/activity';
import scrollBox from './components/scrollBox';
import recommend from './components/recommend';
import popular from './components/popular';
import mBanner from './components/mBanner';
import newGoods from './components/newGoods';
import promotion from './components/promotion';
import alive from './components/alive';
import adsRecommend from './components/adsRecommend';
import coupon from './components/coupon';
import seckill from './components/seckill';
import combination from './components/combination';
import bargain from './components/bargain';
import goodList from './components/goodList';
import picTxt from './components/picTxt';
import titles from './components/titles';
import customerService from './components/customerService';
import tabBar from './components/tabBar';
import { getShare, follow } from '@/api/public.js';
// #ifdef MP
import { SUBSCRIBE_MESSAGE, TIPS_KEY } from '@/config/cache';
// #endif
import { getTemlIds,siteConfig } from '@/api/api.js';
import { mapGetters } from 'vuex';
import { getDiy, getIndexData, getCouponV2, getCouponNewUser } from '@/api/api.js';
// import { getGroomList } from '@/api/store.js';
import { toLogin } from '@/libs/login.js';
let app = getApp();
let statusBarHeight = uni.getSystemInfoSync().statusBarHeight;
export default {
	computed: mapGetters(['isLogin', 'uid']),
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
		tabBar
	},
	data() {
		return {
			followHid: true,
			followUrl:'',
			followCode:false,
			navH:statusBarHeight,
			subscribe: false,
			iShidden:false,
			goodType: 3,
			loading: false,
			loadend: false,
			loadTitle: '下拉加载更多', //提示语
			page: 1,
			limit: this.$config.LIMIT,
			numConfig: 0,
			couponObj: {},
			isCouponShow: false,
			shareInfo: {},
			site_config:'',
			isIframe: app.globalData.isIframe,
			headerSerch:{},//头部搜索
			swiperBg:{},//轮播
			menus:{},//导航
			news:{},//消息公告
			activity:{},//活动魔方
			alive:{},//直播
			scrollBox:{},//快速选择分类
			titles:{},//标题
			goodList:{},//商品列表(商品列表、首发新品、热门榜单、促销单品、精品推荐)
			tabBar:{},//导航
			customerService:{},//客服
			picTxt:{},//图文详情
			bargain:{},//砍价
			combination:{},//拼团
			adsRecommend:{},//广告位
			seckill:{},//秒杀
			coupon:{},//优惠券
			isBorader: ''
		};
	},
	onLoad(options) {
		let that = this;
		// #ifdef H5
		if(app.globalData.isIframe){
			setTimeout(()=>{
				let active;
				document.getElementById('pageIndex').children.forEach(dom=>{
					dom.addEventListener('click', (e)=>{
						e.stopPropagation();
						e.preventDefault();
						if(dom === active) return;
						dom.classList.add('borderShow');
						active && active.classList.remove('borderShow');
						active = dom;
					})
				})
			});
		}
		if (app.globalData.isIframe) {
			uni.hideTabBar();
		}
		this.getFollow();
		if (that.$wechat.isWeixin()) {
			that.$wechat.location().then(res=>{
				uni.setStorageSync('user_latitude', res.latitude);
				uni.setStorageSync('user_longitude', res.longitude);
			})
		}else{
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
			
		// #ifdef H5	
		}
		// #endif
		this.diyData();
		this.getIndexData();
		this.setOpenShare();
		// #ifdef MP
		if (this.$Cache.get(TIPS_KEY)) this.iShidden = true;
		this.getTemlIds();
		// #endif
	},
	// #ifdef MP
	//发送给朋友
	onShareAppMessage: function() {
		// 此处的distSource为分享者的部分信息，需要传递给其他人
		let that = this;
		return {
			title: this.shareInfo.title,
			path: '/pages/index/index',
			imageUrl: this.shareInfo.img
		};
	},
	//分享到朋友圈
	onShareTimeline: function() {
		return {
			title: this.shareInfo.title,
			imageUrl: this.shareInfo.img
		};
	},
	// #endif
	onShow() {
		siteConfig().then(res => {
			this.site_config = res.data.record_No
		}).catch(err => {
			console.error(err.msg);
		});
		if (!app.globalData.isIframe) {
			uni.showTabBar();
			if (this.isLogin) {
				this.getCoupon();
			}
		}
	},
	methods: {
		bindEdit(name,dataName) {
			if (app.globalData.isIframe) {
				window.parent.postMessage(
					{
						name: name,
						dataName: dataName,
						params: {}
					},
					'*'
				);
				return;
			}
		},
		getFollow() {
		   follow().then(res => {
				this.followUrl = res.data.path;
		   }).catch((err) => {
				return this.$util.Tips({
				    title: err.msg
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
		// 优惠券弹窗
		getCoupon() {
			const tagDate = uni.getStorageSync('tagDate') || '',
				nowDate = new Date().toLocaleDateString();
			if (tagDate === nowDate) {
				this.getNewCoupon();
			} else {
				getCouponV2().then(res => {
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
		// 新用户优惠券
		getNewCoupon() {
			const oldUser = uni.getStorageSync('oldUser') || 0;
			if (!oldUser) {
				getCouponNewUser().then(res => {
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
		// 优惠券弹窗关闭
		couponClose() {
			this.isCouponShow = false;
			if (!uni.getStorageSync('oldUser')) {
				this.getNewCoupon();
			}
		},
		// 授权关闭
		// authColse: function(e) {
		// 	this.isShowAuth = e;
		// },
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
		onLoadFun() {},
		diyData() {
			let that = this;
			getDiy().then(res => {
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
				that.tabBar = data.tabBar;
				that.customerService = data.customerService;
				that.picTxt = data.picTxt;
				that.bargain = data.bargain;
				that.combination = data.combination;
				that.adsRecommend = data.adsRecommend;
				that.seckill = data.seckill;
				that.coupon = data.coupon;
			});
		},
		getIndexData() {
			getIndexData().then(res => {
				this.subscribe = res.data.subscribe;
				// #ifdef H5
				localStorage.setItem("itemName", res.data.site_name);
				// #endif
				uni.setNavigationBarTitle({
					title: res.data.site_name
				});
			});
		},
		// 微信分享；
		setOpenShare: function() {
			let that = this;
			getShare().then(res => {
				let data = res.data.data;
				this.shareInfo = data;
				// #ifdef H5
				let url = location.href;
				if (this.$store.state.app.uid) {
					url = url.indexOf('?') === -1 ? url + '?spread=' + this.$store.state.app.uid : url + '&spread=' + this.$store.state.app.uid;
				}
				if (that.$wechat.isWeixin()) {
					let configAppMessage = {
						desc: data.synopsis,
						title: data.title,
						link: url,
						imgUrl: data.img
					};
					that.$wechat.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData'], configAppMessage);
				}
				// #endif
			});
		}
	},
	onReachBottom: function() {
		// this.getGroomList();
	}
};
</script>

<style lang="scss">
page {
	background: #fff;
}
.swiperCon{
		margin: 20rpx 0!important;
		/* #ifdef MP */
		/deep/.swiperBg{
			margin: 20rpx 0!important;
		}
		/* #endif */
		/deep/.swiper{
			swiper,
			.swiper-item,
			image {
				height: 190rpx!important;
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
		z-index: 1000;

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
				background-color: #E93323;
				border-radius: 25rpx;
				font-size: 24rpx;
				text-align: center;
				line-height: 50rpx;
			}
		}

		.followCode {
			.mask{
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
</style>
