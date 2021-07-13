<template>
	<view>
		<view :style="'height:' + systemH + 'px'"></view>
		<view class="bargain">
			<view class="iconfont icon-xiangzuo" v-if="retunTop" @tap="goBack" :style="'top:' + navH + 'px'"></view>
			<view class="header" :class="bargainUid != userInfo.uid ? 'on' : ''">
				<view class="people">
					<!-- :style="'top:'+navH/2+'rpx'" -->
					{{ bargainCount.lookCount || 0 }}人查看 丨 {{ bargainCount.shareCount || 0 }}人分享 丨 {{ bargainCount.userCount || 0 }}人参与
				</view>
				<!-- <view class='time font-color' v-if="bargainUid == userInfo.uid">
					倒计时
					<text>{{countDownDay}}</text>
					天
					<text>{{countDownHour}}</text>
					时
					<text>{{countDownMinute}}</text>
					分
					<text>{{countDownSecond}}</text>
					秒
				</view> -->
				<countDown
					:tipText="'倒计时'"
					:dayText="'天'"
					:hourText="'时'"
					:minuteText="'分'"
					:secondText="'秒'"
					:datatime="datatime"
					:isDay="true"
					v-if="bargainUid == userInfo.uid"
				></countDown>
				<view v-if="bargainUid != userInfo.uid" class="pictxt acea-row row-center-wrapper">
					<view class="pictrue"><image :src="bargainUserInfo.avatar"></image></view>
					<view class="text">
						{{ bargainUserInfo.nickname || '' }}
						<text>邀请您帮忙砍价</text>
					</view>
				</view>
			</view>
			<view class="wrapper">
				<view class="pictxt acea-row row-between-wrapper" @tap="goProduct">
					<view class="pictrue">
						<image :src="bargainInfo.image"></image>
						<view class="bargain_view">
							查看商品
							<text class="iconfont icon-jiantou iconfonts"></text>
						</view>
					</view>
					<view class="text acea-row row-column-around">
						<view class="line2">{{ bargainInfo.title }}</view>
						<view class="money font-color">
							当前: ￥
							<text class="num">{{ bargainInfo.price }}</text>
						</view>
						<view class="successNum">最低:￥{{ bargainInfo.min_price }}</view>
						<!-- <view class='successNum'>已有{{bargainSumCount}}人砍价成功</view> -->
					</view>
				</view>
				<!-- 进度条 -->
				<block v-if="bargainUserHelpInfo.price > 0">
					<view class="cu-progress acea-row row-middle round margin-top">
						<view class="acea-row row-middle bg-red" :style="'width:' + bargainUserHelpInfo.pricePercent + '%;'"></view>
					</view>
					<view class="money acea-row row-between-wrapper">
						<view>已砍{{ bargainUserHelpInfo.alreadyPrice }}元</view>
						<view>还剩{{ bargainUserHelpInfo.price }}元</view>
					</view>
				</block>
				<!-- 自己砍价 -->
				<view v-if="bargainUid == userInfo.uid && (!userBargainStatus || userBargainStatus == bargainSumCount) && bargainUserHelpInfo.price > 0">
					<view class="bargainBnt" @tap="userBargain" v-if="productStock > 0 && quota > 0">立即参与砍价</view>
					<view class="bargainBnt grey" v-if="productStock <= 0 || quota <= 0">商品暂无库存</view>
				</view>
				<!-- <view v-if="bargainUid == userInfo.uid && !userBargainStatus && bargainUserHelpInfo.price > 0">
					<view class='bargainBnt' @tap='userBargain' v-if="productStock>0&&quota>0">
						立即参与砍价
					</view>
					<view class='bargainBnt grey' v-if="productStock<=0||quota<=0">立即参与砍价</view>
				</view> -->
				<!-- 帮助砍价、帮砍成功： -->
				<view v-if="bargainUid == userInfo.uid && bargainUserHelpInfo.price > 0 && userBargainStatus != bargainSumCount">
				    <view class="bargainBnt" @tap="listenerActionSheet">邀请好友帮砍价</view>
					<!-- #ifdef H5 -->
				<!-- 	<view class="bargainBnt" v-if="$wechat.isWeixin()" @click="H5ShareBox = true">邀请好友帮砍价</view> -->
					<!-- #endif -->
					<!-- #ifdef MP -->
					<!-- <button open-type="share" class="bargainBnt">邀请好友帮砍价</button> -->
					<!-- #endif -->
					<view class="tip">
						已有
						<text class="font-color">{{ bargainUserHelpInfo.count }}</text>
						位好友成功砍价
					</view>
				</view>

				<view v-if="bargainUid != userInfo.uid && userBargainStatusHelp && bargainUserHelpInfo.price > 0">
					<view class="bargainBnt" @tap="setBargainHelp">帮好友砍一刀</view>
				</view>
				<view v-if="bargainUid != userInfo.uid && userBargainStatusHelp && bargainUserHelpInfo.price == 0">
					<view class="bargainSuccess">
						<text class="iconfont icon-xiaolian"></text>
						好友已砍价成功
					</view>
					<view class="bargainBnt" @tap="currentBargainUser">我也要参与</view>
				</view>

				<view v-if="bargainUid != userInfo.uid && !userBargainStatusHelp">
					<view class="bargainSuccess">
						<text class="iconfont icon-xiaolian"></text>
						已成功帮助好友砍价
					</view>
					<view class="bargainBnt" @tap="currentBargainUser">我也要参与</view>
				</view>
				<view v-if="bargainUserHelpInfo.price == 0 && bargainUid == userInfo.uid && statusPay != 3">
					<view class="bargainSuccess">
						<text class="iconfont icon-xiaolian"></text>
						恭喜您砍价成功，快去支付
					</view>
					<view class="bargainBnt" @tap="goPay">立即支付</view>
					<view class="bargainBnt on" @tap="goBargainList">抢更多商品</view>
				</view>

				<view class="lock"></view>
			</view>
			<view class="bargainGang">
				<view class="title font-color acea-row row-center-wrapper">
					<view class="pictrue"><image src="../../../static/images/left.png"></image></view>
					<view class="titleCon">砍价帮</view>
					<view class="pictrue on"><image src="../../../static/images/left.png"></image></view>
				</view>
				<view class="list">
					<!-- v-if="index < 3 || !couponsHidden" -->
					<block v-for="(item, index) in bargainUserHelpList" :key="index">
						<view class="item acea-row row-between-wrapper">
							<view class="pictxt acea-row row-between-wrapper">
								<view class="pictrue"><image :src="item.avatar"></image></view>
								<view class="text">
									<view class="name line1">{{ item.nickname }}</view>
									<view class="line1">{{ item.add_time }}</view>
								</view>
							</view>
							<view class="money font-color">
								<text class="iconfont icon-kanjia"></text>
								砍掉{{ item.price }}元
							</view>
						</view>
					</block>
					<!-- <view class="open acea-row row-center-wrapper" @click="openTap" v-if="bargainUserHelpList.length > 3">
						{{ couponsHidden ? '展开更多' : '关闭展开' }}
						<text class="iconfont" :class="couponsHidden == true ? 'icon-xiangxia' : 'icon-xiangshang'"></text>
					</view> -->
				</view>
				<view class="load font-color" @tap="getBargainUser" v-if="!loadend">点击加载更多</view>
				<view class="lock"></view>
			</view>
			<view class="goodsDetails">
				<view class="title font-color acea-row row-center-wrapper">
					<view class="pictrue"><image src="/images/left.png"></image></view>
					<view class="titleCon">商品详情</view>
					<view class="pictrue on"><image src="/images/left.png"></image></view>
				</view>
				<view class="conter">
					<!-- <template is="wxParse" data="{{wxParseData:description.nodes}}" /> -->
					<jyf-parser :html="bargainInfo.description" ref="article" :tag-style="tagStyle"></jyf-parser>
					<!-- <rich-text :nodes="bargainInfo.description" class="conter"></rich-text> -->
				</view>
				<view class="lock"></view>
			</view>
			<view class="goodsDetails">
				<view class="title font-color acea-row row-center-wrapper">
					<view class="pictrue"><image src="/images/left.png"></image></view>
					<view class="titleCon">砍价规则</view>
					<view class="pictrue on"><image src="/images/left.png"></image></view>
				</view>
				<view class="conter">
					<!-- <template is="wxParse" :data="wxParseData:rule.nodes" /> -->
					<jyf-parser :html="bargainInfo.rule" ref="article" :tag-style="tagStyle"></jyf-parser>
				</view>
			</view>
			<view class="bargainTip" :class="active == true ? 'on' : ''">
				<view class="pictrue"><image src="../static/bargainBg.jpg"></image></view>
				<view v-if="bargainUid == userInfo.uid">
					<view class="cutOff">
						您已砍掉
						<text class="font-color">{{ bargainUserBargainPrice.price }}</text>
						元，听说分享次数越多砍价成功的机会越大哦！
					</view>
					<!-- #ifdef MP -->
					<button open-type="share" class="tipBnt">邀请好友帮砍价</button>
					<!-- #endif -->
					<!-- #ifdef H5 -->
					<view class="tipBnt" @tap="getBargainUserBargainPricePoster">邀请好友帮砍价</view>
					<!-- #endif -->
				</view>
				<view v-else>
					<view class="help font-color">成功帮砍{{ bargainUserBargainPrice.price }}元</view>
					<view class="cutOff on">您也可以砍价低价拿哦，快去挑选心仪的商品吧~</view>
					<view @tap="currentBargainUser" class="tipBnt">我也要参与</view>
				</view>
			</view>
			<view class="mask" catchtouchmove="true" v-show="active == true" @tap="close"></view>
		</view>
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
			<button class="item" hover-class='none' @tap="getBargainUserBargainPricePoster">
				<view class="iconfont icon-haibao"></view>
				<view class="">生成海报</view>
			</button>
		</view>
		<view class="mask" v-if="posters" @click="listenerActionClose"></view>
		<!-- 发送给朋友图片 -->
		<view class="share-box" v-if="H5ShareBox"><image src="/static/images/share-info.png" @click="H5ShareBox = false"></image></view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<home></home>
	</view>
</template>

<script>
import {
	getBargainDetail,
	postBargainStartUser,
	postBargainStart,
	postBargainHelpPrice,
	postBargainHelpCount,
	postBargainHelp,
	postBargainHelpList,
	postBargainShare
} from '../../../api/activity.js';
import { postCartAdd } from '../../../api/store.js';
// import wxh from '../../../utils/wxh.js';
// import wxParse from '../../../wxParse/wxParse.js';
import util from '../../../utils/util.js';
import { toLogin } from '@/libs/login.js';
import { mapGetters } from 'vuex';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
import countDown from '@/components/countDown';
import home from '@/components/home';
import parser from '@/components/jyf-parser/jyf-parser';
import { silenceBindingSpread } from '@/utils';
const app = getApp();

