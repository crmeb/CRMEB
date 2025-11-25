// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2021 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
import {
	imageBase64
} from "@/api/public";
import {
	getProductCode, // 普通商品小程序code
} from "@/api/store.js";
import {
	scombinationCode, // 拼团code
	seckillCode // 秒杀
} from '@/api/activity.js';
import i18n from '../utils/lang.js';
let sysHeight = uni.getSystemInfoSync().statusBarHeight + 'px';
export const sharePoster = {
	data() {
		return {
			//二维码参数
			codeShow: false,
			cid: '1',
			codeVal: "", // 要生成的二维码值
			size: 200, // 二维码大小
			unit: 'upx', // 单位
			background: '#FFF', // 背景色
			foreground: '#000', // 前景色
			pdground: '#000', // 角标色
			codeIcon: '', // 二维码图标
			iconsize: 40, // 二维码图标大小
			lv: 3, // 二维码容错级别 ， 一般不用设置，默认就行
			onval: true, // val值变化时自动重新生成二维码
			loadMake: true, // 组件加载完成后自动生成二维码
			base64Show: 0,
			shareQrcode: 0,
			followCode: '',
			selectSku: {},
			currentPage: false,
			sysHeight: sysHeight,
			isShow: 0,
			storeImageBase64: ''
		};
	},
	methods: {
		qrR(res) {
			// #ifdef H5
			if (!this.$wechat.isWeixin() || this.shareQrcode != '1') {
				this.PromotionCode = res;
				this.followCode = ''
			}
			// #endif
			// #ifdef APP-PLUS
			this.PromotionCode = res;
			// #endif
		},
		getImageBase64() {
			let that = this;
			imageBase64(that.storeImage, this.storeInfo.wechat_code)
				.then((res) => {
					that.storeImageBase64 = res.data.image;
					if (this.storeInfo.wechat_code) {
						that.PromotionCode = res.data.code;
					}
				})
				.catch(() => {});
		},
		initPoster(arr2) {
			let that = this;
			uni.getImageInfo({
				src: that.PromotionCode,
				success() {
					if (arr2[2] == "") {
						//海报二维码不存在则从新下载
						that.downloadFilePromotionCode(function(
							msgPromotionCode) {
							arr2[2] = msgPromotionCode;
							if (arr2[2] == "")
								return that.$util.Tips({
									title: i18n.t(
										`海报二维码生成失败`
									),
								});
							that.$util.PosterCanvas(
								arr2,
								that.storeInfo.store_name,
								that.storeInfo.price,
								that.storeInfo.ot_price,
								function(tempFilePath) {
									that.$set(that,
										"posterImage",
										tempFilePath);
									that.$set(that,
										"posterImageStatus",
										true);
									that.$set(that,
										"canvasStatus",
										false);
									that.$set(that,
										"actionSheetHidden",
										!that
										.actionSheetHidden
									);
								}
							);
						});
					} else {
						//生成推广海报
						that.$nextTick(e => {
							that.$util.PosterCanvas(
								arr2,
								that.storeInfo.store_name,
								that.storeInfo.price,
								that.storeInfo.ot_price,
								function(tempFilePath) {
									that.$set(that,
										"posterImage",
										tempFilePath);
									that.$set(that,
										"posterImageStatus",
										true);
									that.$set(that,
										"canvasStatus",
										false);
									that.$set(that,
										"actionSheetHidden",
										!that
										.actionSheetHidden
									);
								}
							);
						})

					}
				},
				fail: function(res) {
					// #ifdef H5
					return that.$util.Tips({
						title: res,
					});
					// #endif
					// #ifdef MP
					return that.$util.Tips({
						title: i18n.t(`正在下载海报,请稍后再试`),
					});
					// #endif
				},
			});
		},
		/**
		 * 生成海报
		 */
		async goPoster(type) {
			let that = this;
			that.posters = false;
			that.$set(that, "canvasStatus", true);
			let arr2
			// #ifdef MP
			let met = type === 'scombination' ? scombinationCode(that.id) : type === 'seckill' ? seckillCode(
				that
				.id,{time_id:this.time_id}) : getProductCode(that.id)
			met.then((res) => {
					uni.downloadFile({
						url: that.setDomain(res.data.code),
						success: function(res) {
							that.$set(that, "isDown", false);
							that.$set(that, "PromotionCode", res.tempFilePath)
							if (typeof successFn == "function")
								successFn && successFn(res.tempFilePath);
							arr2 = [that.posterbackgd, that.storeImage, that.PromotionCode];
							that.initPoster(arr2)
						},
						fail: function() {
							that.$set(that, "isDown", false);
							that.$set(that, "PromotionCode", "");
						},
					});
				})
				.catch((err) => {
					that.$set(that, "isDown", false);
					that.$set(that, "PromotionCode", "");
					return that.$util.Tips({
						title: err,
					});
				});
			// #endif
			// #ifdef H5 || APP-PLUS
			arr2 = [that.posterbackgd, that.storeImageBase64, that.PromotionCode];
			if (!that.storeImageBase64)
				return that.$util.Tips({
					title: i18n.t(`正在下载海报,请稍后再试`),
				});
			that.initPoster(arr2)
			// #endif
		},
		//替换安全域名
		setDomain(url) {
			url = url ? url.toString() : "";
			//本地调试打开,生产请注销
			if (url.indexOf("https://") > -1) return url;
			else return url.replace("http://", "https://");
		},
		//获取海报产品图
		downloadFilestoreImage() {
			let that = this;
			uni.downloadFile({
				url: that.setDomain(that.storeInfo.image),
				success: function(res) {
					that.storeImage = res.tempFilePath;
					that.storeImageBase64 = res.tempFilePath;
				},
				fail: function() {
					return that.$util.Tips({
						title: "",
					});
					that.storeImage = "";
				},
			});
		},
		/**
		 * 获取产品分销二维码
		 * @param function successFn 下载完成回调
		 *
		 */
		downloadFilePromotionCode(successFn) {
			let that = this;
			// #ifdef MP
			getProductCode(that.id)
				.then((res) => {
					uni.downloadFile({
						url: that.setDomain(res.data.code),
						success: function(res) {
							that.$set(that, "isDown", false);
							that.$set(that, "PromotionCode", res.tempFilePath)
							if (typeof successFn == "function")
								successFn && successFn(res.tempFilePath);
						},
						fail: function() {
							that.$set(that, "isDown", false);
							that.$set(that, "PromotionCode", "");
						},
					});
				})
				.catch((err) => {
					that.$set(that, "isDown", false);
					that.$set(that, "PromotionCode", "");
					return that.$util.Tips({
						title: err,
					});
				});
			// #endif
			// #ifdef APP-PLUS
			uni.downloadFile({
				url: that.setDomain(that.PromotionCode),
				success: function(res) {
					that.$set(that, "isDown", false);
					if (typeof successFn == "function")
						successFn && successFn(res.tempFilePath);
					else that.$set(that, "PromotionCode", res.tempFilePath);
				},
				fail: function() {
					that.$set(that, "isDown", false);
					that.$set(that, "PromotionCode", "");
				},
			});
			// #endif
		},
	}
};