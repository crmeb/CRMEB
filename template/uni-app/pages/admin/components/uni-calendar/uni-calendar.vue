<template>
	<view class="uni-calendar" @touchmove.stop.prevent="clean">
		<view v-if="!insert && show" class="uni-calendar__mask" :class="{ 'uni-calendar--mask-show': aniMaskShow }" @click="clean"></view>
		<view v-if="insert || show" class="uni-calendar__content" :class="{ 'uni-calendar--fixed': !insert, 'uni-calendar--ani-show': aniMaskShow }">
			<view v-if="!insert" class="uni-calendar__header uni-calendar--fixed-top">
				<view class="uni-calendar__header-btn-box" @click="close">
					<text class="uni-calendar__header-text uni-calendar--fixed-width">{{ $t(`取消`) }}</text>
				</view>
				<view class="uni-calendar__header-btn-box" @click="confirm">
					<text class="uni-calendar__header-text uni-calendar--fixed-width">{{ $t(`确定`) }}</text>
				</view>
			</view>
			<view class="uni-calendar__header">
				<view class="uni-calendar__header-btn-box" @click="pre">
					<view class="uni-calendar__header-btn uni-calendar--left"></view>
				</view>
				<text class="uni-calendar__header-text">{{ (nowDate.year || '') + $t(`年`) + (nowDate.month || '') + $t(`月`) }}</text>
				<view class="uni-calendar__header-btn-box" @click="next">
					<view class="uni-calendar__header-btn uni-calendar--right"></view>
				</view>
				<text class="uni-calendar__backtoday" @click="backtoday">{{ $t(`回到当天`) }}</text>
			</view>
			<view class="uni-calendar__box">
				<view v-if="showMonth" class="uni-calendar__box-bg">
					<text class="uni-calendar__box-bg-text">{{ nowDate.month }}</text>
				</view>
				<view class="uni-calendar__weeks">
					<view class="uni-calendar__weeks-day">
						<text class="uni-calendar__weeks-day-text">{{ $t(`天`) }}</text>
					</view>
					<view class="uni-calendar__weeks-day">
						<text class="uni-calendar__weeks-day-text">{{ $t(`一`) }}</text>
					</view>
					<view class="uni-calendar__weeks-day">
						<text class="uni-calendar__weeks-day-text">{{ $t(`二`) }}</text>
					</view>
					<view class="uni-calendar__weeks-day">
						<text class="uni-calendar__weeks-day-text">{{ $t(`三`) }}</text>
					</view>
					<view class="uni-calendar__weeks-day">
						<text class="uni-calendar__weeks-day-text">{{ $t(`四`) }}</text>
					</view>
					<view class="uni-calendar__weeks-day">
						<text class="uni-calendar__weeks-day-text">{{ $t(`五`) }}</text>
					</view>
					<view class="uni-calendar__weeks-day">
						<text class="uni-calendar__weeks-day-text">{{ $t(`六`) }}</text>
					</view>
				</view>
				<view class="uni-calendar__weeks" v-for="(item, weekIndex) in weeks" :key="weekIndex">
					<view class="uni-calendar__weeks-item" v-for="(w, weeksIndex) in item" :key="weeksIndex">
						<uni-calendar-item :weeks="w" :calendar="calendar" :selected="selected" :lunar="lunar" @change="choiceDate"></uni-calendar-item>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
import Calendar from './util.js';
import uniCalendarItem from './uni-calendar-item.vue';
export default {
	components: {
		uniCalendarItem
	},
	props: {
		/**
		 * 当前日期
		 */
		date: {
			type: String,
			default: ''
		},
		/**
		 * 打点日期
		 */
		selected: {
			type: Array,
			default() {
				return [];
			}
		},
		/**
		 * 是否开启阴历日期
		 */
		lunar: {
			type: Boolean,
			default: false
		},
		/**
		 * 开始时间
		 */
		startDate: {
			type: String,
			default: ''
		},
		/**
		 * 结束时间
		 */
		endDate: {
			type: String,
			default: ''
		},
		/**
		 * 范围
		 */
		range: {
			type: Boolean,
			default: false
		},
		/**
		 * 插入
		 */
		insert: {
			type: Boolean,
			default: true
		},
		/**
		 * 是否显示月份背景
		 */
		showMonth: {
			type: Boolean,
			default: true
		}
	},
	data() {
		return {
			show: false,
			weeks: [],
			calendar: {},
			nowDate: '',
			aniMaskShow: false
		};
	},
	watch: {
		selected(newVal) {
			this.cale.setSelectInfo(this.nowDate.fullDate, newVal);
			this.weeks = this.cale.weeks;
		}
	},
	created() {
		// 获取日历方法实例
		this.cale = new Calendar({
			date: this.date,
			selected: this.selected,
			startDate: this.startDate,
			endDate: this.endDate,
			range: this.range
		});
		this.init(this.cale.date.fullDate);
	},
	methods: {
		// 取消穿透
		clean() {},
		init(date) {
			this.weeks = this.cale.weeks;
			this.nowDate = this.calendar = this.cale.getInfo(date);
		},
		open() {
			this.show = true;
			this.$nextTick(() => {
				setTimeout(() => {
					this.aniMaskShow = true;
				}, 50);
			});
		},
		close() {
			this.aniMaskShow = false;
			this.$nextTick(() => {
				setTimeout(() => {
					this.show = false;
				}, 300);
			});
		},
		confirm() {
			this.setEmit('confirm');
			this.close();
		},
		change() {
			if (!this.insert) return;
			this.setEmit('change');
		},
		monthSwitch() {
			let { year, month } = this.nowDate;
			this.$emit('monthSwitch', {
				year,
				month: Number(month)
			});
		},
		setEmit(name) {
			let { year, month, date, fullDate, lunar, extraInfo } = this.calendar;
			this.$emit(name, {
				range: this.cale.multipleStatus,
				year,
				month,
				date,
				fulldate: fullDate,
				lunar,
				extraInfo: extraInfo || {}
			});
		},
		choiceDate(weeks) {
			if (weeks.disable) return;
			this.calendar = weeks;
			// 设置多选
			this.cale.setMultiple(this.calendar.fullDate);
			this.weeks = this.cale.weeks;
			this.change();
		},
		backtoday() {
			this.cale.multipleStatus.data = [this.nowDate.fullDate, this.nowDate.fullDate];
			this.cale.multipleStatus.before = this.nowDate.fullDate;
			this.cale.multipleStatus.after = this.nowDate.fullDate;
			this.cale.setDate(this.date);
			this.nowDate = this.calendar = this.cale.getInfo(this.date);
			this.change();
			this.$nextTick((e) => {
				this.weeks = this.cale.weeks;
			});
		},
		pre() {
			const preDate = this.cale.getDate(this.nowDate.fullDate, -1, 'month').fullDate;
			this.setDate(preDate);
			this.monthSwitch();
		},
		next() {
			const nextDate = this.cale.getDate(this.nowDate.fullDate, +1, 'month').fullDate;
			this.setDate(nextDate);
			this.monthSwitch();
		},
		setDate(date) {
			this.cale.setDate(date);
			this.weeks = this.cale.weeks;
			this.nowDate = this.cale.getInfo(date);
		}
	}
};
</script>

