<template>
	<view :style="colorStyle">
		<view class='productList'>
			<view class='search bg-color acea-row row-between-wrapper'>
				<view class='input acea-row row-between-wrapper'><text class='iconfont icon-sousuo'></text>
					<input :placeholder='$t(`搜索商品名称`)' placeholder-class='placeholder' confirm-type='search'
						name="search" :value='where.store_name' @confirm="searchSubmit"></input>
				</view>
				<view class='iconfont' :class='is_switch==true?"icon-pailie":"icon-tupianpailie"' @click='Changswitch'>
				</view>
			</view>
			<view class='nav acea-row row-middle'>
				<view class='item line1' :class='title ? "font-num":""' @click='set_where(1)'>{{$t(`默认`)}}</view>
				<view class='item' @click='set_where(2)'>
					{{$t(`积分`)}}
					<image v-if="price==1" src='../../static/images/up.png'></image>
					<image v-else-if="price==2" src='../../static/images/down.png'></image>
					<image v-else src='../../static/images/horn.png'></image>
				</view>
				<view class='item' @click='set_where(3)'>
					{{$t(`销量`)}}
					<image v-if="stock==1" src='../../static/images/up.png'></image>
					<image v-else-if="stock==2" src='../../static/images/down.png'></image>
					<image v-else src='../../static/images/horn.png'></image>
				</view>
			</view>
			<view class='list acea-row row-between-wrapper' :class='is_switch==true?"":"on"'>
				<view class='item' :class='is_switch==true?"":"on"' hover-class='none'
					v-for="(item,index) in productList" :key="index" @click="godDetail(item)">
					<view class='pictrue' :class='is_switch==true?"":"on"'>
						<image :src='item.image' :class='is_switch==true?"":"on"'></image>
					</view>
					<view class='text' :class='is_switch==true?"":"on"'>
						<view class='name line1'>{{item.title}}</view>
						<view class='money font-color' :class='is_switch==true?"":"on"'><text
								class='num'>{{item.price}}{{$t(`积分`)}}</text></view>
						<view class='vip acea-row row-between-wrapper' :class='is_switch==true?"":"on"'>
							<view class='vip-money' v-if="item.vip_price && item.vip_price > 0">{{item.vip_price}}
								{{$t(`积分`)}}
								<image src='../../static/images/vip.png'></image>
							</view>
							<view class="sales">
								<view class="">
									{{item.sales}}{{$t(`人兑换`)}}
								</view>
								<view class="exchange">{{$t(`兑换`)}}</view>
							</view>
						</view>
					</view>
				</view>
				<view class='loadingicon acea-row row-center-wrapper' v-if='productList.length > 0'>
					<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
				</view>
			</view>
		</view>
		<view class='noCommodity' v-if="productList.length==0 && where.page > 1">
			<view class='emptyBox'>
				<image :src="imgHost + '/statics/images/no-thing.png'"></image>
			</view>
			<recommend :hostProduct="hostProduct"></recommend>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
	import { ref } from 'vue';
	import { onLoad, onPageScroll, onPullDownRefresh, onReachBottom } from '@dcloudio/uni-app';
	import home from '@/components/home';
	import {
		getProductHot
	} from '@/api/store.js';
	import {
		getStoreIntegralList
	} from '@/api/activity.js';
	import recommend from '@/components/recommend';
	import {
		goShopDetail
	} from '@/libs/order.js'
	import { useColorTheme } from '@/composables/useColorTheme';
	import { useAppStore } from '@/store/modules/app';
	import { storeToRefs } from 'pinia';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';

	const { colorStyle } = useColorTheme();

	const appStore = useAppStore();
	const { uid } = storeToRefs(appStore);

	// ---------- data ----------
	const imgHost = HTTP_REQUEST_URL;
	const productList = ref([]);
	const is_switch = ref(true);
	const where = ref({
		store_name: '',
		priceOrder: '',
		salesOrder: '',
		page: 1,
		limit: 20,
	});
	const price = ref(0);
	const stock = ref(0);
	const nows = ref(false);
	const loadend = ref(false);
	const loading = ref(false);
	const loadTitle = ref(i18n.t(`加载更多`));
	const title = ref('');
	const hostProduct = ref([]);
	const hotPage = ref(1);
	const hotLimit = ref(10);
	const hotScroll = ref(false);

	// 滚动监听
	onPageScroll((e) => {
		// 传入scrollTop值并触发所有easy-loadimage组件下的滚动监听事件
		uni.$emit('scroll');
	});

	onLoad((options) => {
		where.value.cid = options.cid || 0;
		where.value['sid'] = options.sid || 0;
		title.value = options.title || '';
		where.value['store_name'] = options.searchValue || '';
		get_product_list();
		get_host_product();
	});

	// ---------- methods ----------
	// 去详情页
	function godDetail(item) {
		goShopDetail(item, uid.value).then(res => {
			uni.navigateTo({
				url: `/pages/activity/integral/index?id=${item.product_id}&activity_id=${item.id}`
			})
		})
	}
	function Changswitch() {
		is_switch.value = !is_switch.value
	}
	function searchSubmit(e) {
		where.value['store_name'] = e.detail.value;
		loadend.value = false;
		where.value['page'] = 1
		get_product_list(true);
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
			// hostProduct.value = res.data
		});
	}
	//点击事件处理
	function set_where(e) {
		switch (e) {
			case 1:
				where.value = {
					store_name: '',
					priceOrder: '',
					salesOrder: '',
					page: 1,
					limit: 20,
				}
				price.value = 0
				stock.value = 0
				break;
			case 2:
				if (price.value == 0) price.value = 1;
				else if (price.value == 1) price.value = 2;
				else if (price.value == 2) price.value = 0;
				stock.value = 0;
				break;
			case 3:
				if (stock.value == 0) stock.value = 1;
				else if (stock.value == 1) stock.value = 2;
				else if (stock.value == 2) stock.value = 0;
				price.value = 0
				break;
			case 4:
				nows.value = !nows.value;
				break;
		}
		loadend.value = false;
		where.value['page'] = 1;
		get_product_list(true);
	}
	//设置where条件
	function setWhere() {
		if (price.value == 0) where.value.priceOrder = '';
		else if (price.value == 1) where.value.priceOrder = 'asc';
		else if (price.value == 2) where.value.priceOrder = 'desc';
		if (stock.value == 0) where.value.salesOrder = '';
		else if (stock.value == 1) where.value.salesOrder = 'asc';
		else if (stock.value == 2) where.value.salesOrder = 'desc';
		where.value.news = nows.value ? 1 : 0;
	}
	//查找产品
	function get_product_list(isPage) {
		setWhere();
		if (loadend.value) return;
		if (loading.value) return;
		if (isPage === true) productList.value = [];
		loading.value = true;
		loadTitle.value = '';
		getStoreIntegralList(where.value).then(res => {
			let list = res.data;
			let newProductList = util.SplitArray(list, productList.value);
			let loadendVal = list.length < where.value.limit;
			loadend.value = loadendVal;
			loading.value = false;
			loadTitle.value = loadendVal ? i18n.t(`我也是有底线的`) : i18n.t(`加载更多`);
			productList.value = newProductList;
			where.value['page'] = where.value.page + 1;
		}).catch(err => {
			loading.value = false;
			loadTitle.value = i18n.t(`加载更多`);
		});
	}

	onPullDownRefresh(() => {

	})

	onReachBottom(() => {
		if (productList.value.length > 0) {
			get_product_list();
		} else {
			get_host_product();
		}

	})
