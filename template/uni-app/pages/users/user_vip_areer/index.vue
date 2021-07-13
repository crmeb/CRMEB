<template>
	<view>
		<view class="detailed">
			<!-- <view class="public_title acea-row row-middle">
				<view class="icons"></view>经验值明细
			</view> -->
			<view v-if="expList.length" class="list">
				<view class="item acea-row row-between-wrapper" v-for="(item,index) in expList">
					<view class="text">
						<view class="name">{{item.title}}</view>
						<view class="data">{{item.add_time}}</view>
					</view>
					<view class="num" v-if="item.pm">+{{item.number}}</view>
					<view class="num on" v-else>-{{item.number}}</view>
				</view>
			</view>
			<view v-if="!expList.length && !loading" class="empty">
				<image class="image" src="/static/images/empty-box.png"></image>
				<view>暂无经验记录</view>
			</view>
		</view>
		<view class='loadingicon acea-row row-center-wrapper' v-if="expList.length">
			<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
		</view>
	</view>
</template>

<script>
	import {
		getUserInfo,
		getlevelInfo,
		getlevelExpList
	} from '@/api/user.js';
	export default {
		data() {
			return {
				loading: false,
				loadend: false,
				loadTitle: '加载更多', //提示语
				page: 1,
				limit: 20,
				expList: []
			}
		},
		created() {
			this.getlevelList();
		},
		methods: {
			getlevelList: function() {
				let that = this
				if (this.loadend) return false;
				if (this.loading) return false;
				getlevelExpList({
					page: that.page,
					limit: that.limit
				}).then(res => {
					let list = res.data,
						loadend = list.length < that.limit;
					let expList = that.$util.SplitArray(list, that.expList);
					that.$set(that, 'expList', expList);
					that.loadend = loadend;
					that.loadTitle = loadend ? '我也是有底线的' : '加载更多';
					that.page = that.page + 1;
					that.loading = false;
				}).catch(err => {
					that.loading = false;
					that.loadTitle = '加载更多';
				});
			}
		},
	}
</script>

<style>
	page {
		background-color: #FFFFFF;
	}
</style>

<style lang="scss" scoped>
	.detailed {
		padding: 0 30rpx 0 30rpx;
		// margin-top: 15rpx;
		background-color: #fff;

		.list {
			// margin-top: 15rpx;

			.item {
				height: 122rpx;
				border-bottom: 1px solid #EEEEEE;

				.text {
					.name {
						font-size: 28rpx;
						color: #282828;
					}

					.data {
						color: #999;
						font-size: 24rpx;
					}
				}

				.num {
					font-size: 32rpx;
					color: #16AC57;
				}

				.on {
					color: #E93323;
				}
			}
		}
	}

	.empty {
		padding-top: 300rpx;
		font-size: 30rpx;
		text-align: center;
		color: #999999;
	}

	.empty .image {
		width: 414rpx;
		height: 214rpx;
		margin-bottom: 20rpx;
	}
</style>
