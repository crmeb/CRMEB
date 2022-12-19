<template>
	<view v-show="!isSortType" :style="{padding:'0 '+prConfig*2+'rpx'}">
		<view class="coupon" :class="bgStyle===0?'':'borderRadius15'" :style="'background-color:'+bgColor+';margin-top:' + mbConfig*2 +'rpx;'" v-if="couponList.length">
			<scroll-view scroll-x="true" style="white-space: nowrap; vertical-align: middle;" show-scrollbar="false">
		
				<view class="wrapper">
				
				<view class="item" :style="item.is_use?'':'background-color:'+ themeColor +';'" style="margin-right: 20rpx;" v-for="(item,index) in couponList"
				 :key="index" hover-class="none">
					<view class="itemCon acea-row row-between-wrapper">
						<view class="text">
							<view class="money"><text>{{$t(`¥`)}}</text>{{item.coupon_price}}</view>
							<view class="info">{{$t(`满`)}}{{item.use_min_price}}{{$t(`元可用`)}}</view>
						</view>
						<view class="bnt" v-if="item.is_use===true"><text>{{$t(`已领取`)}}</text></view>
						<view class="bnt" v-else-if="item.is_use===false" @click="receiveCoupon(item)"><text>{{$t(`立即领取`)}}</text></view>
						<view class="bnt" v-else-if="item.is_use===2"><text>{{$t(`已过期`)}}</text></view>
					</view>
					<view class="roll up-roll" :style="{background:bgColor}"></view>
					<view class="roll down-roll" :style="{background:bgColor}"></view>
				</view>
				<navigator url="/pages/users/user_get_coupon/index" class="more-box" hover-class="none">
					<view class="txt">{{$t(`更多`)}}</view>
					<image src="/static/images/mores.png"></image>
				</navigator>
				</view>	
			</scroll-view>
			<!-- #ifdef MP -->
			<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse" :isGoIndex="false"></authorize> -->
			<!-- #endif -->
		</view>
	</view>
	
</template>

<script>
	import {
		getCoupons,
		setCouponReceive
	} from '@/api/api.js';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import {
		mapGetters
	} from "vuex";
	import {
		toLogin
	} from '@/libs/login.js';
	export default {
		name: 'coupon',
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
		computed: mapGetters(['isLogin']),
		components: {
			// #ifdef MP
			authorize
			// #endif
		},
		watch:{
			isLogin:{
				handler:function(newV,oldV){
					if(newV){
						this.getCoupon();
					}
				},
				deep:true
			}
		},
		data() {
			return {
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				couponList: [],
				bgColor: this.dataConfig.bgColor.color[0].item,
				themeColor: this.dataConfig.themeColor.color[0].item,
				mbConfig: this.dataConfig.mbConfig.val,
				bgStyle: this.dataConfig.bgStyle.type,
				prConfig: this.dataConfig.prConfig.val
			};
		},
		created() {},
		mounted() {
			this.getCoupon();
		},
		methods: {
			getCoupon: function() {
				let that = this;
				let limit = that.$config.LIMIT;
				getCoupons({
					page: 1,
					limit: limit,
					type: -1
				}).then(res => {
					that.$set(that, 'couponList', res.data.list);
				}).catch(err => {
					return that.$util.Tips({
						title: err
					});
				});
			},
			receiveCoupon: function(item) {
				let that = this;
				if (!that.isLogin) {
					toLogin();
				} else {
					setCouponReceive(item.id)
						.then(function() {
							item.is_use = true;
							that.$set(that, 'couponList', that.couponList);
							that.$util.Tips({
								title: that.$t(`领取成功`)
							});
						})
						.catch(function(err) {
							that.$util.Tips({
								title: err
							});
						});
				}
			}
		}
	}
</script>

<style lang="scss">
	.coupon {
		background-color: #fff;
		padding: 20rpx;

		.item {
			display: flex;
			width: 304rpx;
			height: 122rpx;
			background-color: #DDDDDD;
			border-radius: 8rpx;
			color: #fff;
			position: relative;
			display: inline-block;
			flex-shrink: 0;
			.roll{
				position: absolute;
				width: 20rpx;
				height: 20rpx;
				border-radius: 50%;
				background: #fff;
				&.up-roll{
					right: 52rpx;
					top: -10rpx;
				}
				&.down-roll{
					right: 52rpx;
					bottom: -10rpx;
				}
			}
			// &::before {
			// 	position: absolute;
			// 	content: ' ';
			// 	width: 20rpx;
			// 	height: 20rpx;
			// 	border-radius: 50%;
			// 	background-color: #fff;
			// 	right: 52rpx;
			// 	top: -10rpx;
			// }

			// &::after {
			// 	position: absolute;
			// 	content: ' ';
			// 	width: 20rpx;
			// 	height: 20rpx;
			// 	border-radius: 50%;
			// 	background-color: #fff;
			// 	right: 52rpx;
			// 	bottom: -10rpx;
			// }

			.itemCon {
				height: 100%;
				width: 100%;

				.text {
					width: 240rpx;

					.money {
						font-size: 48rpx;
						text-align: center;

						text {
							font-size: 24rpx;
						}
					}

					.info {
						font-size: 24rpx;
						text-align: center;
					}
				}

				.bnt {
					position: relative;
					height: 100%;
					font-size: 20rpx;
					display: block;
					writing-mode: vertical-lr;
					line-height: 1.2;
					width: 64rpx;
					border-left: 1px dashed #fff;
					text{
						position: absolute;
						left: 56%;
						top: 50%;
						transform: translate(-50%,-50%);
					}
				}
			}
		}

		.wrapper {
			display: flex;
		}

		.more-box {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			background-color: #fff;
			border-radius: 16rpx;
			padding: 0 10px;
			height: 122rpx;
			image {
				width: 20rpx;
				height: 20rpx;
				margin-top: 10rpx;
				margin: 10rpx 0 0 5rpx;
			}

			.txt {
				display: block;
				writing-mode: vertical-lr;
				font-size: 20rpx;
				line-height: 1.2;
			}
		}
	}
</style>
