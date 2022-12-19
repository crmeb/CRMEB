<template>
	<view>
		<view v-if="isShow && tabTitle.length">
			<!-- #ifdef MP -->
			<view style="visibility: hidden;" :style="{ height: navHeight + 'px' }" v-if="isFixed"></view>
			<!-- #endif -->
			<view class="navTabBox" :style="'color:'+txtColor+';'" :class="{isFixed:isFixed}">
				<view class="longTab">
					<scroll-view scroll-x="true" style="white-space: nowrap; display: flex;  align-items: center; height: 50rpx;" scroll-with-animation
						:scroll-left="tabLeft" show-scrollbar="true">
						<view :url="'/pages/goods/goods_list/index?cid='+item.id+'&title='+item.cate_name" class="longItem"
							:style='"width:"+"max-content"' :data-index="index" :class="index===tabClick?'click':''"
							v-for="(item,index) in tabTitle" :key="index" :id="'id'+index"
							@click="longClick(item,index)">
							{{$t(item.cate_name)}}
						</view>
						<!-- 		<view class="underlineBox" :style='"transform:translateX("+isLeft+"px);width:"+isWidth+"px"'>
							<view class="underline"></view>
						</view> -->
					</scroll-view>
				</view>
			</view>
		</view>
		<view v-if="!isShow && tabTitle.length && isIframe">
			<!-- #ifdef MP -->
			<view style="visibility: hidden;" :style="{ height: navHeight + 'px' }" v-if="isFixed"></view>
			<!-- #endif -->
			<view class="navTabBox" :style="'color:'+txtColor+';top:'+isTop" :class="{isFixed:isFixed}">
				<view class="longTab">
					<scroll-view scroll-x="true" style="white-space: nowrap; display: flex; align-items: center;" scroll-with-animation
						:scroll-left="tabLeft" show-scrollbar="true">
						
						<view :url="'/pages/goods/goods_list/index?cid='+item.id+'&title='+item.cate_name" class="longItem"
							:style='"width:"+isWidth+"px"' :data-index="index" :class="index===tabClick?'click':''"
							v-for="(item,index) in tabTitle" :key="index" :id="'id'+index"
							@click="longClick(item,index)">
							{{$t(item.cate_name)}}
						</view>
						<view class="underlineBox" :style='"transform:translateX("+isLeft+"px);width:"+isWidth+"px"'>
							<view class="underline"></view>
						</view>
					</scroll-view>
				</view>
			</view>
		</view>
		<view class='index-wrapper' v-else-if="isIframe && !tabTitle.length">
			<view class='scroll-product'>
				<view class="empty-img">{{$t(`暂无数据，请先添加分类`)}}</view>
			</view>
		</view>
	</view>
</template>

<script>
	let app = getApp()
	import {
		getCategoryList
	} from '@/api/store.js';
	export default {
		name: 'tabNav',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isFixed: {
				type: Boolean | String | Number,
				default: false
			}
		},
		data() {
			return {
				tabTitle: [],
				isIframe: app.globalData.isIframe,
				tabLeft: 0,
				isWidth: 0, //每个导航栏占位
				tabClick: 0, //导航栏被点击
				isLeft: 0, //导航栏下划线位置
				bgColor: '',
				// #ifdef H5
				mbConfig: 0,
				// #endif
				// #ifdef MP
				mbConfig: 45,
				// #endif
				txtColor: '#333333',
				fixedTop: 0,
				isTop: 0,
				navHeight: 0,
				isShow: true
			};
		},
		watch: {
			dataConfig: {
				immediate: true,
				handler(nVal, oVal) {
					if (nVal) {
						this.isShow = nVal.isShow.val;
					}
				}
			}
		},
		created() {
			let that = this;
			that.getAllCategory();
			// 获取设备宽度
			uni.getSystemInfo({
				success(e) {
					that.isWidth = 100
				}
			})
		},
		methods: {
			// 导航栏点击
			longClick(item, index) {
				if (this.tabTitle.length > 4) {
					this.tabLeft = (index - 2) * this.isWidth //设置下划线位置
				}
				this.tabClick = index //设置导航点击了哪一个
				this.isLeft = index * this.isWidth //设置下划线位置

				this.$emit('bindSortId', item.id)
				// setTimeout(e => {
				// 	uni.pageScrollTo({
				// 		scrollTop: this.domOffsetTop - 130,
				// 		duration: 300
				// 	})
				// }, 1000)
			},
			// 获取导航
			getAllCategory: function() {
				let that = this;
				getCategoryList().then(res => {
					// res.data.unshift({
					// 	"id": -99,
					// 	'cate_name': '精选'
					// })
					this.$emit('bindSortId', res.data[0].id)
					that.tabTitle = res.data;
				})
			}
		}
	}
</script>

<style lang="scss" scoped>
	.navTabBox {
		// width: 100%;
		// background: linear-gradient(90deg, $bg-star 50%, $bg-end 100%);
		color: #E93323;
		padding: 0 30rpx;
		
		
		// &.isFixed {
		// 	z-index: 10000;
		// 	position: fixed;
			
		// 	// left: 0;
		// 	top: 0;
		// 	// left: 0;
		// 	background-color: #fff;
		// 	// // background: linear-gradient(90deg, var(--view-main-start) 0%, var(--view-main-over) 100%);
		// 	// color: #333 !important;
		// 	// width: 100%;

		// 	// /* #ifdef H5 */
		// 	// padding-top: 20rpx;
		// 	// top: 0;
		// 	// /* #endif */

		// }

		.longTab {
			width: 100%;

			.longItem {
				height: 50rpx;
				display: inline-block;
				line-height: 50rpx;
				text-align: center;
				font-size: 34rpx;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
				margin-right: 40rpx;

				&.click {
					font-size: 36rpx;
					font-weight: bold;
					color: var(--view-theme);
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
					background-color: #E93323;
				}
			}
		}
	}

	.empty-img {
		width: 690rpx;
		height: 200rpx;
		border-radius: 14rpx;
		margin: 26rpx auto 0 auto;
		background-color: #ccc;
		text-align: center;
		line-height: 200rpx;

		.iconfont {
			font-size: 50rpx;
		}
	}
	.sticky-box {
		/* #ifdef APP-PLUS || MP */
		display: flex;
		position: -webkit-sticky;
		/* #endif */
		position: sticky;
		z-index: 998;
		flex-direction: row;
		margin: 0px;
		background: #f5f5f5;
		padding: 10rpx 0;
		/* #ifdef MP || APP-PLUS*/
		//top: 110rpx;
		/* #endif */
		// overflow-x: scroll;
		/deep/ .uni-scroll-view-content{
			display: flex;
			align-items: center;
			width: max-content;
		}
	}
</style>
