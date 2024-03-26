<template>
	<div class="statistical-page" ref="container">
		<div class="navs">
			<div class="list">
				<div class="item" :class="time == 'today' ? 'on' : ''" @click="setTime('today')">
					{{ $t(`今天`) }}
				</div>
				<div class="item" :class="time == 'yesterday' ? 'on' : ''" @click="setTime('yesterday')">
					{{ $t(`昨天`) }}
				</div>
				<div class="item" :class="time == 'seven' ? 'on' : ''" @click="setTime('seven')">
					{{ $t(`最近7天`) }}
				</div>
				<div class="item" :class="time == 'month' ? 'on' : ''" @click="setTime('month')">
					{{ $t(`本月`) }}
				</div>
				<div class="item" :class="time == 'date' ? 'on' : ''" @click="dateTitle">
					<!-- <span class="iconfont icon-xiangxia"></span>
          <span v-for="(value, index) in renderValues" :key="index">
            {{ value }}</span
          > -->
					{{ $t(`自定义`) }}
				</div>
			</div>
		</div>
		<div class="wrapper">
			<div class="title">{{ time == 'date' ? '' : title }}{{ where.type == 1 ? $t(`营业额（元）`) : $t(`订单量（份）`) }}</div>
			<div class="money">{{ time_price }}</div>
			<div class="increase acea-row row-between-wrapper">
				<div>
					{{ time == 'date' ? '' : title }}{{ $t(`增长率`) }}：
					<span :class="increase_time_status === 1 ? 'red' : 'green'">
						{{ increase_time_status === 1 ? '' : '-' }}{{ growth_rate }}%
						<span class="iconfont" :class="increase_time_status === 1 ? 'icon-xiangshang1' : 'icon-xiangxia2'"></span>
					</span>
				</div>
				<div>
					{{ time == 'date' ? '' : title }}{{ $t(`增长`) }}：
					<span :class="increase_time_status === 1 ? 'red' : 'green'">
						{{ increase_time_status === 1 ? '' : '-' }}{{ increase_time }}
						<span class="iconfont" :class="increase_time_status === 1 ? 'icon-xiangshang1' : 'icon-xiangxia2'"></span>
					</span>
				</div>
			</div>
		</div>
		<div class="chart">
			<div class="chart-title">{{ $t(`单位`) }}（{{ where.type == 1 ? $t(`元.`) : $t(`份`) }}）</div>
			<canvas canvas-id="canvasLineA" id="canvasLineA" class="charts" disable-scroll="true" @touchstart="touchLineA" @touchmove="moveLineA" @touchend="touchEndLineA"></canvas>
		</div>
		<div class="public-wrapper">
			<div class="title">
				<span class="iconfont icon-xiangxishuju"></span>
				{{ $t(`详细数据`) }}
			</div>
			<div class="nav acea-row row-between-wrapper">
				<div class="data">{{ $t(`日期`) }}</div>
				<div class="browse">{{ $t(`订单数`) }}</div>
				<div class="turnover">{{ $t(`成交额`) }}</div>
			</div>
			<div class="conter">
				<div class="item acea-row row-between-wrapper" v-for="(item, index) in list" :key="index">
					<div class="data">{{ item.time }}</div>
					<div class="browse">{{ item.count }}</div>
					<div class="turnover">{{ item.price }}</div>
				</div>
			</div>
		</div>
		<!-- #ifdef H5 || APP-PLUS -->
		<uni-calendar
			ref="calendar"
			:date="info.date"
			:insert="info.insert"
			:lunar="info.lunar"
			:startDate="info.startDate"
			:endDate="info.endDate"
			:range="info.range"
			@confirm="confirm"
			:showMonth="info.showMonth"
		/>
		<div class="mask" @touchmove.prevent v-show="current === true" @click="close"></div>
		<!-- #endif -->
		<!-- <Loading :loaded="loaded" :loading="loading"></Loading> -->
	</div>
</template>
<script>
import uCharts from '../components/ucharts/ucharts';
import uniCalendar from '../components/uni-calendar/uni-calendar.vue';
var canvaLineA = null;
// import Calendar from 'mpvue-calendar'
// #ifdef MP-WEIXIN
// import 'mpvue-calendar/src/style.css
// #endif
// #ifdef H5
// import 'mpvue-calendar/src/browser-style.css'
// #endif

