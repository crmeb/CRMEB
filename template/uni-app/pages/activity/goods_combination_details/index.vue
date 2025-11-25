<template>
	<view :style="colorStyle">
		<!-- #ifndef APP-PLUS -->
		<view class='navbar' :style="{height:navH+'rpx',opacity:opacity}">
			<view class='navbarH' :style='"height:"+navH+"rpx;"'>
				<view class='navbarCon acea-row row-center-wrapper'>
					<view class="header acea-row row-center-wrapper">
						<view class="item" :class="navActive === index ? 'on' : ''" v-for="(item,index) in navList"
							:key='index' @tap="tap(item,index)">
							{{ item }}
						</view>
					</view>
				</view>
			</view>
		</view>
		<view id="home" class="home-nav acea-row row-center-wrapper" :class="[opacity>0.5?'on':'']"
			:style="{ top: homeTop +'rpx'}">
			<view class="iconfont icon-fanhui2" @tap="returns"></view>
			<!-- #ifdef MP -->
			<view class="line"></view>
			<view class="iconfont icon-gengduo5" @click="moreNav"></view>
			<!-- #endif -->
		</view>
		<!-- #endif -->

		<!-- #ifdef H5 -->
		<view id="home" class="home-nav right acea-row row-center-wrapper" :class="[opacity>0.5?'on':'']"
			:style="{ top: homeTop +'rpx'}">
			<!-- #ifdef H5 -->
			<view class="iconfont icon-gengduo2" @click="moreNav"></view>
		</view>
		<!-- #endif -->
		<!-- #endif -->
		<homeList :navH="navH" :returnShow="returnShow" :currentPage="currentPage" :sysHeight="sysHeight">
		</homeList>
		<!-- 详情 -->
		<view class='product-con'>
			<scroll-view :scroll-top="scrollTop" scroll-y='true' scroll-with-animation="true"
				:style="'height:'+height+'px;'" @scroll="scroll">
				<view id="past0">
					<!-- #ifdef APP-PLUS || MP -->
					<view class="" :style="'width:100%;' + 'height:'+sysHeight"></view>
					<!-- #endif -->
					<productConSwiper :imgUrls="imgUrls" @showSwiperImg="showSwiperImg"></productConSwiper>
					<view class='wrapper'>
						<view class='share acea-row row-between row-bottom'>
							<view class='money font-color'>
								{{$t(`￥`)}}<text class='num'>{{storeInfo.price || 0}}</text>
								<text
									v-if="attribute.productAttr.length && (attribute.productAttr.length?attribute.productAttr[0].attr_values.length:0) > 1">{{$t(`起`)}}</text>
								<text class='y-money'>{{$t(`￥`)}}{{storeInfo.product_price || 0}}</text>
							</view>
							<view class='iconfont icon-fenxiang' @click="listenerActionSheet"></view>
						</view>
						<view class='introduce'>{{storeInfo.title}}</view>
						<view class='label acea-row row-between-wrapper'>
							<view class='stock'>{{$t(`类型`)}}：{{storeInfo.people || 0}}{{$t(`人团`)}}</view>
							<view>{{$t(`累计销量`)}}：{{storeInfo.total?storeInfo.total:0}} {{$t(storeInfo.unit_name) || ''}}
							</view>
							<view>{{$t(`限量剩余`)}}: {{ storeInfo.quota ? storeInfo.quota : 0 }}
								{{$t(storeInfo.unit_name) || ''}}
							</view>
						</view>
					</view>
					<view class='attribute acea-row row-between-wrapper' @tap='selecAttr'
						v-if='attribute.productAttr.length'>
						<!-- 		<view>{{attr}}：<text class='atterTxt'>{{attrValue}}</text></view>
						<view class='iconfont icon-jiantou'></view> -->
						<view class="flex">
							<view style="display: flex; align-items: center; width: 90%">
								<view class="attr-txt"> {{ attr }}： </view>
								<view class="atterTxt line1" style="width: 82%">{{
						      attrValue
						    }}</view>
							</view>
							<view class="iconfont icon-jiantou"></view>
						</view>
						<view class="acea-row row-between-wrapper" style="margin-top: 7px; padding-left: 70px"
							v-if="skuArr.length > 1">
							<view class="flexs">
								<image :src="item.image" v-for="(item, index) in skuArr.slice(0, 4)" :key="index"
									class="attrImg"></image>
							</view>
							<view class="switchTxt">{{$t(`共`)}}{{ skuArr.length }}{{$t(`种规格可选`)}}</view>
						</view>
					</view>
					<view class="bg-color">
						<view class='notice acea-row row-middle'>
							<view class='num font-num'>
								<text class='iconfont icon-laba'></text>
								{{$t(`已拼`)}}{{pink_ok_sum}}{{$t(`件`)}}<text class='line'>|</text>
							</view>
							<view class='swiper'>
								<swiper :indicator-dots="indicatorDots" :autoplay="autoplay" interval="2500"
									duration="500" vertical="true" circular="true">
									<block v-for="(item,index) in itemNew" :key='index'>
										<swiper-item>
											<view class='line1'>{{item}}</view>
										</swiper-item>
									</block>
								</swiper>
							</view>
						</view>
					</view>
					<view class='assemble'>
						<view class='item acea-row row-between-wrapper' v-for='(item,index) in pink' :key='index'
							v-if="index < AllIndex">
							<view class='pictxt acea-row row-between-wrapper'>
								<view class='pictrue'>
									<image :src='item.avatar'></image>
								</view>
								<view class='text line1'>{{item.nickname}}</view>
							</view>
							<view class='right acea-row row-middle'>
								<view>
									<view class='lack'>{{$t(`还差`)}}<text
											class='font-num'>{{item.count}}</text>{{$t(`人成团`)}}</view>
									<view class='time'>
										<count-down :is-day="false" :tip-text="' '" :day-text="' '" :hour-text="':'"
											:minute-text="':'" :second-text="' '" :datatime="item.stop_time">
										</count-down>
									</view>
								</view>
								<navigator hover-class='none'
									:url="'/pages/activity/goods_combination_status/index?id='+item.id"
									class='spellBnt'>
									{{$t(`去拼单`)}}
									<!-- <text class='iconfont icon-jiantou'></text> -->
								</navigator>
							</view>
						</view>
						<template v-if="pink.length">
							<view class='more' @tap='showAll' v-if="pink.length > AllIndex">{{$t(`查看更多`)}}<text
									class='iconfont icon-xiangxia'></text></view>
							<view class='more' @tap='hideAll'
								v-else-if="pink.length === AllIndex && pink.length !== AllIndexDefault">
								{{$t(`收起`)}}<text class='iconfont icon-xiangshang'></text>
							</view>
						</template>
					</view>
					<view class='playWay'>
						<view class='title acea-row row-between-wrapper'>
							<view>{{$t(`拼团玩法`)}}</view>
						</view>
						<view class='way acea-row row-middle'>
							<view class='item'>
								<text class='num'>①</text>
								<text>{{$t(`开团/参团`)}}</text>
							</view>
							<view class='iconfont icon-arrow'></view>
							<view class='item'>
								<text class='num'>②</text>
								<text>{{$t(`邀请好友`)}}</text>
							</view>
							<view class='iconfont icon-arrow'></view>
							<view class='item'>
								<text class='num'>③</text>
								<text>{{$t(`满员发货`)}}</text>
							</view>
						</view>
					</view>
					<view class='playWay'>
						<view class='title acea-row row-between-wrapper'>
							<view>{{$t(`拼团简介`)}}</view>
						</view>
						<view class='way acea-row row-middle py-16'>
							{{storeInfo.info}}
						</view>
					</view>
				</view>
				
				<view class='userEvaluation' id="past1" v-if="replyCount">
					<view class='title acea-row row-between-wrapper'>
						<view>{{$t(`用户评价`)}}({{replyCount}})</view>
						<navigator class='praise' hover-class='none'
							:url='"/pages/goods/goods_comment_list/index?product_id="+storeInfo.product_id'>
							<text class='font-num'>{{replyChance || 0}}%</text>
							{{$t(`好评率`)}}
							<text class='iconfont icon-jiantou'></text>
						</navigator>
					</view>
					<userEvaluation :reply="reply"></userEvaluation>
				</view>
				<view class='product-intro' id="past2">
					<view class='title'>{{$t(`产品介绍`)}}</view>
					<view class='conter'>
						<!-- <view class="" v-html="storeInfo.description"></view> -->
						<parser :html="storeInfo.description" ref="article" :tag-style="tagStyle"></parser>
					</view>
				</view>
				<view class="uni-p-b-98"></view>
			</scroll-view>
			<view class='footer acea-row row-between-wrapper' :class="{'eject':storeInfo.id}">
				<navigator hover-class="none" class="item" open-type="switchTab" url="/pages/index/index">
					<view class="iconfont icon-shouye6"></view>
					<view class="p_center">{{$t(`首页`)}}</view>
				</navigator>
				<view @tap='setCollect' class='item'>
					<view class='iconfont icon-shoucang1' v-if="storeInfo.userCollect"></view>
					<view class='iconfont icon-shoucang' v-else></view>
					<view class="p_center">{{$t(`收藏`)}}</view>
				</view>
				<view class="bnt acea-row">
					<view class="joinCart bnts" @tap="goProduct" v-if="storeInfo.product_is_show">{{$t(`单独购买`)}}</view>
					<view class="buy bnts" :class="!storeInfo.product_is_show ? 'long-btn' : ''" @tap="goCat"
						v-if='attribute.productSelect.product_stock>0&&attribute.productSelect.quota>0'>
						{{$t(`立即开团`)}}
					</view>
					<view class="buy bnts bg-color-hui" :class="!storeInfo.product_is_show ? 'long-btn' : ''" v-if="!dataShow">
						{{$t(`立即开团`)}}
					</view>
					<view class="buy bnts bg-color-hui" :class="!storeInfo.product_is_show ? 'long-btn' : ''"
						v-if='attribute.productSelect.quota <= 0 || attribute.productSelect.product_stock <= 0'>
						{{$t(`已售罄`)}}
					</view>
				</view>
			</view>
		</view>

		<!-- 分享按钮 -->
		<view class="generate-posters acea-row row-middle" :class="posters ? 'on' : ''">
			<!-- #ifndef MP -->
			<button class="item" hover-class='none' v-if="weixinStatus === true" @click="H5ShareBox = true">
				<!-- <button class="item" hover-class='none' v-if="weixinStatus === true" @click="setShareInfoStatus"> -->
				<view class="iconfont icon-weixin3"></view>
				<view class="">{{$t(`发送给朋友`)}}</view>
			</button>
			<!-- #endif -->
			<!-- #ifdef MP -->
			<button class="item" open-type="share" hover-class='none' @click="goFriend">
				<view class="iconfont icon-weixin3"></view>
				<view class="">{{$t(`发送给朋友`)}}</view>
			</button>
			<!-- #endif -->
			<!-- #ifdef APP-PLUS -->
			<view class="item" @click="appShare('WXSceneSession')">
				<view class="iconfont icon-weixin3"></view>
				<view class="">{{$t(`微信好友`)}}</view>
			</view>
			<view class="item" @click="appShare('WXSenceTimeline')">
				<view class="iconfont icon-pengyouquan"></view>
				<view class="">{{$t(`微信朋友圈`)}}</view>
			</view>
			<!-- #endif -->
			<button class="item" hover-class='none' @tap="goPoster('scombination')">
				<view class="iconfont icon-haibao"></view>
				<view class="">{{$t(`生成海报`)}}</view>
			</button>
		</view>
		<view class="mask" v-if="posters" @click="listenerActionClose"></view>

		<!-- 海报展示 -->
		<view class='poster-pop' v-if="posterImageStatus">
			<image src='/static/images/poster-close.png' class='close' @click="posterImageClose"></image>
			<image class="poster-img" :src='posterImage'></image>
			<!-- #ifndef H5  -->
			<view class='save-poster' @click="savePosterPath">{{$t(`保存到手机`)}}</view>
			<!-- #endif -->
			<!-- #ifdef H5 -->
			<view class="keep">{{$t(`长按图片可以保存到手机`)}}</view>
			<!-- #endif -->
		</view>
		<view class='mask1' v-if="posterImageStatus"></view>
		<canvas class="canvas" canvas-id='myCanvas' v-if="canvasStatus"></canvas>
		<view class="share-box" v-if="H5ShareBox">
			<image :src="imgHost + '/statics/images/share-info.png'" @click="H5ShareBox = false"></image>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<product-window :attr='attribute' :limitNum='1' :type="'combination'" @myevent="onMyEvent" @ChangeAttr="ChangeAttr"
			@ChangeCartNum="ChangeCartNum" @iptCartNum="iptCartNum" @attrVal="attrVal" @getImg="showImg">
		</product-window>
		<swiperPrevie ref="cusSwiperImg" :list="storeInfo.images"></swiperPrevie>
		<cus-previewImg ref="cusPreviewImg" :list="skuArr" @changeSwitch="changeSwitch"
			@shareFriend="listenerActionSheet" />
		<kefuIcon :ids='storeInfo.product_id' :routineContact='routineContact'></kefuIcon>
		<!-- #ifdef H5 || APP-PLUS -->
		<zb-code ref="qrcode" :show="codeShow" :cid="cid" :val="codeVal" :size="size" :unit="unit"
			:background="background" :foreground="foreground" :pdground="pdground" :icon="codeIcon" :iconSize="iconsize"
			:onval="onval" :loadMake="loadMake" @result="qrR" />
		<!-- #endif -->
	</view>
