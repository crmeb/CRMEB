<template>
	<view class="posterCon" :style="colorStyle">
		<view class="poster-poster">
			<view class="tip">
				<text class="iconfont icon-shuoming"></text>
				{{ $t(`提示：点击图片即可保存至手机相册`) }}
			</view>
			<view class="pictrue">
				<!-- <image :src='image' mode="widthFix"></image> -->
				<image class="canvas" :src="posterImage" v-if="posterImage" @click="savePosterPathMp(posterImage)"></image>
				<canvas class="canvas" canvas-id="myCanvas" v-else></canvas>
			</view>
		</view>
		<!-- #ifdef H5 || APP-PLUS -->
		<zb-code
			ref="qrcodes"
			:show="codeShow"
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
		<!-- #endif -->
	</view>
</template>

<script setup>
import zbCode from '@/components/zb-code/zb-code.vue';
import { getBargainPoster, getCombinationPoster, getBargainPosterData, getCombinationPosterData } from '@/api/activity.js';
import { getUserInfo, imgToBase as imgToBaseApi, routineCode as routineCodeApi } from '@/api/user.js';
// #ifdef APP-PLUS
import { TOKENNAME, HTTP_REQUEST_URL } from '@/config/app.js';
// #endif
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import { ref, nextTick, onMounted, getCurrentInstance } from 'vue';
import { onLoad, onReady } from '@dcloudio/uni-app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';

// 组件使用 props 传参
const props = defineProps({
	comType: {
		type: String,
		default: '0'
	},
	comId: {
		type: String,
		default: '0'
	},
	comBargain: {
		type: String,
		default: '0'
	}
});

const emit = defineEmits(['getPosterImgae']);

const { colorStyle } = useColorTheme();

const appStore = useAppStore();
const uidFromStore = () => appStore.uid;

const instance = getCurrentInstance();

// ---------- data ----------
const canvasStatus = ref(true);
const posterImage = ref('');
const parameter = {
	navbar: '1',
	return: '1',
	title: i18n.t(`拼团海报`),
	color: true,
	class: '0'
};
const type = ref(0);
const id = ref(0);
const bargain = ref(0);
const image = ref('');
const from = ref('');
const uid = ref('');
//二维码参数
const codeShow = ref(false);
const cid = ref('1');
const ifShow = ref(true);
const val = ref(''); // 要生成的二维码值
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
const codeSrc = ref('');
const wd = ref(0);
const hg = ref(0);
const posterBag = '../static/posterBag.png';
const mpUrl = ref('');

