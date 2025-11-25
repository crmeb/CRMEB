<template>
	<view class="page_count">
		<view class="bg-img" v-if="imgUrls.length">
			<image :class="{ active: index == swiperCur }" v-for="(i, index) in imgUrls" :src="i.img"></image>
		</view>
		<view class="bag-gradient" :style="[bgGradientStyle]"></view>
		<!--搜索-->
		<view class="my-main">
			<view class="mp-header" id="home" :style="[mpHeaderStyle]">
				<view class="sys-head" :style="{ height: statusBarHeight + 'px' }" v-if="!special"></view>
				<view class="serch-box">
					<view class="serch-wrapper flex" :class="special ? 'on' : ''">
						<view class="title skeleton-rect" :style="[titleStyle]" v-if="searchBox == 0">{{ titleConfig }}</view>
						<view class="logo skeleton-rect" v-if="searchBox == 1">
							<image :src="logoUpImg" mode="heightFix"></image>
						</view>
						<navigator
							v-if="hotWords.length"
							:url="'/pages/goods/goods_search/index?searchVal=' + searchVal"
							:class="logoConfig ? 'input' : 'uninput'"
							hover-class="none"
							class="skeleton-rect"
							:style="[inputStyle]"
						>
							<view class="swiperTxt">
								<swiper :indicator-dots="indicatorDots" :autoplay="autoplay" :interval="interval" :duration="duration" vertical="true" circular="true" @change="textChange">
									<block v-for="(item, index) in hotWords" :key="index">
										<swiper-item catchtouchmove="catchTouchMove">
											<view class="acea-row row-between-wrapper">
												<view class="text acea-row row-between-wrapper">
													<view class="newsTitle line1">{{ item.val }}</view>
												</view>
											</view>
										</swiper-item>
									</block>
								</swiper>
							</view>
							<text class="iconfont icon-ic_search"></text>
						</navigator>
						<navigator v-else url="/pages/goods/goods_search/index" hover-class="none" class="skeleton-rect input" :style="[inputStyle]">
							{{ dataConfig.inputConfig.value }}
							<text class="iconfont icon-ic_search"></text>
						</navigator>
					</view>
				</view>
			</view>
			<view :style="'height:' + (statusBarHeight + 43) + 'px'" v-if="!special && searchShow"></view>
			<view v-if="!dataConfig.classConfig.tabVal" class="navTabBox tabNav">
				<view class="longTab" :style="{ width: `${mainWidth}px` }">
					<scroll-view scroll-x="true" scroll-with-animation :scroll-left="tabLeft" show-scrollbar="true">
						<view
							class="longItem"
							v-for="(item, index) in tabListConfig"
							:key="index"
							:class="{ click: index == tabClick }"
							:id="'id' + index"
							:data-index="index"
							@click="changeTab(item, index)"
						>
							{{ item.text.val }}
						</view>
					</scroll-view>
				</view>
				<view class="category" @click="showCategory">
					<text class="iconfont icon-a-ic_Imageandtextsorting" :style="'color:' + (isScrolled ? txtColor : '#fff')"></text>
				</view>
			</view>
			<view v-if="isCategory" class="category_count">
				<view class="sys-head tui-skeleton" :style="{ height: statusBarHeight + 'px' }" v-if="!special"></view>
				<view class="fs-28">精选类目</view>
				<view class="cate_count grid-column-4 grid-gap-16rpx mt-32">
					<view
						class="category_item"
						:style="[index === tabClick ? classColor : '']"
						@click="changeTab(item, index)"
						v-for="(item, index) in tabListConfig"
						:key="index"
						:id="'ids' + index"
					>
						{{ item.text.val }}
					</view>
				</view>
			</view>
		</view>
		<view class="swiperBg" :style="{ paddingBottom: isMenu ? '20rpx' : '20rpx' }">
			<view class="swiper page_swiper" v-if="imgUrls.length">
				<swiper
					:autoplay="true"
					:circular="circular"
					:interval="intervals"
					:duration="duration"
					:current="swiperCur"
					:previous-margin="swiperMargin"
					:next-margin="swiperMargin"
					@change="swiperChange"
				>
					<block v-for="(item, index) in imgUrls" :key="index">
						<swiper-item :class="{ active: index == swiperCur, scalex: isScale }">
							<view @click="goDetail(item)" class="slide-navigator acea-row row-between-wrapper">
								<image :src="item.img" :style="[imageStyle]" mode="aspectFill" class="slide-image"></image>
							</view>
						</swiper-item>
					</block>
				</swiper>
				<!--重置小圆点的样式  -->
				<view
					class="dots acea-row"
					:class="{
						'row-center': dataConfig.docPosition.tabVal == 1,
						'row-right': dataConfig.docPosition.tabVal == 2
					}"
				>
					<view v-if="dataConfig.docConfig.tabVal == 2" class="dot3">
						<view class="dot" :style="[progressWidth, dotBgColor]">
							<view class="active" :style="[progressValue, dotColor]"></view>
						</view>
					</view>
					<view
						v-else
						class="acea-row"
						:class="{
							dot1: dataConfig.docConfig.tabVal == 0,
							dot2: dataConfig.docConfig.tabVal == 1,
							dot4: dataConfig.docConfig.tabVal == 3
						}"
					>
						<view
							v-for="(item, index) in imgUrls"
							:key="index"
							class="dot"
							:class="{ active: index == swiperCur }"
							:style="[dotBgColor, index == swiperCur ? dotColor : '']"
						></view>
					</view>
				</view>
			</view>
		</view>
		<view v-if="isCategory" class="mask" @click="isCategory = false"></view>
	</view>
