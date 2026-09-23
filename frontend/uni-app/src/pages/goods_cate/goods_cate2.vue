<template>
	<view class="goodCate">
		<!-- <view>
			<scroll-view scroll-y="true" class="scroll-Y"> -->
		<view class="header acea-row row-center-wrapper">
			<view class="pageIndex" hover-class="none" @click="jumpIndex">
				<text class="iconfont icon-shouye3"></text>
			</view>
			<navigator :render-link="false" url="/pages/goods/goods_search/index" class="search acea-row row-center-wrapper" hover-class="none">
				<text class="iconfont icon-xiazai5"></text>
				{{ $t(`搜索商品名称`) }}
			</navigator>
		</view>
		<view class="conter">
			<view class="aside">
				<scroll-view scroll-y="true" scroll-with-animation="true" class="aside-scroll">
					<view class="item acea-row row-center-wrapper" :class="index == navActive ? 'on' : ''" v-for="(item, index) in categoryList" :key="index" @click="tapNav(index, item)">
						<text>{{ $t(item.cate_name) }}</text>
					</view>
				</scroll-view>
			</view>
			<view class="wrapper">
				<view class="bgcolor" v-if="iSlong">
					<view class="longTab acea-row row-middle">
						<scroll-view scroll-x="true" class="longTab-scroll" scroll-with-animation :scroll-left="tabLeft" show-scrollbar="true">
							<view
								class="longItem"
								:style="'width:' + isWidth + 'px'"
								:class="index === tabClick ? 'click' : ''"
								v-for="(item, index) in categoryErList"
								:key="index"
								@click="longClick(index)"
							>
								{{ $t(item.cate_name) }}
							</view>
						</scroll-view>
					</view>
					<view class="openList" @click="openTap"><text class="iconfont icon-xiala"></text></view>
				</view>
				<view v-else>
					<view class="downTab">
						<view class="title acea-row row-between-wrapper">
							<view>{{ categoryTitle }}</view>
							<view class="closeList" @click="closeTap"><text class="iconfont icon-xiala"></text></view>
						</view>
						<view class="children">
							<view class="acea-row row-middle">
								<view class="item line1" :class="index === tabClick ? 'click' : ''" v-for="(item, index) in categoryErList" :key="index" @click="longClick(index)">
									{{ $t(item.cate_name) }}
								</view>
							</view>
						</view>
					</view>
					<!-- <view class="mask" @click="closeTap"></view> -->
				</view>
				<scroll-view
					scroll-y="true"
					scroll-with-animation="true"
					:scroll-top="scrollTop"
					@scroll="scroll"
					class="goods-scroll"
					:lower-threshold="50"
					@scrolltolower="productslist"
				>
					<goodList
						ref="d_goodClassRef"
						:tempArr="tempArr"
						:isLogin="isLogin"
						@gocartduo="goCartDuo"
						@gocartdan="goCartDan"
						@ChangeCartNumDan="ChangeCartNumDan"
						@detail="goDetail"
					></goodList>
					<view class="loadingicon acea-row row-center-wrapper">
						<text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
						{{ loadTitle }}
					</view>
				</scroll-view>
			</view>
		</view>
		<view class="footer acea-row row-between-wrapper">
			<view class="cartIcon acea-row row-center-wrapper" @click="getCartList(0)" v-if="cartCount">
				<view class="iconfont icon-gouwuche-yangshi2"></view>
				<view class="num">{{ cartCount }}</view>
			</view>
			<view class="cartIcon acea-row row-center-wrapper noCart" v-else>
				<view class="iconfont icon-gouwuche-yangshi2"></view>
			</view>
			<view class="money acea-row row-middle">
				<view>
					{{ $t(`￥`) }}
					<text class="num">{{ totalPrice }}</text>
				</view>
				<view class="bnt" @click="subOrder">{{ $t(`去付款`) }}</view>
			</view>
		</view>
		<cartList :cartData="cartData" @closeList="closeList" @ChangeCartNumDan="ChangeCartList" @ChangeSubDel="ChangeSubDel" @ChangeOneDel="ChangeOneDel"></cartList>
		<productWindow
			:attr="attr"
			:isShow="1"
			:iSplus="1"
			:iScart="1"
			:minQty="storeInfo.min_qty"
			@myevent="onMyEvent"
			@ChangeAttr="ChangeAttr"
			@ChangeCartNum="ChangeCartNumDuo"
			@attrVal="attrVal"
			@iptCartNum="iptCartNum"
			@goCat="goCatNum"
			id="product-window"
			:is_vip="is_vip"
		></productWindow>
		<!-- </scroll-view>
		</view> -->
	</view>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { onReachBottom } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import { getCategoryList, getProductslist, getAttr, postCartNum } from '@/api/store.js';
