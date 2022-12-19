<template>
	<view class="recommend acea-row row-between-wrapper">
		<view class="pictrue acea-row row-center-wrapper" v-if="isIframe && !recommendList.length">
			<view style="text-align: center;">
				<text class="iconfont icon-icon25201"></text>
				<view>{{$t(`广告位`)}}</view>
			</view>
		</view>
		<view class="pictrue" v-for="(item, index) in recommendList" :key="index" @click="goDetail(item)" v-if="recommendList.length && isShow && !isIframe">
			<image :src="item.img"></image>
		</view>
		<view class="pictrue" v-for="(item, index) in recommendList" :key="index" @click="goDetail(item)" v-if="recommendList.length && isIframe">
			<image :src="item.img"></image>
		</view>
	</view>
</template>

<script>
	let app = getApp();
	import {
		goPage
	} from '@/libs/order.js';
	export default {
		name: 'adsRecommend',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if(nVal){
						this.recommendList = nVal.imgList.list;
						this.isShow = nVal.isShow.val;
					}
				}
			}
		},
		data() {
			return {
				recommendList: [],
				name:this.$options.name,//component组件固定写法获取当前name；
				isIframe:app.globalData.isIframe,//判断是前台还是后台；
				isShow:true//判断此模块是否显示；
			};
		},
		created() {
		},
		mounted() {
		},
		methods: {
			goDetail(item){
				goPage(item).then(res=>{
					uni.navigateTo({
						url: item.info[0].value
					})
				})
			}
		}
	}
</script>

<style lang="scss">
	.recommend{
		padding: 0 30rpx;
		margin-top: 26rpx;
		.pictrue{
			width: 338rpx;
			height: 206rpx;
			background-color: #ccc;
			border-radius: 8rpx;
			margin-bottom: 15rpx;
			
			image{
				width: 100%;
				height: 100%;
			}
			
			.iconfont{
				font-size: 50rpx;
			}
		}
	}
	.recommend .pictrue:nth-last-child(1) {
	    margin-bottom: 0;
	}
	.recommend .pictrue:nth-last-child(2) {
	    margin-bottom: 0;
	}
</style>

