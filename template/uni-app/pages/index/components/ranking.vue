<template>
	<view :style="[boxStyle]">
		<view class="pt-24 pb-24 pl-24" :style="[boxContentStyle]" @tap="goRank">
			<view class="flex-between-center pr-24">
				<text class="fs-32 fw-500" :style="[titleStyle]" v-if="titleConfig">{{titleTxtConfig}}</text>
				<image :src="titleImg" class="w-102 h-32" v-else></image>
				<text class="fs-24 text--w111-999" :style="[headerBntColor]">
					{{headerBtnText}} <text class="iconfont icon-ic_rightarrow fs-24" :style="[headerBntColor]"></text> 
				</text>
			</view>
			<scroll-view scroll-x="true" class="white-nowrap vertical-middle w-full mt-32"
				show-scrollbar="false" v-if="styleConfig == 0">
				<view class="inline-block mr-20">
					<view class="rank-card" :style="[rankCardStyle]">
						<view class="fs-28 lh-40rpx fw-600 flex-y-center" :style="[rankItemTitleStyle]">
							<text class="iconfont icon-ic_fire fs-32"></text>
							<text class="pl-8 font-color" :style="[rankItemTitleStyle]">{{ $t(`销量榜`) }}</text>
						</view>
						<view class="rd-12rpx bg--w111-fff mt-18 p-14 h-400" v-if="sales.length">
							<view class="rank-pro-item flex-y-center" v-for="(item,index) in sales" :key="index">
								<view class="w-108 h-108 relative">
									<easy-loadimage
									:image-src="item.image" 
									width="108rpx"
									height="108rpx"
									:borderRadius="imgStyle"></easy-loadimage>
									<view class="rank_count fs-24 text--w111-fff flex-center" :class="'rank-count' + (index + 1)">{{index + 1}}</view>
								</view>
								<view class="pl-16">
									<view class="w-130 line1 fs-26 lh-36rpx mb-8">{{item.store_name}}</view>
									<baseMoney :money="item.price" symbolSize="20" integerSize="28" decimalSize="28" :color="priceColor" weight></baseMoney>
								</view>
							</view>
						</view>
					</view>
				</view>
				<view class="inline-block mr-20">
					<view class="rank-card" :style="[rankCardStyle]">
						<view class="fs-28 lh-40rpx fw-600 flex-y-center" :style="[rankItemTitleStyle]">
							<text class="iconfont icon-ic_fire fs-32"></text>
							<text class="pl-8 font-color" :style="[rankItemTitleStyle]">{{ $t(`收藏榜`) }}</text>
						</view>
						<view class="rd-12rpx bg--w111-fff mt-18 p-14 h-400" v-if="collect.length">
							<view class="rank-pro-item flex-y-center" v-for="(item,index) in collect" :key="index">
								<view class="w-108 h-108 relative">
									<easy-loadimage
									:image-src="item.image" 
									width="108rpx"
									height="108rpx"
									:borderRadius="imgStyle"></easy-loadimage>
									<view class="rank_count fs-24 text--w111-fff flex-center" :class="'rank-count' + (index + 1)">{{index + 1}}</view>
								</view>
								<view class="pl-16">
									<view class="w-130 line1 fs-26 lh-36rpx mb-8">{{item.store_name}}</view>
									<baseMoney :money="item.price" symbolSize="20" integerSize="28" decimalSize="28" :color="priceColor" weight></baseMoney>
								</view>
							</view>
						</view>
					</view>
				</view>
				<view class="inline-block mr-20">
					<view class="rank-card" :style="[rankCardStyle]">
						<view class="fs-28 lh-40rpx fw-600 flex-y-center" :style="[rankItemTitleStyle]">
							<text class="iconfont icon-ic_fire fs-32"></text>
							<text class="pl-8 font-color" :style="[rankItemTitleStyle]">{{ $t(`好评榜`) }}</text>
						</view>
						<view class="rd-12rpx bg--w111-fff mt-18 p-14 h-400" v-if="star.length">
							<view class="rank-pro-item flex-y-center" v-for="(item,index) in star" :key="index">
								<view class="w-108 h-108 relative">
									<easy-loadimage
									:image-src="item.image" 
									width="108rpx"
									height="108rpx"
									:borderRadius="imgStyle"></easy-loadimage>
									<view class="rank_count fs-24 text--w111-fff flex-center" :class="'rank-count' + (index + 1)">{{index + 1}}</view>
								</view>
								<view class="pl-16">
									<view class="w-130 line1 fs-26 lh-36rpx mb-8">{{item.store_name}}</view>
									<baseMoney :money="item.price" symbolSize="20" integerSize="28" decimalSize="28" :color="priceColor" weight></baseMoney>
								</view>
							</view>
						</view>
					</view>
				</view>
			</scroll-view>
			<view class="mt-26" v-else>
				<scroll-view scroll-x="true" class="white-nowrap vertical-middle w-full mt-20"
					show-scrollbar="false" >
					<view class="inline-block type-3 rd-16rpx mr-24" :style="[rankCardStyle]" v-if="sales.length">
						<view class="fs-26 fw-500" :style="[rankItemTitleStyle]">{{ $t(`销量榜`) }}</view>
						<view class="flex mt-20">
							<view class="w-296 h-296 relative mr-12">
								<image :src="sales[0].image" class="w-full h-full" :style="{borderRadius:imgStyle}"></image>
								<image class="abs-lt w-72 h-72" :src="`${imgHost}/statics/images/rank_icon1.png`"></image>
							</view>
							<view>
								<view class="relative">
									<easy-loadimage
									:image-src="sales[1].image" 
									width="142rpx"
									height="142rpx"
									:borderRadius="imgStyle"></easy-loadimage>
									<image class="abs-lt w-48 h-48" :src="`${imgHost}/statics/images/rank_icon2.png`"></image>
								</view>
								<view class="w-142 h-142 relative mt-12">
									<easy-loadimage
									:image-src="sales[2].image" 
									width="142rpx"
									height="142rpx"
									:borderRadius="imgStyle"></easy-loadimage>
									<image class="abs-lt w-48 h-48" :src="`${imgHost}/statics/images/rank_icon3.png`"></image>
								</view>
							</view>
						</view>
						<view class="mt-20 fs-26 w-280 line1">{{sales[0].store_name}}</view>
						<view class="flex items-baseline mt-10">
							<baseMoney :money="sales[0].price" symbolSize="20" integerSize="28" decimalSize="28" :color="priceColor" weight></baseMoney>
							<text class="fs-20 text--w111-999 text-line pl-8">{{ $t(`¥`) }}{{sales[0].ot_price}}</text>
						</view>
					</view>
					<view class="inline-block type-3 rd-16rpx mr-24" :style="[rankCardStyle]" v-if="star.length">
						<view class="fs-26 fw-500" :style="[rankItemTitleStyle]">{{ $t(`好评榜`) }}</view>
						<view class="flex mt-20">
							<view class="w-296 h-296 relative mr-12">
								<image :src="star[0].image" class="w-full h-full" :style="{borderRadius:imgStyle}"></image>
								<image class="abs-lt w-72 h-72" :src="`${imgHost}/statics/images/rank_icon1.png`"></image>
							</view>
							<view>
								<view class="w-142 h-142 relative">
									<easy-loadimage
									:image-src="star[1].image" 
									width="142rpx"
									height="142rpx"
									:borderRadius="imgStyle"></easy-loadimage>
									<image class="abs-lt w-48 h-48" :src="`${imgHost}/statics/images/rank_icon2.png`"></image>
								</view>
								<view class="w-142 h-142 relative mt-12">
									<easy-loadimage
									:image-src="star[2].image" 
									width="142rpx"
									height="142rpx"
									:borderRadius="imgStyle"></easy-loadimage>
									<image class="abs-lt w-48 h-48" :src="`${imgHost}/statics/images/rank_icon3.png`"></image>
								</view>
							</view>
						</view>
						<view class="mt-20 fs-26 w-280 line1">{{star[0].store_name}}</view>
						<view class="flex items-baseline mt-10">
							<baseMoney :money="star[0].price" symbolSize="20" integerSize="28" decimalSize="28" :color="priceColor" weight></baseMoney>
							<text class="fs-20 text--w111-999 text-line pl-8">{{ $t(`¥`) }}{{star[0].ot_price}}</text>
						</view>
					</view>
					<view class="inline-block type-3 rd-16rpx mr-24" :style="[rankCardStyle]" v-if="collect.length">
						<view class="fs-26 fw-500" :style="[rankItemTitleStyle]">{{ $t(`收藏榜`) }}</view>
						<view class="flex mt-20">
							<view class="w-296 h-296 relative mr-12">
								<image :src="collect[0].image" class="w-full h-full" :style="{borderRadius:imgStyle}"></image>
								<image class="abs-lt w-72 h-72" :src="`${imgHost}/statics/images/rank_icon1.png`"></image>
							</view>
							<view>
								<view class="w-142 h-142 relative">
									<easy-loadimage
									:image-src="collect[1].image" 
									width="142rpx"
									height="142rpx"
									:borderRadius="imgStyle"></easy-loadimage>
									<image class="abs-lt w-48 h-48" :src="`${imgHost}/statics/images/rank_icon2.png`"></image>
								</view>
								<view class="w-142 h-142 relative mt-12">
									<easy-loadimage
									:image-src="collect[2].image" 
									width="142rpx"
									height="142rpx"
									:borderRadius="imgStyle"></easy-loadimage>
									<image class="abs-lt w-48 h-48" :src="`${imgHost}/statics/images/rank_icon3.png`"></image>
								</view>
							</view>
						</view>
						<view class="mt-20 fs-26 w-280 line1">{{collect[0].store_name}}</view>
						<view class="flex items-baseline mt-10">
							<baseMoney :money="collect[0].price" symbolSize="20" integerSize="28" decimalSize="28" :color="priceColor" weight></baseMoney>
							<text class="fs-20 text--w111-999 text-line pl-8">{{ $t(`¥`) }}{{collect[0].ot_price}}</text>
						</view>
					</view>
				</scroll-view>
			</view>
		</view>
	</view>
