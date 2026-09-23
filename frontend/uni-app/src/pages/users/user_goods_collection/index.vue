<template>
	<view :style="colorStyle">
		<view class='collectionGoods' v-if="collectProductList.length">
			<view class="title-admin">
				<view>{{$t(`当前共`)}} <text class="text"> {{count}} </text> {{$t(`件商品`)}}</view>
				<view class="admin" @click="showRadio">{{checkbox_show?$t(`取消`):$t(`管理`)}}</view>
			</view>
			<checkbox-group @change.stop="checkboxChange">
				<view class='item acea-row' v-for="(item,index) in collectProductList" :key="index">
					<view class="left">
						<checkbox v-show="checkbox_show" :value="item.pid.toString()" :checked="item.checked" backgroundColor="#ffffff" activeBackgroundColor="var(--view-theme, #E93323)" activeBorderColor="var(--view-theme, #E93323)" iconColor="#ffffff" />
						<view class='pictrue' @click="jump(item)">
							<image :src="item.image"></image>
							<view class="invalid acea-row row-center-wrapper" v-if="!item.is_show">
								{{ $t(`已下架`) }}
							</view>
						</view>
					</view>
					<view class='text acea-row row-column-between' @click="jump(item)">
						<view class='name line2'>{{item.store_name}}</view>
						<view class='acea-row row-between-wrapper'>
							<view class='money font-color'>{{$t(`￥`)}}{{item.price}}</view>
							<!-- <view class='delete' @click.stop='delCollection(item.pid,index)'>删除</view> -->
						</view>
					</view>
				</view>
				<view class='loadingicon acea-row row-center-wrapper'>
					<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
				</view>
			</checkbox-group>
		</view>

		<view class='noCommodity' v-else-if="!collectProductList.length && page > 1">
			<view class='pictrue'>
				<image :src="imgHost + '/statics/images/noCollection.png'"></image>
			</view>
			<recommend :hostProduct="hostProduct"></recommend>
		</view>
		<view class='footer acea-row row-between-wrapper' v-if="checkbox_show && collectProductList.length">
			<view>
				<checkbox-group @change="checkboxAllChange">
					<checkbox value="all" :checked="!!isAllSelect" backgroundColor="#ffffff" activeBackgroundColor="var(--view-theme, #E93323)" activeBorderColor="var(--view-theme, #E93323)" iconColor="#ffffff" />
					<text class='checkAll'>{{$t(`全选`)}}({{ids.length}})</text>
				</checkbox-group>
			</view>
			<view class='button acea-row row-middle'>
				<button class='bnt' formType="submit" @click="subDel">{{$t(`取关`)}}</button>
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow, onReachBottom, onPageScroll } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
	getCollectUserList,
	getProductHot,
	collectDel
} from '@/api/store.js';
import {
	toLogin
} from '@/libs/login.js';
import recommend from '@/components/recommend';
import home from '@/components/home';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
import {
	HTTP_REQUEST_URL
} from '@/config/app';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const imgHost = HTTP_REQUEST_URL;
const ids = ref([]);
const hostProduct = ref([]);
const checkbox_show = ref(false);
const loadTitle = ref(i18n.t(`加载更多`));
const loading = ref(false);
const loadend = ref(false);
const collectProductList = ref([]);
const count = ref(0);
const limit = ref(15);
const page = ref(1);
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const hotScroll = ref(false);
const hotPage = ref(1);
const hotLimit = ref(10);
const isAllSelect = ref(false); //全选

// ---------- 生命周期 ----------
onLoad(() => {
	if (isLogin.value) {
		loadend.value = false;
		page.value = 1;
		collectProductList.value = [];
		getUserCollectProduct();
	} else {
		toLogin();
	}
});
onShow(() => {
	loadend.value = false;
	page.value = 1;
	collectProductList.value = [];
	getUserCollectProduct();
});
/**
 * 页面上拉触底事件的处理函数
 * 原代码重复注册了两次 onReachBottom（内容相同），此处仅注册一次
 */
onReachBottom(() => {
	getUserCollectProduct();
});
// 滚动监听
onPageScroll((e) => {
	// 传入scrollTop值并触发所有easy-loadimage组件下的滚动监听事件
	uni.$emit('scroll');
});

// ---------- methods ----------
function showRadio() {
	checkbox_show.value = !checkbox_show.value
}
function checkboxChange(e) {
	if (e.detail.value.length < ids.value.length) {
		isAllSelect.value = false;
	} else if (e.detail.value.length === collectProductList.value.length) {
		isAllSelect.value = true;
	}
	ids.value = e.detail.value;
}
function subDel() {
	if (ids.value.length) {
		collectDel(ids.value).then(res => {
			loadend.value = false;
			util.Tips({
				title: res.msg
			});
			page.value = 1;
			collectProductList.value = [];
			getUserCollectProduct();
			ids.value.length = '';
		});
	} else {
		return util.Tips({
			title: i18n.t(`请选择商品`)
		});
	}

}
function checkboxAllChange(event) {
	let value = event.detail.value;
	if (value.length > 0) {
		setAllSelectValue(1)
	} else {
		setAllSelectValue(0)
	}
}
function setAllSelectValue(status) {
	let selectValue = [];
	let valid = collectProductList.value;
	if (valid.length > 0) {
		let newValid = valid.map(item => {
			if (status) {
				item.checked = true;
				selectValue.push(item.pid);
				isAllSelect.value = true;
			} else {
				item.checked = false;
				isAllSelect.value = false;
			}
			return item;
		});
		collectProductList.value = newValid;
		ids.value = selectValue;
	}
}
function jump(item) {
	if (item.is_show) {
		uni.navigateTo({
			url: "/pages/goods_details/index?id=" + item.pid
		})
	} else {
		util.Tips({
			title: i18n.t(`该商品已下架`)
		})
	}

}
/**
 * 授权回调
 */
