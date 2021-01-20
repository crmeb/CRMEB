<template>
	<view>
		<view class='shoppingCart copy-data'>
			<view class='labelNav acea-row row-around row-middle'>
				<view class='item'><text class='iconfont icon-xuanzhong'></text>100%正品保证</view>
				<view class='item'><text class='iconfont icon-xuanzhong'></text>所有商品精挑细选</view>
				<view class='item'><text class='iconfont icon-xuanzhong'></text>售后无忧</view>
			</view>
			<view class='nav acea-row row-between-wrapper'>
				<view>购物数量 <text class='num font-color'>{{cartCount}}</text></view>
				<view v-if="cartList.valid.length > 0 || cartList.invalid.length > 0" class='administrate acea-row row-center-wrapper'
				 @click='manage'>{{ footerswitch ? '管理' : '取消'}}</view>
			</view>
			<view v-if="cartList.valid.length > 0 || cartList.invalid.length > 0">
				<view class='list'>
					<checkbox-group @change="checkboxChange">
						<block v-for="(item,index) in cartList.valid" :key="index">
							<view class='item acea-row row-between-wrapper'>
								<!-- #ifndef MP -->
								<checkbox :value="(item.id).toString()" :checked="item.checked" :disabled="item.attrStatus?false:true" />
								<!-- #endif -->
								<!-- #ifdef MP -->
								<checkbox :value="item.id" :checked="item.checked" :disabled="item.attrStatus?false:true" />
								<!-- #endif -->
								<navigator :url='"/pages/goods_details/index?id="+item.product_id' hover-class='none' class='picTxt acea-row row-between-wrapper'>
									<view class='pictrue'>
										<image v-if="item.productInfo.attrInfo" :src='item.productInfo.attrInfo.image'></image>
										<image v-else :src='item.productInfo.image'></image>
									</view>
									<view class='text'>
										<view class='line1' :class="item.attrStatus?'':'reColor'">{{item.productInfo.store_name}}</view>
										<view class='infor line1' v-if="item.productInfo.attrInfo">属性：{{item.productInfo.attrInfo.suk}}</view>
										<view class='money' v-if="item.attrStatus">￥{{item.truePrice}}</view>
										<view class="reElection acea-row row-between-wrapper" v-else>
											<view class="title">请重新选择商品规格</view>
											<view class="reBnt cart-color acea-row row-center-wrapper" @click.stop="reElection(item)">重选</view>
										</view>
									</view>
									<view class='carnum acea-row row-center-wrapper' v-if="item.attrStatus">
										<view class="reduce" :class="item.numSub ? 'on' : ''" @click.stop='subCart(index)'>-</view>
										<view class='num'>{{item.cart_num}}</view>
										<!-- <view class="num">
											<input type="number" v-model="item.cart_num" @click.stop @input="iptCartNum(index)" @blur="blurInput(index)"/>
										</view> -->
										<view class="plus" :class="item.numAdd ? 'on' : ''" @click.stop='addCart(index)'>+</view>
									</view>
								</navigator>
							</view>
						</block>
					</checkbox-group>
				</view>
				<view class='invalidGoods' v-if="cartList.invalid.length > 0">
					<view class='goodsNav acea-row row-between-wrapper'>
						<view @click='goodsOpen'><text class='iconfont' :class='goodsHidden==true?"icon-xiangxia":"icon-xiangshang"'></text>失效商品</view>
						<view class='del' @click='unsetCart'><text class='iconfont icon-shanchu1'></text>清空</view>
					</view>
					<view class='goodsList' :hidden='goodsHidden'>
						<block v-for="(item,index) in cartList.invalid" :key='index'>
							<view class='item acea-row row-between-wrapper'>
								<view class='invalid'>失效</view>
								<view class='pictrue'>
									<image v-if="item.productInfo.attrInfo" :src='item.productInfo.attrInfo.image'></image>
									<image v-else :src='item.productInfo.image'></image>
								</view>
								<view class='text acea-row row-column-between'>
									<view class='line1 name'>{{item.productInfo.store_name}}</view>
									<view class='infor line1' v-if="item.productInfo.attrInfo">属性：{{item.productInfo.attrInfo.suk}}</view>
									<view class='acea-row row-between-wrapper'>
										<!-- <view>￥{{item.truePrice}}</view> -->
										<view class='end'>该商品已失效</view>
									</view>
								</view>
							</view>
						</block>
					</view>
				</view>
				<view class='loadingicon acea-row row-center-wrapper' v-if="cartList.valid.length&&!loadend">
					<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
				</view>
				<view class='loadingicon acea-row row-center-wrapper' v-if="cartList.invalid.length&&loadend">
					<text class='loading iconfont icon-jiazai' :hidden='loadingInvalid==false'></text>{{loadTitleInvalid}}
				</view>
			</view>
			<view class='noCart' v-if="cartList.valid.length == 0 && cartList.invalid.length == 0">
				<view class='pictrue'>
					<image src='../../static/images/noCart.png'></image>
				</view>
				<recommend :hostProduct='hostProduct'></recommend>
			</view>
			<view style='height:120rpx;color: #F5F5F5;'></view>
			<view class='footer acea-row row-between-wrapper' v-if="cartList.valid.length > 0">
				<view>
					<checkbox-group @change="checkboxAllChange">
						<checkbox value="all" :checked="!!isAllSelect" /><text class='checkAll'>全选 ({{cartCount}})</text>
					</checkbox-group>
				</view>
				<view class='money acea-row row-middle' v-if="footerswitch==true">
					<text class='font-color'>￥{{selectCountPrice}}</text>
					<form @submit="subOrder" report-submit='true'>
						<button class='placeOrder bg-color' formType="submit">立即下单</button>
					</form>
				</view>
				<view class='button acea-row row-middle' v-else>
					<form @submit="subCollect" report-submit='true'>
						<button class='bnt cart-color' formType="submit">收藏</button>
					</form>
					<form @submit="subDel" report-submit='true'>
						<button class='bnt' formType="submit">删除</button>
					</form>
				</view>
			</view>
		</view>
		<productWindow :attr="attr" :isShow='1' :iSplus='1' :iScart='1' @myevent="onMyEvent" @ChangeAttr="ChangeAttr"
		 @ChangeCartNum="ChangeCartNum" @attrVal="attrVal" @iptCartNum="iptCartNum" @goCat="reGoCat" id='product-window'></productWindow>
		<!-- #ifdef MP -->
		<!-- <authorize :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<view class="uni-p-b-98"></view>
		<z_tabBar :pagePath="'/pages/order_addcart/order_addcart'"></z_tabBar>
	</view>
