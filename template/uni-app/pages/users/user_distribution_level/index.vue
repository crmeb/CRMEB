<template>
	<view :style="colorStyle">
		<view class='member-center'>
			<image class="bag" src="../../../static/images/jf-head.png" mode=""></image>
			<view class='header'>
				<swiper class="swiper" :current="swiperIndex" previous-margin="55rpx" next-margin="55rpx"
					@change="swiperChange">
					<block v-for="(item, index) in distributionLevel" :key="index">
						<swiper-item>
							<view class="swiper-item" :class="{on: swiperIndex == index}"
								:style="{'background-image': 'url('+item.image+')'}">
								<view class="acea-row row-middle user-wrap">
									<image class="image" :src="userInfo.avatar"></image>
									<view class="user-msg">
										<view class="text">
											<view class="name">{{userInfo.nickname}}</view>
											<view class="level" :class="{'level-sty':item.grade > (levelInfo.grade || 0)}">
												{{$t(item.name)}}
											</view>
										</view>
									</view>
									<!-- 		<view v-if="item.grade === levelInfo.grade" class="state">当前等级</view>
									<view v-else-if="item.grade > levelInfo.grade" class="state">
										暂未解锁
									</view> -->
									<view v-if="item.grade > (levelInfo.grade || 0)" class="state">
										<image class="lock" src="../static/lock.png" mode=""></image>
									</view>
								</view>
								<template>
									<view class="level-grow-wrap">
										<view class="level-info" :class="{'lock-sty':item.grade > (levelInfo.grade || 0)}">
											<view class="level-info-title">{{$t(`一级分佣上浮`)}}</view>
											<view class="num">{{item.one_brokerage}}
												<text class="percent">%</text>
											</view>
										</view>
										<view class="level-info" :class="{'lock-sty':item.grade > (levelInfo.grade || 0)}">
											<view class="level-info-title">{{$t(`二级分佣上浮`)}}</view>
											<view class="num">{{item.two_brokerage}}<text class="percent">%</text>
											</view>
										</view>

									</view>
								</template>
							</view>
						</swiper-item>
					</block>
				</swiper>
			</view>
			<view class="skill-section">
				<view class="section-hd acea-row row-between-wrapper">
					<view class="title">
						<view class="line-left">
						</view>
						<text>
							{{$t(`快速升级技巧`)}}
						</text>
					</view>
					<view class="task">
						<text class="task-num">
							{{ taskNum  }}
						</text>
						<text>
							/{{ task.length}}
						</text>
					</view>
				</view>
				<view class="section-bd acea-row">
					<view class="item acea-row row-middle" v-for="(item,index) in task" :key='item.id'>
						<view class="text">
							<view class="title">
								<view class="name line2">
									{{$t(item.name)}}
									<text class="iconfont icon-wenti" @click="opHelp(index)"></text>
								</view>

								<text class="mark">{{item.finish?$t(`已完成`):$t(`未完成`)}}</text>
							</view>
							<view class="process">
								<view
									:style="{width: `${Math.floor((item.new_number / item.number) > 1 ? 100 : item.new_number / item.number* 100)}%`}"
									class="fill"></view>
							</view>
							<view class="info-box">
								<view class="info">{{item.finish ? '' : $t(item.task_type_title)}}</view>
								<view class="link" hover-class="none">
									<text class="new-number">{{item.new_number}}</text>
									/{{item.number}}
								</view>
							</view>
						</view>
					</view>
				</view>
			</view>
			<recommend v-if="hostProduct.length" :hostProduct="hostProduct"></recommend>
			<view class='growthValue' :class='growthValue==false?"on":""'>
				<text class='iconfont icon-guanbi3' @click='growthValue = true'></text>
				<view class='conter'>{{illustrate}}</view>
			</view>
			<view class='mask' :hidden='growthValue' @click='growthValueClose'></view>
		</view>
	</view>
</template>