// ---------- methods ----------
async function getPosterInfo() {
	var url = '';
	let data = {
		id: id.value,
		from: from.value
	};
	let userData = await getUserInfo();
	uid.value = userData.data.uid;
	let goods_img, mp_code, resData, arr, mpUrlTmp;
	uni.showLoading({
		title: i18n.t(`海报生成中`),
		mask: true
	});
	if (type.value == 1) {
		await getBargainPosterData(id.value)
			.then((res) => {
				resData = res.data;
			})
			.catch((err) => {
				util.Tips({
					title: i18n.t(`海报图片获取失败`)
				});
				return;
			});
	} else {
		await getCombinationPosterData(id.value)
			.then((res) => {
				resData = res.data;
			})
			.catch((err) => {
				util.Tips({
					title: i18n.t(`海报图片获取失败`)
				});
				return;
			});
	}

	// #ifdef H5 || APP-PLUS
	let imgData = await imgToBase(resData.image, resData.url);
	arr = [posterBag, imgData.image, imgData.code || codeSrc.value];
	// #endif
	// #ifdef MP
	resData.image = setDomain(resData.image);
	mpUrlTmp = resData.url ? await downloadFilestoreImage(resData.url) : await downloadFilestoreImage(mpUrl.value);
	arr = [posterBag, await downloadFilestoreImage(resData.image), mpUrlTmp];
	// #endif
	nextTick((e) => {
		util.bargainPosterCanvas(arr, resData.title, resData.label, resData.msg, resData.price, wd.value, hg.value, (tempFilePath) => {
			posterImage.value = tempFilePath;
			emit('getPosterImgae', tempFilePath);
		});
	});
}
async function routineCode() {
	let res = await routineCodeApi();
	mpUrl.value = res.data.url;
}
//图片转符合安全域名路径
function downloadFilestoreImage(url) {
	return new Promise((resolve, reject) => {
		uni.downloadFile({
			url: url,
			success: function (res) {
				resolve(res.tempFilePath);
			},
			fail: function () {
				return util.Tips({
					title: ''
				});
			}
		});
	});
}
//替换安全域名
function setDomain(url) {
	url = url ? url.toString() : '';
	//本地调试打开,生产请注销
	if (url.indexOf('https://') > -1) return url;
	else return url.replace('http://', 'https://');
}
async function imgToBase(image, url) {
	let res = await imgToBaseApi({
		image: image,
		code: url
	});

	return res.data;
}
function downloadImg() {}
function savePosterPathMp(url) {
	// #ifdef APP-PLUS
	uni.saveImageToPhotosAlbum({
		filePath: url,
		success: function (res) {
			util.Tips({
				title: i18n.t(`保存成功`),
				icon: 'success'
			});
		},
		fail: function (res) {
			util.Tips({
				title: i18n.t(`保存失败`)
			});
		}
	});
	// #endif
	// #ifdef MP
	uni.getSetting({
		success(res) {
			if (!res.authSetting['scope.writePhotosAlbum']) {
				uni.authorize({
					scope: 'scope.writePhotosAlbum',
					success() {
						uni.saveImageToPhotosAlbum({
							filePath: url,
							success: function (res) {
								util.Tips({
									title: i18n.t(`保存成功`),
									icon: 'success'
								});
							},
							fail: function (res) {
								util.Tips({
									title: i18n.t(`保存失败`)
								});
							}
						});
					},
					fail: function (res) {
						util.Tips({
							title: i18n.t(`保存失败`)
						});
					}
				});
			} else {
				uni.saveImageToPhotosAlbum({
					filePath: url,
					success: function (res) {
						util.Tips({
							title: i18n.t(`保存成功`),
							icon: 'success'
						});
					},
					fail: function (res) {
						util.Tips({
							title: i18n.t(`保存失败`)
						});
					}
				});
			}
		}
	});
	// #endif
	// #ifdef H5
	// 创建隐藏的可下载链接
	var eleLink = document.createElement('a');
	eleLink.download = i18n.t(`海报`);
	eleLink.href = url;
	// 触发点击
	document.body.appendChild(eleLink);
	eleLink.click();
	// #endif
}
function qrR(res) {
	codeSrc.value = res;
}
// #ifdef MP
function savePosterPath() {
	uni.getSetting({
		success(res) {
			if (!res.authSetting['scope.writePhotosAlbum']) {
				uni.authorize({
					scope: 'scope.writePhotosAlbum',
					success() {
						uni.saveImageToPhotosAlbum({
							filePath: posterImage.value,
							success: function (res) {
								posterImageClose();
								util.Tips({
									title: i18n.t(`保存成功`),
									icon: 'success'
								});
							},
							fail: function (res) {
								util.Tips({
									title: i18n.t(`保存失败`)
								});
							}
						});
					}
				});
			} else {
				uni.saveImageToPhotosAlbum({
					filePath: posterImage.value,
					success: function (res) {
						posterImageClose();
						util.Tips({
							title: i18n.t(`保存成功`),
							icon: 'success'
						});
					},
					fail: function (res) {
						util.Tips({
							title: i18n.t(`保存失败`)
						});
					}
				});
			}
		}
	});
}
// #endif

