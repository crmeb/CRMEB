<template>
	<view class="productList">
		<view class='list acea-row row-between-wrapper' v-if="isShow && bastList.length">
			<view class='item' v-for="(item,index) in bastList" :key="index" @click="goDetail(item)">
				<view class='pictrue'>
					<image :src='item.image'></image>
					<span class="pictrue_log_class pictrue_log_big" v-if="item.activity && item.activity.type === '1'">秒杀</span>
					<span class="pictrue_log_class pictrue_log_big" v-if="item.activity && item.activity.type === '2'">砍价</span>
					<span class="pictrue_log_class pictrue_log_big" v-if="item.activity && item.activity.type === '3'">拼团</span>
				</view>
				<view class='text'>
					<view class='name line1'>{{item.store_name}}</view>
					<view class='money font-color'>￥<text class='num'>{{item.price}}</text></view>
					<view class='vip acea-row row-between-wrapper'>
						<view class='vip-money' v-if="item.vip_price && item.vip_price > 0 && item.base">￥{{item.vip_price}}
							<image src='../../../static/images/jvip.png' class="jvip"></image>
						</view>
						<view class='vip-money' v-if="item.vip_price && item.vip_price > 0 && item.is_vip">￥{{item.vip_price}}
							<image src='../../../static/images/vip.png'></image>
						</view>
						<view>已售{{item.sales}}{{item.unit_name}}</view>
					</view>
				</view>
			</view>
		</view>
		<view class='list acea-row row-between-wrapper' v-if="!isShow && isIframe && bastList.length">
			<view class='item' v-for="(item,index) in bastList" :key="index" @click="goDetail(item)">
				<view class='pictrue'>
					<image :src='item.image'></image>
					<span class="pictrue_log_class pictrue_log_big" v-if="item.activity && item.activity.type === '1'">秒杀</span>
					<span class="pictrue_log_class pictrue_log_big" v-if="item.activity && item.activity.type === '2'">砍价</span>
					<span class="pictrue_log_class pictrue_log_big" v-if="item.activity && item.activity.type === '3'">拼团</span>
				</view>
				<view class='text'>
					<view class='name line1'>{{item.store_name}}</view>
					<view class='money font-color'>￥<text class='num'>{{item.price}}</text></view>
					<view class='vip acea-row row-between-wrapper'>
						<view class='vip-money' v-if="item.vip_price && item.vip_price > 0 && item.base">￥{{item.vip_price}}
							<image src='../../../static/images/jvip.png' class="jvip"></image>
						</view>
						<view class='vip-money' v-if="item.vip_price && item.vip_price > 0 && item.is_vip">￥{{item.vip_price}}
							<image src='../../../static/images/vip.png'></image>
						</view>
						<view>已售{{item.sales}}{{item.unit_name}}</view>
					</view>
				</view>
			</view>
		</view>
		<block v-if="isIframe && !bastList.length">
			<view class="empty-img">精品推荐，暂无数据</view>
		</block>
	</view>
</template>

<script>
	let app = getApp()
	import {
		mapState
	} from 'vuex'
	import { goShopDetail,goPage } from '@/libs/order.js'
	import { getHomeProducts } from '@/api/store.js';
	import goodLists from '@/components/goodList/index.vue'
	export default {
		name: 'goodList',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			}
		},
		components: {
			goodLists
		},
		created() {
			
		},
		mounted() {
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if(nVal){
						this.isShow = nVal.isShow.val;
						this.selectType = nVal.tabConfig.tabVal;
						this.$set(this, 'selectId', nVal.selectConfig.activeValue);
						this.$set(this, 'type', nVal.selectSortConfig.activeValue);
						this.salesOrder = nVal.goodsSort.type == 1 ? 'desc' : '';
						this.newsOrder = nVal.goodsSort.type == 2 ? 'news' : '';
						this.ids = nVal.ids?nVal.ids.join(','):'';
						this.numConfig = nVal.numConfig.val;
						this.productslist();
					}
				}
			}
		},
		data() {
			return {
				circular: true,
				interval: 3000,
				duration: 500,
				bastList: [],
				name: this.$options.name,
				isShow: true,
				isIframe: app.globalData.isIframe,
				selectType:0,
				selectId: '',
				salesOrder:'',
				newsOrder:'',
				ids:'',
				page: 1,
				limit: this.$config.LIMIT,
				type: '',
				numConfig:0
			}
		},
		methods: {
			// 产品列表
			productslist: function() {
				let that = this;
				let data = {};
				if (that.selectType) {
					data = {
						page: that.page,
						limit: that.limit,
						type: that.type,
						ids: that.ids,
						selectType: that.selectType
					}
				} else {
					data = {
						page: that.page,
						limit: that.numConfig<=that.limit?that.numConfig:that.limit,
						type: that.type,
						newsOrder: that.newsOrder,
						salesOrder: that.salesOrder,
						selectId: that.selectId,
						selectType: that.selectType
					}
				}
				getHomeProducts(data).then(res => {
					that.bastList = res.data.list;
				}).catch(err => {
					that.$util.Tips({ title: err });
				});
			},
			goDetail(item){
				goPage().then(res=>{
					goShopDetail(item,this.uid).then(res=>{
						uni.navigateTo({
							url:`/pages/goods_details/index?id=${item.id}`
						})
					})
				})
			}
		}
	}
</script>

<style lang="scss">
	.productList .list {
		padding: 0 30rpx;
	}
	
	.productList .list .item {
		width: 335rpx;
		margin-top: 20rpx;
		background-color: #fff;
		border-radius: 20rpx;
		border:1rpx solid #eee;
	}
	
	.productList .list .item .pictrue {
		position: relative;
		width: 100%;
		height: 335rpx;
	}
	
	.productList .list .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 20rpx 20rpx 0 0;
	}
	
	.productList .list .item .text {
		padding: 20rpx 17rpx 26rpx 17rpx;
		font-size: 30rpx;
		color: #222;
	}
	
	.productList .list .item .text .money {
		font-size: 26rpx;
		font-weight: bold;
		margin-top: 8rpx;
	}
	
	.productList .list .item .text .money .num {
		font-size: 34rpx;
	}
	
	.productList .list .item .text .vip {
		font-size: 22rpx;
		color: #aaa;
		margin-top: 7rpx;
	}
	
	.productList .list .item .text .vip .vip-money {
		font-size: 24rpx;
		color: #282828;
		font-weight: bold;
	}
	
	.productList .list .item .text .vip .vip-money image {
		width: 46rpx;
		height: 21rpx;
		margin-left: 4rpx;
	}
	.empty-img {
		width: 690rpx;
		height: 300rpx;
		border-radius: 14rpx;
		margin: 26rpx auto 0 auto;
		background-color: #ccc;
		text-align: center;
		line-height: 300rpx;
		.iconfont{
			font-size: 50rpx;
		}
	}
</style>