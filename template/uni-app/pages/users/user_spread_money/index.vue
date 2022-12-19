<template>
	<view :style="colorStyle">
		<view class='commission-details'>
			<view class='promoterHeader bg-color'>
				<view class='headerCon acea-row row-between-wrapper'>
					<view>
						<view class='name'>{{$t(name)}}</view>
						<view class='money' v-if="recordType == 4">{{$t(`￥`)}}<text class='num'>{{extractCount}}</text></view>
						<view class='money' v-else>{{$t(`￥`)}}<text class='num'>{{recordCount}}</text></view>
					</view>
					<view class='iconfont icon-jinbi1'></view>
				</view>
			</view>
			<view class='sign-record'>
				<block v-for="(item,index) in recordList" :key="index" v-if="recordList.length>0">
					<view class='list'>
						<view class='item'>
							<view class='data'>{{item.time}}</view>
							<view class='listn'>
								<block v-for="(child,indexn) in item.child" :key="indexn">
									<view class='itemn acea-row row-between-wrapper'>
										<view class="title">
											<view class='name line1'>{{$t(child.title)}}</view>
											<view>{{child.add_time}}</view>
											<view class="fail-msg" v-if="child.fail_msg">
												{{$t(`原因`)}}：{{child.fail_msg}}
											</view>
										</view>
										<view class='num font-color' v-if="child.pm == 1">+{{child.number}}</view>
										<view class='num' v-else>-{{child.number}}</view>
									</view>

								</block>
							</view>
						</view>
					</view>
				</block>
				<view class='loadingicon acea-row row-center-wrapper' v-if="recordList.length">
					<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
				</view>
				<view v-if="recordList.length < 1 && page > 1">
					<emptyPage :title='$t(`暂无数据~`)'></emptyPage>
				</view>
			</view>
		</view>
		<!-- #ifdef H5 -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script>
	import {
		getCommissionInfo,
		getSpreadInfo
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
	import emptyPage from '@/components/emptyPage.vue'
	import home from '@/components/home';
	import colors from '@/mixins/color.js';
	export default {
		components: {
			// #ifdef MP
			authorize,
			// #endif
			emptyPage,
			home
		},
		mixins: [colors],
		data() {
			return {
				name: '',
				type: 0,
				page: 1,
				limit: 15,
				loading: false,
				loadend: false,
				loadTitle: this.$t(`加载更多`),
				recordList: [],
				recordType: 0,
				recordCount: 0,
				extractCount: 0,
				times: []
			};
		},
		computed: mapGetters(['isLogin']),
		onLoad(options) {
			if (this.isLogin) {
				this.type = options.type;
			} else {
				toLogin();
			}
		},
		onShow: function() {
			let type = this.type;
			if (type == 1) {
				uni.setNavigationBarTitle({
					title: "提现记录"
				});
				this.name = '提现总额';
				this.recordType = 4;
				this.getRecordList();
				this.getRecordListCount();
			} else if (type == 2) {
				uni.setNavigationBarTitle({
					title: "佣金记录"
				});
				this.name = '佣金明细';
				this.recordType = 3;
				this.getRecordList();
				this.getRecordListCount();
			} else {
				uni.showToast({
					title: '参数错误',
					icon: 'none',
					duration: 1000,
					mask: true,
					success: function(res) {
						setTimeout(function() {
							// #ifndef H5
							uni.navigateBack({
								delta: 1,
							});
							// #endif
							// #ifdef H5
							history.back();
							// #endif

						}, 1200)
					},
				});
			}
		},
		methods: {
			getRecordList: function() {
				let that = this;
				let page = that.page;
				let limit = that.limit;
				let recordType = that.recordType;
				if (that.loading) return;
				if (that.loadend) return;
				that.loading = true;
				that.loadTitle = '';
				getCommissionInfo({
					page: page,
					limit: limit
				}, recordType).then(res => {
					for (let i = 0; i < res.data.time.length; i++) {
						if (!this.times.includes(res.data.time[i])) {
							this.times.push(res.data.time[i])
							this.recordList.push({
								time: res.data.time[i],
								child: []
							})
						}
					}
					for (let x = 0; x < this.times.length; x++) {
						for (let j = 0; j < res.data.list.length; j++) {
							if (this.times[x] === res.data.list[j].time_key) {
								this.recordList[x].child.push(res.data.list[j])
							}
						}
					}
					let loadend = res.data.list.length < that.limit;
					that.loadend = loadend;
					that.loadTitle = loadend ? that.$t(`我也是有底线的`) : that.$t(`加载更多`);
					that.page += 1;
					that.loading = false;
				}).catch(err => {
					that.loading = false;
					that.loadTitle = that.$t(`加载更多`);
				})
			},
			getRecordListCount: function() {
				let that = this;
				getSpreadInfo().then(res => {
					that.recordCount = res.data.commissionCount;
					that.extractCount = res.data.extractCount;
				});
			}
		},
		onReachBottom: function() {
			this.getRecordList();
		}
	}
</script>

<style scoped lang="scss">
	.commission-details .promoterHeader .headerCon .money {
		font-size: 36rpx;
	}

	.commission-details .promoterHeader .headerCon .money .num {
		font-family: 'Guildford Pro';
	}
	.sign-record .list .item .listn .itemn .name{
		width: 100%;
		// max-width: 100%;
		white-space: break-spaces;
	}
	.sign-record .list .item .listn .itemn .title {
		padding-right: 30rpx;
		flex: 1;
	}
</style>
