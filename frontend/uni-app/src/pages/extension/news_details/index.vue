<template>
	<view :style="colorStyle">
		<view class='newsDetail'>
			<view class='title'>{{articleInfo.title || ''}}</view>
			<view class='list acea-row row-middle'>
				<view class='label'>{{articleInfo.catename || ''}}</view>
				<view class='item'>{{articleInfo.add_time || ''}}</view>
				<view class='item'><text class='iconfont icon-liulan'></text>{{articleInfo.visit || ''}}</view>
			</view>
			<view class='conters' v-if="description">
				<!-- #ifndef APP-PLUS -->
				<mp-html :content="description" :tag-style="tagStyle" />
				<!-- #endif -->
				<!-- #ifdef APP-PLUS -->
				<view v-html="description"></view>
				<!-- #endif -->
			</view>
			<navigator :render-link="false" class="picTxt acea-row row-between-wrapper" v-if="store_info.id"
				:url="'/pages/goods_details/index?id='+store_info.id" hover-class="none">
				<view class="pictrue">
					<image :src="store_info.image"></image>
				</view>
				<view class="text">
					<view class="name line1">{{store_info.store_name || ''}}</view>
					<view class="money font-color">
						{{$t(`￥`)}}<text class="num">{{store_info.price || 0}}</text>
					</view>
					<view class="y_money">{{$t(`￥`)}}{{store_info.ot_price || 0}}</view>
				</view>
				<navigator :render-link="false" class="label"><text class="span">{{$t(`查看商品`)}}</text></navigator>
			</navigator>
				<!-- #ifdef H5 -->
				<button class="bnt bg-color" hover-class='none' @click="listenerActionSheet"
					v-if="wechat.isWeixin()">{{$t(`和好友一起分享`)}}</button>
				<!-- #endif -->
			<!-- #ifdef MP -->
			<button class="bnt bg-color" open-type="share" hover-class='none'>{{$t(`和好友一起分享`)}}</button>
			<!-- #endif -->
		</view>
		<shareInfo @setShareInfoStatus="setShareInfoStatus" :shareInfoStatus="shareInfoStatus"></shareInfo>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow, onShareAppMessage, onShareTimeline } from '@dcloudio/uni-app';
import { getArticleDetails } from '@/api/api.js';
import shareInfo from '../components/shareInfo/index.vue';
import home from '@/components/home';
import { useColorTheme } from '@/composables/useColorTheme';
import { userShare } from "@/api/user.js";
import { useAppStore } from '@/store/modules/app';
// #ifdef H5
import wechat from "@/libs/wechat";
// #endif

defineOptions({ name: 'newsDetails' });

const { colorStyle } = useColorTheme();
const appStore = useAppStore();

// ---------- data ----------
const id = ref(0);
const articleInfo = ref([]);
const store_info = ref({});
const description = ref('');
const shareInfoStatus = ref(false);
const tagStyle = {
	img: 'width:100%;display:block;',
	table: 'width:100%',
	video: 'width:100%'
};

/**
 * 生命周期函数--监听页面加载
 */
onLoad((options) => {
	if (options.hasOwnProperty('id')) {
		id.value = options.id;
	} else {
		// #ifndef H5
		uni.navigateBack({
			delta: 1
		});
		// #endif
		// #ifdef H5
		history.back();
		// #endif
	}
});
onShow(() => {
	getArticleOne();
});
onShareAppMessage(() => {
	// 原代码此处还切换过未定义的 actionSheetHidden 状态（无实际效果，迁移时移除）
	userShare();
	return {
		title: articleInfo.value.title || "",
		imageUrl: articleInfo.value.image_input.length ? articleInfo.value.image_input[0] : "",
		path: "/pages/extension/news_details/index?id=" + id.value + "&spid=" + appStore.uid,
	};
});
onShareTimeline(() => {
	userShare();
	return {
		title: articleInfo.value.title,
		query: {
			id: id.value,
			spid: appStore.uid || 0,
		},
		imageUrl: articleInfo.value.image_input.length ? articleInfo.value.image_input[0] : "",
	};
});

