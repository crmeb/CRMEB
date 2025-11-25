<template>
	<view class="goodCate">
		<view id="head" class="header acea-row row-center-wrapper">
			<view class="pageIndex acea-row row-center-wrapper" @click="jumpIndex">
				<text class="iconfont icon-fanhuishouye"></text>
			</view>
			<navigator url="/pages/goods/goods_search/index" class="search acea-row row-middle" hover-class="none">
				<text class="iconfont icon-sousuo5"></text>
				{{ $t(`搜索商品名称`) }}
			</navigator>
		</view>
		<view class="conter">
			<view class="aside">
				<scroll-view scroll-y="true" scroll-with-animation="true" style="height: calc(100% - 100rpx)">
					<view class="item acea-row row-center-wrapper" :class="index == navActive ? 'on' : ''" v-for="(item, index) in categoryList" :key="index" @click="tapNav(index, item)">
						<text>{{ $t(item.cate_name) }}</text>
					</view>
				</scroll-view>
			</view>
			<view class="wrapper">
				<view class="bgcolor" v-if="iSlong">
					<view class="longTab acea-row row-middle" id="category">
						<scroll-view scroll-x="true" style="white-space: nowrap; display: flex; height: 44rpx" scroll-with-animation :scroll-left="tabLeft" show-scrollbar="true">
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
					<view class="openList" @click="openTap"><text class="iconfont icon-xiangxia"></text></view>
				</view>
				<view v-else>
					<view class="downTab">
						<view class="title acea-row row-between-wrapper">
							<view>{{ categoryTitle }}</view>
							<view class="closeList" @click="closeTap"><text class="iconfont icon-xiangxia"></text></view>
						</view>
						<view class="children">
							<view class="acea-row row-middle">
								<view class="item line1" :class="index === tabClick ? 'click' : ''" v-for="(item, index) in categoryErList" :key="index" @click="longClick(index)">
									{{ $t(item.cate_name) }}
								</view>
							</view>
						</view>
					</view>
					<view class="mask" @click="closeTap"></view>
				</view>
				<scroll-view
					scroll-y="true"
					scroll-with-animation="true"
					:scroll-top="0"
					@scroll="scroll"
					:style="{ height: scrollHeight + 'px' }"
					:lower-threshold="50"
					@scrolltolower="productslist"
				>
					<goodClass
						ref="goodClass"
						:tempArr="tempArr"
						:isLogin="isLogin"
						@gocartduo="goCartDuo"
						@gocartdan="goCartDan"
						@ChangeCartNumDan="ChangeCartNumDan"
						@detail="goDetail"
						:endLocation="endLocation"
						@addCart="addCart"
					></goodClass>
					<view class="loadingicon acea-row row-center-wrapper">
						<text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
						{{ loadTitle }}
					</view>
				</scroll-view>
			</view>
		</view>
		<view class="footer acea-row row-between-wrapper" id="cart">
			<view class="cartIcon acea-row row-center-wrapper" @click="getCartList(0)" v-if="cartData.cartList.length">
				<view class="iconfont icon-gouwuche-yangshi1"></view>
				<view class="num">{{ cartCount }}</view>
			</view>
			<view class="cartIcon acea-row row-center-wrapper noCart" v-else>
				<view class="iconfont icon-gouwuche-yangshi1"></view>
			</view>
			<view class="acea-row row-middle">
				<view class="money">
					{{ $t(`￥`) }}
					<text class="num">{{ totalPrice }}</text>
				</view>
				<view class="bnt" :class="cartCount ? '' : 'on'" @click="subOrder">{{ $t(`去付款`) }}</view>
			</view>
		</view>
		<cartList :cartData="cartData" @closeList="closeList" @ChangeCartNumDan="ChangeCartList" @ChangeSubDel="ChangeSubDel" @ChangeOneDel="ChangeOneDel"></cartList>
		<productWindow
			:attr="attr"
			:minQty="storeInfo.min_qty"
			:isShow="1"
			:iSplus="1"
			:iScart="1"
			@myevent="onMyEvent"
			@ChangeAttr="ChangeAttr"
			@ChangeCartNum="ChangeCartNumDuo"
			@attrVal="attrVal"
			@iptCartNum="iptCartNum"
			@goCat="goCatNum"
			:is_vip="is_vip"
			id="product-window"
		></productWindow>
		<ParabalaBall ref="Ball"></ParabalaBall>
	</view>
