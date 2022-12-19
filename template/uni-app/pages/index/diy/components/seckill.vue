<template>
	<view v-show="!isSortType" v-if="spikeList.length>0">
		<view class="spike-box" :class="conStyle?'borderRadius20':''" :style="{background:bgColor,margin:'0 '+prConfig*2+'rpx',marginTop:mbConfig*2+'rpx'}">
			<view class="hd">
				<view class="left">
					<image :src="imgUrl" class="icon" v-if="imgUrl"></image>
					<image src="/static/images/spike-icon-002.gif" class="icon" v-else></image>
					
					<view class="name">{{$t(`限时秒杀`)}}</view>
					<!-- <image src="/static/images/spike-icon-001.png" class="title"></image> -->
					<countDown :is-day="false" :tip-text="' '" :day-text="' '" :hour-text="' : '" :minute-text="' : '" :second-text="' '"
					 :datatime="datatime" :bgColor="countDownColor" :colors="themeColor"></countDown>
				</view>
				<navigator class="more" url="/pages/activity/goods_seckill/index">{{$t(`更多`)}} <text class="iconfont icon-jiantou"
					 hover-class='none'></text></navigator>
			</view>
			<view class="spike-wrapper">
				<scroll-view scroll-x="true" style="white-space: nowrap; display: flex" show-scrollbar="false">
					<navigator class="spike-item" :style="'margin-right:'+ lrConfig*2 +'rpx;'" v-for="(item,index) in spikeList" :key="index" :url="'/pages/activity/goods_seckill_details/index?id='+item.id+'&time='+datatime+'&status=1'"
					 hover-class='none'>
						<view class="img-box">
							<image :src="item.image" mode="aspectFill"></image>
							<view v-if="discountShow" class="msg flex-aj-center" :style="'color:'+ themeColor +';border-color:'+ themeColor +';'">{{item.discountNum}}{{$t(`折`)}}</view>
						</view>
						<view class="info">
							<view v-if="titleShow" class="name line1">{{item.title}}</view>
							<view class="price-box">
								<text v-if="seckillShow" class="tips" :style="'background-color:'+ themeColor +';'">{{$t(`抢`)}}</text>
								<text v-if="priceShow" class="price" :style="'color:'+themeColor+';'"><text>{{$t(`￥`)}}</text>{{item.price}}</text>
							</view>
						</view>
					</navigator>
				</scroll-view>
			</view>
		</view>
	</view>
	
</template>

<script>
	import countDown from '@/components/countDown';
	import {
		getSeckillIndexTime,
		getSeckillList
	} from '@/api/activity.js';
	export default {
		name: 'seckill',
		components:{
			countDown
		},
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isSortType:{
				type: String | Number,
				default:0
			}
		},
		data() {
			return {
				datatime:'',
				spikeList: [],
				countDownColor: this.dataConfig.countDownColor.color[0].item,
				themeColor: this.dataConfig.themeColor.color[0].item,
				numberConfig: this.dataConfig.numberConfig.val,
				lrConfig:this.dataConfig.lrConfig.val,
				mbConfig:this.dataConfig.mbConfig.val,
				imgUrl:this.dataConfig.imgConfig.url,
				priceShow:this.dataConfig.priceShow.val,
				discountShow:this.dataConfig.discountShow.val,
				titleShow:this.dataConfig.titleShow.val,
				seckillShow:this.dataConfig.seckillShow.val,
				conStyle:this.dataConfig.conStyle.type,
				prConfig:this.dataConfig.prConfig.val,
				bgColor:this.dataConfig.bgColor.color[0].item
			};
		},
		created() {
		},
		mounted() {
			this.getSeckillIndexTime();
		},
		methods: {
			getSeckillIndexTime() {
				let limit = this.$config.LIMIT;
				let params = {
					page: 1,
					limit: this.numberConfig>=limit?limit:this.numberConfig,
					type: 'index'
				}
				getSeckillIndexTime().then(res => {
					if (res.data.seckillTimeIndex === -1) {
						return;
					}
					this.datatime = res.data.seckillTime[res.data.seckillTimeIndex].stop
					let id = res.data.seckillTime[res.data.seckillTimeIndex].id
					getSeckillList(id, params).then(({
						data
					}) => {
						data.forEach((item) => {
							let num = 0
							if (item.price > 0 && item.ot_price > 0) num = ((parseFloat(item.price) / parseFloat(item.ot_price)).toFixed(
								2))
							item.discountNum = this.$util.$h.Mul(num, 10)
						})
						this.spikeList = data
					})
				})
			},
		}
	}
</script>

<style lang="scss">
	.spike{
		padding: 20rpx;
	}
	.spike-box {
		padding: 23rpx 20rpx 18rpx 20rpx;
		background-color: #fff;
		overflow: hidden;
		box-shadow: 0px 0px 16px 3px rgba(0, 0, 0, 0.04);
		.hd {
			display: flex;
			align-items: center;
			justify-content: space-between;
	
			.left {
				display: flex;
				align-items: center;
				width: 500rpx;
				.name{
					font-size: 32rpx;
					font-weight: 600;
				}
	
				.icon {
					width: 36rpx;
					height: 36rpx;
					margin-right: 12rpx;
				}
	
				.title {
					width: 134rpx;
					height: 33rpx;
				}
			}
	
			.more {
				font-size: 26rpx;
				color: #999;
	
				.iconfont {
					margin-left: 6rpx;
					font-size: 25rpx;
				}
			}
		}
	
		.spike-wrapper {
			width: 100%;
			margin-top: 27rpx;
	
			.spike-item {
				display: inline-block;
				width: 222rpx;
				background-color: #fff;
				border-radius: 16rpx;
				padding-bottom: 8rpx;
	
				.img-box {
					position: relative;
					height: 222rpx;
	
					image {
						width: 100%;
						height: 222rpx;
						border-radius: 16rpx;
					}
	
					.msg {
						position: absolute;
						left: 10rpx;
						bottom: 16rpx;
						width: 86rpx;
						height: 30rpx;
						background: rgba(255, 255, 255, 1);
						border: 1px solid rgba(255, 109, 96, 1);
						border-radius: 6rpx;
						font-size: 20rpx;
						color: $theme-color;
					}
				}
	
	
				.info {
					margin-top: 10rpx;
					padding: 0 10rpx;
	
					.name {
						font-size: 26rpx;
					}
	
					.price-box {
						display: flex;
						align-items: center;
						justify-content: start;
						margin-top: 4rpx;
	
						.tips {
							display: flex;
							align-items: center;
							justify-content: center;
							width: 28rpx;
							height: 28rpx;
							background-color: $theme-color;
							color: #fff;
							font-size: 20rpx;
							border-radius: 2px;
						}
	
						.price {
							display: flex;
							margin-left: 10rpx;
							color: $theme-color;
							font-size: 28rpx;
							font-weight: bold;
	
							text {
								font-size: 18rpx;
							}
						}
					}
				}
			}
		}
	}
</style>
