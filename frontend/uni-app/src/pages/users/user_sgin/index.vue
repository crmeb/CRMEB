<template>
	<view :style="`${colorStyle || ''};${assetStyle}`">
		<view class="sign" :style="colorStyle">
			<view class="header bgcolor" :style="'background-image: url(' + sginBg + ');'">
				<view class="headerCon acea-row row-between-wrapper">
					<view class="left acea-row row-between-wrapper">
						<image class="sign-heading-image" :src="sginTip" mode="aspectFit" />
					</view>
					<navigator :render-link="false" class="right acea-row row-middle" hover-class="none" url="/pages/users/user_sgin_list/index">
						<view>{{ $t(`明细`) }}</view>
					</navigator>
				</view>
			</view>
			<view class="wrapper">
				<view class="sgin-num">
					<view class="text">
						<text>{{ $t(`已连续签到`) }}</text>
						<text class="num">{{ continuousSignDays }}</text>
						<text>{{ $t(`天`) }}</text>
					</view>
					<view class="tip" v-if="signRemindSwitch == 1">
						<text class="mr16">{{ $t(`签到提醒`) }}</text>
						<switch :checked="remindStatus" :disabled="loading || submitting || remindUpdating" color="#FFCC33" @change="changeRemind" />
					</view>
				</view>
				<view class="list acea-row row-between-wrapper">
					<template v-if="signMode == 0 || signMode == -1">
						<view class="mot-item" v-for="(item, index) in signSystemList" :key="index">
							<view class="row" :class="{ 'sgin-day': e.sign_day, 'last-day': e.is_sign }" v-for="(e, i) in item"
								:key="i">
								<view class="type-img">
									<image class="sign-reward-image" v-if="!e.is_sign" :src="getTypeImg(e.type, e.is_sign)" mode="aspectFit" />
									<text v-else class="iconfont icon-xuanzhong1"></text>
								</view>
								<view class="venus">{{ e.day }}</view>
							</view>
						</view>
					</template>
					<template v-else>
						<view class="mot-item" v-for="(item, index) in signSystemList" :key="index">
							<view class="mot-item-box" :class="{ 'sgin-day': e.sign_day, 'last-day': e.is_sign }"
								v-for="(e, i) in item" :key="i">
								<view class="row">
									<view class="num">+{{ e.point }}</view>
									<view class="type-img">
										<image class="sign-reward-image" v-if="!e.is_sign" :src="getTypeImg(e.type, e.is_sign)" mode="aspectFit" />
										<text v-else class="iconfont icon-xuanzhong1"></text>
									</view>
								</view>
								<view class="text">{{$t(weekArr[i])}}</view>
							</view>
						</view>
					</template>
				</view>
					<button class="but bg-color on" v-if="checkSign && configReady" disabled>{{ $t(`今日已签到，明日再来吧`) }}</button>
					<button class="but bg-color" v-else-if="loadError" :loading="loading" :disabled="loading" @click="refreshSignData">{{ $t(`重新加载`) }}</button>
					<form @submit="goSign" v-else>
						<button class="but bg-color" formType="submit" :loading="loading || submitting" :disabled="loading || submitting || !configReady || !signEnabled">{{ $t(`立即签到`) }}</button>
					</form>
					<view class="tip" v-if="loadError">{{ loadError }}</view>
				<view class="tip" v-if="nextContinuousDays > 0">
					<image class="sign-gift-image" :src="`${imgHost}/statics/images/sgin_icon_4.png`" mode="aspectFit" />
					{{ $t(`再连续签到`) }}{{ nextContinuousDays }}{{ $t(`天，可额外获得惊喜礼包`) }}
				</view>
				<view class="lock"></view>
			</view>
			<view class="wrapper wrapper2">
				<view class="tip">{{ $t(`已累计签到`) }}</view>
				<view class="list2 acea-row row-center row-bottom">
					<view class="item">{{ signCount[0] || 0 }}</view>
					<view class="item">{{ signCount[1] || 0 }}</view>
					<view class="item">{{ signCount[2] || 0 }}</view>
					<view class="item">{{ signCount[3] || 0 }}</view>
					<view class="data">{{ $t(`天`) }}</view>
				</view>
				<view class="tip2" v-if="nextCumulativeDays > 0">
					<image class="sign-gift-image" :src="`${imgHost}/statics/images/sgin_icon_4.png`" mode="aspectFit" />
					{{ $t(`再累计签到`) }}{{ nextCumulativeDays }}{{ $t(`天，可额外获得惊喜礼包`) }}
				</view>
				<view class="list3" v-if="signList.length">
					<view class="item acea-row" v-for="(item, index) in signList" :key="index">
						<view>
							<view class="name line1">{{ $t(item.title) }}</view>
							<view class="data">{{ item.add_time }}</view>
						</view>
						<view class="num">+{{ item.number }}</view>
					</view>
					<view class="loading" @click="goSignList" v-if="signList.length >= 8">
						{{ $t(`点击加载更多`) }}
						<text class="iconfont icon-xiangyou"></text>
					</view>
				</view>
			</view>
			<view class="signTip acea-row row-center-wrapper" :class="active == true ? 'on' : ''">
				<view class="signTipLight loadingpic"></view>
				<view class="signTipCon">
					<view class="signHeight">
						<image class="sign-success-image" src="../static/signH.png" mode="aspectFit"></image>
					</view>
					<view class="state">{{ $t(`签到成功`) }}</view>
					<view class="integral">{{ $t(`获得`) }}{{ integral }}{{ $t(`积分`) }}</view>
					<view class="signTipBnt" @click="close">{{ $t(`好的`) }}</view>
				</view>
			</view>
			<view class="mask" @touchmove.stop.prevent="false" :hidden="active == false"></view>
		</view>
	</view>
