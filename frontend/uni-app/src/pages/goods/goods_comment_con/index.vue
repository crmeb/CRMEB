<template>
	<view :style="colorStyle">
		<form @submit="formSubmit" report-submit='true'>
			<view class='evaluate-con'>
				<view class='goodsStyle acea-row row-between'>
					<view class='pictrue'>
						<image :src='productInfo.image'></image>
					</view>
					<view class='text acea-row row-between'>
						<view class='name line3'>{{productInfo.store_name}}</view>
						<view class='money'>
							<view>{{$t(`￥`)}}{{productInfo.attrInfo.price}}</view>
							<view class='num'>x{{cart_num}}</view>
						</view>
					</view>
				</view>
				<view class='score'>
					<view class='item acea-row row-middle' v-for="(item,indexw) in scoreList" :key="indexw">
						<view>{{item.name}}</view>
						<view class='starsList'>
							<text @click="stars(indexn, indexw)" v-for="(itemn, indexn) in item.stars" :key="indexn"
								class='iconfont'
								:class="item.index >= indexn? 'icon-shitixing font-num':'icon-kongxinxing'"></text>
						</view>
						<text class='evaluate'>{{item.index === -1 ? "" : item.index + 1 + $t(`星`)}}</text>
					</view>
					<view class='textarea'>
						<textarea :placeholder='$t(`商品满足你的期待么？说说你的想法，分享给想买的他们吧`)' name="comment"
							placeholder-class='placeholder'></textarea>
						<view class='list acea-row row-middle'>
							<view class='pictrue' v-for="(item,index) in pics" :key="index">
								<image :src='item'></image>
								<text class='iconfont icon-guanbi1 font-num' @click='DelPic(index)'></text>
							</view>
							<view class='pictrue acea-row row-center-wrapper row-column' @click='uploadpic'
								v-if="pics.length < 8">
								<text class='iconfont icon-icon25201'></text>
								<view>{{$t(`上传图片`)}}</view>
							</view>
						</view>
					</view>
					<button class='evaluateBnt bg-color' formType="submit">{{$t(`立即评价`)}}</button>
				</view>
			</view>
		</form>
		<!-- #ifdef MP -->
		<!-- <authorize @onLoadFun="onLoadFun" :isAuto="isAuto" :isShowAuth="isShowAuth" @authColse="authColse"></authorize> -->
		<!-- #endif -->
		<canvas canvas-id="canvas" v-if="canvasStatus"
			:style="{width: canvasWidth + 'px', height: canvasHeight + 'px',position: 'absolute',left:'-100000px',top:'-100000px'}"></canvas>
	</view>
</template>

