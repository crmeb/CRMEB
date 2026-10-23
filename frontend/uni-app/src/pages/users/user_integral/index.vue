<template>
	<view>
		<view class='integral-details' :style="colorStyle">
			<view class='header'>
				<view class='currentScore'>{{$t(`当前积分`)}}</view>
				<view class="scoreNum">{{userInfo.integral}}</view>
				<view class='line'></view>
				<view class='nav acea-row'>
					<view class='item'>
						<view class='num'>{{userInfo.sum_integral}}</view>
						<view>{{$t(`累计积分`)}}</view>
					</view>
					<view class='item'>
						<view class='num'>{{userInfo.deduction_integral}}</view>
						<view>{{$t(`累计消费`)}}</view>
					</view>
					<view class='item'>
						<view class='num'>{{userInfo.frozen_integral}}</view>
						<view>{{$t(`冻结积分`)}}</view>
					</view>
				</view>
				<view class="apply">
					<view>
						<navigator :render-link="false" url='/pages/users/privacy/index?type=6' hover-class="none">
							<view>{{$t(`积分规则`)}}</view>
						</navigator>
					</view>
				</view>
			</view>
			<view class='wrapper'>
				<view class='nav acea-row'>
					<view class='item acea-row row-center-wrapper' :class='current==index?"on":""'
						v-for="(item,index) in navList" :key='index' @click='nav(index)'><text class='iconfont'
							:class="item.icon"></text>{{item.name}}</view>
				</view>
				<view class='list' :class="{'bag-white': integralList.length}" :hidden='current!=0'>
					<view class='tip acea-row row-middle'><text
							class='iconfont icon-shuoming'></text>{{$t(`提示：积分数值的高低会直接影响您的会员等级`)}}</view>
					<view class='item acea-row row-between-wrapper' v-for="(item,index) in integralList" :key="index">
						<view>
							<view class='state'>{{$t(item.title)}}</view>
							<view>{{item.add_time}}</view>
						</view>
						<view class='num font-color' v-if="item.pm">+{{item.number}}</view>
						<view class='num' v-else>-{{item.number}}</view>
					</view>
					<view class='loadingicon acea-row row-center-wrapper' v-if="integralList.length>0">
						<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
					</view>
					<view class="no-thing" v-if="integralList.length == 0">
						<emptyPage :title="$t(`暂无积分记录哦～`)"></emptyPage>
					</view>
				</view>
				<view class='list2' :hidden='current!=1'>
					<navigator :render-link="false" class='item acea-row row-between-wrapper' hover-class='none' open-type="switchTab"
						url='/pages/index/index'>
						<view class='pictrue'>
							<image src='../static/score.png'></image>
						</view>
						<view class='name'>{{$t(`购买商品可获得积分奖励`)}}</view>
						<view class='earn'>{{$t(`赚积分`)}}</view>
					</navigator>
					<navigator :render-link="false" class='item acea-row row-between-wrapper' hover-class='none'
						url='/pages/users/user_sgin/index'>
						<view class='pictrue'>
							<image src='../static/score.png'></image>
						</view>
						<view class='name'>{{$t(`每日签到可获得积分奖励`)}}</view>
						<view class='earn'>{{$t(`赚积分`)}}</view>
					</navigator>
				</view>
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref, watch } from 'vue';
import { onLoad, onReachBottom } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
	postSignUser,
	getIntegralList as getIntegralListApi
} from '@/api/user.js';
import {
	toLogin
} from '@/libs/login.js';
import emptyPage from '@/components/emptyPage.vue';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const navList = [{
		'name': i18n.t(`分值明细`),
		'icon': 'icon-mingxi'
	},
	{
		'name': i18n.t(`分值提升`),
		'icon': 'icon-tishengfenzhi'
	}
];
const current = ref(0);
const page = ref(1);
const limit = ref(10);
const integralList = ref([]);
const userInfo = ref({});
const loadend = ref(false);
const loading = ref(false);
const loadTitle = ref(i18n.t(`加载更多`));
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权

// ---------- watch ----------
watch(isLogin, (newV, oldV) => {
	if (newV) {
		getUserInfo();
		getIntegralList();
	}
}, { deep: true });

// ---------- 生命周期 ----------
onLoad(() => {
	if (isLogin.value) {
		getUserInfo();
		getIntegralList();
	} else {
		toLogin();
	}
});
/**
 * 页面上拉触底事件的处理函数
 */
onReachBottom(() => {
	getIntegralList();
});

// ---------- methods ----------
/**
 * 授权回调
 */
function onLoadFun() {
	getUserInfo();
	getIntegralList();
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e
}
function getUserInfo() {
	postSignUser({
		sign: 1,
		integral: 1,
		all: 1
	}).then(function(res) {
		userInfo.value = res.data;
	});
}

/**
 * 获取积分明细
 */
function getIntegralList() {
	if (loading.value) return;
	if (loadend.value) return;
	loading.value = true;
	loadTitle.value = '';
	getIntegralListApi({
		page: page.value,
		limit: limit.value
	}).then(function(res) {
		let list = res.data,
			loadendNew = list.length < limit.value;
		integralList.value = util.SplitArray(list, integralList.value);
		page.value = page.value + 1;
		loading.value = false;
		loadend.value = loadendNew;
		loadTitle.value = loadendNew ? i18n.t(`我也是有底线的`) : i18n.t(`加载更多`);
	}, function(res) {
		// 原代码此处误用 this.loading（非箭头函数中 this 为 undefined），修正为直接重置
		loading.value = false;
		loadTitle.value = i18n.t(`加载更多`);
	});
}
function nav(currentNum) {
	current.value = currentNum;
}
</script>

