<template>
	<view>
		<view class="list">
			<view class="product-box">
				<view class="product-list" v-for="(item, i1) in tmp_data" :key="i1" @click="goGoodsDetail(item)">
					<view class="product-item">
						<easy-loadimage mode="widthFix" :image-src="item.image"></easy-loadimage>
						<view class="info">
							<view class="title line2">
								<text class="tag" v-if="item.activity && item.activity.type === '1' && $permission('seckill')">{{$t(`秒杀`)}}</text>
								<text class="tag" v-if="item.activity && item.activity.type === '2' && $permission('bargain')">{{$t(`砍价`)}}</text>
								<text class="tag" v-if="item.activity && item.activity.type === '3' && $permission('combination')">{{$t(`拼团`)}}</text>
								<text class="tag" v-if="item.checkCoupon">{{$t(`券`)}}</text>
								{{ item.store_name }}
							</view>

							<view class="price-box">
								<view>
									<text>{{$t(`￥`)}}</text>
									{{ item.price }}
								</view>
								<view class="sales">
									{{$t(`已售`)}} {{item.sales}}
								</view>
							</view>
						</view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script setup>
	import { ref, onMounted, watch } from 'vue';
	import { storeToRefs } from 'pinia';
	import { useAppStore } from '@/store/modules/app';
	import {
		goShopDetail,
		goPage
	} from '@/libs/order.js'

	defineOptions({ name: 'goodsWaterfall' });

	// 原组件 uid 未定义即被引用（无 mapGetters），此处按同类组件意图接入 store 的 uid
	const appStore = useAppStore();
	const { uid } = storeToRefs(appStore);

	const props = defineProps({
		dataLists: {
			default: []
		}
	});

	const lists = ref([]);
	const showLoad = ref(false);
	const tmp_data = ref([]);
	// 原代码在 watch 中给未声明的 loaded/loadErr 赋值（无效赋值），此处保留同名局部变量以维持原行为
	let loaded = [];
	let loadErr = [];

	function goGoodsDetail(item) {
		goPage().then(res => {
			goShopDetail(item, uid.value).then(res => {
				uni.navigateTo({
					url: `/pages/goods_details/index?id=${item.id}`
				})
			})
		})
	}

	onMounted(() => {
		tmp_data.value = props.dataLists
		// that.showLoadFlag()
	})

	watch(() => props.dataLists, () => {
		loaded = []
		loadErr = []
		tmp_data.value = props.dataLists
	})
</script>

<style lang="scss" scoped>
	.list {
		display: flex;
		margin: 0 30rpx;
	}

	.product-box {
		display: flex;
		flex: 1;
		flex-wrap: wrap;
		width: 100%;
	}

	.flow_item {
		margin: 15upx;
		border-radius: 20upx;
		background: #f4f4f4;
		overflow: hidden;
	}

	.flow_item_con {
		padding: 10upx 20upx 20upx;
	}

	.flow_item_title {
		position: relative;
		font-size: 32upx;
		font-weight: 700;
		margin-bottom: 5upx;
	}

	.flow_item_des {
		font-size: 24upx;
	}

	.pl10 {
		padding-left: 10rpx;
	}

	.product-list {
		display: flex;
		width: calc(50% - 16rpx);
		margin: 2rpx 8rpx;

		.product-item {
			position: relative;
			width: 100%;
			background: #fff;
			border-radius: 10rpx;
			margin-bottom: 8rpx;
			display: flex;
			flex-direction: column;
			justify-content: space-between;

:deep(image),
:deep(.easy-loadimage),
:deep(uni-image){
				width: 100%;
				height: 330rpx;
				border-radius: 10rpx 10rpx 0 0;
			}

			.info {
				flex: 1;
				padding: 14rpx 16rpx;
				display: flex;
				flex-direction: column;
				justify-content: space-between;

				.title {
					font-size: 28rpx;
					height: 76rpx;
					line-height: 38rpx;
				}

				.tag {
					border-radius: 4rpx;
					border: 1px solid var(--view-theme);
					color: var(--view-theme);
					font-size: 20rpx;
					padding: 0rpx 4rpx;
					margin: 10rpx 0;
					margin-right: 10rpx;
					width: max-content;
				}

				.price-box {
					font-size: 34rpx;
					font-weight: 700;
					margin-top: 8px;
					color: var(--view-priceColor);
					display: flex;
					justify-content: space-between;
					// align-items: flex-end;
					align-items: center;

					text {
						font-size: 28rpx;
					}

					.sales {
						color: #999999;
						font-size: 24rpx;
						font-weight: 400;
					}
				}
			}
		}
	}
</style>