import { getStatisticsMonth, getStatisticsTime } from '@/api/admin';
// import Loading from "@components/Loading";
const year = new Date().getFullYear();
const month = new Date().getMonth() + 1;
const day = new Date().getDate();
export default {
	name: 'Statistics',
	components: {
		// Calendar,
		// uCharts
		uniCalendar
	},
	props: {},
	data: function () {
		return {
			value: [
				[year, month, day - 1],
				[year, month, day]
			],
			isrange: true,
			weekSwitch: false,
			ismulti: false,
			monFirst: true,
			clean: false, //简洁模式
			lunar: false, //显示农历
			renderValues: [],
			monthRange: [],
			current: false,
			where: {
				start: '',
				stop: '',
				type: ''
			},
			types: '', //类型|order=订单数|price=营业额
			time: '', //时间|today=今天|yesterday=昨天|month=本月
			title: '', //时间|today=今天|yesterday=昨天|month=本月
			growth_rate: '', //增长率
			increase_time: '', //增长率
			increase_time_status: '', //增长率
			time_price: '', //增长率
			loaded: false,
			loading: false,
			filter: {
				page: 1,
				limit: 10,
				start: '',
				stop: ''
			},
			list: [],
			// charts
			cWidth: '',
			cHeight: '',
			pixelRatio: 1,
			textarea: '',
			LineA: {
				categories: ['2012', '2013', '2014', '2015', '2016', '2017'],
				series: [
					{
						data: [35, 8, 25, 37, 4, 20]
					}
				]
			},
			info: {
				startDate: '',
				endDate: '',
				lunar: false,
				range: true,
				insert: false,
				selected: [],
				showMonth: false
			},
			type: '',
			before: '',
			after: ''
		};
	},
	watch: {
		'$route.params': function (newVal) {
			var that = this;
			if (newVal != undefined) {
				that.setType(newVal.type);
				that.setTime(newVal.time);
				that.getIndex();
			}
		}
	},
	onLoad: function (options) {
		this.type = options.type;
		if (options.before) {
			this.before = options.before;
		}
		if (options.after) {
			this.after = options.after;
		}
		this.setType(options.type);
		this.setTime(options.time);
		this.cWidth = uni.upx2px(690);
		this.cHeight = uni.upx2px(500);

		// this.handelRenderValues();
		// this.getIndex();
		this.getInfo();
		// this.$scroll(this.$refs.container, () => {
		// 	!this.loading && this.getInfo();
		// });
	},
	computed: {
		monthRangeText() {
			return this.monthRange.length ? this.$t(`固定`) : this.$t(`指定范围`);
		}
	},
	methods: {
		getIndex: function () {
			let tempDay = [];
			let tempNum = [];
			var that = this;
			getStatisticsTime(that.where).then(
				(res) => {
					var _info = res.data.chart,
						day = [],
						num = [];
					_info.forEach(function (item) {
						day.push(item.time);
						num.push(item.num);
					});
					that.growth_rate = res.data.growth_rate;
					that.increase_time = res.data.increase_time;
					that.increase_time_status = res.data.increase_time_status;
					that.time_price = res.data.time;

					res.data.chart.forEach((item, index) => {
						tempDay.push(item.time);
						tempNum.push(item.num);
					});
					that.LineA.categories = tempDay;
					that.LineA.series[0].data = tempNum;
					that.showLineA('canvasLineA', that.LineA);
				},
				(error) => {
					that.$util.Tips({
						title: error
					});
				}
			);
		},
		setTime: function (time) {
			let self = this;
			this.time = time;
			var year = new Date().getFullYear(),
				month = new Date().getMonth() + 1,
				day = new Date().getDate();
			this.list = [];
			this.filter.page = 1;
			this.loaded = false;
			this.loading = false;
			switch (time) {
				case 'today':
					this.where.start = new Date(Date.parse(year + '/' + month + '/' + day)).getTime() / 1000;
					this.where.stop = new Date(Date.parse(year + '/' + month + '/' + day)).getTime() / 1000 + 24 * 60 * 60 - 1;
					this.title = this.$t(`今天`);
					this.getIndex();
					this.getInfo();
					break;
				case 'yesterday':
					this.where.start = new Date(Date.parse(year + '/' + month + '/' + day)).getTime() / 1000 - 24 * 60 * 60;
					this.where.stop = new Date(Date.parse(year + '/' + month + '/' + day)).getTime() / 1000 - 1;
					this.title = this.$t(`昨天`);
					this.getIndex();
					this.getInfo();
					break;
				case 'month':
					this.where.start = new Date(year, new Date().getMonth(), 1).getTime() / 1000;
					this.where.stop = new Date(year, month, 1).getTime() / 1000 - 1;
					this.title = this.$t(`本月`);
					this.getIndex();
					this.getInfo();
					break;
				case 'seven':
					this.where.start = new Date(Date.parse(year + '/' + month + '/' + day)).getTime() / 1000 + 24 * 60 * 60 - 7 * 3600 * 24;
					this.where.stop = new Date(Date.parse(year + '/' + month + '/' + day)).getTime() / 1000 + 24 * 60 * 60 - 1;
					this.title = this.$t(`最近7天`);
					this.getIndex();
					this.getInfo();
					break;
				// #ifdef MP
				case 'date':
					let sArr = self.before.split('-');
					let aArr = self.after.split('-');
					let star = this.getTimestamp(sArr[0], sArr[1], sArr[2], 0, 0, 0);
					let stop = this.getTimestamp(aArr[0], aArr[1], aArr[2], 23, 59, 59);
					self.where.start = star;
					self.where.stop = stop;
					Promise.all([self.getIndex(), self.getInfo()]);
					break;
				// #endif
			}
		},
		getTimestamp(year, month, day, hour, min, sec) {
			return new Date(year, month - 1, day, hour, min, sec).getTime() / 1000;
		},
		setType: function (type) {
			switch (type) {
				case 'price':
					this.where.type = 1;
					break;
				case 'order':
					this.where.type = 2;
					break;
			}
		},
		dateTitle: function () {
			// #ifdef H5 || APP-PLUS
			this.$refs.calendar.open();
			this.time = 'date';
			// #endif
			// #ifdef MP
			uni.navigateTo({
				url: '/pages/admin/custom_date/index?type=' + this.type
			});
			// #endif
			// this.current = true;
		},
		close: function () {
			this.current = false;
		},
		getInfo: function () {
			var that = this;
			if (that.loading || that.loaded) return;
			that.loading = true;
			that.filter.start = that.where.start;
			that.filter.stop = that.where.stop;
			getStatisticsMonth(that.filter).then(
				(res) => {
					that.loading = false;
					that.loaded = res.data.length < that.filter.limit;
					that.list.push.apply(that.list, res.data);
					that.filter.page = that.filter.page + 1;
				},
				(error) => {
					that.$util.Tips({
						title: error
					});
				}
			);
		},
		// 创建charts
		showLineA(canvasId, chartData) {
			let _self = this;
			canvaLineA = new uCharts({
				$this: _self,
				canvasId: canvasId,
				type: 'line',
				fontSize: 11,
				padding: [15, 15, 0, 15],
				legend: {
					show: false,
					padding: 5,
					lineHeight: 11,
					margin: 5
				},
				dataLabel: true,
				dataPointShape: true,
				dataPointShapeType: 'hollow',
				background: '#FFFFFF',
				pixelRatio: _self.pixelRatio,
				categories: chartData.categories,
				series: chartData.series,
				animation: true,
				enableScroll: true, //开启图表拖拽功能
				xAxis: {
					disableGrid: false,
					type: 'grid',
					gridType: 'dash',
					itemCount: 4,
					scrollShow: true,
					scrollAlign: 'left'
				},
				yAxis: {
					//disabled:true
					gridType: 'dash',
					splitNumber: 8,
					min: 0,
					max: 30,
					format: (val) => {
						return val.toFixed(0);
					} //如不写此方法，Y轴刻度默认保留两位小数
				},
				width: _self.cWidth * _self.pixelRatio,
				height: _self.cHeight * _self.pixelRatio,
				extra: {
					line: {
						type: 'straight'
					}
				}
			});
		},
		// charts触摸事件
		touchLineA(e) {
			canvaLineA.scrollStart(e);
		},
		moveLineA(e) {
			canvaLineA.scroll(e);
		},
		touchEndLineA(e) {
			canvaLineA.scrollEnd(e);
		},
		// 日历确定
		confirm(e) {
			let self = this;
			if (e.range.after && e.range.before) {
				let star = new Date(e.range.before + ' 00:00:00').getTime() / 1000;
				let stop = new Date(e.range.after + ' 23:59:59').getTime() / 1000;
				self.where.start = star > stop ? stop :star ;
				self.where.stop = star < stop ? stop :star;
				self.list = [];
				self.filter.page = 1;
				self.loaded = false;
				self.loading = false;
				Promise.all([self.getIndex(), self.getInfo()]);
			}
		}
	},
	onReachBottom() {
		this.getInfo();
	}
};
</script>
<style>
/*交易额统计*/
.statistical-page .navs {
	width: 100%;
	height: 96upx;
	background-color: #fff;
	overflow: hidden;
	line-height: 96upx;
	position: fixed;
	top: 0;
	left: 0;
	z-index: 9;
}

