<template>
	<!-- #ifdef MP -->
	<view v-show="!isSortType">
		<view :style="[{'margin-top': mbConfig + 'rpx'},{'background':bg}]" v-if="liveList.length > 0" style="padding-bottom: 20rpx;">
			<view class="title-box">
				<text>{{$t(`推荐好货`)}}</text>
				<navigator hover-class="none" url="/pages/columnGoods/live_list/index" class="more">{{$t(`更多`)}}<text class="iconfont icon-jiantou"></text></navigator>
			</view>
			<!--  -->
			<block v-if="listStyle == 0">
				<view class="live-wrapper-a">
					<navigator class="live-item-a" v-for="(item,index) in liveList" :key="index" :url="'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin?room_id=' + item.room_id+'&custom_params='+custom_params" hover-class="none" :style="[{'box-shadow':`0px 1px 20px ${boxShadow}`}]">
						<view class="img-box">
							<view class="label bgblue" v-if="item.live_status == 102">
								<view class="txt">{{$t(`预告`)}}</view>
								<view class="msg">{{item.show_time}}</view>
							</view>
							<view class="label bggary" v-if="item.live_status==103">
								<image src="/static/images/live-02.png" mode="" style="width: 20rpx; height: 20rpx;"></image>
								<text>{{$t(`回放`)}}</text>
							</view>
							<view class="label bgred" v-if="item.live_status==101">
								<image src="/static/images/live-01.png" mode="" style="width: 21rpx; height: 22rpx;"></image>
								<text>{{$t(`进行中`)}}</text>
							</view>
							<image :src="item.share_img"></image>
						</view>
						<view class="info">
							<view class="title line2">{{item.name}}</view>
							<view class="people">
								<image :src="item.anchor_img" alt="">
								<text>{{item.anchor_name}}</text>
							</view>
							<view class="goods-wrapper">
								<block v-if="item.goods.length <= 3">
									<view class="goods-item" v-for="(goods,index) in item.goods" :key="index">
										<image :src="goods.cover_img" alt="">
										<text class="line1">{{$t(`￥`)}}{{goods.price}}</text>
									</view>
								</block>
								<block v-if=" item.goods.length > 3">
									<view class="goods-item" v-for="(goods,index) in item.goods" :key="index" v-if="index<2">
										<image :src="goods.cover_img" alt="">
										<text class="line1">{{$t(`￥`)}}{{goods.price}}</text>
									</view>
									<view class="goods-item">
										<image :src="item.goods[item.goods.length-1].cover_img" alt="">
										<view class="num">+{{item.goods.length}}</view>	
									</view>
								</block>
								<block v-if="item.goods.length == 0">
									<view class="empty-goods" >{{$t(`暂无商品`)}}</view>
								</block>
							</view>
						</view>
					</navigator>
				</view>
			</block>
			<block v-if="listStyle == 1">
				<view class="live-wrapper-b">
					<navigator class="live-item-b" v-for="(item,index) in liveList" :key="index" :url="'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin?room_id=' + item.room_id+'&custom_params='+custom_params" hover-class="none" :style="[{'box-shadow':`0px 1px 20px ${boxShadow}`}]">
						<view class="img-box">
							<view class="label bgblue" v-if="item.live_status == 102">
								<view class="txt">{{$t(`预告`)}}</view>
								<view class="msg">{{item.show_time}}</view>
							</view>
							<view class="label bggary" v-if="item.live_status==103">
								<image src="/static/images/live-02.png" mode="" style="width: 20rpx; height: 20rpx;"></image>
								<text>{{$t(`回放`)}}</text>
							</view>
							<view class="label bgred" v-if="item.live_status==101">
								<image src="/static/images/live-01.png" mode="" style="width: 21rpx; height: 22rpx;"></image>
								<text>{{$t(`进行中`)}}</text>
							</view>
							<image :src="item.share_img"></image>
						</view>
						<view class="info">
							<view class="title line2">{{item.name}}</view>
							<view class="people">
								<image :src="item.anchor_img" alt="">
								<text>{{item.anchor_name}}</text>
							</view>
						</view>
					</navigator>
				</view>
			</block>
			<block v-if="listStyle == 2">
				<view class="live-wrapper-a live-wrapper-c">
					<navigator class="live-item-a" v-for="(item,index) in liveList" :key="index" :url="'plugin-private://wx2b03c6e691cd7370/pages/live-player-plugin?room_id=' + item.room_id+'&custom_params='+custom_params" hover-class="none" :style="[{'box-shadow':`0px 1px 20px ${boxShadow}`}]">
						<view class="img-box">
							<view class="label bgblue" v-if="item.live_status == 102">
								<view class="txt">{{$t(`预告`)}}</view>
								<view class="msg">{{item.show_time}}</view>
							</view>  
							<view class="label bggary" v-if="item.live_status==103">
								<image src="/static/images/live-02.png" mode="" style="width: 20rpx; height: 20rpx;"></image>
								<text>{{$t(`回放`)}}</text>
							</view>
							<view class="label bgred" v-if="item.live_status==101">
								<image src="/static/images/live-01.png" mode="" style="width: 21rpx; height: 22rpx;"></image>
								<text>{{$t(`进行中`)}}</text>
							</view>
							<image :src="item.share_img"></image>
						</view>
						<view class="info">
							<view class="left">
								<view class="title line2">{{item.name}}</view>
								<view class="people">
									<image :src="item.anchor_img" alt="">
									<text>{{item.anchor_name}}</text>
								</view>
							</view>
							<view class="goods-wrapper">
								<block v-if="item.goods.length <= 2">
									<view class="goods-item" v-for="(goods,index) in item.goods" :key="index">
										<image :src="goods.cover_img" alt="">
										<text class="line1">{{$t(`￥`)}}{{goods.price}}</text>
									</view>
								</block>
								<block v-if=" item.goods.length > 2">
									<view class="goods-item" v-for="(goods,index) in item.goods" :key="index" v-if="index<1">
										<image :src="goods.cover_img" alt="">
										<text class="line1">{{$t(`￥`)}}{{goods.price}}</text>
									</view>
									<view class="goods-item">
										<image :src="item.goods[item.goods.length-1].cover_img" alt="">
										<view class="num">+{{item.goods.length}}</view>	
									</view>
								</block>
							</view>
						</view>
					</navigator>
				</view>
			</block>
		</view>
		
	</view>
	<!-- #endif -->
