<template>
	<view :style="colorStyle">
		<view class='bill-details'>
			<view class='nav acea-row'>
				<view class='item' :class='type==0 ? "on":""' @click='changeType(0)'>全部</view>
				<view class='item' :class='type==1 ? "on":""' @click='changeType(1)'>消费</view>
				<view class='item' :class='type==2 ? "on":""' @click='changeType(2)'>充值</view>
			</view>
			<view class='sign-record'>
				<view class='list' v-for="(item,index) in userBillList" :key="index">
					<view class='item'>
						<view class='data'>{{item.time}}</view>
						<view class='listn'>
							<view class='itemn acea-row row-between-wrapper' v-for="(vo,indexn) in item.child" :key="indexn">
								<view>
									<view class='name line1'>{{vo.title}}</view>
									<view>{{vo.add_time}}</view>
								</view>
								<view class='num' v-if="vo.pm">+{{vo.number}}</view>
								<view class='num font-color' v-else>-{{vo.number}}</view>
							</view>
						</view>
					</view>
				</view>
				<view class='loadingicon acea-row row-center-wrapper' v-if="userBillList.length>0">
					<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
				</view>
				<view v-if="userBillList.length == 0">
					<emptyPage title="暂无账单的记录哦～"></emptyPage>
				</view>
			</view>
		</view>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<home v-if="navigation"></home>
	</view>
</template>

<script>
	import {
		getCommissionInfo
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
	import emptyPage from '@/components/emptyPage.vue';
	import home from '@/components/home';
	import colors from "@/mixins/color";
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
				loadTitle: '加载更多',
				loading: false,
				loadend: false,
				page: 1,
				limit: 15,
				type: 0,
				userBillList: [],
				times:[],
				isAuto: false, //没有授权的不会自动授权
				isShowAuth: false //是否隐藏授权
			};
		},
		computed: mapGetters(['isLogin']),
		onShow() {
			if (this.isLogin) {
				this.getUserBillList();
			} else {
				toLogin();
			}
		},
		/**
		 * 生命周期函数--监听页面加载
		 */
		onLoad: function(options) {
			this.type = options.type || 0;
		},
		/**
		 * 页面上拉触底事件的处理函数
		 */
		onReachBottom: function() {
			this.getUserBillList();
		},
		methods: {
			/**
			 * 授权回调
			 */
			onLoadFun: function() {
				this.getUserBillList();
			},
			// 授权关闭
			authColse: function(e) {
				this.isShowAuth = e
			},
			/**
			 * 获取账户明细
			 */
			getUserBillList: function() {
				let that = this;
				let page = that.page;
				let limit = that.limit;
				if (that.loading) return;
				if (that.loadend) return;
				that.loading = true;
				that.loadTitle = '';
				getCommissionInfo({
					page: page,
					limit: limit
				},that.type).then(res => {
					for (let i = 0; i < res.data.time.length; i++) {
						
						if (!this.times.includes(res.data.time[i])) {
							this.times.push(res.data.time[i])
							this.userBillList.push({
								time: res.data.time[i],
								child: []
							})
						}
					}
					
					for (let x = 0; x < this.times.length; x++) {
						for (let j = 0; j < res.data.list.length; j++) {
							if (this.times[x] === res.data.list[j].time_key) {
								this.userBillList[x].child.push(res.data.list[j])
							}
						}
					}
					let loadend = res.data.list.length < that.limit;
					that.loadend = loadend;
					that.loadTitle = loadend ? '没有更多内容啦~' : '加载更多';
					that.page += 1;
					that.loading = false;
				}).catch(err=>{
					that.loading = false;
					that.loadTitle = '加载更多';
				})
			},
			// getUserBillList: function() {
			// 	let that = this;
			// 	if (that.loadend) return;
			// 	if (that.loading) return;
			// 	that.loading = true;
			// 	that.loadTitle = "";
			// 	let data = {
			// 		page: that.page,
			// 		limit: that.limit
			// 	}
			// 	getCommissionInfo(data, that.type).then(function(res) {
			// 		let list = res.data,
			// 			loadend = list.length < that.limit;
			// 		that.userBillList = that.$util.SplitArray(list, that.userBillList);
			// 		that.$set(that, 'userBillList', that.userBillList);
			// 		that.loadend = loadend;
			// 		that.loading = false;
			// 		that.loadTitle = loadend ? "没有更多内容啦~" : "加载更多";
			// 		that.page = that.page + 1;
			// 	}, function(res) {
			// 		that.loading = false;
			// 		that.loadTitle = '加载更多';
			// 	});
			// },
			/**
			 * 切换导航
			 */
			changeType: function(type) {
				this.type = type;
				this.loadend = false;
				this.page = 1;
				this.times = [];
				this.$set(this, 'userBillList', []);
				this.getUserBillList();
			},
		}
	}
</script>

<style scoped lang='scss'>
	.bill-details .nav {
		background-color: #fff;
		height: 90rpx;
		width: 100%;
		line-height: 90rpx;
	}

	.bill-details .nav .item {
		flex: 1;
		text-align: center;
		font-size: 30rpx;
		color: #282828;
	}

	.bill-details .nav .item.on {
		color: var(--view-theme);
		border-bottom: 3rpx solid var(--view-theme);
	}
</style>
