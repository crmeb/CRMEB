<template>
	<view class="main">
		<view class="top-tabs" :style="colorStyle">
			<view class="tabs" :class="{btborder:type === index}" v-for="(item,index) in tabsList" :key="index"
				@tap="changeTabs(index)">
				{{item.name}}
			</view>
		</view>
		<view v-if="list.length && type ===1" class="list">
			<view v-for="(item, index) in list" :key="index" class="item" @click="goChat(item.to_uid)">
				<view class="image-wrap">
					<image class="image" :src="item.avatar"></image>
				</view>
				<view class="text-wrap">
					<view class="name-wrap">
						<view class="name">{{ item.nickname }}</view>
						<view>{{ item._update_time }}</view>
					</view>
					<view class="info-wrap">
						<view v-if="item.message_type === 1" class="info" v-html="item.message"></view>
						<view v-if="item.message_type === 2" class="info" v-html="item.message"></view>
						<view v-if="item.message_type === 3" class="info">[图片]</view>
						<view v-if="item.message_type === 4" class="info">[语音]</view>
						<view v-if="item.message_type === 5" class="info">[商品]</view>
						<view v-if="item.message_type === 6" class="info">[订单]</view>
						<view class="num" v-if="item.mssage_num">{{ item.mssage_num }}</view>
					</view>
				</view>
			</view>
		</view>
		<view class="list" v-if="list.length && type === 0">
			<view v-for="(item, index) in list" :key="index" class="item" @click="goDetail(item.id)">
				<view class="image-wrap">
					<image v-if="item.type === 1" class="image" src="../../../static/images/admin-msg.png"></image>
					<image v-else class="image" src="../../../static/images/user-msg.png"></image>
					<view class="no-look" v-if="!item.look"></view>
				</view>
				<view class="text-wrap">
					<view class="name-wrap">
						<view class="name">{{ item.title || '--' }}</view>
						<view>{{ item.add_time }}</view>
					</view>
					<view class="info-wrap">
						<view class="info" v-html="item.content"></view>
					</view>
				</view>
			</view>
		</view>
		<view v-else-if="finished && !list.length" class="empty-wrap">
			<view class="image-wrap">
				<image class="image" src="../../../static/images/noMessage.png"></image>
			</view>
			<view>亲、暂无消息记录哟！</view>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		serviceRecord,
		messageSystem
	} from '@/api/user.js';
	import colors from '@/mixins/color.js';
	import home from '@/components/home';
	export default {
		mixins:[colors],
		components: {
			home
		},
		data() {
			return {
				list: [],
				page: 1,
				type: 0,
				limit: 20,
				loading: false,
				finished: false,
				tabsList: [{
					key: 0,
					name: '站内消息'
				}, {
					key: 1,
					name: '客服消息'
				}]
			};
		},
		onShow() {
			this.page = 1
			this.list = []
			console.log(this.type)
			this.changeTabs(this.type)
		},
		onReachBottom() {
			if (this.type === 1) {
				this.getList()
			} else {
				this.messageSystem()
			}
		},
		onPullDownRefresh() {
			console.log('refresh');
			this.page = 1
			this.finished = false
			this.list = []
			if (this.type === 1) {
				this.getList()
			} else {
				this.messageSystem()
			}
		},
		methods: {
			changeTabs(index) {
				this.type = index
				this.page = 1
				this.limit = 20
				this.list = []
				this.finished = false
				if (index === 1) {
					this.getList()
				} else {
					this.messageSystem()
				}
			},
			// 站内信
			messageSystem() {
				if (this.loading || this.finished) {
					return;
				}
				this.loading = true;
				uni.showLoading({
					title: '加载中'
				});
				messageSystem({
						page: this.page,
						limit: this.limit
					})
					.then(res => {
						console.log(res)
						let data = res.data;
						uni.hideLoading();
						this.loading = false;
						this.list = this.list.concat(data.list);
						this.finished = data.list.length < this.limit;
						this.page += 1;
						uni.stopPullDownRefresh();
					})
					.catch(err => {
						console.log(err)
						uni.showToast({
							title: err.msg,
							icon: 'none'
						})
					})
			},
			// 客服list
			getList() {
				if (this.loading || this.finished) {
					return;
				}
				this.loading = true;
				uni.showLoading({
					title: '加载中'
				});
				serviceRecord({
						page: this.page,
						limit: this.limit
					})
					.then(res => {
						uni.stopPullDownRefresh();
						let data = res.data;
						uni.hideLoading();
						this.loading = false;
						data.forEach(item => {
							if (item.message_type === 1) {
								item.message = this.replace_em(item.message);
							}
							if (item.message_type === 2) {
								item.message = this.replace_em(item.message);
							}
						});
						this.list = this.list.concat(data);
						this.finished = data.length < this.limit;
						this.page += 1;
					})
					.catch(err => {
						uni.showToast({
							title: err.msg,
							icon: 'none'
						})
					})
			},
			replace_em(str) {
				str = str.replace(/\[em-([a-z_]*)\]/g, "<span class='em em-$1'/></span>");
				return str;
			},
			goChat(id) {
				// this.$router.push({ path: '/pages/customer_list/chat'})
				uni.navigateTo({
					url: '/pages/customer_list/chat?to_uid=' + id + '&type=1'
				})
			},
			goDetail(id) {
				uni.navigateTo({
					url: '/pages/users/message_center/messageDetail?id=' + id,
				})
			},
		},
	}
