<template>
	<view :style="colorStyle">
		<view class='hotList skeleton-rect index-wrapper' v-if="isShow && hotList.length">
			<!-- <view class='hot-bg'>
			</view> -->
			<!-- 			<view class='title acea-row row-between-wrapper'>
				<view class='text line1'>
					<text class='label'><text class="iconfont icon-paihangbang"></text>{{titleInfo[0].val}}</text>
					{{titleInfo[1].val}}
				</view>
				<view class='more' hover-class="none" @click="gopage(titleInfo[2].val)">
					更多
					<text class="iconfont icon-jiantou"></text>
				</view>
			</view> -->
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
			<view class='list acea-row row-middle'>
				<block v-for="(item,index) in hotList" :key='index'>
					<view class='item' @click="gopage('/pages/goods_details/index?id='+item.id)">
						<view class='pictrue'>
							<easy-loadimage mode="widthFix" :image-src="item.image"></easy-loadimage>
							<image v-if="index == 0" src='/static/images/one.png' class='numPic'></image>
							<image v-else-if="index == 1" src='/static/images/two.png' class='numPic'></image>
							<image v-else-if="index == 2" src='/static/images/three.png' class='numPic'></image>
						</view>
						<view class="rectangle">
							{{$t(`热度 TOP`)}} {{index+1}}
						</view>
						<view class='name line1'>{{item.store_name}}</view>
						<!-- <view class='money font-color'>
							￥
							<text class='num'>{{item.price}}</text>
						</view> -->
					</view>
				</block>
			</view>
		</view>
		<view class='hotList index-wrapper' v-if="!isShow && isIframe && hotList.length">
			<!-- 		<view class='hot-bg'>
				<view class='title acea-row row-between-wrapper'>
					<view class='text line1'>
						<text class='label'><text class="iconfont icon-paihangbang"></text>{{titleInfo[0].val}}</text>
						{{titleInfo[1].val}}
					</view>
					<view class='more' hover-class="none" @click="gopage(titleInfo[2].val)">
						更多
						<text class="iconfont icon-jiantou"></text>
					</view>
				</view>
			</view> -->
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>
						<text class="iconfont icon-shoufaxinpin"></text>
						{{titleInfo[0].val}}
						<!-- <text class='new font-color'>NEW~</text> -->
					</view>
					<view class='line1 txt-btn'>{{titleInfo[1].val}}</view>
				</view>
				<view class='more' @click="gopage(titleInfo[2].val)">
					{{$t(`更多`)}}
					<text class='iconfont icon-jiantou'></text>
				</view>
			</view>
			<view class='list acea-row row-middle'>
				<block v-for="(item,index) in hotList" :key='index'>
					<view class='item' @click="gopage('/pages/goods_details/index?id='+item.id)">
						<view class='pictrue'>
							<image :src='item.image'></image>
							<image v-if="index == 0" src='/static/images/one.png' class='numPic'></image>
							<image v-else-if="index == 1" src='/static/images/two.png' class='numPic'></image>
							<image v-else-if="index == 2" src='/static/images/three.png' class='numPic'></image>
						</view>
						<view class='name line1'>{{item.store_name}}</view>
						<!-- <view class='money font-color'>
							￥
							<text class='num'>{{item.price}}</text>
						</view> -->
					</view>
				</block>
			</view>
		</view>
		<view class='hotList index-wrapper' v-if="isIframe && !hotList.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>
						<text class="iconfont icon-shoufaxinpin"></text>
						{{titleInfo[0].val}}
						<!-- <text class='new font-color'>NEW~</text> -->
					</view>
					<view class='line1 txt-btn'>{{titleInfo[1].val}}</view>
				</view>
				<view class='more' @click="gopage(titleInfo[2].val)">
					{{$t(`更多`)}}
					<text class='iconfont icon-jiantou'></text>
				</view>
			</view>
			<!-- <view class='hot-bg'>
				<view class='title acea-row row-between-wrapper'>
					<view class='text line1'>
						<text class='label'><text class="iconfont icon-paihangbang"></text>{{titleInfo[0].val}}</text>
						{{titleInfo[1].val}}
					</view>
					<view class='more' hover-class="none" @click="gopage(titleInfo[2].val)">
						更多
						<text class="iconfont icon-jiantou"></text>
					</view>
				</view>
			</view> -->
			<view class="empty-img">{{$t(`排行榜、暂无数据`)}}</view>
		</view>
	</view>
