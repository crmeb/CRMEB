<template>
	<view>
		<view class="default" v-if="isIframe && !bargList.length">
			<text>{{$t(`砍价模块，暂无数据`)}}</text>
		</view>
		<view class="combination index-wrapper" v-if="bargList.length&&isShow&&!isIframe">
			<view class='title acea-row row-between-wrapper index-wrapper'>
				<view class='text'>
					<view class='name line1'>{{$t(`砍价活动`)}}</view>
					<view class='line1'>{{$t(`呼朋唤友来砍价`)}}</view>
				</view>
				<navigator class='more' url="/pages/activity/goods_combination/index" hover-class="none">{{$t(`更多`)}}<text class='iconfont icon-jiantou'></text></navigator>
			</view>
			<view class="conter">
				<scroll-view scroll-x="true" style="white-space: nowrap; vertical-align: middle;" show-scrollbar="false">
					<view class="itemCon" v-for="(item, index) in bargList" :key="index" @click="bargDetail(item)">
						<view class="item">
							<view class="pictrue">
								<image :src="item.image"></image>
							</view>
							<view class="name line1">{{item.title}}</view>
							<view class="money">
								<view class="x_money">{{$t(`￥`)}}<text class="num">{{item.min_price}}</text></view>
								<view class="y_money">{{$t(`￥`)}}{{item.ot_price}}</view>
							</view>
						</view>
					</view>
				</scroll-view>
			</view>
		</view>
		<view class="combination index-wrapper" v-if="bargList.length&&isIframe">
			<view class='title acea-row row-between-wrapper index-wrapper'>
				<view class='text'>
					<view class='name line1'>{{$t(`砍价活动`)}}</view>
					<view class='line1'>{{$t(`呼朋唤友来砍价`)}}</view>
				</view>
				<navigator class='more'>{{$t(`更多`)}}<text class='iconfont icon-jiantou'></text></navigator>
			</view>
			<view class="conter">
				<scroll-view scroll-x="true" style="white-space: nowrap; vertical-align: middle;" show-scrollbar="false">
					<view class="itemCon" v-for="(item, index) in bargList" :key="index" @click="bargDetail(item)">
						<view class="item">
							<view class="pictrue">
								<image :src="item.image"></image>
							</view>
							<view class="name line1">{{item.title}}</view>
							<view class="money">
								<view class="x_money">￥<text class="num">{{item.min_price}}</text></view>
								<view class="y_money">￥{{item.ot_price}}</view>
							</view>
						</view>
					</view>
				</scroll-view>
			</view>
		</view>
	</view>
</template>

<script>
	let app = getApp();
	import {
		toLogin
	} from '@/libs/login.js';
	import { mapGetters } from 'vuex';
	import {
		goPage
	} from '@/libs/order.js';
	import { getHomeProducts } from '@/api/store.js';
	export default {
		name: 'bargain',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			}
		},
		computed: {
			 ...mapGetters(['isLogin','uid'])
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if(nVal){
						this.isShow = nVal.isShow.val;
						this.selectType = nVal.tabConfig.tabVal;
						this.$set(this, 'selectId', nVal.selectConfig.activeValue || '');
						this.$set(this, 'type', nVal.titleInfo.type);
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
				bargList: [],
				name:this.$options.name,//component组件固定写法获取当前name；
				isIframe:app.globalData.isIframe,//判断是前台还是后台；
				isShow:true,//判断此模块是否显示；
				selectType:0,
				selectId: '',
				salesOrder:'',
				newsOrder:'',
				ids:'',
				page: 1,
				limit: this.$config.LIMIT,
				type: '',
				numConfig:0
			};
		},
		created() {
		},
		mounted() {
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
					that.bargList = res.data.list;
				}).catch(err => {
					that.$util.Tips({ title: err });
				});
			},
			bargDetail(item){
				goPage(item).then(res=>{
					if (!this.isLogin) {
						toLogin();
					} else {
						uni.navigateTo({
							url: `/pages/activity/goods_bargain_details/index?id=${item.id}&bargain=${this.uid}`
						});
					}
				}) 
			}
		}
	}
</script>

<style lang="scss">
	.default{
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
	.combination{
		width: 100%;
		// height: 288rpx;
		background-color: $uni-bg-color;
		border-radius: 14rpx;
		.conter {
			width: 690rpx;
			height: 320rpx;
			background-color: #FFFFFF;
			border-radius: 12px;
			margin: 26rpx auto 0 auto;
		
			.itemCon {
				display: inline-block;
				width: 174rpx;
				margin-right: 24rpx;
		
				.item {
					width: 100%;
		
					.pictrue {
						width: 100%;
						height: 174rpx;
						border-radius: 6rpx;
		
						image {
							width: 100%;
							height: 100%;
							border-radius: 6rpx;
						}
					}
		
					.name {
						font-size: 24rpx;
						color: #333333;
						margin-top: 10rpx;
					}
		
					.money {
						
						.y_money {
							font-size: 20rpx;
							color: #999999;
							text-decoration: line-through;
						}
		
						.x_money {
							color: #FD502F;
							font-size: 24rpx;
							font-weight: bold;
							margin-top: 3rpx;
		
							.num {
								font-size: 28rpx;
							}
						}
					}
				}
			}
		}
	}
</style>
