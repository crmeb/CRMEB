<template>
	<view class="main-warper" style="background-color: var(--view-theme); padding-bottom: 50rpx" :style="colorStyle">
		<view class="bargain">
			<!-- #ifndef APP-PLUS || MP -->
			<view class="iconfont icon-xiangzuo" v-if="retunTop" @tap="goBack" :style="'top:' + navH + 'px'"></view>
			<!-- #endif -->
			<view :style="'background-image: url(' + (bargainUid != userInfo.uid ? imgHost + picUrl.support : imgHost + picUrl.barga) + ');'" class="header">
				<view class="people">
					{{ peopleCount.lookCount || 0 }}{{ $t(`人查看`) }} 丨 {{ peopleCount.shareCount || 0 }}{{ $t(`人分享`) }} 丨 {{ peopleCount.userCount || 0 }}{{ $t(`人参与`) }}
				</view>
				<countDown
					:tipText="$t(`倒计时`)"
					:dayText="$t(`天`)"
					:hourText="$t(`时`)"
					:minuteText="$t(`分`)"
					:secondText="$t(`秒`)"
					:datatime="datatime"
					:isDay="true"
					v-if="bargainUid == userInfo.uid"
				></countDown>
				<view v-if="bargainUid != userInfo.uid" class="pictxt acea-row row-center-wrapper">
					<view class="pictrue">
						<image :src="bargainUserInfo.avatar"></image>
					</view>
					<view class="text">
						{{ bargainUserInfo.nickname || '' }}
						<text>{{ $t(`邀请您帮忙砍价`) }}</text>
					</view>
				</view>
			</view>
			<view class="wrapper">
				<view class="pictxt acea-row row-between-wrapper" @tap="goProduct">
					<view class="pictrue">
						<image :src="bargainInfo.image"></image>
						<view class="bargain_view" v-if="bargainInfo.product_is_show">
							{{ $t(`查看商品`) }}
							<text class="iconfont icon-jiantou iconfonts"></text>
						</view>
					</view>
					<view class="text acea-row row-column-around">
						<view class="line2">{{ bargainInfo.title || '' }}</view>
						<view class="money">
							{{ $t(`当前`) }}: {{ $t(`￥`) }}
							<text class="num">{{ bargainInfo.price || '' }}</text>
						</view>
						<view class="successNum">{{ $t(`最低`) }}:{{ $t(`￥`) }}{{ bargainInfo.min_price || '' }}</view>
					</view>
				</view>
				<!-- 进度条 -->
				<block v-if="userBargainInfo.price > 0">
					<view class="cu-progress acea-row row-middle round margin-top">
						<view class="acea-row row-middle bg-red" :style="'width:' + userBargainInfo.pricePercent + '%;'"></view>
					</view>
					<view class="money acea-row row-between-wrapper">
						<view>{{ $t(`已砍`) }}{{ userBargainInfo.alreadyPrice }}</view>
						<view>{{ $t(`还剩`) }}{{ userBargainInfo.price }}</view>
					</view>
				</block>
				<!-- 自己砍价 -->
				<view v-if="userBargainInfo.bargainType == 1">
					<view class="bargainBnt" @tap="userBargain" v-if="productStock > 0 && quota > 0">{{ $t(`立即参与砍价`) }}</view>
					<view class="bargainBnt grey" v-if="productStock <= 0 || quota <= 0">{{ $t(`商品暂无库存`) }}</view>
				</view>
				<!-- 帮助砍价、帮砍成功： -->
				<view v-if="userBargainInfo.bargainType == 2">
					<view class="bargainBnt" @tap="shareModal">{{ $t(`邀请好友帮砍价`) }}</view>
					<view class="tip">
						{{ $t(`已有`) }}
						<text class="num">{{ userBargainInfo.count }}</text>
						{{ $t(`位好友成功砍价`) }}
					</view>
				</view>

				<view v-if="userBargainInfo.bargainType == 3">
					<view class="bargainBnt" @tap="setBargainHelp">{{ $t(`帮好友砍一刀`) }}</view>
				</view>
				<view v-if="userBargainInfo.bargainType == 4">
					<view class="bargainSuccess">
						<text class="iconfont icon-xiaolian"></text>
						{{ $t(`好友已砍价成功`) }}
					</view>
					<view class="bargainBnt" @tap="currentBargainUser">{{ $t(`我也要参与`) }}</view>
				</view>

				<view v-if="userBargainInfo.bargainType == 5">
					<view class="bargainSuccess">
						<text class="iconfont icon-xiaolian"></text>
						{{ $t(`已成功帮助好友砍价`) }}
					</view>
					<view class="bargainBnt" @tap="currentBargainUser">{{ $t(`我也要参与`) }}</view>
				</view>
				<view v-if="userBargainInfo.bargainType == 6">
					<view class="bargainSuccess">
						<text class="iconfont icon-xiaolian"></text>
						{{ $t(`恭喜您砍价成功，快去支付`) }}
					</view>
					<view class="bargainBnt" @tap="goPay">{{ $t(`立即支付`) }}</view>
					<view class="bargainBnt on" @tap="goBargainList">{{ $t(`抢更多商品`) }}</view>
				</view>

				<view class="lock" :style="'background-image: url(' + imgHost + picUrl.lock + ');'"></view>
			</view>
			<view class="bargainGang">
				<view class="title acea-row row-center-wrapper">
					<view class="pictrue">
						<image :src="picUrl.lace"></image>
					</view>
					<view class="titleCon">{{ $t(`砍价帮`) }}</view>
					<view class="pictrue on">
						<image :src="picUrl.lace"></image>
					</view>
				</view>
				<view class="list">
					<block v-for="(item, index) in bargainUserHelpList" :key="index" v-if="index < 3 || !couponsHidden">
						<view class="item acea-row row-between-wrapper">
							<view class="pictxt acea-row row-between-wrapper">
								<view class="pictrue">
									<image :src="item.avatar"></image>
								</view>
								<view class="text">
									<view class="name line1">{{ item.nickname }}</view>
									<view class="line1">{{ item.add_time }}</view>
								</view>
							</view>
							<view class="money">
								<text class="iconfont icon-kanjia"></text>
								{{ $t(`砍掉`) }}{{ $t(`￥`) }}{{ item.price }}
							</view>
						</view>
					</block>
					<view class="open acea-row row-center-wrapper" @click="openTap" v-if="bargainUserHelpList.length > 3">
						{{ couponsHidden ? $t(`更多`) : $t(`关闭`) }}
						<text class="iconfont" :class="couponsHidden == true ? 'icon-xiangxia' : 'icon-xiangshang'"></text>
					</view>
				</view>
				<view class="load" v-if="!limitStatus" @tap="getBargainUser">{{ $t(`点击加载更多`) }}</view>
				<view class="lock" :style="'background-image: url(' + imgHost + picUrl.lock + ');'"></view>
			</view>
			<view class="goodsDetails">
				<view class="title acea-row row-center-wrapper">
					<view class="pictrue">
						<image src="/images/left.png"></image>
					</view>
					<view class="titleCon">{{ $t(`商品详情`) }}</view>
					<view class="pictrue on">
						<image src="/images/left.png"></image>
					</view>
				</view>
				<view class="conter">
					<jyf-parser :html="bargainInfo.description" ref="article" :tag-style="tagStyle"></jyf-parser>
				</view>
				<view class="lock" :style="'background-image: url(' + imgHost + picUrl.lock + ');'"></view>
			</view>
			<view class="goodsDetails">
				<view class="title acea-row row-center-wrapper">
					<view class="pictrue">
						<image src="/images/left.png"></image>
					</view>
					<view class="titleCon">{{ $t(`砍价规则`) }}</view>
					<view class="pictrue on">
						<image src="/images/left.png"></image>
					</view>
				</view>
				<view class="conter">
					<jyf-parser :html="bargainInfo.rule" ref="article" :tag-style="tagStyle"></jyf-parser>
				</view>
			</view>
			<view class="bargainTip" :class="active == true ? 'on' : ''">
				<view class="pictrue">
					<image :src="picUrl.popup"></image>
				</view>
				<view v-if="bargainUid == userInfo.uid">
					<view class="cutOff">
						{{ $t(`您已砍掉`) }}
						<text style="color: var(--view-theme)">{{ userBargainPrice }}</text>
						{{ $t(`元，听说分享次数越多砍价成功的机会越大哦`) }}
					</view>
					<!-- #ifdef MP -->
					<button class="tipBnt" @tap="shareModal">{{ $t(`邀请好友帮砍价`) }}</button>
					<!-- #endif -->
					<!-- #ifdef H5 -->
					<view class="tipBnt" @tap="shareModal">{{ $t(`邀请好友帮砍价`) }}</view>
					<!-- #endif -->
				</view>
				<view v-else>
					<view class="help" style="color: #fc4141">{{ $t(`成功帮砍`) }}{{ $t(`￥`) }}{{ userBargainPrice }}</view>
					<view class="cutOff on">{{ $t(`您也可以砍价低价拿哦，快去挑选心仪的商品吧`) }}</view>
					<view @tap="currentBargainUser" class="tipBnt">{{ $t(`我也要参与`) }}</view>
				</view>
			</view>
			<view class="mask" catchtouchmove="true" v-show="active == true" @tap="close"></view>
		</view>
		<!-- 发送给朋友图片 -->
		<view class="share-box" v-if="H5ShareBox">
			<image :src="imgHost + '/statics/images/share-info.png'" @click="H5ShareBox = false"></image>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
		<!-- #ifdef H5 -->
		<view class="followCode" v-if="followCode">
			<view class="pictrue">
				<view class="code-bg"><img class="imgs" :src="codeSrc" /></view>
			</view>
			<view class="mask" @click="closeFollowCode"></view>
		</view>
		<zb-code
			ref="qrcode"
			v-show="false"
			:show="codeShow"
			:cid="cid"
			:val="val"
			:size="size"
			:unit="unit"
			:background="background"
			:foreground="foreground"
			:pdground="pdground"
			:icon="icon"
			:iconSize="iconsize"
			:onval="onval"
			:loadMake="loadMake"
			@result="qrR"
		/>
		<!-- #endif -->
		<!-- #ifdef MP -->
		<canvas class="canvas posters" canvas-id="myCanvas"></canvas>
		<!-- #endif -->
		<div class="posters" v-if="bargainPosterModal">
			<bargainPoster v-if="bargainPosterModal" ref="bargainPoster" comType="1" :comId="id" :comBargain="bargainUid" @getPosterImgae="getPosterImgae"></bargainPoster>
		</div>
		<!-- 海报展示 -->
		<view class="mask" v-if="posterImageModal" @click="listenerActionClose"></view>
		<view class="poster-pop" v-if="posterImageModal">
			<image src="/static/images/poster-close.png" class="close" @click="listenerActionClose"></image>
			<image class="poster-img" :src="posterImage"></image>
			<!-- #ifndef H5  -->
			<view class="save-poster" @click="savePosterPath">{{ $t(`保存到手机`) }}</view>
			<!-- #endif -->
			<!-- #ifdef H5 -->
			<view class="keep">{{ $t(`长按图片可以保存到手机`) }}</view>
			<!-- #endif -->
		</view>
		<!-- 分享按钮 -->
		<view class="generate-posters acea-row row-middle" :class="posters ? 'on' : ''">
			<!-- #ifndef MP -->
			<button class="item" hover-class="none" v-if="weixinStatus === true" @click="H5ShareBox = true">
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
			<!-- #endif -->
			<button class="item" hover-class="none" @click="getBargainUserBargainPricePoster">
				<view class="iconfont icon-haibao"></view>
				<view class="">{{ $t(`生成海报`) }}</view>
			</button>
		</view>
		<view class="mask" v-if="posters" @click="listenerActionClose"></view>
	</view>
