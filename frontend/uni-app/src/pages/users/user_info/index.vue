<template>
	<view>
		<form @submit="formSubmit">
			<view class='personal-data' :style="colorStyle">
				<view class='list'>
					<view class='item acea-row row-between-wrapper'>
						<view>{{$t(`头像`)}}</view>
						<view class="avatar-box" v-if="!mp_is_new" @click.stop='uploadpic'>
							<image :src="userInfo.avatar"></image>
						</view>
						<button v-else class="avatar-box" open-type="chooseAvatar" @chooseavatar="onChooseAvatar">
							<image :src="userInfo.avatar"></image>
						</button>
					</view>
					<view class='item acea-row row-between-wrapper'>
						<view>{{$t(`昵称`)}}</view>
						<view class='input'>
							<!-- #ifdef MP-WEIXIN -->
							<input type="nickname" name="nickname" :maxlength="10"
								:value="userInfo.nickname" @input="onNicknameInput" />
							<!-- #endif -->
							<!-- #ifndef MP-WEIXIN -->
							<input type="text" name="nickname" :maxlength="10"
								v-model="userInfo.nickname" />
							<!-- #endif -->
						</view>
					</view>
					<view class='item acea-row row-between-wrapper'>
						<view>{{$t(`手机号码`)}}</view>
						<!-- #ifdef MP -->
						<button class="input" open-type="getPhoneNumber" @getphonenumber="getphonenumber"
							v-if="!userInfo.phone">{{$t(`点击绑定手机号`)}}
							<text class="iconfont icon-xiangyou"></text>
						</button>
						<!-- #endif -->
						<!-- #ifndef MP -->
						<navigator :render-link="false" url="/pages/users/user_phone/index" hover-class="none" class="input"
							v-if="!userInfo.phone">
							{{$t(`点击绑定手机号`)}}<text class="iconfont icon-xiangyou"></text>
						</navigator>
						<!-- #endif -->

						<view class='input acea-row row-between-wrapper' v-else>
							<view class=""></view>
							<view class="acea-row row-middle">
								<input type='text' disabled='true' name='phone' :value='userInfo.phone'
									class='id'></input>
								<text class='iconfont icon-suozi'></text>
							</view>

						</view>
					</view>
					<view class='item acea-row row-between-wrapper'>
						<view>{{$t(`邮箱`)}}</view>
						<view class='input'>
							<input type="text" name="email" :maxlength="64" v-model="userInfo.email" />
						</view>
					</view>
					<view class='item acea-row row-between-wrapper'>
						<view>{{$t(`ID号`)}}</view>
						<view class='input acea-row row-between-wrapper'>
							<view class=""></view>
							<view class="">
								<text>{{userInfo.uid}}</text>
								<text class='iconfont icon-suozi'></text>
							</view>
						</view>
					</view>
					<!-- #ifdef MP -->
					<view class='item acea-row row-between-wrapper'>
						<view>{{$t(`权限设置`)}}</view>
						<view class="input" @click="Setting">
							{{$t(`点击管理`)}}<text class="iconfont icon-xiangyou"></text>
						</view>
					</view>
					<!-- #endif -->
					<!-- #ifdef H5 -->
					<view class="item acea-row row-between-wrapper" v-if="userInfo.phone && !isWeixin">
						<view>{{$t(`密码`)}}</view>
						<navigator :render-link="false" url="/pages/users/user_pwd_edit/index" hover-class="none" class="input">
							{{$t(`点击修改密码`)}}<text class="iconfont icon-xiangyou"></text>
						</navigator>
					</view>
					<!-- #endif -->
					<view class="item acea-row row-between-wrapper" v-if="userInfo.phone">
						<view>{{$t(`更换手机号码`)}}</view>
						<navigator :render-link="false" url="/pages/users/user_phone/index?type=1" hover-class="none" class="input">
							{{$t(`点击更换手机号码`)}}<text class="iconfont icon-xiangyou"></text>
						</navigator>
					</view>
					<!-- #ifdef APP-PLUS -->
					<view class="item acea-row row-between-wrapper" v-if="userInfo.phone">
						<view>{{$t(`密码`)}}</view>
						<navigator :render-link="false" url="/pages/users/user_pwd_edit/index" hover-class="none" class="input">
							{{$t(`点击修改密码`)}}<text class="iconfont icon-xiangyou"></text>
						</navigator>
					</view>
					<view class="item acea-row row-between-wrapper" @click="initData">
						<view>{{$t(`缓存大小`)}}</view>
						<view class="input">
							{{fileSizeString}}<text class="iconfont icon-xiangyou"></text>
						</view>
					</view>
					<view class="item acea-row row-between-wrapper" @click="updateApp">
						<view>{{$t(`当前版本`)}}</view>
						<view class="input">
							{{version}}<text class="iconfont icon-xiangyou"></text>
						</view>
					</view>
					<!-- #endif -->
					<view class="item acea-row row-between-wrapper" v-if="array.length">
						<view>{{$t(`语言切换`)}}</view>
						<view class="uni-list-cell-db">
							<picker @change="bindPickerChange" range-key="name" :value="setIndex" :range="array">
								<view class="uni-input input">{{array[setIndex].name}}<text
										class="iconfont icon-xiangyou"></text></view>
							</picker>
						</view>
					</view>
					<view class="item acea-row row-between-wrapper">
						<view>{{$t(`地址管理`)}}</view>
						<navigator :render-link="false" url="/pages/users/user_address_list/index" hover-class="none" class="input">
							{{$t(`点击前往`)}}<text class="iconfont icon-xiangyou"></text>
						</navigator>
					</view>
					<view class="item acea-row row-between-wrapper" v-if="userInfo.invioce_func">
						<view>{{$t(`发票管理`)}}</view>
						<navigator :render-link="false" url="/pages/users/user_invoice_list/index" hover-class="none" class="input">
							{{$t(`点击前往`)}}<text class="iconfont icon-xiangyou"></text>
						</navigator>
					</view>
					<view class="item acea-row row-between-wrapper">
						<view>{{$t(`账号注销`)}}</view>
						<navigator :render-link="false" url="/pages/users/user_cancellation/index" hover-class="none" class="input">
							{{$t(`注销后无法恢复`)}}<text class="iconfont icon-xiangyou"></text>
						</navigator>
					</view>
					<view class="item acea-row row-between-wrapper">
						<view>{{$t(`用户协议`)}}</view>
						<navigator :render-link="false" url="/pages/users/privacy/index?type=4" hover-class="none" class="input">
							{{$t(`点击查看`)}}<text class="iconfont icon-xiangyou"></text>
						</navigator>
					</view>
					<view class="item acea-row row-between-wrapper">
						<view>{{$t(`隐私协议`)}}</view>
						<navigator :render-link="false" url="/pages/users/privacy/index?type=3" hover-class="none" class="input">
							{{$t(`点击查看`)}}<text class="iconfont icon-xiangyou"></text>
						</navigator>
					</view>

				</view>

				<button class='modifyBnt bg-color' formType="submit">{{$t(`保存修改`)}}</button>
				<!-- #ifdef H5 || APP-PLUS || MP -->
				<view class="logOut cartcolor acea-row row-center-wrapper" @click="outLogin">{{$t(`退出登录`)}}</view>
				<!-- #endif -->
				<!-- #ifdef APP-PLUS -->
				<app-update ref="appUpdateRef" :force="true" :tabbar="false" :getVer='true' @isNew="isNew"></app-update>
				<!-- #endif -->
			</view>
		</form>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<canvas canvas-id="canvas" v-if="canvasStatus"
			:style="{width: canvasWidth + 'px', height: canvasHeight + 'px',position: 'absolute',left:'-100000px',top:'-100000px'}"></canvas>

	</view>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import { storeToRefs } from 'pinia';
