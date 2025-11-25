<template>
	<view :style="[signWrapStyle]">
		<view class="signIn" :style="[signStyle]">
			<view v-if="dataConfig.styleConfig.tabVal" class="text-wrap acea-row row-middle">
				<image src="../../../static/images/sign-icon-04.png" mode="widthFix" class="image"></image>
				<view class="text">
					<view class="name-wrap acea-row row-middle">
						<view class="name">{{ $t(`签到立即获取`) }}</view>
						<view class="num acea-row row-middle" :style="[numStyle]">
							<view class="inner acea-row row-middle" :class="{ opacity: !dataConfig.toneConfig.tabVal }">
								<image src="../../../static/images/sign-icon-01.png" mode="widthFix" class="icon"></image>
								+{{ sign_give_point }}
							</view>
						</view>
					</view>
					<view>{{ $t(`连续签到`) }} {{continuousSignDays || 0}}{{ $t(`天`) }}</view>
				</view>
				<view class="button" :style="[buttonStyle]" @click="goUserSgin">{{ $t(`立即签到`) }}</view>
			</view>
			<view v-else class="week-wrap acea-row row-middle">
				<view class="week acea-row">
					<view v-for="(item, index) in signList" :key="index" class="item acea-row row-column row-center-wrapper">
						<image v-if="item.is_sign" src="@/static/images/sign-icon-03.png" mode="widthFix" class="image"></image>
						<image v-else-if="item.type == 1" src="@/static/images/sign-icon-01.png" mode="widthFix" class="image"></image>
						<image v-else-if="item.type == 2" src="@/static/images/sign-icon-02.png" mode="widthFix" class="image"></image>
						<view>{{ $t(`周`) }}{{ index | weekFormat }}</view>
					</view>
				</view>
				<view class="button" :style="[buttonStyle]" @click="goUserSgin">{{ $t(`签到`) }}</view>
			</view>
		</view>
	</view>
</template>

<script>
	import {
		getSign
	} from '@/api/api.js';
	export default {
		props: {
			dataConfig: {
				type: Object,
				default: () => {}
			},
			isSortType: {
				type: String | Number,
				default: 0
			}
		},
		filters: {
			weekFormat: function(value) {
				return ['一', '二', '三', '四', '五', '六', '日'][value];
			}
		},
		data() {
			return {
				today: 0,
				signList: [],
				sign_give_point: 0,
				continuousSignDays: 0,
				nextContinuousSignRewardList: {},
			};
		},
		computed: {
			buttonStyle() {
				let styleObject = {};
				if (this.dataConfig.toneConfig.tabVal) {
					styleObject['background'] = `linear-gradient(90deg, ${this.dataConfig.bntBgColor.color[0].item} 0%, ${this.dataConfig.bntBgColor.color[1].item} 100%)`;
					styleObject['color'] = this.dataConfig.bntTxtColor.color[0].item;
				}
				return styleObject;
			},
			signStyle() {
				let color = this.dataConfig.styleConfig.tabVal ? this.dataConfig.moduleColor.color : this.dataConfig.moduleColor2.color;
				let borderRadius = `${this.dataConfig.fillet.val * 2}rpx`;
				if (this.dataConfig.fillet.type) {
					borderRadius =
						`${this.dataConfig.fillet.valList[0].val * 2}rpx ${this.dataConfig.fillet.valList[1].val * 2}rpx ${this.dataConfig.fillet.valList[3].val * 2}rpx ${this.dataConfig.fillet.valList[2].val * 2}rpx`;
				}
				return {
					'border-radius': borderRadius,
					'background': `linear-gradient(270deg, ${color[0].item} 0%, ${color[1].item} 100%)`,
				};
			},
			signWrapStyle() {
				return {
					'padding': `${this.dataConfig.topConfig.val * 2}rpx ${this.dataConfig.prConfig.val * 2}rpx ${this.dataConfig.bottomConfig.val * 2}rpx`,
					'margin-top': `${this.dataConfig.mbConfig.val * 2}rpx`,
					'background': this.dataConfig.bottomBgColor.color[0].item,
				};
			},
			numStyle() {
				let styleObject = {};
				if (this.dataConfig.toneConfig.tabVal) {
					styleObject['background'] = this.dataConfig.labelBgColor.color[0].item;
					styleObject['color'] = this.dataConfig.labelTxtColor.color[0].item;
				}
				return styleObject;
			},
		},
		mounted() {
			const t = new Date();
			const d = t.getDay();
			this.today = d;
			this.getSign();
		},
		methods: {
			goUserSgin() {
				uni.navigateTo({
					url: '/pages/users/user_sgin/index'
				})
			},
			getSign() {
				getSign().then(res => {
					this.continuousSignDays = res.data.continuousSignDays;
					this.sign_give_point = res.data.signGivePoint;
					this.signList = res.data.signList[0];
				});
			},
		},
	}
</script>

<style lang="scss" scoped>
	.signIn {
		padding: 24rpx;
		border-radius: 0rpx 0rpx 16rpx 16rpx;
		background: #FFFFFF;

		.week-wrap {
			height: 156rpx;
			border-radius: 16rpx;
			background: linear-gradient(to right, #FFE8F5, #F1FBFD);

			.week {
				flex: 1;
				font-weight: 500;
				font-size: 22rpx;
				line-height: 30rpx;
				color: #999999;

				.item {
					flex: 1;
				}

				.image {
					width: 48rpx;
					margin-bottom: 16rpx;
				}
			}

			.button {
				width: 88rpx;
				height: 48rpx;
				border-radius: 24rpx;
				margin: 0 20rpx;
				background: linear-gradient(90deg, var(--view-theme) 0%, var(--view-gradient) 100%);
				text-align: center;
				font-size: 24rpx;
				line-height: 48rpx;
				color: #FFFFFF;
			}
		}

		.text-wrap {
			height: 140rpx;
			border-radius: 16rpx;
			background: #FFFFFF;

			.image {
				width: 88rpx;
				height: 88rpx;
				margin-left: 20rpx;
			}

			.text {
				flex: 1;
				padding: 0 20rpx;
				font-size: 24rpx;
				color: #999999;
			}

			.name-wrap {
				margin-bottom: 14rpx;
			}

			.name {
				font-weight: 500;
				font-size: 30rpx;
				color: #282828;
			}

			.num {
				border-radius: 16rpx;
				margin-left: 4rpx;
				background: var(--view-theme);
				font-size: 20rpx;
				line-height: 32rpx;
				color: var(--view-theme);

				.inner {
					height: 32rpx;
					padding: 0 6rpx 0 8rpx;
					border-radius: 16rpx;
				}

				.opacity {
					background: rgba(255, 255, 255, 0.9);
				}
			}

			.icon {
				width: 24rpx;
				height: 24rpx;
			}

			.button {
				width: 140rpx;
				height: 52rpx;
				border-radius: 26rpx;
				margin: 0 20rpx;
				background: linear-gradient(90deg, var(--view-theme) 0%, var(--view-gradient) 100%);
				text-align: center;
				font-size: 24rpx;
				line-height: 52rpx;
				color: #FFFFFF;
			}
		}
	}
</style>