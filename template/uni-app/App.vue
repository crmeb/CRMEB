<script>
	import {
		checkLogin
	} from './libs/login';
	import {
		HTTP_REQUEST_URL
	} from './config/app';
	import {
		getShopConfig,
		silenceAuth
	} from '@/api/public';
	import Auth from '@/libs/wechat.js';
	import Routine from './libs/routine.js';
	import {
		getCartCounts,
	} from '@/api/order.js';
	import {
		colorChange
	} from '@/api/api.js';
	import {
		mapGetters
	} from "vuex"
	import colors from '@/mixins/color.js';
	let green =
		'--view-theme: rgba(66,202,77,1);--view-theme-16: #42CA4D;--view-priceColor:#FF7600;--view-minorColor:rgba(108, 198, 94, 0.5);--view-minorColorT:rgba(66, 202, 77, 0.1);--view-bntColor:#FE960F;--view-op-ten: rgba(66,202,77, 0.1);--view-main-start:#70E038; --view-main-over:#42CA4D;--view-op-point-four: rgba(66,202,77, 0.04);'
	let red =
		'--view-theme: rgba(233,51,35,1);--view-theme-16: #e93323;--view-priceColor:#e93323;--view-minorColor:rgba(233, 51, 35, 0.5);--view-minorColorT:rgba(233, 51, 35, 0.1);--view-bntColor:#FE960F;--view-op-ten: rgba(233,51,35, 0.1);--view-main-start:#FF6151; --view-main-over:#e93323;--view-op-point-four: rgba(233,51,35, 0.04);'
	let blue =
		'--view-theme: rgba(29,176,252,1);--view-theme-16:#1db0fc;--view-priceColor:#FD502F;--view-minorColor:rgba(58, 139, 236, 0.5);--view-minorColorT:rgba(9, 139, 243, 0.1);--view-bntColor:#22CAFD;--view-op-ten: rgba(29,176,252, 0.1);--view-main-start:#40D1F4; --view-main-over:#1DB0FC;--view-op-point-four: rgba(29,176,252, 0.04);'
	let pink =
		'--view-theme: rgba(255,68,143,1);--view-theme-16:#ff448f;--view-priceColor:#FF448F;--view-minorColor:rgba(255, 68, 143, 0.5);--view-minorColorT:rgba(255, 68, 143, 0.1);--view-bntColor:#282828;--view-op-ten: rgba(255,68,143, 0.1);--view-main-start:#FF67AD; --view-main-over:#FF448F;--view-op-point-four: rgba(255,68,143, 0.04);'
	let orange =
		'--view-theme: rgba(254,92,45,1); --view-theme-16:#FE5C2D;--view-priceColor:#FE5C2D;--view-minorColor:rgba(254, 92, 45, 0.5);--view-minorColorT:rgba(254, 92, 45, 0.1);--view-bntColor:#FDB000;--view-op-ten: rgba(254,92,45, 0.1);--view-main-start:#FF9445; --view-main-over:#FE5C2D;--view-op-point-four: rgba(254,92,45, 0.04);'

	export default {
		globalData: {
			spid: 0,
			code: 0,
			isLogin: false,
			userInfo: {},
			MyMenus: [],
			globalData: false,
			isIframe: false,
			tabbarShow: true,
			windowHeight: 0
		},
		mixins: [colors],
		computed: mapGetters(['isLogin', 'cartNum']),
		watch: {
			isLogin: {
				deep: true, //深度监听设置为 true
				handler: function(newV, oldV) {
					if (newV) {
						// this.getCartNum()
					} else {
						this.$store.commit('indexData/setCartNum', '')
					}
				}
			},
			cartNum(newCart, b) {
				this.$store.commit('indexData/setCartNum', newCart + '')
				if (newCart > 0) {
					uni.setTabBarBadge({
						index: Number(uni.getStorageSync('FOOTER_ADDCART')) || 2,
						text: newCart + ''
					})
				} else {
					uni.hideTabBarRedDot({
						index: Number(uni.getStorageSync('FOOTER_ADDCART')) || 2
					})
				}
			}
		},
		onLaunch: function(option) {
			let that = this;
			colorChange('color_change').then(res => {
				switch (res.data.status) {
					case 1:
						uni.setStorageSync('viewColor', blue)
						uni.$emit('ok', blue)
						break;
					case 2:
						uni.setStorageSync('viewColor', green)
						uni.$emit('ok', green)
						break;
					case 3:
						uni.setStorageSync('viewColor', red)
						uni.$emit('ok', red)
						break;
					case 4:
						uni.setStorageSync('viewColor', pink)
						uni.$emit('ok', pink)
						break;
					case 5:
						uni.setStorageSync('viewColor', orange)
						uni.$emit('ok', orange)
						break;
					default:
						uni.setStorageSync('viewColor', red)
						uni.$emit('ok', red)
						break
				}
			});
			if (option.query.spread) {
				that.$Cache.set('spread', option.query.spread);
				that.globalData.spid = option.query.spread;
				that.globalData.pid = option.query.spread;
			}
			// #ifdef APP-PLUS || H5
			uni.getSystemInfo({
				success: function(res) {
					// 首页没有title获取的整个页面的高度，里面的页面有原生标题要减掉就是视口的高度
					// 状态栏是动态的可以拿到 标题栏是固定写死的是44px
					let height = res.windowHeight - res.statusBarHeight - 44
					// #ifdef H5 || APP-PLUS
					that.globalData.windowHeight = res.windowHeight + 'px'
					// #endif
					// // #ifdef APP-PLUS
					// that.globalData.windowHeight = height + 'px'
					// // #endif

				}
			});
			// #endif	
			// #ifdef MP
			if (HTTP_REQUEST_URL == '') {
				console.error(
					"请配置根目录下的config.js文件中的 'HTTP_REQUEST_URL'\n\n请修改开发者工具中【详情】->【AppID】改为自己的Appid\n\n请前往后台【小程序】->【小程序配置】填写自己的 appId and AppSecret"
				);
				return false;
			}
			if (option.query.hasOwnProperty('scene')) {

				switch (option.scene) {
					//扫描小程序码
					case 1047:
						let val = that.$util.getUrlParams(decodeURIComponent(option.query.scene));
						that.globalData.code = val.pid === undefined ? val : val.pid;
						break;
						//长按图片识别小程序码
					case 1048:
						that.globalData.code = option.query.scene;
						break;
						//手机相册选取小程序码
					case 1049:
						that.globalData.code = option.query.scene;
						break;
						//直接进入小程序
					case 1001:
						that.globalData.spid = option.query.scene;
						break;
				}
			}
			const updateManager = wx.getUpdateManager();

			updateManager.onCheckForUpdate(function(res) {
				// 请求完新版本信息的回调
			});

			updateManager.onUpdateReady(function() {
				wx.showModal({
					title: '更新提示',
					content: '新版本已经准备好，是否重启应用？',
					success: function(res) {
						if (res.confirm) {
							// 新的版本已经下载好，调用 applyUpdate 应用新版本并重启
							updateManager.applyUpdate();
						}
					}
				});
			});

			updateManager.onUpdateFailed(function() {
				return that.Tips({
					title: '新版本下载失败'
				});
			});
			// #endif
			// getShopConfig().then(res => {
			// 	this.$store.commit('SETPHONESTATUS', res.data.status);
			// });
			// 获取导航高度；
			uni.getSystemInfo({
				success: function(res) {
					that.globalData.navHeight = res.statusBarHeight * (750 / res.windowWidth) + 91;
				}
			});
			// #ifdef MP
			let menuButtonInfo = uni.getMenuButtonBoundingClientRect();
			that.globalData.navH = menuButtonInfo.top * 2 + menuButtonInfo.height / 2;
			// #endif

			// #ifdef H5
			uni.getSystemInfo({
				success(e) {
					/* 窗口宽度大于420px且不在PC页面且不在移动设备时跳转至 PC.html 页面 */
					if (e.windowWidth > 420 && !window.top.isPC && !/iOS|Android/i.test(e.system)) {
						window.location.pathname = '/static/html/pc.html';
					}
				}
			});
			if (option.query.hasOwnProperty('type') && option.query.type == "iframeWindow") {
				this.globalData.isIframe = true;
			} else {
				this.globalData.isIframe = false;
			}

			if (window.location.pathname !== '/') {
				let snsapiBase = 'snsapi_base';
				let urlData = location.pathname + location.search;
				if (!that.$store.getters.isLogin && uni.getStorageSync('authIng')) {
					uni.setStorageSync('authIng', false)
				}
				if (!that.$store.getters.isLogin && Auth.isWeixin()) {
					let code,
						state,
						scope = ''

					if (option.query.code instanceof Array) {
						code = option.query.code[option.query.code.length - 1]
					} else {
						code = option.query.code
					}


					if (code && code != uni.getStorageSync('snsapiCode') && location.pathname.indexOf(
							'/pages/users/wechat_login/index') === -1) {
						// 存储静默授权code
						uni.setStorageSync('snsapiCode', code);
						let spread = that.globalData.spid ? that.globalData.spid : '';
						silenceAuth({
								code: code,
								spread: that.$Cache.get('spread'),
								spid: that.globalData.code
							})
							.then(res => {
								uni.setStorageSync('snRouter', decodeURIComponent(decodeURIComponent(option.query
									.back_url)));
								if (res.data.key !== undefined && res.data.key) {
									this.$Cache.set('snsapiKey', res.data.key);
								} else {
									let time = res.data.expires_time - this.$Cache.time();
									this.$store.commit('LOGIN', {
										token: res.data.token,
										time: time
									});

									this.$store.commit('SETUID', res.data.userInfo.uid);
									this.$store.commit('UPDATE_USERINFO', res.data.userInfo);
									if (option.query.back_url) {
										location.replace(decodeURIComponent(decodeURIComponent(option.query
											.back_url)));
									}
								}
							})
							.catch(error => {
								let url = ''
								if (option.query.back_url instanceof Array) {
									url = option.query.back_url[option.query.back_url.length - 1]
								} else {
									url = option.query.back_url
								}
								if (!that.$Cache.has('snsapiKey')) {
									if (location.pathname.indexOf('/pages/users/wechat_login/index') === -1) {
										Auth.oAuth(snsapiBase, url);
									}
								}
							});
					} else {
						if (!this.$Cache.has('snsapiKey')) {
							if (location.pathname.indexOf('/pages/users/wechat_login/index') === -1) {
								Auth.oAuth(snsapiBase, urlData);
							}
						}
					}
				} else {
					if (option.query.back_url) {
						location.replace(uni.getStorageSync('snRouter'));
					}
				}
			}
			// #endif
			// #ifdef MP
			// 小程序静默授权
			if (!this.$store.getters.isLogin) {
				Routine.getCode()
					.then(code => {
						this.silenceAuth(code);
					})
					.catch(res => {
						uni.hideLoading();
					});
			}
			// #endif
			// #ifdef H5
			// 添加crmeb chat 统计
			var __s = document.createElement('script');
			__s.src = `${HTTP_REQUEST_URL}/api/get_script`;
			document.head.appendChild(__s);
			// #endif
		},
		mounted() {},
		methods: {
			// 小程序静默授权
			silenceAuth(code) {
				let that = this;
				let spread = that.globalData.spid ? that.globalData.spid : '';
				silenceAuth({
						code: code,
						spread_spid: spread,
						spread_code: that.globalData.code
					})
					.then(res => {
						if (res.data.token !== undefined && res.data.token) {
							uni.hideLoading();
							let time = res.data.expires_time - this.$Cache.time();
							that.$store.commit('LOGIN', {
								token: res.data.token,
								time: time
							});
							that.$store.commit('SETUID', res.data.userInfo.uid);
							that.$store.commit('UPDATE_USERINFO', res.data.userInfo);
						}
					})
					.catch(res => {});
			},
		},
		onHide: function() {

		}
	};
</script>

<style>
	@import url('@/plugin/emoji-awesome/css/google.min.css');
	@import url('@/plugin/animate/animate.min.css');
	@import 'static/css/base.css';
	@import 'static/iconfont/iconfont.css';
	@import 'static/css/guildford.css';
	@import 'static/css/style.scss';

	view {
		box-sizing: border-box;
	}

	page {
		font-family: PingFang SC;
	}

	.bg-color-red {
		background-color: var(--view-theme) !important;
	}

	.syspadding {
		padding-top: var(--status-bar-height);
	}

	.flex {
		display: flex;
	}

	.uni-scroll-view::-webkit-scrollbar {
		/* 隐藏滚动条，但依旧具备可以滚动的功能 */
		display: none;
	}

	::-webkit-scrollbar {
		width: 0;
		height: 0;
		color: transparent;
	}

	.uni-system-open-location .map-content.fix-position {
		height: 100vh;
		top: 0;
		bottom: 0;
	}
</style>
