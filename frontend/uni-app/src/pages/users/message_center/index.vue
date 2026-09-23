<template>
	<view class="main">
		<view class="top-tabs" :style="colorStyle">
			<view class="tabs">
				<view class="item" :class="{btborder:type === index}" v-for="(item,index) in tabsList" :key="index"
					@tap="changeTabs(index)">
					{{$t(item.name)}}
				</view>
			</view>
			<view class="read-all" @click="allLook()">
				{{$t(`全部已读`)}}
			</view>
		</view>
		<view v-if="list.length && type ===1" class="list">
			<view v-for="(item, index) in list" :key="index" class="item" @click="goChat(item)">
				<view class="image-wrap">
					<image class="image" :src="item.avatar"></image>
				</view>
				<view class="text-wrap">
					<view class="name-wrap">
						<view class="name">{{ $t(item.nickname) }}</view>
						<view>{{ item._update_time }}</view>
					</view>
					<view class="info-wrap">
						<view v-if="item.message_type === 1" class="info" v-html="item.message"></view>
						<view v-if="item.message_type === 2" class="info" v-html="item.message"></view>
						<view v-if="item.message_type === 3" class="info">{{$t(`[图片]`)}}</view>
						<view v-if="item.message_type === 4" class="info">{{$t(`[语音]`)}}</view>
						<view v-if="item.message_type === 5" class="info">{{$t(`[商品]`)}}</view>
						<view v-if="item.message_type === 6" class="info">{{$t(`[订单]`)}}</view>
						<view class="num" v-if="item.mssage_num">{{ item.mssage_num }}</view>
					</view>
				</view>
			</view>
		</view>
		<view class="list" v-if="list.length && type === 0">
			<view v-for="(item, index) in list" :key="index">
				<tuiDrawer @click="(e)=>bindClick(e,item)" :key="item.id" :actions="!item.look ? actions :actionsIsLook"
					:params="{id:item.id}">
					<template v-slot:content>
						<view class="item" @click="goDetail(item.id)">
							<view class="image-wrap">
								<image v-if="item.type === 1" class="image" src="../static/admin-msg.png"></image>
								<image v-else class="image" src="../static/user-msg.png"></image>
								<view class="no-look" v-if="!item.look"></view>
							</view>
							<view class="text-wrap">
								<view class="name-wrap">
									<view class="name">{{ $t(item.title) || '--' }}</view>
									<view>{{ item.add_time }}</view>
								</view>
								<view class="info-wrap">
									<view class="info" v-html="item.content"></view>
								</view>
							</view>
						</view>

					</template>

				</tuiDrawer>
			</view>
		</view>
		<view v-else-if="finished && !list.length" class="empty-wrap">
			<view class="image-wrap">
				<image class="image" :src="imgHost + '/statics/images/noMessage.png'"></image>
			</view>
			<view>{{$t(`亲、暂无消息记录哟！`)}}</view>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onShow, onReachBottom, onPullDownRefresh } from '@dcloudio/uni-app';
import {
	serviceRecord,
	messageSystem as messageSystemApi,
	msgLookDel
} from '@/api/user.js';
import home from '@/components/home';
import tuiDrawer from '@/components/tuiDrawer/index.vue'
import { useColorTheme } from '@/composables/useColorTheme';
import i18n from '@/utils/lang.js';
import {
	HTTP_REQUEST_URL
} from '@/config/app';

const { colorStyle } = useColorTheme();

// ---------- data ----------
const imgHost = HTTP_REQUEST_URL;
const list = ref([]);
const page = ref(1);
const type = ref(0);
const limit = ref(20);
const loading = ref(false);
const finished = ref(false);
const tabsList = [{
	key: 0,
	name: '站内消息'
}, {
	key: 1,
	name: '客服消息'
}];
const startData = ref({
	clientX: 0,
	clientY: 0
});
const actions = [{
		name: '删除',
		color: '#fff',
		fontsize: 28, //单位rpx
		width: 70, //单位px
		background: '#E6A23C'
	},
	{
		name: '已读',
		color: '#fff',
		fontsize: 28, //单位rpx
		width: 70, //单位px
		background: '#409EFF'
	},
];
const actionsIsLook = [{
	name: '删除',
	color: '#fff',
	fontsize: 28, //单位rpx
	width: 70, //单位px
	background: '#E6A23C'
}, ];

// ---------- 生命周期 ----------
onShow(() => {
	page.value = 1
	list.value = []
	changeTabs(type.value)
});
onReachBottom(() => {
	if (type.value === 1) {
		getList()
	} else {
		messageSystem()
	}
});
onPullDownRefresh(() => {
	page.value = 1
	finished.value = false
	list.value = []
	if (type.value === 1) {
		getList()
	} else {
		messageSystem()
	}
});

