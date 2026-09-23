<template>
	<div class="group-con" :style="colorStyle">
		<div class="header acea-row row-between-wrapper" v-if="storeCombination">
			<div class="pictrue"><img :src="storeCombination.image" /></div>
			<div class="text">
				<div class="line1" v-text="storeCombination.title"></div>
				<div class="money">
					{{$t(`￥`)}}
					<span class="num" v-text="storeCombination.price"></span>
					<span class="team cart-color">{{storeCombination.people + $t(`人拼`)}}</span>
				</div>
			</div>
			<div v-if="pinkBool === -1" class="iconfont icon-pintuanshibai"></div>
			<div v-else-if="pinkBool === 1" class="iconfont icon-pintuanchenggong font-num"></div>
		</div>
		<div class="wrapper">
			<div class="title acea-row row-center-wrapper" v-if="pinkBool === 0">
				<div class="line"></div>
				<div class="name acea-row row-center-wrapper">
					{{$t(`剩余`)}}
					<CountDown :is-day="false" :tip-text="' '" :day-text="' '" hourText=" : " minute-text=" : "
						second-text="" :datatime="pinkT.stop_time"></CountDown>
					{{$t(`结束`)}}
				</div>
				<div class="line"></div>
			</div>
			<div class="tips font-num" v-if="pinkBool === 1">{{$t(`恭喜您拼团成功`)}}</div>
			<div class="tips" v-else-if="pinkBool === -1">{{$t(`还差`)}}{{ count }}{{$t(`人，拼团失败`)}}</div>
			<div class="tips font-num" v-else-if="pinkBool === 0">{{$t(`拼团中，还差`)}}{{ count }}{{$t(`人拼团成功`)}}</div>
			<div class="list acea-row row-middle"
				:class="[pinkBool === 1 || pinkBool === -1 ? 'result' : '', iShidden ? 'on' : '']">
				<div class="pictrue"><img :src="pinkT.avatar" />
					<view class="dumpling">{{$t(`团长`)}}</view>
				</div>

				<div class="acea-row row-middle" v-if="pinkAll.length > 0">
					<div class="pictrue" v-for="(item, index) in pinkAll" :key="index"><img :src="item.avatar" /></div>
				</div>
				<div class="pictrue" v-for="index in count" :key="index">
					<image class="img-none" src="../static/vacancy.png"> </image>
				</div>
			</div>
			<div v-if="(pinkBool === 1 || pinkBool === -1) && count > 9" class="lookAll acea-row row-center-wrapper"
				@click="lookAll">
				{{ iShidden ? $t(`收起`) : $t(`查看更多`) }}
				<span class="iconfont" :class="iShidden ? 'icon-xiangshang' : 'icon-xiangxia'"></span>
			</div>
			<div v-if="userBool === 1 && isOk == 0 && pinkBool === 0">
				<div class="teamBnt bg-color-red" @click="listenerActionSheet">{{$t(`邀请好友参团`)}}</div>
			</div>
			<div class="teamBnt bg-color-red" v-else-if="userBool === 0 && pinkBool === 0 && count > 0" @click="pay">
				{{$t(`我要参团`)}}
			</div>
			<div class="teamBnt bg-color-red" v-if="pinkBool === 1 || pinkBool === -1"
				@click="goDetail(storeCombination.id)">{{$t(`再次开团`)}}</div>
			<div class="cancel" @click="getCombinationRemove"
				v-if="pinkBool === 0 && userBool === 1 && pinkT.uid == userInfo.uid">
				<span class="iconfont icon-guanbi3"></span>
				{{$t(`取消开团`)}}
			</div>
			<div class="lookOrder" v-if="pinkBool === 1 && orderPid === 0" @click="goOrder">
				{{$t(`查看订单信息`)}}
				<span class="iconfont icon-xiangyou"></span>
			</div>
		</div>
		<div class="group-recommend">
			<div class="title acea-row row-between-wrapper">
				<div>{{$t(`大家都在拼`)}}</div>
				<div class="more" @click="goList">
					{{$t(`更多拼团`)}}
					<span class="iconfont icon-jiantou"></span>
				</div>
			</div>
			<div class="list acea-row row-middle">
				<div class="item" v-for="(item, index) in storeCombinationHost" :key="index" @click="goDetail(item.id)">
					<div class="pictrue">
						<img :src="item.image" />
						<div class="team" v-text="item.people + $t(`人团`)"></div>
					</div>
					<div class="name line1" v-text="item.title"></div>
					<div class="money font-color-red" v-text="$t(`￥`) + item.price"></div>
				</div>
			</div>
		</div>
		<product-window :attr="attr" :limitNum="storeCombination ? storeCombination.once_num : 0" :iSbnt="1" @myevent="onMyEvent" @ChangeAttr="ChangeAttr"
			@ChangeCartNum="ChangeCartNum" @iptCartNum="iptCartNum" @attrVal="attrVal" @goCat="goPay"></product-window>
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
			<!-- #ifndef APP-PLUS -->
			<button class="item" hover-class='none' @tap="goPoster">
				<view class="iconfont icon-haibao"></view>
				<view class="">{{$t(`生成海报`)}}</view>
			</button>
			<!-- #endif -->
		</view>
		<view class="mask" v-if="posters" @click="listenerActionClose"></view>
		<!-- 发送给朋友图片 -->
		<view class="share-box" v-if="H5ShareBox">
			<image :src="imgHost + '/statics/images/share-info.png'" @click="H5ShareBox = false"></image>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<!-- <Product-window v-on:changeFun="changeFun" :attr="attr" :limitNum='1' :iSbnt='1'></Product-window> -->
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</div>
</template>
<script setup>
	import CountDown from '@/components/countDown';
	import ProductWindow from '@/components/productWindow';
	import util from '../../../utils/util.js';
	import Cache from '@/utils/cache.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import { useAppStore } from '@/store/modules/app';
	import {
		getCombinationPink as getCombinationPinkApi,
		postCombinationRemove
	} from '@/api/activity';
	import {
		postCartAdd
	} from '@/api/store';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import home from '@/components/home';
	// #ifdef APP-PLUS
	import {
		TOKENNAME
	} from '@/config/app.js';
	// #endif
	const app = getApp();
	import { useColorTheme } from '@/composables/useColorTheme';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	// #ifdef H5
	import wechat from '@/libs/wechat.js';
	// #endif
	import { ref, computed, watch, onMounted } from 'vue';
	import { storeToRefs } from 'pinia';
	import { onLoad, onShareAppMessage } from '@dcloudio/uni-app';
	import i18n from '@/utils/lang.js';

	const { colorStyle } = useColorTheme();

	const appStore = useAppStore();
	const { isLogin } = storeToRefs(appStore);
	const userData = computed(() => appStore.userInfo || {});

	// ---------- data ----------
	const imgHost = HTTP_REQUEST_URL;
	const currentPinkOrder = ref(''); //当前拼团订单
	const isOk = ref(0); //判断拼团是否完成
	const pinkBool = ref(0); //判断拼团是否成功|0=失败,1=成功
	const userBool = ref(0); //判断当前用户是否在团内|0=未在,1=在
	const pinkAll = ref([]); //团员
	const pinkT = ref([]); //团长信息
	const storeCombination = ref(undefined); //拼团产品
	const storeCombinationHost = ref([]); //拼团推荐
	const pinkId = ref(0);
	const count = ref(0); //拼团剩余人数
	const iShidden = ref(false);
	const isOpen = ref(false); //是否打开属性组件
	const attr = ref({
		cartAttr: false,
		productSelect: {
			image: '',
			store_name: '',
			price: '',
			quota: 0,
			unique: '',
			cart_num: 1,
			quota_show: 0,
			product_stock: 0,
			num: 0
		},
		productAttr: []
	});
	const cart_num = ref('');
	const userInfo = ref({});
	const posters = ref(false);
	const weixinStatus = ref(false);
	const H5ShareBox = ref(false); //公众号分享图片
	const isAuto = ref(false); //没有授权的不会自动授权
	const isShowAuth = ref(false); //是否隐藏授权
	const attrTxt = ref(i18n.t(`请选择`)); //属性页面提示
	const attrValue = ref(''); //已选属性,
	const orderPid = ref(0);
	// 原 Vue2 代码未在 data 中声明、直接挂到 this 上的属性
	const productValue = ref({});

	// ---------- watch ----------
	watch(isLogin, {
		handler: function(newV, oldV) {
			if (newV) {
				getCombinationPink();
			} else {
				toLogin();
			}
		},
		deep: true
	});
	watch(userData, {
		handler: function(newV, oldV) {
			if (newV) {
				userInfo.value = newV;
			}
		},
		deep: true
	});

	// ---------- methods ----------
	// app分享
	// #ifdef APP-PLUS
	function appShare(scene) {
		let routes = getCurrentPages(); // 获取当前打开过的页面路由数组
		let curRoute = routes[routes.length - 1].$page.fullPath // 获取当前页面路由，也就是最后一个打开的页面路由
		uni.share({
			provider: "weixin",
			scene: scene,
			type: 0,
			href: `${HTTP_REQUEST_URL}${curRoute}`,
			title: i18n.t(`您的好友`) + userInfo.value.nickname + i18n.t(`邀请您参团`) + storeCombination.value
				.title,
			imageUrl: storeCombination.value.small_image,
			success: function(res) {
				uni.showToast({
					title: i18n.t(`分享成功`),
					icon: 'success'
				})
				posters.value = false;
			},
			fail: function(err) {
				uni.showToast({
					title: i18n.t(`分享失败`),
					icon: 'none',
					duration: 2000
				})
				posters.value = false;
			}
		});
	}
	// #endif
	// 授权关闭
	function authColse(e) {
		isShowAuth.value = e;
	}
	// 授权后回调
	function onLoadFun(e) {
		userInfo.value = e;
		getCombinationPink();
	}
	/**
	 * 分享打开
	 *
	 */
	function listenerActionSheet() {
		if (isLogin.value == false) {
			toLogin();
		} else {
			// #ifdef H5
			if (wechat.isWeixin() === true) {
				weixinStatus.value = true;
			}
			// #endif
			posters.value = true;

		}
	}
	// 分享关闭
	function listenerActionClose() {
		posters.value = false;
	}
	// 小程序关闭分享弹窗；
	function goFriend() {
		posters.value = false;
	}
	/**
	 * 购物车手动填写
	 *
	 */
	function iptCartNum(e) {
		attr.value.productSelect['cart_num'] = e;
		cart_num.value = e;
	}
	function attrVal(val) {
		attr.value.productAttr[val.indexw].index = attr.value.productAttr[val.indexw].attr_values[val.indexn];
	}
	function onMyEvent() {
		attr.value['cartAttr'] = false;
		isOpen.value = false;
	}
	//将父级向子集多次传送的函数合二为一；
	// changeFun: function(opt) {
	// 	if (typeof opt !== "object") opt = {};
	// 	let action = opt.action || "";
	// 	let value = opt.value === undefined ? "" : opt.value;
	// 	this[action] && this[action](value);
	// },
	// changeattr: function(res) {
	// 	var that = this;
	// 	that.attr.cartAttr = res;
	// },
	//选择属性；
	function ChangeAttr(res) {
		cart_num.value = 1;
		let productSelect = productValue.value[res];
		if (productSelect) {
			attr.value.productSelect['image'] = productSelect.image;
			attr.value.productSelect['price'] = productSelect.price;
			attr.value.productSelect['quota'] = productSelect.quota;
			attr.value.productSelect['unique'] = productSelect.unique;
			attr.value.productSelect['cart_num'] = 1;
			attr.value.productSelect['product_stock'] = productSelect.product_stock;
			attr.value.productSelect['quota_show'] = productSelect.quota_show;
			attrValue.value = res;
			attrTxt.value = i18n.t(`已选择`);
		} else {
			attr.value.productSelect['image'] = storeCombination.value.image;
			attr.value.productSelect['price'] = storeCombination.value.price;
			attr.value.productSelect['quota'] = 0;
			attr.value.productSelect['unique'] = '';
			attr.value.productSelect['cart_num'] = 0;
			attr.value.productSelect['quota_show'] = 0;
			attr.value.productSelect['product_stock'] = 0;
			attrValue.value = '';
			attrTxt.value = i18n.t(`请选择`);
		}
	}
	function ChangeCartNum(res) {
		//changeValue:是否 加|减
		//获取当前变动属性
		let productSelect = productValue.value[attrValue.value];
		if (cart_num.value) {
			productSelect.cart_num = cart_num.value;
			attr.value.productSelect.cart_num = cart_num.value;
		}
		//如果没有属性,赋值给商品默认库存
		if (productSelect === undefined && !attr.value.productAttr.length) productSelect = attr.value
			.productSelect;
		if (productSelect === undefined) return;
		let stock = productSelect.stock || 0;
		let quotaShow = productSelect.quota_show || 0;
		let quota = productSelect.quota || 0;
		let productStock = productSelect.product_stock || 0;
		let num = attr.value.productSelect;
		let nums = storeCombination.value.num || 0;
		//设置默认数据
		if (productSelect.cart_num == undefined) productSelect.cart_num = 1;
		if (res) {
			num.cart_num++;
			let arrMin = [];
			arrMin.push(nums);
			arrMin.push(quota);
			arrMin.push(productStock);
			let minN = Math.min.apply(null, arrMin);
			if (num.cart_num >= minN) {
				attr.value.productSelect['cart_num'] = minN ? minN : 1;
				cart_num.value = minN ? minN : 1;
			}
			// if(quotaShow >= productStock){
			// 	 if (num.cart_num > productStock) {
			// 	 	this.attr.productSelect["cart_num"] = productStock;
			// 	 	this["cart_num"] = productStock;
			// 	 }
			// }else{
			// 	if (num.cart_num > quotaShow) {
			// 		this.attr.productSelect["cart_num"] = quotaShow;
			// 		this["cart_num"] = quotaShow;
			// 	}
			// }
			cart_num.value = num.cart_num;
			attr.value.productSelect['cart_num'] = num.cart_num;
		} else {
			num.cart_num--;
			if (num.cart_num < 1) {
				attr.value.productSelect['cart_num'] = 1;
				cart_num.value = 1;
			}
			cart_num.value = num.cart_num;
			attr.value.productSelect['cart_num'] = num.cart_num;
		}
		// if (res) {
		// 	num.cart_num++;
		// 	if (num.cart_num > quota) {
		// 		this.attr.productSelect["cart_num"] = quota;
		// 		this["cart_num"] = quota;
		// 	}
		// } else {
		// 	num.cart_num--;
		// 	if (num.cart_num < 1) {
		// 		this.attr.productSelect["cart_num"] = 1;
		// 		this["cart_num"] = 1;
		// 	}
		// }
	}
	//默认选中属性；
	function DefaultSelect() {
		let productAttr = attr.value.productAttr,
			value = [];
		for (var key in productValue.value) {
			if (productValue.value[key].quota > 0) {
				value = attr.value.productAttr.length ? key.split(',') : [];
				break;
			}
		}
		for (let i = 0; i < productAttr.length; i++) {
			productAttr[i]['index'] = value[i];
		}
		//sort();排序函数:数字-英文-汉字；
		let productSelect = productValue.value[value.join(',')];
		if (productSelect && productAttr.length) {
			attr.value.productSelect['store_name'] = storeCombination.value.title;
			attr.value.productSelect['image'] = productSelect.image;
			attr.value.productSelect['price'] = productSelect.price;
			attr.value.productSelect['quota'] = productSelect.quota;
			attr.value.productSelect['unique'] = productSelect.unique;
			attr.value.productSelect['cart_num'] = 1;
			attr.value.productSelect['product_stock'] = productSelect.product_stock;
			attr.value.productSelect['quota_show'] = productSelect.quota_show;
			attrValue.value = value.join(',');
			attrTxt.value = i18n.t(`已选择`);
		} else if (!productSelect && productAttr.length) {
			attr.value.productSelect['store_name'] = storeCombination.value.title;
			attr.value.productSelect['image'] = storeCombination.value.image;
			attr.value.productSelect['price'] = storeCombination.value.price;
			attr.value.productSelect['quota'] = 0;
			attr.value.productSelect['unique'] = '';
			attr.value.productSelect['cart_num'] = 0;
			attr.value.productSelect['product_stock'] = 0;
			attr.value.productSelect['quota_show'] = 0;
			attrValue.value = '';
			attrTxt.value = i18n.t(`请选择`);
		} else if (!productSelect && !productAttr.length) {
			attr.value.productSelect['store_name'] = storeCombination.value.title;
			attr.value.productSelect['image'] = storeCombination.value.image;
			attr.value.productSelect['price'] = storeCombination.value.price;
			attr.value.productSelect['quota'] = 0;
			attr.value.productSelect['unique'] = storeCombination.value.unique || '';
			attr.value.productSelect['cart_num'] = 1;
			attr.value.productSelect['quota_show'] = 0;
			attr.value.productSelect['product_stock'] = 0;
			attrValue.value = '';
			attrTxt.value = i18n.t(`请选择`);
		}
	}
	function setProductSelect() {
		var attrTmp = attr.value;
		attrTmp.productSelect.image = storeCombination.value.image;
		attrTmp.productSelect.store_name = storeCombination.value.title;
		attrTmp.productSelect.price = storeCombination.value.price;
		attrTmp.productSelect.quota = 0;
		attrTmp.productSelect.quota_show = 0;
		attrTmp.productSelect.product_stock = 0;
		attrTmp.cartAttr = false;
		attr.value = attrTmp;
	}
	function pay() {
		attr.value.cartAttr = true;
		isOpen.value = true;
	}
	function goPay() {
		var data = {};
		// that.attr.cartAttr = res;
		data.productId = storeCombination.value.product_id;
		data.cartNum = attr.value.productSelect.cart_num;
		data.uniqueId = attr.value.productSelect.unique;
		data.combinationId = storeCombination.value.id;
		data.new = 1;
		postCartAdd(data)
			.then(res => {
				uni.navigateTo({
					url: '/pages/center/order/create/index?new=1&cartId=' + res.data.cartId +
						'&pinkId=' + pinkId.value
				});
			})
			.catch(res => {
				util.Tips({
					title: res
				});
			});
	}
	function goPoster() {
		posters.value = false;
		uni.navigateTo({
			url: '/pages/activity/poster-poster/index?type=2&id=' + pinkId.value
		});
	}
	function goOrder() {
		uni.navigateTo({
			url: '/pages/goods/order_details/index?order_id=' + currentPinkOrder.value
		});
	}
	//拼团列表
	function goList() {
		uni.navigateTo({
			url: '/pages/activity/goods_combination/index'
		});
	}
	//拼团详情
	function goDetail(id) {
		pinkId.value = id;
		// this.getCombinationPink();
		uni.navigateTo({
			url: '/pages/activity/goods_combination_details/index?id=' + id
		});
		// this.$router.push({
		// 	path: "/activity/group_detail/" + id
		// });
	}
	//拼团信息
	function getCombinationPink() {
		getCombinationPinkApi(pinkId.value)
			.then(res => {
				storeCombinationHost.value = res.data.store_combination_host;
				res.data.pinkT.stop_time = parseInt(res.data.pinkT.stop_time);
				storeCombination.value = res.data.store_combination;
				attr.value.productSelect['num'] = res.data.store_combination.num;
				pinkT.value = res.data.pinkT;
				pinkAll.value = res.data.pinkAll;
				count.value = res.data.count;
				userBool.value = res.data.userBool;
				pinkBool.value = res.data.pinkBool;
				isOk.value = res.data.is_ok;
				currentPinkOrder.value = res.data.current_pink_order;
				userInfo.value = res.data.userInfo;
				orderPid.value = res.data.order_pid;
				attr.value.productAttr = res.data.store_combination.productAttr;
				productValue.value = res.data.store_combination.productValue;
				//#ifdef H5
				setOpenShare();
				//#endif
				setProductSelect();
				if (attr.value.productAttr != 0) DefaultSelect();
				if (res.data.is_ok == 1 && res.data.userBool == 0) {
					return util.Tips({
						title: i18n.t(`你不是该团的成员`),
					}, () => {
						uni.navigateTo({
							url: '/pages/activity/goods_combination/index'
						})
					});
				}
			})
			.catch(err => {
				return util.Tips({
					title: err,
				}, () => {
					uni.navigateBack()
					// uni.switchTab({
					// 	 url: '/pages/index/index'
					// })
				});
			});
	}
	//#ifdef H5
	function setOpenShare() {
		let configTimeline = {
			title: i18n.t(`您的好友`) + userInfo.value.nickname + i18n.t(`邀请您参团`) + storeCombination.value.title,
			desc: storeCombination.value.title,
			link: window.location.protocol + '//' + window.location.host +
				'/pages/activity/goods_combination_status/index?id=' + pinkId.value + '&bargain=' +
				userInfo.value.uid +
				'&spid=' +
				userInfo.value.uid,
			imgUrl: storeCombination.value.image
		};
		if (wechat.isWeixin()) {
			wechat
				.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareAppMessage',
					'onMenuShareTimeline'
				], configTimeline)
				.then(res => {})
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
	//拼团取消
	function getCombinationRemove() {
		postCombinationRemove({
				id: pinkId.value,
				cid: storeCombination.value.id
			})
			.then(res => {
				util.Tips({
					title: res.msg
				});
				getCombinationPink()
			})
			.catch(res => {
				util.Tips({
					title: res
				});
			});
	}
	function lookAll() {
		iShidden.value = !iShidden.value;
	}

	// ---------- 生命周期 ----------
	onLoad((options) => {
		// #ifdef MP
		if (options.scene) {
			var value = util.getUrlParams(decodeURIComponent(options.scene));
			if (typeof value === 'object') {
				if (value.id) options.id = value.id;
				//记录推广人uid
				if (value.pid) app.globalData.spid = value.pid;
			}
		}
		// #endif
		if (options.id) {
			pinkId.value = options.id;
		}
		if (isLogin.value == false) {
			Cache.set('login_back_url', `/pages/activity/goods_combination_status/index?id=${options.id}`);
			toLogin();
		}
	});

	//#ifdef MP
	/**
	 * 用户点击右上角分享
	 */
	onShareAppMessage(() => {
		return {
			title: i18n.t(`您的好友`) + userInfo.value.nickname + i18n.t(`邀请您参团`) + storeCombination.value.title,
			path: '/pages/activity/goods_combination_status/index?id=' + pinkId.value,
			imageUrl: storeCombination.value.image
		};
	});
	//#endif

	onMounted(() => {
		if (isLogin.value) {
			getCombinationPink();
		}
	});
