<template>
	<view :style="colorStyle">
		<view class="promoter-order">
			<view class='promoterHeader bg-color'>
				<view class='headerCon acea-row row-between-wrapper'>
					<view>
						<view class='name'>{{$t(`累计推广订单`)}}</view>
						<view><text class='num'>{{count || 0}}</text>{{$t(`单`)}}</view>
					</view>
					<view class='iconfont icon-2'></view>
				</view>
			</view>
			<view class='list' v-if="recordList.length>0">
				<block v-for="(item,index) in recordList" :key="index">
					<view class='item'>
						<view class='title acea-row row-column row-center'>
							<view class='data'>{{item.time}}</view>
							<view>{{$t(`本月累计推广订单`)}}：{{item.count || 0}}{{$t(`单`)}}</view>
						</view>
						<view class='listn'>
							<block v-for="(child,indexn) in item.child" :key="indexn">
								<view class='itenm'>
									<view class='top acea-row row-between-wrapper'>
										<view class='pictxt acea-row row-between-wrapper'>
											<view class='pictrue'>
												<image :src='child.avatar'></image>
											</view>
											<view class='text line1'>{{child.nickname}}</view>
										</view>
										<view class='money' v-if="child.type == 'brokerage'">{{$t(`返佣`)}}：<text
												class='font-color'>{{$t(`￥`)}}{{child.number}}</text></view>
										<view class='money' v-else>{{$t(`暂未返佣`)}}：<text
												class='font-color'>{{$t(`￥`)}}{{child.number}}</text></view>
									</view>
									<view class='bottom'>
										<view><text class='name'>{{$t(`订单编号`)}}：</text>{{child.order_id}}</view>
										<view><text class='name'>{{$t(`下单时间`)}}：</text>{{child.time}}</view>
										<view class="more" v-if="child.children && child.children.length"
											@click="open(child)">
											{{child.open?$t(`收起`):$t(`更多`)}}
											<text class="iconfont"
												:class="child.open?'icon-xiangshang':'icon-xiangxia'"></text>
										</view>
									</view>
									<view class="more-record" v-if="child.open">
										<view class="more-record-list" v-for="(sp,indexs) in child.children"
											:key="indexs">
											<view class="more-record-box">
												<view><text class='name'>{{$t(`单号`)}}：</text>{{sp.order_id}}</view>
												<view class='money' v-if="sp.type == 'brokerage'">{{$t(`返佣`)}}：<text
														class='font-color'>{{$t(`￥`)}}{{sp.number}}</text></view>
												<view class='money' v-else>{{$t(`暂未返佣`)}}：<text
														class='font-color'>{{$t(`￥`)}}{{sp.number}}</text></view>
											</view>
										</view>
									</view>
								</view>
							</block>
						</view>
					</view>
				</block>
			</view>
			<view v-if="recordList.length == 0">
				<emptyPage :title="$t(`暂无推广订单～`)"></emptyPage>
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onReachBottom } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
	spreadOrder,
	divisionOrder
} from '@/api/user.js';
import {
	toLogin
} from '@/libs/login.js';
import emptyPage from '@/components/emptyPage.vue'
import home from '@/components/home';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import i18n from '@/utils/lang.js';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const page = ref(1);
const limit = ref(8);
const status = ref(false);
const recordList = ref([]);
const times = ref([]);
const recordCount = ref(0);
const count = ref(0);
const orderType = ref(0);
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权

// ---------- 生命周期 ----------
onLoad((options) => {
	if (isLogin.value) {
		orderType.value = options.type || 0
		getRecordOrderList(options.type);
	} else {
		toLogin();
	}
});
onReachBottom(() => {
	getRecordOrderList();
});

// ---------- methods ----------
function open(item) {
	item.open = !item.open
}
function onLoadFun() {
	getRecordOrderList();
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e
}
function getRecordOrderList() {
	if (status.value == true) return;
	let fun
	if (orderType.value) {
		fun = divisionOrder
		uni.setNavigationBarTitle({
			title: i18n.t(`推广订单列表`)
		})
	} else {
		fun = spreadOrder
	}
	fun({
		page: page.value,
		limit: limit.value
	}).then(res => {
		for (let i = 0; i < res.data.time.length; i++) {
			if (!times.value.includes(res.data.time[i].time)) {
				times.value.push(res.data.time[i].time)
				recordList.value.push({
					time: res.data.time[i].time,
					count: res.data.time[i].count,
					child: []
				})
			}
		}
		for (let x = 0; x < times.value.length; x++) {
			for (let j = 0; j < res.data.list.length; j++) {
				if (times.value[x] === res.data.list[j].time_key) {
					res.data.list[j].open = false
					recordList.value[x].child.push(res.data.list[j])
				}
			}
		}
		count.value = res.data.count || 0;
		status.value = res.data.list.length < 8;
		page.value += 1;
	});
}
</script>

<style scoped lang="scss">
	.promoter-order .list .item .title {
		height: 133rpx;
		padding: 0 30rpx;
		font-size: 26rpx;
		color: #999;
	}

	.promoter-order .list .item .title .data {
		font-size: 28rpx;
		color: #282828;
		margin-bottom: 5rpx;
	}

	.promoter-order .list .item .listn .itenm {
		background-color: #fff;
		// margin: 0 $uni-index-margin-row;
		border-radius: 8rpx;

		.more-record {
			color: #999;
			font-size: 24rpx;

			.more-record-list {
				padding: 20rpx 30rpx;
				border-top: 1px solid #f2f2f2;

				.more-record-box {
					display: flex;
					justify-content: space-between;
				}
			}
		}
	}

	.promoter-order .list .item .listn .itenm~.itenm {
		margin-top: 12rpx;
	}

	.promoter-order .list .item .listn .itenm .top {
		margin-left: 30rpx;
		padding-right: 30rpx;
		border-bottom: 1rpx solid #eee;
		height: 100rpx;
	}

	.promoter-order .list .item .listn .itenm .top .pictxt {
		width: 320rpx;
	}

	.promoter-order .list .item .listn .itenm .top .pictxt .text {
		width: 230rpx;
		font-size: 30rpx;
		color: #282828;
	}

	.promoter-order .list .item .listn .itenm .top .pictxt .pictrue {
		width: 66rpx;
		height: 66rpx;
	}

	.promoter-order .list .item .listn .itenm .top .pictxt .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
		border: 3rpx solid #fff;
		box-sizing: border-box;
		box-shadow: 0 0 15rpx #aaa;
	}

	.promoter-order .list .item .listn .itenm .top .money {
		font-size: 28rpx;
	}

	.promoter-order .list .item .listn .itenm .bottom {
		padding: 20rpx 30rpx;
		font-size: 28rpx;
		color: #666;
		line-height: 1.6;
		position: relative;

		.more {
			font-size: 24rpx;
			position: absolute;
			right: 12rpx;
			bottom: 24rpx;

			.iconfont {
				font-size: 22rpx;
				margin-left: 5rpx;
			}
		}
	}

	.promoter-order .list .item .listn .itenm .bottom .name {
		color: #999;
	}
</style>
