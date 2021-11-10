<template>
	<view :style="colorStyle">
		<view class='product-con'>
			<scroll-view :scroll-top="scrollTop" scroll-y='true' scroll-with-animation="true"
				:style="'height:'+height+'px;'" @scroll="scroll">
				<view id="past0">
					<productConSwiper :imgUrls='imgUrls'></productConSwiper>
					<view class='nav acea-row row-between-wrapper'>
						<view class="share acea-row row-between row-bottom">
							<view class="money font-color">
								<image src="/static/images/my-point.png" mode=""></image>
								<text class="num" v-text="storeInfo.price || 0"></text>积分
							</view>
							<view></view>
						</view>
					</view>
					<view class='wrapper'>
						<view class='introduce acea-row row-between'>
							<view class='infor'> {{storeInfo.title}}</view>
						</view>
						<view class='label acea-row row-middle'>

							<view class='stock'>原价：{{storeInfo.product_price}}</view>
							<view class='stock'>限量:
								{{ storeInfo.quota_show}}
							</view>
							<view class='stock'>已兑换：{{storeInfo.sales}}
							</view>
						</view>
					</view>
					<view class='attribute acea-row row-between-wrapper' @tap='selecAttr'
						v-if='attribute.productAttr.length'>
						<!-- <view class="df"><text class='atterTxt line1'>{{attr}}：{{attrValue}}</text></view>
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
							<view class="switchTxt">共{{ skuArr.length }}种规格可选</view>
						</view>
					</view>
				</view>
				<view class='product-intro' id="past2">
					<view class='title'>产品介绍</view>
					<view class='conter'>
						<view class="" v-html="storeInfo.description">
						</view>
					</view>
				</view>
			</scroll-view>
			<view class='footer acea-row row-between-wrapper'>
				<navigator hover-class="none" open-type="switchTab" class="item" url="/pages/index/index">
					<view class="iconfont icon-shouye6"></view>
					<view class="p_center">首页</view>
				</navigator>
				<view class="bnt acea-row"
					v-if="attribute.productSelect.quota > 0 && attribute.productSelect.product_stock>0">
					<view class="buy bnts" @tap="goCat">立即兑换</view>
				</view>
				<view class="bnt acea-row" v-else>
					<view class="bnts no-goods">无法兑换</view>
				</view>
			</view>
		</view>
		<product-window :attr='attribute' :limitNum='1' @myevent="onMyEvent" @ChangeAttr="ChangeAttr"
			@ChangeCartNum="ChangeCartNum" @attrVal="attrVal" @iptCartNum="iptCartNum" @getImg="showImg">
		</product-window>
		<cus-previewImg ref="cusPreviewImg" :list="skuArr" @changeSwitch="changeSwitch"
			@shareFriend="listenerActionSheet" />
		<!-- 分享按钮 -->
		<kefuIcon :ids='storeInfo.product_id' :routineContact='routineContact'></kefuIcon>
		<!-- 发送给朋友图片 -->
	</view>
</template>