import {
	getUserInfo as getUserInfoApi,
	userEdit,
	getLogout,
	getLangList as getLangListApi,
	getLangJson,
	mpBindingPhone
} from '@/api/user.js';
import {
	switchH5Login,
} from '@/api/api.js';
import {
	toLogin
} from '@/libs/login.js';
import dayjs from "@/plugin/dayjs/dayjs.min.js";
import Cache from '@/utils/cache';
import { useColorTheme } from '@/composables/useColorTheme';
import { useAppStore } from '@/store/modules/app';
import util from '@/utils/util.js';
import i18n from '@/utils/lang.js';
import appUpdate from "@/components/update/app-update.vue";
// #ifdef MP
import authorize from '@/components/Authorize';
import Routine from '@/libs/routine';
// #endif
// #ifdef H5
import Auth from '@/libs/wechat';
// #endif

const { colorStyle } = useColorTheme();
const appStore = useAppStore();
const { isLogin } = storeToRefs(appStore);

// ---------- data ----------
const userInfo = ref({});
const loginType = 'h5'; //app.globalData.loginType
const userIndex = ref(0);
const switchUserInfo = ref([]);
const isAuto = ref(false); //没有授权的不会自动授权
const isShowAuth = ref(false); //是否隐藏授权
const canvasWidth = ref("");
const canvasHeight = ref("");
const canvasStatus = ref(false);
const fileSizeString = ref('');
const version = ref('');
const array = ref([]);
const setIndex = ref(0);
const mp_is_new = Boolean(Cache.get('MP_VERSION_ISNEW'));
const appUpdateRef = ref(null);

