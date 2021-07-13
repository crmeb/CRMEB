<template>
	<view>
		<view class='distribution-posters'>
			<swiper :indicator-dots="indicatorDots" :autoplay="autoplay" :circular="circular" :interval="interval" :duration="duration"
			 @change="bindchange" previous-margin="40px" next-margin="40px">
				<block v-for="(item,index) in spreadList" :key="index">
					<swiper-item>
						<!-- #ifdef MP -->
						<image :src="item.poster" class="slide-image" :class="swiperIndex == index ? 'active' : 'quiet'" mode='aspectFill' />
						<!-- #endif -->
						<!-- #ifdef H5 -->
						<image :src="item.wap_poster" class="slide-image" :class="swiperIndex == index ? 'active' : 'quiet'" mode='aspectFill' />
						<!-- #endif -->
					</swiper-item>
				</block>
			</swiper>
			<!-- #ifdef MP -->
			<view class='keep bg-color' @click='savePosterPath'>保存海报</view>
			<!-- #endif -->
			<!-- #ifndef MP -->
			<div class="preserve acea-row row-center-wrapper">
				<div class="line"></div>
				<div class="tip">长按保存图片</div>
				<div class="line"></div>
			</div>
			<!-- #endif -->
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<home></home>
	</view>
</template>

<script>
	import {
		getUserInfo,
		spreadBanner,
		userShare
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
	export default {
		components: {
			// #ifdef MP
			authorize,
			// #endif
			home
		},
		data() {
			return {
				imgUrls: [],
				indicatorDots: false,
				circular: false,
				autoplay: false,
				interval: 3000,
				duration: 500,
				swiperIndex: 0,
				spreadList: [],
				userInfo: {},
				poster: '',
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false //是否隐藏授权
			};
		},
		computed: mapGetters({
			'isLogin':'isLogin',
			'userData':'userInfo'
		}),
		watch:{
			isLogin:{
				handler:function(newV,oldV){
					if(newV){
						this.userSpreadBannerList();
					}
				},
				deep:true
			},
			userData:{
				handler:function(newV,oldV){
					if(newV){
						this.$set(this, 'userInfo', newV);
					}
				},
				deep:true
			}
		},
		onLoad() {
			if (this.isLogin) {
				this.userSpreadBannerList();
			} else {
				toLogin();
			}
		},
		/**
		 * 用户点击右上角分享
		 */
		// #ifdef MP
		onShareAppMessage: function() {
			userShare();
			return {
				title: this.userInfo.nickname + '-分销海报',
				imageUrl: this.spreadList[0],
				path: '/pages/index/index?spid=' + this.userInfo.uid,
			};
		},
		// #endif
		methods: {
			onLoadFun: function(e) {
				this.$set(this, 'userInfo', e);
				this.userSpreadBannerList();
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			bindchange(e) {
				let spreadList = this.spreadList;
				this.swiperIndex = e.detail.current;
				this.$set(this, 'poster', spreadList[e.detail.current].poster);
			},
			savePosterPath: function() {
				let that = this;
				uni.downloadFile({
					url: that.poster,
					success(resFile) {
						if (resFile.statusCode === 200) {
							uni.getSetting({
								success(res) {
									if (!res.authSetting['scope.writePhotosAlbum']) {
										uni.authorize({
											scope: 'scope.writePhotosAlbum',
											success() {
												uni.saveImageToPhotosAlbum({
													filePath: resFile.tempFilePath,
													success: function(res) {
														return that.$util.Tips({
															title: '保存成功'
														});
													},
													fail: function(res) {
														return that.$util.Tips({
															title: res.errMsg
														});
													},
													complete: function(res) {},
												})
											},
											fail() {
												uni.showModal({
													title: '您已拒绝获取相册权限',
													content: '是否进入权限管理，调整授权？',
													success(res) {
														if (res.confirm) {
															uni.openSetting({
																success: function(res) {
																	console.log(res.authSetting)
																}
															});
														} else if (res.cancel) {
															return that.$util.Tips({
																title: '已取消！'
															});
														}
													}
												})
											}
										})
									} else {
										uni.saveImageToPhotosAlbum({
											filePath: resFile.tempFilePath,
											success: function(res) {
												return that.$util.Tips({
													title: '保存成功'
												});
											},
											fail: function(res) {
												return that.$util.Tips({
													title: res.errMsg
												});
											},
											complete: function(res) {},
										})
									}
								},
								fail(res) {

								}
							})
						} else {
							return that.$util.Tips({
								title: resFile.errMsg
							});
						}
					},
					fail(res) {
						return that.$util.Tips({
							title: res.errMsg
						});
					}
				})
			},
			setShareInfoStatus: function() {
				if (this.$wechat.isWeixin()) {
					if (this.isLogin) {
						getUserInfo().then(res => {
							let configAppMessage = {
								desc: '分销海报',
								title: res.data.nickname + '-分销海报',
								link: '/pages/index/index?spread=' + res.data.uid,
								imgUrl: this.spreadList[0]
							};
							this.$wechat.wechatEvevt(["updateAppMessageShareData", "updateTimelineShareData"], configAppMessage)
						});
					} else {
						toLogin();
					}

				}
			},
			userSpreadBannerList: function() {
				let that = this;
				uni.showLoading({
					title: '获取中',
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

	.distribution-posters .slide-image.active {
		transform: none;
		transition: all 0.2s ease-in 0s;
	}

	.distribution-posters .slide-image.quiet {
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
