<template>
	<view :style="colorStyle">
		<view class="commission-details">
			<view class="promoterHeader bg-color">
				<view class="headerCon acea-row row-between-wrapper">
					<view>
						<view class="name">{{ $t(name) }}</view>
						<view class="money" v-if="recordType == 4">
							{{ $t(`￥`) }}
							<text class="num">{{ extractCount }}</text>
						</view>
						<view class="money" v-else>
							{{ $t(`￥`) }}
							<text class="num">{{ recordCount }}</text>
						</view>
					</view>
					<view class="iconfont icon-jinbi1"></view>
				</view>
			</view>
			<view class="sign-record">
				<block v-if="recordList.length > 0">
					<view class="list" v-for="(item, index) in recordList" :key="index">
						<view class="item">
							<view class="data">{{ item.time }}</view>
							<view class="listn">
								<block v-for="(child, indexn) in item.child" :key="indexn">
									<view class="itemn acea-row row-between-wrapper">
										<view class="title">
											<view class="name acea-row line1">
												<view
													v-if="child.pm != 1 && child.type == 'extract'"
													class="extract-tag"
													:class="{ 'bg-success': child.extract_status == 1, 'bg-ing': child.extract_status == 0, 'bg-fail': child.extract_status == -1 }"
												>
													{{ child.extract_status == 1 ? $t(`成功`) : child.extract_status == 0 ? $t(`提现中`) : $t(`失败`) }}
												</view>
												<view v-if="type == 2 && child.is_frozen == 1" class="extract-tag bg-ing">{{ $t(`冻结中`) }}</view>
												{{ type == 1 ? $t(child.mark) : $t(child.title) }}
											</view>
											<view>{{ child.add_time }}</view>
											<view class="fail-msg" v-if="child.fail_msg">{{ $t(`原因`) }}：{{ child.fail_msg }}</view>
											<view class="fail-msg" v-if="type == 2 && child.is_frozen == 1">{{ $t(`佣金冻结中，解冻时间：`) }}{{ child.frozen_time }}</view>
										</view>
										<view class="acea-row row-column items-end">
											<view class="num font-color" v-if="child.pm == 1">+{{ child.number }}</view>
											<view class="num flex-y-center" v-else>-{{ child.number }}</view>
											<template v-if="child.state == 'WAIT_USER_CONFIRM'">
												<view
													class="w-100 h-48 rd-30rpx flex-center mt-16 bg-color fs-26 text--w111-fff"
													@tap="jumpPath(routes.withdrawalReceiving + '?type=1&id=' + child.wechat_order_id)"
												>
													{{ $t(`收款`) }}
												</view>
											</template>
										</view>
									</view>
								</block>
							</view>
						</view>
					</view>
				</block>
				<view class="loadingicon acea-row row-center-wrapper" v-if="recordList.length">
					<text class="loading iconfont icon-jiazai" :hidden="loading == false"></text>
					{{ loadTitle }}
				</view>
				<view v-if="recordList.length < 1 && page > 1">
					<emptyPage :title="$t(`暂无数据~`)"></emptyPage>
				</view>
			</view>
		</view>
		<!-- #ifdef H5 -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow, onReachBottom } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import { getCommissionInfo, getSpreadInfo } from '@/api/user.js';
import routes from '@/dynamic-pages/api/routes';
import { toLogin } from '@/libs/login.js';
import emptyPage from '@/components/emptyPage.vue';
import home from '@/components/home';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import i18n from '@/utils/lang.js';
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const name = ref('');
const type = ref(0);
const page = ref(1);
const limit = ref(15);
const loading = ref(false);
const loadend = ref(false);
const loadTitle = ref(i18n.t(`加载更多`));
const recordList = ref([]);
const recordType = ref(0);
const recordCount = ref(0);
const extractCount = ref(0);
const times = ref([]);
const isWeixin = ref(false);

// ---------- 生命周期 ----------
onLoad((options) => {
	if (isLogin.value) {
		type.value = options.type;
	} else {
		toLogin();
	}
});
onShow(() => {
	//#ifdef H5
	isWeixin.value = Auth.isWeixin();
	//#endif
	let typeVal = type.value;
	loadend.value = false;
	page.value = 1;
	times.value = [];
	recordList.value = [];
	if (typeVal == 1) {
		uni.setNavigationBarTitle({
			title: i18n.t(`提现记录`)
		});
		name.value = i18n.t(`提现总额`);
		recordType.value = 4;
		getRecordList();
		getRecordListCount();
	} else if (typeVal == 2) {
		uni.setNavigationBarTitle({
			title: i18n.t(`佣金记录`)
		});
		name.value = i18n.t(`佣金明细`);
		recordType.value = 3;
		getRecordList();
		getRecordListCount();
	} else {
		uni.showToast({
			title: i18n.t(`参数错误`),
			icon: 'none',
			duration: 1000,
			mask: true,
			success: function (res) {
				setTimeout(function () {
					// #ifndef H5
					uni.navigateBack({
						delta: 1
					});
					// #endif
					// #ifdef H5
					history.back();
					// #endif
				}, 1200);
			}
		});
	}
});
onReachBottom(() => {
	getRecordList();
});

// ---------- methods ----------
function jumpPath(url) {
	uni.navigateTo({
		url
	});
}
function getRecordList() {
	if (loading.value) return;
	if (loadend.value) return;
	loading.value = true;
	loadTitle.value = '';
	getCommissionInfo(
		{
			page: page.value,
			limit: limit.value
		},
		recordType.value
	)
		.then((res) => {
			for (let i = 0; i < res.data.time.length; i++) {
				if (!times.value.includes(res.data.time[i])) {
					times.value.push(res.data.time[i]);
					recordList.value.push({
						time: res.data.time[i],
						child: []
					});
				}
			}
			for (let x = 0; x < times.value.length; x++) {
				for (let j = 0; j < res.data.list.length; j++) {
					if (times.value[x] === res.data.list[j].time_key) {
						recordList.value[x].child.push(res.data.list[j]);
					}
				}
			}
			let loadendNew = res.data.list.length < limit.value;
			loadend.value = loadendNew;
			loadTitle.value = loadendNew ? i18n.t(`我也是有底线的`) : i18n.t(`加载更多`);
			page.value += 1;
			loading.value = false;
		})
		.catch((err) => {
			loading.value = false;
			loadTitle.value = i18n.t(`加载更多`);
		});
}
function getRecordListCount() {
	getSpreadInfo().then((res) => {
		recordCount.value = res.data.commissionCount;
		extractCount.value = res.data.extractCount;
	});
}
</script>

<style scoped lang="scss">
.commission-details .promoterHeader .headerCon .money {
	font-size: 36rpx;
}

.commission-details .promoterHeader .headerCon .money .num {
	font-family: 'Guildford Pro';
}
.extract-tag {
	font-size: 24rpx;
	border-radius: 12rpx;
	padding: 4rpx 8rpx;
	background-color: #16ac57;
	color: #fff;
	margin-right: 8rpx;
	&.bg-ing {
		background-color: #ffd600;
		color: #000;
	}
	&.bg-fail {
		background-color: #ff4d4f;
		color: #fff;
	}
}
.sign-record .list .item .listn .itemn .name {
	width: 100%;
	// max-width: 100%;
	white-space: break-spaces;
}
.sign-record .list .item .listn .itemn .title {
	padding-right: 30rpx;
	flex: 1;
}
</style>
