<template>
	<view :style="colorStyle">
		<view class="distribution-posters">
			<swiper
				:indicator-dots="false"
				:autoplay="false"
				:circular="false"
				:interval="3000"
				:duration="500"
				@change="bindchange"
				previous-margin="40px"
				next-margin="40px"
			>
				<swiper-item v-for="(item, index) in posterImage" :key="index">
					<view class="box" :class="swiperIndex === index ? 'active' : 'quiet'">
						<image class="poster-img" :src="item" mode="aspectFit" />
					</view>
				</swiper-item>
			</swiper>
			<!-- #ifndef H5  -->
			<view class="keep bg-color" @click="savePosterPathMp(posterImage[swiperIndex])">{{
				$t(`保存海报`)
			}}</view>
			<!-- #endif -->
			<!-- #ifndef MP || APP-PLUS -->
			<view class="preserve acea-row row-center-wrapper">
				<view class="line"></view>
				<view class="tip">{{ $t(`长按保存图片`) }}</view>
				<view class="line"></view>
			</view>
			<!-- #endif -->
		</view>
		<view v-if="generating" class="gen-mask">
			<view class="gen-box">{{ $t(`海报生成中`) }}</view>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
		<!-- 视口内透明画布：固定设计稿像素，避免量 DOM 竞态 / 离屏导出失败 -->
		<canvas
			class="poster-canvas"
			canvas-id="spreadPosterCanvas"
			:style="{ width: canvasW + 'px', height: canvasH + 'px' }"
			:width="canvasW"
			:height="canvasH"
		/>
		<view class="qrimg">
			<zb-code
				ref="qrcode"
				:show="false"
				:cid="cid"
				:val="val"
				:size="size"
				:unit="unit"
				:background="background"
				:foreground="foreground"
				:pdground="pdground"
				:icon="icon"
				:iconSize="iconsize"
				:onval="onval"
				:loadMake="loadMake"
				@result="qrR"
			/>
		</view>
	</view>
</template>

<script setup>
import { ref, computed, getCurrentInstance } from 'vue';
import { onReady, onHide, onShareAppMessage } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import zbCode from '@/components/zb-code/zb-code.vue';
import {
	getUserInfo,
	routineCode as routineCodeApi,
	spreadMsg,
	imgToBase as imgToBaseApi
} from '@/api/user.js';
import { toLogin } from '@/libs/login.js';
import home from '@/components/home';
import { HTTP_REQUEST_URL } from '@/config/app.js';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);
const uid = computed(() => appStore.uid);
const instance = getCurrentInstance();

const canvasW = 750;
const canvasH = 1334;

const swiperIndex = ref(0);
const userInfo = ref({});
const posterImage = ref([]);
const generating = ref(false);

const cid = ref('spreadQrCode');
const val = ref('');
const size = ref(200);
const unit = ref('upx');
const background = ref('#FFF');
const foreground = ref('#000');
const pdground = ref('#000');
const icon = ref('');
const iconsize = ref(40);
const onval = ref(true);
const loadMake = ref(true);
const codeSrc = ref('');

onReady(async () => {
	if (!isLogin.value) {
		toLogin();
		return;
	}
	val.value = `${HTTP_REQUEST_URL}?spid=${uid.value}`;
	await getUser();
	await generatePosters();
});

onHide(() => {
	generating.value = false;
});

// #ifdef MP
onShareAppMessage(() => {
	return {
		title: (userInfo.value.nickname || '') + '-' + i18n.t(`分销海报`),
		imageUrl: posterImage.value[0] || '',
		path: '/pages/index/index?spid=' + (userInfo.value.uid || uid.value)
	};
});
// #endif

function getUser() {
	return getUserInfo()
		.then((res) => {
			userInfo.value = res.data || {};
		})
		.catch(() => {});
}

function qrR(res) {
	codeSrc.value = res || '';
}

function waitCodeSrc(timeout = 8000) {
	return new Promise((resolve) => {
		if (codeSrc.value) {
			resolve(codeSrc.value);
			return;
		}
		const start = Date.now();
		const timer = setInterval(() => {
			if (codeSrc.value || Date.now() - start > timeout) {
				clearInterval(timer);
				resolve(codeSrc.value || '');
			}
		}, 100);
	});
}

async function imgToBaseFn(url, code) {
	const res = await imgToBaseApi({
		image: url,
		code: code || ''
	});
	return res.data || {};
}

function downloadFilestoreImage(url) {
	return new Promise((resolve, reject) => {
		uni.downloadFile({
			url,
			success: (res) => {
				if (res.statusCode === 200 && res.tempFilePath) {
					resolve(res.tempFilePath);
				} else {
					reject(new Error('download'));
				}
			},
			fail: () => reject(new Error('download'))
		});
	});
}