<script>
	const app = getApp();
	import {
		mapGetters
	} from "vuex";
	import {
		getIntegralProductDetail
	} from '@/api/activity.js';
	import productConSwiper from '@/components/productConSwiper/index.vue'
	import productWindow from './component/productWindow.vue'
	import userEvaluation from '@/components/userEvaluation/index.vue'
	import kefuIcon from '@/components/kefuIcon';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import parser from "@/components/mp-html/mp-html";
	import countDown from '@/components/countDown';
	import {
		imageBase64
	} from "@/api/public";
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		silenceBindingSpread
	} from "@/utils";
	import {
		getUserInfo
	} from '@/api/user.js';
	// #ifdef APP-PLUS
	import {
		TOKENNAME,
		HTTP_REQUEST_URL
	} from '@/config/app.js';
	// #endif
	import colors from "@/mixins/color";
	import cusPreviewImg from "@/components/cus-previewImg/cus-previewImg.vue";
	export default {
		computed: mapGetters(['isLogin']),
		mixins: [colors],
		data() {
			return {
				dataShow: 0,
				id: 0,
				time: 0,
				countDownHour: "00",
				countDownMinute: "00",
				countDownSecond: "00",
				storeInfo: [],
				imgUrls: [],
				parameter: {
					'navbar': '1',
					'return': '1',
					'title': '抢购详情页',
					'color': false
				},
				attribute: {
					cartAttr: false,
					productAttr: [],
					productSelect: {}
				},
				productValue: [],
				isOpen: false,
				attr: '请选择',
				attrValue: '',
				status: 1,
				isAuto: false,
				isShowAuth: false,
				iShidden: false,
				limitNum: 1, //限制本属性产品的个数；
				iSplus: false,
				replyCount: 0, //总评论数量
				reply: [], //评论列表
				replyChance: 0,
				navH: "",
				navList: ['商品', '详情'],
				opacity: 0,
				scrollY: 0,
				topArr: [],
				toView: '',
				height: 0,
				heightArr: [],
				lock: false,
				scrollTop: 0,
				tagStyle: {
					img: 'width:100%;display:block;',
					table: 'width:100%',
					video: 'width:100%'
				},
				datatime: '',
				navActive: 0,
				meunHeight: 0,
				backH: '',
				posters: false,
				weixinStatus: false,
				posterImageStatus: false,
				storeImage: '', //海报产品图
				PromotionCode: '', //二维码图片
				posterImage: '', //海报路径
				actionSheetHidden: false,
				cart_num: 1,
				homeTop: 20,
				returnShow: true,
				H5ShareBox: false, //公众号分享图片
				routineContact: 0,
				skuArr: [],
				selectSku: {},
			}
		},
		components: {
			productConSwiper,
			'productWindow': productWindow,
			userEvaluation,
			kefuIcon,
			"jyf-parser": parser,
			countDown,
			cusPreviewImg,
			// #ifdef MP
			authorize
			// #endif
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.getIntegralProductDetail();
					}
				},
				deep: true
			}
		},
		onLoad(options) {

			let that = this
			let statusBarHeight = ''
			var pages = getCurrentPages();
			that.returnShow = pages.length === 1 ? false : true;
			//设置商品列表高度
			uni.getSystemInfo({
				success: function(res) {
					that.height = res.windowHeight
					statusBarHeight = res.statusBarHeight
					//res.windowHeight:获取整个窗口高度为px，*2为rpx；98为头部占据的高度；
				},
			});
			this.isLogin && silenceBindingSpread();
			// #ifndef APP-PLUS
			this.navH = app.globalData.navHeight
			// #endif
			// #ifdef APP-PLUS
			this.navH = 90
			// #endif
			// #ifdef MP
			let menuButtonInfo = uni.getMenuButtonBoundingClientRect()
			this.meunHeight = menuButtonInfo.height
			this.backH = (that.navH / 2) + (this.meunHeight / 2)

			//扫码携带参数处理
			if (options.scene) {
				let value = this.$util.getUrlParams(decodeURIComponent(options.scene));
				if (value.id) {
					this.id = value.id;
				} else {
					return this.$util.Tips({
						title: '缺少参数无法查看商品'
					}, {
						tab: 3,
						url: 1
					});
				}
				//记录推广人uid
				if (value.pid) app.globalData.spid = value.pid;
				if (value.time) this.datatime = value.time
			}
			// #endif

			if (options.id) {
				this.id = options.id
				this.datatime = Number(options.time)
				this.status = options.status
			}
			if (this.isLogin) {
				this.getIntegralProductDetail();
			} else {
				toLogin();
			}
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
		},
		methods: {
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
							title: '分享成功',
							icon: 'success'
						})
						that.posters = false;
					},
					fail: function(err) {
						uni.showToast({
							title: '分享失败',
							icon: 'none',
							duration: 2000
						})
						that.posters = false;
					}
				});
			},
			// #endif
			/**
			 * 购物车手动填写
			 * 
			 */
			iptCartNum: function(e) {
				this.$set(this.attribute.productSelect, 'cart_num', e);
				this.$set(this, "cart_num", e);
			},
			// 后退
			returns: function() {
				uni.navigateBack()
			},
			onLoadFun: function(data) {
				if (this.isAuto) {
					this.isAuto = false;
					this.isShowAuth = false;
					this.getIntegralProductDetail();
				}
			},
			getIntegralProductDetail: function() {
				let that = this;
				getIntegralProductDetail(that.id).then(res => {
					this.dataShow = 1;
					let title = res.data.storeInfo.title;
					this.storeInfo = res.data.storeInfo;
					this.imgUrls = res.data.storeInfo.images;
					this.storeInfo.description = this.storeInfo.description.replace(/<img/gi,
						'<img style="max-width:100%;height:auto;float:left;display:block" ');
					this.attribute.productAttr = res.data.productAttr;
					this.productValue = res.data.productValue;
					this.attribute.productSelect.num = res.data.storeInfo.num;
					this.replyCount = res.data.replyCount;
					this.reply = res.data.reply ? [res.data.reply] : [];
					this.replyChance = res.data.replyChance;
					that.routineContact = res.data.routine_contact_type;
					for (let key in res.data.productValue) {
						let obj = res.data.productValue[key];
						that.skuArr.push(obj);
					}
					this.$set(this, "selectSku", that.skuArr[0]);
					uni.setNavigationBarTitle({
						title: title.substring(0, 7) + '...'
					});
					// #ifdef H5
					this.PromotionCode = res.data.storeInfo.code_base
					that.storeImage = that.storeInfo.image
					// #endif
					that.DefaultSelect();
					app.globalData.openPages = '/pages/activity/goods_seckill_details/index?id=' + that.id +
						'&time=' + that.time +
						'&status=' + that.status + '&scene=' + that.storeInfo.uid;
					// wxParse.wxParse('description', 'html', that.data.storeInfo.description || '', that, 0);
					// wxh.time(that.data.time, that);
				}).catch(err => {
					that.$util.Tips({
						title: err
					}, {
						tab: 3
					})
				});
			},
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
				let productSelect = this.productValue[value.join(",")];
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
					self.$set(self, "attrTxt", "请选择");
				} else if (!productSelect && !productAttr.length) {
					self.$set(
						self.attribute.productSelect,
						"store_name",
						self.storeInfo.title
					);
					self.$set(self.attribute.productSelect, "image", self.storeInfo.image);
					self.$set(self.attribute.productSelect, "price", self.storeInfo.price);
					self.$set(self.attribute.productSelect, "stock", self.storeInfo.stock);
					self.$set(self.attribute.productSelect, "quota", self.storeInfo.quota);
					self.$set(self.attribute.productSelect, "product_stock", self.storeInfo.product_stock);
					self.$set(
						self.attribute.productSelect,
						"unique",
						self.storeInfo.unique || ""
					);
					self.$set(self.attribute.productSelect, "cart_num", 1);
					self.$set(self.attribute.productSelect, "quota", productSelect.quota);
					self.$set(self.attribute.productSelect, "product_stock", productSelect.product_stock);
					self.$set(self, "attrValue", "");
					self.$set(self, "attrTxt", "请选择");
				}
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
					if (num.cart_num < this.attribute.productSelect.quota) {
						num.cart_num++;
						this.$set(this.attribute.productSelect, "cart_num", num.cart_num);
						this.$set(this, "cart_num", num.cart_num);
						this.$set(this.attribute.productSelect, "cart_num", num.cart_num);
					}

				} else {
					if (num.cart_num == 1) return
					num.cart_num--;
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

					this.attrTxt = "已选择"
				} else {
					this.$set(this.attribute.productSelect, "image", this.storeInfo.image);
					this.$set(this.attribute.productSelect, "price", this.storeInfo.price);
					this.$set(this.attribute.productSelect, "stock", 0);
					this.$set(this.attribute.productSelect, "unique", "");
					this.$set(this.attribute.productSelect, "cart_num", 0);
					this.$set(this.attribute.productSelect, "quota", 0);
					this.$set(this.attribute.productSelect, "quota_show", 0);
					this.$set(this, "attrValue", "");
					this.attrTxt = "已选择"

				}
			},
			scroll: function(e) {
				var that = this,
					scrollY = e.detail.scrollTop;
				var opacity = scrollY / 200;
				opacity = opacity > 1 ? 1 : opacity;
				that.opacity = opacity
				that.scrollY = scrollY
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
				// if (!this.data.good_list.length && id == "past2") {
				//   id = "past3"
				// }
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
				console.log(this.skuArr[e])
				let productSelect = this.skuArr[e];
				this.$set(this, "selectSku", productSelect);
				var skuList = productSelect.suk.split(",");
				console.log(this.attribute.productAttr)
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
					this.$set(this, "attrTxt", "已选择");
					this.$set(this, "attrValue", productSelect.suk);
				}
			},
			/*
			 *  下订单
			 */
			goCat: function() {
				var that = this;
				var productSelect = this.productValue[this.attrValue];
				//打开属性
				if (this.isOpen)
					this.attribute.cartAttr = true
				else
					this.attribute.cartAttr = !this.attribute.cartAttr
				//只有关闭属性弹窗时进行加入购物车
				if (this.attribute.cartAttr === true && this.isOpen == false) return this.isOpen = true
				//如果有属性,没有选择,提示用户选择
				if (this.attribute.productAttr.length && productSelect === undefined && this.isOpen == true) return app
					.$util.Tips({
						title: '请选择属性'
					});
				if (this.cart_num <= 0) {
					return app.$util.Tips({
						title: '请选择数量'
					});
				}
				this.isOpen = false
				uni.navigateTo({
					url: `/pages/points_mall/integral_order?unique=${productSelect.unique}&num=${this.cart_num || 1}`
				});
			},
		}
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
		padding-right: 95rpx;
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
		background-image: linear-gradient(to right, #ff3366 0%, #ff6533 100%);
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

	.product-con .nav {
		width: 100%;
		height: 100rpx;
		padding: 0 30rpx;
		box-sizing: border-box;
		background-color: #fff;

	}

	.product-con .nav .money {
		font-size: 28rpx;
		color: #E93323;
		font-weight: bold;

		image {
			width: 34rpx;
			height: 34rpx;
		}
	}

	.product-con .nav .money .num {
		font-size: 48rpx;
		padding-left: 16rpx;
	}

	.product-con .nav .money .y-money {
		font-size: 26rpx;
		margin-left: 10rpx;
		text-decoration: line-through;
	}

	.product-con .nav .timeItem {
		font-size: 20rpx;
		color: #fff;
		text-align: center;
	}

	.product-con .nav .timeItem .timeCon {
		margin-top: 10rpx;
	}

	.product-con .nav .timeItem .timeCon .num {
		padding: 0 7rpx;
		font-size: 22rpx;
		color: #ff3d3d;
		background-color: #fff;
		border-radius: 2rpx;
	}

	.product-con .nav .timeState {
		font-size: 28RPX;
		color: #FFF;
	}

	.product-con .nav .iconfont {
		color: #fff;
		font-size: 30rpx;
		margin-left: 20rpx;
	}

	.product-con .wrapper {
		padding: 0 32rpx 32rpx 32rpx;
		width: 100%;
		box-sizing: border-box;
	}

	.product-con .wrapper .introduce {
		margin: 0;
	}

	.product-con .wrapper .introduce .infor {
		// width: 570rpx;
	}

	.product-con .wrapper .introduce .iconfont {
		font-size: 37rpx;
		color: #515151;
	}

	.product-con .wrapper .label {
		display: flex;
		justify-content: space-between;
		margin: 18rpx 0 0 0;
		font-size: 24rpx;
		color: #82848f;
	}

	.product-con .wrapper .label .stock {}

	.product-con .footer {
		padding: 0 20rpx 0 30rpx;
		position: fixed;
		bottom: 0;
		width: 100%;
		box-sizing: border-box;
		background-color: #fff;
		z-index: 277;
		border-top: 1rpx solid #f0f0f0;
		height: 100rpx;
		display: flex;
		align-items: center;
		flex-wrap: nowrap;
		height: calc(100rpx+ constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		height: calc(100rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
	}

	.product-con .footer .item {
		width: 100rpx;
		font-size: 18rpx;
		color: #666;
	}

	.product-con .footer .item .iconfont {
		text-align: center;
		font-size: 40rpx;
	}

	.product-con .footer .item .iconfont.icon-shoucang1 {
		color: #f00;
	}

	.product-con .footer .item .iconfont.icon-gouwuche1 {
		font-size: 40rpx;
		position: relative;
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
		width: 100%;
		height: 76rpx;
	}

	.product-con .footer .bnt .bnts {
		width: 100%;
		text-align: center;
		line-height: 76rpx;
		color: #fff;
		font-size: 28rpx;
	}

	.product-con .footer .bnt .joinCart {
		border-radius: 50rpx 0 0 50rpx;
		background-image: linear-gradient(to right, #fea10f 0%, #fa8013 100%);
	}

	.product-con .footer .bnt .buy {
		border-radius: 50rpx;
		// background-image: linear-gradient(to right, #fa6514 0%, #e93323 100%);
		background-color: var(--view-theme);
	}

	.product-con .footer .bnt .no-goods {
		border-radius: 50rpx;
		background-color: #CCCCCC;
	}

	.product-con .conter {
		display: block;
		padding-bottom: 100rpx;
	}

	.product-con .conter img {
		display: block;
	}

	.bg-color-hui {
		background: #bbbbbb !important;
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
		color: #fff;
		position: fixed;
		font-size: 33rpx;
		width: 56rpx;
		height: 56rpx;
		z-index: 99;
		left: 33rpx;
		background: rgba(190, 190, 190, 0.5);
		border-radius: 50%;

		&.on {
			background: unset;
			color: #333;
		}
	}

	.home-nav .line {
		width: 1rpx;
		height: 24rpx;
		background: rgba(255, 255, 255, 0.25);
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

	.df {
		display: flex;
		align-items: center;
		flex-wrap: nowrap;
		width: 100%;
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
		padding-right: 0 24rpx 0;
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