</template>

<script>
	let app = getApp()
	import colors from "@/mixins/color";
	import {
		mapState
	} from 'vuex';
	import {
		goPage
	} from '@/libs/order.js';
	import {
		getHomeProducts
	} from '@/api/store.js';
	export default {
		name: 'goodList',
		mixins: [colors],
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
						this.$set(this, 'selectId', nVal.selectConfig && nVal.selectConfig.activeValue ? nVal.selectConfig
							.activeValue : '');
						this.$set(this, 'type', nVal.selectSortConfig && nVal.selectSortConfig.activeValue ? nVal
							.selectSortConfig.activeValue : '');
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
				hotList: [],
				name: this.$options.name,
				isShow: true,
				isIframe: app.globalData.isIframe,
				selectType: 0,
				selectId: '',
				salesOrder: '',
				newsOrder: '',
				ids: '',
				page: 1,
				limit: 3,
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
					that.hotList = res.data.list;
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
	.hotList {
		background-color: #fff;
		margin: $uni-index-margin-row $uni-index-margin-col;
		border-radius: 12rpx;
	}

	.hotList .hot-bg {
		width: 100%;
		height: 120rpx;
		box-sizing: border-box;
		font-size: 24rpx;
		color: #282828;
		margin-top: 15rpx;

	}

	// .hotList .title {
	// 	padding: 20rpx 20rpx 0 20rpx;

	// 	.iconfont {
	// 		color: var(--view-theme);
	// 		font-size: 36rpx;
	// 		margin-top: 4rpx;
	// 		margin-right: 12rpx;
	// 	}
	// }

	.hotList .title .text {
		display: flex;

		.txt-btn {
			display: flex;
			align-items: flex-end;
			margin-bottom: 8rpx;
			margin-left: 12rpx;
		}
	}

	// .hotList .title .text .label {
	// 	font-size: $uni-index-title-font-size;
	// 	font-weight: bold;
	// 	color: #282828;
	// 	margin-right: 12rpx;
	// }

	// .hotList .title .more {
	// 	font-size: 26rpx;
	// 	color: #999999;
	// 	;
	// }

	// .hotList .title .more .iconfont {
	// 	font-size: 25rpx;
	// 	margin-left: 10rpx;
	// }

	.hotList .list {
		width: 690rpx;
		border-radius: 20rpx;
		background-color: #fff;
		margin: 0rpx auto 0 auto;
		padding: 20rpx 20rpx;
		box-sizing: border-box;
		display: flex;
		justify-content: space-between;
	}

	.hotList .list .item {
		width: 200rpx;
		background: var(--view-op-ten);
		border-radius: 12rpx;

		.rectangle {
			margin: 0 auto;
			border-radius: 30rpx;
			text-align: center;
			margin-top: -30rpx;
			font-size: 24rpx;
			color: #fff;
			z-index: 99;
			position: relative;
			width: 172rpx;
			background-color: var(--view-theme);
		}
	}

	.hotList .list .item~.item {
		// margin-left: 25rpx;
	}

	.hotList .list .item .pictrue {
		width: 180rpx;
		height: 180rpx;
		position: relative;
		margin: 8rpx;
		border-radius: 12rpx;
	}

	.hotList .list .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 12rpx;
	}

	.hotList .list .item .pictrue {

		/deep/,
		/deep/image,
		/deep/.easy-loadimage,
		/deep/uni-image {

			width: 180rpx;
			height: 180rpx;
			border-radius: 12rpx;
		}
	}

	.hotList .list .item .pictrue .numPic {
		width: 50rpx;
		height: 50rpx;
		border-radius: 50%;
		position: absolute;
		top: 7rpx;
		left: 7rpx;
	}

	.hotList .list .item .name {
		font-size: 26rpx;
		color: var(--view-theme);
		margin-top: 12rpx;
		padding: 0 10rpx 10rpx 10rpx;
	}

	.hotList .list .item .money {
		font-size: 20rpx;
		font-weight: bold;
		margin-top: 4rpx;
		text-align: center;
	}

	.hotList .list .item .money .num {
		font-size: 28rpx;
	}

	.empty-img {
		width: 100%;
		height: 340rpx;
		line-height: 340rpx;
		background: #ccc;
		font-size: 40rpx;
		color: #000;
		border-radius: 14rpx;
		text-align: center;
		font-size: 30rpx;
	}

	.font-color {
		color: var(--view-priceColor);
	}
</style>
