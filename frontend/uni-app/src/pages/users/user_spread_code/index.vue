<template>
	<view :style="colorStyle">
		<view class='distribution-posters'>
			<swiper :indicator-dots="indicatorDots" :autoplay="autoplay" :circular="circular" :interval="interval"
				:duration="duration" @change="bindchange" previous-margin="40px" next-margin="40px">
				<block v-for="(item,index) in spreadData" :key="index" class="img-list">
					<swiper-item class="aaa">
						<div class="box" ref="bill" :class="swiperIndex == index ? 'active' : 'quiet'">
							<view class="user-msg">
								<view class="user-code">
									<image class="canvas" :style="{height:hg+'px'}" :src="posterImage[index]"
										v-if="posterImage[index]"></image>
									<canvas class="canvas" :style="{height:hg+'px'}" :canvas-id="'myCanvas'+ index"
										v-else></canvas>
								</view>
							</view>
						</div>
						<!-- <image :src="item.wap_poster" class="slide-image" :class="swiperIndex == index ? 'active' : 'quiet'" mode='aspectFill' /> -->
					</swiper-item>
				</block>
			</swiper>
			<!-- #ifndef H5  -->
			<view class='keep bg-color' @click='savePosterPathMp(posterImage[swiperIndex])'>{{$t(`保存海报`)}}</view>
			<!-- #endif -->
			<!-- #ifndef MP || APP-PLUS -->
			<div class="preserve acea-row row-center-wrapper">
				<div class="line"></div>
				<div class="tip">{{$t(`长按保存图片`)}}</div>
				<div class="line"></div>
			</div>
			<!-- #endif -->
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
		<view class="qrimg">
			<zb-code ref="qrcode" :show="codeShow" :cid="cid" :val="val" :size="size" :unit="unit"
				:background="background" :foreground="foreground" :pdground="pdground" :icon="icon" :iconSize="iconsize"
				:onval="onval" :loadMake="loadMake" @result="qrR" />
		</view>
	</view>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { onReady, onShow, onHide, onShareAppMessage } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import zbCode from '@/components/zb-code/zb-code.vue'
import {
	getUserInfo,
	spreadBanner,
	userShare,
	routineCode as routineCodeApi,
	spreadMsg,
	imgToBase as imgToBaseApi
} from '@/api/user.js';
import {
	toLogin
} from '@/libs/login.js';
import home from '@/components/home';
import {
	TOKENNAME,
	HTTP_REQUEST_URL
} from '@/config/app.js';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
// #ifdef MP
import authorize from '@/components/Authorize';
// #endif
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);
const uid = computed(() => appStore.uid);
const userData = computed(() => appStore.userInfo);

// ---------- data ----------
const imgUrls = ref([]);
const indicatorDots = ref(false);
const posterImageStatus = ref(true);
const circular = ref(false);
const autoplay = ref(false);
const interval = ref(3000);
const duration = ref(500);
const swiperIndex = ref(0);
const spreadList = ref([]);
const userInfo = ref({});
const poster = ref('');
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const spreadData = ref([{}]); //新海报数据
const nickName = ref("");
const siteName = ref("");
const mpUrl = ref("");
const canvasImageUrl = ref('');
const posterImage = ref([]);
//二维码参数
const codeShow = ref(false);
const cid = ref('1');
const ifShow = ref(true);
const val = ref(""); // 要生成的二维码值
const size = ref(200); // 二维码大小
const unit = ref('upx'); // 单位
const background = ref('#FFF'); // 背景色
const foreground = ref('#000'); // 前景色
const pdground = ref('#000'); // 角标色
const icon = ref(''); // 二维码图标
const iconsize = ref(40); // 二维码图标大小
const lv = ref(3); // 二维码容错级别 ， 一般不用设置，默认就行
const onval = ref(true); // val值变化时自动重新生成二维码
const loadMake = ref(true); // 组件加载完成后自动生成二维码
const src = ref(''); // 二维码生成后的图片地址或base64
const codeSrc = ref("");
const wd = ref(0);
const hg = ref(0);
const qrcode = ref("");