</template>

<script>
import { getCategoryList } from '@/api/store.js';
import { getCategoryVersion } from '@/api/api.js';
let statusBarHeight = uni.getSystemInfoSync().statusBarHeight;
export default {
	name: 'homeComb',
	props: {
		dataConfig: {
			type: Object,
			default: () => {}
		},
		isFixed: {
			type: Boolean,
			default: false
		},
		isScrolled: {
			type: Boolean,
			default: false
		},
		isScale: {
			type: Boolean,
			default: false
		},
		isMenu: {
			type: Boolean,
			default: false
		},
		special: {
			type: Number,
			default: 0
		},
		belongIndex: {
			type: Number,
			default: 0
		}
	},
	data() {
		return {
			statusBarHeight: statusBarHeight,
			autoplay: true,
			interval: this.dataConfig.numConfig.val * 1000 || 2500,
			duration: 500,
			logoConfig: this.dataConfig.logoConfig.url,
			tabClick: 0, //导航栏被点击
			isLeft: 0, //导航栏下划线位置
			isWidth: 0, //每个导航栏占位
			mainWidth: 0,
			tabLeft: 0,
			tabTitle: [],
			isTop: 0,
			navHeight: 38,
			indicatorDots: false,
			circular: true,
			intervals: 3000,
			imgUrls: [], //图片轮播数据
			swiperCur: 0,
			searchVal: '',
			bgColor: this.dataConfig.swiperConfig.list.length ? this.dataConfig.swiperConfig.list[0].img : '',
			isCategory: false,
			txtColor: '',
			hotWordShow: false,
			bgColorLeft: '',
			bgColorRight: '',
			searchShow: false,
			titleConfig: this.dataConfig.titleConfig.value,
			searchBox: this.dataConfig.searchBox.tabVal,
			fixConfig: this.dataConfig.searchFix.tabVal
		};
	},
	computed: {
		classColor() {
			let color = this.dataConfig.classColor;
			return {
				background: `linear-gradient(90deg, ${color.color[0].item} 50%, ${color.color[1].item} 100%)`,
				color: '#fff'
			};
		},
		bgGradientStyle() {
			return {
				'background-image': `linear-gradient(to bottom, rgba(245,245,245,0) 0%, rgba(245,245,245,0) 50%, ${
					this.dataConfig.gradientColor ? this.dataConfig.gradientColor.color[0].item : '#f5f5f5'
				} 100%)`
			};
		},
		swiperMargin() {
			return this.dataConfig.styleConfig.tabVal ? '50rpx' : '10rpx';
		},
		mpHeaderStyle() {
			let style = {};
			if (this.isScrolled && this.dataConfig.searchConfig.tabVal) {
				style.background = `linear-gradient(90deg, #FFFFFF 50%, #FFFFFF 100%)`;
				style.position = `fixed`;
				this.searchShow = true;
			} else {
				this.searchShow = false;
			}
			return style;
		},
		inputStyle() {
			let style = {};
			if (this.isScrolled && this.dataConfig.searchConfig.tabVal) {
				style.background = `#F5F5F5`;
				style.color = `#BBBBBB`;
			}
			return style;
		},
		titleStyle() {
			let style = {};
			if (this.isScrolled && this.dataConfig.searchConfig.tabVal) {
				style.color = `#333`;
			}
			return style;
		},
		progressWidth() {
			return {
				width: `${this.dataConfig.swiperConfig.list.length * 20}rpx`
			};
		},
		progressValue() {
			return {
				width: `${((this.swiperCur + 1) / this.dataConfig.swiperConfig.list.length) * 100}%`
			};
		},
		dotBgColor() {
			return {
				background: this.dataConfig.dotBgColor.color[0].item
			};
		},
		dotColor() {
			return {
				background: this.dataConfig.dotColor.color[0].item
			};
		},
		imageStyle() {
			let borderRadius = `${this.dataConfig.filletImg.val * 2}rpx`;
			if (this.dataConfig.filletImg.type) {
				borderRadius = `${this.dataConfig.filletImg.valList[0].val * 2}rpx ${this.dataConfig.filletImg.valList[1].val * 2}rpx ${this.dataConfig.filletImg.valList[3].val * 2}rpx ${
					this.dataConfig.filletImg.valList[2].val * 2
				}rpx`;
			}
			return {
				'border-radius': borderRadius
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
				}
			});
			return tabList;
		},
		hotWords() {
			return this.dataConfig.hotWords.list.filter((item) => {
				return item.val;
			});
		},
		logoUpImg() {
			let img = '';
			if (this.isScrolled && this.dataConfig.searchConfig.tabVal && this.dataConfig.logoUpConfig && this.dataConfig.logoUpConfig.url) {
				img = this.dataConfig.logoUpConfig.url;
			} else {
				img = this.dataConfig.logoConfig.url;
			}
			return img;
		}
	},
	created() {
		var that = this;
		// 获取设备宽度
		uni.getSystemInfo({
			success(e) {
				that.mainWidth = e.windowWidth;
				that.isWidth = (e.windowWidth - 65) / 8;
			}
		});
		that.imgUrls = that.dataConfig.swiperConfig.list;
	},
	mounted() {
		let that = this;
		that.hotWords.forEach((item) => {
			if (item.val) {
				this.hotWordShow = true;
			}
		});
		uni.setStorageSync('hotList', that.hotWords);
	},
	methods: {
		goDetail(url) {
			let urls = url.info[1].value;
			this.$util.JumpPath(urls);
		},
		//替换安全域名
		setDomain: function (url) {
			url = url ? url.toString() : '';
			//本地调试打开,生产请注销
			if (url.indexOf('https://') > -1) return url;
			else return url.replace('http://', 'https://');
		},
		swiperChange(e) {
			let { current, source } = e.detail;
			if (source === 'autoplay' || source === 'touch') {
				this.swiperCur = e.detail.current;
				this.bgColor = this.imgUrls[e.detail.current]['img'];
			}
		},
		textChange(e) {
			let { current, source } = e.detail;
			if (source === 'autoplay' || source === 'touch') {
				this.searchVal = this.hotWords[e.detail.current]['val'];
			}
		},
		/**显示全部分类*/
		showCategory() {
			this.isCategory = true;
		},
		/*跳转为页面*/
		changeTab(item, index) {
			if (this.tabClick == index) return;
			this.tabClick = index; //设置导航点击了哪一个
			this.isLeft = index * this.isWidth + 16; //设置下划线位置
			this.isCategory = false;
			let data = {
				type: item.dataType.tabVal, // 0 微页面 1 商品分类
				microPage: item.microPage.id,
				classPage: item.classPage.id
			};
			this.$emit('bindSortId', data);
		}
	}
};
</script>

