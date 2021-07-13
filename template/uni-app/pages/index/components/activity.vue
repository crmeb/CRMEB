<template>
	<view>
		<view v-if="isShow" class="specialArea acea-row row-between-wrapper">
			<view class="assemble" hover-class="none" @click="gopage(activityOne.info[2].value)">
				<image :src="activityOne.img" alt="img" />
				<view class="text" v-if="activityOne.info">
					<view class="name">{{ activityOne.info[0].value }}</view>
					<view class="infor">{{ activityOne.info[1].value }}</view>
				</view>
			</view>
			<view class="list acea-row row-column-between">
				<view class="item" v-for="(item, index) in activity" :key="index" @click="gopage(item.info[2].value)">
					<image :src="item.img" alt="img" />
					<view class="text">
						<view class="name">{{ item.info[0].value }}</view>
						<view class="infor">{{ item.info[1].value}}</view>
					</view>
				</view>
			</view>
		</view>
		<view v-if="!isShow && isIframe" class="specialArea acea-row row-between-wrapper">
			<view class="assemble" hover-class="none" @click="gopage(activityOne.info[2].value)">
				<image :src="activityOne.img" alt="img" />
				<view class="text" v-if="activityOne.info">
					<view class="name">{{ activityOne.info[0].value }}</view>
					<view class="infor">{{ activityOne.info[1].value }}</view>
				</view>
			</view>
			<view class="list acea-row row-column-between">
				<view class="item" v-for="(item, index) in activity" :key="index" @click="gopage(item.info[2].value)">
					<image :src="item.img" alt="img" />
					<view class="text">
						<view class="name">{{ item.info[0].value }}</view>
						<view class="infor">{{ item.info[1].value}}</view>
					</view>
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
					if(nVal){
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
		created() {
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
			}
		}
	}
</script>

<style lang="scss">
	.specialArea {
		padding: 30rpx;
	}

	.specialArea .assemble {
		width: 260rpx;
		height: 260rpx;
		position: relative;
	}

	.specialArea .assemble image {
		width: 100%;
		height: 100%;
		border-radius: 5rpx;
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
		height: 260rpx;
		width: 416rpx;
	}

	.specialArea .item {
		width: 100%;
		height: 124rpx;
		position: relative;
	}

	.specialArea .item img {
		width: 100%;
		height: 100%;
	}

	.specialArea .item .text {
		position: absolute;
		top: 23rpx;
		left: 28rpx;
	}

	.specialArea .list {
		height: 260rpx;
		width: 416rpx;
	}

	.specialArea .item {
		width: 100%;
		height: 124rpx;
		position: relative;
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
