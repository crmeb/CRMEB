<template>
	<view :style="colorStyle">
		<block v-if="bargain.length>0">
			<div class="bargain-record" ref="container">
				<div class="item" v-for="(item, index) in bargain" :key="index">
					<div class="picTxt acea-row row-between-wrapper">
						<div class="pictrue">
							<image :src="item.image" />
						</div>
						<div class="text acea-row row-column-around">
							<div class="line1" style="width: 100%;">{{ item.title }}</div>
							<count-down :justify-left="'justify-content:left'" :is-day="true" :tip-text="$t(`倒计时`) "
								:day-text=" $t(`天`) " :hour-text=" $t(`时`) " :minute-text=" $t(`分`) " :second-text=" $t(`秒`)"
								:datatime="item.datatime" v-if="item.status === 1"></count-down>
							<div class="successTxt font-num" v-else-if="item.status === 3">{{$t(`砍价成功`)}}</div>
							<div class="endTxt" v-else>{{$t(`活动已结束`)}}</div>
							<div class="money font-num">
								{{$t(`已砍至`)}}<span class="symbol">{{$t(`￥`)}}</span><span class="num">{{ item.residue_price }}</span>
							</div>
						</div>
					</div>
					<div class="bottom acea-row row-between-wrapper">
						<div class="purple" v-if="item.status === 1">{{$t(`活动进行中`)}}</div>
						<div class="success" v-if="item.status === 3">{{$t(`砍价成功`)}}</div>
						<div class="end" v-if="item.status === 2">{{$t(`活动已结束`)}}</div>
						<div class="acea-row row-middle row-right">
							<div class="bnt cancel" v-if="item.status === 1"
								@click="getBargainUserCancel(item.bargain_id)">
								{{$t(`取消活动`)}}
							</div>
							<div class="bnt bg-color-red" v-if="item.status === 1" @click="goDetail(item.bargain_id)">
								{{$t(`继续砍价`)}}
							</div>
						</div>
						<div class="acea-row row-middle row-right success"  v-if="item.status === 3">
							{{item.success_time}}
						</div>
					</div>
				</div>
				<Loading :loaded="status" :loading="loadingList"></Loading>
			</div>
		</block>
		<block v-if="bargain.length == 0">
			<emptyPage :title="$t(`暂无砍价记录`)"></emptyPage>
		</block>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>
<script setup>
	import CountDown from "@/components/countDown";
	import emptyPage from '@/components/emptyPage.vue'
	import {
		getBargainUserList as getBargainUserListApi,
		getBargainUserCancel as getBargainUserCancelApi
	} from "@/api/activity";
	import {
		getUserInfo as getUserInfoApi
	} from '@/api/user.js';
	import Loading from "@/components/Loading";
	import home from '@/components/home';
	import util from '@/utils/util.js';
	import { useColorTheme } from '@/composables/useColorTheme';
	import { onLoad, onReachBottom } from '@dcloudio/uni-app';
	import { ref } from 'vue';

	const { colorStyle } = useColorTheme();

	// ---------- data ----------
	const bargain = ref([]);
	const status = ref(false); //砍价列表是否获取完成 false 未完成 true 完成
	const loadingList = ref(false); //当前接口是否请求完成 false 完成 true 未完成
	const page = ref(1); //页码
	const limit = ref(20); //数量
	const userInfo = ref({});

	// ---------- methods ----------
	function goDetail(id) {
		uni.navigateTo({
			url: `/pages/activity/goods_bargain_details/index?id=${id}&bargain=${userInfo.value.uid}`
		})
	}
	// 砍价列表
	function goList() {
		uni.navigateTo({
			url: '/pages/activity/goods_bargain/index'
		})
	}
	function getBargainUserList() {
		if (loadingList.value) return;
		if (status.value) return;
		getBargainUserListApi({
				page: page.value,
				limit: limit.value
			})
			.then(res => {
				status.value = res.data.length < limit.value;
				bargain.value.push.apply(bargain.value, res.data);
				page.value++;
				loadingList.value = false;
			})
			.catch(res => {
				util.Tips({
					title: res
				})
			});
	}
	/**
	* 取消砍价活动
	* @param {number} bargainId - 砍价活动ID
	*/
	function getBargainUserCancel(bargainId) {
		getBargainUserCancelApi({
				bargainId: bargainId
			})
			.then(res => {
				status.value = false;
				loadingList.value = false;
				page.value = 1;
				bargain.value = [];
				getBargainUserList();
				util.Tips({
					title: res.msg
				})
			})
			.catch(res => {
				util.Tips({
					title: res
				})
			});
	}
	/**
	 * 获取个人用户信息
	 */
	function getUserInfo() {
		getUserInfoApi().then(res => {
			userInfo.value = res.data;
		});
	}

	// ---------- 生命周期 ----------
	onLoad(() => {
		getBargainUserList();
		getUserInfo();
	});

	onReachBottom(() => {
		getBargainUserList();
	});
</script>

<style lang="scss">
	/*砍价记录*/
	.bargain-record .item .picTxt .text .time .styleAll {
		color: #fc4141;
		font-size: 24rpx;
	}

	.bargain-record .item .picTxt .text .time .red {
		color: #999;
		font-size: 24rpx;
	}

	.bargain-record .item {
		background-color: #fff;
		margin-bottom: 12upx;
	}

	.bargain-record .item .picTxt {
		height: 210upx;
		border-bottom: 1px solid #f0f0f0;
		padding: 0 30upx;
	}

	.bargain-record .item .picTxt .pictrue {
		width: 150upx;
		height: 150upx;
	}

	.bargain-record .item .picTxt .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 6upx;
	}

	.bargain-record .item .picTxt .text {
		width: 515upx;
		font-size: 30upx;
		color: #282828;
		height: 150upx;
	}

	.bargain-record .item .picTxt .text .time {
		font-size: 24upx;
		color: #868686;
		justify-content: left !important;
	}

	.bargain-record .item .picTxt .text .successTxt {
		font-size: 24rpx;
	}

	.bargain-record .item .picTxt .text .endTxt {
		font-size: 24rpx;
		color: #999;
	}

	.bargain-record .item .picTxt .text .money {
		font-size: 24upx;
	}

	.bargain-record .item .picTxt .text .money .num {
		font-size: 32upx;
		font-weight: bold;
	}

	.bargain-record .item .picTxt .text .money .symbol {
		font-weight: bold;
	}

	.bargain-record .item .bottom {
		height: 100upx;
		padding: 0 30upx;
		font-size: 27upx;
	}

	.bargain-record .item .bottom .purple {
		color: #f78513;
	}

	.bargain-record .item .bottom .end {
		color: #999;
	}

	.bargain-record .item .bottom .success {
		color: #e93323;
	}

	.bargain-record .item .bottom .bnt {
		font-size: 27upx;
		color: #fff;
		width: 176upx;
		height: 60upx;
		border-radius: 32upx;
		text-align: center;
		line-height: 60upx;
	}

	.bargain-record .item .bottom .bnt.cancel {
		color: #aaa;
		border: 1px solid #ddd;
	}

	.bargain-record .item .bottom .bnt~.bnt {
		margin-left: 18upx;
	}
</style>
