<template>
	<view class="index-wrapper" :class="{borderShow:isBorader}">
		<view class='wrapper' v-if="isShow && benefit.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>{{titleInfo[0].val}}</view>
					<view class='line1'>{{titleInfo[1].val}}</view>
				</view>
				<view class='more' @click="gopage('/pages/columnGoods/HotNewGoods/index?type=4')">更多<text class='iconfont icon-jiantou'></text></view>
			</view>
			<promotionGood :benefit="benefit"></promotionGood>
		</view>
		<view class='wrapper' v-if="!isShow && isIframe && benefit.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>{{titleInfo[0].val}}</view>
					<view class='line1'>{{titleInfo[1].val}}</view>
				</view>
				<view class='more' @click="gopage('/pages/columnGoods/HotNewGoods/index?type=4')">更多<text class='iconfont icon-jiantou'></text></view>
			</view>
			<promotionGood :benefit="benefit"></promotionGood>
		</view>
		<view class='wrapper' v-if="isIframe && !benefit.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>{{titleInfo[0].val}}</view>
					<view class='line1'>{{titleInfo[1].val}}</view>
				</view>
				<view class='more' @click="gopage('/pages/columnGoods/HotNewGoods/index?type=4')">更多<text class='iconfont icon-jiantou'></text></view>
			</view>
			<view class="empty-img">暂无数据</view>
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
	import promotionGood from '@/components/promotionGood/index.vue';
	import {
		getGroomList
	} from '@/api/store.js';
	export default {
		name: 'j_promotion',
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
		components: {
			promotionGood
		},
		computed: {
			...mapState({
				datas: state => state.indexData.indexDatas
			})
		},
		watch: {
			'datas': {
				handler(nVal, oVal) {
					this.benefit = nVal.benefit
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
			this.isIframe = app.globalData.isIframe;
			if (this.dataConfig.titleInfo) {
				this.titleInfo = this.dataConfig.titleInfo.list
			}
			if (this.dataConfig.isShow) {
				this.isShow = this.dataConfig.isShow.val
			}
			this.getGroomList();
		},
		mounted() {
			if (this.datas.benefit) {
				this.benefit = this.datas.benefit
			}
		},
		data() {
			return {
				benefit: [],
				salesInfo: "库存商品优惠促销活动",
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
			getGroomList() {
				getGroomList(4, {
					page: 1,
					limit: this.numConfig
				}).then(res => {
					this.benefit = res.data.list;
				});
			}
		}
	}
</script>

<style>
	.empty-img {
		width: calc(100% - 60rpx);
		height: 375rpx;
		margin: 0 auto;
		line-height: 375rpx;
		background: #ccc;
		font-size: 40rpx;
		color: #666;
		text-align: center;
	}
</style>