<script setup>
	import { ref, watch } from 'vue';
	import { onLoad } from '@dcloudio/uni-app';
	import { storeToRefs } from 'pinia';
	import {
		orderProduct,
		orderComment
	} from '@/api/order.js';
	import {
		toLogin
	} from '@/libs/login.js';
	import { useAppStore } from '@/store/modules/app';
	import { useColorTheme } from '@/composables/useColorTheme';
	import util from '@/utils/util.js';
	import i18n from '@/utils/lang.js';
	// #ifdef MP
	import authorize from '@/components/Authorize';
	// #endif

	const { colorStyle } = useColorTheme();

	const appStore = useAppStore();
	const { isLogin } = storeToRefs(appStore);

	const pics = ref([]);
	const scoreList = ref([{
			name: i18n.t(`商品质量`),
			stars: ["", "", "", "", ""],
			index: -1
		},
		{
			name: i18n.t(`服务态度`),
			stars: ["", "", "", "", ""],
			index: -1
		}
	]);
	const orderId = ref('');
	const unique = ref('');
	const productInfo = ref({});
	const cart_num = ref(0);
	const isAuto = ref(false); //没有授权的不会自动授权
	const isShowAuth = ref(false); //是否隐藏授权
	const canvasWidth = ref("");
	const canvasHeight = ref("");
	const canvasStatus = ref(false);

	watch(isLogin, (newV, oldV) => {
		if (newV) {
			getOrderProduct();
		}
	}, { deep: true });

	onLoad((options) => {
		if (!options.unique || !options.uni) return util.Tips({
			title: i18n.t(`缺少参数`)
		}, {
			tab: 3,
			url: 1
		});
		unique.value = options.unique;
		orderId.value = options.uni;
		if (isLogin.value) {
			getOrderProduct();
		} else {
			toLogin();
		}
	});

	function onLoadFun() {
		getOrderProduct();
	}
	// 授权关闭
	function authColse(e) {
		isShowAuth.value = e
	}
	/**
	 * 获取某个产品详情
	 *
	 */
	function getOrderProduct() {
		orderProduct(unique.value).then(res => {
			productInfo.value = res.data.productInfo;
			cart_num.value = res.data.cart_num;
		});
	}
	function stars(indexn, indexw) {
		scoreList.value[indexw].index = indexn;
	}
	/**
	 * 删除图片
	 *
	 */
	function DelPic(index) {
		let pic = pics.value[index];
		pics.value.splice(index, 1);
		pics.value = pics.value;
	}

	/**
	 * 上传文件
	 *
	 */
	function uploadpic() {
		canvasStatus.value = true
		util.uploadImageChange('upload/image', function(res) {
			pics.value.push(res.data.url);
		}, (res) => {
			canvasStatus.value = false
		}, (res) => {
			canvasWidth.value = res.w
			canvasHeight.value = res.h
		});
	}

	/**
	 * 立即评价
	 */
	function formSubmit(e) {
		let formId = e.detail.formId,
			value = e.detail.value,
			product_score = scoreList.value[0].index + 1 === 0 ? "" : scoreList.value[0].index + 1,
			service_score = scoreList.value[1].index + 1 === 0 ? "" : scoreList.value[1].index + 1;
		if (!value.comment) return util.Tips({
			title: i18n.t(`请填写你对宝贝的心得`)
		});
		value.product_score = product_score;
		value.service_score = service_score;
		value.pics = pics.value;
		value.unique = unique.value;
		// 评价必须落到用户点进来的这笔订单：同商品重复购买时 unique 不唯一，后端需按订单号定位
		value.uni = orderId.value;
		uni.showLoading({
			title: i18n.t(`正在发布评论`)
		});
		orderComment(value).then(res => {
			uni.hideLoading();
			if (res.data.to_lottery) {
				let jumpPath = '/pages/goods/goods_comment_con/lottery_comment?type=4&order_id=' +
					orderId.value + '&date=' + Date.parse(new Date())
				util.Tips({
					title: i18n.t(`感谢您的评价`),
					icon: 'success'
				}, jumpPath);
			} else {
				util.Tips({
					title: i18n.t(`感谢您的评价`),
					icon: 'success'
				})
				setTimeout(e => {
					uni.navigateBack()
				}, 1500)
			}
		}).catch(err => {
			uni.hideLoading();
			return util.Tips({
				title: err
			});
		});
	}
</script>

<style lang="scss" scoped>
	.evaluate-con .score {
		background-color: #fff;
		border-top: 1rpx solid #f5f5f5;
		font-size: 28rpx;
		color: #282828;
		padding: 48rpx 30rpx 65rpx 30rpx;
	}

	.evaluate-con .score .item~.item {
		margin-top: 30rpx;
	}

	.evaluate-con .score .item .starsList {
		padding: 0 35rpx 0 40rpx;
	}

	.evaluate-con .score .item .starsList .iconfont {
		font-size: 40rpx;
		color: #aaa;
	}

	.evaluate-con .score .item .starsList .iconfont~.iconfont {
		margin-left: 20rpx;
	}

	.evaluate-con .score .item .evaluate {
		color: #aaa;
		font-size: 24rpx;
	}

	.evaluate-con .score .textarea {
		width: 690rpx;
		background-color: #fafafa;
		border-radius: 10rpx;
		margin-top: 48rpx;
	}

	.evaluate-con .score .textarea textarea {
		font-size: 28rpx;
		padding: 20rpx 8rpx 0 8rpx;
		width: 100%;
		box-sizing: border-box;
		height: 160rpx;
	}

	.evaluate-con .score .textarea .placeholder {
		color: #bbb;
	}

	.evaluate-con .score .textarea .list {
		margin-top: 25rpx;
		padding-left: 5rpx;
	}

	.evaluate-con .score .textarea .list .pictrue {
		width: 140rpx;
		height: 140rpx;
		margin: 0 0 35rpx 25rpx;
		position: relative;
		font-size: 22rpx;
		color: #bbb;
	}

	.evaluate-con .score .textarea .list .pictrue:nth-last-child(1) {
		border: 1rpx solid #ddd;
		box-sizing: border-box;
	}

	.evaluate-con .score .textarea .list .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 3rpx;
	}

	.evaluate-con .score .textarea .list .pictrue .icon-guanbi1 {
		font-size: 45rpx;
		position: absolute;
		top: -20rpx;
		right: -20rpx;
	}

	.evaluate-con .score .textarea .list .pictrue .icon-icon25201 {
		color: #bfbfbf;
		font-size: 50rpx;
	}

	.evaluate-con .score .evaluateBnt {
		font-size: 30rpx;
		color: #fff;
		width: 690rpx;
		height: 86rpx;
		border-radius: 43rpx;
		text-align: center;
		line-height: 86rpx;
		margin-top: 45rpx;
	}
</style>
