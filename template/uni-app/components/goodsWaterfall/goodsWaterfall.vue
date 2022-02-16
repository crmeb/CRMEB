<template>
	<view>
		<view class="feng_flow">
			<view class="flow_block">
				<view class="product-list pr10" v-for="(item, i1) in lists1" :key="i1" @click="goGoodsDetail(item)">
					<view class="product-item">
						<image :src="item.image" mode="widthFix" fade-show style="width: 100%;"></image>
						<view class="info">
							<view class="title line2">{{ item.store_name }}</view>
							<view class="tag" v-if="item.activity && item.activity.type === '1'">秒杀</view>
							<view class="tag" v-if="item.activity && item.activity.type === '2'">砍价</view>
							<view class="tag" v-if="item.activity && item.activity.type === '3'">拼团</view>
							<view class="price-box">
								<view>
									<text>￥</text>
									{{ item.price }}
								</view>
								<view class="sales">
									已售 {{item.sales}}
								</view>
							</view>
						</view>
					</view>
				</view>
			</view>
			<view class="flow_block">
				<view class="product-list pl10" v-for="(item2, i2) in lists2" :key="i2" @click="goGoodsDetail(item2)">
					<view class="product-item">
						<image :src="item2.image" mode="widthFix" fade-show style="width: 100%;"></image>
						<view class="info">
							<view class="title line2">{{ item2.store_name }}</view>
							<view class="tag" v-if="item2.activity && item2.activity.type === '1'">秒杀</view>
							<view class="tag" v-if="item2.activity && item2.activity.type === '2'">砍价</view>
							<view class="tag" v-if="item2.activity && item2.activity.type === '3'">拼团</view>
							<view class="price-box">
								<view>
									<text>￥</text>
									{{ item2.price }}
								</view>
								<view class="sales">
									已售 {{item2.sales}}
								</view>
							</view>
						</view>
					</view>
				</view>
			</view>
		</view>
		<view class="feng_flow" style="display: none;">
			<view class="flow_block">
				<view class="flow_item" v-for="(data,da_i) in dataLists" :key="da_i">
					<image :src="data.image" @error="imgError" @load="imgLoad" :id="da_i" mode="widthFix"
						style="width:100%;"></image>
				</view>
			</view>
			<view class="flow_block"></view>
		</view>
	</view>
</template>

<script>
	import {
		goShopDetail,
		goPage
	} from '@/libs/order.js'
	export default {
		name: 'goodsWaterfall',
		props: {
			dataLists: {
				default: []
			}
		},
		data() {
			return {
				lists1: [], //左侧内容
				lists2: [], //右侧内容
				list1Height: 0, //左侧计算高度
				list2Height: 0, //右侧计算高度
				tmp_data: [],
				loaded: [], //图片加载成功数组
				loadErr: [], //图片加载失败数组
				showLoad: false,
			};
		},
		methods: {
			goGoodsDetail(item) {
				goPage().then(res => {
					goShopDetail(item, this.uid).then(res => {
						uni.navigateTo({
							url: `/pages/goods_details/index?id=${item.id}`
						})
					})
				})
			},
			//处理数据
			refresData() {
				this.hideLoadFlag()
				// if (this.loaded.length + this.loadErr.length < this.tmp_data.length) return;
				const that = this
				that.list1Height = 0
				that.list2Height = 0
				that.lists1 = []
				that.lists2 = []
				if (!this.tmp_data.length) {
					that.list1Height = 0
					that.list2Height = 0
					that.lists1 = []
					that.lists2 = []
					return
				}
				let a = 0,
					b = 0
				if (this.tmp_data.length) {
					for (let i = 0; i < this.tmp_data.length; i++) {
						if (that.list1Height > that.list2Height) {
							that.list2Height += this.tmp_data[i].img_height
							// if (that.lists1[b]) {
							// 	that.lists2.splice(b, 1, this.tmp_data[i])
							// } else {
							that.lists2.push(this.tmp_data[i])
							// }
							b += 1
						} else {
							that.list1Height += this.tmp_data[i].img_height
							// if (that.lists1[a]) {
							// 	that.lists1.splice(a, 1, this.tmp_data[i])
							// } else {
							that.lists1.push(this.tmp_data[i])
							// }
							a += 1
						}
						if (this.tmp_data.length - 1 === i) {
							// 设置当前左右件数
						}
					}

				}
			},
			//图片加载完成补齐数据
			imgLoad(e) {
				this.loaded.push(e.target.id)
				//存储数据
				// this.tmp_data[e.target.id]['img_width'] = e.detail.width
				this.tmp_data[e.target.id]['img_height'] = (167.5 * e.detail.height) / e.detail.width
			},
			//图片未加载成功触发
			imgError(e) {
				this.loadErr.push(e.target.id)
			},
			showLoadFlag() {
				if (!this.showLoad) {
					this.showLoad = true
				}
			},
			hideLoadFlag() {
				if (this.showLoad) {
					uni.hideLoading();
					this.showLoad = false;
				}
			}
		},
		mounted() {
			const that = this
			that.tmp_data = that.dataLists
			that.showLoadFlag()
		},
		watch: {
			dataLists() {
				this.loaded = []
				this.loadErr = []
				this.tmp_data = this.dataLists
				this.showLoadFlag()
			},
			loaded(e) {
				//最后一个加载完成负责刷新数据
				// if (e.length === this.tmp_data.length) {
				this.refresData()
				// }
			},
			loadErr() {
				//最后一个加载完成负责刷新数据
				// this.refresData()
			}
		}
	};
</script>

<style lang="scss" scoped>
	.feng_flow {
		display: flex;
		margin: 0 30rpx;
	}

	.flow_block {
		display: flex;
		flex: 1;
		flex-direction: column;
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

	.pr10 {
		padding-right: 10rpx;
	}

	.product-list {
		display: flex;

		.product-item {
			position: relative;
			width: 100%;
			background: #fff;
			border-radius: 10rpx;
			margin-bottom: 20rpx;

			image {
				width: 100%;
				// height: 330rpx;
				border-radius: 10rpx 10rpx 0 0;
			}

			.info {
				padding: 14rpx 16rpx;

				.title {
					font-size: 28rpx;
				}

				.tag {
					border-radius: 4rpx;
					border: 1px solid var(--view-theme);
					color: var(--view-theme);
					font-size: 20rpx;
					padding: 0rpx 4rpx;
					margin: 10rpx 0;
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
