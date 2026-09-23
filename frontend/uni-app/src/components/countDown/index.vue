<template>
  <view class="time" :style="justifyLeft">
    <text class="red" v-if="tipText">{{ tipText }}</text>
    <text class="styleAll" :style="[timeStyle]" v-if="isDay === true">{{
      day
    }}</text>
    <text class="timeTxt red" :style="[timeTxtStyle]" v-if="dayText">{{
      dayText
    }}</text>
    <text class="styleAll" :style="[timeStyle]">{{ hour }}</text>
    <text class="timeTxt red" :style="[timeTxtStyle]" v-if="hourText">{{
      hourText
    }}</text>
    <text class="styleAll" :style="[timeStyle]">{{ minute }}</text>
    <text class="timeTxt red" :style="[timeTxtStyle]" v-if="minuteText">{{
      minuteText
    }}</text>
    <text class="styleAll" :style="[timeStyle]">{{ second }}</text>
    <text class="timeTxt red" :style="[timeTxtStyle]" v-if="secondText">{{
      secondText
    }}</text>
  </view>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

defineOptions({ name: "countDown" });

const props = defineProps({
  justifyLeft: {
    type: String,
    default: "",
  },
  //距离开始提示文字
  tipText: {
    type: String,
    default: "倒计时",
  },
  dayText: {
    type: String,
    default: "天",
  },
  hourText: {
    type: String,
    default: "时",
  },
  minuteText: {
    type: String,
    default: "分",
  },
  secondText: {
    type: String,
    default: "秒",
  },
  datatime: {
    type: Number,
    default: 0,
  },
  isDay: {
    type: Boolean,
    default: true,
  },
  bgColor: {
    type: String,
    default: "",
  },
  colors: {
    type: String,
    default: "",
  },
});

const day = ref("00");
const hour = ref("00");
const minute = ref("00");
const second = ref("00");

const timeStyle = computed(() => {
  return {
    background: props.bgColor,
    color: props.colors,
  };
});
const timeTxtStyle = computed(() => {
  if (props.colors === "rgba(255, 255, 255, 0)") {
    return {
      color: "transparent",
    };
  }
  return {};
});

function show_time() {
  function runTime() {
    //时间函数
    let intDiff = props.datatime - Date.parse(new Date()) / 1000; //获取数据中的时间戳的时间差；
    let d = 0,
      h = 0,
      m = 0,
      s = 0;
    if (intDiff > 0) {
      //转换时间
      if (props.isDay === true) {
        d = Math.floor(intDiff / (60 * 60 * 24));
      } else {
        d = 0;
      }
      h = Math.floor(intDiff / (60 * 60)) - d * 24;
      m = Math.floor(intDiff / 60) - d * 24 * 60 - h * 60;
      s =
        Math.floor(intDiff) -
        d * 24 * 60 * 60 -
        h * 60 * 60 -
        m * 60;
      if (h <= 9) h = "0" + h;
      if (m <= 9) m = "0" + m;
      if (s <= 9) s = "0" + s;
      day.value = d;
      hour.value = h;
      minute.value = m;
      second.value = s;
    } else {
      day.value = "00";
      hour.value = "00";
      minute.value = "00";
      second.value = "00";
    }
  }
  runTime();
  setInterval(runTime, 1000);
}

onMounted(() => {
  show_time();
});
</script>

<style>
.time {
  display: flex;
  justify-content: center;
}
.red {
  color: var(--view-theme);
  margin: 0 4rpx;
}
</style>
