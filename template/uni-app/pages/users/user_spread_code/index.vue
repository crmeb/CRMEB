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

<script>
	import zbCode from '@/components/zb-code/zb-code.vue'
	import {
		getUserInfo,
		spreadBanner,
		userShare,
		routineCode,
		spreadMsg,
		imgToBase
	} from '@/api/user.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import home from '@/components/home';
	import {
		TOKENNAME,
		HTTP_REQUEST_URL
	} from '@/config/app.js';
	import colors from '@/mixins/color.js';
	export default {
		components: {
			// #ifdef MP
			authorize,
			// #endif
			home,
			zbCode
		},
		mixins: [colors],
		data() {
			return {
				imgUrls: [],
				indicatorDots: false,
				posterImageStatus: true,
				circular: false,
				autoplay: false,
				interval: 3000,
				duration: 500,
				swiperIndex: 0,
				spreadList: [],
				userInfo: {},
				poster: '',
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				spreadData: [{}], //新海报数据
				nickName: "",
				siteName: "",
				mpUrl: "",
				canvasImageUrl: '',
				posterImage: [],
				//二维码参数
				codeShow: false,
				cid: '1',
				ifShow: true,
				val: "", // 要生成的二维码值
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
				codeSrc: "",
				wd: 0,
				hg: 0,
				qrcode: ""
			};
		},
		computed: mapGetters({
			'isLogin': 'isLogin',
			'userData': 'userInfo',
			'uid': 'uid'
		}),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.userSpreadBannerList();
					}
				},
				deep: true
			},
			userData: {
				handler: function(newV, oldV) {
					if (newV) {
						this.$set(this, 'userInfo', newV);
					}
				},
				deep: true
			}
		},
		async onReady() {
			if (this.isLogin) {
				this.val = `${HTTP_REQUEST_URL}?spid=${this.uid}`
				await this.getUser()
			} else {
				toLogin();

			}
			this.$nextTick(() => {
				let selector = uni.createSelectorQuery().select('.aaa');
				selector.fields({
					size: true
				}, data => {
					this.wd = data.width
					this.hg = data.height
				}).exec();
			})
		},
		onShow() {

		},
		onHide() {
			uni.hideLoading();
		},
		/**
		 * 用户点击右上角分享
		 */
		// #ifdef MP
		onShareAppMessage() {
			return {
				title: this.userInfo.nickname + '-' + this.$t(`分销海报`),
				imageUrl: this.spreadList[0],
				path: '/pages/index/index?spread=' + this.userInfo.uid,
			};
		},
		// #endif
		methods: {
			getUser() {
				getUserInfo().then(res => {
					this.userInfo = res.data
				})
			},
			onLoadFun: function(e) {
				this.$set(this, 'userInfo', e);
				this.userSpreadBannerList();
			},
			qrR(res) {
				this.codeSrc = res
				this.spreadMsgs()
			},
			//获取图片
			async spreadMsgs() {

				let res = await spreadMsg()
				this.spreadData = res.data.spread
				this.nickName = res.data.nickname
				this.siteName = res.data.site_name
				// #ifdef MP
				this.qrcode = await this.imgToBase(res.data.qrcode)
				// #endif
				// #ifdef MP
				await this.routineCode()
				let mpUrl = await this.downloadFilestoreImage(this.mpUrl)
				// #endif
				uni.showLoading({
					title: this.$t(`海报生成中`),
					mask: true
				});

				for (let i = 0; i < res.data.spread.length; i++) {
					let that = this
					let arr2, img
					// #ifdef MP	
					arr2 = [mpUrl, await this.downloadFilestoreImage(res.data.spread[i].pic)]
					// #endif
					// #ifdef H5
					img = await this.imgToBase(res.data.spread[i].pic, res.data.qrcode)
					arr2 = [img.code || this.codeSrc, img.image]
					// #endif
					// #ifdef APP-PLUS
					img = await this.imgToBase(res.data.spread[i].pic, res.data.qrcode)
					arr2 = [img.code || this.codeSrc, res.data.spread[i].pic]
					// #endif
					that.$util.userPosterCanvas(arr2, res.data.nickname, res.data.site_name, i, this
						.wd,
						this.hg, (
							tempFilePath) => {
							that.$set(that.posterImage, i, tempFilePath);
							// #ifdef MP
							if (!that.posterImage.length) {
								return that.$util.Tips({
									title: that.$t(`小程序二维码需要发布正式版后才能获取到`)
								});
							}
							// #endif
						});
				}
				uni.hideLoading();

			},
			downloadImg() {
				uni.saveImageToPhotosAlbum({
					filePath: this.posterImage[this.swiperIndex],
					success: function() {}
				});
			},
			async routineCode() {
				let res = await routineCode()
				this.mpUrl = res.data.url
			},
			async imgToBase(url, code) {
				let res = await imgToBase({
					image: url,
					code: code
				})
				return res.data
			},
			// 二维码生成
			codeImg() {
				// http://当前域名+"?spread="+用户uid
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			bindchange(e) {
				let spreadList = this.spreadList;
				this.swiperIndex = e.detail.current;
				// this.$set(this, 'poster', spreadList[e.detail.current].poster);
			},
			// #ifdef MP
			savePosterPathMp(url) {
				let that = this;
				uni.getSetting({
					success(res) {
						if (!res.authSetting['scope.writePhotosAlbum']) {
							uni.authorize({
								scope: 'scope.writePhotosAlbum',
								success() {
									uni.saveImageToPhotosAlbum({
										filePath: url,
										success: function(res) {
											that.$util.Tips({
												title: that.$t(`保存成功`),
												icon: 'success'
											});
										},
										fail: function(res) {
											that.$util.Tips({
												title: that.$t(`保存失败`),
											});
										}
									});
								}
							});
						} else {
							uni.saveImageToPhotosAlbum({
								filePath: url,
								success: function(res) {
									that.$util.Tips({
										title: that.$t(`保存成功`),
										icon: 'success'
									});
								},
								fail: function(res) {
									that.$util.Tips({
										title: that.$t(`保存失败`),
									});
								}
							});
						}
					}
				});
			},
			// #endif
			// #ifdef APP-PLUS
			savePosterPathMp(url) {
				let that = this;
				uni.saveImageToPhotosAlbum({
					filePath: url,
					success: function(res) {
						that.$util.Tips({
							title: that.$t(`保存成功`),
							icon: 'success'
						});
					},
					fail: function(res) {
						that.$util.Tips({
							title: that.$t(`保存失败`),
						});
					}
				});
			},
			// #endif
			//图片转符合安全域名路径
			downloadFilestoreImage(url) {
				return new Promise((resolve, reject) => {
					let that = this;
					uni.downloadFile({
						url: url,
						success: function(res) {
							resolve(res.tempFilePath);
						},
						fail: function() {
							return that.$util.Tips({
								title: ''
							});
						}
					});
				})
			},
			setShareInfoStatus: function() {
				if (this.$wechat.isWeixin()) {
					if (this.isLogin) {
						getUserInfo().then(res => {
							let configAppMessage = {
								desc: this.$t(`分销海报`),
								title: res.data.nickname + '-' + this.$t(`分销海报`),
								link: '/pages/index/index?spread=' + res.data.uid,
								imgUrl: this.spreadList[0]
							};
							this.$wechat.wechatEvevt(["updateAppMessageShareData", "updateTimelineShareData"],
								configAppMessage)
						});
					} else {
						toLogin();
					}

				}
			},
			userSpreadBannerList: function() {
				let that = this;
				uni.showLoading({
					title: that.$t(`获取中`),
					mask: true,
				})
				spreadBanner().then(res => {
					uni.hideLoading();
					that.$set(that, 'spreadList', res.data);
					that.$set(that, 'poster', res.data[0].poster);
					// #ifdef H5
					that.setShareInfoStatus();
					// #endif
				}).catch(err => {
					uni.hideLoading();
				});
			}
		}
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

	.distribution-posters /deep/.active {
		transform: none;
		transition: all 0.2s ease-in 0s;
	}

	.distribution-posters /deep/ .quiet {
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