import { vcartList, getCartCounts, cartDel } from '@/api/order.js';
import productWindow from '@/components/productWindow';
import goodList from '@/components/catGoodList';
import cartList from '@/components/cartList';
import { goShopDetail } from '@/libs/order.js';
import { toLogin } from '@/libs/login.js';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';

const emit = defineEmits(['jumpIndex']);

const appStore = useAppStore();
const { isLogin, uid } = storeToRefs(appStore);

// ---------- data ----------
const categoryList = ref([]);
const navActive = ref(0);
const categoryTitle = ref('');
const categoryErList = ref([]);
const tabLeft = ref(0);
const scrollTop = ref(0);
const old = reactive({
	scrollTop: 0
});
const isWidth = ref(0); //每个导航栏占位
const tabClick = ref(0); //导航栏被点击
const iSlong = ref(true);
const tempArr = ref([]);
const loading = ref(false);
const loadend = ref(false);
const loadTitle = ref(i18n.t(`加载更多`));
const page = ref(1);
const limit = ref(10);
const cid = ref(0); //一级分类
const sid = ref(0); //二级分类
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const attr = reactive({
	cartAttr: false,
	productAttr: [],
	productSelect: {}
});
const productValue = ref([]);
const attrValue = ref(''); //已选属性
const storeName = ref(''); //多属性产品名称
const id = ref(0);
const cartData = reactive({
	cartList: [],
	iScart: false
});
const cartCount = ref(0);
const totalPrice = ref(0.0);
const is_vip = ref(0); //是否是会员
const cart_num = ref(0);
const storeInfo = ref({});

const d_goodClassRef = ref(null);

// ---------- mounted ----------
function onUploadCatData() {
	getAllCategory(1);
	getCartNum();
}
onMounted(() => {
	// 获取设备宽度
	uni.getSystemInfo({
		success(e) {
			isWidth.value = e.windowWidth / 5;
		}
	});
	if (!categoryList.value.length) {
		getAllCategory(1);
		getCartNum();
		getCartList(1);
	}
	uni.$on('uploadCatData', onUploadCatData);
});
onBeforeUnmount(() => {
	uni.$off('uploadCatData', onUploadCatData);
});

// ---------- methods ----------
function jumpIndex() {
	emit('jumpIndex');
}
// 生成订单；
function subOrder() {
	let list = cartData.cartList,
		ids = [];
	if (list.length) {
		list.forEach((item) => {
			ids.push(item.id);
		});
		uni.navigateTo({
			url: '/pages/center/order/create/index?cartId=' + ids.join(',')
		});
		cartData.iScart = false;
	} else {
		return util.Tips({
			title: i18n.t(`请选择产品`)
		});
	}
}
// 计算总价；
function getTotalPrice() {
	let list = cartData.cartList,
		price = 0.0;
	list.forEach((item) => {
		if (item.attrStatus && item.status) {
			price = util.$h.Add(price, util.$h.Mul(item.cart_num, item.truePrice));
		}
	});
	totalPrice.value = price;
}
function ChangeSubDel(event) {
	let list = cartData.cartList,
		ids = [];
	list.forEach((item) => {
		ids.push(item.id);
	});
	cartDel(ids.join(',')).then((res) => {
		cartData['cartList'] = [];
		cartData.iScart = false;
		totalPrice.value = 0.0;
		page.value = 1;
		loadend.value = false;
		tempArr.value = [];
		productslist();
		getCartNum();
	});
}
function ChangeOneDel(id, index) {
	let list = cartData.cartList;
	cartDel(id.toString()).then((res) => {
		list.splice(index, 1);
		if (!list.length) {
			cartData.iScart = false;
			page.value = 1;
			loadend.value = false;
			tempArr.value = [];
			productslist();
		}
		getCartNum();
	});
}
function getCartList(iSshow) {
	vcartList().then((res) => {
		cartData['cartList'] = res.data;
		if (res.data.length) {
			cartData['iScart'] = iSshow ? false : !cartData.iScart;
		} else {
			cartData['iScart'] = false;
		}
		getTotalPrice();
	});
}
function closeList(e) {
	cartData['iScart'] = e;
	page.value = 1;
	loadend.value = false;
	tempArr.value = [];
	productslist();
}
function getCartNum() {
	getCartCounts().then((res) => {
		cartCount.value = res.data.count;
		d_goodClassRef.value.addIng = false;
	});
}

