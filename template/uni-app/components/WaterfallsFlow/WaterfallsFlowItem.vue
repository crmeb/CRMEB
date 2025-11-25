<template>
	<view class="wf-item-page wf-page0">
		<view class='pictrue'>
			<easy-loadimage
			mode="widthFix"
			:image-src="item.image"
			width="100%"
			height="345rpx"
			borderRadius="16rpx 16rpx 0 0"></easy-loadimage>
		</view>
		<view class="info_box">
			<view class="w-full line2 fs-28 text--w111-333 lh-40rpx">
				<text v-if="item.brand_name" class="brand-tag">{{ item.brand_name }}</text>{{item.store_name}}
			</view>
			<view class="mt-8">
				<view class="flex-y-center flex-wrap mt-8">
					<baseMoney :money="item.price" symbolSize="24" integerSize="40" decimalSize="24" weight></baseMoney>
					<view class="inline-block h-26 lh-28rpx rd-14rpx bg--w111-F7E9CD fs-22 ml-8" 
						v-if="Number(item.vip_price) > 0">
						<text class="inline-block h-26 lh-28rpx svip_rd fs-18 bg--w111-484643 text--w111-FDDAA4 px-8">SVIP</text>
						<text class="px-8 fs-22 SemiBold">¥{{item.vip_price}}</text>
					</view>
				</view>
				<view class="flex-between-center mt-12">
					<text class="fs-22 text--w111-999">已售{{item.sales}}{{item.unit_name}}</text>
					<view class="w-44 h-44 rd-24 bg-gradient flex-center" v-if="goDetail=='goDetail'">
						<text class="iconfont icon-ic_ShoppingCart1 text--w111-fff fs-26"></text>
					</view>
					<view class="w-44 h-44 rd-24 bg-gradient flex-center" @tap.stop="addCartChange" v-else>
						<text class="iconfont icon-ic_ShoppingCart1 text--w111-fff fs-26"></text>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>
<script>
	import easyLoadimage from '@/components/easy-loadimage/easy-loadimage.vue'
	import {mapGetters} from "vuex";
	import {HTTP_REQUEST_URL} from '@/config/app';
	export default {
		components: {
			easyLoadimage
		},
		props: {
			item: {
				type: Object,
				require: true
			},
			type: {
				type: Number,
				default: 0
			},
			recommend:{
				type: Boolean,
				default: false
			},
			goDetail: {
				type: String,
				default: ''
			}
		},
		data() {
			return {
				domain: HTTP_REQUEST_URL
			}
		},
		methods: {
			addCartChange(){
				this.$eventHub.$emit('onCartAddChange',this.item);
			},
		}
	}
</script>
<style lang="scss" scoped>
	.wf-item-page {
		background: #fff;
		overflow: hidden;
		border-radius: 20rpx;
	}
	.info_box{
		padding: 16rpx 20rpx;
		border-radius: 0 0 20rpx 20rpx;
		background-color: #fff;
	}
	.text-primary-con{
		color: var(--view-theme);
	}
	.bg-primary-light{
		background: var(--view-minorColorT);
	}
	.bg--w111-484643{
		background: linear-gradient(90deg, #484643 0%, #1F1B17 100%);
	}
	.text--w111-FDDAA4{
		color: #FDDAA4;
	}
	.svip_rd{
		border-radius: 14rpx 0 8rpx 14rpx;
	}
</style>
