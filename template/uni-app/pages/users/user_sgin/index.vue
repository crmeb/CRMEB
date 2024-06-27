<template>
	<view :style="colorStyle">
		<view class="sign" :style="colorStyle">
			<view class="header bgcolor" :style="'background-image: url(' + sginBg + ');'">
				<view class="headerCon acea-row row-between-wrapper">
					<view class="left acea-row row-between-wrapper">
						<img :src="sginTip" alt="" srcset="" />
					</view>
					<navigator class="right acea-row row-middle" hover-class="none" url="/pages/users/user_sgin_list/index">
						<view>{{ $t(`明细`) }}</view>
					</navigator>
				</view>
			</view>
			<view class="wrapper">
				<view class="sgin-num">
					<view class="text">
						<text>已连续签到</text>
						<text class="num">{{ continuousSignDays }}</text>
						<text>天</text>
					</view>
					<view class="tip" v-if="signRemindSwitch == 1">
						<text class="mr16">签到提醒</text>
						<switch :checked="remindStatus" color="#FFCC33" @change="changeRemind" />
					</view>
				</view>
				<view class="list acea-row row-between-wrapper">
					<template v-if="signMode == 0 || signMode == -1">
						<view class="mot-item" v-for="(item, index) in signSystemList" :key="index">
							<view class="row" :class="{ 'sgin-day': e.sign_day, 'last-day': e.is_sign }" v-for="(e, i) in item"
								:key="i">
								<view class="type-img">
									<img v-if="!e.is_sign" :src="getTypeImg(e.type, e.is_sign)" alt="" srcset="" />
									<text v-else class="iconfont icon-xuanzhong1"></text>
								</view>
								<view class="venus">{{ e.day }}</view>
							</view>
						</view>
					</template>
					<template v-else>
						<view class="mot-item" v-for="(item, index) in signSystemList" :key="index">
							<view class="mot-item-box" :class="{ 'sgin-day': e.sign_day, 'last-day': e.is_sign }"
								v-for="(e, i) in item" :key="i">
								<view class="row">
									<view class="num">+{{ e.point }}</view>
									<view class="type-img">
										<img v-if="!e.is_sign" :src="getTypeImg(e.type, e.is_sign)" alt="" srcset="" />
										<text v-else class="iconfont icon-xuanzhong1"></text>
									</view>
								</view>
								<view class="text">{{weekArr[i]}}</view>
							</view>
						</view>
					</template>
				</view>
				<button class="but bg-color on" v-if="checkSign">{{ $t(`今日已签到，明日再来吧`) }}</button>
				<form @submit="goSign" v-else>
					<button class="but bg-color" formType="submit">{{ $t(`立即签到`) }}</button>
				</form>
				<view class="tip" v-if="nextContinuousDays > 0">
					<img :src="`${imgHost}/statics/images/sgin_icon_4.png`" alt="" />
					再连续签到{{ nextContinuousDays }}天，可额外获得惊喜礼包
				</view>
				<view class="lock"></view>
			</view>
			<view class="wrapper wrapper2">
				<view class="tip">{{ $t(`已累计签到`) }}</view>
				<view class="list2 acea-row row-center row-bottom">
					<view class="item">{{ signCount[0] || 0 }}</view>
					<view class="item">{{ signCount[1] || 0 }}</view>
					<view class="item">{{ signCount[2] || 0 }}</view>
					<view class="item">{{ signCount[3] || 0 }}</view>
					<view class="data">{{ $t(`天`) }}</view>
				</view>
				<view class="tip2" v-if="nextCumulativeDays > 0">
					<img :src="`${imgHost}/statics/images/sgin_icon_4.png`" alt="" />
					{{ $t(`再累计签到`) }}{{ nextCumulativeDays }}{{ $t(`天，可额外获得惊喜礼包`) }}
				</view>
				<view class="list3" v-if="signList.length">
					<view class="item acea-row" v-for="(item, index) in signList" :key="index">
						<view>
							<view class="name line1">{{ $t(item.title) }}</view>
							<view class="data">{{ item.add_time }}</view>
						</view>
						<view class="num">+{{ item.number }}</view>
					</view>
					<view class="loading" @click="goSignList" v-if="signList.length >= 8">
						{{ $t(`点击加载更多`) }}
						<text class="iconfont icon-xiangyou"></text>
					</view>
				</view>
			</view>
			<view class="signTip acea-row row-center-wrapper" :class="active == true ? 'on' : ''">
				<view class="signTipLight loadingpic"></view>
				<view class="signTipCon">
					<view class="signHeight">
						<image src="../static/signH.png"></image>
					</view>
					<view class="state">{{ $t(`签到成功`) }}</view>
					<view class="integral">{{ $t(`获得`) }}{{ integral }}{{ $t(`积分`) }}</view>
					<view class="signTipBnt" @click="close">{{ $t(`好的`) }}</view>
				</view>
			</view>
			<view class="mask" @touchmove.stop.prevent="false" :hidden="active == false"></view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from 'vuex';
	import {
		postSignUser,
		getSignConfig,
		getSignList,
		setSignIntegral,
		changeRemindStatus
	} from '@/api/user.js';
	import {
		setFormId,
		colorChange
	} from '@/api/api.js';
	import colors from '@/mixins/color';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	export default {
		components: {
			// #ifdef MP
			authorize
			// #endif
		},
		mixins: [colors],
		data() {
			return {
				active: false,
				userInfo: {},
				signCount: [],
				signSystemList: [],
				signList: [],
				integral: 0,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false, //是否隐藏授权
				sign_index: 0,
				picUrl: [],
				imgHost: HTTP_REQUEST_URL,
				sginBg: '',
				sginTip: '',
				signMode: 0, // 0月签到 1周签到
				nextContinuousDays: 0,
				nextCumulativeDays: 0,
				continuousSignDays: 0,
				signRemindSwitch: 0,
				checkSign: 0,
				remindStatus: false,
				weekArr: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.getUserInfo();
						this.getSignSysteam();
						this.getSignList();
					}
				},
				deep: true
			}
		},
		onLoad() {
			if (this.isLogin) {
				this.getColor();
				this.getUserInfo();
				this.getSignSysteam();
				this.getSignList();
			} else {
				toLogin();
			}
		},
		methods: {
			/**
			 * 授权回调
			 */
			onLoadFun: function() {
				this.getUserInfo();
				this.getSignSysteam();
				this.getSignList();
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e;
			},
			getColor() {
				colorChange('color_change').then((res) => {
					this.sginBg = `${this.imgHost}/statics/images/sgin_bg_${res.data.status}.png`;
					this.sginTip = `${this.imgHost}/statics/images/sgin_tip_${res.data.status}.png`;
					let theme = ['#1db0fc', '#42CA4D', '#e93323', '#ff448f', '#FE5C2D'];
					uni.setNavigationBarColor({
						frontColor: '#ffffff', // 必写项
						backgroundColor: theme[res.data.status - 1] // 必写项
					});
				});
			},
			/**
			 * 获取签到配置
			 */
			getSignSysteam: function() {
				let that = this;
				getSignConfig().then((res) => {
					if (!res.data.signStatus) {
						return that.$util.Tips({
							title: that.$t(`签到功能已关闭`)
						}, {
							tab: 3
						});
					}
					that.$set(that, 'signSystemList', res.data.signList);
					that.signMode = res.data.signMode;
					that.nextContinuousDays = res.data.nextContinuousDays;
					that.nextCumulativeDays = res.data.nextCumulativeDays;
					that.continuousSignDays = res.data.continuousSignDays;
					that.signRemindSwitch = res.data.signRemindSwitch;
					that.checkSign = res.data.checkSign;
					that.remindStatus = !!res.data.signRemindStatus;
					that.signCount = that.PrefixInteger(res.data.cumulativeSignDays, 4);
				});
			},
			changeRemind(e) {
				let status = e.detail.value ? 1 : 0;
				changeRemindStatus(status).then((res) => {
					console.log(res);
				});
			},
			getTypeImg(type, isSgin) {
				let src;
				if (isSgin) {
					src = `${this.imgHost}/statics/images/sgin_suc_1.png`;
					return src;
				}
				switch (type) {
					case 1:
						src = `${this.imgHost}/statics/images/sgin_icon_1.png`;
						break;
					case 2:
						src = `${this.imgHost}/statics/images/sgin_icon_2.png`;
						break;
					case 3:
						src = `${this.imgHost}/statics/images/sgin_icon_3.png`;
						break;
					case 4:
						src = `${this.imgHost}/statics/images/sgin_icon_3.png`;
						break;
				}
				return src;
			},
			/**
			 * 去签到记录页面
			 *
			 */
			goSignList: function() {
				return this.$util.Tips('/pages/users/user_sgin_list/index');
			},
			/**
			 * 获取用户信息
			 */
			getUserInfo: function() {
				let that = this;
				postSignUser({
					sign: 1
				}).then((res) => {
					// res.data.integral = parseInt(res.data.integral);
					// let sum_sgin_day = res.data.sum_sgin_day;
					// that.$set(that, 'userInfo', res.data);
					// // that.signCount = that.PrefixInteger(sum_sgin_day, 4);
					// that.sign_index = res.data.sign_num;
				});
			},

			/**
			 * 获取签到列表
			 *
			 */
			getSignList: function() {
				let that = this;
				getSignList({
					page: 1,
					limit: 8
				}).then((res) => {
					that.$set(that, 'signList', res.data);
				});
			},
			/**
			 * 数字转中文
			 *
			 */
			Rp: function(n) {
				let cnum = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'];
				let s = '';
				n = '' + n; // 数字转为字符串
				for (let i = 0; i < n.length; i++) {
					s += cnum[parseInt(n.charAt(i))];
				}
				return s;
			},
			/**
			 * 数字分割为数组
			 * @param int num 需要分割的数字
			 * @param int length 需要分割为n位数组
			 */
			PrefixInteger: function(num, length) {
				return (Array(length).join('0') + num).slice(-length).split('');
			},

			/**
			 * 用户签到
			 */
			goSign: function(e) {
				let that = this,
					sum_sgin_day = that.userInfo.sum_sgin_day;
				if (that.userInfo.is_day_sgin)
					return this.$util.Tips({
						title: that.$t(`您今日已签到!`)
					});
				setSignIntegral()
					.then((res) => {
						that.active = true;
						that.integral = res.data.integral;
						// that.sign_index = (that.sign_index + 1) > that.signSystemList.length ? 1 : that
						// 	.sign_index + 1;
						// that.signCount = that.PrefixInteger(sum_sgin_day + 1, 4);
						// that.$set(that.userInfo, 'is_day_sgin', true);
						// that.$set(that.userInfo, 'integral', that.$util.$h.Add(that.userInfo.integral, res.data
						// 	.integral));
						that.getSignSysteam();
						that.getSignList();
					})
					.catch((err) => {
						return this.$util.Tips({
							title: err
						});
					});
			},
			/**
			 * 关闭签到提示
			 */
			close: function() {
				this.active = false;
			}
		}
	};
