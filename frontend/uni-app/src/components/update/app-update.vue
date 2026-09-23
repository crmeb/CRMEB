<template>
	<view class="wrap" v-if="popup_show">
		<view class="popup-bg" :style="getHeight">
			<view class="popup-content" :class="{ 'popup-content-show': popup_show }">
				<view class="update-wrap">
					<image src="./images/img.png" class="top-img"></image>
					<view class="content">
						<text class="title">{{$t(`发现新版本`)}}{{ update_info.version }}</text>
						<!-- 升级描述 -->
						<view class="title-sub" v-html="update_info.info"></view>
						<!-- 升级按钮 -->
						<button class="btn" v-if="downstatus < 1" @click="nowUpdate()">
							{{$t(`立即升级`)}}
						</button>
						<!-- 下载进度 -->
						<view class="sche-wrap" v-else>
							<!-- 更新包下载中 -->
							<view class="sche-bg">
								<view class="sche-bg-jindu" :style="lengthWidth"></view>
							</view>
							<text class="down-text">{{$t(`下载进度`)}}:{{ (downSize / 1024 / 1024).toFixed(2) }}M/{{
                  (fileSize / 1024 / 1024).toFixed(2)
                }}M</text>
						</view>
					</view>
				</view>
				<image src="./images/close.png" class="close-ioc" @click="closeUpdate()"></image>
			</view>
		</view>
	</view>
</template>

