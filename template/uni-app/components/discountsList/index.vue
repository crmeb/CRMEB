<template>
	<view class="discounts">
		<view class="title">
			<text>搭配购</text>
			<span class="iconfont icon-xiangyou"></span>
		</view>
		<view class="discounts-list">
			<view class="discounts-box" v-for="item in discountsData" :key="item.id">
				<image class="discounts-img" :src="item.image" mode=""></image>
				<view class="discounts-msg">
					<text class="discounts-title line1">{{item.title}}</text>
					<view class="discounts-attr">
						<text class="line2" v-for="(attr,index) in item.products" :key="index">
							<text v-if="index > 0">+</text>
							<text>
								{{attr.title.substring(0,5)}}
							</text>
						</text>
					</view>
					<text class="discounts-kc">库存：{{item.is_limit ? item.limit_num : '充足'}}</text>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	export default {
		data() {
			return {

			}
		},
		props: {
			discountsData: {

			}
		}
	}
</script>

<style lang="scss" scoped>
	.discounts {
		background-color: #fff;

		.title {
			display: flex;
			justify-content: space-between;
			border-bottom: 1px solid #EEEEEE;
			padding: 24rpx 30rpx;
			margin-top: 20rpx;
			color: #333;

			.iconfont {
				font-size: 14px;
				color: #7a7a7a;
			}
		}

		.discounts-list {
			display: flex;
			align-items: center;
			width: 100%;
			overflow-x: scroll;

			.discounts-box {
				display: flex;
				align-items: center;
				padding: 24rpx;

				.discounts-img {
					width: 180rpx;
					height: 180rpx;
					border-radius: 8rpx;
					margin-right: 24rpx;
				}

				.discounts-msg {
					display: flex;
					flex-direction: column;
					justify-content: space-around;
					color: #333333;
					height: 164rpx;

					.discounts-title {
						font-size: 28rpx;
						font-weight: bold;
						width: 150px;
						span{
							width: 100%;
						}
					}

					.discounts-attr {
						display: flex;
						flex-wrap: wrap;
						width: 300rpx;
						max-height: 2rem;
						font-size: 20rpx;
						overflow: hidden;
					}

					.discounts-kc {
						color: var(--view-theme);
						font-size: 20rpx;
					}
				}
			}
		}
	}
</style>
