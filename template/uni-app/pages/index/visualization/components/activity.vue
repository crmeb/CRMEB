<template>
	<view>
		<view v-if="isShow" class="specialArea acea-row row-between-wrapper">
			<view class="assemble skeleton-rect" hover-class="none" @click="gopage(activityOne.info[2].value)">
				<!-- <easy-loadimage mode="widthFix" :image-src="activityOne.img"></easy-loadimage> -->
				<image :src="activityOne.img" alt="" srcset="">
					<!-- <view class="text" v-if="activityOne.info">
					<view class="name">{{ activityOne.info[0].value }}</view>
					<view class="infor">{{ activityOne.info[1].value }}</view>
				</view> -->
			</view>
			<view class="list acea-row row-column-between">
				<view class="item skeleton-rect" v-for="(item, index) in activity" :key="index"
					@click="gopage(item.info[2].value)">
					<easy-loadimage mode="widthFix" :image-src="item.img"></easy-loadimage>
					<!-- 	<view class="text">
						<view class="name">{{ item.info[0].value }}</view>
						<view class="infor">{{ item.info[1].value}}</view>
					</view> -->
				</view>
			</view>
		</view>
		<view v-if="!isShow && isIframe" class="specialArea acea-row row-between-wrapper">
			<view class="assemble" hover-class="none" @click="gopage(activityOne.info[2].value)">
				<image :src="activityOne.img" alt="img" />
				<!-- <view class="text" v-if="activityOne.info">
					<view class="name">{{ activityOne.info[0].value }}</view>
					<view class="infor">{{ activityOne.info[1].value }}</view>
				</view> -->
			</view>
			<view class="list acea-row row-column-between">
				<view class="item" v-for="(item, index) in activity" :key="index" @click="gopage(item.info[2].value)">
					<image :src="item.img" alt="img" />
					<!-- <view class="text">
						<view class="name">{{ item.info[0].value }}</view>
						<view class="infor">{{ item.info[1].value}}</view>
					</view> -->
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	let app = getApp()
	import {
		goPage
	} from '@/libs/order.js'
	export default {
		name: 'activity',
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
						let data = JSON.parse(JSON.stringify(nVal.imgList.list))
						this.activityOne = nVal.imgList.list[0]
						data.splice(0, 1)
						this.activity = data
						this.isShow = nVal.isShow.val
					}
				}
			}
		},
		data() {
			return {
				activity: [],
				activityOne: {},
				name: this.$options.name,
				isShow: true,
				isIframe: app.globalData.isIframe
			}
		},
		created() {},
		methods: {
			gopage(url) {
				goPage().then(res => {
					if (url.indexOf("http") != -1) {
						// #ifdef H5
						location.href = url
						// #endif
					} else {
						if (['/pages/goods_cate/goods_cate', '/pages/order_addcart/order_addcart',
								'/pages/user/index'
							].indexOf(url) ==
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
			}
		}
	}
</script>

<style lang="scss" scoped>
	.specialArea {
		background: linear-gradient(180deg, #fff 0%, #f5f5f5 100%);
		// background-color: $uni-bg-color;
		padding: 0 $uni-index-margin-col 0 $uni-index-margin-col;
		border-radius: $uni-border-radius-index;
		// box-shadow: $uni-index-box-shadow;
	}

	.specialArea .assemble {
		width: 336rpx;
		height: 300rpx;
		position: relative;
	}

	.specialArea .assemble image {
		width: 100%;
		height: 100%;
		border-radius: 5rpx;
	}

	.specialArea .assemble {

		/deep/,
		/deep/image,
		/deep/.easy-loadimage,
		/deep/uni-image {

			width: 336rpx;
			height: 300rpx;
			border-radius: 5rpx;
		}
	}

	.specialArea .assemble .text {
		position: absolute;
		top: 37rpx;
		left: 22rpx;
	}

	.specialArea .name {
		font-size: 30rpx;
		color: #fff;
	}

	.specialArea .infor {
		font-size: 22rpx;
		color: rgba(255, 255, 255, 0.8);
		margin-top: 5rpx;
	}

	.specialArea .list {
		width: 344rpx;
		height: 300rpx;
	}

	.specialArea .item {
		width: 100%;
		height: 146rpx;
		position: relative;
	}

	.specialArea .item img {
		width: 100%;
		height: 100%;
	}

	.specialArea .item {

		/deep/,
		/deep/image,
		/deep/.easy-loadimage,
		/deep/uni-image {

			width: 100%;
			height: 146rpx;
		}
	}

	.specialArea .item .text {
		position: absolute;
		top: 23rpx;
		left: 28rpx;
	}

	.specialArea .item image {
		width: 100%;
		height: 100%;
	}

	.specialArea .item .text {
		position: absolute;
		top: 23rpx;
		left: 28rpx;
	}
</style>
