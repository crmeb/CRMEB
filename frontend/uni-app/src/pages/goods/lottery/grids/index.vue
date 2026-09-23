<template>
	<view :style="colorStyle">
		<view class="lottery" v-if="lotteryShow && loading">
			<image class="lottery-header" :src="image" mode=""></image>
			<view class="grids">
				<image class="grids-bag" src="../../static/lottery-bag.png" mode=""></image>
				<view class="grids-top">
					<image src="../../static/font-left.png" mode=""></image>
					<view class="grids-title">
						<view>{{ $t(`恭喜您`) }}，{{ $t(`获得`) }} {{ lottery_num }} {{ $t(`次`) }}</view>
					</view>
					<image src="../../static/font-right.png" mode=""></image>
				</view>
				<view class="winning-tips-list" v-if="userList.data.length">
					<text class="iconfont icon-huabanfuben"></text>
					<noticeBar :showMsg="userList.data"></noticeBar>
				</view>
				<view class="grids-box">
					<gridsLottery class="" :prizeData="prize" :lotteryNum="lottery_num" @get_winingIndex="getWiningIndex" @luck_draw_finish="luck_draw_finish"></gridsLottery>
				</view>
			</view>
			<!-- #ifdef H5 -->
			<view class="invite-people" v-if="factor == 5" @click="H5ShareBox = true">
				<view class="invite">
					{{ $t(`邀请好友`) }}
				</view>
			</view>
			<!-- #endif -->
			<showBox v-if="userList.data.length && is_all_record" :showMsg="userList"></showBox>
			<showBox v-if="htmlData.data && is_content" :showMsg="htmlData"></showBox>
			<showBox v-if="myList.data.length && is_personal_record" :showMsg="myList"></showBox>
			<lotteryAlert :aleartStatus="aleartStatus" @close="closeLottery" :alData="alData" :aleartType="aleartType"></lotteryAlert>
			<view class="mask" v-if="aleartStatus || addressModel" @click="lotteryAleartClose"></view>
			<userAddress
				:aleartStatus="addressModel"
				@getAddress="getAddress"
				@close="
					() => {
						addressModel = false;
					}
				"
			></userAddress>
			<!-- #ifdef H5 -->
			<!-- 分享-->
			<view class="share-box" v-if="H5ShareBox">
				<image :src="imgHost + '/statics/images/share-info.png'" @click="H5ShareBox = false"></image>
			</view>
			<!-- #endif -->
			<!-- #ifdef H5 -->
			<view class="followCode" v-if="followCode">
				<view class="pictrue">
					<view class="code-bg"><img class="imgs" :src="codeSrc" /></view>
				</view>
				<view class="mask" @click="closeFollowCode"></view>
			</view>
			<zb-code ref="qrcode" v-show="false" :show="codeShow" :cid="cid" :val="val" :onval="onval" :loadMake="loadMake" @result="qrR" />
			<!-- #endif -->
		</view>
		<view class="no-lottery" v-else-if="!lotteryShow && loading">
			<image :src="imgHost + '/statics/images/no-thing.png'"></image>
			<text>{{ $t(`商家暂未上架活动哦`) }}～</text>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref, watch } from 'vue';
import { onLoad, onShareAppMessage } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import { getLotteryData as getLotteryDataApi, startLottery, receiveLottery } from '@/api/lottery.js';
import { getUserInfo } from '@/api/user.js';
import zbCode from '@/components/zb-code/zb-code.vue';
import gridsLottery from '../../components/lottery/index.vue';
import showBox from '../components/showbox.vue';
import noticeBar from '../components/noticeBar.vue';
import lotteryAlert from '../components/lotteryAlert.vue';
import userAddress from '../components/userAddress.vue';
import home from '@/components/home';
import { toLogin } from '@/libs/login.js';
import { useAppStore } from '@/store/modules/app';
import { useColorTheme } from '@/composables/useColorTheme';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
import { HTTP_REQUEST_URL } from '@/config/app';
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif
const app = getApp();

const { colorStyle } = useColorTheme();

const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);
const { uid } = storeToRefs(appStore);