.statistical-page .navs .list {
	overflow-y: hidden;
	overflow-x: auto;
	white-space: nowrap;
	-webkit-overflow-scrolling: touch;
	width: 100%;
}

.statistical-page .navs .item {
	font-size: 32upx;
	color: #282828;
	margin-left: 60upx;
	display: inline-block;
}

.statistical-page .navs .item.on {
	color: #2291f8;
}

.statistical-page .navs .item .iconfont {
	font-size: 25upx;
	margin-left: 13upx;
}

.statistical-page .wrapper {
	width: 740upx;
	background-color: #fff;
	border-radius: 10upx;
	margin: 119upx auto 0 auto;
	padding: 50upx 60upx;
}

.statistical-page .wrapper .title {
	font-size: 30upx;
	color: #999;
	text-align: center;
}

.statistical-page .wrapper .money {
	font-size: 72upx;
	color: #fba02a;
	text-align: center;
	margin-top: 10upx;
}

.statistical-page .wrapper .increase {
	font-size: 28upx;
	color: #999;
	margin-top: 20upx;
}

.statistical-page .wrapper .increase .red {
	color: #ff6969;
}

.statistical-page .wrapper .increase .green {
	color: #1abb1d;
}

.statistical-page .wrapper .increase .iconfont {
	font-size: 23upx;
	margin-left: 15upx;
}

