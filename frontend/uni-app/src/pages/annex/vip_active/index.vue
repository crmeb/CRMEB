<template>
	<view :style="assetStyle">
		<view class="head">
			<image class="image" src="@/pages/users/static/member-wang.png"></image>
			<view class="title">
				<view class="line"></view>
				<image class="font" src="@/pages/users/static/member-font.png"></image>
				<view class="line"></view>
			</view>
		</view>
		<form class="form" @submit="active">
			<input class="input" name="account" :placeholder="$t(`请输入卡号`)" placeholder-style="color:#C38D5D" />
			<input class="input" name="password" :placeholder="$t(`请输入卡密`)" placeholder-style="color:#C38D5D" password />
			<button class="button" form-type="submit">{{$t(`确认激活`)}}</button>
		</form>
		<!-- 会员权益 -->
		<view class="right-section">
			<view class="section-hd acea-row row-center-wrapper">
				<view class="title acea-row row-center row-bottom"><text class="iconfont icon-huiyuan2"></text>{{$t(`SVIP会员尊享权`)}}</view>
			</view>
			<view class="section-bd acea-row row-between-wrapper">
				<view v-for="item in memberRights" :key="item.id" class="acea-row row-middle item">
					<image class="image" :src="item.pic"></image>
					<view class="text">
						<view class="name">{{item.title || ''}}</view>
						<view>{{item.explain || ''}}</view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script setup>
	import { ref } from 'vue';
	import { onLoad } from '@dcloudio/uni-app';
	import {
		memberCard as memberCardApi,
		memberCardDraw
	} from '@/api/user.js';
	import i18n from '@/utils/lang.js';
	import { HTTP_REQUEST_URL } from '@/config/app';

	const assetStyle = `--ui-vip-active-head:url(${HTTP_REQUEST_URL}/statics/images/vip-active-head.png);--ui-vip-active-form:url(${HTTP_REQUEST_URL}/statics/images/vip-active-form.png);--ui-vip-section-title:url(${HTTP_REQUEST_URL}/statics/images/vip-section-title.png);`;

	// ---------- data ----------
	const memberRights = ref([]);

	onLoad(() => {
		getMemberCard();
	});

	// ---------- methods ----------
	// 会员权益
	function getMemberCard() {
		uni.showLoading({
			title: i18n.t(`正在加载中`)
		});
		memberCardApi().then(res => {
			uni.hideLoading();
			memberRights.value = res.data.member_rights;
		}).catch(err => {
			uni.showToast({
				title: err,
				icon: 'none'
			});
		});
	}
	// 激活
	function active(e) {
		let formData = e.detail.value,
			data = {
				member_card_code: '',
				member_card_pwd: '',
				from: 'weixinh5'
			};
		if (!formData.account) {
			return uni.showToast({
				title: i18n.t(`请输入卡号`),
				icon: 'none'
			});
		}
		if (!formData.password) {
			return uni.showToast({
				title: i18n.t(`请输入卡密`),
				icon: 'none'
			});
		}
		data.member_card_code = formData.account;
		data.member_card_pwd = formData.password;
		// #ifdef H5
		let ua = navigator.userAgent.toLowerCase();
		if (ua.match(/MicroMessenger/i) == 'micromessenger') {
			data.from = 'weixin'
		}
		// #endif
		// #ifdef MP
		data.from = 'routine';
		// #endif
		uni.showLoading({
			title: i18n.t(`正在激活`)
		});
		memberCardDraw(data).then(res => {
			uni.showToast({
				title: res.msg,
				icon: 'success'
			});
			uni.navigateTo({
				url: '/pages/annex/vip_paid/index'
			});
		}).catch(err => {
			uni.showToast({
				title: err,
				icon: 'none'
			});
		});
	}
</script>

<style>
	page {
		background-color: #FFFFFF;
	}
</style>

<style lang="scss" scoped>
	.head {
		padding-top: 37rpx;
		padding-bottom: 108rpx;
		background-image: var(--ui-vip-active-head);
		background-position: center;
		background-size: cover;
		background-repeat: no-repeat;

		.image {
			display: block;
			width: 66rpx;
			height: 52rpx;
			margin-right: auto;
			margin-left: auto;
		}

		.title {
			display: flex;
			justify-content: center;
			align-items: center;
			margin-top: 22rpx;

			.line {
				width: 70rpx;
				height: 2rpx;
				background-color: #E6C3A5;
			}

			.font {
				width: 215rpx;
				height: 40rpx;
				margin-right: 25rpx;
				margin-left: 25rpx;
			}
		}
	}

	.form {
		display: block;
		padding: 57rpx 55rpx 53rpx;
		margin: -57rpx 30rpx 30rpx;
		background-image: var(--ui-vip-active-form);
		background-position: center;
		background-size: 100% 100%;
		background-repeat: no-repeat;

		.input {
			height: 80rpx;
			padding-right: 28rpx;
			padding-left: 28rpx;
			border-radius: 6rpx;
			border: 1px solid rgba(255, 255, 255, 0.2);
			background: rgba(254, 247, 236, 0.4);
			font-size: 28rpx;

			~.input {
				margin-top: 30rpx;
			}
		}

		.button {
			height: 80rpx;
			border-radius: 40rpx;
			margin-top: 50rpx;
			background: #353841;
			font-size: 32rpx;
			line-height: 80rpx;
			color: #FFFFFF;
		}
	}

	.right-section {
		background-color: #FFFFFF;

		.section-hd {
			padding-top: 34rpx;
			padding-bottom: 34rpx;

			.title {
				width: 543rpx;
				height: 90rpx;
				background-image: var(--ui-vip-section-title);
				background-position: center;
				background-size: cover;
				background-repeat: no-repeat;
				font-size: 34rpx;
				color: #89735B;
			}

			.iconfont {
				font-size: 34rpx;
				color: #89735B;
			}
		}

		.section-bd {
			padding: 0 30rpx 15rpx;

			.item {
				width: 338rpx;
				height: 140rpx;
				padding-left: 30rpx;
				border-radius: 6rpx;
				margin-bottom: 14rpx;
				background-color: #F7F7F7;
			}

			.image {
				width: 80rpx;
				height: 80rpx;
			}

			.text {
				flex: 1;
				min-width: 0;
				margin-left: 22rpx;
				overflow: hidden;
				white-space: nowrap;
				font-size: 24rpx;
				line-height: 33rpx;
				color: #9A856D;
			}

			.name {
				margin-bottom: 2rpx;
				font-size: 26rpx;
				line-height: 37rpx;
				color: #333333;
			}
		}
	}
</style>