// ---------- 生命周期 ----------
onLoad((options) => {
	if (props.comType != 1) {
		// #ifdef MP
		from.value = 'routine';
		// #endif
		// #ifdef H5 || APP-PLUS
		from.value = 'wechat';
		// #endif

		if (options.hasOwnProperty('type') && options.hasOwnProperty('id')) {
			type.value = options.type;
			id.value = options.id;
			if (options.type == 1) {
				bargain.value = options.bargain;
				uni.setNavigationBarTitle({
					title: i18n.t(`砍价海报`)
				});
			} else {
				uni.setNavigationBarTitle({
					title: i18n.t(`拼团海报`)
				});
			}
		} else {
			return app.Tips(
				{
					title: i18n.t(`参数错误`),
					icon: 'none'
				},
				{
					tab: 3,
					url: 1
				}
			);
		}
	}
});

onMounted(() => {
	if (props.comType == 1) {
		// #ifdef MP
		from.value = 'routine';
		// #endif
		// #ifdef H5 || APP-PLUS
		from.value = 'wechat';
		// #endif

		uni.setNavigationBarTitle({
			title: i18n.t(`砍价海报`)
		});
		type.value = props.comType;
		id.value = props.comId;
		bargain.value = props.comBargain;

		// #ifdef H5
		val.value =
			window.location.origin + '/pages/activity/goods_bargain_details/index?id=' + id.value + '&bargain=' + uidFromStore() + '&spread=' + uidFromStore();
		// #endif
		// #ifdef APP-PLUS
		val.value = HTTP_REQUEST_URL + '/pages/activity/goods_bargain_details/index?id=' + id.value + '&bargain=' + uidFromStore() + '&spread=' + uidFromStore();
		// #endif

		nextTick(() => {
			uni
				.createSelectorQuery()
				.in(instance.proxy)
				.select('.pictrue')
				.boundingClientRect((data) => {
					wd.value = data.width;
					hg.value = data.height;
				})
				.exec();
		});
		routineCode();
		setTimeout((e) => {
			getPosterInfo();
		}, 200);
	}
});

onReady(() => {
	if (props.comType != 1) {
		// #ifdef H5
		if (type.value == 1) {
			val.value =
				window.location.origin + '/pages/activity/goods_bargain_details/index?id=' + id.value + '&bargain=' + uidFromStore() + '&spread=' + uidFromStore();
		} else if (type.value == 2) {
			val.value = window.location.origin + '/pages/activity/goods_combination_status/index?id=' + id.value + '&spread=' + uidFromStore();
		}
		// #endif
		// #ifdef APP-PLUS
		if (type.value == 1) {
			val.value =
				HTTP_REQUEST_URL + '/pages/activity/goods_bargain_details/index?id=' + id.value + '&bargain=' + uidFromStore() + '&spread=' + uidFromStore();
		} else if (type.value == 2) {
			val.value = HTTP_REQUEST_URL + '/pages/activity/goods_combination_status/index?id=' + id.value + '&spread=' + uidFromStore();
		}
		// #endif
		setTimeout((e) => {}, 200);
		nextTick(function () {
			let selector = uni.createSelectorQuery().select('.pictrue');
			selector
				.fields(
					{
						size: true
					},
					(data) => {
						wd.value = data.width;
						hg.value = data.height;
						getPosterInfo();
					}
				)
				.exec();
		});
		routineCode();
	}
});
</script>

<style>
.posterCon {
	position: fixed;
	top: 0;
	width: 100%;
	left: 0;
	height: 100%;
	background-color: var(--view-theme);
	bottom: 0;
	overflow-y: auto;
}

.poster-poster .tip {
	height: 80rpx;
	font-size: 26rpx;
	color: #e8c787;
	text-align: center;
	line-height: 80rpx;
	user-select: none;
}

.poster-poster .tip .iconfont {
	font-size: 36rpx;
	vertical-align: -4rpx;
	margin-right: 18rpx;
}

.canvas {
	width: 100%;
	height: 1100rpx;
}

.poster-poster .pictrue {
	width: 700rpx;
	/* height: 100%; */
	margin: 0 auto 50rpx auto;
	display: flex;
	justify-content: center;
}

.poster-poster .pictrue image {
	width: 100%;
	/* height: 100%; */
}
</style>
