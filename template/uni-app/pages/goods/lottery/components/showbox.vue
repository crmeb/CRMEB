<template>
	<view class="show-box">
		<view class="in-border">
			<view class="table-title">
				<image src="../../static/head-l-point.png" mode=""></image>
				<view class="text" v-if="showMsg.type === 'user'">
					{{$t(`中奖记录`)}}
				</view>
				<view class="text" v-else-if="showMsg.type === 'me'">
					{{$t(`我的奖品`)}}
				</view>
				<view class="text" v-else-if="showMsg.type === 'html'">
					{{$t(`活动规则`)}}
				</view>
				<image src="../../static/head-r-point.png" mode=""></image>
			</view>
			<view class="table" v-if="['me','user'].includes(showMsg.type)">
				<view class="table-head">
					<view class="nickname">{{showMsg.type === 'user' ? $t(`昵称`) : $t(`序号`)}}</view>
					<view class="table-name">{{$t(`奖品名称`)}}</view>
					<view class="table-name time">{{$t(`获奖时间`)}}</view>
				</view>
				<view class="table-d">
					<view class="table-body" v-for="(item,index) in showMsg.data" :key="index">
						<view class="nickname">
							{{showMsg.type === 'user' ? item.user.nickname : index + 1}}
						</view>
						<view class="table-name">
							{{item.prize.name}}
						</view>
						<view class="table-name time">
							{{item.add_time}}
						</view>
					</view>
				</view>
			</view>
			<view class="content" v-else v-html="showMsg.data"></view>
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
			showMsg: {
				type: Object
			},
		}
	}
</script>

<style lang="scss" scoped>
	.show-box {
		display: flex;
		flex-direction: column;
		align-items: center;
		background-color: #FFFEFE;
		margin: 20px;
		padding: 4px;
		border-radius: 12rpx;
		box-shadow: 0px 3px 0px 0px #FCF5C8;
	}

	.in-border {
		width: 100%;
		display: flex;
		flex-direction: column;
		align-items: center;
		border: 1px dashed #FF7F5F;
		border-radius: 12rpx;
		padding: 35rpx 0;

	}

	.table-title {
		display: flex;
		align-items: center;
		margin-bottom: 30rpx;

		.text {
			color: #E74435;
			font-size: 36rpx;
			font-weight: 600;
			padding: 0 12rpx;
		}

		image {
			width: 50rpx;
			height: 16rpx;
		}
	}

	.table-d {
		max-height: 200rpx;
		overflow-y: scroll;
	}

	.table {
		width: 100%;

		.table-head,
		.table-body {
			display: flex;
			justify-content: space-around;
			width: 100%;
		}

		.table-head {
			color: #A57E7E;

			.nickname {
				width: 30%;
				padding: 10rpx 20rpx;
			}

			.table-name {
				width: 30%;
				text-align: left;
				padding: 10rpx 20rpx;
			}

			.time {
				width: 40%;
			}
		}



		.table-body {
			color: #282828;

			.nickname {
				width: 30%;
				font-size: 24rpx;
				padding: 10rpx 20rpx;
			}

			.table-name {
				width: 30%;
				text-align: left;
				text-overflow: ellipsis;
				white-space: nowrap;
				overflow: hidden;
				font-size: 24rpx;
				padding: 10rpx 20rpx;
			}

			.time {
				width: 40%;
			}
		}
	}

	.content {
		width: 100%;
		padding: 0 20rpx;
	}
</style>