// ---------- methods ----------
function getArticleOne() {
	getArticleDetails(id.value).then(res => {
		uni.setNavigationBarTitle({
			title: res.data.title.substring(0, 7) + "..."
		});
		articleInfo.value = res.data;
		store_info.value = res.data.store_info ? res.data.store_info : {};
		description.value = res.data.content;
		if (description.value) {
			description.value = description.value.replace(/<img/gi, '<img style="max-width:100%;height:auto;float:left;display:block" ');
			description.value = description.value.replace(/<video/gi, '<video style="width:100%;height:300px;display:block" ');
		}
		// #ifdef H5
		if (wechat.isWeixin()) {
			setShareInfo();
		}
		// #endif
	});
}
function listenerActionSheet() {
	shareInfoStatus.value = true
}
function setShareInfoStatus() {
	shareInfoStatus.value = false
}
function setShareInfo() {
	let href = location.href + '&spid=' + appStore.uid;
	let configAppMessage = {
		title: articleInfo.value.title,
		desc: articleInfo.value.synopsis,
		link: href,
		imgUrl: articleInfo.value.image_input.length ? articleInfo.value.image_input[0] : ""
	};
	wechat.wechatEvevt(["updateAppMessageShareData", "updateTimelineShareData",
		"onMenuShareAppMessage",
		"onMenuShareTimeline",
	], configAppMessage);
}
</script>

<style lang="scss">
	page {
		background-color: #fff !important;
	}
	.newsDetail{
		padding-bottom: 40rpx;
	}
	.newsDetail .title {
		padding: 0 30rpx;
		font-size: 34rpx;
		color: #282828;
		font-weight: bold;
		margin: 45rpx 0 23rpx 0;
		line-height: 1.5;
	}

	.newsDetail .list {
		margin: 0 30rpx;
		// border-bottom: 1rpx solid #eee;
		padding-bottom: 25rpx;
	}

	.newsDetail .list .label {
		font-size: 24rpx;
		color: #B1B2B3;
		// height: 38rpx;
		// border-radius: 3rpx;
		// text-align: center;
		// line-height: 38rpx;
		// padding: 0 10rpx;
	}

	.newsDetail .list .item {
		margin-left: 27rpx;
		font-size: 24rpx;
		color: #B1B2B3;
	}

	.newsDetail .list .item .iconfont {
		font-size: 28rpx;
		margin-right: 10rpx;
	}

	.newsDetail .list .item .iconfont.icon-shenhezhong {
		font-size: 26rpx;
	}

	.newsDetail .conters {
		position: relative;
		margin-top: 20rpx;
		width: 100%;
		// padding-bottom: 100rpx;
		overflow: hidden;
		padding: 0 30rpx;
		font-size: 28rpx;
		color: #8A8B8C;
		line-height: 1.7;
		display: block;
		img {
			display: block;
		}
	}

	.newsDetail .picTxt {
		width: 690rpx;
		height: 200rpx;
		border-radius: 20rpx;
		border: 1px solid #e1e1e1;
		position: relative;
		margin: 30rpx;
	}

	.newsDetail .picTxt .pictrue {
		width: 200rpx;
		height: 200rpx;
	}

	.newsDetail .picTxt .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 20rpx 0 0 20rpx;
		display: block;
	}

	.newsDetail .picTxt .text {
		width: 460rpx;
	}

	.newsDetail .picTxt .text .name {
		font-size: 30rpx;
		color: #282828;
	}

	.newsDetail .picTxt .text .money {
		font-size: 24rpx;
		margin-top: 40rpx;
		font-weight: bold;
	}

	.newsDetail .picTxt .text .money .num {
		font-size: 36rpx;
	}

	.newsDetail .picTxt .text .y_money {
		font-size: 26rpx;
		color: #999;
		text-decoration: line-through;
	}

	.newsDetail .picTxt .label {
		position: absolute;
		background-color: #303131;
		width: 160rpx;
		height: 50rpx;
		right: -7rpx;
		border-radius: 25rpx 0 6rpx 25rpx;
		text-align: center;
		line-height: 50rpx;
		bottom: 24rpx;
	}

	.newsDetail .picTxt .label .span {
		background-image: linear-gradient(to right, #fff71e 0%, #f9b513 100%);
		-webkit-background-clip: text;
		-webkit-text-fill-color: transparent;
	}

	.newsDetail .picTxt .label:after {
		content: " ";
		position: absolute;
		width: 0;
		height: 0;
		border-bottom: 8rpx solid #303131;
		border-right: 8rpx solid transparent;
		top: -7rpx;
		right: 0;
	}

	.newsDetail .bnt {
		color: #fff;
		font-size: 30rpx;
		width: 690rpx;
		height: 90rpx;
		border-radius: 45rpx;
		margin: 48rpx auto;
		text-align: center;
		line-height: 90rpx;
	}
</style>