</template>

<script>
import ParabalaBall from '@/components/parabolaBall/ParabolaBall.vue';
import { getCategoryList, getProductslist, getAttr, postCartNum } from '@/api/store.js';
import { vcartList, getCartCounts, cartDel } from '@/api/order.js';
import productWindow from '@/components/productWindow';
import goodClass from '@/components/goodClass';
import cartList from '@/components/cartList';
import { mapGetters } from 'vuex';
import { goShopDetail } from '@/libs/order.js';
import { toLogin } from '@/libs/login.js';
let windowHeight = uni.getSystemInfoSync().windowHeight;
let sysHeight = uni.getSystemInfoSync().statusBarHeight;
let titleBarHeight = uni.getSystemInfo().titleBarHeight;
export default {
	computed: mapGetters(['isLogin', 'uid']),
	components: {
		productWindow,
		goodClass,
		cartList,
		ParabalaBall
	},
	props: {
		isNew: {
			type: Boolean,
			default: false
		}
	},
	watch: {
		isNew(newVal) {
			this.getAllCategory(1);
		}
	},
	data() {
		return {
			windowHeight: windowHeight,
			showCateDrawer: false,
			sysHeight: sysHeight,
			titleBarHeight: titleBarHeight,
			categoryList: [],
			navActive: 0,
			categoryTitle: '',
			categoryErList: [],
			tabLeft: 0,
			scrollTop: 0,
			old: {
				scrollTop: 0
			},
			isWidth: 0, //每个导航栏占位
			tabClick: 0, //导航栏被点击
			iSlong: true,
			tempArr: [],
			loading: false,
			loadend: false,
			loadTitle: this.$t(`加载更多`),
			page: 1,
			limit: 10,
			cid: 0, //一级分类
			sid: 0, //二级分类
			isAuto: false, //没有授权的不会自动授权
			isShowAuth: false, //是否隐藏授权
			attr: {
				cartAttr: false,
				productAttr: [],
				productSelect: {}
			},
			productValue: [],
			attrValue: '', //已选属性
			storeName: '', //多属性产品名称
			id: 0,
			cartData: {
				cartList: [],
				iScart: false
			},
			cartCount: 0,
			totalPrice: 0.0,
			lengthCart: 0,
			is_vip: 0, //是否是会员
			cart_num: 0,
			storeInfo: {},
			endLocation: {},
			scrollHeight: 0
		};
	},
	onLoad() {
		this.$nextTick(() => {
			// uni
			// 	.createSelectorQuery()
			// 	.select('#cart')
			// 	.boundingClientRect((res) => {
			// 		const { windowTop } = uni.getSystemInfoSync();
			// 		this.endLocation = {
			// 			x: res.left + uni.upx2px(120) / 2,
			// 			// #ifdef H5
			// 			y: res.top + windowTop,
			// 			// #endif
			// 			// #ifndef H5
			// 			y: res.top
			// 			// #endif
			// 		};
			// 	})
			// 	.exec();
		});
	},
	mounted() {
		let that = this;
		that.lengthCart = that.cartData.cartList;
		// 获取设备宽度
		uni.getSystemInfo({
			success(e) {
				that.isWidth = e.windowWidth / 5;
			}
		});
		!that.categoryList.length && this.getAllCategory(1);
		uni.$on('uploadCatData', () => {
			this.getAllCategory(1);
		});
		if (this.isLogin) {
			this.getCartNum();
			this.getCartList(1);
		}
		// #ifndef MP
		setTimeout(() => {
			const query = uni.createSelectorQuery().in(this);
			let h = 0;
			query
				.select('#head')
				.boundingClientRect((data) => {
					h += data.height;
					console.log(data.height);
				})

				.exec();
			const query2 = uni.createSelectorQuery().in(this);
			query2
				.select('#category')
				.boundingClientRect((data) => {
					h += data.height;
					console.log(data.height);
				})
				.exec();
			const query3 = uni.createSelectorQuery().in(this);
			query3
				.select('#cart')
				.boundingClientRect((data) => {
					h += data.height;
					console.log(data.height);
				})
				.exec();
			this.scrollHeight = windowHeight - h - sysHeight;
			console.log(windowHeight, h, sysHeight, this.scrollHeight);
		}, 1000);
		// #endif
		// #ifdef MP
		uni.getSystemInfo({
			success: (e) => {
				let StatusBar = e.statusBarHeight;
				let rect = uni.getMenuButtonBoundingClientRect();
				let CustomBar, HeaderBar;
				if (e.system.toLowerCase().indexOf('ios') > -1) {
					//IOS
					CustomBar = rect.bottom + (rect.top - e.statusBarHeight) * 2;
					HeaderBar = CustomBar - e.statusBarHeight;
				} else {
					//安卓
					HeaderBar = rect.height + (rect.top - e.statusBarHeight) * 2;
					CustomBar = HeaderBar + e.statusBarHeight;
				}
				console.log(e, windowHeight, CustomBar, HeaderBar);
				this.scrollHeight = windowHeight - HeaderBar - CustomBar;
			}
		});
		// #endif
	},
	methods: {
		jumpIndex() {
			this.$emit('jumpIndex');
		},
		addCart(detail) {
			// #ifdef H5
			const { windowTop } = uni.getSystemInfoSync();
			detail.y += windowTop;
			// #endif
			// this.$refs.Ball.showBall({
			// 	start: detail,
			// 	src: [detail.img, ''][Math.round(Math.random())],
			// 	end: this.endLocation
			// }).then(() => {})
		},
		// 生成订单；
		subOrder: function () {
			let that = this,
				list = that.cartData.cartList,
				ids = [];
			if (list.length) {
				list.forEach((item) => {
					ids.push(item.id);
				});
				uni.navigateTo({
					url: '/pages/goods/order_confirm/index?cartId=' + ids.join(',')
				});
				that.cartData.iScart = false;
			} else {
				return that.$util.Tips({
					title: this.$t(`请选择产品`)
				});
			}
		},
		// 计算总价；
		getTotalPrice: function () {
			let that = this,
				list = that.cartData.cartList,
				totalPrice = 0.0;
			list.forEach((item) => {
				if (item.attrStatus && item.status) {
					totalPrice = that.$util.$h.Add(totalPrice, that.$util.$h.Mul(item.cart_num, item.truePrice));
				}
			});
			that.$set(that, 'totalPrice', totalPrice);
		},
		ChangeSubDel: function (event) {
			let that = this,
				list = that.cartData.cartList,
				ids = [];
			list.forEach((item) => {
				ids.push(item.id);
			});
			cartDel(ids.join(',')).then((res) => {
				that.$set(that.cartData, 'cartList', []);
				that.cartData.iScart = false;
				that.totalPrice = 0.0;
				that.page = 1;
				that.loadend = false;
				that.tempArr = [];
				that.productslist();
				that.getCartNum();
			});
		},
		ChangeOneDel: function (id, index) {
			let that = this,
				list = that.cartData.cartList;
			cartDel(id.toString()).then((res) => {
				list.splice(index, 1);
				if (!list.length) {
					that.cartData.iScart = false;
					that.page = 1;
					that.loadend = false;
					that.tempArr = [];
					that.productslist();
				}
				that.getCartNum();
			});
		},
		getCartList(iSshow) {
			let that = this;
			vcartList().then((res) => {
				that.$set(that.cartData, 'cartList', res.data);
				if (res.data.length) {
					that.$set(that.cartData, 'iScart', iSshow ? false : !that.cartData.iScart);
				} else {
					that.$set(that.cartData, 'iScart', false);
				}
				that.getTotalPrice();
			});
		},
		closeList(e) {
			this.$set(this.cartData, 'iScart', e);
			this.page = 1;
			this.loadend = false;
			this.tempArr = [];
			this.productslist();
		},
		getCartNum: function () {
			let that = this;
			getCartCounts().then((res) => {
				that.cartCount = res.data.count;
				that.$refs.goodClass.addIng = false;
			});
		},

		onMyEvent: function () {
			this.$set(this.attr, 'cartAttr', false);
		},
		/**
		 * 默认选中属性
		 *
		 */
		DefaultSelect: function () {
			let productAttr = this.attr.productAttr;
			let value = [];
			for (let key in this.productValue) {
				if (this.productValue[key].stock > 0) {
					value = this.attr.productAttr.length ? key.split(',') : [];
					break;
				}
			}
			for (let i = 0; i < productAttr.length; i++) {
				this.$set(productAttr[i], 'index', value[i]);
			}
			//sort();排序函数:数字-英文-汉字；
			let productSelect = this.productValue[value.join(',')];
			if (productSelect && productAttr.length) {
				this.$set(this.attr.productSelect, 'store_name', this.storeName);
				this.$set(this.attr.productSelect, 'image', productSelect.image);
				this.$set(this.attr.productSelect, 'price', productSelect.price);
				this.$set(this.attr.productSelect, 'stock', productSelect.stock);
				this.$set(this.attr.productSelect, 'unique', productSelect.unique);
				this.$set(this.attr.productSelect, 'cart_num', this.storeInfo.min_qty);
				this.$set(this.attr.productSelect, 'min_qty', this.storeInfo.min_qty);
				this.$set(this.attr.productSelect, 'vip_price', productSelect.vip_price);
				this.$set(this, 'attrValue', value.join(','));
			} else if (!productSelect && productAttr.length) {
				this.$set(this.attr.productSelect, 'store_name', this.storeName);
				this.$set(this.attr.productSelect, 'image', this.storeInfo.image);
				this.$set(this.attr.productSelect, 'price', this.storeInfo.price);
				this.$set(this.attr.productSelect, 'stock', 0);
				this.$set(this.attr.productSelect, 'unique', '');
				this.$set(this.attr.productSelect, 'cart_num', 0);
				this.$set(this.attr.productSelect, 'min_qty', 0);
				this.$set(this, 'attrValue', '');
				this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
			} else if (!productSelect && !productAttr.length) {
				this.$set(this.attr.productSelect, 'store_name', this.storeName);
				this.$set(this.attr.productSelect, 'image', this.storeInfo.image);
				this.$set(this.attr.productSelect, 'price', this.storeInfo.price);
				this.$set(this.attr.productSelect, 'stock', this.storeInfo.stock);
				this.$set(this.attr.productSelect, 'unique', this.storeInfo.unique || '');
				this.$set(this.attr.productSelect, 'cart_num', this.storeInfo.min_qty);
				this.$set(this.attr.productSelect, 'min_qty', this.storeInfo.min_qty);
				this.$set(this, 'attrValue', '');
				this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
			}
		},
		/**
		 * 属性变动赋值
		 *
		 */
		ChangeAttr: function (res) {
			let productSelect = this.productValue[res];
			if (productSelect && productSelect.stock > 0) {
				this.$set(this.attr.productSelect, 'image', productSelect.image);
				this.$set(this.attr.productSelect, 'price', productSelect.price);
				this.$set(this.attr.productSelect, 'stock', productSelect.stock);
				this.$set(this.attr.productSelect, 'unique', productSelect.unique);
				this.$set(this.attr.productSelect, 'vip_price', productSelect.vip_price);
				this.$set(this.attr.productSelect, 'cart_num', this.storeInfo.min_qty);
				this.$set(this.attr.productSelect, 'min_qty', this.storeInfo.min_qty);
				this.$set(this, 'attrValue', res);
			} else if (productSelect && productSelect.stock == 0) {
				this.$set(this.attr.productSelect, 'image', productSelect.image);
				this.$set(this.attr.productSelect, 'price', productSelect.price);
				this.$set(this.attr.productSelect, 'stock', 0);
				this.$set(this.attr.productSelect, 'unique', '');
				this.$set(this.attr.productSelect, 'vip_price', productSelect.vip_price);
				this.$set(this.attr.productSelect, 'cart_num', 0);
				this.$set(this.attr.productSelect, 'min_qty', 0);
				this.$set(this, 'attrValue', '');
			} else {
				this.$set(this.attr.productSelect, 'image', this.storeInfo.image);
				this.$set(this.attr.productSelect, 'price', this.storeInfo.price);
				this.$set(this.attr.productSelect, 'stock', 0);
				this.$set(this.attr.productSelect, 'unique', '');
				this.$set(this.attr.productSelect, 'vip_price', this.storeInfo.vip_price);
				this.$set(this.attr.productSelect, 'cart_num', 0);
				this.$set(this.attr.productSelect, 'min_qty', 0);
				this.$set(this, 'attrValue', '');
			}
		},
		attrVal(val) {
			this.$set(this.attr.productAttr[val.indexw], 'index', this.attr.productAttr[val.indexw].attr_values[val.indexn]);
		},
		/**
		 * 购物车手动填写
		 *
		 */
		iptCartNum: function (e) {
			// this.$set(this.attr.productSelect, 'cart_num', e);
			if (e) {
				let number = this.storeInfo.min_qty;
				if (Number.isInteger(parseInt(e)) && parseInt(e) >= this.storeInfo.min_qty) {
					number = parseInt(e);
				}
				this.$nextTick((e) => {
					this.$set(this.attr.productSelect, 'cart_num', e < 0 ? this.storeInfo.min_qty : number);
				});
			}
		},
		onLoadFun() {},
		// 产品列表
		productslist: function () {
			let that = this;
			if (that.loadend) return;
			if (that.loading) return;
			that.loading = true;
			that.loadTitle = '';
			getProductslist({
				page: that.page,
				limit: that.limit,
				type: 1,
				cid: that.cid,
				sid: that.sid
			})
				.then((res) => {
					let list = res.data,
						loadend = list.length < that.limit;
					that.tempArr = that.$util.SplitArray(list, that.tempArr);
					that.$set(that, 'tempArr', that.tempArr);
					that.loading = false;
					that.loadend = loadend;
					that.loadTitle = loadend ? that.$t(`没有更多内容啦~`) : that.$t(`加载更多`);
					that.page == 1 && this.goTop();
					that.page = that.page + 1;
				})
				.catch((err) => {
					(that.loading = false), (that.loadTitle = that.$t(`加载更多`));
				});
		},

		// 改变单属性购物车
		ChangeCartNumDan(changeValue, index, item) {
			let num = this.tempArr[index];
			let stock = this.tempArr[index].stock;
			this.ChangeCartNum(changeValue, num, stock, 0, item.id);
		},
		// 改变多属性购物车
		ChangeCartNumDuo(changeValue) {
			//获取当前变动属性
			let productSelect = this.productValue[this.attrValue];
			//如果没有属性,赋值给商品默认库存
			if (productSelect === undefined && !this.attr.productAttr.length) productSelect = this.attr.productSelect;
			//无属性值即库存为0；不存在加减；
			if (productSelect === undefined) return;
			let stock = productSelect.stock || 0;
			let num = this.attr.productSelect;
			this.ChangeCartNum(changeValue, num, stock, 1, this.id);
		},
		// 已经加入购物车时的购物加减；
		ChangeCartList(changeValue, index) {
			let list = this.cartData.cartList;
			let num = list[index];
			let stock = list[index].trueStock;
			if (changeValue && list[index].productInfo.limit_type == 1 && num.cart_num >= list[index].productInfo.limit_num) {
				this.$set(num, 'cart_num', list[index].productInfo.limit_num);
				this.$util.Tips({
					title: this.$t(`最大限购数量${list[index].productInfo.limit_num}`)
				});
				return;
			}
			this.ChangeCartNum(changeValue, num, stock, 0, num.product_id, index, 1);
			if (!list.length) {
				this.cartData.iScart = false;
				this.page = 1;
				this.loadend = false;
				this.tempArr = [];
				this.productslist();
			}
		},
		// 购物车加减计算函数
		ChangeCartNum(changeValue, num, stock, isDuo, id, index, cart) {
			this.$refs.goodClass.addIng = false;
			if (changeValue) {
				num.cart_num++;
				if (num.cart_num > stock) {
					if (isDuo) {
						this.$set(this.attr.productSelect, 'cart_num', stock ? stock : 1);
						this.$set(this, 'cart_num', stock ? stock : 1);
					} else {
						num.cart_num = stock ? stock : 0;
						this.$set(this, 'tempArr', this.tempArr);
						this.$set(this.cartData, 'cartList', this.cartData.cartList);
					}
					return this.$util.Tips({
						title: this.$t(`该产品没有更多库存了`)
					});
				} else {
					if (!isDuo) {
						if (cart) {
							this.goCat(0, id, 1, 1, num.product_attr_unique);
							this.getTotalPrice();
						} else {
							this.goCat(0, id, 1);
						}
					}
				}
			} else {
				num.cart_num--;
				if (num.cart_num < num.min_qty) {
					this.cartData.cartList.splice(index, 1);
					num.cart_num = 0;
				} else if (num.cart_num == 0) {
					this.cartData.cartList.splice(index, 1);
					if (isDuo) {
						this.$set(this.attr.productSelect, 'cart_num', this.storeInfo.min_qty);
						this.$set(this, 'cart_num', this.storeInfo.min_qty);
					}
				}
				if (num.cart_num < 0) {
					if (isDuo) {
						this.$set(this.attr.productSelect, 'cart_num', this.storeInfo.min_qty);
						this.$set(this, 'cart_num', this.storeInfo.min_qty);
					} else {
						num.cart_num = 0;
						this.$set(this, 'tempArr', this.tempArr);
						this.$set(this.cartData, 'cartList', this.cartData.cartList);
					}
				} else {
					if (!isDuo) {
						if (cart) {
							this.goCat(0, id, 0, 1, num.product_attr_unique, num);
							this.getTotalPrice();
						} else {
							this.goCat(0, id, 0, 0, false, num);
						}
					}
				}
			}
		},
		// 多规格加入购物车；
		goCatNum() {
			this.goCat(1, this.id, 1);
		},
		/*
		 * 加入购物车
		 */
		goCat: function (duo, id, type, cart, unique, data) {
			let that = this;
			if (duo) {
				let productSelect = that.productValue[this.attrValue];
				//如果有属性,没有选择,提示用户选择
				if (that.attr.productAttr.length && productSelect === undefined)
					return that.$util.Tips({
						title: that.$t(`该产品没有更多库存了`)
					});
			}
			if (that.attr.productSelect.cart_num == 0) {
				return that.$util.Tips({
					title: that.$t(`不能输入0喔`)
				});
			}
			let q = {
				product_id: id,
				type: type,
				unique: duo ? that.attr.productSelect.unique : cart ? unique : ''
			};
			if (!that.cartData.iScart) q.num = duo ? that.attr.productSelect.cart_num : this.storeInfo.min_qty;
			data && data.cart_num < data.min_qty ? (q.num = data.min_qty) : '';
			postCartNum(q)
				.then(function (res) {
					if (duo) {
						that.attr.cartAttr = false;
						that.$util.Tips({
							title: that.$t(`添加成功`)
						});
						// that.page = 1;
						// that.loadend = false;
						that.tempArr.forEach((item, index) => {
							if (item.id == that.id) {
								let arrtStock = that.attr.productSelect.stock;
								let objNum = parseInt(item.cart_num) + parseInt(that.attr.productSelect.cart_num);
								item.cart_num = objNum > arrtStock ? arrtStock : objNum;
							}
						});
						// that.productslist();
					}
					that.getCartNum();
					if (!cart) {
						that.getCartList(1);
					}
				})
				.catch((err) => {
					that.attr.productSelect.cart_num = this.storeInfo.min_qty || that.attr.productSelect.limit_num;
					return that.$util.Tips({
						title: err
					});
				});
		},
		// 点击默认单属性购物车
		goCartDan(item, index) {
			if (!this.isLogin) {
				this.getIsLogin();
			} else {
				if (!item.cart_button) {
					goShopDetail(item, this.uid).then((res) => {
						uni.navigateTo({
							url: `/pages/goods_details/index?id=${item.id}`
						});
					});
					return;
				}
				this.tempArr[index].cart_num <= item.min_qty ? (this.tempArr[index].cart_num = item.min_qty) : 1;
				this.$set(this, 'tempArr', this.tempArr);
				this.goCat(0, item.id, 1, 0, 0, item);
			}
		},
		goCartDuo(item) {
			if (!this.isLogin) {
				this.getIsLogin();
			} else {
				if (!item.cart_button) {
					goShopDetail(item, this.uid).then((res) => {
						uni.navigateTo({
							url: `/pages/goods_details/index?id=${item.id}`
						});
					});
					return;
				}
				uni.showLoading({
					title: this.$t(`正在加载中`)
				});

				this.storeName = item.store_name;
				this.getAttrs(item.id);
				this.$set(this, 'id', item.id);
				this.$set(this.attr, 'cartAttr', true);
			}
		},
		getIsLogin() {
			toLogin();
		},
		// 商品详情接口；
		getAttrs(id) {
			let that = this;
			getAttr(id, 0).then((res) => {
				uni.hideLoading();
				that.$set(that.attr, 'productAttr', res.data.productAttr);
				that.$set(that, 'productValue', res.data.productValue);
				that.$set(that, 'is_vip', res.data.storeInfo.is_vip);
				that.$set(that, 'storeInfo', res.data.storeInfo);
				that.DefaultSelect();
			});
		},
		// 去详情页
		goDetail(item) {
			goShopDetail(item, this.uid).then((res) => {
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${item.id}`
				});
			});
		},

		openTap() {
			this.iSlong = false;
		},
		closeTap() {
			this.iSlong = true;
		},
		getAllCategory(type) {
			let that = this;
			if (type || !uni.getStorageSync('CAT3_DATA')) {
				getCategoryList().then((res) => {
					let data = res.data;
					uni.setStorageSync('CAT3_DATA', data);
					data.forEach((item) => {
						item.children.unshift({
							id: 0,
							cate_name: that.$t(`全部`)
						});
					});
					that.categoryTitle = data[0].cate_name;
					that.cid = data[0].id;
					that.sid = 0;
					that.navActive = 0;
					that.tabClick = 0;
					that.categoryList = data;
					that.page = 1;
					that.loadend = false;
					that.tempArr = [];
					that.categoryErList = res.data[0].children ? res.data[0].children : [];
					that.productslist();
				});
			} else {
				let data = uni.getStorageSync('CAT3_DATA');
				data.forEach((item) => {
					item.children.unshift({
						id: 0,
						cate_name: that.$t(`全部`)
					});
				});
				if (!that.cid) {
					that.categoryTitle = data[0].cate_name;
					that.cid = data[0].id;
					that.sid = 0;
					that.navActive = 0;
					that.tabClick = 0;
					that.categoryList = data;
					that.page = 1;
					that.loadend = false;
					that.productslist();
				}
			}
		},
		scroll(e) {
			this.old.scrollTop = e.detail.scrollTop;
		},
		goTop(e) {
			// 解决view层不同步的问题
			this.scrollTop = this.old.scrollTop;
			this.$nextTick(() => {
				this.scrollTop = 0;
			});
		},
		tapNav(index, item) {
			let list = this.categoryList[index];
			this.navActive = index;
			this.categoryTitle = list.cate_name;
			this.categoryErList = item.children ? item.children : [];
			this.tabClick = 0;
			this.tabLeft = 0;
			this.cid = list.id;
			this.sid = 0;
			this.page = 1;
			this.loadend = false;
			this.tempArr = [];

			this.productslist();
		},
		// 导航栏点击
		longClick(index) {
			if (this.categoryErList.length > 3) {
				this.tabLeft = (index - 1) * (this.isWidth + 6); //设置下划线位置
			}
			this.tabClick = index; //设置导航点击了哪一个
			this.iSlong = true;
			this.sid = this.categoryErList[index].id;
			this.page = 1;
			this.loadend = false;
			this.tempArr = [];
			this.productslist();
		}
	}
};
</script>

<style lang="scss">
page {
	background-color: #fff;
}

/deep/.product-window.joinCart {
	z-index: 999;
}

::-webkit-scrollbar {
	width: 0;
	height: 0;
	color: transparent;
	display: none;
}
.loadingicon {
	padding-bottom: 106rpx;
}
.goodCate {
	/deep/.mask {
		z-index: 99;
	}

	/deep/.attrProduct {
		.mask {
			z-index: 100;
		}
	}

	.header {
		position: fixed;
		height: 128rpx;
		background-color: #fff;
		top: 0;
		left: 0;
		width: 100%;
		z-index: 99;
		border-bottom: 1px solid #eee;
		padding: 0 28rpx;

		.pageIndex {
			width: 68rpx;
			height: 68rpx;
			border-radius: 50%;
			background-color: var(--view-theme);

			.iconfont {
				color: #fff;
				font-size: 30rpx;
			}
		}

		.search {
			width: 600rpx;
			height: 68rpx;
			border-radius: 36rpx;
			background-color: #f7f7f7;
			font-size: 26rpx;
			color: #cccccc;
			margin-left: 22rpx;
			padding: 0 30rpx;
			box-sizing: border-box;

			.iconfont {
				font-size: 30rpx;
				margin-right: 18rpx;
				color: #666666;
			}
		}
	}

	.conter {
		margin-top: 128rpx;
		// height: 100vh;
		background-color: #fff;
		position: relative;
		.aside {
			position: fixed;
			width: 23%;
			left: 0;
			bottom: 0;
			top: 0;
			background-color: #f7f7f7;
			overflow-y: auto;
			overflow-x: hidden;
			margin-top: 128rpx;
			z-index: 99;
			padding-bottom: 140rpx;

			.item {
				height: 100rpx;
				width: 100%;
				font-size: 26rpx;
				color: #333333;

				&.on {
					background-color: #ffffff;
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
			position: fixed;
			top: 232rpx;
			right: 0;
			width: 77%;
			float: right;
			background-color: #ffffff;
			overflow: hidden;
			.bgcolor {
				width: 100%;
				background-color: #ffffff;
			}

			.goodsList {
				margin-top: 0 !important;
			}

			.mask {
				z-index: 9;
			}

			.longTab {
				width: 65%;
				position: fixed;
				top: 128rpx;
				height: 100rpx;
				z-index: 99;
				background-color: #ffffff;

				.longItem {
					height: 44rpx;
					display: inline-block;
					line-height: 44rpx;
					text-align: center;
					font-size: 26rpx;
					overflow: hidden;
					text-overflow: ellipsis;
					white-space: nowrap;
					color: #333333;
					background-color: #f7f7f7;
					border-radius: 22rpx;
					margin-left: 12rpx;

					&.click {
						font-weight: bold;
						background-color: var(--view-theme);
						color: #ffffff;
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
						background-color: #ffffff;
					}
				}
			}

			.openList {
				width: 12%;
				height: 100rpx;
				background-color: #ffffff;
				line-height: 100rpx;
				padding-left: 30rpx;
				position: fixed;
				right: 0;
				top: 128rpx;
				z-index: 99;

				.iconfont {
					font-size: 22rpx;
					color: #666666;
				}
			}

			.downTab {
				width: 77%;
				position: fixed;
				top: 0;
				margin-top: 128rpx;
				z-index: 99;
				background-color: #ffffff;
				right: 0;

				.title {
					height: 100rpx;
					font-size: 26rpx;
					color: #999999;
					padding-left: 20rpx;

					.closeList {
						width: 90rpx;
						height: 100%;
						line-height: 100rpx;
						padding-left: 30rpx;
						transform: rotate(180deg);

						.iconfont {
							font-size: 22rpx;
							color: #666666;
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
							background-color: var(--view-theme);
							color: #ffffff;
						}
					}
				}
			}

			.goodsList {
				margin-top: 228rpx;
				padding: 0 20rpx 0 20rpx;

				/deep/.item {
					margin-bottom: 33rpx !important;

					// .pictrue {
					// 	height: 216rpx;
					// }

					.text {
						font-size: 26rpx;
					}

					.bottom {
						padding-right: 18rpx;

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
		width: 100%;
		position: fixed;
		left: 0;
		bottom: 0;
		background-color: #fff;
		box-shadow: 0px -3px 16px rgba(36, 12, 12, 0.05);
		z-index: 101;
		box-sizing: border-box;
		padding: 12rpx 30rpx;
		padding-bottom: calc(12rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		padding-bottom: calc(12rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
		.cartIcon {
			width: 124rpx;
			height: 106rpx;
			position: relative;
			margin-top: -64rpx;

			.iconfont {
				font-size: 100rpx;
				color: var(--view-theme);
			}

			.num {
				min-width: 12rpx;
				color: var(--view-theme);
				border-radius: 15px;
				position: absolute;
				right: 0;
				font-size: 16rpx;
				padding: 0 11rpx;
				background-color: #fff;
				height: 36rpx;
				line-height: 34rpx;
				top: 24rpx;
				border: 1rpx solid var(--view-theme);
			}
		}

		.money {
			font-size: 26rpx;
			font-weight: bold;
			color: var(--view-priceColor);
			margin-right: 34rpx;

			.num {
				font-size: 34rpx;
			}
		}

		.bnt {
			width: 222rpx;
			height: 76rpx;
			background-color: var(--view-theme);
			border-radius: 46px;
			line-height: 76rpx;
			text-align: center;
			color: #fff;
			font-size: 28rpx;
			margin-right: 30rpx;

			&.on {
				background: #bbbbbb;
			}
		}
	}
}
</style>
