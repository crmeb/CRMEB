<template>
	<view>
		<view class='goodList' v-if="isShow && bastList.length && !isIframe">
			<block v-for="(item,index) in bastList" :key="index">
				<view @click="goDetail(item)" class='item acea-row row-between-wrapper' hover-class="none">
					<view class='pictrue'>
						<image :src='item.image'></image>
						<span class="pictrue_log pictrue_log_class"
							v-if="item.activity && item.activity.type === '1'">{{$t(`秒杀`)}}</span>
						<span class="pictrue_log pictrue_log_class"
							v-if="item.activity && item.activity.type === '2'">{{$t(`砍价`)}}</span>
						<span class="pictrue_log pictrue_log_class"
							v-if="item.activity && item.activity.type === '3'">{{$t(`拼团`)}}</span>
					</view>
					<view class='underline'>
						<view class='text'>
							<view class='line1'>{{item.store_name}}</view>
							<view class='money font-color'>{{$t(`￥`)}}<text class='num'>{{item.price}}</text></view>
							<view class="vip-money acea-row row-middle"
								v-if="item.base && item.vip_price && item.vip_price > 0">{{$t(`￥`)}}{{item.vip_price || 0}}
								<image src='/static/images/jvip.png' class="jvip"></image><text
									class='num'>{{$t(`已售`)}}{{item.sales}}{{item.unit_name}}</text>
							</view>
							<view class='vip-money acea-row row-middle'
								v-if="item.is_vip && item.vip_price && item.vip_price > 0">{{$t(`￥`)}}{{item.vip_price || 0}}
								<image src='/static/images/vip.png'></image><text
									class='num'>{{$t(`已售`)}}{{item.sales}}{{item.unit_name}}</text>
							</view>
							<view class='vip-money acea-row row-middle' v-else><text
									class='num'>{{$t(`已售`)}}{{item.sales}}{{item.unit_name}}</text></view>
						</view>
					</view>
				</view>
			</block>
		</view>
		<view class='goodList' v-if="bastList.length && isIframe">
			<block v-for="(item,index) in bastList" :key="index">
				<view @click="goDetail(item)" class='item acea-row row-between-wrapper' hover-class="none">
					<view class='pictrue'>
						<image :src='item.image'></image>
						<span class="pictrue_log pictrue_log_class"
							v-if="item.activity && item.activity.type === '1'">{{$t(`秒杀`)}}</span>
						<span class="pictrue_log pictrue_log_class"
							v-if="item.activity && item.activity.type === '2'">{{$t(`砍价`)}}</span>
						<span class="pictrue_log pictrue_log_class"
							v-if="item.activity && item.activity.type === '3'">{{$t(`拼团`)}}</span>
					</view>
					<view class='underline'>
						<view class='text'>
							<view class='line1'>{{item.store_name}}</view>
							<view class='money font-color'>{{$t(`￥`)}}<text class='num'>{{item.price}}</text></view>
							<view class="vip-money acea-row row-middle"
								v-if="item.base && item.vip_price && item.vip_price > 0">{{$t(`￥`)}}{{item.vip_price || 0}}
								<image src='/static/images/jvip.png' class="jvip"></image><text
									class='num'>{{$t(`已售`)}}{{item.sales}}{{item.unit_name}}</text>
							</view>
							<view class='vip-money acea-row row-middle'
								v-if="item.is_vip && item.vip_price && item.vip_price > 0">{{$t(`￥`)}}{{item.vip_price || 0}}
								<image src='/static/images/vip.png'></image><text
									class='num'>{{$t(`已售`)}}{{item.sales}}{{item.unit_name}}</text>
							</view>
							<view class='vip-money acea-row row-middle' v-else><text
									class='num'>{{$t(`已售`)}}{{item.sales}}{{item.unit_name}}</text></view>
						</view>
					</view>
				</view>
			</block>
		</view>
		<view class="empty-img" v-if="!bastList.length && isIframe">{{$t(`商品列表，暂无数据`)}}</view>
	</view>
</template>

<script>
	let app = getApp()
	import {
		mapGetters
	} from "vuex";
	import {
		goShopDetail,
		goPage
	} from '@/libs/order.js';
	import {
		getHomeProducts
	} from '@/api/store.js';
	export default {
		name: 'goodList',
		computed: mapGetters(['uid']),
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
					if (nVal) {
						this.isShow = nVal.isShow.val;
						this.selectType = nVal.tabConfig.tabVal;
						this.$set(this, 'selectId', nVal.selectConfig.activeValue || '');
						this.$set(this, 'type', nVal.selectSortConfig.activeValue);
						this.salesOrder = nVal.goodsSort.type == 1 ? 'desc' : '';
						this.newsOrder = nVal.goodsSort.type == 2 ? 'news' : '';
						this.ids = nVal.ids ? nVal.ids.join(',') : '';
						this.numConfig = nVal.numConfig.val;
						this.productslist();
					}
				}
			}
		},
		data() {
			return {
				bastList: [],
				name: this.$options.name,
				isIframe: false,
				isShow: true,
				selectType: 0,
				selectId: '',
				salesOrder: '',
				newsOrder: '',
				ids: '',
				page: 1,
				limit: this.$config.LIMIT,
				type: '',
				numConfig: 0
			};
		},
		created() {
			this.isIframe = app.globalData.isIframe;
		},
		mounted() {},
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

<style scoped lang='scss'>
	.empty-img {
		width: 690rpx;
		height: 300rpx;
		border-radius: 14rpx;
		margin: 26rpx auto 0 auto;
		background-color: #ccc;
		text-align: center;
		line-height: 300rpx;

		.iconfont {
			font-size: 50rpx;
		}
	}

	.goodList .item {
		position: relative;
		padding-left: 30rpx;
		box-shadow: 2px 1px 6px 1px rgba(0, 0, 0, 0.03);
	}

	.goodList .item .pictrue {
		width: 180rpx;
		height: 180rpx;
		position: relative;
	}

	.goodList .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 20rpx;
	}

	.goodList .item .pictrue .numPic {
		position: absolute;
		left: 7rpx;
		top: 7rpx;
		width: 40rpx;
		height: 40rpx;
		border-radius: 50%;
	}

	.goodList .item .underline {
		padding: 30rpx 30rpx 30rpx 0;
		border-bottom: 1px solid #f5f5f5;
	}

	.goodList .item:nth-last-child(1) .underline {
		border-bottom: 0;
	}

	.goodList .item .text {
		font-size: 30rpx;
		color: #222;
		width: 489rpx;
	}

	.goodList .item .text .money {
		font-size: 26rpx;
		font-weight: bold;
		margin-top: 50rpx;
	}

	.goodList .item .text .money .num {
		font-size: 34rpx;
	}

	.goodList .item .text .vip-money {
		font-size: 24rpx;
		color: #282828;
		font-weight: bold;
		margin-top: 15rpx;
	}

	.goodList .item .text .vip-money .jvip {
		width: 46rpx;
		height: 22rpx;
	}

	.goodList .item .text .vip-money image {
		width: 66rpx;
		height: 25rpx;
		margin-right: 8rpx;
		margin-left: 8rpx;
	}

	.goodList .item .text .vip-money .num {
		font-size: 22rpx;
		color: #aaa;
		font-weight: normal;
		margin-top: -2rpx;

		~.num {
			margin-left: 22rpx;
		}
	}

	.goodList .item .iconfont {
		position: absolute;
		right: 30rpx;
		width: 50rpx;
		height: 50rpx;
		border-radius: 50%;
		font-size: 30rpx;
		bottom: 38rpx;
	}
</style>
