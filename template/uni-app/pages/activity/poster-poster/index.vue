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

<script>
import zbCode from '@/components/zb-code/zb-code.vue';
import { getBargainPoster, getCombinationPoster, getBargainPosterData, getCombinationPosterData } from '@/api/activity.js';
import { getUserInfo, imgToBase, routineCode } from '@/api/user.js';
// #ifdef APP-PLUS
import { TOKENNAME, HTTP_REQUEST_URL } from '@/config/app.js';
// #endif
import colors from '@/mixins/color.js';
export default {
	components: {
		zbCode
	},
	mixins: [colors],
	// 组件使用 props 传参
	props: {
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
	},
	data() {
		return {
			canvasStatus: true,
			posterImage: '',
			parameter: {
				navbar: '1',
				return: '1',
				title: this.$t(`拼团海报`),
				color: true,
				class: '0'
			},
			type: 0,
			id: 0,
			bargain: 0,
			image: '',
			from: '',
			uid: '',
			//二维码参数
			codeShow: false,
			cid: '1',
			ifShow: true,
			val: '', // 要生成的二维码值
			size: 200, // 二维码大小
			unit: 'upx', // 单位
			background: '#FFF', // 背景色
			foreground: '#000', // 前景色
			pdground: '#000', // 角标色
			icon: '', // 二维码图标
			iconsize: 40, // 二维码图标大小
			lv: 3, // 二维码容错级别 ， 一般不用设置，默认就行
			onval: true, // val值变化时自动重新生成二维码
			loadMake: true, // 组件加载完成后自动生成二维码
			src: '', // 二维码生成后的图片地址或base64
			codeSrc: '',
			wd: 0,
			hg: 0,
			posterBag: '../static/posterBag.png',
			mpUrl: ''
		};
	},
	onLoad(options) {
		if (this.comType != 1) {
			// #ifdef MP
			this.from = 'routine';
			// #endif
			// #ifdef H5 || APP-PLUS
			this.from = 'wechat';
			// #endif

			var that = this;
			if (options.hasOwnProperty('type') && options.hasOwnProperty('id')) {
				this.type = options.type;
				this.id = options.id;
				if (options.type == 1) {
					this.bargain = options.bargain;
					uni.setNavigationBarTitle({
						title: that.$t(`砍价海报`)
					});
				} else {
					uni.setNavigationBarTitle({
						title: that.$t(`拼团海报`)
					});
				}
			} else {
				return app.Tips(
					{
						title: that.$t(`参数错误`),
						icon: 'none'
					},
					{
						tab: 3,
						url: 1
					}
				);
			}
		}
	},
	mounted() {
		if (this.comType == 1) {
			// #ifdef MP
			this.from = 'routine';
			// #endif
			// #ifdef H5 || APP-PLUS
			this.from = 'wechat';
			// #endif

			var that = this;
			uni.setNavigationBarTitle({
				title: that.$t(`砍价海报`)
			});
			this.type = this.comType;
			this.id = this.comId;
			this.bargain = this.comBargain;

			// #ifdef H5
			this.val =
				window.location.origin + '/pages/activity/goods_bargain_details/index?id=' + this.id + '&bargain=' + this.$store.state.app.uid + '&spread=' + this.$store.state.app.uid;
			// #endif
			// #ifdef APP-PLUS
			this.val = HTTP_REQUEST_URL + '/pages/activity/goods_bargain_details/index?id=' + this.id + '&bargain=' + this.$store.state.app.uid + '&spread=' + this.$store.state.app.uid;
			// #endif

			this.$nextTick(() => {
				uni
					.createSelectorQuery()
					.in(this)
					.select('.pictrue')
					.boundingClientRect((data) => {
						this.wd = data.width;
						this.hg = data.height;
					})
					.exec();
			});
			this.routineCode();
			setTimeout((e) => {
				this.getPosterInfo();
			}, 200);
		}
	},
	onReady() {
		if (this.comType != 1) {
			// #ifdef H5
			if (this.type == 1) {
				this.val =
					window.location.origin + '/pages/activity/goods_bargain_details/index?id=' + this.id + '&bargain=' + this.$store.state.app.uid + '&spread=' + this.$store.state.app.uid;
			} else if (this.type == 2) {
				this.val = window.location.origin + '/pages/activity/goods_combination_status/index?id=' + this.id + '&spread=' + this.$store.state.app.uid;
			}
			// #endif
			// #ifdef APP-PLUS
			if (this.type == 1) {
				this.val =
					HTTP_REQUEST_URL + '/pages/activity/goods_bargain_details/index?id=' + this.id + '&bargain=' + this.$store.state.app.uid + '&spread=' + this.$store.state.app.uid;
			} else if (this.type == 2) {
				this.val = HTTP_REQUEST_URL + '/pages/activity/goods_combination_status/index?id=' + this.id + '&spread=' + this.$store.state.app.uid;
			}
			// #endif
			setTimeout((e) => {}, 200);
			this.$nextTick(function () {
				let selector = uni.createSelectorQuery().select('.pictrue');
				selector
					.fields(
						{
							size: true
						},
						(data) => {
							this.wd = data.width;
							this.hg = data.height;
							this.getPosterInfo();
						}
					)
					.exec();
			});
			this.routineCode();
		}
	},
	methods: {
		async getPosterInfo() {
			var that = this,
				url = '';
			let data = {
				id: that.id,
				from: that.from
			};
			let userData = await getUserInfo();
			this.uid = userData.data.uid;
			let goods_img, mp_code, resData, arr, mpUrl;
			uni.showLoading({
				title: that.$t(`海报生成中`),
				mask: true
			});
			if (that.type == 1) {
				await getBargainPosterData(that.id)
					.then((res) => {
						resData = res.data;
					})
					.catch((err) => {
						that.$util.Tips({
							title: that.$t(`海报图片获取失败`)
						});
						return;
					});
			} else {
				await getCombinationPosterData(that.id)
					.then((res) => {
						resData = res.data;
					})
					.catch((err) => {
						that.$util.Tips({
							title: that.$t(`海报图片获取失败`)
						});
						return;
					});
			}

			// #ifdef H5 || APP-PLUS
			let imgData = await this.imgToBase(resData.image, resData.url);
			arr = [this.posterBag, imgData.image, imgData.code || this.codeSrc];
			// #endif
			// #ifdef MP
			resData.image = that.setDomain(resData.image);
			mpUrl = resData.url ? await this.downloadFilestoreImage(resData.url) : await this.downloadFilestoreImage(this.mpUrl);
			arr = [this.posterBag, await this.downloadFilestoreImage(resData.image), mpUrl];
			// #endif
			this.$nextTick((e) => {
				that.$util.bargainPosterCanvas(arr, resData.title, resData.label, resData.msg, resData.price, this.wd, this.hg, (tempFilePath) => {
					this.posterImage = tempFilePath;
					this.$emit('getPosterImgae', tempFilePath);
				});
			});
		},
		async routineCode() {
			let res = await routineCode();
			this.mpUrl = res.data.url;
		},
		//图片转符合安全域名路径
		downloadFilestoreImage(url) {
			return new Promise((resolve, reject) => {
				let that = this;
				uni.downloadFile({
					url: url,
					success: function (res) {
						resolve(res.tempFilePath);
					},
					fail: function () {
						return that.$util.Tips({
							title: ''
						});
					}
				});
			});
		},
		//替换安全域名
		setDomain: function (url) {
			url = url ? url.toString() : '';
			//本地调试打开,生产请注销
			if (url.indexOf('https://') > -1) return url;
			else return url.replace('http://', 'https://');
		},
		async imgToBase(image, url) {
			let res = await imgToBase({
				image: image,
				code: url
			});

			return res.data;
		},
		downloadImg() {},
		savePosterPathMp(url) {
			let that = this;
			// #ifdef APP-PLUS
			uni.saveImageToPhotosAlbum({
				filePath: url,
				success: function (res) {
					that.$util.Tips({
						title: that.$t(`保存成功`),
						icon: 'success'
					});
				},
				fail: function (res) {
					that.$util.Tips({
						title: that.$t(`保存失败`)
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
										that.$util.Tips({
											title: that.$t(`保存成功`),
											icon: 'success'
										});
									},
									fail: function (res) {
										that.$util.Tips({
											title: that.$t(`保存失败`)
										});
									}
								});
							},
							fail: function (res) {
								that.$util.Tips({
									title: that.$t(`保存失败`)
								});
							}
						});
					} else {
						uni.saveImageToPhotosAlbum({
							filePath: url,
							success: function (res) {
								that.$util.Tips({
									title: that.$t(`保存成功`),
									icon: 'success'
								});
							},
							fail: function (res) {
								that.$util.Tips({
									title: that.$t(`保存失败`)
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
			eleLink.download = that.$t(`海报`);
			eleLink.href = url;
			// 触发点击
			document.body.appendChild(eleLink);
			eleLink.click();
			// #endif
		},
		qrR(res) {
			this.codeSrc = res;
		},
		// #ifdef MP
		savePosterPath: function () {
			let that = this;
			uni.getSetting({
				success(res) {
					if (!res.authSetting['scope.writePhotosAlbum']) {
						uni.authorize({
							scope: 'scope.writePhotosAlbum',
							success() {
								uni.saveImageToPhotosAlbum({
									filePath: that.posterImage,
									success: function (res) {
										that.posterImageClose();
										that.$util.Tips({
											title: that.$t(`保存成功`),
											icon: 'success'
										});
									},
									fail: function (res) {
										that.$util.Tips({
											title: that.$t(`保存失败`)
										});
									}
								});
							}
						});
					} else {
						uni.saveImageToPhotosAlbum({
							filePath: that.posterImage,
							success: function (res) {
								that.posterImageClose();
								that.$util.Tips({
									title: that.$t(`保存成功`),
									icon: 'success'
								});
							},
							fail: function (res) {
								that.$util.Tips({
									title: that.$t(`保存失败`)
								});
							}
						});
					}
				}
			});
		}
		// #endif
	}
};
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