function onMyEvent() {
	attr['cartAttr'] = false;
}
/**
 * 默认选中属性
 *
 */
function DefaultSelect() {
	let productAttr = attr.productAttr;
	let value = [];
	for (let key in productValue.value) {
		if (productValue.value[key].stock > 0) {
			value = attr.productAttr.length ? key.split(',') : [];
			break;
		}
	}
	for (let i = 0; i < productAttr.length; i++) {
		productAttr[i]['index'] = value[i];
	}
	//sort();排序函数:数字-英文-汉字；
	let productSelect = productValue.value[value.join(',')];
	if (productSelect && productAttr.length) {
		attr.productSelect['store_name'] = storeName.value;
		attr.productSelect['image'] = productSelect.image;
		attr.productSelect['price'] = productSelect.price;
		attr.productSelect['stock'] = productSelect.stock;
		attr.productSelect['unique'] = productSelect.unique;
		attr.productSelect['cart_num'] = storeInfo.value.min_qty;
		attr.productSelect['vip_price'] = productSelect.vip_price;
		attrValue.value = value.join(',');
	} else if (!productSelect && productAttr.length) {
		attr.productSelect['store_name'] = storeName.value;
		attr.productSelect['image'] = storeInfo.value.image;
		attr.productSelect['price'] = storeInfo.value.price;
		attr.productSelect['stock'] = 0;
		attr.productSelect['unique'] = '';
		attr.productSelect['cart_num'] = 0;
		attr.productSelect['vip_price'] = storeInfo.value.vip_price;
		attrValue.value = '';
	} else if (!productSelect && !productAttr.length) {
		attr.productSelect['store_name'] = storeName.value;
		attr.productSelect['image'] = storeInfo.value.image;
		attr.productSelect['price'] = storeInfo.value.price;
		attr.productSelect['stock'] = storeInfo.value.stock;
		attr.productSelect['unique'] = storeInfo.value.unique || '';
		attr.productSelect['cart_num'] = storeInfo.value.min_qty;
		attrValue.value = '';
		attr.productSelect['vip_price'] = storeInfo.value.vip_price;
	}
}
/**
 * 属性变动赋值
 *
 */
function ChangeAttr(res) {
	let productSelect = productValue.value[res];
	if (productSelect && productSelect.stock > 0) {
		attr.productSelect['image'] = productSelect.image;
		attr.productSelect['price'] = productSelect.price;
		attr.productSelect['stock'] = productSelect.stock;
		attr.productSelect['unique'] = productSelect.unique;
		attr.productSelect['vip_price'] = productSelect.vip_price;
		attr.productSelect['cart_num'] = storeInfo.value.min_qty;
		attrValue.value = res;
	} else if (productSelect && productSelect.stock == 0) {
		attr.productSelect['image'] = productSelect.image;
		attr.productSelect['price'] = productSelect.price;
		attr.productSelect['stock'] = 0;
		attr.productSelect['unique'] = '';
		attr.productSelect['vip_price'] = productSelect.vip_price;
		attr.productSelect['cart_num'] = 0;
		attrValue.value = '';
	} else {
		attr.productSelect['image'] = storeInfo.value.image;
		attr.productSelect['price'] = storeInfo.value.price;
		attr.productSelect['stock'] = 0;
		attr.productSelect['unique'] = '';
		attr.productSelect['vip_price'] = storeInfo.value.vip_price;
		attr.productSelect['cart_num'] = 0;
		attrValue.value = '';
	}
}
function attrVal(val) {
	attr.productAttr[val.indexw]['index'] = attr.productAttr[val.indexw].attr_values[val.indexn];
}
/**
 * 购物车手动填写
 *
 */
