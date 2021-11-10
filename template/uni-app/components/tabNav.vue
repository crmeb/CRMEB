<template>
	<view class="navTabBox">
		<view class="longTab">
			<scroll-view scroll-x="true" style="white-space: nowrap; display: flex;" scroll-with-animation :scroll-left="tabLeft" show-scrollbar="true">
				<view class="longItem" :style='"width:"+isWidth+"px"' :data-index="index" :class="index===tabClick?'click':''" v-for="(item,index) in tabTitle" :key="index" :id="'id'+index" @click="longClick(index)">{{item.cate_name}}</view>
				<view class="underlineBox" :style='"transform:translateX("+isLeft+"px);width:"+isWidth+"px"'>
					<view class="underline"></view>
				</view>
			</scroll-view>
		</view>
		<view class="child-box" v-if="tabClick>0 && tabTitle[tabClick].children.length>0">
			<scroll-view scroll-x="true" style="white-space: nowrap; display: flex;align-items: center; height: 100%;" scroll-with-animation :scroll-left="tabLeft" show-scrollbar="false">
				<view class="wrapper">
					<view v-for="(item,index) in tabTitle[tabClick].children" class="child-item" :class="{on:index == childIndex}" @click="childTab(tabClick,index)">
						<image :src="item.pic" mode=""></image>
						<view class="txt">{{item.cate_name}}</view>
					</view>
				</view>
			</scroll-view>
		</view>
	</view>
</template>

<script>
	import {
		getProductslist,
		getProductHot
	} from '@/api/store.js';
	export default {
		name: 'navTab',
		props: {
			tabTitle: {
				type: Array,
				default: []
			}

		},
		data() {
			return {
				tabClick: 0, //导航栏被点击
				isLeft: 0, //导航栏下划线位置
				isWidth: 0, //每个导航栏占位
				tabLeft:0,
				swiperIndex:0,
				childIndex:0,
				childID:0
			};
		},
		created() {
			
			var that = this
			// 获取设备宽度
			uni.getSystemInfo({
				success(e) {
					that.isWidth = e.windowWidth / 5 
				}
			})
		},
		methods: {
			// 导航栏点击
			longClick(index){
				this.childIndex = 0;
				if(this.tabTitle.length>5){
					var tempIndex = index - 2;
					tempIndex = tempIndex<=0 ? 0 : tempIndex;
					this.tabLeft = (index-2) * this.isWidth //设置下划线位置
				}
				this.tabClick = index //设置导航点击了哪一个
				this.isLeft = index * this.isWidth //设置下划线位置
				let obj = {
					type:'big',  //大标题
					index:index
				}
				this.parentEmit(obj)
				// this.$parent.currentTab = index //设置swiper的第几页
			},
			// 导航子类点击
			childTab(tabClick,index){
				this.childIndex = index
				let obj = {
					parentIndex:tabClick,
					childIndex:index,
					type:'small' //小标题
				}
				this.parentEmit(obj)
			},
			parentEmit(data){
				this.$emit('changeTab', data);
			}
		}
	}
</script>

<style lang="scss" scoped>
	.navTabBox {
		width: 100%;
		color: rgba(255, 255, 255, 1);
		.click {
			color: white;
		}
		.longTab {
			width: 100%;
			/* #ifdef H5 */
			padding-bottom: 20rpx;
			/* #endif */
			/* #ifdef MP */
			padding-top: 12rpx;
			padding-bottom: 12rpx;
			/* #endif */
			.longItem{ 
				height: 50upx; 
				display: inline-block;
				line-height: 50upx;
				text-align: center;
				font-size: 30rpx;
				&.click{
					font-weight: bold;
				}
			}
			.underlineBox {
				height: 3px;
				width: 20%;
				display: flex;
				align-content: center;
				justify-content: center;
				transition: .5s;
				.underline {
					width: 33rpx;
					height: 4rpx;
					background-color: white;
				}
			}
		}
	}
	.child-box{
		width: 100%;
		position: relative;
		// height: 152rpx;
		background-color: #fff;
		/* #ifdef H5 */
		box-shadow: 0 2px 5px 1px rgba(0, 0, 0, 0.02);
		/* #endif */
		/* #ifdef MP */
		box-shadow: 0 2rpx 3rpx 1rpx #f9f9f9;
		/* #endif */
		
		.wrapper{
			display: flex;
			align-items: center;
			padding: 20rpx 0;
			background: #fff;
			/* #ifdef H5 */
			//box-shadow: 0 2px 5px 1px rgba(0, 0, 0, 0.06);
			/* #endif */
		}
		.child-item{
			flex-shrink: 0;
			width:140rpx;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			margin-left: 10rpx;
			image{
				width: 90rpx;
				height: 90rpx;
				border-radius: 50%;
			}
			.txt{
				font-size: 24rpx;
				color: #282828;
				text-align: center;
				margin-top: 10rpx;
			}
			&.on{
				image{
					border: 1px solid $theme-color-opacity;
				}
				.txt{
					color: $theme-color;
				}
			}
		}
	}
</style>