</template>

<script setup>
import { ref, watch } from 'vue';
import { onLoad, onShow, onHide, onUnload } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
	toLogin
} from '@/libs/login.js';
import {
	getSignConfig,
	getSignList as getSignListApi,
	setSignIntegral,
	changeRemindStatus
} from '@/api/user.js';
import {
	colorChange
} from '@/api/api.js';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
import {
	HTTP_REQUEST_URL
} from '@/config/app';

const { colorStyle } = useColorTheme();
const assetStyle = `--ui-sign-lock:url(${HTTP_REQUEST_URL}/statics/images/sign-lock.png);--ui-sign-num:url(${HTTP_REQUEST_URL}/statics/images/sign-num.png);--ui-sign-tip-light:url(${HTTP_REQUEST_URL}/statics/images/sign-tip-light.png);--ui-sign-tip-con:url(${HTTP_REQUEST_URL}/statics/images/sign-tip-con.png);`;
const appStore = useAppStore();
const { isLogin, uid } = storeToRefs(appStore);

// ---------- data ----------
const active = ref(false);
const signCount = ref([]);
const signSystemList = ref([]);
const signList = ref([]);
const integral = ref(0);
const imgHost = HTTP_REQUEST_URL;
const sginBg = ref('');
const sginTip = ref('');
const signMode = ref(0); // 0月签到 1周签到
const nextContinuousDays = ref(0);
const nextCumulativeDays = ref(0);
const continuousSignDays = ref(0);
const signRemindSwitch = ref(0);
const checkSign = ref(0);
const remindStatus = ref(false);
const loading = ref(false);
const submitting = ref(false);
const remindUpdating = ref(false);
const configReady = ref(false);
const signEnabled = ref(false);
const loadError = ref('');
const weekArr = ['周一', '周二', '周三', '周四', '周五', '周六', '周日'];
let pageVisible = false;
let accountVersion = 0;
let remindVersion = 0;
let requestVersion = 0;
let refreshPromise = null;
let refreshDate = '';
let loadedDate = '';
let dailyTimer;

