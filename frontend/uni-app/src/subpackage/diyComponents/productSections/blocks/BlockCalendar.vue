<template>
  <!--
    BlockCalendar 日历概览
    渲染：月历组件，按日期高亮库存状态与最低价
    适用：门票/酒店/课程/租赁的日期选择预览
    注：详情页只读展示，不采集下单数据；精确交互归下单页
  -->
  <view class="block-calendar">
    <!-- 月份切换 -->
    <view class="block-calendar__header">
      <view class="block-calendar__month-btn" @click="prevMonth">
        <text class="iconfont icon-xiangzuo"></text>
      </view>
      <view class="block-calendar__month-title">{{ currentTitle }}</view>
      <view class="block-calendar__month-btn" @click="nextMonth">
        <text class="iconfont icon-xiangyou"></text>
      </view>
    </view>

    <!-- 星期表头 -->
    <view class="block-calendar__weeks">
      <view
        class="block-calendar__week"
        v-for="(w, idx) in weekLabels"
        :key="idx"
      >
        {{ $t(w) }}
      </view>
    </view>

    <!-- 日期网格 -->
    <view class="block-calendar__days">
      <view
        class="block-calendar__day"
        :class="[
          'block-calendar__day--' + (day.status || 'empty'),
          {
            'block-calendar__day--outside': !day.inMonth,
            'block-calendar__day--selected': isSelectedDay(day),
            'block-calendar__day--in-range': isInRange(day),
            'block-calendar__day--range-start': isRangeStart(day),
            'block-calendar__day--range-end': isRangeEnd(day),
          },
        ]"
        v-for="(day, idx) in displayDays"
        :key="idx"
        @click="onSelectDay(day)"
      >
        <view class="block-calendar__day-num">{{ day.day }}</view>
        <view class="block-calendar__day-price" v-if="day.inMonth && day.min_price !== null && day.min_price !== undefined">
          <text>¥{{ day.min_price }}</text>
        </view>
        <view class="block-calendar__day-status" v-if="day.inMonth && day.status">
          <text>{{ statusText(day.status) }}</text>
        </view>
      </view>
    </view>

    <!-- 入住/离店日期展示（range 模式） -->
    <view class="block-calendar__range-info" v-if="isRangeMode && (checkInDate || checkOutDate)">
      <view class="block-calendar__range-item">
        <text class="block-calendar__range-label">{{ $t(`入住`) }}</text>
        <text class="block-calendar__range-value">{{ checkInDate || $t(`请选择`) }}</text>
      </view>
      <view class="block-calendar__range-separator">
        <text>{{ nightsText }}</text>
      </view>
      <view class="block-calendar__range-item">
        <text class="block-calendar__range-label">{{ $t(`离店`) }}</text>
        <text class="block-calendar__range-value">{{ checkOutDate || $t(`请选择`) }}</text>
      </view>
    </view>

    <!-- 图例 -->
    <view class="block-calendar__legend">
      <view class="block-calendar__legend-item">
        <view class="block-calendar__legend-dot block-calendar__legend-dot--sufficient"></view>
        <text>{{ $t(`充足`) }}</text>
      </view>
      <view class="block-calendar__legend-item">
        <view class="block-calendar__legend-dot block-calendar__legend-dot--tight"></view>
        <text>{{ $t(`紧张`) }}</text>
      </view>
      <view class="block-calendar__legend-item">
        <view class="block-calendar__legend-dot block-calendar__legend-dot--sold_out"></view>
        <text>{{ $t(`售罄`) }}</text>
      </view>
    </view>
  </view>
</template>

<script setup>
/**
 * 日历概览子渲染器
 * block 结构：{type, title, config:{mode, show_stock}, dates:[{date, status, min_price, stock_level}]}
 * dates 由后端按日期字符串（YYYY-MM-DD）下发，组件按当前月份过滤展示
 * mode 枚举：single 单日（门票）/ range 日期范围（酒店）
 */
import { ref, computed, watch } from "vue";
import i18n from "@/utils/lang.js";

defineOptions({ name: "BlockCalendar" });

