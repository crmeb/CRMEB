<template>
	<!-- 拼团活动 -->
	<view>
		<view :style="[boxStyle]" v-if="combinationList.length">
			<view>
				<!-- 拼团头部 -->
				<view class="w-full h-96 px-24 flex-between-center bg-cover" :style="[headerStyle]">
					<view class="flex-y-center">
						<text class="fs-32 lh-44rpx fw-500" :style="[titleStyle]" v-if="titleConfig">{{ titleTxtConfig }}</text>
						<image :src="titleImg" class="w-140 h-32" v-else></image>
						<text class="fs-28 text--w111-ccc px-20" :style="[dividerColor]">|</text>
						<view class="avatar-group flex-y-center mr-20" v-if="pinkInfo.avatars && pinkInfo.avatars.length">
							<image v-for="(item, index) in pinkInfo.avatars" :key="index" :src="item" mode="" class="w-36 h-36 rd-50-p111-"></image>
						</view>
						<text class="fs-26 text--w111-999 lh-36rpx" :style="[tipsColor]">{{ pinkInfo.pink_count }}{{ $t(`人参与拼团`) }}</text>
					</view>
					<view class="flex-y-center fs-24 text--w111-999" :style="[headerBntColor]" @tap="goPage('/pages/activity/goods_combination/index')">
						<text>{{ rightBntTxt }}</text>
						<text class="iconfont icon-ic_rightarrow fs-24" :style="[headerBntColor]"></text>
					</view>
				</view>
				<!-- 拼团列表 -->
				<!-- 单列 -->
				<view class="pt-32 pr-20 pb-32 pl-20" :style="[boxContentStyle]" v-if="goodStyleConfig == 0">
					<view class="w-full flex justify-between item" v-for="(item, index) in combinationList" :key="index" @tap="goDetail(item)">
						<easy-loadimage :image-src="item.image" width="240rpx" height="240rpx" :borderRadius="imgStyle"></easy-loadimage>
						<view class="flex-1 flex-col justify-between pl-20 h-240">
							<view class="w-full">
								<view class="w-full fs-28 h-80 lh-40rpx line2" :style="[productStyle]" v-if="checkboxInfo.includes(0)">{{ item.title }}</view>
								<view class="flex mt-14" v-if="checkboxInfo.includes(1)">
									<view class="flex fs-20 rd-8rpx" :style="[labelBg]">
										<text class="tuan-num text--w111-fff flex-center" v-if="checkboxInfo.includes(1)">{{ item.people }}{{ $t(`人团`) }}</text>
										<text class="complete flex-center" :style="[pinkNumStyle]">已拼{{ item.pink_count || 0 }}份</text>
									</view>
								</view>
							</view>
							<view class="flex justify-between items-end">
								<view class="flex-col">
									<baseMoney
										:money="item.price"
										symbolSize="24"
										integerSize="36"
										decimalSize="36"
										weight
										:color="priceColor"
										preFix="拼团价"
										preFixSize="24"
										:textColor="priceColor"
										v-if="checkboxInfo.includes(2)"
									></baseMoney>
									<text class="text-line fs-28 text--w111-999 pt-14 Regular" v-if="checkboxInfo.includes(3)" :style="[otPriceColor]">{{ $t(`¥`) }}{{ item.product_price }}</text>
								</view>
								<view class="w-144 h-56 rd-30rpx flex-center fs-24 text--w111-fff" v-if="!showBtn" :style="[btnBgColor]">{{ $t(`去拼团`) }}</view>
							</view>
						</view>
					</view>
				</view>
				<!-- 两列 -->
				<view class="grid-column-2 grid-gap-22rpx pt-32 pr-20 pb-32 pl-20" :style="[boxContentStyle]" v-if="goodStyleConfig == 1">
					<view v-for="(item, index) in combinationList" :key="index" @tap="goDetail(item)">
						<easy-loadimage :image-src="item.image" width="100%" height="324rpx" :borderRadius="imgStyle"></easy-loadimage>
						<view class="w-full mt-16 line1" :style="[productStyle]" v-if="checkboxInfo.includes(1)">
							<view class="inline fs-20 rd-4rpx mr-10 rd-4rpx" :style="[labelBg]">
								<text class="complete flex-center rd-4rpx" :style="[pinkNumStyle]">{{ item.people }}{{ $t(`人团`) }}</text>
							</view>
							<view class="inline fs-28 lh-40rpx" v-if="checkboxInfo.includes(0)">{{ item.title }}</view>
						</view>
						<view class="flex justify-between items-end mt-12">
							<view class="flex-col">
								<baseMoney :money="item.price" symbolSize="24" integerSize="36" decimalSize="36" weight :color="priceColor" v-if="checkboxInfo.includes(2)"></baseMoney>
								<text class="text-line fs-28 text--w111-999 Regular" v-if="checkboxInfo.includes(3)" :style="[otPriceColor]">{{ $t(`¥`) }}{{ item.product_price }}</text>
							</view>
							<view class="w-144 h-56 rd-30rpx flex-center fs-24 text--w111-fff bg--w111-E93323" v-if="!showBtn" :style="[btnBgColor]">{{ $t(`去拼团`) }}</view>
						</view>
					</view>
				</view>
				<!-- 三列 -->
				<view class="grid-column-3 grid-gap-18rpx pt-32 pr-20 pb-32 pl-20" :style="[boxContentStyle]" v-if="goodStyleConfig == 2">
					<view class="relative" v-for="(item, index) in combinationList" :key="index" @tap="goDetail(item)">
						<view class="abs-tag z-20" :style="[labelBg]" v-if="checkboxInfo.includes(1)">
							<text class="circle-tag flex-center fs-22" :style="[pinkNumStyle]">{{ item.people }}{{ $t(`人团`) }}</text>
						</view>
						<easy-loadimage :image-src="item.image" width="100%" height="212rpx" :borderRadius="imgStyle"></easy-loadimage>
						<view class="w-full line1 mt-16 fs-26" :style="[productStyle]" v-if="checkboxInfo.includes(0)">{{ item.title }}</view>
						<view class="flex items-baseline">
							<baseMoney :money="item.price" symbolSize="24" integerSize="36" decimalSize="36" weight :color="priceColor" v-if="checkboxInfo.includes(2)"></baseMoney>
						</view>
						<view class="text-line fs-24 text--w111-999 Regular lh-32rpx" :style="[otPriceColor]" v-if="checkboxInfo.includes(3)">{{ $t(`¥`) }}{{ item.product_price }}</view>
					</view>
				</view>
				<!-- 滑动 -->
				<scroll-view scroll-x="true" show-scrollbar="false" :style="[boxContentStyle]" class="white-nowrap vertical-middle w-full p-32" v-if="goodStyleConfig == 3">
					<view class="inline-block relative" :class="{ 'ml-20': index }" v-for="(item, index) in combinationList" :key="index" @tap="goDetail(item)">
						<view class="abs-tag z-20" :style="[labelBg]" v-if="checkboxInfo.includes(1)">
							<text class="circle-tag flex-center fs-22" :style="[pinkNumStyle]">{{ item.people }}{{ $t(`人团`) }}</text>
						</view>
						<easy-loadimage :image-src="item.image" width="224rpx" height="224rpx" :borderRadius="imgStyle"></easy-loadimage>
						<view class="w-222 line1 mt-16 fs-26" :style="[productStyle]" v-if="checkboxInfo.includes(0)">{{ item.title }}</view>
						<baseMoney class="mt-12" :money="item.price" symbolSize="24" integerSize="36" decimalSize="36" weight :color="priceColor" v-if="checkboxInfo.includes(2)"></baseMoney>
						<view class="text-line fs-24 Regular lh-32rpx" :style="[otPriceColor]" v-if="checkboxInfo.includes(3)">{{ $t(`¥`) }}{{ item.product_price }}</view>
					</view>
				</scroll-view>
			</view>
		</view>
	</view>