// ---------- watch ----------
watch(isLogin, (newV, oldV) => {
	if (newV) {
		getUserInfo();
	}
}, { deep: true });

// ---------- 生命周期 ----------
onLoad(() => {
	if (isLogin.value) {
		getUserInfo();
		getLangList()
		// #ifdef APP-PLUS
		formatSize()
		// 获取版本号
		plus.runtime.getProperty(plus.runtime.appid, (inf) => {
			version.value = inf.version;
		});
		// #endif
	} else {
		toLogin();
	}
});

// ---------- methods ----------
function getLangList() {
	getLangListApi().then(res => {
		array.value = res.data
		setLang();
	})
}
function isNew() {
	util.Tips({
		title: i18n.t(`当前为最新版本`)
	});
}
function getphonenumber(e) {
	if (e.detail.errMsg == 'getPhoneNumber:ok') {
		Routine.getCode()
			.then(code => {
				let data = {
					code,
					iv: e.detail.iv,
					encryptedData: e.detail.encryptedData,
				}
				mpBindingPhone(data).then(res => {
					getUserInfo()
					util.Tips({
						title: res.msg,
						icon: 'success'
					});
				}).catch(err => {
					return util.Tips({
						title: err
					});
				})
			})
			.catch(error => {
				uni.hideLoading();
			});
	}
}
function setLang() {
	array.value.map((item, i) => {
		if (i18n.global.locale == item.value) {
			setIndex.value = i
		}
	})
}
function bindPickerChange(e, item) {
	setIndex.value = e.detail.value
	Cache.set('locale', array.value[setIndex.value].value)
	getLangJson().then(res => {
		uni.setStorageSync('localeJson', res.data);
		i18n.global.setLocaleMessage(array.value[setIndex.value].value, res.data[array.value[
			setIndex.value].value]);
		nextTick(e => {
			i18n.global.locale = array.value[setIndex.value].value;
		})
	})
}

function updateApp() {
	appUpdateRef.value.update(); //调用子组件 检查更新
}
function formatSize() {
	plus.cache.calculate(function(size) {
		let sizeCache = parseInt(size);
		if (sizeCache == 0) {
			fileSizeString.value = "0B";
		} else if (sizeCache < 1024) {
			fileSizeString.value = sizeCache + "B";
		} else if (sizeCache < 1048576) {
			fileSizeString.value = (sizeCache / 1024).toFixed(2) + "KB";
		} else if (sizeCache < 1073741824) {
			fileSizeString.value = (sizeCache / 1048576).toFixed(2) + "MB";
		} else {
			fileSizeString.value = (sizeCache / 1073741824).toFixed(2) + "GB";
		}
	});
}