</script>

<style lang="scss" scoped>
	.list {
		// background-color: #fff;
		overflow: hidden;
		padding-top: 100rpx;

		.item {
			background-color: #fff;
			display: flex;
			align-items: center;
			// height: 130rpx;
			padding: 30rpx 30rpx;
			margin: 10rpx 20rpx;
			border-radius: 12rpx;
			box-shadow: 0px 0px 1px 0px rgba(235, 214, 214, 0.75);
			-webkit-box-shadow: 0px 0px 1px 0px rgba(235, 214, 214, 0.75);
			-moz-box-shadow: 0px 0px 1px 0px rgba(235, 214, 214, 0.75);

			~.item {
				border-top: 1rpx solid #f5f5f5;
			}
			.image{
				border-radius: 50%;
			}
			
		}

		.image-wrap {
			width: 88rpx;
			height: 88rpx;
			border-radius: 50%;
			position: relative;

			.no-look {
				position: absolute;
				width: 18rpx;
				height: 18rpx;
				border-radius: 50%;
				background-color: #1ABB1D;
				top: 0rpx;
				right: 0rpx;
				z-index: 999;
			}
		}

		.image {
			display: block;
			width: 100%;
			height: 100%;
		}

		.text-wrap {
			flex: 1;
			min-width: 0;
			margin-left: 20rpx;
		}

		.name-wrap {
			display: flex;
			align-items: center;
			font-size: 20rpx;
			color: #ccc;
		}

		.name {
			flex: 1;
			min-width: 0;
			margin-right: 20rpx;
			overflow: hidden;
			white-space: nowrap;
			text-overflow: ellipsis;
			font-size: 28rpx;
			color: #333;
		}

		.info-wrap {
			display: flex;
			align-items: center;
			margin-top: 18rpx;
		}

		.info {
			flex: 1;
			min-width: 0;
			overflow: hidden;
			white-space: nowrap;
			text-overflow: ellipsis;
			font-size: 24rpx;
			color: #999;
		}

		.num {
			min-width: 32rpx;
			height: 32rpx;
			border-radius: 16rpx;
			margin-left: 20rpx;
			background-color: #e93323;
			font-size: 20rpx;
			line-height: 32rpx;
			text-align: center;
			color: #fff;
		}
	}

	.empty-wrap {
		font-size: 26rpx;
		text-align: center;
		color: #999;

		.image-wrap {
			width: 414rpx;
			height: 436rpx;
			padding-top: 100rpx;
			margin: 0rpx auto 0;
		}

		.image {
			display: block;
			width: 100%;
			height: 100%;
		}
	}

	.main {
		position: relative;
	}

	.top-tabs {
		position: fixed;
		width: 100%;
		display: flex;
		align-items: center;
		background-color: #fff;
		font-size: 28rpx;
		border-radius: 8rpx;
		padding: 20rpx 0;
		margin-bottom: 10rpx;
		z-index: 1000;
	}

	.tabs {
		display: flex;
		align-items: center;
		padding: 4rpx 15rpx;
		margin: 0 20rpx;
	}

	.btborder {
		color: #fff;
		background-color: var(--view-theme);
		border-radius: 30rpx;
	}
</style>