<script setup>
	// 原实现用模块级 vm 指向组件实例供回调访问，script setup 闭包可直接引用响应式变量，不再需要 vm
	import {
		ref,
		computed
	} from 'vue';
	import {
		getUpdateInfo as getUpdateInfoApi
	} from '@/api/public.js'

	defineOptions({
		name: "appUpdate"
	});

	//@是否强制更新
	const props = defineProps({
		tabbar: {
			type: Boolean,
			default: false, //是否有原生tabbar组件
		},
		getVer: {
			type: Boolean,
			default: false, //是否有原生tabbar组件
		},
	});

	const emit = defineEmits(['isNew']);

	// ---------- data ----------
	const popup_show = ref(false); //弹窗是否显示
	const platform = ref(""); //ios or android
	const version = ref("1.0.0"); //当前软件版本
	const need_update = ref(false); // 是否更新
	const downing = ref(false); //是否下载中
	const downstatus = ref(0); //0未下载  1已开始 2已连接到资源  3已接收到数据  4下载完成
	const update_info = ref({
		os: "", //设备系统
		version: "", //最新版本
		info: "", //升级说明
	});
	const fileSize = ref(0); //文件大小
	const downSize = ref(0); //已下载大小
	const viewObj = ref(null); //原生遮罩view

	// ---------- computed ----------
	// 下载进度计算
	const lengthWidth = computed(function() {
		let w = (downSize.value / fileSize.value) * 100;
		if (!w) {
			w = 0;
		} else {
			w = w.toFixed(2);
		}
		return {
			width: w + "%", //return 宽度半分比
		};
	});
	const getHeight = computed(function() {
		let bottom = 0;
		if (props.tabbar) {
			bottom = 50;
		}
		return {
			bottom: bottom + "px",
			height: "auto",
		};
	});

	// ---------- methods ----------
	// 检查更新
	function update() {
		// #ifdef APP-PLUS
		// 获取手机系统信息
		uni.getSystemInfo({
			success: function(res) {
				platform.value = res.platform; //ios  or android
				console.log("手机系统信息", platform.value);
			},
		});

		// 获取版本号
		plus.runtime.getProperty(plus.runtime.appid, function(inf) {
			version.value = inf.version;
		});
		console.log("当前版本", version.value);
		getUpdateInfo(); //获取更新信息
		// #endif
	}

	// 获取线上版本信息
	function getUpdateInfo() {
		//向后台发起请求，获取最新版本号
		getUpdateInfoApi(platform.value === "ios" ? 2 : 1)
			.then((res) => {
				if (Array.isArray(res.data)) {
					return emit('isNew')
				}
				const tagDate = uni.getStorageSync('app_update_time') || '',
					nowDate = new Date().toLocaleDateString();
				if (tagDate !== nowDate && !props.getVer) {
					uni.setStorageSync('app_update_time', new Date().toLocaleDateString());
				} else if ((tagDate !== nowDate) && props.getVer) {
					if (!res.data.is_force) return
				} else if (tagDate == nowDate && !props.getVer && !res.data.is_force) {
					return
				}
				// 这里的返回的数据跟后台约定
				let data = res.data;
				// 循环获取当前设备对应的更新数据
				update_info.value = data;
				if (!update_info.value.platform) {
					// 后台未配置当前系统的升级数据
				} else {
					checkUpdate(); ///检查是否更新
				}
			})
			.catch((err) => {
				popup_show.value = false
			});
	}
	// 检查是否更新
	function checkUpdate() {
		need_update.value = compareVersion(version.value, update_info.value.version); // 检查是否需要升级
		if (need_update.value) {
			popup_show.value = true; //线上版本号大于当前安装的版本号  显示升级框
			if (props.tabbar) {
				//页面是否有原生tabbar组件
				// 创建原生view用来遮罩tabbar的点击事件 (如果是没有用原生的tabbar这一步可以取消)
				viewObj.value = new plus.nativeObj.View("viewObj", {
					bottom: "0px",
					left: "0px",
					height: "50px",
					width: "100%",
					backgroundColor: "rgba(0,0,0,.6)",
				});
				viewObj.value.show(); //显示原生遮罩
			}
		} else {
			emit('isNew')
		}
	}

	// 取消更新
	function closeUpdate() {
		if (update_info.value.is_force) {
			// 强制更新，取消退出app
			platform.value == "android" ?
				plus.runtime.quit() :
				plus.ios
				.import("UIApplication")
				.sharedApplication()
				.performSelector("exit");
		} else {
			popup_show.value = false; //关闭升级弹窗
			if (viewObj.value) viewObj.value.hide(); //隐藏原生遮罩
		}
	}
	// 立即更新
	function nowUpdate() {
		if (downing.value) return false; //如果正在下载就停止操作
		downing.value = true; //状态改变 正在下载中

		if (/\.apk$/.test(update_info.value.url)) {
			// 如果是apk地址
			download_wgt(); // 安装包/升级包更新
		} else if (/\.wgt$/.test(update_info.value.url)) {
			// 如果是更新包
			download_wgt(); // 安装包/升级包更新
		} else {
			plus.runtime.openURL(update_info.value.url, function() {
				//调用外部浏览器打开更新地址
				plus.nativeUI.toast("打开错误");
			});
		}
	}
	// 下载升级资源包
	function download_wgt() {
		plus.nativeUI.showWaiting("下载更新文件..."); //下载更新文件...
		let options = {
			method: "get",
		};
		let dtask = plus.downloader.createDownload(
			update_info.value.url,
			options,
			function(d, status) {}
		);

		dtask.addEventListener("statechanged", function(task, status) {
			if (status === null) {} else if (status == 200) {
				//在这里打印会不停的执行，请注意，正式上线切记不要在这里打印东西///////////////////////////////////////////////////
				downstatus.value = task.state;
				switch (task.state) {
					case 3: // 已接收到数据
						downSize.value = task.downloadedSize;
						if (task.totalSize) {
							fileSize.value = task.totalSize; //服务器须返回正确的content-length才会有长度
						}
						break;
					case 4:
						installWgt(task.filename); // 安装wgt包
						break;
				}
			} else {
				plus.nativeUI.closeWaiting();
				plus.nativeUI.toast("下载出错");
				downing.value = false;
				downstatus.value = 0;
			}
		});
		dtask.start();
	}

	// 安装文件
	function installWgt(path) {
		plus.nativeUI.showWaiting("安装更新文件..."); //安装更新文件...
		plus.runtime.install(
			path, {},
			function() {
				plus.nativeUI.closeWaiting();
				// 应用资源下载完成！
				plus.nativeUI.alert("应用资源下载完成！", function() {
					plus.runtime.restart();
				});
			},

			function(e) {
				plus.nativeUI.closeWaiting();
				// 安装文件失败
				plus.nativeUI.alert("安装更新文件失败[" + e.code + "]：" + e.message);
			}
		);
	}
	// 对比版本号
	function compareVersion(ov, nv) {
		if (!ov || !nv || ov == "" || nv == "") {
			return false;
		}
		let b = false,
			ova = ov.split(".", 4),
			nva = nv.split(".", 4);
		for (let i = 0; i < ova.length && i < nva.length; i++) {
			let so = ova[i],
				no = parseInt(so),
				sn = nva[i],
				nn = parseInt(sn);
			if (nn > no || sn.length > so.length) {
				return true;
			} else if (nn < no) {
				return false;
			}
		}
		if (nva.length > ova.length && 0 == nv.indexOf(ov)) {
			return true;
		} else {
			return false;
		}
	}

	// ---------- created（顶层直写） ----------
	if (!props.getVer) update()

	// 供父组件 $refs.appUpdate.update() 调用（pages/users/user_info/index.vue）
	defineExpose({
		update,
	});
