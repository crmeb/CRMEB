<template>
	<view class="index-product-wrapper" :class="iSshowH?'on':''" :style="'margin-top:'+mbConfig*2+'rpx;'"
		v-show="!isSortType">
		<view class="nav-bd"
			:style="{justifyContent:titleConfig===0?'flex-start':titleConfig===1?'space-around':'flex-end'}">
			<view class="item" v-for="(item,index) in explosiveMoney" :index="index"
				@click="ProductNavTab(item.link.activeVal,index)">
				<view class="txt" :style="{ 'color': index==ProductNavindex ? themeColor : '' }">
					{{$t(item.chiild[0].val)}}</view>
				<view class="label"
					:style="{ 'background': index==ProductNavindex ? themeColor : '','color': index==ProductNavindex ? '#fff' : '' }">
					{{$t(item.chiild[1].val)}}</view>
			</view>
		</view>
		<!-- 首发新品 -->
		<view class="list-box animated" :class='tempArr.length > 0?"fadeIn on":""'>
			<view class="item" v-for="(item,index) in tempArr" :key="index" @click="goDetail(item)">
				<view class="pictrue">
					<span class="pictrue_log pictrue_log_class" :style="'background-color:'+labelColor"
						v-if="item.activity && item.activity.type === '1'">{{$t(`秒杀`)}}</span>
					<span class="pictrue_log pictrue_log_class" :style="'background-color:'+labelColor"
						v-if="item.activity && item.activity.type === '2'">{{$t(`砍价`)}}</span>
					<span class="pictrue_log pictrue_log_class" :style="'background-color:'+labelColor"
						v-if="item.activity && item.activity.type === '3'">{{$t(`拼团`)}}</span>
					<!-- <image :src="item.image" mode="aspectFit"></image> -->
					<easy-loadimage mode="widthFix" :image-src="item.image"></easy-loadimage>
				</view>
				<view class="text-info">
					<view class="title line1" v-if="titleShow">{{item.store_name}}</view>
					<view class="old-price" v-if="opriceShow"><text>{{$t(`￥`)}}</text>{{item.ot_price}}</view>
					<view class="price" :style="{color:fontColor}">
						<view v-if="priceShow">
							<text>{{$t(`￥`)}}</text>{{item.price}}
						</view>
						<view class="txt" :style="'border:1px solid '+labelColor+';color:'+labelColor"
							:class="priceShow?'':'on'" v-if="item.checkCoupon && couponShow">{{$t(`券`)}}</view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		name: 'promotionList',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			tempArr: {
				type: Array,
				default: []
			},
			iSshowH: {
				type: Boolean,
				default: false
			},
			isSortType: {
				type: String | Number,
				default: 0
			}
		},
		data() {
			return {
				ProductNavindex: 0,
				explosiveMoney: this.dataConfig.tabConfig.list,
				numConfig: this.dataConfig.numConfig.val,
				// imgStyle: this.dataConfig.imgStyle.type,
				mbConfig: this.dataConfig.mbConfig.val,
				themeColor: this.dataConfig.themeColor.color[0].item,
				titleShow: this.dataConfig.titleShow.val, //标题是否显示
				opriceShow: this.dataConfig.opriceShow.val, //原价是否显示
				priceShow: this.dataConfig.priceShow.val, //价格是否显示
				couponShow: this.dataConfig.couponShow.val, //优惠券标签是否显示
				titleConfig: this.dataConfig.titleConfig.type, //标题位置
				fontColor: this.dataConfig.fontColor.color[0].item,
				labelColor: this.dataConfig.labelColor.color[0].item
			};
		},
		created() {},
		methods: {
			// 首发新品切换
			ProductNavTab(type, index) {
				this.ProductNavindex = index;
				this.$emit('changeTab', type);
			},
			goDetail(item) {
				this.$emit('detail', item);
			}
		}
	}
</script>

<style lang="scss">
	.index-product-wrapper {
		margin: 30rpx 20rpx 0 20rpx;

		&.on {
			min-height: 1500rpx;
		}

		.nav-bd {
			display: flex;
			align-items: center;
			margin-top: 30rpx;

			.item {
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				width: 25%;

				&.on {
					border-radius: 0;
				}

				.txt {
					font-size: 32rpx;
					color: #282828;
				}

				.label {
					display: flex;
					align-items: center;
					justify-content: center;
					width: 124rpx;
					height: 32rpx;
					margin-top: 5rpx;
					font-size: 24rpx;
					color: #999;
					border-radius: 16rpx;
				}
			}
		}

		.list-box {
			display: flex;
			flex-wrap: wrap;
			justify-content: space-between;
			margin-top: 30rpx;

			.item {
				width: 345rpx;
				margin-bottom: 20rpx;
				background-color: #fff;
				border-radius: 20rpx;
				overflow: hidden;
				position: relative;

				/deep/.easy-loadimage image {
					width: 100%;
					height: 346rpx;
				}

				.pictrue_log {
					width: 92rpx;
					height: 44rpx;
					font-size: 26rpx;
					line-height: 44rpx;
				}

				image {
					width: 100%;
					height: 346rpx;
					display: block;
				}

				.text-info {
					padding: 10rpx 20rpx 15rpx;

					.title {
						color: #222222;
					}

					.old-price {
						margin-top: 4rpx;
						font-size: 26rpx;
						color: #AAAAAA;
						text-decoration: line-through;

						text {
							margin-right: 2px;
							font-size: 20rpx;
						}
					}

					.price {
						display: flex;
						align-items: flex-end;
						font-size: 36rpx;
						font-weight: 550;

						text {
							padding-bottom: 4rpx;
							font-size: 26rpx;
							font-weight: normal;
						}

						.txt {
							display: flex;
							align-items: center;
							justify-content: center;
							width: 28rpx;
							height: 28rpx;
							margin-left: 15rpx;
							margin-bottom: 10rpx;
							border-radius: 4rpx;
							font-size: 20rpx;
							font-weight: normal;

							&.on {
								margin-left: 0;
							}
						}
					}
				}
			}

			&.on {
				display: flex;
			}
		}
	}
</style>
