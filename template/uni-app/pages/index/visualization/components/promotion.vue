<template>
	<view class="">
		<view class="index-wrapper">
			<view class='wrapper' v-if="isShow && benefit.length">
				<view class='title acea-row row-between-wrapper'>
					<view class='text'>
						
						<view class='name line1'><text class="iconfont icon-cuxiaodanpin"></text>{{$t(titleInfo[0].val)}}</view>
						<view class='line1 txt-btn'>{{$t(titleInfo[1].val)}}</view>
					</view>
					<view class='more' @click="gopage(titleInfo[2].val)">{{$t(`更多`)}}<text class='iconfont icon-jiantou'></text>
					</view>
				</view>
				<promotionGood :benefit="benefit"></promotionGood>
			</view>
			<view class='wrapper' v-if="!isShow && isIframe && benefit.length">
				<view class='title acea-row row-between-wrapper'>
					<view class='text'>
						<view class='name line1'><text class="iconfont icon-cuxiaodanpin"></text>{{titleInfo[0].val}}</view>
						<view class='line1 txt-btn'>{{titleInfo[1].val}}</view>
					</view>
					<view class='more' @click="gopage(titleInfo[1].val)">{{$t(`更多`)}}<text class='iconfont icon-jiantou'></text>
					</view>
				</view>
				<promotionGood :benefit="benefit"></promotionGood>
			</view>
			<view class='wrapper' v-if="isIframe && !benefit.length">
				<view class='title acea-row row-between-wrapper'>
					<view class='text'>
						<view class='name line1'><text class="iconfont icon-cuxiaodanpin"></text>{{titleInfo[0].val}}</view>
						<view class='line1 txt-btn'>{{titleInfo[1].val}}</view>
					</view>
					<view class='more' @click="gopage(titleInfo[1].val)">{{$t(`更多`)}}<text class='iconfont icon-jiantou'></text>
					</view>
				</view>
				<view class="empty-img">{{$t(`促销单品，暂无数据`)}}</view>
			</view>
		</view>
	</view>
</template>

<script>
	let app = getApp()
	import {
		mapState
	} from 'vuex'
	import {
		goPage
	} from '@/libs/order.js'
	import {
		getHomeProducts
	} from '@/api/store.js';
	import promotionGood from '@/components/promotionGood/index.vue';
	export default {
		name: 'goodList',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			}
		},
		components: {
			promotionGood
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
		created() {
			this.isIframe = app.globalData.isIframe;
		},
		mounted() {},
		data() {
			return {
				benefit: [],
				salesInfo: this.$t(`库存商品优惠促销活动`),
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
					that.benefit = res.data.list;
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
			}
		}
	}
</script>

<style lang="scss">
	.index-wrapper {
		background-color: $uni-bg-color;
		margin: $uni-index-margin-row $uni-index-margin-col 5rpx $uni-index-margin-col;
		border-radius: $uni-border-radius-index;

		// box-shadow: $uni-index-box-shadow;
	}

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
</style>
