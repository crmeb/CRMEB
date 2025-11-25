<script>
	import {
		checkLogin
	} from './libs/login';
	import {
		HTTP_REQUEST_URL
	} from './config/app';
	import {
		getShopConfig,
		silenceAuth,
		getSystemVersion,
		basicConfig,
		remoteRegister
	} from '@/api/public';
	import Auth from '@/libs/wechat.js';
	import Routine from './libs/routine.js';
	import {
		silenceBindingSpread
	} from '@/utils';
	import {
		colorChange,
		getCrmebCopyRight
	} from '@/api/api.js';
	import {
		getLangJson,
		getLangVersion
	} from '@/api/user.js';
	import {
		mapGetters
	} from 'vuex';
	import colors from '@/mixins/color.js';
	import Cache from '@/utils/cache';
	import themeList from '@/utils/theme';
	import {
		debug
	} from 'util';

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
			windowHeight: 0,
			locale: ''
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
						this.$store.commit('indexData/setCartNum', '');
					}
				}
			},
			cartNum(newCart, b) {
				this.$store.commit('indexData/setCartNum', newCart + '');
				if (newCart > 0) {
					uni.setTabBarBadge({
						index: Number(uni.getStorageSync('FOOTER_ADDCART')) || 2,
						text: newCart + ''
					});
				} else {
					uni.hideTabBarRedDot({
						index: Number(uni.getStorageSync('FOOTER_ADDCART')) || 2
					});
				}
			}
		},
		onShow() {
			const queryData = uni.getEnterOptionsSync(); // uni-app版本 3.5.1+ 支持
			if (queryData.query.spread) {
				this.$Cache.set('spread', queryData.query.spread);
				this.globalData.spid = queryData.query.spread;
				this.globalData.pid = queryData.query.spread;
				silenceBindingSpread(this.globalData);
			}
			if (queryData.query.spid) {
				this.$Cache.set('spread', queryData.query.spid);
				this.globalData.spid = queryData.query.spid;
				this.globalData.pid = queryData.query.spid;
				silenceBindingSpread(this.globalData);
			}
			if (queryData.query.agent_id) {
				this.$Cache.set('agent_id', queryData.query.agent_id);
				this.globalData.agent_id = queryData.query.agent_id;
				silenceBindingSpread(this.globalData);
			}
			// #ifdef MP
			if (queryData.query.scene) {
				let param = this.$util.getUrlParams(decodeURIComponent(queryData.query.scene));
				if (param.pid) {
					this.$Cache.set('spread', param.pid);
					this.globalData.spid = param.pid;
					this.globalData.pid = param.pid;
				} else {
					switch (queryData.scene) {
						//扫描小程序码
						case 1047:
							this.globalData.code = queryData.query.scene;
							break;
							//长按图片识别小程序码
						case 1048:
							this.globalData.code = queryData.query.scene;
							break;
							//手机相册选取小程序码
						case 1049:
							this.globalData.code = queryData.query.scene;
							break;
							//直接进入小程序
						case 1001:
							this.globalData.spid = queryData.query.scene;
							break;
					}
				}
				silenceBindingSpread(this.globalData);
			}
			// #endif
		},
		async onLaunch(option) {
			uni.hideTabBar();
			let that = this;
			basicConfig().then((res) => {
				uni.setStorageSync('BASIC_CONFIG', res.data);
			});
			// #ifdef H5
			if (option.query.hasOwnProperty('mdType') && option.query.mdType == 'iframeWindow') {
				this.globalData.isIframe = true;
			} else {
				this.globalData.isIframe = false;
			}
			if (!this.isLogin && option.query.hasOwnProperty('remote_token')) {
				this.remoteRegister(option.query.remote_token);
			}
			// #endif
			colorChange('color_change').then((res) => {
				uni.setStorageSync('is_diy', res.data.is_diy);
				uni.$emit('is_diy', res.data.is_diy);
				const themeMap = {
					1: 'blue',
					2: 'green',
					3: 'red',
					4: 'pink',
					5: 'orange'
				};

				const status = res.data.status;
				const themeKey = themeMap[status] || 'red'; // 默认使用红色
				const selectedTheme = themeList[themeKey];
				uni.setStorageSync('color_status', res.data.status);
				uni.setStorageSync('viewColor', selectedTheme);
				uni.$emit('ok', selectedTheme, status);
			});
			getLangVersion().then((res) => {
				let version = res.data.version;
				if (version != uni.getStorageSync('LANG_VERSION')) {
					getLangJson().then((res) => {
						let value = Object.keys(res.data)[0];
						Cache.set('locale', Object.keys(res.data)[0]);
						this.$i18n.setLocaleMessage(value, res.data[value]);
						uni.setStorageSync('localeJson', res.data);
					});
				}
				uni.setStorageSync('LANG_VERSION', version);
			});

			// #ifdef APP-PLUS || H5
			uni.getSystemInfo({
				success: function(res) {
					// 首页没有title获取的整个页面的高度，里面的页面有原生标题要减掉就是视口的高度
					// 状态栏是动态的可以拿到 标题栏是固定写死的是44px
					let height = res.windowHeight - res.statusBarHeight - 44;
					// #ifdef H5 || APP-PLUS
					that.globalData.windowHeight = res.windowHeight + 'px';
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

			const updateManager = wx.getUpdateManager();
			const startParamObj = wx.getEnterOptionsSync();
			if (wx.canIUse('getUpdateManager') && startParamObj.scene != 1154) {
				const updateManager = wx.getUpdateManager();
				updateManager.onCheckForUpdate(function(res) {
					if (res.hasUpdate) {
						updateManager.onUpdateFailed(function() {
							return that.Tips({
								title: '新版本下载失败'
							});
						});
						updateManager.onUpdateReady(function() {
							wx.showModal({
								title: '更新提示',
								content: '新版本已经下载好，是否重启当前应用？',
								success(res) {
									if (res.confirm) {
										updateManager.applyUpdate();
									}
								}
							});
						});
						updateManager.onUpdateFailed(function() {
							wx.showModal({
								title: '发现新版本',
								content: '请删除当前小程序，重启搜索打开...'
							});
						});
					}
				});
			}
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
			const version = uni.getSystemInfoSync().SDKVersion;
			if (Routine.compareVersion(version, '2.21.3') >= 0) {
				that.$Cache.set('MP_VERSION_ISNEW', true);
			} else {
				that.$Cache.set('MP_VERSION_ISNEW', false);
			}
			// #endif

			// #ifdef MP
			// 小程序静默授权
			// if (!this.$store.getters.isLogin) {
			// 	Routine.getCode()
			// 		.then(code => {
			// 			this.silenceAuth(code);
			// 		})
			// 		.catch(res => {
			// 			uni.hideLoading();
			// 		});
			// }
			// #endif
			// #ifdef H5
			// 添加crmeb chat 统计
			// var __s = document.createElement('script');
			// __s.src = `${HTTP_REQUEST_URL}/api/get_script`;
			// document.head.appendChild(__s);


			fetch(`${HTTP_REQUEST_URL}/api/get_script`)
				.then(response => response.text())
				.then(content => {
					// 尝试解析是否为HTML（带<script>标签）
					const isHTML = content.trim().startsWith('<script');

					let externalScripts = [];
					let inlineScripts = [];

					if (isHTML) {
						// 情况1：带<script>标签，用DOMParser解析
						const parser = new DOMParser();
						const doc = parser.parseFromString(content, 'text/html');
						const scripts = doc.querySelectorAll('script');

						externalScripts = Array.from(scripts).filter(script => script.src);
						inlineScripts = Array.from(scripts).filter(script => !script.src);
					} else {
						// 情况2：不带<script>标签，直接当作内联脚本处理
						inlineScripts = [{
							textContent: content
						}];
					}

					// 1. 先加载所有外部脚本（如果有）
					const loadExternalScripts = externalScripts.map(script => {
						return new Promise((resolve, reject) => {
							const newScript = document.createElement('script');
							newScript.src = script.src;
							newScript.onload = resolve;
							newScript.onerror = reject;
							document.body.appendChild(newScript);
						});
					});

					// 2. 等外部脚本加载完成后，再执行内联脚本
					Promise.all(loadExternalScripts)
						.then(() => {
							inlineScripts.forEach(script => {
								const newScript = document.createElement('script');
								newScript.textContent = script.textContent;
								document.body.appendChild(newScript);
							});
						})
						.catch(error => console.error('Failed to load external scripts:', error));
				})
				.catch(error => console.error('Error fetching script:', error));


			// #endif
			getCrmebCopyRight().then((res) => {
				uni.setStorageSync('copyRight', res.data);
			});
		},
		// #ifdef H5
		onHide() {
			this.$Cache.clear('snsapiKey');
		},
		// #endif
		methods: {
			remoteRegister(remote_token) {
				remoteRegister({
					remote_token
				}).then((res) => {
					let data = res.data;
					if (data.get_remote_login_url) {
						location.href = data.get_remote_login_url
					} else {
						this.$store.commit('LOGIN', {
							token: data.token,
							time: data.expires_time - this.$Cache.time()
						});
						this.$store.commit('SETUID', data.userInfo.uid);
						location.reload();
					}
				});
			}
			// 小程序静默授权
			// silenceAuth(code) {
			// 	let that = this;
			// 	let spread = that.globalData.spid ? that.globalData.spid : '';
			// 	silenceAuth({
			// 			code: code,
			// 			spread_spid: spread,
			// 			spread_code: that.globalData.code
			// 		})
			// 		.then(res => {
			// 			if (res.data.token !== undefined && res.data.token) {
			// 				uni.hideLoading();
			// 				let time = res.data.expires_time - this.$Cache.time();
			// 				that.$store.commit('LOGIN', {
			// 					token: res.data.token,
			// 					time: time
			// 				});
			// 				that.$store.commit('SETUID', res.data.userInfo.uid);
			// 				that.$store.commit('UPDATE_USERINFO', res.data.userInfo);
			// 			}
			// 		})
			// 		.catch(res => {});
			// },
		}
	};
</script>

<style>
	@import url('@/plugin/emoji-awesome/css/tuoluojiang.css');
	@import url('@/plugin/animate/animate.min.css');
	@import 'static/css/base.css';
	@import 'static/iconfont/iconfont.css';
	@import 'static/css/guildford.css';
	@import 'static/css/style.scss';
	@import 'static/css/unocss.css';
	@import 'static/fonts/font.scss';

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

	.open-location {
		width: 100%;
		height: 100vh;
	}
</style>