// ---------- watch ----------
watch([isLogin, uid], () => {
	resetSignState();
	if (pageVisible && isLogin.value) {
		refreshSignData();
		scheduleDailyRefresh();
	} else {
		clearTimeout(dailyTimer);
	}
});

// ---------- 生命周期 ----------
onLoad(getColor);

onShow(() => {
	pageVisible = true;
	if (isLogin.value) {
		refreshSignData();
		scheduleDailyRefresh();
	} else {
		toLogin();
	}
});

onHide(() => {
	pageVisible = false;
	clearTimeout(dailyTimer);
});

onUnload(() => {
	pageVisible = false;
	clearTimeout(dailyTimer);
	resetSignState();
});

// ---------- methods ----------
function errorText(error) {
	return typeof error === 'string' ? error : error?.msg || error?.message || error?.errMsg || i18n.t(`请求失败`);
}

function getDayKey() {
	const now = new Date();
	return `${now.getFullYear()}-${now.getMonth()}-${now.getDate()}`;
}

function scheduleDailyRefresh() {
	clearTimeout(dailyTimer);
	const now = new Date();
	const midnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
	dailyTimer = setTimeout(() => {
		if (pageVisible && isLogin.value) {
			refreshSignData();
			scheduleDailyRefresh();
		}
	}, midnight.getTime() - now.getTime() + 100);
}

function resetSignState() {
	accountVersion++;
	remindVersion++;
	requestVersion++;
	refreshPromise = null;
	loadedDate = '';
	active.value = false;
	loading.value = false;
	submitting.value = false;
	remindUpdating.value = false;
	configReady.value = false;
	signEnabled.value = false;
	checkSign.value = false;
	remindStatus.value = false;
	signRemindSwitch.value = 0;
	continuousSignDays.value = 0;
	nextContinuousDays.value = 0;
	nextCumulativeDays.value = 0;
	signCount.value = [];
	signSystemList.value = [];
	signList.value = [];
	loadError.value = '';
}

function getColor() {
	return colorChange('color_change').then((res) => {
		sginBg.value = `${imgHost}/statics/images/sgin_bg_${res.data.status}.png`;
		sginTip.value = `${imgHost}/statics/images/sgin_tip_${res.data.status}.png`;
		let theme = ['#1db0fc', '#42CA4D', '#e93323', '#ff448f', '#FE5C2D'];
		uni.setNavigationBarColor({
			frontColor: '#ffffff', // 必写项
			backgroundColor: theme[res.data.status - 1] // 必写项
		});
	}).catch((error) => util.Tips({ title: errorText(error) }));
}
/**
 * 获取签到配置
 */
function refreshSignData(force = false) {
	if (!isLogin.value) return Promise.resolve();
	const date = getDayKey();
	if (force !== true && refreshPromise && refreshDate === date) return refreshPromise;
	const version = ++requestVersion;
	const reminderVersion = remindVersion;
	refreshDate = date;
	loading.value = true;
	configReady.value = false;
	loadError.value = '';
	if (loadedDate !== date) {
		checkSign.value = false;
		active.value = false;
	}
	refreshPromise = Promise.allSettled([
		getSignConfig(),
		getSignListApi({ page: 1, limit: 8 })
	]).then(([config, history]) => {
		if (version !== requestVersion || date !== getDayKey()) return;
		if (config.status === 'rejected') {
			loadError.value = errorText(config.reason);
			return;
		}
		const data = config.value.data;
		signEnabled.value = Number(data.signStatus) === 1;
		if (!signEnabled.value) {
			loadError.value = i18n.t(`签到功能已关闭`);
			return util.Tips({ title: loadError.value }, { tab: 3 });
		}
		signSystemList.value = Array.isArray(data.signList) ? data.signList : [];
		signMode.value = data.signMode;
		nextContinuousDays.value = Number(data.nextContinuousDays) || 0;
		nextCumulativeDays.value = Number(data.nextCumulativeDays) || 0;
		continuousSignDays.value = Number(data.continuousSignDays) || 0;
		signRemindSwitch.value = Number(data.signRemindSwitch) || 0;
		checkSign.value = !!Number(data.checkSign);
		if (!remindUpdating.value && reminderVersion === remindVersion) remindStatus.value = !!Number(data.signRemindStatus);
		signCount.value = PrefixInteger(Number(data.cumulativeSignDays) || 0, 4);
		loadedDate = date;
		configReady.value = true;
		if (history.status === 'fulfilled') {
			signList.value = Array.isArray(history.value.data) ? history.value.data : [];
		} else {
			util.Tips({ title: errorText(history.reason) });
		}
	}).catch((error) => {
		if (version === requestVersion) loadError.value = errorText(error);
	}).finally(() => {
		if (version === requestVersion) {
			loading.value = false;
			refreshPromise = null;
		}
	});
	return refreshPromise;
}

