<template>
	<view :style="colorStyle">
		<view class='flash-sale'>
			<view class='header'>
				<img mode="widthFix" class="presellBg" :src="picUrl" alt="">
			</view>
			<view class="main_count">
				<view class="presellList acea-row row-between-wrapper">
					<view class='timeList'>
						<block v-for="(item,index) in timeList" :key='index'>
							<view @tap='settimeList(item,item.key)' class='item' :class="active == index + 1?'on':''">
								<view class='time'><span>{{item.name || ''}}</span></view>
							</view>
						</block>
					</view>
				</view>
				<view class='list'>
					<block v-for="(item,index) in presellList" :key='index'>
						<view class='item acea-row row-between-wrapper' @tap='goDetails(item)'>
							<view class='pictrue'>
								<image :src='item.image'></image>
							</view>
							<view class='text acea-row row-column-around'>
								<view class='name line2'>{{item.store_name || ''}}</view>
								<view class='booking'>
									<text v-if="item.presell_type != 0 && active != 1" class="count"
										style="color: #999;">{{$t(`已预定`)}}{{item.sales ? item.sales : 0}}{{item.unit_name || ''}}</text>
									<text v-else style="color: #999; font-size: 24rpx;">{{$t(`未开始`)}}</text>
								</view>
								<view v-if="item.coupon" class='coupon acea-row row-between-wrapper'
									style="margin-top: 14rpx;">
									<view class='hide line1 acea-row'>
										<view class='activity'>
											<!-- 满{{item.coupon.use_min_price}}减{{item.coupon.coupon_price}} -->
										</view>
									</view>
								</view>
								<view class="progress">
									<view class='presell_price'>
										<text class="presell_text">{{$t(`预售价`)}}</text>
										<text class="price">{{$t(`￥`)}} <text>{{ item.price }}</text></text>
									</view>
									<text class="iconfont icon-yushouanniu"></text>
									<view v-if="active != 1" class='order_btn'>
										{{ active === 2  ? $t(`立即预定`) : $t(`已结束`) }}
									</view>
									<view v-else class="unStartBtn">
										<text>{{$t(`开售时间`)}}</text>
										<view>
											{{ new Date(item.presale_start_time*1000).getMonth()+1 }}/{{ new Date(item.presale_start_time*1000).getDate() }}
											{{ new Date(item.presale_start_time*1000).getHours()<10?'0'+ 
										new Date(item.presale_start_time*1000).getHours():new Date(item.presale_start_time*1000).getHours() || '00'}}:
											{{ new Date(item.presale_start_time*1000).getMinutes()<10?"0" + new Date(item.presale_start_time*1000).getMinutes():
										new Date(item.presale_start_time*1000).getMinutes() || '00'}}
										</view>
									</view>
								</view>
							</view>
						</view>
					</block>
					<view class='noCommodity' v-if="presellList.length == 0">
						<view class='emptyBox'>
							<image :src="imgHost + '/statics/images/no-thing.png'"></image>
							<view class="tips">{{$t(`暂无商品，去看点别的吧`)}}</view>
						</view>
					</view>
				</view>
			</view>

		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</view>
</template>

