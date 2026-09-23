// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2026 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不可去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

import { ref, nextTick } from 'vue';
import { imageBase64 } from '@/api/public';
import { getProductCode } from '@/api/store.js'; // 普通商品小程序code
import { scombinationCode, seckillCode } from '@/api/activity.js'; // 拼团/秒杀
import i18n from '@/utils/lang.js';
import util from '@/utils/util.js';
// #ifdef H5
import wechat from '@/libs/wechat.js';
// #endif

const sysHeight = uni.getWindowInfo().statusBarHeight + 'px';

/**
 * 商品海报/分销二维码 composable（替代 mixins/sharePoster.js）
 *
 * 原 mixin 把海报状态写到各页面 this 上；本 composable 自持全部海报状态，
 * 页面解构后在模板直接使用（posterImageStatus / canvasStatus / PromotionCode 等）。
 *
 * 用法：
 *   const {
 *     posterImageStatus, canvasStatus, posterImage, PromotionCode,
 *     posters, actionSheetHidden, codeShow, codeVal, sysHeight, storeImageBase64,
 *     qrR, getImageBase64, goPoster, downloadFilestoreImage, downloadFilePromotionCode,
 *   } = useSharePoster(() => ({
 *     id: idRef.value,              // 商品/活动 id
 *     timeId: timeIdRef.value,      // 秒杀场次 id（可空）
 *     storeInfo: storeInfoRef.value,// { store_name, price, ot_price, image, wechat_code }
 *     posterbackgd: posterbackgdRef.value, // 海报背景图地址
 *   }));
 *
 * @param {Function} ctxGetter 返回页面侧输入的 getter（每次调用取最新值）
 */
