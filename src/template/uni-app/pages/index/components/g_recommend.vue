<template>
	<view :class="{borderShow:isBorader}">
		<view class='index-wrapper' v-if="isShow && bastList.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>{{titleInfo[0].val}}</view>
					<view class='line1'>{{titleInfo[1].val}}</view>
				</view>
				<view class='more' @click="gopage('/pages/columnGoods/HotNewGoods/index?type=1')">
					更多
					<text class='iconfont icon-jiantou'></text>
				</view>
			</view>
			<view class='boutique'>
				<swiper autoplay="true" indicator-dots="true" :circular="circular" :interval="interval" :duration="duration"
				 indicator-color="rgba(252,65,65,0.3)" indicator-active-color="#fc4141">
					<block v-for="(item,index) in bastBanner">
						<swiper-item :key='index'>
							<view style='width:100%;height:100%;' hover-class='none' @click="gopage(item.info[0].value)">
								<image :src="item.img" class="slide-image" />
							</view>
						</swiper-item>
					</block>
				</swiper>
			</view>
			<goodList :bastList="bastList"></goodList>
		</view>
		<view class='index-wrapper' v-if="!isShow && isIframe && bastList.length">
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>{{titleInfo[0].val}}</view>
					<view class='line1'>{{titleInfo[1].val}}</view>
				</view>
				<view class='more' @click="gopage('/pages/columnGoods/HotNewGoods/index?type=1')">
					更多
					<text class='iconfont icon-jiantou'></text>
				</view>
			</view>
			<view class='boutique'>
				<swiper autoplay="true" indicator-dots="true" :circular="circular" :interval="interval" :duration="duration"
				 indicator-color="rgba(252,65,65,0.3)" indicator-active-color="#fc4141">
					<block v-for="(item,index) in bastBanner" :key='index'>
						<swiper-item>
							<view style='width:100%;height:100%;' hover-class='none' @click="gopage(item.info[0].value)">
								<image :src="item.img" class="slide-image" />
							</view>
						</swiper-item>
					</block>
				</swiper>
			</view>
			<goodList :bastList="bastList"></goodList>
		</view>
		<view v-if="isIframe && !bastList.length" class='index-wrapper'>
			<view class='title acea-row row-between-wrapper'>
				<view class='text'>
					<view class='name line1'>{{titleInfo[0].val}}</view>
					<view class='line1'>{{titleInfo[1].val}}</view>
				</view>
				<view class='more' @click="gopage('/pages/columnGoods/HotNewGoods/index?type=1')">
					更多
					<text class='iconfont icon-jiantou'></text>
				</view>
			</view>
			<view class='boutique'>
				<swiper autoplay="true" indicator-dots="true" :circular="circular" :interval="interval" :duration="duration"
				 indicator-color="rgba(252,65,65,0.3)" indicator-active-color="#fc4141">
					<block v-for="(item,index) in bastBanner" :key='index'>
						<swiper-item>
							<view style='width:100%;height:100%;' hover-class='none' @click="gopage(item.info[0].value)">
								<image :src="item.img" class="slide-image" />
							</view>
						</swiper-item>
					</block>
				</swiper>
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
	import goodList from '@/components/goodList/index.vue'
	import {
		getGroomList
	} from '@/api/store.js';
	export default {
		name: 'g_recommend',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			activeName: {
				type: String,
				default: ''
			}
		},
		components: {
			goodList
		},
		computed: {
			...mapState({
				datas: state => state.indexData.indexDatas.info,
			}),
		},
		created() {
			if (this.dataConfig.imgList) {
				this.bastBanner = this.dataConfig.imgList.list
			}
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
				this.bastList = this.datas.bastList
			}
		},
		watch: {
			'datas': {
				handler(nVal, oVal) {
					this.bastList = nVal.bastList
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
		data() {
			return {
				circular: true,
				interval: 3000,
				duration: 500,
				bastList: [],
				bastBanner: [],
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
					if (url.indexOf("http") != -1) {
						// #ifdef H5
						location.href = url
						// #endif
					} else {
						if (['/pages/goods_cate/goods_cate', '/pages/order_addcart/order_addcart', '/pages/user/index'].indexOf(url) ==
							-1) {

							uni.navigateTo({
								url: url
							})
						} else {
							uni.navigateTo({
								url: url
							})
						}
					}
				})
			},
			getGroomList() {
				getGroomList(1, {
					page: 1,
					limit: this.numConfig
				}).then(res => {
					const {
						banner,
						list
					} = res.data;
					if (list.length) {
						this.bastList = list;
					}
				})
			}
		}
	}
</script>

<style lang="scss">
	.boutique {
		width: 690rpx;
		height: 300rpx;
		margin: 28rpx auto 0 auto;
	}

	.boutique swiper {
		width: 100%;
		height: 100%;
		position: relative;
	}

	.boutique image {
		width: 100%;
		height: 260rpx;
	}

	.boutique .wx-swiper-dot {
		width: 7rpx;
		height: 7rpx;
		border-radius: 50%;
	}

	.boutique .wx-swiper-dot-active {
		width: 20rpx;
		border-radius: 5rpx;
	}

	.boutique .wx-swiper-dots.wx-swiper-dots-horizontal {
		margin-bottom: -8rpx;
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
