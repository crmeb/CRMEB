<template>
	<view class="aleart" v-if="aleartStatus" :style="'background-image: url(' + giftbag + ');'">
		<template v-if="!posterImageStatus">
			<text class="iconfont icon-cha2 close" @click="posterImageClose"></text>
			<view class="from">赠送给好友一份礼物</view>
			<view class="message">{{ giftData.message }}</view>
			<view class="aleart-body">
				<image class="goods-img" :src="giftData.image" mode=""></image>
			</view>
			<view class="title line1">
				{{ $t(giftData.title) }}
			</view>
			<!-- #ifdef H5 -->
			<view class="btn" @click="copyLink()">
				{{ $t('复制礼物链接') }}
			</view>
			<!-- #endif -->
			<!-- #ifndef H5 -->
			<button class="btn" open-type="share" hover-class="none">
				{{ $t(`送给好友`) }}
			</button>
			<!-- #endif -->
			<view class="btn-clear" @click="getPoster()">
				{{ $t('保存海报') }}
			</view>
		</template>
		<template v-if="posterImageStatus">
			<text class="iconfont icon-cha2 close" @click="posterImageClose"></text>
			<image class="poster-img" :src="posterImage"></image>
			<!-- #ifdef H5 -->
			<view class="keep">{{ $t(`长按图片可以保存到手机`) }}</view>
			<!-- #endif -->
		</template>
		<!-- #ifdef H5 || APP-PLUS -->
		<zb-code
			ref="qrcode"
			:show="codeShow"
			:cid="cid"
			:val="codeVal"
			:size="size"
			:unit="unit"
			:background="background"
			:foreground="foreground"
			:pdground="pdground"
			:icon="codeIcon"
			:iconSize="iconsize"
			:onval="onval"
			:loadMake="loadMake"
			@result="qrR"
		/>
		<!-- #endif -->
	</view>
</template>