<script setup>
	import {
		getSeckillIndexTime,
		getPresellList
	} from '../../../api/activity.js';
	import home from '@/components/home/index.vue'
	import { useColorTheme } from '@/composables/useColorTheme';
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	import {
		colorChange
	} from '@/api/api.js';
	import { ref } from 'vue';
	import { onLoad, onReachBottom, onShareAppMessage, onShareTimeline } from '@dcloudio/uni-app';
	import i18n from '@/utils/lang.js';

	const { colorStyle } = useColorTheme();

	// ---------- data ----------
	const imgHost = HTTP_REQUEST_URL;
	const topImage = ref('');
	const presellList = ref([]);
	const timeList = [{
		name: i18n.t(`未开始`),
		key: 1
	}, {
		name: i18n.t(`抢购中`),
		key: 2
	}, {
		name: i18n.t(`已结束`),
		key: 3
	}, ];
	const active = ref(2);
	const type = ref(0);
	const scrollLeft = ref(0);
	const interval = ref(0);
	const status = ref(1);
	const page = ref(1);
	const limit = ref(8);
	const loading = ref(false);
	const loadend = ref(false);
	const pageloading = ref(false);
	const picList = [
					imgHost + '/statics/images/presell-bg-1.jpg',
					imgHost + '/statics/images/presell-bg-2.jpg',
					imgHost + '/statics/images/presell-bg-3.jpg',
					imgHost + '/statics/images/presell-bg-4.jpg',
					imgHost + '/statics/images/presell-bg-5.jpg'
			];
	const picUrl = ref('');

	// ---------- methods ----------
	function getPresellProductList() {
		var data = {
			page: page.value,
			limit: limit.value,
			time_type: active.value
		};
		if (loadend.value) return;
		if (pageloading.value) return;
		pageloading.value = true
		getPresellList(data).then(res => {
			var newpresellList = res.data.list;
			var newloadend = newpresellList.length < limit.value;
			page.value++;
			presellList.value = presellList.value.concat(newpresellList),
				page.value = page.value;
			pageloading.value = false;
			loadend.value = newloadend;
		}).catch(err => {
			pageloading.value = false
		});
	}
	function settimeList(item, index) {
		active.value = index
		type.value = active.value;
		loadend.value = false;
		page.value = 1;
		presellList.value = [];
		// wxh.time(e.currentTarget.dataset.stop, that);
		getPresellProductList();
	}
	function goDetails(item) {
		uni.navigateTo({
			url: '/pages/goods_details/index?id=' + item.id
		})
	}

	/**
	 * 用户点击右上角分享
	 */
	// #ifdef MP
	onShareAppMessage(() => {
		wx.showShareMenu({
			withShareTicket: true,
			menus: ['shareAppMessage', 'shareTimeline']
		})
		return {
			title: i18n.t(`预售活动`),
			path: 'pages/activity/presell/index',
		}
	});
	onShareTimeline(() => {
		return {
			title: i18n.t(`预售活动`),
			query: {
				key: ''
			},
			imageUrl: ''
		}
	});
	// #endif
	onLoad(() => {
		getPresellProductList();
		colorChange('color_change').then(res => {
			switch (res.data.status) {
				case 1:
					picUrl.value = picList[0]
					break;
				case 2:
					picUrl.value = picList[1]
					break;
				case 3:
					picUrl.value = picList[2]
					break;
				case 4:
					picUrl.value = picList[3]
					break;
				case 5:
					picUrl.value = picList[4]
					break;
				default:
					picUrl.value = picList[2]
					break
			}
		});
	});

	/**
	 * 页面上拉触底事件的处理函数
	 */
	onReachBottom(() => {
		getPresellProductList();
	});
</script>

