<template>
	<view>
		<view class='commission-details'>
			<view class='promoterHeader bg-color'>
				<view class='headerCon acea-row row-between-wrapper'>
					<view>
						<view class='name'>{{name}}</view>
						<view class='money' v-if="recordType == 4">￥<text class='num'>{{extractCount}}</text></view>
						<view class='money' v-else>￥<text class='num'>{{recordCount}}</text></view>
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
								<block v-for="(child,indexn) in item.list" :key="indexn">
									<view class='itemn acea-row row-between-wrapper'>
										<view>
											<view class='name line1'>{{child.title}}</view>
											<view>{{child.add_time}}</view>
										</view>
										<view class='num font-color' v-if="child.pm == 1">+{{child.number}}</view>
										<view class='num' v-else>-{{child.number}}</view>
									</view>
								</block>
							</view>
						</view>
					</view>
				</block>
				<view v-if="recordList.length == 0">
					<emptyPage title='暂无提现记录~'></emptyPage>
				</view>
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<home></home>
	</view>
</template>

<script>
	import {
		getCommissionInfo,
		spreadCount,
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
	export default {
		components: {
			// #ifdef MP
			authorize,
			// #endif
			emptyPage,
			home
		},
		data() {
			return {
				name: '',
				type: 0,
				page: 0,
				limit: 8,
				recordList: [],
				recordType: 0,
				recordCount: 0,
				status: false,
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false ,//是否隐藏授权
				extractCount:0
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
			onLoadFun() {
				this.getRecordList();
				this.getRecordListCount();
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			getRecordList: function() {
				let that = this;
				let page = that.page;
				let limit = that.limit;
				let status = that.status;
				let recordType = that.recordType;
				let recordList = that.recordList;
				let recordListNew = [];
				if (status == true) return;
				getCommissionInfo({
					page: page,
					limit: limit
				}, recordType).then(res => {
					let len = res.data.length;
					let recordListData = res.data;
					recordListNew = recordList.concat(recordListData);
					that.status = limit > len;
					that.page = limit + page;
					that.$set(that, 'recordList', recordListNew);
				});
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
</style>
