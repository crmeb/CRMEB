<template>
	<view>
		<view class="productList" :style="colorStyle">
			<view class='index-wrapper acea-row row-between-wrapper' v-if="isShow && bastList.length">
				<view class='title acea-row row-between-wrapper'>
					<view class='text'>
						<view class='name line1'>
							<text class="iconfont icon-jingpintuijian1"></text>
							{{$t(titleInfo[0].val)}}
						</view>
						<view class='line1 txt-btn'>{{$t(titleInfo[1].val)}}</view>
					</view>
					<view class='more' @click="gopage(titleInfo[2].val)">
						{{$t(`更多`)}}
						<text class='iconfont icon-jiantou'></text>
					</view>
				</view>
				<view class="item-box">
					<view class='item' v-for="(item,index) in bastList" :key="index" @click="goDetail(item)">
						<view class='pictrue'>
							<easy-loadimage mode="widthFix" :image-src="item.image"></easy-loadimage>
							<!-- 			<span class="pictrue_log_class pictrue_log_big" v-if="item.activity && item.activity.type === '1'">秒杀</span>
						<span class="pictrue_log_class pictrue_log_big" v-if="item.activity && item.activity.type === '2'">砍价</span>
						<span class="pictrue_log_class pictrue_log_big" v-if="item.activity && item.activity.type === '3'">拼团</span> -->
						</view>
						<view class='text'>
							<view class='name line2'>{{item.store_name}}</view>
							<view class="type">
								<view class="type-sty" v-if="item.activity && item.activity.type == '1'">{{$t(`秒杀`)}}
								</view>
								<view class="type-sty" v-if="item.activity && item.activity.type == '2'">{{$t(`砍价`)}}
								</view>
								<view class="type-sty" v-if="item.activity && item.activity.type == '3'">{{$t(`砍价`)}}
								</view>
							</view>
							<view class='vip acea-row'>
								<view class='money font-color'>{{$t(`￥`)}}<text class='num'>{{item.price}}</text></view>
								<view class='vip-money' v-if="item.vip_price && item.vip_price > 0 && item.base">
									{{$t(`￥`)}}{{item.vip_price}}
									<image src='/static/images/jvip.png' class="jvip"></image>
								</view>
								<view class='vip-money' v-if="item.vip_price && item.vip_price > 0 && item.is_vip">
									{{$t(`￥`)}}{{item.vip_price}}
									<image src='/static/images/vip.png'></image>
								</view>
								<!-- <view>已售{{item.sales}}{{item.unit_name}}</view> -->
							</view>
						</view>
					</view>
				</view>

			</view>
			<view class='index-wrapper list acea-row row-between-wrapper' v-if="!isShow && isIframe && bastList.length">
				<view class='title acea-row row-between-wrapper'>
					<view class='text'>
						<view class='name line1'>
							<text class="iconfont icon-jingpintuijian1"></text>
							{{titleInfo[0].val}}
						</view>
						<view class='line1 txt-btn'>{{titleInfo[1].val}}</view>
					</view>
					<view class='more' @click="gopage(titleInfo[2].val)">
						{{$t(`更多`)}}
						<text class='iconfont icon-jiantou'></text>
					</view>
				</view>
				<view class="item-box">

					<view class='item' v-for="(item,index) in bastList" :key="index" @click="goDetail(item)">
						<view class='pictrue'>
							<image :src='item.image'></image>
							<!-- 			<span class="pictrue_log_class pictrue_log_big" v-if="item.activity && item.activity.type === '1'">秒杀</span>
					<span class="pictrue_log_class pictrue_log_big" v-if="item.activity && item.activity.type === '2'">砍价</span>
					<span class="pictrue_log_class pictrue_log_big" v-if="item.activity && item.activity.type === '3'">拼团</span> -->
						</view>
						<view class='text'>
							<view class='name line2'>{{item.store_name}}</view>
							<view class="type">
								<view class="type-sty" v-if="item.activity && item.activity.type == '1'">{{$t(`秒杀`)}}
								</view>
								<view class="type-sty" v-if="item.activity && item.activity.type == '2'">{{$t(`砍价`)}}
								</view>
								<view class="type-sty" v-if="item.activity && item.activity.type == '3'">{{$t(`砍价`)}}
								</view>
								<view class="type-sty" v-if="item.checkCoupon">{{$t(`ticket`)}}</view>
							</view>
							<view class='money font-color'>{{$t(`￥`)}}<text class='num'>{{item.price}}</text></view>
							<view class='vip acea-row row-between-wrapper'>
								<view class='vip-money' v-if="item.vip_price && item.vip_price > 0 && item.base">
									{{$t(`￥`)}}{{item.vip_price}}
									<image src='/static/images/jvip.png' class="jvip"></image>
								</view>
								<view class='vip-money' v-if="item.vip_price && item.vip_price > 0 && item.is_vip">
									{{$t(`￥`)}}{{item.vip_price}}
									<image src='/static/images/vip.png'></image>
								</view>
								<!-- <view>已售{{item.sales}}{{item.unit_name}}</view> -->
							</view>
						</view>
					</view>
				</view>
			</view>
			<block v-if="isIframe && !bastList.length">
				<view class='index-wrapper' v-if="isIframe && !fastList.length">
					<view class='title acea-row row-between-wrapper'>
						<view class='text'>
							<view class='name line1'>
								<text class="iconfont icon-jingpintuijian1"></text>
								{{titleInfo[0].val}}
							</view>
							<view class='line1 txt-btn'>{{titleInfo[1].val}}</view>
						</view>
						<navigator class='more' open-type="switchTab" :url="titleInfo[2].val">{{$t(`更多`)}}<text
								class='iconfont icon-jiantou'></text></navigator>
					</view>
					<view class='scroll-product'>
						<view class="empty-img">{{$t(`精品推荐，暂无数据`)}}</view>
					</view>
				</view>
			</block>
		</view>
	</view>