function iptCartNum(e) {
	// attr.productSelect['cart_num'] = e;
	if (e) {
		let number = storeInfo.value.min_qty;
		if (Number.isInteger(parseInt(e)) && parseInt(e) >= storeInfo.value.min_qty) {
			number = parseInt(e);
		}
		nextTick((e) => {
			attr.productSelect['cart_num'] = e < 0 ? storeInfo.value.min_qty : number;
		});
	}
}
function onLoadFun() {}
// 产品列表
function productslist() {
	if (loadend.value) return;
	if (loading.value) return;
	loading.value = true;
	loadTitle.value = '';
	getProductslist({
		page: page.value,
		limit: limit.value,
		type: 1,
		cid: cid.value,
		sid: sid.value
	})
		.then((res) => {
			let list = res.data,
				loadendLocal = list.length < limit.value;
			tempArr.value = util.SplitArray(list, tempArr.value);
			loading.value = false;
			loadend.value = loadendLocal;
			loadTitle.value = loadendLocal ? i18n.t(`没有更多内容啦~`) : i18n.t(`加载更多`);
			page.value == 1 && goTop();
			page.value = page.value + 1;
		})
		.catch((err) => {
			(loading.value = false), (loadTitle.value = i18n.t(`加载更多`));
		});
}
function scroll(e) {
	old.scrollTop = e.detail.scrollTop;
}
function goTop(e) {
	// 解决view层不同步的问题
	scrollTop.value = old.scrollTop;
	nextTick(() => {
		scrollTop.value = 0;
	});
}
// 改变单属性购物车
function ChangeCartNumDan(changeValue, index, item) {
	let num = tempArr.value[index];
	let stock = tempArr.value[index].stock;
	ChangeCartNum(changeValue, num, stock, 0, item.id);
}
// 改变多属性购物车
function ChangeCartNumDuo(changeValue) {
	//获取当前变动属性
	let productSelect = productValue.value[attrValue.value];
	//如果没有属性,赋值给商品默认库存
	if (productSelect === undefined && !attr.productAttr.length) productSelect = attr.productSelect;
	//无属性值即库存为0；不存在加减；
	if (productSelect === undefined) return;
	let stock = productSelect.stock || 0;
	let num = attr.productSelect;
	ChangeCartNum(changeValue, num, stock, 1, id.value);
}
// 已经加入购物车时的购物加减；
function ChangeCartList(changeValue, index) {
	let list = cartData.cartList;
	let num = list[index];
	let stock = list[index].trueStock;
	ChangeCartNum(changeValue, num, stock, 0, num.product_id, index, 1);
	if (!list.length) {
		cartData.iScart = false;
		page.value = 1;
		loadend.value = false;
		tempArr.value = [];
		productslist();
	}
}
// 购物车加减计算函数
function ChangeCartNum(changeValue, num, stock, isDuo, id, index, cart) {
	d_goodClassRef.value.addIng = false;
	if (changeValue) {
		num.cart_num++;
		if (num.cart_num > stock) {
			if (isDuo) {
				attr.productSelect['cart_num'] = stock ? stock : 1;
				cart_num.value = stock ? stock : 1;
			} else {
				num.cart_num = stock ? stock : 0;
				tempArr.value = tempArr.value;
				cartData['cartList'] = cartData.cartList;
			}
			return util.Tips({
				title: i18n.t(`该产品没有更多库存了`)
			});
		} else {
			if (!isDuo) {
				if (cart) {
					goCat(0, id, 1, 1, num.product_attr_unique, num);
					getTotalPrice();
				} else {
					goCat(0, id, 1, 0, false, num);
				}
			}
		}
	} else {
		num.cart_num--;
		if (num.cart_num < num.min_qty) {
			cartData.cartList.splice(index, 1);
			num.cart_num = 0;
		} else if (num.cart_num == 0) {
			cartData.cartList.splice(index, 1);
		}
		if (num.cart_num < 0) {
			if (isDuo) {
				attr.productSelect['cart_num'] = storeInfo.value.min_qty;
				cart_num.value = storeInfo.value.min_qty;
			} else {
				num.cart_num = 0;
				tempArr.value = tempArr.value;
				cartData['cartList'] = cartData.cartList;
			}
		} else {
			if (!isDuo) {
				if (cart) {
					goCat(0, id, 0, 1, num.product_attr_unique, num);
					getTotalPrice();
				} else {
					goCat(0, id, 0, 0, false, num);
				}
			}
		}
	}
}
// 多规格加入购物车；
function goCatNum() {
	goCat(1, id.value, 1);
}
/*
 * 加入购物车
 */