async function changeRemind(e) {
	if (remindUpdating.value || loading.value || submitting.value || !configReady.value) return;
	const previous = remindStatus.value;
	const status = e.detail.value ? 1 : 0;
	const version = accountVersion;
	remindVersion++;
	remindStatus.value = !!status;
	remindUpdating.value = true;
	try {
		await changeRemindStatus(status);
	} catch (error) {
		if (version === accountVersion) {
			remindStatus.value = previous;
			util.Tips({ title: errorText(error) });
		}
	} finally {
		if (version === accountVersion) {
			remindVersion++;
			remindUpdating.value = false;
		}
	}
}
function getTypeImg(type, isSgin) {
	let src;
	if (isSgin) {
		src = `${imgHost}/statics/images/sgin_suc_1.png`;
		return src;
	}
	switch (type) {
		case 1:
			src = `${imgHost}/statics/images/sgin_icon_1.png`;
			break;
		case 2:
			src = `${imgHost}/statics/images/sgin_icon_2.png`;
			break;
		case 3:
			src = `${imgHost}/statics/images/sgin_icon_3.png`;
			break;
		case 4:
			src = `${imgHost}/statics/images/sgin_icon_3.png`;
			break;
	}
	return src;
}
/**
 * 去签到记录页面
 *
 */
function goSignList() {
	return util.Tips('/pages/users/user_sgin_list/index');
}
/**
 * 数字分割为数组
 * @param int num 需要分割的数字
 * @param int length 需要分割为n位数组
 */
function PrefixInteger(num, length) {
	return (Array(length).join('0') + num).slice(-length).split('');
}

/**
 * 用户签到
 */
async function goSign() {
	if (!isLogin.value) return toLogin();
	if (submitting.value || loading.value) return;
	if (loadedDate !== getDayKey()) await refreshSignData();
	if (submitting.value || loading.value || !configReady.value || !signEnabled.value) return;
	if (checkSign.value) return util.Tips({ title: i18n.t(`您今日已签到!`) });
	const version = accountVersion;
	submitting.value = true;
	try {
		const res = await setSignIntegral();
		if (version !== accountVersion) return;
		checkSign.value = true;
		active.value = true;
		integral.value = Number(res.data.integral) || 0;
		uni.$emit('signInRefresh', { signed: true });
		await refreshSignData(true);
	} catch (error) {
		if (version === accountVersion) util.Tips({ title: errorText(error) });
	} finally {
		if (version === accountVersion) submitting.value = false;
	}
}
/**
 * 关闭签到提示
 */
function close() {
	active.value = false;
}
</script>