function onLoadFun() {
	loadend.value = false;
	page.value = 1;
	collectProductList.value = [];
	getUserCollectProduct();
	// get_host_product();
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e
}
/**
 * 获取收藏产品
 */
function getUserCollectProduct() {
	if (loading.value) return;
	if (loadend.value) return;
	loading.value = true;
	loadTitle.value = "";
	getCollectUserList({
		page: page.value,
		limit: limit.value
	}).then(res => {
		count.value = res.data.count;
		let collectProductListNew = res.data.list;
		collectProductListNew.map(e => {
			e.checked = false
		})

		let loadendNew = collectProductListNew.length < limit.value;
		collectProductList.value = util.SplitArray(collectProductListNew, collectProductList.value);
		loadend.value = loadendNew;
		loadTitle.value = loadendNew ? i18n.t(`我也是有底线的`) : i18n.t(`加载更多`);
		if (!collectProductList.value.length && page.value == 1) get_host_product();
		page.value = page.value + 1;
		loading.value = false;
	}).catch(err => {
		loading.value = false;
		loadTitle.value = i18n.t(`加载更多`);
	});
}
/**
 * 获取我的推荐
 */
function get_host_product() {
	if (hotScroll.value) return
	getProductHot(
		hotPage.value,
		hotLimit.value,
	).then(res => {
		hotPage.value++
		hotScroll.value = res.data.length < hotLimit.value
		hostProduct.value = hostProduct.value.concat(res.data)
	});
}
</script>

<style scoped lang="scss">
	.collectionGoods {
		background-color: #fff;
		border-top: 1rpx solid #eee;
	}

	.collectionGoods .item {
		margin-left: 30rpx;
		border-bottom: 1rpx solid #eee;
		height: 180rpx;
		display: flex;
		align-items: center;
		flex-wrap: nowrap;
	}

	.left {
		display: flex;
		align-items: center;
		margin-right: 20rpx;
	}

	.collectionGoods .item .pictrue {
		width: 130rpx;
		height: 130rpx;
		margin-left: 20rpx;
		position: relative;

		.invalid {
			position: absolute;
			width: 100%;
			height: 100%;
			top: 0;
			left: 0;
			background-color: rgba(0, 0, 0, 0.4);
			backdrop-filter: blur(3px);
			color: #fff;
		}

	}

	.collectionGoods .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.collectionGoods .item .text {
		height: 130rpx;
		flex: 1;
		font-size: 28rpx;
		color: #282828;
		padding-right: 20rpx;
	}

	.collectionGoods .item .text .name {
		width: max-contnet;
	}

	.collectionGoods .item .text .money {
		font-size: 26rpx;
	}

	.collectionGoods .item .text .delete {
		font-size: 26rpx;
		color: #282828;
		width: 144rpx;
		height: 46rpx;
		border: 1px solid #bbb;
		border-radius: 4rpx;
		text-align: center;
		line-height: 46rpx;
	}

	.noCommodity {
		background-color: #fff;
		padding-top: 1rpx;
		border-top: 0;
	}

	.title-admin {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 20rpx;
		border-bottom: 1px solid #f2f2f2;

		.text {
			color: var(--view-theme);
		}

		.admin {
			color: var(--view-theme);
		}
	}

	.footer {
		z-index: 999;
		width: 100%;
		height: 96rpx;
		background-color: #fafafa;
		position: fixed;
		padding: 0 30rpx;
		box-sizing: border-box;
		border-top: 1rpx solid #eee;
		bottom: 0;
	}

	.footer.on {
		// #ifndef H5
		bottom: 0rpx;
		// #endif
		// #ifdef MP || APP-PLUS
		bottom: 100rpx;
		bottom: calc(100rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		bottom: calc(100rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		// #endif
	}

	.footer .checkAll {
		font-size: 28rpx;
		color: #282828;
		margin-left: 16rpx;
	}

	// .shoppingCart .footer checkbox .wx-checkbox-input{background-color:#fafafa;}
	.footer .money {
		font-size: 30rpx;
	}

	.footer .placeOrder {
		color: #fff;
		font-size: 30rpx;
		width: 226rpx;
		height: 70rpx;
		border-radius: 50rpx;
		text-align: center;
		line-height: 70rpx;
		margin-left: 22rpx;
	}

	.footer .button .bnt {
		font-size: 28rpx;
		color: #999;
		border-radius: 50rpx;
		border: 1px solid #999;
		width: 160rpx;
		height: 60rpx;
		text-align: center;
		line-height: 60rpx;
	}

	.footer .button form~form {
		margin-left: 17rpx;
	}
</style>