function goCat(duo, id, type, cart, unique, data) {
	if (duo) {
		let productSelect = productValue.value[attrValue.value];
		//如果有属性,没有选择,提示用户选择
		if (attr.productAttr.length && productSelect === undefined) {
			return util.Tips({
				title: i18n.t(`该产品没有更多库存了`)
			});
		}
		if (attr.productSelect.cart_num <= 0) {
			return util.Tips({
				title: i18n.t(`最少添加 1 件商品`)
			});
		}
	}
	let q = {
		product_id: id,
		type: type,
		unique: duo ? attr.productSelect.unique : cart ? unique : ''
	};
	data && data.cart_num < data.min_qty ? (q.num = data.min_qty) : '';
	if (!cartData.iScart) q.num = duo ? attr.productSelect.cart_num : storeInfo.value.min_qty;
	postCartNum(q)
		.then(function (res) {
			if (duo) {
				attr.cartAttr = false;
				util.Tips({
					title: i18n.t(`添加成功`)
				});
				// page.value = 1;
				// loadend.value = false;
				tempArr.value.forEach((item, index) => {
					if (item.id == id) {
						let arrtStock = attr.productSelect.stock;
						let objNum = parseInt(item.cart_num) + parseInt(attr.productSelect.cart_num);
						item.cart_num = objNum > arrtStock ? arrtStock : objNum;
					}
				});
				// productslist();
			}
			getCartNum();
			if (!cart) {
				getCartList(1);
			}
		})
		.catch((err) => {
			return util.Tips({
				title: err
			});
		});
}
// 点击默认单属性购物车
function goCartDan(item, index) {
	if (!isLogin.value) {
		getIsLogin();
	} else {
		if (!item.cart_button) {
			goShopDetail(item, uid.value).then((res) => {
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${item.id}`
				});
			});
			return;
		}
		item.cart_num = item.min_qty;
		tempArr.value = tempArr.value;
		goCat(0, item.id, 1);
	}
}
function goCartDuo(item) {
	if (!isLogin.value) {
		getIsLogin();
	} else {
		if (!item.cart_button) {
			goShopDetail(item, uid.value).then((res) => {
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${item.id}`
				});
			});
			return;
		}
		storeName.value = item.store_name;
		getAttrs(item.id);
		id.value = item.id;
		attr['cartAttr'] = true;
	}
}
function getIsLogin() {
	toLogin();
}
// 商品详情接口；
function getAttrs(goodsId) {
	getAttr(goodsId, 0).then((res) => {
		attr['productAttr'] = res.data.productAttr;
		productValue.value = res.data.productValue;
		is_vip.value = res.data.storeInfo.is_vip;
		storeInfo.value = res.data.storeInfo;
		DefaultSelect();
	});
}
// 去详情页
function goDetail(item) {
	goShopDetail(item, uid.value).then((res) => {
		uni.navigateTo({
			url: `/pages/goods_details/index?id=${item.id}`
		});
	});
}

