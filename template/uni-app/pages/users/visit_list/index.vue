<template>
  <!-- 浏览记录 -->
	<view>
		<view class="record" :style="colorStyle" v-if="visitList.length">
			<view class="nav acea-row row-between-wrapper">
				<view class="left">{{$t(`共`)}} <text class="num">{{count}}</text>{{$t(`件商品`)}}</view>
				<view class="font-num" v-if="!isShowChecked" @click="switchTap">{{$t(`管理`)}}</view>
				<view v-else @click="switchTap">{{$t(`取消`)}}</view>
			</view>
			<view class="list">
				<checkbox-group @change="checkboxChange">
					<view class="item" v-for="(item,index) in visitList" :key="index">
						<view class="title">
							<checkbox v-if="isShowChecked" :value="item.time" :checked="item.checked" />
							<text>{{item.time}}</text>
						</view>
						<checkbox-group @change="(e)=>{picCheckbox(e,index)}">
							<view class="picList acea-row row-middle">
								<view class="picTxt" v-for="(j,jindex) in item.picList" :key="jindex" @click.stop="goDetails(j)">
									<view class="pictrue">
										<image :src="j.image"></image>
                      <!-- <lazyLoad :src="j.image" width="100%" height="100%"></lazyLoad> -->
										<checkbox v-if="isShowChecked" :value="(j.id).toString()" :checked="j.checked" class="checkbox" />
										<view class="masks acea-row row-center-wrapper" v-if="!isShowChecked && j.stock<=0">
											<view class="bg">
												<view>{{$t(`已售罄`)}}</view>
											</view>
										</view>
										<view class="masks acea-row row-center-wrapper" v-if="!isShowChecked && !j.is_show">
											<view class="bg">
												<view>{{$t(`已下架`)}}</view>
											</view>
										</view>
									</view>
									<view class="money">¥<text class="num">{{j.product_price}}</text></view>
								</view>
							</view>
						</checkbox-group>
					</view>
				</checkbox-group>
				<view class='loadingicon acea-row row-center-wrapper'>
					<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
				</view>
			</view>
			<view class="footer acea-row row-between-wrapper" v-if="isShowChecked">
				<checkbox-group @change="checkboxAllChange">
					<checkbox value="all" :checked="isAllSelect" />
					<text class='checkAll'>{{$t(`全选`)}}</text>
				</checkbox-group>
				<view class="acea-row row-middle">
					<view class="bnt acea-row row-center-wrapper" @click="collect">{{$t(`收藏`)}}</view>
					<view class="bnt on acea-row row-center-wrapper" @click="del">{{$t(`删除`)}}</view>
				</view>
			</view>
		</view>
		<view class='noCommodity' v-else-if="!visitList.length && page == 2">
			<view class='pictrue'>
				<image :src="imgHost + '/statics/images/no-thing.png'"></image>
			</view>
			<view class="acea-row row-center-wrapper tip">{{$t(`暂无数据`)}}</view>
			<recommend :hostProduct="hostProduct"></recommend>
		</view>
	</view>