<style lang="scss" scoped>
.scrollColor {
	transition: background-color 0.5s ease;
	background-color: #fff;
	color: #333 !important;

	.longItem {
		// color: #333 !important;

		&.click {
			&::after {
				background: #333 !important;
			}
		}
	}
}

.page_count {
	position: relative;
	overflow: hidden;
	.bag-gradient {
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		overflow: hidden;
		z-index: 0;
	}
	.bg-img {
		position: absolute;
		width: 100%;
		height: 100%;
		top: 0;
		z-index: 0;
		filter: blur(0);
		overflow: hidden;
		image.active {
			opacity: 1;
			transform: scale(1.5);
		}
		image {
			position: absolute;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			filter: blur(30rpx);
			transform: scale(1.5);
			opacity: 0;
			transition: opacity 0.5s ease;
		}
	}
}

.my-main {
	transition: background-color 0.5s ease;
}

.swiperTxt {
	width: 300rpx;
	height: 100%;
	line-height: 58rpx;
	overflow: hidden;
}

.swiperTxt .text {
	width: 480rpx;
}

.swiperTxt .text .label {
	font-size: 20rpx;
	color: #ff4c48;
	width: 64rpx;
	height: 30rpx;
	border-radius: 40rpx;
	text-align: center;
	line-height: 28rpx;
	border: 2rpx solid #ff4947;
}