// ---------- watch ----------
watch(isLogin, (newV, oldV) => {
	if (newV) {
		userSpreadBannerList();
	}
}, { deep: true });
watch(userData, (newV, oldV) => {
	if (newV) {
		userInfo.value = newV;
	}
}, { deep: true });

// ---------- 生命周期 ----------
onReady(async () => {
	if (isLogin.value) {
		val.value = `${HTTP_REQUEST_URL}?spid=${uid.value}`
		await getUser()
	} else {
		toLogin();

	}
	nextTick(() => {
		let selector = uni.createSelectorQuery().select('.aaa');
		selector.fields({
			size: true
		}, data => {
			wd.value = data.width
			hg.value = data.height
		}).exec();
	})
});
onShow(() => {

});
onHide(() => {
	uni.hideLoading();
});
/**
 * 用户点击右上角分享
 */
// #ifdef MP
onShareAppMessage(() => {
	return {
		title: userInfo.value.nickname + '-' + i18n.t(`分销海报`),
		imageUrl: spreadList.value[0],
		path: '/pages/index/index?spread=' + userInfo.value.uid,
	};
});
// #endif

// ---------- methods ----------
function getUser() {
	getUserInfo().then(res => {
		userInfo.value = res.data
	})
}
function onLoadFun(e) {
	userInfo.value = e;
	userSpreadBannerList();
}
function qrR(res) {
	codeSrc.value = res
	spreadMsgs()
}
//获取图片
async function spreadMsgs() {

	let res = await spreadMsg()
	spreadData.value = res.data.spread
	nickName.value = res.data.nickname
	siteName.value = res.data.site_name
	// #ifdef MP
	qrcode.value = await imgToBaseFn(res.data.qrcode)
	// #endif
	// #ifdef MP
	await routineCodeFn()
	let mpUrlNew = await downloadFilestoreImage(mpUrl.value)
	// #endif
	uni.showLoading({
		title: i18n.t(`海报生成中`),
		mask: true
	});

	for (let i = 0; i < res.data.spread.length; i++) {
		let arr2, img
		// #ifdef MP
		arr2 = [mpUrlNew, await downloadFilestoreImage(res.data.spread[i].pic)]
		// #endif
		// #ifdef H5
		img = await imgToBaseFn(res.data.spread[i].pic, res.data.qrcode)
		arr2 = [img.code || codeSrc.value, img.image]
		// #endif
		// #ifdef APP-PLUS
		img = await imgToBaseFn(res.data.spread[i].pic, res.data.qrcode)
		arr2 = [img.code || codeSrc.value, res.data.spread[i].pic]
		// #endif
		util.userPosterCanvas(arr2, res.data.nickname, res.data.site_name, i, wd.value,
			hg.value, (
			tempFilePath) => {
			posterImage.value[i] = tempFilePath;
			// #ifdef MP
			if (!posterImage.value.length) {
				return util.Tips({
					title: i18n.t(`小程序二维码需要发布正式版后才能获取到`)
				});
			}
			// #endif
		});
	}
	uni.hideLoading();

}
function downloadImg() {
	uni.saveImageToPhotosAlbum({
		filePath: posterImage.value[swiperIndex.value],
		success: function() {}
	});
}
async function routineCodeFn() {
	let res = await routineCodeApi()
	mpUrl.value = res.data.url
}
async function imgToBaseFn(url, code) {
	let res = await imgToBaseApi({
		image: url,
		code: code
	})
	return res.data
}
// 二维码生成
function codeImg() {
	// http://当前域名+"?spread="+用户uid
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e
}
function bindchange(e) {
	let spreadListCur = spreadList.value;
	swiperIndex.value = e.detail.current;
	// poster.value = spreadListCur[e.detail.current].poster;
}
// #ifdef MP
function savePosterPathMp(url) {
	uni.getSetting({
		success(res) {
			if (!res.authSetting['scope.writePhotosAlbum']) {
				uni.authorize({
					scope: 'scope.writePhotosAlbum',
					success() {
						uni.saveImageToPhotosAlbum({
							filePath: url,
							success: function(res) {
								util.Tips({
									title: i18n.t(`保存成功`),
									icon: 'success'
								});
							},
							fail: function(res) {
								util.Tips({
									title: i18n.t(`保存失败`),
								});
							}
						});
					}
				});
			} else {
				uni.saveImageToPhotosAlbum({
					filePath: url,
					success: function(res) {
						util.Tips({
							title: i18n.t(`保存成功`),
							icon: 'success'
						});
					},
					fail: function(res) {
						util.Tips({
							title: i18n.t(`保存失败`),
						});
					}
				});
			}
		}
	});
}
// #endif
// #ifdef APP-PLUS
function savePosterPathMp(url) {
	uni.saveImageToPhotosAlbum({
		filePath: url,
		success: function(res) {
			util.Tips({
				title: i18n.t(`保存成功`),
				icon: 'success'
			});
		},
		fail: function(res) {
			util.Tips({
				title: i18n.t(`保存失败`),
			});
		}
	});
}
// #endif
//图片转符合安全域名路径
function downloadFilestoreImage(url) {
	return new Promise((resolve, reject) => {
		uni.downloadFile({
			url: url,
			success: function(res) {
				resolve(res.tempFilePath);
			},
			fail: function() {
				return util.Tips({
					title: ''
				});
			}
		});
	})
}
function setShareInfoStatus() {
	if (Auth.isWeixin()) {
		if (isLogin.value) {
			getUserInfo().then(res => {
				let configAppMessage = {
					desc: i18n.t(`分销海报`),
					title: res.data.nickname + '-' + i18n.t(`分销海报`),
					link: '/pages/index/index?spread=' + res.data.uid,
					imgUrl: spreadList.value[0]
				};
				Auth.wechatEvevt(["updateAppMessageShareData", "updateTimelineShareData"],
					configAppMessage)
			});
		} else {
			toLogin();
		}

	}
}
function userSpreadBannerList() {
	uni.showLoading({
		title: i18n.t(`获取中`),
		mask: true,
	})
	spreadBanner().then(res => {
		uni.hideLoading();
		spreadList.value = res.data;
		poster.value = res.data[0].poster;
		// #ifdef H5
		setShareInfoStatus();
		// #endif
	}).catch(err => {
		uni.hideLoading();
	});
}
</script>

