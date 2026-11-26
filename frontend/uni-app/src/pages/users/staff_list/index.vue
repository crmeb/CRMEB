<template>
	<view :style="colorStyle">
		<view class="promoter-list">
			<view class="promoterHeader bg-color">
				<view class="headerCon acea-row row-between-wrapper">
					<view>
						<view class="name">
							{{ $t(`员工人数`) }}
							<view class="invitation" @click="openAdd">{{ $t(`添加`) }}</view>
						</view>
						<view>
							<text class="num">{{ teamCount }}</text>
							{{ $t(`人`) }}
						</view>
					</view>
					<view class="iconfont icon-tuandui"></view>
				</view>
			</view>
			<!-- <view class='search acea-row row-between-wrapper'>
				<view class='input'><input :placeholder="$t(`点击搜索会员名称`)" placeholder-class='placeholder' v-model="keyword"
						@confirm="submitForm" confirm-type='search' name="search"></input></view>
				<button class='iconfont icon-sousuo2' @click="submitForm"></button>
			</view> -->
			<view class="list">
				<block v-for="(item, index) in recordList" :key="index">
					<view class="item acea-row row-between-wrapper">
						<view class="picTxt acea-row row-between-wrapper">
							<view class="pictrue">
								<image :src="item.avatar"></image>
							</view>
							<view class="text">
								<view class="name line1">{{ item.nickname }}</view>
								<view>{{ $t(`加入时间`) }}: {{ item.division_change_time }}</view>
								<view>{{ $t(`分佣比例`) }}: {{ item.division_percent }}%</view>
							</view>
						</view>
						<view class="right">
							<view>
								<text class="num font-color">{{ item.childCount ? item.childCount : 0 }}</text>
								{{ $t(`人`) }}
							</view>
							<view>
								<text class="num">{{ item.orderCount ? item.orderCount : 0 }}</text>
								{{ $t(`单`) }}
							</view>
							<view>
								<text class="num">{{ item.numberCount ? item.numberCount : 0 }}</text>
								{{ $t(`元`) }}
							</view>
						</view>
					</view>
					<view class="item-btn">
						<view class="change" @click="changeData(item)">{{ $t(`修改分佣比例`) }}</view>
						<view class="clear" @click="clear(item, index)">{{ $t(`删除`) }}</view>
					</view>
				</block>
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<home></home>
		<view class="refund-input" :class="refund_close ? 'on' : ''">
			<view class="input-msg">
				<text class="iconfont icon-guanbi5" @tap="refund_close = false"></text>
				<view class="refund-input-title">{{ $t(`修改分佣比例`) }}</view>
				<view class="refund-input-sty">
					<input type="number" v-model="agent_percent" :placeholder="$t(`请输入百分比`)" />
				</view>
				<view class="refund-bth">
					<!-- <view class="close-refund" @click="refund_close = false">取消</view> -->
					<view class="submit-refund" @click="refundSubmit()">{{ $t(`提交`) }}</view>
				</view>
			</view>
		</view>
		<view class="mask invoice-mask" v-if="refund_close" @click="refund_close = false"></view>
		<view class="refund-input" :class="addClose ? 'on' : ''">
			<view class="input-msg">
				<text class="iconfont icon-guanbi5" @tap="addClose = false"></text>
				<view class="refund-input-title">{{ $t(`添加员工`) }}</view>
				<view class="refund-input-sty">
					<input v-model="account" :placeholder="$t(`请输入手机号或用户ID`)" />
				</view>
				<view class="refund-input-sty">
					<input type="digit" v-model="addPercent" :placeholder="$t(`请输入佣金比例`)" />
				</view>
				<view class="refund-bth">
					<view class="submit-refund" @click="addSubmit()">{{ $t(`提交`) }}</view>
				</view>
			</view>
		</view>
		<view class="mask invoice-mask" v-if="addClose" @click="addClose = false"></view>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow, onHide, onReachBottom } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import { clerkPeople, setClerkPercent, delClerkPercent, bindStaff } from '@/api/user.js';
import { toLogin } from '@/libs/login.js';
import home from '@/components/home';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
let app = getApp();

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const total = ref(0);
const totalLevel = ref(0);
const agent_percent = ref(null);
const teamCount = ref(0);
const page = ref(1);
const limit = ref(20);
const keyword = ref('');
const sort = ref('');
const grade = ref(0);
const uid = ref(0);
const status = ref(false);
const recordList = ref([]);
const refund_close = ref(false);
const addClose = ref(false);
const account = ref('');
const addPercent = ref('');
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
// 原代码未在 data 声明、直接挂到实例上的字段
const is_show = ref(false);

// ---------- 生命周期 ----------
onLoad(() => {
	if (isLogin.value) {
		userSpreadNewList();
	} else {
		toLogin();
	}
});
onShow(() => {
	if (is_show.value) userSpreadNewList();
	// getUrlCode()
});
onHide(() => {
	is_show.value = true;
});
onReachBottom(() => {
	userSpreadNewList();
});