<style lang="scss">
	page {
		background-color: var(--view-theme);
	}

	.noCommodity {
		padding-bottom: 30rpx;
		padding: 200rpx 0;

		.emptyBox {
			text-align: center;
			padding-top: 20rpx;

			.tips {
				color: #aaa;
				font-size: 26rpx;
			}

			image {
				width: 414rpx;
				height: 304rpx;
			}
		}
	}

	.flash-sale {
		height: 100vh;
		background-color: var(--view-theme);
	}

	.flash-sale .header {
		width: 100%;
		position: relative;
	}

	.flash-sale .main_count {
		position: relative;
		top: -150rpx;

	}

	.flash-sale .header .presellBg {
		width: 750rpx;
	}

	.flash-sale .presellList {
		bottom: 0;
		margin: 0 30rpx;
		width: 690rpx;
		background: #fff;
		border-radius: 16rpx;
		line-height: 80rpx;
		height: 80rpx;

	}

	.flash-sale .presellList .priceTag {
		width: 75rpx;
		height: 70rpx;

	}

	.flash-sale .presellList .priceTag image {
		opacity: 1;
	}

	.flash-sale .presellList .priceTag image {
		width: 100%;
		height: 100%;
	}

	.flash-sale .timeList {
		display: flex;
		justify-content: center;
		align-items: center;
		margin: 0 auto;
	}

	.flash-sale .timeList .item {
		font-size: 20rpx;
		color: #666;
		text-align: center;
		box-sizing: border-box;
		width: 224rpx;
	}

	.flash-sale .timeList .item .time {
		font-size: 26rpx;
		color: #AAAAAA;
	}

	.flash-sale .timeList .item.on .time {
		color: var(--view-theme);
		font-weight: 600;

		span {
			position: relative;

			&::after {
				content: '';
				display: inline-block;
				width: 100%;
				height: 4rpx;
				background: var(--view-theme);
				position: absolute;
				left: 0;
				bottom: -4rpx;
				border-radius: 2rpx;
			}
		}

	}

	.activity {
		height: 56rpx;
		padding: 0 11px;
		color: #e93323;
		font-size: 22rpx;
		line-height: 50rpx;
		position: relative;
		background-image: url('@/pages/activity/static/couponBg.png');
		background-size: 100%;
		background-repeat: no-repeat;
	}

	.flash-sale .list {
		margin-top: 24rpx;
		background-color: var(--view-theme);
		padding-bottom: 30rpx;
	}

	.flash-sale .list .item {
		// height: 278rpx;
		position: relative;
		width: 690rpx;
		margin: 0 auto 20rpx auto;
		background-color: #fff;
		border-radius: 20rpx;
		padding: 30rpx 25rpx;
	}

	.flash-sale .list .item .pictrue {
		width: 230rpx;
		height: 230rpx;
		border-radius: 10rpx;
	}

	.flash-sale .list .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 10rpx;
	}

	.flash-sale .list .item .text {
		width: 380rpx;
		height: 230rpx;
		font-size: 30rpx;
		color: #333;
	}

	.flash-sale .list .item .text .name {
		width: 100%;
		color: #282828;
		font-weight: bold;
		font-size: 28rpx;
	}

	.flash-sale .list .item .text .booking {
		font-size: 30rpx;
		color: #E93323;
	}

	.flash-sale .list .item .text .booking .count {
		font-size: 24rpx;
		color: #E93323;
	}

	.flash-sale .list .item .text .limit {
		font-size: 22rpx;
		color: #999;
		margin-bottom: 5rpx;
	}

	.flash-sale .list .item .text .limit .limitPrice {
		margin-left: 10rpx;
	}

	.flash-sale .list .item .text .progress {
		width: 392rpx;
		height: 80rpx;
		overflow: hidden;
		margin-top: 16rpx;
		position: relative;
		display: flex;
		align-items: center;

		.icon-yushouanniu {
			position: absolute;
			right: 82rpx;
			width: 44%;
			font-size: 92rpx;
			line-height: 80rpx;
			height: 80rpx;
			z-index: 98;
			color: var(--view-theme);
		}

		.presell_price {
			float: left;
			width: 55%;
			text-align: center;
			line-height: 15px;
			padding: 8rpx 0;
			background-image: url('@/pages/activity/static/preBtnLeft.png');
			background-size: 100%;
			z-index: 99;
			height: 80rpx;
			position: absolute;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-direction: column;

			.presell_text {
				display: block;
				color: #E93323;
				font-size: 20rpx;
				margin-bottom: 5rpx;
			}

			.price {
				font-size: 26rpx;
				color: #E93323;

				text {
					font-weight: bold;
				}
			}
		}

		.order_btn {
			float: left;
			width: 50%;
			text-align: center;
			color: #FFFFFF;
			font-size: 26rpx;
			line-height: 80rpx;
			z-index: 999;
			position: absolute;
			right: 8rpx;
		}

		.unStartBtn {
			position: absolute;
			// float: left;
			right: 8rpx;
			width: 50%;
			text-align: center;
			color: #FFFFFF;
			font-size: 20rpx;
			z-index: 999;
			padding: 4rpx 0;

			text {
				font-size: 22rpx;
			}
		}
	}
</style>
