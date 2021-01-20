<template>
	<view>
		<!-- #ifdef H5 -->
		<view v-for="(item, index) in styleConfig" :key="index" @click="bindParent(item)">
			<component
				:is="item.name"
				:index="index"
				:activeName="activeName"
				:dataConfig="item"
				:pagePath="'/pages/index/index'"
				@changeBarg="changeBarg"
				@changeTab="changeTab"
				:tempArr="tempArr"
				:iSshowH="iSshowH"
				@detail="goDetail"
				@bindIframe="bindIframe"
			></component>
		</view>
		<!-- #endif -->
		<!-- #ifdef MP -->
		<block v-for="(item, index) in styleConfig" :key="index">
			<a_headerSerch v-if="item.name == 'a_headerSerch'" :dataConfig="item"></a_headerSerch>
			<b_swiperBg v-if="item.name == 'b_swiperBg'" :dataConfig="item"></b_swiperBg>
			<c_menus v-if="item.name == 'c_menus'" :dataConfig="item"></c_menus>
			<d_news v-if="item.name == 'd_news'" :dataConfig="item"></d_news>
			<e_activity v-if="item.name == 'e_activity'" :dataConfig="item"></e_activity>
			<f_alive v-if="item.name == 'f_alive'" :dataConfig="item"></f_alive>
			<f_scroll_box v-if="item.name == 'f_scroll_box'" :dataConfig="item"></f_scroll_box>
			<g_recommend v-if="item.name == 'g_recommend'" :dataConfig="item"></g_recommend>
			<h_popular v-if="item.name == 'h_popular'" :dataConfig="item"></h_popular>
			<i_m_banner v-if="item.name == 'i_m_banner'" :dataConfig="item"></i_m_banner>
			<i_new_goods v-if="item.name == 'i_new_goods'" :dataConfig="item"></i_new_goods>
			<j_promotion v-if="item.name == 'j_promotion'" :dataConfig="item"></j_promotion>
			<z_tabBar v-if="item.name == 'z_tabBar'" :dataConfig="item" :pagePath="'/pages/index/index'"></z_tabBar>
		</block>
		<!-- #endif -->
		<view class="loadingicon acea-row row-center-wrapper" v-if="tempArr.length && styleConfig[styleConfig.length - 1].name == 'promotionList'">
			<text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
			{{ loadTitle }}
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse" :isGoIndex="false"></authorize> -->
		<!-- #endif -->
		<view class="uni-p-b-98"></view>
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
import { getShare } from '@/api/public.js';
// #ifdef H5
import mConfig from './components/index.js';
// #endif
// #ifdef MP
import { SUBSCRIBE_MESSAGE, TIPS_KEY } from '@/config/cache';
import a_headerSerch from './components/a_headerSerch';
import b_swiperBg from './components/b_swiperBg';
import c_menus from './components/c_menus';
import d_news from './components/d_news';
import e_activity from './components/e_activity';
import f_scroll_box from './components/f_scroll_box';
import g_recommend from './components/g_recommend';
import h_popular from './components/h_popular';
import i_m_banner from './components/i_m_banner';
import i_new_goods from './components/i_new_goods';
import j_promotion from './components/j_promotion';
import f_alive from './components/f_alive';
import z_tabBar from './components/z_tabBar';
import authorize from '@/components/Authorize.vue';
import { getTemlIds } from '@/api/api.js';
// #endif
import { mapGetters } from 'vuex';
import { getDiy, getIndexData, getCouponV2, getCouponNewUser } from '@/api/api.js';
import { getGroomList } from '@/api/store.js';
import { goShopDetail } from '@/libs/order.js';
import { toLogin } from '@/libs/login.js';
let app = getApp();
export default {
	computed: mapGetters(['isLogin', 'uid']),
	components: {
		couponWindow,
		// #ifdef H5
		...mConfig,
		// #endif
		// #ifdef MP
		authorize,
		a_headerSerch,
		b_swiperBg,
		c_menus,
		d_news,
		e_activity,
		f_scroll_box,
		g_recommend,
		h_popular,
		i_m_banner,
		i_new_goods,
		j_promotion,
		f_alive,
		// authorize,
		z_tabBar
		// #endif
	},
	data() {
		return {
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
			isIframe: false,
			activeName: '',
			isAuto: false, //没有授权的不会自动授权
			isShowAuth: false, //是否隐藏授权,
			couponObj: {},
			couponType: 1,
			isCouponShow: false,
			shareInfo: {}
		};
	},
	onLoad(options) {
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
		this.setOpenShare();
		// #ifdef H5
		window.addEventListener('message', this.handleMessageFromParent, false);
		if (app.globalData.isIframe) {
			uni.hideTabBar();
		}
		// #endif
		// #ifdef MP
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
			imageUrl: this.storeInfo.img
		};
	},
	//分享到朋友圈
	onShareTimeline: function() {
		return {
			title: this.shareInfo.title,
			imageUrl: this.storeInfo.img
		};
	},
	// #endif
	onShow() {
		if (!app.globalData.isIframe) {
			uni.showTabBar();
			if (this.isLogin) {
				this.getCoupon();
			}
		}
	},
	methods: {
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
							this.couponType = 2;
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
			if (this.couponType === 1) {
				uni.setStorageSync('tagDate', new Date().toLocaleDateString());
			}
			if (this.couponType === 2) {
				uni.setStorageSync('oldUser', 1);
				this.couponType = 1;
			}
			if (!uni.getStorageSync('oldUser')) {
				this.getNewCoupon();
			}
		},
		// 授权关闭
		authColse: function(e) {
			this.isShowAuth = e;
		},
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
		onLoadFun() {},
		bindParent(item) {
			this.activeName = item.name;
			if (app.globalData.isIframe) {
				window.parent.postMessage(
					{
						name: item.name,
						params: {}
					},
					'*'
				);
				return;
			}
		},
		// #ifdef H5
		handleMessageFromParent(event) {
			var data = event.data;
			// console.log(event.data,'handleMessageFromParent')
		},
		// #endif
		// 对象转数组
		objToArr(data) {
			for (let name in data) {
				if (name === "z_tabBar") {
					app.globalData.tabbarShow = data[name].isShow.val;
					break;
				}
			}
			let obj = Object.keys(data);

			let m = obj.map(function(key) {
				data[key].name = key;
				return data[key];
			});
			return m;
		},
		diyData() {
			let that = this;
			getDiy().then(res => {
				try {
					if (res.data.a_headerSerch.hotList.list.length > 0) {
						uni.setStorageSync('hotList', res.data.a_headerSerch.hotList.list);
					}
				} catch (err) {}
				// let obj = {"name":"f_alive"}
				that.styleConfig = that.objToArr(res.data);
				// that.styleConfig.unshift(obj)
				// #ifdef MP
				let obj = {};
				obj.name = 'k_live';
				that.styleConfig.splice(5, 0, obj);
				// #endif
			});
		},
		getIndexData() {
			let self = this;
			getIndexData().then(res => {
				uni.setNavigationBarTitle({
					title: res.data.site_name
				});
				self.$store.commit('indexData/setIndexData', res.data);
			});
		},
		// 微信分享；
		setOpenShare: function() {
			let that = this;
			getShare().then(res => {
				let data = res.data.data;
				this.shareInfo = data;
				// #ifdef H5
				if (that.$wechat.isWeixin()) {
					let configAppMessage = {
						desc: data.synopsis,
						title: data.title,
						link: location.href,
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

<style>
page {
	background: #fff;
}
</style>