export default {
	components: {
		countDown,
		// #ifdef MP
		authorize,
		// #endif
		home,
		'jyf-parser': parser
	},
	/**
	 * 页面的初始数据
	 */
	data() {
		return {
			countDownDay: '00',
			countDownHour: '00',
			countDownMinute: '00',
			countDownSecond: '00',
			active: false,
			id: 0, //砍价产品编号
			userInfo: {}, //当前用户信息
			bargainUid: 0, //开启砍价用户
			bargainUserInfo: {}, //开启砍价用户信息
			bargainUserId: 0, //开启砍价编号
			bargainInfo: [], //砍价产品
			page: 1,
			limit: 5,
			loading: false,
			loadend: false,
			bargainUserHelpList: [],
			bargainUserHelpInfo: [],
			bargainUserBargainPrice: 0,
			status: '', // 0 开启砍价   1  朋友帮忙砍价  2 朋友帮忙砍价成功 3 完成砍价  4 砍价失败 5已创建订单
			bargainCount: [], //分享人数  浏览人数 参与人数
			retunTop: true,
			bargainPartake: 0,
			isHelp: false,
			interval: null,
			userBargainStatus: 0, //判断自己是否砍价
			productStock: 0, //判断是否售罄；
			quota: 0, //判断是否已限量；
			userBargainStatusHelp: true,
			navH: '',
			statusPay: '',
			bargainSumCount: 0,
			bargainPrice: 0,
			datatime: 0,
			tagStyle: {
				img: 'width:100%;display:block;',
				table: 'width:100%',
				video: 'width:100%'
			},
			H5ShareBox: false, //公众号分享图片
			systemH: 0,
			isAuto: false, //没有授权的不会自动授权
			isShowAuth: false, //是否隐藏授权
			pages: '', 
			posters: false,
			weixinStatus: false,
			couponsHidden: true
		};
	},
	computed: mapGetters(['isLogin']),
	watch: {
		isLogin: {
			handler: function(newV, oldV) {
				if (newV) {
					this.getBargainDetails();
					this.addShareBargain();
				}
			},
			deep: true
		}
	},
	/**
	 * 生命周期函数--监听页面加载
	 */
	onLoad: function(options) {
		var that = this;
		// #ifdef MP
		uni.getSystemInfo({
			success: function(res) {
				that.systemH = res.statusBarHeight;
				that.navH = that.systemH + 10;
			}
		});
		// #endif

		var pages = getCurrentPages();
		if (pages.length <= 1) {
			that.retunTop = false;
		}
		//扫码携带参数处理
		// #ifdef MP
		if (options.scene) {
			var value = util.getUrlParams(decodeURIComponent(options.scene));
			if (typeof value === 'object') {
				if (value.id) options.id = value.id;
				if (value.bargain) options.bargain = value.bargain;
				//记录推广人uid
				if (value.pid) app.globalData.spid = value.pid;
			} else {
				app.globalData.spid = value;
			}
		}
		//记录推广人uid
		if (options.spid) app.globalData.spid = options.spid;
		// #endif
		if (options.hasOwnProperty('id')) {
			that.id = options.id;
			that.bargainUid = options.bargain || 0;
		}

		if (this.isLogin) {
			console.log(that.bargainUid, 'that.bargainUid');
			if (that.bargainUid == 'undefined') {
				that.bargainUid = that.$store.state.app.uid;
			}
			this.getBargainDetails();
			this.addShareBargain();
			// app.globalData.openPages = '/pages/activity/goods_bargain_details/index?id=' + this.id + '&bargain=' + this.bargainUid +
			// 	'&spid=' + e.detail.uid;
			// this.$set(that, 'bargainPartake', e.detail.uid);
		} else {
			toLogin();
		}

		this.isLogin && silenceBindingSpread();
		uni.setNavigationBarTitle({
			title: '砍价详情'
		});
	},
	methods: {
		/**
		 * 分享打开
		 * 
		 */
		listenerActionSheet: function() {
			if (this.isLogin == false) {
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
		// 小程序关闭分享弹窗；
		goFriend: function() {
			this.posters = false;
		},
		openTap() {
			this.$set(this, 'couponsHidden', !this.couponsHidden);
		},
		// 授权关闭
		authColse: function(e) {
			this.isShowAuth = e;
		},
		// 去商品页
		goProduct() {
			uni.navigateTo({
				url: `/pages/goods_details/index?id=${this.bargainInfo.product_id}`
			});
		},
		// 自己砍价；
		userBargain: function() {
			let that = this;
			if (that.userInfo.uid == that.bargainUid) {
				if (that.userBargainStatus == that.bargainInfo.num) {
					return that.$util.Tips({
						title: `该商品每人限购${that.bargainInfo.num}${that.bargainInfo.unit_name}`
					});
				} else {
					that.setBargain();
				}
			}
		},
		goBack: function() {
			uni.navigateBack({
				delta: 1
			});
		},
		gobargainUserInfo: function() {
			//获取开启砍价用户信息
			var that = this;
			var data = {
				userId: that.bargainUid
			};
			postBargainStartUser({
				bargainId: that.id,
				bargainUserUid: that.bargainUid
			}).then(res => {
				that.$set(that, 'bargainUserInfo', res.data);
			});
		},
		goPay: function() {
			//立即支付
			var that = this;
			var data = {
				productId: that.bargainInfo.product_id,
				bargainId: that.id,
				cartNum: 1,
				uniqueId: '',
				combinationId: 0,
				secKillId: 0,
				new: 1
			};
			postCartAdd(data)
				.then(res => {
					uni.navigateTo({
						url: '/pages/users/order_confirm/index?new=1&cartId=' + res.data.cartId
					});
				})
				.catch(err => {
					return that.$util.Tips({ title: err });
				});
		},
		getBargainDetails: function() {
			//获取砍价产品详情
			var that = this;
			var id = that.id;
			getBargainDetail(id)
				.then(function(res) {
					that.bargainInfo = res.data.bargain;
					that.bargainPrice = res.data.bargain.price;
					that.userInfo = res.data.userInfo;
					that.bargainSumCount = res.data.bargainSumCount;
					that.userBargainStatus = res.data.userBargainStatus;
					that.productStock = res.data.bargain.attr.product_stock;
					that.quota = res.data.bargain.attr.quota;
					that.datatime = res.data.bargain.stop_time;
					that.pages = '/pages/activity/goods_bargain_details/index?id=' + that.id + '&bargain=' + that.bargainUid + '&scene=' + that.userInfo.uid;
					console.log(that.pages);
					uni.setNavigationBarTitle({
						title: res.data.bargain.title.substring(0, 13) + '...'
					});
					that.getBargainHelpCount();
					that.bargainUserHelpList = [];
					that.getBargainUser();
					that.gobargainUserInfo();
					//#ifdef H5
					that.setOpenShare();
					//#endif
				})
				.catch(function(err) {
					// uni.navigateTo({
					// 	url: window.location.protocol + "//" + window.location.host +'/pages/activity/goods_bargain/index',
					// });
					that.$util.Tips(
						{
							title: err
						},
						{
							tab: 2,
							url: '/pages/activity/goods_bargain/index'
						}
					);
				});
		},
		getBargainHelpCount: function() {
			//获取砍价帮总人数、剩余金额、进度条、已经砍掉的价格
			var that = this;
			var data = { bargainId: that.id, bargainUserUid: that.bargainUid };
			postBargainHelpCount(data).then(res => {
				var price = util.$h.Sub(that.bargainPrice, res.data.alreadyPrice);
				that.bargainUserHelpInfo = res.data;
				that.bargainInfo.price = parseFloat(price) <= 0 ? 0 : price;
				that.userBargainStatusHelp = res.data.userBargainStatus;
				that.statusPay = res.data.status;
			});
		},
		currentBargainUser: function() {
			//当前用户砍价
			this.$set(this, 'bargainUid', this.userInfo.uid);
			this.setBargain();
		},
		setBargain: function() {
			//参与砍价
			var that = this;
			postBargainStart(that.id).then(
				res => {
					if (res.code === 'subscribe') {
						return;
					}
					that.$set(that, 'bargainUserId', res.data);
					that.getBargainUserBargainPrice();
					that.setBargainHelp();
					that.getBargainHelpCount();
					that.userBargainStatus = 1;
				},
				error => {
					that.$util.Tips({
						title: error
					});
				}
			);
		},
		setBargainHelp: function() {
			//帮好友砍价
			var that = this;
			var data = {
				bargainId: that.id,
				bargainUserUid: that.bargainUid
			};
			postBargainHelp(data)
				.then(res => {
					that.$set(that, 'bargainUserHelpList', []);
					that.$set(that, 'isHelp', true);
					that.getBargainUser();
					that.getBargainUserBargainPrice();
					that.getBargainHelpCount();
				})
				.catch(err => {
					that.$util.Tips({
						title: err
					});
					that.$set(that, 'bargainUserHelpList', []);
					that.getBargainUser();
				});
		},
		getBargainUser: function() {
			//获取砍价帮
			let that = this;
			if (that.loading) return;
			if (that.loadend) return;
			that.loading = true;
			let data = {
				bargainId: that.id,
				bargainUserUid: that.bargainUid,
				page: that.page,
				limit: that.limit
			};
			postBargainHelpList(data).then(res => {
				let list = res.data;
				let loadend = list.length < that.limit;
				that.bargainUserHelpList = that.$util.SplitArray(list, that.bargainUserHelpList);
				that.$set(that, 'bargainUserHelpList', that.bargainUserHelpList);
				that.loadend = loadend;
				that.page = that.page + 1;
				that.loading = false;
			}).catch(err => {
					that.loading = false;
				});
		},
		getBargainUserBargainPricePoster: function() {
			var that = this;
			that.posters = false;
			//#ifdef H5
			if (this.$wechat.isWeixin()) {
				this.active = false;
				this.H5ShareBox = true;
			} else {
			//#endif	
				uni.navigateTo({
					url: '/pages/activity/poster-poster/index?type=1&id=' + that.id
				});
			//#ifdef H5	
			}
			//#endif
		},
		getBargainUserBargainPrice: function() {
			//获取帮忙砍价砍掉多少金额
			var that = this;
			var data = {
				bargainId: that.id,
				bargainUserUid: that.bargainUid
			};
			postBargainHelpPrice(data)
				.then(res => {
					that.$set(that, 'bargainUserBargainPrice', res.data);
					that.$set(that, 'active', true);
				})
				.catch(err => {
					that.$set(that, 'active', false);
				});
		},
		goBargainList: function() {
			uni.navigateTo({
				url: '/pages/activity/goods_bargain/index'
			});
		},
		close: function() {
			this.$set(this, 'active', false);
		},
		onLoadFun: function(e) {
			this.getBargainDetails();
			this.addShareBargain();
			// this.pages = '/pages/activity/goods_bargain_details/index?id=' + this.id + '&bargain=' + this.bargainUid +
			// 	'&spid=' + e.uid;
			this.$set(this, 'bargainPartake', e.uid);
		},
		addShareBargain: function() {
			//添加分享次数 获取人数
			var that = this;
			postBargainShare(this.id).then(res => {
				that.$set(that, 'bargainCount', res.data);
				this.pages = '/pages/activity/goods_bargain_details/index?id=' + this.id + '&bargain=' + this.bargainUid + '&spid=' + this.userInfo.uid;
			});
		},
		//#ifdef H5
		setOpenShare() {
			let that = this;
			let configTimeline = {
				title: '您的好友' + that.userInfo.nickname + '邀请您砍价' + that.bargainInfo.title,
				desc: that.bargainInfo.info,
				link: window.location.protocol + '//' + window.location.host + '/pages/activity/goods_bargain_details/index?id=' + that.id + '&bargain=' + that.userInfo.uid,
				imgUrl: that.bargainInfo.image
			};
			if (this.$wechat.isWeixin()) {
				this.$wechat
					.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareAppMessage', 'onMenuShareTimeline'], configTimeline)
					.then(res => {
						console.log(res);
					})
					.catch(res => {
						if (res.is_ready) {
							res.wx.updateAppMessageShareData(configTimeline);
							res.wx.updateTimelineShareData(configTimeline);
							res.wx.onMenuShareAppMessage(configTimeline);
							res.wx.onMenuShareTimeline(configTimeline);
						}
					});
			}
		}
		//#endif
	},

	/**
	 * 生命周期函数--监听页面初次渲染完成
	 */
	onReady: function() {},
	/**
	 * 生命周期函数--监听页面显示
	 */
	onShow: function() {},

	/**
	 * 生命周期函数--监听页面隐藏
	 */
	onHide: function() {
		if (this.interval !== null) clearInterval(this.interval);
	},

	/**
	 * 生命周期函数--监听页面卸载
	 */
	onUnload: function() {
		if (this.interval !== null) clearInterval(this.interval);
	},

	/**
	 * 页面相关事件处理函数--监听用户下拉动作
	 */
	onPullDownRefresh: function() {},

	/**
	 * 页面上拉触底事件的处理函数
	 */
	onReachBottom: function() {},

	//#ifdef MP
	/**
	 * 用户点击右上角分享
	 */
	onShareAppMessage: function() {
		let that = this,
			share = {
				title: '您的好友' + that.userInfo.nickname + '邀请您帮他砍' + that.bargainInfo.title + ' 快去帮忙吧！',
				path: '/pages/activity/goods_bargain_details/index?id=' + this.id + '&bargain=' + this.bargainUid + '&pid=' + this.userInfo.uid,
				imageUrl: that.bargainInfo.image
			};
		that.close();
		that.addShareBargain();
		return share;
	}
	//#endif
};
</script>

<style lang="scss">
page {
	background-color: #e93323 !important;
}
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
.bargain .bargainGang .open {
	font-size: 24rpx;
	color: #999;
	margin-top: 30rpx;
}

.bargain .bargainGang .open .iconfont {
	font-size: 25rpx;
	margin: 5rpx 0 0 10rpx;
}

.bargain .icon-xiangzuo {
	font-size: 40rpx;
	color: #fff;
	position: fixed;
	top: 56rpx;
	left: 30rpx;
	z-index: 99;
	font-size: 36rpx;
}

.bargain .header {
	background-image: url('data:image/png;base64,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');
	background-repeat: no-repeat;
	background-size: 100% 100%;
	width: 698rpx;
	height: 572rpx;
	margin: 0 auto;
	padding-top: 0.1rpx;
}

.bargain .header.on {
	background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAssAAAI8CAMAAAAwQRZIAAADAFBMVEUAAACpJRycEhLaaUOcEhLca0TgbET/kmH+kmGcEhL/kmH/kWHebEb+kmHcZkP/kWH/kWH+kmH/kWHie0/og1ScEhL/kWH+kmHfeE/9k2L/kWHdbEf/kWH3n2bifFGdFBP1m2T0mWPxlWHkf1Pmg1X5omj6kl/ukF7qilr/kmHnhljrjFv7pGr1kVf0i1D/kWHyj1jtRSn8jkP8pmv5hT3tQyj6h0DrOSX9jUT+kkXsPif8ikLqNyTsPifrPCb/lkb+kET/lkbzYDPtQyjrQyn9jkTrOSXvSyvqNyT+k0X5ekHzYjT0ZDTyd0DyWjD0ZTTqNCP////rPCb/mhrsPyftRSnuTCvtQij9jUP+kUTrOSXvTiz9j0PuSSr/l0bqNyT+k0T7hD/vUSzuRyr8ikH8i0L6gT78h0D1dTb/lUX/rEbvUy3wUy7zaTPpNSP9oUPxWi/yYTH1cjbzXzLxVi/0ZDTyXzDqNSTxXC/+pUT+p0T0azTwWC7zZTLyYzHwVy79o0PyWjH2eDf1ZzX5fT3yXDH4hjv0bjX0YjP1cDX4dzv3czn/qUX7mEDwVS30bjT8mkH2bTf1ajb/mUf3cDj2ezjzZzL2eDj9nUL9n0L5eTz4gzr6jz74dTryWDD8nEH5ezz6fz33gDr3fjn/s1P5iz37lT//5sb/rkbqMyP/zY36kj75iTz+mCL4dUL5ekX3ckD9kyKamV/5iDv5hDJPWUH/+fL9lSynpmf/oCj5d0P7kjX7iSr/8eP6fUj/69X9mzb7jTH8jiiHg0L/r0f/pzcBAAD/4Lj/umJDLwD/2ar/wHD4v7CtbE5XYUGRkFn/x3/4fSwsHxr/1Jt6djf/iz0mFgdtaTH9yqOMik32dB1jaUQ/Rzb+xrmBeUb7uY+xsXdjXin6omTCcENSPAf4kVH1t6b6rXvXglD+18a1uov4dC/2bhP9l0PfdUOIXDelfUP4k2q8iVV+ShDGyZz3sA62lYDFxHn/wXiim0/WwFX1XACoZBrYqJHGnlr482qOCpvwAAAAUXRSTlMABQoJEA8WdW8WaXscYySLnYCXLDcdg11DiI8ypL9PJbWqoFpmyVaWfZNyitNJIqk/Xojb8nKctMy2nXHrzYn1XemySTqn2fL01+KOyvri8vrMR7jzAACR+UlEQVR42uyczY7aMBRGgxAsRt1U8AzdBSEvEFEWjhdZ5CUstjzPvHBdm5tT151Qykw7hPuVJPb9sytOPB6CplLdp+VuW3/rWmP6vmmOw+B+yL4G2dgchmPT9L0xbfet3u6WlUr1qbTc1Z05BHbt642yge2D6WqlWvWftatfTHN0r+8gd2zMS72rVKp/q9W2M82fLcP+JrMdGtNtV5VK9eFabALFDhxvA5k+TWzIBaI3i0ql+hgt9y+Hoy0RpOunlmECsOHJbfFqj4eXvW6kVe+sXWfAeHKjgMfjp+f91QrERKBNp7to1ftosW0b5yNhE/CWgBI+hS6k0yr2H65pt7rjUN25rWgbK1hxyiH0dLjgxoCwEJYlezm8FLFNqxsO1V9qEzmGMWkmxHxQakKfmApsi02GzwqWe2pG4QdC5HlTqVQ3ad31zkOcrJweACEuKIUm6MozaVJLOrAcSxDGuJ7lOQa4vltXKtWfafv1CJ1wdjH4EezoglG4TYHCs6RLcDp8Eks6gZ6RonyycFcczbZSqa5oWR8cK3A6IDMBJX4oTpyyMKd2FFfJlLxUC6oFYIpQ7NJkQu5Q6+5Z9bZWXW8TewKR0JdaQijQyrrKkk2uRHMGR49SaDYOd0jKFZIlIx227/QBoer3IDc2wSY4QQ8kC3wAzQLL0gyBFBEDMOYV5eYRK/V+5Z+WbRRn1S9aBpDhJMEJZYAaeQQ8zBy5MJGa6mV1sHJhC45AXJIDzrrZUIkWdW99IaEMhiAc+sA9ijbCHEMJejvv1zgycRFu+1qfpKiCtsbBDABhoQFEUIUw5ZSisiQeTPTKdLz5oE4/2Xh6rdsh46IUnuk4XCWy2K9LtuKpkVGLsNAcWv3c+YlVN9NMTQB7v1i3S9dUXOkX2eZbpXpGfTHO/3O93p0wXcKZL5XqyVT3fp6yfV2pnkerdvAz1tDqh85Pop2xfuayRr+//wSqG/8UanSrMW8tullvLnINnT5Bma2W894ml3KtPt6epdbG+aeTM/oAZXZaf/QvfGf/OWWV5nnpL0k+TxtL1xnvHaPcWQDDWWmemwLJglo4zrGZv/MwiOATLiRyEmgpSVxZEAf1zvSnp0MU1jO+i0lpnp8CyYLWOconpC/8pAY8yUWchYtGPFBBZwKMu4h4RA5My7SYGTV+/q9IJDnFHKJPaZ6HVsaCIUhcXiArYCQrFlG0QiewBI34kATQUoOGMCY0CviXfG4yCWKQEXGxcndBvpipGBRo1oeBD61l60ZKg+KZJlAHjUiBAbywLEoqfnIy0jwHw0h8isDpwZibDjAvaXlP/BIm/jGI+yEV1U/oHlmdgx9hVjCVNkcWAe1QgQtugygGOqMVuGGLWSQrgUFkSvT4ytxALIms6CgFSH3XVaqH1P7Ie5kugkLWRUUoQBdOrNQtYGczk+eVEmApRCw1qYy5cHpKyNCMedQn2w+oTVMuTyUQvly9cOQq46lOpi/yaedrMkSWJhyoML01CoNho9XoX/F6MK0Pk0hkztvdUE/E9E1ARmmflmeU6wlEFVk4D/qRxgNp0brzWzrJ5TH0EfN0rX7p6FG0H3IWTnfScbp6Q2C+HUpmOZ13P9gUGPaV6gG063nnwuvEG8n5JF5cEksPH6Jsbpd0UnM+aZJIBOfiijCUXSaejji/eGG6eV6vX9b/9Fq0NsMr40J6p/zdp489BpOfukEEp7M4ZTRpAW5yY2NMQuVER8phZSwSZGJXlnGKM6DVjcYn136AM9jJGQEkUIBaMI4tMSSMY1NKQGyKTx6AHtGnsET4H/E+/KNKGsoTzFTGKcSelwS4jHWkx+zy0nnESTca39k7g9W2gSAMu4Tk1Fvfobea4oOxycH2wQe/gQRL0PtD36Gw038/pgMKimVFLfPHlmZnZ2ZX3U9bx07adet5LwQFl7bJt+rzi61I0KkpdlCqjgqHyDYEW2UT8cLIjf1Wn5ZvXa62eRihCr/b6QFXDattBzdPfTHMPj/WXqvOF6ATzyw1xFRBj/i1VBi2p3zQpWrUU586lQhFUKeDbcJvA5mwbTXUxTSHXxFLxTPC25/jQBPu5bOE/CBwndoeG378zS/86l/oQOK+oBuA3N5oieAWdzk/Fq82KIybQDEOdQrWpBQoVwXT3SKYzEdFwRiyFa1rPeb3gOvT+TR4sWJQC18sKootQIEyqBb9MRWkdQK2wGvAUTPlNpOTCqT6calALCfXYbmn3JpXpu3R8YgNylEERVZxRsBlQs24yI0lKRLGwD06JURB8A2p0cyteVV6Pb1L6kcC6MeeWhAHlE6qMz2aJhYvsMINOZxeN6mVSJvyOC/eH/siEBHFSZoTXGJoT6MaE+XWvDadL8NCgor/Q+068g2NVejlOqRm0DV/eu6z9eMypGbRJX9M/1P1tBtSs2mXvw/4edoeBqmvBzPrCdFE+MjqaY6LnPdi4tyYXQiKIti7wgXWr2rFOhP+MA75LeDi4uORxh/A9CJGiNajGf0fPORzpCFHnyqCUYj1XDE6oFLTt2MYyNqYtHWqz5CqgHhRflCiwhzyg5PP0vO1ra+DABa0wG2ZcTliOQMFLKvI0AMHT0J6BoA+1QQbRZGsUTVUTx3uut5hyJH92N+vCsVsHaSoAKMO1/xxo+X17TYMYGccuAebmIIsBuTYRh0SYulv1s0vbiBHPFgqWDX4lAC2mrKIAmWXb0/VgG4KkssFym8Tw8c8mbZG45Jv+butS+t8YhtjKTiazBKBCpfPR7Edwgmcm5sMSAmjWY+OquteAAXS6LBWG0t+Nx1rCXZ6gbwN1zrcXEmjkpLzdcayetr3LKopnBurQAg9DloJqHGoFrnWwoGIocFuKbbidJgMIk3gm+Ccsq20C6UxxBu8iTlg7vP9jOW0PYBYWH2E6KCNFbLDMmMJGLLYT+ENQ+yzsSJEcaeRBHflVA+culiRHfN9cL6fsZh+XFhoNAbJXJo2LvxizqBhfLA5lJ+bLKRX1u4fEHg9VuzMsyh/dG4BPV371AK65ovmh4mXyqkFlC+aH66flz61kC75bw48UudTn1pK+U7zI7XrU4tqt0k1rfS7vhIaBXOeygVzan6Z+QLHSpbiojDzO8AH6uUAJ5wjM45MHMSNrJ6dSlE4K41iDXIh0fePzBMREzwcx3gtd+CuS7YHOuSvm8yv7e1vTgtnyfiDeSAzZyFPATTUA5LFlQi0AHztIwK3D9PsGEp+rgQz3KqUkmkjMnI9QCcVCrEUbRbXjSH3Ld/OmFs/L2ytLLtDE3p0ZjVrH8T1AOBpArZGR6HHiii9ADtO6mEKIgt1Y1gDtJg5JjXM5JqEaGuo5e9gxtQJkDVLMwmylHw74wH6fjKSTOIGitgBQcwilKR+tqZqijJwgPLGn1KARMkCTA0ApDgtyjJDTVBtpg75/i4ykycT15zkUsMc1uRS6OXISywST983qfn0yqbEuhbQZUXkpUM4sG0RpCQVAG5Fsr9TB4BVivpqAjcm3t7fRzCvNhZ+jUo/dwbsK0KdUKqKlANYatLNfPMD7fm06wvCphm8sBsjcSIINwM2fTptwoPitMileMjAD0wYlMEORyqREkQ4CpW88r25ubQfhwVF7mLHCH1Yd6qf2jU9qB9r453rAvab1Az6ci2pUeCWqHzN/xHifj0dS2oFOuanJvfqOVFeiY75G9p3onwoqZXokDDfo5dEeUXKz7MT5f9GCfOH9fVWSlfeUzd+xuw6szsd6Am1ZOCiQQ6noNGArn6FIfHGOnTFbAJomEdlEa2YSaclyof/9nWT+iDKXeHBoesEJGvbqVdmwJMvQ8dOwUFZ6vNQYNdRkfF5dmKI2m0gOcl03JPF3APXVJJUlCYXRjnimbjHn9kyAbvihPk+lLv6aGyJ4c5wtmZcOXCyFrn4qeixsj4oRoXCpYpggQuUol+pjQeZnDRJ8KEclvuD4CL9yGQzSe5BeHd3vFBXRf44akq1rJUw/2bv3F1cq6IwPr7fhSiI2KiFYKHIMMiV22hxC+3kNnMhxQSSGZgmdVJZWaUYsElQkAu+UCxEUSzEB74QBFH0b7AJtpbuWet8+WW5nJPJnTE3NzlfkrP3Xo99krV/Z5vJzYzXpttf9LUWCWyHLLF82BXYUrAkg0cABOukkQVBLuSAo6LY3QN/LoFKjE8jozpMLztHaCsPMAYtxYYLmqrIbU56fgR4TkgCL5+YY/uLzXvmxX/sA4lyiEhS8CI41wByYFSSGyk27EWcB9oJmoqTk4sLRcQxgzwIq5FJ3kCcMAuxCUm8oUBUTj3yQ0Conw7ND4ALf678bxiy8NRbsOFB8HI2tRaOo4tgZl5yXX0YZhFTM66dufmceVGUG62sGpgX+g7GbqMVVvPdjAbltVED82l1abfRiuvSVqPT6NndRiuv5sv5p9H2bqMbQM2vTc3XRRWr6w/EeI6JdEbzcvDpcFZ1Q5u18HPDPd+6eEh30bo2v9A6T090ZyhUVwe3ukfmEm99PKdGkjxyg2khBjlnupSyKXtyYx0OC/HX7UavDZm1PlcjQsMkOjR/aqBeT3VZRLo2oJqw7WYSopRstCtWE3pfN49hfiZVdGSVOTIFrb3JZDTqvdIfDIfDw8PD975979vSDI/V7/d7o9Fkstdi3nQtmtF8CSu5PSaeXDaEg3nUhMtWqcyvufMJSGn+RxB1evqFbljE7vQANbAHpdECp4BiFtbJ79iKFB9I9vAi+S1Yjbt17iuT13r9weG3RYVeZKP31FpjeA9eGY8mV3g9vFSmdgPQUxDhphZzunbdFK/driaDbVBWcKyfTjS17DR/nqvu+0RexkhWV2WkvNOloGdxygNqN7P2QgbcZRUtYdW5Vz4lQlbR3mTUHxZG35vhdQqvyUfeuL/Ig4b9QjS7sbVgq2fIuV1CGOJjtQi3Ln1eAyMbyiIrmVX5JIrafM/oRN1yIRRMJYXbYAjLiB3Bm9oQSD+uWkiXwXNFNWpNRr2h7bMObWmnHcblbq0sJs9S7LA3mrT8pBKsqQtxkEmMLvP0OqOfOoZpGdGn6qEK7A0Xmn8zOUGXIof0KDnCUOekm/3gnJWtgpiAK6/1hmBrR3uA6bdulgu/D5xteQ3ooytANN1m43kTfoxr+ijPlNnOeVAdavfcViOEttMyQXGgTj1GmX4GOJNyHiBhxYxlb9Q/fA9BZuoAMErGmfawP9rbTU+Pd0jYE27pMs1R4Cs3hlCJ+kuZeZuPmRF6vntq7Z4yBgzrcvK+R4uwtF6D4/9DxnOre97arbGJ5JoI0I/+5v9pkvVM9/y1O2clF5rFF3PSGxpt5U6jgYkGr/VcuUMkpvIR3nhyhudMbH3AOaj588xT8WncyuvKUf/QOAvkBVgxgHFivdis8YOH8iiqerY9r5524/CF5pM5Fx9hdFdcrdGgQAaaQvDQrDKBsRlkkjkMPdWPQrvI+5p6NXEOaj7MOPEjjKhOHGY3R9ocUW/Fko2ADHuOqYuOAeqt3LAJuRXlyvh2dh7ly2n3QcC5Y3frqcWThW9+ELNnc05l2HwBdFYXp/ViZTodygay9BBxYYJpIguZpqDL6XzEfLuTPrzNFyyGFEBlbA+wJ5gIf/QnPLNQDL3cRJl7i2pwZcCU0S1nx9sYR6f5mhF6ytaoaKaA1M/6YeRRRR5HX2utrLCy5lKSMpjAuoFuG+/1DhcXTGbPNU7X2+Pphzr5K6Mwqo7XQo+YqGpCKjVVxzwyqMvaWEfZzTczpKd3xGOHRbG+VgSUFWFjSg7+wJrWouLT56Bh3eWcnnX3aHi4MhqOdlUAXoYUa0H5qISopxCECehK1GHqBW+/e4hszTczpj/3UUFruIs2UZZasoBX5TYn+MvNGtDRusWFKlvyiqm3Bz8SVGFJdny8ZJQDWQc62r258nkuzc9/rmdTTecaUF7VLFzz7TjKu+QV1GDSjdTG0TxEM81s2/IsIFKa35k61vOdFVR3ld5cRA2Pup0VVPPvf1tbD+x0Vk67o5UluXrj3Fk57Tywtem67cXOqunKyr1Nzuq1OqumFzf+rxld6qyYWjcAyatJ86b/k8mqvVm+EfbklaV5s98yl0+W/z8d1I+T9/z35KE90JrTvNGfMvsny9KBmoOqf2Ad7BxDFsZojy4GOdIP3fH50zbU4X/SuBsKd/JrLM7kUpbbiYh59BXGmeQ1w2Z/yrytIlA3aHZPPFBUoe68gy8eLeHUZLeiOJN63dFwATzTcH4CY0zJsbCGo+5BvG4BTqXQkUJRIlhmjAsHQ6w6oM39LZOnvHAHLuu7BfyImN168IRausMay1KmbEXMVTm1YU0GYswONN4/9uGvTEhOhWEvN8aaQMdy0zTMSAKu2otpYq9KUFLHqpKqAUVQD8IpuIqieTyKFOtxqeg86m3qFzNuf6kiOJQfmyAXd/gAFWqZSMRyTWhkBqiG5St9ITQUZYalqCtDqJNFTFYNIcwytI4Sqo6czrmw1qTKs348pWcTp4uqf0XXsWnmhWlgfjzEqXYeoIN7rJNTIuyWzSl2NvSvDFwSZkHJBtegi3BwAG08WhdmIbo7NkiMHyjSnlr1BBQkuYMMv80QqUZ9k4IUrrll48IBfVnZvAVzZR93eWVqGVBBfPRT5yTkcQdF52Z+MPd82mBzOTswiFmDbJ8vAjEdibMKXNHG7gm0ECrZEMmTWy4FctjhsZowk8ibGHcGTfIVHguIIccgvDCMK0q7cnRt4gdzT+8ApDU6+DjVX35cjNLeSwAHtmPdbNTqD0+jw/kGrHOzoRYljutimFeR/VZ4cWrMiDUoRmMJjpQcBgpgb9nED+aeq157OwFJN5hpdcCeemkl1SPMY0bxv/aARONWLMiHhJsYyIVR4Zg4se5+GISJc4Qc2P0TDTDLuwICW2qjBqs3zENEppr+5v35l4upekXQ14ZV6kwgkYzxqw/I0cOs7/ThMu2vg+Hxwamq4AJQtwDjoNwdMYkBqmaCYl0nZrd2cPwA4zIk1IZkySf/YC8jnF49yjExKJcf5TXAtGm/MaV3GEVtwRurIk9kHjEiNfvY+3HRG8FXReahd40ojAPFmcPMyvKRD8vIMJTJ0iDWJIfNI6e7/eTq+Oxi1rPM6LTrJIPSJWYcXncthxI1bIM0MaG1ezTxkGnj3mU8FyqJ2ifYDffFf7qrne0dRwVeHEQblUbMFCsm9w98xD4pPj3Zd9DqLqf3ZXAohx6vWCW43S0k+kNPo0r0aTjTYC/Uqt1t1xchF7Vtj3oRnGfcuHcZF+sL045FltEcfsgiTi1HDBS8O4LjiiAhicN5IcyJYeA3zDgFXTASq5ZzymczM3+RDiJfsyszvoBRB5LpRd4YtztuIaYy6FZFkWC3JGyb9i7j3h1YMzrLzQm2xmtDiD2op4V5YCi4zyNnqrQm8chW3zFyJESR7YXGihnN594ZM/s2xkNZxOyh4gaWawO25bST2yz0dSEQRce94REuv35Lu0FHZaVcpQSqcNoZzJTNanNJqT1H0869Wxsjf4chhtlErNQAB8hECVo6HWuUKIeK/LbzbeauFd/GR3qr4JwditihsefAlNZ8FjJQxJQ8dYvVvQytJ5K1EQ9sHgst8nPTcSz95Eo9FNOC3N3+gPuqyzM40jVORcoxbNM+tgBhG7mcprNP0DX7dLHixbBhn2U8P1Me6BXY4lNkq856ZKDdKmblKs2rcrvND90Cdc9XH3C1/9rIoYZG580tGhfJL68SikS7sHbeZNdEJPgA0h1Qb4SxpiTK5mVOk7W9Ltf59PUfMzgtt48rF/WMmEOnDTQl27dCWSI3bs6/mNy2cxCQU8PY9lPwrez0YVzpU3f25kV6p1pyx09EmKkiD7xFncYQQ8aQY7yJUEEIygQohgBYh1fgnVJNhHJ8bL094Rlq4BwamdS8A4FxZ4iYi1RVGzcLhHtnQ35h6lkvFYCpWhFuOoColnjFEZEOuHymNwfnpcg35AUDQgsEAP5COtKr5243rLJhjDE6eIu7ruQ4NuNvDDwDgrMF4D9ngg/YwZJB5j0WV+MqrcjN3d7gxhIYL6Red7Z2bWFbJGt+G0wtWQOP5xHope7sLNVxE/4s800XqC2vnUIHrzYFNR4I+J6Eg0C7EyDTQas/2BD1W1YpO+jVWwecfQTfHm5Gkc3IBsqj2ni80X504aattddFrxYv+vhBYzbqGI4kQr4S8TOciuFksEGaVNWiNNQ7lF5IQqUpovwf6UqRQcFmW/8Pme/dEVtUzO42okwaqI6IPMxxPknbBCswGmyURmKYosAcW0aoaxYZWeQnrf8/ZV9qL66DWlMEOsfRdnpa5H65qachoxSlFvUViQcH2UzL5FGEkIsw+wPRT+ElQSfsdebXkxKds9b9a/nPtBGbw0K65rp3XynLW3R8EHY+FANm5Aix9vBgweJWZYERPRsAJNODtqZzA4F2K9IsckC7TuFpSrAUVOzd9jno4Foqvu4//t10IRRoASrDu45ruhJaTomk9Rc0Dos4VRD0iyrGkbcAkbZoskUjYzdyepHpZsAEb54hB56pzkuqZbQWLXC9/eC0hV/7H/8unsDnErbnCWuM0jgJ9KAHV44CYMhM8fOfBZmgjQ/TgC5njimTs5Yze07H83r/+Hf7zhnQPFvaUX9pgrnV0FH7emmdfyv72fYZdHCW3Df7G6xR+3ppff/174H2ddLBuL/RGrMPLFlr+0eZnztDUfaTgeM8HfT6G67eecC8fw2lX9cvfz6VSkNRGNEgGfCROV+dKcqv9BEmxJDuIgaUg+gzwoRx/nwxJrtpUK9zClJZB8TqsAIswHyt6R/lunBiBRkmrstNpaOXaj8fZdb6FS03B/lAxYMyiOY6jrEmBrmNaSobBKpVJnk2RgrA/orMssoRIoI4c4Y5Fx9Oo4e6KyhUXrcTdWFrHfV8G1EDbxhrSH0jtTFFbgDPKFeAFZWDAeE32SHFgtwk5CDWzCCu2UjuV6fBpsipXwnMztBGZrYxI2tc4OlD8xAh+8yMPGNgzkqVjXbZQJoGocD3On4r/5aX8jsGqkIvVzWXEJQJPnlX1qqLECEkqyMiB0QSDjHxQAroEa+Dt4pnZnOCHOGSU0uyguQkkAPXLfJpM8xpQagr5uA4oYuUivWlNfybzBenbyGgkUsYQCmfd+1OVYnUTY2C4N1QZj1RJIYOA2wgiPJkWeA4NxJlOhdJqLcZzPvTontf5VXX/YSxXmqUQPUVE0eK2l+/fzC5bWefopVeNQJGG4OuiiKeFUP94J0dQbOoFcqs7DkKzBeMz9azq0+bHMDsJRS27CdsFi6RGBdGBrf5wSEGcH481GD9fl1qe99ZU1Vc7AzVkEc1FpceoWAlykKhTcK+oNwo7MyUTZQytD4MU1qTNywXRFsOWdPrRVxvL/61nXBbtW913L4DYlOQ2R0Y4QlbeHJLMKwh8x40KLuA+aCqDeVFlBuTbDhoGCCiuWp2bl+U4tLOasWg3t5fvkC5F6Hu2R0Fn7lw48jJdDDQ1CufdX7MNYvnPT7YX762F6AYerkJ6dXg2bblJas9nlLc85auW+2hxmJhxg0W7g7SvOupPtLYH0Vm9Yc8CvY5LAkxL4MqhWcuIn1kmsYSzmR6QmbWMxy195eu+RuzIzrlNWnL7KvB85K35W8++uibb76qlrsii5ETIkxFDFH0TNALopZkaUBF4/6q5QhQNhLi8qnL5ObgWhKfOvCc4kvT88Up3q8XzPUbs8MJx3Xauu44L3db/vpypQ8dZ4iDT1a4p3sOEBxYc1vTQ9jpcAI8eKHUhZ1LahopHwmE6oD5zWXDXL8xO5YJ5Hk4n57nG3lb/vjyjD4f9xqZ0NH+0rVdQ7IxDMcL4XwK3dDb8jeXL//47rs/vf/j50bzV71GJvTO/nJ18sbMm4usm6MSzqek+Ubelvc/vHz5i5dNP7xl7zSardmFPthftrZP2pP9Vo8xuu40L3Vbtn35t5crvfttszUj9Mb+UsXGnFE+Jcco4DyP5ht7W7Yf/b47/PGXH4zm9xuYEeqcc9Gv/vnrXzXuEzbmhUhGEeZl6pZlf7b8kX7ue//T46358xV6mzGOAwxjuohAmgVOU2c690/m/vr+j/L3rl+f0avlXt2Kdp49UZHkGZxv9dut6mScl/2J88X9/1dXQxs+x/j8J8Hcm+XGO5gCUHYInrrcOKizLBSAl/EY/hFeWp5qeZyU8uY5lV76+fs/fv/91WtS3JThOCnRvOyt+aaXeOnlJmG4etWs2NQx5eJdJVfekPfZ5ctv/fDDb/4xxt8F5h9K+9W4SCQi54O1t7tF9rhzkEcpFoxXdvno6RbPJgAV6SeQQafS3LKla8tbrj6eNkcilThR1axwWgeqmpblqpkscF8xVXMmlncyyXBcx3Pcmpeh5/etAmB73KEKVqKAo8XLx5AYqh65dvzf0OcYn75/TPOPpfdLaX21HQ2j1o9TquSuLN4o3IKhCtbMDPpCW2ZauTWhrD6xwJYJBrkSPMNPRg54MyVPiKfKmacpbwhbmKav9VFdtblYn0WzsXnOyvJ8ktF125ovlNfN/aqQU9/KgsG51wZhHZdFMiTr9a9f9yyfuTMuBP/0sumLtwrEvxSqvysb83SVQSkMWG+sdKEA3EQ9ECunsiB5xS4hAE0fXEWhO2G00lcuRLrHM4/J+Ze/Y0ULUH5yrK+/MX2tXRiutUj+kPWsLAvl2+994MFnnnriofvuvOP+h+8vuufO+5545pG7Kt16V96cl/uW+RkxF0WVNPYukSiSbqL5puD60etTY3s0/qpYhm/98uUxzT+W/rsvv/xTaSAGysAiCbAY0mBMg1oRkmdKbt2jMJUXivhn+xg6iz6uN418h/YjE1Ogr6+i8L5PNvXOyvLNtz99TPETDz10331333nHHffc//DDDz/55OOPP/bYY48++vgdTwC0lD/S+P916T+wPEf9Q925vcZRR3EcLV7wijeMSr014l1ExRdfRPRF7EPIi6+C+pAX/4mSh8WAIGJMlKhNuhKzQmC6IbTLrsWYmtSYtQ+u28bF1DRG1huCVIuX8ztnT797PLOT2d1swe/O/O7z28z8PnPmzG9m20hGcFz7fu2tUm5QNFkgizxE83PENBtm1igvJA1tJAkUosw1R1cQan3eyzdz/bpGqAHKBmbpQbspseZZuaDBdpRmBLpn+aHrHn744QcfJJYDzJcBZmKZYd69e/cdl/cBZ+NonD/L/FCa47DPRJLel9QACh4xwyz1B0YJZdU8mebiIHsc05RjEIRNCWWhDy86/pTmck012ihJiECMbIXMqHxYlNXQQKZF+sUsLQiRhKbjsGAT2tFJEUT+FVVOzAPatjTPnSxPBy2RGRjHgceQcJoyWidhFyxffz2zzDCzYb6UWL4Ghplhvuuu3ZfcEkvzebPMj8vuyiKBhFqCRQO0DkkK9OgZsiX3MbkTA4sMczi6L47SCBPdS9nFZYoXaH6Z4klGOkcQKBK0akJNIZgGi8BR4ZIsTgbNSDlCaQ3DLg21MeeVThQ1Lv+vSAdahc25BTYn9AZEELE8MeoMttcCQ7vK0GZZrqssNTMQc0YKzCpj08TyTFQWRePVenVYNBVVq9XxmalylVSesixfbwwzsXxJYPlmYllg3h1gJt2pxtnD3PsngBc9pmcwhSRQybBybAhGUw5DWvOhDthL9SxbXYH5vX0vj/I4LspY0LieEcOcbwyy6q0JXMgn6ENJCASbGoMbLRLwaoy40qaNJlzfVoKs9iUfyZo2fssSn6RWedrZ0dFmaIcYWmb2ODNbGEgjuV8+DHIV4sbAYBA4YVken9poaKZW26zWCV7im9K1jfHqzCZpqjrlWH7YG+abYZgF5gceuKPvBk/z+THMTzSggySrRKNE6ZRFk/LhEm3BpVogoCrMI6OjObyMsSiVCwz3JDnMo14TE6P0AScpxJQqqKNWyLcp6SswLNmkjsDyaowtzYWKIYI2P9CFZB6TzANJCEaiATEq/usvVzcHRJWpEFQqa5WpqF6j9FS9yrfkxTLBDJatYQbLdwfDDC+DYX7gpqtAs4O5lzg/q5ziaIDdOBmb7CRsIzfOd3YK8yGCqfnC+ypTfJTpPhPPchfsbcvuRKrijs8GTD1CdFswF1he7gJieBgRBgqhHRnNGH+ZsBU9T1CLXqoGiBfr9S3OGpgfu45QBsyYyiCYlWWF+QHSXXtuuMHB3Ptp5gfNTns4IYOoq0Fk+4rIN4YRJlpLUiA6oxSvspkptWBlIhm9VhtNBKuuNSkMNLVFMZKu0PeENHJ8BXJO7jLtZYB8unOI4WEciTUxKDBqZrm8sZkne7y+GcjNFwsUvFTfoF7Xz1YorK0HmKsEs7J8HQwzqR8e892YylDDTHr0phuIZgtz713mZ3ineyOwDMtcMiyvssO8TEWwy4LfhONywhZoywkDNErRUrMSoytsp1uFKCzwbRpVmkK9lmAj6VvSUkX76xBkTwqQd+dhpB64/977TdUjwrd69myZkI7OjlN/G2drIQzBWv1sMNf5qbJhWWHe0/CY77k8sMyG2cN8x21Npvn8uMxX7Ci477VmGTCHeQ2MLFkofudzNYQl4kBQFAkzklfGRYiFTi2TDGppUbY0TwKtaCoJRRVprQGlaCNZjYPmchARO+QQpL3kmsJOeBjxzCIbZ5fhZOS3sltklgu1WoWs8Hi1ECzyVqVSK5dn6uuVSrEClpsM863kZPQzy/dcA5itx/zoo4/uvqXJNJ8Xw/zIvl4JLH8yYGAmf1lvegqDRjkAqszxYqTM2XLDM8pkQSMX4TzBqZFWOGn0bHFTbXQaWxV1mnigS8HDcErDclW9YrrvCzhvlqvrIb9e3dxcj+gV0FqlMlWesSwTzH0P3hq8jP7+/ssuvYdgvp9Yvtt5zIkw9wrlC57d12O9Z8ZtUR+QsPIfMNoUSFgCcqDEC1Wdtel+O5w4VoNW8IrtASBNdolyuIq93znL5eE8uRLEc+3s2RqlqvUo2tgskI8xQ50P07xz4Lo+DJYVZrLLt+7ZQyzfd999BPP99zPLdl4usEy6/TaB2bnMvaH51n09l15PMZZHQ5wfyC8uUKZUmisRwyGaeC3w8NprEvFHCjTBi0gb8qqNJaQF62vIaVYz6Iu3ItkS/VaOz6BKytAWffJUm5HzJApSvrzaHcpFeBgdsVzfDDMX68SyTMEVy+NT5XpRWC4Ml6OoEGw0nmHDMDPLBPOVDPP9DLPzmIXmO28g9dww486vV3pXE/zbPgvzmTAL1/gJtmJLi3IoOUqohCdDLco5IXlsS9IC2yOFmuE6nCVQ01fouVQ501TDCc2KZIu51NMTC8e7Y3kIHkZHLFdreWJ1M7D8EqWI6/Iw8duaZYW5r+8czIQysyweszoZ8DJI/Qbm3hrmi0EeQi2iHC+msH2WI76gWpjJIItKShGFCjQQVWYAqinSNkKnhYtiyPXGGGsSKVRzBudBpYBvcMKJwPPJabTU0TRGMRs0TToanpK4gbNjhzqNjL9cabDMMxbM7XYsy+1fX9+twjJgFi8DHjO8jAduAcy9NsxPYOclog+F8VxaspHCQYtP0XEvGJjx8uNr/w9VBqZTteOptnRTw4n1zOzidNDyZNDCoFPUBO67hlvEGFI/j3GOZUrkKa4ns3xVYPkWNswCc7+BOW4qg72M286bYX6WD4TuMGUkixIq4kVSoJ1zvDHaYxtJaXo2OBUG5v8Tx4xyfjRVQ9qvlDduC8K0g3Z+MK1mD5vDbPGVEhkgjF6sXa4XBvLkcGxVt2H5KmaZlGSYvZfRZ2FWlnde1+tOc4QUpxlYHBN7oAB6WBsJbYqeOB8Fw/w/hplGvZKqYbj1S2WW5+lmoXvNtjkssSxvlondjfxAoRwlsXzFVTDMgNlPZZAIZXgZ9MgEMF/YS5gfl1MWtlbw5VhCkRQjqREXc0rrTDttOYt51tQwj7iSxMY9VLgGv5KW5aPpzHJ6zZWC4usO62FPOYyeZVrXwzsYAeJqMsskYhkwY17OGWbYZdLTvTXMmFzW/YTHAOmh0HoQ6iVQO5x5FcO8GAezMjgiIS9YaRmxlNpcqHelpkNeJdQA34O+0RB5aUaNKuFWjXOuDn+BrCmnMQpx886rmHO279Q1/PBlf/s4eATHP2Y8kJQR8SxvBLtcCz7zGiXqiSwHmJ1hjveY7e3f/TDMvWT51thjAXx9GXLbFyA1G56PFGIt8wgABj4Nji2LKBeKedEaSoawkUEf4FVqNKAIXWiIvklaUuGb/JDXJhwhRWp8+0QOp2ySlgdj0DyOZ4GG+hx9RQlOGhTeXU4YKC/PMr1SVBgPc8phLmOpKvwO02VoxrNMMKth7ktjmPH0zzkZPWH5cdlFaMzkXC1StqXLjXEogZhlPMh2lpk5gBowKqDIcqw4mXa2JAgbasF/84Ig6lQKuGAvKGfRp9kGfUopsVxMY5bjTTApH/PyhvS7GmOWc3Ks5SgjTMeyvNmZrb9EYVRfD2E5oudXw1RQGOfnfoWNsmNZYE64/fOG+WmCuedOxq69Yd/HaO91DXngqcXIgk/OSBYFwJjFG4tZPv6qupIOZo8xhGKfL1nZtshYlJthjv9aJAXloKM4kyDTsyjdNEaYG/YN86EwG/dS3UgJL7DYn5ToKIBjydCHKvb+9P2pz7//aa8OhWW5vFmpVWrl+nqtthlFU7Vajd6+2KoN19ezmxvRcDRV2RwuzxiWWxvme5LmmPvPh5PxJKMmDAJqiaRKErQiyYE25g2jaP8YS0iWlIry+8NQFOZxXfUwJ+ProaPhdSoZfDsSLLVBuZhyc/wiysob4LjX5Oap/Hjcy87BLE/H3D7mdNTibMlPn3+zclD0zS+nGpw3s0xPrOv1avRSFH4dNTVOIf/erzxDFcEcz1SrIVKWL75CaE40zPEe893nw8l4SlCWHVWQDbKCsFCqSaQpFR2Z5YceRyKFVyvRz2GmODtPljmfDHN6DcZoZKdFMxisM4nnGZTwYxFraudy8SbYexLT1DjWLNPRjOj4wvZwSuPvvzlodOLUGFhOUETreAggzzJgTuEx86wcwwwnowcsXwSDC341hFDkFM2CpCP7WzXmgROYXy10CTOoWZg0GqJB/28jdNyZGigXUtt591f59+cnA7Ijc+1MZLQwy7N2vPQ6StqrJEMniWawnF5g+WJlmWFuxzDvvi3OydhpF6NLHZEXcfUJaxTfKtLf+xXnqfHxGJhH2tacuxxnbT9k+Vi5ufaBBsqiWuotBo1kv61kynik1M5ERkuzHK93f2o4Fyu8rijNn491x/LFKQyzgVlZfqCHLMPF6E4807Za5EO+PEjaD2Pdrtq0z2zq3cUYFjmh37ZRzr+e/nKR6kX8OW6YfiKjtVmO1+dNJJ/kkPXtTrBMCiyLYSaleMWIWCb1luWLdgDlVT327EEcFjPcsUoHDoCLA4hoRTEvPOruJkk3EaQW9HoByL3Q9wHzLYyyqKJFTvaPkk4wsRLnRyzq1SP9RAbMe6JZht48xfyKTsBE/9wty/GGec/2L+U7lncc5j2dQgwHY+k/ZmU/I965iCdiQgKQ7Mk+4Md3EqdCQHlhSVjPrs5TrgReaZXA8SiLxoSymmX9o0ic4BCdaQZdSjd4cGKvHtw+biJjsMVEBg4zCmGWvU4JvmyW/zyhWIPljnRxELGsMLd+YILX5YJgl3t58/dMVyhHOMY4wEfIW4PT1/4v5IUM+VBasppnlKSEL9EOBG5KASWHYLULQ8EWytaNVfsHlZLhhMQVPJOQQlQBXdRwj8CdC/DgxEwtl7g+fiIj3ptY8E40zLJX5pxdPvn1n//880fIgeXxF95Pq2cfaRKzbL2MZMMMJ+OunrO8a2+G9nssQxoTcR4yuQwy0n7WTSAV2VhEwKz9X8gfSCl/+S4SsKhcyJsLMptsqDQXLzQSlEXr/uuTNoXgRkB8vmIPvAn2XjRMhrUaKoyfscsM8EliObjMsMufNY2jGW0Ukygg7b1o1wW7gi4iXQyYYZgBMwyzZ/n2nrN8Kx8DWinmRfeOA9kfLpFFMhKR+PmHN41jY4cxSG3/Qr4EWnJJmiMS/HyI1jqv8zjXzQH1lprzKG9JCVTKxd64avWIxocApjlEQj4mFaEFmRRyc40UWrFZzjTxyKEMD9vlFTXMf/zx9wnD8pfcxuBLeSWBs2+eg3kPo8wwk6yXQbLzcrHvMTPLd/ac5cdl18FpA1/dLS2TCoEaoBOyqzEDlQlu9FJHHgZI4itwovzlexqV/oKBW0tSYscjFmVvlgnCeB2KuXYs+xNWxGdcl5qNMjoqEpGUZdUKgXwixE0sK/KyQVixsN6l9U1OP77rAjXMQe72L6Vhvr/nLD+byQiqIuyLSaCBHDZNz8L4WbscV5HyHYUmDLaX2x7yF4yGGn1PTseLTKBB2avCKM8vu01xIho/yL8PtJM6nDknBZMFlo2+ejPDLPOoIrKdsFl+I0Mh6dldqgsV5itivYxkj7m/1yxfn+lKmBQzc0pc0QHKi8ayhRmAbGstxszcFpN+xs+lC+dYXkpw2BNRrh04RG1ezefdpo7l+Lu7rJK/M3o3E6tTK006iMReZjmdhObrLcowzAJz4pNssPx0r1l+ItOFgKw1OrOZ/al+JeTnSw0MrnPvMxzv4GzJMWJwT7yXMpeEcn6TUV4oJJ2JkJ7tXkvHs1bosb0rWZSJ09hPJ1aaPppY+TqTyDKuxG+eM8yPCMpwmQFzesN8+22YX+4Ny89loJ1heTIc3Agms71xyYECf9fk0cl2crYknSdL26K8blH2fVthbtI1dydTh1cyAi9Oew8SuQTxCYo4wamDnyezDIP80ZuafI785QuYZM+ywuw95jutYabX5Hr70ucVme4068xblg/ufr0/N7JNi66e7dqHH/q7ppYWtND+jF+O+v+QcBxqhUaO6hNQ/hAoW03KmRi654g+SSfkdCdOmD8h4C87HTy5snLy4AoFJE6Eu8C0LI8FlBs4X3EB7v4czIlzzHj2twcs98YsP9klyzqP4Q6uv0EHeWDRKTcXUOaVWE52IZbbfxxTlLOFWT7a6p3iQwlWuWBQdrtzSE5ECkIYMnM4QFYFnExd6Ghw6Frp509/P/3DF19+efL3oC9Onvzyi2OnwXIb6meWAbN7+rf9w7/dt/Wa5ccz3cnOL8v/a5Z7gxn3mncsehFKHxIFYbV3TV6T7vdWqd4Z5r4xu2BQn+fvb22Vi68DZX+aUN+sgDEtLNxjOgqXd8bDiDKt9NOXp7e21gjjLdbvv/+1trV18FRqlt/hgJZ3HifovMdMMh5z0sO/u+7vOcvPmr883Q4iSXs56x4Uh4P7Riymkw4sr+ADNGTvmrwIvDZxWGLTSYo/TwrzVJ+EchYo+4lx/OUQvsh1hZOpOw/jSGsn4fuDx9aOrRV/+23rGGnrt9+Ka8e2Vj7/dRuW30FKBjnMysUb5iviPGZlGYZ5N6nPoNwDlq9rk15fFg0SzDRa+E+tI3E9lrNO//WsY7RIPYDl+cSBxDRGOzd+5xAreBy3QXnrxw8P0Yax92xHeVuvVveYQ57xzu6V33aDpNlf//qGEF5b+y6wrPHa73+dFpbfMVZJQ0TUgBdebyHq/NM/ZdkZZv/Pft7pzHICy7uue/DGS266+tpr7735pssvof+b+KFd7czIYU8oRga1NiABZqJZ/50+QRmPUNoeHVg3f9fkL+qLbXW+jM6BmHGPklGm7QzK3uJ74ZUg177QtYfBX7oPY9SEJyV/Pf3XsQDx6dPHjtFK8VrIn67BLosLAXox/pIn/cva+f3EUUVxXCVtkUrrr7RqjYnW4I8EbHwwGt+a+FJtjOk/4KPpI39CH2js+LLJGhPpJsZCSBooBSpQYSFbxFZRFy0pWTUI/kSM2mobxajx3HP28t3LOTM7zPrdZXd2ftzd2fuZ75x77mU2eiOiiWeJOt35R7IjZt3625+a5Y62PfeLbhfd6bSntb2jzjD8gt8dPNGu8TTJf0Ey7VWQVfzuEszQ0CmaxSwXs9cOS4W0DePQV1O4MeLhvI3jVxvvVz0G3usxBMcPhDamOkM0fkH8orxpfyEIB1CL7/7wFaHrHNmxzM+kaz/4GIPr0NOsXklJjuSIFhx0JNO9Xo4ZQUbY+nswQDkhJXdgzxNPPBGQzHrsscfuu293y4Ht8dcrOixgMrohxeC74Gf6NQP6adaxjYbehXNV1FFTGYIAsDzflyDXdFS9fkk6isLJX7VsHEd9v7XeTof5UPIBeRqth+zqkTd1QQb8hp+8CX33kdY1WuBY9pVckCm8kidfRlQ4+0YhorFyNcbcFI6Xw6gMP8JIt/72p2O5Y/cTpKor388o3+5Rdtq9e0f7bTbL9xQ8o1WURZj0rzHHsy5Tfsm5SdK5Y/68dAo1lSHCgKXVla7dZKHwATODouWTEF/p7fTGWvYVjIoY+ZRdl/15pNsDyXdMnDgrLH/vJSzTAmJZVhKEg4qtre2IFkb0RH/3OJJVxJwcZOCCXEG0HB9i7H3CSQUYHuXdrHubD2wzw2VAGisQrcnHxohF6MZZ54wRxsmTDMHJkyfrDS3S5p+4Mle9lM4PE5tEMyFaj9f6uVryD1g0YUkWYXMugISucpWNvPy/RBikXlQPKoUfheXpYlWe5YJj2SmsQmxZOytilDlg1sash8vZo+UeEJSTbblpj6AMluHKYJl+FXPPHW0dOlzGHuCJp6AQX+j1ySGtydd5GbF8OWOEQRTQTWiYGEscvQzzR2swcQNB7CQXT3ITfJdnWSQL5YFuP1ZHfPIGfrZsgYOOX+EgwPYxaYw+/Po165Jeo6euOAcoe/NWAQowvMa2XPTf7/chy+GJl6TIBs0RBcyBMQcw1wsyHoUtx1+ztuneJ1gg2bRlQtmp+W7ZFOFyIbOOxZxsJ91CTmNkizAEMiaFUFYncl4q9HAvhBo3xItEE3pzvxBvE0qOIsz+utpvLcuwvgANuWlshkmfi9E9NuE4a71GGvlvo6sQoylu9n0kMI/SFCUzBjXLsOBQ0Qm+ORUON8GXyZi3FmTsE5STL4y/U1BGsCyujGBZSBaW77jjjh0HmmrHe0ayAxF2RYIjr4ikd5i3Iuu1dYyW0lPWCAMy4+UxLDcH4o8lbk+ob03TDMF3J7PKX55FJWCC1kBRx9MpVD3VOJ0AiKjKE7+sHp669j3h/P2P08XpHz+ilNy1qcLqashyhMdIkw2Y7wbLpCAtZ6eYwfL+BJQ31B6gLEKEAVcWlEk7d9bS/KyQGmGPMMEgg+TqmuCdrNfOLZyL2JcNqVE+mlSwHBcuYzm90MY+kYgyWK8r2HIxA8rYhVn7X2dqd+6S0ZGSRtiht1F7fJc6Wq3MXF1dZZrZkj/6Z3X1t/XKv8JyhGhY6pmnUNMyLU0/EjLMaPw5msOryz1gGfOjQDnelrfdD5aRwtCuDJSdQPPBiCW7A07lBXaQF8mafONnO+vmWC5E9qUxjqY5jQ4ARt01R9sARmOk2yyxjMXoXIchbpFl9/7TL2dGecL35qirGQYHWp/uBUolHNz9wqCvNZkq/FIuVyozlfI4dfV99M8/y+PD5fLw8PBqFBHLvIVs5YRTLhZIOR7mg4EvqyBDXcPI/xbrQ4/YKINkpDBIiTkMQRksE8zNd8v2hyIv8AuFMwrhrE99IKhg+xQoh5rTl7NMjAIw5DNoNAEUZWhYCpQDRMB6Kv3g3hJRcBZbtqPliSB3bg1MPX+xL41cWRJkbK69w6vfXC2zKjMzM+Xx4QqBzFq/uupYNoQiYFwbtB8KfNnDHF72U438JJYfuydE2Q4xbr4/DJaBchhhCMkkBnnnDlJrh0tMR9nls26apkI0xG10pQ83Vaml0FkTjRm9ECAdRwL6msNBcPDMOhojjVIQPyaqs3asLU+bQ6Lcx+tLyOekTGni2H472qRfZmYWZsobqjDHrPHKwsL6t1eiLWuX8mXLmAOW3Q++7yeSGeXEH8S+G7YMV7ZsOXRlVnN70037o+zy/2at2ixDUZSl5YdaPJnOmIGDFUIMAKNwuAUwixfi2OJrWwpbdcNvzh4ShVi/sc75o/6N+6NQvy2SGVccxHwjSwbL5aWZ9Q8ysLxfBRnbk4OMHY7lx4lkhTJs2dPcGrb8kMPQ7b7QlZt3NDc3t9z1fJRdPuumUhEXomNo8GQa9yXqrWfMOpyery7r5Wbjh3pLQrkXKNfVdHH2SGrhY/Mj3Sd4aLdSn4TLs4mE9qT+WffqW56IAl1dIZarAJediOHhcdEwUX4lA8vPg2WVlcOv8YQBcwLKYRYD3SQcYAQRBgkoS4AR2LLToUKUUfBe9eVOulD6UgO9sr1MAt17YcxhCpkX9upPIMEjbT9m9KrNcdm00JU9higoRnS5gIvFy6c31DP7XtLqdBTJp5LyaRL5OL2TY4lDAPvSjP9D/C/f19ubWHa+PFyjcWiZ2oPvZ2D5hfjGH4lZDoMM8uVHgbIKloO23+1Bw0/Zsmr3iSuLLZN2TE29GWUTvFf56qculD6dvVfW1Y2TOJsejT8r5ttr4ECbM09uszl93hbcnODpDQteWwXZC/k4fVBNINa3v8TZtBHGAO0RayQKtLSyNFPZzPKyEz0NzyxlYfnstrC3BDDvMgPm5h2P7tgHkoEybBm6vwbl+1UOw46VBWbW7qmpqcFs1gzvVd/uMRdKX8w87qvXi7GAtQHK6kouE6AD0V5GWWN+0aEM0ODpjQpe2xsK+Tjj7DGAZfaP/qWNMCZ6vcIgY2FlodaWyzPD48tlvnatg7qy8P5n9Bs716KtqDPSvSUwZvywVHs1yGghxPaBZBNl0AxbNnMYmmW4MqnluSmnE1EGwXtVGoNDadV631LdQAg5A2MuCcuXTXM0s3Hng8NEeXqjmtcsIx+nv6NedFlm/+/y6fDrOhVkMRZXFio1KI9fX6bQ4rMvSeM0tb7y/mfUgZKKZaCc98OLFMthjNH+QNv+vQ+3tjS37AtIBsqwZfgybBk5DPSSaJRFQnJLy9NTrHejDLK9ty9bGgN1EwKRaMz2FfFlwfmi8b94cGV4euOC12pbno6x3YHwvKCP1UspI4yhXqgrqtHqyuJiGSxX1l66XhkfL9KFltbWXazxJbP8XbQ1PRWy3BT2/AnM4stte/e2trTsY4oFZbPdR3+Il8PuazsdhxQGUGaYD02JBqMtKz9ZbTxNq1+zNXtRtlg3UCnWmM0r4pcE5WldNFjTnt64yPb1QTgXN+pkKOyy1IF9X5azWFSjb4jl4VqtLY8Ty7SZex7/Y6ss56N8Z14af+H/sCqWD9zT3t7e5lhu3XXrrdqVwXLoy/fCllNGGCCZRBingTlvTh8R6TRGnhZlE6OY2pjRQArMccwIU+fCouHpjQteWz84wtmjlGC979GF6tK8pfq63gpScitLlIuDlsrjFGr8cbnMkccfnzDLZ/Ko0ro0kzBULhz3GfjyAWJ5f1vbw227YMoWyjeFvtyCgRhJDT9tyy2O5T1TUCHi3eIDMGBW5svOYE6eG3+kWaMHO38uI8kAIoUxI+QNAlErsXxaVbv29MbTGHUPQWQpSkhjZL5GXo86i3XnN7BzLM84eMsV0pJ7KJPWb9xY55TGH184lr/fqFq6KbPCS1Cwz/uyuhoXfPnAAefLbe2wZKCs0nFQe9DwYwURhsphoN1HemqqFuY8UGZq804eXdLGorx/OqaDCTrVFrjtl0mIl/urj/3xxmxfEb8Ul41DqSR4eiPSkTqEfJwVSk2U/AXKLlrR8pGe+jqqz2L9XEfC5RL58vhGT4ln+a8bN/4adwLLUrEseQHf4hlgwj0/HJfI2NUBljnGOLABMuILFkgO1aF6/HTftRkstzg9MxXALMg6+QlA7CejYIVJ4ISkGC0lJ+2r0byzyr4UcpFkLcr9THOMMQ8p/3WbA+UghTFE5XCJ8lBqPI1hRer8cVmlhN4OWsQHKGtUR9OpNQaK5csq5D3NnQuLnmUPNCeY/2KUwXJYySTPNlT7inv+7PH4HR13kTZ8+W7mFyQHKNsw36lGLSPCCBt+GmVJY0AnqntCUntjy+UyVBojn4ddozNvNPWZul+IoydfS7YxmwPxkY0LRngM9Iu4VHp2LJ/OKBQfjvQoyRv080dHPs7cSfoEAvOcETqk1QC9o0dZHk5tVNbhRWEZqlAqzgksX7v2XR5KqnHgfVAl5WyWdxHE7obwQmXjlFrYl+NsWQ/DQLBMOhSgPDh1Np8ovZPII4PaybwLl+c3MXY+9ZmagRDqvGKM2b4i/uyoxgycccH0eCSrjk5bIa57DyrYs8y2jBX1xan7SfZKxS22lOXQqZ5vuvNevyiWqb8vZPlKytrGWbqzM49hn9qXO6oskxAjw5NBMmw50F3w5diG307bllubQ5QHpwYBc5RqR4ETTrWfcujxofoxrzSaA3WBYMyB1+uB+MjGBYDrQksZUT5t9/nJe0AqH6cOWJIZ6MxtraW8ab+iDZZXQpYry5xfDnw5sWoReHQGNGwHyrG+fNdmktHqS7zg8n3qf/w0yrYtt+4JXXmQVJAd6ExH8zGFUw+zvHkE3Xza6LQnRCIwZtX00VkttmWziaRLHNuqYk15tEehrE4keicF+A8bTGkLyRDVn+iqZxn9fmvDFstRrBvzPVfbQOokLu4JWNa+TLotBFlI1q6s1R5ny/aYZbhyS+tTAJnuokjvV7z0kAxu+im7nsd6yTriameknzUS+pyWNRDfXO9CP0qkhxEOVuMlrulXHRE4h/T74ReTKWgo+fJxHklAUMpsMJcyyt9WqFN50dKXnzDL4XgMi2WyXVvH83lYGlZ8OExjaJbvInTBMYEMUw5B1mq6M+wnCfNxOlgGyq3PgGUon146jVHkpt8rXEkhy3+m7MMeGCGCWMwSiyZs+Pp0IGqsV0J5dOfiBuo1qqpv7/kvaVPGvpFKvJ7/zIpShSAXPaSA33pKm98Sezfydl5Ev322uDjjWIbGFcsaZC2QnDvbSXw/n+TLpO1VcsExUAbMMWoTlFlqIEZsDoNQbn3aBxe1ML9L55VO/uh5kjzZQiwRDMQ37PrPurWGnNqIIYvkMWsgPhSgrEQhwOk4zW3eiA4kmLJu9Om3UPk4lerxa402OjJPUIa68qJP3oEvJ7BMylEl0x+mq9zmYM85R0HOPZ893nkwaPqB5Q5m+TbPsHAMhSDHGvNjRDLd69gyUBZXJh2qiZQDmPMgGDzbLBd1D7a26zmC2wZHtYoudPla4YcungDKWS4lx8XQgxTLrwbgsloXOWiQlbsYOlLPtImTrBqq/wKtnoygK5jWmms4pe13SvaRFG2w/M5nX9Rhmfn0FANnme35jWSOcHCcExkqJScsk7bdoqRIRohhqD2MlnfHJjFqXJlZluCC/kKdlc8uuyQ3mVJyOKmB+Mg6K8iUZnXnblcXVTTJg0w315LS5mkNxCdE9Y9OllxJdONypVSH53n0E9uNOfkEpYHYdUfp83LxLPmsJJ1q0wjS6klrpW4o+y/L3Vlv1LC8tFwux7K89P5nbMOR1K14skdZRDbMVMtMeTze2cQsq//ETiZZ91vbunmPj5atgRihLbfU2PJOYVkrymP/gDOOWz+pe7CJnJ9U1pln2+pRObWRLi1uSSnq9UB8X6KyeqewYPbaS6NJI9BkOzHlohkszHJorQpHPi4Bwa7Gex7p3f1bQ28jxviyfH15GDQHLC9fZ5a5jj3F/mWUc8y6KTcRUOCw3sUs67bf9liSQ1NOprnDjDBI5vA4htnpPskpa57f5QOQIyU4ND+TaEomOS6eT05jgDxbp3XtaPkEl7qy4GVrphGddhkSu+2LbYUKp8qUFe9pPq65k0jINTAyr0upW2qH235La9dpoCePx+CsHEheWxOWpY59JXuGZc5xX888dwP7/APh1fG3i7Y5Zm2Ob0mBMrQXtmxGyyqzzHoK9IYoD57J53zclJNJmYAt86tJ4ARwcoZdz/fYOq86wI3aGbH/oXpID8RHt7ZGR+sCDiZDoxzFxJtyURp9CWeRZAQbScihodylJVXlcnL0jyXL19fWXH9frS8vL6/RzKlvxJcFUZ7CJD/KTV7LhLx61sjJbQOzt1SJBsIByXXVdK+wHDM+TnX4iZ4ZtDz53UG6RbIPeJRdkZvckcYIkmKTHHrMpXSWaZVTM5k7aox7MwfiD2nsj3rilCTRVowdEU/hN0zZbh/apc6mQLChhByiJ62CGM83C4sE82J5mbzZsVvVGmt5+GruY2IZlQmaSfAvj7Sv/OM05/nNIca2Jk9rCLB/EZpyXZ47EGKki5ZJe5+2PJlYpr8znuL8q/QAyQu/bw4nlcbI5VL/E/acvlRiySSuT/dVA5kgEEXUowrVGhlysAJ+Zb0wZW3aKFadRS7WR3CkoYQcGspab+RESysE88LC0jDRTGKGZWp5vLJwNU8sE5msV73ropKDyTymiOaD4Sg5kAyeAXFAckq1J4zEUKllJnnv3kMGyV5nc1C4Z9BP5kD8XA52naxR1QFu1c4QJ6tVMFEyB+Krudrst9QEvJwUTMdFLj0pEERCLrOO2g3lUznRVWJ5hTqySQtLlfK/fzmt07D8hUXSr4fBsqF8wvwXEGIAZCVNMky5nlpUy8+0ZUFZYJ7SKEP5+vtnDsSntdJazmnjUonVBkx3QFtRj+EsXTAH4neh8RfMrsoVG0x3c+NuNsWhpxt9KAzqRj4uGUFrtYs9pi7Hdx/6d934vmiiOyei/11dWLnygdOVK1c+OPL373//feN3miGzvqF4ORlcLQk7XkS3381pSZbwIq2adsKWk6JloNy6CeQA5TPKmLXMgfh0xqo3wBNI6s25WujP1043WlLBb7N32wPxu9D4C46QbrpJmfLXvXGPbwImJ5+7WVwcJvjjztXPpHXj/BFETpaAskoIDnmQeU94iu5VNH9ZJJY/8Oh+e+P39698UMPyKlhOrbxgvx0gpyf5pq3QvP1esGwkMcIev73E8o7NTT6ATPczqVj+UKUxOPToSWlvfWrzbqmaDRkJriKjBGiDQPSCrvweJk/IlWepeP/CziAndwqe8+RIKZhKzMdhJ5GQC45Frdnp2C/P75aTMC0HaZQTLaz8rywjot5HHEtHNN9SkrwldexOjpYRLEu4/GSsK58hcSojWeZA/FxOZ537TBEURQVjNyRs6Eq/xDSYA/FLBCZ8URdrq4ubgBdTmfK8MBQrKkhdpFeF791ouCZ2jiKUtxOC3aZqGn/fXgHLn/+NGCMDy4hHjj/AuAHjWJqFdZC8Bd1FICfbMlAmPQWQNcqkXD2ZA/Etu075c2ZzunqQjwtKO/cfd9fWQkMUhUkuESVyyaPyRngQeVNeUP6GPPoLisTL2Tk8eDNKKZejodSRjITjfonklhBSrhGFrFlrls9qLXtwQnznnJnZc2b2zD7zzTdrrb3mzI4yTMTfgPlO7jMQaT7reBNnEu31HAL2ZjL5cGZFAbljwe8z+G60MNOmbkfw4P61G0DvbY0TN64xbpzYdz7D5aw/mBYwRUHokM36DZb9OUz9UVmeXmO59/n21R9Fm0vwLErEfxaEMQatPdgwBjzHdnvfqaJvwkT8HdBrZ4ZnUe4NrudxJK5qq8jgcNSDHQbkdu9xwP4EWSA7MlwWPKKHuN+42+DWrVs0vNnghMSXfx4b6bNSacwjZrK1Nb5SnIDBL5AZd/m5BDnL5SU2piyKDC63en9hIj7kui0V0+dTeM4F8biBLKVhDCe/vZD95Y42eBfQW8rgUA5lebxB5ARTJTuGCMhpFkimQTs7ijtPDz5UISbU4xOCa9eyXM5jpfJYRzRWBiup8T1/fkmZZ1oug8rGwmAs9qpML6DNyAgT8Tuddc4WHOwJIV4acMxdN6N43EmhU8/1SAxEMuH8OdslB3UBIZehGlaOQzGd6tfOElcee5GIAnKDwz8TEMxuvaO4d+fdxZvC5ROvGS+FyjdfP/kVLqfOxkSDZarLGFlZtt//MqZMg4nhrGUjy3Okq8QZyoBrii31ojgE5LpVX1wev+My4nGmm4S/8on4zNjI+buKg5+jX9nDna8Ox7Y0N5yEq8aocOUxv1EYkDuytp3McPqy+GrYvjp359inG6zDrzsp1UFnEeaXNPnTXE7KgFUwHTACRJubqaEwb7K/ncTJMmOF8hiabHBgK+17StoSnko8R9oVJuInyHV79m3gFYEbOzUe57pJatULE/FJNNX584K/UwXz66QlJG0N0YxQDTn+28d6MkUCXL8NZDae52LPqyggNwhPozj1322dBlKS4daUhHsHXr16/Plaw2WeZbnMx5Q/zFL5CGXxSSg1xRWhLuMlYLdvSIwZa6xlH8VQLnMAY/8+IXRA5QMHOg17mwYpsZvGh4n4nQ4S8bM4Fibi80GhAY+DeNxZksb62JVhIj4f2MCvollcI79kEzqhkzuqTCjjKIfLrmpsWU+HpkLlMjbAs9R0dw3ou5b7f0PIOn3NT6SNwE/WnK1rGo4yL5+DywlcTom53FFl4mWlhGNM8wR6UW6mVyvVwONYlofH6PHx7STWwmAuE4X3i7tHA8/lRJBWpWYSpc6zMBE/JUSdszgbJeKXO7+BxuOsRS7L9Il33hDl1QIG7ULNBsoFNm3Rle1DYBrkOLxLpBnw9QE42c2uICDnwpaDrFF2hJ0+tyWH7UxJ+Ty/cEG4nBK4fOv2g5RUlzu6sEJnCJWV01KUWXhqb6zLwwPVz1Eum0wMa2LMGYewMvGY3obI+w4QmuYAKHc6YSJ+8nKdf4qBV0/QKyRlpTy/6g1R/cob2bRaBmXFUZXDGTXUTour4nr5OjzKSH+/H6mrXIe18wfQjCy6CYfq/O1Dd2+KLlOJuXzzxKFDL5jL3zm6qsg6bEYYTBohAJ9/I+ZPmBz/JQaoPGcCy3JkXhxQpBz6URwiJch1qzt+KiAjgHic7SaB+MZ8Lf16p1B1AIhyHIkDx9BdUuaqa9DnHXGnMDI27JnNvqf/Cpvlxv8Yl4EHtw9dE11msC7fVS6nX8SsEX8S88aJLutz/MYJkxliYsyZVtN4v+fyAdJkGrZyOUzE71/2ifh7ItQM8X+obwgG1xJGjC4SJuL35eoLOXT2R4QdWVF+j6QIdGPn7Ix+fAsYzio03Ox8JWvCYvd/VWo3WtAnRpGAJ7fpJlZ6RMl9Kb64TbhwYUguzxjxRzFqznf6/KarLi/UxAulMqwL+rRz2Sfi+yOYf0h5IKw4UIjH2W6SqlcDvRDeJo4folYWXylQ6LjwouwTlX1ShLqAllCFobLgVGivrI2CzmXlOq29MAuZC2yWmwAUMi8BD+jGv9tXiMBSfHObCoeG5fLsEX8YU6MMOcjynEWkyuT6xRZGO5c3BYn4Ah82jrE2ImPRcK0fxOOIWKDKLk8HXrWInL/TX7lcGCIXZS8jykf3REmXcAGpiqKGVCtD+xzwYz46TBgEBldvr/9jpODvSituIm8YrC6+FgqZBTt4G3H51rVbt268bKh9g6avDWtjLBjxpzF6OnTZyzJxmUjMb8tkYGvKAHax7d47Hch1pegBMCYRU2NqyIGKn9Ow11++IaA9YSicPxvVLQhavaKfE+VLmaRLcQG5JiGXgmbnAxHf6df29kV4BkkzmMI81pK8tdwxunztA+VffG5MjhM3b9a+3/2huLx0xJ/H1HHM5VCW5yxHT5+ncjuXLzvGqLwe8ZrIv3Hl/k/ACatAlj0ZPJG17gjZLTjqzJuyEJSh09gD74QGKsp7Dmc6+uKkS7iAwimQud7BfO9z8LMNMklx7u9se0JXbJRLdmITDtPzQ7fvWt+PcOj2+aG4vHLEX8CouWNd9zWTmbDEUVnsZKCTMkBmpxWsMGwsh9kg6gAHJfC1ucu6jzig9/Gxso+F4UTBIe/7/zv0xIu1Ei5gAfjzyJvK8GjtL4QdaSdzWbRhXQIeMHstl+++SP8elymgMd3Lsujy4sDps1xOGaBLxImnjzordc6aXPzDZsXT35oBwQ2gxxr/aBDRzh5h5GoYY7wwaEQ5FwHrl6GNARfQMwuyG9XY2xllyO1GymAOkPDyp7h803P5xMvhuLxsxF/ClIkuF2NOjcU2GAcaQ5ZziA3DWnwGsd46HjqR2wl1C9lYFYFbd1T9MAA5dLB9zSJOlP3eCEvz/ReH2zeNrkNZMsrO3+XSQIYl8+YEPDjxH3F5xKip0GXIMnE5Y2DkZXk9u35rA6lytsPRs6q3EHLHiT1HLCl60CnTTRJ4VlfbxBG9JW2ijARh7E3pE0G9C1gElwQLtUf6wa92FP830wYEmoss1qT169P6n+DyenrzGHN0iHnrdfxXuAw2T7Tmco0V4LJ0jjgq0+8hLaQJLa3nAnP5dHRntWXbKTESu1kPf8DcqXgpRj9+0k5V7IQl7ES52+3yiF5IPDL9bfw1D9pFuSoLqbTEvYA5F7ArVXepFOq3nG9dqmzgbeBLcdXOVHdk7mJIwIztwk5CSh8f1jhzvmH2GS6+sVy2pK3XwgAA6VeN+BsAm6dDlhsuG1PZYSvTVpspL3oTuBiGMc5yEAtyKzfJVzXNus7DV7rzQr2SluEXjXFdN5dWruO0v++tFILKujwdxL1oyaJZ4gdEuaTFmlqF+JfaXMAuL4yzyJJPxLv++oirQE8Uf8HB2eaj1H1ujLRIdlZ/QNZlHK03cqvqCyrS68F1Lr5Zz1xeD86LTgmDmwk97PyWRXgALv8tjJwyx8iy4bJHZ71CWiINYXDbKtgS1olRtu1mYrPeMsBDswoL696yqyiYPCcDwe/zN3tsZIBnC3R1Aq70JjLI3+ZF+awYDabO0nVjeBewaQDS94AB9rKKerXtnqJtSAKNe026Ct/+L9SdQUrDQBSGC6IX0GsoHsTqSTyBO3OCSiAHcCEIgoHBVUSYRSGVLoqQCzS7QLpw24Uz/ft4Tf8hU+nC+mk0NpPH6Hx5vmRKkmHMkJfLFQuM47yEy26TdxltsABdwSe+KKsfrgZ/zskluRzOyulrmkTIRReal3v2qxPMcL0UooV62Ll4gUZoIeCp6zQx5rbgzEodQj4n0I4iFNIPvt+hhnRwSDoFpEInl4auGR8dEpJOaSOzKnrNmU3Xg5jJxLt2Pv0u67L2Lnu8y7X/WM7b6bhqh23rluS3XA8OgKPTc6gMl0Vl4iGJgcKQTnCst+j9E0PRES2HoTTMNCaPfiaui3fMIMYHJ2UGl/86+Jk/ROf7umhITcoUUJ9SwfgCRn9JqgdybFyVWeiM7PjVV7cYne8OVnOPUZeHpaAuC3U1Fhb/0mXH8dk5XB6Ky8QorvIdP2/PVwuNf1OzX0ENuYnFE7EVZ4C0orzM5OsYqg9KE0KEZYyUOk9BJj0hM9TYCndPEu8b5VetBnru5MJTPzjv1JqFa6aYyw/O5RqUS3G5FqqqIpep0gi/eDM4GI4vLuGyJmU2OV5jMEXSyNjOkoxSG2ND42FCrhja0vdP1oZC9GgeDcn95+4hLz9vgPzKMeJYyfM9RPLyPtxiIZsPz2XPycXwNXA1Lh0lu9EE/swz93qxGr7GrWVcxLIBYYp8i0JjRHeGsgihWBPxKR7SFIAxPYehCXTLYrcdjikbaxMm2ZOwyoeYl8GNO85GabqhMWXkXu6aLdaSz7Cy6TJWC9v102BLJk089/iuZD/UlktuGzEQRHNhLWeZTesO4TlyhZwsnHooPAhjwwvJgNzjaTab1R8Oi7YXiyWL9KmYQ+0wNXRKcyUEM9WjXJzW+OrC/itYYB2f/uPyd/9SXs0t5uN752dynLl/h9zemssIctxfLDmOU516ZpV2PBPiTp0ZgoyLpbVlO/IYHuf2MOWNZ81aM6THSqYgElkrdpPRAuVaJUHknrxDANFSfqKDYMXL5o1r4NkSVSg7DYyfbvliswKaremZ9pBmtJXpNP7E3p+RI7zdWvmj+ZZc/mAPL5U1nMv0KM9Di2KAHpgT0FYZS2RYFNwp2NMzxQRFAYwzaCAiCKgXE0CsMjTtkE48gGQqHl/YGAMfaQnI1jKDkfsllBaFYU5YiE0sLU89fKssEc3QUqyx9X6ftnN/Sm4XLsRx6P31ZnJp9sWyytk1nsxW2OhM87C2BRQ4pvAGPWoy4TYVueodDVbEEt4EAAUgRusFa1USG83bCqhhnYkX2qoaKLeXFKLZU8QtYvldn/kfI5S97VdClMk3+PwDuPxy4RvLD49YAnSUtnkJeZgBkgkjuQtxwXAnjtImTlNJlwIQoBhYNmuhNhKIXksuCV/dDQA2GBnJLWJA9Hm0I09yGb4ejDj8Qb0fl9Npdccj+veBWXFDW04FFMQFRlJPQKLE7peXOkg5o4WWDKAqrktI//hO4Wr5YSmWVF4t74kGy9f6Nh1dKqPchF5eu1wFuWy1wnBV3KhXppE5T5jnuaA8M0ExY/MUp9JI0v/sm8FOw0AMRPlARH/D50p7zIUcKv6axaPR02JVJaA0aclAd+2xY7z1aAEJXnaGU+uStZIl0Q5pOH0LNqFYalyMdrkikHkG0QuzYaAyuM2YFNLiIieBqAOjWp3rsuIh7BfBjT/foCHq6hO/XsBps/CNw7CFmqG42r2NndKCgcV5c48chKXMkBNInBkrKEYRJE16v+O+lh3+7nfKbjlfmnklT+Jns3LFeEP4mE4VeMNHNTC8bqJJIJaF2Vs9vov5CpQymL9Z/CGXhtgoRDaJlHW7pdXazngoMujBnjOoSASz2rQR3+eBqkX5g2miV5NmXItbbo9a/iNmHXmkQJR3G0nhFAUTwa4uYJD1QVCJ+vyNbDosHM4N1MRaEXZhMRBI0vq1fxVTne7VR59Cy/OC3Egxh17aOjorOiEicol0nCZTj8rTnjExyTrPrENZLFTMteli3JVNgUTfYLRA0YmNDBk0wkGUSEek2HMBcXI5qcq4ngqKHc6QH9FWwLNpeQkkCCtDSmAGcrDeS8QCSc9j8wapPK162XQR+8QNLIQHKZN84D5wfVIWq1u21DYelVhE0aq7curYllgWWpf+2wLMmA+r5be2LuLAVji3dbGXv/m8m5bPcWABNtPyhPmwWn5tv8Oh5d1j7Xt5B/8jdV8tf8SBjXBpa+IfavkSB36GQ8uf7JvNahxHFIU7hGyzySbgp/DCL+Bk55A8yYBbjbxpJiBMo9FqIAs9b+7c48PHpUTT46jlMtFRT9X9r6q+p5XQGv9XvL/dE69c3o7vjsvvh87wbFw+PW2efWdv+Ei3AG7k1yULsiYbFR1XDsVPFAq1WLbWvKBJYO2m3HdwrPn2+fE9cPl0qiPMtGITdpyNB8w3Ad1rTeqsbTKqGzZALnsRYZUdTUUPN/SZWHiiRLkgF4vgskJ+/Fh1BqPI5GDL3+A2zOoEDbGqGVk6/ceN4xRmu0+dcvlDbsxblcIJ2X7KJrpwm6IjJDvZ3tuD+26gFm0lCtIVrBjxrBRtZdQ1fd0D6RsT0oYyz3DbDmqCWiJYUufomvvlBluRA4rkaN+HoTN88FmMFGGrZtt5QLkV5PikGmX9fPOKb4TPpRcxSzIpm84yolgSVMfojsvvtOM14OaIrRFf8RxvXvGNcLvWy3WHCY76hPvd0Bnenp4fr1zuAsfTvng7dIbfTjvj5oG7+/DwYFWCge2hWpgjlTAKMgMyyc6gNgqpKDXdH034GidGLNgYcdYaXK5AYEmuMmDhOO1pZ/w2dIZfTzvjXjfWrZQsG+RtSUrDFK+fEKmF7Utdp8nsVCkhuBgFmqVQkKjGx25V0y5kYkVnS2BZR5UHm1EilbmoKNQSAWqneLoS05Xxvw6d4c3p2TCdxqe4TItFpBxyVicwqgtQ3ixyNLkmsktV5pJLba/O0mkkV1txyRQJZ50iOlCyTQ5hRbsowbOXFzTk6M2WAlLYvFWeLHZ2f9qGcbq+0dnpN0Nn+FnnOQFkTExIYBx9xqnNnQsLC78q1YjCXdmB1pAL7sBNipZYJiSEhnXQj7l6UFAlBp6s0RRBRGtsq2prmwtfR/Wm7S99wwMwT43556Ez/MhGcxwnCcJocUz/JH+MNafqoYDp8PA1uHmxLMhbsWLY4sFaf7lvKcGe/gv+oZ90RN2Na6yKkfGTun2ZRqyChPT8OPSGv5KiU57rsv38iXkaQ9NxOaYPH4PkCJIrbGN6xjRezDIdH4x7JolW7jEgoa0ZkcGa7tXQU2WW5LFulZBaGv86aulWAnVP1UERQCjHfIwmmLSFu+rcJM3NVcscGqI0R4zQ2EJ3/6wkvoyfz+FkfDnOhC22rkl23YXUsGcgCTIq4GgCxZhCXvG5wLYUZHcbU5fgvEw7y4jHsgJgFrXDqtlWr+Y0tuUkDC6pdNXxjv0UnmVlJe9JTpX0cNbg1dJ8lu69ZprXZANOkJEDpOCCxsMRvrpdl2tU19TjSQ5jEsfT7AxaLeN40jh291X8+NJnnioQo2BDsZ4aCxo+uI/qHgeiQZawcf/tOuecseSaL7ad84PBYZFmyYYzJUi26ayl4gIUCCs6W8eSPA6ISlnPYQpxlIHb5dlSBWnsJ1aTqPjL2ZTM2Twm/8Q7iOj/2taOeRa5UZmZRlft7iuf8eUibQ6gA8xF2pTp9po65yRZDLIETKkcPAOFQ864EF0K4axOUySi7WVBG5yiNPJSogRkQi9aFTB4O6hsPtUUSjy7tufMZdgBuI9S6Mr1GDeEdPfVovhCxpaDjAhXY3bDYnJbYba5Ro8tFKbhsUVMdBRyXQAu8YHDctUSLMCW48O2WMkhwLEUZd8wF4djqKZnDeAtzGbLympPPevX8BobmbGNmwnd3dcx4PL6Y7r9iAS1XDYbuPkmlJoCdSTLDRsCxMlbCMYvXarwix2KQoIABEiNdDRoFCBWFrIt4AnRuhXZnYidBwAvj6FPR5jO4jtCglzJZTAi5FUMAK0IFrvn8tt18npGxgTaQPAIAWinf6/CJHdFE+2VC2ZSQrG2kaLBWVaINRNglYPZHMTVRR0lw6gAe6aU0qXDN7wBBK0oTzmxo3S5jB0muUt6lyl83v67Z2w7vAHdfR0jvpDB9q/AuN15dEvhXEiaoZRgkjgDh63QqBKHBeSBG2YLlAOsAiekS6oZbAuLuW6kXM/U0NkrYqe84DhzFDXgZNjPDN1vp6/DuLXP3X0dYxh+mXbGqfxqAYXhlTmea5eIVyx+BaGRBezlciIsLjMRZRfEE6aBD//7C1KvqTxFJENNJJ3aBXImwpokdneadkZ3f8Iehp+mvXGmhfN8nu/vU5AenpRiup/nCIsANSRUBc1ypyBdlhRzsktiIKLjUuCsoDDGh8hQVfcCry+/l7zYlGuTtndPDCdRnTn1GGLSqpKVO6sWO/ABOZkqcKo8y+wNq1C48+ReJJW8soCGedobPw394a9pZxzy9uZAdy+UkC41RquGFPX43ky7cAm/kwX1lyVCMDs02KiRfC8kMeljq0IxKNeSt6In0KWBnlDzFXc+EjlySnu8V9ZnQ6qlIELYEfbDtDM6/LPf5Y8lz4mlNT3OXwnotI6e638j7M/lDv9UEn8smXbGcX7Fi+M4bcJS1SuSOvxTCS+Y2ScHxLjYFaOmZbGthi6y5ZzCOL/ixTFOCbWBhkgBS5EUEiNZMRPinxB7fL08DO+0XZ1Ah4GW9khcMCU82Z+zJrkkzhtxyOv6OMw4sCEfMFjAjxUACrBMk0r9dRC1feltcSBbRM9ChqW0NX+k0ky1UUkOI1KGLl8vxwvmLweAx58WKXJoShAkhTRiZDWnUz6UhicjaPzBRklykWElvSlnbAW5lNRH4XHVki7nmnbLYiGlqvsMqWiQjWA2UMjdWpTvRNZmG18u6iW4jSXFgs6U958Otk00PQUJ5qtSRGQCpbqvHb5ejhfMi8ChmD75fHB0IjT9hDtFOuOn40GABznklLom24hhUABwMwElSg5+JglQEjMiVMODRhSK+SeFzUDYun/HKA7mNjtJ4OGU5DbHPC6rmKrIL2xAe6vVDOjuX5Vc8MOyN8bDK14Y47I3fhh6xJ/LzpgOr3hhTMvO6PCb+Be8X/bG4RUvjGVvdPlKLl7KLXvjeHjFi+K47I0uX8nFtz6XvTEeQMXjdg+Ol8XjtdGkIL7wpsZlb3T5Sm4Yfl/ulru7u9hgfjQtYdIlVxgFFGzW0CW65tS0Aam1oj7ieNQsWQpOgHXdjtgqjI1YNMzsrg3AR3H9UBAXkdgaRztY8PTJvaETpYmN5GZhxoUR0/L70CV+Ss6KvD415JaSvhzTnLMmk18RmFLxyX3PH5OI6mSKNABSAPpeWKRcHNIkaBE8WiUvyjFbpCh2jdRSASKUwKEUU7YhgRXYPocnlaBHB+iyWAYqUtYbVevcFNGRKzQJ/L6SIEuA1NrHTExPj9+Su+BP7Ze9w8k7QwZ0Hle5NOI0VPf4hW/uOSQRLiJNIzIUIWNRAGQq9qJCfNawQwprAmx+xlBMYjjMni3KSl2TrVmDxzEmrJSzzRIf4pyZ89/ia20ivG5bReSieOL0sdlDp68x4kUGZ8qRQ+CovqVoSMRX40j7QNujOuKpOrRZr1oYsR6KH6nZFsErWC/UcBw8SfOVnSK3GN2hAohqFzaIWoCxyr2+xogXGXd7Yym3+oiIejxqwIpQJuxHco9SseKyQCIW57EYkU4HgOpWvXgt5005OgTCnaI05bGxZiGqlkDseODfXuj0NUa8yLjbHceAyePJckBezymZh4yy0EeXpJ4mOOLSLADBYVRdzhG1uJeQoBl2uk4OAktxSpUrW2U5rDxhSsIoVQlk2MedvNsdnb7GGIY3153jI9JKQFVO8Ip5BYRviaerjWnjGuu7g0rrBQBP0Ep+pXUbh2nL4qjjWkvA9SHEdviP/fyNjHazVfuIXqmMAnCDaQul4toD/vX2v8Gn0oSPltA2gOaSga/Tb2Nc8MdHDt6w1EfCBbAgK1nVcB634H/Et3/ZO3scx40oCNswnPkGPoUDJxuuDWxgwxdwpnQUUAAxUEAv4WBTHUFn9WOVCp/bjeVYmBmBAlQiu99fP7L7lXqpHc3ue6KrDX3UVqReKwRwhGmwyV+QMn6DfzoMdCbT+22y8SxLP6Taw0sF+LwcD7wBDlrwlOpMaZqqDmgWaXHFcsYu02Y/+tWHv+FfdERmASSez8t03GhqitOaSWfydmYB1Dz/9VlUdWepDiFq/BGl6gVKSSJiS9AhG5loFKIuAhkIjjst8RKTWbak9C0Qn5uwzFVQIyaM+QM0Jt0Hcs1Gf84mk31EL6o52AChYTQFq5raZkWNB276o1/9FPuc6Sws9WRyDjkzW01KJjvO0DfbOBkUKLtL4eLBAgnVWIY81nKGXcTjdy4Gh99cgQg7LEiRZD0hDCCp/fCJ+2/zQlHFJZzI2H19BUR1TyKmj8xEaWMh1XA2cwd4uaAs1sLKxQCdB4NREn2WEtmejf4Ee8F3X86mnW/djF5OT0NqOH7WAV/Pobw83qDPtmYFNPSQSgAMABdC77oeDF7PBH3Qwbqxec/87xEruGrJcs2Dq5YtVA3aAHMdVzVLkN2OdWUHNTKy19e5vf/eAfzuSVYj5g7n6kqw6EmF2JqkFGmisKLtltlRheqUsNI8f37gJngudqqaLlyKV4o3KFu9UcejzcoUV5+Y1Nk5sqFPG/7oVx/+Ft4tL3FXwlnnxXbZcs3wEu1VoCleUnUV52FlkUvJ7Pz78wM3gR4mvMGoTOZwadJVI5PThTJcUVXdz4waKV3j5PPzZAkb/uhXH/7O5m+hpmq66sZDWyEs91YLn0v1oiiyWgUnx2D1fPj8wA1wyO7hYqYmLohIqUrKICWnELJLcKQYoVTSS97yR7/6X/48CWHIRGLIrixDaE6sTO6zn6vVQGmOPS4LrVcdgvpDhNjkr9adB2Ch0Sl/AgXHKYvaApEemuQSuRlGOqxkq3bYxD2SOtfhht23E4wRMMpCrtWsE+CqjImJaxwHKCmB3Qh+W/ZO27A4/QB3fZYKJTb5O9h87fPcAmoD7Mzx60E4Q3vqvEjmiVuoC7kkWwvHEoM9iUhAMvUoEnMFxZEhfbK0nFQXxOJw0spgMMoqp+BIJkmwB8biF3kdgT+XIpfCh75adKnb4L7zreLvxG32C5/Gx+auR58tpvM4xihB3ciQSAjKE8sEHW6GMA4dCQHg7bRX38m6AeuaGf2r6ShAXw40YnCrqr1vKsvUmDf7hU/j52Uqk7g5TtWYt9MylVkTKnWWJGtFKKpi58ySBcvoGuQh1o+HB94dR3GvmuoEaS5KFcQ7UkvOSeqI7rFzHWU0K8LxRZ5//mbT+HVeqFx3v0xY9C1xWYSFjjpFSMWIoKK8moJ5rVWap1KXMOVa9FnDqhsOD7w7lmKN81yLbgq6gCL4OKmmZZjkVMg0qmDesue5dNFcQYtpclOeUQWdS93wT0oWfPtJ79axoHWQ4MYkzXoUpkx8kuzYhC+mWhGF+c2gt4J8z4cH3hnPKUcwSZt1XFwOMZNVnTjEVhHAu3dSaKyKL+On7X5Jzvg4GpkDgN6zesn0LBJiiJ3uogwrNWhFul5ZsXfD+uQoK2D4un/1FtAxYMMDcCI4rJ3DyohhpiTN2tODiQixfUShyGkQNv64XA/Mvllmbd42nJRRICxD8GRwXCzj/CIRY0Ur8lFSIqxbUxu9gMHeSK2gAHX0OCzTcx+OBAwUsHE5AjBzX5cu6bl7lKBlOIcGy6aFhnySZMFB/VATYEOPprgbf1yuB+bu5gEmAGEbj02orWlQdaqmkBS+ySp3DDFZ8JHYjIeBCSSTD8RCCm+DkO5ACisS3MHFxEuX3LztICTeXDXJuVo/mVyLK1m2w/l0EmbZiQaTNGVBo1oBPPcR4GkNbNcbf1yuB+aJOa2TubfTvgRX2CfsEPBAOmzPcDxR/TCYTwjJIK9tCiZBJGwMybbPBRmrPBigvCykJsINLffe7si2IuTq7TUVHJ3ywcn/WTKUJkkfN235i0U8ML8B1jl9fAYQgxrF0AGf+p4b6FLW0L996EhK298EfjoDT84VOFs6gEK+xt++vRlw7GmKdH3J8GC4g8dlPTC/P6auHlfj8Arb1Ze6LQ6vvfY8vj+2/7hcv4w93gJfnh94NxzHW2Czv4ItGH+MN8Dw9UL0FvoXQRAy+kowtuPKuCtAuqtuvr/29esxjTfAxr+MYXwYb4FjV5+uUF3ICllWy31cGYZ27Lm8Doas+F6ycofcMwogtI1FJmK8BT58cwf4abwFhvBAJ83RB2p8R/hmWyHcW058BGW0Qsgpp3oSJPuRzEkUu1zJmZAMsNUOg9uzqCuQgxkpghBCjaROIgjNbJtLDeMtsOnvLgc/jMJpvx8DSWrpek8v1nGSsO+jQ40FYTCMisViCi5YwJBWwZC7GhAF7oUcRLsnp9O5jym3ArhBp4a80J8rMufEcVu2SYsYoKL5ekyIzFn+rPYeQzWx2R4fkmuPrQXhP3xzD/i9aFwU9FQz74Isp6xJtbJ6ZqfyOk6KQzRcZuHEmDqGYw842GnUeA2OQaMF8GctybU+nP2McK+BMERJqFg7G2OHkFXHSV0qRkkNh0mF3KryqRzUT4qkbFCb/lU/8PMJCksqHRJ6TqfFVjiptZeVS6tpJ1BdtVnU46tAMV/G3VzpTXDK5gP3vCm7XJYoZnaY0Yjkmpu3zbvBY+7hb+QW/Hg6aappoCvQOhEinHTA+B5EamO+a2yV4UO7zNlOAeWhtDqDZpdiR2sJcA9/I7fgD5ZiFWuUfXn8fHzgHTC+XJY3wF38jdyCD/ubYGi2k+FyYNGB0EYPssoOMOFtBmMY4saE0l+s9WQ8OuKapRvwBXUVjGN26XWi72+Cu/gbuQW/7G+DIXwLMYchhpgiUK9BQQ2rEkgmNYgZpMYGId2RFFYkpCs4KA7DPbE6uLtGNRgbXYfVJE02C/bZxWSkRbea2533N8Em/0d3AL77tL8JBgC5MKHg7ExEo0ATOyJiigLZ2lQYkFDRu+vRNGBU+Pm1u0LmbRDZh2LakUROe/C0fwXWU3za/nfkgo/7d1iGp946PPDGOL1MRXzrXhqsae/gO3LBT7plHcwFcZWwT1dwfB4eeFNMTyvFwoXvBcDeFnfxQz/j++4NSrs2cbreFftjY35HaC95eT9GqQNqp0dRPh/YKn6r/0MlAB+Zl27eGjNC1SklEkMwkEoumfuNeULERLsOotZtvRPx+sugX4+3zzNTC9YeQN8r9x7Md/eI4YcMFsQymqR+vrhCZAykwbVQWjWsV19ZpIIi6vBZjbs6iYy4BCckfjdcyKliIw0gJYpVXLYoF14uk/uWIKs1AjyYc2JyTBVD4pmRZECpJKWC6hDhM4FUxDEeyekYS3f1iKGHjJam2UuhaUNddzEUiGHdiEAYJ5dJMAmhohDDBCckupEtLltii9mmi5guBkwOdwaSwFOpGcst/Cc1sZAPsnNltVyQBWCMvbwZiIwqA95hzLKHj+IoBZSZguWMZGBOsks8Rb2rR4zlIaNnrPXMbXcxlyTzrrrQWGsY7tq7RMFp2gnAKoBjJQrA3HfA8Mrhw+uTrg/Y76hU1UNlqBPs2Hnl27HlAnFd0Rh4j9zdI0Y9ZDztdpnsQlwTuF7SNFEm7Wb3pFZWL5YMi+USaOtOrJdcaU/TA2+Ek6sTJqqAl/KJ3BRKtUqhKCEPgya6i6zqmfU7J76rR4zlxyU1eRPZUymlml1RfJELnqqCAvFfYyrOhjps2MmWkN3ysnV64G0wi59ZV/9R6G3ooi0Hn76XsJLjVIFD6FKcw6l2FnYKu6sflBgfLm9uT6demosEPy9kdv7DSkRdDp0JM3VZxYrSAJG5kijl9MCbwPuvq/SUvUSGJyq5uxTLpYH2qedT/sBcwtmQE6tK3s13MYJfTELPWJPwqVUQ4gvDL2E2LS8z251HMtojxOfTNBe60sw6WqWNQoszaWZaNYSQLjqQDVP8MkoiCbATf98BTzPxkm0g1jaM8qvDIBlR/nk+ZZVFWks2lPanqZmVzx+wIqoM6pBTHyf5M7pS3M93MfgHP3eG5w68Tl1DiLiLU/gzfrIw9B/2zl7XaSAKwiAEFeJdKBAVFICgQKJ0Y6VZyvgdeIM8M86Mh0+HBRN+ri+OMrZ3z9+uveeMV3BJLqoFTLQaiw6KbesygrA44Ye8jCJC1Y9BooyJ8qCMlgMbA2WWg0GKlgsHq8r8i08W7imdSTNKLk4PY+k2GMkoNZrcUL66q6QyajWCwuGf6Bb897/es8fH9jMyT4gEjD/gPHnDAewQmQ14QaVoEkMLUKl0iA3whpSrw3sHF/shDI/Ujakg3K0PjAStSOlZB9mHrMYYu+SxJH8d2qvRFuW//s+jfox3rU16XVfh/CRInE4CxphKqIPisPDlP8Tpn0+o887QAgqho5J6Gp38yY5pikvhbMoUCST2v/+ViD/AK/NR5ASskxwpjHeeqKQOTI6KxyOPvyrU6Vcs+Nc8OfUWupVRiKv2f/+sR+fUZCXXSrcwphw2zc1ou1xtoqiENwBePdghXmg553y07KMF4e+oPHiTBeeh9quJdTbL6iub+jGM7TcvDKc0mFaVOhvm/jXBg5DBTMW9pQKiquzIU52NfnYwGaZEx14HEe9pM84kZUeRpMOFs89meVywBJ1LpVgZE2CMPlKxnXxpteKJVqv1eklgLGx2kBKFz0M9Vq64nTmJucZ2ggDQgIIWEtluZZZshjTSaZi2UJKOm6SDfnByMfO+WXEIJ2FnlcPDuGZEk8rKoXrWZ4EnROFZW4OFFlIRH95odYizKVk439jAs9MkiDoJxycP9og3ze9rSIjgxopamxPtJBqhchJH1iXGutTMnc5crjtamERAaC/JzpiEKPER7Y7Ak8BMEmGMg23lUXB4LJQO8Vkc8UaGfB/DWEm8OnHblbssldAx6ooeDmIMjzl9CI6yJh+Cz7azf78O3mZhZGZJFBwNp6sbXo8JS6ggRY7k8pjyfqNRDkruliDJgECFogmyMdgtIcSgSaobZJ4Lqlqxk7nrHQEGBG4kMEk3nDerpuOY6jSERvINS5DScjwUhXKpjSdvwu5+uGw8/OTF0EYwpNAkgqUTkgiSRICDT3+AL6scWXddjnDnX43Dum65PCKVqUlFpHygoSGhodToT/v74bLxYrwQ7e8j2ul4MuYeMe3RcgVBiFNx0anBcTwmGE93k15lNly44/+JV47+WSa06tMA60cc0yJgbeT4zrHLv/md8bSN22E6CqqVuWZKSIpVFZQDuOJul/JOssKIiVjo5N4j5MghzRbfTI3lhISBsG8yWX1OkwQY7kVY5UmYdMZ0YimZC/bi1Ay22DRuiH38RkQA3owbQmUy09SF0pMEGx0znW2Oi82hGE4Y2RARw7zgVIeYd3W0H2WJwqkmjPOJS+0dL2tTKu/0b37+29+GaMcepyqIgpYnzJIBta9jsRaEoiKSMMFE2LTcBRduC70RYd2FCi5PQxs3xE7/5id8GrdDJbPZdQEIv5g49RZTF1W5bGGFvhMaAFMYmDtNvIy8jZZo8AJeLsRNqbyb3yIHwItxS1y4U8Eg8FfBuP8+jNi7QJ+WcTvs+G9+Zzz+MP4+hj+OPN7wm1jJ63Bx9pHQAcqHfX1n1QCv19Pw9xjcuu/J3BBB5286/xhN18XBd4zWW37uucONpTe/frBrPJsXM4xuoNxgHcSOWOIGohjJfKClRq3UsdmAyR02LJXYzIVCbHFoHBotwEtg78IBMCAiAJ6iWzBKDPkHOSWVFKcBOKgCSjoUxKo8e7BvvLn8Be5yQYbxIKMOOCizypkOKlLPBDU0BchIcMf+eBzDFJXkscSFiZG8ZP2rATEzg+VW2cjE6bCg0OUufuSh7A4dZSG4ZVgfBT/vQenh/rDnH8gZ74aOsjrRkg4W3UfAbvnVkFQrDiysdPVNNuvUckbIYIGzbG+eiyEaAIfgP9O1JUJIiJQ8UwxMiSMTZRqbeMQQPG6WajUzZ2EGz0kkWTeSYJgsTSd7r2Ic69NWRXJFNSTu8UP4Fa+0qqw6Yug4IxmCw3bbpZ4BZNpGkpvEUjx3je23WiGOkE4CfHQcgfxRJIChsG8xhjQEGqGqOlRhCeTpigWNNxRRQrz03K3M5GwnwUkiVJWVdgnOvoNkuwfWoXJk0C4/hF/xfFmZOvUs046aUKLDWNjvwGhMgzZoZ6buvwRURVGDsYiwpZoKvZmmA8xaicBVRTZkH/V96Be2OnUS6ytpRRnhokFPrMnrJq9DOoEq7+w3vIDyaTkvJi83YAOOlUis64DT6akXQAMXR62MpUNaoxQ8BvgutPYRf4Sac7RsuRJpQSkQFLYK0yGzzt1+Qs4wXrD4kgIAFUE/5mK0Gy7AmJ0XZqahQ8H5h9j1v5Pw67iGu8GNzH+BYWPs7hdvgboxb4sbmf8/Kl/Htjx/iXXzjXlsN6xiHDbGh31+ZbXH62FbQOYxJ1WMQX390iVBfFXWvcHIoP/6JkGRx3oLLnk8zkHRYlhcPspSeCoEvIAEVNOwNXb+z9f3sDEDvjA8o/CRNqSBkQonBlbKwYTLwLhkK6SMVT0OhdnMI9gmAX7LyL0ygMeU4gAGcUvZMi++0Ri2xRVty2zMG2IMxdyhUn5oDAsiy6+eebBEteKJkOGM7RHKA0FaqIuf0AgNDzI3bdHLDWNCkzhsjCvalucv/pWN+TAf5xOgIiOigxplzWIGjVeB1ql/j0NJPxIanVq04gHVh5jRH/b7Nb8erw/fZ8kJcgMdZT6Y7NKsLIZcjFWIRzA25nF7tIEt8j7QEHkgFB3Ojxtkpzu0Ldm2w7rbmD0W0apL5/PqtmVtzEkXZEySdEiwZilOAxcZHgTmFKRr3MZcbudzaPMx3iNg79yen8WKOz2aMhbAYZM1tbBoWVI6C5jLm+ArZWbuq9qW543ZCwOozopNhZZIVukkMrbwn7Yj1d3SbJip8z+QedRTLG0bzpeerfkBSeS3EwMZpjCdExCepgSlLle1LZ9/lEEW/gS/P5qdmRL/FiM6cR0Lk7tPA28LqNxG9zaMUHmj7IMr+iFGNub7gLZLXWpc2IVjtHyAtGN/pDrA8MR8CDU8IiYiQMWQyePqgvlAZuLx0gyMyip9NumOO2yOq9uWz99iPdwD+Li+sfQhBxwZUn157ZPG5RjGCGGJYupUGWMhonoHumECDxOYUEJ5jGrk1CChMRcvqcTDPWDn31gF4MXhPhByLaDgGIvak544GFQR3kXCD+HDwxi7m8JN/Ei8AP0LKcD/jv7Mc7gXXMknMQzj0fvDvWC44T6pPLy/jg/IVTw/XIgbmc+4DiofDlfwdZIf4NUBfHaDgu1zVDy45Y21iyPiRmYDKLWHgzuABWsfUev0c4/6yLK/uoavk/R4u6xxPmGxtIWmoSHGz1ElLDbDodGZMPG0NzLPgILkJhTWSWZdFTcdaSlDdAFJfmbe829DXMNLZaKuHsJKhK+Fwp/tsISZ7MYbSY6lHredeaZy8lISKyVI9qPBeMcnJqNzFiVNCvaVvbPZaVyJgvCMRjOrES8wT8ECISGYDSMu6yi+lq7EKu//DheqpvSp1cQEEuK/rtjd56+P4+OKSexOuP6yUPwK6ejTUVJrNifSBhczGgaJWa2HzJH+XTlSLyGnj4jpoygyEkKAxCGIXLl+fVkqbqqdLglMMwAqXVppa/fKyUwdoB6QDjEj4a9oWh26fVE3XxaLH//sRsKq3zTvxsLi7l4bxtVuJNRkXg25n3ajYYG3ScDXu91oYHJtZkhLK+dCo8CFxFjwSLvRDXlwAWIA4yuTt0nKKBixMYa5md7H0am80OtxwZ/diMi8XC1mrhs9bIuUkKjSHYqiB0ZHqrUUWjqQzBA8KqMkysOzsJdAe0jpTr0bKL4bEfP/McRh3OxGhBmQFrpZCWCFJGJkicig0AvWWqSBmAgC0eqqLIRKspwexc/KmW2KbTcWlv3Bz7goPv51iBhqS4drGG/FwUGkCnAI/+AoDLUN9ZABoTI6TiylhFZvZKAuRxRyeFTG/TP3nw5/G1dVldDQ3dB30SlYafBieSD905pQnQ+oHEAvrQRSX0wdDWHS1vHBLx//VM+OAqVFcoCWZ9hki00ZG6daDFYtOdcq2VyQt4uMaI1CUWMXmUCrCaMpal1sbfEf/Iw/5evdlbMMcc1ZeSEqFcaoMHW8RGC/kIPhsMPY/O/T7LFj311GSYKNFCVFjA1WU+SMZ0yRO6x3wAo++Bk3XViZVgjjKgKmpEQSTKETLERDtSTTKk7Nqhf1kUglqBC2guDFMI6FyRrJvaPoTf7Ff/DL16UgWQXK6z4lQoO2FipH2ZIMbT8DBh0oZ8Zu0ATwlqcImtjjBlSNaAzwuQZmhAV+Mep1XHanwG7YBf1BzWZmiKIzBxVY3TkWFIFxMT8hPiYESkYhqwSt5CSSOVOx2YyWYVrZd7A7Ua1R6hBcy5yBD8B1dxyOP0gmT8m5nS2yWWHyKWFh377Zvo6zFXImTVKw/WSMJmCIwuRL9KdYvbDlukRnxmKnegJ+xqgbGzn8kNKW9JDJK3N3CWVobcKFFzO5mKLtlq1H4imRmUFFRqvd6FjBpWVw2Y2P/RNK6Xi/4L7yDYDwgwE5yQ4gfAWCp0DlFb3D8LuM8XGau+JPXd4VTALd+Fjul0lex8X47zI+Rue8/ehM4k6CO50bgfzGs6hhe9AliD7ZNVKrhrNNGQVFxTEFrOodxguuukkAOr08diGPtbDU9tC1s/Wll01tlJ1i1dio1TLs7/iox4tBTdQyMRrZGJToiVC5W8HN6wm+y3gB59GCVe4iQCPsxNOZ4YwjqkyByCMvEXeyRNKSEJl5RJjKWXlN1zAmdC1D0H2qMAZyqYGj8IYHUTUzEesA9TEPD0FFEySwTOac3HUL+7Hl+VzLEHQ7DLLCJBiDtXhg1BnZaWyW/gy7nF4dWXBA9szrYasMiAClnWQXte8mgHVdw2BexnSwywQ7FM3c4C9+MaHBLjPJDiZGOcAG05VJadYZIsic87VTm+KCt29dojsndKopXLz4i7XMw6i/ld0FfQd6JNS+f1kiYpaCXo6s8iKjRArDirliZhfj4nJ0YiSRwo0s5jYpIhKLP/SMmNUhxRprxFf3HeCoG4rEEIARg1daH5zOiR6W/M3rIdx3gisRUQtWrxiovT1FiPU+VtfYLRaOSFI+w/w5bN4Bdoz0IDnVxocE4RHV2gfg/t4tUb3sWa9CWrdKOa3akA7JmShnfGq9pvKMiWWpP7n1Nn53fSgZuhopUKgdJpasTZMlYzJEmukaxkYustsHa+BKLe1QhjAUBTmPANmLV35ZMOgal43Z5dTPcnohkjMkNMSuTzPOtLifwH/Pd0xSCMqjhkLy6pcO7fDxh46zEIDPQWLVovST+Oj0TqR2FsLTUNomNxQVXhsSqDOG+MoaxdQhJssqvkuy//YfJwL3oKOrPXiRAAZ64pDBTNlc7AosHCgihByoG4DfyV2XPNL6bviVuOwnhm5G6CeGdV6OAzf9ecBpvDbOk8391LDSy3Hg+8Ox1OyOGD/fc/Phu3guPKzla1H78euxPw/4ZDP/dxr9COiGK/e43J9anuhb5u0S3jf3U8Ta3ywf/Zb52MPazY/O/Zjo2pvlt64yn/Y0u+CrGv1o2GZ5HXdL/C9+H8HF7XnIvC06y3Nicz8JUDJwu+4ry4Zxfx4qa912z+tf5Vn+4Mn5TY4vkMhbVgOsdxpGjasjK3xQoJruefEQt14OZ3OfzitKjwEJbY8RDIX0E2Byr8K9Xu71fSvq1J//tnqoGQZRWwaJ0VHfhsnrhok4VuAdFje9LARlZWIUo1Cg70TOyK5T/qi1z33D+Hb3kQLD5wMPRUlpzAeB6TcCRIN8HQFlIHRNEub/wN+w39pUzsdFrfNon/v24+ftdpu/+Id8OIPGEtPUPVSmq7ldjyVtCc6qzIoKP+GzKVkCTzHcA+F13PVO0IJ9RjwUoHKm376RjVKl4O1z3xD+pGxm2cvi6lHAcCue+ByP12JJgeKhZO4qQ3S5kgEh7EsPsEBpTNC1iiSChmJ46dmpwpIeVf4gTjUEkMdddjvFxS5dEqrF5OnW8UPL78Olyka5XV9oGlFWlRRnwVFJibFWRtqgJkscEiCvYwTJdr/3bk03HM57CcDLDip5X6TJJkjHbhkuWknFqpLJAkkdxrYpQHJC/RyIdr8PgN/Q8GUFHENO1ThshJPxQAHoiCQfXKYjmYOhh91uPncGD1tRX9XCDb7xyrbmb5IM4oaDg4RACQkrnPTAOgNzRBMOYCrvYtgWEaT8FBQchbZYYCMOggAUx2fxhGW+XvM3SYYvZmzPiP4k4/oTYnhTGIYxnKc/aWnaJYy9+PGwnSeOZvEs0a/2FwQOwa/b7YyxGhL/xW2bsjyE+3kf3b94g7+L2Mftts3CeAOX28VjIVRuV+PexNW2YRZoE4oOuczcMAO0C8uHXWZumDza3LiD8PV62zBxtHskjcwv2Gxnj0blid4APDs2c+dzu933DnxfNplnjrv2C0XvwY9G5snirt25/gCZ+Yu8iVijNjLAXY2D8wzEHhiESnvcUxsOpENBOBTUsVH5FPOMNgUlSwl1owWADQlKu0Yhxsw4tmVxw0PxMaqNOvB6qJsNI5MKibhitz1KHXwlC2k3kUqg8xzIV9aKHeaJ2tzmE30AP0VmSJmSq+WASY4zOi41CMpTWOFDTIrJFqRZtxofqgLVIZEYxtnn1qvzG3GEqu4IcQYGkclCnLGgUMIiq23ZFh43jIvDeFjjv+87BZnh8QugGeSQ1ZJ6gRNojFJNAHwcroJCTgbt0xuWksGrl1izwh2GQ/+ClJGlEpFeAY6iDbvZZzxOle1REiGJywqoJzBW2VLnRuWjzsycXqh/eFcfNEjmYKwWOIQMIsjpDKi8RUwWNoOFsURqBcmlRrAKcYVopGR/MOFnKJZoGOlD0oDdRXQoYp50o/IRHwDripcHqjZwqGndIRHmBh1U2/GyB2xhT7aBEZzUswLk8Ak1TWWMXEsEUCPAK2wP2se+48jcMBk0KjcyLwSNykfieyPzNNDu9h2Pb9ebhgngus3BOB5fbzYNo+OmzYw7CRqZR0eben8q/N40jIr2hajT4WrTMCLa11RPifvHTcNIeGy/g3Fa/LndNIyC2/YDy6fGxcOmYQQ8tJ+9Pz1+trsmI+CuzSYK2l2TeaPdISnQrs3NF+1a3Ofh8uVyxn8bgIZUuQesGNx6tYwCGBAX41ARWEH0AQcyGjriMNgv1H0+jPSP7dcPPxNczoBvhQ7VfPjx2BKjOiiJWSF4y+MrpwLKNFiIhcRwWglp8EfCowTYEZWkSJzIwp4nK0FrxV3tqDo7M7JdwDgPLu5SbpffbQhm2JAA+QhQAxHQHaNQLxlvi/QimeG4DJReMsVeW+ATnIkDhSR0jma7UdySpTbleWiB2dUrhyfqnb1rFzA+G99uVHbWHAQOZRgb+m0wEOje8bCRQaQT1NkCsSNkEJyIw2AzXr39WByYLFpQyZg8MtkWkRzQNyS1Xv71gNLJQSGT8qZ96jsDrkIGOjWcOKE6h99mXHAEspKEQGywNIgRmuDIKwdyGvA3Voie52qTg+Bq+aSq82xVBgGSwt1I7CdltK3dtj4X7m9hDcQw4A0W2IWD8UgKIh9ARytozgJw0oNN7F4x8lRIzYuDfBEZzz6QDnsVjkhAcNtuW//f3tnkNg1FUVgIlRHqBliFB5Ely8kkVZRhxkyyg0xZk3eJfY6OPh6BUCCEtL2H1L7v/rkony+PJIVb6XHdonGOyTlSmAgbx4UkdB58dsE1ArhZcUREn63aKt9a74fPpX+m2irfVt3+82sX0/SmqleVb65Pu8+lf6Bd/ad9t9dD7TP+gYb6aevnqfYZd67aX9xevJ5RuqR6/eLl6P3mc+lq2tTrF/9HvG/yHB0xb63j+Qnzqt/q8ffbkXWs90f+uz70zXNyjMEZo6UK02Wcz0U7PAgfjXHhpz3Chaf1qexnqecGQRr9tBCzr38t7g7UjQADMWC1eGZDD59t5blugcbHsVkgVlyarkTpyXrOU6as8++evJ/dFLHx4uDqVLKmC8Gx/tJ3H/rYf/OMSWDsAwTlJLfTY9DiDFw8kOc8r3LikrBDL745l0OwbR7QCYWMZxf4Sz3t1FLultsEz7534n39VN/daLX30+RjnrpMvxALBY6HCCuk0SUn8CcZKGKlnVrDt0Sf5CVBuZaiXiYoNbwny/n20iv1PlDpYK7iE1fZ14fi7kmPfRBtj+ASfOEviUDiEiZ0CiUnOj82IDqEBThawmgaOITdlsjFN+ggoqId1A5wr9gCW+odUn5fr8Tdmbp9njyeU4uxhCNPJCLecm+SADEn8OFitIF3G9QCJl4OFOfIJZp8aLba1udWc29RWkP5LjXvmpsn1Vbsc+G8HAQghJPTRTEcKUKs8FwOwzlCl9O+Rbt2yneqbrxM6nV0+QoAjn25ArSefy18z2+DUL18cbd6GHg2z4DCvhrwlwf3H/B5OR/zSndifZDonvW0O5aeqV39mwH3rXer/bH0DO1X9b823L0e+2Ppl6oX4l6GaqNR24tXo3er8Vj6qcbaXrwkfRgOx9IPdRjqE3EvTJ9+sW3+oseFMCd0jQIqLnnwyZkFxwvFpyxpQG1fP5v6ArVd+4n1E+kvrFPWyyk2mId1iJDDEQmLZs74Po1CrpXCpasOJDdnWIZZfgst9FR5JaP9na/rA/cvVN14/CJcTicIOIOxGWZJ1tocgQQObFsuDGAAJFId/pICcFeuLWWcdFcQnE1y5JZnMZSupQMnu0jVI1HdMfU234vW+9V49NO9kGFATqHEnIm1zFNlwEemm/NDLz71Wnq41WKGVFPGzcMVdePYVjtxHHjNLkwqorOyT6d2zsYU2WrpimTl9yKNq/p5vheth81+Jiwz7CSWPK/0BaaBzRI3ciox3Atcly8d08tTVc1dyO4BokSrWjvBAjV3tzmnKPfkUb18KSqp8fzw3ePCOXNx8MeHajOXl8B+U29Yv3h92OzNi8nUUw/MwZK5Oh/Ct8mFb9oINcGr6iz8SyuxbbrVerbFoNmbrcXlMnGuyzu0KFcXopr/ntt+yDLcpKhQjZfjSXnyiOR68eJV6KNms4jJgA5J8s8LsSOnUdXR6AkisaKI808ZjguB7hSUBbGB1Mqm3EFeZGc9L0WqYsrUScuT3A7JqVx/I76KZdPfqO+ZXFTu/aY+2flq9GGmOWOMP6qz5owh5HwOYYkZK1Mi8Iyyz8kP9mY2BclZ2E2dO6St/O6Wch1yG2RD4dshIKfSZc4V7EtmzeRXpplmUwGfjfDaxKAmDc4zHPAhJVnMwpdk2pHuqB9ZNX0lOhp6UZvUCKt2F69RHzZjsGoAticIBRyYTUiGZJMCWoQrSE7cp7QLxITAnevRF9OWCYZ/fimsoM5jkfw69bAaxUCwCrZMXZhuWHZ+YiwtRwDXTtr7WnqAP2W5doO7rZCfBl6c3VPk00uRcVWvXbxavet2wiHPuwwYxlSCg7GYf4EJIklTXtoRhG4v8wW8amFXTvRPxMswC+NOhWStd119hOh1a9ubQ8YbINrTsuMYTtcFJMb4+ezUMR1p4EotJRIdI5wmZOTgJZdLCfvvQ1/vVr8BPQ57gGzJA5n4mwnOwKUOp0/tpDWvKaDcMZJYKj3XYMFZuVBuSx5uyf1QH7V/I3pY7UwOU9di2OWstBb8MOP8RFlk4qYwAIJ+atk/YLMLYe1CirlLmm4p2dU2+U1p2x9CJYCybwAhgkltAGVTIRPmqE60pTZoYoA7t1NuGByO2QP7/DZqc/EG9XEzhhedgMpnmAq8IO1fwN/ACvu2YFjCYuSmOfkOpBb6f/r30ZM01jt8b1PvNJzDT3AJOMCcEywFSFAN80Sb/a/P/JplI82TA6QR7CdIcpO3jOR65eLt6sNq15LTgAQ/8H6GuIy4JyVCNh3jneL1Engn2wztUDzZcBiW5bE5nebi3areFnnrehrGMCexWWhQDF1ZhvXY9MDjJHgN/mnLtKYc8n1r2PKvSYmi3llTysehfrK6pL3GsPec1AgMxGwtJuYt0Ikof1kTSE4KqoiUzHb8WnIll3hBhRVum+36ZGs/1N6ixA+gdP2BfQQycxA3NYSJZ7HrtFQHNCjMenKN75hpSoOJNm40wbNbN8WTPC479F39uEip1cOMszHL9AupjOIspoVEz+bIjvBHFzrQaQqes4mHgSwvG46gzpVyk8wg10vJpZ/gvBc8wtInhqZhk60JqoXTlRgc56VKLJicUj5Ngle29jUpzoVyKbdFaEnZF8ilS3q/HUaj6IPPsgQwOwsbAZeXe1H7tztaBd55bcaRMnM9n51Kc6/HYVtbi9Iv9bRZGyKQM7DRRMC2M0HNNR7UgTFRY80In6ho9s7YExtua72pVy1Kz9XHbhgZpNCJwqOPsM6G11leIFLBm2RyFCMSrL+MQ1dv7ZV+U59W/b75qIPFrF5+MZrbcRppMz1REFTdsgWYLvhwaIe8qn9Bq/Rnev8knsWiZ6xNSSh7xxxP2G432vErd27mBDVEFC1xgpNSxfFT7ZBLf6V3M8+jiD1BKAtbZ1uCoE3y5IfCgJom1HCN5Zo6jjPH9W5I6Tp67Dbrg5nzV5C2w+T5kJAtME0UqnVaPCBvjjGmw3rT1efqS+haG45hd2CSLmcdpVCroFH9orMPBtcZ5GeB33iryWE31LaidH2x4+g2/ZiJemJIM6szry074DRV9gR8dhfyj/2mq11F6RZ6WIjeHUxsxIyN10aIZ4tBOowb6MNuobjezivdWO8et6sZ6VFUStAskwnNyaFTzHjHGeLV9rFmcem/6v3jttsM/Xq3N5uAjZ1HG9/v1v2w6baPtS0u3ZnePz5tu261GYa+n9nejYsO06yDzN1uPQeGYbPquu1TEfwP9RXAbtY8JH/C6gAAAABJRU5ErkJggg==');
}

.bargain .header .pictxt {
	margin: 330rpx auto 0 auto;
	font-size: 26rpx;
	color: #fff;
}

.bargain .header .pictxt .pictrue {
	width: 56rpx;
	height: 56rpx;
	margin-right: 30rpx;
}

.bargain .header .pictxt .pictrue image {
	width: 100%;
	height: 100%;
	border-radius: 50%;
	border: 2rpx solid #fff;
}

.bargain .header .pictxt .text text {
	margin-left: 20rpx;
}

.bargain .header .time {
	background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAbgAAACmCAMAAACfv2reAAAAk1BMVEUAAAD/nSL/oCj/oCj/nSH/nSH/oCj/mhr/mhr/oCj/oCj/oCj/nSH/niT/mhr/mhr/oCj/mhr/oCj/oCj/mhr/niP/oCj/oCj/mhr/////oCj/mhr/nyT/w3f/rkf/rEL/pjT/tlr/xXv/uF3//vz/vmv/wXP/vWr/79r/1aD/05r/wXL/nB//5MH/+/X/t1r/5MP28hXaAAAAGXRSTlMABvndiVtUVPjkOC8Dk+DarKyQC+QH86amYv5b+wAAA6JJREFUeNrs3Nlu4kAQheEyGOywBkImcR8Sh7Avycz7P920G8l3MYtccko63wXc969GXuiS20XJ6ygF1SQdvyaRiL5kDKrZOBFtgxjID7tVRjVZ7Q45EA9EV4z5ntVqttqfEIuqBKevjGr3dUIiiqIZ9hkp2GMWiZ4Ocv5Oqljl6IieCQ4ZqThgInpG2GWkYoeR6EnBX0olK6SiB8hICSAlhjOE4YxiOKMYziiGM4rhjGI4o5TCIWA4PQhqzzcPGE4PMA9q3m0fAcPpKdfYk3o47z1gOD3Ae+A8qUfuLby/C4bTA5zXOPekHktvu11v12uG0wOc13jpybWi7ku75X6yOR43bwHD6QHegmK13U/+tF+6kZS6bVfls8RweoDPkqvS7pZ/lXTVNpvNkTtOW7nj/Gq7avFACrG7YOlt196W4fQA5zVeeu6CWLyu81rT3lP1VWWBV5WKgEVQeVX51Bu2nNcViR7890Of93EKVO7j+iFYJJ1iv/XlEj450QZ8BHJJv9hzHXn0n1M+q2ze9c8qpz7Zo7T9Z4+vdZoHyJV6Pllbin0XMVzzrg/37JO1xHl8kfoLAHIt5zHcb8FwRjGcUQxnFMMZdU84HrNq3jdSxXBjHmzUssPo9nA8Sty8Aya3h+Ph/caFw/s3h+O4jMbtMXtWDCcJTv8yqqQ/oKYMx5FQDSpHQumGGww5hK1e32EI23BwTziOPWzYOBFRDydRZ8JBo/VJR5NOJHeGI1MYziiGM4rhjGI4oxjOKIYziuGMYjijGM4ohjOK4YxiOKMYziiGM4rhjGI4oxjOKIYziuH+s0cHJAAAAACC/r9uR6AXnBI3JW5K3JS4KXFT4qbETYmbEjclbkrclLgpcVPipsRNiZsSNyVuStyUuClxU+KmxE2JmxI3JW5K3JS4KXFT4qbETYmbEjclbkrclLgpcVPipsRNiZsSNyVuStyUuClxU+KmxE2JmxI3JW5K3JS4KXFT4qbETYmbEjclbkrclLgpcVPipsRNiZsSNyVuStyUuClxtUdHJwzDUAxFZTs2xInBxLQf2n/QvrTQGSLQ+ZAGuKIcTpTDiXI4UQ4nyuFEOZwohxPlcKIcTpTDiXI4UQ4nyuFEOZwohxPlcKIcThQD3jEJJmSRzCixHSakkyyYsRdMyCA5UWPzAZNxZJIVaY/bXU7G8Q32AhpDvvqCPd7qIzM0hI0mZsPtHDQp48RPKzQZpeEv1Vky7fFymTXh9gGY1gZJcqJI8QAAAABJRU5ErkJggg==');
	background-repeat: no-repeat;
	background-size: 100% 100%;
	width: 440rpx;
	height: 166rpx;
	margin: 340rpx auto 0 auto;
	font-size: 22rpx;
	text-align: center;
	padding-top: 11rpx;
	box-sizing: border-box;
}
.bargain .header .time .red {
	color: #fc4141;
}
.bargain .header .people {
	text-align: center;
	color: #fff;
	font-size: 20rpx;
	position: absolute;
	width: 85%;
	/* #ifdef MP */
	height: 44px;
	line-height: 44px;
	/* #endif */
	/* #ifdef H5 */
	top: 58rpx;
	/* #endif */
}

.bargain .header .time text {
	color: #333;
}

.bargain .wrapper,
.bargain .bargainGang,
.bargain .goodsDetails {
	width: 660rpx;
	border: 6rpx solid #fc8b42;
	background-color: #fff;
	border-radius: 20rpx;
	margin: -162rpx auto 0 auto;
	box-sizing: border-box;
	padding: 0 24rpx 47rpx 24rpx;
	position: relative;
}

.bargain .wrapper .pictxt {
	margin: 26rpx 0 37rpx 0;
}

.bargain .wrapper .pictxt .pictrue {
	width: 180rpx;
	height: 180rpx;
	position: relative;
}

.bargain .wrapper .pictxt .pictrue image {
	width: 100%;
	height: 100%;
	border-radius: 6rpx;
}

.bargain .wrapper .pictxt .text {
	width: 395rpx;
	font-size: 28rpx;
	color: #282828;
	height: 180rpx;
}

.bargain .wrapper .pictxt .text .money {
	font-weight: bold;
	font-size: 24rpx;
}

.bargain .wrapper .pictxt .text .money .num {
	font-size: 36rpx;
}

.bargain .wrapper .pictxt .text .successNum {
	font-size: 22rpx;
	color: #999;
}

.bargain .wrapper .cu-progress {
	overflow: hidden;
	height: 12rpx;
	background-color: #eee;
	width: 100%;
	border-radius: 20rpx;
}

.bargain .wrapper .cu-progress .bg-red {
	width: 0;
	height: 100%;
	transition: width 0.6s ease;
	border-radius: 20rpx;
	background-image: linear-gradient(to right, #ffa363 0%, #e93323 100%);
}

.bargain .wrapper .money {
	font-size: 22rpx;
	color: #999;
	margin-top: 15rpx;
}

.bargain .wrapper .bargainSuccess {
	font-size: 26rpx;
	color: #282828;
	text-align: center;
}

.bargain .wrapper .bargainSuccess .iconfont {
	font-size: 45rpx;
	color: #54c762;
	padding-right: 18rpx;
	vertical-align: -5rpx;
}

.bargain .wrapper .bargainBnt {
	font-size: 30rpx;
	font-weight: bold;
	color: #fff;
	width: 600rpx;
	height: 80rpx;
	border-radius: 40rpx;
	background-image: linear-gradient(to right, #f67a38 0%, #f11b09 100%);
	text-align: center;
	line-height: 80rpx;
	margin-top: 32rpx;
}

.bargain .wrapper .bargainBnt.on {
	border: 2rpx solid #e93323;
	color: #e93323;
	background-image: linear-gradient(to right, #fff 0%, #fff 100%);
	width: 596rpx;
	height: 76rpx;
}

.bargain .wrapper .bargainBnt.grey {
	color: #fff;
	background-image: linear-gradient(to right, #bbbbbb 0%, #bbbbbb 100%);
}

.bargain .wrapper .tip {
	font-size: 22rpx;
	color: #999;
	text-align: center;
	margin-top: 20rpx;
}

.bargain .wrapper .lock,
.bargain .bargainGang .lock,
.bargain .goodsDetails .lock {
	background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAiQAAABCCAYAAABnwc0eAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQyIDc5LjE2MDkyNCwgMjAxNy8wNy8xMy0wMTowNjozOSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo4YmQzMTQ1Ny01MGY2LWFmNDMtYmY4Yi1kNWRjZTMxZDg5MTUiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6M0RCMkU3QUEzQzBCMTFFOUI2N0VEOEJBMDUwMTU2ODMiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6M0RCMkU3QTkzQzBCMTFFOUI2N0VEOEJBMDUwMTU2ODMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTggKFdpbmRvd3MpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NDRmMWQxNmItZTIxMC1lYzQwLWJmODYtYzE4OWRiYzNmOGYyIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjhiZDMxNDU3LTUwZjYtYWY0My1iZjhiLWQ1ZGNlMzFkODkxNSIv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');
	background-repeat: no-repeat;
	background-size: 100% 100%;
	width: 548rpx;
	height: 66rpx;
	position: absolute;
	left: 50%;
	transform: translateX(-50%);
	bottom: -43rpx;
	z-index: 5;
}

.bargain .bargainGang {
	margin: 13rpx auto 0 auto;
}

.bargain .bargainGang .title,
.bargain .goodsDetails .title {
	font-size: 32rpx;
	font-weight: bold;
	height: 80rpx;
	margin-top: 30rpx;
}

.bargain .bargainGang .title .pictrue,
.bargain .goodsDetails .title .pictrue {
	width: 46rpx;
	height: 24rpx;
}

.bargain .bargainGang .title .pictrue.on,
.bargain .goodsDetails .title .pictrue.on {
	transform: rotate(180deg);
}

.bargain .bargainGang .title .pictrue image,
.bargain .goodsDetails .title .pictrue image {
	width: 100%;
	height: 100%;
	display: block;
}

.bargain .bargainGang .title .titleCon,
.bargain .goodsDetails .title .titleCon {
	margin: 0 20rpx;
}

.bargain .bargainGang .list .item {
	border-bottom: 1rpx dashed #ddd;
	height: 112rpx;
}

.bargain .bargainGang .list .item .pictxt {
	width: 310rpx;
}

.bargain .bargainGang .list .item .pictxt .pictrue {
	width: 70rpx;
	height: 70rpx;
}

.bargain .bargainGang .list .item .pictxt .pictrue image {
	width: 100%;
	height: 100%;
	border-radius: 50%;
	border: 2rpx solid #e93323;
}

.bargain .bargainGang .list .item .pictxt .text {
	width: 225rpx;
	font-size: 20rpx;
	color: #999;
}

.bargain .bargainGang .list .item .pictxt .text .name {
	font-size: 25rpx;
	color: #282828;
	margin-bottom: 7rpx;
}

.bargain .bargainGang .list .item .money {
	font-size: 25rpx;
}

.bargain .bargainGang .list .item .money .iconfont {
	font-size: 35rpx;
	vertical-align: middle;
	margin-right: 10rpx;
}

.bargain .bargainGang .load {
	font-size: 24rpx;
	text-align: center;
	line-height: 80rpx;
	height: 80rpx;
}

.bargain .goodsDetails {
	margin: 13rpx auto 0 auto;
}

.bargain .goodsDetails ~ .goodsDetails {
	margin-bottom: 50rpx;
}

.bargain .goodsDetails .conter {
	margin-top: 20rpx;
	overflow: hidden;
}

.bargain .goodsDetails .conter image {
	width: 100% !important;
	display: block !important;
}

.bargain .bargainTip {
	position: fixed;
	top: 50%;
	left: 50%;
	width: 560rpx;
	margin-left: -280rpx;
	z-index: 111;
	border-radius: 20rpx;
	background-color: #fff;
	transition: all 0.3s ease-in-out 0s;
	opacity: 0;
	transform: scale(0);
	padding-bottom: 60rpx;
	margin-top: -330rpx;
}

.bargain .bargainTip.on {
	opacity: 1;
	transform: scale(1);
}

.bargain .bargainTip .pictrue {
	width: 100%;
	height: 321rpx;
}

.bargain .bargainTip .pictrue image {
	width: 100%;
	height: 100%;
	border-radius: 20rpx 20rpx 0 0;
}

.bargain .bargainTip .cutOff {
	font-size: 30rpx;
	color: #666;
	padding: 0 29rpx;
	text-align: center;
	margin-top: 50rpx;
}

.bargain .bargainTip .cutOff.on {
	margin-top: 26rpx;
}

.bargain .bargainTip .help {
	font-size: 32rpx;
	font-weight: bold;
	text-align: center;
	margin-top: 40rpx;
}

.bargain .bargainTip .tipBnt {
	font-size: 32rpx;
	color: #fff;
	width: 360rpx;
	height: 82rpx;
	border-radius: 41rpx;
	background-image: linear-gradient(to right, #f67a38 0%, #f11b09 100%);
	text-align: center;
	line-height: 82rpx;
	margin: 50rpx auto 0 auto;
}

.bargain_view {
	width: 180rpx;
	height: 48rpx;
	background: rgba(0, 0, 0, 0.5);
	opacity: 1;
	border-radius: 0 0 6rpx 6rpx;
	position: absolute;
	bottom: 0;
	font-size: 22rpx;
	color: #fff;
	text-align: center;
	line-height: 48rpx;
}

.iconfonts {
	font-size: 22rpx !important;
}

.wxParse-div {
	width: auto !important;
	height: auto !important;
}

.bargain .mask {
	z-index: 100;
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
</style>