</script>

<style scoped lang="scss">
	.productList .search {
		width: 100%;
		height: 86rpx;
		padding-left: 23rpx;
		box-sizing: border-box;
		position: fixed;
		left: 0;
		top: 0;
		z-index: 9;
	}

	.productList .search .input {
		width: 640rpx;
		height: 60rpx;
		background-color: #fff;
		border-radius: 50rpx;
		padding: 0 20rpx;
		box-sizing: border-box;
	}

	.productList .search .input input {
		width: 548rpx;
		height: 100%;
		font-size: 26rpx;
	}

	.productList .search .input .placeholder {
		color: #999;
	}

	.productList .search .input .iconfont {
		font-size: 35rpx;
		color: #555;
	}

	.productList .search .icon-pailie,
	.productList .search .icon-tupianpailie {
		color: #fff;
		width: 62rpx;
		font-size: 40rpx;
		height: 86rpx;
		line-height: 86rpx;
	}

	.productList .nav {
		height: 86rpx;
		color: #454545;
		position: fixed;
		left: 0;
		width: 100%;
		font-size: 28rpx;
		background-color: #fff;
		margin-top: 86rpx;
		top: 0;
		z-index: 9;
	}

	.productList .nav .item {
		width: 33%;
		text-align: center;
	}

	.productList .nav .item.font-color {}

	.productList .nav .item image {
		width: 15rpx;
		height: 19rpx;
		margin-left: 10rpx;
	}

	.productList .list {
		padding: 0 20rpx;
		margin-top: 172rpx;
	}

	.productList .list.on {
		background-color: #fff;
		border-top: 1px solid #f6f6f6;
	}

	.productList .list .item {
		width: 345rpx;
		margin-top: 20rpx;
		background-color: #fff;
		border-radius: 20rpx;
	}

	.productList .list .item.on {
		width: 100%;
		display: flex;
		border-bottom: 1rpx solid #f6f6f6;
		padding: 30rpx 0;
		margin: 0;
	}

	.productList .list .item .pictrue {
		position: relative;
		width: 100%;
		height: 345rpx;
	}

	.productList .list .item .pictrue.on {
		width: 180rpx;
		height: 180rpx;
	}

	.productList .list .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 20rpx 20rpx 0 0;
	}

	.productList .list .item .pictrue image.on {
		border-radius: 6rpx;
	}

	.productList .list .item .text {
		padding: 20rpx 17rpx 26rpx 17rpx;
		font-size: 30rpx;
		color: #222;
	}

	.productList .list .item .text.on {
		width: 508rpx;
		padding: 0 0 0 22rpx;
	}

	.productList .list .item .text .money {
		font-size: 22rpx;
		margin-top: 8rpx;
	}

	.productList .list .item .text .money.on {
		margin-top: 50rpx;
	}

	.productList .list .item .text .money .num {
		font-size: 34rpx;
	}

	.productList .list .item .text .vip {
		font-size: 22rpx;
		margin-top: 7rpx;

		.sales {
			color: #aaa;
			width: 100%;
			display: flex;
			justify-content: space-between;

			.exchange {
				border: 1px solid var(--view-theme);
				border-radius: 20rpx;
				padding: 0 12rpx;
				color: var(--view-theme);
			}
		}
	}

	.productList .list .item .text .vip.on {
		margin-top: 12rpx;
	}

	.productList .list .item .text .vip .vip-money {
		font-size: 24rpx;
		color: #282828;
		font-weight: bold;
	}

	.productList .list .item .text .vip .vip-money image {
		width: 64rpx;
		height: 26rpx;
		margin-left: 4rpx;
	}

	.noCommodity {
		background-color: #fff;
		padding-bottom: 30rpx;

		.emptyBox {
			text-align: center;
			padding-top: 20rpx;

			.tips {
				color: #aaa;
				font-size: 26rpx;
			}

			image {
				width: 414rpx;
				height: 304rpx;
			}
		}
	}
</style>