</template>

<script>
	import {
		getCartList,
		getCartCounts,
		changeCartNum,
		cartDel,
		getResetCart
	} from '@/api/order.js';
	import {
		getProductHot,
		collectAll,
		getProductDetail
	} from '@/api/store.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	import recommend from '@/components/recommend';
	import productWindow from '@/components/productWindow';
	import z_tabBar from "@/pages/index/components/z_tabBar.vue"
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	export default {
		components: {
			recommend,
			productWindow,
			z_tabBar,
			// #ifdef MP
			authorize
			// #endif
		},
		data() {
			return {
				cartCount: 0,
				goodsHidden: true,
				footerswitch: true,
				hostProduct: [],
				cartList: {
					valid: [],
					invalid: []
				},
				isAllSelect: false, //全选
				selectValue: [], //选中的数据
				selectCountPrice: 0.00,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				hotScroll: false,
				hotPage: 1,
				hotLimit: 10,
				loading: false,
				loadend: false,
				loadTitle: '加载更多', //提示语
				page: 1,
				limit: 20,
				loadingInvalid: false,
				loadendInvalid: false,
				loadTitleInvalid: '加载更多', //提示语
				pageInvalid: 1,
				limitInvalid: 20,
				attr: {
					cartAttr: false,
					productAttr: [],
					productSelect: {}
				},
				productValue: [], //系统属性
				storeInfo: {},
				attrValue: '', //已选属性
				attrTxt: '请选择', //属性页面提示
				cartId: 0,
				product_id: 0
			};
		},
		computed: mapGetters(['isLogin']),
		onLoad: function(options) {
			let that = this;
			if (that.isLogin == false) {
				toLogin();
			}
		},
		onShow: function() {
			if (this.isLogin == true) {
				this.hotPage = 1;
				this.hostProduct = [],
				this.hotScroll = false,
				this.getHostProduct();
				this.loadend = false;
				this.page = 1;
				this.cartList.valid = [];
				this.getCartList();
				this.loadendInvalid = false;
				this.pageInvalid = 1;
				this.cartList.invalid = [];
				this.getInvalidList();
				this.getCartNum();
				this.goodsHidden = true;
				this.footerswitch = true;
				this.hostProduct = [];
				this.hotScroll = false;
				this.hotPage = 1;
				this.hotLimit = 10;
				this.cartList = {
						valid: [],
						invalid: []
					},
					this.isAllSelect = false; //全选
				this.selectValue = []; //选中的数据
				this.selectCountPrice = 0.00;
				this.cartCount = 0;
				this.isShowAuth = false;
			};
		},
		methods: {
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e;
			},
			// 修改购物车
			reGoCat: function() {
				let that = this,
					productSelect = that.productValue[this.attrValue];
				//如果有属性,没有选择,提示用户选择
				if (
					that.attr.productAttr.length &&
					productSelect === undefined
				)
					return that.$util.Tips({
						title: "产品库存不足，请选择其它"
					});
					
				let q = {
					id: that.cartId,
					product_id: that.product_id,
					num: that.attr.productSelect.cart_num,
					unique: that.attr.productSelect !== undefined ?
						that.attr.productSelect.unique : ""
				};
				getResetCart(q)
					.then(function(res) {
						that.attr.cartAttr = false;
						that.$util.Tips({
							title: "添加购物车成功",
							success: () => {
								that.loadend = false;
								that.page = 1;
								that.cartList.valid = [];
								that.getCartList();
								that.getCartNum();
							}
						});
					})
					.catch(res => {
						return that.$util.Tips({
							title: res.msg
						});
					});
			},
			onMyEvent: function() {
				this.$set(this.attr, 'cartAttr', false);
			},
			reElection: function(item) {
				this.getGoodsDetails(item)
			},
			/**
			 * 获取产品详情
			 * 
			 */
			getGoodsDetails: function(item) {
				uni.showLoading({
					title: '加载中',
					mask: true
				});
				let that = this;
				that.cartId = item.id;
				that.product_id = item.product_id;
				getProductDetail(item.product_id).then(res => {
					uni.hideLoading();
					that.attr.cartAttr = true;
					let storeInfo = res.data.storeInfo;
					that.$set(that, 'storeInfo', storeInfo);
					that.$set(that.attr, 'productAttr', res.data.productAttr);
					that.$set(that, 'productValue', res.data.productValue);
					that.DefaultSelect();
				}).catch(err => {
					uni.hideLoading();
				})
			},
			/**
			 * 属性变动赋值
			 * 
			 */
			ChangeAttr: function(res) {
				let productSelect = this.productValue[res];
				if (productSelect && productSelect.stock > 0) {
					this.$set(this.attr.productSelect, "image", productSelect.image);
					this.$set(this.attr.productSelect, "price", productSelect.price);
					this.$set(this.attr.productSelect, "stock", productSelect.stock);
					this.$set(this.attr.productSelect, "unique", productSelect.unique);
					this.$set(this.attr.productSelect, "cart_num", 1);
					this.$set(this, "attrValue", res);
					this.$set(this, "attrTxt", "已选择");
				} else {
					this.$set(this.attr.productSelect, "image", this.storeInfo.image);
					this.$set(this.attr.productSelect, "price", this.storeInfo.price);
					this.$set(this.attr.productSelect, "stock", 0);
					this.$set(this.attr.productSelect, "unique", "");
					this.$set(this.attr.productSelect, "cart_num", 0);
					this.$set(this, "attrValue", "");
					this.$set(this, "attrTxt", "请选择");
				}
			},
			/**
			 * 默认选中属性
			 * 
			 */
			DefaultSelect: function() {
				let productAttr = this.attr.productAttr;
				let value = [];
				for (var key in this.productValue) {
					if (this.productValue[key].stock > 0) {
						value = this.attr.productAttr.length ? key.split(",") : [];
						break;
					}
				}
				for (let i = 0; i < productAttr.length; i++) {
					this.$set(productAttr[i], "index", value[i]);
				}
				//sort();排序函数:数字-英文-汉字；
				let productSelect = this.productValue[value.sort().join(",")];
				if (productSelect && productAttr.length) {
					this.$set(
						this.attr.productSelect,
						"store_name",
						this.storeInfo.store_name
					);
					this.$set(this.attr.productSelect, "image", productSelect.image);
					this.$set(this.attr.productSelect, "price", productSelect.price);
					this.$set(this.attr.productSelect, "stock", productSelect.stock);
					this.$set(this.attr.productSelect, "unique", productSelect.unique);
					this.$set(this.attr.productSelect, "cart_num", 1);
					this.$set(this, "attrValue", value.sort().join(","));
					this.$set(this, "attrTxt", "已选择");
				} else if (!productSelect && productAttr.length) {
					this.$set(
						this.attr.productSelect,
						"store_name",
						this.storeInfo.store_name
					);
					this.$set(this.attr.productSelect, "image", this.storeInfo.image);
					this.$set(this.attr.productSelect, "price", this.storeInfo.price);
					this.$set(this.attr.productSelect, "stock", 0);
					this.$set(this.attr.productSelect, "unique", "");
					this.$set(this.attr.productSelect, "cart_num", 0);
					this.$set(this, "attrValue", "");
					this.$set(this, "attrTxt", "请选择");
				} else if (!productSelect && !productAttr.length) {
					this.$set(
						this.attr.productSelect,
						"store_name",
						this.storeInfo.store_name
					);
					this.$set(this.attr.productSelect, "image", this.storeInfo.image);
					this.$set(this.attr.productSelect, "price", this.storeInfo.price);
					this.$set(this.attr.productSelect, "stock", this.storeInfo.stock);
					this.$set(
						this.attr.productSelect,
						"unique",
						this.storeInfo.unique || ""
					);
					this.$set(this.attr.productSelect, "cart_num", 1);
					this.$set(this, "attrValue", "");
					this.$set(this, "attrTxt", "请选择");
				}
			},
			attrVal(val) {
				this.$set(this.attr.productAttr[val.indexw], 'index', this.attr.productAttr[val.indexw].attr_values[val.indexn]);
			},
			/**
			 * 购物车数量加和数量减
			 * 
			 */
			ChangeCartNum: function(changeValue) {
				//changeValue:是否 加|减
				//获取当前变动属性
				let productSelect = this.productValue[this.attrValue];
				//如果没有属性,赋值给商品默认库存
				if (productSelect === undefined && !this.attr.productAttr.length)
					productSelect = this.attr.productSelect;
				//无属性值即库存为0；不存在加减；
				if (productSelect === undefined) return;
				let stock = productSelect.stock || 0;
				let num = this.attr.productSelect;
				if (changeValue) {
					num.cart_num++;
					if (num.cart_num > stock) {
						this.$set(this.attr.productSelect, "cart_num", stock ? stock : 1);
						this.$set(this, "cart_num", stock ? stock : 1);
					}
				} else {
					num.cart_num--;
					if (num.cart_num < 1) {
						this.$set(this.attr.productSelect, "cart_num", 1);
						this.$set(this, "cart_num", 1);
					}
				}
			},
			/**
			 * 购物车手动填写
			 * 
			 */
			iptCartNum: function(e) {
				this.$set(this.attr.productSelect, 'cart_num', e);
			},
			subDel: function(event) {
				let that = this,
					selectValue = that.selectValue;
				if (selectValue.length > 0)
					cartDel(selectValue).then(res => {
						that.loadend = false;
						that.page = 1;
						that.cartList.valid = [];
						that.getCartList();
						that.getCartNum();
					});
				else
					return that.$util.Tips({
						title: '请选择产品'
					});
			},
			getSelectValueProductId: function() {
				let that = this;
				let validList = that.cartList.valid;
				let selectValue = that.selectValue;
				let productId = [];
				if (selectValue.length > 0) {
					for (let index in validList) {
						if (that.inArray(validList[index].id, selectValue)) {
							productId.push(validList[index].product_id);
						}
					}
				};
				return productId;
			},
			subCollect: function(event) {
				let that = this,
					selectValue = that.selectValue;
				if (selectValue.length > 0) {
					let selectValueProductId = that.getSelectValueProductId();
					collectAll(that.getSelectValueProductId().join(',')).then(res => {
						return that.$util.Tips({
							title: res.msg,
							icon: 'success'
						});
					}).catch(err => {
						return that.$util.Tips({
							title: err
						});
					});
				} else {
					return that.$util.Tips({
						title: '请选择产品'
					});
				}
			},
			subOrder: function(event) {
				let that = this,
					selectValue = that.selectValue;
				if (selectValue.length > 0) {
					uni.navigateTo({
						url: '/pages/users/order_confirm/index?cartId=' + selectValue.join(',')
					});
				} else {
					return that.$util.Tips({
						title: '请选择产品'
					});
				}
			},
			checkboxAllChange: function(event) {
				let value = event.detail.value;
				if (value.length > 0) {
					this.setAllSelectValue(1)
				} else {
					this.setAllSelectValue(0)
				}
			},
			setAllSelectValue: function(status) {
				let that = this;
				let selectValue = [];
				let valid = that.cartList.valid;
				if (valid.length > 0) {
					// for (let index in valid) {
					// 	if (status == 1) {
					// 		if(valid[index].attrStatus){
					// 			valid[index].checked = true;
					// 			selectValue.push(valid[index].id);
					// 		}else{
					// 			valid[index].checked = false;
					// 		}
					// 	} else valid[index].checked = false;
					// }
					let newValid = valid.map(item=>{
						if (status) {
							if (item.attrStatus) {
								item.checked = true;
								selectValue.push(item.id);
							} else{
								item.checked = false;
							}
						} else{
							item.checked = false;
						}
						return item;
					});
					that.$set(that.cartList, 'valid', newValid);
					that.selectValue = selectValue;
					that.switchSelect();
				}
			},
			checkboxChange: function(event) {
				let that = this;
				let value = event.detail.value;
				let valid = that.cartList.valid;
				for (let index in valid) {
					if (that.inArray(valid[index].id, value)){
						if(valid[index].attrStatus){
							valid[index].checked = true;
						}else{
							valid[index].checked = false;
						}
					} else {
						valid[index].checked = false;
					} 
				}
				that.$set(that.cartList, 'valid', valid);
				let newArr = that.cartList.valid.filter(item => item.attrStatus);
				that.isAllSelect = value.length == newArr.length;
				that.selectValue = value;
				that.switchSelect();
			},
			inArray: function(search, array) {
				for (let i in array) {
					if (array[i] == search) {
						return true;
					}
				}
				return false;
			},
			switchSelect: function() {
				let that = this;
				let validList = that.cartList.valid;
				let selectValue = that.selectValue;
				let selectCountPrice = 0.00;
				if (selectValue.length < 1) {
					that.selectCountPrice = selectCountPrice;
				} else {
					for (let index in validList) {
						if (that.inArray(validList[index].id, selectValue)) {
							selectCountPrice = that.$util.$h.Add(selectCountPrice, that.$util.$h.Mul(validList[index].cart_num, validList[
								index].truePrice))
						}
					}
					that.selectCountPrice = selectCountPrice;
				}
			},
			/**
			 * 购物车手动填写
			 * 
			 */
			iptCartNum: function(index) {
				let item = this.cartList.valid[index];
				if (item.cart_num) {
					this.setCartNum(item.id, item.cart_num);
				}
				this.switchSelect();
			},
			blurInput: function(index) {
				let item = this.cartList.valid[index];
				if (!item.cart_num) {
					item.cart_num = 1;
					this.$set(this.cartList, 'valid', this.cartList.valid)
				}
			},
			subCart: function(index) {
				let that = this;
				let status = false;
				let item = that.cartList.valid[index];
				item.cart_num = Number(item.cart_num) - 1;
				if (item.cart_num < 1) status = true;
				if (item.cart_num <= 1) {
					item.cart_num = 1;
					item.numSub = true;
				} else {
					item.numSub = false;
					item.numAdd = false;
				}
				if (false == status) {
					that.setCartNum(item.id, item.cart_num, function(data) {
						that.cartList.valid[index] = item;
						that.switchSelect();
					});
				}
			},
			addCart: function(index) {
				let that = this;
				let item = that.cartList.valid[index];
				item.cart_num = Number(item.cart_num) + 1;
				let productInfo = item.productInfo;
				if (productInfo.hasOwnProperty('attrInfo') && item.cart_num >= item.productInfo.attrInfo.stock) {
					item.cart_num = item.productInfo.attrInfo.stock;
					item.numAdd = true;
					item.numSub = false;
				} else {
					item.numAdd = false;
					item.numSub = false;
				}
				that.setCartNum(item.id, item.cart_num, function(data) {
					that.cartList.valid[index] = item;
					that.switchSelect();
				});
			},
			setCartNum(cartId, cartNum, successCallback) {
				let that = this;
				changeCartNum(cartId, cartNum).then(res => {
					successCallback && successCallback(res.data);
				});
			},
			getCartNum: function() {
				let that = this;
				getCartCounts().then(res => {
					that.cartCount = res.data.count;
				});
			},
			getCartList: function() {
				let that = this;
				if (this.loadend) return false;
				if (this.loading) return false;
				let data = {
					page: that.page,
					limit: that.limit,
					status: 1
				}
				getCartList(data).then(res => {
					let cartList = res.data;
					let valid = cartList.valid,
						loadend = valid.length < that.limit;
					let validList = that.$util.SplitArray(valid, that.cartList.valid);
					let numSub = [{
						numSub: true
					}, {
						numSub: false
					}];
					let numAdd = [{
							numAdd: true
						}, {
							numAdd: false
						}],
						selectValue = [];
					if (validList.length > 0) {
						for (let index in validList) {
							if (validList[index].cart_num == 1) {
								validList[index].numSub = true;
							} else {
								validList[index].numSub = false;
							}
							let productInfo = validList[index].productInfo;
							if (productInfo.hasOwnProperty('attrInfo') && validList[index].cart_num == validList[index].productInfo.attrInfo
								.stock) {
								validList[index].numAdd = true;
							} else if (validList[index].cart_num == validList[index].productInfo.stock) {
								validList[index].numAdd = true;
							} else {
								validList[index].numAdd = false;
							}
							if(validList[index].attrStatus){
								validList[index].checked = true;
								selectValue.push(validList[index].id);
							}else{
								validList[index].checked = false;
							}
						}
					}
					that.$set(that.cartList, 'valid', validList);
					that.loadend = loadend;
					that.loadTitle = loadend ? '我也是有底线的' : '加载更多';
					that.page = that.page + 1;
					that.loading = false;
					// that.goodsHidden = cartList.valid.length <= 0 ? false : true;
					that.selectValue = selectValue;
					let newArr = validList.filter(item => item.attrStatus);
					that.isAllSelect = newArr.length == selectValue.length && newArr.length;
					that.switchSelect();
				}).catch(function() {
					that.loading = false;
					that.loadTitle = '加载更多';
				})
			},
			getInvalidList: function() {
				let that = this;
				if (this.loadendInvalid) return false;
				if (this.loadingInvalid) return false;
				let data = {
					page: that.pageInvalid,
					limit: that.limitInvalid,
					status: 0
				}
				getCartList(data).then(res => {
					let cartList = res.data,
						invalid = cartList.invalid,
						loadendInvalid = invalid.length < that.limitInvalid;
					let invalidList = that.$util.SplitArray(invalid, that.cartList.invalid);
					that.$set(that.cartList, 'invalid', invalidList);
					that.loadendInvalid = loadendInvalid;
					that.loadTitleInvalid = loadendInvalid ? '我也是有底线的' : '加载更多';
					that.pageInvalid = that.pageInvalid + 1;
					that.loadingInvalid = false;
				}).catch(res => {
					that.loadingInvalid = false;
					that.loadTitleInvalid = '加载更多';
				})

			},
			getHostProduct: function() {
				let that = this;
				if (that.hotScroll) return
				getProductHot(
					that.hotPage,
					that.hotLimit,
				).then(res => {
					that.hotPage++
					that.hotScroll = res.data.length < that.hotLimit
					that.hostProduct = that.hostProduct.concat(res.data)
				});
			},
			goodsOpen: function() {
				let that = this;
				that.goodsHidden = !that.goodsHidden;
			},
			manage: function() {
				let that = this;
				that.footerswitch = !that.footerswitch;
			},
			unsetCart: function() {
				let that = this,
					ids = [];
				for (let i = 0, len = that.cartList.invalid.length; i < len; i++) {
					ids.push(that.cartList.invalid[i].id);
				}
				cartDel(ids).then(res => {
					that.$util.Tips({
						title: '清除成功'
					});
					that.$set(that.cartList, 'invalid', []);
				}).catch(res => {

				});
			}
		},
		onReachBottom() {
			let that = this;
			if (that.loadend) {
				that.getInvalidList();
			} else {
				that.getCartList();
			}
			if (that.cartList.valid.length == 0 && that.cartList.invalid.length == 0) {
				that.getHostProduct();
			}
		}
	}
