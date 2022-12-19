<template>
	<view>
		<view class="default" v-if="isIframe && !spikeList.length">
			<text>{{$t(`秒杀模块，暂无数据`)}}</text>
		</view>
		<view class="seckill" v-if="spikeList.length && isShow && !isIframe">
			<view class="title acea-row row-between-wrapper">
				<view class="acea-row row-middle">
					<view class="name">{{$t(`限时秒杀`)}}</view>
					<view class="point">{{point}} {{$t(`场`)}}</view>
					<countDown :is-day="false" :tip-text="' '" :day-text="' '" :hour-text="' : '" :minute-text="' : '" :second-text="' '"
					 :datatime="datatime"></countDown>
				</view>
				<navigator url="/pages/activity/goods_seckill/index" hover-class="none" class="more acea-row row-center-wrapper">{{$t(`更多`)}}<text class="iconfont icon-jiantou"></text></navigator>
			</view>
			<view class="conter">
				<scroll-view scroll-x="true" style="white-space: nowrap; vertical-align: middle;" show-scrollbar="false">
					<view class="itemCon" v-for="(item, index) in spikeList" :key="index" @click="goDetail(item)">
						<view class="item">
							<view class="pictrue">
								<image :src="item.image"></image>
							</view>
							<view class="name line1">{{item.title}}</view>
							<view class="money">
								<view class="x_money">{{$t(`￥`)}}<text class="num">{{item.price}}</text></view>
								<view class="y_money">{{$t(`￥`)}}{{item.ot_price}}</view>
							</view>
						</view>
					</view>
				</scroll-view>
			</view>
		</view>
		<view class="seckill" v-if="spikeList.length && isIframe">
			<view class="title acea-row row-between-wrapper">
					<view class="acea-row row-middle">
						<view class="name">{{$t(`限时秒杀`)}}</view>
						<view class="point">{{point}} {{$t(`场`)}}</view>
						<countDown :is-day="false" :tip-text="' '" :day-text="' '" :hour-text="' : '" :minute-text="' : '" :second-text="' '"
						 :datatime="datatime"></countDown>
					</view>
					<navigator class="more acea-row row-center-wrapper">{{$t(`更多`)}}<text class="iconfont icon-jiantou"></text></navigator>
				</view>
				<view class="conter">
					<scroll-view scroll-x="true" style="white-space: nowrap; vertical-align: middle;" show-scrollbar="false">
						<view class="itemCon" v-for="(item, index) in spikeList" :key="index" @click="goDetail(item)">
							<view class="item">
								<view class="pictrue">
									<image :src="item.image"></image>
								</view>
								<view class="name line1">{{item.title}}</view>
								<view class="money">
									<view class="x_money">{{$t(`￥`)}}<text class="num">{{item.price}}</text></view>
									<view class="y_money">{{$t(`￥`)}}{{item.ot_price}}</view>
								</view>
							</view>
						</view>
					</scroll-view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	let app = getApp();
	import {
		goPage
	} from '@/libs/order.js';
	import { getHomeProducts } from '@/api/store.js';
	import countDown from '@/components/countDown';
	export default {
		name: 'seckill',
		components: {
			countDown
		},
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			}
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
				datatime: 0,
				point: "",
				spikeList: [],
				name:this.$options.name,//component组件固定写法获取当前name；
				isIframe:app.globalData.isIframe,//判断是前台还是后台；
				isShow:true,
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
					that.spikeList = res.data.list;
					that.point = res.data.time;
					that.datatime = res.data.stop;
				}).catch(err => {
					that.$util.Tips({ title: err });
				});
			},
			goDetail(item){
				goPage(item).then(res=>{
					uni.navigateTo({
						url: `/pages/activity/goods_seckill_details/index?id=${item.id}&time=${this.datatime}&status=1`
					})
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
	.seckill {
		width: 690rpx;
		height: 410rpx;
		margin: 0 auto;
		padding-top: 35rpx;
		background-color: $uni-bg-color;
		// border-top:1rpx solid #eee;

		.title {
			.name{
				font-size: 32rpx;
				color: #282828;
				font-weight: bold;
			}

			// .lines {
			// 	width: 1rpx;
			// 	height: 24rpx;
			// 	background-color: #1DB0FC;
			// 	opacity: 0.6;
			// 	margin-left: 16rpx;
			// }

			.point {
				font-size: 30rpx;
				font-weight: bold;
				color: #fc4141;
				margin-left: 16rpx;
			}

			/deep/.time {
				font-size: 24rpx;

				.styleAll {
					width: 35rpx;
					height: 35rpx;
					background-color: rgba(252, 60, 62, 0.09);
					border-radius: 6rpx;
					color: rgb(233, 51, 35);
					text-align: center;
				}

				.red {
					&~.red {
						color: #333333;
						padding: 0 4rpx;
					}
				}
			}

			.more {
				font-size: 26rpx!important;
				color: #333;
				.iconfont {
					    margin-left: 8rpx;
					    font-size: 26rpx!important;
					    vertical-align: 2rpx;
				}
			}
		}

		.conter {
			width: 100%;
			height: 320rpx;
			background-color: #FFFFFF;
			border-radius: 12px;
			margin-top: 26rpx;

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