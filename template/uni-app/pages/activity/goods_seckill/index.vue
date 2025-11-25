<template>
	<div :style="colorStyle">
		<view class="flash-sale">
			<view class="saleBox"></view>
			<view class="header" v-if="timeList.length > 0">
				<image :src="timeList[active].slide"></image>
			</view>
			<view class="seckillList acea-row row-between-wrapper">
				<view class="priceTag">
					<image src="../static/priceTag.png"></image>
				</view>
				<view class="timeLsit">
					<scroll-view
						class="scroll-view_x"
						scroll-x
						scroll-with-animation
						:scroll-left="scrollLeft"
						style="width: auto; overflow: hidden; height: 106rpx"
						:scroll-into-view="intoindex"
					>
						<block v-for="(item, index) in timeList" :key="index">
							<view @tap="settimeList(item, index)" class="item" :class="active == index ? 'on' : ''" :id="'sort' + index">
								<view class="time">{{ item.time }}</view>
								<view class="state">{{ $t(item.state) }}</view>
							</view>
						</block>
					</scroll-view>
				</view>
			</view>
			<view class="list">
				<block v-for="(item, index) in seckillList" :key="index">
					<view class="item acea-row row-between-wrapper" @tap="goDetails(item)">
						<view class="pictrue">
							<image :src="item.image"></image>
						</view>
						<view class="text acea-row row-column-around">
							<view class="name line2">{{ item.title }}</view>
							<view class="money font-color">
								{{ $t(`￥`) }}
								<text class="num font-color">{{ item.price }}</text>
								<text class="y_money">{{ $t(`￥`) }}{{ item.product_price }}</text>
							</view>
							<view class="limit">
								{{ $t(`限量`) }}
								<text class="limitPrice">{{ item.quota_show }}{{ $t(item.unit_name) || '' }}</text>
							</view>
							<view class="progress">
								<view class="bg-reds" :style="'width:' + item.percent + '%;'"></view>
								<view class="piece">{{ $t(`已抢`) }}{{ item.percent }}%</view>
							</view>
						</view>
						<view class="grab bg-color" v-if="status == 1">{{ $t(`抢购中`) }}</view>
						<view class="grab bg-color" v-else-if="status == 2">{{ $t(`未开始`) }}</view>
						<view class="grab bg-color-hui" v-else>{{ $t(`已结束`) }}</view>
					</view>
				</block>
			</view>
		</view>
		<view class="noCommodity" v-if="seckillList.length == 0 && (page != 1 || active == 0)">
			<view class="emptyBox">
				<image :src="imgHost + '/statics/images/no-thing.png'"></image>
				<view class="tips">{{ $t(`暂无商品，去看点别的吧`) }}</view>
			</view>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
	</div>
</template>

<script>
import { getSeckillIndexTime, getSeckillList } from '../../../api/activity.js';
import home from '@/components/home/index.vue';
import colors from '@/mixins/color.js';
import { HTTP_REQUEST_URL } from '@/config/app';
export default {
	components: {
		home
	},
	mixins: [colors],
	data() {
		return {
			imgHost: HTTP_REQUEST_URL,
			topImage: '',
			seckillList: [],
			timeList: [],
			active: 5,
			scrollLeft: 0,
			interval: 0,
			status: 1,
			countDownHour: '00',
			countDownMinute: '00',
			countDownSecond: '00',
			page: 1,
			limit: 8,
			loading: false,
			loadend: false,
			pageloading: false,
			intoindex: '',
			time_id: 0
		};
	},
	onLoad() {
		this.getSeckillConfig();
	},
	methods: {
		getSeckillConfig: function () {
			let that = this;
			getSeckillIndexTime().then((res) => {
				that.topImage = res.data.lovely;
				that.timeList = res.data.seckillTime;
				that.active = res.data.seckillTimeIndex;
				that.$nextTick(() => {
					that.intoindex = 'sort' + res.data.seckillTimeIndex;
				});
				if (that.timeList.length) {
					// wxh.time(that.data.timeList[that.data.active].stop, that);
					that.scrollLeft = (that.active - 1.37) * 100;
					setTimeout(function () {
						that.loading = true;
					}, 2000);
					(that.seckillList = []), (that.page = 1);
					that.status = that.timeList[that.active].status;
					that.getSeckillList();
				}
			});
		},
		getSeckillList: function () {
			var that = this;
			var data = {
				page: that.page,
				limit: that.limit
			};
			if (that.loadend) return;
			if (that.pageloading) return;
			this.pageloading = true;
			getSeckillList(that.timeList[that.active].id, data)
				.then((res) => {
					var seckillList = res.data;
					var loadend = seckillList.length < that.limit;
					that.page++;
					(that.seckillList = that.seckillList.concat(seckillList)), (that.page = that.page);
					that.pageloading = false;
					that.loadend = loadend;
				})
				.catch((err) => {
					that.pageloading = false;
				});
		},
		settimeList: function (item, index) {
			var that = this;
			this.active = index;
			if (that.interval) {
				clearInterval(that.interval);
				that.interval = null;
			}
			that.interval = 0;
			that.countDownHour = '00';
			that.countDownMinute = '00';
			that.countDownSecond = '00';
			that.status = that.timeList[that.active].status;
			that.loadend = false;
			that.page = 1;
			that.seckillList = [];
			// wxh.time(e.currentTarget.dataset.stop, that);
			that.getSeckillList();
		},
		goDetails(item) {
			console.log(this.time_id);
			uni.navigateTo({
				url: '/pages/activity/goods_seckill_details/index?id=' + item.id + '&time_id=' + this.timeList[this.active].id
			});
		}
	},
	/**
	 * 页面上拉触底事件的处理函数
	 */
	onReachBottom: function () {
		this.getSeckillList();
	}
};
</script>