</script>

<style scoped lang="scss">
	.shoppingCart{
		/* #ifdef H5 */
		// padding-bottom: 0;
		// padding-bottom: constant(safe-area-inset-bottom);  
		// padding-bottom: env(safe-area-inset-bottom);
		/* #endif */
	}
	.shoppingCart .labelNav {
		height: 76rpx;
		padding: 0 30rpx;
		font-size: 22rpx;
		color: #8c8c8c;
		position: fixed;
		left: 0;
		width: 100%;
		box-sizing: border-box;
		background-color: #f5f5f5;
		z-index: 5;
		top: 0;
	}

	.shoppingCart .labelNav .item .iconfont {
		font-size: 25rpx;
		margin-right: 10rpx;
	}

	.shoppingCart .nav {
		width: 100%;
		height: 80rpx;
		background-color: #fff;
		padding: 0 30rpx;
		box-sizing: border-box;
		font-size: 28rpx;
		color: #282828;
		position: fixed;
		left: 0;
		z-index: 5;
		top: 76rpx;
	}

	.shoppingCart .nav .num {
		margin-left: 12rpx;
	}

	.shoppingCart .nav .administrate {
		font-size: 26rpx;
		color: #282828;
		width: 110rpx;
		height: 46rpx;
		border-radius: 6rpx;
		border: 1px solid #868686;
	}

	.shoppingCart .noCart {
		margin-top: 171rpx;
		background-color: #fff;
		padding-top: 0.1rpx;
	}

	.shoppingCart .noCart .pictrue {
		width: 414rpx;
		height: 336rpx;
		margin: 78rpx auto 56rpx auto;
	}

	.shoppingCart .noCart .pictrue image {
		width: 100%;
		height: 100%;
	}

	.shoppingCart .list {
		margin-top: 171rpx;
	}

	.shoppingCart .list .item {
		padding: 25rpx 30rpx;
		background-color: #fff;
		margin-bottom: 15rpx;
	}

	.shoppingCart .list .item .picTxt {
		width: 627rpx;
		position: relative;
	}

	.shoppingCart .list .item .picTxt .pictrue {
		width: 160rpx;
		height: 160rpx;
	}

	.shoppingCart .list .item .picTxt .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.shoppingCart .list .item .picTxt .text {
		width: 444rpx;
		font-size: 28rpx;
		color: #282828;
	}

	.shoppingCart .list .item .picTxt .text .reColor {
		color: #999;
	}

	.shoppingCart .list .item .picTxt .text .reElection {
		margin-top: 20rpx;
	}

	.shoppingCart .list .item .picTxt .text .reElection .title {
		font-size: 24rpx;
	}

	.shoppingCart .list .item .picTxt .text .reElection .reBnt {
		width: 120rpx;
		height: 46rpx;
		border-radius: 23rpx;
		font-size: 26rpx;
	}

	.shoppingCart .list .item .picTxt .text .infor {
		font-size: 24rpx;
		color: #868686;
		margin-top: 16rpx;
	}

	.shoppingCart .list .item .picTxt .text .money {
		font-size: 32rpx;
		color: #282828;
		margin-top: 28rpx;
	}

	.shoppingCart .list .item .picTxt .carnum {
		height: 47rpx;
		position: absolute;
		bottom: 7rpx;
		right: 0;
	}

	.shoppingCart .list .item .picTxt .carnum view {
		border: 1rpx solid #a4a4a4;
		width: 66rpx;
		text-align: center;
		height: 100%;
		line-height: 40rpx;
		font-size: 28rpx;
		color: #a4a4a4;
	}

	.shoppingCart .list .item .picTxt .carnum .reduce {
		border-right: 0;
		border-radius: 3rpx 0 0 3rpx;
	}

	.shoppingCart .list .item .picTxt .carnum .reduce.on {
		border-color: #e3e3e3;
		color: #dedede;
	}

	.shoppingCart .list .item .picTxt .carnum .plus {
		border-left: 0;
		border-radius: 0 3rpx 3rpx 0;
	}

	.shoppingCart .list .item .picTxt .carnum .num {
		color: #282828;
	}

	.shoppingCart .invalidGoods {
		background-color: #fff;
	}

	.shoppingCart .invalidGoods .goodsNav {
		width: 100%;
		height: 66rpx;
		padding: 0 30rpx;
		box-sizing: border-box;
		font-size: 28rpx;
		color: #282828;
	}

	.shoppingCart .invalidGoods .goodsNav .iconfont {
		color: #424242;
		font-size: 28rpx;
		margin-right: 17rpx;
	}

	.shoppingCart .invalidGoods .goodsNav .del {
		font-size: 26rpx;
		color: #999;
	}

	.shoppingCart .invalidGoods .goodsNav .del .icon-shanchu1 {
		color: #999;
		font-size: 33rpx;
		vertical-align: -2rpx;
		margin-right: 8rpx;
	}

	.shoppingCart .invalidGoods .goodsList .item {
		padding: 20rpx 30rpx;
		border-top: 1rpx solid #f5f5f5;
	}

	.shoppingCart .invalidGoods .goodsList .item .invalid {
		font-size: 22rpx;
		color: #fff;
		width: 70rpx;
		height: 36rpx;
		background-color: #aaa;
		border-radius: 3rpx;
		text-align: center;
		line-height: 36rpx;
	}

	.shoppingCart .invalidGoods .goodsList .item .pictrue {
		width: 140rpx;
		height: 140rpx;
	}

	.shoppingCart .invalidGoods .goodsList .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6rpx;
	}

	.shoppingCart .invalidGoods .goodsList .item .text {
		width: 433rpx;
		font-size: 28rpx;
		color: #999;
		height: 140rpx;
	}

	.shoppingCart .invalidGoods .goodsList .item .text .name {
		width: 100%;
	}

	.shoppingCart .invalidGoods .goodsList .item .text .infor {
		font-size: 24rpx;
	}

	.shoppingCart .invalidGoods .goodsList .item .text .end {
		font-size: 26rpx;
		color: #bbb;
	}

	.shoppingCart .footer {
		z-index: 9;
		width: 100%;
		height: 96rpx;
		background-color: #fafafa;
		position: fixed;
		padding: 0 30rpx;
		box-sizing: border-box;
		border-top: 1rpx solid #eee;
		bottom: calc(100rpx+ constant(safe-area-inset-bottom));///兼容 IOS<11.2/
		bottom: calc(100rpx + env(safe-area-inset-bottom));///兼容 IOS>11.2/
	}

	.shoppingCart .footer .checkAll {
		font-size: 28rpx;
		color: #282828;
		margin-left: 16rpx;
	}

	// .shoppingCart .footer checkbox .wx-checkbox-input{background-color:#fafafa;}
	.shoppingCart .footer .money {
		font-size: 30rpx;
	}

	.shoppingCart .footer .placeOrder {
		color: #fff;
		font-size: 30rpx;
		width: 226rpx;
		height: 70rpx;
		border-radius: 50rpx;
		text-align: center;
		line-height: 70rpx;
		margin-left: 22rpx;
	}

	.shoppingCart .footer .button .bnt {
		font-size: 28rpx;
		color: #999;
		border-radius: 50rpx;
		border: 1px solid #999;
		width: 160rpx;
		height: 60rpx;
		text-align: center;
		line-height: 60rpx;
	}

	.shoppingCart .footer .button form~form {
		margin-left: 17rpx;
	}
</style>
<style>
/* 	@supports (bottom: constant(safe-area-inset-bottom)) or (bottom: env(safe-area-inset-bottom)){
	.shoppingCart .footer{
	bottom: calc(var(--window-bottom) + constant(safe-area-inset-bottom));
	bottom: calc(var(--window-bottom) + env(safe-area-inset-bottom));
	}
	} */
</style>