const props = defineProps({
  block: {
    type: Object,
    default: () => ({}),
  },
  // 已选中的票种 id（来自 price_list block），选中后日历按该票种联动展示价格/状态
  selectedSkuId: {
    type: [Number, String],
    default: null,
  },
  // 日历风格：month=全月历网格 / week=单行日历（按周切换）
  calendarStyle: {
    type: String,
    default: "month",
  },
  // 回显初始选中日期（YYYY-MM-DD），购物车重选等场景由父组件传入
  initialSelected: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["blockSelect"]);

const weekLabels = ["日", "一", "二", "三", "四", "五", "六"];
// 当前展示的年月
const viewYear = ref(0);
const viewMonth = ref(0);
// 当前选中日期字符串（YYYY-MM-DD）—— single 模式使用
const selectedDate = ref("");
// range 模式：入住日期 / 离店日期
const checkInDate = ref("");
const checkOutDate = ref("");
// 单行日历：周偏移量（0=基准周），基准日期（dates 首日或今天）
const viewWeekOffset = ref(0);
const weekBaseDate = ref(null);

watch(
  () => props.initialSelected,
  (v) => {
    if (v !== null && v !== undefined && v !== "") {
      // 回显选中日期并跳转到对应月份
      selectedDate.value = v;
      const d = new Date(v.replace(/-/g, "/"));
      if (!isNaN(d.getTime())) {
        viewYear.value = d.getFullYear();
        viewMonth.value = d.getMonth() + 1;
      }
    }
  },
  { immediate: true }
);

/**
 * 是否为单行日历风格（按周切换）
 */
const isWeekMode = computed(() => {
  return props.calendarStyle === "week";
});
/**
 * 日历标题：全月历=YYYY年MM月；单行日历=当周所属月份（跨月显示 X月-Y月）
 */
const currentTitle = computed(() => {
  if (isWeekMode.value) {
    return weekTitleText.value;
  }
  if (!viewYear.value) return "";
  return `${viewYear.value}年${String(viewMonth.value).padStart(2, "0")}月`;
});
/**
 * 是否为日期范围模式（酒店入住/离店）
 */
const isRangeMode = computed(() => {
  const mode = (props.block && props.block.config && props.block.config.mode) || "single";
  return mode === "range";
});
/**
 * 当前展示的日期数组：week 模式返回单周 7 天，month 模式返回月网格
 */
const displayDays = computed(() => {
  return isWeekMode.value ? weekDays.value : calendarDays.value;
});
/**
 * 单行日历：当前周的 7 天（基于 weekBaseDate + viewWeekOffset）
 * 跨月日期按真实日期连续显示，不补齐上下月
 * @return {Array} [{day, dateStr, inMonth, status, min_price}]
 */
const weekDays = computed(() => {
  if (!weekBaseDate.value) return [];
  // 回到本周日（周起始），再加偏移
  const base = new Date(weekBaseDate.value);
  base.setDate(base.getDate() - base.getDay() + viewWeekOffset.value * 7);
  const datesMap = buildDatesMap();
  const days = [];
  for (let i = 0; i < 7; i++) {
    const d = new Date(base);
    d.setDate(base.getDate() + i);
    const dateStr = formatDate(d.getFullYear(), d.getMonth() + 1, d.getDate());
    const dateInfo = datesMap[dateStr] || {};
    const skuDetail = dateInfo.sku_detail || {};
    let dispPrice = dateInfo.min_price;
    let dispStatus = dateInfo.status || "";
    // 已选票种时按该票种联动展示当日价格/状态，未选时展示全局最低价/状态
    if (props.selectedSkuId !== null && props.selectedSkuId !== undefined) {
      const sd = skuDetail[props.selectedSkuId];
      if (sd) {
        dispPrice = sd.price;
        dispStatus = sd.status || dispStatus;
      } else if (Object.keys(skuDetail).length) {
        // 当日无该票种库存记录：视为该票种不可售
        dispStatus = "sold_out";
        dispPrice = null;
      }
    }
    days.push({
      day: d.getDate(),
      dateStr,
      inMonth: true,
      status: dispStatus,
      min_price: dispPrice,
    });
  }
  return days;
});
/**
 * 单行日历标题文案：同年同月=YYYY年MM月；跨月=X月-Y月
 */
const weekTitleText = computed(() => {
  const days = weekDays.value;
  if (!days.length) return "";
  const [fy, fm] = days[0].dateStr.split("-");
  const [ly, lm] = days[6].dateStr.split("-");
  if (fy === ly && fm === lm) {
    return `${fy}年${fm}月`;
  }
  return `${fm}月 - ${lm}月`;
});
/**
 * 住宿晚数文案
 */
const nightsText = computed(() => {
  if (checkInDate.value && checkOutDate.value) {
    const nights = calcNights(checkInDate.value, checkOutDate.value);
    return `${nights}晚`;
  }
  return "→";
});
/**
 * 日历日期网格（含上下月补齐）
 * @return {Array} [{day, inMonth, status, min_price}]
 */
const calendarDays = computed(() => {
  if (!viewYear.value || !viewMonth.value) return [];
  const firstDay = new Date(viewYear.value, viewMonth.value - 1, 1);
  const lastDay = new Date(viewYear.value, viewMonth.value, 0);
  const startWeekDay = firstDay.getDay();
  const daysInMonth = lastDay.getDate();

  // 上月补齐
  const prevMonthLastDay = new Date(viewYear.value, viewMonth.value - 1, 0).getDate();
  const days = [];
  for (let i = startWeekDay - 1; i >= 0; i--) {
    days.push({
      day: prevMonthLastDay - i,
      inMonth: false,
      status: "",
      min_price: null,
    });
  }
  // 当月日期
  const datesMap = buildDatesMap();
  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = formatDate(viewYear.value, viewMonth.value, d);
    const dateInfo = datesMap[dateStr] || {};
    // 已选票种时按该票种联动展示当日价格/状态，未选时展示全局最低价/状态
    const skuDetail = dateInfo.sku_detail || {};
    let dispPrice = dateInfo.min_price;
    let dispStatus = dateInfo.status || "";
    if (props.selectedSkuId !== null && props.selectedSkuId !== undefined) {
      const sd = skuDetail[props.selectedSkuId];
      if (sd) {
        dispPrice = sd.price;
        dispStatus = sd.status || dispStatus;
      } else if (Object.keys(skuDetail).length) {
        // 当日无该票种库存记录：视为该票种不可售
        dispStatus = "sold_out";
        dispPrice = null;
      }
    }
    days.push({
      day: d,
      inMonth: true,
      status: dispStatus,
      min_price: dispPrice,
    });
  }
  // 下月补齐至 7 的倍数
  const tailCount = (7 - (days.length % 7)) % 7;
  for (let i = 1; i <= tailCount; i++) {
    days.push({ day: i, inMonth: false, status: "", min_price: null });
  }
  return days;
});