function absUrl(url) {
	if (!url) return '';
	if (/^https?:\/\//i.test(url) || url.startsWith('data:')) return url;
	return String(HTTP_REQUEST_URL).replace(/\/$/, '') + '/' + String(url).replace(/^\//, '');
}

async function resolveQrAndBg(pic, serverQrcode) {
	const bg = absUrl(pic);
	// #ifdef MP
	const routine = await routineCodeApi();
	const mpUrl = routine?.data?.url;
	if (!mpUrl) {
		throw new Error(i18n.t(`小程序二维码需要发布正式版后才能获取到`));
	}
	const [qrPath, bgPath] = await Promise.all([
		downloadFilestoreImage(mpUrl),
		downloadFilestoreImage(bg)
	]);
	return [qrPath, bgPath];
	// #endif

	// #ifdef H5 || APP-PLUS
	const img = await imgToBaseFn(bg, serverQrcode ? absUrl(serverQrcode) : '');
	const qr = img.code || (await waitCodeSrc());
	if (!qr) {
		throw new Error(i18n.t(`二维码生成失败`));
	}
	if (!img.image) {
		throw new Error(i18n.t(`无法获取图片信息`));
	}
	return [qr, img.image];
	// #endif
}

async function generatePosters() {
	if (generating.value) return;
	generating.value = true;
	try {
		const res = await spreadMsg();
		const list = res?.data?.spread || [];
		if (!list.length) {
			throw new Error(i18n.t(`暂无海报`));
		}
		const nickname = res.data.nickname || userInfo.value.nickname || '';
		const siteName = res.data.site_name || '';
		const serverQrcode = res.data.qrcode || '';

		// #ifdef H5 || APP-PLUS
		if (!serverQrcode) {
			await waitCodeSrc();
		}
		// #endif

		const images = [];
		for (let i = 0; i < list.length; i++) {
			const arr2 = await resolveQrAndBg(list[i].pic, serverQrcode);
			const path = await util.userPosterCanvas(
				arr2,
				nickname,
				siteName,
				i,
				canvasW,
				canvasH,
				null,
				instance?.proxy
			);
			images.push(path);
		}
		posterImage.value = images;
		// #ifdef H5
		setShareInfoStatus();
		// #endif
	} catch (err) {
		util.Tips({
			title: (err && err.message) || i18n.t(`海报生成失败`)
		});
	} finally {
		generating.value = false;
	}
}

function bindchange(e) {
	swiperIndex.value = e.detail.current;
}

function savePosterPathMp(url) {
	if (!url) {
		return util.Tips({ title: i18n.t(`海报生成中`) });
	}
	uni.saveImageToPhotosAlbum({
		filePath: url,
		success: () => {
			util.Tips({
				title: i18n.t(`保存成功`),
				icon: 'success'
			});
		},
		fail: () => {
			// #ifdef MP
			uni.getSetting({
				success(res) {
					if (!res.authSetting['scope.writePhotosAlbum']) {
						uni.authorize({
							scope: 'scope.writePhotosAlbum',
							success() {
								uni.saveImageToPhotosAlbum({
									filePath: url,
									success: () =>
										util.Tips({
											title: i18n.t(`保存成功`),
											icon: 'success'
										}),
									fail: () =>
										util.Tips({
											title: i18n.t(`保存失败`)
										})
								});
							},
							fail: () =>
								util.Tips({
									title: i18n.t(`保存失败`)
								})
						});
						return;
					}
					util.Tips({
						title: i18n.t(`保存失败`)
					});
				}
			});
			// #endif
			// #ifndef MP
			util.Tips({
				title: i18n.t(`保存失败`)
			});
			// #endif
		}
	});
}

function setShareInfoStatus() {
	// #ifdef H5
	if (!Auth.isWeixin()) return;
	if (!isLogin.value) {
		toLogin();
		return;
	}
	getUserInfo().then((res) => {
		Auth.wechatEvevt(['updateAppMessageShareData', 'updateTimelineShareData'], {
			desc: i18n.t(`分销海报`),
			title: (res.data.nickname || '') + '-' + i18n.t(`分销海报`),
			link: '/pages/index/index?spid=' + res.data.uid,
			imgUrl: posterImage.value[0] || ''
		});
	});
	// #endif
}
</script>

<style lang="scss">
page {
	background-color: #a3a3a3 !important;
}

/* 须留在视口内（小程序离屏 canvas 导出常失败），仅透明隐藏 */
.gen-mask {
	position: fixed;
	inset: 0;
	z-index: 1000;
	background: rgba(0, 0, 0, 0.35);
	display: flex;
	align-items: center;
	justify-content: center;
}

.gen-box {
	min-width: 240rpx;
	padding: 28rpx 36rpx;
	border-radius: 12rpx;
	background: rgba(0, 0, 0, 0.7);
	color: #fff;
	font-size: 28rpx;
	text-align: center;
}

.poster-canvas {
	position: fixed;
	left: 0;
	top: 0;
	opacity: 0;
	pointer-events: none;
	z-index: -1;
}

.qrimg {
	position: fixed;
	left: 0;
	top: 0;
	width: 1px;
	height: 1px;
	opacity: 0;
	overflow: hidden;
	pointer-events: none;
	z-index: -1;
}

.box {
	width: 100%;
	height: 100%;
	border-radius: 18rpx;
	overflow: hidden;
	display: flex;
	align-items: center;
	justify-content: center;

	.poster-img {
		width: 100%;
		height: 100%;
	}
}

.distribution-posters swiper {
	width: 100%;
	height: 1000rpx;
	position: relative;
	margin-top: 40rpx;
}

.distribution-posters :deep(.active) {
	transform: none;
	transition: all 0.2s ease-in 0s;
}

.distribution-posters :deep(.quiet) {
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