const imgHost = HTTP_REQUEST_URL;
const lotteryShow = ref(true);
const loading = ref(false);
const H5ShareBox = ref(false);
// #ifdef H5
const isWeixin = Auth.isWeixin();
// #endif
const addressModel = ref(false);
const lottery_num = ref(0);
const aleartType = ref(0);
const aleartStatus = ref(false);
const lottery_draw_param = ref({
	startIndex: 3, //开始抽奖位置，从0开始
	totalCount: 3, //一共要转的圈数
	winingIndex: 1, //中奖的位置，从0开始
	speed: 100 //抽奖动画的速度 [数字越大越慢,默认100]
});
const userList = ref({
	type: 'user',
	data: []
});
const myList = ref({
	type: 'me',
	data: []
});
const htmlData = ref({
	type: 'html',
	data: ''
});
const prize = ref([]);
const factor_num = ref(0);
const id = ref(0);
const alData = ref({});
const type = ref('');
const followCode = ref(false);
//二维码参数
const codeShow = ref(false);
const cid = '1';
const ifShow = ref(true);
const val = ref(''); // 要生成的二维码值
const lv = 3; // 二维码容错级别 ， 一般不用设置，默认就行
const onval = true; // val值变化时自动重新生成二维码
const loadMake = true; // 组件加载完成后自动生成二维码
const src = ref(''); // 二维码生成后的图片地址或base64
const codeSrc = ref('');
const image = ref(''); //上部背景图
const is_content = ref(0);
const is_all_record = ref(0);
const is_personal_record = ref(0);
const factor = ref(0);
const lottery_id = ref(0);

watch(isLogin, (newV, oldV) => {
	if (newV) {
		getLotteryData(type.value, lottery_id.value);
	}
}, { deep: true });

onLoad((option) => {
	type.value = option.type;
	lottery_id.value = option.lottery_id || 0;
	if (isLogin.value) {
		getLotteryData(type.value, lottery_id.value);
	} else {
		toLogin();
	}
});

// 分享朋友圈及朋友
onShareAppMessage(() => {
	return {
		title: i18n.t(`积分抽奖`),
		path: '/pages/goods/lottery/grids/index?type=' + type.value + '&lottery_id=' + lottery_id.value + '&spread=' + uid.value,
		imageUrl: image.value
	}
});

//#ifdef H5
function ShareInfo(data) {
	let href = location.href;
	if (Auth.isWeixin()) {
		getUserInfo().then((res) => {
			href = href.indexOf('?') === -1 ? href + '?spread=' + res.data.uid : href + '&spread=' + res.data.uid;
			let configAppMessage = {
				desc: data.name,
				title: data.name,
				link: href,
				imgUrl: data.image
			};
			Auth
				.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData', 'onMenuShareAppMessage', 'onMenuShareTimeline'], configAppMessage)
				.then((res) => {})
				.catch((err) => {});
		});
	}
}
//#endif
function getLotteryData(typeVal, lotteryIdVal) {
	uni.showLoading({
		title: i18n.t(`获取抽奖信息`)
	});
	getLotteryDataApi(typeVal, lotteryIdVal)
		.then((res) => {
			loading.value = true;
			factor_num.value = res.data.lottery.factor_num;
			id.value = res.data.lottery.id;
			image.value = res.data.lottery.image;
			prize.value = res.data.lottery.prize;
			lottery_num.value = res.data.lottery_num;
			htmlData.value.data = res.data.lottery.content;
			is_content.value = res.data.lottery.is_content;
			is_personal_record.value = res.data.lottery.is_personal_record;
			is_all_record.value = res.data.lottery.is_all_record;
			factor.value = res.data.lottery.factor;
			userList.value.data = res.data.all_record;
			myList.value.data = res.data.user_record;
			prize.value.push({});
			// #ifdef H5
			if (isLogin.value) {
				ShareInfo(res.data.lottery);
			}
			// #endif
			uni.hideLoading();
		})
		.catch((err) => {
			uni.hideLoading();
			lotteryShow.value = false;
			loading.value = true;
			util.Tips({
				title: err
			});
		});
}
function closeLottery(status) {
	aleartStatus.value = false;
	getLotteryData(type.value, lottery_id.value);
	if (alData.value.type === 6) {
		addressModel.value = true;
	}
}
function getAddress(data) {
	let addData = data;
	addData.id = alData.value.lottery_record_id;
	addData.address = data.address.province + data.address.city + data.address.district + data.detail;
	receiveLottery(addData)
		.then((res) => {
			util.Tips({
				title: i18n.t(`领取成功`)
			});
			addressModel.value = false;
		})
		.catch((err) => {
			util.Tips({
				title: err
			});
		});
}
function getWiningIndex(callback) {
	aleartType.value = 0;
	startLottery({
		id: id.value,
		type: type.value
	})
		.then((res) => {
			if (res.data.code === 'subscribe') {
				followCode.value = true;
				codeSrc.value = res.data.url;
				return;
			}
			prize.value.forEach((item, index) => {
				if (res.data.id === item.id) {
					alData.value = res.data;
					lottery_draw_param.value.winingIndex = index;
					callback(lottery_draw_param.value);
				}
			});
		})
		.catch((err) => {
			util.Tips({
				title: err
			});
		});
	// //props修改在小程序和APP端不成功，所以在这里使用回调函数传参，
}
// 抽奖完成
function luck_draw_finish(param) {
	aleartType.value = 2;
	aleartStatus.value = true;
}
function qrR(res) {
	codeSrc.value = res;
}
</script>