</script>

<style lang="scss" scoped>
	.popup-bg {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		position: fixed;
		top: 0;
		left: 0rpx;
		right: 0;
		bottom: 0;
		width: 750rpx;
		background-color: rgba(0, 0, 0, 0.6);
		z-index: 10000;
	}

	.popup-content {
		display: flex;
		flex-direction: column;
		align-items: center;
	}

	.popup-content-show {
		animation: mymove 500ms;
		transform: scale(1);
	}

	@keyframes mymove {
		0% {
			transform: scale(0);
			/*开始为原始大小*/
		}

		100% {
			transform: scale(1);
		}
	}

	.update-wrap {
		width: 580rpx;
		border-radius: 18rpx;
		position: relative;
		display: flex;
		flex-direction: column;
		background-color: #ffffff;
		padding: 170rpx 30rpx 0;

		.top-img {
			position: absolute;
			left: 0;
			width: 100%;
			height: 256rpx;
			top: -128rpx;
		}

		.content {
			display: flex;
			flex-direction: column;
			align-items: center;
			padding-bottom: 40rpx;

			.title {
				font-size: 32rpx;
				font-weight: bold;
				color: #6526f3;
			}

			.title-sub {
				text-align: center;
				font-size: 24rpx;
				color: #666666;
				padding: 30rpx 0;
			}

			.btn {
				width: 460rpx;
				display: flex;
				align-items: center;
				justify-content: center;
				color: #ffffff;
				font-size: 30rpx;
				height: 80rpx;
				line-height: 80rpx;
				border-radius: 100px;
				background-color: #6526f3;
				margin-top: 20rpx;
			}
		}
	}

	.close-ioc {
		width: 70rpx;
		height: 70rpx;
		margin-top: 30rpx;
	}

	.sche-wrap {
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: flex-end;
		padding: 10rpx 50rpx 0;

		.sche-wrap-text {
			font-size: 24rpx;
			color: #666;
			margin-bottom: 20rpx;
		}

		.sche-bg {
			position: relative;
			background-color: #cccccc;
			height: 30rpx;
			border-radius: 100px;
			width: 480rpx;
			display: flex;
			align-items: center;

			.sche-bg-jindu {
				position: absolute;
				left: 0;
				top: 0;
				height: 30rpx;
				min-width: 40rpx;
				border-radius: 100px;
				background: url(images/round.png) #5775e7 center right 4rpx no-repeat;
				background-size: 26rpx 26rpx;
			}
		}

		.down-text {
			font-size: 24rpx;
			color: #5674e5;
			margin-top: 16rpx;
		}
	}
</style>