<script>
	import {
		agentLevelList,
		agentLevelTaskList
	} from '@/api/user.js';
	import {
		getProductHot
	} from '@/api/store.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	import recommend from '@/components/recommend';
	import colors from '@/mixins/color.js';
	export default {
		components: {
			recommend
		},
		mixins:[colors],
		data() {
			return {
				reach_count: 0,
				distributionLevel: [],
				swiperIndex: 0,
				growthValue: true,
				task: [], //任务列表
				illustrate: '', //任务说明
				level_id: 0, //任务id,
				hostProduct: [],
				grade: 0,
				hotScroll: false,
				hotPage: 1,
				hotLimit: 10,
				level_title: '',
				level_discount: '',
				levelInfo: {},
				userInfo: {},
				taskInfo: {},
				taskNum: 0
			};
		},
		computed: mapGetters(['isLogin']),
		watch: {
			distributionLevel: function() {
				let that = this;
				if (that.distributionLevel.length > 0) {
					that.distributionLevel.forEach(function(item, index) {
						if (item.is_clear === false) {
							// that.swiper.slideTo(index);
							that.activeIndex = index;
							that.grade = item.grade;
						}
					});
				}
			},
			isLogin: {
				handler: function(newV, oldV) {
					if (newV) {
						this.get_host_product();
					}
				},
				deep: true
			}
		},
		onLoad() {
			if (this.isLogin) {
				this.agentLevelList();
			} else {
				toLogin();
			}
			this.get_host_product();
		},
		methods: {
			agentLevelList: function() {
				agentLevelList().then(res => {
					const {
						level_info,
						level_list,
						task,
						user
					} = res.data;
					this.levelInfo = level_info;
					this.distributionLevel = level_list;
					this.userInfo = user;
					this.taskInfo = task;
					this.levelInfo.exp = parseFloat(this.levelInfo.exp);
					this.levelInfo.rate = Math.floor(this.levelInfo.exp / this.levelInfo.exp_num * 100);
					if (this.levelInfo.rate > 100) {
						this.levelInfo.rate = 100;
					}
					const index = level_list.findIndex((
							grade, v
						) =>
						grade.id === user.agent_level
					);
					if (index !== -1) {
						this.swiperIndex = index === -1 ? 0 : index;
					}
					this.level_id = this.distributionLevel[index === -1 ? 0 : index].id || 0;
					this.getTask();
				});
			},
			/**
			 * 获取我的推荐
			 */
			get_host_product: function() {
				let that = this;
				if (that.hotScroll) return
				getProductHot(
					that.hotPage,
					that.hotLimit,
				).then(res => {
					that.hotPage++
					that.hotScroll = res.data.length < that.hotLimit
					that.hostProduct = that.hostProduct.concat(res.data)
				});
			},
			/**
			 * 会员切换
			 * 
			 */
			swiperChange(e) {
				let index = e.detail.current;
				this.swiperIndex = index;
				this.level_id = this.distributionLevel[index].id || 0;
				this.level_title = this.distributionLevel[index].name || '';
				this.level_discount = this.distributionLevel[index].discount || '';
				// this.grade = this.distributionLevel[index].grade
				this.getTask();
			},
			/**
			 * 关闭说明
			 */
			growthValueClose: function() {
				this.growthValue = true;
			},
			/**
			 * 打开说明
			 */
			opHelp: function(index) {
				this.growthValue = false;
				this.illustrate = this.task[index].desc;
			},
			/**
			 * 获取任务要求
			 */
			getTask: function() {
				let that = this;
				that.taskNum = 0
				agentLevelTaskList(that.level_id).then(res => {
					that.task = res.data
					for (let i = 0; i < that.task.length; i++) {
						if (that.task[i].finish) {
							that.taskNum += 1
						}
					}
				});
			}
		},
		onReachBottom() {
			if (!this.hotScroll) {
				this.get_host_product();
			}
		}
	}
</script>