<style scoped lang="scss">
	.bgcolor {
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 750rpx;
		height: 438rpx;
	}

	.sign {
		padding-bottom: 40rpx;
	}

	.sign .header {
		width: 100%;
	}

	.sign .header .headerCon {
		padding: 0 0 0 30rpx;
		height: 234rpx;
	}

	.sign .header .headerCon .left {
		width: 530rpx;
		font-size: 32rpx;
		color: #fff;
		font-weight: bold;

		.sign-heading-image {
			flex-shrink: 0;
			width: 388rpx;
			height: 128rpx;
			margin-top: 56rpx;
		}
	}

	.sign .header .headerCon .left .integral text {
		font-size: 24rpx;
		margin-top: 19rpx;
		background-color: #ff9000;
		text-align: center;
		border-radius: 6rpx;
		font-weight: normal;
		padding: 4rpx 15rpx;
	}

	.sign .header .headerCon .text {
		width: 410rpx;
	}

	.sign .header .headerCon .left .pictrue {
		width: 100rpx;
		height: 100rpx;
		border-radius: 50%;
		border: 4rpx solid #ecddbc;
	}

	.sign .header .headerCon .left .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
	}

	.sign .header .headerCon .right {
		// width: 142rpx;
		padding: 6rpx 12rpx 6rpx 16rpx;
		border-radius: 50rpx 0 0 50rpx;
		font-size: 24rpx;
		color: #fff;
		background: rgba(255, 255, 255, 0.2);
	}

	.sign .header .headerCon .right .iconfont {
		font-size: 33rpx;
		padding: 0 10rpx 0 30rpx;
		margin-top: 5rpx;
	}

	.sign .wrapper {
		background-color: #fff;
		margin: -188rpx 20rpx 0 20rpx;
		border-radius: 15rpx;
		padding: 32rpx 32rpx 58rpx 32rpx;
		position: relative;

		.tip {
			display: flex;
			justify-content: center;
			align-items: center;
			font-size: 24rpx;
			font-weight: 400;
			color: #999999;
			line-height: 34rpx;
			margin-top: 20rpx;

			.sign-gift-image {
				flex-shrink: 0;
				width: 26rpx;
				height: 26rpx;
				margin-right: 10rpx;
				margin-bottom: 1rpx;
			}
		}

		.sgin-num {
			display: flex;
			justify-content: space-between;
			align-items: center;
			font-size: 32rpx;
			font-weight: 500;
			color: #333333;
			line-height: 44rpx;
			margin-bottom: 38rpx;

			.num {
				font-size: 40rpx;
				font-family: D-DIN-PRO-SemiBold, D-DIN-PRO;
				font-weight: 600;
				color: #fe5c2d;
				line-height: 40rpx;
				margin: 0 4rpx;
			}

			.tip {
				display: flex;
				align-items: center;
				font-size: 24rpx;
				font-family: PingFang SC-Regular, PingFang SC;
				font-weight: 400;
				color: #333333;
				line-height: 34rpx;

				.mr16 {
					margin-right: 16rpx;
				}
			}
		}

:deep(uni-switch .uni-switch-input:after){
			width: 32rpx;
			height: 32rpx;
			background-color: #fff;
			margin: 4rpx 0 4rpx 4rpx;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
		}

:deep(uni-switch .uni-switch-input:before){
			width: 72rpx;
			height: 40rpx;
			background-color: #eeeeee;
		}

:deep(uni-switch .uni-switch-input){
			width: 76rpx;
			height: 44rpx;
			background-color: #eeeeee;
		}

:deep(uni-switch .uni-switch-input.uni-switch-input-checked:after){
			transform: translateX(32rpx);
		}

:deep(.uni-switch-input-checked){
			background-color: var(--view-theme) !important;
			border-color: var(--view-theme) !important;
		}

:deep(.wx-switch-input){
			width: 80rpx !important;
			height: 44rpx !important;
			background-color: #eeeeee;
		}

		/*白色样式（false的样式）*/
:deep(.wx-switch-input::before){
			width: 76rpx !important;
			height: 40rpx !important;
			background-color: #eeeeee;
		}

		/*绿色样式（true的样式）*/
:deep(.wx-switch-input::after){
			width: 32rpx !important;
			height: 32rpx !important;
			margin: 4rpx 4rpx 4rpx 4rpx;
		}

:deep(.wx-switch-input-checked){
			background-color: var(--view-theme) !important;
			border-color: var(--view-theme) !important;
		}
	}

	.sign .wrapper .list {
		.mot-item {
			display: flex;
			margin-bottom: 20rpx;
			width: 100%;

			// justify-content: space-between;
			.row.sgin-day {
				border: 2rpx solid var(--view-op-point-eight);
			}

			.mot-item-box.sgin-day .row {
				border: 2rpx solid var(--view-op-point-eight);
				color: var(--view-theme);
				background: var(--view-minorColorT);
			}

			.mot-item-box.last-day .row {
				color: var(--view-theme);
				background: var(--view-minorColorT);
			}

			.row.last-day {
				color: var(--view-theme);
				background: var(--view-minorColorT);
			}

			.mot-item-box.last-day .num {
				color: var(--view-theme);
			}

			.row:last-child {
				margin-right: 0;
			}
		}

		.mot-item-box.sgin-day .num {
			color: var(--view-theme);
		}

		.mot-item-box {
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			align-items: center;

			.text {
				font-size: 24rpx;
				font-weight: 400;
				color: #999999;
				line-height: 34rpx;
				padding-right: 14rpx;
				margin-top: 8rpx;
			}

			.num {
				font-size: 26rpx;
				font-weight: 600;
				color: #666666;
				line-height: 24rpx;
				margin-bottom: 16rpx;
			}

			.icon-xuanzhong1 {
				font-size: 40rpx;
				color: var(--view-theme);
			}
		}

		.mot-item-box:last-child {
			.row {
				margin-right: 0;
			}
		}

		.row {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			height: 110rpx;
			background: #f5f5f5;
			border-radius: 16rpx;
			width: 80rpx;
			margin-right: 14rpx;

			.type-img {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 40rpx;
				height: 40rpx;

				.sign-reward-image {
					flex-shrink: 0;
					width: 100%;
					height: 100%;
				}

				.icon-xuanzhong1 {
					font-size: 40rpx;
					color: var(--view-theme);
				}
			}

			.venus {
				font-size: 24rpx;
				font-weight: 400;
				color: #999999;
				line-height: 34rpx;
				margin-top: 14rpx;
			}
		}
	}

	.sign .wrapper .list .item {
		font-size: 22rpx;
		color: #8a8886;
		text-align: center;
	}

	.sign .wrapper .list .item .rewardTxt {
		width: 74rpx;
		height: 32rpx;
		background-color: #f4b409;
		border-radius: 16rpx;
		font-size: 20rpx;
		color: #a57d3f;
		line-height: 32rpx;
	}

	.sign .wrapper .list .item .num {
		font-size: 30rpx;
		color: #999;
	}

	.sign .wrapper .list .item .num.on {
		color: #ff9000;
	}

	.sign .wrapper .list .item .venus {
		margin: 10rpx auto;

		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 56rpx;
		height: 56rpx;
	}

	.sign .wrapper .list .item .venus.venusSelect {}

	.sign .wrapper .list .item .venus.reward {
		width: 75rpx;
		height: 56rpx;
	}

	.sign .wrapper .but {
		width: 564rpx;
		height: 88rpx;
		font-size: 30rpx;
		line-height: 88rpx;
		color: #fff;
		border-radius: 50rpx;
		text-align: center;
		margin: 30rpx auto 0 auto;
	}

	.sign .wrapper .but.bg-color {
		background-color: var(--view-theme);
	}

	.sign .wrapper .but.on {
		background-color: var(--view-minorColor) !important;
	}

	.sign .wrapper .lock {
		background-image: var(--ui-sign-lock);
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 558rpx;
		height: 68rpx;
		position: absolute;
		left: 50%;
		transform: translateX(-50%);
		bottom: -42rpx;
		z-index: 9;
	}

	.sign .wrapper2 {
		margin-top: 15rpx;
		padding: 73rpx 0 56rpx 0;
	}

	.sign .wrapper2 .tip {
		font-size: 30rpx;
		color: #666;
		text-align: center;
	}

	.sign .wrapper2 .list2 {
		margin: 45rpx 0 26rpx 0;
	}

	.sign .wrapper2 .list2 .item {
		border-radius: 10rpx;
		background-color: var(--view-theme);
		width: 80rpx;
		height: 116rpx;
		background-repeat: no-repeat;
		background-size: 100% 100%;
		color: #fff;
		font-size: 72rpx;
		text-align: center;
		line-height: 116rpx;
		margin-right: 19rpx;
		background-image: var(--ui-sign-num);
	}

	.sign .wrapper2 .list2 .data {
		font-size: 30rpx;
		color: #232323;
	}

	.sign .wrapper2 .tip2 {
		padding: 0 55rpx;
		text-align: center;
		font-size: 24rpx;
		font-weight: 400;
		color: #999999;
		line-height: 34rpx;
		display: flex;
		align-items: center;
		justify-content: center;

		.sign-gift-image {
			flex-shrink: 0;
			width: 26rpx;
			height: 26rpx;
			margin-right: 10rpx;
			margin-bottom: 1rpx;
		}
	}

	.sign .list3 {
		margin: 0rpx 37rpx 0 37rpx;
		border-top: 1px dashed #d8d8d8;
	}

	.sign .list3 .item {
		align-items: center;
		justify-content: space-between;
		// border-bottom: 1px solid #eee;
		height: 130rpx;
	}

	.sign .list3 .item .name {
		color: #232323;
		font-size: 30rpx;
		width: 400rpx;
	}

	.sign .list3 .item .data {
		font-size: 24rpx;
		color: #bbbbbb;
		margin-top: 9rpx;
	}

	.sign .list3 .item .num {
		font-size: 36rpx;
		font-family: 'Guildford Pro';
		color: var(--view-theme);
	}

	.sign .list3 .loading {
		font-size: 26rpx;
		color: #282828;
		height: 97rpx;
		line-height: 97rpx;
		text-align: center;
	}

	.sign .list3 .loading .iconfont {
		font-size: 25rpx;
		color: #212121;
		vertical-align: 2rpx;
		margin-left: 10rpx;
	}

	.sign .signTip {
		width: 644rpx;
		height: 645rpx;
		position: fixed;
		top: 50%;
		left: 50%;
		margin-left: -322rpx;
		margin-top: -322.5rpx;
		z-index: 99;
		text-align: center;
		transition: all 0.3s ease-in-out 0s;
		opacity: 0;
		transform: scale(0);
	}

	.sign .signTip .signTipLight {
		background-image: var(--ui-sign-tip-light);
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 100%;
		height: 100%;
	}

	.sign .signTip.on {
		opacity: 1;
		transform: scale(1);
	}

	.sign .signTip .signTipCon {
		background-image: var(--ui-sign-tip-con);
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 420rpx;
		height: 410rpx;
		margin-top: -642rpx;
		position: relative;
		background-color: var(--view-theme);
		border-radius: 10rpx;
	}

	.sign .signTip .signTipCon .signHeight {
		width: 134rpx;
		height: 120rpx;
		position: absolute;
		left: 50%;
		margin-left: -67rpx;
		top: -60rpx;
	}

	.sign .signTip .signTipCon .signHeight .sign-success-image {
		width: 100%;
		height: 100%;
	}

	.sign .signTip .signTipCon .state {
		font-size: 34rpx;
		color: #fff;
		margin-top: 150rpx;
	}

	.sign .signTip .signTipCon .integral {
		font-size: 30rpx;
		color: rgba(255, 255, 255, 0.6);
		margin-top: 9rpx;
	}

	.sign .signTip .signTipCon .signTipBnt {
		font-size: 30rpx;
		color: #fff;
		width: 260rpx;
		height: 76rpx;
		background-color: #f8d168;
		border-radius: 38rpx;
		line-height: 76rpx;
		margin: 48rpx auto 0 auto;
	}
</style>
