<template>
	<view class="index-wrapper" :class="{borderShow:isBorader}">
		<view class='wrapper' v-if="isShow && firstList.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>
						{{titleInfo[0].val}}
						<text class='new font-color'>NEW~</text>
					</view>
					<view class='line1'>{{titleInfo[1].val}}</view>
				</view>
				<view class='more' @click="gopage('/pages/columnGoods/HotNewGoods/index?type=3')">
					更多
					<text class='iconfont icon-jiantou'></text>
				</view>
			</view>
			<view class='newProducts'>
				<scroll-view class="scroll-view_x" scroll-x style="width:auto;overflow:hidden;">
					<block v-for="(item,index) in firstList" :key='index'>
						<view class='item' @click="goDetail(item)">
							<view class='img-box'>
								<image :src='item.image'></image>
								<text class="pictrue_log_medium pictrue_log_class" v-if="item.activity && item.activity.type ==='1'">
									秒杀
								</text>
								<text class="pictrue_log_medium pictrue_log_class" v-if="item.activity && item.activity.type === '2'">
									砍价
								</text>
								<text class="pictrue_log_medium pictrue_log_class" v-if="item.activity && item.activity.type === '3'">
									拼团
								</text>
							</view>
							<view class='pro-info line1'>{{item.store_name}}</view>
							<view class='money font-color'>￥{{item.price}}</view>
						</view>
					</block>
				</scroll-view>
			</view>
		</view>
		<view class='wrapper' v-if="!isShow && isIframe && firstList.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>
						{{titleInfo[0].val}}
						<text class='new font-color'>NEW~</text>
					</view>
					<view class='line1'>{{titleInfo[1].val}}</view>
				</view>
				<view class='more' @click="gopage('/pages/columnGoods/HotNewGoods/index?type=3')">
					更多
					<text class='iconfont icon-jiantou'></text>
				</view>
			</view>
			<view class='newProducts'>
				<scroll-view class="scroll-view_x" scroll-x style="width:auto;overflow:hidden;">
					<block v-for="(item,index) in firstList" :key='index'>
						<view class='item' @click="goDetail(item)">
							<view class='img-box'>
								<image :src='item.image'></image>
								<text class="pictrue_log_medium pictrue_log_class" v-if="item.activity && item.activity.type ==='1'">
									秒杀
								</text>
								<text class="pictrue_log_medium pictrue_log_class" v-if="item.activity && item.activity.type === '2'">
									砍价
								</text>
								<text class="pictrue_log_medium pictrue_log_class" v-if="item.activity && item.activity.type === '3'">
									拼团
								</text>
							</view>
							<view class='pro-info line1'>{{item.store_name}}</view>
							<view class='money font-color'>￥{{item.price}}</view>
						</view>
					</block>
				</scroll-view>
			</view>
		</view>
		<view class='wrapper' v-if="isIframe && !firstList.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>
						{{titleInfo[0].val}}
						<text class='new font-color'>NEW~</text>
					</view>
					<view class='line1'>{{titleInfo[1].val}}</view>
				</view>
				<view class='more' @click="gopage('/pages/columnGoods/HotNewGoods/index?type=3')">
					更多
					<text class='iconfont icon-jiantou'></text>
				</view>
			</view>
			<view class='newProducts'>
				<view class="empty-img">暂无数据</view>
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
		getGroomList
	} from '@/api/store.js';
	export default {
		name: 'i_new_goods',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			activeName: {
				type: String,
				default: ''
			},
		},
		computed: {
			...mapState({
				datas: state => state.indexData.indexDatas.info
			}),
			...mapGetters(['uid']),
		},
		watch: {
			'datas': {
				handler(nVal, oVal) {
					this.firstList = nVal.firstList
				},
				deep: true
			},
			activeName: {
				handler(nVal, oVal) {
					if (nVal == this.name && app.globalData.isIframe) {
						this.isBorader = true
					} else {
						this.isBorader = false
					}
				},
				deep: true
			}
		},
		created() {
			if (this.dataConfig.titleInfo) {
				this.titleInfo = this.dataConfig.titleInfo.list
			}
			if (this.dataConfig.isShow) {
				this.isShow = this.dataConfig.isShow.val
			}
			this.getGroomList();
		},
		mounted() {
			if (this.datas) {
				this.firstList = this.datas.firstList
			}
		},
		data() {
			return {
				firstList: [],
				firstInfo: "多个优质商品最新上架",
				titleInfo: [],
				isBorader: false,
				name: this.$options.name,
				isShow: true,
				isIframe: app.globalData.isIframe,
				numConfig: this.dataConfig.numConfig.val
			}
		},
		methods: {
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
			getGroomList() {
				getGroomList(3, {
					page: 1,
					limit: this.numConfig
				}).then(res => {
					this.firstList = res.data.list;
				})
			}
		}
	}
</script>

<style lang="scss">
	.wrapper .newProducts {
		white-space: nowrap;
		padding: 0 30rpx;
		margin: 35rpx 0 42rpx 0;
	}

	.wrapper .newProducts .item {
		display: inline-block;
		width: 240rpx;
		margin-right: 20rpx;
		border: 1rpx solid #eee;
		border-radius: 12rpx;
	}

	.wrapper .newProducts .item:nth-last-child(1) {
		margin-right: 0;
	}

	.wrapper .newProducts .item .img-box {
		width: 100%;
		height: 240rpx;
		position: relative;
	}

	.wrapper .newProducts .item .img-box image {
		width: 100%;
		height: 100%;
		border-radius: 12rpx 12rpx 0 0;
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
		font-size: 26rpx;
		font-weight: bold;
	}

	.empty-img {
		width: 100%;
		height: 375rpx;
		line-height: 375rpx;
		background: #ccc;
		font-size: 40rpx;
		color: #666;
		text-align: center;
	}
</style>