</template>

<script>
	import {
		getLiveList
	} from '@/api/api.js';
	export default {
		name: 'liveBroadcast',
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
				// endBg: this.dataConfig.endBg.color[0].item,
				// notBg: this.dataConfig.notBg.color[0].item,
				// playBg: this.dataConfig.playBg.color[0].item,
				listStyle: this.dataConfig.listStyle.type,
				mbConfig: this.dataConfig.mbConfig.val,
				liveList: [],
				custom_params:'',
				// bg:this.dataConfig.bg.color[0].item,
				// boxShadow: this.dataConfig.boxShadow.color[0].item,
				limit:this.dataConfig.limit.val
			};
		},
		created() {},
		mounted() {
			this.custom_params= encodeURIComponent(JSON.stringify({spid:this.$store.state.app.uid}))
			this.getLiveList();
		},
		methods: {
			// 
			getLiveList: function() {
				let limit = this.$config.LIMIT;
				getLiveList(1, this.limit == undefined ? 10 : this.limit)
					.then(res => {
						this.liveList = res.data;
					})
					.catch(res => {
					});
			}
		}
	};
</script>

<style lang="scss">
	.live-wrapper {
		position: relative;
		width: 100%;
		overflow: hidden;
		border-radius: 16rpx;

		image {
			width: 100%;
			height: 400rpx;
		}

		.live-top {
			z-index: 20;
			position: absolute;
			left: 0;
			top: 0;
			display: flex;
			align-items: center;
			justify-content: center;
			color: #fff;
			width: 180rpx;
			height: 54rpx;
			border-radius: 0rpx 0px 18rpx 0px;

			image {
				width: 30rpx;
				height: 30rpx;
				margin-right: 10rpx;
				/* #ifdef H5 */
				display: block;
				/* #endif */
			}
		}

		.live-title {
			position: absolute;
			left: 0;
			bottom: 6rpx;
			width: 100%;
			height: 70rpx;
			line-height: 70rpx;
			text-align: center;
			font-size: 30rpx;
			color: #fff;
			background: rgba(0, 0, 0, 0.35);
		}

		&.mores {
			width: 100%;

			.item {
				position: relative;
				width: 320rpx;
				display: inline-block;
				border-radius: 16rpx;
				overflow: hidden;
				margin-right: 20rpx;

				image {
					width: 320rpx;
					height: 180rpx;
					border-radius: 16rpx;
				}

				.live-title {
					height: 40rpx;
					line-height: 40rpx;
					text-align: center;
					font-size: 22rpx;
				}

				.live-top {
					width: 120rpx;
					height: 36rpx;
					font-size: 22rpx;

					image {
						width: 20rpx;
						height: 20rpx;
					}
				}
			}
		}
	}
	.live-wrapper-a{
		padding: 0rpx 20rpx 0;
		.live-item-a{
			display: flex;
			background: #fff;
			margin-bottom: 20rpx;
			border-radius: 16rpx;
			overflow: hidden;
			&:last-child{
				margin-bottom: 0;
			}
			.img-box{
				position: relative;
				width: 340rpx;
				height: 270rpx;
				image{
					width: 100%;
					height: 100%;
				}
			}
			.info{
				flex: 1;
				display: flex;
				flex-direction: column;
				justify-content: space-between;
				padding: 15rpx 20rpx;
				.title{
					font-size: 30rpx;
					color: #333;
				}
				.people{
					display: flex;
					align-items: center;
					color: #999;
					font-size: 24rpx;
					margin-top: 10rpx;
					image{
						width: 32rpx;
						height: 32rpx;
						border-radius: 50%;
						margin-right: 10rpx;
					}
				}
				.goods-wrapper{
					display: flex;
					.goods-item{
						position: relative;
						width: 96rpx;
						height: 96rpx;
						margin-right: 20rpx;
						overflow: hidden;
						border-radius: 16rpx;
						&:last-child{
							margin-right: 0;
						}
						image{
							width: 100%;
							height: 100%;
							border-radius: 16rpx;
						}
						.bg{
							position: absolute;
							left: 0;
							top: 0;
							width: 100%;
							height: 100%;
							border-radius: 16rpx;
							background: rgba(0, 0, 0, 0.3);
						}
						text{
							position: absolute;
							left: 0;
							bottom: 0;
							width: 100%;
							height: 60rpx;
							line-height: 70rpx;
							color: #fff;
							background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.75) 100%);
						}
						.num{
							display: flex;
							align-items: center;
							justify-content: center;
							position: absolute;
							left: 0;
							top: 0;
							width: 100%;
							height: 100%;
							background: rgba(0, 0, 0, 0.3);
							color: #fff;
							font-size: 28rpx;
						}
					}
				}
				.empty-goods{
					width: 96rpx;
					height: 96rpx;
					border-radius: 6rpx;
					background-color: #B2B2B2;
					color: #fff;
					font-size: 20rpx;
					text-align: center;
					line-height: 96rpx;
				}
			}
		}
		&.live-wrapper-c{
			.live-item-a{
				display: flex;
				flex-direction: column;
				.img-box{
					width: 100%;
					border-radius: 8px 8px 0 0;
				}
				.info{
					display: flex;
					justify-content: space-between;
					align-items: center;
					flex-direction: initial;
					.left{
						width: 69%;
					}
					.goods-wrapper{
						flex: 1;
					}
				}	
			}
		}
	}
	.live-wrapper-b{
		padding: 0rpx 20rpx 0;
		display: flex;
		justify-content: space-between;
		flex-wrap: wrap;
		.live-item-b{
			width: 345rpx;
			background-color: #fff;
			border-radius: 16rpx;
			overflow: hidden;
			margin-bottom: 20rpx;
			overflow: hidden;
			.img-box{
				position: relative;
				image{
					width: 100%;
					height: 274rpx;
				}
			}
			.info{
				display: flex;
				flex-direction: column;
				padding: 20rpx;
				.title{
					font-size: 30rpx;
					color: #333;
				}
				.people{
					display: flex;
					margin-top: 10rpx;
					color: #999;
					image{
						width: 36rpx;
						height: 36rpx;
						border-radius: 50%;
						margin-right: 10rpx;
					}
				}
			}
		}
	}
	.label{
		display: flex;
		align-items: center;
		justify-content: center;
		position: absolute;
		left: 20rpx;
		top: 20rpx;
		border-radius: 22rpx 0px 22rpx 22rpx;
		font-size: 24rpx;
		color: #fff;
		image{
			margin-right: 10rpx;
		}
		text{
			font-size: 22rpx;
		}
	}
	.bgred{
		width: 132rpx;
		height: 38rpx;
		background: linear-gradient(270deg, #F5742F 0%, #FF1717 100%)
	}
	.bggary{
		width: 108rpx;
		height: 38rpx;
		background: linear-gradient(270deg, #999999 0%, #666666 100%)
	}
	.bgblue{
		width: 220rpx;
		height: 38rpx;
		background: rgba(0,0,0,0.36);
		overflow: hidden;
		.txt{
				position: relative;
				left: -5rpx;
				display: flex;
				align-items: center;
				justify-content: center;
				width: 38px;
				height: 100%;
				text-align: center;
				background: linear-gradient(270deg, #2FA1F5 0%, #0076FF 100%);
			}	
		}
	.title-box{
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 20rpx;
		font-size: 32rpx;
		.more{
			display: flex;
			align-items: center;
			justify-content: center;
			
			font-size: 26rpx;
			color: #666;
			.iconfont{
				font-size: 26rpx;
				margin-top: 8rpx;
			}
		}
	}
</style>
