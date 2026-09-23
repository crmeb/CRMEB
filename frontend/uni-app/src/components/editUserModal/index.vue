<template>
	<view :style="colorStyle">
		<view class="product-window" :class="{'on':isShow}">
			<view class="iconfont icon-guanbi" @click="closeAttr"></view>
			<view class="mp-data">
				<image :src="mpData.siteLogo" mode=""></image>
				<text class="mp-name">{{mpData.siteName}} 申请</text>
			</view>
			<view class="trip-msg">
				<view class="title">
					{{$t(`获取您的昵称、头像`)}}
				</view>
				<view class="trip">
					{{$t(`提供具有辨识度的用户中心界面`)}}
				</view>
			</view>
			<form @submit="formSubmit">
				<view class="edit">
					<view class="avatar edit-box">
						<view class="left">
							<view class="head">{{$t(`头像`)}}</view>
							<!-- <image :src="userInfo.avatar || defaultAvatar" mode=""></image> -->
							<view class="avatar-box" v-if="!mp_is_new" @click.stop='uploadpic'>
								<image :src="userInfo.avatar || defHead"></image>
							</view>
							<button v-else class="avatar-box" open-type="chooseAvatar" @chooseavatar="onChooseAvatar">
								<image :src="userInfo.avatar || defHead"></image>
							</button>
						</view>
						<!-- <view class="iconfont icon-xiangyou"></view> -->
					</view>
					<view class="nickname edit-box">
						<view class="left">
							<view class="head">{{$t(`昵称`)}}</view>
							<view class='input'>
								<!-- #ifdef MP-WEIXIN -->
								<input type="nickname" placeholder-class="pl-sty"
									:placeholder="$t(`请输入昵称`)" name="nickname" :maxlength="16"
									:value="userInfo.nickname" @input="onNicknameInput" />
								<!-- #endif -->
								<!-- #ifndef MP-WEIXIN -->
								<input type="text" placeholder-class="pl-sty"
									:placeholder="$t(`请输入昵称`)" name="nickname" :maxlength="16"
									v-model="userInfo.nickname" />
								<!-- #endif -->
							</view>
						</view>
						<!-- <view class="iconfont icon-xiangyou"></view> -->
					</view>

				</view>

				<view class="bottom">
					<button class="save" formType="submit" :class="{'open': userInfo.avatar}">
						{{$t(`保存`)}}
					</button>
				</view>
			</form>
		</view>
		<canvas canvas-id="canvas" v-if="canvasStatus"
			:style="{width: canvasWidth + 'px', height: canvasHeight + 'px',position: 'absolute',left:'-100000px',top:'-100000px'}"></canvas>
		<view class="mask" @touchmove.prevent v-if="isShow" @click="closeAttr"></view>
	</view>

</template>