</template>

<script>
	let app = getApp()
	import {
		mapState
	} from 'vuex'
	import {
		goShopDetail,
		goPage
	} from '@/libs/order.js'
	import {
		getHomeProducts
	} from '@/api/store.js';
	import goodLists from '@/components/goodList/index.vue'
	import colors from "@/mixins/color";
	export default {
		name: 'goodList',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			}
		},
		mixins: [colors],
		components: {
			goodLists
		},
		created() {

		},
		mounted() {},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if (nVal) {
						this.isShow = nVal.isShow.val;
						this.selectType = nVal.tabConfig.tabVal;
						this.$set(this, 'selectId', nVal.selectConfig.activeValue || '');
						this.$set(this, 'type', nVal.selectSortConfig.activeValue);
						this.salesOrder = nVal.goodsSort.type == 1 ? 'desc' : '';
						this.newsOrder = nVal.goodsSort.type == 2 ? 'news' : '';
						this.ids = nVal.ids ? nVal.ids.join(',') : '';
						this.numConfig = nVal.numConfig.val;
						this.titleInfo = nVal.titleInfo.list;
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
				selectType: 0,
				selectId: '',
				salesOrder: '',
				newsOrder: '',
				ids: '',
				page: 1,
				limit: this.$config.LIMIT,
				type: '',
				numConfig: 0,
				titleInfo: []
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
						limit: that.numConfig <= that.limit ? that.numConfig : that.limit,
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
					that.$util.Tips({
						title: err
					});
				});
			},
			gopage(url) {
				uni.navigateTo({
					url: url
				})
			},
			goDetail(item) {
				goPage().then(res => {
					goShopDetail(item, this.uid).then(res => {
						uni.navigateTo({
							url: `/pages/goods_details/index?id=${item.id}`
						})
					})
				})
			}
		}
	}
</script>

<style lang="scss">
	.productList {
		background-color: #fff;
		margin: 20rpx 30rpx;
		border-radius: $uni-border-radius-index;
	}

	.title {
		display: flex;
		margin: 0;
		width: 100%;
		margin: 0 20rpx;

		.text {
			display: flex;

			.name {
				font-size: $uni-index-title-font-size;
				font-weight: bold;
			}

			.txt-btn {
				display: flex;
				align-items: flex-end;
				margin-bottom: 8rpx;
				margin-left: 12rpx;
			}

		}
	}

	.productList .item {
		width: 100%;
		padding: 25rpx 0;
		background-color: #fff;
		border-radius: 10rpx;
		display: flex;
		// border:1rpx solid #eee;
	}

	.productList .item .pictrue {
		position: relative;
		width: 180rpx;
		height: 180rpx;
	}

	.productList .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 10rpx;
	}

	.productList .item .pictrue {

		/deep/,
		/deep/image,
		/deep/.easy-loadimage,
		/deep/uni-image {

			width: 180rpx;
			height: 180rpx;
			border-radius: 10rpx;
		}
	}

	.productList .item:nth-child(even) {
		border-top: 1rpx solid #EEEEEE;
		border-bottom: 1rpx solid #EEEEEE;
	}

	.productList .item .text {
		width: 460rpx;
		padding: 0rpx 17rpx 0rpx 17rpx;
		font-size: 30rpx;
		color: #222;
		display: flex;
		flex-direction: column;
		justify-content: space-between;

		.name {
			font-size: 28rpx;
		}

		.type {
			display: flex;

			.type-sty {
				padding: 0 5rpx;
				border: 1px solid var(--view-theme);
				color: var(--view-theme);
				font-size: 24rpx;
				border-radius: 4rpx;
			}
		}
	}

	.productList .item .text .money {
		font-size: 26rpx;
		font-weight: bold;
	}

	.productList .item .text .money .num {
		font-size: 34rpx;
		color: var(--view-priceColor);
	}

	.productList .item .text .vip {
		font-size: 22rpx;
		color: var(--view-priceColor);
		margin-top: 7rpx;
		display: flex;
		align-items: center;
	}

	.productList .item .text .vip .vip-money {
		font-size: 24rpx;
		color: #282828;
		font-weight: bold;
	}

	.productList .item .text .vip .vip-money image {
		width: 46rpx;
		height: 21rpx;
		margin-left: 4rpx;
	}

	.empty-img {
		width: 690rpx;
		height: 300rpx;
		border-radius: 10rpx;
		margin: 26rpx auto 0 auto;
		background-color: #ccc;
		text-align: center;
		line-height: 300rpx;

		.iconfont {
			font-size: 50rpx;
		}
	}

	.font-color {
		color: var(--view-priceColor);
	}

	.item-box {
		margin: 0 auto;
	}
</style>