.swiperTxt .text .newsTitle {
	width: 300rpx;
	font-size: 24rpx;
	// color: #fff;
}

.swiperTxt swiper {
	height: 100%;
}

.mp-header {
	z-index: 99;
	// position: fixed;
	position: relative;
	left: 0;
	top: 0;
	width: 100%;

	&.on {
		position: relative;
	}

	.serch-box {
		height: 43px;
	}

	.logo {
		height: 60rpx;
		margin-right: 20rpx;

		image {
			width: 100%;
			height: 100%;
		}
	}

	.serch-wrapper {
		align-items: center;
		/* #ifdef MP */
		padding: 0 220rpx 0 30rpx;
		/* #endif */
		/* #ifndef MP */
		padding: 0 30rpx;
		/* #endif */
		height: 100%;

		&.on {
			padding: 0 30rpx;
		}

		.title,
		.map {
			font-size: 28rpx;
			color: #fff;
			margin-right: 24rpx;
		}

		.map {
			.iconfont {
				font-size: 26rpx;
			}
			.icon-ic_location4 {
				margin-right: 12rpx;
			}
		}

		&.on {
			padding: 0 30rpx;
		}

		.input,
		.uninput {
			flex: 1;
			min-width: 0;
			position: relative;
			display: flex;
			align-items: center;
			height: 60rpx;
			padding: 0 32rpx;
			border-radius: 30rpx;
			background: rgba(255, 255, 255, 0.4);
			font-size: 28rpx;
			color: #ffffff;

			.iconfont {
				position: absolute;
				right: 25rpx;
				top: 13rpx;
			}
		}
	}
}

.tabNav {
	padding-top: 10rpx;
}