export function useSharePoster(ctxGetter) {
	// —— 页面侧共享输出（原 mixin 写到页面 this 上的字段）——
	const posters = ref(false);
	const canvasStatus = ref(false);
	const isDown = ref(false);
	const PromotionCode = ref('');
	const posterImage = ref('');
	const posterImageStatus = ref(false);
	const actionSheetHidden = ref(false);
	const storeImage = ref('');
	const storeImageBase64 = ref('');

	// —— 二维码组件参数（原 mixin data）——
	const codeShow = ref(false);
	const cid = ref('1');
	const codeVal = ref(''); // 要生成的二维码值
	const size = ref(200); // 二维码大小
	const unit = ref('upx'); // 单位
	const background = ref('#FFF'); // 背景色
	const foreground = ref('#000'); // 前景色
	const pdground = ref('#000'); // 角标色
	const codeIcon = ref(''); // 二维码图标
	const iconsize = ref(40); // 二维码图标大小
	const lv = ref(3); // 二维码容错级别 ， 一般不用设置，默认就行
	const onval = ref(true); // val值变化时自动重新生成二维码
	const loadMake = ref(true); // 组件加载完成后自动生成二维码
	const base64Show = ref(0);
	const shareQrcode = ref(0);
	const followCode = ref('');
	const selectSku = ref({});
	const currentPage = ref(false);
	const isShow = ref(0);

	const ctx = () => ctxGetter() || {};

	function qrR(res) {
		// #ifdef H5
		if (!wechat.isWeixin() || shareQrcode.value != '1') {
			PromotionCode.value = res;
			followCode.value = '';
		}
		// #endif
		// #ifdef APP-PLUS
		PromotionCode.value = res;
		// #endif
	}

	function getImageBase64() {
		const info = ctx().storeInfo || {};
		imageBase64(storeImage.value, info.wechat_code)
			.then((res) => {
				storeImageBase64.value = res.data.image;
				if (info.wechat_code) {
					PromotionCode.value = res.data.code;
				}
			})
			.catch(() => {});
	}

	function initPoster(arr2) {
		const info = ctx().storeInfo || {};
		uni.getImageInfo({
			src: PromotionCode.value,
			success() {
				if (arr2[2] == '') {
					//海报二维码不存在则从新下载
					downloadFilePromotionCode(function (msgPromotionCode) {
						arr2[2] = msgPromotionCode;
						if (arr2[2] == '')
							return util.Tips({
								title: i18n.t(`海报二维码生成失败`),
							});
						util.PosterCanvas(
							arr2,
							info.store_name,
							info.price,
							info.ot_price,
							function (tempFilePath) {
								posterImage.value = tempFilePath;
								posterImageStatus.value = true;
								canvasStatus.value = false;
								actionSheetHidden.value = !actionSheetHidden.value;
							}
						);
					});
				} else {
					//生成推广海报
					nextTick((e) => {
						util.PosterCanvas(
							arr2,
							info.store_name,
							info.price,
							info.ot_price,
							function (tempFilePath) {
								posterImage.value = tempFilePath;
								posterImageStatus.value = true;
								canvasStatus.value = false;
								actionSheetHidden.value = !actionSheetHidden.value;
							}
						);
					});
				}
			},
			fail: function (res) {
				// #ifdef H5
				return util.Tips({
					title: res,
				});
				// #endif
				// #ifdef MP
				return util.Tips({
					title: i18n.t(`正在下载海报,请稍后再试`),
				});
				// #endif
			},
		});
	}

	/**
	 * 生成海报
	 */
	async function goPoster(type) {
		posters.value = false;
		canvasStatus.value = true;
		let arr2;
		// #ifdef MP
		const c = ctx();
		let met =
			type === 'scombination'
				? scombinationCode(c.id)
				: type === 'seckill'
				? seckillCode(c.id, { time_id: c.timeId })
				: getProductCode(c.id);
		met
			.then((res) => {
				uni.downloadFile({
					url: setDomain(res.data.code),
					success: function (res) {
						isDown.value = false;
						PromotionCode.value = res.tempFilePath;
						arr2 = [ctx().posterbackgd, storeImage.value, PromotionCode.value];
						initPoster(arr2);
					},
					fail: function () {
						isDown.value = false;
						PromotionCode.value = '';
					},
				});
			})
			.catch((err) => {
				isDown.value = false;
				PromotionCode.value = '';
				return util.Tips({
					title: err,
				});
			});
		// #endif
		// #ifdef H5 || APP-PLUS
		arr2 = [ctx().posterbackgd, storeImageBase64.value, PromotionCode.value];
		if (!storeImageBase64.value)
			return util.Tips({
				title: i18n.t(`正在下载海报,请稍后再试`),
			});
		initPoster(arr2);
		// #endif
	}

	//替换安全域名
	function setDomain(url) {
		url = url ? url.toString() : '';
		//本地调试打开,生产请注销
		if (url.indexOf('https://') > -1) return url;
		else return url.replace('http://', 'https://');
	}

	//获取海报产品图
	function downloadFilestoreImage() {
		const info = ctx().storeInfo || {};
		uni.downloadFile({
			url: setDomain(info.image),
			success: function (res) {
				storeImage.value = res.tempFilePath;
				storeImageBase64.value = res.tempFilePath;
			},
			fail: function () {
				return util.Tips({
					title: '',
				});
				storeImage.value = '';
			},
		});
	}

	/**
	 * 获取产品分销二维码
	 * @param function successFn 下载完成回调
	 *
	 */
	function downloadFilePromotionCode(successFn) {
		// #ifdef MP
		getProductCode(ctx().id)
			.then((res) => {
				uni.downloadFile({
					url: setDomain(res.data.code),
					success: function (res) {
						isDown.value = false;
						PromotionCode.value = res.tempFilePath;
						if (typeof successFn == 'function')
							successFn && successFn(res.tempFilePath);
					},
					fail: function () {
						isDown.value = false;
						PromotionCode.value = '';
					},
				});
			})
			.catch((err) => {
				isDown.value = false;
				PromotionCode.value = '';
				return util.Tips({
					title: err,
				});
			});
		// #endif
		// #ifdef APP-PLUS
		uni.downloadFile({
			url: setDomain(PromotionCode.value),
			success: function (res) {
				isDown.value = false;
				if (typeof successFn == 'function')
					successFn && successFn(res.tempFilePath);
				else PromotionCode.value = res.tempFilePath;
			},
			fail: function () {
				isDown.value = false;
				PromotionCode.value = '';
			},
		});
		// #endif
	}

	return {
		// 页面共享状态
		posters,
		canvasStatus,
		isDown,
		PromotionCode,
		posterImage,
		posterImageStatus,
		actionSheetHidden,
		storeImage,
		storeImageBase64,
		// 二维码参数
		codeShow,
		cid,
		codeVal,
		size,
		unit,
		background,
		foreground,
		pdground,
		codeIcon,
		iconsize,
		lv,
		onval,
		loadMake,
		base64Show,
		shareQrcode,
		followCode,
		selectSku,
		currentPage,
		sysHeight,
		isShow,
		// 方法
		qrR,
		getImageBase64,
		initPoster,
		goPoster,
		setDomain,
		downloadFilestoreImage,
		downloadFilePromotionCode,
	};
}