watch(
  () => props.block,
  () => {
    initViewMonth();
  },
  { immediate: true }
);

/**
 * 初始化展示月份与单行日历基准日期
 * 全月历：取 dates 中第一个日期所在月，否则取当前月
 * 单行日历：weekBaseDate 取 dates 首日或今天，viewWeekOffset 重置为 0
 */
function initViewMonth() {
  const dates = (props.block && props.block.dates) || [];
  if (dates.length && dates[0].date) {
    const [y, m, d] = dates[0].date.split("-");
    viewYear.value = parseInt(y, 10);
    viewMonth.value = parseInt(m, 10);
    weekBaseDate.value = new Date(parseInt(y, 10), parseInt(m, 10) - 1, parseInt(d, 10));
  } else {
    const now = new Date();
    viewYear.value = now.getFullYear();
    viewMonth.value = now.getMonth() + 1;
    weekBaseDate.value = new Date(now.getFullYear(), now.getMonth(), now.getDate());
  }
  viewWeekOffset.value = 0;
}
/**
 * 构建 date 字符串到日期信息的映射
 */
function buildDatesMap() {
  const dates = (props.block && props.block.dates) || [];
  const map = {};
  dates.forEach((d) => {
    if (d && d.date) map[d.date] = d;
  });
  return map;
}
/**
 * 格式化日期为 YYYY-MM-DD
 */
function formatDate(y, m, d) {
  return `${y}-${String(m).padStart(2, "0")}-${String(d).padStart(2, "0")}`;
}
function statusText(status) {
  const map = {
    sufficient: "可买",
    tight: "紧张",
    sold_out: "售罄",
  };
  return map[status] || "";
}
/**
 * 上一页：month 模式切换上月，week 模式回退一周（viewWeekOffset 驱动 weekDays）
 */
