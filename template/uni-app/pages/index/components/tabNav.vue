<template>
	<!-- 商品分类 -->
	<view>
		<!-- #ifdef MP || APP-PLUS -->
		<!-- <view :style="{height: (40+dataConfig.topConfig.val*2+dataConfig.bottomConfig.val*2) + 'rpx'}" v-if="!fromType"></view> -->
		<!-- #endif -->
		<view :class="{
			tabNav1: dataConfig.styleConfig.tabVal == 0,
			tabNav2: dataConfig.styleConfig.tabVal == 1,
			tabNav3: dataConfig.styleConfig.tabVal == 2,
			isFixed: isFixed,
			on: isFixed && special
		}">
			<view :style="[tabNavStyle]">
				<view :style="[tabNavBgColor]">
					<scroll-view scroll-x="true" :scroll-left="tabLeft" scroll-with-animation>
						<view class="list acea-row row-middle">
							<view v-for="(item, index) in tabListConfig" :key="index" :class="{ on: index == tabClick }" :style="[
								index == tabClick ? textStyle : {}
							]" class="item" @click="longClick(item, index)">
								{{ item.text.val }}
								<text v-if="dataConfig.styleConfig.tabVal != 2" class="line" :style="[lineStyle]"></text>
							</view>
						</view>
					</scroll-view>
				</view>
			</view>
		</view>
		<view v-if="isFixed" style="height: 80rpx"></view>
	</view>
</template>