function initData() {
	uni.showModal({
		title: i18n.t(`清除缓存`),
		content: i18n.t(`确定清楚本地缓存数据吗`),
		success: (res) => {
			if (res.confirm) {
				clearCache()
				formatSize()
			} else if (res.cancel) {
				// 原代码此处误用未定义的 that，修正为直接提示
				return util.Tips({
					title: i18n.t(`取消`)
				});
			}
		}
	});
}
function clearCache() {
	let os = plus.os.name;
	if (os == 'Android') {
		let main = plus.android.runtimeMainActivity();
		let sdRoot = main.getCacheDir();
		let files = plus.android.invoke(sdRoot, "listFiles");
		let len = files.length;
		for (let i = 0; i < len; i++) {
			let filePath = '' + files[i]; // 没有找到合适的方法获取路径，这样写可以转成文件路径
			plus.io.resolveLocalFileSystemURL(filePath, function(entry) {
				if (entry.isDirectory) {
					entry.removeRecursively(function(entry) { //递归删除其下的所有文件及子目录
						uni.showToast({
							title: i18n.t(`缓存清理完成`),
							duration: 2000
						});
						formatSize(); // 重新计算缓存
					}, function(e) {
						console.log(e.message)
					});
				} else {
					entry.remove();
				}
			}, function(e) {});
		}
	} else { // ios暂时未找到清理缓存的方法，以下是官方提供的方法，但是无效，会报错
		plus.cache.clear(function() {
			uni.showToast({
				title: i18n.t(`缓存清理完成`),
				duration: 2000
			});
			formatSize();
		});
	}
}
/**
 * 授权回调
 */
function onLoadFun() {
	getUserInfo();
}
// 授权关闭
function authColse(e) {
	isShowAuth.value = e
}
/**
 * 小程序设置
 */
function Setting() {
	uni.openSetting({
		success: function(res) {}
	});
}
function switchAccounts(index) {
	let userInfoItem = switchUserInfo.value[index];
	userIndex.value = index;
	if (switchUserInfo.value.length <= 1) return true;
	if (userInfoItem === undefined) return util.Tips({
		title: i18n.t(`切换的账号不存在`)
	});
	if (userInfoItem.user_type === 'h5') {
		uni.showLoading({
			title: i18n.t(`正在切换中`)
		});
		switchH5Login().then(res => {
			uni.hideLoading();
			appStore.LOGIN({
				'token': res.data.token,
				'time': Cache.strTotime(res.data.expires_time) - Cache.time()
			});
			getUserInfo();
		}).catch(err => {
			uni.hideLoading();
			return util.Tips({
				title: err
			});
		})
	} else {
		appStore.LOGOUT();
		uni.showLoading({
			title: i18n.t(`正在切换中`)
		});
		toLogin();
	}
}
/**
 * 退出登录
 *
 */
function outLogin() {
	if (loginType == 'h5') {
		uni.showModal({
			title: i18n.t(`提示`),
			content: i18n.t(`确认退出登录`),
			success: function(res) {
				if (res.confirm) {
					getLogout()
						.then(res => {
							// uni.clearStorage()
							appStore.LOGOUT();
							uni.reLaunch({
								url: '/pages/index/index'
							})
						})
						.catch(err => {});
				} else if (res.cancel) {}
			}
		});
	}
}
/**
 * 获取用户详情
 */
function getUserInfo() {
	getUserInfoApi().then(res => {
		userInfo.value = res.data;
		let switchUserInfoNew = res.data.switchUserInfo || [];
		for (let i = 0; i < switchUserInfoNew.length; i++) {
			if (switchUserInfoNew[i].uid == userInfo.value.uid) userIndex.value = i;
			// 切割h5用户；user_type状态：h5、routine（小程序）、wechat（公众号）；注：只有h5未注册手机号时，h5才可和小程序或是公众号数据想通；
			//#ifdef H5
			if (
				!Auth.isWeixin() &&
				switchUserInfoNew[i].user_type != "h5" &&
				switchUserInfoNew[i].phone === ""
			)
				switchUserInfoNew.splice(i, 1);
			//#endif
		}
		switchUserInfo.value = switchUserInfoNew;
	});
}
/**
 * 上传文件
 *
 */