<script>
import { HTTP_REQUEST_URL } from '@/config/app';
export default {
	data() {
		return {
			aleartData: {},
			bag: HTTP_REQUEST_URL + '/statics/images/canvas-bag.png',
			giftBorder: HTTP_REQUEST_URL + '/statics/images/gift-border.png',
			giftbag: HTTP_REQUEST_URL + '/statics/images/gift-bag.png',
			//二维码参数
			codeShow: false,
			cid: '1',
			codeVal: '', // 要生成的二维码值
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
			PromotionCode: '',
			posterImageStatus: false,
			posterImage: ''
		};
	},
	props: {
		giftData: {
			type: Object
		},
		aleartStatus: {
			type: Boolean,
			default: false
		}
	},
	watch: {
		aleartStatus(status) {
			if (!status) {
				this.aleartData = {};
			} else {
				// #ifdef H5
				this.codeVal = window.location.origin + '/pages/goods/receive_gift/index?id=' + this.giftData.id + '&spid=' + this.$store.state.app.uid;
				// #endif
				// #ifdef APP-PLUS
				this.codeVal = HTTP_REQUEST_URL + '/pages/goods/receive_gift/index?id=' + this.giftData.id + '&spid=' + this.$store.state.app.uid;
				// #endif
				// #ifdef MP
				this.PromotionCode = this.giftData.code;
				// #endif
			}
		}
	},
	methods: {
		copyLink() {
			uni.setClipboardData({
				data: this.codeVal
			});
		},
		qrR(res) {
			console.log(res);
			// #ifdef H5
			if (!this.$wechat.isWeixin() || this.shareQrcode != '1') {
				this.PromotionCode = res;
			}
			// #endif
			// #ifdef APP-PLUS
			this.PromotionCode = res;
			// #endif
		},
		//隐藏弹窗
		posterImageClose() {
			this.posterImageStatus = false
			this.$emit('close', false);
		},

		drawPoster(loadedImages, name, store_name) {
			// 截断标题函数
			function truncateTitle(title, maxLength) {
				if (title.length > maxLength) {
					return title.substring(0, maxLength) + '...';
				}
				return title;
			}
			// 获取canvas上下文
			const ctx = uni.createCanvasContext('posterCanvas');
			return new Promise(async (resolve, reject) => {
				uni.getImageInfo({
					src: loadedImages[0],
					success: (res) => {
						// 海报尺寸
						const posterWidth = 375;
						const posterHeight = 579;
						// const posterWidth = res.width / 2;
						// const posterHeight = res.height / 2;
						console.log(posterWidth, posterHeight);
						// 绘制背景图
						ctx.drawImage(loadedImages[0], 0, 0, posterWidth, posterHeight);
						ctx.save();
						// 头像和标题的布局

						const avatarSize = 22; // 头像尺寸
						const nickname = name; // 昵称
						const title = '赠送给好友一份礼物'; // 标题文字
						const titleFontSize = 14; // 标题字号
						const nicknameFontSize = 14; // 昵称字号
						const padding = 10; // 元素之间的间距
						// 计算标题宽度
						ctx.setFontSize(titleFontSize);
						const titleWidth = ctx.measureText(title).width;
						const nicknameWidth = ctx.measureText(nickname).width;
						// 计算头像和标题的总宽度
						const totalWidth = avatarSize + padding + nicknameWidth + padding + titleWidth;

						// 计算起始绘制位置（水平居中）
						const startX = (posterWidth - totalWidth) / 2;
						const startY = 77; // 距离顶部的距离

						// 绘制头像
						// ctx.drawImage(loadedImages[3], startX, startY, avatarSize, avatarSize);
						const avatarX = startX + avatarSize / 2; // 头像中心点 X
						const avatarY = startY + avatarSize / 2; // 头像中心点 Y
						ctx.save(); // 保存画布状态
						ctx.beginPath();
						ctx.arc(avatarX, avatarY, avatarSize / 2, 0, Math.PI * 2); // 绘制圆形路径
						ctx.clip(); // 裁剪圆形区域
						ctx.drawImage(loadedImages[3], startX, startY, avatarSize, avatarSize); // 绘制头像
						ctx.restore(); // 恢复画布状态

						// 绘制昵称
						ctx.setFontSize(nicknameFontSize);
						ctx.setTextAlign('left');
						ctx.fillText(nickname, startX + avatarSize + padding, startY + avatarSize - 5); // 调整文字垂直居中
						// 绘制标题
						ctx.setFontSize(titleFontSize);
						ctx.fillText(title, startX + avatarSize + padding + nicknameWidth + padding, startY + avatarSize - 5);

						// 商品图尺寸
						const productImageSize = 225; // 商品图尺寸为 225px x 225px

						// 绘制商品图边框
						const productBorderX = (posterWidth - productImageSize) / 2; // 水平居中
						const productBorderY = startY + avatarSize + 31; // 距离头像和标题的间距
						ctx.drawImage(loadedImages[1], productBorderX, productBorderY, productImageSize, productImageSize);

						// 绘制商品图
						const productImagePadding = 10; // 商品图与边框的内边距
						const productImageX = productBorderX + productImagePadding;
						const productImageY = productBorderY + productImagePadding + 11;
						const productImageInnerSize = productImageSize - 2 * productImagePadding; // 商品图实际绘制尺寸
						ctx.drawImage(loadedImages[2], productImageX, productImageY, productImageInnerSize, productImageInnerSize - 10);

						// 绘制商品标题
						const productTitle = store_name;
						const maxTitleLength = 20; // 标题最大长度
						const truncatedTitle = truncateTitle(productTitle, maxTitleLength); // 截断标题
						ctx.setFontSize(14);
						ctx.setTextAlign('center');
						ctx.fillText(truncatedTitle, posterWidth / 2, productBorderY + productImageSize + 26);
						// 绘制分享二维码
						const qrCodeSize = 100;
						const qrCodeX = (posterWidth - qrCodeSize) / 2;
						const qrCodeY = productBorderY + productImageSize + 63; // 距离商品标题的间距
						ctx.drawImage(loadedImages[4], qrCodeX, qrCodeY, qrCodeSize, qrCodeSize);

						// 绘制完成
						ctx.draw(false, () => {
							console.log(posterWidth, posterHeight);
							// 生成海报图片
							uni.canvasToTempFilePath({
								canvasId: 'posterCanvas',
								width: posterWidth,
								height: posterHeight,
								success: (res) => {
									console.log(res.tempFilePath);
									resolve(res.tempFilePath);
								},

								fail: (err) => {
									console.log(err);
									reject(err);
								}
							});
						});
					}
				});
			});
		},
		loadImage(src) {
			return new Promise((resolve, reject) => {
				const img = new Image();
				img.crossOrigin = 'anonymous'; // 允许跨域
				img.src = src;
				img.onload = () => {
					console.log(img);
					resolve(img);
				};
				img.onerror = (err) => reject(err);
			});
		},
		share() {
			this.$emit('shareH5');
		},
		getPoster() {
			let images = [this.bag, this.giftBorder, this.giftData.image, this.giftData.avatar, this.PromotionCode];
			console.log(images);
			let postImg = ['', '', '', '', ''];
			// #ifdef MP
			for (let i = 0; i < images.length; i++) {
				uni.downloadFile({
					url: images[i],
					success: (res) => {
						if (res.statusCode == 200) {
							postImg[i] = res.tempFilePath;
						}
						console.log(i, images.length - 1, postImg);
						const allNotEmpty = postImg.every((item) => item !== '');
						if (allNotEmpty) this.goPoster(postImg);
					},
					fail: function () {
						this.$set(this, 'PromotionCode', '');
					}
				});
			}
			// #endif
			// #ifndef MP
			this.goPoster(images);
			// #endif
		},
		goPoster(postImg) {
			this.drawPoster(postImg, this.giftData.nickname, this.giftData.title)
				.then((posterPath) => {
					console.log('海报生成成功:', posterPath);
					// #ifdef APP-PLUS || MP
					this.savePosterPathMp(posterPath);
					// #endif
					// #ifdef H5
					this.posterImage = posterPath;
					this.posterImageStatus = true;
					// #endif
				})
				.catch((err) => {
					console.error('海报生成失败:', err);
				});
		},
		// #ifdef APP-PLUS || MP
		savePosterPathMp(url) {
			let that = this;
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
		// #endif
		savePic(url) {
			var a = document.createElement('a'); // 生成一个a元素
			a.download = 'Gift'; // 设置图片名称
			a.style.display = 'none';
			a.href = url; // 将生成的URL设置为a.href属性
			document.body.appendChild(a); // 将a标签追加到文档对象中
			a.click(); // 触发a的单击事件
			a.remove(); // 一次性的，用完就删除a标签
		}
	}
};
</script>

<style lang="scss" scoped>
.aleart {
	width: 600rpx;
	height: 980rpx;
	position: fixed;
	left: 50%;
	transform: translateX(-50%);
	z-index: 999;
	top: 50%;
	margin-top: -490rpx;
	background-color: #fff;
	border-radius: 32rpx;
	background-size: 100% 100%;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	.poster-img {
		width: 100%;
		height: 100%;
		border-radius: 32rpx;
	}
	.from {
		font-size: 28rpx;
		font-weight: 500;
		color: #333333;
		margin-bottom: 16rpx;
	}
	.message {
		font-weight: 400;
		font-size: 26rpx;
		color: #999999;
		margin-bottom: 42rpx;
	}
	.aleart-body {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 396rpx;
		height: 419rpx;
		background-image: url('../../static/gift-border.png');
		background-size: 100% 100%;
		margin-bottom: 32rpx;
		.goods-img {
			width: 360rpx;
			height: 360rpx;
			margin-top: 24rpx;
		}
	}
	.title {
		max-width: 80%;
		font-weight: 400;
		font-size: 28rpx;
		color: #333333;
		margin-bottom: 52rpx;
		text-align: center;
	}
	.btn,
	.btn-clear {
		width: 396rpx;
		height: 80rpx;
		line-height: 80rpx;
		border-radius: 20px;
		text-align: center;
		font-size: 28rpx;
	}
	.btn {
		color: #fff;
		background: linear-gradient(90deg, #ff7931 0%, #e93323 100%);
	}
	.btn-clear {
		margin-top: 20rpx;
		color: #e93323;
		border: 1px solid #e93323;
	}
	.keep {
		font-size: 24rpx;
		font-weight: bold;
		color: rgba(255, 255, 255, 0.7);
		position: fixed;
		right: calc(50% - 130rpx);
		bottom: -45rpx;
		display: block;
	}
	.close {
		font-size: 50rpx;
		font-weight: bold;
		color: #fff;
		position: fixed;
		right: calc(50% - 23rpx);
		bottom: -110rpx;
		display: block;
	}
}
</style>