</script>
<style lang="scss" scoped>
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

	/*开团*/
	.group-con .header {
		width: 100%;
		height: 186rpx;
		background-color: #fff;
		border-top: 1px solid #f5f5f5;
		padding: 0 30rpx;
		position: relative;
	}

	.group-con .header .iconfont {
		font-size: 100rpx;
		position: absolute;
		color: #ccc;
		right: 33rpx;
		bottom: 20rpx;
	}

	.group-con .header .pictrue {
		width: 140rpx;
		height: 140rpx;
	}

	.group-con .header .pictrue img {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.group-con .header .text {
		width: 540rpx;
		font-size: 30rpx;
		color: #222;
	}

	.group-con .header .text .money {
		font-size: 24rpx;
		font-weight: bold;
		margin-top: 15rpx;
	}

	.group-con .header .text .money .num {
		font-size: 32rpx;
	}

	.group-con .header .text .money .team {
		padding: 1rpx 10rpx;
		font-weight: normal;
		border-radius: 50rpx;
		font-size: 20rpx;
		vertical-align: 4rpx;
		margin-left: 15rpx;
	}

	.group-con .wrapper {
		background-color: #fff;
		margin-top: 20rpx;
		padding: 2rpx 0 35rpx 0;
	}

	.group-con .wrapper .title {
		margin-top: 30rpx;
	}

	.group-con .wrapper .title .line {
		width: 136rpx;
		height: 1px;
		background-color: #ddd;
	}

	.group-con .wrapper .title .name {
		margin: 0 45rpx;
		font-size: 28rpx;
		color: #282828;
	}

	.group-con .wrapper .title .name .time {
		margin: 0 14rpx;
	}

	.group-con .wrapper .title .name .timeTxt {
		color: #fc4141;
	}

	.group-con .wrapper .title .name :deep(.time .styleAll){
		background-color: var(--view-minorColorT);
		text-align: center;
		border-radius: 3rpx;
		font-size: 28rpx;
		font-weight: bold;
		display: inline-block;
		vertical-align: middle;
		color: var(--view-theme);
		padding: 2rpx 5rpx;
	}

	.group-con .wrapper .tips {
		font-size: 30rpx;
		font-weight: bold;
		text-align: center;
		margin-top: 30rpx;
		color: #999;
	}

	.group-con .wrapper .list {
		padding: 0 30rpx;
		margin-top: 45rpx;
	}

	.group-con .wrapper .list.result {
		max-height: 240rpx;
	}

	.group-con .wrapper .list.result.on {
		max-height: 2000rpx;
	}

	.group-con .wrapper .list .pictrue {
		width: 94rpx;
		height: 94rpx;
		margin: 0 0 29rpx 35rpx;
	}

	.group-con .wrapper .list .pictrue {
		position: relative;
	}

	.group-con .wrapper .list .pictrue .dumpling {
		width: 72rpx;
		height: 32rpx;
		line-height: 32rpx;
		font-size: 18rpx;
		text-align: center;
		color: #fff;
		top: -12rpx;
		right: -20rpx;
		border-radius: 18rpx;
		position: absolute;
		background-color: var(--view-theme);
	}

	.group-con .wrapper .list .pictrue img,
	.group-con .wrapper .list .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
		border: 2rpx solid var(--view-theme);
	}

	.group-con .wrapper .list .pictrue image.img-none {
		border: none;
	}

	.group-con .wrapper .lookAll {
		font-size: 24rpx;
		color: #282828;
		padding-top: 10rpx;
	}

	.group-con .wrapper .lookAll .iconfont {
		font-size: 25rpx;
		margin: 2rpx 0 0 10rpx;
	}

	.group-con .wrapper .teamBnt {
		font-size: 30rpx;
		width: 620rpx;
		height: 86rpx;
		border-radius: 50rpx;
		text-align: center;
		line-height: 86rpx;
		color: #fff;
		margin: 21rpx auto 0 auto;
	}

	.group-con .wrapper .cancel,
	.group-con .wrapper .lookOrder {
		text-align: center;
		font-size: 24rpx;
		color: #282828;
		padding-top: 30rpx;
	}

	.group-con .wrapper .cancel .iconfont {
		font-size: 35rpx;
		color: #2c2c2c;
		vertical-align: -4rpx;
		margin-right: 9rpx;
	}

	.group-con .wrapper .lookOrder .iconfont {
		font-size: 25rpx;
		color: #2c2c2c;
		margin-left: 10rpx;
	}

	.group-con .group-recommend {
		background-color: #fff;
		margin-top: 25rpx;
	}

	.group-con .group-recommend .title {
		padding-right: 30rpx;
		margin-left: 30rpx;
		height: 85rpx;
		border-bottom: 1px solid #eee;
		font-size: 28rpx;
		color: #282828;
	}

	.group-con .group-recommend .title .more {
		color: #808080;
	}

	.group-con .group-recommend .title .more .iconfont {
		margin-left: 13rpx;
		font-size: 28rpx;
	}

	.group-con .group-recommend .list {
		margin-top: 30rpx;
	}

	.group-con .group-recommend .list .item {
		width: 210rpx;
		margin: 0 0 25rpx 30rpx;
	}

	.group-con .group-recommend .list .item .pictrue {
		width: 100%;
		height: 210rpx;
		position: relative;
	}


	.group-con .group-recommend .list .item .pictrue img {
		width: 100%;
		height: 100%;
		border-radius: 10rpx;
	}

	.group-con .group-recommend .list .item .pictrue .team {
		position: absolute;
		top: 28rpx;
		left: -5rpx;
		min-width: 100rpx;
		height: 36rpx;
		line-height: 36rpx;
		text-align: center;
		border-radius: 0 18rpx 18rpx 0;
		font-size: 20rpx;
		color: #fff;
		background-color: var(--view-theme);
		// background-image: linear-gradient(to right, #fb5445 0%, #e93323 100%);
	}

	.group-con .group-recommend .list .item .name {
		font-size: 28rpx;
		color: #333;
		margin-top: 0.18rem;
	}

	.group-con .group-recommend .list .item .money {
		font-weight: bold;
		font-size: 28rpx;
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
