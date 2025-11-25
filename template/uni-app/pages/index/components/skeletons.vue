<template>
	<view class="skeleton">
		<!-- #ifdef MP || APP -->
		<view class="blank"></view>
		<!-- #endif -->
		<view class="header acea-row row-between-wrapper">
			<view class="left"></view>
			<view class="right"></view>
		</view>
		<view class="tabNav acea-row row-between-wrapper">
			<view class="item"></view>
			<view class="item"></view>
			<view class="item"></view>
			<view class="item"></view>
		</view>
		<view class="swipers"></view>
		<view class="nav acea-row row-between-wrapper">
			<view class="item"></view>
			<view class="item"></view>
			<view class="item"></view>
			<view class="item"></view>
			<view class="item"></view>
		</view>
		<view class="nav acea-row row-between-wrapper">
			<view class="item"></view>
			<view class="item"></view>
			<view class="item"></view>
			<view class="item"></view>
			<view class="item"></view>
		</view>
		<view class="header acea-row row-between-wrapper">
			<view class="left"></view>
			<view class="right"></view>
		</view>
		<view class="coupon acea-row row-between-wrapper">
			<view class="item"></view>
			<view class="item"></view>
		</view>
		<view class="goodList">
			<view class="item acea-row row-between row-top">
				<view class="pictrue"></view>
				<view class="text">
					<view class="name"></view>
					<view class="money"></view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
</script>

<style lang="scss">
	.skeleton{
		padding: 0 30rpx;
		.blank{
			width: 100%;
			height: 150rpx;
			background-color: #F5F5F5;
		}
		.header{
			.left{
				width: 200rpx;
				height: 50rpx;
				background-color: #e9e9e9;
			}
			.right{
				width: 460rpx;
				height: 50rpx;
				background-color: #e9e9e9;
			}
		}
		.tabNav{
			margin-top: 20rpx;
			.item{
				width: 150rpx;
				height: 50rpx;
				background-color: #e9e9e9;
			}
		}
		.swipers{
			margin-top: 20rpx;
			width: 100%;
			height: 400rpx;
			background-color: #e9e9e9;
		}
		.nav{
			margin: 20rpx 0;
			.item{
				width: 100rpx;
				height: 100rpx;
				border-radius: 50%;
				background-color: #e9e9e9;
			}
		}
		.coupon{
			margin-top: 20rpx;
			.item{
				width: 330rpx;
				height: 120rpx;
				background-color: #e9e9e9;
			}
		}
		.goodList{
			margin-top: 20rpx;
			.item{
				.pictrue{
					width: 220rpx;
					height: 220rpx;
					background-color: #e9e9e9;
				}
				.text{
					.name{
						width: 440rpx;
						height: 76rpx;
						background-color: #e9e9e9;
					}
					.money{
						width: 100rpx;
						height: 34rpx;
						background-color: #e9e9e9;
						margin-top: 10rpx;
					}
				}
			}
		}
	}
</style>