<style lang="scss">
page {
	background-color: #f5f5f5 !important;
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

.flash-sale .header {
	width: 710rpx;
	height: 300rpx;
	margin: -215rpx auto 0 auto;
	border-radius: 20rpx;
}

.flash-sale .header image {
	width: 100%;
	height: 100%;
	border-radius: 20rpx;
}

.flash-sale .seckillList {
	padding: 0 20rpx;
}

.flash-sale .seckillList .priceTag {
	width: 75rpx;
	height: 70rpx;
}

.flash-sale .seckillList .priceTag image {
	width: 100%;
	height: 100%;
}

.flash-sale .timeLsit {
	width: 610rpx;
	white-space: nowrap;
	margin: 10rpx 0;
}

.flash-sale .timeLsit .item {
	display: inline-block;
	font-size: 20rpx;
	color: #666;
	text-align: center;
	padding: 11rpx 0;
	box-sizing: border-box;
	height: 96rpx;
	margin-right: 35rpx;
}

.flash-sale .timeLsit .item .time {
	width: 120rpx;
	font-size: 36rpx;
	font-weight: 600;
	color: #333;
}

.flash-sale .timeLsit .item.on .time {
	color: var(--view-theme);
}

.flash-sale .timeLsit .item.on .state {
	width: 120rpx;
	height: 30rpx;
	line-height: 30rpx;
	border-radius: 15rpx;
	background: var(--view-theme);
	color: #fff;
}

.flash-sale .countDown {
	height: 92rpx;
	border-bottom: 1rpx solid #f0f0f0;
	margin-top: -14rpx;
	font-size: 28rpx;
	color: #282828;
}

.flash-sale .countDown .num {
	font-size: 28rpx;
	font-weight: bold;
	background-color: #ffcfcb;
	padding: 4rpx 7rpx;
	border-radius: 3rpx;
}

.flash-sale .countDown .text {
	font-size: 28rpx;
	color: #282828;
	margin-right: 13rpx;
}

.flash-sale .list .item {
	height: 230rpx;
	position: relative;
	width: 710rpx;
	margin: 0 auto 20rpx auto;
	background-color: #fff;
	border-radius: 20rpx;
	padding: 0 25rpx;
}

.flash-sale .list .item .pictrue {
	width: 180rpx;
	height: 180rpx;
	border-radius: 10rpx;
}

.flash-sale .list .item .pictrue image {
	width: 100%;
	height: 100%;
	border-radius: 10rpx;
}

.flash-sale .list .item .text {
	width: 460rpx;
	font-size: 30rpx;
	color: #333;
	height: 200rpx;
}

.flash-sale .list .item .text .name {
	width: 100%;
}

.flash-sale .list .item .text .money {
	font-size: 30rpx;
}

.flash-sale .list .item .text .money .num {
	font-size: 40rpx;
	font-weight: 500;
	font-family: 'Guildford Pro';
}

.flash-sale .list .item .text .money .y_money {
	font-size: 24rpx;
	color: #999;
	text-decoration-line: line-through;
	margin-left: 15rpx;
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
	overflow: hidden;
	background-color: #ffefef;
	width: 260rpx;
	border-radius: 18rpx;
	height: 18rpx;
	position: relative;
}

.flash-sale .list .item .text .progress .bg-reds {
	width: 0;
	height: 100%;
	transition: width 0.6s ease;
	background: linear-gradient(90deg, rgba(233, 51, 35, 1) 0%, rgba(255, 137, 51, 1) 100%);
}

.flash-sale .list .item .text .progress .piece {
	position: absolute;
	left: 8%;
	transform: translate(0%, -50%);
	top: 49%;
	font-size: 16rpx;
	color: #ffb9b9;
}

.flash-sale .list .item .grab {
	font-size: 28rpx;
	color: #fff;
	width: 150rpx;
	height: 54rpx;
	border-radius: 27rpx;
	text-align: center;
	line-height: 54rpx;
	position: absolute;
	right: 30rpx;
	bottom: 30rpx;
	background: #bbbbbb;
}

.flash-sale .saleBox {
	width: 100%;
	height: 230rpx;
	background: var(--view-theme);
	border-radius: 0 0 50rpx 50rpx;
}
</style>