// ---------- methods ----------
function openAdd() {
	account.value = '';
	addPercent.value = '';
	addClose.value = true;
}
function addSubmit() {
	const target = (account.value || '').trim();
	if (!target) {
		return util.Tips({
			title: i18n.t(`请输入手机号或用户ID`)
		});
	}
	if (addPercent.value === '' || Number(addPercent.value) < 0) {
		return util.Tips({
			title: i18n.t(`请输入佣金比例`)
		});
	}
	bindStaff({
		account: target,
		division_percent: addPercent.value
	})
		.then((res) => {
			util.Tips(
				{
					title: res.msg,
					icon: 'success'
				},
				() => {
					addClose.value = false;
					page.value = 1;
					limit.value = 20;
					status.value = false;
					recordList.value = [];
					userSpreadNewList();
				}
			);
		})
		.catch((err) => {
			util.Tips({
				title: err
			});
		});
}
function onLoadFun(e) {
	userSpreadNewList();
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e;
}
function setSort(sortType) {
	sort.value = sortType;
	page.value = 1;
	limit.value = 20;
	status.value = false;
	recordList.value = [];
	userSpreadNewList();
}
// setKeyword: function(e) {
// 	keyword.value = e.detail.value;
// },
function submitForm() {
	page.value = 1;
	limit.value = 20;
	status.value = false;
	recordList.value = [];
	userSpreadNewList();
}
function clear(data, index) {
	uni.showModal({
		title: i18n.t(`删除员工`),
		content: i18n.t(`确定删除该员工?`),
		success: (res) => {
			if (res.confirm) {
				delClerkPercent(data.uid)
					.then((res) => {
						recordList.value.splice(index, 1);
						// userSpreadNewList();
						teamCount.value -= 1;
						return util.Tips({
							title: i18n.t(`删除成功`),
							icon: 'success'
						});
					})
					.catch((err) => {
						return util.Tips({
							title: err
						});
					});
			} else if (res.cancel) {
				return util.Tips({
					title: i18n.t(`已取消`)
				});
			}
		}
	});
}
function changeData(data) {
	refund_close.value = true;
	uid.value = data.uid;
}
function refundSubmit() {
	if (agent_percent.value < 0) {
		return util.Tips({
			title: i18n.t(`请输入比例`)
		});
	}
	setClerkPercent({
		agent_percent: agent_percent.value,
		uid: uid.value
	})
		.then((res) => {
			util.Tips(
				{
					title: res.msg,
					icon: 'success'
				},
				() => {
					refund_close.value = false;
					page.value = 1;
					limit.value = 20;
					keyword.value = '';
					sort.value = '';
					status.value = false;
					agent_percent.value = null;
					recordList.value = [];
					userSpreadNewList();
				}
			);
		})
		.catch((err) => {
			util.Tips({
				title: err
			});
		});
}
function setType(gradeNum) {
	if (grade.value != gradeNum) {
		grade.value = gradeNum;
		page.value = 1;
		limit.value = 20;
		keyword.value = '';
		sort.value = '';
		status.value = false;
		recordList.value = [];
		userSpreadNewList();
	}
}
function userSpreadNewList() {
	if (status.value == true) return;
	clerkPeople({
		page: page.value,
		limit: limit.value,
		keyword: keyword.value,
		grade: grade.value,
		sort: sort.value
	}).then((res) => {
		let len = res.data.list.length;
		let recordListData = res.data.list;
		let recordListNew = recordList.value.concat(recordListData);
		total.value = res.data.total;
		totalLevel.value = res.data.totalLevel;
		teamCount.value = res.data.count;
		status.value = limit.value > len;
		page.value = page.value + 1;
		recordList.value = recordListNew;
	});
}
</script>

<style scoped lang="scss">
.invoice-mask {
	background-color: #ccc;
	opacity: 0.7;
}

.promoter-list .nav {
	background-color: #fff;
	height: 86rpx;
	line-height: 86rpx;
	font-size: 28rpx;
	color: #282828;
	border-bottom: 1rpx solid #eee;
}

.promoter-list .nav .item.on {
	border-bottom: 5rpx solid #e93323;
	color: #e93323;
}

.promoter-list .search {
	width: 100%;
	background-color: #fff;
	height: 86rpx;
	padding-left: 30rpx;
	box-sizing: border-box;
}

.promoter-list .search .input {
	width: 610rpx;
	height: 60rpx;
	border-radius: 50rpx;
	background-color: #f5f5f5;
	text-align: center;
	position: relative;
}

.promoter-list .name {
	display: flex;

	.invitation {
		background-color: #fff;
		border-radius: 6rpx;
		padding: 0rpx 10rpx;
		margin-left: 20rpx;
		color: #d13d25;
	}
}

.promoter-list .search .input input {
	height: 100%;
	font-size: 26rpx;
	width: 610rpx;
	text-align: center;
}