function prevMonth() {
  if (isWeekMode.value) {
    viewWeekOffset.value--;
    return;
  }
  if (viewMonth.value === 1) {
    viewMonth.value = 12;
    viewYear.value--;
  } else {
    viewMonth.value--;
  }
}
/**
 * 下一页：month 模式切换下月，week 模式前进一周
 */
function nextMonth() {
  if (isWeekMode.value) {
    viewWeekOffset.value++;
    return;
  }
  if (viewMonth.value === 12) {
    viewMonth.value = 1;
    viewYear.value++;
  } else {
    viewMonth.value++;
  }
}
/**
 * 点击日期
 * 非当月日期不响应，售罄日期提示
 * single 模式：选中单个日期
 * range 模式：第一次点击=入住日，第二次点击=离店日（必须晚于入住日），第三次点击重置为入住日
 * @param {Object} day 日期对象 {day, inMonth, status, min_price}
 */
function onSelectDay(day) {
  if (!day.inMonth) return;
  if (day.status === "sold_out") {
    return uni.showToast({ title: i18n.t("该日已售罄"), icon: "none" });
  }
  // week 模式下 viewYear/viewMonth 与展示的周不同步，须直接取 day.dateStr
  const dateStr = isWeekMode.value ? day.dateStr : formatDate(viewYear.value, viewMonth.value, day.day);

  if (isRangeMode.value) {
    onRangeSelect(dateStr, day);
  } else {
    onSingleSelect(dateStr, day);
  }
}
/**
 * single 模式选中逻辑
 */
function onSingleSelect(dateStr, day) {
  selectedDate.value = dateStr;
  emit("blockSelect", {
    type: "calendar",
    item: { date: dateStr, day: day.day, status: day.status, min_price: day.min_price },
  });
}
/**
 * range 模式选中逻辑
 * 第一次点击=入住日，第二次点击=离店日
 * 离店日必须晚于入住日，否则重新选择入住日
 */
function onRangeSelect(dateStr, day) {
  if (!checkInDate.value || (checkInDate.value && checkOutDate.value)) {
    // 选择入住日（首次或重选）
    checkInDate.value = dateStr;
    checkOutDate.value = "";
    selectedDate.value = dateStr;
    emit("blockSelect", {
      type: "calendar",
      item: { check_in: dateStr, check_out: "", nights: 0 },
    });
  } else {
    // 选择离店日
    if (dateStr <= checkInDate.value) {
      // 离店日不晚于入住日，重新选择入住日
      checkInDate.value = dateStr;
      checkOutDate.value = "";
      selectedDate.value = dateStr;
      emit("blockSelect", {
        type: "calendar",
        item: { check_in: dateStr, check_out: "", nights: 0 },
      });
      return;
    }
    checkOutDate.value = dateStr;
    const nights = calcNights(checkInDate.value, checkOutDate.value);
    emit("blockSelect", {
      type: "calendar",
      item: { check_in: checkInDate.value, check_out: checkOutDate.value, nights: nights },
    });
  }
}
/**
 * 计算住宿晚数
 * @param {String} checkIn 入住日期 YYYY-MM-DD
 * @param {String} checkOut 离店日期 YYYY-MM-DD
 * @return {Number}
 */
function calcNights(checkIn, checkOut) {
  const diff = new Date(checkOut).getTime() - new Date(checkIn).getTime();
  return Math.round(diff / (24 * 60 * 60 * 1000));
}
/**
 * 判断日期是否选中（single 模式或 range 的入住日）
 */
function isSelectedDay(day) {
  if (!day.inMonth) return false;
  // week 模式下 viewYear/viewMonth 与展示的周不同步，须直接取 day.dateStr
  const dateStr = isWeekMode.value ? day.dateStr : formatDate(viewYear.value, viewMonth.value, day.day);
  if (isRangeMode.value) {
    return dateStr === checkInDate.value || dateStr === checkOutDate.value;
  }
  return selectedDate.value === dateStr;
}
/**
 * 判断日期是否在入住/离店区间内（不含首尾）
 */
function isInRange(day) {
  if (!isRangeMode.value || !checkInDate.value || !checkOutDate.value) return false;
  if (!day.inMonth) return false;
  const dateStr = isWeekMode.value ? day.dateStr : formatDate(viewYear.value, viewMonth.value, day.day);
  return dateStr > checkInDate.value && dateStr < checkOutDate.value;
}
/**
 * 判断是否为入住日
 */