<style lang="scss">
	page {
		background-color: #a3a3a3 !important;
	}

	.canvas {
		width: 100%;
		// height: 550px;
	}

	.box {
		width: 100%;
		height: 100%;
		position: relative;
		border-radius: 18rpx;
		overflow: hidden;

		.user-msg {
			position: absolute;
			width: 100%;
			height: 100%;
			display: flex;
			align-items: center;
			justify-content: center;

			.user-code {
				width: 100%;
				// height: 100%;
				display: flex;
				align-items: center;
				justify-content: center;
				justify-content: space-between;

				image {
					width: 100%;
				}
			}
		}
	}

	.img-list {
		margin-right: 40px;
	}

	.distribution-posters swiper {
		width: 100%;
		height: 1000rpx;
		position: relative;
		margin-top: 40rpx;
	}

	.distribution-posters .slide-image {
		width: 100%;
		height: 100%;
		margin: 0 auto;
		border-radius: 15rpx;
	}

	.distribution-posters :deep(.active){
		transform: none;
		transition: all 0.2s ease-in 0s;
	}

	.distribution-posters :deep(.quiet){
		transform: scale(0.8333333);
		transition: all 0.2s ease-in 0s;
	}

	.distribution-posters .keep {
		font-size: 30rpx;
		color: #fff;
		width: 600rpx;
		height: 80rpx;
		border-radius: 50rpx;
		text-align: center;
		line-height: 80rpx;
		margin: 38rpx auto;
	}

	.distribution-posters .preserve {
		color: #fff;
		text-align: center;
		margin-top: 38rpx;
	}

	.distribution-posters .preserve .line {
		width: 100rpx;
		height: 1px;
		background-color: #fff;
	}

	.distribution-posters .preserve .tip {
		margin: 0 30rpx;
	}
</style>
