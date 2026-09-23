<template>
	<view>
		<view class="detailed">
			<!-- <view class="public_title acea-row row-middle">
				<view class="icons"></view>{{ $t(`经验值明细`) }}
			</view> -->
			<view v-if="expList.length" class="list">
				<view class="item acea-row row-between-wrapper" v-for="(item,index) in expList">
					<view class="text">
						<view class="name">{{$t(item.title)}}</view>
						<view class="data">{{item.add_time}}</view>
					</view>
					<view class="num" v-if="item.pm">+{{item.number}}</view>
					<view class="num on" v-else>-{{item.number}}</view>
				</view>
			</view>
			<view v-if="!expList.length && !loading" class="empty">
				<image class="image" :src="imgHost + '/statics/images/empty-box.png'"></image>
				<view>{{$t(`暂无经验记录`)}}</view>
			</view>
		</view>
		<view class='loadingicon acea-row row-center-wrapper' v-if="expList.length">
			<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
		</view>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onReachBottom } from '@dcloudio/uni-app';
import {
	getUserInfo,
	getlevelInfo,
	getlevelExpList
} from '@/api/user.js';
import { HTTP_REQUEST_URL } from '@/config/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';

// ---------- data ----------
const imgHost = HTTP_REQUEST_URL;
const loading = ref(false);
const loadend = ref(false);
const loadTitle = ref(i18n.t(`加载更多`)); //提示语
const page = ref(1);
const limit = ref(20);
const expList = ref([]);

// ---------- created ----------
getlevelList();

// ---------- methods ----------
function getlevelList() {
	if (loadend.value) return false;
	if (loading.value) return false;
	getlevelExpList({
		page: page.value,
		limit: limit.value
	}).then(res => {
		let list = res.data,
			loadendNew = list.length < limit.value;
		expList.value = util.SplitArray(list, expList.value);
		loadend.value = loadendNew;
		loadTitle.value = loadendNew ? i18n.t(`我也是有底线的`) : i18n.t(`加载更多`);
		page.value = page.value + 1;
		loading.value = false;
	}).catch(err => {
		loading.value = false;
		loadTitle.value = i18n.t(`加载更多`);
	});
}

// ---------- 生命周期 ----------
onReachBottom(() => {
	getlevelList();
});
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