<script setup>
	import {
		ref,
		reactive
	} from 'vue';
	import {
		useColorTheme
	} from '@/composables/useColorTheme';
	import Cache from '@/utils/cache';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';
	import defHeadImg from '@/static/images/def_avatar.png';
	import {
		userEdit,
	} from '@/api/user.js';

	const props = defineProps({
		isShow: {
			type: Boolean,
			default: false
		}
	});

	const emit = defineEmits(['closeEdit', 'editSuccess']);

	const {
		colorStyle
	} = useColorTheme();

	// ---------- data ----------
	// 原 require('@/static/images/def_avatar.png')，Vue3/Vite 下改为 ESM 静态导入
	const defHead = defHeadImg;
	const mp_is_new = ref(Boolean(Cache.get('MP_VERSION_ISNEW')));
	const userInfo = reactive({
		avatar: '',
		nickname: '',
	});
	const mpData = ref(uni.getStorageSync('copyRight') || {
		siteLogo: '',
		siteName: ''
	});
	const canvasStatus = ref(false);
	// 原实现挂在实例上但未在 data 声明（Vue2 非响应式），此处补声明为 ref
	const canvasWidth = ref(0);
	const canvasHeight = ref(0);

	// ---------- methods ----------
	/**
	 * 上传文件
	 *
	 */
	function uploadpic() {
		canvasStatus.value = true
		util.uploadImageChange('upload/image', (res) => {
			let info = userInfo;
			if (info !== undefined) {
				userInfo.avatar = res.data.url;
			}
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
			userInfo.avatar = res.data.url
		}, (err) => {
			console.log(err)
		})
	}
	function onNicknameInput(e) {
		const nickname = e?.detail?.value;
		if (typeof nickname === 'string') userInfo.nickname = nickname;
	}

	function closeAttr() {
		emit('closeEdit');
	}
	/**
	 * 提交修改
	 */
	function formSubmit(e) {
		if (!userInfo.avatar) return util.Tips({
			title: i18n.t(`请上传头像`)
		});
		const nickname = e?.detail?.value?.nickname || userInfo.nickname;
		if (!nickname) return util.Tips({
			title: i18n.t(`请输入昵称`)
		});
		userInfo.nickname = nickname
		userEdit(userInfo).then(res => {
			emit('editSuccess')
			return util.Tips({
				title: res.msg,
				icon: 'success'
			}, {
				tab: 3
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
<style>
	.pl-sty {
		color: #999999;
		font-size: 30rpx;
	}
</style>
<style scoped lang="scss">
	.product-window.on {
		transform: translate3d(0, 0, 0);
	}

	.mask {
		z-index: 99;
	}

	.product-window {
		position: fixed;
		bottom: 0;
		width: 100%;
		left: 0;
		background-color: #fff;
		z-index: 1000;
		border-radius: 20rpx 20rpx 0 0;
		transform: translate3d(0, 100%, 0);
		transition: all .3s cubic-bezier(.25, .5, .5, .9);
		padding: 38rpx 40rpx;
		padding-bottom: 80rpx;
		padding-bottom: calc(80rpx + constant(safe-area-inset-bottom)); ///兼容 IOS<11.2/
		padding-bottom: calc(80rpx + env(safe-area-inset-bottom)); ///兼容 IOS>11.2/

		.icon-guanbi {
			position: absolute;
			top: 40rpx;
			right: 40rpx;
			font-size: 24rpx;
			font-weight: bold;
			color: #999;
		}

		.mp-data {
			display: flex;
			align-items: center;
			margin-bottom: 30rpx;

			.mp-name {
				font-size: 28rpx;
				font-weight: bold;
				color: #000000;
			}

			image {
				width: 48rpx;
				height: 48rpx;
				border-radius: 50%;
				margin-right: 16rpx;
			}
		}

		.trip-msg {
			padding-bottom: 32rpx;
			border-bottom: 1px solid #F5F5F5;

			.title {
				font-size: 30rpx;
				font-weight: bold;
				color: #000;
				margin-bottom: 6rpx;
			}

			.trip {
				font-size: 26rpx;
				color: #777777;
			}
		}

		.edit {
			border-bottom: 1px solid #F5F5F5;

			.avatar {
				border-bottom: 1px solid #F5F5F5;
			}

			.nickname {
				.input {
					width: 100%;

				}

				input {
					height: 80rpx;
				}
			}

			.edit-box {
				display: flex;
				justify-content: space-between;
				align-items: center;
				font-size: 30rpx;
				padding: 22rpx 0;

				.left {
					display: flex;
					align-items: center;
					flex: 1;

					.head {
						color: rgba(0, 0, 0, 0.9);
						white-space: nowrap;
						margin-right: 60rpx;
					}

					button {
						flex: 1;
						display: flex;
						align-items: center;
					}
				}

				image {
					width: 80rpx;
					height: 80rpx;
					border-radius: 6rpx;
				}
			}

			.icon-xiangyou {
				color: #cfcfcf;
			}
		}

		.bottom {
			display: flex;
			align-items: center;
			justify-content: center;

			.save {
				border: 1px solid #F5F5F5;
				display: flex;
				align-items: center;
				justify-content: center;
				width: 368rpx;
				height: 80rpx;
				border-radius: 12rpx;
				margin-top: 52rpx;
				background-color: #F5F5F5;
				color: #ccc;
				font-size: 30rpx;
				font-weight: bold;
			}

			.save.open {
				border: 1px solid #fff;
				background-color: #07C160;
				color: #fff;
			}
		}
	}
</style>
