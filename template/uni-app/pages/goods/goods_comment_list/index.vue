<template>
	<view :style="colorStyle">
		<view class='evaluate-list'>
			<view class='generalComment acea-row row-between-wrapper'>
				<view class='acea-row row-middle'>
					<view class='evaluate'>{{$t(`评分`)}}</view>
					<view class='start' :class="'star'+replyData.reply_star"></view>
				</view>
				<view>{{$t(`好评率`)}}<text class='font-num'>{{replyData.reply_chance}}%</text></view>
			</view>
			<view class='nav acea-row row-middle'>
				<view class='item' :class='type==0 ? "bg-color":""' @click='changeType(0)'>
					{{$t(`全部`)}}({{replyData.sum_count}})
				</view>
				<view class='item' :class='type==1 ? "bg-color":""' @click='changeType(1)'>
					{{$t(`好评`)}}({{replyData.good_count}})
				</view>
				<view class='item' :class='type==2 ? "bg-color":""' @click='changeType(2)'>
					{{$t(`中评`)}}({{replyData.in_count}})
				</view>
				<view class='item' :class='type==3 ? "bg-color":""' @click='changeType(3)'>
					{{$t(`差评`)}}({{replyData.poor_count}})
				</view>
			</view>
			<userEvaluation :reply="reply"></userEvaluation>
			<view class='loadingicon acea-row row-center-wrapper' v-if="reply.length>0">
				<text class='loading iconfont icon-jiazai' :hidden='loading==false'></text>{{loadTitle}}
			</view>
			<view class='noCommodity' v-if="reply.length==0">
				<view class='emptyBox'>
					<image :src="imgHost + '/statics/images/noMessage.png'"></image>
				</view>
				<view class="text">
					{{$t(`暂无评论`)}}
				</view>
			</view>
		</view>
	</view>
</template>

<script>
	import {
		getReplyList,
		getReplyConfig
	} from '@/api/store.js';
	import userEvaluation from '@/components/userEvaluation';
	import colors from '@/mixins/color.js';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	import {
		toLogin
	} from '@/libs/login.js';
	import {
		mapGetters
	} from "vuex";
	export default {
		components: {
			userEvaluation
		},
		mixins: [colors],
		computed: mapGetters(['isLogin']),
		data() {
			return {
				imgHost: HTTP_REQUEST_URL,
				replyData: {},
				product_id: 0,
				reply: [],
				type: 0,
				loading: false,
				loadend: false,
				loadTitle: this.$t(`加载更多`),
				page: 1,
				limit: 20
			};
		},
		/**
		 * 生命周期函数--监听页面加载
		 */
		onLoad(options) {
			let that = this;
			if (!options.product_id) return that.$util.Tips({
				title: that.$t(`缺少参数`)
			}, {
				tab: 3,
				url: 1
			});
			that.product_id = options.product_id;
		},
		onShow() {
			if (this.isLogin) {
				this.getProductReplyCount();
				this.getProductReplyList();
			} else {
				toLogin()
			}
		},
		methods: {
			/**
			 * 获取评论统计数据
			 * 
			 */
			getProductReplyCount: function() {
				let that = this;
				getReplyConfig(that.product_id).then(res => {
					that.$set(that, 'replyData', res.data);
				});
			},
			/**
			 * 分页获取评论
			 */
			getProductReplyList: function() {
				let that = this;
				if (that.loadend) return;
				if (that.loading) return;
				that.loading = true;
				that.loadTitle = '';
				getReplyList(that.product_id, {
					page: that.page,
					limit: that.limit,
					type: that.type,
				}).then(res => {
					let list = res.data,
						loadend = list.length < that.limit;
					that.reply = that.$util.SplitArray(list, that.reply);
					that.$set(that, 'reply', that.reply);
					that.loading = false;
					that.loadend = loadend;
					that.loadTitle = loadend ? that.$t(`没有更多内容啦~`) : that.$t(`加载更多`);
					that.page = that.page + 1;
				}).catch(err => {
					that.loading = false,
						that.loadTitle = that.$t(`加载更多`)
				});
			},
			/*
			 * 点击事件切换
			 * */
			changeType: function(e) {
				let type = parseInt(e);
				if (type == this.type) return;
				this.type = type;
				this.page = 1;
				this.loadend = false;
				this.$set(this, 'reply', []);
				this.getProductReplyList();
			}
		},
		/**
		 * 页面上拉触底事件的处理函数
		 */
		onReachBottom: function() {
			this.getProductReplyList();
		},
	}
</script>

<style lang="scss">
	page {
		background-color: #fff;
	}

	.evaluate-list .generalComment {
		height: 94rpx;
		padding: 0 30rpx;
		margin-top: 1rpx;
		background-color: #fff;
		font-size: 28rpx;
		color: #808080;
	}

	.evaluate-list .generalComment .evaluate {
		margin-right: 7rpx;
	}

	.evaluate-list .nav {
		font-size: 24rpx;
		color: #282828;
		padding: 0 30rpx 32rpx 30rpx;
		background-color: #fff;
		border-bottom: 1rpx solid #f5f5f5;
	}

	.evaluate-list .nav .item {
		font-size: 24rpx;
		color: #282828;
		border-radius: 6rpx;
		height: 54rpx;
		padding: 0 20rpx;
		background-color: #f4f4f4;
		line-height: 54rpx;
		margin-right: 17rpx;
	}

	.evaluate-list .nav .item.bg-color {
		color: #fff;
	}

	.noCommodity {
		background-color: #fff;
		padding-bottom: 30rpx;
		padding-top: 100rpx;

		.text {
			padding-top: 40rpx;
			text-align: center;
			color: #aaa;
		}

		.emptyBox {
			text-align: center;
			padding-top: 20rpx;

			.tips {
				color: #aaa;
				font-size: 26rpx;
				text-align: center;
			}

			image {
				width: 414rpx;
				height: 304rpx;
			}
		}
	}
</style>