</template>

<script>
import zbCode from '@/components/zb-code/zb-code.vue';
import bargainPoster from '../poster-poster/index.vue';
import { getBargainDetail, postBargainStartUser, postBargainStart, postBargainHelp, postBargainHelpList, postBargainShare } from '../../../api/activity.js';
import { colorChange } from '@/api/api.js';
import { postCartAdd } from '../../../api/store.js';
import util from '../../../utils/util.js';
import { toLogin } from '@/libs/login.js';
import { mapGetters } from 'vuex';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
import countDown from '@/components/countDown';
import home from '@/components/home';
import parser from '@/components/jyf-parser/jyf-parser';
import { TOKENNAME, HTTP_REQUEST_URL } from '@/config/app.js';
const app = getApp();
import colors from '@/mixins/color';

export default {
	components: {
		countDown,
		// #ifdef MP
		authorize,
		// #endif
		home,
		'jyf-parser': parser,
		bargainPoster
	},
	/**
	 * 页面的初始数据
	 */
	mixins: [colors],
	data() {
		return {
			imgHost: HTTP_REQUEST_URL,
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
			userBargainInfo: [],
			offset: 0,
			limit: 20,
			limitStatus: false,
			bargainUserHelpList: [],
			bargainUserHelpInfo: [],
			userBargainPrice: 0,
			status: '', // 0 开启砍价   1  朋友帮忙砍价  2 朋友帮忙砍价成功 3 完成砍价  4 砍价失败 5已创建订单
			peopleCount: [], //分享人数  浏览人数 参与人数
			retunTop: true,
			bargainPartake: 0,
			isHelp: false,
			interval: null,
			userBargainStatus: 0, //判断自己是否砍价
			bargainSumCount: 0, // 购买次数
			productStock: 0, //判断是否售罄；
			quota: 0, //判断是否已限量；
			userBargainStatusHelp: true,
			navH: '',
			statusPay: '',
			bargainPrice: 0,
			datatime: 0,
			offest: '',
			tagStyle: {
				img: 'width:100%;display:block;',
				table: 'width:100%',
				video: 'width:100%'
			},
			H5ShareBox: false, //公众号分享图片
			systemH: 100,
			isAuto: false, //没有授权的不会自动授权
			isShowAuth: false, //是否隐藏授权
			pages: '',
			posters: false,
			weixinStatus: false,
			couponsHidden: true,
			followCode: false,
			//二维码参数
			codeShow: false,
			cid: '1',
			ifShow: true,
			val: '', // 要生成的二维码值
			size: 200, // 二维码大小
			unit: 'upx', // 单位
			background: '#FFF', // 背景色
			foreground: '#000', // 前景色
			pdground: '#000', // 角标色
			icon: '', // 二维码图标
			iconsize: 40, // 二维码图标大小
			lv: 3, // 二维码容错级别 ， 一般不用设置，默认就行
			onval: true, // val值变化时自动重新生成二维码
			loadMake: true, // 组件加载完成后自动生成二维码
			src: '', // 二维码生成后的图片地址或base64
			codeSrc: '',
			picUrl: {},
			picList: [
				{
					popup: '../static/bulet.jpg',
					barga: '/statics/system_images/bargain_dt_bg_0.jpeg',
					support: '/statics/system_images/bargain_dt2_bg_0.jpeg',
					lock: '/statics/system_images/bargain_dt_lock_0.png',
					lace: '../static/buled.png'
				},
				{
					popup: '../static/greent.jpg',
					barga: '/statics/system_images/bargain_dt_bg_1.jpeg',
					support: '/statics/system_images/bargain_dt2_bg_1.jpeg',
					lock: '/statics/system_images/bargain_dt_lock_1.png',
					lace: '../static/greend.png'
				},
				{
					popup: '../static/redt.jpg',
					lace: '../static/redd.png',
					barga: '/statics/system_images/bargain_dt_bg_2.jpeg',
					support: '/statics/system_images/bargain_dt2_bg_2.jpeg',
					lock: '/statics/system_images/bargain_dt_lock_2.png'
				},
				{
					popup: '../static/pinkt.jpg',
					lace: '../static/pinkd.png',
					barga: '/statics/system_images/bargain_dt_bg_3.jpeg',
					support: '/statics/system_images/bargain_dt2_bg_3.jpeg',
					lock: '/statics/system_images/bargain_dt_lock_3.png'
				},
				{
					popup: '../static/oranget.jpg',
					lace: '../static/oranged.png',
					barga: '/statics/system_images/bargain_dt_bg_4.jpeg',
					support: '/statics/system_images/bargain_dt2_bg_4.jpeg',
					lock: '/statics/system_images/bargain_dt_lock_4.png'
				}
			],
			bargainPosterModal: false,
			posterImageModal: false,
			posterImage: ''
		};
	},
	computed: mapGetters(['isLogin']),
	watch: {
		isLogin: {
			handler: function (newV, oldV) {
				if (newV) {
					this.getBargainDetails();
					this.addShareBargain();
				}
			},
			deep: true
		},
		colorStatus(newValue, oldValue) {
			if (newValue) {
				this.colorShow(newValue);
			}
		}
	},
	/**
	 * 生命周期函数--监听页面加载
	 */
	onLoad(options) {
		var that = this;
		// #ifdef H5
		if (this.$wechat.isWeixin()) {
			this.weixinStatus = true;
		}
		// #endif
		if (!this.colorStatus) {
			colorChange('color_change').then((res) => {
				this.colorShow(res.data.status);
			});
		}
		// #ifdef MP
		uni.getSystemInfo({
			success: function (res) {
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
			if (that.bargainUid == 'undefined' || !that.bargainUid) {
				that.bargainUid = that.$store.state.app.uid;
			}
			this.getBargainDetails();
			this.addShareBargain();
		} else {
			this.$Cache.set('login_back_url', `/pages/activity/goods_bargain_details/index?id=${options.id}&bargain=${this.bargainUid}`);
			toLogin();
		}
		uni.setNavigationBarTitle({
			title: this.$t(`砍价详情`)
		});
	},
	methods: {
		getPosterImgae(url) {
			this.posterImage = url;
			this.bargainPosterModal = false;
			this.posterImageModal = true;
		},
		colorShow(colorStatus) {
			switch (colorStatus) {
				case 1:
					this.picUrl = this.picList[0];
					break;
				case 2:
					this.picUrl = this.picList[1];
					break;
				case 3:
					this.picUrl = this.picList[2];
					break;
				case 4:
					this.picUrl = this.picList[3];
					break;
				case 5:
					this.picUrl = this.picList[4];
					break;
				default:
					this.picUrl = this.picList[2];
					break;
			}
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
				title: that.bargainInfo.title,
				imageUrl: that.bargainInfo.small_image,
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
		qrR(res) {
			this.codeSrc = res;
		},
		// #endif
		/**
		 * 分享打开
		 *
		 */
		listenerActionSheet() {
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
		shareModal() {
			this.active = false;
			this.posters = true;
		},
		getBargainUserBargainPricePoster() {
			if (!this.posterImage) {
				this.bargainPosterModal = true;
				this.posters = false;
			} else {
				this.bargainPosterModal = false;
				this.posterImageModal = true;
			}
			// uni.navigateTo({
			// 	url: '/pages/activity/poster-poster/index?type=1&id=' + this.id + '&bargain=' + this.bargainUid
			// });
		},
		// 分享关闭
		listenerActionClose() {
			this.posters = false;
			this.posterImageModal = false;
		},
		// 小程序关闭分享弹窗；
		goFriend() {
			this.posters = false;
		},
		openTap() {
			this.$set(this, 'couponsHidden', !this.couponsHidden);
		},
		// 授权关闭
		authColse(e) {
			this.isShowAuth = e;
		},
		// 去商品页
		goProduct() {
			if (!this.bargainInfo.product_is_show) return;
			uni.navigateTo({
				url: `/pages/goods_details/index?id=${this.bargainInfo.product_id}`
			});
		},
		// 自己砍价；
		userBargain() {
			let that = this;
			if (that.userInfo.uid == that.bargainUid) {
				if (that.userBargainInfo.bargainOrderCount >= that.bargainInfo.num) {
					return that.$util.Tips({
						title: that.$t(`该商品每人限购`) + `${that.bargainInfo.num}${that.bargainInfo.unit_name}`
					});
				} else {
					that.setBargain();
				}
			}
		},
		goBack() {
			uni.navigateBack({
				delta: 1
			});
		},
		gobargainUserInfo() {
			//获取开启砍价用户信息
			var that = this;
			var data = {
				userId: that.bargainUid
			};
			postBargainStartUser({
				bargainId: that.id,
				bargainUserUid: that.bargainUid
			}).then((res) => {
				that.$set(that, 'bargainUserInfo', res.data);
			});
		},
		goPay() {
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
				.then((res) => {
					uni.navigateTo({
						url: '/pages/goods/order_confirm/index?new=1&cartId=' + res.data.cartId
					});
				})
				.catch((err) => {
					return that.$util.Tips({
						title: err
					});
				});
		},
		getBargainDetails() {
			//获取砍价产品详情
			var that = this;
			var id = that.id;
			getBargainDetail(id, that.bargainUid)
				.then((res) => {
					that.bargainInfo = res.data.bargain;
					that.userBargainInfo = res.data.userBargainInfo;
					that.bargainPrice = res.data.bargain.price;
					that.userInfo = res.data.userInfo;
					that.productStock = res.data.bargain.attr.product_stock;
					that.quota = res.data.bargain.attr.quota;
					that.datatime = res.data.bargain.stop_time;
					that.pages = '/pages/activity/goods_bargain_details/index?id=' + that.id + '&bargain=' + that.bargainUid + '&scene=' + that.userInfo.uid;
					uni.setNavigationBarTitle({
						title: res.data.bargain.title.substring(0, 13) + '...'
					});
					that.bargainUserHelpList = [];
					that.getBargainUser();
					if (that.bargainUid != that.userInfo.uid) that.gobargainUserInfo();
					//#ifdef H5
					that.setOpenShare();
					//#endif
				})
				.catch(function (err) {
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
		currentBargainUser() {
			//当前用户砍价
			this.$set(this, 'bargainUid', this.userInfo.uid);
			this.setBargain();
		},
		setBargain() {
			//参与砍价
			var that = this;
			postBargainStart(that.id).then(
				(res) => {
					that.$set(that, 'userBargainPrice', res.data.price);
					that.$set(that, 'active', true);
					that.getBargainDetails();
					that.userBargainStatus = 1;
				},
				(error) => {
					that.$util.Tips({
						title: error
					});
				}
			);
		},
		setBargainHelp() {
			//帮好友砍价
			var that = this;
			var data = {
				bargainId: that.id,
				bargainUserUid: that.bargainUid
			};
			postBargainHelp(data)
				.then((res) => {
					that.$set(that, 'userBargainPrice', res.data.price);
					that.$set(that, 'active', true);
					that.getBargainDetails();
				})
				.catch((err) => {
					that.$util.Tips({
						title: err
					});
					that.getBargainDetails();
				});
		},
		getBargainUser() {
			//获取砍价帮
			var that = this;
			var data = {
				bargainId: that.id,
				bargainUserUid: that.bargainUid,
				offset: that.offset,
				limit: that.limit
			};
			postBargainHelpList(data).then((res) => {
				var bargainUserHelpListNew = [];
				var bargainUserHelpList = that.bargainUserHelpList;
				var len = res.data.length;

				bargainUserHelpListNew = bargainUserHelpList.concat(res.data);

				that.$set(that, 'bargainUserHelpList', res.data);
				that.$set(that, 'limitStatus', data.limit > len);
				that.$set(that, 'offest', Number(data.offset) + Number(data.limit));
			});
		},
		goBargainList() {
			uni.navigateTo({
				url: '/pages/activity/goods_bargain/index'
			});
		},
		close() {
			this.$set(this, 'active', false);
		},
		addShareBargain() {
			//添加分享次数 获取人数
			var that = this;
			postBargainShare(this.id).then((res) => {
				that.$set(that, 'peopleCount', res.data);
				this.pages = '/pages/activity/goods_bargain_details/index?id=' + this.id + '&bargain=' + this.bargainUid + '&spid=' + this.userInfo.uid;
			});
		},
		//#ifdef H5
		setOpenShare() {
			let that = this;
			let configTimeline = {
				title: that.$t(`您的好友`) + that.userInfo.nickname + that.$t(`邀请您砍价`) + that.bargainInfo.title,
				desc: that.bargainInfo.info,
				link:
					window.location.protocol +
					'//' +
					window.location.host +
					'/pages/activity/goods_bargain_details/index?id=' +
					that.id +
					'&bargain=' +
					that.userInfo.uid +
					'&spid=' +
					this.userInfo.uid,
				imgUrl: that.bargainInfo.image
			};
			if (this.$wechat.isWeixin()) {
				this.$wechat
					.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareAppMessage', 'onMenuShareTimeline'], configTimeline)
					.then((res) => {})
					.catch((res) => {
						if (res.is_ready) {
							res.wx.updateAppMessageShareData(configTimeline);
							res.wx.updateTimelineShareData(configTimeline);
							res.wx.onMenuShareAppMessage(configTimeline);
							res.wx.onMenuShareTimeline(configTimeline);
						}
					});
			}
		},
		closeFollowCode() {
			this.$set(this, 'followCode', false);
		},
		//#endif
		savePosterPath() {
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
		}
	},
	/**
	 * 生命周期函数--监听页面隐藏
	 */
	onHide: function () {
		if (this.interval !== null) clearInterval(this.interval);
	},
	/**
	 * 生命周期函数--监听页面卸载
	 */
	onUnload: function () {
		if (this.interval !== null) clearInterval(this.interval);
	},
	//#ifdef MP
	/**
	 * 用户点击右上角分享
	 */
	onShareAppMessage: function () {
		let that = this,
			share = {
				title: that.$t(`您的好友`) + that.userInfo.nickname + this.$t(`邀请您砍价`) + that.bargainInfo.title + this.$t(`go_help`),
				path: '/pages/activity/goods_bargain_details/index?id=' + this.id + '&bargain=' + this.bargainUid + '&spid=' + this.userInfo.uid,
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
	// background-color: #e93323 !important;
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
	background-repeat: no-repeat;
	background-size: 100% 100%;
	width: 100%;
	height: 572rpx;
	margin: 0 auto;
	padding-top: 340rpx;
	position: relative;
}

.bargain .header .pictxt {
	margin: -60rpx auto 0 auto;
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
	width: 440rpx;
	font-size: 22rpx;
	line-height: 36rpx;
	text-align: center;
	box-sizing: border-box;
	position: absolute;
	left: 50%;
	margin-left: -220rpx;
	top: 298rpx;
}

.bargain .header .time .red {
	color: var(--view-theme);
}

.bargain .header .people {
	text-align: center;
	color: #fff;
	font-size: 20rpx;
	position: absolute;
	width: 100%;
	/* #ifdef MP || APP-PLUS */
	height: 44px;
	line-height: 44px;
	top: 40rpx;
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
	margin: -190rpx auto 0 auto;
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
	background-image: linear-gradient(to right, var(--view-minorColor) 0%, var(--view-theme) 100%);
}

.bargain .wrapper .money {
	font-size: 22rpx;
	color: #999;
	margin-top: 15rpx;
	color: var(--view-priceColor);
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
	// background-image: linear-gradient(to right, var(--view-minorColor) 0%, var(--view-theme) 100%);
	background-color: var(--view-theme);
	text-align: center;
	line-height: 80rpx;
	margin-top: 32rpx;
}

.bargain .wrapper .bargainBnt.on {
	border: 2rpx solid var(--view-theme);
	color: var(--view-theme);
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

.bargain .wrapper .tip .num {
	color: var(--view-theme);
}

.bargain .wrapper .lock,
.bargain .bargainGang .lock,
.bargain .goodsDetails .lock {
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
	color: var(--view-theme);
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
	border: 2rpx solid var(--view-theme);
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
	color: var(--view-theme);
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
	color: var(--view-theme);
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
	// background-image: linear-gradient(to right, var(--view-minorColor) 0%, var(--view-theme) 100%);
	background-color: var(--view-theme);
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

.followCode {
	.pictrue {
		width: 500rpx;
		height: 530rpx;
		border-radius: 12px;
		left: 50%;
		top: 50%;
		margin-left: -250rpx;
		margin-top: -360rpx;
		position: fixed;
		z-index: 10000;

		.code-bg {
			display: flex;
			justify-content: center;
			width: 100%;
			height: 100%;
			background-image: url('~@/static/images/code-bg.png');
			background-size: 100% 100%;
		}

		.imgs {
			width: 310rpx;
			height: 310rpx;
			margin-top: 92rpx;
		}
	}

	.mask {
		z-index: 9999;
	}
}
.main-warper {
	position: relative;
	/deep/ .posterCon {
		position: static;
	}
}
.posters {
	position: fixed;
	bottom: -5000px;
	left: -5000px;
}

.poster-pop {
	width: 450rpx;
	height: 714rpx;
	position: fixed;
	left: 50%;
	transform: translateX(-50%);
	z-index: 399;
	top: 50%;
	margin-top: -377rpx;
	.poster-img{
		border-radius: 6px;
	}
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
	margin-top: 20rpx;
}

.poster-pop .keep {
	color: #fff;
	text-align: center;
	font-size: 25rpx;
	margin-top: 10rpx;
}
.canvas {
	width: 700rpx;
	height: 1100rpx;
}
</style>