.navTabBox {
	color: #ffffff;
	padding: 0 57rpx 0 30rpx;
	// z-index: 99;
	position: relative;
	// position: fixed;
	left: 0;
	width: 100%;
	// padding-top: 5px;

	&.on {
		position: relative;
	}

	scroll-view {
		/* #ifdef MP */
		width: 640rpx;
		/* #endif */
		/* #ifndef MP */
		width: 666rpx;
		/* #endif */
		height: 82rpx;
		white-space: nowrap;
	}

	.click {
		color: white;
	}

	.longTab {
		// height: 34px;

		.longItem {
			display: inline-block;
			text-align: center;
			font-size: 28rpx;
			color: #ffffff;
			max-width: 160rpx;
			margin-right: 30rpx;
			position: relative;
			font-weight: 400;
			line-height: 82rpx;

			&:last-child {
				margin-right: 0;
			}

			&.click {
				font-weight: 600;
				font-size: 30rpx;
				color: #ffffff;

				&::after {
					// content: '';
					// transition: .5s;
					// width: 19rpx;
					// height: 3rpx;
					// background: #FFFFFF;
					// position: absolute;
					// bottom: 19rpx;
					// left: 50%;
					// transform: translateX(-50%);
				}
			}
		}
	}

	.category {
		position: absolute;
		right: 0;
		top: 50%;
		width: 57rpx;
		height: 45rpx;
		padding-left: 11rpx;
		line-height: 45rpx;
		z-index: 10;
		transform: translateY(-50%);

		.iconfont {
			font-size: 35rpx;
		}

		&::before {
			content: '';
			position: absolute;
			top: 50%;
			left: 0;
			width: 2rpx;
			height: 28rpx;
			background: linear-gradient(135deg, #ffffff 0%, rgba(216, 216, 216, 0) 100%);
			transform: translateY(-50%);
		}
	}

	&.isFixed {
		z-index: 10;
		position: fixed;
		left: 0;
		width: 100%;
	}
}

.category_count {
	width: 100%;
	background: #fff;
	padding: 32rpx;
	position: fixed;
	top: 0;
	left: 0;
	z-index: 100;
	border-radius: 0 0 24rpx 24rpx;

	.cate_count {
		.category_item {
			padding: 0 20rpx;
			height: 72rpx;
			text-align: center;
			line-height: 72rpx;
			word-wrap: break-word;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
			background-color: #f5f5f5;
			border-radius: 8rpx;
			font-size: 24rpx;
		}
	}
}

.mask {
	position: fixed;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: rgba(0, 0, 0, 0.7);
	z-index: 22;
}

.scrolled {
	z-index: 99;
	position: fixed;
	left: 0;
	top: 0;
	width: 100%;
	background-color: #fff;

	.longItem,
	.click,
	.category text {
		color: #000 !important;
	}

	.underline {
		background: #000 !important;
	}

	.input,
	.uninput {
		background: rgba(0, 0, 0, 0.22) !important;
	}

	.click {
		&::after {
			background-color: #fff !important;
		}
	}
}

.swiperBg {
	z-index: 1;

	.page_swiper {
		position: relative;
		width: 100%;
		height: auto;
		margin: 0 auto;
		border-radius: 15rpx;
		overflow-x: hidden;
		z-index: 20;
		padding: 5rpx 10rpx 0;

		uni-swiper {
			height: 320rpx;
		}

		swiper-item {
			border-radius: 15rpx;
		}

		.swiper-item,
		image,
		.acea-row.row-between-wrapper {
			width: 100%;
			height: 100%;
			margin: 0 auto;
			border-radius: 15rpx;
		}

		swiper {
			width: 100%;
			display: block;
		}

		image {
			transform: scale(0.93);
			transition: all 0.6s ease;
		}

		swiper-item.active,
		swiper-item.scalex {
			image {
				transform: scale(1);
			}
		}

		/*用来包裹所有的小圆点  */
		.dots {
			// width: 156rpx;
			// height: 36rpx;
			display: flex;
			flex-direction: row;
			position: absolute;
			right: 20rpx;
			left: 20rpx;
			bottom: 23rpx;
		}

		/*未选中时的小圆点样式 */
		.dot1 {
			.dot {
				width: 12rpx;
				height: 12rpx;
				border-radius: 6rpx;
				margin-right: 16rpx;
				background-color: rgba(0, 0, 0, 0.1);

				&:last-child {
					margin-right: 0;
				}

				/*选中以后的小圆点样式  */
				&.active {
					background: #e93323;
				}
			}
		}

		.dot2 {
			.dot {
				width: 10rpx;
				height: 10rpx;
				border-radius: 5rpx;
				margin-right: 8rpx;
				background-color: rgba(0, 0, 0, 0.1);

				&:last-child {
					margin-right: 0;
				}

				/*选中以后的小圆点样式  */
				&.active {
					width: 18rpx;
					background: #e93323;
				}
			}
		}

		.dot4 {
			.dot {
				width: 20rpx;
				height: 6rpx;
				border-radius: 3rpx;
				margin-right: 10rpx;
				background-color: #dddddd;

				&:last-child {
					margin-right: 0;
				}

				/*选中以后的小圆点样式  */
				&.active {
					background: #e93323;
				}
			}
		}

		.dot3 {
			.dot {
				height: 6rpx;
				border-radius: 3rpx;
				margin-right: 10rpx;
				background-color: rgba(0, 0, 0, 0.1);

				&:last-child {
					margin-right: 0;
				}

				/*选中以后的小圆点样式  */
				.active {
					height: 6rpx;
					border-radius: 3rpx;
					background: #e93323;
				}
			}
		}
	}
}

/deep/.dot0 .uni-swiper-dots-horizontal {
	left: 10%;
}

/deep/.dot1 .uni-swiper-dots-horizontal {
	left: 50%;
}

/deep/.dot2 .uni-swiper-dots-horizontal {
	left: 90%;
}
</style>