function openTap() {
	iSlong.value = false;
}
function closeTap() {
	iSlong.value = true;
}
function getAllCategory(type) {
	if (type || !uni.getStorageSync('CAT2_DATA')) {
		getCategoryList().then((res) => {
			uni.setStorageSync('CAT2_DATA', res.data);
			let data = res.data;
			data.forEach((item) => {
				item.children.unshift({
					id: 0,
					cate_name: i18n.t(`全部`)
				});
			});
			categoryTitle.value = data[0].cate_name;
			cid.value = data[0].id;
			sid.value = 0;
			navActive.value = 0;
			tabClick.value = 0;
			categoryList.value = data;
			page.value = 1;
			loadend.value = false;
			tempArr.value = [];
			categoryErList.value = res.data[0].children ? res.data[0].children : [];
			productslist();
		});
	} else {
		let data = uni.getStorageSync('CAT2_DATA');
		data.forEach((item) => {
			item.children.unshift({
				id: 0,
				cate_name: i18n.t(`全部`)
			});
		});
		if (!cid.value) {
			categoryTitle.value = data[0].cate_name;
			cid.value = data[0].id;
			sid.value = 0;
			navActive.value = 0;
			tabClick.value = 0;
			categoryList.value = data;
			categoryErList.value = data[0].children ? data[0].children : [];
			page.value = 1;
			loadend.value = false;
			tempArr.value = [];
		}
		productslist();
	}
}
function tapNav(index, item) {
	let list = categoryList.value[index];
	navActive.value = index;
	categoryTitle.value = list.cate_name;
	categoryErList.value = item.children ? item.children : [];
	tabClick.value = 0;
	tabLeft.value = 0;
	cid.value = list.id;
	sid.value = 0;
	page.value = 1;
	loadend.value = false;
	tempArr.value = [];
	productslist();
}
// 导航栏点击
function longClick(index) {
	if (categoryErList.value.length > 3) {
		tabLeft.value = (index - 1) * (isWidth.value + 6); //设置下划线位置
	}
	tabClick.value = index; //设置导航点击了哪一个
	iSlong.value = true;
	sid.value = categoryErList.value[index].id;
	page.value = 1;
	loadend.value = false;
	tempArr.value = [];
	productslist();
}

// 暴露给父组件 goods_cate.vue（classTwoRef.productslist()）
defineExpose({
	productslist
});

onReachBottom(() => {
	productslist();
});
</script>

<style lang="scss">
.scroll-Y {
	height: 100vh;
}

page {
	background-color: #fff;
	height: 100vh;
	overflow: hidden;
}

:deep(.product-window.joinCart){
	z-index: 999;
}

::-webkit-scrollbar {
	width: 0;
	height: 0;
	color: transparent;
	display: none;
}