</template>
<script>
	import {
		getVisitList,
		getProductHot,
		deleteVisitList,
		collectAll
	} from '@/api/store.js';
	import {
		mapGetters
	} from "vuex";
	import {
		toLogin
	} from '@/libs/login.js';
	import recommend from '@/components/recommend';
	import home from '@/components/home';
	import colors from '@/mixins/color.js';
	import {HTTP_REQUEST_URL} from '@/config/app';
	export default {
		components: {
			recommend,
			home,
		},
		mixins: [colors],
		data() {
			return {
				isShowChecked: 0,
				count: 0,
				times: [],
				isAllSelect: false,
				hostProduct: [],
				loadTitle: this.$t(`加载更多`),
				loading: false,
				loadend: false,
				visitList: [],
				limit: 21,
				page: 1,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				hotScroll: false,
				hotPage: 1,
				hotLimit: 10,
				isItemAll: [],
				imgHost:HTTP_REQUEST_URL 
			};
		},
		computed: mapGetters(['isLogin']),
		onLoad() {
			if (this.isLogin) {
				this.loadend = false;
				this.page = 1;
				this.visitList = [];
				this.get_user_visit_list();
				this.get_host_product();
			} else {
				toLogin();
			}
		},
		onShow() {
			uni.removeStorageSync('form_type_cart');
			this.times = [];
			this.loadend = false;
			this.page = 1;
			this.visitList = [];
			this.get_user_visit_list();
		},
		methods: {
			goDetails(item){
				if(this.isShowChecked || !item.is_show) return false;
				uni.navigateTo({
					url: '/pages/goods_details/index?id=' + item.product_id
				})
			},
			switchTap(){
				this.isShowChecked = !this.isShowChecked;
			},
			collect(){
				let ids = [];
				this.visitList.forEach(item=>{
					item.picList.forEach(j=>{
						if(j.checked){
							ids.push(j.product_id)
						}
					})
				})
				if(!ids.length){
					return this.$util.Tips({
						title: '请选择收藏商品'
					});
				}
				let str = ids.join(',');
				collectAll(str).then(res=>{
					return this.$util.Tips({
						title: res.msg
					});
				})
			},
			del(){
				let ids = [];
				this.visitList.forEach(item=>{
					item.picList.forEach(j=>{
						if(j.checked){
							ids.push(j.product_id)
						}
					})
				})
				if(!ids.length){
					return this.$util.Tips({
						title: '请选择删除商品'
					});
				}
				deleteVisitList({ids}).then(res=>{
					this.times = [];
					this.loadend = false;
					this.page = 1;
					this.$set(this, 'visitList', []);
					this.get_user_visit_list();
					return this.$util.Tips({
						title: res.msg
					});
				})
			},
			picCheckbox(event, index) {
				let that = this,
					picTime = event.detail.value;
				that.visitList[index].picList.forEach(j => {
					if (picTime.indexOf(j.id + '') !== -1) {
						j.checked = true;
					} else {
						j.checked = false;
					}
				})
				if(that.visitList[index].picList.length == picTime.length){
					that.visitList[index].checked = true;
				}else{
					that.visitList[index].checked = false;
				}
				let visitObj = [];
				that.visitList.forEach(item=>{
					if(item.checked){
						visitObj.push(item.time)
					}else{
						if(visitObj.indexOf(item.time) !== -1){
               visitObj.remove(item.time);
						}
					}
				})
				if(visitObj.length == that.visitList.length){
					that.isAllSelect = true;
				}else{
					that.isAllSelect = false;
				}
			},
			checkboxChange(event) {
				let that = this,
					timeList = event.detail.value;
				that.isItemAll = timeList;
				that.visitList.forEach((item, index) => {
					if (timeList.indexOf(item.time) !== -1) {
						item.checked = true;
					} else {
						item.checked = false;
					}
					item.picList.forEach(j => {
						if (item.checked) {
							j.checked = true;
						} else {
							j.checked = false;
						}
					})
				})
				if (timeList.length === that.visitList.length) {
					that.isAllSelect = true;
				} else {
					that.isAllSelect = false;
				}
			},
			forGoods(val) {
				let that = this;
				if (!that.visitList.length) return
				that.visitList.forEach((item) => {
					if (val) {
						item.checked = true;
					} else {
						item.checked = false;
					}
					item.picList.forEach(j => {
						if (val) {
							j.checked = true;
						} else {
							j.checked = false;
						}
					})
				})
			},
			checkboxAllChange(event) {
				let value = event.detail.value;
				if (value.length) {
					this.isAllSelect = true;
					this.forGoods(1)
				} else {
					this.isAllSelect = false;
					this.forGoods(0)
				}
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			/**
			 * 获取记录产品
			 */
			get_user_visit_list: function() {
				let that = this;
				if (this.loading) return;
				if (this.loadend) return;
				that.loading = true;
				that.loadTitle = "";
				getVisitList({
					page: that.page,
					limit: that.limit
				}).then(res => {
					this.count = res.data.count;
					for (let i = 0; i < res.data.time.length; i++) {
						if (this.times.indexOf(res.data.time[i]) == -1) {
							this.times.push(res.data.time[i])
							this.visitList.push({
								time: res.data.time[i],
								picList: []
							})
						}
					}
					for (let x = 0; x < this.times.length; x++) {
						this.visitList[x].checked = this.isAllSelect ? true : false;
						for (let j = 0; j < res.data.list.length; j++) {
							if (this.times[x] === res.data.list[j].time_key) {
								if (this.isAllSelect) {
									res.data.list[j].checked = true;
								} else {
									res.data.list[j].checked = false;
								}
								this.visitList[x].picList.push(res.data.list[j])
							}
						}
					}
					let loadend = res.data.list.length < that.limit;
					that.loadend = loadend;
					that.loadTitle = loadend ? that.$t(`没有更多内容啦~`) : that.$t(`加载更多`);
					that.page = that.page + 1;
					that.loading = false;
				}).catch(err => {
					that.loading = false;
					that.loadTitle = that.$t(`加载更多`);
				});
			},
			/**
			 * 获取我的推荐
			 */
			get_host_product: function() {
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
			}
		},
		onReachBottom() {
			if (this.visitList.length) {
				this.get_user_visit_list();
			} else {
				this.get_host_product();
			}
		},
		// 滚动监听
		onPageScroll(e) {
			// 传入scrollTop值并触发所有easy-loadimage组件下的滚动监听事件
			uni.$emit('scroll');
		},
	}
</script>
<style lang="scss">
	page {
		background-color: #fff;
	}
	
	.noCommodity .pictrue{
		width: 414rpx;
		height: 304rpx;
		margin: 30rpx auto 0 auto;
		.tip{
			color: #bbb;
			font-size: 25rpx;
		}
	}

	.record .pictrue /deep/checkbox .uni-checkbox-input {
		background-color: rgba(0, 0, 0, 0.16);
	}
	
	.record .pictrue /deep/checkbox .wx-checkbox-input {
		background-color: rgba(0, 0, 0, 0.16);
	}

	.record {
		.footer {
			box-sizing: border-box;
			padding: 0 30rpx;
			width: 100%;
			height: 96rpx;
			box-shadow: 0px -4px 20px 0px rgba(0, 0, 0, 0.06);
			background-color: #fff;
			position: fixed;
			bottom: 0;
			z-index: 30;
			height: calc(96rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
			height: calc(96rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
			padding-bottom: constant(safe-area-inset-bottom); ///兼容 IOS<11.2/
			padding-bottom: env(safe-area-inset-bottom); ///兼容 IOS>11.2/

			.bnt {
				width: 160rpx;
				height: 60rpx;
				border-radius: 30rpx;
				border: 1rpx solid #ccc;
				color: #666666;

				&.on {
					border: 1rpx solid var(--view-theme);
					margin-left: 16rpx;
					color: var(--view-theme);
				}
			}
		}

		.nav {
			border-bottom: 1rpx solid #eee;
			color: #999999;
			font-size: 28rpx;
			height: 74rpx;
			padding: 0 30rpx;

			.left {
				color: #333;

				.num {
					color: var(--view-theme);
					margin: 0 10rpx;
				}
			}
		}

		.list {
			padding-top: 32rpx;
			padding-bottom: 96rpx;

			.item {
				.title {
					padding: 0 30rpx;
					margin-bottom: 34rpx;
					font-size: 34rpx;
					font-weight: 600;
				}

				.picList {
					padding: 0 30rpx 0 12rpx;

					.picTxt {
						margin-left: 18rpx;
						margin-bottom: 48rpx;

						.pictrue {
							width: 218rpx;
							height: 218rpx;
							border-radius: 10rpx;
							position: relative;

							image {
								width: 100%;
								height: 100%;
								border-radius: 10rpx;
							}
							
							.masks {
								position: absolute;
								top: 0;
								left: 0;
								right: 0;
								bottom: 0;
								background: rgba(0, 0, 0, 0.2);
								border-radius: 10rpx;
							
								.bg {
									width: 110rpx;
									height: 110rpx;
									background: #000000;
									opacity: 0.6;
									color: #fff;
									font-size: 22rpx;
									border-radius: 50%;
									padding: 22rpx 0;
									text-align: center;
								}
							}

							.checkbox {
								position: absolute;
								right: 10rpx;
								top: 14rpx;
							}
						}

						.money {
							font-size: 24rpx;
							color: var(--view-priceColor);
							font-weight: 600;
							margin-top: 15rpx;

							.num {
								font-size: 32rpx;
								margin-left: 6rpx;
							}
						}
					}
				}
			}
		}
	}
</style>