</script>

<style scoped lang="scss">
	.bgcolor {
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 750rpx;
		height: 438rpx;
	}

	.sign {
		padding-bottom: 40rpx;
	}

	.sign .header {
		width: 100%;
	}

	.sign .header .headerCon {
		padding: 0 0 0 30rpx;
		height: 234rpx;
	}

	.sign .header .headerCon .left {
		width: 530rpx;
		font-size: 32rpx;
		color: #fff;
		font-weight: bold;

		img {
			width: 388rpx;
			height: 128rpx;
			margin-top: 56rpx;
		}
	}

	.sign .header .headerCon .left .integral text {
		font-size: 24rpx;
		margin-top: 19rpx;
		background-color: #ff9000;
		text-align: center;
		border-radius: 6rpx;
		font-weight: normal;
		padding: 4rpx 15rpx;
	}

	.sign .header .headerCon .text {
		width: 410rpx;
	}

	.sign .header .headerCon .left .pictrue {
		width: 100rpx;
		height: 100rpx;
		border-radius: 50%;
		border: 4rpx solid #ecddbc;
	}

	.sign .header .headerCon .left .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 50%;
	}

	.sign .header .headerCon .right {
		// width: 142rpx;
		padding: 6rpx 12rpx 6rpx 16rpx;
		border-radius: 50rpx 0 0 50rpx;
		font-size: 24rpx;
		color: #fff;
		background: rgba(255, 255, 255, 0.2);
	}

	.sign .header .headerCon .right .iconfont {
		font-size: 33rpx;
		padding: 0 10rpx 0 30rpx;
		margin-top: 5rpx;
	}

	.sign .wrapper {
		background-color: #fff;
		margin: -188rpx 20rpx 0 20rpx;
		border-radius: 15rpx;
		padding: 32rpx 32rpx 58rpx 32rpx;
		position: relative;

		.tip {
			display: flex;
			justify-content: center;
			align-items: center;
			font-size: 24rpx;
			font-weight: 400;
			color: #999999;
			line-height: 34rpx;
			margin-top: 20rpx;

			img {
				width: 26rpx;
				height: 26rpx;
				margin-right: 10rpx;
				margin-bottom: 1rpx;
			}
		}

		.sgin-num {
			display: flex;
			justify-content: space-between;
			align-items: center;
			font-size: 32rpx;
			font-weight: 500;
			color: #333333;
			line-height: 44rpx;
			margin-bottom: 38rpx;

			.num {
				font-size: 40rpx;
				font-family: D-DIN-PRO-SemiBold, D-DIN-PRO;
				font-weight: 600;
				color: #fe5c2d;
				line-height: 40rpx;
				margin: 0 4rpx;
			}

			.tip {
				display: flex;
				align-items: center;
				font-size: 24rpx;
				font-family: PingFang SC-Regular, PingFang SC;
				font-weight: 400;
				color: #333333;
				line-height: 34rpx;

				.mr16 {
					margin-right: 16rpx;
				}
			}
		}

		/deep/ uni-switch .uni-switch-input:after {
			width: 32rpx;
			height: 32rpx;
			background-color: #fff;
			margin: 4rpx 0 4rpx 4rpx;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
		}

		/deep/uni-switch .uni-switch-input:before {
			width: 72rpx;
			height: 40rpx;
			background-color: #eeeeee;
		}

		/deep/ uni-switch .uni-switch-input {
			width: 76rpx;
			height: 44rpx;
			background-color: #eeeeee;
		}

		/deep/ uni-switch .uni-switch-input.uni-switch-input-checked:after {
			transform: translateX(32rpx);
		}

		/deep/ .uni-switch-input-checked {
			background-color: var(--view-theme) !important;
			border-color: var(--view-theme) !important;
		}

		/deep/ .wx-switch-input {
			width: 80rpx !important;
			height: 44rpx !important;
			background-color: #eeeeee;
		}

		/*白色样式（false的样式）*/
		/deep/ .wx-switch-input::before {
			width: 76rpx !important;
			height: 40rpx !important;
			background-color: #eeeeee;
		}

		/*绿色样式（true的样式）*/
		/deep/ .wx-switch-input::after {
			width: 32rpx !important;
			height: 32rpx !important;
			margin: 4rpx 4rpx 4rpx 4rpx;
		}

		/deep/ .wx-switch-input-checked {
			background-color: var(--view-theme) !important;
			border-color: var(--view-theme) !important;
		}
	}

	.sign .wrapper .list {
		.mot-item {
			display: flex;
			margin-bottom: 20rpx;
			width: 100%;

			// justify-content: space-between;
			.row.sgin-day {
				border: 2rpx solid var(--view-op-point-eight);
			}

			.mot-item-box.sgin-day .row {
				border: 2rpx solid var(--view-op-point-eight);
				color: var(--view-theme);
				background: var(--view-minorColorT);
			}

			.mot-item-box.last-day .row {
				color: var(--view-theme);
				background: var(--view-minorColorT);
			}

			.row.last-day {
				color: var(--view-theme);
				background: var(--view-minorColorT);
			}

			.mot-item-box.last-day .num {
				color: var(--view-theme);
			}

			.row:last-child {
				margin-right: 0;
			}
		}

		.mot-item-box.sgin-day .num {
			color: var(--view-theme);
		}

		.mot-item-box {
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			align-items: center;

			.text {
				font-size: 24rpx;
				font-weight: 400;
				color: #999999;
				line-height: 34rpx;
				padding-right: 14rpx;
				margin-top: 8rpx;
			}

			.num {
				font-size: 26rpx;
				font-weight: 600;
				color: #666666;
				line-height: 24rpx;
				margin-bottom: 16rpx;
			}

			.icon-xuanzhong1 {
				font-size: 40rpx;
				color: var(--view-theme);
			}
		}

		.mot-item-box:last-child {
			.row {
				margin-right: 0;
			}
		}

		.row {
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			height: 110rpx;
			background: #f5f5f5;
			border-radius: 16rpx;
			width: 80rpx;
			margin-right: 14rpx;

			.type-img {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 40rpx;
				height: 40rpx;

				img {
					width: 100%;
					height: 100%;
				}

				.icon-xuanzhong1 {
					font-size: 40rpx;
					color: var(--view-theme);
				}
			}

			.venus {
				font-size: 24rpx;
				font-weight: 400;
				color: #999999;
				line-height: 34rpx;
				margin-top: 14rpx;
			}
		}
	}

	.sign .wrapper .list .item {
		font-size: 22rpx;
		color: #8a8886;
		text-align: center;
	}

	.sign .wrapper .list .item .rewardTxt {
		width: 74rpx;
		height: 32rpx;
		background-color: #f4b409;
		border-radius: 16rpx;
		font-size: 20rpx;
		color: #a57d3f;
		line-height: 32rpx;
	}

	.sign .wrapper .list .item .num {
		font-size: 30rpx;
		color: #999;
	}

	.sign .wrapper .list .item .num.on {
		color: #ff9000;
	}

	.sign .wrapper .list .item .venus {
		margin: 10rpx auto;

		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 56rpx;
		height: 56rpx;
	}

	.sign .wrapper .list .item .venus.venusSelect {}

	.sign .wrapper .list .item .venus.reward {
		width: 75rpx;
		height: 56rpx;
	}

	.sign .wrapper .but {
		width: 564rpx;
		height: 88rpx;
		font-size: 30rpx;
		line-height: 88rpx;
		color: #fff;
		border-radius: 50rpx;
		text-align: center;
		margin: 30rpx auto 0 auto;
	}

	.sign .wrapper .but.bg-color {
		background-color: var(--view-theme);
	}

	.sign .wrapper .but.on {
		background-color: var(--view-minorColor) !important;
	}

	.sign .wrapper .lock {
		background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAi4AAABECAYAAACmur7KAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQyIDc5LjE2MDkyNCwgMjAxNy8wNy8xMy0wMTowNjozOSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDowYWFmYjU3Mi03MGJhLTRiNDctOTI2Yi0zOThlZDkzZDkxMDkiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MkY0OEQxQjdEMDFDMTFFODgwMTlGMzZDRjQ3RTkwMTgiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MkY0OEQxQjZEMDFDMTFFODgwMTlGMzZDRjQ3RTkwMTgiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTggKFdpbmRvd3MpIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6ZmRjNTM0MmUtNmFkOC1iMDRhLThjZTEtMjk2YWYzM2FkMmUxIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjBhYWZiNTcyLTcwYmEtNGI0Ny05MjZiLTM5OGVkOTNkOTEwOSIv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');
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 558rpx;
		height: 68rpx;
		position: absolute;
		left: 50%;
		transform: translateX(-50%);
		bottom: -42rpx;
		z-index: 9;
	}

	.sign .wrapper2 {
		margin-top: 15rpx;
		padding: 73rpx 0 56rpx 0;
	}

	.sign .wrapper2 .tip {
		font-size: 30rpx;
		color: #666;
		text-align: center;
	}

	.sign .wrapper2 .list2 {
		margin: 45rpx 0 26rpx 0;
	}

	.sign .wrapper2 .list2 .item {
		border-radius: 10rpx;
		background-color: var(--view-theme);
		width: 80rpx;
		height: 116rpx;
		background-repeat: no-repeat;
		background-size: 100% 100%;
		color: #fff;
		font-size: 72rpx;
		text-align: center;
		line-height: 116rpx;
		margin-right: 19rpx;
		background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFAAAAB0CAYAAAASLLWNAAAAAXNSR0IArs4c6QAAADhlWElmTU0AKgAAAAgAAYdpAAQAAAABAAAAGgAAAAAAAqACAAQAAAABAAAAUKADAAQAAAABAAAAdAAAAAAmaeexAAAA0ElEQVR4Ae3RwQkAMAgEQZOW0n9PlpAi/Hgw/g+WscoRIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAQJLA6e6XFLyt9W4LSusBOPwYwCGgOQECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAgQIECAAAECBAhkCXwtvwMLKdfzbAAAAABJRU5ErkJggg==');
	}

	.sign .wrapper2 .list2 .data {
		font-size: 30rpx;
		color: #232323;
	}

	.sign .wrapper2 .tip2 {
		padding: 0 55rpx;
		text-align: center;
		font-size: 24rpx;
		font-weight: 400;
		color: #999999;
		line-height: 34rpx;
		display: flex;
		align-items: center;
		justify-content: center;

		img {
			width: 26rpx;
			height: 26rpx;
			margin-right: 10rpx;
			margin-bottom: 1rpx;
		}
	}

	.sign .list3 {
		margin: 0rpx 37rpx 0 37rpx;
		border-top: 1px dashed #d8d8d8;
	}

	.sign .list3 .item {
		align-items: center;
		justify-content: space-between;
		// border-bottom: 1px solid #eee;
		height: 130rpx;
	}

	.sign .list3 .item .name {
		color: #232323;
		font-size: 30rpx;
		width: 400rpx;
	}

	.sign .list3 .item .data {
		font-size: 24rpx;
		color: #bbbbbb;
		margin-top: 9rpx;
	}

	.sign .list3 .item .num {
		font-size: 36rpx;
		font-family: 'Guildford Pro';
		color: var(--view-theme);
	}

	.sign .list3 .loading {
		font-size: 26rpx;
		color: #282828;
		height: 97rpx;
		line-height: 97rpx;
		text-align: center;
	}

	.sign .list3 .loading .iconfont {
		font-size: 25rpx;
		color: #212121;
		vertical-align: 2rpx;
		margin-left: 10rpx;
	}

	.sign .signTip {
		width: 644rpx;
		height: 645rpx;
		position: fixed;
		top: 50%;
		left: 50%;
		margin-left: -322rpx;
		margin-top: -322.5rpx;
		z-index: 99;
		text-align: center;
		transition: all 0.3s ease-in-out 0s;
		opacity: 0;
		transform: scale(0);
	}

	.sign .signTip .signTipLight {
		background-image: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAoQAAAKFCAMAAABiNQjYAAAAjVBMVEUAAAD///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////8DizOFAAAAL3RSTlMABAsHDxMdGRYnKyEvQiQ+OUYzSjZOYFtkUmhteVhVdHCCfoaLkJWZnaGmrLG4xJtLVuIAALjgSURBVHja7NzdkuogEATgNO37P/OZYiFggKO1Q1yw+iMhP7d2DUGJh4iIiIiI/Abqrbklcj8MbyiQ8imo2oGmnRRDuQtyf83gJZGKoNwG46YUSo8yKN8HgHWIWx6VAZz5PG8qhnIXVNqLozpRBOUueJNSKHeBQiguUAjlCyiE4imCUCUUH3/6YJtCKH8H1qDhWP4ArP1swKyJCT8WQq3G+Q7VwoIpQQQBGhCGjF085ktDFUJJ2csNqZ9RVsA3gYcftDZxZ0i7dSWFh21efNvhBq1K3BVSBEurbRRCnPMpqBxupHxgqezBjs92DKGsDtcLlLVWdsCuIQSqYq6xeQN4eo7HmcO9Q5gn9NACxeXl8KXzsqBvfgjx0RDmHJ5NT4dLO+cgeTjuIf2VMASawFfgzmASQ6h3BjZQ1QgMMHXeEEa0ZpthiiVDvHFeTQzh9d1S/ZSyIOQeODBEMnaHS06aYdkj1ge6Sy6yEkS9RbUslCJo3bAGGvjHyafEVdUvd8xlEvMKIfqPh5otLwWpxxXzYdrDWo7dS/cs19EEZWGjmcjPTtYxdFbCglUtbIblyRlsnw/1tzerQR9TDiuBvkr4AudUQhAchDAGUQPygtDDjjAnhHcPxyRAYkhfYi+oXwT7McQWIYwwpr95WA4abCMYUinEB0LIST/MYOiAVjispflCpieE2AW4JyaPENLxcUsIWQGIIU1LllLlD2BfCiF9IXx0Wn1qF3NCWIwzqNcIVtIUwXYgjmaHsLQYvhRJXwgReAWiT8PxOgAOWTRSBukP4cMEEw+2W9cpiwHz14uBerV0cYMSmFJoewXOELbC9dQ7BWeHQrg4HOBIroDlZHYIW4cDYghHMaQyuK5h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');
		background-repeat: no-repeat;
		background-size: 100% 100%;
		width: 100%;
		height: 100%;
	}

	.sign .signTip.on {
		opacity: 1;
		transform: scale(1);
	}

	.sign .signTip .signTipCon {
		background: url('data:image/png;base64,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') no-repeat;
		background-size: 100% 100%;
		width: 420rpx;
		height: 410rpx;
		margin-top: -642rpx;
		position: relative;
		background-color: var(--view-theme);
		border-radius: 10rpx;
	}

	.sign .signTip .signTipCon .signHeight {
		width: 134rpx;
		height: 120rpx;
		position: absolute;
		left: 50%;
		margin-left: -67rpx;
		top: -60rpx;
	}

	.sign .signTip .signTipCon .signHeight image {
		width: 100%;
		height: 100%;
	}

	.sign .signTip .signTipCon .state {
		font-size: 34rpx;
		color: #fff;
		margin-top: 150rpx;
	}

	.sign .signTip .signTipCon .integral {
		font-size: 30rpx;
		color: rgba(255, 255, 255, 0.6);
		margin-top: 9rpx;
	}

	.sign .signTip .signTipCon .signTipBnt {
		font-size: 30rpx;
		color: #fff;
		width: 260rpx;
		height: 76rpx;
		background-color: #f8d168;
		border-radius: 38rpx;
		line-height: 76rpx;
		margin: 48rpx auto 0 auto;
	}
</style>