// ---------- methods ----------
function start(e) {
	startData.value.clientX = e.changedTouches[0].clientX;
	startData.value.clientY = e.changedTouches[0].clientY;
}
function end(e) {
	const subX = e.changedTouches[0].clientX - startData.value.clientX;
	const subY = e.changedTouches[0].clientY - startData.value.clientY;
	if (subY > 50 || subY < -50) {
		console.log('上下滑')
	} else {
		if (subX > 50) {
			console.log('右滑')
			if (type.value == 1) {
				type.value = 0
				changeTabs(type.value)
			}
		} else if (subX < -50) {
			if (type.value == 0) {
				type.value = 1
				changeTabs(type.value)
			}
			console.log('左滑')
		} else {
			console.log('无效')
		}
	}
}
// 滑动点击操作
function bindClick(e, item) {
	if (e.index == 0) {
		msgLookDel({
			id: item.id,
			key: 'is_del',
			value: 1
		}).then(res => {
			let i = list.value.findIndex(e => {
				return e.id === item.id
			})
			list.value.splice(i, 1)
		}).catch(err => {
			uni.showToast({
				title: err.msg,
				icon: 'none'
			})
		})
	} else {
		// 已读
		msgLookDel({
			id: item.id,
			key: 'look',
			value: 1
		}).then(res => {
			item.look = 1
		}).catch(err => {
			uni.showToast({
				title: err.msg,
				icon: 'none'
			})
		})
	}
}
function allLook() {
	msgLookDel({
		id: 0,
		key: 'look',
		value: 1,
		all: 1
	}).then(res => {
		page.value = 1
		limit.value = 20
		list.value = []
		finished.value = false
		if (type.value === 1) {
			getList()
		} else {
			messageSystem()
		}
	}).catch(err => {
		uni.showToast({
			title: err.msg,
			icon: 'none'
		})
	})
}
function changeTabs(index) {
	type.value = index
	page.value = 1
	limit.value = 20
	list.value = []
	finished.value = false
	if (index === 1) {
		getList()
	} else {
		messageSystem()
	}
}
// 站内信
function messageSystem() {
	if (loading.value || finished.value) {
		return;
	}
	loading.value = true;
	uni.showLoading({
		title: i18n.t(`加载中`)
	});
	messageSystemApi({
			page: page.value,
			limit: limit.value
		})
		.then(res => {
			let data = res.data;
			uni.hideLoading();
			loading.value = false;
			list.value = list.value.concat(data.list);
			finished.value = data.list.length < limit.value;
			page.value += 1;
			uni.stopPullDownRefresh();
		})
		.catch(err => {
			uni.showToast({
				title: err.msg,
				icon: 'none'
			})
		})
}
// 客服list
function getList() {
	if (loading.value || finished.value) {
		return;
	}
	loading.value = true;
	uni.showLoading({
		title: i18n.t(`加载中`)
	});
	serviceRecord({
			page: page.value,
			limit: limit.value
		})
		.then(res => {
			uni.stopPullDownRefresh();
			let data = res.data;
			uni.hideLoading();
			loading.value = false;
			data.forEach(item => {
				if (item.message_type === 1) {
					item.message = replace_em(item.message);
				}
				if (item.message_type === 2) {
					item.message = replace_em(item.message);
				}
			});
			list.value = list.value.concat(data);
			finished.value = data.length < limit.value;
			page.value += 1;
		})
		.catch(err => {
			uni.showToast({
				title: err.msg,
				icon: 'none'
			})
		})
}
function replace_em(str) {
	str = str.replace(/\[([^\[\]]+)\]/g, "<span class='em $1'/></span>");
	return str;
}
function goChat(item) {
	item.mssage_num = 0
	uni.navigateTo({
		url: '/pages/extension/customer_list/chat?to_uid=' + item.to_uid + '&type=1'
	})
}
function goDetail(id) {
	uni.navigateTo({
		url: '/pages/users/message_center/messageDetail?id=' + id,
	})
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

			.image {
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
		min-height: 100vh;
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
		transition: all 0.3s;
		justify-content: space-between;

		.read-all {
			margin-right: 24rpx;
			font-size: 24rpx;
		}
	}

	.tabs {
		display: flex;
	}

	.item {
		display: flex;
		align-items: center;
		padding: 4rpx 15rpx;
		margin: 0 20rpx;
		animation: Gradient 0.3s;
		border-radius: 30rpx;
		transition: all 0.4s;
	}

	.btborder {
		color: #fff;
		background-color: var(--view-theme);
		border-radius: 30rpx;
	}

	@-webkit-keyframes Gradient {
		0% {
			background-color: pink;
		}

		50% {
			background-position: 100% 50%
		}

		100% {
			background-position: 0% 50%
		}
	}

	@-moz-keyframes Gradient {
		0% {
			background-position: 0% 50%
		}

		50% {
			background-position: 100% 50%
		}

		100% {
			background-position: 0% 50%
		}
	}

	@keyframes Gradient {
		0% {
			background-position: 0% 50%
		}

		50% {
			background-position: 100% 50%
		}

		100% {
			background-position: 0% 50%
		}
	}
</style>
