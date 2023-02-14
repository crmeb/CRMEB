<template>
	<view class="">

		<view class="index-wrapper" :style="colorStyle">
			<view class='wrapper' v-if="isShow && firstList.length">
				<view class='title acea-row row-between-wrapper'>
					<view class='text'>
						<view class='name line1'>
							<text class="iconfont icon-shoufaxinpin"></text>
							{{$t(titleInfo[0].val)}}
							<!-- <text class='new font-color'>NEW~</text> -->
						</view>
						<view class='line1 txt-btn'>{{$t(titleInfo[1].val)}}</view>
					</view>
					<view class='more' @click="gopage(titleInfo[2].val)">
						{{$t(`更多`)}}
						<text class='iconfont icon-jiantou'></text>
					</view>
				</view>
				<view class='newProducts'>
					<scroll-view class="scroll-view_x" scroll-x style="width:auto;overflow:hidden;">
						<block v-for="(item,index) in firstList" :key='index'>
							<view class='item' @click="goDetail(item)">
								<view class='img-box'>
									<easy-loadimage mode="widthFix" :image-src="item.image"></easy-loadimage>
									<text class="pictrue_log_medium pictrue_log_class"
										v-if="item.activity && item.activity.type ==='1'">
										{{$t(`秒杀`)}}
									</text>
									<text class="pictrue_log_medium pictrue_log_class"
										v-if="item.activity && item.activity.type === '2'">
										{{$t(`砍价`)}}
									</text>
									<text class="pictrue_log_medium pictrue_log_class"
										v-if="item.activity && item.activity.type === '3'">
										{{$t(`拼团`)}}
									</text>
								</view>
								<view class='pro-info line1'>{{item.store_name}}</view>
								<view class='money font-color'><text class="rmb">{{$t(`￥`)}}</text>{{item.price}}</view>
							</view>
						</block>
					</scroll-view>
				</view>
			</view>
			<view class='wrapper' v-if="!isShow && isIframe && firstList.length">
				<view class='title acea-row row-between-wrapper'>
					<view class='text'>
						<view class='name line1'>
							<text class="iconfont icon-shoufaxinpin"></text>
							{{titleInfo[0].val}}
						</view>
						<view class='line1 txt-btn'>{{titleInfo[1].val}}</view>
					</view>
					<view class='more' @click="gopage(titleInfo[2].val)">
						{{$t(`更多`)}}
						<text class='iconfont icon-jiantou'></text>
					</view>
				</view>
				<view class='newProducts'>
					<scroll-view class="scroll-view_x" scroll-x style="width:auto;overflow:hidden;">
						<block v-for="(item,index) in firstList" :key='index'>
							<view class='item' @click="goDetail(item)">
								<view class='img-box'>
									<image :src='item.image'></image>
									<text class="pictrue_log_medium pictrue_log_class"
										v-if="item.activity && item.activity.type ==='1'">
										{{$t(`秒杀`)}}
									</text>
									<text class="pictrue_log_medium pictrue_log_class"
										v-if="item.activity && item.activity.type === '2'">
										{{$t(`砍价`)}}
									</text>
									<text class="pictrue_log_medium pictrue_log_class"
										v-if="item.activity && item.activity.type === '3'">
										{{$t(`拼团`)}}
									</text>
								</view>
								<view class='pro-info line1'>{{item.store_name}}</view>
								<view class='money font-color'>
									<text class="rmb">{{$t(`￥`)}}</text>{{item.price}}
								</view>
							</view>
						</block>
					</scroll-view>
				</view>
			</view>
			<view class='wrapper' v-if="isIframe && !firstList.length">
				<view class='title acea-row row-between-wrapper'>
					<view class='text'>
						<view class='name line1'>
							<text class="iconfont icon-shoufaxinpin"></text>
							{{titleInfo[0].val}}
						</view>
						<view class='line1 txt-btn'>{{titleInfo[1].val}}</view>
					</view>
					<view class='more' @click="gopage(titleInfo[2].val)">
						{{$t(`更多`)}}
						<text class='iconfont icon-jiantou'></text>
					</view>
				</view>
				<view class='newProducts'>
					<view class="empty-img">{{$t(`首发新品，暂无数据`)}}</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	let app = getApp()
	import {
		goPage,
		goShopDetail
	} from '@/libs/order.js'
	import {
		mapState,
		mapGetters
	} from 'vuex';
	import {
		getHomeProducts
	} from '@/api/store.js';
	import colors from "@/mixins/color";
	export default {
		name: 'goodList',
		mixins: [colors],
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			}
		},
		computed: {
			...mapGetters(['uid']),
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
						this.titleInfo = nVal.titleInfo.list;
						this.productslist();
					}
				}
			}
		},
		created() {},
		mounted() {},
		data() {
			return {
				firstList: [],
				firstInfo: this.$t(`多个优质商品最新上架`),
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
					that.firstList = res.data.list;
				}).catch(err => {
					that.$util.Tips({
						title: err
					});
				});
			},
			gopage(url) {
				goPage().then(res => {
					uni.navigateTo({
						url: url
					})
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
			},
		}
	}
</script>

<style lang="scss">
	.index-wrapper {
		background-color: $uni-bg-color;
		margin: $uni-index-margin-row $uni-index-margin-col;
		border-radius: $uni-border-radius-index;
	}

	.title {
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


	.wrapper .newProducts {
		white-space: nowrap;
		padding: 0rpx 20rpx 0rpx 20rpx;
		margin: 20rpx 0;
	}

	.wrapper .newProducts .item {
		display: inline-block;
		width: 200rpx;
		margin-right: 20rpx;
		border-radius: 20rpx;
	}

	.wrapper .newProducts .item:nth-last-child(1) {
		margin-right: 0;
	}

	.wrapper .newProducts .item .img-box {
		width: 100%;
		height: 200rpx;
		position: relative;
	}

	.wrapper .newProducts .item .img-box image {
		width: 100%;
		height: 100%;
		border-radius: 12rpx 12rpx 0 0;
	}

	.wrapper .newProducts .item .img-box {

		/deep/,
		/deep/image,
		/deep/.easy-loadimage,
		/deep/uni-image {

			width: 100%;
			height: 200rpx;
			border-radius: 12rpx 12rpx 0 0;
		}
	}

	.wrapper .newProducts .item .pro-info {
		font-size: 28rpx;
		color: #333;
		text-align: center;
		padding: 19rpx 10rpx 0 10rpx;
	}

	.wrapper .newProducts .item .money {
		padding: 0 10rpx 18rpx 10rpx;
		text-align: center;
		font-size: 30rpx;
		font-weight: bold;

		.rmb {
			font-weight: bold;
			color: var(--view-priceColor);
			font-size: 10px;
		}
	}

	.empty-img {
		width: 640rpx;
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

	.font-color {
		color: var(--view-priceColor);
	}
</style>
