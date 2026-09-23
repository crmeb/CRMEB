<template>
	<view>
		<view class='sign-record'>
		   <view class='list' v-for="(item,index) in signList" :key="index">
		      <view class='item'>
		         <view class='data'>{{item.month}}</view>
		         <view class='listn'>
		            <view class='itemn acea-row row-between-wrapper' v-for="(itemn,indexn) in item.list" :key="indexn">
		               <view>
		                  <view class='name line1'>{{$t(itemn.title)}}</view>
		                  <view>{{itemn.add_time}}</view>
		               </view>
		               <view class='num'>+{{itemn.number}}</view>
		            </view>
		         </view>
		      </view>
		   </view>
			    <view class='loadingicon acea-row row-center-wrapper' v-if="signList.length > 0 || loading || loadError" @click="getSignMoneList">
		        <text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadtitle}}
		    </view>
				<view v-if="signList.length == 0 && !loading && !loadError"><emptyPage :title="$t(`暂无签到记录~`)"></emptyPage></view>
		</view>
	</view>
</template>

<script setup>
import { ref, watch } from 'vue';
import { onShow, onHide, onUnload, onReachBottom } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import { getSignMonthList } from '@/api/user.js';
import { toLogin } from '@/libs/login.js';
import emptyPage from '@/components/emptyPage';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';

const appStore = useAppStore();
const { isLogin, uid } = storeToRefs(appStore);

// ---------- data ----------
const loading = ref(false);
const loadend = ref(false);
const loadtitle = ref(i18n.t(`加载更多`));
const page = ref(1);
const limit = ref(8);
const signList = ref([]);
const loadError = ref('');
let pageVisible = false;
let requestVersion = 0;

// ---------- watch ----------
watch([isLogin, uid], () => {
	resetSignList();
	if (pageVisible && isLogin.value) {
		getSignMoneList();
	}
});

// ---------- 生命周期 ----------
onShow(() => {
	pageVisible = true;
	if (isLogin.value) {
		if (page.value === 1) getSignMoneList();
	} else {
		toLogin();
	}
});

onHide(() => {
	pageVisible = false;
});

onUnload(() => {
	pageVisible = false;
	resetSignList();
});

onReachBottom(() => {
	getSignMoneList();
});

// ---------- methods ----------
function resetSignList() {
	requestVersion++;
	page.value = 1;
	signList.value = [];
	loading.value = false;
	loadend.value = false;
	loadError.value = '';
	loadtitle.value = i18n.t(`加载更多`);
}
/**
   * 获取签到记录列表
  */
async function getSignMoneList() {
	if (loading.value || loadend.value || !isLogin.value) return;
	const version = requestVersion;
	const currentPage = page.value;
	loading.value = true;
	loadError.value = '';
	loadtitle.value = '';
	try {
		const res = await getSignMonthList({ page: currentPage, limit: limit.value });
		if (version !== requestVersion) return;
		const list = Array.isArray(res.data) ? res.data : [];
		const loadendNew = list.length < limit.value;
		signList.value = util.SplitArray(list, signList.value);
		page.value = currentPage + 1;
		loadend.value = loadendNew;
		loadtitle.value = loadendNew ? i18n.t(`我也是有底线的`) : i18n.t(`加载更多`);
	} catch (error) {
		if (version === requestVersion) {
			loadError.value = typeof error === 'string' ? error : error?.msg || error?.message || i18n.t(`请求失败`);
			loadtitle.value = i18n.t(`重新加载`);
			util.Tips({ title: loadError.value });
		}
	} finally {
		if (version === requestVersion) loading.value = false;
	}
}
</script>

<style>
.sign-record .num{
	color: #fe5c2d!important;
}
</style>