</template>

<script>
	import { HTTP_REQUEST_URL } from '@/config/app';
	import { diyRankApi } from "@/api/activity.js"
	export default {
		name:'ranking',
		props:{
			dataConfig: {
				type: Object,
				default: () => {}
			},
		},
		data(){
			return {
				imgHost: HTTP_REQUEST_URL,
				collect:[],
				sales:[],
				star:[]
			}
		},
		computed:{
			titleStyle(){
				let titleText = this.dataConfig.titleText
				return {
					fontStyle: !titleText.tabVal?'normal':titleText.tabList[titleText.tabVal].style,
					fontWeight: !titleText.tabVal?'bold':'normal',
					color: this.dataConfig.titleColor.color[0].item,
					fontSize: this.dataConfig.titleNumber.val*2+'rpx'
				}
			},
			boxStyle(){
				return {
					padding: `${this.dataConfig.topConfig.val * 2}rpx ${this.dataConfig.prConfig.val * 2}rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
					marginTop: `${this.dataConfig.mbConfig.val * 2}rpx`,
					background: this.dataConfig.bottomBgColor.color[0].item,
				}
			},
			boxContentStyle() {
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
			styleConfig(){
				return this.dataConfig.styleConfig.tabVal
			},
			/*标题是文本还是图片*/
			titleConfig(){
				return this.dataConfig.titleConfig.tabVal
			},
			/*标题文本*/
			titleTxtConfig(){
				return this.dataConfig.titleTxtConfig.value
			},
			/*标题图片*/
			titleImg(){
				return this.dataConfig.imgConfig.url
			},
			/*卡片颜色和圆角*/
			rankCardStyle(){
				let filletBg = this.dataConfig.filletBg.type;
				let valListBg = this.dataConfig.filletBg.valList;
				let filletValBg = this.dataConfig.filletBg.val;
				return {
					borderRadius: filletBg ? valListBg[0].val+ 'px ' +valListBg[1].val+ 'px ' + valListBg[3].val + 'px ' + valListBg[2].val +'px': filletValBg +'px',
					background: `linear-gradient(172deg, ${this.dataConfig.listBgColor.color[0].item} 0%, ${this.dataConfig.listBgColor.color[1].item} 100%)`
				}
			},
			/*商品图片圆角样式*/
			imgStyle(){
				let borderRadius = `${this.dataConfig.filletImg.val * 2}rpx`;
				if (this.dataConfig.filletImg.type) {
					borderRadius =
						`${this.dataConfig.filletImg.valList[0].val * 2}rpx ${this.dataConfig.filletImg.valList[1].val * 2}rpx ${this.dataConfig.filletImg.valList[3].val * 2}rpx ${this.dataConfig.filletImg.valList[2].val * 2}rpx`;
				}
				return borderRadius
			},
			rankItemTitleStyle(){
				return {
					color: this.dataConfig.toneConfig.tabVal ? `${this.dataConfig.classColor.color[0].item} !important` : 'var(--view-theme) !important'
				}
			},
			/* 价格颜色 */
			priceColor(){
				return this.dataConfig.toneConfig.tabVal ? this.dataConfig.goodsPriceColor.color[0].item : 'var(--view-theme)'
			},
			/*头部按钮样式*/
			headerBntColor(){
				return {
					color: this.dataConfig.headerBntColor.color[0].item,
					fontSize: `${this.dataConfig.bntNumber.val * 2}rpx`
				}
			},
			headerBtnText(){
				return this.dataConfig.rightBntConfig.value
			}
		},
		mounted() {
			this.getList();
		},
		methods:{
			getList(){
				diyRankApi().then(res=>{
					this.collect = res.data.collect;
					this.sales = res.data.sales;
					this.star = res.data.star;
				})
			},
			goRank(){
				uni.navigateTo({
					url:'/pages/columnGoods/rank/index'
				})
			}
		}
	}
</script>

<style>
	.rank-card {
		width: 372rpx;
		padding: 22rpx 16rpx 18rpx;
	}
	.rank_count{
		width:30rpx;
		height:32rpx;
		position: absolute;
		top:0;
		left:0;
		background-size: cover;
	}
	.rank-count1{
		background-image:url('@/static/images/rank1_icon.png');
	}
	.rank-count2{
		background-image:url('@/static/images/rank2_icon.png');
	}
	.rank-count3{
		background-image:url('@/static/images/rank3_icon.png');
	}
	.type-3{
		padding: 20rpx 36rpx 20rpx 18rpx;
	}
	.rank-pro-item ~ .rank-pro-item{
		margin-top: 16rpx;
	}
</style>