function uploadpic() {
	canvasStatus.value = true
	util.uploadImageChange('upload/image', (res) => {
		let userInfoItem = switchUserInfo.value[userIndex.value];
		if (userInfoItem !== undefined) {
			userInfo.value.avatar = res.data.url;
		}
		switchUserInfo.value[userIndex.value] = userInfoItem;
		canvasStatus.value = false
	}, (res) => {
		canvasStatus.value = false
	}, (res) => {
		canvasWidth.value = res.w
		canvasHeight.value = res.h
	});
}
// 微信头像获取
function onChooseAvatar(e) {
	const avatarUrl = e?.detail?.avatarUrl;
	if (!avatarUrl) return;
	util.uploadImgs('upload/image', avatarUrl, (res) => {
		userInfo.value.avatar = res.data.url
	}, (err) => {
		console.log(err)
	})
}
function onNicknameInput(e) {
	const nickname = e?.detail?.value;
	if (typeof nickname === 'string') userInfo.value.nickname = nickname;
}
/**
 * 提交修改
 */
function formSubmit(e) {
	let value = e.detail.value,
		userInfoItem = switchUserInfo.value[userIndex.value];
	if (!value.nickname) return util.Tips({
		title: i18n.t(`请输入姓名`)
	});
	if (value.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.email)) return util.Tips({
		title: i18n.t(`邮箱格式错误`)
	});
	value.avatar = userInfo.value.avatar;
	userEdit(value).then(res => {
		return util.Tips({
			title: res.msg,
			icon: 'success'
		}, {
			tab: 3,
			url: 1
		});
	}).catch(msg => {
		return util.Tips({
			title: msg || i18n.t(`保存失败`)
		}, {
			tab: 3,
			url: 1
		});
	});
}
</script>

