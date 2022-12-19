<template>
	<view v-show="!isSortType">
		<!-- #ifdef H5 -->
		<view class="follow acea-row row-between-wrapper" :style="'background:' + bgColor + ';margin-top:' + mbConfig + 'rpx;'" v-if='subscribe === false'>
			<view class="picTxt acea-row row-middle">
				<view class="pictrue"><image :src="imgConfig"></image></view>
				<view class="name line1">{{ titleConfig }}</view>
			</view>
			<view class="notes acea-row row-center-wrapper" :style="'color:' + themeColor + ';border-color:' + themeColor + ';'" @click="followTap">{{$t(`关注`)}}</view>
		</view>
		<view class="followCode" v-if="followCode">
			<view class="pictrue">
				<view class="code-bg"><img class="imgs" :src="followUrl" /></view>
			</view>
			<view class="mask" @click="closeFollowCode"></view>
		</view>
		<!-- #endif -->
	</view>
</template>

<script>
// #ifdef H5
import {
	follow
} from '@/api/api.js';
import {
	getSubscribe
} from '@/api/public';
export default {
	name: 'follow',
	props: {
		dataConfig: {
			type: Object,
			default: () => {}
		},
		isSortType:{
			type: String | Number,
			default:0
		}
	},
	data() {
		return {
			followCode: false,
			followUrl:this.dataConfig.codeConfig?this.dataConfig.codeConfig.url : '',
			bgColor:this.dataConfig.bgColor.color[0].item,
			imgConfig:this.dataConfig.imgConfig.url,
			mbConfig:this.dataConfig.mbConfig.val,
			themeColor:this.dataConfig.themeColor.color[0].item,
			titleConfig:this.dataConfig.titleConfig.value,
			subscribe:false
		};
	},
	created() {},
	mounted() {
		getSubscribe().then(res => {
			this.subscribe = res.data.subscribe || false;
		}).catch(() => {})
	},
	methods: {
		followTap(){
			this.followCode = true;
			// this.getFollow();
		},
		closeFollowCode(){
			this.followCode = false
		},
	}
}
// #endif
</script>

<style lang="scss">
.follow {
	padding: 0 20rpx;
	height: 140rpx;
	background: rgba(0, 0, 0, 0.02);
	.picTxt {
		.pictrue {
			width: 92rpx;
			height: 92rpx;
			border-radius: 50%;
			image {
				width: 100%;
				height: 100%;
				border-radius: 50%;
			}
		}
		.name {
			font-size: 32rpx;
			color: #000;
			margin-left: 32rpx;
			width: 400rpx;
		}
	}
	.notes {
		font-size: 28rpx;
		color: #02a0e8;
		width: 120rpx;
		height: 52rpx;
		border: 2rpx solid rgba(2, 160, 232, 1);
		opacity: 1;
		border-radius: 6rpx;
	}
}
.followCode {
	.pictrue {
		width: 500rpx;
		height: 530rpx;
		border-radius: 12px;
		left: 50%;
		top: 50%;
		margin-left: -250rpx;
		margin-top: -360rpx;
		position: fixed;
		z-index: 10000;
		.code-bg {
			display: flex;
			justify-content: center;
			width: 100%;
			height: 100%;
			background-image: url('~@/static/images/code-bg.png');
			background-size: 100% 100%;
		}
		.imgs {
			width: 310rpx;
			height: 310rpx;
			margin-top: 92rpx;
		}
	}
	.mask {
		z-index: 9999;
	}
}
</style>
