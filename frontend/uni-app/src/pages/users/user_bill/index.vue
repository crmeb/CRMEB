<template>
	<view :style="colorStyle">
		<view class='bill-details'>
			<view class='nav acea-row'>
				<view class='item' :class='type==0 ? "on":""' @click='changeType(0)'>{{$t(`全部`)}}</view>
				<view class='item' :class='type==1 ? "on":""' @click='changeType(1)'>{{$t(`消费`)}}</view>
				<view class='item' :class='type==2 ? "on":""' @click='changeType(2)'>{{$t(`充值`)}}</view>
			</view>
			<view class='sign-record'>
				<view class='list' v-for="(item,index) in userBillList" :key="index">
					<view class='item'>
						<view class='data'>{{item.time}}</view>
						<view class='listn'>
							<view class='itemn acea-row row-between-wrapper' v-for="(vo,indexn) in item.child" :key="indexn">
								<view>
									<view class='name line1'>{{$t(vo.title)}}</view>
									<view>{{vo.add_time}}</view>
								</view>
								<view class='num' v-if="vo.pm">+{{vo.number}}</view>
								<view class='num font-color' v-else>-{{vo.number}}</view>
							</view>
						</view>
					</view>
				</view>
				<view class='loadingicon acea-row row-center-wrapper' v-if="userBillList.length>0">
					<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
				</view>
				<view v-if="userBillList.length == 0">
					<emptyPage :title="$t(`暂无账单的记录哦～`)"></emptyPage>
				</view>
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<home v-if="navigation"></home>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow, onReachBottom } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
	getCommissionInfo
} from '@/api/user.js';
import {
	toLogin
} from '@/libs/login.js';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
import emptyPage from '@/components/emptyPage.vue';
import home from '@/components/home';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import i18n from '@/utils/lang.js';

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const loadTitle = ref(i18n.t(`加载更多`));
const loading = ref(false);
const loadend = ref(false);
const page = ref(1);
const limit = ref(15);
const type = ref(0);
const userBillList = ref([]);
const times = ref([]);
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权

// ---------- 生命周期 ----------
onShow(() => {
	if (isLogin.value) {
		getUserBillList();
	} else {
		toLogin();
	}
});
/**
 * 生命周期函数--监听页面加载
 */
onLoad((options) => {
	type.value = options.type || 0;
});
/**
 * 页面上拉触底事件的处理函数
 */
onReachBottom(() => {
	getUserBillList();
});

// ---------- methods ----------
/**
 * 授权回调
 */
function onLoadFun() {
	getUserBillList();
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e
}
/**
 * 获取账户明细
 */
function getUserBillList() {
	if (loading.value) return;
	if (loadend.value) return;
	loading.value = true;
	loadTitle.value = '';
	getCommissionInfo({
		page: page.value,
		limit: limit.value
	}, type.value).then(res => {
		for (let i = 0; i < res.data.time.length; i++) {

			if (!times.value.includes(res.data.time[i])) {
				times.value.push(res.data.time[i])
				userBillList.value.push({
					time: res.data.time[i],
					child: []
				})
			}
		}

		for (let x = 0; x < times.value.length; x++) {
			for (let j = 0; j < res.data.list.length; j++) {
				if (times.value[x] === res.data.list[j].time_key) {
					userBillList.value[x].child.push(res.data.list[j])
				}
			}
		}
		let loadendNew = res.data.list.length < limit.value;
		loadend.value = loadendNew;
		loadTitle.value = loadendNew ? i18n.t(`我也是有底线的`) : i18n.t(`加载更多`);
		page.value += 1;
		loading.value = false;
	}).catch(err => {
		loading.value = false;
		loadTitle.value = i18n.t(`加载更多`);
	})
}
/**
 * 切换导航
 */
function changeType(typeNum) {
	type.value = typeNum;
	loadend.value = false;
	page.value = 1;
	times.value = [];
	userBillList.value = [];
	getUserBillList();
}
</script>

<style scoped lang='scss'>
	.bill-details .nav {
		background-color: #fff;
		height: 90rpx;
		width: 100%;
		line-height: 90rpx;
	}

	.bill-details .nav .item {
		flex: 1;
		text-align: center;
		font-size: 30rpx;
		color: #282828;
	}

	.bill-details .nav .item.on {
		color: var(--view-theme);
		border-bottom: 3rpx solid var(--view-theme);
	}
</style>