</template>

<script>
	const app = getApp();
	import {
		mapGetters
	} from "vuex";
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import productConSwiper from '@/components/productConSwiper/index.vue'
	import swiperPrevie from "@/components/cusPreviewImg/swiperPrevie.vue";
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		getCombinationDetail,
		scombinationCode
	} from '@/api/activity.js';
	import {
		postCartAdd,
		collectAdd,
		collectDel
	} from '@/api/store.js';
	import {
		imageBase64
	} from "@/api/public";
	import productWindow from '@/components/productWindow/index.vue'
	import userEvaluation from '@/components/userEvaluation/index.vue'
	import countDown from '@/components/countDown/index.vue'
	import kefuIcon from '@/components/kefuIcon';
	import {
		getProductCode
	} from '@/api/store.js'
	import {
		getUserInfo
	} from '@/api/user.js';
	// #ifdef APP-PLUS
	import {
		TOKENNAME
	} from '@/config/app.js';
	// #endif
	import colors from '@/mixins/color.js';
	import parser from "@/components/jyf-parser/jyf-parser";
	import cusPreviewImg from "@/components/cusPreviewImg/index.vue";
	import menuIcon from "@/components/menuIcon.vue";
	import {
		sharePoster
	} from "@/mixins/sharePoster";
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	import homeList from '@/components/homeList';
	let sysHeight = uni.getSystemInfoSync().statusBarHeight + 'px';
	export default {
		components: {
			productConSwiper,
			kefuIcon,
			// #ifdef MP
			authorize,
			// #endif
			"product-window": productWindow,
			userEvaluation,
			countDown,
			cusPreviewImg,
			parser,
			menuIcon,
			homeList,
			swiperPrevie
		},
		computed: mapGetters({
			'isLogin': 'isLogin',
			'userData': 'userInfo'
		}),
		mixins: [colors, sharePoster],
		data() {
			return {
				imgHost: HTTP_REQUEST_URL,
				dataShow: 0,
				navH: '',
				id: 0,
				userInfo: {},
				itemNew: [],
				indicatorDots: false,
				circular: true,
				autoplay: true,
				interval: 3000,
				duration: 500,
				attribute: {
					cartAttr: false,
					productAttr: [],
					productSelect: {}
				},
				productValue: [],
				isOpen: false,
				attr: this.$t(`请选择`),
				attrValue: '',
				AllIndex: 2,
				maxAllIndex: 0,
				replyChance: '',
				limitNum: 1,
				timeer: null,
				iSplus: false,
				navList: [this.$t(`商品`), this.$t(`评价`), this.$t(`详情`)],
				opacity: 0,
				scrollY: 0,
				topArr: [],
				toView: '',
				height: 0,
				heightArr: [],
				lock: false,
				scrollTop: 0,
				storeInfo: {},
				pink_ok_sum: 0,
				pink: [],
				replyCount: 0,
				reply: [],
				imgUrls: [],
				sharePacket: '',
				tagStyle: {
					img: 'width:100%;display:block;',
					table: 'width:100%',
					video: 'width:100%'
				},
				posters: false,
				weixinStatus: false,
				posterImageStatus: false,
				canvasStatus: false, //海报绘图标签
				storeImage: '', //海报产品图
				PromotionCode: '', //二维码图片
				posterImage: '', //海报路径
				posterbackgd: '/static/images/posterbackgd.png',
				navActive: 0,
				actionSheetHidden: false,
				attrTxt: '',
				cart_num: '',
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				AllIndexDefault: 0,
				homeTop: 20,
				returnShow: true,
				H5ShareBox: false,
				routineContact: 0,
				skuArr: [],
				selectSku: {},
				showMenuIcon: false,
				currentPage: false,
				sysHeight: sysHeight,
			}
		},
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						// this.downloadFilePromotionCode();
						this.combinationDetail();
					}
				},
				deep: true
			},
		},
		onLoad(options) {
			let that = this
			var pages = getCurrentPages();
			that.returnShow = pages.length === 1 ? false : true;
			this.$nextTick(() => {
				// #ifdef MP
				const menuButton = uni.getMenuButtonBoundingClientRect();
				const query = uni.createSelectorQuery().in(this);
				query
					.select('#home')
					.boundingClientRect(data => {
						this.homeTop = menuButton.top * 2 + menuButton.height - data.height;
					})
					.exec();
				// #endif
			})
			// #ifdef MP
			this.navH = app.globalData.navHeight;
			// #endif
			// #ifdef H5
			that.navH = 96;
			// #endif
			// #ifdef APP-PLUS
			that.navH = 30;
			// #endif
			//设置商品列表高度
			uni.getSystemInfo({
				success: function(res) {
					that.height = res.windowHeight
					//res.windowHeight:获取整个窗口高度为px，*2为rpx；98为头部占据的高度；
				},
			});
			//扫码携带参数处理
			// #ifdef MP
			if (options.scene) {
				let value = this.$util.getUrlParams(decodeURIComponent(options.scene));
				if (value.id) options.id = value.id;
				//记录推广人uid
				if (value.pid) app.globalData.spid = value.pid;
			}
			if (!options.id && !options.scene) return this.$util.Tips({
				title: this.$t(`缺少参数无法查看商品`)
			}, {
				tab: 3,
				url: 1
			});
			//记录推广人uid
			if (options.spid) app.globalData.spid = options.spid;
			// #endif
			if (options.hasOwnProperty('id')) {
				this.id = options.id
				if (this.isLogin) {
					this.combinationDetail();
				} else {
					// #ifdef H5 || APP-PLUS
					try {
						uni.setStorageSync('comGoodsId', options.id);
					} catch (e) {}
					// #endif 
					this.$Cache.set('login_back_url',
						`/pages/activity/goods_combination_details/index?id=${options.id}`);
					toLogin();
				}
			} else {
				try {
					let val = uni.getStorageSync('comGoodsId');
					if (val != '') {
						this.id = val
						this.combinationDetail();
					}
				} catch (e) {
					uni.showToast({
						title: this.$t(`参数错误`),
						icon: 'none',
						duration: 1000,
						mask: true,
					})
				}
			};

		},
		onNavigationBarButtonTap(e) {
			this.currentPage = !this.currentPage
		},
		methods: {
			moreNav() {
				this.currentPage = !this.currentPage
			},
			showSwiperImg(index) {
				this.$refs.cusSwiperImg.open(index);
			},
			qrR(res) {
				// #ifdef H5
				if (!this.$wechat.isWeixin() || this.shareQrcode != '1') {
					this.PromotionCode = res;
					this.followCode = ''
				}
				// #endif
				// #ifdef APP-PLUS
				this.PromotionCode = res;
				// #endif
			},

			// app分享
			// #ifdef APP-PLUS
			appShare(scene) {
				let that = this
				let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
				let curRoute = routes[routes.length - 1].$page.fullPath // 获取当前页面路由，也就是最后一个打开的页面路由
				uni.share({
					provider: "weixin",
					scene: scene,
					type: 0,
					href: `${HTTP_REQUEST_URL}${curRoute}`,
					title: that.storeInfo.title,
					summary: that.storeInfo.info,
					imageUrl: that.storeInfo.small_image,
					success: function(res) {
						uni.showToast({
							title: this.$t(`分享成功`),
							icon: 'success'
						})
						that.posters = false;
					},
					fail: function(err) {
						uni.showToast({
							title: this.$t(`分享失败`),
							icon: 'none',
							duration: 2000
						})
						that.posters = false;
					}
				});
			},
			// #endif

			showAll: function() {
				this.AllIndexDefault = this.AllIndex;
				this.AllIndex = this.pink.length;
			},
			hideAll: function() {
				this.AllIndex = this.AllIndexDefault;
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e;
			},
			/**
			 * 购物车手动填写
			 * 
			 */
			iptCartNum: function(e) {
				this.$set(this.attribute.productSelect, 'cart_num', e);
				this.$set(this, "cart_num", e);
			},
			// 返回
			returns() {
				// #ifdef H5
				return history.back();
				// #endif
				// #ifndef H5
				return uni.navigateBack({
					delta: 1,
				})
				// #endif
			},
			// 获取详情
			combinationDetail() {
				var that = this;
				var data = that.id;
				getCombinationDetail(data).then((res) => {
					that.dataShow = 1;
					uni.setNavigationBarTitle({
						title: res.data.storeInfo.title.substring(0, 16)
					})
					that.imgUrls = res.data.storeInfo.images;
					that.storeInfo = res.data.storeInfo;
					that.storeInfo.description = that.storeInfo.description.replace(/<img/gi,
						'<img style="max-width:100%;height:auto;float:left;display:block" ');
					that.attribute.productSelect.num = res.data.storeInfo.num;
					that.pink = res.data.pink;
					that.pinkAll = res.data.pinkAll;
					that.reply = res.data.reply ? [res.data.reply] : [];
					that.replyCount = res.data.replyCount;
					that.itemNew = res.data.pink_ok_list;
					that.pink_ok_sum = res.data.pink_ok_sum;
					that.replyChance = res.data.replyChance;
					that.attribute.productAttr = res.data.productAttr;
					that.productValue = res.data.productValue;
					if (!this.storeInfo.wechat_code) {
						// #ifdef H5
						this.codeVal = window.location.origin +
							'/pages/activity/goods_combination_details/index?id=' + this.id +
							'&spid=' + this.$store.state.app.uid
						// #endif	
						// #ifdef APP-PLUS
						this.codeVal = HTTP_REQUEST_URL + '/pages/activity/goods_combination_details/index?id=' +
							this.id +
							'&spid=' + this.$store.state.app.uid
						// #endif	
					} else {
						that.$set(that, "PromotionCode", this.storeInfo.wechat_code);
					}
					that.routineContact = Number(res.data.routine_contact_type);
					for (let key in res.data.productValue) {
						let obj = res.data.productValue[key];
						that.skuArr.push(obj);
					}
					that.$set(that, "selectSku", that.skuArr[0]);
					var navList = [that.$t(`商品`), that.$t(`详情`)];
					if (res.data.replyCount) {
						navList.splice(1, 0, that.$t(`评价`));
					}
					that.$set(that, 'navList', navList);
					that.storeImage = that.storeInfo.image
					// #ifdef H5
					that.setShare();
					that.getImageBase64();
					// #endif
					// #ifdef APP-PLUS
					that.downloadFilestoreImage();
					// that.downloadFileAppCode();
					// #endif
					// #ifdef MP
					that.downloadFilestoreImage();
					// that.downloadFilePromotionCode();
					// #endif
					// that.setProductSelect();
					that.DefaultSelect();
					setTimeout(function() {
						that.infoScroll();
					}, 500);

				}).catch(function(err) {
					that.$util.Tips({
						title: err
					}, {
						tab: 3
					})
				})
			},
			// app获取二维码
			downloadFileAppCode() {
				let that = this;
				uni.downloadFile({
					url: that.setDomain(that.PromotionCode),
					success: function(res) {
						that.PromotionCode = res.tempFilePath;
					},
					fail: function() {
						return that.$util.Tips({
							title: ''
						});
						that.PromotionCode = '';
					},
				});
			},
			//#ifdef H5
			setShare: function() {
				this.$wechat.isWeixin() &&
					this.$wechat.wechatEvevt([
						"updateAppMessageShareData",
						"updateTimelineShareData",
						"onMenuShareAppMessage",
						"onMenuShareTimeline"
					], {
						desc: this.storeInfo.info,
						title: this.storeInfo.title,
						link: location.href,
						imgUrl: this.storeInfo.image
					}).then(res => {}).catch(err => {});
			},
			//#endif
			// setTime: function() { //到期时间戳
			// 	var that = this;
			// 	var endTimeList = that.pink;
			// 	that.pink.map(item => {
			// 		item.time = {
			// 			day: '00',
			// 			hou: '00',
			// 			min: '00',
			// 			sec: '00'
			// 		};
			// 	});
			// 	var countDownArr = [];
			// 	var timeer = setInterval(function() {
			// 		var newTime = new Date().getTime() / 1000;
			// 		for (var i in endTimeList) {
			// 			var endTime = endTimeList[i].stop_time;
			// 			var obj = [];
			// 			if (endTime - newTime > 0) {
			// 				var time = endTime - newTime;
			// 				var day = parseInt(time / (60 * 60 * 24));
			// 				var hou = parseInt(time % (60 * 60 * 24) / 3600);
			// 				var min = parseInt(time % (60 * 60 * 24) % 3600 / 60);
			// 				var sec = parseInt(time % (60 * 60 * 24) % 3600 % 60);
			// 				hou = parseInt(hou) + parseInt(day * 24);
			// 				obj = {
			// 					day: that.timeFormat(day),
			// 					hou: that.timeFormat(hou),
			// 					min: that.timeFormat(min),
			// 					sec: that.timeFormat(sec)
			// 				}
			// 			} else {
			// 				obj = {
			// 					day: '00',
			// 					hou: '00',
			// 					min: '00',
			// 					sec: '00'
			// 				}
			// 			}
			// 			endTimeList[i].time = obj;
			// 		}
			// 		that.pink = endTimeList
			// 	}, 1000);
			// 	that.timeer = timeer
			// },
			// timeFormat(param) { //小于10的格式化函数
			// 	return param < 10 ? '0' + param : param;
			// },
			/**
			 * 默认选中属性
			 * 
			 */
			DefaultSelect: function() {

				let self = this
				let productAttr = self.attribute.productAttr;
				let value = [];
				for (var key in this.productValue) {
					if (this.productValue[key].quota > 0) {
						value = this.attribute.productAttr.length ? key.split(",") : [];
						break;
					}
				}
				for (let i = 0; i < productAttr.length; i++) {
					this.$set(productAttr[i], "index", value[i]);
				}
				//sort();排序函数:数字-英文-汉字；
				let productSelect = self.productValue[value.join(",")];

				if (productSelect && productAttr.length) {
					self.$set(
						self.attribute.productSelect,
						"store_name",
						self.storeInfo.title
					);
					self.$set(self.attribute.productSelect, "image", productSelect.image);
					self.$set(self.attribute.productSelect, "price", productSelect.price);
					self.$set(self.attribute.productSelect, "stock", productSelect.stock);
					self.$set(self.attribute.productSelect, "unique", productSelect.unique);
					self.$set(self.attribute.productSelect, "quota", productSelect.quota);
					self.$set(self.attribute.productSelect, "quota_show", productSelect.quota_show);
					self.$set(self.attribute.productSelect, "product_stock", productSelect.product_stock);
					self.$set(self.attribute.productSelect, "cart_num", 1);
					self.$set(self, "attrValue", value.join(","));
					self.attrValue = value.join(",")
				} else if (!productSelect && productAttr.length) {
					self.$set(
						self.attribute.productSelect,
						"store_name",
						self.storeInfo.title
					);
					self.$set(self.attribute.productSelect, "image", self.storeInfo.image);
					self.$set(self.attribute.productSelect, "price", self.storeInfo.price);
					self.$set(self.attribute.productSelect, "quota", 0);
					self.$set(self.attribute.productSelect, "quota_show", 0);
					self.$set(self.attribute.productSelect, "product_stock", 0);
					self.$set(self.attribute.productSelect, "stock", 0);
					self.$set(self.attribute.productSelect, "unique", "");
					self.$set(self.attribute.productSelect, "cart_num", 0);
					self.$set(self, "attrValue", "");
					self.$set(self, "attrTxt", this.$t(`请选择`));
				} else if (!productSelect && !productAttr.length) {
					self.$set(
						self.attribute.productSelect,
						"store_name",
						self.storeInfo.title
					);
					self.$set(self.attribute.productSelect, "image", self.storeInfo.image);
					self.$set(self.attribute.productSelect, "price", self.storeInfo.price);
					self.$set(self.attribute.productSelect, "stock", self.storeInfo.stock);
					self.$set(self.attribute.productSelect, "quota", 0);
					self.$set(self.attribute.productSelect, "product_stock", 0);
					self.$set(
						self.attribute.productSelect,
						"unique",
						self.storeInfo.unique || ""
					);
					self.$set(self.attribute.productSelect, "cart_num", 1);
					self.$set(self, "attrValue", "");
					self.$set(self, "attrTxt", this.$t(`请选择`));
				}
			},

			infoScroll: function() {
				var that = this,
					topArr = [],
					heightArr = [];
				for (var i = 0; i < that.navList.length; i++) { //productList
					//获取元素所在位置
					var query = uni.createSelectorQuery().in(this);
					var idView = "#past" + i;
					if (!this.replyCount && i == 1) {
						idView = "#past" + 2;
					}
					query.select(idView).boundingClientRect();
					query.exec(function(res) {
						var top = res[0].top;
						var height = res[0].height;
						topArr.push(top);
						heightArr.push(height);
						that.topArr = topArr
						that.heightArr = heightArr
					});
				};
			},
			// 授权后回调
			onLoadFun: function(e) {
				this.userInfo = e
				this.combinationDetail();
			},
			selecAttr: function() {
				this.attribute.cartAttr = true
			},
			onMyEvent: function() {
				this.$set(this.attribute, 'cartAttr', false);
				this.$set(this, 'isOpen', false);
			},
			/**
			 * 购物车数量加和数量减
			 * 
			 */
			ChangeCartNum: function(changeValue) {
				//changeValue:是否 加|减
				//获取当前变动属性
				let productSelect = this.productValue[this.attrValue];
				if (this.cart_num) {
					productSelect.cart_num = this.cart_num;
					this.attribute.productSelect.cart_num = this.cart_num;
				}
				//如果没有属性,赋值给商品默认库存
				if (productSelect === undefined && !this.attribute.productAttr.length)
					productSelect = this.attribute.productSelect;
				//无属性值即库存为0；不存在加减；
				if (productSelect === undefined) return;
				let stock = productSelect.stock || 0;
				let quotaShow = productSelect.quota_show || 0;
				let quota = productSelect.quota || 0;
				let productStock = productSelect.product_stock || 0;
				let num = this.attribute.productSelect;
				let nums = this.storeInfo.num || 0;
				//设置默认数据
				if (productSelect.cart_num == undefined) productSelect.cart_num = 1;
				if (changeValue) {
					num.cart_num++;
					let arrMin = [];
					arrMin.push(nums);
					arrMin.push(quota);
					arrMin.push(productStock);
					let minN = Math.min.apply(null, arrMin);
					if (num.cart_num >= minN) {
						this.$set(this.attribute.productSelect, "cart_num", minN ? minN : 1);
						this.$set(this, "cart_num", minN ? minN : 1);
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
					this.$set(this, "cart_num", num.cart_num);
					this.$set(this.attribute.productSelect, "cart_num", num.cart_num);

				} else {
					num.cart_num--;
					if (num.cart_num < 1) {
						this.$set(this.attribute.productSelect, "cart_num", 1);
						this.$set(this, "cart_num", 1);
					}
					this.$set(this, "cart_num", num.cart_num);
					this.$set(this.attribute.productSelect, "cart_num", num.cart_num);
				}
			},
			attrVal(val) {
				this.attribute.productAttr[val.indexw].index = this.attribute.productAttr[val.indexw].attr_values[val
					.indexn];
			},
			/**
			 * 属性变动赋值
			 * 
			 */
			ChangeAttr: function(res) {
				this.$set(this, 'cart_num', 1);
				let productSelect = this.productValue[res];
				this.$set(this, "selectSku", productSelect);
				if (productSelect) {
					this.$set(this.attribute.productSelect, "image", productSelect.image);
					this.$set(this.attribute.productSelect, "price", productSelect.price);
					this.$set(this.attribute.productSelect, "stock", productSelect.stock);
					this.$set(this.attribute.productSelect, "unique", productSelect.unique);
					this.$set(this.attribute.productSelect, "cart_num", 1);
					this.$set(this.attribute.productSelect, "quota", productSelect.quota);
					this.$set(this.attribute.productSelect, "quota_show", productSelect.quota_show);
					this.$set(this, "attrValue", res);

					this.attrTxt = this.$t(`已选择`)
				} else {
					this.$set(this.attribute.productSelect, "image", this.storeInfo.image);
					this.$set(this.attribute.productSelect, "price", this.storeInfo.price);
					this.$set(this.attribute.productSelect, "stock", 0);
					this.$set(this.attribute.productSelect, "unique", "");
					this.$set(this.attribute.productSelect, "cart_num", 0);
					this.$set(this.attribute.productSelect, "quota", 0);
					this.$set(this.attribute.productSelect, "quota_show", 0);
					this.$set(this, "attrValue", "");
					this.attrTxt = this.$t(`已选择`)
				}
			},
			// 单独购买
			goProduct() {
				uni.navigateTo({
					url: '/pages/goods_details/index?id=' + this.storeInfo.product_id
				})
			},
			// 立即购买
			goCat() {
				var that = this;
				this.currentPage = false
				var productSelect = this.productValue[this.attrValue];
				//打开属性
				if (this.isOpen)
					this.attribute.cartAttr = true
				else
					this.attribute.cartAttr = !this.attribute.cartAttr
				//只有关闭属性弹窗时进行加入购物车
				if (this.attribute.cartAttr === true && this.isOpen == false) return this.isOpen = true
				//如果有属性,没有选择,提示用户选择
				if (this.attribute.productAttr.length && productSelect === undefined && this.isOpen == true) return that
					.$util.Tips({
						title: this.$t(`请选择`)
					});
				var data = {
					productId: that.storeInfo.product_id,
					secKillId: 0,
					bargainId: 0,
					combinationId: that.id,
					cartNum: that.cart_num,
					uniqueId: productSelect !== undefined ? productSelect.unique : '',
					is_new: 1,
				};
				postCartAdd(data).then(function(res) {
					that.isOpen = false
					uni.navigateTo({
						url: '/pages/goods/order_confirm/index?new=1&cartId=' + res.data.cartId
					});
				}).catch(function(res) {
					uni.showToast({
						title: res,
						icon: 'none'
					})
				})
			},
			/**
			 * 收藏商品
			 */
			setCollect: function() {
				var that = this;
				if (this.storeInfo.userCollect) {
					collectDel([this.storeInfo.product_id]).then(res => {
						that.storeInfo.userCollect = !that.storeInfo.userCollect
					})
				} else {
					collectAdd(this.storeInfo.product_id).then(res => {
						that.storeInfo.userCollect = !that.storeInfo.userCollect
					})
				}
			},
			open(data) {
				this.showMenuIcon = data;
			},
			/**
			 * 分享打开
			 * 
			 */
			listenerActionSheet: function() {
				this.currentPage = false
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
			//隐藏海报
			posterImageClose: function() {
				this.posterImageStatus = false
			},
			//替换安全域名
			setDomain: function(url) {
				url = url ? url.toString() : '';
				//本地调试打开,生产请注销
				if (url.indexOf("https://") > -1) return url;
				else return url.replace('http://', 'https://');
			},

			/**
			 * 获取产品分销二维码
			 * @param function successFn 下载完成回调
			 * 
			 */
			downloadFilePromotionCode: function(successFn) {
				let that = this;
				scombinationCode(that.id).then(res => {
					uni.downloadFile({
						url: that.setDomain(res.data.code),
						success: function(res) {
							that.$set(that, 'isDown', false);
							if (typeof successFn == 'function')
								successFn && successFn(res.tempFilePath);
							else
								that.$set(that, 'PromotionCode', res.tempFilePath);
						},
						fail: function() {
							that.$set(that, 'isDown', false);
							that.$set(that, 'PromotionCode', '');
						},
					});
				}).catch(err => {
					that.$set(that, 'isDown', false);
					that.$set(that, 'PromotionCode', '');
				});
			},

			// 小程序关闭分享弹窗；
			goFriend: function() {
				this.posters = false;
			},


			/*
			 * 保存到手机相册
			 */
			// #ifdef MP
			savePosterPath: function() {
				let that = this;
				uni.getSetting({
					success(res) {
						if (!res.authSetting['scope.writePhotosAlbum']) {
							uni.authorize({
								scope: 'scope.writePhotosAlbum',
								success() {
									uni.saveImageToPhotosAlbum({
										filePath: that.posterImage,
										success: function(res) {
											that.posterImageClose();
											that.$util.Tips({
												title: this.$t(`保存成功`),
												icon: 'success'
											});
										},
										fail: function(res) {
											that.$util.Tips({
												title: this.$t(`保存失败`)
											});
										}
									})
								}
							})
						} else {
							uni.saveImageToPhotosAlbum({
								filePath: that.posterImage,
								success: function(res) {
									that.posterImageClose();
									that.$util.Tips({
										title: this.$t(`保存成功`),
										icon: 'success'
									});
								},
								fail: function(res) {
									that.$util.Tips({
										title: this.$t(`保存失败`)
									});
								},
							})
						}
					}
				})
			},
			// #endif
			//#ifdef APP-PLUS
			savePosterPath() {
				let that = this
				uni.saveImageToPhotosAlbum({
					filePath: that.posterImage,
					success: function(res) {
						that.posterImageClose();
						that.$util.Tips({
							title: this.$t(`保存成功`),
							icon: 'success'
						});
					},
					fail: function(res) {
						that.$util.Tips({
							title: this.$t(`保存失败`)
						});
					}
				});
			},
			// #endif
			setShareInfoStatus: function() {
				let data = this.storeInfo;
				let href = location.href;
				if (this.$wechat.isWeixin()) {
					getUserInfo().then(res => {
						href =
							href.indexOf("?") === -1 ?
							href + "?spread=" + res.data.uid :
							href + "&spread=" + res.data.uid;

						let configAppMessage = {
							desc: data.store_info,
							title: data.store_name,
							link: href,
							imgUrl: data.image
						};
						this.$wechat.wechatEvevt(["updateAppMessageShareData", "updateTimelineShareData"],
							configAppMessage)
					});
				}
			},





			scroll: function(e) {
				var that = this,
					scrollY = e.detail.scrollTop;
				var opacity = scrollY / 200;
				opacity = opacity > 1 ? 1 : opacity;
				that.opacity = opacity
				that.scrollY = scrollY
				that.$set(that, "showMenuIcon", false);
				that.$set(that, 'currentPage', false);
				if (that.lock) {
					that.lock = false
					return;
				}
				for (var i = 0; i < that.topArr.length; i++) {
					if (scrollY < that.topArr[i] - (app.globalData.navHeight / 2) + that.heightArr[i]) {
						that.navActive = i
						break
					}
				}
			},
			tap: function(item, index) {
				var id = item.id;
				var index = index;
				var that = this;
				if (!this.replyCount && id == "past1") {
					id = "past2"
				}
				this.toView = id;
				this.navActive = index;
				this.lock = true;
				this.scrollTop = index > 0 ? that.topArr[index] - (app.globalData.navHeight / 2) : that.topArr[index]
			},
			//点击sku图片打开轮播图
			showImg(index) {
				this.$refs.cusPreviewImg.open(this.selectSku.suk);
			},
			//滑动轮播图选择商品
			changeSwitch(e) {
				let productSelect = this.skuArr[e];
				this.$set(this, "selectSku", productSelect);
				var skuList = productSelect.suk.split(",");
				this.$set(this.attribute.productAttr[0], "index", skuList[0]);
				if (skuList.length == 2) {
					this.$set(this.attribute.productAttr[0], "index", skuList[0]);
					this.$set(this.attribute.productAttr[1], "index", skuList[1]);
				} else if (skuList.length == 3) {
					this.$set(this.attribute.productAttr[0], "index", skuList[0]);
					this.$set(this.attribute.productAttr[1], "index", skuList[1]);
					this.$set(this.attribute.productAttr[2], "index", skuList[2]);
				} else if (skuList.length == 4) {
					this.$set(this.attribute.productAttr[0], "index", skuList[0]);
					this.$set(this.attribute.productAttr[1], "index", skuList[1]);
					this.$set(this.attribute.productAttr[2], "index", skuList[2]);
					this.$set(this.attribute.productAttr[3], "index", skuList[3]);
				}
				if (productSelect) {
					this.$set(this.attribute.productSelect, "image", productSelect.image);
					this.$set(this.attribute.productSelect, "price", productSelect.price);
					this.$set(this.attribute.productSelect, "stock", productSelect.stock);
					this.$set(this.attribute.productSelect, "unique", productSelect.unique);
					this.$set(this.attribute.productSelect, "vipPrice", productSelect.vipPrice);
					this.$set(this, "attrTxt", this.$t(`已选择`));
					this.$set(this, "attrValue", productSelect.suk);
				}
			},
		},
		//#ifdef MP
		onShareAppMessage() {
			return {
				title: this.storeInfo.title,
				path: '/pages/activity/goods_combination_details/index?id=' + this.id + '&spid=' + this.$store.state.app.uid,
				imageUrl: this.storeInfo.image
			};
		}
		//#endif

	}
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
		/* #ifdef MP */
		// padding-right: 45rpx;
		/* #endif */
	}

	.icon-xiangzuo {
		/* #ifdef H5 */
		top: 30rpx !important;
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
		content: "";
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
	}

	.icon-xiangzuo {
		/* color: #000;
		position: fixed;
		font-size: 40rpx;
		width: 100rpx;
		height: 56rpx;
		line-height: 54rpx;
		z-index: 1000;
		left: 33rpx; */
	}

	.product-con .wrapper {
		padding-bottom: 26rpx;
	}

	.product-con .wrapper .share .money .y-money {
		color: #82848f;
		margin-left: 13rpx;
		text-decoration: line-through;
		font-weight: normal;
	}

	.product-con .notice {
		width: 100%;
		height: 62rpx;
		margin-top: 20rpx;
		padding: 0 30rpx;
		box-sizing: border-box;
		background-color: rgba(255, 255, 255, 0.88);
	}

	.product-con .notice .num {
		font-size: 24rpx;
	}

	.product-con .notice .num .iconfont {
		font-size: 30rpx;
		vertical-align: -3rpx;
		margin-right: 20rpx;
	}

	.product-con .notice .num .line {
		color: #282828;
		margin-left: 15rpx;
	}

	.product-con .notice .swiper {
		height: 100%;
		width: 360rpx;
		line-height: 62rpx;
		overflow: hidden;
		margin-left: 14rpx;
	}

	.product-con .notice .swiper swiper {
		height: 100%;
		width: 100%;
		overflow: hidden;
		font-size: 24rpx;
		color: #282828;
	}

	.product-con .assemble {
		background-color: #fff;
	}

	.product-con .assemble .item {
		padding-right: 30rpx;
		margin-left: 30rpx;
		border-bottom: 1rpx solid #f0f0f0;
		height: 132rpx;
	}

	.product-con .assemble .item .pictxt {
		width: 295rpx;
	}

	.product-con .assemble .item .pictxt .text {
		width: 194rpx;
	}

	.product-con .assemble .item .pictxt .pictrue {
		width: 80rpx;
		height: 80rpx;
	}

	.product-con .assemble .item .pictxt .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
	}

	.product-con .assemble .item .right .lack {
		font-size: 24rpx;
		color: #333333;
	}

	.product-con .assemble .item .right .time {
		position: relative;
		left: -10rpx;
		font-size: 22rpx;
		color: #82848f;
		margin-top: 5rpx;
	}

	.product-con .assemble .item .right .spellBnt {
		font-size: 24rpx;
		color: #fff;
		width: 140rpx;
		height: 50rpx;
		border-radius: 50rpx;
		// background-image: linear-gradient(to right, #ff2358 0%, #ff0000 100%);
		text-align: center;
		line-height: 50rpx;
		background-color: var(--view-theme);
		margin-left: 30rpx;
	}

	.product-con .assemble .item .right .spellBnt .iconfont {
		font-size: 25rpx;
		margin-left: 5rpx;
	}

	.product-con .assemble .more {
		font-size: 24rpx;
		color: #282828;
		text-align: center;
		height: 90rpx;
		line-height: 90rpx;
	}

	.product-con .assemble .more .iconfont {
		margin-left: 13rpx;
		font-size: 25rpx;
	}

	.product-con .playWay {
		background-color: #fff;
		padding: 0 30rpx;
		margin-top: 20rpx;
		font-size: 28rpx;
		color: #282828;
	}

	.product-con .playWay .title {
		height: 86rpx;
		border-bottom: 1rpx solid #eee;
	}

	.product-con .playWay .title .iconfont {
		margin-left: 13rpx;
		font-size: 28rpx;
		color: #717171;
	}

	.product-con .playWay .way {
		min-height: 110rpx;
		font-size: 26rpx;
		color: #282828;
	}

	.product-con .playWay .way .iconfont {
		color: #cdcdcd;
		font-size: 40rpx;
		margin: 0 35rpx;
	}

	.product-con .playWay .way .item .num {
		font-size: 34rpx;
		margin-right: 6rpx;
		width: 17px;
		height: 28px;
		display: inline-block;
		vertical-align: middle;
	}

	.product-con .playWay .way .item .tip {
		font-size: 22rpx;
		color: #a5a5a5;
		margin-top: 7rpx;
	}

	.product-con .footer {
		padding: 0 20rpx 0 30rpx;
		position: fixed;
		bottom: 0;
		width: 100%;
		box-sizing: border-box;
		z-index: 277;
		border-top: 1rpx solid #f0f0f0;
		height: 100rpx;
		height: calc(100rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		height: calc(100rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		background-color: rgba(255, 255, 255, 0.85);
		backdrop-filter: blur(10px);
		transform: translate3d(0, 100%, 0);
		transition: all .3s cubic-bezier(.25, .5, .5, .9);
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

	.product-con .conter {
		display: block;
		padding-bottom: 100rpx;
	}

	.product-con .conter img {
		display: block;
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

	.product-con .footer .bnt .bnts.long-btn{
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
		background-color: var(--view-bntColor);
	}

	.product-con .footer .bnt .buy {
		border-radius: 0 50rpx 50rpx 0;
		background-color: var(--view-theme);
	}

	.setCollectBox {
		font-size: 18rpx;
		color: #666;
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
		opacity: .5;
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
			left: unset
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
		background: #B3B3B3;
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