<style lang="scss" scoped>
	.swiper {
		.swiper-item {
			height: 100%;
			border-radius: 6rpx;
			background: center/100% 100% no-repeat;
			transform: scale(0.9);
			transition: all 0.2s ease-in 0s;
			line-height: 1.1;

			&.on {
				transform: none;
			}
		}

		.user-wrap {
			padding-top: 20rpx;
			padding-left: 22rpx;
			line-height: 1.1;

			.image {
				width: 90rpx;
				height: 90rpx;
				border-radius: 50%;
			}

			.user-msg {
				margin-left: 14rpx;

				.text {
					flex: 1;
					display: flex;
					align-items: center;
					min-width: 0;
					font-size: 22rpx;
					color: #666666;

					.num {
						margin-right: 10rpx;
						margin-left: 10rpx;
						font-size: 30rpx;
						font-style: italic;
					}
				}
			}

			.name {
				flex: 1;
				overflow: hidden;
				white-space: nowrap;
				text-overflow: ellipsis;
				font-weight: bold;
				font-size: 28rpx;
				color: #fff;
				margin-right: 8rpx;
			}

			.state {
				position: absolute;
				top: 0rpx;
				right: 0;
				width: 70rpx;
				height: 70rpx;

				.lock {
					width: 100%;
					height: 100%;
				}
			}
		}

		.grow-wrap {
			padding-left: 34rpx;
			margin-top: 70rpx;
			font-size: 20rpx;
			color: #474747;
			display: flex;

			.num {
				margin-right: 8rpx;
				margin-left: 8rpx;
				font-size: 26rpx;
			}
		}

		.level {
			font-size: 24rpx;
			color: #fff;
			border-radius: 4rpx;
			border:1px solid #fff;
			padding: 3rpx 8rpx;
		}
	}


	.skill-section {
		margin: 24rpx 30rpx 20rpx 30rpx;
		background-color: #FFF;
		border-radius: 8rpx;

		.section-hd {
			padding: 38rpx 36rpx 0 36rpx;
			font-weight: bold;
			font-size: 32rpx;
			color: #282828;

			.title {
				display: flex;
				align-items: center;
				font-size: 32rpx;

				.line-left {
					margin-right: 16rpx;
					width: 8rpx;
					height: 40rpx;
					border-radius: 4rpx;
					background-color: #E8B869;
				}
			}

			.task {
				color: #999999;
				font-size: 26rpx;
				font-weight: 400;
			}

			.task-num {
				color: #C6985C;
			}
		}

		.section-bd {
			padding: 30rpx;

			.item {
				width: 100%;
				// height: 140rpx;
				padding: 10px 25rpx;
				border-radius: 4rpx;
				background-color: #F8F8F8;
				box-shadow: 0 5rpx 10rpx 0 #F8F8F8;

				.name {
					font-size: 28rpx;
				}

				~.item {
					margin-top: 24rpx;
				}
			}

			.text {
				flex: 1;
			}

			.title {
				font-weight: bold;
				font-size: 30rpx;
				color: #282828;
				display: flex;
				justify-content: space-between;

				.icon-wenti {
					color: #ccc;
					margin-left: 10rpx;
				}

				.mark {
					text-align: right;
					margin-left: 20rpx;
					font-weight: normal;
					font-size: 24rpx;
					color: #999999;
					white-space: nowrap;
				}
			}

			.process {
				height: 12rpx;
				border-radius: 6rpx;
				margin-top: 22rpx;
				background-color: #EEEEEE;

				.fill {
					height: 100%;
					border-radius: 6rpx;
					background-color: #E7B667;
				}
			}

			.info-box {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-top: 18rpx;
			}

			.info {
				font-size: 22rpx;
				color: #999999;
			}

			.link {
				font-size: 26rpx;
				color: #999999;

				.new-number {
					color: #C6985C;
				}
			}
		}
	}

	.bag {
		position: absolute;
		width: 100%;
		height: 285rpx;
	}

	.member-center .header {
		// height: 470rpx;
		padding-top: 37rpx;
	}

	.member-center .header swiper {
		position: relative;
		// height: 330rpx;
	}

	.member-center .growthValue {
		background-color: #fff;
		border-radius: 16rpx;
		position: fixed;
		top: 266rpx;
		left: 50%;
		width: 560rpx;
		min-height: 440rpx;
		margin-left: -280rpx;
		z-index: 99;
		transform: translate3d(0, -200%, 0);
		transition: all .3s cubic-bezier(.25, .5, .5, .9);
	}

	.member-center .growthValue.on {
		transform: translate3d(0, 0, 0);
	}

	.member-center .growthValue .pictrue {
		width: 100%;
		height: 257rpx;
		position: relative;
	}

	.member-center .growthValue .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 16rpx 16rpx 0 0;
	}

	.member-center .growthValue .conter {
		padding: 0 35rpx;
		font-size: 30rpx;
		color: #333;
		margin-top: 58rpx;
		line-height: 1.5;
		height: 350rpx;
		overflow: auto;
	}

	.member-center .growthValue .iconfont {
		position: absolute;
		font-size: 65rpx;
		color: #fff;
		bottom: -90rpx;
		left: 50%;
		transform: translateX(-50%);
	}

	.level-grow-wrap {
		position: absolute;
		display: flex;
		left: 30rpx;
		bottom: 39rpx;
		font-size: 20rpx;
		color: #474747;



		.level-info {
			display: flex;
			justify-content: center;
			flex-direction: column;
			margin-right: 40rpx;

			.level-info-title {
				font-size: 22rpx;
				color: #FFFFFF;
				opacity: 0.6;
			}

			.num {
				color: #fff;
				font-size: 40rpx;
				margin-top: 20rpx;

				.percent {
					font-size: 24rpx;
				}
			}
		}

		.lock-sty {
			opacity: 0.7;
		}

	}

	.swiper .level-sty {
		opacity: 0.7;
	}
</style>