<style scoped lang="scss">
	.integral-details .header {
		// background-image: url('data:image/jpeg;base64,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');
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 100%;
		height: 460rpx;
		font-size: 72rpx;
		color: #fff;
		padding: 31rpx 0 45rpx 0;
		box-sizing: border-box;
		text-align: center;
		font-family: 'Guildford Pro';
		background-color: var(--view-theme);
	}

	.integral-details .header .currentScore {
		font-size: 26rpx;
		color: rgba(255, 255, 255, 0.8);
		text-align: center;
		margin-bottom: 11rpx;
	}

	.integral-details .header .scoreNum {
		font-family: "Guildford Pro";
	}

	.integral-details .header .line {
		width: 60rpx;
		height: 3rpx;
		background-color: #fff;
		margin: 20rpx auto 0 auto;
	}

	.integral-details .header .nav {
		font-size: 22rpx;
		color: rgba(255, 255, 255, 0.8);
		flex: 1;
		margin-top: 35rpx;
	}

	.integral-details .header .nav .item {
		width: 33.33%;
		text-align: center;
	}

	.integral-details .header .nav .item .num {
		color: #fff;
		font-size: 40rpx;
		margin-bottom: 5rpx;
		font-family: 'Guildford Pro';
	}

	.integral-details .wrapper .nav {
		flex: 1;
		width: 690rpx;
		border-radius: 20rpx 20rpx 0 0;
		margin: -96rpx auto 0 auto;
		background-color: #f7f7f7;
		height: 96rpx;
		font-size: 30rpx;
		color: #bbb;
	}

	.integral-details .wrapper .nav .item {
		text-align: center;
		width: 50%;
	}

	.integral-details .wrapper .nav .item.on {
		background-color: #fff;
		color: var(--view-theme);
		font-weight: bold;
		border-radius: 20rpx 0 0 0;
	}

	.integral-details .wrapper .nav .item:nth-of-type(2).on {
		border-radius: 0 20rpx 0 0;
	}

	.integral-details .wrapper .nav .item .iconfont {
		font-size: 38rpx;
		margin-right: 10rpx;
	}

	.integral-details .wrapper .list {
		padding: 24rpx 30rpx;
	}

	.integral-details .wrapper .list.bag-white {
		background-color: #fff;
	}

	.integral-details .wrapper .list .tip {
		font-size: 25rpx;
		width: 690rpx;
		height: 60rpx;
		border-radius: 50rpx;
		background-color: #fff5e2;
		border: 1rpx solid #ffeac1;
		color: #c8a86b;
		padding: 0 20rpx;
		box-sizing: border-box;
		margin-bottom: 24rpx;
	}

	.integral-details .wrapper .list .tip .iconfont {
		font-size: 35rpx;
		margin-right: 15rpx;
	}

	.integral-details .wrapper .list .item {
		height: 124rpx;
		border-bottom: 1rpx solid #eee;
		font-size: 24rpx;
		color: #999;
	}

	.integral-details .wrapper .list .item .state {
		font-size: 28rpx;
		color: #282828;
		margin-bottom: 8rpx;
	}

	.integral-details .wrapper .list .item .num {
		font-size: 36rpx;
		font-family: 'Guildford Pro';
		color: #16AC57;
	}

	.integral-details .wrapper .list .item .num.font-color {
		color: #E93323 !important;
	}

	.integral-details .wrapper .list2 {
		background-color: #fff;
		padding: 24rpx 0;
	}

	.integral-details .wrapper .list2 .item {
		background-image: linear-gradient(to right, #fff7e7 0%, #fffdf9 100%);
		width: 690rpx;
		height: 180rpx;
		position: relative;
		border-radius: 10rpx;
		margin: 0 auto 20rpx auto;
		padding: 0 25rpx 0 180rpx;
		box-sizing: border-box;
	}

	.integral-details .wrapper .list2 .item .pictrue {
		width: 90rpx;
		height: 150rpx;
		position: absolute;
		bottom: 0;
		left: 45rpx;
	}

	.integral-details .wrapper .list2 .item .pictrue image {
		width: 100%;
		height: 100%;
	}

	.integral-details .wrapper .list2 .item .name {
		width: 285rpx;
		font-size: 30rpx;
		font-weight: bold;
		color: #c8a86b;
	}

	.integral-details .wrapper .list2 .item .earn {
		font-size: 26rpx;
		color: #c8a86b;
		border: 2rpx solid #c8a86b;
		text-align: center;
		line-height: 52rpx;
		height: 52rpx;
		width: 160rpx;
		border-radius: 50rpx;
	}

	.apply {
		top: 52rpx;
		right: 0;
		position: absolute;
		width: max-content;
		height: 56rpx;
		padding: 0 14rpx;
		background-color: #fff1db;
		color: #a56a15;
		font-size: 22rpx;
		border-radius: 30rpx 0 0 30rpx;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.empty-box {
		padding-bottom: 300rpx;
	}
</style>