<style scoped lang="scss">
	.cartcolor {
		color: var(--view-theme);
		border: 1px solid var(--view-theme);
	}

	.personal-data {
		padding-bottom: 50rpx;
		padding-bottom: calc(50rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		padding-bottom: calc(50rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/
	}

	.personal-data .wrapper {
		margin: 10rpx 0;
		background-color: #fff;
		padding: 36rpx 30rpx 13rpx 30rpx;
	}

	.personal-data .wrapper .title {
		margin-bottom: 30rpx;
		font-size: 32rpx;
		color: #282828;
	}

:deep(.uni-input-input){
		font-size: 28rpx;
	}

	.personal-data .wrapper .wrapList .item {
		width: 690rpx;
		height: 160rpx;
		background-color: #f8f8f8;
		border-radius: 20rpx;
		margin-bottom: 22rpx;
		padding: 0 30rpx;
		position: relative;
		border: 2rpx solid #f8f8f8;
		box-sizing: border-box;
	}

	.personal-data .wrapper .wrapList .item.on {
		border-color: #e93323;
		border-radius: 20rpx;
		background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAArIAAACgCAYAAADw+I85AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyhpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQ1IDc5LjE2MzQ5OSwgMjAxOC8wOC8xMy0xNjo0MDoyMiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTkgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6M0QzNkY3NzlCNzJCMTFFOTgyNEU4QzhGQTRFRUY2REQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6M0QzNkY3N0FCNzJCMTFFOTgyNEU4QzhGQTRFRUY2REQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDozRDM2Rjc3N0I3MkIxMUU5ODI0RThDOEZBNEVFRjZERCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDozRDM2Rjc3OEI3MkIxMUU5ODI0RThDOEZBNEVFRjZERCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pn3rJMAAAArUSURBVHja7N3NXuLIGsDhqigK2Ou+grmEuf/t2fT+bOYKZn9aW5Q6qaQSIoKfoCQ8z29QRBSBzX+q31RiSikAAMDYVF4CAACELAAACFkAABCyAAAIWQAAELIAACBkAQAQsgAAIGQBAEDIAgCAkAUAQMgCAICQBQAAIQsAgJAFAAAhCwAAQhYAACELAABCFgAAhCwAAAhZAACELAAACFkAABCyAAAIWQAAELIAACBkAQAQsgAAIGQBAEDIAgCAkAUAQMgCAICQBQAAIQsAgJAFAAAhCwAAQhYAACELAABCFgAAhCwAAAhZAACELAAACFkAABCyAAAIWQAAELIAACBkAQAQsgAAIGQBAEDIAgCAkAUAQMgCAICQBQAAIQsAgJAFAAAhCwAAQhYAACELAABCFgAAhCwAAAhZAACELAAACFkAABCyAAAIWQAAELIAACBkAQBAyAIAIGQBAEDIAgCAkAUAQMgCAMAJuPQSAABMy79///XaXfJi5qy0YFUuqVzW9eWhvqzK9b1+/vpHyAIAcMjCqxs1tldj/zHl/6oU4rz+ctY2a3tzjO2n0F6tUqobMYZ5fX1V337XBm0MMbX3SuXnvv1peqcBAKYlXl+VSI2lZJuIzSuwi7pUY3/HFPsijYMPcVOps9hG7W19fRVT+50YT6TXvdUAABML2at5V6rdTdfNSmzXquX2FOKTr7trsVvBjeVOISzLyuyfNnNTOIWWFbIAAFNzfd2umjYrsmlWR+i8KuusXbhurudZgTZpU6w/p82Ka0oldJvb47z+cp3HDU5kQVbIAgBMTVwsmzitr1V1ni5C07Pd5EAXtCVlm3BNTfS27dvGbAiDcYPUr9TWvys91jetT2BEVsgCAEwuZOeLJkDr/+Z5sbXdb7UdCIixb9M2WDdjss2n4X274YN2LraJ3fzjeUTh9yk8TyELADC1kM0rsjHVTRpnTYam2I8LNBOuaRO0TbaWbQhidyRYKveLmz0P+vu223ZV8ZWtuYQsAADvD9nlTTMb23/dxelg9TUM4nSzRLvZsSANf274u9uvZnXm/hGyAAAcVHWzzKusl5uDtvq9YtvvpzZJmwGC+GS1tR83iHuGYMuPXtbfF7IAABxWXP7IyVkNT4awGQ/Y7FswHBkIW9e7W1Kfv0/GDKpTeJ5CFgBgapbLPAJQxX5X2DIuEPsdYtsSTak/nKv5Xir7GQxWZNvvlZGC/pReUcgCAHB41c2PnbfHrc+v3bbv61MhZAEAJibmkE1pXRdo9SRDuxXVuJWp3XBsGYDdfL9frx38jub767LVgZAFAOCAIdvsWpBjs5tlHZx4tvmQNhsVdH1bAjYO9pTtrlX9cEJvfQrPU8gCAExMdXOTPz3knQvCk/1iU4iDhO3HCuKT8yK0v6P/mfL9wTFf9W0PpzBvIGQBACYmLm7yOMCqDtB5f6hXak94UFo0lPMklO22ykFfg71mNyu3/ZkUNltz1b+7vYOQBQDgkCG7vMmxmWdkVyGfiWvH3rD9yWeb22O/KVdfuqVy29HZOBwuWKVmbEHIAgBw6JBdLMqKaryLMV3GwRFcqRykVXWt2g0V9KfyimV7rsEEbTkILLbDCXftqIGDvQAAOLTFsjtxwbrOzds6PJcpPT8pQnctlV6N/XlsBwd9lZXcsp/sbZXiuszJClkAAA4rzuclUpsl11UdoXcxxXm709Zg7rUp1fJ13KzKDnbfGhwQFu/qr1fdoGwUsgAAHD5kF32JlhD9E5ots+KiCv0JvAZzr3GzPUGJ235lNo8TpHjbBnF373QSz1PIAgBMLWSvrtoQTf3ga5YP0nqsP89jPgCs7dz2Q4xhu03T5mfuYnNyhTjYzSAE228BALDXv3//9aGf+/mf/5ai3Zy0q4wOrGOIv1NoznEwq0P3sv66yl+XLs0ztfV9wkOO2NieVKFP29SeKqyP2I/+fUIWAIDdZrP+6nDhdDMa0JyZ60+57LvPM9+0CJsfttq6NMetCVkAgIn57pXST0Zr7tOLEqzd552ELAAA3x2u3aV6zw8LWQAAvlKO1Vm5XHzmFwlZAABGE69CFgDgDb5z1vTnr3+m8BLmcL06VnMKWQCAwzRVt9rYHVWf5c2r8g4Bef/WVWi3tZq6WF6L6/DOmVchCwDwdcGWY+0q7N+ZKpa4vSj3y2F7H9ptr9IZvh5CFgDgm+UVx8UHgm0Ye7ehXaUVsEIWAOBLLEq0fTb+lqFdnb0d8WtxXS7fcq4EIQsA8HY5Pmc7bs9jAt0MbJ6HXZe460YLuhna7eDrVjF/j+x1yM9lHo48AytkAQAOY7EnYu9Cu7KadsRtd7DXqtzvqgTgdhTm3z2Gldmq/K0n0ZBCFgDgdd02UkM5UPNK6uMbf0eO2nyQV161XYanq5lX5fZTnpn91jGCfVUNAMB+OdwWOyL2f++I2KHH8rPrrds/cvDYV/XiTWhXkuOp/WEAAOy3axXy944QfY9uNXc7mK9P7Lnnlegf4UT/FV/IAgC8bHukII8HPB7g9z6W3/XSY32nvEK8DKe5SixkAQBecbkVcmlHfH7G9okRYvj+1c/chz9OLKqFLADAO23vUrAKhz0jV7dt10uP+dXhniP2YgxvjpAFANhvO+gejvAYD6885lfJK7D5oK44ljfH9lsAAPttL/o9HuExHl95zK+QdyS4HtubI2QBAPbbXp1cH+Ex1q885rEd4pS7J/F/GQAAvD1sx260EStkAQBelr4gZKtXHvNYlmOOWCELAPCy7X/2P8aBWBevPOYx5JXY2djfHCELALDf9oFYxzi+6PKVxzxGxF5N4c0RsgAA++3a4/WQ4wUxPF8ZfTji85lPJWKFLADAy3JUbp9565DbVF2H52cOWx3puczCCLfYErIAAB93vyM+DzEre7EjLO+P9Bzy+MJyam+MkAUAeNmf8HwngeUnO6raEZapPNYxem85xTdGyAIAvCwH5u2Ohsqnc/3IyuxF+dntDrsNh996K5aIjVN8Y4QsAMDr8tzq/Y6O+hHaA6jeEoqx3PfHjga7D8eZjZ2H42wZdhKcohYA4G1uw+5dBvKc61UJ0XxZh81esFW5zML+HQ9W4fmK7yHMwoR2KBCyAACf8zvs3oc1ltveG473R4rYqvydkyZkAQDeJ4fnQwnFj86ednO3x9pq6zN/m5AFAJiwVYnZbqzgrdGYAzavwu7aCeFQrs6l8YQsAMDH5BC9K5fcVHkmNR9YVQ3CNt8nz8s+DuL3mPJjz8/lDRCyAACf9/AFkfoWZzFSIGQBAF7x89c/Y/pzZ+fWdvaRBQCYhvm5PWEhCwAwftfn2HVCFgBg3GIJ2bMjZAEAxu06nNEBXkIWAGAaujOKnSUhCwAwXme7GitkAQDG66xXY2tJyAIAjNMsnPFqbG0tZAEAxun6zJ+/kAUAGKF8Bq9z77hHIQsAMD5XXoLwIGQBAMYlz8XOzvw1WAcrsgAAo2M1NoRV/iBkAQDGZeYlCPdCFgBgXHK7XYjYZrRAyAIAjMi5r8am+nI3rHoAAITsGNyWmBWyAAAjkncrOOexgjxSsBreIGQBAMbh8oyfew7Y2+0bhSwAgJA9ZQ+7Ivbcyx4AQMietvt9EStkAQDGIc/HntO/pKcSsCtlDwAwbufUbHkV9i4MdifwogAAjNfUdyvIJzhYhcHJDtQ9AMA0TGmsIJVYzZfH0B7M9fiRX/R/AQYA1i4UF+HkevkAAAAASUVORK5CYII=");
		background-size: 100% 100%;
		background-color: #fff9f9;
		background-repeat: no-repeat;
	}

	.personal-data .wrapper .wrapList .item .picTxt {
		width: 445rpx;
	}

	.personal-data .wrapper .wrapList .item .picTxt .pictrue {
		width: 96rpx;
		height: 96rpx;
		position: relative;
	}

	.personal-data .wrapper .wrapList .item .picTxt .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
	}

	.personal-data .wrapper .wrapList .item .picTxt .pictrue .alter {
		width: 30rpx;
		height: 30rpx;
		border-radius: 50%;
		position: absolute;
		bottom: 0;
		right: 0;
	}

	.personal-data .wrapper .wrapList .item .picTxt .text {
		width: 325rpx;
	}

	.personal-data .wrapper .wrapList .item .picTxt .text .name {
		width: 100%;
		font-size: 30rpx;
		color: #282828;
	}

	.personal-data .wrapper .wrapList .item .picTxt .text .phone {
		font-size: 24rpx;
		color: #999;
		margin-top: 10rpx;
	}

	.personal-data .wrapper .wrapList .item .bnt {
		font-size: 24rpx;
		background-color: #fff;
		border-radius: 27rpx;
		width: 140rpx;
		height: 54rpx;
		border: 2rpx solid #e93323;
	}

	.personal-data .wrapper .wrapList .item .currentBnt {
		position: absolute;
		right: 0;
		top: 0;
		font-size: 26rpx;
		background-color: rgba(233, 51, 35, 0.1);
		width: 140rpx;
		height: 48rpx;
		border-radius: 0 20rpx 0 20rpx;
	}

	.personal-data .list {
		margin-top: 15rpx;
		background-color: #fff;
	}

	.personal-data .list .item {
		padding: 30rpx 30rpx 30rpx 0;
		border-bottom: 1rpx solid #f2f2f2;
		margin-left: 30rpx;
		font-size: 28rpx;
		color: #282828;
	}

	.personal-data .list .item .phone {
		width: 160rpx;
		height: 56rpx;
		font-size: 24rpx;
		color: #fff;
		line-height: 56rpx;
		border-radius: 32rpx
	}

	.personal-data .list .item .pictrue {
		width: 88rpx;
		height: 88rpx;
	}

	.personal-data .list .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
	}

	.personal-data .list .item .input {
		flex: 1;
		text-align: right;
		color: #868686;
		font-size: 28rpx;

		input {
			padding-right: 10rpx;
		}

		.icon-suozi {
			margin-left: 10rpx;
		}
	}

	.personal-data .list .item .input .id {
		// width: 180rpx;
	}

	.personal-data .list .item .input .iconfont {
		font-size: 24rpx;
	}

	.personal-data .modifyBnt {
		font-size: 32rpx;
		color: #fff;
		width: 690rpx;
		height: 90rpx;
		border-radius: 50rpx;
		text-align: center;
		line-height: 90rpx;
		margin: 76rpx auto 0 auto;
	}

	.personal-data .logOut {
		font-size: 32rpx;
		text-align: center;
		width: 690rpx;
		height: 90rpx;
		border-radius: 45rpx;
		margin: 30rpx auto 0 auto;
	}

	.avatar-box {
		width: 96rpx;
		height: 96rpx;

		image {
			width: 100%;
			height: 100%;
			border-radius: 50%;
		}
	}
</style>