.statistical-page .chart {
	width: 690upx;
	background-color: #fff;
	border-radius: 10upx;
	margin: 23upx auto 0 auto;
	/* padding: 25upx 22upx 0 22upx; */
}

.statistical-page .chart .chart-title {
	padding: 20upx 20upx 10upx;
	font-size: 26upx;
	color: #999;
}

.statistical-page .chart canvas {
	width: 100%;
	height: 530rpx;
}

.statistical-page .chart .company {
	font-size: 26upx;
	color: #999;
}

.yd-confirm {
	background-color: #fff;
	font-size: unset;
	width: 540upx;
	height: 250upx;
	border-radius: 40upx;
}

.yd-confirm-hd {
	text-align: center;
}

.yd-confirm-title {
	color: #030303;
	font-weight: bold;
	font-size: 36upx;
}

.yd-confirm-bd {
	text-align: center;
	font-size: 28upx;
	color: #333333;
}

.yd-confirm-ft {
	line-height: 90upx;
	margin-top: 14px;
	border-top: 1upx solid #eee;
}

.yd-confirm-ft > a {
	color: #e93323;
}

.yd-confirm-ft > a.primary {
	border-left: 1upx solid #eee;
	color: #e93323;
}

.echarts {
	width: 100%;
	height: 550upx;
}

.calendar-wrapper {
	position: fixed;
	bottom: 0;
	left: 0;
	width: 100%;
	z-index: 777;
	transform: translate3d(0, 100%, 0);
	transition: all 0.3s cubic-bezier(0.25, 0.5, 0.5, 0.9);
}

.calendar-wrapper.on {
	transform: translate3d(0, 0, 0);
}

.statistical-page .wrapper .increase {
	font-size: 26upx;
}

.statistical-page .wrapper .increase .iconfont {
	margin-left: 0;
}

.public-wrapper .title {
	font-size: 30upx;
	color: #282828;
	padding: 0 30upx;
	margin-bottom: 20upx;
}

.public-wrapper .title .iconfont {
	color: #2291f8;
	font-size: 40upx;
	margin-right: 13upx;
	vertical-align: middle;
}

.public-wrapper {
	margin: 18upx auto 0 auto;
	width: 690upx;
	background-color: #fff;
	border-radius: 10upx;
	padding-top: 25upx;
}

.public-wrapper .nav {
	padding: 0 30upx;
	height: 70upx;
	line-height: 70upx;
	font-size: 24upx;
	color: #999;
}

.public-wrapper .data {
	width: 210upx;
	text-align: left;
}

.public-wrapper .browse {
	width: 192upx;
	text-align: right;
}

.public-wrapper .turnover {
	width: 227upx;
	text-align: right;
}

.public-wrapper .conter {
	padding: 0 30upx;
}

.public-wrapper .conter .item {
	border-bottom: 1px solid #f7f7f7;
	height: 70upx;
	font-size: 24upx;
}

.public-wrapper .conter .item .turnover {
	color: #d84242;
}
</style>
