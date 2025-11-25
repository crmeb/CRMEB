<template>
	<!-- 新闻播报 -->
	<view :style="[newsStyle]" v-if="newsList.length">
		<view class="news" v-if="dataConfig.styleConfig.tabVal" :style="[newsWrapperStyle]">
			<view class="acea-row row-between-wrapper news-top">
				<view v-if="dataConfig.titleConfig.tabVal" :style="[titleStyle]">{{ dataConfig.titleTxtConfig.value }}</view>
				<image v-else :src="dataConfig.imgConfig.url" mode="heightFix" class="image"></image>
				<view v-if="!dataConfig.buttonConfig.tabVal" class="more" 
					@click="moreTab(linkConfig)" :style="[moreStyle]">{{ $t(`更多`) }}<text
						class="iconfont icon-ic_rightarrow"></text></view>
			</view>
			<view class="news-bottom">
				<view v-for="(item, index) in newsList" :key="index" class="acea-row row-middle item" :style="[itemStyle]" @click="moreTab(item.chiild[1].val)">
					<view class="number">{{ index + 1 }}</view>
					<view class="text line1">{{ item.chiild[0].val }}</view>
				</view>
			</view>
		</view>
		<view class="news-scroll acea-row row-middle" :style="[newsWrapperStyle]" v-else>
			<view class="news-left">
				<view class="text" v-if="dataConfig.titleConfig.tabVal" :style="[leftStyle]">{{ dataConfig.titleTxtConfig.value }}</view>
				<image class="image" :src="dataConfig.imgConfig.url" mode="heightFix" v-else></image>
			</view>
			<view class="news-center" :style="[itemStyle]">
				<view v-if="dataConfig.rollConfig.tabVal" @click="moreTab(newsList[0].chiild[1].val)">
					<uniNoticeBar scrollable single showGetMore background-color="transparent" :color="dataConfig.newsColor.color[0].item" :speed="50"
						:text="newsList[0].chiild[0].val"></uniNoticeBar>
				</view>
				<swiper v-else class="swiper" :indicator-dots="indicatorDots" :autoplay="autoplay" interval="2500" :duration="duration" vertical="true" circular="true" @change='changeIndex'>
					<swiper-item v-for="(item,index) in newsList" :key="index">
						<view @click="moreTab(item.chiild[1].val)">{{ item.chiild[0].val }}</view>
					</swiper-item>
				</swiper>
			</view>
			<view v-if="!dataConfig.buttonConfig.tabVal" class="iconfont icon-ic_rightarrow" :style="[moreStyle]" @click="moreTab(newsList[swiperIndex].chiild[1].val)"></view>
		</view>
	</view>
</template>

<script>
	import uniNoticeBar from '@/components/uniNoticeBar/uni-notice-bar.vue';
	export default {
		components: {
			uniNoticeBar
		},
		name: 'news',
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
		},
		data() {
			return {
				indicatorDots: false,
				autoplay: true,
				duration: 500,
				newsList: [],
				swiperIndex: 0
			};
		},
		computed: {
			newsStyle() {
				return {
					padding: `${this.dataConfig.topConfig.val * 2}rpx ${this.dataConfig.prConfig.val * 2}rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
					marginTop: `${this.dataConfig.mbConfig.val * 2}rpx`,
					background: this.dataConfig.bottomBgColor.color[0].item,
				};
			},
			newsWrapperStyle() {
				let borderRadius = `${this.dataConfig.fillet.val * 2}rpx`;
				if (this.dataConfig.fillet.type) {
					borderRadius =
						`${this.dataConfig.fillet.valList[0].val * 2}rpx ${this.dataConfig.fillet.valList[1].val * 2}rpx ${this.dataConfig.fillet.valList[3].val * 2}rpx ${this.dataConfig.fillet.valList[2].val * 2}rpx`;
				}
				return {
					borderRadius,
					background: `linear-gradient(90deg, ${this.dataConfig.moduleColor.color[0].item} 0%, ${this.dataConfig.moduleColor.color[1].item} 100%)`,
				};
			},
			moreStyle() {
				return {
					color: this.dataConfig.bntColor.color[0].item,
				};
			},
			titleStyle() {
				return {
					color: this.dataConfig.titleColor.color[0].item,
				};
			},
			leftStyle(){
				return {
					background: this.dataConfig.toneConfig.tabVal ? `linear-gradient(90deg, ${this.dataConfig.titleBgColor.color[0].item} 0%, ${this.dataConfig.titleBgColor.color[1].item} 100%)` : 'var(--view-theme)',
					color: this.dataConfig.toneConfig.tabVal ? this.dataConfig.titleColor.color[0].item : '#ffffff'
				}
			},
			itemStyle() {
				return {
					color: this.dataConfig.newsColor.color[0].item,
				};
			},
			linkConfig(){
				return this.dataConfig.linkConfig.value ? this.dataConfig.linkConfig.value : '/pages/extension/news_list/index'
			}
		},
		mounted(){
			let list = this.dataConfig.listConfig.list;
			let newsList = [];
			list.forEach(item=>{
				if(item.show){
					newsList.push(item);
				}
			})
			this.newsList = newsList;
		},
		methods: {
			changeIndex(event){
				this.swiperIndex = event.detail.current;
			},
			moreTab(url) {
				this.$util.JumpPath(url);
			}
		}
	}
</script>

<style lang="scss">
	.news-scroll {
		height: 88rpx;
		padding: 0 20rpx;
		background: #FFFFFF;
	}

	.news-left {
		margin-right: 16rpx;

		.text {
			height: 40rpx;
			padding: 0 12rpx;
			border-radius: 8rpx;
			// background: #FCEAE9;
			font-weight: 500;
			font-size: 26rpx;
			line-height: 40rpx;
			// color: #E93323;
		}

		.image {
			display: block;
			// width: 48rpx;
			height: 48rpx;
		}
	}

	.news-scroll .news-center {
		flex: 1;
		min-width: 0;
		height: 36rpx;
		font-size: 26rpx;
		line-height: 36rpx;
		color: #333333;
	}

	.news-scroll .swiper {
		height: 36rpx;
	}

	.news-scroll .iconfont {
		margin-left: 16rpx;
		font-size: 24rpx;
	}

	.news {
		padding: 32rpx 24rpx;
		background: #FFFFFF;
	}

	.news-top {
		font-weight: 500;
		font-size: 32rpx;
		line-height: 36rpx;
		color: #333333;

		.image {
			display: block;
			// width: 140rpx;
			height: 36rpx;
		}

		.more {
			font-weight: 400;
			font-size: 24rpx;
			color: #999999;
		}

		.iconfont {
			font-size: 24rpx;
		}
	}

	.news-bottom {
		margin-top: 32rpx;

		.item {
			font-size: 28rpx;
			line-height: 40rpx;
			color: #282828;

			+.item {
				margin-top: 24rpx;
			}

			&:nth-child(1) {
				.number {
					color: #E93323;
				}
			}

			&:nth-child(2) {
				.number {
					color: #FF7300;
				}
			}

			&:nth-child(3) {
				.number {
					color: #FFC300;
				}
			}
		}

		.number {
			width: 30rpx;
			font-family: SemiBold;
			font-size: 30rpx;
			color: #999999;
		}

		.text {
			flex: 1;
			overflow: hidden;
			white-space: nowrap;
			text-overflow: ellipsis;
		}
	}
</style>