</template>

<script>
import { openPinkSubscribe } from '@/utils/SubscribeMessage.js';
import { pink } from '@/api/api.js';
import { getCombinationList } from '@/api/activity.js';
export default {
	name: 'combination',
	props: {
		dataConfig: {
			type: Object,
			default: () => {}
		},
		isSortType: {
			type: String | Number,
			default: 0
		}
	},
	data() {
		return {
			pinkInfo: '',
			combinationList: []
		};
	},
	computed: {
		titleStyle() {
			let titleText = this.dataConfig.titleText;
			return {
				fontStyle: !titleText.tabVal ? 'normal' : titleText.tabList[titleText.tabVal].style,
				fontWeight: !titleText.tabVal ? 'bold' : 'normal',
				color: this.dataConfig.titleColor.color[0].item,
				fontSize: this.dataConfig.titleNumber.val * 2 + 'rpx'
			};
		},
		boxStyle() {
			return {
				padding: `${this.dataConfig.topConfig.val * 2}rpx ${this.dataConfig.prConfig.val * 2}rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
				marginTop: `${this.dataConfig.mbConfig.val * 2}rpx`,
				background: this.dataConfig.bottomBgColor.color[0].item
			};
		},
		boxContentStyle() {
			let br = `${this.dataConfig.fillet.val * 2}rpx`;
			let borderRadius = `0 0 ${br} ${br}`;
			if (this.dataConfig.fillet.type) {
				borderRadius = `0 0 ${this.dataConfig.fillet.valList[3].val * 2}rpx ${this.dataConfig.fillet.valList[2].val * 2}rpx`;
			}
			return {
				borderRadius,
				background: `linear-gradient(90deg, ${this.dataConfig.moduleColor.color[0].item} 0%, ${this.dataConfig.moduleColor.color[1].item} 100%)`
			};
		},
		/*商品模板*/
		goodStyleConfig() {
			return this.dataConfig.goodStyleConfig.tabVal;
		},
		styleConfig() {
			return this.dataConfig.styleConfig.tabVal;
		},
		headerStyle() {
			let br = `${this.dataConfig.fillet.val * 2}rpx`,
				borderRadius = '',
				imgBgUrl = this.dataConfig.imgBgConfig.url;
			if (this.dataConfig.fillet.type) {
				borderRadius = `${this.dataConfig.fillet.valList[0].val * 2}rpx ${this.dataConfig.fillet.valList[1].val * 2}rpx 0 0`;
			} else {
				borderRadius = `${br} ${br} 0 0`;
			}
			return {
				backgroundImage: this.styleConfig
					? 'url(' + imgBgUrl + ')'
					: `linear-gradient(90deg,${this.dataConfig.headerBgColor.color[0].item} 0%,${this.dataConfig.headerBgColor.color[1].item} 100%)`,
				borderRadius
			};
		},
		/*标题是文本还是图片*/
		titleConfig() {
			return this.dataConfig.titleConfig.tabVal;
		},
		/*标题文本*/
		titleTxtConfig() {
			return this.dataConfig.titleTxtConfig.value;
		},
		/*标题图片*/
		titleImg() {
			return this.styleConfig ? this.titleUrl : this.titleColorUrl;
		},
		titleColorUrl() {
			return this.dataConfig.imgColorConfig.url;
		},
		titleUrl() {
			return this.dataConfig.imgConfig.url;
		},
		/*标题提示文字*/
		tipsColor() {
			return {
				color: this.styleConfig ? this.dataConfig.tipsColor.color[0].item : this.dataConfig.tipsColor2.color[0].item
			};
		},
		/*分割线颜色*/
		dividerColor() {
			return {
				color: this.dataConfig.dividerColor.color[0].item
			};
		},
		/*头部按钮文本*/
		rightBntTxt() {
			return this.dataConfig.rightBntConfig.value;
		},
		/*头部按钮样式*/
		headerBntColor() {
			return {
				color: this.styleConfig ? this.dataConfig.headerBntColor.color[0].item : this.dataConfig.headerBntColor2.color[0].item,
				fontSize: `${this.dataConfig.bntNumber.val * 2}rpx`
			};
		},
		/*商品图片圆角样式*/
		imgStyle() {
			let borderRadius = `${this.dataConfig.filletImg.val * 2}rpx`;
			if (this.dataConfig.filletImg.type) {
				borderRadius = `${this.dataConfig.filletImg.valList[0].val * 2}rpx ${this.dataConfig.filletImg.valList[1].val * 2}rpx ${this.dataConfig.filletImg.valList[3].val * 2}rpx ${
					this.dataConfig.filletImg.valList[2].val * 2
				}rpx`;
			}
			return borderRadius;
		},
		/*商品名称样式*/
		productStyle() {
			return {
				color: this.dataConfig.goodsNameColor.color[0].item,
				fontWeight: this.dataConfig.goodsName.tabVal ? 'normal' : 'bold'
			};
		},
		/* 展示信息 */
		checkboxInfo() {
			return this.dataConfig.checkboxInfo.type;
		},
		pinkNumStyle() {
			return {
				color: this.dataConfig.toneConfig.tabVal ? this.dataConfig.labelColor.color[0].item : 'var(--view-theme)'
				// background: this.dataConfig.toneConfig.tabVal ? this.dataConfig.labelColor.color[0].item : 'var(--view-theme)'
			};
		},
		labelBg() {
			return {
				background: this.dataConfig.toneConfig.tabVal ? this.dataConfig.labelColor.color[0].item : 'var(--view-theme)',
				height: '32rpx'
			};
		},
		/* 价格颜色 */
		priceColor() {
			return this.dataConfig.toneConfig.tabVal ? this.dataConfig.pinkPriceColor.color[0].item : 'var(--view-theme)';
		},
		/* 划线价颜色 */
		otPriceColor() {
			return {
				color: this.dataConfig.goodsPriceColor.color[0].item
			};
		},
		showBtn() {
			return this.dataConfig.pinkConfig.tabVal;
		},
		/* 按钮颜色 */
		btnBgColor() {
			return {
				background: this.dataConfig.toneConfig.tabVal
					? `linear-gradient(90deg,${this.dataConfig.goodsBntColor.color[1].item} 0%,${this.dataConfig.goodsBntColor.color[0].item} 100%)`
					: 'linear-gradient(90deg, var(--view-theme) 0%, var(--view-gradient) 100%)',
				color: this.dataConfig.goodsBntTxtColor.color[0].item
			};
		},
		/*商品数量*/
		numberConfig() {
			return this.dataConfig.numberConfig.val;
		}
	},
	mounted() {
		this.pink();
		this.getCombinationList();
	},
	methods: {
		goPage(url) {
			uni.navigateTo({
				url
			});
		},
		goDetail(item) {
			console.log(item);
			// #ifndef MP
			uni.navigateTo({
				url: `/pages/activity/goods_combination_details/index?id=${item.id}&type=3`
			});
			// #endif
			// #ifdef MP
			openPinkSubscribe().then((res) => {
				uni.navigateTo({
					url: `/pages/activity/goods_combination_details/index?id=${item.id}&type=3`
				});
			});
			// #endif
		},
		// 拼团列表
		getCombinationList: function () {
			let that = this;
			let limit = that.$config.LIMIT;
			let data = {
				page: 1,
				limit: this.numberConfig >= limit ? limit : this.numberConfig
			};
			getCombinationList(data)
				.then((res) => {
					that.combinationList = res.data;
				})
				.catch((res) => {
					return that.$util.Tips({
						title: res
					});
				});
		},
		// 拼团数据（拼团人数头部图片）
		pink: function () {
			pink().then((res) => {
				this.pinkInfo = res.data;
			});
		}
	}
};
</script>

<style lang="scss">
.Regular {
	font-family: 'Regular';
}
.bg-cover {
	background-size: cover;
}
.item ~ .item {
	margin-top: 32rpx;
}
.inline-flex {
	display: inline-flex;
}
.tuan-num {
	display: inline-flex;
	justify-content: center;
	align-items: center;
	padding: 0 8rpx;
	height: 32rpx;
	border-radius: 8rpx 0 0 8rpx;
}
.complete {
	display: inline-flex;
	justify-content: center;
	align-items: center;
	padding: 0 8rpx;
	height: 32rpx;
	line-height: 32rpx;
	border-radius: 0 8rpx 8rpx 0;
	background: rgba(255, 255, 255, 0.9);
}
.rd-4rpx {
	border-radius: 4rpx !important;
}
.avatar-group image {
	margin-right: -10rpx;
}
.abs-tag {
	position: absolute;
	left: 10rpx;
	top: 10rpx;
	width: 84rpx;
	height: 30rpx;
	border-radius: 20rpx;
}
.circle-tag {
	width: 84rpx;
	height: 30rpx;
	border-radius: 20rpx;
	background: rgba(255, 255, 255, 0.9);
}
scroll-view {
	box-sizing: border-box;
}
</style>
