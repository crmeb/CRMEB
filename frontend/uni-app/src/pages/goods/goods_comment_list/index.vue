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

<script setup>
	import { ref } from 'vue';
	import { onLoad, onShow, onReachBottom } from '@dcloudio/uni-app';
	import { storeToRefs } from 'pinia';
	import {
		getReplyList,
		getReplyConfig
	} from '@/api/store.js';
	import userEvaluation from '@/components/userEvaluation';
	import { useColorTheme } from '@/composables/useColorTheme';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	import {
		toLogin
	} from '@/libs/login.js';
	import { useAppStore } from '@/store/modules/app';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';

	const { colorStyle } = useColorTheme();

	const appStore = useAppStore();
	const { isLogin } = storeToRefs(appStore);

	const imgHost = HTTP_REQUEST_URL;
	const replyData = ref({});
	const product_id = ref(0);
	const reply = ref([]);
	const type = ref(0);
	const loading = ref(false);
	const loadend = ref(false);
	const loadTitle = ref(i18n.t(`加载更多`));
	const page = ref(1);
	const limit = 20;

	/**
	 * 生命周期函数--监听页面加载
	 */
	onLoad((options) => {
		if (!options.product_id) return util.Tips({
			title: i18n.t(`缺少参数`)
		}, {
			tab: 3,
			url: 1
		});
		product_id.value = options.product_id;
	});

	onShow(() => {
		if (isLogin.value) {
			getProductReplyCount();
			getProductReplyList();
		} else {
			toLogin()
		}
	});

	/**
	 * 获取评论统计数据
	 *
	 */
	function getProductReplyCount() {
		getReplyConfig(product_id.value).then(res => {
			replyData.value = res.data;
		});
	}
	/**
	 * 分页获取评论
	 */
	function getProductReplyList() {
		if (loadend.value) return;
		if (loading.value) return;
		loading.value = true;
		loadTitle.value = '';
		getReplyList(product_id.value, {
			page: page.value,
			limit: limit,
			type: type.value,
		}).then(res => {
			let list = res.data,
				loadendVal = list.length < limit;
			reply.value = util.SplitArray(list, reply.value);
			reply.value = reply.value;
			loading.value = false;
			loadend.value = loadendVal;
			loadTitle.value = loadendVal ? i18n.t(`没有更多内容啦~`) : i18n.t(`加载更多`);
			page.value = page.value + 1;
		}).catch(err => {
			loading.value = false,
				loadTitle.value = i18n.t(`加载更多`)
		});
	}
	/*
	 * 点击事件切换
	 * */
	function changeType(e) {
		let newType = parseInt(e);
		if (newType == type.value) return;
		type.value = newType;
		page.value = 1;
		loadend.value = false;
		reply.value = [];
		getProductReplyList();
	}

	/**
	 * 页面上拉触底事件的处理函数
	 */
	onReachBottom(() => {
		getProductReplyList();
	});
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