<style lang="scss" scoped>
.uni-calendar {
	/* #ifndef APP-NVUE */
	display: flex;
	/* #endif */
	flex-direction: column;
}

.uni-calendar__mask {
	position: fixed;
	bottom: 0;
	top: 0;
	left: 0;
	right: 0;
	background-color: $uni-bg-color-mask;
	transition-property: opacity;
	transition-duration: 0.3s;
	opacity: 0;
	/* #ifndef APP-NVUE */
	z-index: 99;
	/* #endif */
}

.uni-calendar--mask-show {
	opacity: 1;
}

.uni-calendar--fixed {
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	transition-property: transform;
	transition-duration: 0.3s;
	transform: translateY(460px);
	/* #ifndef APP-NVUE */
	z-index: 99;
	/* #endif */
}

.uni-calendar--ani-show {
	transform: translateY(0);
}

.uni-calendar__content {
	background-color: #fff;
}

.uni-calendar__header {
	position: relative;
	/* #ifndef APP-NVUE */
	display: flex;
	/* #endif */
	flex-direction: row;
	justify-content: center;
	align-items: center;
	height: 50px;
	border-bottom-color: $uni-border-color;
	border-bottom-style: solid;
	border-bottom-width: 1px;
}

.uni-calendar--fixed-top {
	/* #ifndef APP-NVUE */
	display: flex;
	/* #endif */
	flex-direction: row;
	justify-content: space-between;
	border-top-color: $uni-border-color;
	border-top-style: solid;
	border-top-width: 1px;
}

.uni-calendar--fixed-width {
	width: 50px;
	// padding: 0 15px;
}

.uni-calendar__backtoday {
	position: absolute;
	right: 0;
	top: 25rpx;
	padding: 0 5px;
	padding-left: 10px;
	height: 25px;
	line-height: 25px;
	font-size: 12px;
	border-top-left-radius: 25px;
	border-bottom-left-radius: 25px;
	color: $uni-text-color;
	background-color: $uni-bg-color-hover;
}

.uni-calendar__header-text {
	text-align: center;
	width: 100px;
	font-size: $uni-font-size-base;
	color: $uni-text-color;
}

.uni-calendar__header-btn-box {
	/* #ifndef APP-NVUE */
	display: flex;
	/* #endif */
	flex-direction: row;
	align-items: center;
	justify-content: center;
	width: 50px;
	height: 50px;
}

.uni-calendar__header-btn {
	width: 10px;
	height: 10px;
	border-left-color: $uni-text-color-placeholder;
	border-left-style: solid;
	border-left-width: 2px;
	border-top-color: $uni-color-subtitle;
	border-top-style: solid;
	border-top-width: 2px;
}

.uni-calendar--left {
	transform: rotate(-45deg);
}

.uni-calendar--right {
	transform: rotate(135deg);
}

.uni-calendar__weeks {
	position: relative;
	/* #ifndef APP-NVUE */
	display: flex;
	/* #endif */
	flex-direction: row;
}

.uni-calendar__weeks-item {
	flex: 1;
}

.uni-calendar__weeks-day {
	flex: 1;
	/* #ifndef APP-NVUE */
	display: flex;
	/* #endif */
	flex-direction: column;
	justify-content: center;
	align-items: center;
	height: 45px;
	border-bottom-color: #f5f5f5;
	border-bottom-style: solid;
	border-bottom-width: 1px;
}

.uni-calendar__weeks-day-text {
	font-size: 14px;
}

.uni-calendar__box {
	position: relative;
}

.uni-calendar__box-bg {
	/* #ifndef APP-NVUE */
	display: flex;
	/* #endif */
	justify-content: center;
	align-items: center;
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
}

.uni-calendar__box-bg-text {
	font-size: 200px;
	font-weight: bold;
	color: $uni-text-color-grey;
	opacity: 0.1;
	text-align: center;
	/* #ifndef APP-NVUE */
	line-height: 1;
	/* #endif */
}
</style>