.promoter-list .search .input .placeholder {
	color: #bbb;
}

.promoter-list .search .input .iconfont {
	position: absolute;
	right: 28rpx;
	color: #999;
	font-size: 28rpx;
	top: 50%;
	transform: translateY(-50%);
}

.promoter-list .search .iconfont {
	font-size: 45rpx;
	color: #515151;
	width: 110rpx;
	height: 60rpx;
	line-height: 60rpx;
}

.promoter-list .list {
	margin-top: 12rpx;
}

.promoter-list .list .sortNav {
	background-color: #fff;
	height: 76rpx;
	border-bottom: 1rpx solid #eee;
	color: #333;
	font-size: 28rpx;
}

.promoter-list .list .sortNav .sortItem {
	text-align: center;
	flex: 1;
}

.promoter-list .list .sortNav .sortItem image {
	width: 24rpx;
	height: 24rpx;
	margin-left: 6rpx;
	vertical-align: -3rpx;
}

.promoter-list .list .item {
	background-color: #fff;
	border-bottom: 1rpx solid #eee;
	height: 152rpx;
	padding: 0 30rpx 0 20rpx;
	font-size: 24rpx;
	color: #666;
}

.item-btn {
	display: flex;
	align-items: center;
	justify-content: flex-end;
	background-color: #fff;
}

.clear,
.change {
	padding: 5rpx 15rpx;
	margin: 10rpx;
	border-radius: 6rpx;
}

.clear {
	background-color: #ccc;
	color: #fff;
}

.change {
	background-color: #de4d36;
	color: #fff;
}

.promoter-list .list .item .picTxt {
	width: 440rpx;
}

.promoter-list .list .item .picTxt .pictrue {
	width: 106rpx;
	height: 106rpx;
	border-radius: 50%;
}

.promoter-list .list .item .picTxt .pictrue image {
	width: 100%;
	height: 100%;
	border-radius: 50%;
	border: 3rpx solid #fff;
	box-shadow: 0 0 10rpx #aaa;
	box-sizing: border-box;
}

.promoter-list .list .item .picTxt .text {
	width: 304rpx;
	font-size: 24rpx;
	color: #666;
}

.promoter-list .list .item .picTxt .text .name {
	font-size: 28rpx;
	color: #333;
	margin-bottom: 13rpx;
}

.promoter-list .list .item .right {
	width: 240rpx;
	text-align: right;
	font-size: 22rpx;
	color: #333;
}

.promoter-list .list .item .right .num {
	margin-right: 7rpx;
}

.refund-input {
	position: fixed;
	bottom: 0;
	left: 0;
	width: 100%;
	border-radius: 16rpx 16rpx 0 0;
	background-color: #fff;
	z-index: 99;
	padding: 40rpx 0 70rpx 0;
	transition: all 0.3s cubic-bezier(0.25, 0.5, 0.5, 0.9);
	transform: translate3d(0, 100%, 0);

	.refund-input-title {
		font-size: 32rpx;
		margin-bottom: 60rpx;
		color: #282828;
	}

	.refund-input-sty {
		border: 1px solid #ddd;
		padding: 20rpx 20rpx;
		border-radius: 40rpx;
		width: 100%;
		margin: 20rpx 65rpx;
	}

	.input-msg {
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		position: relative;
		margin: 0 65rpx;

		.iconfont {
			position: absolute;
			font-size: 32rpx;
			color: #282828;
			top: 8rpx;
			right: -30rpx;
		}
	}

	.refund-bth {
		display: flex;
		margin: 0 65rpx;
		margin-top: 20rpx;
		justify-content: space-around;
		width: 100%;

		.close-refund {
			padding: 24rpx 80rpx;
			border-radius: 80rpx;
			color: #fff;
			background-color: #ccc;
		}

		.submit-refund {
			width: 100%;
			padding: 24rpx 0rpx;
			text-align: center;
			border-radius: 80rpx;
			color: #fff;
			background-color: #f62c2c;
		}
	}
}

.refund-input.on {
	transform: translate3d(0, 0, 0);
}

.poster-pop {
	width: 450rpx;
	height: 450rpx;
	position: fixed;
	padding: 10rpx;
	background-color: #fff;
	left: 50%;
	transform: translateX(-50%);
	z-index: 399;
	top: 50%;
	margin-top: -357rpx;
}

.poster-pop image {
	width: 100%;
	height: 100%;
	display: block;
}

.poster-pop .close {
	width: 46rpx;
	height: 75rpx;
	position: fixed;
	right: 0;
	top: -73rpx;
	display: block;
}

.poster-pop .save-poster {
	background-color: #df2d0a;
	font-size: ：22rpx;
	color: #fff;
	text-align: center;
	height: 76rpx;
	line-height: 76rpx;
	width: 450rpx;
	margin-left: -10rpx;
}

.poster-pop .keep {
	color: #fff;
	text-align: center;
	font-size: 25rpx;
	margin-top: 30rpx;
}
</style>