<style lang="scss" scoped>
page {
	background-color: #e74435;
}
.lottery {
	background-color: #e74435;
	padding: 0 0 20rpx 0;

	.lottery-header {
		width: 100%;
		height: 580rpx;
		margin: 0;
	}

	.grids {
		width: 100%;
		height: 800rpx;
		display: flex;
		flex-direction: column;
		justify-content: center;
		align-items: center;
		margin-top: -200rpx;
		position: relative;
		padding: 30rpx;

		.grids-bag {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
		}

		.grids-box {
			width: 560rpx;
			height: 560rpx;
			// z-index: 10000;
		}

		.grids-top {
			display: flex;

			image {
				width: 40rpx;
				height: 40rpx;
			}

			.grids-title {
				display: flex;
				justify-content: center;
				width: 100%;
				font-size: 20px;
				color: #fff;
				z-index: 999;
				padding: 0 14rpx;

				.grids-frequency {
					color: #ffd68e;
				}
			}
		}

		.winning-tips-list {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 50%;
			font-size: 20rpx;
			line-height: 40rpx;
			height: 40rpx;
			font-weight: 400;
			color: #fff8f8;
			margin: 20rpx 0;
			z-index: 999;
			background: linear-gradient(90deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.3) 51%, rgba(255, 255, 255, 0) 100%);

			.iconfont {
				font-size: 20rpx;
				margin-right: 10rpx;
			}
		}
	}

	.invite-people {
		display: flex;
		justify-content: center;

		.invite {
			display: flex;
			justify-content: center;
			align-items: center;
			width: 558rpx;
			height: 76rpx;
			font-size: 32rpx;
			font-weight: 600;
			color: #e74435;
			background: #ffd68e;
			box-shadow: 0px 6px 0px 0px rgba(185, 16, 0, 0.3);
			border-radius: 38px;
			margin: 76rpx;
		}
	}
}

.mask {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: rgba(0, 0, 0, 0.8);
	z-index: 9;
}

.share-box {
	z-index: 1300;
	position: fixed;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;

	image {
		width: 100%;
		height: 100%;
	}
}

.followCode {
	.pictrue {
		width: 500rpx;
		height: 530rpx;
		border-radius: 12px;
		left: 50%;
		top: 50%;
		margin-left: -250rpx;
		margin-top: -360rpx;
		position: fixed;
		z-index: 10000;

		.code-bg {
			display: flex;
			justify-content: center;
			width: 100%;
			height: 100%;
			background-image: url('@/static/images/code-bg.png');
			background-size: 100% 100%;
		}

		.imgs {
			width: 310rpx;
			height: 310rpx;
			margin-top: 92rpx;
		}
	}

	.mask {
		z-index: 9999;
	}
}

.no-lottery {
	display: flex;
	justify-content: center;
	flex-direction: column;
	align-items: center;
	font-size: 28rpx;
	color: #ccc;
	image {
		padding: 50rpx;
	}
}

[v-cloak] {
	display: none;
}
</style>
