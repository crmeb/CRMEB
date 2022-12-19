<template>
	<view v-show="!isSortType" :style="{padding:'0 '+prConfig*2+'rpx'}">
		<view class="articleList" :class="{borderRadius15:bgStyle===1}" :style="'background-color:'+bgColor+';margin-top:'+ mbConfig*2 +'rpx;'" v-if="articleList.length">
			<view v-if="listStyle">
				<navigator :url='"/pages/extension/news_details/index?id="+item.id' hover-class='none' class="item acea-row row-between-wrapper" :class="conStyle?'borderRadius15':''"
				 :style="'margin-bottom:'+itemConfig*2+'rpx;'" v-for="(item,index) in articleList" :key='index'>
					<view class="pictrue">
						<image :src="item.image_input[0]" mode="aspectFill"></image>
					</view>
					<view class="text">
						<view class="name line2">{{item.title}}</view>
						<view class="time">{{item.add_time}}</view>
					</view>
				</navigator>
			</view>
			<view v-else>
				<navigator :url='"/pages/extension/news_details/index?id="+item.id' hover-class='none' class="item acea-row row-between-wrapper" :class="conStyle?'borderRadius15':''"
				 :style="'margin-bottom:'+itemConfig*2+'rpx;'" v-for="(item,index) in articleList" :key='index'>
					<view class="text">
						<view class="name line2">{{item.title}}</view>
						<view class="time">{{item.add_time}}</view>
					</view>
					<view class="pictrue">
						<image :src="item.image_input[0]" mode="aspectFill"></image>
					</view>
				</navigator>
			</view>
		</view>
	</view>
</template>

<script>
	import {
		getArticleList
	} from '@/api/api.js';
	export default {
		name: 'articleList',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isSortType:{
				type: String | Number,
				default:0
			}
		},
		data() {
			return {
				articleList: [],
				numConfig: this.dataConfig.numConfig.val,
				selectConfig: this.dataConfig.selectConfig.activeValue,
				listStyle: this.dataConfig.listStyle.type,
				bgColor: this.dataConfig.bgColor.color[0].item,
				itemConfig: this.dataConfig.itemConfig.val,
				mbConfig: this.dataConfig.mbConfig.val,
				bgStyle: this.dataConfig.bgStyle.type,//背景样式
				prConfig: this.dataConfig.prConfig.val,//背景边距
				conStyle: this.dataConfig.conStyle.type,//内容样式
			}
		},
		created() {},
		mounted() {
			this.getCidArticle();
		},
		methods: {
			getCidArticle: function() {
				let that = this;
				let limit = this.$config.LIMIT;
				getArticleList(that.selectConfig || 0, {
					page: 1,
					limit: this.numConfig >= limit ? limit : this.numConfig
				}).then(res => {
					that.articleList = res.data;
				});
			},
		}
	}
</script>

<style lang="scss">
	.articleList {
		background-color: #fff;
		padding: 20rpx 0;

		.item {
			padding: 20rpx;
			background-color: #fff;
			margin: 0 20rpx;
			&:last-child{
				margin-bottom: 0!important;
			}
			
			.text {
				width: 60%;

				.name {
					font-size: 30rpx;
					color: #282828;
					height: 82rpx;
				}

				.time {
					font-size: 24rpx;
					color: #999;
					margin-top: 40rpx;
				}
			}

			.pictrue {
				width: 37%;
				height: 156rpx;
				border-radius: 6rpx;

				image {
					width: 100%;
					height: 100%;
					border-radius: 6rpx;
				}
			}
		}
	}
</style>