function isRangeStart(day) {
  if (!isRangeMode.value || !checkInDate.value || !day.inMonth) return false;
  const dateStr = isWeekMode.value ? day.dateStr : formatDate(viewYear.value, viewMonth.value, day.day);
  return dateStr === checkInDate.value;
}
/**
 * 判断是否为离店日
 */
function isRangeEnd(day) {
  if (!isRangeMode.value || !checkOutDate.value || !day.inMonth) return false;
  const dateStr = isWeekMode.value ? day.dateStr : formatDate(viewYear.value, viewMonth.value, day.day);
  return dateStr === checkOutDate.value;
}
</script>

<style lang="scss" scoped>
.block-calendar {
  &__header {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8rpx 0 20rpx;
  }

  &__month-btn {
    padding: 8rpx 16rpx;
    color: #666;

    .iconfont {
      font-size: 24rpx;
    }
  }

  &__month-title {
    margin: 0 24rpx;
    font-size: 28rpx;
    font-weight: 600;
    color: #333;
  }

  &__weeks,
  &__days {
    display: flex;
    flex-wrap: wrap;
  }

  &__week {
    width: calc(100% / 7);
    text-align: center;
    font-size: 22rpx;
    color: #999;
    padding: 8rpx 0;
  }

  &__day {
    width: calc(100% / 7);
    text-align: center;
    padding: 12rpx 0;
    min-height: 88rpx;
    border-radius: 8rpx;
    transition: background-color 0.2s;

    &--outside {
      opacity: 0.3;
    }

    // 选中日期高亮
    &--selected {
      background: var(--view-op-ten);

      .block-calendar__day-num {
        color: var(--view-theme, #e93323);
        font-weight: 600;
      }
    }

    &-num {
      font-size: 26rpx;
      color: #333;
      margin-bottom: 4rpx;
    }

    &-price {
      font-size: 20rpx;
      color: var(--view-theme, #e93323);
      margin-bottom: 2rpx;
    }

    &-status {
      font-size: 18rpx;
      color: #999;
    }

    /* 充足：绿色 */
    &--sufficient {
      .block-calendar__day-status {
        color: #52c41a;
      }
    }

    /* 紧张：橙色 */
    &--tight {
      .block-calendar__day-status {
        color: #ff7a00;
      }
    }

    /* 售罄：灰色淡化 */
    &--sold_out {
      opacity: 0.5;
      .block-calendar__day-num {
        color: #999;
        text-decoration: line-through;
      }
    }

    /* range 模式：区间内日期 */
    &--in-range {
      background: var(--view-op-point-four);
      border-radius: 0;
    }

    /* range 模式：入住日（左圆角） */
    &--range-start {
      background: var(--view-theme, #e93323) !important;
      border-radius: 8rpx 0 0 8rpx;

      .block-calendar__day-num,
      .block-calendar__day-price,
      .block-calendar__day-status {
        color: #fff !important;
      }
    }

    /* range 模式：离店日（右圆角） */
    &--range-end {
      background: var(--view-theme, #e93323) !important;
      border-radius: 0 8rpx 8rpx 0;

      .block-calendar__day-num,
      .block-calendar__day-price,
      .block-calendar__day-status {
        color: #fff !important;
      }
    }
  }

  /* 入住/离店日期展示条 */
  &__range-info {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16rpx 0;
    margin-top: 12rpx;
    background: #f9f9f9;
    border-radius: 8rpx;
  }

  &__range-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
  }

  &__range-label {
    font-size: 22rpx;
    color: #999;
    margin-bottom: 4rpx;
  }

  &__range-value {
    font-size: 28rpx;
    font-weight: 600;
    color: var(--view-theme, #e93323);
  }

  &__range-separator {
    padding: 0 24rpx;
    font-size: 24rpx;
    color: #999;
  }

  &__legend {
    display: flex;
    justify-content: center;
    padding: 16rpx 0 0;

    &-item {
      display: flex;
      align-items: center;
      margin: 0 12rpx;
      font-size: 22rpx;
      color: #666;
    }

    &-dot {
      width: 12rpx;
      height: 12rpx;
      border-radius: 50%;
      margin-right: 6rpx;

      &--sufficient {
        background: #52c41a;
      }
      &--tight {
        background: #ff7a00;
      }
      &--sold_out {
        background: #d9d9d9;
      }
    }
  }
}
</style>