.goodCate {
	display: flex;
	flex-direction: column;
	height: 100vh;
	.mask {
		z-index: 99;
	}

:deep(.attrProduct){
	.mask {
		z-index: 100;
	}
}

	.header {
		flex-shrink: 0;
		height: 128rpx;
		background-color: #fff;
		width: 100%;
		z-index: 99;
		border-bottom: 1px solid #f0f0f0;

		.pageIndex {
			width: 68rpx;
			height: 68rpx;
			border-radius: 50%;
			background-color: var(--view-theme);
			text-align: center;
			line-height: 68rpx;

			.iconfont {
				color: #fff;
				font-size: 30rpx;
			}
		}

		.search {
			width: 600rpx;
			height: 68rpx;
			border-radius: 36rpx;
			background-color: #f8f8f8;
			font-size: 26rpx;
			color: #adadad;
			margin-left: 22rpx;

			.iconfont {
				font-size: 30rpx;
				margin: 4rpx 16rpx 0 0;
			}
		}
	}

	.conter {
		flex: 1;
		display: flex;
		overflow: hidden;
		background-color: #fff;

		.aside {
			flex-shrink: 0;
			width: 23%;
			background-color: #f7f7f7;
			overflow: hidden;
			display: flex;
			flex-direction: column;

			.aside-scroll {
				flex: 1;
				height: 100%;
			}

			.item {
				height: 100rpx;
				width: 100%;
				font-size: 26rpx;
				color: #424242;

				&.on {
					background-color: #fff;
					width: 100%;
					text-align: center;
					color: var(--view-theme);
					font-weight: 500;
					position: relative;

					&::after {
						content: '';
						position: absolute;
						width: 6rpx;
						height: 46rpx;
						background: var(--view-theme);
						border-radius: 0 4rpx 4rpx 0;
						left: 0;
					}
				}
			}
		}

		.wrapper {
			flex: 1;
			display: flex;
			flex-direction: column;
			overflow: hidden;
			background-color: #fff;

			.bgcolor {
				flex-shrink: 0;
				width: 100%;
				background-color: #fff;
				display: flex;
				align-items: center;
			}

			.goodsList {
				margin-top: 0 !important;
			}

			.mask {
				z-index: 9;
			}

			.longTab {
				flex: 1;
				height: 100rpx;
				background-color: #fff;
				overflow: hidden;

				.longTab-scroll {
					white-space: nowrap;
					height: 44rpx;
				}

				.longItem {
					height: 44rpx;
					display: inline-block;
					line-height: 44rpx;
					text-align: center;
					font-size: 26rpx;
					overflow: hidden;
					text-overflow: ellipsis;
					white-space: nowrap;
					color: #989898;
					background-color: #f7f7f7;
					border-radius: 22rpx;
					margin-left: 12rpx;

					&.click {
						font-weight: bold;
						background-color: var(--view-minorColorT);
						color: var(--view-theme);
					}
				}

				.underlineBox {
					height: 3px;
					width: 20%;
					display: flex;
					align-content: center;
					justify-content: center;
					transition: 0.5s;

					.underline {
						width: 33rpx;
						height: 4rpx;
						background-color: white;
					}
				}
			}

			.openList {
				flex-shrink: 0;
				width: 12%;
				height: 100rpx;
				background-color: #fff;
				line-height: 100rpx;
				padding-left: 30rpx;

				.iconfont {
					font-size: 22rpx;
					color: #989898;
				}
			}

			.downTab {
				width: 100%;
				background-color: #fff;
				position: relative;
				z-index: 99;

				.title {
					height: 100rpx;
					font-size: 26rpx;
					color: #adadad;
					padding-left: 20rpx;

					.closeList {
						width: 90rpx;
						height: 100%;
						line-height: 100rpx;
						padding-left: 30rpx;
						transform: rotate(180deg);

						.iconfont {
							font-size: 22rpx;
							color: #989898;
						}
					}
				}

				.children {
					max-height: 500rpx;
					overflow-x: hidden;
					overflow-y: auto;
					padding-bottom: 20rpx;

					.item {
						height: 60rpx;
						background-color: #f7f7f7;
						border-radius: 30rpx;
						line-height: 60rpx;
						padding: 0 15rpx;
						margin: 0 0 20rpx 20rpx;
						width: 165rpx;
						text-align: center;

						&.click {
							font-weight: bold;
							background-color: var(--view-minorColorT);
							color: var(--view-theme);
						}
					}
				}
			}

			.goods-scroll {
				flex: 1;
				height: 0;
			}

			.goodsList {
				margin-top: 0 !important;
				padding: 0 30rpx 0 20rpx;

:deep(.item){
					margin-bottom: 33rpx !important;

					.pictrue {
						// height: 216rpx;
					}

					.text {
						font-size: 26rpx;
					}

					.bottom {
						.sales {
							.money {
								font-size: 34rpx;

								text {
									font-size: 26rpx;
								}
							}
						}

						.cart {
							.pictrue {
								width: 50rpx;
								height: 50rpx;
							}
						}
					}
				}
			}
		}
	}

	.footer {
		flex-shrink: 0;
		width: 100%;
		background-color: #fff;
		box-shadow: 0px -3rpx 16rpx rgba(36, 12, 12, 0.05);
		z-index: 101;
		padding: 12rpx 30rpx;
		box-sizing: border-box;
		padding-bottom: calc(12rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		padding-bottom: calc(12rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/

		.cartIcon {
			width: 96rpx;
			height: 96rpx;
			border-radius: 50%;
			position: relative;
			margin-top: -36rpx;

			.iconfont {
				font-size: 115rpx;
				margin-top: -5px;
				color: var(--view-theme);
			}

			&.noCart {
				.iconfont {
					color: #cbcbcb;
				}
			}

			.num {
				background-color: var(--view-priceColor);
				min-width: 12rpx;
				color: #fff;
				border-radius: 15px;
				position: absolute;
				right: -6rpx;
				top: -10rpx;
				font-size: 22rpx;
				padding: 0 10rpx;
				height: 34rpx;
				line-height: 34rpx;
			}
		}

		.money {
			color: var(--view-priceColor);
			font-size: 28rpx;
			font-weight: bold;

			.num {
				font-size: 42rpx;
			}

			.bnt {
				width: 222rpx;
				height: 76rpx;
				background: var(--view-theme);
				border-radius: 46rpx;
				line-height: 76rpx;
				text-align: center;
				color: #fff;
				margin-left: 24rpx;
			}
		}
	}
}
</style>