<script>
	import {
		getCategoryList
	} from '@/api/store.js';
	import {
		getCategoryVersion
	} from '@/api/api.js';
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
			},
			fromType: {
				type: Number,
				default: 0
			},
			special: {
				type: Number,
				default: 0
			}
		},
		data() {
			return {
				tabTitle: [],
				tabLeft: 0,
				isWidth: 0, //每个导航栏占位
				tabClick: 0, //导航栏被点击
				isLeft: 0, //导航栏下划线位置
				fixedTop: 0,
				isTop: 0,
				navHeight: 45,
				tabList: 45,
			};
		},
		computed: {
			lineStyle() {
				let styleObject = {};
				if (this.dataConfig.toneConfig.tabVal) {
					switch (this.dataConfig.styleConfig.tabVal) {
						case 0:
							styleObject['background'] = `linear-gradient(90deg, ${this.dataConfig.decorateColor.color[0].item} 0%, ${this.dataConfig.decorateColor.color[1].item} 100%)`;
							break;
						case 1:
							styleObject['border-bottom-color'] = this.dataConfig.decorateColor2.color[0].item;
							break;
					}
				}
				return styleObject;
			},
			textStyle() {
				let styleObject = {};
				if (this.dataConfig.toneConfig.tabVal) {
					switch (this.dataConfig.styleConfig.tabVal) {
						case 0:
							styleObject['color'] = this.dataConfig.textColor.color[0].item;
							break;
						case 1:
							styleObject['color'] = this.dataConfig.textColor2.color[0].item;
							break;
						case 2:
							styleObject['background'] = `linear-gradient(90deg, ${this.dataConfig.decorateColor.color[0].item} 0%, ${this.dataConfig.decorateColor.color[1].item} 100%)`;
							styleObject['color'] = this.dataConfig.textColor3.color[0].item;
							break;
					}
				}
				return styleObject;
			},
			tabNavBgColor() {
				let borderRadius = `${this.dataConfig.fillet.val * 2}rpx`;
				if (this.dataConfig.fillet.type) {
					borderRadius =
						`${this.dataConfig.fillet.valList[0].val * 2}rpx ${this.dataConfig.fillet.valList[1].val * 2}rpx ${this.dataConfig.fillet.valList[2].val * 2}rpx ${this.dataConfig.fillet.valList[3].val * 2}rpx`;
				}
				return {
					'border-radius': borderRadius,
					'background': `linear-gradient(90deg, ${this.dataConfig.moduleColor.color[0].item} 0%, ${this.dataConfig.moduleColor.color[1].item} 100%)`,
				};
			},
			tabNavStyle() {
				return {
					'padding': `${this.dataConfig.topConfig.val * 2}rpx ${this.dataConfig.prConfig.val * 2}rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
					'margin-top': `${this.dataConfig.mbConfig.val * 2}rpx`,
				};
			},
			tabListConfig() {
				let tabList = this.dataConfig.tabListConfig.list;
				tabList.unshift({
					classPage: {
						id: 0
					},
					dataType: {
						tabVal: 0
					},
					microPage: {
						id: 0
					},
					text: {
						val: '首页'
					},
				});
				return tabList
			},
		},
		created() {
			let that = this;
			that.getAllCategory();
			// 获取设备宽度
			uni.getSystemInfo({
				success(e) {
					that.isWidth = e.windowWidth / 5
				}
			})
		},
		methods: {
			// 导航栏点击
			longClick(item, index) {
				if (this.tabTitle.length > 5) {
					this.tabLeft = (index - 2) * this.isWidth //设置下划线位置
				}
				this.tabClick = index //设置导航点击了哪一个
				this.isLeft = index * this.isWidth //设置下划线位置
				let data = {
					type: item.dataType.tabVal, //0 商品分类 1 微页面
					microPage: item.microPage.id,
					classPage: item.classPage.id,
				};
				this.$emit('bindSortId', data);
			},
			setCategory(data) {
				data.unshift({
					"id": -99,
					'cate_name': '首页'
				})
				this.tabTitle = data;
				// #ifdef MP || APP-PLUS
				this.isTop = (uni.getSystemInfoSync().statusBarHeight + 43) + 'px'
				// #endif
				// #ifdef H5 
				this.isTop = 0
				// #endif
			},
			getCategory() {
				getCategoryList().then(res => {
					uni.setStorageSync('category', JSON.stringify(res.data));
					this.setCategory(res.data);
				})
			},
			// 获取导航
			getAllCategory: function() {
				let that = this;
				let category = uni.getStorageSync('category');
				this.getCategory();
			}
		}
	}
</script>

<style lang="scss">
	.tabNav1 {
		&.isFixed {
			z-index: 45;
			position: fixed;
			left: 0;
			width: 100%;
			/* #ifdef H5 */
			top: 0;
			/* #endif */
		}
		&.on{
			top:0
		}

		.list {
			flex-wrap: nowrap;
			height: 80rpx;
			margin-left: 24rpx;
		}

		.item {
			position: relative;
			z-index: 2;
			flex-shrink: 0;
			height: 80rpx;
			margin: 0 55rpx 0 0;
			font-size: 28rpx;
			line-height: 80rpx;
			color: #333333;

			&:last-child {
				margin: 0 24rpx 0 0;
			}
		}

		.on {
			font-weight: bold;
			font-size: 32rpx;

			.line {
				position: absolute;
				bottom: 22rpx;
				left: 50%;
				z-index: -1;
				width: 100%;
				height: 8rpx;
				border-radius: 4rpx;
				background: linear-gradient(90deg, var(--view-theme) 0%, var(--view-gradient) 100%);
				transform: translateX(-50%);
			}
		}
	}

	.tabNav2 {
		.list {
			flex-wrap: nowrap;
			height: 80rpx;
			margin-left: 24rpx;
		}

		.item {
			position: relative;
			z-index: 2;
			flex-shrink: 0;
			height: 80rpx;
			margin: 0 55rpx 0 0;
			font-size: 28rpx;
			line-height: 80rpx;
			color: #333333;

			&:last-child {
				margin: 0 24rpx 0 0;
			}
		}

		.on {
			font-weight: bold;
			font-size: 32rpx;
			color: var(--view-theme);

			.line {
				position: absolute;
				bottom: 8rpx;
				left: 50%;
				z-index: -1;
				width: 64rpx;
				height: 64rpx;
				border: 4rpx solid var(--view-theme);
				border-top-color: transparent;
				border-right-color: transparent;
				border-left-color: transparent;
				border-radius: 50%;
				transform: translateX(-50%);
			}
		}
	}

	.tabNav3 {
		.list {
			flex-wrap: nowrap;
			height: 80rpx;
			margin-left: 15rpx;
		}

		.item {
			position: relative;
			flex-shrink: 0;
			height: 80rpx;
			padding: 0 20rpx;
			margin: 0 15rpx 0 0;
			font-size: 28rpx;
			line-height: 80rpx;
			color: #333333;
		}

		.on {
			height: 48rpx;
			border-radius: 24rpx;
			background: linear-gradient(90deg, var(--view-theme) 0%, var(--view-gradient) 100%);
			text-align: center;
			font-size: 26rpx;
			line-height: 48rpx;
			color: #FFFFFF;
		}
	}
</style>